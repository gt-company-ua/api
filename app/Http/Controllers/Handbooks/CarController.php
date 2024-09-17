<?php

namespace App\Http\Controllers\Handbooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Handbooks\CarMarkRequest;
use App\Http\Requests\Handbooks\CarModelRequest;
use App\Http\Requests\Handbooks\FindVehicleRequest;
use App\Models\CarMark;
use App\Models\CarModel;
use App\Models\TransportCategory;
use App\Models\TransportPower;
use App\Services\api\Ingo;
use App\Services\api\OneC;
use App\Services\api\Opendatabot;
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

    public function findVehicleIngo(FindVehicleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $search = (new Ingo())->findCarByNum($data['search']);

        if (!empty($search['brand_code'])) {
            $carMark = CarMark::where('external_id', $search['brand_code'])->first();
            $search['car_mark_id'] = $carMark->id ?? null;
        }

        if (!empty($search['model_code'])) {
            $carModel = CarModel::where('external_id', $search['model_code'])->first();
            $search['car_model_id'] = $carModel->id ?? null;
        }

        return $this->sendResponse($search);
    }

    public function findVehicleOpendatabot(FindVehicleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $search = (new Opendatabot())->transport($data['search']);

        if (!empty($search['brand'])) {
            $carMark = CarMark::where('name', $search['brand'])->first();
            $search['car_mark_id'] = $carMark->id ?? null;

            if (!empty($search['model']) && !empty($search['car_mark_id'])) {
                $carModel = CarModel::where('car_mark_id', $search['car_mark_id'])->where('name', $search['model'])->first();
                $search['car_model_id'] = $carModel->id ?? null;
            }
        }

        if (!empty($search['kind'])) {
            $transportCategory = TransportCategory::where('kind', 'like', '%' . mb_substr($search['kind'], 0, 6) . '%')->first();
            $search['transport_category_id'] = $transportCategory->id ?? null;

            if (!empty($search['capacity']) && !is_null($transportCategory)) {
                $transportPower = TransportPower::where('transport_category_id', $transportCategory->id)->where('capacity', '<=', $search['capacity'])->whereNotNull('capacity')->orderByDesc('capacity')->first();
                $search['transport_power_id'] = $transportPower->id ?? null;
            }
        }

        return $this->sendResponse($search);
    }
}
