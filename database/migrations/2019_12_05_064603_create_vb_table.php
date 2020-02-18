<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVbTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('vb', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('code');
			$table->string('name');
			$table->integer('price_from');
			$table->integer('price_to');
			$table->integer('category_id');
			$table->integer('products_limit');
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
		Schema::dropIfExists('vb');
	}
}
