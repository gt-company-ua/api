<?php

namespace App\Services\api;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Opendatabot
{
    private function request(string $uri, array $params): array
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = env('OPENDATABOT_URL') . $uri;

            $client = Http::timeout(100);

            $params['apiKey'] = env('OPENDATABOT_KEY');

            $response = $client->get($requestUrl, $params);

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                Log::info('Opendatabot Request() code: ' . $code . '. URL: ' . $requestUrl);
                Log::info($json);
                Log::info($body);

                return json_decode($body, true);
            }

            $result = json_decode($body, true);
            unset($result['forDevelopers']);

            return $result;
        }catch (RequestException $e){
            return [];
        }
    }

    public function transport(string $number): array
    {
        return $this->request('transport', ['number' => $number]);
    }
}
