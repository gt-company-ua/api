<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use App\Models\Order;
use App\Models\OrderContract;
use App\Models\OrderFile;
use App\Models\OrderInsurant;
use App\Models\OrderTourist;
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
        $tourists = $request['tourists'] ?? [];

        if (!empty($request['files'])) {
            $files = $request['files'];
        }

        unset($request['transport'], $request['insurant'], $request['files'], $request['tourists']);

        $request['order_type'] = $orderType;

        $order = Order::create($request);

        $order->transport()->save($transport);
        $order->insurant()->save($insurant);

        if (count($tourists) > 0) {
            $saveTourists = [];
            foreach ($tourists as $tourist) {
                $saveTourists[] = new OrderTourist($tourist);
            }

            $order->tourists()->saveMany($saveTourists);
        }


        if (isset($files)) {
            try {
                $this->uploadFiles($order, $files);
            } catch (\Exception $e) {
                $order->delete();

                return null;
            }
        }

        return $order->load(['transport', 'insurant', 'contract', 'files', 'tourists'])->refresh();
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
                $path = '/' . $order->order_type . '/' . $order->id . '/';
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

    public function parseInn($inn): array
    {
        if (empty($inn) || !preg_match('/^\d{10}$/',$inn)) return ['status' => false];

        $result = [];
        $result['inn'] = $inn;
        $result['sex'] = (substr($inn, 8, 1) % 2) ? 'Male' : 'Female';

        $split = str_split($inn);

        $summ = $split[0]*(-1) + $split[1]*5 + $split[2]*7 + $split[3]*9 + $split[4]*4 + $split[5]*6 + $split[6]*10 + $split[7]*5 + $split[8]*7;

        $result['control'] = (int)($summ - (11 * (int)($summ/11)));

        if ($result['control'] == 10){
            $result['control'] = 0;
        }

        $result['status'] = $result['control'] == (int)$split[9];

        $inn = substr($inn, 0, 5);

        $normal_date = date('d.m.Y', strtotime('01/01/1900 + ' . $inn . ' days - 1 days'));

        $result['birth'] = $normal_date;

        list($result['day'], $result['month'], $result['year']) = explode('.', $normal_date);

        return $result;
    }
}