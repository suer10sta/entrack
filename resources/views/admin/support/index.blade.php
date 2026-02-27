<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.My Support Tickets') }} - DalyTrac</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 overflow-y-auto ">
            <div class="w-full px-4 md:px-6 py-4">
                <div class="flex flex-wrap -mx-3 mb-5">
                    <div class="w-full">
                        <div class="w-full lg:w-10/12 xl:w-8/12 mx-auto my-4">

                            {{-- Page Header --}}
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-800">{{ __('messages.My Support Tickets') }}</h1>
                                    <p class="text-base text-gray-500">
                                        {{ __('messages.View the tickets you have opened with the DalyTrac support team.') }}
                                    </p>
                                </div>
                                <div>
                                    <a href="{{ route('admin.support.create') }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        {{ __('messages.New Support Request') }}
                                    </a>
                                </div>
                            </div>

                            {{-- Tickets Card --}}
                            <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                                @if($tickets->isEmpty())
                                    <p class="text-sm text-gray-500">
                                        {{ __('messages.You have not opened any support tickets yet.') }}
                                    </p>
                                @else
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ __('messages.Subject') }}
                                                    </th>
                                                    <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ __('messages.Category') }}
                                                    </th>
                                                    <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ __('messages.Priority') }}
                                                    </th>
                                                    <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ __('messages.Status') }}
                                                    </th>
                                                    <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ __('messages.Created at') }}
                                                    </th>
                                                    <th scope="col" class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ __('messages.Actions') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($tickets as $ticket)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-gray-900">
                                                            <div class="font-medium">{{ $ticket->subject }}</div>
                                                            <div class="text-xs text-gray-500 line-clamp-1">
                                                                {{ Str::limit($ticket->message, 80) }}
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-700">
                                                            {{ $ticket->category ?? '—' }}
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            @php
                                                                $priorityColors = [
                                                                    'low' => 'bg-emerald-100 text-emerald-800',
                                                                    'medium' => 'bg-amber-100 text-amber-800',
                                                                    'high' => 'bg-red-100 text-red-800',
                                                                ];
                                                                $priority = $ticket->priority ?? 'medium';
                                                            @endphp
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $priorityColors[$priority] ?? 'bg-gray-100 text-gray-800' }}">
                                                                {{ __('messages.' . ucfirst($priority)) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            @php
                                                                $statusColors = [
                                                                    'open' => 'bg-blue-100 text-blue-800',
                                                                    'in_progress' => 'bg-amber-100 text-amber-800',
                                                                    'resolved' => 'bg-emerald-100 text-emerald-800',
                                                                    'closed' => 'bg-gray-100 text-gray-800',
                                                                ];
                                                                $status = $ticket->status ?? 'open';
                                                            @endphp
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                                                {{ __('messages.' . ucfirst(str_replace('_', ' ', $status))) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-500 text-xs">
                                                            {{ $ticket->created_at->format('Y-m-d H:i') }}
                                                        </td>
                                                        <td class="px-4 py-3 text-right">
                                                            <form method="POST" action="{{ route('admin.support.destroy', $ticket) }}" class="inline-block"
                                                                  onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this ticket?') }}');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="inline-flex items-center px-2.5 py-1.5 border border-red-200 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500">
                                                                    {{ __('messages.Delete') }}
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-4">
                                        {{ $tickets->links() }}
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>


