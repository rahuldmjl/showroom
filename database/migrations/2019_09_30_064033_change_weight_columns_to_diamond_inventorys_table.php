<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWeightColumnsToDiamondInventorysTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('diamond_inventorys', function (Blueprint $table) {
			DB::statement('ALTER TABLE `dml_diamond_inventorys` CHANGE `total_diamond_weight` `total_diamond_weight` DECIMAL(12,3) NOT NULL;');
			DB::statement('ALTER TABLE `dml_diamond_inventorys` CHANGE `ave_rate` `ave_rate` DECIMAL(12,2) NOT NULL;');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('diamond_inventorys', function (Blueprint $table) {
			DB::statement('ALTER TABLE `dml_diamond_inventorys` CHANGE `total_diamond_weight` `total_diamond_weight` DECIMAL(8,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_diamond_inventorys` CHANGE `ave_rate` `ave_rate` DECIMAL(11,2) NOT NULL;');
		});
	}
}
