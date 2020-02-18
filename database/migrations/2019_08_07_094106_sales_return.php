<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SalesReturn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_return', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customer_id');
            $table->bigInteger('order_id');
            $table->bigInteger('sales_return_no');
            $table->string('invoice_no', 255);
            $table->longText('product_data');
            $table->string('supply_place', 255);
            $table->string('payment_mode', 255);
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
        Schema::dropIfExists('sales_return');
    }
}
