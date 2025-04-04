<?php

namespace App\Services;

use GuzzleHttp\Client;

class AssetValueService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('ALPHA_VANTAGE_API_KEY');
    }

    public function getCurrentValue($symbol)
    {
        $response = $this->client->get('https://www.alphavantage.co/query', [
            'query' => [
                'function' => 'TIME_SERIES_INTRADAY',
                'symbol' => $symbol,
                'interval' => '1min',
                'apikey' => $this->apiKey
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $timeSeries = $data['Time Series (1min)'] ?? [];
        $latest = reset($timeSeries); // Último valor
        return $latest['4. close'] ?? null; // Precio de cierre
    }
}