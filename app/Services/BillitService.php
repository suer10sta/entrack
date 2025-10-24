<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillitService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.sandbox.billit.be';

    public function __construct()
    {
        $this->apiKey = config('services.billit.api_key');
    }

    public function sendInvoice(array $invoiceData)
    {
        try {
            // Log the API key (first few characters) for debugging
            Log::info('Using API Key: ' . substr($this->apiKey, 0, 8) . '...');

            $response = Http::withHeaders([
                'ApiKey' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/v1/orders', $invoiceData);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Billit API Error', [
                'status' => $response->status(),
                'response' => $response->json(),
                'headers' => $response->headers(),
                'request_url' => $this->baseUrl . '/v1/orders',
            ]);

            throw new \Exception('Failed to send invoice to Billit: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Billit API Exception', [
                'message' => $e->getMessage(),
                'api_key_length' => strlen($this->apiKey),
            ]);
            throw $e;
        }
    }
} 