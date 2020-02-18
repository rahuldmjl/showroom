<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stone_shape', 255);
            $table->string('stone_quality',255);
            $table->longText('range_data');
            $table->longText('labour_charge');
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
        Schema::dropIfExists('quotation_data');
    }
}
