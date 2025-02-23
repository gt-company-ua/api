<?php

namespace App\Services\api;

use App\Models\Order;
use App\Models\OrderContract;
use App\Models\OsagoCashback;
use App\Models\OsagoCity;
use App\Models\TransportCategory;
use App\Models\TransportPower;
use App\Models\VzrRangeDay;
use App\Services\OrderService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Ingo
{
    const GREENCARD_TRANSPORT_CATEGORIES = ['car' => 'A', 'moto' => 'B', 'bus' => 'E', 'truck' => 'C', 'trailer' => 'F'];
    const API_NAME = "INGO";

    const METHOD_GET = 'GET';
    const METHOD_PATCH = 'PATCH';
    const METHOD_POST = 'POST';

    const OSAGO_FRANCHISES = [0];

    const PHONE = '+380639583957';
    const EMAIL = 'greencard.ukraine.online@gmail.com';

    const TRAVEL_P6ID = 95;

    const DOC_TYPES = [
//        1 => 'паспорт',
//        2 => 'ID-паспорт',
//        3 => 'водійське посвідчення',
//        4 => 'пенсійне посвідчення',
//        5 => 'посвідчення про інвалідність',
//        6 => 'посвідчення учасника війни',
        7 => 'закордонний паспорт',
//        8 => 'іноземний паспорт',
//        9 => 'посвідка на проживання',
//        10 => 'реєстраційний талон',
//        11 => 'свідоцтво про народження',
//        12 => 'чорнобильське посвідчення',
//        13 => 'інший',
//        14 => 'іноземне посвідчення водія'
    ];

    const VZR_TARIFFS = ['ECONOM', 'STANDARD', self::VZR_ELIT, self::VZR_STANDARD_PLUS];
    const VZR_ELIT = 'ELIT';
    const VZR_STANDARD_PLUS = 'STANDARD_PLUS';

    const TERRITORIES_IDS = [5, 6, 7, 8];
    const TERRITORIES = [
        5 => 'Весь світ',
        6 => 'Країни Європи, країни СНД, Грузія, Туреччина, Египет, Болгарія, Ізраїль, ОАЕ, Туніс',
        7 => 'Весь світ крім США, Канади, Японії',
        8 => 'Шенгенська зона, Країни Європи, СНД, Грузія',
    ];

    const TERRITORIES_RU = [
        5 => 'Весь мир',
        6 => 'Страны Европы, страны СНГ, Грузия, Турция, Египет, Болгария, Израиль, ОАЭ, Тунис',
        7 => 'Весь мир кроме США, Канады, Японии',
        8 => 'Шенгенская зона, Страны Европы, СНГ, Грузия',
    ];

    const GOAL_IDS = ['T', 'W', 'PW', 'AR', 'SE', 'SA'];
    const GOALS = [
        'T' => 'Туризм, навчання',
        'W' => 'Робота (інтелектуальна праця)',
        'PW' => 'Робота (фізична праця)',
        'AR' => 'Активний спорт',
        'SE' => 'Екстремальний спорт',
        //'SA' => 'Активний спорт'
    ];

    const GOALS_RU = [
        'T' => 'Туризм, обучение',
        'W' => 'Работа (интеллектуальный труд)',
        'PW' => 'Работа (физический труд)',
        'AR' => 'Активный спорт',
        'SE' => 'Экстремальный спорт',
        //'SA' => 'Активний спорт'
    ];

    const DISCOUNTS = [
        1 => 'Учасники війни, що визначені законом',
        2 => 'Інваліди II групи',
        3 => 'Особи, які постраждали внаслідок Чорнобильської катастрофи',
        4 => 'Пенсіонери громадяни'
    ];

    const LANG_RU = 'ru';
    const LANG_UA = 'ua';
    private function request(string $uri, array $params, $method = self::METHOD_POST, ?string $filename = null, $timeout = 100): array
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = env('INGO_URL') . $uri;

            $client = Http::withHeaders([
                'authid' => env('INGO_LOGIN'),
                'authkey' => env('INGO_PASSWORD')
            ])
                ->timeout($timeout)
                ->withBody($json, 'application/json; charset=UTF-8');

            if ( ! is_null($filename)) {
                $tempName = storage_path('app/public/policies')
                    . DIRECTORY_SEPARATOR . $filename;

                $client->sink($tempName);
            }

            if ($method === self::METHOD_GET) {
                $response = $client->get($requestUrl);
            } else if ($method === self::METHOD_PATCH) {
                $response = $client->patch($requestUrl);
            } else {
                $response = $client->post($requestUrl);
            }

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                Log::info('Request() code: ' . $code . '. URL: ' . $method . ' ' . $requestUrl);
                Log::info($json);
                Log::info($body);

                return json_decode($body, true);
            } elseif ( ! is_null($filename)) {
                return ['status' => true, 'status_code' => $code];
            }

//            if ($uri === '/osago/calculate') {
//                Log::info($json);
//                Log::info($body);
//            }

            return json_decode($body, true);
        }catch (\Exception $e){
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

    public function greenCardCalculate(array $data): array
    {
        $transportCategory = TransportCategory::whereId($data['transport']['transport_category_id'])->first();

        $params = [
            'startFrom' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'period' => $this->periodFormat($data['trip_duration']),
            'vehicleType' => self::GREENCARD_TRANSPORT_CATEGORIES[$transportCategory->alias] ?? null,
            'zone' => $this->greenCardZone($data['trip_country']),
        ];

        $response = $this->request('/greencard/calculate', $params, self::METHOD_POST, null, 10);
        //Log::debug("Calculate GreenCard request:", $params);
        //Log::debug("Calculate GreenCard response:", $response);

        return $response;
    }

    public function greenCardDraft(Order $order): array
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
            'phone' => self::PHONE,
            'email' => self::EMAIL
        ];

        try {
            $response = $this->request('/greencard/register', $params);

            Log::debug("Save GreenCard (order: ".$order->id.") request", $params);
            Log::debug("Save GreenCard (order: ".$order->id.") response", $response);

            if (! empty($response['info']) && ! empty($response['info']['id'])) {
                $contract = [
                    //'number' => $response['info']['mainCode'],
                    'external_id' => $response['info']['id'],
                    'state' => 'Draft',
                    'policy_link' => $response['info']['directLink'] ?? '',
                    'api_name' => self::API_NAME
                ];
                (new OrderService($order))->saveContract($contract);
            }

            $order->status_contract = OrderContract::STATUS_CONTRACT_SENT;
            $order->save();
        } catch (\Exception $e) {
            Log::error('Save GreenCard request error:' . $e->getMessage());

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

            $response = $this->request('/greencard/' . $order->contract->external_id . '/confirm', ['validationCode' => $sms], self::METHOD_PATCH);

            Log::debug("Confirm GreenCard (order: ".$order->id.") response", $response);
            $this->updateContractState($order, $response);

            return $response;
        }

        return null;
    }

    public function greenCardPrintForm(Order $order): array
    {
        $files = [];

        if (! is_null($order->contract) && ! empty($order->contract->external_id)) {
            $statusCode = 200;

            foreach (['form', 'certificate'] as $formType) {
                $filename = $order->id . '-' . $formType . '.pdf';
                $response = $this->request('/greencard/' . $order->contract->external_id . '/pdf?formType=' . $formType, [], self::METHOD_GET, $filename);

                if (isset($response['status_code']) && $statusCode < $response['status_code']) {
                    $statusCode = $response['status_code'];
                }

                if (isset($response['status']) && $response['status'] === true && $response['status_code'] === 200) {
                    $files[] = $filename;
                }
            }

            $this->updateContractDownload($order->contract, $statusCode);
        }

        return $files;
    }

    public function greenCardPrintOffer(Order $order): array
    {
        $files = [];

        if (! is_null($order->contract) && ! empty($order->contract->external_id)) {
            $statusCode = 200;

            foreach (['personal_offer'] as $formType) {
                $filename = $order->id . '-' . $formType . '.pdf';
                $response = $this->request('/greencard/' . $order->contract->external_id . '/pdf?formType=' . $formType, [], self::METHOD_GET, $filename);

                if (isset($response['status_code']) && $statusCode < $response['status_code']) {
                    continue;
                }

                if (isset($response['status']) && $response['status'] === true && $response['status_code'] === 200) {
                    $files[] = $filename;
                }
            }
        }

        return $files;
    }

    private function updateContractDownload(OrderContract $contract, $statusCode)
    {
        $contract->download_attempts ++;
        $contract->download_status_code = $statusCode;
        $contract->sent_police = $statusCode === 200;

        if ($contract->download_attempts > 10) {
            $contract->state = 'Error';
        }

        $contract->save();
    }

    private function calculateVzrDays(array $data): int
    {
        $start = new \DateTime($data['polis_start']);
        $end   = new \DateTime($data['polis_end']);

        $interval = $end->diff($start);

        return intval($interval->format('%a'));
    }


    public function vzrDraft(Order $order): array
    {
        $days = $this->calculateVzrDays(['polis_start' => $order->polis_start, 'polis_end' => $order->polis_end]);

        $params = [
            'requestId' => (string) $order->id,
            'startFrom' => date('Y-m-d', strtotime($order->polis_start)) . ' 00:00:00',
            'period' => $days . 'd',
            'territories' => json_decode($order->territory),
            'medicalPocket' => $order->tariff,
            'medicalCover' => $order->insured_sum,
            'medicalCurrency' => 'EUR',
            'tourists' => [],
            'customerIsPhysicalPerson' => true,
            'customerIsResident' => true,
            'customerIdentCode' => $order->insurant->inn,
            'customerFirstName' => $order->insurant->name,
            'customerSecondName' => $order->insurant->surname,
            //'customerThirdName' => $order->insurant->patronymic,
            'customerBirthday' => $order->insurant->birth,
            'customerDocType' => $order->insurant->doc_type,
            'customerDocSeries' => $order->insurant->doc_series,
            'customerDocNumber' => $order->insurant->doc_number,
            'address' => $order->insurant->address,
            'phone' => $order->insurant->phone,
            'email' => $order->email,
            'p6id' => self::TRAVEL_P6ID
        ];

        if ($order->tariff === 'ELIT') {
            $params['accidentCover'] = 30000;
            $params['accidentCurrency'] = 'UAH';
        }

        if ($order->multiple_trip) {
            $params['period'] = '365d';
            $params['multi'] = true;
            $params['multiDays'] = $order->vzrDay->days;
        }

        foreach ($order->tourists as $tourist) {
            $fullNameToParts = explode(' ', $tourist->full_name, 2);

            $params['tourists'][] = [
                'goal' => $tourist->goal,
                'birthday' => $tourist->birth,
                'firstName' => $fullNameToParts[1] ?? null,
                'secondName' => $fullNameToParts[0],
                'passportSeries' => $tourist->doc_series,
                'passportNumber' => $tourist->doc_number,
            ];
        }

        try {
            $response = $this->request('/travel/register', $params);

            Log::debug("Save VZR (order: ".$order->id.") request", $params);
            Log::debug("Save VZR (order: ".$order->id.") response", $response);

            if (! empty($response['info']) && ! empty($response['info']['id'])) {
                $startDate = $response['info']['startFrom'] ?? null;
                $endDate = $response['info']['untilTo'] ?? null;
                $contract = [
                    'external_id' => $response['info']['id'],
                    'number' => $response['info']['number'] ?? null,
                    'state' => 'Draft',
                    'start_date' => !is_null($startDate) ? date('Y-m-d', strtotime($startDate)) : null,
                    'end_date' => !is_null($endDate) ? date('Y-m-d', strtotime($endDate)) : null,
                    'api_name' => self::API_NAME
                ];
                (new OrderService($order))->saveContract($contract);
            }

            $order->status_contract = OrderContract::STATUS_CONTRACT_SENT;
            $order->save();
        } catch (\Exception $e) {
            Log::error('Save VZR request error:' . $e->getMessage());

            $order->status_contract = OrderContract::STATUS_CONTRACT_ERROR;
            $order->save();

            return [];
        }

        return $response;
    }
    public function vzrCalculate(array $data, string $medicalPocket): array
    {
        $params = [
            'startFrom' => date('Y-m-d', strtotime($data['polis_start'])) . ' 00:00:00',
            'period' => $this->calculateVzrDays($data) . 'd',
            'territories' => $data['territories'],
            'medicalPocket' => $medicalPocket,
            'medicalCover' => $data['insured_sum'],
            'medicalCurrency' => 'EUR',
            'p6id' => self::TRAVEL_P6ID,
            'tourists' => [],
        ];

        if ($medicalPocket === self::VZR_ELIT) {
            if (intval($data['insured_sum']) === 30000) {
                return [];
            }
            $params['accidentCover'] = 30000;
            $params['accidentCurrency'] = 'UAH';
        }

        if ($data['multiple_trip'] === true) {
            $duration = VzrRangeDay::find($data['vzr_range_day_id']);

            $params['multi'] = true;
            $params['multiDays'] = $duration->days;
        }

        if (isset($data['tourists']) && count($data['tourists']) > 0) {
            foreach ($data['tourists'] as $tourist) {
                $params['tourists'][] = ['goal' => $tourist['goal'], 'birthday' => date('Y-m-d', strtotime($tourist['birth']))];
            }
        } elseif (isset($data['ranges']) && count($data['ranges']) > 0) {
            foreach ($data['ranges'] as $range) {
                if ($range === '0') {
                    $age = 0;
                } else {
                    $ages = explode('-', $range['range']);
                    $age = (int) $ages[0];
                }

                $params['tourists'][] = ['goal' => $range['goal'], 'age' => $age];
            }
        }

        return $this->request('/travel/calculate', $params);
    }


    public function vzrConfirm(Order $order): bool
    {
        if (! is_null($order->contract) && ! empty($order->contract->external_id)) {
            $sms = (!empty($order->send_sms)) ? $order->send_sms : mt_rand(100000, 999999);
            $response = $this->request('/travel/' . $order->contract->external_id . '/confirm', ['validationCode' => $sms, 'p6id' => self::TRAVEL_P6ID], self::METHOD_PATCH);

            Log::debug("Confirm VZR (order: ".$order->id.") response", $response);
            $this->updateContractState($order, $response);

            return false;
        }

        Log::debug('Sign VZR contract filed, order_id: ' . $order->id);

        return false;
    }

    public function vzrPrintForm(Order $order): array
    {
        $files = [];

        if (! is_null($order->contract) && ! empty($order->contract->external_id)) {
            $statusCode = 200;

            foreach (['form'] as $formType) {
                $filename = $order->id . '-' . $formType . '.pdf';
                $response = $this->request('/travel/' . $order->contract->external_id . '/pdf?formType=' . $formType, [], self::METHOD_GET, $filename);

                if (isset($response['status_code']) && $statusCode < $response['status_code']) {
                    $statusCode = $response['status_code'];
                }

                if (isset($response['status']) && $response['status'] === true && $response['status_code'] === 200) {
                    $files[] = $filename;
                }
            }

            $this->updateContractDownload($order->contract, $statusCode);
        }

        return $files;
    }

    public function carBrands()
    {
        $response = $this->request('/osago/car-brands', [], self::METHOD_GET);

        return $response['data']['exportData'] ?? [];
    }

    public function carModels()
    {
        $response = $this->request('/osago/car-models', [], self::METHOD_GET);

        return $response['data']['exportData'] ?? [];
    }

    public function transportBrands()
    {
        $response = $this->request('/osago/transport-brands?vehicleType=B4', [], self::METHOD_GET);

        return $response['data'] ?? [];
    }

    public function transportModels($brandID)
    {
        $response = $this->request('/osago/transport-models?brandId='.$brandID, [], self::METHOD_GET);

        return $response['data'] ?? [];
    }

    public function findCarByNum(string $number)
    {
        $response = $this->request('/osago/plate-parse?regNo=' . $number, [], self::METHOD_GET);

        return $response['data'] ?? [];
    }

    public function getCities()
    {
        $response = $this->request('/osago/cities', [], self::METHOD_GET);

        return $response['data']['exportData'] ?? [];
    }

    public function osagoCalculate(array $data): array
    {
        $transportPower = TransportPower::whereId($data['transport']['transport_power_id'])->first();

        if (empty($data['city_id'])) {
            $ariaValue = 5;
            $ariaKey = 'zoneId';
        } else {
            $city = OsagoCity::find($data['city_id']);
            $ariaValue = $city->external_id;
            $ariaKey = 'cityCode';
        }

        $params = [
            'startFrom' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'period' => $this->periodFormat($data['trip_duration'] ?? 12),
            //'bonusMalus' => 1,
            'privelege' => ($data['discount_check']) ? $data['discount_type'] : 0,
            'franchise' => $data['franchise'],
            $ariaKey => $ariaValue,
            'usage' => '111111111111',
            'vehicleType' => $transportPower->type_auto ?? null,
            'customerIsPhysicalPerson' => ($data['insurant']['type'] === Order::INSURANT_PHYSICAL) ? 1 : 0,
            'customerIsResident' => 1,
        ];

        $useScoring = (isset($data['use_scoring']) && $data['use_scoring'] === true);

        if ($useScoring) {
            $params['vehicleYear'] = $data['transport']['car_year'];
            $params['customerBirthday'] = $data['insurant']['birth'];
            $params['useScoring'] = $useScoring;
        }

        if (isset($data['dgo_limit'])) {
            $params['subDgo'] = $data['dgo_limit'];
        }

        if (isset($data['transport']['otk_date'])) {
            $params['nextInspection'] = $data['transport']['otk_date'];
        }

        $response = $this->request('/osago/calculate', $params);
        if (isset($response['data']['amount'])) {
            $cashback = OsagoCashback::where('franchise', $data['franchise'])->first();
            if (!is_null($cashback)) {
                $total = round($response['data']['amount'], 2);
                if (isset($response['data']['dgo'])) {
                    $total += round($response['data']['dgo'], 2);
                }

                $response['cashback'] = round($total / 100 * $cashback->amount);
            }
        }

        return $response;
    }

    public function osagoDraft(Order $order): array
    {
        $city = OsagoCity::find($order->city_id);

        $params = [
            'startFrom' => date('d.m.Y', strtotime($order->polis_start)) . ' 00:00:00',
            'period' => $this->periodFormat($order->trip_duration),
            //'bonusMalus' => 1,
            'privelege' => ($order->discount_check) ? $order->discount_type : 0,
            'franchise' => $order->franchise,
            'zoneId' => $city->zone,
            'cityCode' => $city->external_id,
            'address' => (!empty($order->insurant->address)) ? $order->insurant->address : $order->city_name,
            'usage' => '111111111111',
            'asTaxi' => ($order->use_as_taxi) ? 1 : 0,

            'customerIsPhysicalPerson' => ($order->insurant->type === Order::INSURANT_PHYSICAL) ? 1 : 0,
            'customerIsResident' => 1,
            'customerIdentCode' => $order->insurant->inn,
            'customerFirstName' => $order->insurant->name,
            'customerSecondName' => $order->insurant->surname,
            'customerThirdName' => $order->insurant->patronymic,
            'customerBirthday' => date('Y-m-d', strtotime($order->insurant->birth)),
            'customerDocType' => $order->insurant->doc_type,
            'customerDocSeries' => $order->insurant->doc_series,
            'customerDocNumber' => $order->insurant->doc_number,
            'customerDocDate' => $order->insurant->doc_date,
            'customerDocIssuer' => $order->insurant->doc_given,

            'vehicleType' => $order->transport->power->type_auto ?? null,
            'vehicleBrandCode' => "11487",
            'vehicleModelCode' => "0",
            'vehicleTitle' => $order->transport->car_mark . ' ' . $order->transport->car_model,
            'vehicleRegNo' => $order->transport->gov_num,
            'vehicleVin' => $order->transport->vin,
            'vehicleYear' => $order->transport->car_year,

            'phone' => $order->insurant->phone,
            'email' => $order->email,
            'docId' => (string) $order->id
        ];

        if (!is_null($order->transport->otk_date)) {
            $params['nextInspection'] = $order->transport->otk_date;
        }

        if ($order->use_scoring) {
            $params['useScoring'] = true;
        }

        if (!is_null($order->dgo_limit)) {
            $params['subDgo'] = $order->dgo_limit;
        }

        if ($order->insurant->doc_type == 2 && !is_null($order->insurant->doc_adv)) {
            $params['customerDocAdv'] = $order->insurant->doc_adv;
        }

        try {
            $response = $this->request('/osago/register', $params);

            Log::debug("Save OSAGO (order: ".$order->id.") request", $params);
            Log::debug("Save OSAGO (order: ".$order->id.") response", $response);

            if (! empty($response['info']) && ! empty($response['info']['id'])) {
                $contract = [
                    //'number' => $response['info']['mainCode'],
                    'external_id' => $response['info']['id'],
                    'state' => 'Draft',
                    'policy_link' => $response['info']['directLink'],
                    'api_name' => self::API_NAME
                ];
                (new OrderService($order))->saveContract($contract);
            }

            $order->status_contract = OrderContract::STATUS_CONTRACT_SENT;
            $order->save();
        } catch (\Exception $e) {
            Log::error('Save GreenCard request error:' . $e->getMessage());

            $order->status_contract = OrderContract::STATUS_CONTRACT_ERROR;
            $order->save();

            return [];
        }

        return $response;
    }

    public function osagoConfirm(Order $order): ?array
    {
        if (! is_null($order->contract) && ! empty($order->contract->external_id)) {
            $sms = (!empty($order->send_sms)) ? $order->send_sms : mt_rand(100000, 999999);
            $response = $this->request('/osago/' . $order->contract->external_id . '/confirm', ['validationCode' => $sms], self::METHOD_PATCH);

            Log::debug("Confirm OSAGO (order: ".$order->id.") response", $response);
            $this->updateContractState($order, $response);

            return $response;
        }

        return null;
    }

    public function osagoPrintForm(Order $order): array
    {
        $files = [];

        if (! is_null($order->external_id) && ! empty($order->contract->external_id)) {
            $statusCode = 200;

            foreach (['form'] as $formType) {
                $filename = $order->id . '-' . $formType . '.pdf';
                $response = $this->request('/osago/' . $order->contract->external_id . '/pdf?formType=' . $formType, [], self::METHOD_GET, $filename);

                if (isset($response['status_code']) && $statusCode < $response['status_code']) {
                    $statusCode = $response['status_code'];
                }

                if (isset($response['status']) && $response['status'] === true && $response['status_code'] === 200) {
                    $files[] = $filename;
                }
            }

            $this->updateContractDownload($order->contract, $statusCode);
        }

        return $files;
    }

    private function updateContractState($order, $response)
    {
        if (! empty($response['status_code']) && $response['status_code'] == 200) {
            $contract = [
                'state' => 'Signed',
            ];
            (new OrderService($order))->saveContract($contract);
        } else if (! empty($response['status_code']) && $response['status_code'] >= 400 && $response['status_code'] < 500) {
            $contract = [
                'state' => 'Error',
            ];
            (new OrderService($order))->saveContract($contract);
        }
    }
}
