<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceCustomerDivisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_customer_division', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('parent_customer_id');
            $table->string('child_customer_name', 255);
            $table->text('child_customer_address');
            $table->string('child_customer_pan',20)->nullable();
            $table->string('child_customer_email',50)->nullable();
            $table->string('child_customer_contact',50)->nullable();
            $table->bigInteger('invoice_id');
            $table->bigInteger('order_id');
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
        Schema::dropIfExists('invoice_customer_division');
    }
}
