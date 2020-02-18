-<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorstonemanagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendorstonemanages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stone_shape')->nullable();
            $table->string('stone_clarity')->nullable();
            $table->string('stone_color')->nullable();
            $table->string('stone_carat_from')->nullable();
            $table->string('stone_carat_to')->nullable();
            $table->string('stone_price')->nullable();
            $table->string('vendor_id')->nullable();
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
        Schema::dropIfExists('vendorstonemanages');
    }
}
