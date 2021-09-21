<?php

namespace App;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class Sirena
{
    public function getApikey(){
        $baseUrl = env('API_SIRENA');
        $response = Http::get(env('API_SIRENA').'prospect/56b367bffdc27b03003fc3ee?api-key=Yf9EkNsI4w5NFCo5r8w3r30F6P1oi2O7');
        return $response->status();
    }
}
