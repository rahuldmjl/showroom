<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnStoneInProductsStone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_stone', function (Blueprint $table) {
            //
              $table->string('stone_rate',255)->default(0)->change();
              $table->string('stone_amount',255)->default(0)->change();
              $table->string('mm_size',255)->nullable()->change();
              $table->string('seive_size',255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products_stone', function (Blueprint $table) {
            //
        });
    }
}
