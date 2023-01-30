<?php

namespace App\Services\api;

use App\Exceptions\OneCRequestException;
use App\Models\Order;
use App\Services\GreenCardService;
use App\Services\OrderService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneC
{
    private $apiUrl, $username, $password;

    public function __construct()
    {
        $this->apiUrl = env('ONE_C_URL');
        $this->username = env('ONE_C_LOGIN');
        $this->password = env('ONE_C_PASSWORD');
    }


    /**
     * @throws OneCRequestException
     */
    private function request(string $uri, array $params, ?string $filename = null): array
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = $this->apiUrl . $uri;

            $client = Http::withBasicAuth($this->username, $this->password)
                ->withBody($json, 'application/json; charset=UTF-8');

            if ( ! is_null($filename)) {
                $tempName = storage_path('app/public/greencard')
                    . DIRECTORY_SEPARATOR . $filename;

                $client->sink($tempName);
            }

            $response = $client->post($requestUrl);

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                Log::info('Request() code: ' . $code);
                Log::info($json);
                Log::info($body);

                throw new OneCRequestException('Request() code: ' . $code);
                return [];
            } elseif ( ! is_null($filename)) {
                return ['status' => true];
            }

            return json_decode($body, true);
        }catch (RequestException $e){
            throw new OneCRequestException('Request() request error: ' . $e->getMessage());

            return [];
        }
    }

    private function internalId($orderId): string
    {
        return "api-id-" . $orderId;
    }

    public function saveGreenCard(Order $order, $status = 'Signed'): array
    {
        $parseInn = (new OrderService(null))->parseInn($order->insurant->inn);

        $calculateParams = [
            'transport' => [
                'transport_category_id' => $order->transport->transport_category_id
            ],
            'trip_country' => $order->trip_country,
            'trip_duration' => $order->trip_duration
        ];

        $price = (new GreenCardService())->calculate($calculateParams, true);

        $date = date('Y-m-d', strtotime('+1 day'));
        $startDate = date('Y-m-d', strtotime($order->polisStart));

        if ($startDate < $date) {
            $startDate = $date;
        }

        $contract = $order->contract;

        $params = [
            'Product' => 'ZK',
            'ID' => $this->internalId($order->id),
            'Status' => $status,
            'BMR' => ($order->trip_country === Order::TRIP_COUNTRY_SNG),
            'Date' => date('Y-m-d\TH:i:s'),
            'StartDate' => $startDate . 'T00:00:00',
            'Srok' => $order->trip_duration,
            'BlankNumber' => (empty($contract->number)) ? 0 : $contract->number,
            'City' => $order->city_name,
            'InsPremium' => round($price, 2),
            'IsCitizen' => true,
            'CitizenCountry' => 'Ukraine',

            'IsPerson' => true,
            'IdentCode' => $order->insurant->inn,
            //'EDDRCode' => '',
            'Name' => $order->insurant->name,
            'LastName' => $order->insurant->surname,
            'MiddleName' => '',
            'BirthDate' => date('Y-m-d', strtotime($order->insurant->birth)),
            'Gender' => $parseInn['sex'] ?? 'Male',
            'Address' => $order->city_name,
            'Phone' => $order->insurant->phone,
            'NameLat' => $order->insurant->name_latin . ' ' . $order->insurant->surname_latin,
            'AddressLat' => $this->transliterate($order->city_name),
            'Email' => $order->email,

            'DocumentType' => -1,
            'DocSeries' => '',
            'DocNumber' => '',
            'Issued' => '',
            'IssueDate' => '',

            'RegNo' => $order->transport->gov_num,
            'VIN' => $order->transport->vin,
            'VehicleType' => $this->vehicleTypeByAlias($order->transport->category->alias),
            'Mark' => $order->transport->car_mark,
            'Model' => $order->transport->car_model,
            'ProdYear' => $order->transport->car_year,
        ];

        if ($status === 'Signed') {
            if ( ! empty($contract->number)) {
                $params['BlankNumber'] = $contract->number;
            }

            $params['OTP'] = $order->send_sms;
        }

        try {
            $response = $this->request('Save', $params);
            Log::debug("Save GreenCard response", $response);

            if (isset($response['result']) && $response['result']) {
                $contract = [
                    'number' => $response['BlankNumber'],
                    'external_id' => $response['Number'],
                    'state' => $status,
                    'end_date' => $response['EndDate'],
                    'policy_link' => $response['MTIBULink']
                ];
                (new OrderService($order))->saveContract($contract);

                $order->send_sms = $response['OTP'];
                $order->save();
            }
        } catch (\Exception $e) {
            return [];
        }

        return $response;
    }

    public function getPrintForm($id, $number)
    {
        $params = [
            'ID' => $this->internalId($id),
            'Number' => $number
        ];

        $filename = $id . '.zip';

        $response = $this->request('GetPrintForm', $params, $filename);

        return $response['status'] ? $filename : '';
    }

    private function transliterate($input): string
    {
        $gost = array(
            "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
            "е"=>"e", "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i",
            "й"=>"i","к"=>"k","л"=>"l", "м"=>"m","н"=>"n",
            "о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t",
            "у"=>"u","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch",
            "ш"=>"sh","щ"=>"sh","ы"=>"i","э"=>"e","ю"=>"u",
            "я"=>"ya",
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
            "Е"=>"E","Ё"=>"Yo","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"I","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"C","Ч"=>"Ch",
            "Ш"=>"Sh","Щ"=>"Sh","Ы"=>"I","Э"=>"E","Ю"=>"U",
            "Я"=>"Ya",
            "ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>"",
            "ї"=>"j","і"=>"i","ґ"=>"g","є"=>"ye",
            "Ї"=>"J","І"=>"I","Ґ"=>"G","Є"=>"YE"
        );
        return strtr($input, $gost);
    }

    private function vehicleTypeByAlias(string $alias): ?string
    {
        $transportTypes = [
            'car' => 'A',
            'ecar' => 'A',
            'moto' => 'B',
            'bus' => 'E',
            'truck' => 'C',
            'trailer' => 'F'
        ];

        return $transportTypes[$alias] ?? null;
    }

    public function findVehicle($value, $searchType = 'RegNo'): array
    {
        $params = [
            $searchType => $value
        ];

        try {
            $response = $this->request('findvehicle', $params);
        } catch (\Exception $e) {
            return [];
        }

        return $response;
    }

    public function sendOTP($id, $number): array
    {
        $params = [
            'ID' => $this->internalId($id),
            'Number' => $number,
        ];

        try {
            $response = $this->request('SendOTP', $params);
        } catch (\Exception $e) {
            return [];
        }

        return $response;
    }
}