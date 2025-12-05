<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Services\BillitService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class BillitController extends Controller
{
    protected $billitService;

    public function __construct(BillitService $billitService)
    {
        $this->billitService = $billitService;
    }

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
            $response = $this->billitService->sendInvoice($invoiceData);
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
       
        if (!$client) {
            return response()->json([
                'success' => false, 
                'message' => 'Client not found for invoice',
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
            return response()->json(['success' => false, 'message' => 'Enterprise not found for invoice'], 404);
        }

        $orderData = $this->mapInvoiceToBillitPayload($invoice, $client, $project, $entreprise);

        // Optional: force a preferred PEPPOL identifier via query (?preferred_identifier=9925:BE0823758741)
        $preferredIdentifier = trim((string)$request->query('preferred_identifier', ''));
        if ($preferredIdentifier !== '') {
            if (preg_match('/^(\d{4}):([\w\d]+)$/', $preferredIdentifier, $m)) {
                $scheme = $m[1];
                $value = $m[2];
                // Force ReceiverEndpointID (PEPPOL)
                $orderData['ReceiverEndpointID'] = [
                    'schemeID' => $scheme,
                    'value' => $value,
                ];
                // Add preference hint (both casings for forward-compatibility)
                $orderData['ElectronicInvoicing'] = [ 'PreferredIdentifier' => $preferredIdentifier ];
                $orderData['electronicInvoicing'] = [ 'preferredIdentifier' => $preferredIdentifier ];
            } else {
                \Log::warning('Preferred identifier has unexpected format; expected scheme:value', [ 'preferred_identifier' => $preferredIdentifier ]);
            }
        }
        try {
            $channel = $request->query('channel'); // e.g., 'Email' to force email sending
            $response = $this->billitService->sendInvoiceViaPeppol($orderData, $channel);

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

            return response()->json(['success' => true, 'billit_response' => $response]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
        return back()->with('error', 'Erreur : ' . $e->getMessage());
    }

    if (!$client) {
        return back()->with('error', 'Client introuvable pour cette facture.');
    }

    $project = null;
    if (property_exists($invoice, 'project_id') && $invoice->project_id) {
        $project = \App\Models\Project::find($invoice->project_id);
    }

    $entreprise = \App\Models\Entreprise::find($invoice->entreprise_id);
    if (!$entreprise) {
        return back()->with('error', 'Entreprise introuvable pour cette facture.');
    }

    $orderData = $this->mapInvoiceToBillitPayload($invoice, $client, $project, $entreprise, $type);

    // Optional PEPPOL preferred ID
    $preferredIdentifier = trim((string)$request->query('preferred_identifier', ''));
    if ($preferredIdentifier !== '') {
        if (preg_match('/^(\d{4}):([\w\d]+)$/', $preferredIdentifier, $m)) {
            $orderData['ReceiverEndpointID'] = [
                'schemeID' => $m[1],
                'value' => $m[2],
            ];
            $orderData['ElectronicInvoicing'] = [ 'PreferredIdentifier' => $preferredIdentifier ];
            $orderData['electronicInvoicing'] = [ 'preferredIdentifier' => $preferredIdentifier ];
        }
    }

    try {
        $channel = $request->query('channel');
        $response = $this->billitService->sendInvoiceViaPeppol($orderData, $channel);

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

        return back()->with('success', 'Facture envoyée avec succès via Billit (' . $type . ')');

    } catch (\Exception $e) {
        return back()->with('error', 'Échec d’envoi : ' . $e->getMessage());
    }
}


    public function listInvoicesByType(Request $request, $type): JsonResponse
    {
        $type = strtolower((string) $type);
        $enterpriseId = auth()->user() ? auth()->user()->entreprise_id : null;
        if (!$enterpriseId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
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
    protected function mapInvoiceToBillitPayload($invoice, $client, $project = null, $entreprise = null, $type = null)
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
                $hours = round($minutes / 60, 2);
                if ($project && isset($project->cost_per_hour) && (float)$project->cost_per_hour > 0 && $hours > 0) {
                    $quantity = $hours;
                    $unitPrice = (float)$project->cost_per_hour;
                } elseif ($hours > 0) {
                    $quantity = $hours;
                    $unitPrice = round($baseAmount / max($hours, 1), 2);
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

                if (Storage::disk('public')->exists($relativePath)) {
                    $binary = Storage::disk('public')->get($relativePath);
                    $sizeBytes = is_string($binary) ? strlen($binary) : 0;
                    $firstBytes = is_string($binary) ? substr($binary, 0, 8) : '';
                    \Log::info('Billit attach - file located', [
                        'path' => $relativePath,
                        'size_bytes' => $sizeBytes,
                        'first8' => bin2hex((string)$firstBytes),
                    ]);

                    // Basic PDF validation: non-empty and starts with %PDF-
                    $looksPdf = is_string($binary) && $sizeBytes > 100 && strncmp($binary, "%PDF-", 5) === 0;
                    if ($looksPdf) {
                        $fileName = "{$entreprise->invoice_prefix}-{$yearForName}{$month}{$invoice->number}.pdf";
                        $base64 = base64_encode($binary);
                        \Log::info('Billit attach - base64 prepared', [
                            'length' => strlen($base64),
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
                            'starts_with' => is_string($binary) ? substr($binary, 0, 5) : null,
                        ]);
                    }
                } else {
                    \Log::warning('Billit attach - file not found on do_spaces', [
                        'path' => $relativePath,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Could not attach PDF to Billit payload', [
                'error' => $e->getMessage(),
            ]);
        }

        return $payload;
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

            Mail::raw($body, function ($message) use ($email, $binary, $fileName) {
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
