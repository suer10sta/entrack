<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Export time transfer') }} - {{ $project->name }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    <x-dashboard.sidebar />
    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />
        <main class="flex-1 p-8">
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="mb-6">
                        <a href="{{ route('admin.projects.show', $project) }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; {{ $project->name }}</a>
                        <h1 class="text-2xl font-bold text-slate-900 mt-2">{{ __('messages.Export time transfer') }}</h1>
                        <p class="text-sm text-slate-500 mt-1">{{ __('messages.Export time transfer description') }}</p>
                    </div>

                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg">{{ session('error') }}</div>
                    @endif

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 mb-6 text-sm text-slate-700">
                        <p class="font-semibold mb-2">{{ __('messages.Export format') }}</p>
                        <code class="block text-xs bg-white border rounded p-2">date, task_name, duration_minutes, comment</code>
                    </div>

                    <form method="POST" action="{{ route('admin.projects.time-transfer.export.download', $project) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Start Date') }} ({{ __('messages.optional') }})</label>
                                <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-lg border-slate-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.End Date') }} ({{ __('messages.optional') }})</label>
                                <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full rounded-lg border-slate-300">
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                {{ __('messages.Download CSV') }}
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
