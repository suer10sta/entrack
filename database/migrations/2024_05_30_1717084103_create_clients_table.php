<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {

		$table->id();
		$table->string('name');
		$table->integer('client_number')->nullable();
		$table->integer('TVA')->nullable();
		$table->string('due_date')->nullable();
		$table->string('Billing_address')->nullable();
		$table->string('email');
		$table->string('phone');
		$table->string('address');
		$table->string('city');
		$table->string('zip');
		$table->string('country');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
