<?php

namespace App\Services;

use App\Models\AssistMePrice;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssistMeService
{
    public function create(Order $order)
    {
        $assistMePrice = $this->getPrice($order->transport->transport_category_id, $order->trip_duration);

        if (is_null($assistMePrice)) {
            return;
        }

        $contract = [
            'number' => date('ym') . '-' . $order->id,
            'price' => $assistMePrice->price,
            'payment_status' => 'invoice_wait'
        ];

        (new OrderService($order))->saveAssistMe($contract);

        //$this->createInvoice($order);
    }

    public function getPrice($transportCategoryId, $tripDuration)
    {
        return AssistMePrice::where('transport_category_id', $transportCategoryId)
            ->where('trip_duration', $tripDuration)
            ->first();
    }

    public function createInvoice(Order $order)
    {
        $order = $order->load(['assist'])->refresh();

        $price = $order->assist->price;

        if ($price <= 0) {
            return;
        }

        $orderUid = 'assist_' . $order->id . '_' . Str::random(3);

        $invoiceParams = [
            'action'       => 'invoice_send',
            'version'      => '3',
            'email'        => $order->email,
            'amount'       => $price,
            'currency'     => 'UAH',
            'server_url'   => route('orders.liqpay.status.assist', ['order' => $order->uuid]),
            'result_url'   => route('orders.liqpay.result.assist', ['order' => $order->uuid]),
            'order_id'     => $orderUid,
            'expired_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'description'  => (new OrderService($order))->getInsurantFullName() . ', ІПН: '
                . $order->insurant->inn,
        ];

        $sendInvoice = (new LiqPay(env('LIQPAY_ASSIST_PUPLIC_KEY'), env('LIQPAY_ASSIST_PRIVATE_KEY')))->api('request', $invoiceParams);

        if (isset($sendInvoice->status)) {
            $contract = [
                'payment_type' => 'liqpay',
                'payment_status' => $sendInvoice->status,
                'payment_url' => $sendInvoice->href,
                'payment_id' => $sendInvoice->order_id,
            ];

            (new OrderService($order))->saveAssistMe($contract);
        } else {
            Log::error("Liqpay assist payment failed (order: ".$order->id."): ", (array)$sendInvoice);
        }
    }
}
