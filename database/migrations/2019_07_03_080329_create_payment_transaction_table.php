<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transaction', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('payment_id');
            $table->string('invoice_number')->nullable();
            $table->string('invoice_attachment')->nullable();
            $table->decimal('invoice_amount', 11, 2);
            $table->bigInteger('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transaction');
    }
}
