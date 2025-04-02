<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\v2\GreenCardCalculateRequest;
use App\Http\Requests\v2\GreenCardDraftRequest;
use App\Http\Requests\v2\GreenCardSaveRequest;
use App\Models\Order;
use App\Services\api\Ingo;
use App\Services\api\TasIns;
use App\Services\AssistMeService;
use App\Services\GreenCardService;
use App\Services\OrderService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class GreenCardController extends Controller
{
    use ApiResponser;

    public function calculate(GreenCardCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $calculate = (new Ingo())->greenCardCalculate($data);

        $prices = [];

        if (!empty($calculate['data'])) {
            $priceGos = $price = round($calculate['data']['amount'], 2);
            if (!empty(env('DISCOUNT_INGO')) && env('DISCOUNT_INGO') > 0) {
                $price = round($price - ($price / 100 * env('DISCOUNT_INGO')) , 0);
            }
            $prices[] = [
                'insurance_company' => Ingo::API_NAME,
                'cashback_amount' => (new GreenCardService())->getCashback($data['trip_duration'], $data['trip_country'], $request['transport']['transport_category_id'], Ingo::API_NAME),
                'price' => $price,
                'price_gos' => $priceGos,
            ];
        }

        $calculate = (new TasIns())->greenCardCalculate($data);

        if (!empty($calculate) && $calculate['result']) {
            $priceGos = $price = round($calculate['InsPremium'], 2);
            $discount = !empty(env('DISCOUNT_TAS')) && env('DISCOUNT_TAS') > 0 ? env('DISCOUNT_TAS') : null;

            if ($data['trip_duration'] == 12 || $data['trip_duration'] == 3 || $data['trip_duration'] == 1) {
                $discount = !empty(env('DISCOUNT_TAS12')) && env('DISCOUNT_TAS12') > 0 ? env('DISCOUNT_TAS12') : null;
            }

            if (! is_null($discount)) {
                $price = round($price - ($price / 100 * $discount) , 0);
            }

            $prices[] = [
                'insurance_company' => TasIns::API_NAME,
                'cashback_amount' => (new GreenCardService())->getCashback($data['trip_duration'], $data['trip_country'], $request['transport']['transport_category_id'], TasIns::API_NAME),
                'price' => $price,
                'price_gos' => $priceGos,
            ];
        }

        $assist = ( ! empty($request['with_assist_me']) && $request['with_assist_me'])
            ? (new AssistMeService())->getPrice($request['transport']['transport_category_id'], $request['trip_duration'])
            : null;

        return $this->sendResponse([
            'prices' => $prices,
            'assist_me_price' => $assist->price ?? null,
            'assist_me_old_price' => $assist->old_price ?? null
        ]);
    }

    public function store(GreenCardSaveRequest $request): JsonResponse
    {
        try {
            $saveOrder = (new GreenCardService())->saveOrderV2($request);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($saveOrder, 201);
    }

    public function draft(GreenCardDraftRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $data['draft'] = true;

            $order = (new OrderService(null))->saveOrder($data, Order::ORDER_TYPE_GC);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order, 200);
    }
}
