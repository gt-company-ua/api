<?php

namespace App\Http\Controllers\handbooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\handbooks\SearchCityRequest;
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
}
