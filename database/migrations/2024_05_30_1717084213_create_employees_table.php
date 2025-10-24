<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {

		$table->id();
		$table->string('name');
		$table->string('email');
		$table->string('password');
		$table->string('phoneNumber');
		$table->integer('age');
		$table->string('cnss');
		$table->string('bankAccountNumber');
		$table->date('Date_Entree');
		$table->date('Date_Release');
		$table->integer('workHours_limit');
		$table->string('image')->nullable();
		$table->integer('user_id')->unsigned();
		$table->integer('status')->default('0');
		$table->integer('salary');
		$table->integer('Cotisation_CNSS');
		$table->integer('Cotisation_AMO');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}