<?php

namespace App\Services\api;

use App\Exceptions\ProfitsoftAuthException;
use App\Exceptions\ProfitsoftRequestException;
use App\Models\Order;
use App\Models\OsagoTariff;
use App\Services\OrderService;
use App\Services\OsagoService;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Profitsoft
{
    private $apiUrl, $username, $password;

    public function __construct()
    {
        $this->apiUrl = env('PS_URL');
        $this->username = env('PS_LOGIN');
        $this->password = env('PS_PASSWORD');
    }

    /**
     * @throws ProfitsoftRequestException
     * @throws ProfitsoftAuthException
     */
    private function auth(): string
    {
        $requestUrl = $this->apiUrl . 'oauth';

        $formParams = [
            'username' => $this->username,
            'password' => $this->password
        ];

        try{
            $response = Http::asForm()->post($requestUrl, $formParams);

            $body = $response->body();
            $code = $response->status();

            if ($code !== 200) {
                throw new ProfitsoftAuthException('Profitsoft auth failed. Status code: ' . $code);
            }

            $result = json_decode($body);

            $token = $result->access_token ?? null;

            if (empty($token)) {
                throw new ProfitsoftAuthException('Profitsoft auth failed. Token is empty');
            }

            return $token;
        }catch (RequestException $e){
            throw new ProfitsoftRequestException('Auth() request error: ' . $e->getMessage());
        }
    }

    /**
     * @throws ProfitsoftRequestException
     */
    private function request(string $uri, array $params): array
    {
        try {
            $token = $this->auth();
        }catch (Exception $e) {
            Log::error($e);

            return [];
        }

        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = $this->apiUrl . $uri;
            $response = Http::withToken($token)->withBody($json, 'application/json')->post($requestUrl);

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                Log::info($json);
                Log::info($body);

                throw new ProfitsoftRequestException('Request() code: ' . $code);
            }

            return json_decode($body, true);
        }catch (RequestException $e){
            throw new ProfitsoftRequestException('Request() request error: ' . $e->getMessage());

            return [];
        }
    }

    public function searchCity(string $search, $searchBy = 'name', $onlyMtsbu = false): array
    {
        $formParams = [
            $searchBy => $search,
            "pageSize" => 500
        ];

        try {
            $result = $this->request('rest/dict/town/listByFilter', $formParams);
        } catch (Exception $e) {
            return [];
        }

        $cities = [];

        if (isset($result['status']) && $result['status'] === 'SUCCESS') {
            foreach ($result['objects'] as $city) {
                if ($onlyMtsbu && is_null($city['mtsbuId'])) continue;

                $cities[] = $city;
            }
        }

        return $cities;
    }

    public function reserve(Order $order): array
    {
        $calculate = (new OsagoService())->calculate($order->toArray());

        if($order->foreign_check){
            $DCityID = 3345;
        }else{
            $DCityID = ($order['city_id'] > 0) ? $order['city_id'] : 3797;
        }

        $discount = ($order->discount_check) ? 4: 0;

        $tariff = OsagoTariff::whereAlias($order->tariff)->first();

        $params = [
            "contractId" => env('PS_CONTRACT_ID'),
            "agentId" => env('PS_AGENT_ID'),
            "agent" => env('PS_AGENT_NAME'),
            "StartDate" => date('Y-m-d H:i:s', strtotime($order->polis_start)),
            "DPeriodID" => "13",
            "DBonusMalusID" => "3",
            "K1" => $calculate['k1'],
            "K2" => $calculate['k2'],
            "K3" => $calculate['k3'],
            "K4" => $calculate['k4'],
            "K5" => $calculate['k5'],
            "K6" => $calculate['k6'],
            "K7" => 1,
            "K8" => 1,
            "DPrivelegeID" => $discount,
            "DDiscountID" => "0",
            "Franchise" => $tariff->franchise,
            "InsPremium" => $order->price,
            "PaymentDate" => date('Y-m-d'),
            "DCitizenStatusID" => ($order->foreign_check) ? "2" : "1",
            "DPersonStatusID" => ($order->insurant->type == Order::INSURANT_JURISTIC) ? "2" : "1",
            "IdentCode" => $order->insurant->inn,
            'Surname' => $order->insurant->surname,
            'Name' => $order->insurant->name,
            'PName' => $order->insurant->patname,
            "BirthDate" => date('Y-m-d',strtotime($order->insurant->birth)),
            "Address" => $order->insurant->address,
            'DocumentType' => Order::DOC_API_ID[$order->insurant->doc_type],
            'DocName' =>  Order::DOC_NAMES[$order->insurant->doc_type],
            "DocSeries" => $order->insurant->doc_series,
            "DocNumber" => preg_replace('/\D/', '', $order->insurant->doc_number),
            'issued' => $order->insurant->doc_given,
            'issueDate' => date('Y-m-d',strtotime($order->insurant->doc_date)),
            "DCityID" => $DCityID,
            'RegNo' => $order->transport->gov_num,
            'VIN' => $order->transport->vin,
            'DVehicleTypeID' => $order->transport->power->api_id,
            'CarMake' => $order->transport->car_mark,
            'CarModel' => $order->transport->car_model,
            "AutoDescr" => $order->transport->car_mark . " " . $order->transport->car_model,
            "DSphereUseID" => "1",
            "DExpLimitID" => "1",
            "VehicleUsage" => '111111111111',
            "ProdYear" => $order->transport->car_year,
            "notes" => ""
        ];

        try {
            $response = $this->request('rest/api/contract/EPN24/v2?operation=register', $params);

            if (isset($response['result']) && $response['result'] == 1) {
                $contract = [
                    'number' => $response['MainCode'],
                    'external_id' => $response['systemContractId'],
                    'policy_link' => $response['PolicyDirectLink'],
                    'state' => 'draft'
                ];

                (new OrderService($order))->saveContract($contract);
            }
        } catch (Exception $e) {
            return [];
        }

        return $response;
    }

    public function confirm(Order $order): array
    {
        $params = [
            'MainCode' => $order->contract->number,
            'agentId' => env('PS_AGENT_ID'),
            'agent' => env('PS_AGENT_NAME'),
            'systemContractId' => $order->contract->external_id
        ];

        if (!is_null($order->send_sms)) {
            $params['otpCode'] = $order->send_sms;
        }

        try {
            $response = $this->request('rest/api/contract/EPN24/v2?operation=confirm', $params);

            $contract = [
                'number' => $response['MainCode'],
                'external_id' => $response['systemContractId'],
                'start_date' => $response['StartDate'],
                'end_date' => $response['EndDate'],
                'policy_link' => $response['PolicyDirectLink'],
                'state' => 'confirm'
            ];

            (new OrderService($order))->saveContract($contract);
        } catch (Exception $e) {
            return [];
        }

        return $response;
    }

}