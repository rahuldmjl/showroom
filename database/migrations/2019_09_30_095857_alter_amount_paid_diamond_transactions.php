<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAmountPaidDiamondTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('diamond_transactions', function (Blueprint $table) {

                  DB::statement('ALTER TABLE `dml_diamond_transactions` CHANGE `amount_paid` `amount_paid` DECIMAL(12,2) NULL DEFAULT NULL;');

                    DB::statement('ALTER TABLE `dml_diamond_transactions` CHANGE `amount_paid_with_gst` `amount_paid_with_gst` DECIMAL(12,2) NULL DEFAULT NULL;');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('diamond_transactions', function (Blueprint $table) {
            });
    }
}
