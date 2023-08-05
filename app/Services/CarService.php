<?php

namespace App\Services;

use App\Models\CarMark;
use App\Models\CarModel;
use App\Services\api\Ingo;

class CarService
{
    public function updateCars()
    {
        $this->updateBrands();
        $this->updateModels();
    }
    private function updateBrands()
    {
        $brands = (new Ingo())->carBrands();

        foreach ($brands as $brand) {
            CarMark::updateOrCreate(
                ['external_id' => $brand['DMarkID']],
                ['name' => $brand['Name']]
            );
        }
    }

    private function updateModels()
    {
        $models = (new Ingo())->carModels();

        $marks = CarMark::whereNotNull('external_id')->get()->pluck('id', 'external_id')->toArray();

        foreach ($models as $model) {
            if ( ! isset($marks[$model['DMarkID']])) continue;

            CarModel::updateOrCreate(
                ['external_id' => $model['DModelID'], 'car_mark_id' => $marks[$model['DMarkID']]],
                ['name' => $model['Name']]
            );
        }
    }
}
