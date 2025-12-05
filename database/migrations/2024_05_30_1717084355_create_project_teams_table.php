<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTeamsTable extends Migration
{
    public function up()
    {
        Schema::create('project_teams', function (Blueprint $table) {

        $table->id();
		$table->string('team_name');
		$table->integer('team_id')->unsigned();
		$table->integer('project_id')->unsigned();
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('project_teams');
    }
}
