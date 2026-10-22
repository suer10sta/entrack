<?php

namespace Tests\Feature;

use App\Http\Controllers\admin\tasksController;
use App\Models\Employees;
use App\Models\Project;
use App\Models\User;
use App\Services\TimeTransferService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PointageMoveCommentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        Schema::create('projects', function ($table) {
            $table->id(); $table->string('name'); $table->integer('entreprise_id'); $table->integer('archived')->default(0); $table->timestamps();
        });
        Schema::create('tasks', function ($table) {
            $table->id(); $table->string('title'); $table->integer('project_id'); $table->string('project_name')->nullable();
            $table->integer('entreprise_id'); $table->timestamps();
        });
        Schema::create('employees', function ($table) {
            $table->id(); $table->string('name'); $table->double('cost_per_hour'); $table->timestamps();
        });
        Schema::create('employees_tasks', function ($table) {
            $table->id(); $table->integer('task_id'); $table->integer('employee_id'); $table->string('task_name');
            $table->string('employee_name'); $table->integer('entreprise_id'); $table->timestamps();
        });
        Schema::create('tasks_timer', function ($table) {
            $table->id();
            foreach (['task_id', 'task_employee_id', 'project_id', 'employee_id', 'entreprise_id'] as $field) $table->integer($field);
            $table->string('task_name'); $table->string('start_date'); $table->string('end_date');
            $table->double('duration'); $table->double('cost'); $table->integer('paused')->default(0); $table->text('comment')->nullable(); $table->timestamps();
        });
        DB::table('projects')->insert([['id' => 1, 'name' => 'Source', 'entreprise_id' => 9], ['id' => 2, 'name' => 'Destination', 'entreprise_id' => 9]]);
        DB::table('tasks')->insert([['id' => 1, 'title' => 'Original', 'project_id' => 1, 'entreprise_id' => 9], ['id' => 2, 'title' => 'Target', 'project_id' => 2, 'entreprise_id' => 9]]);
        DB::table('employees')->insert([['id' => 1, 'name' => 'Original employee', 'cost_per_hour' => 60], ['id' => 2, 'name' => 'Target employee', 'cost_per_hour' => 90]]);
        $user = \Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 1, 'entreprise_id' => 9]);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Admin');
        $this->actingAs($user);
    }

    private function source(?string $comment): int
    {
        return DB::table('tasks_timer')->insertGetId(['task_id' => 1, 'task_employee_id' => 7, 'project_id' => 1,
            'employee_id' => 1, 'entreprise_id' => 9, 'task_name' => 'Original', 'start_date' => '2026-10-22 09:00:00',
            'end_date' => '2026-10-22 11:00:00', 'duration' => 120, 'cost' => 120, 'comment' => $comment]);
    }

    private function move(int $source, int $minutes = 120, int $employee = 1): object
    {
        $response = app(tasksController::class)->moveDuration(Request::create('/move-duration', 'POST', [
            'original_task_id' => $source, 'new_task_id' => 2, 'employee_id' => $employee,
            'start_date' => '2026-10-22 09:00:00', 'end_date' => '2026-10-22 11:00:00', 'duration' => $minutes,
        ]));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('success', $response->getData(true)['status']);
        return DB::table('tasks_timer')->where('id', '!=', $source)->latest('id')->first();
    }

    public function test_full_move_to_another_task_and_project_preserves_exact_comment(): void
    {
        $comment = "  Correction facture client\nRéférence A-42 — 'urgent' <texte>  ";
        $id = $this->source($comment);
        $target = $this->move($id);
        $this->assertSame($comment, $target->comment);
        $this->assertEquals(120, $target->duration);
        $this->assertEquals(120, $target->cost);
        $this->assertEquals(2, $target->task_id);
        $this->assertEquals(2, $target->project_id);
        $this->assertNull(DB::table('tasks_timer')->find($id));
    }

    public function test_partial_move_keeps_comment_on_both_parts_and_preserves_totals(): void
    {
        $id = $this->source('Correction facture client');
        $target = $this->move($id, 45);
        $original = DB::table('tasks_timer')->find($id);
        $this->assertSame('Correction facture client', $target->comment);
        $this->assertSame($original->comment, $target->comment);
        $this->assertEquals(75, $original->duration);
        $this->assertEquals(45, $target->duration);
        $this->assertEquals(120, $original->cost + $target->cost);
    }

    public function test_move_to_another_employee_keeps_comment_and_existing_cost_rule(): void
    {
        $id = $this->source('Correction facture client');
        $target = $this->move($id, 120, 2);
        $this->assertSame('Correction facture client', $target->comment);
        $this->assertEquals(2, $target->employee_id);
        $this->assertEquals(180, $target->cost);
    }

    public function test_null_and_empty_comments_are_preserved_without_inventing_text(): void
    {
        foreach ([null, ''] as $comment) {
            $id = $this->source($comment);
            $this->assertSame($comment, $this->move($id)->comment);
        }
    }

    public function test_all_role_routes_share_the_fixed_controller_action(): void
    {
        foreach (['admin', 'employee', 'Responsable_de_projet', 'Responsable_gestionnaire', 'SuperAdmin'] as $prefix) {
            $route = app('router')->getRoutes()->match(Request::create('/'.$prefix.'/tasks/move-duration', 'POST'));
            $this->assertSame(tasksController::class.'@moveDuration', $route->getActionName());
        }
    }

    public function test_moving_entire_task_to_another_project_keeps_existing_pointage_comment(): void
    {
        $id = $this->source('Correction facture client');
        $response = app(tasksController::class)->moveTask(Request::create('/move-task', 'POST', ['task_id' => 1, 'new_project_id' => 2]));
        $this->assertSame(200, $response->getStatusCode());
        $timer = DB::table('tasks_timer')->find($id);
        $this->assertSame('Correction facture client', $timer->comment);
        $this->assertEquals(120, $timer->duration);
        $this->assertEquals(2, $timer->project_id);
    }

    public function test_csv_export_import_already_preserves_comment(): void
    {
        $this->source('Correction facture client');
        DB::table('tasks')->where('id', 2)->update(['title' => 'Original']);
        $service = app(TimeTransferService::class);
        $rows = $service->exportProjectTimers(Project::find(1), Carbon::parse('2026-10-22'), Carbon::parse('2026-10-22'));
        $parsed = $service->parseCsv($service->rowsToCsv($rows));
        $service->import(Project::find(2), Employees::find(2), $parsed, 1);
        $target = DB::table('tasks_timer')->where('project_id', 2)->first();
        $this->assertSame('Correction facture client', $target->comment);
        $this->assertEquals(120, $target->duration);
    }
}
