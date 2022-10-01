<?php

namespace Database\Seeders;

use App\Models\KaskoInsuranceValue;
use App\Models\KaskoTariff;
use Illuminate\Database\Seeder;

class KaskoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createTariffs();
        $this->createInsuranceValues();
    }

    private function createTariffs()
    {
        $data = [
            [
                'alias' => '50x50',
                'coefficient' => 0.60
            ],
            [
                'alias' => 'standart',
                'coefficient' => 1,
            ],
            [
                'alias' => 'first_case',
                'coefficient' => 0.70,
            ],
        ];

        foreach ($data as $values) {
            KaskoTariff::create($values);
        }
    }

    private function createInsuranceValues()
    {
        $data = [
            [
                'price_to' => 300000,
                'coefficient' => 4.33,
                'prices' => [
                    ['years' => 0, 'coefficient' => 1.00],
                    ['years' => 1, 'coefficient' => 1.00],
                    ['years' => 2, 'coefficient' => 1.05],
                    ['years' => 3, 'coefficient' => 1.25],
                    ['years' => 4, 'coefficient' => 1.35],
                    ['years' => 5, 'coefficient' => 1.48],
                    ['years' => 6, 'coefficient' => 1.60],
                    ['years' => 7, 'coefficient' => 1.72],
                    ['years' => 8, 'coefficient' => 1.84],
                    ['years' => 9, 'coefficient' => 1.96],

                    ['years' => 10, 'coefficient' => 1.20],
                    ['years' => 11, 'coefficient' => 1.25],
                    ['years' => 12, 'coefficient' => 1.30],
                    ['years' => 13, 'coefficient' => 1.40],
                    ['years' => 14, 'coefficient' => 1.50],
                    ['years' => 15, 'coefficient' => 1.60],
                    ['years' => 16, 'coefficient' => 1.70],
                    ['years' => 17, 'coefficient' => 1.80],
                    ['years' => 18, 'coefficient' => 1.90]
                ]
            ],
            [
                'price_from' => 300001,
                'price_to' => 600000,
                'coefficient' => 3.87,
                'prices' => [
                    ['years' => 0, 'coefficient' => 1.00],
                    ['years' => 1, 'coefficient' => 1.00],
                    ['years' => 2, 'coefficient' => 1.03],
                    ['years' => 3, 'coefficient' => 1.23],
                    ['years' => 4, 'coefficient' => 1.33],
                    ['years' => 5, 'coefficient' => 1.45],
                    ['years' => 6, 'coefficient' => 1.57],
                    ['years' => 7, 'coefficient' => 1.69],
                    ['years' => 8, 'coefficient' => 1.81],
                    ['years' => 9, 'coefficient' => 1.92],

                    ['years' => 10, 'coefficient' => 1.20],
                    ['years' => 11, 'coefficient' => 1.25],
                    ['years' => 12, 'coefficient' => 1.30],
                    ['years' => 13, 'coefficient' => 1.40],
                    ['years' => 14, 'coefficient' => 1.50],
                    ['years' => 15, 'coefficient' => 1.60],
                    ['years' => 16, 'coefficient' => 1.70],
                    ['years' => 17, 'coefficient' => 1.80],
                    ['years' => 18, 'coefficient' => 1.90]
                ]
            ],
            [
                'price_from' => 600001,
                'price_to' => 900000,
                'coefficient' => 3.43,
                'prices' => [
                    ['years' => 0, 'coefficient' => 1.00],
                    ['years' => 1, 'coefficient' => 1.00],
                    ['years' => 2, 'coefficient' => 1.02],
                    ['years' => 3, 'coefficient' => 1.20],
                    ['years' => 4, 'coefficient' => 1.30],
                    ['years' => 5, 'coefficient' => 1.43],
                    ['years' => 6, 'coefficient' => 1.55],
                    ['years' => 7, 'coefficient' => 1.67],
                    ['years' => 8, 'coefficient' => 1.79],
                    ['years' => 9, 'coefficient' => 1.90],

                    ['years' => 10, 'coefficient' => 1.20],
                    ['years' => 11, 'coefficient' => 1.25],
                    ['years' => 12, 'coefficient' => 1.30],
                    ['years' => 13, 'coefficient' => 1.40],
                    ['years' => 14, 'coefficient' => 1.50],
                    ['years' => 15, 'coefficient' => 1.60],
                    ['years' => 16, 'coefficient' => 1.70],
                    ['years' => 17, 'coefficient' => 1.80],
                    ['years' => 18, 'coefficient' => 1.90]
                ]
            ],
            [
                'price_from' => 900001,
                'coefficient' => 3.18,
                'prices' => [
                    ['years' => 0, 'coefficient' => 1.00],
                    ['years' => 1, 'coefficient' => 1.00],
                    ['years' => 2, 'coefficient' => 1.01],
                    ['years' => 3, 'coefficient' => 1.20],
                    ['years' => 4, 'coefficient' => 1.28],
                    ['years' => 5, 'coefficient' => 1.42],
                    ['years' => 6, 'coefficient' => 1.52],
                    ['years' => 7, 'coefficient' => 1.65],
                    ['years' => 8, 'coefficient' => 1.75],
                    ['years' => 9, 'coefficient' => 1.86],

                    ['years' => 10, 'coefficient' => 1.20],
                    ['years' => 11, 'coefficient' => 1.25],
                    ['years' => 12, 'coefficient' => 1.30],
                    ['years' => 13, 'coefficient' => 1.40],
                    ['years' => 14, 'coefficient' => 1.50],
                    ['years' => 15, 'coefficient' => 1.60],
                    ['years' => 16, 'coefficient' => 1.70],
                    ['years' => 17, 'coefficient' => 1.80],
                    ['years' => 18, 'coefficient' => 1.90]
                ]
            ],
            [
                'is_truck' => true,
                'coefficient' => 1.75
            ],
        ];

        foreach ($data as $values) {
            $prices = $values['prices'] ?? [];
            unset($values['prices']);

            $insuranceValue = KaskoInsuranceValue::create($values);

            if (count($prices) > 0) {
                $insuranceValue->prices()->createMany($prices);
            }
        }
    }
}
