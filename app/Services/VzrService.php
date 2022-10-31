<?php

namespace App\Services;

use App\Http\Requests\VzrSaveRequest;
use App\Models\CovidPrice;
use App\Models\VzrRangeDay;
use App\Services\api\CurrencyService;
use Exception;

class VzrService
{
    private $prices;


    public function saveOrder(VzrSaveRequest $request)
    {
        $data = $request->validated();

        $price = $this->calculate($data);

        $data['price'] = $price['price'];

        $order = (new OrderService(null))->saveOrder($data, 'vzr');

        if (is_null($order)) {
            return null;
        }

        return $order;
    }

    /**
     * @throws Exception
     */
    public function calculate(array $data): ?array
    {
        $this->loadPrices();

        $currencies = (new CurrencyService())->getCurrencies();

        if($data['multiple_trip'] === true){
            $duration = VzrRangeDay::find($data['vzr_range_day_id']);

            $days = $duration->days;

            $basePrice = $duration->sum;

            $basePrice *= $currencies[CurrencyService::EUR];
        } else {
            $start = new \DateTime($data['polis_start']);
            $end   = new \DateTime($data['polis_end']);

            $interval = $end->diff($start);

            $days = $interval->format('%a') + 1;

            $basePrice = $this->prices['days_' . $days] * $currencies[CurrencyService::USD];
        }

        $allPrice = 0;

        foreach ($data['tourists'] as $tourist){
            $rate = $this->getRateTourist($tourist['birth'], $data);

            if($rate === false){
                return null;
            }

            $allPrice += $basePrice * $rate;
        }

        if($data['with_covid'] === true){
            $price_covid = $this->searchCovidPrice($days);
            $allPrice += $price_covid * count($data['tourists']);
        }

        if($data['epolis'] === false && $data['with_greencard'] === false){
            $allPrice += 40;
        }

        return [
            'price' => round($allPrice),
            'days' => intval($days)
        ];
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

    /**
     * @throws Exception
     */
    private function getRateTourist($birth, $data)
    {
        $start = new \DateTime($birth);
        $end   = new \DateTime(date('Y-m-d'));

        $interval = $end->diff($start);

        $age = intval($interval->format('%y'));

        if($age >= 0 && $age < 1) {
            $ageRate = '0';
        } else if($age >= 1 && $age <= 59) {
            $ageRate = '1-59';
        } else if($age >= 60 && $age <= 65) {
            $ageRate = '60-65';
        } else if($age >= 66 && $age <= 75) {
            $ageRate = '66-75';
        } else {
            return false;
        }

        $rate = 1;

        $rate *= $this->prices['terra_' . $data['territory']];

        $rate *= $this->prices['sport_' . $data['sport']];

        $rate *= $this->prices['target_' . $data['target']];

        $rate *= $this->prices['insurance_' . $data['insured_sum']];

        $rate *= $this->prices['age_' . $ageRate];

        if($rate < 1){
            $rate = 0;
        }

        return $rate;
    }

    private function loadPrices(): void
    {
        $pricesCsv = storage_path('app/prices') . '/vzr.csv';

        $csv = [];
        if (($fileCsv = fopen($pricesCsv, "r")) !== FALSE) {
            while (($data = fgetcsv($fileCsv, 1000, ";")) !== FALSE) {
                $csv[$data[0]] = $data[1];
            }
            fclose($fileCsv);
        }

        $this->prices = $csv;
    }
}