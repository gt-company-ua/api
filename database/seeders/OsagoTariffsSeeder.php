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
                'coefficient' => 1.00
            ],[
                'franchise' => 1600,
                'tariff' => OsagoService::TARIFF_STANDART,
                'coefficient' => 1.20
            ],[
                'franchise' => 0,
                'tariff' => OsagoService::TARIFF_MAXIMUM,
                'coefficient' => 1.40
            ]
        ];

        foreach ($data as $row) {
            OsagoTariff::create($row);
        }
    }
}
