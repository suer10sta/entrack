<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTimerTable extends Migration
{
    public function up()
    {
        Schema::create('tasks_timer', function (Blueprint $table) {

		$table->id();
		$table->integer('task_employee_id')->unsigned();
		$table->timestamp('start_date')->nullable();
		$table->timestamp('end_date')->nullable();
		$table->integer('duration')->nullable();
		$table->float('cost')->nullable();		
		$table->string('task_name');
		$table->integer('employee_id')->unsigned()->default('0');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks_timer');
    }
}
