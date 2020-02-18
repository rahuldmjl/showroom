<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotoshopCachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photoshop_caches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('product_id');
            $table->string('action_name');
            $table->Integer('action_by');
            $table->datetime('action_date_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photoshop_caches');
    }
}
