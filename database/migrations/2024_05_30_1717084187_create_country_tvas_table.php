<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountryTvasTable extends Migration
{
    public function up()
    {
        Schema::create('country_tvas', function (Blueprint $table) {
        $table->id();
		$table->string('country');
		$table->integer('tva');
		$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('country_tvas');
    }
}
