<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- IMPORTANT pour AJAX --}}
    <title>{{ __('messages.Task Details') }} — DalyTrac</title>
    @vite('resources/css/app.css')
    <style>
        td.editable-comment { cursor: pointer; }
        td.editable-comment:hover { background-color: #f0fafb !important; }
        td.editable-comment .comment-text { border-bottom: 1px dashed #9ca3af; }
        td.editable-comment .comment-text:empty::before {
            content: '{{ __('messages.Double-click to add comment') }}';
            font-size: 0.75rem;
            color: #9ca3af;
            font-style: italic;
        }
        /* Sticky table head on desktop */
        @media (min-width: 1024px) {
            #taskDetailsTable thead th { position: sticky; top: 0; z-index: 5; }
        }
        /* Mobile: table becomes stacked cards (no side-scroll) */
        @media (max-width: 1023px) {
            #taskDetailsTable thead { display: none; }
            #taskDetailsTable tbody { display: block; width: 100%; }
            #taskDetailsTable tbody tr.di-day { display: block; margin: 0 0 2px 0; padding: 0; border: none; background: none; box-shadow: none; }
            #taskDetailsTable tbody tr.di-row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
                margin-bottom: 10px;
                padding: 12px;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                background: #fff;
                box-shadow: 0 2px 10px -4px rgba(11,38,56,.08);
            }
            #taskDetailsTable tbody tr.di-total {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                margin-bottom: 10px;
                padding: 10px 12px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background: #f8fafc;
            }
            #taskDetailsTable tbody td {
                display: block;
                width: 100%;
                padding: 8px 10px !important;
                border: 1px solid rgba(226,232,240,.8);
                border-radius: 10px;
                background: rgba(248,250,252,.7);
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
            }
            #taskDetailsTable tbody td[data-span="2"] { grid-column: span 2 / span 2; }
            #taskDetailsTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #94a3b8;
                margin-bottom: 4px;
            }
            #taskDetailsTable tbody tr.di-total td,
            #taskDetailsTable tbody tr.di-day td {
                border: none;
                background: none;
                padding: 0 !important;
            }
            #taskDetailsTable tbody tr.di-total td[data-label]::before,
            #taskDetailsTable tbody tr.di-day td[data-label]::before { content: none; }
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-8">

<div class="w-full max-w-7xl mx-auto">

    @php
        $diTasks = $tasks ?? collect();
        $diTotal = (int) $diTasks->sum('duration');
        $diDays = $diTasks->map(function ($t) { return \Carbon\Carbon::parse($t->start_date)->format('Y-m-d'); })->unique()->count();
        $diFirst = $diTasks->first();
    @endphp

    <!-- {{-- Header --}}
    <div class="flex items-center gap-3 mb-4">
        <a href="javascript:history.back()" title="{{ __('messages.Cancel') }}" aria-label="{{ __('messages.Cancel') }}"
            class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#0B2638] hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        </a>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">{{ __('messages.Task Details') }}</p>
                        <h1 class="text-[19px] sm:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight truncate" title="{{ $diFirst->task_name ?? '' }}">
                            {{ $diFirst->task_name ?? __('messages.Task Details') }}
                        </h1>
                    </div>
                    <div class="flex flex-shrink-0 items-center gap-1.5">
                        @php
                            $diTaskId = $diFirst->task_id ?? null;
                            $editRoute = '#';
                            $deleteRoute = '#';
                            if (Auth::user()->hasRole('Admin')) {
                                $editRoute = $diTaskId ? route('admin.tasks.edit', $diTaskId) : '#';
                                $deleteRoute = $diTaskId ? route('admin.tasks.destroy', $diTaskId) : '#';
                            } elseif (Auth::user()->hasRole('Responsable de projet')) {
                                $editRoute = $diTaskId ? route('Responsable_de_projet.tasks.edit', $diTaskId) : '#';
                                $deleteRoute = $diTaskId ? route('Responsable_de_projet.tasks.destroy', $diTaskId) : '#';
                            } elseif (Auth::user()->hasRole('Employee')) {
                                $editRoute = $diTaskId ? route('employee.tasks.edit', $diTaskId) : '#';
                                $deleteRoute = $diTaskId ? route('employee.tasks.destroy', $diTaskId) : '#';
                            } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                $editRoute = $diTaskId ? route('Responsable_gestionnaire.tasks.edit', $diTaskId) : '#';
                            }
                        @endphp
                        @can('Update task')
                        <a href="{{ $editRoute }}" title="{{ __('messages.Edit') }}" aria-label="{{ __('messages.Edit') }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#2D7080] hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M4 20h4l9.5-9.5-4-4L4 16v4z"/></svg>
                        </a>
                        @endcan
                        @can('Delete task')
                        <form method="post" action="{{ $deleteRoute }}" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="{{ __('messages.Delete') }}" aria-label="{{ __('messages.Delete') }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        @endcan
        </div>
        <span class="dt-badge dt-badge-navy flex-shrink-0">
            <span class="dt-dot"></span>
            <span class="tabular-nums">{{ floor($diTotal / 60) }}h:{{ str_pad($diTotal % 60, 2, '0', STR_PAD_LEFT) }}m</span>
        </span>
    </div> -->

    {{-- Message d'alerte --}}
    @if(session('alertmessage'))
    <div class="mb-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
        <strong class="font-bold text-[13px]">{{ __('messages.Error!') }}</strong>
        <span class="block sm:inline text-[13px]">{{ session('alertmessage') }}</span>
        <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2 right-3 font-bold text-lg leading-none" aria-label="{{ __('messages.Close') }}">&times;</button>
    </div>
    @endif

    {{-- Summary strip --}}
    <!-- <div class="grid grid-cols-3 gap-2.5 mb-3">
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3.5 py-2.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.07em] text-slate-400">{{ __('messages.Duration') }}</p>
            <p class="text-[17px] font-bold text-[#0B2638] tabular-nums leading-tight mt-0.5">{{ floor($diTotal / 60) }}h:{{ str_pad($diTotal % 60, 2, '0', STR_PAD_LEFT) }}m</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3.5 py-2.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.07em] text-slate-400">{{ __('messages.Task') }}</p>
            <p class="text-[17px] font-bold text-[#0B2638] tabular-nums leading-tight mt-0.5">{{ $diTasks->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3.5 py-2.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.07em] text-slate-400">{{ __('messages.End Date') }}</p>
            <p class="text-[17px] font-bold text-[#0B2638] tabular-nums leading-tight mt-0.5">{{ $diDays }}</p>
        </div>
    </div> -->

    {{-- Table card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="taskDetailsTable" class="min-w-full divide-y divide-slate-100">
                <thead class="bg-white">
                    <tr>
                        <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-[0.07em] whitespace-nowrap">Tâche</th>
                        <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-[0.07em] whitespace-nowrap">Employé</th>
                        <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-[0.07em] whitespace-nowrap">Date Début</th>
                        <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-[0.07em] whitespace-nowrap">Date Fin</th>
                        <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-[0.07em] whitespace-nowrap">Durée</th>
                        <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-[0.07em] whitespace-nowrap">
                            {{ __('messages.Comment column') }}
                            <span class="block text-[10px] font-medium text-slate-400 normal-case tracking-normal">{{ __('messages.Double-click to add comment') }}</span>
                        </th>
                        <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-[0.07em] whitespace-nowrap">Déplacer Durée</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-50">
                    {{-- LOGIQUE PHP D'ORIGINE RESTAURÉE --}}
                    @php
                        $total_duration = 0;
                        $grouped_tasks = $tasks->groupBy(function($task) {
                            return \Carbon\Carbon::parse($task->start_date)->format('Y-m-d');
                        })->sortKeys();
                    @endphp

                    @foreach($grouped_tasks as $date => $day_tasks)
                        @php
                            $daily_duration = 0;
                            $formatted_date = \Carbon\Carbon::parse($date)->format('d-m-Y');
                        @endphp

                        {{-- Ligne d'en-tête de Date --}}
                        <tr class="di-day">
                            <td colspan="7" class="px-4 py-2 bg-slate-200/60">
                                <h6 class="mb-0 text-[12.5px] font-bold text-[#0B2638]">{{ \Carbon\Carbon::parse($date)->format('l') }} : {{ $formatted_date }}</h6>
                            </td>
                        </tr>

                        @foreach($day_tasks as $task)
                            <tr class="di-row hover:bg-slate-50/70 transition-colors">
                                {{-- Tâche --}}
                                <td data-label="{{ __('messages.Task') }}" data-span="2" class="px-4 py-2.5 whitespace-nowrap text-[13px] font-semibold text-[#0B2638]">
                                    {{$task->task_name}}
                                </td>
                                {{-- Employé (LOGIQUE D'ORIGINE RESTAURÉE) --}}
                                <td data-label="{{ __('messages.Employees') }}" class="px-4 py-2.5 whitespace-nowrap text-[13px] text-slate-600">
                                    @php
                                        $employee_name = App\Models\Employees::where('id', $task->employee_id)->pluck('name')->first();
                                    @endphp
                                    {{$employee_name}}
                                </td>
                                {{-- Date Début --}}
                                <td class="clickable px-4 py-2.5 whitespace-nowrap text-[13px] text-[#2D7080] font-medium cursor-pointer hover:underline" data-field="start_date" data-task-id="{{ $task->id }}">
                                    {{ \Carbon\Carbon::parse($task->start_date)->format('d-m-Y H:i:s') }}
                                </td>
                                {{-- Date Fin --}}
                                <td class="clickable px-4 py-2.5 whitespace-nowrap text-[13px] text-[#2D7080] font-medium cursor-pointer hover:underline" data-field="end_date" data-task-id="{{ $task->id }}">
                                    {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('d-m-Y H:i:s') : 'En cours' }}
                                </td>
                                {{-- Durée (LOGIQUE D'ORIGINE RESTAURÉE) --}}
                                <td id="duration-{{ $task->id }}" class="duration clickable px-4 py-2.5 whitespace-nowrap text-[13px] text-[#0B2638] font-bold cursor-pointer hover:underline tabular-nums" data-task-id="{{ $task->id }}">
                                    @php
                                        $tasktotalduration = $task->duration;
                                        $taskhours = floor($tasktotalduration / 60);
                                        $taskminutes = $tasktotalduration % 60;
                                        $total_duration += $tasktotalduration;
                                        $daily_duration += $tasktotalduration;
                                    @endphp
                                    {{$taskhours}}h:{{ str_pad($taskminutes, 2, '0', STR_PAD_LEFT) }}m
                                </td>
                                {{-- Commentaire --}}
                                <td class="px-4 py-2.5 text-[13px] text-slate-600 whitespace-normal break-words editable-comment"
                                    data-label="{{ __('messages.Comment column') }}"
                                    data-id="{{ $task->id }}"
                                    data-field="comment"
                                    @php
                                        if (Auth::user()->hasRole('Admin')) {
                                            $commentUpdateRoute = route('admin.tasks.updateField', ['field' => 'comment', 'id' => $task->id]);
                                        } elseif (Auth::user()->hasRole('Responsable de projet')) {
                                            $commentUpdateRoute = route('Responsable_de_projet.tasks.updateField', ['field' => 'comment', 'id' => $task->id]);
                                        } elseif (Auth::user()->hasRole('Employee')) {
                                            $commentUpdateRoute = route('employee.tasks.updateField', ['field' => 'comment', 'id' => $task->id]);
                                        } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                            $commentUpdateRoute = route('Responsable_gestionnaire.tasks.updateField', ['field' => 'comment', 'id' => $task->id]);
                                        } else {
                                            $commentUpdateRoute = '';
                                        }
                                    @endphp
                                    data-update-url="{{ $commentUpdateRoute }}">
                                    <span class="comment-text">{{ $task->comment }}</span>
                                </td>
                                {{-- Déplacer Durée --}}
                                <td class="px-4 py-2.5 whitespace-nowrap text-sm">
                                    <button class="move-duration-btn inline-flex items-center gap-1.5 bg-[#0B2638] hover:bg-[#14364d] text-white font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors"
                                            data-task-id="{{$task->id}}"
                                            data-project-id="{{$task->project_id}}"
                                            data-employee-id="{{$task->employee_id}}"
                                            data-start-date="{{$task->start_date}}"
                                            data-end-date="{{$task->end_date}}"
                                            data-duration="{{$task->duration}}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                        Déplacer
                                    </button>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Ligne Total Quotidien (LOGIQUE D'ORIGINE RESTAURÉE) --}}
                        <tr class="di-total bg-slate-50/60">
                            <td colspan="4" class="px-4 py-2.5 text-right text-[12.5px] font-bold text-slate-500">
                                Total Quotidien
                            </td>
                            <td class="px-4 py-2.5 text-[13px] font-bold text-[#0B2638] tabular-nums">
                                @php
                                    $dailyhours = floor($daily_duration / 60);
                                    $dailyminutes = $daily_duration % 60;
                                @endphp
                                {{$dailyhours}}h:{{ str_pad($dailyminutes, 2, '0', STR_PAD_LEFT) }}m
                            </td>
                            <td class="px-4 py-2.5 hidden lg:table-cell"></td>
                            <td class="px-4 py-2.5 hidden lg:table-cell"></td>
                        </tr>
                    @endforeach

                    {{-- Ligne Grand Total (LOGIQUE D'ORIGINE RESTAURÉE) --}}
                    <tr class="di-total bg-[#0B2638]/[0.04] border-t-2 border-[#0B2638]/10">
                        <td colspan="4" class="px-4 py-3 text-right text-[13.5px] font-bold text-[#0B2638]">
                            Grand Total
                        </td>
                        <td class="px-4 py-3 text-[14px] font-bold text-[#2D7080] tabular-nums">
                            @php
                                $totalhours = floor($total_duration / 60);
                                $totalminutes = $total_duration % 60;
                            @endphp
                            {{$totalhours}}h:{{ str_pad($totalminutes, 2, '0', STR_PAD_LEFT) }}m
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell"></td>
                        <td class="px-4 py-3 hidden lg:table-cell"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>
</div>

{{-- ====================================================== --}}
{{-- MODALS                                                 --}}
{{-- ====================================================== --}}

<div id="editDateModal"
     class="fixed inset-0 z-50 flex items-center justify-center hidden bg-[#0B2638]/50 transition-opacity px-4"
     aria-labelledby="editDateModalLabel"
     role="dialog"
     aria-modal="true">
    <div class="relative w-full max-w-md p-5 mx-auto bg-white rounded-xl shadow-xl transform transition-all sm:p-6">
        <form id="editDateForm">
            {{-- En-tête --}}
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]" id="editDateModalLabel">Modifier Date et Heure</h5>
                <button type="button" data-modal-close="editDateModal" class="text-slate-400 bg-transparent rounded-lg hover:bg-slate-100 hover:text-slate-600 text-sm p-1.5 ml-auto inline-flex items-center transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
            {{-- Corps --}}
            <div class="py-4 space-y-3">
                <div>
                    <label for="dateInput" class="block text-[12.5px] font-medium text-slate-700 mb-1">Date</label>
                    <input type="date" id="dateInput" required
                           class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm">
                </div>
                <div>
                    <label for="timeInput" class="block text-[12.5px] font-medium text-slate-700 mb-1">Heure</label>
                    <input type="time" id="timeInput" required
                           class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm">
                </div>
                <input type="hidden" id="taskId" value="">
                <input type="hidden" id="fieldName" value="">
            </div>
            {{-- Pied de page --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 pt-3 border-t border-slate-100">
                <button type="button" data-modal-close="editDateModal" class="mt-2 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    Fermer
                </button>
                <button type="button" id="saveChangesBtn" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent bg-[#0B2638] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#14364d] transition-colors">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<div id="moveDurationModal"
     class="fixed inset-0 z-50 flex items-center justify-center hidden bg-[#0B2638]/50 transition-opacity px-4"
     aria-labelledby="moveDurationModalLabel"
     role="dialog"
     aria-modal="true">
    <div class="relative w-full max-w-lg p-5 mx-auto bg-white rounded-xl shadow-xl transform transition-all sm:p-6 max-h-[90vh] overflow-y-auto">
        <form id="moveDurationForm">
            {{-- En-tête --}}
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <h5 class="text-[15px] font-bold text-[#0B2638]" id="moveDurationModalLabel">Déplacer la Durée</h5>
                <button type="button" data-modal-close="moveDurationModal" class="text-slate-400 bg-transparent rounded-lg hover:bg-slate-100 hover:text-slate-600 text-sm p-1.5 ml-auto inline-flex items-center transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
            {{-- Corps --}}
            <div class="py-4 space-y-3">
                <div>
                    <label for="projectSelect" class="block text-[12.5px] font-medium text-slate-700 mb-1">Projet</label>
                    <select id="projectSelect" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="taskSelect" class="block text-[12.5px] font-medium text-slate-700 mb-1">Tâche</label>
                    <select id="taskSelect" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm"></select>
                </div>
                <div>
                    <label for="employeeSelect" class="block text-[12.5px] font-medium text-slate-700 mb-1">Employé</label>
                    <select id="employeeSelect" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="moveStartDate" class="block text-[12.5px] font-medium text-slate-700 mb-1">Date de Début</label>
                        <input type="datetime-local" id="moveStartDate" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm">
                    </div>
                    <div>
                        <label for="moveEndDate" class="block text-[12.5px] font-medium text-slate-700 mb-1">Date de Fin</label>
                        <input type="datetime-local" id="moveEndDate" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm">
                        <p id="moveEndDateError" class="mt-2 text-sm text-red-600 hidden">La date de fin doit être après la date de début.</p>
                    </div>
                </div>
                <div>
                    <label for="moveDuration" class="block text-[12.5px] font-medium text-slate-700 mb-1">Durée (HH:MM)</label>
                    <input type="text" id="moveDuration" required pattern="^([0-9]{2}h:[0-9]{2}m)$" placeholder="00h:00m" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080] sm:text-sm">
                    <p id="moveDurationError" class="mt-2 text-sm text-red-600 hidden">Veuillez entrer la durée au format : 00h:00m</p>
                </div>
                <input type="hidden" id="moveDurationMinutes">
                <input type="hidden" id="originalTaskId" value="">
            </div>
            {{-- Pied de page --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 pt-3 border-t border-slate-100">
                <button type="button" data-modal-close="moveDurationModal" class="mt-2 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    Fermer
                </button>
                <button type="button" id="saveMoveChangesBtn" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent bg-[#0B2638] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#14364d] transition-colors">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 1. jQuery (requis pour AJAX) --}}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

{{-- 4. Scripts de logique de la page (LOGIQUE D'ORIGINE RESTAURÉE AVEC CONTRÔLE DE MODAL TAILWIND) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- Fonctions de contrôle des Modals Tailwind ---
    const modals = {
        editDateModal: document.getElementById('editDateModal'),
        moveDurationModal: document.getElementById('moveDurationModal')
    };

    function openModal(modalId) {
        if (modals[modalId]) modals[modalId].classList.remove('hidden');
    }

    function closeModal(modalId) {
        if (modals[modalId]) modals[modalId].classList.add('hidden');
    }

    // Ajouter des écouteurs pour tous les boutons de fermeture
    document.querySelectorAll('[data-modal-close]').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal-close');
            closeModal(modalId);
        });
    });

    // Fermer en cliquant sur le fond grisé
    Object.values(modals).forEach(modal => {
        if (modal) {
            modal.addEventListener('click', e => {
                if (e.target === modal) closeModal(modal.id);
            });
        }
    });
    // --- Fin des fonctions de contrôle des Modals ---


    // Gérer les clics sur les cellules date/heure
    const cells = document.querySelectorAll('.clickable');
    cells.forEach(cell => {
        cell.addEventListener('click', function() {
            const field = this.getAttribute('data-field');
            const taskId = this.getAttribute('data-task-id');
            const currentValue = this.textContent.trim();

            // Vérifier si c'est une cellule de durée
            if (this.classList.contains('duration')) {
                var newValue = prompt("Entrer la nouvelle durée pour la tâche (ex: 1h:30m ou 90m)");
                if (newValue) {
                    var updatedurl;
                    @if(Auth::user()->hasRole('Admin'))
                        updatedurl = '/admin/tasks/update-duration/' + taskId;
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        updatedurl = '/Responsable_de_projet/tasks/update-duration/' + taskId;
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                        updatedurl = '/employee/tasks/update-duration/' + taskId;
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        updatedurl = '/Responsable_gestionnaire/tasks/update-duration/' + taskId;
                    @endif

                    $.ajax({
                        url: updatedurl,
                        type: 'POST',
                        data: {
                            duration: newValue,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status == 'success') {
                                location.reload();
                            } else {
                                location.reload();
                            }
                        }
                    });
                }
            } else {
                // Gérer l'édition de date/heure
                if (currentValue !== 'En cours' && currentValue) {
                    const [datePart, timePart] = currentValue.split(' ');
                    const [day, month, year] = datePart.split('-');
                    const formattedDate = `${year}-${month}-${day}`;
                    const formattedTime = timePart ? timePart.substring(0, 5) : '00:00';

                    document.getElementById('dateInput').value = formattedDate;
                    document.getElementById('timeInput').value = formattedTime;
                    document.getElementById('taskId').value = taskId;
                    document.getElementById('fieldName').value = field;

                    openModal('editDateModal'); // Appel modal
                }
            }
        });
    });

    // Gérer le bouton "Sauvegarder" dans le modal de date
    document.getElementById('saveChangesBtn').addEventListener('click', function() {
        const taskId = document.getElementById('taskId').value;
        const field = document.getElementById('fieldName').value;
        const date = document.getElementById('dateInput').value;
        const time = document.getElementById('timeInput').value;
        const newValue = `${date} ${time}`;

        var updateUrl;
        @if(Auth::user()->hasRole('Admin'))
            updateUrl = `/admin/tasks/update-${field}/${taskId}`;
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
            updateUrl = `/Responsable_de_projet/tasks/update-${field}/${taskId}`;
        @endif
        @if(Auth::user()->hasRole('Employee'))
            updateUrl = `/employee/tasks/update-${field}/${taskId}`;
        @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            updateUrl = `/Responsable_gestionnaire/tasks/update-${field}/${taskId}`;
        @endif

        $.ajax({
            url: updateUrl,
            type: 'POST',
            data: {
                [field]: newValue,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status === 'success') {
                    location.reload();
                } else {
                    alert('Échec de la mise à jour.');
                }
            }
        });
        closeModal('editDateModal');
    });

    // ==========================================================
    // DEBUT DU CODE DU SECOND SCRIPT (MAINTENANT FUSIONNÉ)
    // ==========================================================

    // Gérer les clics sur le bouton "Déplacer Durée"
    document.querySelectorAll('.move-duration-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-task-id');
            const projectId = this.getAttribute('data-project-id');
            const employeeId = this.getAttribute('data-employee-id');
            const startDate = this.getAttribute('data-start-date'); // (Assurez-vous que le HTML est corrigé en 'data-start_date')
            const endDate = this.getAttribute('data-end-date');
            const duration = this.getAttribute('data-duration');

            document.getElementById('projectSelect').value = projectId;
            document.getElementById('employeeSelect').value = employeeId;
            document.getElementById('moveStartDate').value = formatDateTimeForInput(startDate);
            document.getElementById('moveEndDate').value = formatDateTimeForInput(endDate);
            document.getElementById('moveDuration').value = minutesToHoursMinutes(parseInt(duration));
            document.getElementById('moveDurationMinutes').value = duration;
            document.getElementById('originalTaskId').value = taskId;

            loadTasksForProject(projectId, taskId);

            // Appel du modal Tailwind (fonctionnera maintenant)
            openModal('moveDurationModal');
        });
    });

    // Gérer le changement de sélection de projet
    document.getElementById('projectSelect').addEventListener('change', function() {
        loadTasksForProject(this.value);
    });

    // Formater la date/heure pour l'input
    function formatDateTimeForInput(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const userTimezoneOffset = date.getTimezoneOffset() * 60000;
        const localDate = new Date(date.getTime() - userTimezoneOffset);
        return localDate.toISOString().slice(0, 16);
    }

    // Charger les tâches pour un projet
    function loadTasksForProject(projectId, selectedTaskId = null) {
        let url;
        @if(Auth::user()->hasRole('Admin'))
            url = `/admin/tasks/get-project-tasks/${projectId}`;
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
            url = `/Responsable_de_projet/tasks/get-project-tasks/${projectId}`;
        @endif
        @if(Auth::user()->hasRole('Employee'))
            url = `/employee/tasks/get-project-tasks/${projectId}`;
        @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            url = `/Responsable_gestionnaire/tasks/get-project-tasks/${projectId}`;
        @endif

        fetch(url)
            .then(response => response.json())
            .then(tasks => {
                const taskSelect = document.getElementById('taskSelect');
                taskSelect.innerHTML = '';

                tasks.forEach(task => {
                    const option = new Option(task.name, task.id);
                    if (selectedTaskId && task.id == selectedTaskId) {
                        option.selected = true;
                    }
                    taskSelect.add(option);
                });
            });
    }

    // Convertir les minutes en format HH:MM
    function minutesToHoursMinutes(minutes) {
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        return `${hours.toString().padStart(2, '0')}h:${remainingMinutes.toString().padStart(2, '0')}m`;
    }

    // Convertir le format HH:MM en minutes
    function hoursMinutesToMinutes(durationString) {
        const matches = durationString.match(/^(\d{2})h:(\d{2})m$/);
        if (!matches) return null;

        const hours = parseInt(matches[1]);
        const minutes = parseInt(matches[2]);
        return (hours * 60) + minutes;
    }

    // Gérer l'état d'erreur pour les inputs Tailwind
    function setInputError(inputId, errorId, message, isError) {
        const input = document.getElementById(inputId);
        const errorEl = document.getElementById(errorId);
        if (isError) {
            input.classList.add('border-red-500', 'focus:ring-red-500');
            input.classList.remove('border-gray-300', 'focus:ring-blue-500');
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        } else {
            input.classList.remove('border-red-500', 'focus:ring-red-500');
            input.classList.add('border-gray-300', 'focus:ring-blue-500');
            errorEl.classList.add('hidden');
        }
    }

    // Auto-calculer la durée
    function calculateAndSetDuration() {
        const startValue = document.getElementById('moveStartDate')?.value;
        const endValue = document.getElementById('moveEndDate')?.value;
        const durationInput = document.getElementById('moveDuration');
        const durationMinutesInput = document.getElementById('moveDurationMinutes');

        if (!startValue || !endValue) return;

        const start = new Date(startValue);
        const end = new Date(endValue);
        const diffMs = end - start;

        if (Number.isNaN(diffMs) || diffMs < 0) {
            setInputError('moveEndDate', 'moveEndDateError', 'La date de fin doit être après la date de début.', true);
            durationInput.value = '00h:00m';
            durationMinutesInput.value = '0';
            return;
        } else {
            setInputError('moveEndDate', 'moveEndDateError', '', false);
        }

        const minutes = Math.floor(diffMs / 60000);
        durationInput.value = minutesToHoursMinutes(minutes);
        durationMinutesInput.value = String(minutes);
        setInputError('moveDuration', 'moveDurationError', '', false);
    }

    // Écouter les changements sur les inputs de date/heure
    const moveStartDateEl = document.getElementById('moveStartDate');
    const moveEndDateEl = document.getElementById('moveEndDate');
    if (moveStartDateEl) moveStartDateEl.addEventListener('change', calculateAndSetDuration);
    if (moveEndDateEl) moveEndDateEl.addEventListener('change', calculateAndSetDuration);

    // Valider le format de la durée
    document.getElementById('moveDuration').addEventListener('input', function(e) {
        const input = e.target;
        const minutes = hoursMinutesToMinutes(input.value);

        if (minutes !== null) {
            setInputError('moveDuration', 'moveDurationError', '', false);
            document.getElementById('moveDurationMinutes').value = minutes;
        } else {
            setInputError('moveDuration', 'moveDurationError', 'Veuillez entrer la durée au format : 00h:00m', true);
        }
    });

    // Gérer le bouton "Sauvegarder" du modal "Déplacer Durée"
    document.getElementById('saveMoveChangesBtn').addEventListener('click', function() {
        const durationInput = document.getElementById('moveDuration');
        const durationMinutes = document.getElementById('moveDurationMinutes').value;

        if (!durationInput.value.match(/^[0-9]{2}h:[0-9]{2}m$/)) {
            alert('Veuillez entrer la durée au format correct (00h:00m)');
            setInputError('moveDuration', 'moveDurationError', 'Veuillez entrer la durée au format : 00h:00m', true);
            return;
        }

        if (document.getElementById('moveEndDate').classList.contains('border-red-500')) {
             alert('La date de fin ne peut pas être antérieure à la date de début.');
             return;
        }

        const formData = {
            original_task_id: document.getElementById('originalTaskId').value,
            new_task_id: document.getElementById('taskSelect').value,
            employee_id: document.getElementById('employeeSelect').value,
            start_date: document.getElementById('moveStartDate').value,
            end_date: document.getElementById('moveEndDate').value,
            duration: durationMinutes,
            _token: '{{ csrf_token() }}'
        };

        let url;
        @if(Auth::user()->hasRole('Admin'))
            url = '/admin/tasks/move-duration';
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
            url = '/Responsable_de_projet/tasks/move-duration';
        @endif
        @if(Auth::user()->hasRole('Employee'))
            url = '/employee/tasks/move-duration';
        @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            url = '/Responsable_gestionnaire/tasks/move-duration';
        @endif

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    location.reload();
                } else {
                    alert('Échec du déplacement de la durée : ' + (response.message || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Détails de l\'erreur:', { status, error, response: xhr.responseText });
                alert('Erreur lors du déplacement de la durée : ' + error);
            }
        });

        closeModal('moveDurationModal');
    });


    document.querySelectorAll('.editable-comment').forEach(function(cell) {
        cell.addEventListener('dblclick', function() {
            var span = cell.querySelector('.comment-text');
            if (!span) return;
            var originalText = span.textContent || '';
            if (cell.getAttribute('data-editing') === '1') return;
            var updateUrl = cell.getAttribute('data-update-url');
            if (!updateUrl) return;
            cell.setAttribute('data-editing', '1');
            var input = document.createElement('textarea');
            input.className = 'block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-1 px-2';
            input.value = originalText;
            input.rows = 2;
            span.style.display = 'none';
            cell.appendChild(input);
            input.focus();
            input.select();
            var save = function(newValue) {
                var trimmed = (newValue || '').trim();
                if (trimmed === (originalText || '').trim()) { cancel(); return; }
                var field = cell.getAttribute('data-field') || 'comment';
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch(updateUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: field + '=' + encodeURIComponent(trimmed)
                }).then(resp => resp.json())
                .then(json => {
                    if (json.status === 'success') span.textContent = trimmed;
                    else { span.textContent = originalText; alert(json.message || 'Echec'); }
                }).catch(() => { span.textContent = originalText; alert('Erreur reseau'); })
                .finally(() => cleanup());
            };
            var cancel = () => cleanup();
            var cleanup = () => {
                if (input && input.parentNode) input.parentNode.removeChild(input);
                span.style.display = '';
                cell.removeAttribute('data-editing');
            };
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); save(input.value); }
                else if (e.key === 'Escape') { e.preventDefault(); cancel(); }
            });
            input.addEventListener('blur', () => save(input.value));
        });
    });

});
</script>
</body>
</html>
