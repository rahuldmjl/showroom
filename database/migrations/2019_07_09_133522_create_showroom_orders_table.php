<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShowroomOrdersTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('showroom_orders', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('order_number');
			$table->string('po_number');
			$table->integer('total_qty')->default(1);
			$table->decimal('order_total', 11, 2)->default(0.00);
			$table->unsignedBigInteger('vendor');
			$table->foreign('vendor')->references('id')->on('users');
			$table->unsignedBigInteger('created_by');
			$table->foreign('created_by')->references('id')->on('users');
			$table->softDeletes();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('showroom_orders');
	}
}
