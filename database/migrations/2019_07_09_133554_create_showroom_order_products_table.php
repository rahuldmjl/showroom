<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShowroomOrderProductsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('showroom_order_products', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('order_id');
			$table->foreign('order_id')->references('id')->on('showroom_orders');
			$table->bigInteger('product_id');
			$table->string('sku');
			$table->string('certificate');
			$table->integer('qty')->default(1);
			$table->integer('metal_quality');
			$table->decimal('metal_weight', 11, 2);
			$table->string('diamond_quality');
			$table->decimal('diamond_weight', 11, 2);
			$table->decimal('product_price', 11, 2);
			$table->decimal('product_total', 11, 2);
			$table->string('criteria_status')->nullable();
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
		Schema::drop('showroom_order_products');
	}
}
