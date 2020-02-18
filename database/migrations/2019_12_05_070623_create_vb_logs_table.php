<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVbLogsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('vb_logs', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('vb_id');
			$table->foreign('vb_id')->references('id')->on('vb');
			$table->integer('product_id');
			$table->string('certificate_no');
			$table->enum('action', ['ADD', 'REMOVE'])->default('ADD');
			$table->unsignedBigInteger('transaction_by');
			$table->foreign('transaction_by')->references('id')->on('users');
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
		Schema::dropIfExists('vb_logs');
	}
}
