<?php

namespace App\Services\api;

use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;

class GoogleAnalytics
{
    private $tracking_id = 'UA-110662271-1';
    private $v = '1';

    private function request($data)
    {

        $data['v'] = $this->v;
        $data['tid'] = $this->tracking_id;

        try{
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,'https://www.google-analytics.com/collect');
            curl_setopt($ch,CURLOPT_HEADER,true);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($data));
            $result = curl_exec($ch);
        }catch (Exception $e){
            Log::error('GA ERROR:' . $e->getMessage());

            $result = false;
        }

        return $result;
    }

    public function transaction(Order $order)
    {
        $price = $order->price + $order->gc_plus_price;

        $send = [
            'cid' => ($order->ga_id != '') ? $this->getGaId($order->ga_id) : 555,
            't' => 'transaction',
            'ti' => $order->uuid,
            'ta' => 'liqpay',
            'tr' => $price,
            'dr' => 'https://greentravel.ua/thankyou',
            'dt' => 'Thank You',
            'cu' => 'UAH',
        ];

        return $this->request($send);
    }

    function getGaId($gaIdFull)
    {
        if($gaIdFull != ''){
            $id = explode('.', $gaIdFull);

            unset($id[0], $id[1]);

            return implode('.', $id);
        }

        return $gaIdFull;
    }
}