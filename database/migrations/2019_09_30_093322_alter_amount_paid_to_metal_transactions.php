<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAmountPaidToMetalTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('metal_transactions', function (Blueprint $table) {

               DB::statement('ALTER TABLE `dml_metal_transactions` CHANGE `amount_paid` `amount_paid` DECIMAL(12,2) NULL DEFAULT NULL;');
           
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('metal_transactions', function (Blueprint $table) {
            //
        });
    }
}
