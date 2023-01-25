<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use App\Models\Order;
use App\Models\OrderContract;
use App\Models\OrderFile;
use App\Models\OrderInsurant;
use App\Models\OrderTourist;
use App\Models\OrderTransport;
use App\Models\Promocode;
use App\Services\api\GoogleAnalytics;
use App\Services\api\OneC;
use App\Services\api\Profitsoft;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    private $order;

    const PAYMENT_STATUS_OK = 'success';

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

        $promocode = $request['promocode'] ?? null;

        unset($request['transport'], $request['insurant'], $request['files'], $request['tourists'], $request['promocode']);

        $request['order_type'] = $orderType;
        $request['full_price'] = $request['price'];

        $request['price'] = $this->usePromocode($promocode, $request['price'], $orderType);
        $request['dont_call'] = (isset($request['dont_call']) && $request['dont_call']);

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
                Log::error('Upload error:' . $e->getMessage());
                $order->delete();

                return null;
            }
        }

        $this->order = $order;

        $this->savePromocode($promocode, $orderType);

        $this->createInvoice();

        (new CrmService($this->order))->sendCrm();

        return $order->load(['transport', 'insurant', 'contract', 'files', 'tourists'])->refresh();
    }

    public function createInvoice(): string
    {
        $price = $this->order->price + $this->order->gc_plus_price;

        if ($price <= 0) {
            return '';
        }

        $orderUid = $this->order->order_type . '_' . $this->order->id . '_' . Str::random(3);

        $invoiceParams = [
            'action'       => 'invoice_send',
            'version'      => '3',
            'email'        => $this->order->email,
            'amount'       => $price,
            'currency'     => 'UAH',
            'server_url'   => route('orders.liqpay.status', ['order' => $this->order->uuid]),
            'result_url'   => route('orders.liqpay.result', ['order' => $this->order->uuid]),
            'order_id'     => $orderUid,
            'expired_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'description'  => $this->getInsurantFullName() . ', ІПН: '
                . $this->order->insurant->inn,
        ];

        $sendInvoice = (new LiqPay())->api('request', $invoiceParams);

        if (isset($sendInvoice->status)) {
            $this->order->payment_type = 'liqpay';
            $this->order->payment_status = $sendInvoice->status;
            $this->order->payment_url = $sendInvoice->href;
            $this->order->payment_id = $sendInvoice->order_id;
            $this->order->ga_id = (new GoogleAnalytics())->getGaId();
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
                $path = '/' . $order->order_type . '/' . $order->id;
                $localPath = public_path('storage') . $path;
                $ext = $file->getClientOriginalExtension();
                $randName = Str::uuid() . '.' . $ext;

                try {
                    $file->move($localPath, $randName);
                } catch (\Exception $e) {
                    throw new FileUploadException("Ошибка при загрузке файла " . $name . ' Error: '. $e->getMessage());
                    return;
                }

                $saveFiles[] = new OrderFile([
                    'name' => $name,
                    'path' => $path .'/'. $randName,
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

        $normalDate = date('Y-m-d', strtotime('01/01/1900 + ' . $inn . ' days - 1 days'));

        $result['birth'] = $normalDate;

        list($result['year'], $result['month'], $result['day']) = explode('-', $normalDate);

        return $result;
    }

    public function actionsAfterPayment()
    {
        if ($this->order->payment_status != self::PAYMENT_STATUS_OK) {
            return;
        }

        (new CrmService($this->order))->updateDeal();

        (new GoogleAnalytics())->transaction($this->order);

        if ($this->order->type === Order::ORDER_TYPE_GC) {
            $this->saveGreenCard1C();
        } else if ($this->order->type === Order::ORDER_TYPE_OSAGO) {
            $this->saveOsago1C();
        }
    }

    private function saveOsago1C()
    {
        $save1c = (new Profitsoft())->confirm($this->order);
    }

    private function saveGreenCard1C()
    {
        $save1c = (new OneC())->saveGreenCard($this->order);

        if ( ! empty($save1c['Number'])) {
            $filename = (new OneC())->getPrintForm(
                $this->order->id, $save1c['Number']
            );
            if ( ! empty($filename)) {
                $filePath = storage_path('app/public/greencard')
                    . DIRECTORY_SEPARATOR . $filename;

                /*$this->sendEmailPolicy(
                    $save1c['Number'], $filePath, $order['email']
                );*/ //TODO Сделать отправку полиса клиенту
            }
        }
    }

    public function usePromocode(?string $code, float $basePrice, string $orderType)
    {
        if (empty($code)) {
            return $basePrice;
        }

        $promocode = Promocode::where('code', $code)->active($orderType)->first();

        if ( ! is_null($promocode)
            && (is_null($promocode->max_uses)
                || $promocode->max_uses > $promocode->used)
        ) {
            if ($promocode->type === 'percent') {
                $discount = $basePrice / 100 * $promocode->discount;
            } else {
                $discount = $basePrice - $promocode->discount;
            }

            $basePrice -= $discount;
        }

        return ceil($basePrice);
    }

    private function savePromocode(?string $code, string $orderType)
    {
        if (empty($this->order) || empty($code)) {
            return;
        }

        $promocode = Promocode::where('code', $code)->active($orderType)->first();

        $this->order->promocode_id = $promocode->id ?? null;
    }
}