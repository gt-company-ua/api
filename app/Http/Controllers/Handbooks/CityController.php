<?php

namespace App\Http\Controllers\Handbooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Handbooks\SearchCityRequest;
use App\Models\City;
use App\Services\api\Profitsoft;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    use ApiResponser;

    public function searchMtsbu(SearchCityRequest $request): JsonResponse
    {
        $data = $request->validated();

        $cities = (new Profitsoft())->searchCity($data['search']);

        return $this->sendResponse($cities);
    }

    public function searchLocal(SearchCityRequest $request): JsonResponse
    {
        $data = $request->validated();

        $cities = City::where('name', 'like', $data['search'] . '%')->orderBy('zone')->orderBy('name')->get();

        return $this->sendResponse($cities);
    }
}
