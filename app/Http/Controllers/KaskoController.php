<?php

namespace App\Http\Controllers;

use App\Http\Requests\KaskoCalculateRequest;
use App\Http\Requests\KaskoSaveRequest;
use App\Models\Order;
use App\Services\KaskoService;
use App\Services\OrderService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class KaskoController extends Controller
{
    use ApiResponser;

    public function store(KaskoSaveRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['price'] = (new KaskoService())->calculate($data, false);

        $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_KASKO);

        return $this->sendResponse($order, 201);
    }

    public function calculate(KaskoCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $calculate = (new KaskoService())->calculate($data);

        return $this->sendResponse(['price' => $calculate]);
    }
}
