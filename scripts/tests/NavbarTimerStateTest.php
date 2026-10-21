<?php

namespace Tests\Feature;

use App\Http\Controllers\admin\tasksController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NavbarTimerStateTest extends TestCase
{
    private string $originalTimezone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalTimezone = date_default_timezone_get();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        Schema::create('employees', function ($table) {
            $table->id(); $table->integer('user_id'); $table->integer('workHours_limit')->default(0);
        });
        Schema::create('tasks_timer', function ($table) {
            $table->id(); $table->integer('employee_id'); $table->integer('task_employee_id');
            foreach (['start_date', 'end_date', 'resume_date', 'duration', 'paused'] as $column) $table->string($column)->nullable();
        });
        DB::table('employees')->insert(['id' => 2, 'user_id' => 1]);
        $user = \Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 1]);
        $user->shouldReceive('hasRole')->andReturn(true);
        $this->actingAs($user);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        date_default_timezone_set($this->originalTimezone);
        parent::tearDown();
    }

    public function test_elapsed_is_server_calculated_in_every_supported_user_timezone(): void
    {
        foreach (['UTC', 'Europe/Brussels', 'Africa/Casablanca', 'America/New_York'] as $zone) {
            date_default_timezone_set($zone);
            Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:23', $zone));
            DB::table('tasks_timer')->delete();
            DB::table('tasks_timer')->insert(['employee_id' => 2, 'task_employee_id' => 7, 'start_date' => '2026-10-21 12:00:00', 'paused' => '0', 'duration' => '0']);
            $data = app(tasksController::class)->get_timer_state()->getData(true);
            $this->assertSame(23, $data['tasksTimer']['elapsed_seconds']);
            $this->assertSame(23, $data['allTimers'][0]['elapsed_seconds']);
            $this->assertSame(Carbon::parse('2026-10-21 12:00:00', $zone)->getTimestamp() * 1000, $data['tasksTimer']['started_at_ms']);
            $this->assertSame('2026-10-21 12:00:00', $data['tasksTimer']['start_date']);
        }
    }

    public function test_pause_resume_and_new_timer_elapsed_values(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:23'));
        foreach ([['0', '2026-10-21 12:00:23', null, '0', 0], ['1', '2026-10-21 11:00:00', null, '2.5', 150], ['0', '2026-10-21 11:00:00', '2026-10-21 12:00:00', '2.5', 173]] as [$paused, $start, $resume, $duration, $expected]) {
            DB::table('tasks_timer')->delete();
            DB::table('tasks_timer')->insert(['employee_id' => 2, 'task_employee_id' => 7, 'start_date' => $start, 'resume_date' => $resume, 'paused' => $paused, 'duration' => $duration]);
            $data = app(tasksController::class)->get_timer_state()->getData(true);
            $this->assertSame($expected, $data['tasksTimer']['elapsed_seconds']);
            $this->assertSame($expected, $data['allTimers'][0]['elapsed_seconds']);
        }
    }
}
