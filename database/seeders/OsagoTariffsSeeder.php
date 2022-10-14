<?php

namespace Database\Seeders;

use App\Models\OsagoTariff;
use App\Services\OsagoService;
use Illuminate\Database\Seeder;

class OsagoTariffsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'franchise' => 3200,
                'tariff' => OsagoService::TARIFF_ECONOM,
                'coefficient' => 0.80
            ],[
                'franchise' => 1600,
                'tariff' => OsagoService::TARIFF_STANDART,
                'coefficient' => 0.95
            ],[
                'franchise' => 0,
                'tariff' => OsagoService::TARIFF_MAXIMUM,
                'coefficient' => 1.00
            ]
        ];

        foreach ($data as $row) {
            OsagoTariff::create($row);
        }
    }
}
