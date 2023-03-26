<?php

namespace App\Services\api;

use App\Exceptions\OneCRequestException;
use App\Models\City;
use App\Models\TransportPower;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Salamandra
{
    private function request(string $uri, $params = [], $sendPost = true): array
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = env('SALAMANDRA_URL') . $uri;

            $prepareRequest = Http::withToken(env('SALAMANDRA_TOKEN'));

            if ($sendPost) {
                $response = $prepareRequest->withBody($json, 'application/json')->post($requestUrl);
            } else {
                $response = $prepareRequest->get($requestUrl, $params);
            }

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                Log::info('Request() code: ' . $code);
                Log::info($json);
                Log::info($body);

                return [];
            }

            return json_decode($body, true);
        }catch (RequestException $e){
            Log::error("Salamandra exception:" . $e->getMessage());

            return [];
        }
    }

    public function cities(): array
    {
        return $this->request('agent/cities', [], false);
    }

    private function info()
    {
        $response = $this->request('agent/osago/info', [], false);

        if ( ! isset($response['success']) || $response['success'] !== true) {
            return null;
        }

        return $response['data'];
    }

    public function calculate(array $data, $all = false): ?array
    {
        $info = $this->info();
        if (is_null($info)) {
            return null;
        }

        $power = TransportPower::find($data['transport']['transport_power_id']);
        $city = City::find($data['city_id']);

        $params = [
            'productId' => $info['productId'],
            'cityId' => $city->external_id,
            'vehicleTypeId' => $power->api_id,
            'franchise' => $data['franchise'] ?? 0,
            'dgoLimit' => $data['dgo_limit'] ?? 0,
            'isPu' => $data['is_pu'] ?? false,
            'isDms' => $data['is_dms'] ?? false,
        ];

        $url = 'agent/osago/calculate';

        if ($all) {
            $url .= '/all';
        }

        return $this->request($url, $params);
    }
}
