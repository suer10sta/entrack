<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmtpConfigTable extends Migration
{
    public function up()
    {
        Schema::create('smtp_config', function (Blueprint $table) {

        $table->id();
		$table->string('mail_host');
		$table->string('mail_port');
		$table->string('mail_password');
		$table->string('mail_username');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('smtp_config');
    }
}
