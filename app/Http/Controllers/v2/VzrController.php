<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Draft\VzrDraftRequest;
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
        $error = null;

        foreach (Ingo::VZR_TARIFFS as $tariff) {
            $calculate = (new Ingo())->vzrCalculate($data, $tariff);

            if (isset($calculate['data']['amount'])) {
                $tariffs[$tariff] = round($calculate['data']['amount'], 2);
            } else if (isset($calculate['message'])) {
                $error = $calculate['message'];
            }
        }

        if (count($tariffs) === 0 && ! is_null($error)) {
            return $this->sendError($error, 422);
        }

        return $this->sendResponse($tariffs);
    }


    public function store(VzrSaveRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $calculate = (new Ingo())->vzrCalculate($data, $data['tariff']);
            $data['price'] = 0;

            if (isset($calculate['data']['amount'])) {
                $data['price'] = $calculate['data']['amount'];
            } else if (isset($calculate['message'])) {
                return $this->sendError($calculate['message'], 422);
            }

            $data['status_contract'] = OrderContract::STATUS_CONTRACT_NOT_SENT;

            $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_VZR);

            (new Ingo())->vzrDraft($order);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order, 201);
    }

    public function draft(VzrDraftRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $data['draft'] = true;

            $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_VZR);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order, 200);
    }

    public function territories(): JsonResponse
    {
        return $this->sendResponse(Ingo::TERRITORIES);
    }

    public function documents(): JsonResponse
    {
        return $this->sendResponse(Ingo::DOC_TYPES);
    }

    public function goals(): JsonResponse
    {
        return $this->sendResponse(Ingo::GOALS);
    }
}
