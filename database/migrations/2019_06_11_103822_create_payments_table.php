<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('payments', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('customer_id')->nullable();
			$table->string('customer_name')->nullable();
			$table->string('invoice_number')->nullable();
			$table->string('invoice_attachment')->nullable();
			$table->decimal('invoice_amount', 11, 2);
			$table->date('due_date');
			$table->tinyInteger('account_status')->default(0);
			$table->tinyInteger('payment_status')->default(0);
			$table->enum('payment_form', ['Incoming', 'Outgoing']);
			$table->unsignedBigInteger('payment_type');
			$table->foreign('payment_type')->references('id')->on('payment_types');
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
		Schema::dropIfExists('payments');
	}
}
