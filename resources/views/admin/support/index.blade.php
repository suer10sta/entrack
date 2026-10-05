<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.My Support Tickets') }} - DalyTrac</title>
    @vite('resources/css/app.css')
    <style>
        #supportTicketsTable th {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .07em;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }
        #supportTicketsTable td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            font-size: 12.5px;
            vertical-align: middle;
        }
        #supportTicketsTable tbody tr:hover td { background: #f8fafc; }
        .support-ticket-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
        }
        @media (max-width: 767px) {
            #supportTicketsTable { width: 100%; min-width: 0; }
            #supportTicketsTable thead { display: none; }
            #supportTicketsTable tbody { display: flex; flex-direction: column; gap: 10px; }
            #supportTicketsTable tbody tr { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 7px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; }
            #supportTicketsTable tbody td { min-width: 0; padding: 8px !important; border: 1px solid #f1f5f9; border-radius: 7px; overflow-wrap: anywhere; }
            #supportTicketsTable tbody td[data-label]::before { display: block; margin-bottom: 4px; color: #94a3b8; content: attr(data-label); font-size: 9px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
            #supportTicketsTable tbody td[data-span="2"] { grid-column: span 2; }
        }
    </style>
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
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
        <main class="flex-1 min-w-0 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">
            <div class="w-full max-w-7xl mx-auto">

                            {{-- Page Header --}}
                            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-4">
                                <div class="min-w-0">
                                    <p class="dt-overline mb-0.5">{{ __('messages.Help & Support') }}</p>
                                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.My Support Tickets') }}</h1>
                                    <p class="text-[12px] text-slate-500 mt-0.5">
                                        {{ __('messages.View the tickets you have opened with the DalyTrac support team.') }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('admin.support.create') }}"
                                       class="dt-btn dt-btn-primary justify-center w-full sm:w-auto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        {{ __('messages.New Support Request') }}
                                    </a>
                                </div>
                            </div>

                            @if(session('success'))
                                <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('messages.Support ticket deleted successfully') }}
                                </div>
                            @endif

                            {{-- Tickets Card --}}
                            <div class="dt-card overflow-hidden">
                                <div class="flex  justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
                                    <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.My Support Tickets') }}</h2>
                                    <span class="dt-badge dt-badge-navy text-[11px]"><span class="dt-dot"></span>{{ $tickets->total() }} {{ __('messages.Total') }}</span>
                                </div>
                                @if($tickets->isEmpty())
                                    <div class="px-5 py-12 text-center">
                                        <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5m-8 7l-2 1 1-4a9 9 0 1116 0 9 9 0 01-12 3z"/></svg>
                                        </div>
                                        <p class="text-[13px] font-medium text-slate-500">{{ __('messages.You have not opened any support tickets yet.') }}</p>
                                    </div>
                                @else
                                    <div class="overflow-x-auto">
                                        <table id="supportTicketsTable" class="w-full min-w-[780px] sm:min-w-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="w-[27%]">
                                                        {{ __('messages.Subject') }}
                                                    </th>
                                                    <th scope="col" class="w-[16%]">
                                                        {{ __('messages.Category') }}
                                                    </th>
                                                    <th scope="col" class="w-[12%]">
                                                        {{ __('messages.Priority') }}
                                                    </th>
                                                    <th scope="col" class="w-[12%]">
                                                        {{ __('messages.Status') }}
                                                    </th>
                                                    <th scope="col" class="w-[18%]">
                                                        {{ __('messages.Created at') }}
                                                    </th>
                                                    <th scope="col" class="w-[15%] text-right">
                                                        {{ __('messages.Actions') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($tickets as $ticket)
                                                    <tr>
                                                        <td data-label="{{ __('messages.Subject') }}" data-span="2" class="text-slate-800">
                                                            <div class="font-semibold text-[12.5px] text-[#0B2638]">{{ $ticket->subject }}</div>
                                                            <div class="text-[11px] text-slate-500 line-clamp-1">
                                                                {{ Str::limit($ticket->message, 80) }}
                                                            </div>
                                                        </td>
                                                        <td data-label="{{ __('messages.Category') }}" class="text-slate-600">
                                                            @php
                                                                $categoryLabels = [
                                                                    'billing' => 'Billing & invoices',
                                                                    'technical' => 'Technical issue',
                                                                    'account' => 'Account / access',
                                                                    'feature' => 'Feature request',
                                                                    'other' => 'Other',
                                                                ];
                                                            @endphp
                                                            {{ $ticket->category ? (isset($categoryLabels[$ticket->category]) ? __('messages.' . $categoryLabels[$ticket->category]) : $ticket->category) : '—' }}
                                                        </td>
                                                        <td data-label="{{ __('messages.Priority') }}">
                                                            @php
                                                                $priorityColors = [
                                                                    'low' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-600/10',
                                                                    'medium' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/15',
                                                                    'high' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/15',
                                                                ];
                                                                $priority = $ticket->priority ?? 'medium';
                                                            @endphp
                                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $priorityColors[$priority] ?? 'bg-slate-100 text-slate-700 ring-1 ring-slate-600/10' }}">
                                                                {{ __('messages.' . ucfirst($priority)) }}
                                                            </span>
                                                        </td>
                                                        <td data-label="{{ __('messages.Status') }}">
                                                            @php
                                                                $statusColors = [
                                                                    'open' => 'bg-[#2D7080]/10 text-[#2D7080] ring-1 ring-[#2D7080]/20',
                                                                    'in_progress' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/15',
                                                                    'resolved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/15',
                                                                    'closed' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-600/10',
                                                                ];
                                                                $status = $ticket->status ?? 'open';
                                                            @endphp
                                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusColors[$status] ?? 'bg-slate-100 text-slate-600 ring-1 ring-slate-600/10' }}">
                                                                {{ __('messages.' . ucfirst(str_replace('_', ' ', $status))) }}
                                                            </span>
                                                        </td>
                                                        <td data-label="{{ __('messages.Created at') }}" class="text-[11px] text-slate-500 whitespace-nowrap">
                                                            {{ $ticket->created_at->format('Y-m-d H:i') }}
                                                        </td>
                                                        <td data-label="{{ __('messages.Actions') }}" class="text-right">
                                                            <form method="POST" action="{{ route('admin.support.destroy', $ticket) }}" class="inline-block"
                                                                  onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this ticket?') }}');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="dt-btn dt-btn-ghost text-red-700 text-[11px] px-2.5 py-1.5">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7-3h8m-9 3 1 14h6l1-14"/></svg>
                                                                    {{ __('messages.Delete') }}
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="flex justify-end px-5 py-3 border-t border-slate-100 bg-[#f8fafc]">
                                        {{ $tickets->links() }}
                                    </div>
                                @endif
                            </div>

            </div>
        </main>
    </div>
</body>
</html>


