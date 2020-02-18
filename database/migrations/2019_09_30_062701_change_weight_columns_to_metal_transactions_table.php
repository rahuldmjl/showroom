<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWeightColumnsToMetalTransactionsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('metal_transactions', function (Blueprint $table) {
			DB::statement('ALTER TABLE `dml_metal_transactions` CHANGE `metal_weight` `metal_weight` DECIMAL(12,3) NOT NULL;');
			DB::statement('ALTER TABLE `dml_metal_transactions` CHANGE `amount_paid` `amount_paid` DECIMAL(12,2) NOT NULL;');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('metal_transactions', function (Blueprint $table) {
			DB::statement('ALTER TABLE `dml_metal_transactions` CHANGE `metal_weight` `metal_weight` DECIMAL(8,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_metal_transactions` CHANGE `amount_paid` `amount_paid` DECIMAL(11,2) NOT NULL;');
		});
	}
}
