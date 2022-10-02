<?php

namespace App\Services;

use App\Http\Requests\KaskoSaveRequest;
use App\Models\KaskoInsuranceValue;
use App\Models\KaskoPrice;
use App\Models\KaskoTariff;
use App\Models\Order;
use App\Models\OrderInsurant;
use App\Models\OrderTransport;
use Illuminate\Database\Eloquent\Builder;

class KaskoService
{
    public function calculate(array $data): float
    {
        $coefficent = $this->calculateCoefficent($data);

        return round($data['insured_sum'] / 100 * $coefficent, 2);
    }

    private function findInsuranceValueByPrice(int $price)
    {
        $insuranceValue = KaskoInsuranceValue::where(
            function (Builder $query) use ($price) {
                $query->where('price_from', '<=', $price)
                    ->orWhereNull('price_from');
            }
            )
            ->where('price_to', '>=', $price)
            ->first();

        if (is_null($insuranceValue)) {
            $insuranceValue = KaskoInsuranceValue::orderBy('price_to', 'desc')
                ->first();
        }

        return $insuranceValue;
    }

    private function calculateCoefficent(array $data): float
    {
        $coefficient = $this->getBaseCoefficient($data['insured_sum']);

        if (isset($data['is_truck']) && $data['is_truck'] === true) {
            $insuranceValue = KaskoInsuranceValue::where('is_truck', true)
                ->first();
            $coefficient *= $insuranceValue->coefficient ?? 1;
        }

        $coefficient *= $this->coefficientYear($data['transport']['car_year'], $data['insured_sum']);

        $tariff = KaskoTariff::where('alias', $data['tariff'])->first();

        $coefficient *= $tariff->coefficient ?? 1;

        return round($coefficient, 2);
    }

    private function getBaseCoefficient(int $price): float
    {
        $insuranceValue = $this->findInsuranceValueByPrice($price);

        return $insuranceValue->coefficient ?? 1;
    }

    private function coefficientYear(int $carYear, int $price): float
    {
        $insuranceValue = $this->findInsuranceValueByPrice($price);

        $years = date('Y') - $carYear;
        $kaskoPrice = KaskoPrice::where('kasko_insurance_value_id', $insuranceValue->id)
            ->where('years', $years)->first();

        if (is_null($kaskoPrice)) {
            $kaskoPrice = KaskoPrice::where('kasko_insurance_value_id', $insuranceValue->id)
                ->orderBy('coefficient', 'desc')->first();
        }

        return $kaskoPrice->coefficient ?? 1;
    }

    public function saveOrder(array $request)
    {
        $transport = new OrderTransport($request['transport']);
        $insurant = new OrderInsurant($request['insurant']);

        unset($request['transport'], $request['insurant']);

        $request['order_type'] = 'kasko';

        $order = Order::create($request);

        $order->transport()->save($transport);
        $order->insurant()->save($insurant);

        return $order->load(['transport', 'insurant'])->refresh();
    }

}