<?php

namespace Database\Seeders;

use App\Models\VzrRange;
use Illuminate\Database\Seeder;

class VzrSeeder extends Seeder
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
                'name' => '180',
                'active' => true,
                'days' => [
                    ['days' => 30, 'sum' => 13.20],
                    ['days' => 45, 'sum' => 17.27],
                    ['days' => 90, 'sum' => 30.69]
                ],
            ],[
                'name' => '365',
                'active' => true,
                'days' => [
                    ['days' => 30, 'sum' => 15.02],
                    ['days' => 45, 'sum' => 19.63],
                    ['days' => 90, 'sum' => 34.88],
                    ['days' => 180, 'sum' => 63.94],
                    ['days' => 365, 'sum' => 129.66]
                ],
            ]
        ];

        foreach ($data as $values) {
            $days = $values['days'] ?? [];
            unset($values['days']);

            $vzrRange = VzrRange::create($values);

            if (count($days) > 0) {
                $vzrRange->days()->createMany($days);
            }
        }
    }
}
