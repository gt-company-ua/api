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
        $brands = (new Ingo())->transportBrands();

        foreach ($brands as $brand) {
            CarMark::updateOrCreate(
                ['external_id' => $brand['id']],
                ['name' => $brand['title']]
            );
        }
    }

    private function updateModels()
    {
        $marks = CarMark::whereNotNull('external_id')->get();

        foreach ($marks as $mark) {
            $models = (new Ingo())->transportModels($mark->external_id);
            foreach ($models as $model) {
                CarModel::updateOrCreate(
                    ['external_id' => $model['id']],
                    ['name' => $model['title'], 'car_mark_id' => $mark->id]
                );
            }
        }

    }
}
