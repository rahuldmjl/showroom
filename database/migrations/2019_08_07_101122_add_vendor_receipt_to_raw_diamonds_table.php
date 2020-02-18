<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVendorReceiptToRawDiamondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raw_diamonds', function (Blueprint $table) {
             $table->string('return_to_vendor_receipt')->after('memo_returned')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raw_diamonds', function (Blueprint $table) {
            //
        });
    }
}
