<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceAndMemoColumnsToCostingdatas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('costingdatas', function (Blueprint $table) {
            $table->bigInteger('request_invoice')->nullable();
            $table->bigInteger('return_memo')->nullable();
            $table->bigInteger('batch_no')->nullable();
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
