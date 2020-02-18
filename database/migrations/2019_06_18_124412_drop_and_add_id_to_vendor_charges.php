<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropAndAddIdToVendorCharges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_charges', function (Blueprint $table) {
            $table->dropColumn('id'); 
         });
        Schema::table('vendor_charges', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_charges', function (Blueprint $table) {
             //$table->bigIncrements('id')->first(); 
        });
    }
}
