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
		$table->bigInteger('user_id')->unsigned();
		$table->bigInteger('project_id')->unsigned();
		$table->string('project_name');
		$table->string('status')->default('0');
		$table->bigInteger('entreprise_id')->unsigned()->default(0);
		$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}