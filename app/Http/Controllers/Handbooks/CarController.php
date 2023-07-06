<?php

namespace App\Http\Controllers\Handbooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Handbooks\CarMarkRequest;
use App\Http\Requests\Handbooks\CarModelRequest;
use App\Http\Requests\Handbooks\FindVehicleRequest;
use App\Models\CarMark;
use App\Models\CarModel;
use App\Services\api\OneC;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarController extends Controller
{
    use ApiResponser;

    public function marks(CarMarkRequest $request): JsonResponse
    {
        $filter = $request->validated();

        $marks = CarMark::orderBy('name');

        if (!empty($filter['search'])) {
            $marks->where('name', 'like', $filter['search'] . '%');
        }

        return $this->sendResponse($marks->get());
    }

    public function models($carMarkId, CarModelRequest $request): JsonResponse
    {
        $filter = $request->validated();

        $marks = CarModel::where('car_mark_id', $carMarkId);

        if (!empty($filter['search'])) {
            $marks->where('name', 'like', $filter['search'] . '%');
        }

        return $this->sendResponse($marks->get());
    }

    public function findVehicle(FindVehicleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $search = (new OneC())->findVehicle($data['search']);

        return $this->sendResponse($search);
    }
}
