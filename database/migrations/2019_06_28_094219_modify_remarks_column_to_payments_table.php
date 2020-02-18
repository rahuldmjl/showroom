<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyRemarksColumnToPaymentsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('payments', function (Blueprint $table) {
			//$table->text('remakrs')->nullable()->change();
			//$table->renameColumn('remakrs', 'remarks');
			DB::statement('ALTER TABLE `dml_payments` CHANGE `remakrs` `remarks` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('payments', function (Blueprint $table) {
			//$table->string('remarks')->change();
			//$table->renameColumn('remarks', 'remakrs');
			//DB::statement('ALTER TABLE `dml_payments` CHANGE `remarks` `remakrs` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
		});
	}
}
