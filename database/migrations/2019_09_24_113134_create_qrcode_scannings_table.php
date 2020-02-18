<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQrcodeScanningsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('qrcode_scannings', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('certificate_no', 255);
			$table->unsignedBigInteger('created_by');
			$table->foreign('created_by')->references('id')->on('users');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('qrcode_scannings');
	}
}
