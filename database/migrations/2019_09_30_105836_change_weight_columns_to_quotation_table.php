<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWeightColumnsToQuotationTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('quotation', function (Blueprint $table) {
			DB::statement('ALTER TABLE `dml_quotation` CHANGE `total_metal_weight` `total_metal_weight` DECIMAL(12,3) NOT NULL;');
			DB::statement('ALTER TABLE `dml_quotation` CHANGE `total_stone_caret` `total_stone_caret` DECIMAL(12,3) NOT NULL;');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('quotation', function (Blueprint $table) {
			DB::statement('ALTER TABLE `dml_quotation` CHANGE `total_metal_weight` `total_metal_weight` DECIMAL(8,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_quotation` CHANGE `total_stone_caret` `total_stone_caret` DECIMAL(8,2) NOT NULL;');
		});
	}
}
