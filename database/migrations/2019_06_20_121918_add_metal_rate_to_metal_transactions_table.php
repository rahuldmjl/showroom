<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetalRateToMetalTransactionsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('metal_transactions', function (Blueprint $table) {
			$table->decimal('metal_rate', 8, 2)->after('amount_paid');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('metal_transactions', function (Blueprint $table) {
			$table->dropColumn('metal_rate');
		});
	}
}
