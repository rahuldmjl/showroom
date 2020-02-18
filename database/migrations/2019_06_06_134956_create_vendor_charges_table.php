<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_charges', function (Blueprint $table) {
            $table->bigIncrements('vendor_id'); 
            $table->string('from_mm');
            $table->string('to_mm');
            $table->string('type');
            $table->float('labour_charge');
            $table->string('product_type');
            $table->string('diamond_type');
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
        Schema::dropIfExists('vendor_charges');
    }
}
