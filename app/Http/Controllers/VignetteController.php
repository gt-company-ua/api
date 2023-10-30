<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vignette\CheckVehiclesRequest;
use App\Http\Requests\Vignette\ProductsRequest;
use App\Http\Requests\Vignette\SaveVignetteRequest;
use App\Models\VignetteOrder;
use App\Services\api\Vignette;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class VignetteController extends Controller
{
    use ApiResponser;
    public function products(ProductsRequest $request): JsonResponse
    {
        $filter = $request->validated();

        $products = (new Vignette())->products($filter['country_id']);

        return $this->sendResponse($products);
    }

    public function save(SaveVignetteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $cars = $data['cars'];

        $checkVehicles = (new Vignette())->checkVehicles($cars);

        if ( ! $checkVehicles) {
            return $this->sendError("Неправильний номер транспорту", 400);
        }

        unset($data['cars']);

        $vignette = VignetteOrder::create($data);

        $vignette->cars()->createMany($cars);

        (new Vignette())->order($vignette);

        return $this->sendResponse($vignette);
    }

    public function checkVehicles(CheckVehiclesRequest $request): JsonResponse
    {
        $data = $request->validated();

        $checkVehicles = (new Vignette())->checkVehicles($data['cars']);

        if ( ! $checkVehicles) {
            return $this->sendResponse(['success' => false], 400);
        }

        return $this->sendSuccess();
    }
}
