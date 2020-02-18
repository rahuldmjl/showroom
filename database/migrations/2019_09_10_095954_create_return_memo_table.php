<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReturnMemoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_memo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customer_id');
            $table->bigInteger('franchise_id');
            $table->string('franchise_name', 255);
            $table->bigInteger('franchise_address');
            $table->text('product_data');
            $table->text('product_ids');
            $table->text('grand_total_data');
            $table->string('return_number', 255);
            $table->string('approval_memo_number', 255);
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('return_memo');
    }
}
