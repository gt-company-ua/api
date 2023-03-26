<?php

namespace App\Http\Controllers\Osago;

use App\Http\Controllers\Controller;
use App\Http\Requests\Osago\SalamandraCalculateRequest;
use App\Http\Requests\Osago\SalamandraTariffsRequest;
use App\Services\api\Salamandra;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalamandraController extends Controller
{
    use ApiResponser;

    public function calculate(SalamandraCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $calculate = (new Salamandra())->calculate($data);

        $code = count($calculate) > 0 ? 200 : 422;

        return $this->sendResponse($calculate, $code);
    }

    public function tariffs(SalamandraTariffsRequest $request): JsonResponse
    {
        $data = $request->validated();

        $calculate = (new Salamandra())->calculate($data, true);

        $code = count($calculate) > 0 ? 200 : 422;

        return $this->sendResponse($calculate, $code);
    }
}
