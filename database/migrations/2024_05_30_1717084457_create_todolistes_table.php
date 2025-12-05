<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodolistesTable extends Migration
{
    public function up()
    {
        Schema::create('todolistes', function (Blueprint $table) {

		$table->id();
		$table->string('title');
		$table->string('start');
		$table->string('end');
		$table->string('description');
		$table->integer('user_id')->unsigned();
		$table->integer('status');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('todolistes');
    }
}
