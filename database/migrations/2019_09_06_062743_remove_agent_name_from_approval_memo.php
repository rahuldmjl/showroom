<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAgentNameFromApprovalMemo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('approval_memo', function (Blueprint $table) {
            $table->dropColumn('agent_name');
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
            $table->dropColumn('agent_name');
        });
    }
}
