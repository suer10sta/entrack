<?php

namespace App\Models;

use App\Models\calendars;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'user_id',
        'source_id',
        'source_type',
        'type',
        'title',
        'message',
        'metadata',
        'trigger_time',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'trigger_time' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Sync events happening within the next hour into alert records.
     */
    public static function syncUpcomingEventAlerts(int $entrepriseId, ?int $userId = null): void
    {
        $now = Carbon::now();
        $oneHourLater = $now->copy()->addHour();

        $events = calendars::where('entreprise_id', $entrepriseId)
            ->whereBetween('start_date', [$now, $oneHourLater])
            ->orderBy('start_date')
            ->get();

        foreach ($events as $event) {
            $startTime = Carbon::parse($event->start_date);
            $minutesUntil = max(0, $now->diffInMinutes($startTime, false));
            $timeMessage = self::formatTimeMessage($minutesUntil, $startTime);

            static::updateOrCreate(
                [
                    'entreprise_id' => $entrepriseId,
                    'source_id' => $event->id,
                    'source_type' => calendars::class,
                    'type' => 'event',
                ],
                [
                    'user_id' => $userId,
                    'title' => $event->title,
                    'message' => $timeMessage,
                    'metadata' => [
                        'event_id' => $event->id,
                        'description' => $event->description,
                        'start' => $event->start_date,
                        'end' => $event->end_date,
                        'minutes_until' => $minutesUntil,
                    ],
                    'trigger_time' => $startTime,
                ]
            );
        }
    }

    protected static function formatTimeMessage(int $minutesUntil, Carbon $startTime): string
    {
        if ($minutesUntil <= 0) {
            return 'Event starting now at '.$startTime->format('H:i');
        }

        if ($minutesUntil < 60) {
            $unit = $minutesUntil === 1 ? 'minute' : 'minutes';
            return "Event in {$minutesUntil} {$unit} (".$startTime->format('H:i').')';
        }

        $hours = intdiv($minutesUntil, 60);
        $mins = $minutesUntil % 60;

        if ($mins === 0) {
            $unit = $hours === 1 ? 'hour' : 'hours';
            return "Event in {$hours} {$unit} (".$startTime->format('H:i').')';
        }

        $hourLabel = $hours === 1 ? 'hour' : 'hours';
        $minuteLabel = $mins === 1 ? 'minute' : 'minutes';

        return "Event in {$hours} {$hourLabel} and {$mins} {$minuteLabel} (".$startTime->format('H:i').')';
    }
}

