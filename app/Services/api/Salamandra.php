<?php

namespace App\Services\api;

use App\Exceptions\OneCRequestException;
use App\Models\City;
use App\Models\Order;
use App\Models\TransportPower;
use App\Services\OrderService;
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

    private function dataForCalculator($data): ?array
    {
        $info = $this->info();
        if (is_null($info)) {
            return null;
        }

        $power = TransportPower::find($data['transport']['transport_power_id']);
        $city = City::find($data['city_id']);

        return [
            'productId' => $info['productId'],
            'cityId' => $city->external_id,
            'vehicleTypeId' => $power->api_id,
            'franchise' => $data['franchise'] ?? 0,
            'dgoLimit' => $data['dgo_limit'] ?? 0,
            'isPu' => $data['is_pu'] ?? false,
            'isDms' => $data['is_dms'] ?? false,
        ];
    }

    public function calculate(array $data, $all = false): ?array
    {
        $params = $this->dataForCalculator($data);
        if (is_null($params)) {
            return null;
        }

        $url = 'agent/osago/calculate';

        if ($all) {
            $url .= '/all';
        }

        return $this->request($url, $params);
    }

    public function order(Order $order)
    {
        $calculator = $this->dataForCalculator([
            'franchise' => $order->franchise,
            'dgo_limit' => $order->dgo_limit,
            'is_pu' => $order->is_pu,
            'is_dms' => $order->is_dms,
            'transport'=> ['transport_power_id' => $order->transport->power->id]

        ]);

        $city = City::find($order->city_id);

        $insurer = [
            'phone' => $order->insurant->phone,
            'email' => $order->email,
            'firstname' => $order->insurant->name,
            'lastname' => $order->insurant->surname,
            'midname' => $order->insurant->patronymic,
            'identnum' => $order->insurant->inn,
            'birthday' => date('d.m.Y',strtotime($order->insurant->birth)),
            'documentType' => Order::DOC_SALAMANDRA_API_ID[$order->insurant->doc_type],
            'documentNumber' => preg_replace('/\D/', '', $order->insurant->doc_number),
            'documentSeries' => $order->insurant->doc_type === Order::DOC_ID ? null : $order->insurant->doc_series,
            'documentDate' => date('d.m.Y',strtotime($order->insurant->doc_date)),
            'documentIssuedBy' => $order->insurant->doc_given,
            'cityName' => $city->name,
            'street' => $order->insurant->street,
            'house' => $order->insurant->house,
            'apartment' => $order->insurant->flat,
        ];

        $insuranceObject = [
            'carVIN' => $order->transport->vin,
            'carYear' => $order->transport->car_year,
            'carBrand' => $order->transport->car_mark,
            'carModel' => $order->transport->car_model,
            'carNumber' => $order->transport->gov_num,
        ];

        $params = [
            'calculator' => $calculator,
            'insurer' => $insurer,
            'insuranceObject' => $insuranceObject,
            'dateStart' => date('d.m.Y', strtotime($order->polis_start))
        ];

        $response = $this->request('agent/osago/order', $params);

        if (isset($response['success']) && $response['success']) {
            $number = null;

            if (isset($response['data']['splitPolicies']['osago'])) {
                $number = $response['data']['splitPolicies']['osago']['prefix']
                    . ' '
                    . $response['data']['splitPolicies']['osago']['number'];
            }

            $contract = [
                'number' => $number,
                'external_id' => $response['data']['id'],
                'state' => 'draft'
            ];

            (new OrderService($order))->saveContract($contract);
        } else {
            Log::debug("Reserve response", $response);
            Log::debug("Reserve request", $params);
        }
    }
}
