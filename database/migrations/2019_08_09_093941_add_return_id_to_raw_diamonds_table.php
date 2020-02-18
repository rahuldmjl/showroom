<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReturnIdToRawDiamondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raw_diamonds', function (Blueprint $table) {
            $table->string('return_id')->after('memo_returned')->nullable();
            $table->date('return_date')->after('return_to_vendor_receipt')->nullable();
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
            $table->dropIfExists('return_id');
            $table->dropIfExists('return_date');
        });
    }
}
