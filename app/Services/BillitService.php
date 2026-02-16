<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillitService
{
    protected $apiKey;
    protected $baseUrl;
    protected $defaultChannel;

    public function __construct()
    {
        $this->apiKey = config('services.billit.api_key');
        $this->baseUrl = rtrim((string) config('services.billit.base_url'), '/');
        $this->defaultChannel = (string) config('services.billit.channel', 'Peppol');
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

    public function sendInvoiceViaPeppol(array $invoiceData, ?string $channelOverride = null)
    {
        // Ensure Customer identifiers are present for PEPPOL routing (e.g., GLN/Peppol ID)
        // Many Billit accounts route automatically when valid identifiers exist.
        // Optionally include a channel hint if supported by your Billit plan.
        $channel = $channelOverride ?: $this->defaultChannel;
        if (!isset($invoiceData['SendChannels'])) {
            if ($channelOverride) {
                $invoiceData['SendChannels'] = [[ 'Channel' => $channelOverride ]];
            } else {
                // Prefer Peppol, fallback to Email as documented in Billit
                $invoiceData['SendChannels'] = [
                    [ 'Channel' => 'Peppol' ],
                    [ 'Channel' => 'Email' ],
                ];
            }
        }

        // Step 1: Create the order
        $created = $this->sendInvoice($invoiceData);

        Log::info('Billit create response', ['response' => $created]);
        // Step 2: Extract order id from creation response
        $orderId = $this->extractOrderId($created);
        if (!$orderId) {
            Log::warning('Billit send - could not extract order id from create response', [ 'response' => $created ]);
            return $created; // return create response; caller may handle
        }

        // Step 3: Send the order using commands/send (bulk endpoint with Transporttype)
        // Choose Peppol only if we have a valid identifier (GLN) or ReceiverEndpointID; else default to Email
        $transportOrder = $channelOverride ?: (
            $this->shouldUsePeppol($invoiceData['Customer'] ?? [], $invoiceData) ? 'Peppol' : 'Email'
        );
        try {
            $commandsEndpoint = $this->baseUrl . "/v1/orders/commands/send";
            $payload = [
                'Transporttype' => $transportOrder,
                'OrderIDs' => [ (int)$orderId ],
            ];

            $sendResponse = Http::withHeaders([
                'ApiKey' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($commandsEndpoint, $payload);

            if ($sendResponse->successful()) {
                return [ 'created' => $created, 'send' => $sendResponse->json(), 'transport' => $transportOrder ];
            }

            // Fallback: if Peppol fails, try Email once
            if (!$channelOverride && strcasecmp($transportOrder, 'Peppol') === 0) {
                Log::warning('Billit send via Peppol failed, retrying via Email', [
                    'status' => $sendResponse->status(),
                    'response' => $sendResponse->json(),
                    'order_id' => $orderId,
                ]);
                $payload['Transporttype'] = 'Email';
                $sendEmailResp = Http::withHeaders([
                    'ApiKey' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($commandsEndpoint, $payload);

                if ($sendEmailResp->successful()) {
                    return [ 'created' => $created, 'send' => $sendEmailResp->json(), 'transport' => 'Email' ];
                }

                Log::error('Billit send via Email also failed', [
                    'status' => $sendEmailResp->status(),
                    'response' => $sendEmailResp->json(),
                    'order_id' => $orderId,
                ]);
                return [ 'created' => $created, 'send_error' => $sendEmailResp->json(), 'status' => $sendEmailResp->status(), 'transport' => 'Email' ];
            }

            Log::error('Billit send error', [
                'status' => $sendResponse->status(),
                'response' => $sendResponse->json(),
                'order_id' => $orderId,
            ]);
            return [ 'created' => $created, 'send_error' => $sendResponse->json(), 'status' => $sendResponse->status(), 'transport' => $transportOrder ];
        } catch (\Exception $e) {
            Log::error('Billit send exception', [ 'message' => $e->getMessage(), 'order_id' => $orderId, 'transport' => $transportOrder ]);
            return [ 'created' => $created, 'send_exception' => $e->getMessage(), 'transport' => $transportOrder ];
        }
    }

    /**
     * Extract the order id from various possible Billit create responses
     */
    protected function extractOrderId($response)
    {
        // Response can be a plain integer id, a numeric string, or an object/array
        if (is_int($response)) {
            return $response;
        }
        if (is_string($response) && ctype_digit($response)) {
            return (int)$response;
        }
        if (is_array($response)) {
            if (isset($response['Id'])) return $response['Id'];
            if (isset($response['OrderId'])) return $response['OrderId'];
            if (isset($response['Order']['Id'])) return $response['Order']['Id'];
            if (isset($response['id'])) return $response['id'];
        }
        return null;
    }
    /**
     * Decide if we should use Peppol: valid GLN or ReceiverEndpointID present.
     * Note: Billit doesn't accept 'PEPPOL' as an IdentifierType, so we check for:
     * 1. Valid GLN in Identifiers array
     * 2. ReceiverEndpointID in the payload (which contains PEPPOL schemeID and value)
     */
    protected function shouldUsePeppol($customer, array $fullPayload = []): bool
    {
        // Check for ReceiverEndpointID in payload (PEPPOL routing)
        if (isset($fullPayload['ReceiverEndpointID']) && 
            is_array($fullPayload['ReceiverEndpointID']) &&
            !empty($fullPayload['ReceiverEndpointID']['schemeID']) &&
            !empty($fullPayload['ReceiverEndpointID']['value'])) {
            return true;
        }
        
        // Check for valid GLN in customer identifiers
        if (!is_array($customer)) return false;
        $identifiers = $customer['Identifiers'] ?? [];
        if (!is_array($identifiers) || empty($identifiers)) return false;
        foreach ($identifiers as $identifier) {
            $type = strtoupper((string)($identifier['IdentifierType'] ?? ''));
            $value = preg_replace('/\s+/', '', (string)($identifier['Identifier'] ?? ''));
            if ($value === '') continue;
            if ($type === 'GLN') {
                if ($this->isValidGln($value)) return true; // only valid GLN qualifies
            }
        }
        return false;
    }

    /**
     * Validate GLN using GS1 Mod-10 algorithm (13 digits)
     */
    protected function isValidGln(string $gln): bool
    {
        $gln = trim($gln);
        if (!preg_match('/^\d{13}$/', $gln)) return false;
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int)$gln[$i];
            $sum += $digit * (($i % 2 === 0) ? 1 : 3);
        }
        $check = (10 - ($sum % 10)) % 10;
        return $check === (int)$gln[12];
    }
}
