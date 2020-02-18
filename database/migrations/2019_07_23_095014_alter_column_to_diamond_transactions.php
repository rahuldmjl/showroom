<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnToDiamondTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('diamond_transactions', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
        Schema::table('diamond_transactions', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('transaction_type');
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
