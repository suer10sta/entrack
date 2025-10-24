<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

		$table->id();
		$table->string('name');
		$table->string('email');
		$table->timestamp('email_verified_at')->nullable();
		$table->string('password');
		$table->string('remember_token')->nullable();
		$table->integer('is_activate')->default('1');
		$table->integer('is_employee')->default('0');
		$table->integer('is_loggedIn')->nullable();
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}