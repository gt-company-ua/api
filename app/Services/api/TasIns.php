<?php

namespace App\Services\api;

use App\Models\Order;
use App\Models\OrderContract;
use App\Models\TransportCategory;
use App\Services\OrderService;
use Doctrine\DBAL\ConnectionException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TasIns
{
    const GREENCARD_TRANSPORT_CATEGORIES = ['car' => 'A', 'moto' => 'B', 'truck' => 'C', 'trailer' => 'F'];
    const API_NAME = "TAS";
    const PHONE = '+380639583957';
    const EMAIL = 'greencard.ukraine.online@gmail.com';
    private function request(string $uri, array $params, $timeout = 10): array
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = env('TAS_URL') . $uri;
            $response = Http::withHeaders([
                'authid' => env('TAS_LOGIN'),
                'authkey' => env('TAS_PASSWORD')
            ])->timeout($timeout)->withBody($json, 'application/json')->post($requestUrl);

            $body = $response->body();
            $code = $response->status();

            if ($code >= 300) {
                Log::info('Request() code: ' . $code . '. URL: ' . $requestUrl);
                Log::info($json);
                Log::info($body);

                return [];
            }

            return json_decode($body, true);
        } catch (\Exception $e){
            Log::error($e->getMessage());

            return [];
        }
    }

    public function greenCardCalculate(array $data): array
    {
        $transportCategory = TransportCategory::whereId($data['transport']['transport_category_id'])->first();

        $params = [
            'agentId' => env('TAS_AGENT_ID'),
            'DPeriodID' => (string) $this->periodFormat($data['trip_duration']),
            'Territory' => $this->greenCardZone($data['trip_country']),
            'DVehicleTypeID' => self::GREENCARD_TRANSPORT_CATEGORIES[$transportCategory->alias] ?? null,
            'DExpAgeID' => "1",
        ];

        return $this->request('GC?operation=calculate', $params);
    }

    public function greenCardRegister(Order $order): array
    {
        $params = [
            "contractId" => env('TAS_CONTRACT_ID'),
            "agentId" => env('TAS_AGENT_ID'),
            "StartDate" => date('Y-m-d', strtotime($order->polis_start)),
            "DPeriodID" => (string) $this->periodFormat($order->trip_duration),
            'DExpAgeID' => "1",
            'Territory' => $this->greenCardZone($order->trip_country),
            "InsPremium" => $order->price,
            "DPersonStatusID" => ($order->insurant->type == Order::INSURANT_JURISTIC) ? "U" : "P",
            "DCitizenStatusID" => ($order->foreign_check) ? "2" : "1",

            "IdentCode" => $order->insurant->inn,
            "BirthDate" => date('Y-m-d',strtotime($order->insurant->birth)),
            'Name' => $order->insurant->name,
            'Name_eng' => $order->insurant->name_latin,
            'Surname' => $order->insurant->surname,
            'Surname_eng' => $order->insurant->surname_latin,
            'PName' => $order->insurant->patronymic,
            'Country' => "", //TODO узнать
            "Address" => $order->insurant->address,
            "PhoneNumber" => self::PHONE,

            'RegNo' => $order->transport->gov_num,
            'VIN' => $order->transport->vin,
            'DVehicleTypeID' => self::GREENCARD_TRANSPORT_CATEGORIES[$order->transport->category->alias] ?? null,
            'CarMake' => $order->transport->car_mark,
            'CarModel' => $order->transport->car_model,
            "ProdYear" => $order->transport->car_year,

            'DocTypeID' => Order::DOC_TAS_API_ID[$order->insurant->doc_type] ?? 1,
            'DocName' =>  Order::DOC_NAMES[$order->insurant->doc_type] ?? Order::DOC_NAMES[Order::DOC_PASSPORT],
            "DocSeries" => $order->insurant->doc_series,
            "DocNumber" => preg_replace('/\D/', '', $order->insurant->doc_number),
            //'DocIssued' => $order->insurant->doc_given,
            //'DocIssueDate' => date('Y-m-d',strtotime($order->insurant->doc_date)),
        ];

        try {
            $response = $this->request('GC?operation=register', $params, 20);

            Log::debug("Save Tas GreenCard (order: ".$order->id.") request", $params);
            Log::debug("Save Tas GreenCard (order: ".$order->id.") response", $response);

            if (! empty($response['Result'])) {
                $contract = [
                    'number' => $response['Number'],
                    'external_id' => $response['MainCode'],
                    'state' => 'Draft',
                    'policy_link' => $response['OfferForm'] ?? '',
                    'api_name' => self::API_NAME
                ];
                (new OrderService($order))->saveContract($contract);
            }

            $order->status_contract = OrderContract::STATUS_CONTRACT_SENT;
            $order->save();
        } catch (\Exception $e) {
            Log::error('Save Tas GreenCard request error:' . $e->getMessage());

            $order->status_contract = OrderContract::STATUS_CONTRACT_ERROR;
            $order->save();

            return [];
        }

        return $response;
    }

    public function greenCardConfirm(Order $order): ?array
    {
        if (! is_null($order->contract) && ! empty($order->contract->external_id)) {
            $sms = (!empty($order->send_sms)) ? $order->send_sms : mt_rand(100000, 999999);

            $params = [
                'contractId' => env('TAS_CONTRACT_ID'),
                'agentId' => env('TAS_AGENT_ID'),
                'Number' => $order->contract->number,
                'Otp' => $sms,
            ];

            $response =  $this->request('GC?operation=confirm', $params, 20);
            Log::debug("Confirm Tas GreenCard (order: ".$order->id.") request", $params);
            Log::debug("Confirm Tas GreenCard (order: ".$order->id.") response", $response);

            if (! empty($response['Result'])) {
                $contract = [
                    'state' => 'Signed',
                    'number' => $response['Number'],
                    'external_id' => $response['MainCode'],
                    'policy_link' => $response['printForm'] ?? '',
                ];
                (new OrderService($order))->saveContract($contract);
            } else if (isset($response['Result'])) {
                $contract = [
                    'state' => 'Error',
                    'response' => $response['Texterror'] ?? '',
                ];
                (new OrderService($order))->saveContract($contract);
            }
        }

        return null;
    }

    private function periodFormat(int $period): int
    {
        return ($period === 0) ? 15 : $period;
    }

    private function greenCardZone(string $country): string
    {
        return ($country === Order::TRIP_COUNTRY_SNG) ? 7 : 6;
    }
}
