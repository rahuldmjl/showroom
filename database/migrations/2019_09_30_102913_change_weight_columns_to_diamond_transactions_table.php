<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWeightColumnsToDiamondTransactionsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('diamond_transactions', function (Blueprint $table) {
			DB::statement('ALTER TABLE `dml_diamond_transactions` CHANGE `diamond_weight` `diamond_weight` DECIMAL(12,3) NOT NULL;');
			DB::statement('ALTER TABLE `dml_diamond_transactions` CHANGE `amount_paid` `amount_paid` DECIMAL(12,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_diamond_transactions` CHANGE `amount_paid_with_gst` `amount_paid_with_gst` DECIMAL(12,2) NOT NULL;');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('diamond_transactions', function (Blueprint $table) {
			DB::statement('ALTER TABLE `dml_diamond_transactions` CHANGE `diamond_weight` `diamond_weight` DECIMAL(8,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_diamond_transactions` CHANGE `amount_paid` `amount_paid` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_diamond_transactions` CHANGE `amount_paid_with_gst` `amount_paid_with_gst` DECIMAL(11,2) NOT NULL;');
		});
	}
}
