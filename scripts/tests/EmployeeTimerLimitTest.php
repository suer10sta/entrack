<?php

namespace Tests\Feature;

use App\Http\Controllers\admin\tasksController;
use App\Models\Employees;
use App\Models\User;
use App\Services\EmployeeTimerLimit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeTimerLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        Schema::create('users', function ($table) {
            $table->id(); $table->string('timezone')->nullable(); $table->integer('is_loggedIn')->default(1); $table->timestamps();
        });
        Schema::create('employees', function ($table) {
            $table->id(); $table->integer('user_id'); $table->decimal('workHours_limit', 12, 4)->default(8);
            $table->decimal('cost_per_hour', 12, 4)->default(60); $table->timestamps();
        });
        Schema::create('employees_tasks', function ($table) {
            $table->id(); $table->integer('employee_id'); $table->integer('task_id')->default(1); $table->string('task_name')->default('Task');
        });
        Schema::create('tasks_timer', function ($table) {
            $table->id(); $table->integer('employee_id'); $table->integer('task_employee_id');
            foreach (['start_date', 'end_date', 'pause_date', 'resume_date', 'comment'] as $field) $table->string($field)->nullable();
            $table->double('duration')->default(0); $table->double('cost')->default(0); $table->integer('paused')->default(0); $table->timestamps();
        });
        Schema::create('work_days_repports', function ($table) {
            $table->id(); $table->integer('employee_id'); $table->integer('month'); $table->integer('year');
        });
        DB::table('users')->insert(['id' => 1, 'timezone' => 'UTC']);
        DB::table('employees')->insert(['id' => 2, 'user_id' => 1]);
        DB::table('employees_tasks')->insert(['id' => 7, 'employee_id' => 2]);
        Carbon::setTestNow(Carbon::parse('2026-10-22 17:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function timer(array $values = []): int
    {
        return DB::table('tasks_timer')->insertGetId($values + ['employee_id' => 2, 'task_employee_id' => 7, 'start_date' => '2026-10-22 09:00:00']);
    }

    private function sync(): array
    {
        return app(EmployeeTimerLimit::class)->synchronize(Employees::findOrFail(2));
    }

    public function test_under_limit_and_unlimited_timers_are_not_changed(): void
    {
        $id = $this->timer();
        Carbon::setTestNow(Carbon::parse('2026-10-22 16:59:00', 'UTC'));
        $this->assertSame(['stopped' => 0, 'auto_stop_in_seconds' => 60], $this->sync());
        $this->assertNull(DB::table('tasks_timer')->where('id', $id)->value('end_date'));
        DB::table('employees')->update(['workHours_limit' => 0]);
        Carbon::setTestNow(Carbon::parse('2026-10-23 23:00:00', 'UTC'));
        $this->assertSame(['stopped' => 0, 'auto_stop_in_seconds' => null], $this->sync());
        $this->assertNull(DB::table('tasks_timer')->where('id', $id)->value('end_date'));
    }

    public function test_exact_limit_stops_and_records_duration_cost_and_off_work_status(): void
    {
        $id = $this->timer(['comment' => 'Existing note']);
        $this->assertSame(1, $this->sync()['stopped']);
        $timer = DB::table('tasks_timer')->find($id);
        $this->assertEquals(480, $timer->duration);
        $this->assertEquals(480, $timer->cost);
        $this->assertSame('2026-10-22 17:00:00', $timer->end_date);
        $this->assertSame('Existing note', $timer->comment);
        $this->assertEquals(0, DB::table('users')->value('is_loggedIn'));
        $this->assertSame(0, $this->sync()['stopped']);
    }

    public function test_delayed_check_caps_at_exact_limit_and_leaves_manual_entries_untouched(): void
    {
        $manual = $this->timer(['start_date' => '2026-10-22 07:00:00', 'end_date' => '2026-10-22 09:00:00', 'duration' => 120, 'cost' => 120, 'comment' => 'Manual']);
        $before = DB::table('tasks_timer')->find($manual);
        $id = $this->timer(['start_date' => '2026-10-22 11:00:00']);
        Carbon::setTestNow(Carbon::parse('2026-10-22 19:00:00', 'UTC'));
        $this->sync();
        $timer = DB::table('tasks_timer')->find($id);
        $this->assertEquals(360, $timer->duration);
        $this->assertSame('2026-10-22 17:00:00', $timer->end_date);
        $this->assertEquals(480, DB::table('tasks_timer')->sum('duration'));
        $this->assertEquals($before, DB::table('tasks_timer')->find($manual));
    }

    public function test_pause_then_resume_excludes_pause_and_retains_correct_wall_clock_end(): void
    {
        $id = $this->timer();
        Carbon::setTestNow(Carbon::parse('2026-10-22 12:00:00', 'UTC'));
        app(tasksController::class)->pause(7, 2);
        Carbon::setTestNow(Carbon::parse('2026-10-22 14:00:00', 'UTC'));
        $this->assertSame(['stopped' => 0, 'auto_stop_in_seconds' => null], $this->sync());
        $this->assertEquals(180, DB::table('tasks_timer')->find($id)->duration);
        app(tasksController::class)->resume(7, 2);
        $this->assertSame(18000, $this->sync()['auto_stop_in_seconds']);
        Carbon::setTestNow(Carbon::parse('2026-10-22 19:30:00', 'UTC'));
        $this->sync();
        $timer = DB::table('tasks_timer')->find($id);
        $this->assertEquals(480, $timer->duration);
        $this->assertSame('2026-10-22 19:00:00', $timer->end_date);
        $this->assertEquals(0, $timer->paused);
    }

    public function test_concurrent_timers_share_daily_budget_and_stop_at_same_instant(): void
    {
        $first = $this->timer();
        $second = $this->timer(['task_employee_id' => 8, 'start_date' => '2026-10-22 10:00:00']);
        Carbon::setTestNow(Carbon::parse('2026-10-22 14:00:00', 'UTC'));
        $this->assertSame(2, $this->sync()['stopped']);
        $this->assertEquals(270, DB::table('tasks_timer')->find($first)->duration);
        $this->assertEquals(210, DB::table('tasks_timer')->find($second)->duration);
        foreach ([$first, $second] as $id) $this->assertSame('2026-10-22 13:30:00', DB::table('tasks_timer')->find($id)->end_date);
    }

    public function test_navbar_endpoint_stops_all_timers_and_returns_idle_for_task_synchronization(): void
    {
        $this->timer();
        $user = \Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 1]);
        $user->shouldReceive('hasRole')->andReturn(true);
        $this->actingAs($user);
        $data = app(tasksController::class)->get_timer_state()->getData(true);
        $this->assertTrue($data['auto_stopped']);
        $this->assertNull($data['tasksTimer']);
        $this->assertSame([], $data['allTimers']);
        $this->assertNull($data['auto_stop_in_seconds']);
        $this->assertSame(0, DB::table('tasks_timer')->whereNull('end_date')->count());
    }

    public function test_command_resolves_dependencies_and_uses_employee_timezone(): void
    {
        DB::table('users')->update(['timezone' => 'Europe/Brussels']);
        Carbon::setTestNow(Carbon::parse('2026-10-22 15:00:00', 'UTC'));
        $id = $this->timer();
        $this->artisan('tasks:check-running')->expectsOutput('Running tasks check completed')->assertExitCode(0);
        $this->assertSame('2026-10-22 17:00:00', DB::table('tasks_timer')->find($id)->end_date);
        $this->assertEquals(480, DB::table('tasks_timer')->find($id)->duration);
    }

    public function test_start_of_new_day_does_not_consume_previous_days_pointage(): void
    {
        $this->timer(['start_date' => '2026-10-21 09:00:00', 'end_date' => '2026-10-21 17:00:00', 'duration' => 480]);
        $this->timer();
        Carbon::setTestNow(Carbon::parse('2026-10-22 10:00:00', 'UTC'));
        $this->assertSame(25200, $this->sync()['auto_stop_in_seconds']);
    }

    public function test_normal_stop_at_limit_keeps_comment_and_matches_automatic_duration(): void
    {
        $id = $this->timer();
        $request = \Illuminate\Http\Request::create('/stop', 'POST', ['stop_comment' => 'Completed plans']);
        app(tasksController::class)->stop($request, 7, 2);
        $timer = DB::table('tasks_timer')->find($id);
        $this->assertEquals(480, $timer->duration);
        $this->assertSame('Completed plans', $timer->comment);
        $this->assertSame('2026-10-22 17:00:00', $timer->end_date);
    }

    public function test_start_and_pause_at_limit_cannot_reactivate_employee(): void
    {
        $id = $this->timer();
        app(tasksController::class)->pause(7, 2);
        $this->assertSame('2026-10-22 17:00:00', DB::table('tasks_timer')->find($id)->end_date);
        app(tasksController::class)->start(\Illuminate\Http\Request::create('/start', 'POST'), 7, 2);
        $this->assertSame(0, DB::table('tasks_timer')->whereNull('end_date')->count());
        $this->assertEquals(0, DB::table('users')->value('is_loggedIn'));
    }

    public function test_synchronizing_one_employee_never_changes_another_employee(): void
    {
        DB::table('users')->insert(['id' => 3, 'timezone' => 'UTC']);
        DB::table('employees')->insert(['id' => 4, 'user_id' => 3]);
        $this->timer();
        $other = $this->timer(['employee_id' => 4]);
        $this->sync();
        $this->assertNull(DB::table('tasks_timer')->find($other)->end_date);
        $this->assertEquals(1, DB::table('users')->where('id', 3)->value('is_loggedIn'));
    }

    public function test_decimal_hour_limit_and_seconds_are_not_rounded_to_whole_minutes(): void
    {
        DB::table('employees')->update(['workHours_limit' => 0.125]);
        $id = $this->timer();
        Carbon::setTestNow(Carbon::parse('2026-10-22 09:07:29', 'UTC'));
        $this->assertSame(1, $this->sync()['auto_stop_in_seconds']);
        Carbon::setTestNow(Carbon::parse('2026-10-22 09:08:00', 'UTC'));
        $this->sync();
        $timer = DB::table('tasks_timer')->find($id);
        $this->assertEquals(7.5, $timer->duration);
        $this->assertSame('2026-10-22 09:07:30', $timer->end_date);
    }
}
