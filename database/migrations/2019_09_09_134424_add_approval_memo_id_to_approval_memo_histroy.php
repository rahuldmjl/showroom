<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovalMemoIdToApprovalMemoHistroy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('approval_memo_histroy', function (Blueprint $table) {
            $table->bigInteger('approval_memo_id')->unsigned(); 
            $table->foreign('approval_memo_id')->references('id')->on('approval_memo'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('approval_memo_histroy', function (Blueprint $table) {
            //
        });
    }
}
