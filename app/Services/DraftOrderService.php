<?php

namespace App\Services;

use App\Models\Order;

class DraftOrderService
{
    public function sendDraftOrders()
    {
        $orders = Order::where('draft', true)->where('draft_sent', false)->where('updated_at', '<=', date('Y-m-d H:i:s', strtotime('-20 minutes')));

        foreach ($orders as $order) {
            $order->draft_sent = true;
            $order->save();

            (new CrmService($order))->sendCrm();
        }

        print 'Draft sent ok';
    }
}
