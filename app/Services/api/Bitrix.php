<?php

namespace App\Services\api;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Bitrix
{
    private $apiUrl, $username, $password;

    public function __construct()
    {
        $this->apiUrl = env('BITRIX_URL');
        $this->username = env('BITRIX_LOGIN');
        $this->password = env('BITRIX_PASSWORD');
    }

    private function request(string $uri, array $params): array
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = $this->apiUrl . $uri;

            $client = Http::withBasicAuth($this->username, $this->password)
                ->timeout(15)
                ->withBody($json, 'application/json; charset=UTF-8');

            $response = $client->post($requestUrl);

            $body = $response->body();
            $code = $response->status();

            if ($body === "null" || $body === null || $code > 300) {
                Log::info('Bitrix request() code: ' . $code);
                Log::info($json);
                Log::info($body);

                return [];
            }

            return json_decode($body, true);
        }catch (RequestException $e){
            Log::error('Bitrix request() error: ' . $e->getMessage());

            return [];
        }
    }

    public function getContact(string $phone): array
    {
        $param = ['phone' => '+'.preg_replace("/[^0-9]/", '', $phone)];

        return $this->request('getContactDetails', $param);
    }
}
