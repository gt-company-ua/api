<?php

namespace App\Services;

use App\Http\Requests\GreenCardSaveRequest;
use App\Models\TransportCategory;
use App\Services\api\OneC;
use App\Services\api\Profitsoft;

class GreenCardService
{
    public function saveOrder(GreenCardSaveRequest $request)
    {
        $data = $request->validated();

        $data['price'] = $this->calculate($data);

        $order = (new OrderService(null))->saveOrder($data, 'greencard');

        if (is_null($order)) {
            return null;
        }

        if ($order->upload_docs === false) {
            (new OneC())->saveGreenCard($order, 'Draft');
        }

        return $order;
    }

    public function calculate(array $data, $gos = false) {
        $prices = $this->loadPrices();

        $transportCategory = TransportCategory::whereId($data['transport']['transport_category_id'])->first();

        if($transportCategory->alias === 'car'){
            $transportCategory->alias = 'auto';
        }

        $prefix = $gos ? 'gos_' : '';
        $basePrice = round($prices[$prefix . $data['trip_country'] . '_' . $transportCategory->alias][$data['trip_duration']]);

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
}