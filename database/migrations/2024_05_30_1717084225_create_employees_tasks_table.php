<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTasksTable extends Migration
{
    public function up()
    {
        Schema::create('employees_tasks', function (Blueprint $table) {

        $table->id();
		$table->string('task_name');
		$table->integer('task_id');
		$table->integer('employee_id');
		$table->string('employee_name');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('employees_tasks');
    }
}
