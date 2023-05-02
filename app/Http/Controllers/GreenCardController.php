<?php

namespace App\Http\Controllers;

use App\Http\Requests\GreenCardCalculateRequest;
use App\Http\Requests\GreenCardSaveRequest;
use App\Services\AssistMeService;
use App\Services\GreenCardService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GreenCardController extends Controller
{
    use ApiResponser;

    public function calculate(GreenCardCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $price = (new GreenCardService())->calculate($data);
        $priceGos = (new GreenCardService())->calculate($data, true);
        $assist = ( ! empty($request['with_assist_me']) && $request['with_assist_me'])
            ? (new AssistMeService())->getPrice($request['transport']['transport_category_id'], $request['trip_duration'])
            : null;

        return $this->sendResponse([
            'price' => $price,
            'price_gos' => $priceGos,
            'cashback_amount' => round($priceGos - $price),
            'assist_me_price' => $assist->price ?? null
        ]);
    }

    public function store(GreenCardSaveRequest $request): JsonResponse
    {
        try {
            $saveOrder = (new GreenCardService())->saveOrder($request);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($saveOrder, 201);
    }

}
