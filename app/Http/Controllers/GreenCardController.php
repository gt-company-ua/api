<?php

namespace App\Http\Controllers;

use App\Http\Requests\GreenCardCalculateRequest;
use App\Http\Requests\GreenCardSaveRequest;
use App\Services\api\Ingo;
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
        $calculate = (new Ingo())->greenCardCalculate($data);

        if (empty($calculate['data'])) {
            return $this->sendError("Нажаль не вдалося порахувати вартість полісу. Спробуйте пізніше");
        }

        //$priceGos = (new GreenCardService())->calculate($data, true);
        $assist = ( ! empty($request['with_assist_me']) && $request['with_assist_me'])
            ? (new AssistMeService())->getPrice($request['transport']['transport_category_id'], $request['trip_duration'])
            : null;

        $amount = round($calculate['data']['amount'], 2);

        return $this->sendResponse([
            'price' => $amount,
            'price_gos' => $amount,
            'cashback_amount' => (new GreenCardService())->getCashback($data['trip_duration']),
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
