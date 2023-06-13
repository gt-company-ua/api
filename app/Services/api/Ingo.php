<?php

namespace App\Services\api;

use App\Exceptions\OneCRequestException;
use App\Models\Order;
use App\Models\TransportCategory;
use App\Services\GreenCardService;
use App\Services\OrderService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Ingo
{
    const GREENCARD_TRANSPORT_CATEGORIES = ['car' => 'A', 'moto' => 'B', 'bus' => 'E', 'truck' => 'C', 'trailer' => 'F'];
    private function request(string $uri, array $params, $get = false, ?string $filename = null): array
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = $this->apiUrl . $uri;

            $client = Http::withHeaders([
                'authid' => env('INGO_LOGIN'),
                'authkey' => env('INGO_PASSWORD')
            ])
                ->timeout(100)
                ->withBody($json, 'application/json; charset=UTF-8');

            if ( ! is_null($filename)) {
                $tempName = storage_path('app/public/greencard')
                    . DIRECTORY_SEPARATOR . $filename;

                $client->sink($tempName);
            }

            if ($get) {
                $response = $client->get($requestUrl);
            } else {
                $response = $client->post($requestUrl);
            }

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                Log::info('Request() code: ' . $code);
                Log::info($json);
                Log::info($body);

                return [];
            } elseif ( ! is_null($filename)) {
                return ['status' => true];
            }

            return json_decode($body, true);
        }catch (RequestException $e){
            return [];
        }
    }

    private function periodFormat(int $period): string
    {
        return ($period === 0) ? '15d' : $period . 'm';
    }

    private function greenCardZone(string $country): string
    {
        return ($country === Order::TRIP_COUNTRY_SNG) ? 1 : 2;
    }

    public function greenCardCalculate(array $data)
    {
        $transportCategory = TransportCategory::whereId($data['transport']['transport_category_id'])->first();

        $params = [
            'startFrom' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'period' => $this->periodFormat($data['trip_duration']),
            'vehicleType' => self::GREENCARD_TRANSPORT_CATEGORIES[$transportCategory->alias] ?? null,
            'zone' => $this->greenCardZone($data['trip_country']),
        ];

        return $this->request('/greencard/calculate', $params);
    }

    public function greenCardDraft(Order $order)
    {
        $date = date('Y-m-d', strtotime('+1 day'));
        $startDate = date('Y-m-d', strtotime($order->polis_start));

        if ($startDate < $date) {
            $startDate = $date;
        }

        $params = [
            'startFrom' => $startDate . ' 00:00:00',
            'period' => $this->periodFormat($order->trip_duration),
            'zone' => $this->greenCardZone($order->trip_country),
            'customerIdentCode' => $order->insurant->inn,
            'customerFirstName' => $order->insurant->name_latin,
            'customerSecondName' => $order->insurant->surname_latin,
            'customerBirthday' => date('Y-m-d', strtotime($order->insurant->birth)),
            'vehicleType' => self::GREENCARD_TRANSPORT_CATEGORIES[$order->transport->category->alias] ?? null,
            'vehicleTitle' => $order->transport->car_mark . ' ' . $order->transport->car_model,
            'vehicleRegNo' => $order->transport->gov_num,
            'vehicleVin' => $order->transport->vin,
            'address' => $order->city_name,
        ];

        try {
            $response = $this->request('/greencard/register', $params);

            Log::debug("Save GreenCard request", $params);
            Log::debug("Save GreenCard response", $response);

            if ( ! empty($response['id'])) {
                $contract = [
                    'number' => $response['mainCode'],
                    'external_id' => $response['id'],
                    'state' => 'Draft',
                    'policy_link' => $response['publicUrl']
                ];
                (new OrderService($order))->saveContract($contract);
            }

            $order->status_contract = GreenCardService::STATUS_CONTRACT_SENT;
            $order->save();
        } catch (\Exception $e) {
            Log::error('Save GreenCard request error:' . $e->getMessage());

            $order->status_contract = GreenCardService::STATUS_CONTRACT_ERROR;
            $order->save();

            return [];
        }

        return $response;
    }

    public function greenCardConfirm(Order $order)
    {
        if (! is_null($order->contract) && ! empty($order->contract->number)) {
            $this->request('/greencard/' . $order->contract->number . '/confirm', []);
        }
    }

    public function greenCardPrintForm(Order $order)
    {
        $status = true;
        $filename = $order->id . '.zip';

        if (! is_null($order->contract) && ! empty($order->contract->number)) {
            $response = $this->request('/greencard/' . $order->contract->number . '/confirm', [], true, $filename);

            $status = $response['status'] ?? null;
        }

        return $status ? $filename : '';
    }
}
