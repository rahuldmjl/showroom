<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsCategoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('category_id')->default('0');
            $table->unsignedInteger('product_id')->default('0');
            $table->integer('position')->default('0');
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
        Schema::dropIfExists('products_category');
    }
}
