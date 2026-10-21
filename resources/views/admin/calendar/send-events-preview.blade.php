<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Send Calendar Events - DalyTrac') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 lg:p-8">
            
            <div class="max-w-7xl mx-auto">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {{-- Left Column: PDF Preview --}}
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 flex flex-col h-full">
                            <div class="p-6 border-b border-gray-200">
                                <h4 class="text-lg font-semibold text-gray-900">{{ __('messages.Calendar Events Preview') }} - {{ $monthName }}</h4>
                            </div>
                            <div class="p-6 flex-1">
                                <div class="w-full h-full border border-gray-200 rounded-lg overflow-hidden bg-gray-100" style="min-height: 600px;">
                                    <iframe src="{{ route('admin.calendar.events.pdf', ['events' => $eventIds, 'month' => $month, 'year' => $year]) }}" 
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
                                <h4 class="text-lg font-semibold text-gray-900">{{ __('messages.Email Options') }}</h4>
                            </div>
                            <div class="p-6">
                                <form id="sendEventsForm" action="{{ route('admin.calendar.send-events-email') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="events" value="{{ $eventIds }}">
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    
                                    {{-- Recipients --}}
                                    <div>
                                        <label for="recipients" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Recipients label') }} <span class="text-red-500">*</span></label>
                                        <textarea id="recipients" name="recipients" rows="3" 
                                                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                                  placeholder="{{ __('messages.Recipients placeholder') }}" required></textarea>
                                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.Separate emails helper') }}</p>
                                    </div>
                                    
                                    {{-- Subject --}}
                                    <div>
                                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Email Subject') }} <span class="text-red-500">*</span></label>
                                        <input type="text" id="subject" name="subject" 
                                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                               value="{{ __('messages.Calendar Events Subject Prefix') }} {{ $monthName }}" required>
                                    </div>
                                    
                                    {{-- Body --}}
                                    <div>
                                        <label for="body" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Email Body') }} <span class="text-red-500">*</span></label>
                                        <textarea id="body" name="body" rows="6" 
                                                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>{{ __('messages.Calendar Events default body', ['month' => $monthName]) }}</textarea>
                                    </div>
                                    
                                    {{-- Attachments --}}
                                    <div>
                                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Additional Attachments') }}</label>
                                        <input type="file" id="attachments" name="attachments[]" 
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
                                               multiple>
                                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.Calendar Attachments helper') }}</p>
                                    </div>
                                    
                                    {{-- Submit Button --}}
                                    <div class="pt-4">
                                        <button type="submit" 
                                                class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('messages.Send email button') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#sendEventsForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                
                // Disable button and show loading
                submitBtn.prop('disabled', true);
                submitBtn.html('<svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Sending...');
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        alert(@js(__('messages.Email sent successfully!')));
                        window.location.href = '{{ route("admin.calendar.index") }}';
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON?.error || 'Failed to send email. Please try again.';
                        alert(errorMessage);
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalText);
                    }
                });
            });
        });
    </script>
</body>

</html>

