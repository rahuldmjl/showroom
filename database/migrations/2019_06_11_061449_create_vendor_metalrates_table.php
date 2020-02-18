<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorMetalratesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_metalrates', function (Blueprint $table) {
           
             $table->bigIncrements('metalrates_id');
                $table->string('metal_quality');
                $table->string('metal_type');
                 $table->decimal('gold_rate',5,2);
                  $table->decimal('fineness',5,2);
                  $table->integer('gold_content')->unsigned();
                  $table->decimal('weight',7,3);
                  $table->decimal('rate',12,2);
                   $table->double('metal_price',8,2);
                   $table->integer('vendor_id');

            

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
        Schema::dropIfExists('vendor_metalrates');
    }
}
