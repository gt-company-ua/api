<?php

namespace App\Services;

use App\Http\Requests\GreenCardSaveRequest;
use App\Mail\OrderOffer;
use App\Models\GreencardCashback;
use App\Models\Order;
use App\Models\OrderContract;
use App\Models\TransportCategory;
use App\Services\api\Ingo;
use App\Services\api\TasIns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class GreenCardService
{
    public function saveOrder(GreenCardSaveRequest $request): ?Order
    {
        $data = $request->validated();

        $calculate = (new Ingo())->greenCardCalculate($data);

        $amount = 0;
        if ( ! empty($calculate['data'])) {
            $amount = round($calculate['data']['amount'], 2);
        }

        $data['price'] = $amount;
        $data['cashback_amount'] = $this->getCashback($data['trip_duration'], $data['trip_country'], $data['transport']['transport_category_id']);
        $data['status_contract'] = OrderContract::STATUS_CONTRACT_NOT_SENT;
        $data['sent_offer'] = false;

        $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_GC);

        if (is_null($order)) {
            return null;
        }

        return $order->load(['transport', 'insurant', 'contract', 'files', 'tourists', 'assist'])->refresh();
    }

    public function saveOrderV2(\App\Http\Requests\v2\GreenCardSaveRequest $request): ?Order
    {
        $data = $request->validated();
        $amount = 0;

        if ($data['insurance_company'] === Ingo::API_NAME) {
            $calculate = (new Ingo())->greenCardCalculate($data);
            if ( ! empty($calculate['data'])) {
                $amount = round($calculate['data']['amount'], 2);
            }
        } else if ($data['insurance_company'] === TasIns::API_NAME) {
            $calculate = (new TasIns())->greenCardCalculate($data);
            if (!empty($calculate) && $calculate['result']) {
                $amount = round($calculate['InsPremium'], 2);
            }
        }

        $data['price'] = $amount;
        $data['cashback_amount'] = $this->getCashback($data['trip_duration'], $data['trip_country'], $data['transport']['transport_category_id']);
        $data['status_contract'] = OrderContract::STATUS_CONTRACT_NOT_SENT;
        $data['sent_offer'] = false;

        $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_GC);

        if (is_null($order)) {
            return null;
        }

        return $order->load(['transport', 'insurant', 'contract', 'files', 'tourists', 'assist'])->refresh();
    }

    public function getCashback($months, $tripCountry, $transportCategoryID)
    {
        $transportCategory = TransportCategory::find($transportCategoryID);

        $transportType = 'default';

        if (!is_null($transportCategory)) {
            if ($transportCategory->alias === 'bus') {
                $transportType = 'truck';
            }

            if (in_array($transportCategory->alias, GreencardCashback::TRANSPORT_TYPE)) {
                $transportType = $transportCategory->alias;
            }
        }

        $cashback = GreencardCashback::where('months', $months)->where('trip_country', $tripCountry)->where('transport_type', $transportType)->first();

        return $cashback->amount ?? 0;
    }

    public function calculate(array $data, $gos = false, $usePromocode = true) {
        $prices = $this->loadPrices();

        $transportCategory = TransportCategory::whereId($data['transport']['transport_category_id'])->first();

        if($transportCategory->alias === 'car'){
            $transportCategory->alias = 'auto';
        }

        $prefix = $gos ? 'gos_' : '';
        $basePrice = round($prices[$prefix . $data['trip_country'] . '_' . $transportCategory->alias][$data['trip_duration']]);

        if ($gos === false && $usePromocode === true) {
            $basePrice = (new OrderService(null))->usePromocode($data['promocode'] ?? null, $basePrice, Order::ORDER_TYPE_GC);
        }

        return floor($basePrice);
    }

    private function loadPrices(): array
    {
        $pricesCsv = storage_path('app/prices') . '/zk.csv';

        $rowN = -1; // номер строки
        $csv = [];
        if (($fileCsv = fopen($pricesCsv, "r")) !== FALSE) {
            while (($data = fgetcsv($fileCsv, 1000, ";")) !== FALSE) {
                $row = [];

                if ($rowN == -1) {  // первую пропускаем, т.к. там заголовки
                    $rowN++; continue;
                }

                $num = count($data); // длина строки
                $rowN++;

                for ($c=0; $c < $num; $c++) { // сначала собираем всё в одну строку
                    if (($data[$c] != '===') && ($data[$c] != '')) { // если значение не равно визуальному разделителю
                        $row[] = $data[$c];
                    }
                }

                for ($r=2; $r < count($row); $r++) { // перебираем строку, начиная с 3й ячейки, т.к...
                    $csv[$row[0] . '_' . $row[1]][] = intval(preg_replace('/\s+/', '', $row[$r])); // из первых двух формируется имя строки вида sng_auto и т.д. +++ через intval и preg_replace парсится число, если вдруг оно записано через пробел
                }
            }
            fclose($fileCsv);
        }

        return $csv;
    }

    function sendGreenCardDraft()
    {
        $orders = Order::where('order_type', Order::ORDER_TYPE_GC)
            ->where('upload_docs', false)
            ->where('status_contract', OrderContract::STATUS_CONTRACT_NOT_SENT)
            ->limit(10)
            ->get();

        foreach ($orders as $order) {
            if ($order->insurance_company === TasIns::API_NAME) {
                (new TasIns())->greenCardRegister($order);
            } else {
                (new Ingo())->greenCardDraft($order);
            }

            if ($order->payment_status === OrderService::PAYMENT_STATUS_OK || (!is_null($order->partner) && $order->paid)) {
                (new OrderService($order))->saveGreenCard1C();
            }

            $order = Order::find($order->id);
            $this->sendGreenCardOffer($order);
        }
    }

    public function sendGreenCardConfirm()
    {
        $orders = Order::where('order_type', Order::ORDER_TYPE_GC)
            ->where('polis_start', '>=', date('Y-m-d'))
            ->where('upload_docs', false)
            ->where('payment_status', OrderService::PAYMENT_STATUS_OK)
            ->where('status_contract', OrderContract::STATUS_CONTRACT_SENT)
            ->whereHas('contract', function (Builder $query) {
                $query->whereIn('api_name', [Ingo::API_NAME, TasIns::API_NAME]);
                $query->where('state', 'Draft');
            })
            ->limit(10)
            ->get();

        foreach ($orders as $order) {
            (new OrderService($order))->saveGreenCard1C();
        }
    }

    public function cronGreenCardOffers()
    {
        $orders = Order::where('order_type', Order::ORDER_TYPE_GC)
            ->where('upload_docs', false)
            ->where('status_contract', OrderContract::STATUS_CONTRACT_SENT)
            ->where('sent_offer', false)
            ->where('confirm_sms', false)
            ->limit(5)
            ->get();

        foreach ($orders as $order) {
            $this->sendGreenCardOffer($order);
        }
    }

    private function sendGreenCardOffer(Order $order)
    {
        $files = (new Ingo())->greenCardPrintOffer($order);
        if (count($files) === 0) {
            return;
        }

        $code = mt_rand(100000, 999999);

        Mail::to($order->email)->bcc(env('MAIL_OFFICE'))->send(new OrderOffer($files, $code));

        $order->send_sms = $code;
        $order->sent_offer = true;
        $order->save();
    }
}
