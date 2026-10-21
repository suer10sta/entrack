<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Preview import') }} - {{ $project->name }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    <x-dashboard.sidebar />
    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />
        <main class="flex-1 p-8">
            <div class="max-w-6xl mx-auto space-y-6">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="mb-6">
                        <a href="{{ route('admin.projects.time-transfer.import', $project) }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; {{ __('messages.Back to import') }}</a>
                        <h1 class="text-2xl font-bold text-slate-900 mt-2">{{ __('messages.Preview import') }}</h1>
                        <p class="text-sm text-slate-500 mt-1">{{ $project->name }} — {{ $employee->name }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                            <p class="text-xs font-semibold uppercase text-blue-700">{{ __('messages.Lines') }}</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $preview['line_count'] }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold uppercase text-emerald-700">{{ __('messages.Total hours') }}</p>
                            <p class="text-2xl font-bold text-emerald-900">{{ number_format($preview['total_hours'], 2, ',', ' ') }} h</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-xs font-semibold uppercase text-amber-700">{{ __('messages.Tasks to create') }}</p>
                            <p class="text-2xl font-bold text-amber-900">{{ $preview['tasks_to_create'] }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('messages.Début') }}</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('messages.Fin') }}</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('messages.Task') }}</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('messages.Minutes') }}</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('messages.Comment column') }}</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('messages.Action column') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($preview['rows'] as $row)
                                    <tr>
                                        <td class="px-4 py-3">{{ $row['start_date'] }}</td>
                                        <td class="px-4 py-3">{{ $row['end_date'] }}</td>
                                        <td class="px-4 py-3 font-medium">{{ $row['task_name'] }}</td>
                                        <td class="px-4 py-3">{{ $row['duration_minutes'] }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $row['comment'] ?: '—' }}</td>
                                        <td class="px-4 py-3">
                                            @if($row['action'] === 'create')
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">{{ __('messages.Create task') }}</span>
                                            @else
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">{{ __('messages.Reuse task') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <form method="POST" action="{{ route('admin.projects.time-transfer.confirm', $project) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                {{ __('messages.Confirm import') }}
                            </button>
                        </form>
                        <a href="{{ route('admin.projects.time-transfer.import', $project) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-50">
                            {{ __('messages.Cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
