<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archives des Factures - BizManager</title>
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- PUISQUE CETTE PAGE UTILISE LES MODALS BOOTSTRAP, LE CSS BOOTSTRAP EST REQUIS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Styles personnalisés pour les selects Tailwind */
        select.form-select-custom {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        /* Style pour les lignes de note de crédit */
        .credit-note-row {
            background-color: #fffbeb; /* bg-yellow-50 */
        }
        .credit-note-text {
            color: #d97706; /* text-yellow-600 */
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Contenu Principal --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">

            <div class="w-full max-w-7xl mx-auto"> {{-- Utilisation de max-w-7xl pour une page large --}}

                {{-- Message de session (style Tailwind) --}}
                @if(session('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Succès !</strong>
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
                </div>
                @endif
                
                {{-- Déterminer les routes en premier --}}
                @php
                    $returnRoute = '';
                    if(Auth::user()->hasRole('Admin')) {
                        $returnRoute = route('admin.invoices.invoicehistory');
                    } elseif(Auth::user()->hasRole('Responsable de projet')) {
                        $returnRoute = route('Responsable_de_projet.invoices.invoicehistory');
                    } elseif(Auth::user()->hasRole('Employee')) {
                        $returnRoute = route('employee.invoices.invoicehistory');
                    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $returnRoute = route('Responsable_gestionnaire.invoices.invoicehistory');
                    }
                    
                    $totalPaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('Payment_status', 1)->where('archived', 1)->sum('total_ttc');
                    $totaltva = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('archived', 1)->sum('total_tva');
                @endphp

                {{-- Carte de la Table (style Tailwind) --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    
                    {{-- En-tête de la Carte --}}
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Tableau des Archives de Factures</h2>
                            </div>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                {{-- Infos Total --}}
                                <div class="flex items-center gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-700">Total Payé :</span>
                                        <span class="font-bold text-green-600">€{{ number_format($totalPaid, 2, ',', ' ') }}</span>
                                    </div>
                                    <span class="text-gray-300">|</span>
                                    <div>
                                        <span class="font-medium text-gray-700">Total TVA :</span>
                                        <span class="font-bold text-gray-800">€{{ number_format($totaltva, 2, ',', ' ') }}</span>
                                    </div>
                                </div>
                                {{-- Bouton Retour --}}
                                <a type="button" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm" href="{{ $returnRoute }}">
                                    Retour
                                </a>
                            </div>
                        </div>
                        
                        {{-- Filtres (style Tailwind) --}}
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="clientFilter" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par Client :</label>
                                <select id="clientFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                    <option value="">Tous les Clients</option>
                                    @foreach($clients as $client)
                                    <option value="{{$client->name}}">{{$client->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="projectFilter" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par Projet :</label>
                                <select id="projectFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                    <option value="">Tous les Projets</option>
                                    @foreach($projects as $project)
                                    <option value="{{$project->name}}">{{strtoupper($project->name)}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="paymentFilter" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par Paiement :</label>
                                <select id="paymentFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                    <option value="">Toutes les Factures</option>
                                    <option value="Paid">Payé</option>
                                    <option value="Unpaid">Impayé</option> {{-- Note: Probablement inutile pour les archives --}}
                                </select>
                            </div>
                            <div>
                                <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par Type :</label>
                                <select id="typeFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom">
                                    <option value="">Tous les Types</option>
                                    <option value="Partial Period">Période Partielle</option>
                                    <option value="Full Period">Période Complète</option>
                                    {{-- Ajoutez d'autres types si nécessaire --}}
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Tableau (style Tailwind) --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="invoicesTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facture N°</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom Projet</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TVA</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type Facture</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créée le</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coût Total</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoices as $invoice)
                                <tr @if($invoice->Payment_status == -1) class="credit-note-row" @endif>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @php
                                            $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                                            $today = \Carbon\Carbon::now();
                                            $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
                                            $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
                                            
                                            $invoiceRoute = '#';
                                            if(Auth::user()->hasRole('Responsable de projet')) $invoiceRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            elseif(Auth::user()->hasRole('Admin')) $invoiceRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            elseif(Auth::user()->hasRole('Employee')) $invoiceRoute = route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $invoiceRoute = route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                        @endphp
                                        <a href="{{ $invoiceRoute }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{strtoupper($invoice->project_name)}}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @php $project = App\Models\Project::find($invoice->project_id); @endphp
                                        {{$project->tva ?? 'N/A'}}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @php $client = \App\Models\Client::where('id', $invoice->client_id)->first(); @endphp
                                        {{$client->name ?? 'N/A'}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 capitalize">
                                        @if($invoice->type == 'Partial') {{$invoice->type}}-{{$invoice->partial}}
                                        @elseif($invoice->type == 'Percentage') {{$invoice->type}}-{{$invoice->percentage}}
                                        @else {{$invoice->type}}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{$invoice->created_at}}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                        €<span id="totalCostValue">{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                        {{-- Logique de bouton consolidée --}}
                                        @php
                                            $isPaid = $invoice->Payment_status == 1;
                                            $isCreditNote = $invoice->Payment_status == -1;
                                            $deactivateRoute = '';
                                            $activateRoute = '';
                                            $sendRoute = route('admin.invoice.sendInvoice', $invoice->id); // Fallback, ajustez si nécessaire
                                            $reminderRouteBase = '';
                                            $creditNoteRoute = '';
                                            $detailedCreditNoteRoute = '';

                                            if(Auth::user()->hasRole('Admin')) {
                                                $deactivateRoute = route('admin.invoice.deactivate', $invoice->id);
                                                $activateRoute = route('admin.invoice.activate', $invoice->id);
                                                $reminderRouteBase = 'admin.invoice.sendManualremind';
                                                $creditNoteRoute = 'admin.invoice.creditNote';
                                                $detailedCreditNoteRoute = 'admin.invoice.detailedCreditNote';
                                            } elseif(Auth::user()->hasRole('Responsable de projet')) {
                                                $deactivateRoute = route('Responsable_de_projet.invoice.deactivate', $invoice->id);
                                                $activateRoute = route('Responsable_de_projet.invoice.activate', $invoice->id);
                                                $reminderRouteBase = 'Responsable_de_projet.invoice.sendManualremind';
                                                $creditNoteRoute = 'Responsable_de_projet.invoice.creditNote';
                                                $detailedCreditNoteRoute = 'Responsable_de_projet.invoice.detailedCreditNote';
                                            } elseif(Auth::user()->hasRole('Employee')) {
                                                $deactivateRoute = route('employee.invoice.deactivate', $invoice->id);
                                                $activateRoute = route('employee.invoice.activate', $invoice->id);
                                                $reminderRouteBase = 'employee.invoice.sendManualremind';
                                                $creditNoteRoute = 'employee.invoice.creditNote';
                                                $detailedCreditNoteRoute = 'employee.invoice.detailedCreditNote';
                                            } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                $deactivateRoute = route('Responsable_gestionnaire.invoice.deactivate', $invoice->id);
                                                $activateRoute = route('Responsable_gestionnaire.invoice.activate', $invoice->id);
                                                $reminderRouteBase = 'Responsable_gestionnaire.invoice.sendManualremind';
                                                $creditNoteRoute = 'Responsable_gestionnaire.invoice.creditNote';
                                                $detailedCreditNoteRoute = 'Responsable_gestionnaire.invoice.detailedCreditNote';
                                            }
                                        @endphp
                                        
                                        {{-- Bouton Payé/Impayé (Style Tailwind) --}}
                                        @if($isCreditNote)
                                            <span class="credit-note-text px-3 py-1 text-xs font-semibold rounded-full">Note de Crédit</span>
                                        @elseif($isPaid)
                                            <form action="{{ $deactivateRoute }}" method="post" onsubmit="return confirm('Êtes-vous sûr ?');" class="inline-block">
                                                @csrf
                                                <button type="submit" class="payment-button px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium">
                                                    Payé
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ $activateRoute }}" method="post" onsubmit="return confirm('Êtes-vous sûr ?');" class="inline-block">
                                                @csrf
                                                <button type="submit" class="payment-button px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium">
                                                    Impayé
                                                </button>
                                            </form>
                                        @endif
                                        
                                        {{-- Bouton Envoyer (pour modal Bootstrap) --}}
                                        <button type="button" class="px-3 py-1 bg-gray-700 hover:bg-gray-800 text-white rounded-lg text-xs font-medium"
                                                data-bs-toggle="modal" data-bs-target="#sendInvoiceModal"
                                                data-invoice-id="{{ $invoice->id }}">
                                            {{ $invoice->sentInvoice == 0 ? 'Envoyer' : 'Renvoyer' }}
                                        </button>

                                        {{-- Menu Rappel (géré par Alpine.js + Tailwind) --}}
@if(!$isPaid && !$isCreditNote)
    @can('Send invoice')
    <div class="inline-block relative" x-data="{ open: false }" @click.away="open = false">
        <button @click="open = !open" 
                class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-xs font-medium" 
                type="button" aria-haspopup="true" :aria-expanded="open.toString()">
            Rappel
            <svg class="inline-block w-4 h-4 ml-1 -mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        </button>
        
        <ul x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            {{-- CORRECTION : Ouvre vers le HAUT pour éviter d'être coupé --}}
            class="absolute z-10 right-0 bottom-full mb-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            style="display: none;" 
            role="menu" aria-orientation="vertical">
            
            <li role="presentation">
                <a href="{{ route($reminderRouteBase, ['id' => $invoice->id, 'reminder' => 1]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Envoyer Rappel 1</a>
            </li>
            <li role="presentation">
                <a href="{{ route($reminderRouteBase, ['id' => $invoice->id, 'reminder' => 2]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Envoyer Rappel 2</a>
            </li>
            <li role="presentation">
                <a href="{{ route($reminderRouteBase, ['id' => $invoice->id, 'reminder' => 3]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Envoyer Rappel 3</a>
            </li>
        </ul>
    </div>
    @endcan
@endif

{{-- Menu Note de Crédit (géré par Alpine.js + Tailwind) --}}
<div class="inline-block relative" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open" 
            class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-medium" 
            type="button" aria-haspopup="true" :aria-expanded="open.toString()">
        Note de Crédit
        <svg class="inline-block w-4 h-4 ml-1 -mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
    </button>

    <ul x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        {{-- CORRECTION : Ouvre vers le HAUT pour éviter d'être coupé --}}
        class="absolute z-10 right-0 bottom-full mb-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        style="display: none;" 
        role="menu" aria-orientation="vertical">

        @if($invoice->type == 'Detailed')
            <li role="presentation">
                <a href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Complète</a>
            </li>
        @else
            <li role="presentation">
                <a href="{{ route($creditNoteRoute, $invoice->id) }}?type=full" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Complète</a>
            </li>
        @endif
    </ul>
</div>

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div> 

            </div>
        </main>
    </div>

    {{-- MODALS BOOTSTRAP (Conservés pour la fonctionnalité) --}}
    
    <div class="modal fade" id="partialCreditNoteModal" tabindex="-1" aria-labelledby="partialCreditNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="partialCreditNoteModalLabel">Note de Crédit Partielle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="partialCreditNoteForm">
                        <div class="mb-3">
                            <label for="partialAmount" class="form-label">Entrer le montant :</label>
                            <input type="number" class="form-control" id="partialAmount" name="partialAmount" step="0.0001" required>
                            <span id="error-message" style="color: red; display: none;">Le montant dépasse le coût total.</span>
                        </div>
                        <input type="hidden" id="invoiceId" name="invoiceId">
                        <input type="hidden" id="totalCost" name="totalCost">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" onclick="submitPartialCreditNote()">Soumettre</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="sendInvoiceModal" tabindex="-1"
        aria-labelledby="sendInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendInvoiceModalLabel">Envoyer la Facture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="sendInvoiceForm" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="fileUpload" class="form-label">Télécharger un fichier (optionnel) :</label>
                            <input type="file" class="form-control" id="fileUpload" name="file[]" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- FIN DES MODALS --}}


    @livewireScripts
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    {{-- Bootstrap JS est REQUIS pour les Modals et Dropdowns --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('dashboard_layouts.scripts') {{-- Gardez ceci si cela inclut Alpine.js ou d'autres scripts nécessaires --}}
    <script src="//unpkg.com/alpinejs" defer></script>

<script>
// Fonction pour afficher le modal de note de crédit partielle
function showPartialPopup(invoiceId, totalCost) {
    document.getElementById('invoiceId').value = invoiceId;
    document.getElementById('totalCost').value = totalCost;
    var modal = new bootstrap.Modal(document.getElementById('partialCreditNoteModal'));
    modal.show();
}

// Fonction pour soumettre la note de crédit partielle
function submitPartialCreditNote() {
    var form = document.getElementById('partialCreditNoteForm');
    var amount = parseFloat(document.getElementById('partialAmount').value.replace(',', '.'));
    var invoiceId = document.getElementById('invoiceId').value;
    var totalCost = parseFloat(document.getElementById('totalCost').value.replace(',', '.'));
    var errorMessage = document.getElementById('error-message');

    if (amount > totalCost) {
        errorMessage.style.display = 'block';
        return;
    } else {
        errorMessage.style.display = 'none';
    }

    var url = '';
    @if(Auth::user()->hasRole('Responsable de projet'))
        var url = `{{ route('Responsable_de_projet.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
    @elseif(Auth::user()->hasRole('Admin'))
        var url = `{{ route('admin.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
    @elseif(Auth::user()->hasRole('Employee'))
        var url = `{{ route('employee.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
        var url = `{{ route('Responsable_gestionnaire.invoice.creditNote', '') }}/${invoiceId}?type=partial&amount=${amount}`;
    @endif

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.text())
    .then(html => {
        document.open();
        document.write(html);
        document.close();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request: ' + error.message);
    });
}
</script>

<script>
// JavaScript pour filtrer le tableau
document.addEventListener("DOMContentLoaded", function() {
    const clientFilter = document.getElementById('clientFilter');
    const projectFilter = document.getElementById('projectFilter');
    const paymentFilter = document.getElementById('paymentFilter');
    const typeFilter = document.getElementById('typeFilter');

    clientFilter.addEventListener('change', filterTable);
    projectFilter.addEventListener('change', filterTable);
    paymentFilter.addEventListener('change', filterTable);
    typeFilter.addEventListener('change', filterTable);

    function filterTable() {
        const clientValue = clientFilter.value.toLowerCase();
        const projectValue = projectFilter.value.toLowerCase();
        const paymentValue = paymentFilter.value.toLowerCase(); // Changé en minuscule
        const typeValue = typeFilter.value.toLowerCase();

        const rows = document.querySelectorAll('#invoicesTable tbody tr');
        rows.forEach(row => {
            const clientName = row.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
            const projectName = row.querySelector('td:nth-child(2)').textContent.trim().toLowerCase();
            
            // Logique de statut de paiement mise à jour
            let paymentStatus = '';
            const paymentButton = row.querySelector('.payment-button');
            if (paymentButton) {
                paymentStatus = paymentButton.textContent.trim().toLowerCase();
            } else if (row.querySelector('.credit-note-text')) {
                // Vous pouvez définir un statut spécifique si vous voulez filtrer les notes de crédit
                paymentStatus = 'credit note'; 
            }

            const invoiceType = row.querySelector('td:nth-child(5)').textContent.trim().toLowerCase(); // Index de colonne corrigé

            // Logique de filtrage
            const clientMatch = clientValue === '' || clientName.includes(clientValue);
            const projectMatch = projectValue === '' || projectName.includes(projectValue);
            const paymentMatch = paymentValue === '' || paymentStatus.includes(paymentValue);
            const typeMatch = typeValue === '' || invoiceType.includes(typeValue); // Utilise 'includes' pour les types partiels

            if (clientMatch && projectMatch && paymentMatch && typeMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
});
</script>

<script>
    // Script pour le modal "Send Invoice"
    document.addEventListener('DOMContentLoaded', function () {
        var sendInvoiceModal = document.getElementById('sendInvoiceModal');
        sendInvoiceModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var invoiceId = button.getAttribute('data-invoice-id');
            var form = document.getElementById('sendInvoiceForm');
            
            // Définir l'action du formulaire en fonction du rôle
            var actionUrl = '';
            @if(Auth::user()->hasRole('Admin'))
                actionUrl = '{{ route("admin.invoice.sendInvoice", ":invoice_id") }}';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                actionUrl = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":invoice_id") }}';
            @elseif(Auth::user()->hasRole('Employee'))
                actionUrl = '{{ route("employee.invoice.sendInvoice", ":invoice_id") }}';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                actionUrl = '{{ route("Responsable_gestionnaire.invoice.sendInvoice", ":invoice_id") }}';
            @endif
            
            form.action = actionUrl.replace(':invoice_id', invoiceId);
        });
    });
</script>

</body>
</html>