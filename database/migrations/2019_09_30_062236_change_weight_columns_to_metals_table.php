<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWeightColumnsToMetalsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('metals', function (Blueprint $table) {
			$table->decimal('total_metal_weight', 12, 3)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('metals', function (Blueprint $table) {
			$table->decimal('total_metal_weight', 8, 2)->change();
		});
	}
}
