<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionColumnsToMetalTransactionTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('metal_transactions', function (Blueprint $table) {
			$table->unsignedBigInteger('transaction_id')->after('transaction_type')->nullable();
			$table->foreign('transaction_id')->references('id')->on('payments');
			$table->string('invoice_number')->after('purchased_invoice');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('transaction_id');
		Schema::dropIfExists('invoice_number');
	}
}
