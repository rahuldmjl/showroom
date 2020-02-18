<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPrimaryFromDiamonds extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('diamonds', function (Blueprint $table) {
			//$table->dropPrimary();
			//$table->unsignedInteger('packet_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('diamonds', function (Blueprint $table) {
			//$table->dropPrimary();
			//$table->unsignedInteger('packet_id');
			//DB::unprepared('ALTER TABLE `diamonds` DROP PRIMARY KEY');
		});
	}
}
