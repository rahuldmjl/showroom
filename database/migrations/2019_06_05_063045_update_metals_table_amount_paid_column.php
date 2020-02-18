<?php

use Illuminate\Database\Migrations\Migration;

class UpdateMetalsTableAmountPaidColumn extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		/*Schema::table('metal_transactions', function (Blueprint $table) {
				$table->decimal('amount_paid', 11, 2)->change();
			});
			Schema::table('metals', function (Blueprint $table) {
				$table->decimal('avg_rate', 11, 2)->change();
		*/
		DB::statement('ALTER TABLE `dml_metal_transactions` CHANGE `amount_paid` `amount_paid` DECIMAL(11,2) NOT NULL;');
		DB::statement('ALTER TABLE `dml_metals` CHANGE `avg_rate` `avg_rate` DECIMAL(11,2) NOT NULL;');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		/*Schema::table('metal_transactions', function (Blueprint $table) {
				$table->decimal('amount_paid', 8, 2)->change();
			});
			Schema::table('metals', function (Blueprint $table) {
				$table->decimal('avg_rate', 8, 2)->change();
		*/
		DB::statement('ALTER TABLE `dml_metal_transactions` CHANGE `amount_paid` `amount_paid` DECIMAL(8,2) NOT NULL;');
		DB::statement('ALTER TABLE `dml_metals` CHANGE `avg_rate` `avg_rate` DECIMAL(8,2) NOT NULL;');
	}
}
