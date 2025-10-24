<?php

namespace App\Http\Controllers;

use App\Services\BillitService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
} 