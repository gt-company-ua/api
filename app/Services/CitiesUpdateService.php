<?php

namespace App\Services;

use App\Models\City;
use App\Services\api\Salamandra;

class CitiesUpdateService
{
    public function __invoke()
    {
        try {
            $cities = (new Salamandra())->cities();
        } catch (\Exception $e) {
            print $e->getMessage();
            return $e->getMessage();
        }

        $places = [];
        $parts = [];

        foreach ($cities as $city) {
            if (empty($city['name'])) continue;

            $places[] = [
                'external_id' => $city['id'],
                'name' => $city['name'],
                'name_eng' => $city['nameEng'] ?? null,
                'zone' => $city['zone'] ?? null,
                'koatuu' => $city['koatuu'] ?? null,
                'mtibuCode' => $city['mtibuCode'] ?? null,
                'ewaId' => $city['ewaId'] ?? null,
                'region_id' => !empty($city['region']) ? $city['region']['id'] : null,
                'region_name' => !empty($city['region']) ? $city['region']['name'] : null,
            ];

            if (count($places) >= 20) {
                $parts[] = $places;
                $places = [];
            }
        }

        if (count($places) > 0) {
            $parts[] = $places;
        }

        foreach ($parts as $places) {
            City::upsert($places, ['external_id'], ['name', 'name_eng', 'zone', 'koatuu', 'mtibuCode', 'ewaId', 'region_id', 'region_name']);
        }


        print 'Update successfully';
    }
}
