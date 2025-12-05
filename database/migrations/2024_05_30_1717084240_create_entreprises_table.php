<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntreprisesTable extends Migration
{
    public function up()
    {
        Schema::create('entreprises', function (Blueprint $table) {

		$table->id();
		$table->string('name');
		$table->integer('phone')->nullable();
		$table->string('email');
		$table->string('Address')->nullable();
		$table->string('image')->nullable();
		$table->integer('is_activated');
		$table->timestamp('duration')->nullable();
		$table->integer('tva');
		$table->string('bank_name');
		$table->string('iban');
		$table->string('swift');
		$table->string('bic');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('entreprises');
    }
}
