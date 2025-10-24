<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('invoices_history', function (Blueprint $table) {

		$table->id();
		$table->string('project_name');
		$table->integer('number');
		$table->string('link');
		$table->integer('client_id');
		$table->integer('project_id');
		$table->date('due_date')->nullable();
		$table->date('end_date')->nullable();
		$table->date('start_date')->nullable();
		$table->string('type');
		$table->integer('Payment_status')->nullable();
		$table->integer('sentReminder');
		$table->integer('sentInvoice')->default('0');
		$table->unsignedBigInteger('entreprise_id')->default(0);
		$table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices_history');
    }
}