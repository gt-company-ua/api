<?php

namespace App\Services\api;

use Illuminate\Support\Facades\Log;

class CrmApi
{
    public $url = 'https://wdi24.com.ua/rest/1/tj17rkikz9kmy5mv/';

    public $leadAdd = 'crm.lead.add.json';
    public $contactAdd = 'crm.contact.add.json';
    public $contactList = 'crm.contact.list.json';
    public $contactGet = 'crm.contact.get.json';
    public $contactUpdate = 'crm.contact.update.json';
    public $dealAdd = 'crm.deal.add.json';
    public $dealUpdate = 'crm.deal.update.json';
    public $dealGet = 'crm.deal.get.json';
    public $universalListItemAdd = 'lists.element.add.json';
    public $universalListItemUpdate = 'lists.element.update.json';
    public $universalList = 'lists.element.get.json';

    public function addListItem(array $params)
    {
        return $this->makeRequest($this->url . $this->universalListItemAdd . '/', $params);
    }

    public function updateListItem(array $params)
    {
        return $this->makeRequest($this->url . $this->universalListItemUpdate . '/', $params);
    }

    public function findListItem(array $params)
    {
        return $this->makeRequest($this->url . $this->universalList . '/', $params);
    }

    public function createDeal(array $params)
    {
        return $this->makeRequest($this->url . $this->dealAdd . '/', $params);
    }

    public function updateDeal(array $params)
    {
        return $this->makeRequest($this->url . $this->dealUpdate . '/', $params);
    }

    public function getDeal(array $params)
    {
        return $this->makeRequest($this->url . $this->dealGet . '/', $params);
    }

    public function updateContact(array $params)
    {
        return $this->makeRequest($this->url . $this->contactUpdate . '/', $params);
    }

    public function createContact(array $params)
    {
        return $this->makeRequest($this->url . $this->contactAdd . '/', $params);
    }

    public function createLead(array $params)
    {
        return $this->makeRequest($this->url . $this->leadAdd . '/', $params);
    }

    public function getContact(array $params)
    {
        return $this->makeRequest($this->url . $this->contactGet . '/', $params);
    }

    public function findContact(array $params)
    {
        return $this->makeRequest($this->url . $this->contactList . '/', $params);
    }

    private function makeRequest(string $url, array $params)
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => http_build_query($params)
        ));

        $result = curl_exec($curl);

        if ( ! $result){
            Log::error('CRM curl error ' . $url . ':' . curl_error($curl). ' Request: '. json_encode($params, JSON_UNESCAPED_UNICODE));
        }

        curl_close($curl);

        return json_decode($result, 1);
    }
}