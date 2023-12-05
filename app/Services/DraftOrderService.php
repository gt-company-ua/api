<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class DraftOrderService
{
    public function sendDraftOrders()
    {
        $orders = Order::where('draft', true)
            ->whereNotNull('email')
            ->where('draft_sent', false)
            ->where('updated_at', '<=', date('Y-m-d H:i:s', strtotime('-5 minutes')))->get();

        foreach ($orders as $order) {
            $order->draft_sent = true;
            $order->save();

            if (empty($order->email) || is_null($order->insurant) || empty($order->insurant->phone)) {
                continue;
            }

            (new CrmService($order))->sendCrm();
        }

        print 'Draft sent ok';
    }
}
