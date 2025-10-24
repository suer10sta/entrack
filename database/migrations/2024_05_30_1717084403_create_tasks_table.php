<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {

		$table->id();
		$table->string('title');
		$table->date('start');
		$table->date('end');
		$table->string('description');
		$table->string('user_id')->unsigned();
		$table->string('project_id')->unsigned();
		$table->string('project_name');
		$table->string('status')->unsigned()->default('0');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}