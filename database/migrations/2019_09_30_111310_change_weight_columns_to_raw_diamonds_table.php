<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWeightColumnsToRawDiamondsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('raw_diamonds', function (Blueprint $table) {
			//
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `total_weight` `total_weight` DECIMAL(12,3) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `cvd_weight` `cvd_weight` DECIMAL(12,3) NOT NULL DEFAULT \'0.000\';');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `cvd_rejected` `cvd_rejected` DECIMAL(12,3) NOT NULL DEFAULT \'0.00\';');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `cvd_loss` `cvd_loss` DECIMAL(12,3) NOT NULL DEFAULT \'0.00\';');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `assorting_weight` `assorting_weight` DECIMAL(12,3) NOT NULL DEFAULT \'0.00\';');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `assorting_rejected` `assorting_rejected` DECIMAL(12,3) NOT NULL DEFAULT \'0.00\';');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `assorting_loss` `assorting_loss` DECIMAL(12,3) NOT NULL DEFAULT \'0.00\';');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `sizing_weight` `sizing_weight` DECIMAL(12,3) NOT NULL DEFAULT \'0.00\';');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `sizing_rejected` `sizing_rejected` DECIMAL(12,3) NOT NULL DEFAULT \'0.00\';');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `sizing_loss` `sizing_loss` DECIMAL(12,3) NOT NULL DEFAULT \'0.00\';');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `total_loss` `total_loss` DECIMAL(12,3) NOT NULL DEFAULT \'0.00\';');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `total_rejected_weight` `total_rejected_weight` DECIMAL(12,3) NOT NULL DEFAULT \'0.00\';');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('raw_diamonds', function (Blueprint $table) {
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `total_weight` `total_weight` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `cvd_weight` `cvd_weight` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `cvd_rejected` `cvd_rejected` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `cvd_loss` `cvd_loss` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `assorting_weight` `assorting_weight` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `assorting_rejected` `assorting_rejected` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `assorting_loss` `assorting_loss` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `sizing_weight` `sizing_weight` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `sizing_rejected` `sizing_rejected` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `sizing_loss` `sizing_loss` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `total_loss` `total_loss` DECIMAL(11,2) NOT NULL;');
			DB::statement('ALTER TABLE `dml_raw_diamonds` CHANGE `total_rejected_weight` `total_rejected_weight` DECIMAL(11,2) NOT NULL;');
		});
	}
}
