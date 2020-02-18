<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('costings', function (Blueprint $table) {
            $table->bigIncrements('id');
             $table->string('name')->nullable();
            $table->string('diamond_gold_price')->nullable();
            $table->string('gold_handling')->nullable();
            $table->string('diamond_handling')->nullable();
            $table->string('igi_charges')->nullable();
            $table->string('diamond_quality')->nullable();
            $table->string('stone_carat')->nullable();
            $table->string('gemstone_type')->nullable();
            $table->string('jobwork_status')->nullable();
            $table->string('vendor_id')->nullable();
            $table->string('status')->nullable();
            $table->string('costing_id')->nullable();
            $table->string('purchase_no')->nullable();
            $table->string('purchase_no_date')->nullable();
            $table->string('stone_shape')->nullable();
            $table->string('hallmarking')->nullable();
            $table->string('fancy_diamond_handling')->nullable();
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
        Schema::dropIfExists('costings');
    }
}
