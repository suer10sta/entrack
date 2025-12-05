<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntrepriseChargesTable extends Migration
{
    public function up()
    {
        Schema::create('entreprise_charges', function (Blueprint $table) {

        $table->id();
		$table->string('charge_name');
		$table->integer('price');
        $table->unsignedBigInteger('entreprise_id')->default(0);
        $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('entreprise_charges');
    }
}
