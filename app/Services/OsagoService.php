<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use App\Http\Requests\OsagoSaveRequest;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\OsagoCoefficient;
use App\Models\OsagoTariff;
use App\Models\TransportPower;
use App\Services\api\Profitsoft;
use Illuminate\Support\Str;

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
}