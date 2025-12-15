<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Report Preview</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    {{-- Main wrapper --}}
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Content --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tasks</p>
                        <h1 class="text-2xl font-bold text-gray-900">Customer report preview</h1>
                        <p class="text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} · {{ $projectName }}
                        </p>
                    </div>
                    <a href="{{ route('admin.tasks.pointage') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18"/></svg>
                        Back to tasks
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- PDF preview --}}
                    <div class="lg:col-span-2">
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <div>
                                    <h2 class="text-base font-semibold text-gray-900">PDF preview</h2>
                                    <p class="text-sm text-gray-600">Check the worksheet before sending it to the customer.</p>
                                </div>
                            </div>
                            <div class="relative bg-gray-100 min-h-[620px]">
                                <iframe src="{{ $pdfUrl }}" class="absolute inset-0 w-full h-full border-0 rounded-b-lg"></iframe>
                            </div>
                        </div>
                    </div>

                    {{-- Email form --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden lg:sticky lg:top-6">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h2 class="text-base font-semibold text-gray-900">Send by email</h2>
                                <p class="text-sm text-gray-600">Recipients will receive the attached worksheet PDF.</p>
                            </div>
                            <div class="p-6 space-y-6">
                                @if(session('success'))
                                    <div class="flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                                        <svg class="w-5 h-5 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        <div class="text-sm font-medium">{{ session('success') }}</div>
                                    </div>
                                @endif

                                <form action="{{ route('admin.CustomerPointagerepports.send') }}" method="POST" class="space-y-5">
                                    @csrf
                                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                                    <input type="hidden" name="project_id" value="{{ $projectId }}">
                                    <input type="hidden" name="employee_id" value="{{ $employeeId }}">
                                    <input type="hidden" name="task_ids" value="{{ json_encode($taskIds) }}">
                                    <input type="hidden" name="client_id" value="{{ $clientId }}">
                                    <input type="hidden" name="redirect_to" value="{{ route('admin.index') }}">

                                    <div class="space-y-1">
                                        <label for="recipients" class="text-sm font-medium text-gray-700">Recipients (comma-separated)</label>
                                        <textarea id="recipients" name="recipients" rows="3" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            placeholder="email@example.com, another@example.com">{{ implode(',', $projectContacts) }}</textarea>
                                    </div>

                                    <div class="space-y-1">
                                        <label for="subject" class="text-sm font-medium text-gray-700">Subject</label>
                                        <input type="text" id="subject" name="subject" required
                                            value="Worksheet - {{ $projectName }} - {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>

                                    <div class="space-y-1">
                                        <label for="body" class="text-sm font-medium text-gray-700">Message</label>
                                        <textarea id="body" name="body" rows="8" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">Bonjour,
Je vous transmets ci-joint le worksheet pour validation.

N'hésitez pas à me contacter si vous avez besoin de précisions ou de documents complémentaires.
Merci d'avance pour votre retour.

Bien à vous</textarea>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button type="submit" class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full sm:w-auto">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            Send email
                                        </button>
                                        <a href="{{ route('admin.tasks.pointage') }}" class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 w-full sm:w-auto">
                                            Cancel
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

    @livewireScripts
</body>
</html>