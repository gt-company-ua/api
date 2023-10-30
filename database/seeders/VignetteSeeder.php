<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class VignetteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            [
                'name' => 'Austria',
                'code' => 'at',
                'products' => [
                    [
                        'name' => 'vignette-at-2a',
                        'vehicle_type' => 'car',
                    ],
                    [
                        'name' => 'vignette-at-2b',
                        'vehicle_type' => 'van',
                    ],
                    [
                        'name' => 'vignette-at-1m',
                        'vehicle_type' => 'moto',
                    ],
                    [
                        'name' => 'tunnel-at-a9',
                        'vehicle_type' => 'all',
                    ],
                    [
                        'name' => 'tunnel-at-a10',
                        'vehicle_type' => 'all',
                    ],
                    [
                        'name' => 'tunnel-at-a11',
                        'vehicle_type' => 'all',
                    ],
                    [
                        'name' => 'tunnel-at-a13',
                        'vehicle_type' => 'all',
                    ],
                    [
                        'name' => 'tunnel-at-s16',
                        'vehicle_type' => 'all',
                    ],
                ]
            ],
            [
                'name' => 'Switzerland',
                'code' => 'ch',
                'products' => [
                    [
                        'name' => 'vignette-ch-2a',
                        'vehicle_type' => 'car',
                    ],
                    [
                        'name' => 'vignette-ch-2b',
                        'vehicle_type' => 'van',
                    ],
                    [
                        'name' => 'vignette-ch-1m',
                        'vehicle_type' => 'moto',
                    ],
                ]
            ],
            [
                'name' => 'Hungary',
                'code' => 'hu',
                'products' => [
                    [
                        'name' => 'vignette-hu-2a',
                        'vehicle_type' => 'car',
                    ],
                    [
                        'name' => 'vignette-hu-2b',
                        'vehicle_type' => 'van',
                    ],
                    [
                        'name' => 'vignette-hu-2c',
                        'vehicle_type' => 'bus',
                    ],
                    [
                        'name' => 'vignette-hu-1m',
                        'vehicle_type' => 'moto',
                    ],
                ]
            ],
            [
                'name' => 'Slovakia',
                'code' => 'sk',
                'products' => [
                    [
                        'name' => 'vignette-sk-2a',
                        'vehicle_type' => 'car',
                    ],
                    [
                        'name' => 'vignette-sk-2b',
                        'vehicle_type' => 'van',
                    ]
                ]
            ],
            [
                'name' => 'Czech Republic',
                'code' => 'cz',
                'products' => [
                    [
                        'name' => 'vignette-cz-2a',
                        'vehicle_type' => 'car',
                    ],
                    [
                        'name' => 'vignette-cz-2b',
                        'vehicle_type' => 'van',
                    ]
                ]
            ],
            [
                'name' => 'Slovenia',
                'code' => 'si',
                'products' => [
                    [
                        'name' => 'vignette-si-2a',
                        'vehicle_type' => 'car',
                    ],
                    [
                        'name' => 'vignette-si-2b',
                        'vehicle_type' => 'van',
                    ],
                    [
                        'name' => 'vignette-si-1m',
                        'vehicle_type' => 'moto',
                    ]
                ]
            ],
            [
                'name' => 'Bulgaria',
                'code' => 'bg',
                'products' => [
                    [
                        'name' => 'vignette-bg-2a',
                        'vehicle_type' => 'car',
                    ],
                    [
                        'name' => 'vignette-bg-2b',
                        'vehicle_type' => 'van',
                    ]
                ]
            ],
            [
                'name' => 'Romania',
                'code' => 'ro',
                'products' => [
                    [
                        'name' => 'vignette-ro-2a',
                        'vehicle_type' => 'car',
                    ],
                    [
                        'name' => 'vignette-ro-2b',
                        'vehicle_type' => 'van',
                    ]
                ]
            ],

            [
                'name' => 'Ukraine',
                'code' => 'ua',
            ],

        ];

        foreach ($countries as $country) {
            $products = $country['products'] ?? [];
            unset($country['products']);

            $country = Country::create($country);

            if (count($products)) {
                $country->vignetteProducts()->createMany($products);
            }
        }
    }
}
