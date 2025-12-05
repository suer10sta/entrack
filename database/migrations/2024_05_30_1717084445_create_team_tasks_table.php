<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamTasksTable extends Migration
{
    public function up()
    {
        Schema::create('team_tasks', function (Blueprint $table) {
        $table->id();
		$table->string('task_name');
		$table->bigInteger('team_id')->unsigned();
		$table->bigInteger('task_id')->unsigned();
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();     
        });
    }

    public function down()
    {
        Schema::dropIfExists('team_tasks');
    }
}
