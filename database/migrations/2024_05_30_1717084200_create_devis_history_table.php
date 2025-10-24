<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevisHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('devis_history', function (Blueprint $table) {

		$table->id();
		$table->integer('devis_number')->default('0');
		$table->string('Project_name');
		$table->string('emails')->nullable();
		$table->string('client_id')->nullable();
		$table->string('client_name')->nullable();
		$table->integer('acceptanceStatus')->default('0');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devis_history');
    }
}