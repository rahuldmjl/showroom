<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWeightColumnsToShowroomOrderProductsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('showroom_order_products', function (Blueprint $table) {
			$table->decimal('metal_weight', 12, 3)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('showroom_order_products', function (Blueprint $table) {
			$table->decimal('metal_weight', 11, 2)->change();
		});
	}
}
