<?php

namespace App\Http\Controllers\Osago;

use App\Http\Controllers\Controller;
use App\Http\Requests\Osago\SalamandraCalculateRequest;
use App\Http\Requests\Osago\SalamandraSaveRequest;
use App\Http\Requests\Osago\SalamandraTariffsRequest;
use App\Services\api\Salamandra;
use App\Services\OsagoService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class SalamandraController extends Controller
{
    use ApiResponser;

    public function store(SalamandraSaveRequest $request): JsonResponse
    {
        try {
            $saveOrder = (new OsagoService())->saveOrderSalamandra($request);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($saveOrder);
    }
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
