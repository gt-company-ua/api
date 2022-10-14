<?php

namespace Database\Seeders;

use App\Models\CovidPrice;
use Illuminate\Database\Seeder;

class CovidPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            array('days' => '15', 'price' => '79.00'),
            array('days' => '30', 'price' => '169.00'),
            array('days' => '60', 'price' => '349.00'),
            array('days' => '90', 'price' => '399.00'),
            array('days' => '120', 'price' => '449.00'),
            array('days' => '150', 'price' => '499.00'),
            array('days' => '180', 'price' => '549.00'),
            array('days' => '210', 'price' => '649.00'),
            array('days' => '240', 'price' => '699.00'),
            array('days' => '270', 'price' => '749.00'),
            array('days' => '300', 'price' => '799.00'),
            array('days' => '330', 'price' => '849.00'),
            array('days' => '360', 'price' => '899.00')
        ];

        foreach ($data as $row) {
            CovidPrice::create($row);
        }
    }
}
