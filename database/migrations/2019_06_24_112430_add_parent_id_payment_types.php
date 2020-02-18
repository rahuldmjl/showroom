<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdPaymentTypes extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('payment_types', function (Blueprint $table) {
			$table->unsignedBigInteger('parent_id')->default(0)->after('name');
		});

		/*Schema::table('payment_types', function (Blueprint $table) {
			$table->foreign('parent_id')->references('id')->on('payment_types')->onDelete('cascade')->onUpdate('cascade');
		});*/
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('parent_id');
	}
}
