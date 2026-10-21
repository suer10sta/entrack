<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Services\BillitService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use setasign\Fpdi\Fpdi;

class BillitController extends Controller
{

    public function test()
    {
        return view('admin.billit-test');
    }

    public function sendTestInvoice(): JsonResponse
    {
        $invoiceData = [
            'OrderType' => 'Invoice',
            'OrderDirection' => 'Income',
            'OrderNumber' => 'QS-001',
            'OrderDate' => '2025-01-01',
            'ExpiryDate' => '2025-01-31',
            'Customer' => [
                'Name' => 'Billit',
                'VATNumber' => 'BE0563846944',
                'PartyType' => 'Customer',
                'Identifiers' => [
                    [
                        'IdentifierType' => 'GLN',
                        'Identifier' => '5430003732007'
                    ]
                ],
                'Addresses' => [
                    [
                        'AddressType' => 'InvoiceAddress',
                        'Name' => 'Billit',
                        'Street' => 'Oktrooiplein',
                        'StreetNumber' => '1',
                        'City' => 'Ghent',
                        'Box' => '301',
                        'CountryCode' => 'BE'
                    ],
                    [
                        'AddressType' => 'DeliveryAddress',
                        'Name' => 'Billit',
                        'Street' => 'Oktrooiplein',
                        'StreetNumber' => '1',
                        'City' => 'Ghent',
                        'Box' => '301',
                        'CountryCode' => 'BE'
                    ]
                ]
            ],
            'OrderLines' => [
                [
                    'Quantity' => 1,
                    'UnitPriceExcl' => 10.0,
                    'Description' => 'tshirts',
                    'VATPercentage' => 6.0
                ],
                [
                    'Quantity' => 2,
                    'UnitPriceExcl' => 3.75,
                    'Description' => 'hoodies',
                    'VATPercentage' => 21.0
                ]
            ]
        ];

        try {
            $entreprise = auth()->user()
                ? Entreprise::find(auth()->user()->entreprise_id)
                : null;
            $billit = BillitService::forEntreprise($entreprise);
            if (! $billit->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.Billit is not configured. Add your Billit API key under Enterprise Information for this company.'),
                ], 422);
            }
            $response = $billit->sendInvoice($invoiceData);
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => \App\Support\LocalizedError::message($e)
            ], 500);
        }
    }

    /**
     * Sends a real invoice (from invoices_history) to Billit as a Peppol e-invoice.
     * Maps data and totals as a single line.
     */
    public function sendInvoiceHistoryBillit(Request $request, $id): JsonResponse
    {
        // You may want to protect this endpoint in production.
        try {
            $invoice = \App\Models\invoices_history::findOrFail($id);
            
            // Debug logging instead of dd()
            \Log::info('Invoice found:', [
                'id' => $invoice->id,
                'client_id' => $invoice->client_id ?? 'NOT SET',
                'project_id' => $invoice->project_id ?? 'NOT SET',
                'entreprise_id' => $invoice->entreprise_id ?? 'NOT SET',
                'all_fields' => $invoice->toArray()
            ]);
            
            // Get the client
            $client = null;
            if (isset($invoice->client_id) && $invoice->client_id) {
                $client = \App\Models\Client::where('id', $invoice->client_id)->first();
                \Log::info('Client lookup:', ['found' => $client ? 'YES' : 'NO', 'id' => $invoice->client_id]);
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching invoice/client:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => \App\Support\LocalizedError::message($e)], 500);
        }
       
        if (!$client) {
            return response()->json([
                'success' => false, 
                'message' => __('messages.Client not found for invoice'),
                'debug' => [
                    'invoice_id' => $invoice->id,
                    'client_id_in_invoice' => $invoice->client_id ?? null,
                    'invoice_data' => $invoice->toArray()
                ]
            ], 404);
        }

        // Get the project for detailed info
        $project = null;
        if (property_exists($invoice, 'project_id') && $invoice->project_id) {
            $project = \App\Models\Project::find($invoice->project_id);
        }

        // Get the enterprise/company info
        $entreprise = \App\Models\Entreprise::find($invoice->entreprise_id);
        if (!$entreprise) {
            return response()->json(['success' => false, 'message' => __('messages.Enterprise not found for invoice')], 404);
        }

        if (! auth()->user() || (int) $invoice->entreprise_id !== (int) auth()->user()->entreprise_id) {
            return response()->json(['success' => false, 'message' => __('messages.Unauthorized')], 403);
        }

        $billit = BillitService::forEntreprise($entreprise);
        if (! $billit->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.Billit is not configured for this enterprise. Add your Billit API key under Enterprise Information.'),
            ], 422);
        }

        // Build payload to create the invoice/order in Billit only.
        // We deliberately do NOT trigger the Billit "commands/send" endpoint here,
        // so that the user can send the invoice manually from within Billit (Peppol/email/etc.).
        $orderData = $this->mapInvoiceToBillitPayload($invoice, $client, $project, $entreprise, null, $request);

        /**
         * ORIGINAL AUTO-SEND LOGIC (now disabled on purpose):
         *
         * // Optional: force a preferred PEPPOL identifier via query (?preferred_identifier=9925:BE0823758741)
         * $preferredIdentifier = trim((string)$request->query('preferred_identifier', ''));
         * if ($preferredIdentifier !== '') {
         *     if (preg_match('/^(\d{4}):([\w\d]+)$/', $preferredIdentifier, $m)) {
         *         $scheme = $m[1];
         *         $value  = $m[2];
         *         // Force ReceiverEndpointID (PEPPOL)
         *         $orderData['ReceiverEndpointID'] = [
         *             'schemeID' => $scheme,
         *             'value'    => $value,
         *         ];
         *         // Add preference hint (both casings for forward-compatibility)
         *         $orderData['ElectronicInvoicing']  = [ 'PreferredIdentifier'  => $preferredIdentifier ];
         *         $orderData['electronicInvoicing'] = [ 'preferredIdentifier' => $preferredIdentifier ];
         *     } else {
         *         \Log::warning(
         *             'Preferred identifier has unexpected format; expected scheme:value',
         *             [ 'preferred_identifier' => $preferredIdentifier ]
         *         );
         *     }
         * }
         *
         * // AUTO-SEND (Peppol / Email) – now commented:
         * $channel  = $request->query('channel'); // e.g., 'Email' to force email sending
         * $response = $this->billitService->sendInvoiceViaPeppol($orderData, $channel);
         */

        try {
            // Only create the order in Billit; no automatic Peppol/email sending.
            $response = $billit->sendInvoice($orderData);

            // Optional forward to provided recipients (from contacts modal)
            $recipientsJson = (string)$request->input('forward_recipients', '');
            if ($recipientsJson !== '') {
                $emails = json_decode($recipientsJson, true);
                if (is_array($emails)) {
                    foreach ($emails as $email) {
                        $email = trim((string)$email);
                        if ($email !== '') {
                            $this->sendInvoicePdfToEmail($invoice, $entreprise, $email);
                        }
                    }
                }
            }

            // Mark invoice as sent
            $invoice->sentInvoice = 1;
            $invoice->save();

            return response()->json(['success' => true, 'billit_response' => $response]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => \App\Support\LocalizedError::message($e)], 500);
        }
    }

    public function sendInvoiceHistoryBillitByType(Request $request, $id, $type)
{
    try {
        $invoice = \App\Models\invoices_history::findOrFail($id);

        \Log::info('Invoice found:', [
            'id' => $invoice->id,
            'client_id' => $invoice->client_id ?? 'NOT SET',
            'project_id' => $invoice->project_id ?? 'NOT SET',
            'entreprise_id' => $invoice->entreprise_id ?? 'NOT SET',
            'type' => $type,
        ]);

        $client = null;
        if (isset($invoice->client_id) && $invoice->client_id) {
            $client = \App\Models\Client::where('id', $invoice->client_id)->first();
        }
    } catch (\Exception $e) {
        \Log::error('Error fetching invoice/client:', ['error' => $e->getMessage()]);
        return back()->with('error', \App\Support\LocalizedError::message($e));
    }

    if (!$client) {
        return back()->with('error', __('messages.Client introuvable pour cette facture.'));
    }

    $project = null;
    if (property_exists($invoice, 'project_id') && $invoice->project_id) {
        $project = \App\Models\Project::find($invoice->project_id);
    }

    $entreprise = \App\Models\Entreprise::find($invoice->entreprise_id);
    if (!$entreprise) {
        return back()->with('error', __('messages.Entreprise introuvable pour cette facture.'));
    }

    if (! auth()->user() || (int) $invoice->entreprise_id !== (int) auth()->user()->entreprise_id) {
        return back()->with('error', __('messages.Non autorisé.'));
    }

    $billit = BillitService::forEntreprise($entreprise);
    if (! $billit->isConfigured()) {
        return back()->with('error', __('messages.Billit n’est pas configuré. Ajoutez votre clé API Billit dans les informations entreprise.'));
    }

    // Build payload to create the invoice/order in Billit only.
    // We deliberately do NOT trigger the Billit "commands/send" endpoint here,
    // so that the user can send the invoice manually from within Billit (Peppol/email/etc.).
    $orderData = $this->mapInvoiceToBillitPayload($invoice, $client, $project, $entreprise, $type, $request);

    /**
     * ORIGINAL AUTO-SEND LOGIC (now disabled on purpose):
     *
     * // Optional PEPPOL preferred ID
     * $preferredIdentifier = trim((string)$request->query('preferred_identifier', ''));
     * if ($preferredIdentifier !== '') {
     *     if (preg_match('/^(\d{4}):([\w\d]+)$/', $preferredIdentifier, $m)) {
     *         $orderData['ReceiverEndpointID'] = [
     *             'schemeID' => $m[1],
     *             'value'    => $m[2],
     *         ];
     *         $orderData['ElectronicInvoicing']  = [ 'PreferredIdentifier'  => $preferredIdentifier ];
     *         $orderData['electronicInvoicing'] = [ 'preferredIdentifier' => $preferredIdentifier ];
     *     }
     * }
     *
     * // AUTO-SEND (Peppol / Email) – now commented:
     * $channel  = $request->query('channel');
     * $response = $this->billitService->sendInvoiceViaPeppol($orderData, $channel);
     */

    $channel = strtoupper((string) $request->query('channel', ''));

    try {
        if ($channel === 'PEPPOL') {
            $response = $billit->sendInvoiceViaPeppol($orderData, 'Peppol');
        } else {
            // Only create the order in Billit; no automatic Peppol/email sending.
            $response = $billit->sendInvoice($orderData);
        }

        // Optional email forwarding
        $recipientsJson = (string)$request->input('forward_recipients', '');
        if ($recipientsJson !== '') {
            $emails = json_decode($recipientsJson, true);
            if (is_array($emails)) {
                foreach ($emails as $email) {
                    $email = trim((string)$email);
                    if ($email !== '') {
                        $this->sendInvoicePdfToEmail($invoice, $entreprise, $email);
                    }
                }
            }
        }

        // Mark invoice as sent
        $invoice->sentInvoice = 1;
        $invoice->save();

        $successMsg = $channel === 'PEPPOL'
            ? 'Facture créée dans Billit pour envoi Peppol (' . $type . ')'
            : 'Facture envoyée avec succès via Billit (' . $type . ')';

        return back()->with('success', $successMsg);

    } catch (\Exception $e) {
        return back()->with('error', \App\Support\LocalizedError::message($e));
    }
}


    public function listInvoicesByType(Request $request, $type): JsonResponse
    {
        $type = strtolower((string) $type);
        $enterpriseId = auth()->user() ? auth()->user()->entreprise_id : null;
        if (!$enterpriseId) {
            return response()->json(['success' => false, 'message' => __('messages.Unauthorized')], 401);
        }

        $query = \App\Models\invoices_history::query()
            ->where('entreprise_id', $enterpriseId)
            ->orderBy('created_at', 'desc');

        // Map UI types to stored values
        switch ($type) {
            case 'detailed':
                $query->where('type', 'Detailed');
                break;
            case 'partial':
                $query->where('type', 'Partial');
                break;
            case 'percentage':
                $query->where('type', 'Percentage');
                break;
            case 'duration':
            case 'period':
                // Some code paths name it Period, others Duration
                $query->whereIn('type', ['Duration', 'Period']);
                break;
            case 'full':
            default:
                // Full invoices are those without a specific type or explicitly marked
                $query->where(function ($q) {
                    $q->whereNull('type')->orWhere('type', 'Full');
                });
                break;
        }

        $invoices = $query->limit(50)->get();

        // Attach client names efficiently
        $clientIds = $invoices->pluck('client_id')->filter()->unique()->all();
        $clients = \App\Models\Client::whereIn('id', $clientIds)->get()->keyBy('id');

        $data = $invoices->map(function ($inv) use ($clients) {
            $client = $clients->get($inv->client_id);
            return [
                'id' => $inv->id,
                'number' => $inv->number,
                'project_name' => $inv->project_name,
                'client_name' => $client ? $client->name : ($inv->client_name ?? null),
                'total_htva' => (float) ($inv->total_htva ?? 0),
                'created_at' => optional($inv->created_at)->toDateTimeString(),
            ];
        })->values();

        return response()->json(['success' => true, 'invoices' => $data]);
    }

    /**
     * Convert country name to ISO 3166-1 alpha-2 code
     */
    protected function getCountryCode($countryName)
    {
        $countryMap = [
            'Belgium' => 'BE',
            'Belgique' => 'BE',
            'België' => 'BE',
            'France' => 'FR',
            'Netherlands' => 'NL',
            'Nederland' => 'NL',
            'Pays-Bas' => 'NL',
            'Germany' => 'DE',
            'Allemagne' => 'DE',
            'Duitsland' => 'DE',
            'Luxembourg' => 'LU',
            'United Kingdom' => 'GB',
            'Royaume-Uni' => 'GB',
            'Spain' => 'ES',
            'Espagne' => 'ES',
            'Italy' => 'IT',
            'Italie' => 'IT',
            'Portugal' => 'PT',
            'Austria' => 'AT',
            'Autriche' => 'AT',
            'Switzerland' => 'CH',
            'Suisse' => 'CH',
            'Poland' => 'PL',
            'Pologne' => 'PL',
            'Czech Republic' => 'CZ',
            'Tchéquie' => 'CZ',
            'Denmark' => 'DK',
            'Danemark' => 'DK',
            'Sweden' => 'SE',
            'Suède' => 'SE',
            'Norway' => 'NO',
            'Norvège' => 'NO',
            'Finland' => 'FI',
            'Finlande' => 'FI',
            'Ireland' => 'IE',
            'Irlande' => 'IE',
            'Greece' => 'GR',
            'Grèce' => 'GR',
            'Hungary' => 'HU',
            'Hongrie' => 'HU',
            'Romania' => 'RO',
            'Roumanie' => 'RO',
            'Maroc' => 'MA',
            'Morocco' => 'MA',
        ];

        // If already a 2-letter code, return as is
        if (strlen($countryName) == 2) {
            return strtoupper($countryName);
        }

        // Look up in map (case-insensitive)
        foreach ($countryMap as $name => $code) {
            if (strcasecmp($name, $countryName) === 0) {
                return $code;
            }
        }

        // Default to BE if not found
        \Log::warning('Unknown country name, defaulting to BE', ['country' => $countryName]);
        return 'BE';
    }

    /**
     * Helper to map invoices_history and Client to Billit order payload
     * Based on your full invoice structure
     */
    protected function mapInvoiceToBillitPayload($invoice, $client, $project = null, $entreprise = null, $type = null, Request $request = null)
    {
        // Calculate dates
        $invoiceDate = $invoice->created_at ? $invoice->created_at->format('Y-m-d') : now()->format('Y-m-d');
        $dueDate = $invoice->created_at 
            ? $invoice->created_at->addDays(abs((int)filter_var($client->due_date ?? 30, FILTER_SANITIZE_NUMBER_INT)))->format('Y-m-d')
            : now()->addDays(30)->format('Y-m-d');

        // Build invoice number (matching your template format)
        $invoiceNumber = ($entreprise && $entreprise->invoice_prefix ? $entreprise->invoice_prefix . '-' : '') 
                        . now()->format('ym') 
                        . $invoice->number;

        // Calculate VAT percentage from invoice totals or project
        $vatPercentage = 0;
        if (isset($invoice->total_htva, $invoice->total_tva) && $invoice->total_htva > 0) {
            $vatPercentage = round(($invoice->total_tva / $invoice->total_htva) * 100, 2);
        } elseif ($project && isset($project->tva)) {
            $vatPercentage = (float)$project->tva;
        }

        // Build customer data
        $countryName = $client->billing_country ?? $client->country ?? 'BE';
        $countryCode = $this->getCountryCode($countryName);
        $customerData = [
            'Name' => $client->name,
            'VATNumber' => $client->TVA,
            'PartyType' => 'Customer',
            'Email' => $client->email,
            'Addresses' => [
                [
                    'AddressType' => 'InvoiceAddress',
                    'Name' => $client->name,
                    'Street' => $client->billing_street ?? $client->address ?? '',
                    'City' => $client->billing_city ?? $client->city ?? '',
                    'CountryCode' => $countryCode,
                    'PostalCode' => $client->billing_zip ?? $client->zip ?? '',
                ]
            ],
        ];

        // If client has GLN and/or Peppol ID, include both for Peppol routing
        // Note: Billit only accepts standard identifier types like GLN in the Identifiers array
        // PEPPOL routing is handled via ReceiverEndpointID, not through Identifiers
        $identifiers = [];
        if (!empty($client->gln)) {
            $glnValue = (string)$client->gln;
            $identifiers[] = [
                'IdentifierType' => 'GLN',
                'Identifier' => $glnValue,
            ];
        }
        
        $receiverEndpoint = null;
        $preferredIdentifierFromClient = null;
        if (!empty($client->peppol_id)) {
            // Example format: 9925:BE0823758741 or 0208:0823758741
            // This format: schemeID:value (e.g., 9925:BE0823758741 where 9925 is the scheme ID)
            $raw = trim((string)$client->peppol_id);
            if (preg_match('/^(\d{4}):([\w\d]+)$/', $raw, $m)) {
                $scheme = $m[1];
                $value = $m[2];
                $receiverEndpoint = [
                    'schemeID' => $scheme,
                    'value' => $value,
                ];
                $preferredIdentifierFromClient = $raw;
            } else {
                // If format doesn't match expected pattern, log a warning but don't fail
                \Log::warning('Billit PEPPOL ID format unexpected', [
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'peppol_id' => $raw,
                    'expected_format' => 'schemeID:value (e.g., 9925:BE0823758741)'
                ]);
            }
        }
        
        // Only add Identifiers array if we have valid identifiers (GLN is valid, PEPPOL is not)
        if (!empty($identifiers)) {
            $customerData['Identifiers'] = $identifiers;
        }

        // Determine line amount and description per type
        $normalizedType = strtolower((string)$type);
        $baseAmount = (float)($invoice->total_htva ?? 0);
        $lineDescription = strtoupper($invoice->project_name ?? 'Service');

        $quantity = 1;
        $unitPrice = $baseAmount;

        switch ($normalizedType) {
            
            case 'period':
                // Use invoice->duration (minutes) → quantity in hours
                $minutes = (int)($invoice->duration ?? 0);
                // Keep full precision for unit price math; round only for the quantity sent out.
                $hoursRaw = $minutes / 60;
                $hours = round($hoursRaw, 2);
                if ($project && isset($project->cost_per_hour) && (float)$project->cost_per_hour > 0 && $hours > 0) {
                    $quantity = $hours;
                    $unitPrice = (float)$project->cost_per_hour;
                } elseif ($hours > 0) {
                    $quantity = $hours;
                    // If we already have a computed base amount for the period,
                    // derive the hourly unit price so that (qty * unit price) matches baseAmount,
                    // including when qty < 1 hour.
                    $unitPrice = round($baseAmount / max($hoursRaw, 0.000001), 2);
                } else {
                    $quantity = 1;
                    $unitPrice = $baseAmount;
                }
                $lineDescription = $lineDescription;
                break;
            case 'partial':
                if (isset($invoice->partial) && (float)$invoice->partial > 0) {
                    $baseAmount = (float)$invoice->partial;
                }
                $quantity = 1;
                $unitPrice = $baseAmount;
                $lineDescription = $lineDescription;
                break;
            case 'percentage':
                $quantity = (float)$invoice->percentage;
                $unitPrice = (float)$invoice->total_htva / (float)$invoice->percentage;
                $lineDescription = $lineDescription;
                break;
            case 'detailed':
                $quantity = 1;
                $unitPrice = $baseAmount;
                $lineDescription =  $lineDescription;
                break;
            case 'full':
            default:
                $quantity = 1;
                $unitPrice = $baseAmount;
                $lineDescription = $lineDescription;
                break;
        }

        // Build order lines. For 'detailed' type, explode invoice->details JSON to lines
        $orderLines = [];
        if ($normalizedType === 'detailed' && !empty($invoice->details)) {
            $decodedDetails = null;
            try {
                $decodedDetails = is_string($invoice->details)
                    ? json_decode($invoice->details, true)
                    : $invoice->details;
            } catch (\Throwable $e) {
                $decodedDetails = null;
            }

            if (is_array($decodedDetails) && !empty($decodedDetails)) {
                foreach ($decodedDetails as $detail) {
                    $dQuantity = isset($detail['quantity']) ? (float)$detail['quantity'] : 1.0;
                    $dUnit = null;
                    if (isset($detail['unit_price'])) {
                        $dUnit = (float)$detail['unit_price'];
                    } elseif (isset($detail['total']) && $dQuantity > 0) {
                        $dUnit = (float)$detail['total'] / $dQuantity;
                    } else {
                        $dUnit = 0.0;
                    }
                    $dDescription = isset($detail['description']) && $detail['description'] !== ''
                        ? (string)$detail['description']
                        : 'Detail';

                    $orderLines[] = [
                        'Quantity' => round($dQuantity, 2),
                        'UnitPriceExcl' => round($dUnit, 2),
                        'Description' => $dDescription,
                        'VATPercentage' => $vatPercentage,
                    ];
                }
            }
        }

        // Fallback to single line when not detailed or details missing/invalid
        if (empty($orderLines)) {
            $orderLines = [
                [
                    'Quantity' => $quantity,
                    'UnitPriceExcl' => $unitPrice,
                    'Description' => $lineDescription,
                    'VATPercentage' => $vatPercentage,
                ]
            ];
        }

        $notes = [];
        if (isset($invoice->comment) && $invoice->comment) {
            $notes[] = trim((string)$invoice->comment);
        }
        if ($project && $project->description) {
            $notes[] = 'Informations: ' . $project->description;
        }

        $payload = [
            'OrderType' => 'Invoice',
            'OrderDirection' => 'Income',
            'OrderNumber' => $invoiceNumber,
            'OrderDate' => $invoiceDate,
            'ExpiryDate' => $dueDate,
            'Customer' => $customerData,
            'OrderLines' => $orderLines,
        ];

        if ($receiverEndpoint) {
            $payload['ReceiverEndpointID'] = $receiverEndpoint;
            // Also express the preference for this identifier
            $preferred = $preferredIdentifierFromClient
                ?: ((isset($receiverEndpoint['schemeID'], $receiverEndpoint['value']))
                    ? ($receiverEndpoint['schemeID'] . ':' . $receiverEndpoint['value'])
                    : null);
            if ($preferred) {
                $payload['ElectronicInvoicing'] = [ 'PreferredIdentifier' => $preferred ];
                $payload['electronicInvoicing'] = [ 'preferredIdentifier' => $preferred ];
            }
        }

        if (!empty($notes)) {
            $payload['Note'] = implode("\n\n", $notes);
        }
        if ($entreprise) {
            $payload['PaymentMeansNote'] = "Veuillez payer sur le compte IBAN: " . ($entreprise->iban ?? '') . " - " . ($entreprise->bank_name ?? '');
        }

        // Try to attach our generated PDF invoice file to Billit payload (if available)
        // If attachments are provided, merge PDF attachments with the invoice PDF
        try {
            if ($entreprise && $invoice && isset($invoice->created_at, $invoice->number)) {
                $year = $invoice->created_at->format('Y');
                $yearForName = $invoice->created_at->format('y');
                $month = $invoice->created_at->format('m');

                $defaultRelativePath = "invoices/{$entreprise->id}/{$year}/{$entreprise->invoice_prefix}-{$invoice->number}.pdf";

                $relativePath = $defaultRelativePath;
                if (!empty($invoice->merged_pdf_path)) {
                    $relativePath = $invoice->merged_pdf_path;
                }

                // Check if we have PDF attachments to merge
                $mergedPdfBinary = null;
                if ($request && $request->hasFile('attachments')) {
                    $mergedPdfBinary = $this->mergeInvoiceWithAttachments($invoice, $entreprise, $relativePath, $request);
                }

                // Use merged PDF if available, otherwise use original
                $pdfBinary = $mergedPdfBinary;
                if ($pdfBinary === null && Storage::disk('public')->exists($relativePath)) {
                    $pdfBinary = Storage::disk('public')->get($relativePath);
                }

                if ($pdfBinary && is_string($pdfBinary)) {
                    $sizeBytes = strlen($pdfBinary);
                    $firstBytes = substr($pdfBinary, 0, 8);
                    \Log::info('Billit attach - file located', [
                        'path' => $relativePath,
                        'merged' => $mergedPdfBinary !== null,
                        'size_bytes' => $sizeBytes,
                        'first8' => bin2hex($firstBytes),
                    ]);

                    // Basic PDF validation: non-empty and starts with %PDF-
                    $looksPdf = $sizeBytes > 100 && strncmp($pdfBinary, "%PDF-", 5) === 0;
                    if ($looksPdf) {
                        $fileName = "{$entreprise->invoice_prefix}-{$yearForName}{$month}{$invoice->number}.pdf";
                        $base64 = base64_encode($pdfBinary);
                        \Log::info('Billit attach - base64 prepared', [
                            'length' => strlen($base64),
                            'merged' => $mergedPdfBinary !== null,
                        ]);
                        // Per Billit docs: to replace Billit's generated PDF, use OrderPDF
                        // Ref: https://docs.billit.be/docs/include-your-own-pdf-and-attachments
                        $payload['OrderPDF'] = [
                            'FileName' => $fileName,
                            'FileContent' => $base64,
                        ];
                    } else {
                        \Log::warning('Billit attach - PDF validation failed', [
                            'path' => $relativePath,
                            'size_bytes' => $sizeBytes,
                            'starts_with' => substr($pdfBinary, 0, 5),
                        ]);
                    }
                } else {
                    \Log::warning('Billit attach - file not found or could not be read', [
                        'path' => $relativePath,
                        'has_merged' => $mergedPdfBinary !== null,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Could not attach PDF to Billit payload', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Process non-PDF attachments (Excel, CSV, images) and add to OrderAttachments
        if ($request) {
            $this->processAttachments($request, $payload);
        }

        return $payload;
    }

    /**
     * Merge invoice PDF with PDF attachments using Fpdi
     * Returns the merged PDF binary content, or null if no PDFs to merge
     */
    protected function mergeInvoiceWithAttachments($invoice, $entreprise, $invoicePath, Request $request): ?string
    {
        try {
            // Check if invoice PDF exists
            if (!Storage::disk('public')->exists($invoicePath)) {
                \Log::warning('Billit merge - invoice PDF not found', ['path' => $invoicePath]);
                return null;
            }

            // Get PDF attachments
            $files = $request->file('attachments');
            if (!is_array($files)) {
                $files = [$files];
            }

            $pdfFiles = [];
            foreach ($files as $file) {
                if ($file && $file->isValid() && $file->getClientMimeType() === 'application/pdf') {
                    $pdfFiles[] = $file;
                }
            }

            // If no PDF attachments, return null (use original invoice PDF)
            if (empty($pdfFiles)) {
                return null;
            }

            // Create temporary directory for PDF processing
            $tempDir = storage_path('app/temp/');
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            // Download main invoice to temp directory
            $mainInvoicePath = $tempDir . 'main_invoice_' . uniqid() . '.pdf';
            file_put_contents($mainInvoicePath, Storage::disk('public')->get($invoicePath));

            // Initialize PDF merger
            $pdf = new Fpdi();
            
            // Add main invoice to merged PDF
            $pageCount = $pdf->setSourceFile($mainInvoicePath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $template = $pdf->importPage($i);
                $pdf->AddPage();
                $pdf->useTemplate($template);
            }

            // Add PDF attachments
            $tempFiles = [$mainInvoicePath];
            foreach ($pdfFiles as $file) {
                try {
                    $tempPath = $tempDir . 'attachment_' . uniqid() . '.pdf';
                    move_uploaded_file($file->getPathname(), $tempPath);
                    $tempFiles[] = $tempPath;
                    
                    // Add pages from attachment PDF
                    $pageCount = $pdf->setSourceFile($tempPath);
                    for ($i = 1; $i <= $pageCount; $i++) {
                        $template = $pdf->importPage($i);
                        $pdf->AddPage();
                        $pdf->useTemplate($template);
                    }
                    
                    \Log::info('Billit merge - PDF attachment added', [
                        'file' => $file->getClientOriginalName(),
                        'pages' => $pageCount,
                    ]);
                } catch (\Throwable $e) {
                    \Log::warning('Billit merge - failed to add PDF attachment', [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Get merged PDF content
            $mergedPdfContent = $pdf->Output('S');

            // Clean up temp files
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }

            \Log::info('Billit merge - PDFs merged successfully', [
                'invoice_path' => $invoicePath,
                'attachments_count' => count($pdfFiles),
                'merged_size' => strlen($mergedPdfContent),
            ]);

            return $mergedPdfContent;
        } catch (\Throwable $e) {
            \Log::error('Billit merge - error merging PDFs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Process uploaded non-PDF attachments and add them to the Billit order payload
     * PDFs are handled separately by merging with the invoice PDF
     * Supported file types: Excel (.xlsx, .xls), CSV, JPEG, PNG
     */
    protected function processAttachments(?Request $request, array &$orderData): void
    {
        if (!$request || !$request->hasFile('attachments')) {
            return;
        }

        $files = $request->file('attachments');
        if (!is_array($files)) {
            $files = [$files];
        }

        $allowedMimeTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-excel', // .xls
            'text/csv',
            'text/plain', // CSV can also be text/plain
            'image/jpeg',
            'image/jpg',
            'image/png',
        ];

        $allowedExtensions = ['xlsx', 'xls', 'csv', 'jpg', 'jpeg', 'png'];
        $maxFileSize = 10 * 1024 * 1024; // 10MB

        $attachments = [];

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                \Log::warning('Billit attachment - invalid file', [
                    'file' => $file ? $file->getClientOriginalName() : 'null',
                ]);
                continue;
            }

            // Skip PDF files (they are merged with invoice PDF)
            if ($file->getClientMimeType() === 'application/pdf') {
                continue;
            }

            // Validate file size
            if ($file->getSize() > $maxFileSize) {
                \Log::warning('Billit attachment - file too large', [
                    'file' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'max' => $maxFileSize,
                ]);
                continue;
            }

            // Validate file type
            $mimeType = $file->getMimeType();
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($mimeType, $allowedMimeTypes) && !in_array($extension, $allowedExtensions)) {
                \Log::warning('Billit attachment - unsupported file type', [
                    'file' => $file->getClientOriginalName(),
                    'mime' => $mimeType,
                    'extension' => $extension,
                ]);
                continue;
            }

            try {
                $fileContent = file_get_contents($file->getRealPath());
                if ($fileContent === false) {
                    \Log::warning('Billit attachment - could not read file', [
                        'file' => $file->getClientOriginalName(),
                    ]);
                    continue;
                }

                $base64Content = base64_encode($fileContent);
                $fileName = $file->getClientOriginalName();

                $attachments[] = [
                    'FileName' => $fileName,
                    'FileContent' => $base64Content,
                ];

                \Log::info('Billit attachment - file processed', [
                    'file' => $fileName,
                    'size' => strlen($fileContent),
                    'base64_length' => strlen($base64Content),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Billit attachment - processing error', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($attachments)) {
            $orderData['OrderAttachments'] = $attachments;
            \Log::info('Billit attachments - added to payload', [
                'count' => count($attachments),
            ]);
        }
    }

    protected function sendInvoicePdfToEmail($invoice, $entreprise, string $email): void
    {
        try {
            if (!$entreprise || !$invoice || !isset($invoice->created_at, $invoice->number)) return;

            $year = $invoice->created_at->format('Y');
            $yearForName = $invoice->created_at->format('y');
            $month = $invoice->created_at->format('m');
            $defaultRelativePath = "invoices/{$entreprise->id}/{$year}/{$entreprise->invoice_prefix}-{$invoice->number}.pdf";
            $relativePath = !empty($invoice->merged_pdf_path) ? $invoice->merged_pdf_path : $defaultRelativePath;

            if (!Storage::disk('public')->exists($relativePath)) {
                \Log::warning('WinAuditor forward - PDF not found', ['path' => $relativePath]);
                return;
            }

            $binary = Storage::disk('public')->get($relativePath);
            if (!is_string($binary) || strlen($binary) < 100 || strncmp($binary, "%PDF-", 5) !== 0) {
                \Log::warning('WinAuditor forward - invalid PDF content', ['path' => $relativePath]);
                return;
            }

            $fileName = "{$entreprise->invoice_prefix}-{$yearForName}{$month}{$invoice->number}.pdf";

            $subject = 'Invoice ' . $fileName;
            $body = 'Please find attached the invoice PDF for import into WinAuditor.';

            Mail::raw($body, function ($message) use ($email, $binary, $fileName, $entreprise) {
                \App\Mail\CompanySender::apply($message, $entreprise);
                $message->to($email)
                        ->subject($fileName)
                        ->attachData($binary, $fileName, ['mime' => 'application/pdf']);
            });

            \Log::info('WinAuditor forward - email sent', ['to' => $email, 'file' => $fileName]);
        } catch (\Throwable $e) {
            \Log::error('WinAuditor forward - failed', ['error' => $e->getMessage()]);
        }
    }
} 
