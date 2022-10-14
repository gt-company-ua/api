<?php

namespace Database\Seeders;

use App\Models\KaskoInsuranceValue;
use App\Models\TransportCategory;
use Illuminate\Database\Seeder;

class TransportSeeder extends Seeder
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
                'name_ua' => 'Легковий автомобіль',
                'name_ru' => 'Легковой автомобиль',
                'alias' => 'car',
                'ordering' => 1,
                'powers' => [
                    [
                        'name_ua' => 'до 1600 куб.см',
                        'name_ru' => 'до 1600 куб.см',
                        'api_id' => '1',
                        'ordering' => 1,
                        'coefficient' => 1.00
                    ],[
                        'name_ua' => '1601 — 2000 см3',
                        'name_ru' => '1601 — 2000 см3',
                        'api_id' => '2',
                        'ordering' => 2,
                        'coefficient' => 1.14
                    ],[
                        'name_ua' => '2001 — 3000 см3',
                        'name_ru' => '2001 — 3000 см3',
                        'api_id' => '3',
                        'ordering' => 3,
                        'coefficient' => 1.18
                    ],[
                        'name_ua' => '3000 см3 і більше',
                        'name_ru' => '3000 см3 и более',
                        'api_id' => '4',
                        'ordering' => 4,
                        'coefficient' => 1.82
                    ]
                ]
            ],
            [
                'name_ua' => 'Електромобіль',
                'name_ru' => 'Электромобиль',
                'alias' => 'ecar',
                'ordering' => 2,
                'powers' => [
                    [
                        'name_ua' => 'до 85 кВт',
                        'name_ru' => 'до 85 кВт',
                        'api_id' => '1',
                        'ordering' => 1,
                        'coefficient' => 1.00
                    ],[
                        'name_ua' => '86-100 кВт',
                        'name_ru' => '86-100 кВт',
                        'api_id' => '2',
                        'ordering' => 2,
                        'coefficient' => 1.14
                    ],[
                        'name_ua' => '101-150 кВт',
                        'name_ru' => '101-150 кВт',
                        'api_id' => '3',
                        'ordering' => 3,
                        'coefficient' => 1.18
                    ],[
                        'name_ua' => 'більше 150 кВт',
                        'name_ru' => 'больше 150 кВт',
                        'api_id' => '4',
                        'ordering' => 4,
                        'coefficient' => 1.82
                    ]
                ]
            ],
            [
                'name_ua' => 'Мотоцикл, моторолер',
                'name_ru' => 'Мотоцикл, мотороллер',
                'alias' => 'moto',
                'ordering' => 3,
                'powers' => [
                    [
                        'name_ua' => 'До 300 см3',
                        'name_ru' => 'До 300 см3',
                        'api_id' => '5',
                        'ordering' => 1,
                        'coefficient' => 0.37
                    ],[
                        'name_ua' => 'Від 300 см3 і більше',
                        'name_ru' => 'От 300 см3 и более',
                        'api_id' => '6',
                        'ordering' => 2,
                        'coefficient' => 0.67
                    ]
                ],
            ],
            [
                'name_ua' => 'Пасажирський автобус',
                'name_ru' => 'Пассажирский автобус',
                'alias' => 'bus',
                'ordering' => 4,
                'powers' => [
                    [
                        'name_ua' => 'До 20 осіб',
                        'name_ru' => 'До 20 человек',
                        'api_id' => '9',
                        'ordering' => 1,
                        'coefficient' => 3.30
                    ],[
                        'name_ua' => 'Від 20 осіб і більше',
                        'name_ru' => 'От 20 человек и более',
                        'api_id' => '10',
                        'ordering' => 2,
                        'coefficient' => 4.00
                    ]
                ],
            ],
            [
                'name_ua' => 'Вантажний автомобіль',
                'name_ru' => 'Грузовой автомобиль',
                'alias' => 'truck',
                'ordering' => 5,
                'powers' => [
                    [
                        'name_ua' => 'До 2 тонн',
                        'name_ru' => 'До 2 тонн',
                        'api_id' => '7',
                        'ordering' => 1,
                        'coefficient' => 2.00
                    ],[
                        'name_ua' => 'Від 2 тонн і більше',
                        'name_ru' => 'От 2 тонн и более',
                        'api_id' => '8',
                        'ordering' => 2,
                        'coefficient' => 2.20
                    ]
                ],
            ],
            [
                'name_ua' => 'Причіп, трейлер',
                'name_ru' => 'Прицеп, трейлер',
                'alias' => 'trailer',
                'ordering' => 6,
                'powers' => [
                    [
                        'name_ua' => 'До легкового',
                        'name_ru' => 'К легковому',
                        'api_id' => '11',
                        'ordering' => 1,
                        'coefficient' => 0.34
                    ],[
                        'name_ua' => 'До вантажного',
                        'name_ru' => 'К грузовому',
                        'api_id' => '12',
                        'ordering' => 2,
                        'coefficient' => 0.50
                    ]
                ],
            ],
        ];

        foreach ($data as $values) {
            $powers = $values['powers'] ?? [];
            unset($values['powers']);

            $insuranceValue = TransportCategory::create($values);

            if (count($powers) > 0) {
                $insuranceValue->powers()->createMany($powers);
            }
        }
    }
}
