<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workdays Report Preview - DalyTrac</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden font-sans" x-data>

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper --}}
    <div class="flex flex-col flex-1 overflow-y-auto lg:ml-55">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
            
            <div class="max-w-7xl mx-auto">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 flex flex-col h-full">
                            <div class="p-6 border-b border-gray-200">
                                <h4 class="text-lg font-semibold text-gray-900">Workdays Report Preview</h4>
                            </div>
                            <div class="p-6 flex-1">
                                <div class="w-full h-full border border-gray-200 rounded-lg overflow-hidden bg-gray-100" style="min-height: 600px;">
                                    <iframe src="{{ route('admin.workDaysReports.pdf', ['year' => $year, 'month' => $month, 'employees' => $employees, 'excluded_columns' => $excludedColumns]) }}" 
                                            class="w-full h-full" 
                                            style="height: 600px; border: none;">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Email Form --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200">
                                <h4 class="text-lg font-semibold text-gray-900">Email Options</h4>
                            </div>
                            <div class="p-6">
                                <form action="{{ route('admin.workDaysReports.sendEmail') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    {{-- Hidden Inputs (Logic Preserved) --}}
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="employees" value="{{ $employees }}">
                                    <input type="hidden" name="excluded_columns" value="{{ $excludedColumns }}">
                                    
                                    {{-- Recipients --}}
                                    <div>
                                        <label for="emails" class="block text-sm font-medium text-gray-700 mb-1">Recipients</label>
                                        <textarea id="emails" name="emails" rows="3" 
                                                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                                  placeholder="Enter email addresses separated by commas">{{ $entreprise->workdays_emails }}</textarea>
                                        <p class="mt-1 text-xs text-gray-500">Separate multiple emails with commas.</p>
                                    </div>
                                    
                                    {{-- Subject --}}
                                    <div>
                                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Email Subject</label>
                                        <input type="text" id="subject" name="subject" 
                                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                               value="Workdays Report - {{ $monthName }} {{ $year }}">
                                    </div>
                                    
                                    {{-- Body --}}
                                    <div>
                                        <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Email Body</label>
                                        <textarea id="body" name="body" rows="6" 
                                                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">Objet : Envoi des informations pour l'établissement des fiches de paie
Bonjour,
Je vous transmets ci-joint les éléments nécessaires à l'établissement des fiches de paie.
Vous trouverez le Workdays Report en annexe.

N'hésitez pas à me contacter si vous avez besoin de précisions ou de documents complémentaires.
Merci d'avance pour votre retour.

Bien cordialement</textarea>
                                    </div>

                                    {{-- Attachments --}}
                                    <div>
                                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">Additional Attachments (optional)</label>
                                        <input type="file" id="attachments" name="attachments[]" multiple
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        <p class="mt-1 text-xs text-gray-500">Select multiple files if needed.</p>
                                    </div>
                                    
                                    {{-- Save Emails Checkbox --}}
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="saveEmails" name="saveEmails" type="checkbox" value="1" checked 
                                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="saveEmails" class="font-medium text-gray-700">Save emails</label>
                                            <p class="text-gray-500">Save these addresses for future reports.</p>
                                        </div>
                                    </div>
                                    
                                    {{-- Actions --}}
                                    <div class="pt-4 space-y-3">
                                        <button type="submit" class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            Send Email
                                        </button>
                                        
                                        <a href="{{ route('admin.workDaysReports.index') }}" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                            </svg>
                                            Back to Reports
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </main>
    </div>

  @include('dashboard_layouts.scripts')
</body>
</html>
