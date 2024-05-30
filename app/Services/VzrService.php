<?php

namespace App\Services;

use App\Http\Requests\VzrSaveRequest;
use App\Models\CovidPrice;
use App\Models\Order;
use App\Models\VzrCashback;
use App\Models\VzrRangeDay;
use App\Services\api\CurrencyService;
use Exception;

class VzrService
{
    private $prices;

    const AGE_RANGES = ['0-3', '4-12', '13-17', '18-59', '60-65', '66-70'];

    /**
     * @throws Exception
     */
    public function saveOrder(VzrSaveRequest $request): ?\App\Models\Order
    {
        $data = $request->validated();

        $price = $this->calculate($data, false);

        $data['price'] = $price['price'];

        $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_VZR);

        if (is_null($order)) {
            return null;
        }

        return $order;
    }

    /**
     * @throws Exception
     */
    public function calculate(array $data, $usePromocode = true): ?array
    {
        $this->loadPrices();

        $currencies = (new CurrencyService())->getCurrencies();
        $multiCoefficient = 1;

        if($data['multiple_trip'] === true){
            $duration = VzrRangeDay::find($data['vzr_range_day_id']);

            $days = $duration->days;

            $multiCoefficient = 1.05;
        } else {
            $start = new \DateTime($data['polis_start']);
            $end   = new \DateTime($data['polis_end']);

            $interval = $end->diff($start);

            $days = intval($interval->format('%a')) + 1;
        }

        $countRate = $this->getRateInsuredCount(count($data['tourists'] ?? []));

        $keyDays = ($data['insured_sum'] == '30000') ? 1 : 2;

        $basePrice = $this->prices['days_' . $days][$keyDays] * $multiCoefficient * $countRate;

        $currency = ($data['territory'] == 'world') ? CurrencyService::USD : CurrencyService::EUR;

        $allPrice = 0;

        foreach ($data['tourists'] as $tourist){
            $rate = $this->getRateTourist($tourist['birth'], $data);

            if($rate === false){
                return null;
            }

            $allPrice += round($basePrice * $rate * $currencies[$currency]);
        }

//        if($data['with_covid'] === true){
//            $price_covid = $this->searchCovidPrice($days);
//            $allPrice += $price_covid * count($data['tourists']);
//        }

        $ranges = [];
        $rangesTotalPrice = 0;

        if (isset($data['ranges']) && is_array($data['ranges'])) {
            foreach ($data['ranges'] as $ageRange) {
                $rateRange = $this->calculateRate($data, $ageRange);
                $price = round($basePrice * $rateRange * $currencies[$currency]);

                $ranges[] = $price;

                $rangesTotalPrice += $price;
            }
        }

        //$allPrice *= $currencies[$currency];

        if($data['epolis'] === false && $data['with_greencard'] === false){
            $allPrice += 40;
            if ($rangesTotalPrice > 0) $rangesTotalPrice += 40;
        }

        if ($usePromocode === true) {
            $allPrice = (new OrderService(null))->usePromocode($data['promocode'] ?? null, $allPrice, Order::ORDER_TYPE_VZR);
            $rangesTotalPrice = (new OrderService(null))->usePromocode($data['promocode'] ?? null, $rangesTotalPrice, Order::ORDER_TYPE_VZR);
        }

        return [
            'price' => round($allPrice),
            'days' => intval($days),
            'ranges' => $ranges,
            'ranges_total_price' => round($rangesTotalPrice),
        ];
    }

    private function getRateInsuredCount($count)
    {

        if($count >= 0 && $count <= 3) {
            $range = '0-3';
        } else if($count >= 4 && $count <= 10) {
            $range = '4-10';
        } else if($count >= 11 && $count <= 20) {
            $range = '11-20';
        } else if($count >= 21) {
            $range = '21';
        } else {
            return 1;
        }

        return round($this->prices['insured_' . $range][1], 2) ?? 1;
    }

    private function searchCovidPrice($days): float
    {
        $covidPrice = CovidPrice::where('days', '>=', $days)
            ->orderBy('days', 'ASC')->first();


        if(is_null($covidPrice)){
            $covidPrice = CovidPrice::orderBy('days', 'DESC')->first();
        }

        return $covidPrice->price ?? 0.00;
    }

    private function calculateRate($data, $ageRate)
    {
        $rate = $this->prices['age_' . $ageRate][1];

        if (isset($this->prices['target_' . $data['target']])) {
            $rate *= $this->prices['target_' . $data['target']][1];
        }

        $rate *= $this->prices['terra_' . $data['territory']][1];

        $rate *= $this->prices['sport_' . $data['sport']][1];

        if($rate < 1){
            $rate = 1;
        }

        return $rate;
    }

    /**
     * @throws Exception
     */
    private function getRateTourist($birth, $data)
    {
        $start = new \DateTime($birth);
        $end   = new \DateTime(date('Y-m-d'));

        $interval = $end->diff($start);

        $age = intval($interval->format('%y'));

        if($age >= 0 && $age <= 3) {
            $ageRate = '0';
        } else if($age >= 4 && $age <= 15) {
            $ageRate = '4-15';
        } else if($age >= 16 && $age <= 18) {
            $ageRate = '16-18';
        } else if($age >= 19 && $age <= 60) {
            $ageRate = '19-60';
        } else if($age >= 61 && $age <= 65) {
            $ageRate = '61-65';
        } else if($age >= 66 && $age <= 70) {
            $ageRate = '66-70';
        } else {
            return false;
        }

        return $this->calculateRate($data, $ageRate);
    }

    private function loadPrices(): void
    {
        $pricesCsv = storage_path('app/prices') . '/vzr.csv';

        $csv = [];
        if (($fileCsv = fopen($pricesCsv, "r")) !== FALSE) {
            while (($data = fgetcsv($fileCsv, 1000, ";")) !== FALSE) {
                $csv[$data[0]] = $data;
            }
            fclose($fileCsv);
        }

        $this->prices = $csv;
    }

    public function getCashback($tariff, $amount): ?float
    {
        $cashback = VzrCashback::where('tariff', $tariff)->first();

        if (is_null($cashback) || is_null($cashback->amount) || $cashback->amount <= 0) {
            return null;
        }

        return ceil(round($amount / 100 * $cashback->amount, 0) / 10) * 10;
    }
}
