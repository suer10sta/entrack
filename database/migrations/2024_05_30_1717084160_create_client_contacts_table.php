<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientContactsTable extends Migration
{
    public function up()
    {
        Schema::create('client_contacts', function (Blueprint $table) {

		$table->id();
		$table->string('name');
		$table->string('functionality');
		$table->integer('mobile_fix');
		$table->string('email');
		$table->string('address');
		$table->integer('is_comptable')->nullable();
		$table->integer('client_id');
		$table->string('project_name');
		$table->integer('project_id');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('client_contacts');
    }
}