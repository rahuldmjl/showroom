<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdjustableColumnsDiamondTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::table('diamond_transactions', function (Blueprint $table) {
            $table->enum('is_adjustable', ['0', '1'])->default(0);
            $table->string('custom_stone_quality',255)->nullable();
            $table->string('custom_mm_size',255)->nullable();
            $table->string('custom_sieve_size',255)->nullable();
            $table->enum('is_voucher_no_generated',['0', '1'])->default(0);
            $table->enum('is_handover',['0', '1'])->default(0);
            $table->timestamp('handover_at');
        });
        Schema::table('metal_transactions', function (Blueprint $table) {
            $table->enum('is_voucher_no_generated',['0', '1'])->default(0);
            $table->enum('is_handover',['0', '1'])->default(0);
            $table->timestamp('handover_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
