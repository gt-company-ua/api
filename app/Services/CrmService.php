<?php

namespace App\Services;

use App\Models\Order;
use App\Services\api\CrmApi;
use DateInterval;
use DateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CrmService
{
    private $order;
    private $crm;
    var $transportTypesBitrix = ['car' => 539,'ecar' => '','moto' => 543,'bus' => 540,'truck' => 541, 'trailer' => 542];

    public function __construct(?Order $order)
    {
        $this->order = $order;
        $this->crm = new CrmApi();
    }

    public function sendCrm(){
        $contactId = $this->contact();

        if( ! is_null($contactId)) {
            $this->order->crm_contact_id = $contactId;

            $dealId = $this->createDeal();

            if( ! is_null($dealId)){
                $this->order->crm_deal_id = $dealId;

                $this->order->crm_car_id = $this->addCar();
            }
        }

        $this->order->save();
    }

    public function updateDeal()
    {
        if (empty($this->order->crm_deal_id)) {
            return false;
        }

        if ($this->order->type === Order::ORDER_TYPE_OSAGO) {
            $fields['STAGE_ID'] = '2';
        } else {
            $fields['STAGE_ID'] = 'C5:EXECUTING';
        }

        $params = [
            'id' => $this->order->crm_deal_id,
            'fields' => $fields,
            'params' => ['REGISTER_SONET_EVENT' => 'Y']
        ];

        return $this->crm->updateDeal($params);
    }

    private function contact(): ?int
    {
        $orderService = new OrderService($this->order);

        $phone = '+'.preg_replace("/[^0-9]/", '', $this->order->insurant->phone);

        $fio = $orderService->getInsurantFullName();

        $params = [
            'filter' => ['PHONE' => $phone],
            'select' => ['ID']
        ];
        $response = $this->crm->findContact($params);

        if (is_array($response) && array_key_exists('result', $response) && is_array($response['result']) && count($response['result'])) {
            return intval($response['result'][0]['ID']);
        } else { // Не найден, создаем
            $fields = [
                'ASSIGNED_BY_ID' => 30, // Идентификатор ответственного - int
                'PHONE' => [0 => ['VALUE' => $phone, 'VALUE_TYPE' => 'MOBILE']], // Телефон
                'EMAIL' => [0 => ['VALUE' => $this->order->email, 'VALUE_TYPE' => 'WORK']], // Email
                'TITLE' => $fio, // ФИО - string

                'LAST_NAME' => $this->order->insurant->surname, // ФИО - string
                'NAME' => $this->order->insurant->name, // ФИО - string
                'SECOND_NAME' => $this->order->insurant->patronymic,

                'BIRTHDATE' => $this->order->insurant->birth, // Дата рождения - date(Y-m-d)
                'UF_CRM_1538481374886' => $this->order->insurant->inn, // ИНН - string

                'UF_CRM_1538481286020' => ($this->order->type === Order::ORDER_TYPE_GC) ?  $this->order->insurant->surname_latin . ' ' . $this->order->insurant->name_latin : '', // ФИО (латиница) - string

                'UF_CRM_1552406481309' => $this->order->insurant->surname_latin, // ЛАТ - фамилия - string
                'UF_CRM_1552406504749' => $this->order->insurant->name_latin, // ЛАТ - имя - string
            ];

            $params = [
                'fields' => $fields,
                'params' => ['REGISTER_SONET_EVENT' => 'Y']
            ];

            $response = $this->crm->createContact($params);

            if (array_key_exists('result', $response) && intval($response['result']) > 0) {
                return intval($response['result']);
            }else{
                Log::error('Crm contact add failed: '.json_encode($fields). '. Response: '.json_encode($response));
            }
        }

        return null;
    }

    /**
     * @throws \Exception
     */
    private function createDeal(): ?int
    {
        $orderService = new OrderService($this->order);

        $contactId = $this->order->crm_contact_id;

        $fio = $orderService->getInsurantFullName();

        $price = $this->order->price + $this->order->gc_plus_price;

        $fields = [
            'ASSIGNED_BY_ID' => [7238, 9958, 10436], // Идентификатор ответственного - int|array
            'TITLE' => $fio.' '.date('d.m.Y H:i:s'), // ФИО - дата создания - строка
            'CONTACT_ID' => $contactId, // Идентификатор контакта
            'CATEGORY_ID' => ($this->order->type === Order::ORDER_TYPE_OSAGO) ? 0 : 5,
            'STAGE_ID' => ($this->order->type === Order::ORDER_TYPE_OSAGO) ? 'NEW' : 'C5:PREPARATION', // Стадия сделки
            'COMMENTS' => (string) $this->order->comment, // Примечание к заказу
            'OPPORTUNITY' => $price, // Стоимость тарифа

            'UF_CRM_1538408950685' => $this->order->insurant->inn, // ИНН - string

            'UF_CRM_1554120853140' => $this->order->id, // ID Заказа (сайт)

            'UF_CRM_1538415459284' => $this->order->insurant->phone, // Телефон О КЛИЕНТЕ - string
            'UF_CRM_1538416149715' => $this->order->email, // E-mail О КЛИЕНТЕ - string
            'UF_CRM_1538409137436' => $this->order->insurant->birth,
            'UF_CRM_1538483330399' => ($this->order->payment_type === 'cash') ? 'Наличными при получении страховки' : 'Картой онлайн', // Способ оплаты ОПЛАТА ВЗР - string
            'UF_CRM_1538415860677' => $fio,
            'UF_CRM_1538408288850' => 'N', // Нужна доставка зарубеж - string (Y - да , N - нет)
            'UF_CRM_1538408555178' => 'N', // Многоразовые поездки ВЗР - string (Y - да , N - нет)
            'UF_CRM_1538482354207' => 'epolis' // Доставка по Украине (1 день бесплатно) string
        ];

        if ($this->order->type === Order::ORDER_TYPE_OSAGO || $this->order->type === Order::ORDER_TYPE_GC){
            $fields['UF_CRM_5BC76EACF1061'] = $this->order->transport->vin; // VIN код авто
            $fields['UF_CRM_5BC76EACD53DF'] = $this->order->transport->car_model; // Модель авто
            $fields['UF_CRM_5BC76EACB9024'] = $this->order->transport->car_mark; // Марка авто
            $fields['UF_CRM_5BC76EAC9C2B5'] = $this->order->transport->gov_num; // Гос. номер авто
            $fields['UF_CRM_1570026194130'] = $this->order->transport->car_year; // Год выпуска
            $fields['UF_CRM_1552293395'] = ($this->order->dont_call) ? 591 : 592; // Не перезванивать?
            $fields['UF_CRM_5BC76EAC79329'] = [$this->transportTypesBitrix[$this->order->transport->category->alias]]; //Транспортное средство

            if ($this->order->type === Order::ORDER_TYPE_OSAGO){

                $franchise = 613;
                if($this->order->tariff === 'standart') {
                    $franchise = 614;
                }elseif ($this->order->tariff === 'econom'){
                    $franchise = 615;
                }

                $fields['UF_CRM_1570025505301'] = $franchise;
                $fields['UF_CRM_1570025563206'] = $price; // Сумма по франшизе
                $fields['UF_CRM_1570026469690'] = ($this->order->insurant->doc_type === Order::DOC_PASSPORT) ? 616 : 617; // Тип документа
                $fields['UF_CRM_1570026488977'] = $this->order->insurant->doc_number; // Серия и номер документа
                $fields['UF_CRM_1570026556973'] = $this->order->insurant->doc_date; // Дата выдачи документа
                $fields['UF_CRM_1570026571587'] = $this->order->insurant->doc_given; // Кем выдан


                $fields['UF_CRM_1570025375841'] = 1; // Электронный полис
                $fields['UF_CRM_1570025311815'] = ($this->order->insurant->type === Order::INSURANT_PHYSICAL) ? 611 : 612; // Страхователь
                $fields['UF_CRM_1570025264241'] = ($this->order->foreign_check) ? 1 : 0; // Иностранная регистрация
                $fields['UF_CRM_1570025162716'] = $this->order->city_name; // Место/город регистрации
                //$fields['UF_CRM_1570025120707'] = $this->registrationTypeBitrix[$raw['registrationType']]; // Тип регистрации авто
                $fields['UF_CRM_1570024598189'] = $this->order->transport->power->name_ua; // Объём двигателя

                $fields['UF_CRM_5BC772FD90BCA'] = $this->order->polis_start->format('Y-m-d'); // Дата начала действия

                if (! is_null($this->order->polis_end)) {
                    $fields['UF_CRM_5BC772FDAC7AA'] = $this->order->polis_end->format('Y-m-d'); // Дата завершения действия
                }



            } else {
                $fields['UF_CRM_1539694455447'] = ($this->order->trip_country == Order::TRIP_COUNTRY_SNG) ? '521' : '520'; // ЗОНА ПОКРЫТИЯ ГРИН, идентификатор значения из списка соответствия
                /**
                 * {"NAME":"Европа","VALUE":520},{"NAME":"СНГ","VALUE":521}]
                 */
                $fields['UF_CRM_1538408097164'] = 318; // Тариф, идентификатор значения из списка соответствия
                /**
                 * [{"NAME":"не выбрано","VALUE":""},{"NAME":"Стандарт","VALUE":318},{"NAME":"Экспресс","VALUE":319},{"NAME":"Все включено","VALUE":320}]
                 */
                $fields['UF_CRM_1538409076468'] = $this->order->polis_start->format('Y-m-d'); // Дата начала действия полиса (ГРИН) - date

                $getTrip = $this->getTripDuration($this->order->trip_duration);

                $fields['UF_CRM_1538640527267'] = $getTrip['trip_duration']; // Срок поездки, идентификатор значения из списка соответствия
                /**
                 * [{"NAME":"не выбрано","VALUE":""},{"NAME":"15 дней","VALUE":356},{"NAME":"1 месяц","VALUE":357},{"NAME":"2 месяца","VALUE":358},{"NAME":"3 месяца","VALUE":359},{"NAME":"4 месяца","VALUE":360},{"NAME":"5 месяцев","VALUE":361},{"NAME":"6 месяцев","VALUE":362},{"NAME":"7 месяцев","VALUE":363},{"NAME":"8 месяцев","VALUE":364},{"NAME":"9 месяцев","VALUE":365},{"NAME":"10 месяцев","VALUE":366},{"NAME":"11 месяцев","VALUE":367},{"NAME":"1 год","VALUE":368}]
                 */

                $dateEnd = (new DateTime($this->order->polis_start))->add(new DateInterval($getTrip['daysAddToStart']))->sub(new DateInterval('P1D'))->format('Y-m-d');

                $fields['UF_CRM_1539249323446'] = $dateEnd; // Дата окончания действия ГРИН - date

                $fields['UF_CRM_1538483726245'] = $fio; // Куда и кому доставить - string

                $fields['UF_CRM_1538409173204'] = ($this->order->payment_type === 'cash') ? 'N' : 'Y'; // Онлайн оплата любой картой мира ГРИН - string (Y - да , N - нет)

            }
        } else if($this->order->type === Order::ORDER_TYPE_VZR){
            //$fields['UF_CRM_1538483726245'] = $raw['recipientSurname'].' '.$raw['recipientName']; // Куда и кому доставить - string

            $fields['UF_CRM_1538408525914'] = ($this->order->territory == Order::TERRITORY_EU) ? 334 : 333; // Зона покрытия ВЗР, идентификатор значения из списка соответствия
            $fields['UF_CRM_1538408575218'] = $this->order->polis_start->format('Y-m-d'); // Дата начала действия ВЗР - date
            $fields['UF_CRM_1538408588107'] = $this->order->polis_end->format('Y-m-d'); // Дата завершения действия ВЗР - date
            $fields['UF_CRM_1538641702047'] = $this->order->sport; // Занятия спортом ВЗР - string
            $fields['UF_CRM_1538641665539'] = $this->order->target; // Цель поездки ВЗР - string
            $fields['UF_CRM_1538484216016'] = $this->order->insured_sum; // Страховая сумма ВЗР - string
            $fields['UF_CRM_1538641788486'] = 'Электронная версия'; // Электронный полис ВЗР - string
            $fields['UF_CRM_1538408820154'] = $this->order->comment; // ВЗР комментарий - string

        }

        if ( ! is_null($this->order->files)){
            foreach ($this->order->files as $file){
                $fileGet = Storage::disk('public')->get($file->path);
                $fields['UF_CRM_1540210623'][] = [
                    'fileData' => [$file->name, base64_encode($fileGet)]
                ];
            }
        }

        $params = [
            'fields' => $fields,
            'params' => ['REGISTER_SONET_EVENT' => 'Y']
        ];

        $response = $this->crm->createDeal($params);


        if (array_key_exists('result', $response) && intval($response['result']) > 0) {
            $dealId = intval($response['result']);

            if($this->order->type === Order::ORDER_TYPE_VZR){
                $this->sendTourists();
            }

            return $dealId;
        }

        Log::error('Create deal failed: '.json_encode($fields). ' Response: '.json_encode($response));

        return null;
    }

    private function sendTourists()
    {
        foreach ($this->order->tourists as $tourist){
            $touristParams = [
                'NAME' => $tourist->doc_number, // Номер загран паспорта - string
                'PROPERTY_108' => $tourist->birth, // День рождения туриста - date(Y-m-d)
                'PROPERTY_110' => $tourist->full_name, // ФИО туриста - string,
                'PROPERTY_106' => [
                    'D_' . $this->order->crm_deal_id,
                    'C_' . $this->order->crm_contact_id
                ]
            ];

            $params = [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => 29,
                'FILTER' => [
                    '=NAME' => $touristParams['NAME'] // Номер загран паспорта
                ]
            ];

            $response = $this->crm->findListItem($params);

            if (array_key_exists('result', $response) && intval($response['result']) > 0) { // Найден
                $touristId = intval($response['result'][0]['ID']);

                // Обновляем привязку
                $params = [
                    'IBLOCK_TYPE_ID' => 'lists',
                    'IBLOCK_ID' => 29,
                    'ELEMENT_ID' => $touristId,
                    'FIELDS' => [
                        'NAME' => $response['result'][0]['NAME'],
                        'PROPERTY_108' => $response['result'][0]['PROPERTY_108'], // День рождения туриста - date(Y-m-d)
                        'PROPERTY_110' => $response['result'][0]['PROPERTY_110'], // ФИО туриста - string
                        'PROPERTY_106' => array_merge($touristParams['PROPERTY_106'], $response['result'][0]['PROPERTY_106'])
                    ],
                ];

                $this->crm->updateListItem($params);

            } else { // Не найден, создаем запись
                $params = [
                    'IBLOCK_TYPE_ID' => 'lists',
                    'IBLOCK_ID' => 29,
                    'ELEMENT_CODE' => $touristParams['NAME'],
                    'FIELDS' => $touristParams,
                ];

                $this->crm->addListItem($params);
            }
        }

    }

    private function addCar(): ?int
    {
        if($this->order->type === Order::ORDER_TYPE_VZR) {
            return null;
        }

        $params = [
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_ID' => 28,
            'FILTER' => [
                '=NAME' => $this->order->transport->gov_num // Гос. номер авто
            ]
        ];

        $response = $this->crm->findListItem($params);

        if (is_array($response) && array_key_exists('result', $response) && is_array($response['result']) && count($response['result'])) { // Найден

            return intval($response['result'][0]['ID']);

        } else {
            $fields = [
                'NAME' => $this->order->transport->gov_num, // Гос. номер авто - string
                'PROPERTY_100' => $this->order->transport->car_mark, // Марка авто - string
                'PROPERTY_101' => $this->order->transport->car_model, // Модель авто - string
                'PROPERTY_102' => $this->order->transport->vin, // VIN код авто - string
                //'PROPERTY_103' => $raw['PROPERTY_103'], // Категория ТС - string
                'PROPERTY_104' => $this->order->city_name, // Место регистрации - string
                'PROPERTY_105' => $this->order->transport->car_year, // Год выпуска - string
                'PROPERTY_111' => $this->transportTypesBitrix[$this->order->transport->category->alias], // Тип ТС, идентификатор значения из списка соответствия
                /**
                 * [{"NAME":"Легковое","VALUE":"79"},{"NAME":"Автобус","VALUE":"80"},{"NAME":"Грузовое","VALUE":"81"},{"NAME":"Прицеп","VALUE":"82"},{"NAME":"Мотоцикл","VALUE":"83"}]
                 */
                'PROPERTY_98' => [
                    'D_' . $this->order->crm_deal_id,
                    'C_' . $this->order->crm_contact_id
                ], // Связи - array
            ];

            $params = [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => 28,
                'ELEMENT_CODE' => $this->order->transport->gov_num,
                'FIELDS' => $fields,
            ];

            $response = $this->crm->addListItem($params);

            if (array_key_exists('result', $response) && intval($response['result']) > 0) {
                return intval($response['result']);
            }
        }

        Log::error('Create car failed: '.json_encode($fields). ' Response: '.json_encode($response));

        return null;
    }

    private function getTripDuration($value): array
    {
        $trip_duration  = '';
        $daysAddToStart = '';
        switch ($value) {
        case 0:
            $trip_duration  = '356';
            $daysAddToStart = 'P15D';
            break;
        case 1:
            $trip_duration  = '357';
            $daysAddToStart = 'P1M';
            break;
        case 2:
            $trip_duration  = '358';
            $daysAddToStart = 'P2M';
            break;
        case 3:
            $trip_duration  = '359';
            $daysAddToStart = 'P3M';
            break;
        case 4:
            $trip_duration  = '360';
            $daysAddToStart = 'P4M';
            break;
        case 5:
            $trip_duration  = '361';
            $daysAddToStart = 'P5M';
            break;
        case 6:
            $trip_duration  = '362';
            $daysAddToStart = 'P6M';
            break;
        case 7:
            $trip_duration  = '363';
            $daysAddToStart = 'P7M';
            break;
        case 8:
            $trip_duration  = '364';
            $daysAddToStart = 'P8M';
            break;
        case 9:
            $trip_duration  = '365';
            $daysAddToStart = 'P9M';
            break;
        case 10:
            $trip_duration  = '366';
            $daysAddToStart = 'P10M';
            break;
        case 11:
            $trip_duration  = '367';
            $daysAddToStart = 'P11M';
            break;
        case 12:
            $trip_duration  = '368';
            $daysAddToStart = 'P12M';
            break;
        }

        return [
            'trip_duration'  => $trip_duration,
            'daysAddToStart' => $daysAddToStart,
        ];
    }
}