@php
    // Ensure $projectsToday is available. 
    // If not passed (e.g. from controller), it will be null, but the controller should pass it.
    // In content.blade.php, we pass it explicitly.
    // We also need $avatarColors and $todayStart/$todayEnd if we want to be safe, but 
    // looking at the loop, only $avatarColors and $project are used heavily.
    // Ideally, the logic inside the loop that calculates duration should also be moved, but
    // for now we will copy the logic inside the loop as is.

    $avatarColors = ['bg-emerald-500', 'bg-gray-400', 'bg-amber-500', 'bg-red-500', 'bg-purple-500', 'bg-sky-500', 'bg-rose-800', 'bg-slate-700', 'bg-yellow-400', 'bg-indigo-500'];
    $hasWorkedToday = $projectsToday->isNotEmpty();
    $todayStart = \Carbon\Carbon::today();
    $todayEnd = \Carbon\Carbon::today()->endOfDay();
@endphp

<div class="divide-y divide-gray-200">
    @foreach($projectsToday as $project)
        @php
            $projecttasksId = \App\Models\Tasks::where('project_id', $project->id)->pluck('id');
            // Calculate today's work duration for this project
            $today = \Carbon\Carbon::today();
            $todayDuration = \App\Models\tasks_timer::where(function ($q) use ($projecttasksId, $project) {
                $q->whereIn('task_id', $projecttasksId)
                    ->orWhere('project_id', $project->id);
            })
            ->whereDate('start_date', $today)
            ->whereNotNull('duration')
            ->sum('duration');
            
            // Calculate total duration for comparison with estimated hours
            $totalduration = \App\Models\tasks_timer::where(function ($q) use ($projecttasksId, $project) {
                $q->whereIn('task_id', $projecttasksId)
                    ->orWhere('project_id', $project->id);
            })->sum('duration');
            
            $difference = ($project->estimated_hours ?? 0) * 60 - $totalduration;
            $hours = floor(abs($todayDuration) / 60);
            $minutes = abs($todayDuration) % 60;
            $diffhours = floor(abs($difference) / 60);
            $diffminutes = abs($difference) % 60;
            $estimatedMinutes = ($project->estimated_hours ?? 0) * 60;
            $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
            $formattedProgressPercentage = number_format($progressPercentage, 2);
        @endphp

        <div class="px-6 py-4 grid grid-cols-12 gap-4 items-center hover:bg-gray-50">

            {{-- PROJECT INFO (col-span-3) --}}
            <div class="col-span-3 flex items-center gap-3">
                <div
                    class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full text-white font-semibold {{ $avatarColors[$loop->index % count($avatarColors)] }}">
                    {{ strtoupper(substr($project->name, 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $project->name }}</p>
                    <p class="text-xs text-gray-500">{{ __('messages.Est. Hours') }}: {{ $project->estimated_hours ?? 'N/A' }}h</p>
                </div>
            </div>

            {{-- BUDGET & COSTS (col-span-2) --}}
            <div class="col-span-2 text-sm space-y-1">
                <p class="text-gray-600"><span class="text-gray-500">{{__('messages.Budget')}}:</span>
                    <span class="font-medium text-gray-800">€{{ number_format($project->budget ?? 0, 2, ',', ' ') }}</span>
                </p>
                <p class="text-gray-600"><span class="text-gray-500">{{__('messages.CPH')}}:</span>
                    <span
                        class="font-medium text-gray-800">€{{ number_format($project->cost_per_hour ?? 0, 2, ',', ' ') }}</span>
                </p>
                <p class="text-gray-600"><span class="text-gray-500">{{__('messages.VAT')}}:</span>
                    <span class="font-medium text-gray-800">{{ $project->tva ?? 0 }}%</span>
                </p>
            </div>

            {{-- TIME TRACKING (col-span-2) --}}
            <div class="col-span-2 text-sm space-y-1">
                <p class="text-gray-600"><span class="text-gray-500">{{__('messages.Duration')}}:</span>
                    <span class="font-bold text-gray-800">{{ $hours }}h:{{ $minutes }}m</span>
                </p>
                <p class="font-medium 
                                                            @if($difference > 0) text-green-500 border-b-2 border-green-500
                                                            @elseif($difference < 0) text-red-500 border-b-2 border-red-500
                                                            @else text-blue-500 border-b-2 border-blue-500 @endif
                                                            border-dotted w-fit">
                    @if($difference > 0)+@elseif($difference < 0)-@endif{{ $diffhours }}h:{{ $diffminutes }}m
                </p>
            </div>

            {{-- PROGRESS (col-span-1) --}}
            <div class="col-span-1">
                @if($project->estimated_hours !== null)
                    <div class="flex flex-col gap-1 w-full">
                        <span class="text-xs font-medium text-gray-700">{{ $formattedProgressPercentage }}%</span>
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full
                                                                                                                    @if($progressPercentage < 50) bg-emerald-500
                                                                                                                    @elseif($progressPercentage < 75) bg-amber-500
                                                                                                                    @else bg-red-500 @endif"
                                style="width: {{ min($progressPercentage, 100) }}%;">
                            </div>
                        </div>
                    </div>
                @else
                    <span class="text-xs font-medium text-gray-600">{{ __('messages.No Estimated Hours') }}</span>
                @endif
            </div>

            {{-- CLIENT & EMPLOYEES (col-span-3) --}}
            <div class="col-span-3 space-y-2">
                <div>
                    <p class="text-xs text-gray-500">{{__('messages.Client')}}:</p>
                    @if($project->client)
                        <p class="text-sm font-medium text-blue-600">{{ $project->client->name }}</p>
                    @else
                        <p class="text-sm font-medium text-gray-500">N/A</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('messages.Employees') }} :</p>
                    @php
                        // Get employees who worked on this project today
                        $projectEmployeeIds = \App\Models\tasks_timer::where(function ($q) use ($project, $todayStart, $todayEnd) {
                            $q->where('project_id', $project->id)
                                ->orWhereIn('task_id', \App\Models\Tasks::where('project_id', $project->id)->pluck('id'));
                        })
                            ->where(function ($q) use ($todayStart, $todayEnd) {
                                $q->where('start_date', '<=', $todayEnd)
                                    ->where(function ($qq) use ($todayStart) {
                                        $qq->whereNull('end_date')
                                            ->orWhere('end_date', '>=', $todayStart);
                                    });
                            })
                            ->distinct()
                            ->pluck('employee_id');
                        $projectEmployees = \App\Models\Employees::whereIn('id', $projectEmployeeIds)->get();
                    @endphp
                    <div class="flex flex-wrap gap-1 mt-1">
                        @forelse($projectEmployees as $emp)
                            <span
                                class="inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded">{{ $emp->name }}</span>
                        @empty
                            <span class="text-xs text-gray-500">{{ __('messages.No employees worked today') }}</span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ACTIONS (col-span-1) --}}
            <div class="col-span-1 flex justify-end gap-2">
                <a href="{{ route('admin.projects.show', $project->id) }}" title="View project"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#0B2638] text-white hover:bg-[#14364d] transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-.07.207-.07.431 0 .639C20.268 16.057 16.477 19 12 19c-4.478 0-8.268-2.943-9.542-7a1.186 1.186 0 010-.639z" />
                    </svg>
                </a>
                <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" title="Delete project" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 23 23">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>

{{-- No projects worked today message --}}
@if(!$hasWorkedToday)
    <div class="px-6 py-10 text-center">
        <div class="dt-empty-icon mx-auto mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <p class="text-[13.5px] font-semibold text-[#0B2638] mb-1">{{ __('messages.No Active Projects Today') }}</p>
        <p class="text-[13px] text-slate-500 max-w-sm mx-auto">{{ __('messages.No projects have logged work today. Start tracking time to see it here.') }}</p>
    </div>
@endif