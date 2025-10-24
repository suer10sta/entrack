<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelHasPermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('model_has_permissions', function (Blueprint $table) {

		$table->bigInteger('permission_id')->unsigned();
		$table->string('model_type');
		$table->bigInteger('model_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_has_permissions');
    }
}