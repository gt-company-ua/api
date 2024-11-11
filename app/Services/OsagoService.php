<?php

namespace App\Services;

use App\Http\Requests\Osago\IngoSaveRequest;
use App\Http\Requests\Osago\SalamandraSaveRequest;
use App\Http\Requests\OsagoSaveRequest;
use App\Models\CarMark;
use App\Models\CarModel;
use App\Models\City;
use App\Models\Order;
use App\Models\OsagoCity;
use App\Models\OsagoCoefficient;
use App\Models\OsagoTariff;
use App\Models\TransportPower;
use App\Services\api\Ingo;
use App\Services\api\Profitsoft;
use App\Services\api\Salamandra;

class OsagoService
{
    const TARIFF_ECONOM = 'econom';
    const TARIFF_STANDART = 'standart';
    const TARIFF_MAXIMUM = 'maximum';
    const TARIFFS = [
        self::TARIFF_ECONOM,
        self::TARIFF_STANDART,
        self::TARIFF_MAXIMUM,
    ];

    public function calculate(array $data, $usePromocode = true): array
    {
        $power = TransportPower::where('id', $data['transport']['transport_power_id'])->first();

        $k1 = $power->coefficient;

        $k4 = $this->getCoefficient($data['insurant']['type']);

        $k3 = 1;
        $k5 = 1;
        $k6 = 1;
        $k  = 1;

        $city = ( ! empty($data['city_id']) && $data['city_id'] > 0)
            ? $data['city_id'] : $data['city_name'];

        $searchCity = $this->searchMtsbuCity($city);

        $zone = $searchCity['calcZone'] ?? '5';

        $k2 = $this->getCoefficient('zone' . $zone);

        if ($data['foreign_check'] === true) {
            $k4 = $this->getCoefficient('zone7');
            $k2 = $this->getCoefficient('zone6');
        }

        $discountAlias = ($data['discount_check'] === true) ? 'discount_yes' : 'discount_no';
        $discount = $this->getCoefficient($discountAlias);

        $prices = [];

        $tariffs = OsagoTariff::orderBy('franchise', 'DESC')->get();

        foreach ($tariffs as $tariff) {
            $tariffCoefficient = $tariff->coefficient;
            $price       = 180 * $k1 * $k2 * $k3 * $k4 * $k5 * $k6 * $k
                * $tariffCoefficient * $discount;

            if ($tariff->tariff == self::TARIFF_MAXIMUM) {
                $price += 320;
            }

            if ($usePromocode === true) {
                $price = (new OrderService(null))->usePromocode($data['promocode'] ?? null, $price, Order::ORDER_TYPE_OSAGO);
            }

            $prices[$tariff->tariff] = [
                'tariff' => $tariff->tariff,
                'price'  => ceil($price),
            ];
        }

        return [
            'k'      => $k,
            'k1'     => $k1,
            'k2'     => $k2,
            'k3'     => $k3,
            'k4'     => $k4,
            'k5'     => $k5,
            'k6'     => $k6,
            'prices' => $prices
        ];
    }

    private function getCoefficient($alias): float
    {
        $coefficient = OsagoCoefficient::whereAlias($alias)->first();

        return $coefficient->coefficient ?? 1.00;
    }

    public function searchMtsbuCity($city)
    {
        if (empty($city)) return null;

        $searchBy   = is_numeric($city) ? 'mtsbuId' : 'name';
        $searchCity = (new Profitsoft())->searchCity($city, $searchBy);

        if (count($searchCity) === 1) {
            return $searchCity[0];
        }

        return null;
    }

    public function saveOrder(OsagoSaveRequest $request): ?Order
    {
        $data = $request->validated();

        $prices = $this->calculate($data, false);

        $data['price'] = $prices['prices'][$data['tariff']]['price'] ?? null;

        $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_OSAGO);

        if (is_null($order)) {
            return null;
        }

        if ($order->upload_docs === false) {
            (new Profitsoft())->reserve($order);
        }

        return $order;
    }

    public function saveOrderSalamandra(SalamandraSaveRequest $request): ?Order
    {
        $data = $request->validated();

        $calculate = (new Salamandra())->calculate($data);

        if (isset($calculate['success']) && $calculate['success'] && isset($calculate['data']['totalPayment'])) {
            $data['price'] = $calculate['data']['totalPayment'];
        } elseif (isset($calculate['message'])) {
            if (! isset($data['comment'])) $data['comment'] = '';

            $data['comment'] .= ' Ошибка рассчитать стоимость: ' . $calculate['message'];
        }

        $city = City::find($data['city_id']);

        $data['city_name'] = $city->name;

        $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_OSAGO);

        if (is_null($order)) {
            return null;
        }

        if ($order->upload_docs === false) {
            (new Salamandra())->order($order);
        }

        return $order;
    }

    public function saveOrderIngo(IngoSaveRequest $request): ?Order
    {
        $data = $request->validated();

        $calculate = (new Ingo())->osagoCalculate($data);

        if (isset($calculate['data']['amount'])) {
            $data['price'] = $calculate['data']['amount'];

            if (isset($calculate['data']['dgo'])) {
                $data['price'] += round($calculate['data']['dgo'], 2);
            }

            if (isset($calculate['cashback'])) {
                $data['cashback_amount'] = $calculate['cashback'];
            }
        }

        $city = OsagoCity::find($data['city_id']);

        $data['city_name'] = $city->name;

        if (!empty($data['transport']['car_mark_code'])) {
            $carMark = CarMark::where('external_id', $data['transport']['car_mark_code'])->first();
            $data['transport']['car_mark_id'] = $carMark->id;
            unset($data['transport']['car_mark_code']);
        }

        if (!empty($data['transport']['car_model_code'])) {
            $carModel = CarModel::where('external_id', $data['transport']['car_model_code'])->first();
            $data['transport']['car_model_id'] = $carModel->id;
            unset($data['transport']['car_model_code']);
        }

        unset($data['transport']['car_mark_code'], $data['transport']['car_model_code']);

        if (!empty($data['transport']['car_mark_id'])) {
            $carMark = CarMark::find($data['transport']['car_mark_id']);
            $data['transport']['car_mark'] = $carMark->name;
        }

        if (!empty($data['transport']['car_model_id'])) {
            $carModel = CarModel::find($data['transport']['car_model_id']);
            $data['transport']['car_model'] = $carModel->name;
        }

        $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_OSAGO);

        if (is_null($order)) {
            return null;
        }

        if ($order->upload_docs === false) {
            (new Ingo())->osagoDraft($order);
        }

        return $order;
    }
}
