<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsMetalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('products_metal', function (Blueprint $table) {
            $table->bigIncrements('grp_metal_id');
            $table->unsignedBigInteger('metal_product_id');
            $table->foreign('metal_product_id')->references('id')->on('products');
            $table->integer('metal_type_id');
            $table->integer('metal_quality_id');
            $table->decimal('metal_weight',7,3);
            $table->decimal('metal_actual_weight',7,3);
            $table->decimal('metal_rate',12,3);
            $table->decimal('metal_labour_charge',12,3);
            $table->integer('metal_amount'); 
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
        Schema::dropIfExists('products_metal');
    }
}
