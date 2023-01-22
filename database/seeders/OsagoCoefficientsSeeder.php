<?php

namespace Database\Seeders;

use App\Models\OsagoCoefficient;
use App\Services\OsagoService;
use Illuminate\Database\Seeder;

class OsagoCoefficientsSeeder extends Seeder
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
                'name' => 'Есть льгота',
                'alias' => 'discount_yes',
                'coefficient' => 0.57
            ],[
                'name' => 'Нет льготы',
                'alias' => 'discount_no',
                'coefficient' => 1.00
            ],[
                'name' => 'Физ. лицо',
                'alias' => 'juristic',
                'coefficient' => 1.76
            ],[
                'name' => 'Юр. лицо',
                'alias' => 'juristic',
                'coefficient' => 1.80
            ],[
                'name' => 'Зона 1',
                'alias' => 'zone1',
                'coefficient' => 4.40
            ],[
                'name' => 'Зона 2',
                'alias' => 'zone2',
                'coefficient' => 3.35
            ],[
                'name' => 'Зона 3',
                'alias' => 'zone3',
                'coefficient' => 2.70
            ],[
                'name' => 'Зона 4',
                'alias' => 'zone4',
                'coefficient' => 2.40
            ],[
                'name' => 'Зона 5',
                'alias' => 'zone5',
                'coefficient' => 1.50
            ],[
                'name' => 'Зона 6',
                'alias' => 'zone6',
                'coefficient' => 9.00
            ],[
                'name' => 'Зона 7',
                'alias' => 'zone7',
                'coefficient' => 8.00
            ]
        ];
        foreach ($data as $row) {
            OsagoCoefficient::create($row);
        }

    }
}
