<?php

namespace App\Services\api;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    const USD = 'USD';
    const EUR = 'EUR';
    const CURRENCIES = [self::USD, self::EUR];

    public function getCurrencies()
    {
        if(date('H') < 16){
            $date = date('Y-m-d', strtotime('-1 day'));
        }else{
            $date = date('Y-m-d');
        }

        $currencies = Currency::whereDate('updated_at', '>=', $date)->get()->pluck('sum', 'code');

        if (count($currencies) === 0) {
            $this->updateCurrencies();

            $currencies = Currency::all()->pluck('sum', 'code');
        }

        return $currencies;
    }

    private function updateCurrencies()
    {
        $loadCurrencies = $this->loadCurrencies();

        foreach ($loadCurrencies as $currency) {
            if (! isset($currency['cc']) || ! in_array($currency['cc'], self::CURRENCIES)) {
                continue;
            }

            Currency::updateOrCreate(
                ['code' => $currency['cc']],
                ['sum' => $currency['rate']]
            );
        }
    }

    private function loadCurrencies()
    {
        try {
            $response = Http::timeout(3)
                ->get('https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange/exchange_rates', ['json' => true]);

            $body = $response->body();

            return json_decode($body, true);
        } catch (\Exception $e) {
            Log::error("Load currencies error: " . $e->getMessage());
            return [];
        }
    }
}