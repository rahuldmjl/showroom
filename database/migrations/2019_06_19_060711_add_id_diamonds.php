<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdDiamonds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
       Schema::table('diamonds', function (Blueprint $table) {
            //$table->bigIncrements('id')->before('packet_id');
            //$table->primary(array('id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('diamonds', function (Blueprint $table) {
            Schema::dropIfExists('id');
        });
    }
}
