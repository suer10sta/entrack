<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class UpdateTasksTimerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tasks_timer records
        $tasksTimers = DB::table('tasks_timer')->get();

        foreach ($tasksTimers as $tasksTimer) {
            // Get the project_id for the given task_id
            $task = DB::table('tasks')->where('id', $tasksTimer->task_id)->first();

            if ($task) {
                // Update the project_id in the tasks_timer table
                DB::table('tasks_timer')
                    ->where('id', $tasksTimer->id)
                    ->update(['project_id' => $task->project_id]);
            }
        }
    }
}
