<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyStoneColumnsToDiamondsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('diamonds', function (Blueprint $table) {
			$table->string('stone_quality', 255)->change();
			$table->string('stone_shape', 255)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('diamonds', function (Blueprint $table) {
			$table->integer('stone_quality')->change();
			$table->integer('stone_shape')->change();
		});
	}
}
