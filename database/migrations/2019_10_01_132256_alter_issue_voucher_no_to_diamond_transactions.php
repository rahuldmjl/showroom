<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterIssueVoucherNoToDiamondTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('diamond_transactions', function (Blueprint $table) {
        
        DB::statement('ALTER TABLE `dml_diamond_transactions` CHANGE `issue_voucher_no` `issue_voucher_no` varchar(255) NULL DEFAULT NULL;');
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
            //
        });
    }
}
