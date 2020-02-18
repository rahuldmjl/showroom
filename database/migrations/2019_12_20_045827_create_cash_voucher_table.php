<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashVoucherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_voucher', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customer_id');
            $table->bigInteger('invoice_id');
            $table->decimal('invoice_amount', 11,2);
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
        Schema::dropIfExists('cash_voucher');
    }
}
