<?php

namespace App\Services;

use App\Models\Entreprise;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillitService
{
    protected $apiKey;
    protected $baseUrl;
    protected $defaultChannel;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null, ?string $defaultChannel = null)
    {
        // API key must be provided per-enterprise; no env fallback.
        $this->apiKey = (string) ($apiKey ?? '');
        $this->baseUrl = rtrim((string) ($baseUrl ?? config('services.billit.base_url')), '/');
        $this->defaultChannel = (string) ($defaultChannel ?? config('services.billit.channel', 'Peppol'));
    }

    /**
     * Create a Billit client using the platform (env/config) API key explicitly.
     * Intended for flows that are not tenant-scoped (e.g. manager subscription invoices).
     */
    public static function fromPlatformConfig(): self
    {
        return new self((string) config('services.billit.api_key'));
    }

    /**
     * Use the entreprise's Billit API key when set. No global fallback.
     */
    public static function forEntreprise(?Entreprise $entreprise): self
    {
        $apiKey = '';
        if ($entreprise && filled($entreprise->billit_api_key)) {
            $apiKey = $entreprise->billit_api_key;
        }

        return new self($apiKey);
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function sendInvoice(array $invoiceData)
    {
        try {
            // Log the outgoing payload (without sensitive headers) for debugging
            Log::info('Billit payload before sending', [
                'url' => $this->baseUrl . '/v1/orders',
                'payload' => $invoiceData,
            ]);

            Log::info('Billit request using configured API key', [
                'api_key_prefix' => $this->apiKey !== '' ? substr($this->apiKey, 0, 6) . '…' : '(empty)',
            ]);

            $response = Http::withHeaders([
                'ApiKey' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/v1/orders', $invoiceData);

            if ($response->successful()) {
                Log::info('Billit API success', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return $response->json();
            }

            Log::error('Billit API Error', [
                'status' => $response->status(),
                'response' => $response->body(),
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

        // Step 1: Create the order ONLY.
        // We deliberately do NOT call the commands/send endpoint here so that
        // the user can send the invoice manually from within the Billit UI.
        $created = $this->sendInvoice($invoiceData);

        Log::info('Billit create response (no auto-send)', ['response' => $created]);

        // Return the create response directly; callers can inspect it or log it.
        return $created;
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
