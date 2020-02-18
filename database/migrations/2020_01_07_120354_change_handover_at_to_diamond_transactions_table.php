<?php

use Illuminate\Database\Migrations\Migration;

class ChangeHandoverAtToDiamondTransactionsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		DB::statement("ALTER TABLE `dml_diamond_transactions` CHANGE `handover_at` `handover_at` TIMESTAMP NULL DEFAULT NULL;");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		DB::statement("ALTER TABLE `dml_diamond_transactions` CHANGE `handover_at` `handover_at` TIMESTAMP;");
	}
}
