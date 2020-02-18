<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalMemoHistroyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_memo_histroy', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('approval_no', 255)->nullable();
            $table->bigInteger('product_id')->nullable();
            $table->enum('status', ['approval', 'invoice','return_memo'])->nullable();
            $table->dateTime('date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_memo_histroy');
    }
}
