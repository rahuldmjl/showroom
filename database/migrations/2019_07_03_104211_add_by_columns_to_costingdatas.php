<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddByColumnsToCostingdatas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('costingdatas', function (Blueprint $table) {
            $table->string('approved_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->string('igi_by')->nullable();
            $table->string('invoice_requested_by')->nullable();
            $table->string('memo_returned_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('costingdatas', function (Blueprint $table) {
            //
        });
    }
}
