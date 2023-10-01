<?php

namespace App\Services\api;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Turbosms
{
    private function request(string $uri, array $params, $get = false): array
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = env('TURBOSMS_URL') . $uri;

            $client = Http::withToken(env('TURBOSMS_TOKEN'))
                ->timeout(10)
                ->withBody($json, 'application/json; charset=UTF-8');

            if ($get) {
                $response = $client->get($requestUrl);
            } else {
                $response = $client->post($requestUrl);
            }

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                $getPost = ($get) ? 'GET' : 'POST';
                Log::info('Turbosms request() code: ' . $code . '. URL: ' . $getPost . ' ' . $requestUrl);
                Log::info($json);
                Log::info($body);
            }
            Log::info($json);
            Log::info($body);

            return json_decode($body, true);
        }catch (RequestException $e){
            return [];
        }
    }

    public function messageSend($phone, $message)
    {
        $params = [
            'recipients' => [$phone],
            'sender' => env('TURBOSMS_SENDER'),
            'text' => (string) $message,
            'sms' => [
                'sender' => env('TURBOSMS_SENDER'),
                'text' => (string) $message,
            ]
        ];

        return $this->request('/message/send.json', $params);
    }
}
