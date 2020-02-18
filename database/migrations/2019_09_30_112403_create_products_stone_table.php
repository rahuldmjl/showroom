<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsStoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_stone', function (Blueprint $table) {
            $table->bigIncrements('grp_stone_id');
            $table->string('stone_stone',255);
            $table->integer('stone_shape');
            $table->string('seive_size',255); 
            $table->string('mm_size',255); 
            $table->unsignedInteger('stone_product_id');
            $table->integer('stone_type')->nullable();
            $table->integer('stone_subtype')->nullable();
            $table->integer('stone_cut')->nullable();
            $table->float('carat',10,9);
            $table->integer('stone_use');
            $table->string('stone_setting_type',255)->nullable();
            $table->integer('stone_clarity')->nullable();
            $table->string('stone_rate',255); 
            $table->integer('stone_amount');
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
        Schema::dropIfExists('products_stone');
    }
}
