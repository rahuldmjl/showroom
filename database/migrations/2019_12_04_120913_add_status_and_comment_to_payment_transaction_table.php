<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndCommentToPaymentTransactionTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('payment_transaction', function (Blueprint $table) {
			$table->enum('status', ['Pending', 'Bank Paid', 'Cash Paid'])->default('Pending');
			$table->text('comment')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('payment_transaction', function (Blueprint $table) {
			$table->dropColumn('status');
			$table->dropColumn('comment');
		});
	}
}
