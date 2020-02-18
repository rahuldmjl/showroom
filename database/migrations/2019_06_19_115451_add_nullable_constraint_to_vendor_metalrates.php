<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableConstraintToVendorMetalrates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_metalrates', function (Blueprint $table) {
             $table->string('metal_quality')->nullable()->change();
                $table->string('metal_type')->nullable()->change();
                 $table->decimal('gold_rate',5,2)->nullable()->change();
                  $table->decimal('fineness',5,2)->nullable()->change();
                  $table->integer('gold_content')->unsigned()->nullable()->change();
                  $table->decimal('weight',7,3)->nullable()->change();
                  $table->decimal('rate',12,2)->nullable()->change();
                   $table->decimal('metal_price',8,2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_metalrates', function (Blueprint $table) {
            //
        });
    }
}
