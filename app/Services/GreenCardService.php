<?php

namespace App\Services;

use App\Models\TransportCategory;

class GreenCardService
{
    function calculate(array $data, $gos = false) {
        $prices = $this->loadPrices();

        $transportCategory = TransportCategory::whereId($data['transport']['transport_category_id'])->first();

        $transportType = $data['transport']['transport_category_id'];
        if($transportCategory->alias === 'car'){
            $transportType = 'auto';
        }

        $prefix = $gos ? 'gos_' : '';
        $basePrice = round($prices[$prefix . $data['trip_country'] . '_' . $transportType][$data['trip_duration']]);

        return floor($basePrice);
    }

    private function loadPrices(): array
    {
        $prices_csv = storage_path('app/prices') . '/zk.csv';

        $row_n = -1; // номер строки
        $csv = [];
        if (($file_csv = fopen($prices_csv, "r")) !== FALSE) {
            while (($data = fgetcsv($file_csv, 1000, ";")) !== FALSE) {
                $row = [];

                if ($row_n == -1) {  // первую пропускаем, т.к. там заголовки
                    $row_n++; continue;
                }

                $num = count($data); // длина строки
                $row_n++;

                for ($c=0; $c < $num; $c++) { // сначала собираем всё в одну строку
                    if (($data[$c] != '===') && ($data[$c] != '')) { // если значение не равно визуальному разделителю
                        $row[] = $data[$c];
                    }
                }

                for ($r=2; $r < count($row); $r++) { // перебираем строку, начиная с 3й ячейки, т.к...
                    $csv[$row[0] . '_' . $row[1]][] = intval(preg_replace('/\s+/', '', $row[$r])); // из первых двух формируется имя строки вида sng_auto и т.д. +++ через intval и preg_replace парсится число, если вдруг оно записано через пробел
                }
            }
            fclose($file_csv);
        }

        return $csv;
    }
}