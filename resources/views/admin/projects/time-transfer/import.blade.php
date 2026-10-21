<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Import time transfer') }} - {{ $project->name }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    <x-dashboard.sidebar />
    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />
        <main class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="mb-6">
                        <a href="{{ route('admin.projects.show', $project) }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; {{ $project->name }}</a>
                        <h1 class="text-2xl font-bold text-slate-900 mt-2">{{ __('messages.Import time transfer') }}</h1>
                        <p class="text-sm text-slate-500 mt-1">{{ __('messages.Import time transfer description') }}</p>
                    </div>

                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg whitespace-pre-line">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.projects.time-transfer.preview', $project) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('messages.CSV file') }}</label>
                            <input type="file" name="file" accept=".csv,.txt" required class="w-full text-sm">
                            <p class="text-xs text-slate-500 mt-1">start_date, end_date, task_name, duration_minutes, comment (optionnel)</p>
                        </div>

                        <div class="border-t border-slate-200 pt-6">
                            <p class="text-sm font-semibold text-slate-800 mb-3">{{ __('messages.Import employee') }}</p>

                            <div class="space-y-3">
                                <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" name="employee_mode" value="create" class="mt-1" {{ old('employee_mode', $importEmployee ? 'existing' : 'create') === 'create' ? 'checked' : '' }}>
                                    <span>
                                        <span class="block font-medium text-slate-900">{{ __('messages.Create import employee') }}</span>
                                        <span class="block text-sm text-slate-500">{{ \App\Services\TimeTransferService::IMPORT_EMPLOYEE_NAME }}</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" name="employee_mode" value="existing" class="mt-1" {{ old('employee_mode', $importEmployee ? 'existing' : 'create') === 'existing' ? 'checked' : '' }}>
                                    <span class="w-full">
                                        <span class="block font-medium text-slate-900 mb-2">{{ __('messages.Use existing employee') }}</span>
                                        <select name="employee_id" class="w-full rounded-lg border-slate-300 text-sm">
                                            <option value="">{{ __('messages.Select employee') }}</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" @selected(old('employee_id', $importEmployee?->id) == $employee->id)>
                                                    {{ $employee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                {{ __('messages.Preview import') }}
                            </button>
                            <a href="{{ route('admin.projects.show', $project) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-50">
                                {{ __('messages.Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
