<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsDeliveredToApprovalMemo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('approval_memo', function (Blueprint $table) {
            $table->enum('is_delivered', array(0,1))->nullable()->default(0)->comment('0 = Not delivered, 1 = Delivered');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('approval_memo', function (Blueprint $table) {
            //
        });
    }
}
