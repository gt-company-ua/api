@component('mail::message')
# Заказ №{{ $order->id }}

@component('mail::table')
| Поле       | Значение     |
| ---------- |:-------------|
| Дата создадния | {{ $order->created_at }} |
| Тип полиса | {{ $order->order_type }} |
| Тариф | {{ $order->tariff }} |
| Франшиза | {{ $order->franchise }} |
| Доп. услуга "Додаткові розширення ліміту" | {{ $order->dgo_limit }} |
| Доп. услуга "Пряме врегулювання" | @if ($order->is_pu) Да @else Нет @endif |
| Доп. услуга "Медична допомога при ДТП" | @if ($order->is_dms) Да @else Нет @endif |
| Начало действия полиса | {{ $order->polis_start }} |
| Конец действия полиса | {{ $order->polis_end }} |
| Иностранная регистрация | @if ($order->foreign_check) Да @else Нет @endif |
| Есть льгота | @if ($order->discount_check) Да @else Нет @endif |
| Страна | {{ $order->trip_country }} |
| Продолжительность | {{ $order->trip_duration }} |
| Email | {{ $order->email }} |
| Город | {{ $order->city_name }} |
| Цена | {{ $order->price }} |
| Цена без промокода | {{ $order->full_price }} |
| Страховая сумма | {{ $order->insured_sum }} |
| Статус оплаты | {{ $order->payment_status }} |
| Ссылка на оплату | {{ $order->payment_url }} |
| Комментарий | {{ $order->comment }} |
@if($order->order_type == \App\Models\Order::ORDER_TYPE_VZR && substr_count($order->territory, '[') > 0)
@foreach(json_decode($order->territory) as $ter)
| Территория действия | {{ \App\Services\api\Ingo::TERRITORIES[$ter] }} |
@endforeach
@else
| Территория действия | {{ $order->territory }} |
@endif
| Занятия спортом | {{ $order->sport }} |
| Цель поездки | {{ $order->target }} |
| Многоразовые поездки | @if ($order->multiple_trip) Да @else Нет @endif |
@if(!is_null($order->code))
| Код идентификации | {{ $order->code }} |
| Время действия кода | {{ $order->code_date_end }} |
@endif
@if(!is_null($order->vzrDay))
| ВЗР дней | {{ $order->vzrDay->days }} |
@endif
| Сумма cashback | {{ $order->cashback_amount }} |
| Телефон для cashback | {{ $order->cashback_phone }} |
| Отправить cashback ВСУ | @if ($order->cashback_to_vsu) Да @else Нет @endif |
@if(!is_null($order->transport))
| ИНФОРМАЦИЯ О ТРАНСПОРТЕ | - |
| Марка | {{ $order->transport->car_mark }} |
| Модель | {{ $order->transport->car_model }} |
| Год | {{ $order->transport->car_year }} |
| Гос. номер | {{ $order->transport->gov_num }} |
| VIN | {{ $order->transport->vin }} |
@if(!is_null($order->transport->category))
| Тип ТС | {{ $order->transport->category->name_ua }} |
@endif
@if(!is_null($order->transport->power))
| Мощность ТС | {{ $order->transport->power->name_ua }} |
@endif
@endif
@if(!is_null($order->insurant))
| ИНФОРМАЦИЯ О СТРАХОВАТЕЛЕ | - |
| Тип | {{ $order->insurant->type }} |
| Телефон | {{ $order->insurant->phone }} |
| ФИО | {{ $order->insurant->surname }} {{ $order->insurant->name }} {{ $order->insurant->patronymic }}|
| ФИО лат. | {{ $order->insurant->surname_latin }} {{ $order->insurant->name_latin }} |
| День рождения | {{ $order->insurant->birth }} |
| Адрес | {{ $order->insurant->address }} |
| Адрес лат. | {{ $order->insurant->address_latin }} |
| Улица | {{ $order->insurant->street }} |
| Дом | {{ $order->insurant->house }} |
| Квартира | {{ $order->insurant->flat }} |
| ИНН | {{ $order->insurant->inn }} |
| Тип документа | {{ $order->insurant->doc_type }} |
| Номер документа | {{ $order->insurant->doc_number }} |
| Серия документа | {{ $order->insurant->doc_series }} |
| Дата выдачи документа | {{ $order->insurant->doc_date }} |
| Кем выдан документ | {{ $order->insurant->doc_given }} |
@endif
@if(!is_null($order->contract))
| ИНФОРМАЦИЯ О ПОЛИСЕ | - |
| Номер полиса | {{ $order->contract->number }} |
| Номер полиса 2 | {{ $order->contract->external_id }} |
| Состояние | {{ $order->contract->state }} |
| Ссылка | {{ $order->contract->policy_link }} |
@endif
| AssistMe | @if (!is_null($order->assist)) Да @else Нет @endif |
@if(!is_null($order->assist))
| Цена | {{ $order->assist->price }} |
| Статус оплаты | {{ $order->assist->payment_status }} |
| Ссылка на оплату | {{ $order->assist->payment_url }} |
@endif
@if(!is_null($order->tourists) && count($order->tourists) > 0)
| ИНФОРМАЦИЯ О ТУРИСТАХ | - |
@foreach($order->tourists as $tourist)
| ФИО | {{ $tourist->full_name }} |
| Серия документа | {{ $tourist->doc_series }} |
| Номер документа | {{ $tourist->doc_number }} |
| День рождения | {{ $tourist->birth }} |
| Цель поездки | @if (!is_null($tourist->goal)) {{ \App\Services\api\Ingo::GOALS[$tourist->goal] }} @endif |
@endforeach

@endif
@endcomponent

@endcomponent
