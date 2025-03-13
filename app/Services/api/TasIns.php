<?php

namespace App\Services\api;

use App\Models\Order;
use App\Models\OrderContract;
use App\Models\OsagoCashback;
use App\Models\OsagoCity;
use App\Models\TransportCategory;
use App\Models\TransportPower;
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
    const POST_CODE = '01001';
    const EMAIL = 'greencard.ukraine.online@gmail.com';
    const OSAGO_PERIOD = "13";
    const DSphereUseID_REGULAR = "1";
    const DSphereUseID_TAXI = "2";
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
            $jsonResponse = json_decode($body, true);

            if ($code >= 300 || (isset($jsonResponse['result']) && $jsonResponse['result'] === false)) {
                Log::info('Request() code: ' . $code . '. URL: ' . $requestUrl);
                Log::info($json);
                Log::info($body);

                return (isset($jsonResponse['result'])) ? $jsonResponse : [];
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
            'contractId' => 'tas-' . $order->id,
            "agentId" => env('TAS_AGENT_ID'),
            "StartDate" => date('Y-m-d', strtotime($order->polis_start)),
            "DPeriodID" => (string) $this->periodFormat($order->trip_duration),
            'DExpAgeID' => "1",
            'Territory' => $this->greenCardZone($order->trip_country),
            "InsPremium" => $order->full_price,
            "DPersonStatusID" => ($order->insurant->type == Order::INSURANT_JURISTIC) ? "U" : "P",
            "DCitizenStatusID" => ($order->foreign_check) ? "2" : "1",

            "IdentCode" => $order->insurant->inn,
            "BirthDate" => date('Y-m-d',strtotime($order->insurant->birth)),
            'Name' => $order->insurant->name,
            'Name_eng' => $order->insurant->name_latin,
            'Surname' => $order->insurant->surname,
            'Surname_eng' => $order->insurant->surname_latin,
            'PName' => $order->insurant->patronymic,
            'Country' => "Україна",
            "Address" => $order->city_name,
            "PhoneNumber" => self::PHONE,

            'RegNo' => $order->transport->gov_num,
            'VIN' => $order->transport->vin,
            'DVehicleTypeID' => self::GREENCARD_TRANSPORT_CATEGORIES[$order->transport->category->alias] ?? null,
            'CarMake' => $order->transport->car_mark,
            'CarModel' => $order->transport->car_model,
            "ProdYear" => $order->transport->car_year,

            'DocTypeID' => Order::DOC_TAS_API_ID[$order->insurant->doc_type] ?? Order::DOC_FOREIGN_PASSPORT,
            'DocName' =>  Order::DOC_NAMES[$order->insurant->doc_type] ?? Order::DOC_NAMES[Order::DOC_FOREIGN_PASSPORT],
            "DocSeries" => $order->insurant->doc_series,
            "DocNumber" => preg_replace('/\D/', '', $order->insurant->doc_number),
            //'DocIssued' => $order->insurant->doc_given,
            //'DocIssueDate' => date('Y-m-d',strtotime($order->insurant->doc_date)),
        ];

        try {
            $response = $this->request('GC?operation=register', $params, 30);

            Log::debug("Save Tas GreenCard (order: ".$order->id.") request", $params);
            Log::debug("Save Tas GreenCard (order: ".$order->id.") response", $response);

            if (! empty($response['result'])) {
                $contract = [
                    'number' => $response['Number'],
                    'file_link' => $response['MainCode'],
                    'state' => 'Draft',
                    'policy_link' => $response['offerForm'] ?? '',
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
        if (! is_null($order->contract) && ! empty($order->contract->number)) {
            $sms = (!empty($order->send_sms)) ? $order->send_sms : mt_rand(100000, 999999);

            $params = [
                'contractId' => 'tas-' . $order->id,
                'agentId' => env('TAS_AGENT_ID'),
                'Number' => $order->contract->number,
                'Otp' => $sms,
            ];

            $response =  $this->request('GC?operation=confirm', $params, 30);
            Log::debug("Confirm Tas GreenCard (order: ".$order->id.") request", $params);
            Log::debug("Confirm Tas GreenCard (order: ".$order->id.") response", $response);

            if (! empty($response['result'])) {
                $contract = [
                    'state' => 'Signed',
                    'number' => $response['Number'],
                    'file_link' => $response['MainCode'],
                    'policy_link' => $response['printForm'] ?? '',
                ];
                (new OrderService($order))->saveContract($contract);
            } else if (isset($response['result'])) {
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

    public function downloadPolicy(Order $order, $filename): array
    {
        if (is_null($order->contract) || empty($order->contract->policy_link)) {
            Log::debug("Order hasn't contract: " . $order->id);
            return [];
        }

        $files = [];
        $client = Http::timeout(30);
        $tempName = storage_path('app/public/policies')
            . DIRECTORY_SEPARATOR . $filename;

        $client->sink($tempName);

        $response = $client->get($order->contract->policy_link);
        if ($response->status() === 200) {
            $files[] = $filename;
        }

        return $files;
    }

    public function updateContractDownload(OrderContract $contract, $statusCode)
    {
        $contract->download_attempts ++;
        $contract->download_status_code = $statusCode;
        $contract->sent_police = $statusCode === 200;

        if ($contract->download_attempts > 10) {
            $contract->state = 'Error';
        }

        $contract->save();
    }

    public function osagoCalculate(array $data): array
    {
        $power = TransportPower::find($data['transport']['transport_power_id']);

        if (empty($data['city_id'])) {
            $ariaValue = 5;
            $ariaKey = 'Zone';
        } else {
            $city = OsagoCity::find($data['city_id']);
            $ariaValue = $city->koatuu;
            $ariaKey = 'CityCode';
        }

        $params = [
            'agentId' => env('TAS_AGENT_ID'),
            'DPeriodID' => self::OSAGO_PERIOD,
            'DPrivelegeID' => ($data['discount_check']) ? "1" : "0",
            "DCitizenStatusID" => empty($data['foreign_check']) ? "1" : "2",
            "DPersonStatusID" => ($data['insurant']['type'] == Order::INSURANT_JURISTIC) ? "2" : "1",
            $ariaKey => $ariaValue,
            'isOwner' => true,
            'ownerBirthYear' => date('Y', strtotime($data['insurant']['birth'])),
            'DVehicleTypeID' => $power->api_id,
            'DSphereUseID' => ($data['use_as_taxi']) ? self::DSphereUseID_TAXI : self::DSphereUseID_REGULAR,
            'DAgeExpID' => "1",
            'isUaRegistration' => empty($data['foreign_check']),
        ];

        $response = $this->request('Osago/?operation=calculate', $params);

        if (!empty($response['result'])) {
            $cashback = OsagoCashback::where('franchise', $data['franchise'])->first();
            if (!is_null($cashback)) {
                $total = round($response['InsPremium'], 2);
                $response['cashback'] = round($total / 100 * $cashback->amount);
            }
        } else {
            Log::debug("Osago Calculate Error", $response);
        }

        return $response;
    }

    public function osagoRegister(Order $order): array
    {
        $params = [
            'contractId' => 'tas-' . $order->id,
            "agentId" => env('TAS_AGENT_ID'),
            "СalcId" => $order->contract_num,
            "StartDate" => date('Y-m-d H:i:s', strtotime($order->polis_start)),
            "PaymentDate" => date('Y-m-d'),

            "InsPremium" => $order->full_price,
            "DPersonStatusID" => ($order->insurant->type == Order::INSURANT_JURISTIC) ? "U" : "P",
            "DCitizenStatusID" => ($order->foreign_check) ? "2" : "1",

            "IdentCode" => $order->insurant->inn,
            "BirthDate" => date('Y-m-d',strtotime($order->insurant->birth)),
            'Name' => $order->insurant->name,
            'Surname' => $order->insurant->surname,
            'PName' => $order->insurant->patronymic,
            "Address" => $order->city_name,
            "PostCode" => '01001',
            "PhoneNumber" => self::PHONE,

            'RegNo' => $order->transport->gov_num,
            'VIN' => $order->transport->vin,
            'CarMake' => $order->transport->car_mark,
            'CarModel' => $order->transport->car_model,
            'AutoDescr' => $order->transport->car_mark . ' ' . $order->transport->car_model,
            "ProdYear" => $order->transport->car_year,

            'DocumentType' => Order::DOC_OSAGO_TAS_API_ID[$order->insurant->doc_type] ?? Order::DOC_FOREIGN_PASSPORT,
            'DocName' =>  Order::DOC_NAMES[$order->insurant->doc_type] ?? Order::DOC_NAMES[Order::DOC_FOREIGN_PASSPORT],
            "DocSeries" => $order->insurant->doc_series,
            "DocNumber" => preg_replace('/\D/', '', $order->insurant->doc_number),
            'DocIssued' => $order->insurant->doc_given,
            'DocIssueDate' => date('Y-m-d',strtotime($order->insurant->doc_date)),
        ];

        try {
            $response = $this->request('Osago?operation=register', $params, 20);

            Log::debug("Save Tas Osago (order: ".$order->id.") request", $params);
            Log::debug("Save Tas Osago (order: ".$order->id.") response", $response);

            if (! empty($response['result'])) {
                $contract = [
                    'number' => $response['MainCode'],
                    'file_link' => $response['policyDirectLink'],
                    'state' => 'Draft',
                    'policy_link' => $response['offerForm'] ?? '',
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

    public function osagoConfirm(Order $order): ?array
    {
        if (! is_null($order->contract) && ! empty($order->contract->number)) {
            $sms = (!empty($order->send_sms)) ? $order->send_sms : mt_rand(100000, 999999);

            $params = [
                'agentId' => env('TAS_AGENT_ID'),
                'MainCode' => $order->contract->number,
                'Otp' => $sms,
            ];

            $response =  $this->request('GC?operation=signconfirm', $params, 20);
            Log::debug("Confirm Tas Osago (order: ".$order->id.") request", $params);
            Log::debug("Confirm Tas Osago (order: ".$order->id.") response", $response);

            if (! empty($response['result'])) {
                $contract = [
                    'state' => 'Signed',
                    'number' => $response['Number'],
                    'file_link' => $response['MainCode'],
                    'policy_link' => $response['printForm'] ?? '',
                ];
                (new OrderService($order))->saveContract($contract);
            } else if (isset($response['result'])) {
                $contract = [
                    'state' => 'Error',
                    'response' => $response['Texterror'] ?? '',
                ];
                (new OrderService($order))->saveContract($contract);
            }
        }

        return null;
    }
}
