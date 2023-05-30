<?php

namespace App\Services;

use App\Http\Requests\GreenCardSaveRequest;
use App\Models\Order;
use App\Models\TransportCategory;
use App\Services\api\OneC;
use App\Services\api\Profitsoft;
use Illuminate\Support\Facades\Log;

class GreenCardService
{
    const STATUS_CONTRACT_NOT_SENT = 'not_sent';
    const STATUS_CONTRACT_SENT = 'sent';
    const STATUS_CONTRACT_ERROR = 'error';
    public function saveOrder(GreenCardSaveRequest $request): ?Order
    {
        $data = $request->validated();

        $price = $this->calculate($data, false, false);
        $priceGos = $this->calculate($data, true, false);

        $data['price'] = $priceGos;
        $data['cashback_amount'] = round($priceGos - $price);
        $data['status_contract'] = self::STATUS_CONTRACT_NOT_SENT;

        $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_GC);

        if (is_null($order)) {
            return null;
        }

        return $order->load(['transport', 'insurant', 'contract', 'files', 'tourists', 'assist'])->refresh();
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
            ->where('status_contract', self::STATUS_CONTRACT_NOT_SENT)
            ->limit(2)
            ->get();

        $timeStart = date('d.m.Y H:i:s');

        foreach ($orders as $order) {
            (new OneC())->saveGreenCard($order, 'Draft');
        }

        Log::debug('sendGreenCardDraft() Time start:' . $timeStart . '. Time end: '. date('d.m.Y H:i:s'));
    }
}
