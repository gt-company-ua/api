<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use App\Mail\OrderCreated;
use App\Mail\OrderPayment;
use App\Models\Order;
use App\Models\OrderAssistMeContract;
use App\Models\OrderContract;
use App\Models\OrderFile;
use App\Models\OrderInsurant;
use App\Models\OrderTourist;
use App\Models\OrderTransport;
use App\Models\Promocode;
use App\Services\api\GoogleAnalytics;
use App\Services\api\Ingo;
use App\Services\api\OneC;
use App\Services\api\Profitsoft;
use App\Services\api\Salamandra;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public function saveAssistMe(array $contract)
    {
        if (is_null($this->order->assist)) {
            $contract = new OrderAssistMeContract($contract);

            $this->order->assist()->save($contract);
        } else {
            $this->order->assist()->update($contract);
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
        $assistMe = $request['with_assist_me'] ?? false;

        unset($request['transport'], $request['insurant'], $request['files'], $request['tourists'], $request['promocode'], $request['with_assist_me']);

        $request['order_type'] = $orderType;
        $request['full_price'] = $request['price'];

        if (empty($request['city_name'])) {
            $request['city_name'] = 'Київ';
        }

        $request['price'] = $this->usePromocode($promocode, $request['price'], $orderType);
        $request['dont_call'] = (isset($request['dont_call']) && $request['dont_call']);

        if (isset($request['territories'])) {
            $request['territory'] = json_encode($request['territories']);

            unset($request['territories']);
        }

        $order = Order::create($request);

        $order->transport()->save($transport);

        if (count($tourists) > 0) {
            $saveTourists = [];
            foreach ($tourists as $tourist) {
                $tourist['birth'] = date('Y-m-d', strtotime($tourist['birth']));

                if (!isset($tourist['full_name']) && isset($tourist['name'])) {
                    $tourist['full_name'] = $tourist['surname'] . ' ' . $tourist['name'];

                    unset($tourist['surname'], $tourist['name']);
                }

                if (is_null($insurant->surname)) {
                    $fullNameParts = explode(' ', $tourist['full_name']);
                    $insurant->surname = $fullNameParts[0] ?? null;
                    $insurant->name = $fullNameParts[1] ?? null;
                    $insurant->patronymic = $fullNameParts[2] ?? null;
                    $insurant->birth = $tourist['birth'];
                    $insurant->doc_number = $tourist['doc_number'] ?? null;
                }

                unset($tourist['range']);
                $saveTourists[] = new OrderTourist($tourist);
            }

            $order->tourists()->saveMany($saveTourists);
        }

        if (! is_null($insurant->birth)) {
            $insurant->birth = date('Y-m-d', strtotime($insurant->birth));
        }

        $order->insurant()->save($insurant);

        if (isset($files)) {
            try {
                $this->uploadFiles($order, $files);
            } catch (\Exception $e) {
                Log::error('Upload error:' . $e->getMessage());
                $order->delete();

                return null;
            }
        }

        if ($assistMe) {
            (new AssistMeService())->create($order);
        }

        $this->order = $order->load(['transport', 'insurant', 'contract', 'files', 'tourists', 'assist'])->refresh();

        $this->savePromocode($promocode, $orderType);

        $this->createInvoice();

        (new CrmService($this->order))->sendCrm();

        try {
            Mail::send(new OrderCreated($this->order));
        } catch (\Exception $e) {
            Log::error("Send mail failed: " . $e->getMessage());
        }

        return $this->order;
    }

    public function createInvoice(): string
    {
        $price = $this->order->price + $this->order->gc_plus_price;

        if ($price <= 0) {
            return '';
        }

        $orderUid = $this->order->order_type . '_' . $this->order->id . '_' . Str::random(3);

        $splitRules = [];

        if (!is_null($this->order->assist) && $this->order->assist->price > 0) {
            $splitRules[] = [
                'public_key' => env('LIQPAY_POLICE_PUBLIC_KEY'),
                'amount' => $this->order->price,
                'commission_payer' => 'receiver',
                'server_url' => route('orders.liqpay.status', ['order' => $this->order->uuid])
            ];

            $price += $this->order->assist->price;

            $splitRules[] = [
                'public_key' => env('LIQPAY_ASSIST_PUPLIC_KEY'),
                'amount' => $this->order->assist->price,
                'commission_payer' => 'receiver',
                'server_url' => route('orders.liqpay.status.assist', ['order' => $this->order->uuid])
            ];
        }

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
                . $this->order->insurant->inn
        ];

        if (count($splitRules) > 0) {
            $invoiceParams['split_rules'] = $splitRules;
        }

        $sendInvoice = (new LiqPay(env('LIQPAY_PUPLIC_KEY'), env('LIQPAY_PRIVATE_KEY')))->api('request', $invoiceParams);

        if (isset($sendInvoice->status)) {
            $this->order->payment_type = 'liqpay';
            $this->order->payment_status = $sendInvoice->status;
            $this->order->payment_url = $sendInvoice->href;
            $this->order->payment_id = $sendInvoice->order_id;
            //$this->order->ga_id = (new GoogleAnalytics())->getGaId();
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

        (new GoogleAnalytics())->transaction($this->order);

        (new CrmService($this->order))->updateDeal();

        if (is_null($this->order->upload_docs) || $this->order->upload_docs === false) {
            if ($this->order->order_type === Order::ORDER_TYPE_GC) {
                $this->saveGreenCard1C();
            } else if ($this->order->order_type === Order::ORDER_TYPE_VZR) {
                $this->saveVzr1C();
            } else if ($this->order->order_type === Order::ORDER_TYPE_OSAGO) {
                $this->saveOsago1C();
            }
        }

        Mail::send(new OrderCreated($this->order));
    }

    private function saveOsago1C()
    {
        if ( ! is_null($this->order->contract)) {
            switch ($this->order->contract->api_name){
                case 'salamandra':
                    (new Salamandra())->release($this->order);
                    break;
                case 'profitsoft':
                    (new Profitsoft())->confirm($this->order);
                    break;
                case Ingo::API_NAME:
                    $response = (new Ingo())->osagoConfirm($this->order);

                    if (! empty($response['data']['id'])) {
                        $files = (new Ingo())->osagoPrintForm($this->order);

                        if (count($files) > 0) {
                            Mail::to($this->order->email)->bcc(env('MAIL_OFFICE'))->send(new OrderPayment($files));
                        }
                    }
                    break;
                default:
                    break;
            }
        }
    }

    public function saveGreenCard1C()
    {
        $this->order = Order::find($this->order->id);

        $count = OrderContract::where('order_id', $this->order->id)->where('state', 'Draft')->count();
        if ($count <= 0) {
            return;
        }

        $ingo = new Ingo();

        $response = $ingo->greenCardConfirm($this->order);

        if (! empty($response['data']) && ! empty($response['data']['id'])) {
            $files = $ingo->greenCardPrintForm($this->order);

            if (count($files) > 0) {
                Mail::to($this->order->email)->bcc(env('MAIL_OFFICE'))->send(new OrderPayment($files));
            }
        }
    }

    public function saveVzr1C()
    {
        $this->order = Order::find($this->order->id);

        $count = OrderContract::where('order_id', $this->order->id)->where('state', 'Draft')->count();

        if ($count <= 0) {
            Log::debug('VZR Draft contract not found, order_id: ' . $this->order->id);

            return;
        }

        $ingo = new Ingo();

        $sent = $ingo->vzrConfirm($this->order);

        if ($sent) {
            $files = $ingo->vzrPrintForm($this->order);

            if (count($files) > 0) {
                Mail::to($this->order->email)->bcc(env('MAIL_OFFICE'))->send(new OrderPayment($files));
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
