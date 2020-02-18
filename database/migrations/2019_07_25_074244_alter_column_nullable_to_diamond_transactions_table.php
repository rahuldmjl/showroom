<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnNullableToDiamondTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('diamond_transactions', function (Blueprint $table) {
           $table->dropColumn('mm_size');
        });
        Schema::table('diamond_transactions', function (Blueprint $table) {
            $table->decimal('mm_size', 11, 2)->nullable()->after('diamond_weight');
        });
        Schema::table('diamond_transactions', function (Blueprint $table) {
            $table->dropColumn('sieve_size');
        });
        Schema::table('diamond_transactions', function (Blueprint $table) {
            $table->decimal('sieve_size', 11, 2)->nullable()->after('diamond_weight');
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
