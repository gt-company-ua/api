<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\v2\OsagoCalculateRequest;
use App\Http\Requests\v2\OsagoDraftRequest;
use App\Http\Requests\v2\OsagoSaveRequest;
use App\Models\Order;
use App\Services\api\Ingo;
use App\Services\api\TasIns;
use App\Services\OrderService;
use App\Services\OsagoService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class OsagoController extends Controller
{
    use ApiResponser;
    public function calculate(OsagoCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $prices = [];

        $calculate = (new Ingo())->osagoCalculate($data);
        if (isset($calculate['data']['amount'])) {
            $prices[] = [
                'insurance_company' => Ingo::API_NAME,
                'total' => round($calculate['data']['amount'], 2),
                'amount' => round($calculate['data']['amount'], 2),
                'cashback' => $calculate['cashback'] ?? null,
            ];
        }

        $calculate = (new TasIns())->osagoCalculate($data);
        if (!empty($calculate) && $calculate['result']) {
            $price = round($calculate['InsPremium'], 2);
            $prices[] = [
                'insurance_company' => TasIns::API_NAME,
                'total' => $price,
                'amount' => $price,
                'cashback' => $calculate['cashback'] ?? null,
            ];
        }

        return $this->sendResponse($prices);
    }

    public function store(OsagoSaveRequest $request): JsonResponse
    {
        try {
            $saveOrder = (new OsagoService())->saveOrderV2($request);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($saveOrder);
    }
    public function draft(OsagoDraftRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $data['draft'] = true;

            $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_OSAGO);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order);
    }
}
