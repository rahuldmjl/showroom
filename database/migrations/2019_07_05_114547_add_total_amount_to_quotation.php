<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalAmountToQuotation extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('quotation', function (Blueprint $table) {
			$table->decimal('total_amount', 8, 2)->after('product_data');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('quotation', function (Blueprint $table) {
			//
		});
	}
}
