<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmSmsRequest;
use App\Http\Requests\PromocodeRequest;
use App\Models\Order;
use App\Models\Promocode;
use App\Services\api\OneC;
use App\Services\LiqPay;
use App\Services\OrderService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponser;


    public function show(string $uuid): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();

        return $this->sendResponse($order);
    }

    public function sendSms($uuid): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();

        if ($order->order_type === Order::ORDER_TYPE_GC) {
            if ( ! is_null($order->contract) && ! is_null($order->contract->external_id)) {
                $result = (new OneC())->sendOTP($order->id, $order->contract->external_id);

                if (isset($result['Result']) && $result['Result']) {
                    return $this->sendSuccess();
                } else {
                    return $this->sendResponse(
                        [
                            'status' => $result['Result'] ?? false,
                            'message' => $result['Message'] ?? null
                        ]
                    );
                }
            }
        }

        return $this->sendError("По этому полису отправка СМС невозможна");
    }

    public function confirmSms(ConfirmSmsRequest $request, $uuid): JsonResponse
    {
        $data = $request->validated();
        $count = Order::where('uuid', $uuid)->where('send_sms', $data['code'])->count();

        if ($count > 0) {
            return $this->sendSuccess();
        }

        return $this->sendError("Неправильный код СМС");
    }


    public function liqPayStatus(Request $request)
    {
        $liqpay = new LiqPay();

        $signature = $liqpay->str_to_sign($request->post('data'));

        if ($signature === $request->post('signature')) {
            $data = $liqpay->decode_params($request->post('data'));

            $orderId = explode('_', $data['order_id']);
            $orderUuid = $orderId[1];

            $order = Order::where('uuid', $orderUuid)->firstOrFail();

            $order->payment_status = $data['status'];
            $order->save();

            (new OrderService($order))->actionsAfterPayment();
        }
    }

    public function liqPayResult(Request $request)
    {
        //
    }

    public function promocode(PromocodeRequest $request)
    {
        $promocode = Promocode::where('code', $request->get('promocode'))
            ->active($request->get('polis_type'))
            ->firstOrFail();

        return $this->sendResponse([
            'code' => $promocode->code,
            'type' => $promocode->type,
            'discount' => $promocode->discount,
            'expired_at' => $promocode->expired_at
        ]);
    }
}
