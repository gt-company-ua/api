<?php

namespace App\Services;

use App\Models\City;
use App\Models\OsagoCity;
use App\Services\api\Ingo;
use App\Services\api\Salamandra;

class CitiesUpdateService
{
    public function salamandra()
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

    public function ingo()
    {
        try {
            $cities = (new Ingo())->getCities();
        } catch (\Exception $e) {
            print $e->getMessage();
        }

        $places = [];
        $parts = [];

        foreach ($cities as $city) {
            if (empty($city['Name'])) continue;

            $places[] = [
                'external_id' => $city['DMREOCityID'],
                'name' => $city['Name'],
                'zone' => $city['ZoneCode'] ?? null,
                'koatuu' => $city['KOATUU'] ?? null,
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
            OsagoCity::upsert($places, ['external_id'], ['name', 'zone', 'koatuu']);
        }

        print 'Update successfully';
    }
}
