<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmSmsRequest;
use App\Http\Requests\PromocodeRequest;
use App\Mail\AssistMe;
use App\Models\Order;
use App\Models\Promocode;
use App\Services\api\OneC;
use App\Services\api\Salamandra;
use App\Services\LiqPay;
use App\Services\OrderService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    use ApiResponser;


    public function show(string $uuid): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();

        //$preview = (new Salamandra())->releaseStatus($order);

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

        return $this->sendError("За цим полісом відправка СМС неможлива");
    }

    public function confirmSms(ConfirmSmsRequest $request, $uuid): JsonResponse
    {
        $data = $request->validated();
        $order = Order::where('uuid', $uuid)->where('send_sms', $data['code'])->first();

        if (!is_null($order)) {
            $order->confirm_sms = true;
            $order->save();

            return $this->sendSuccess();
        }

        return $this->sendError("Введений код не вірний. Перевірте та спробуйте ще раз. Це дозволить отримати поліс автоматично. Або перейдіть до сплати без коду, менеджер зв'яжеться одразу після оплати та сформує поліс в ручну");
    }


    public function liqPayStatus(Request $request)
    {
        $liqpay = new LiqPay(env('LIQPAY_PUPLIC_KEY'), env('LIQPAY_PRIVATE_KEY'));

        $signature = $liqpay->str_to_sign($request->post('data'));

        Log::debug('Liqpay status request', $request->all());
        Log::debug('Liqpay signature ' . $signature);

        $data = $liqpay->decode_params($request->post('data'));
        Log::debug('Liqpay data', $data);

        $orderId = explode('_', $data['order_id']);
        $orderUuid = $orderId[1];

        $order = Order::where('id', $orderUuid)->firstOrFail();

        $order->payment_status = $data['status'];
        $order->save();

        Log::debug('Liqpay order ID '. $orderUuid . ' Status: ' . $data['status']);

        (new OrderService($order))->actionsAfterPayment();
    }

    public function liqPayResult(string $uuid)
    {
        $liqpay = new LiqPay(env('LIQPAY_PUPLIC_KEY'), env('LIQPAY_PRIVATE_KEY'));

        $order = Order::where('uuid', $uuid)->firstOrFail();

        $data = $liqpay->api("request", array(
            'action'        => 'status',
            'version'       => '3',
            'order_id'      => $order->payment_id
        ));

        if ($data->status !== 'error' && $order->payment_status === 'invoice_wait') {
            $order->payment_status = $data->status;
            $order->save();

            (new OrderService($order))->actionsAfterPayment();
        }

        return redirect(env('LIQPAY_REDIRECT_URL') . '?order=' . $uuid);
    }

    public function liqPayStatusAssist(Request $request)
    {
        $liqpay = new LiqPay(env('LIQPAY_ASSIST_PUPLIC_KEY'), env('LIQPAY_ASSIST_PRIVATE_KEY'));

        $signature = $liqpay->str_to_sign($request->post('data'));

        Log::debug('Liqpay assist status request', $request->all());
        Log::debug('Liqpay assist signature ' . $signature);

        $data = $liqpay->decode_params($request->post('data'));
        Log::debug('Liqpay assist data', $data);

        $orderId = explode('_', $data['order_id']);
        $orderUuid = $orderId[1];

        $order = Order::where('id', $orderUuid)->firstOrFail();

        $contract = [
            'payment_status' => $data['status']
        ];

        (new OrderService($order))->saveAssistMe($contract);

        if ($data['status'] === 'success' && $order->assist->payment_status === 'invoice_wait') {
            Mail::to($order->email)->bcc(env('MAIL_OFFICE'))->send(new AssistMe($order));
        }

        Log::debug('Liqpay assist order ID '. $orderUuid . ' Status: ' . $data['status']);

        (new OrderService($order))->actionsAfterPayment();
    }

    public function liqPayResultAssist(string $uuid)
    {
        $liqpay = new LiqPay(env('LIQPAY_ASSIST_PUPLIC_KEY'), env('LIQPAY_ASSIST_PRIVATE_KEY'));

        $order = Order::where('uuid', $uuid)->firstOrFail();

        $data = $liqpay->api("request", array(
            'action'        => 'status',
            'version'       => '3',
            'order_id'      => $order->assist->payment_id
        ));

        Log::debug('Liqpay assist result order ID '. $order->id . ' Status: ' . $data->status);

        if ($data->status !== 'error' && $order->assist->payment_status === 'invoice_wait') {
            $contract = [
                'payment_status' => $data->status
            ];

            (new OrderService($order))->saveAssistMe($contract);

            (new OrderService($order))->actionsAfterPayment();

            if ($data->status === 'success') {
                Mail::to($order->email)->bcc(env('MAIL_OFFICE'))->send(new AssistMe($order));
            }
        }

        return redirect(env('LIQPAY_REDIRECT_URL') . '?order=' . $uuid . '&assist_pay=1');
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

    public function getFile(string $uuid, string $filename)
    {
        Order::where('uuid', $uuid)->firstOrFail();

        if (Storage::disk('public')->exists('/policies/' . $filename)) {
            return Storage::disk('public')->download('/policies/' . $filename, $filename);
        }

        return $this->sendError("File " . $filename . " not found", 404);
    }
}
