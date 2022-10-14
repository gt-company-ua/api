<?php

namespace App\Services\api;

use App\Exceptions\ProfitsoftAuthException;
use App\Exceptions\ProfitsoftRequestException;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Profitsoft
{
    private $apiUrl, $username, $password;

    public function __construct()
    {
        $this->apiUrl = env('PS_URL');
        $this->username = env('PS_LOGIN');
        $this->password = env('PS_PASSWORD');
    }

    /**
     * @throws ProfitsoftRequestException
     * @throws ProfitsoftAuthException
     */
    private function auth(): string
    {
        $requestUrl = $this->apiUrl . 'oauth';

        $formParams = [
            'username' => $this->username,
            'password' => $this->password
        ];

        try{
            $response = Http::asForm()->post($requestUrl, $formParams);

            $body = $response->body();
            $code = $response->status();

            if ($code !== 200) {
                throw new ProfitsoftAuthException('Profitsoft auth failed. Status code: ' . $code);
            }

            $result = json_decode($body);

            $token = $result->access_token ?? null;

            if (empty($token)) {
                throw new ProfitsoftAuthException('Profitsoft auth failed. Token is empty');
            }

            return $token;
        }catch (RequestException $e){
            throw new ProfitsoftRequestException('Auth() request error: ' . $e->getMessage());
        }
    }

    /**
     * @throws ProfitsoftRequestException
     */
    private function request(string $uri, array $params): array
    {
        try {
            $token = $this->auth();
        }catch (Exception $e) {
            Log::error($e);

            return [];
        }

        $json = json_encode($params, JSON_UNESCAPED_UNICODE);

        try{
            $requestUrl = $this->apiUrl . $uri;
            $response = Http::withToken($token)->withBody($json, 'application/json')->post($requestUrl);

            $body = $response->body();
            $code = $response->status();

            if ($code > 300) {
                Log::info($json);
                Log::info($body);

                throw new ProfitsoftRequestException('Request() code: ' . $code);
            }

            return json_decode($body, true);
        }catch (RequestException $e){
            throw new ProfitsoftRequestException('Request() request error: ' . $e->getMessage());

            return [];
        }
    }

    public function searchCity(string $search, $searchBy = 'name', $onlyMtsbu = true): array
    {
        $formParams = [
            $searchBy => $search,
            "pageSize" => 500
        ];

        try {
            $result = $this->request('rest/dict/town/listByFilter', $formParams);
        } catch (Exception $e) {
            return [];
        }

        $cities = [];

        if (isset($result['status']) && $result['status'] === 'SUCCESS') {
            foreach ($result['objects'] as $city) {
                if ($onlyMtsbu && is_null($city['mtsbuId'])) continue;

                $cities[] = $city;
            }
        }

        return $cities;
    }

}