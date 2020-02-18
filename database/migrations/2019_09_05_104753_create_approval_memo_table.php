<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalMemoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_memo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customer_id');
            $table->string('approval_no', 255);
            $table->longText('product_ids');
            $table->bigInteger('franchisee_id');
            $table->string('agent_name', 255);
            $table->string('approval_type', 255);
            $table->string('deposit_type', 255);
            $table->enum('status', ['pending', 'complete','cancelled']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_memo');
    }
}
