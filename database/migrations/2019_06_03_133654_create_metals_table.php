<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetalsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('metals', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('metal_type');
			$table->decimal('total_metal_weight', 8, 2);
			$table->decimal('avg_rate', 8, 2);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('metals');
	}
}
