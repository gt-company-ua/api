<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderInsurant;
use App\Models\OrderTransport;
use Illuminate\Support\Str;

class OrderService
{
    private $order;

    public function __construct(?Order $order)
    {
        $this->order = $order;
    }

    public function saveOrder(array $request, string $orderType)
    {
        $transport = new OrderTransport($request['transport'] ?? []);
        $insurant = new OrderInsurant($request['insurant'] ?? []);

        unset($request['transport'], $request['insurant']);

        $request['order_type'] = $orderType;

        $order = Order::create($request);

        $order->transport()->save($transport);
        $order->insurant()->save($insurant);

        return $order->load(['transport', 'insurant', 'contract'])->refresh();
    }

    public function createInvoice(): string
    {
        $price = $this->order->price + $this->order->gc_plus_price;

        $order_uid = $this->order->type . '_' . $this->order->uuid . '_' . Str::random(3);

        $invoiceParams = [
            'action'       => 'invoice_send',
            'version'      => '3',
            'email'        => $this->order->email,
            'amount'       => $price,
            'currency'     => 'UAH',
            'server_url'   => route('orders.liqpay.status'),
            'result_url'   => route('orders.liqpay.result'),
            'order_id'     => $order_uid,
            'expired_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'description'  => $this->getInsurantFullName() . ', ІПН: '
                . $this->order->insurant->inn,
        ];

        $sendInvoice = (new LiqPay())->api('request', $invoiceParams);

        if (isset($sendInvoice->status)) {
            $this->order->payment_type = 'liqpay';
            $this->order->payment_status = $sendInvoice->status;
            $this->order->payment_url = $sendInvoice->href;
            $this->order->save();
        }

        return (string) $sendInvoice->href ?? '';
    }

    public function getInsurantFullName(): string
    {
        $fio = [
            $this->order->insurant->surname,
            $this->order->insurant->name,
            $this->order->insurant->patronymic,
        ];

        return implode(' ', $fio);
    }
}