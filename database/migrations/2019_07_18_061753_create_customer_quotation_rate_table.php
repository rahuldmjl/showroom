<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerQuotationRateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_quotation_rate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customer_id');
            $table->string('stone_shape', 255);
            $table->string('stone_quality', 255);
            $table->longText('ston_range_data');
            $table->bigInteger('quotation_id')->unsigned();
            $table->foreign('quotation_id')->references('id')->on('quotation');
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
        Schema::dropIfExists('customer_quotation_rate');
    }
}
