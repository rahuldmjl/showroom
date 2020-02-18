<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountPaidWithGstToDiamondTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('diamond_transactions', function (Blueprint $table) {
            $table->decimal('amount_paid_with_gst', 8, 2)->after('amount_paid');
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
