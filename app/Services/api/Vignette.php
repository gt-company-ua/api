<?php

namespace App\Services\api;

use App\Models\Country;
use App\Models\VignetteOrder;
use App\Models\VignetteProduct;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Vignette
{
    private function request(string $uri, array $params = [], $get = false): array
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = env('VIGNETTE_URL') . $uri;

            $client = Http::withToken(env('VIGNETTE_TOKEN'))
                ->timeout(100)
                ->withBody($json, 'application/json; charset=UTF-8');

            if ($get) {
                $response = $client->get($requestUrl);
            } else {
                $response = $client->post($requestUrl);
            }

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                $getPost = ($get) ? 'GET' : 'POST';
                Log::info('Request() code: ' . $code . '. URL: ' . $getPost . ' ' . $requestUrl);
                Log::info($json);
                Log::info($body);
            }

            return json_decode($body, true);
        }catch (RequestException $e){
            return [];
        }
    }

    public function webhook(string $uri, array $params = [])
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = 'https://vignette.id/api/insurance/upload';

            $client = Http::withToken(env('VIGNETTE_TOKEN'))
                ->timeout(100)
                ->withBody($json, 'application/json; charset=UTF-8');

            $response = $client->post($requestUrl);

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                Log::info('Webhook request() code: ' . $code . '. URL: ' . $requestUrl);
                Log::info($json);
                Log::info($body);
            }
        }catch (RequestException $e){
            Log::error('Webhook error:' . $e->getMessage());
        }
    }

    public function checkVehicles(array $cars): bool
    {
        $checkCars = [];

        foreach ($cars as $car) {
            $country = Country::find($car['country_id']);

            $checkCars[] = [
                'plate' => $car['gov_num'],
                'country' => $country->code
            ];
        }

        $result = $this->request('/public/validate-vehicle?bug_report=true', ['cars' => $checkCars]);

        return empty($result['error']);
    }

    public function products(int $countryID): array
    {
        $getProducts = $this->request('/public/products?currency=UAH', [], true);
        if (!isset($getProducts['result'])) {
            return [];
        }

        $vignetteProducts = VignetteProduct::where('country_id', $countryID)->pluck('id', 'name');
        $products = [];

        foreach ($getProducts['result'] as $product) {
            if ( ! isset($vignetteProducts[$product['name']])) {
                continue;
            }

            $prices = [];

            foreach ($product['price'] as $period => $price) {
                $prices[] = [
                    'period' => $period,
                    'price' => $price['total_price'],
                    'currency' => $price['currency'],
                ];
            }

            $products[] = [
                'id' => $vignetteProducts[$product['name']],
                'title' => $product['title'],
                'icon' => $product['icon'],
                'color' => $product['color'],
                'priority' => $product['priority'],
                'restrictions' => $product['restrictions'],
                'prices' => $prices
            ];
        }

        return $products;
    }

    public function order(VignetteOrder $order): bool
    {
        $params = [
            'terms_and_privacy_accepted' => true,
            'order_has_been_paid' => true,
            'email' => $order->email,
            'cars' => [],
            'products' => [[
                'custom_id' => (string) $order->id,
                'name' => $order->product->name,
                'start_date' => strtotime($order->start_date),
                'period' => $order->period,
            ]],
        ];

        foreach ($order->cars as $car) {
            $params['cars'][] = [
                'plate' => $car->gov_num,
                'country' => $car->country->code,
            ];
        }

        $send = $this->request('/public/orders?bug_report=true', $params);

        Log::debug("Response save order", $send);

        if (!isset($send['result']) || !isset($send['result']['orders'][0])) {
            return false;
        }

        $order->amount_fee = $send['result']['orders'][0]['profit'];
        $order->external_id = $send['result']['orders'][0]['id'];
        $order->currency = $send['result']['orders'][0]['currency'];
        $order->save();

        return true;
    }
}
