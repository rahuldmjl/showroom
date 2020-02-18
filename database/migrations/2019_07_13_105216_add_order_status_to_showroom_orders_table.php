<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderStatusToShowroomOrdersTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('showroom_orders', function (Blueprint $table) {
			$table->enum('order_status', ['Pending', 'Given to Vendor', 'In Progress', 'Completed'])->default('Pending')->after('order_total');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('showroom_orders', function (Blueprint $table) {
			Schema::dropColumn('order_status');
		});
	}
}
