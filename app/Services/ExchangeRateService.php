<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    public function getUsdExchangeRate(): float
    {
        try {
            $response = file_get_contents("https://open.er-api.com/v6/latest/USD");
            $data = json_decode($response, true);
            return $data['rates']['EUR'] ?? 1.0;
        } catch (\Throwable $e) {
            Log::error('Failed to fetch exchange rate: ' . $e->getMessage());
            return 1.0;
        }
    }
}
