<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVbProductsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('vb_products', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('vb_id');
			$table->foreign('vb_id')->references('id')->on('vb');
			$table->integer('product_id');
			$table->string('certificate_no');
			$table->integer('position')->default(0);
			$table->unsignedBigInteger('added_by');
			$table->foreign('added_by')->references('id')->on('users');
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
		Schema::dropIfExists('vb_products');
	}
}
