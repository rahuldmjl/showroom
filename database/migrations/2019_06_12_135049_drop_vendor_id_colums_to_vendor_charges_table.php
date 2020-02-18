<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropVendorIdColumsToVendorChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_charges', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
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
            //$table->bigIncrements('vendor_id'); 
        });
    }
}
