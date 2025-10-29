<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Parameters - BizManager</title>
    @vite('resources/css/app.css') {{-- Ensure Tailwind is loaded --}}
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden font-sans"> {{-- Changed bg-gray-50 to bg-gray-100 --}}

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-8"> {{-- Changed bg-gray-50 & padding --}}

             {{-- 1. Header Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Invoice Parameters</h1>
                    <p class="text-sm text-gray-600">Configure invoice settings and templates</p>
                </div>
            </div>

            {{-- Determine Form Action URL based on Role --}}
            @php
                $updateUrl = ''; // Default empty
                if(Auth::user()->hasRole('Responsable de projet')) $updateUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos'; // Use route() helper ideally
                elseif(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('SuperAdmin')) $updateUrl = '/admin/Enterprise_infos/updateInfos'; // Use route() helper ideally
                elseif(Auth::user()->hasRole('Employee')) $updateUrl = '/employee/Enterprise_infos/updateInfos'; // Use route() helper ideally
                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $updateUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos'; // Use route() helper ideally

                // It's better to use route() helper if routes are named, e.g., route('admin.enterprise.update')
            @endphp

            {{-- Form encompassing all settings cards --}}
            <form action="{{ $updateUrl }}" method="POST" class="space-y-8">
                @csrf
                {{-- @method('PUT') --}}

                {{-- 2. Invoice Information Card --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5">
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900">Invoice Information</h5>
                        <p class="text-sm text-gray-500 mt-1">Mandatory information for all invoices</p>
                    </div>

                    {{-- Invoice Prefix --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="invoice_prefix" class="block text-sm font-medium text-gray-700 mb-1">Invoice Prefix</label>
                            <input
                                id="invoice_prefix"
                                class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" {{-- Removed readonly and bg-gray --}}
                                type="text" name="invoice_prefix"
                                value="{{ $entreprise->invoice_prefix ?? '' }}" />
                             <p class="mt-1 text-xs text-gray-500">Used as prefix for all invoice numbers</p>
                        </div>
                    </div>

                    {{-- General Conditions --}}
                    <div>
                         <label for="general_conditions" class="block text-sm font-medium text-gray-700 mb-1">General Conditions</label>
                         <textarea
                            id="general_conditions"
                            class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" {{-- Removed readonly and bg-gray --}}
                            name="general_conditions" rows="5">{{ $entreprise->general_conditions ?? '' }}</textarea> 
                         <p class="mt-1 text-xs text-gray-500">Displayed at the bottom of invoices</p>
                    </div>
                </div>

                {{-- 3. Invoice Body Message Card --}}
                 <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5">
                     <div>
                        <h5 class="text-lg font-semibold text-gray-900">Invoice Body Message</h5>
                        <p class="text-sm text-gray-500 mt-1">Message sent with invoice emails</p>
                    </div>
                     <div>
                        <label for="invoice_body" class="block text-sm font-medium text-gray-700 mb-1">Message Content</label>
                        <textarea
                            id="invoice_body"
                            class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" {{-- Removed readonly and bg-gray --}}
                            name="invoice_body" rows="4">{{ $entreprise->invoice_body ?? '' }}</textarea> 
                        <p class="mt-1 text-xs text-gray-500">This message will be included in invoice emails</p>
                    </div>
                 </div>

                {{-- 4. Reminder Messages Card --}}
                 <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5">
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900">Reminder Messages</h5>
                        <p class="text-sm text-gray-500 mt-1">Customize reminder emails for unpaid invoices</p>
                    </div>
                    {{-- First Reminder --}}
                    <div>
                        <label for="first_reminder" class="block text-sm font-medium text-gray-700 mb-1">First Reminder</label>
                        <textarea
                            id="first_reminder"
                            class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" {{-- Removed readonly and bg-gray --}}
                            name="first_reminder" rows="4">{{ $entreprise->first_reminder ?? '' }}</textarea> 
                        <p class="mt-1 text-xs text-gray-500">Sent 5 days after invoice due date</p> 
                    </div>
                     {{-- Second Reminder --}}
                    <div>
                        <label for="second_reminder" class="block text-sm font-medium text-gray-700 mb-1">Second Reminder</label>
                        <textarea
                            id="second_reminder"
                            class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" {{-- Removed readonly and bg-gray --}}
                            name="second_reminder" rows="4">{{ $entreprise->second_reminder ?? '' }}</textarea> 
                         <p class="mt-1 text-xs text-gray-500">Sent 10 days after invoice due date</p> 
                    </div>
                    {{-- Third Reminder --}}
                    <div>
                        <label for="third_reminder" class="block text-sm font-medium text-gray-700 mb-1">Third Reminder</label>
                        <textarea
                            id="third_reminder"
                            class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" {{-- Removed readonly and bg-gray --}}
                            name="third_reminder" rows="4">{{ $entreprise->third_reminder ?? '' }}</textarea> 
                         <p class="mt-1 text-xs text-gray-500">Sent 15 days after invoice due date</p> 
                    </div>
                 </div>

                {{-- 5. Footer Buttons --}}
                <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                    {{-- Back Button --}}
                    @php
                        $backRoute = route('admin.index'); // Default
                        if (Auth::user()->hasRole('Responsable de projet')) $backRoute = route('Responsable_de_projet.index');
                        elseif (Auth::user()->hasRole('Employee')) $backRoute = route('employee.index');
                        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $backRoute = route('Responsable_gestionnaire.index');
                    @endphp
                    <a href="{{ $backRoute }}" class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                        {{-- Left Arrow Icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>

                     {{-- Save Button --}}
                    <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm disabled:opacity-50" {{ !$updateUrl ? 'disabled' : '' }}>
                         {{-- Save Icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Save All Changes
                    </button>
                </div>

            </form>

        </main>
    </div> {{-- End Main Wrapper --}}

    @livewireScripts
    {{-- Removed old jQuery and AJAX script --}}
    {{-- Include AlpineJS if needed for other components --}}
    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}

</body>
</html>