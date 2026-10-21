<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.Send Calendar Events - DalyTrac') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/dalytrac-calendar-preview.css') }}">
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 min-w-0 overflow-y-auto">
        <x-dashboard.navbar />
        @php($pdfUrl = route('admin.calendar.events.pdf', ['events' => $eventIds, 'month' => $month, 'year' => $year]))
        <main class="calendar-email-preview">
            <div class="cep-container">
                <!-- <nav class="cep-breadcrumb" aria-label="{{ __('messages.Planning') }}">
                    <a href="{{ route('admin.calendar.index') }}">{{ __('messages.Planning') }}</a>
                    <span aria-hidden="true">/</span>
                    <span>{{ __('messages.Calendar Events Preview') }}</span>
                </nav>
                <header class="cep-header">
                    <div>
                        <span class="cep-eyebrow">{{ __('messages.Planning') }}</span>
                        <h1>{{ __('messages.Send Calendar Events') }}</h1>
                        <p>{{ __('messages.calendar_preview_intro') }}</p>
                    </div>
                    <span class="cep-period">{{ $monthName }}</span>
                </header> -->
                <div class="cep-layout">
                    <section class="cep-card cep-preview" aria-labelledby="pdf-preview-heading">
                        <div class="cep-card-heading">
                            <div class="cep-heading-group">
                                <span class="cep-icon" aria-hidden="true">PDF</span>
                                <div><h2 id="pdf-preview-heading">{{ __('messages.Calendar Events Preview') }}</h2><p>{{ $monthName }}</p></div>
                            </div>
                            <a class="cep-open" href="{{ $pdfUrl }}" target="_blank" rel="noopener">{{ __('messages.calendar_preview_open') }} <span aria-hidden="true">↗</span></a>
                        </div>
                        <div class="cep-pdf-frame">
                            <iframe src="{{ $pdfUrl }}" title="{{ __('messages.Calendar Events Preview') }} — {{ $monthName }}"></iframe>
                        </div>
                        <div class="cep-included"><span aria-hidden="true">✓</span> {{ __('messages.calendar_preview_included') }}</div>
                    </section>
                    <section class="cep-card cep-composer" aria-labelledby="email-options-heading">
                        <div class="cep-card-heading">
                            <div class="cep-heading-group">
                                <span class="cep-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 7 9 6 9-6"/></svg></span>
                                <div><h2 id="email-options-heading">{{ __('messages.Email Options') }}</h2><p>{{ __('messages.calendar_preview_compose') }}</p></div>
                            </div>
                        </div>
                        <form id="sendEventsForm" action="{{ route('admin.calendar.send-events-email') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="events" value="{{ $eventIds }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <div class="cep-fields">
                                <div class="cep-field">
                                    <label for="recipients">{{ __('messages.Recipients label') }} <span>*</span></label>
                                    <textarea id="recipients" name="recipients" rows="2" placeholder="{{ __('messages.Recipients placeholder') }}" aria-describedby="recipients-help" required></textarea>
                                    <p id="recipients-help">{{ __('messages.Separate emails helper') }}</p>
                                </div>
                                <div class="cep-field">
                                    <label for="subject">{{ __('messages.Email Subject') }} <span>*</span></label>
                                    <input type="text" id="subject" name="subject" value="{{ __('messages.Calendar Events Subject Prefix') }} {{ $monthName }}" required>
                                </div>
                                <div class="cep-field">
                                    <label for="body">{{ __('messages.Email Body') }} <span>*</span></label>
                                    <textarea id="body" name="body" rows="7" required>{{ __('messages.Calendar Events default body', ['month' => $monthName]) }}</textarea>
                                </div>
                                <div class="cep-attachments cep-field">
                                    <label for="attachments">{{ __('messages.Additional Attachments') }}</label>
                                    <input type="file" id="attachments" name="attachments[]" multiple aria-describedby="attachments-help">
                                    <p id="attachments-help">{{ __('messages.Calendar Attachments helper') }}</p>
                                </div>
                            </div>
                            <div class="cep-form-footer">
                                <button type="submit" class="cep-send">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m22 2-7 20-4-9-9-4 20-7ZM22 2 11 13"/></svg>
                                    {{ __('messages.Send email button') }}
                                </button>
                            </div>
                        </form>
                    </section>
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

