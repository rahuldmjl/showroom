<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExhibitionsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('exhibitions', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('title');
			$table->string('place');
			$table->text('address')->nullable();
			$table->decimal('markup', 5, 2);
			$table->integer('qty');
			$table->text('products_certificates');
			$table->text('products_ids');
			$table->text('products_data');
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
		Schema::dropIfExists('exhibitions');
	}
}
