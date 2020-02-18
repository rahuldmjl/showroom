<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorHandlingChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_handling_charges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('vendor_id')->nullable();
            $table->string('gold_handling')->nullable();
            $table->string('diamond_handling')->nullable();
            $table->string('fancy_diamond_handling')->nullable();
            $table->string('igi_charges')->nullable();
            $table->string('hallmarking')->nullable();
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('vendor_handling_charges');
    }
}
