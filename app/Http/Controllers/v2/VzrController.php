<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\v2\VzrCalculateRequest;
use App\Http\Requests\v2\VzrSaveRequest;
use App\Models\Order;
use App\Models\OrderContract;
use App\Services\api\Ingo;
use App\Services\OrderService;
use App\Services\VzrService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class VzrController extends Controller
{
    use ApiResponser;

    use ApiResponser;

    public function calculate(VzrCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $tariffs = [];

        foreach (VzrService::INGO_TARIFFS as $tariff) {
            $calculate = (new Ingo())->vzrCalculate($data, $tariff);

            if (isset($calculate['data']['amount'])) {
                $tariffs[$tariff] = round($calculate['data']['amount'], 2);
            }
        }

        return $this->sendResponse($tariffs);
    }


    public function store(VzrSaveRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $calculate = (new Ingo())->vzrCalculate($data, $data['tariff']);

            if (isset($calculate['data']['amount'])) {
                $data['price'] = $calculate['data']['amount'];
            }

            $data['status_contract'] = OrderContract::STATUS_CONTRACT_NOT_SENT;

            $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_VZR);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order, 201);
    }

    public function territories()
    {
        return $this->sendResponse(VzrService::TERRITORIES);
    }
}
