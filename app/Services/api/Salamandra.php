<?php

namespace App\Services\api;

use App\Exceptions\OneCRequestException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Salamandra
{
    /**
     * @throws OneCRequestException
     */
    private function request(string $uri, $params = [], $sendPost = true): array
    {
        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = env('SALAMANDRA_URL') . $uri;

            $prepareRequest = Http::withToken(env('SALAMANDRA_TOKEN'));

            if ($sendPost) {
                $response = $prepareRequest->withBody($json, 'application/json')->post($requestUrl);
            } else {
                $response = $prepareRequest->get($requestUrl, $params);
            }

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                Log::info('Request() code: ' . $code);
                Log::info($json);
                Log::info($body);

                return [];
            }

            return json_decode($body, true);
        }catch (RequestException $e){
            Log::error("Salamandra exception:" . $e->getMessage());

            return [];
        }
    }

    public function cities(): array
    {
        return $this->request('agent/cities', [], false);
    }
}
