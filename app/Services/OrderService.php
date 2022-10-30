<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use App\Models\Order;
use App\Models\OrderContract;
use App\Models\OrderFile;
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

    public function saveContract(array $contract)
    {
        if (is_null($this->order->contract)) {
            $contract = new OrderContract($contract);

            $this->order->contract()->save($contract);
        } else {
            $this->order->contract()->update($contract);
        }
    }

    public function saveOrder(array $request, string $orderType): ?Order
    {
        $transport = new OrderTransport($request['transport'] ?? []);
        $insurant = new OrderInsurant($request['insurant'] ?? []);

        if (!empty($request['files'])) {
            $files = $request['files'];
        }

        unset($request['transport'], $request['insurant'], $request['files']);

        $request['order_type'] = $orderType;

        $order = Order::create($request);

        $order->transport()->save($transport);
        $order->insurant()->save($insurant);

        if (isset($files)) {
            try {
                $this->uploadFiles($order, $files);
            } catch (\Exception $e) {
                $order->delete();

                return null;
            }
        }

        return $order->load(['transport', 'insurant', 'contract', 'files'])->refresh();
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

    /**
     * @throws FileUploadException
     */
    private function uploadFiles(Order $order, $files)
    {
        if (isset($files)) {
            $saveFiles = [];

            foreach($files as $file) {
                $name = $file->getClientOriginalName();
                $path = '/osago/' . $order->id . '/';
                $localPath = public_path('storage') . $path;
                $ext = $file->getClientOriginalExtension();
                $randName = Str::uuid() . '.' . $ext;

                try {
                    $file->move($localPath, $randName);
                } catch (\Exception $e) {
                    throw new FileUploadException("Ошибка при загрузке файла " . $name);
                    return;
                }

                $saveFiles[] = new OrderFile([
                    'name' => $name,
                    'path' => $path . $randName,
                    'extension' => $file->getClientOriginalExtension()
                ]);
            }

            if (count($saveFiles) > 0) {
                $order->files()->saveMany($saveFiles);
            }
        }
    }
}