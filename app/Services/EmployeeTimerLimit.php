<?php

namespace App\Services;

use App\Models\Employees;
use App\Models\tasks_timer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeTimerLimit
{
    /**
     * Keep the existing daily rule: minutes are charged to the timer's start date.
     * Reconcile atomically and return the next deadline for the browser ticker.
     */
    public function synchronize(Employees $employee): array
    {
        $result = ['stopped' => 0, 'auto_stop_in_seconds' => null];
        if ((float) $employee->workHours_limit <= 0) {
            return $result;
        }

        return DB::transaction(function () use ($employee, $result) {
            $employee = Employees::whereKey($employee->id)->lockForUpdate()->first();
            $limit = (float) $employee->workHours_limit * 3600;
            if ($limit <= 0) return $result;

            $user = User::find($employee->user_id);
            $zone = $user?->timezone ?: config('app.timezone', 'UTC');
            if (!in_array($zone, timezone_identifiers_list(), true)) $zone = config('app.timezone', 'UTC');
            $now = Carbon::now($zone);
            $open = tasks_timer::where('employee_id', $employee->id)->whereNull('end_date')
                ->orderBy('start_date')->orderBy('id')->lockForUpdate()->get();

            foreach ($open->groupBy(fn ($timer) => Carbon::parse($timer->start_date, $zone)->toDateString()) as $day => $timers) {
                $completed = (float) tasks_timer::where('employee_id', $employee->id)
                    ->whereDate('start_date', $day)->whereNotNull('end_date')->sum('duration') * 60;
                $segments = $timers->map(function ($timer) use ($zone) {
                    $paused = (int) $timer->paused === 1;
                    return [
                        'timer' => $timer,
                        'saved' => $paused || $timer->resume_date ? max(0, (float) $timer->duration * 60) : 0,
                        'anchor' => Carbon::parse($timer->resume_date ?: $timer->start_date, $zone)->getTimestamp(),
                        'paused' => $paused,
                    ];
                });
                $active = $segments->where('paused', false);
                $secondsAt = fn ($segment, $at) => $segment['saved'] + ($segment['paused'] ? 0 : max(0, $at - $segment['anchor']));
                $totalAt = fn ($at) => $completed + $segments->sum(fn ($segment) => $secondsAt($segment, $at));
                $total = $totalAt($now->getTimestamp());

                if ($total < $limit) {
                    if ($active->isNotEmpty()) {
                        $remaining = (int) ceil(($limit - $total) / $active->count());
                        $result['auto_stop_in_seconds'] = $result['auto_stop_in_seconds'] === null
                            ? $remaining : min($remaining, $result['auto_stop_in_seconds']);
                    }
                    continue;
                }

                // Locate the instant the aggregate budget was reached, rather than
                // using the later polling/cron time or start + duration (which loses pauses).
                $end = $now->getTimestamp();
                if ($active->isNotEmpty()) {
                    $low = min($end, $active->min('anchor'));
                    $high = $end;
                    while ($low < $high) {
                        $middle = (int) floor(($low + $high) / 2);
                        if ($totalAt($middle) >= $limit) $high = $middle;
                        else $low = $middle + 1;
                    }
                    $end = $low;
                }

                $available = max(0, $limit - $completed);
                foreach ($segments as $segment) {
                    $timer = $segment['timer'];
                    $seconds = min($available, $secondsAt($segment, $end));
                    $available = max(0, $available - $seconds);
                    $endedAt = Carbon::createFromTimestamp($end, $zone);
                    if ($segment['paused'] && $timer->pause_date) {
                        $endedAt = $endedAt->min(Carbon::parse($timer->pause_date, $zone));
                    }
                    $endedAt = $endedAt->max(Carbon::parse($timer->start_date, $zone));
                    $timer->duration = $seconds / 60;
                    $timer->cost = (float) $employee->cost_per_hour * $seconds / 3600;
                    $timer->end_date = $endedAt->format('Y-m-d H:i:s');
                    $timer->paused = 0;
                    $timer->save();
                    $result['stopped']++;
                }
            }

            if ($result['stopped'] && !tasks_timer::where('employee_id', $employee->id)->whereNull('end_date')->exists()) {
                User::whereKey($employee->user_id)->update(['is_loggedIn' => 0]);
            }

            return $result;
        });
    }

    public function synchronizeAll(): void
    {
        Employees::whereIn('id', tasks_timer::whereNull('end_date')->select('employee_id'))
            ->where('workHours_limit', '>', 0)->eachById(fn ($employee) => $this->synchronize($employee));
    }
}
