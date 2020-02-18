<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostingdatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('costingdatas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('image')->nullable();
            $table->string('item')->nullable();
            $table->string('po_no')->nullable();
            $table->string('order_no')->nullable();
            $table->string('certificate_no')->nullable();
            $table->string('sku')->nullable();
            $table->string('style')->nullable();
            $table->string('metal_karat')->nullable();
            $table->string('color')->nullable();
            $table->string('ringsize')->nullable();
            $table->string('product_category')->nullable();
            $table->string('gross_weight')->nullable();
            $table->string('metal_weight')->nullable();
            $table->string('metalrate')->nullable();
            $table->string('metalamount')->nullable();
            $table->string('labouramount')->nullable();
            $table->string('diamond_pcs')->nullable();
            $table->string('diamond_weight')->nullable();
            $table->string('colorstone_pcs')->nullable();
            $table->string('colorstone_weight')->nullable();
            $table->string('material_category')->nullable();
            $table->string('material_type')->nullable();
            $table->string('material_quality')->nullable();
            $table->string('seive_size')->nullable();
            $table->string('material_mm_size')->nullable();
            $table->string('material_pcs')->nullable();
            $table->string('material_weight')->nullable();
            $table->string('stone_rate')->nullable();
            $table->string('stone_amount')->nullable();
            $table->string('total_stone_amount')->nullable();
            $table->string('total_amount')->nullable();
            $table->string('costingdata_id')->nullable();
            $table->string('sgst')->nullable();
            $table->string('cgst')->nullable();
            $table->string('igi_charges')->nullable();
            $table->string('hallmarking')->nullable();      
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
        Schema::dropIfExists('costingdatas');
    }
}
