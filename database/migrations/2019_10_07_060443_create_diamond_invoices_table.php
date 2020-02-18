<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiamondInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diamond_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('customer_id')->nullable();
            $table->string('diamond_data',500)->nullable();
            $table->integer('discount')->nullable();
            $table->integer('final_price')->nullable();
            $table->string('invoice_number',255)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('created_by',255)->nullable();
            $table->string('status',255)->nullable();
            $table->string('description',255)->nullable();
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
        Schema::dropIfExists('diamond_invoices');
    }
}
