<?php

namespace App\Http\Controllers\Osago;

use App\Http\Controllers\Controller;
use App\Http\Requests\Osago\IngoCalculateRequest;
use App\Http\Requests\Osago\IngoSaveRequest;
use App\Services\api\Ingo;
use App\Services\OsagoService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class IngoController extends Controller
{
    use ApiResponser;

    public function store(IngoSaveRequest $request): JsonResponse
    {
        try {
            $saveOrder = (new OsagoService())->saveOrderIngo($request);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($saveOrder);
    }
    public function calculate(IngoCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $calculate = (new Ingo())->osagoCalculate($data);

        if (!isset($calculate['data']['amount'])) {
            return $this->sendResponse($calculate, 400);
        }

        $prices = [
            'total' => round($calculate['data']['amount'], 2),
            'amount' => round($calculate['data']['amount'], 2),
        ];

        if (isset($calculate['data']['dgo'])) {
            $prices['dgo'] = round($calculate['data']['dgo'], 2);
            $prices['total'] += round($calculate['data']['dgo'], 2);
        }

        return $this->sendResponse($prices);
    }
}
