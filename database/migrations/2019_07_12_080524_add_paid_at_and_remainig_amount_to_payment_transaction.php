<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaidAtAndRemainigAmountToPaymentTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_transaction', function (Blueprint $table) {
            $table->decimal('remaining_amount', 11, 2)->after('invoice_amount')->default(0);
            $table->date('paid_at')->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_transaction', function (Blueprint $table) {
            $table->dropIfExists('remaining_amount');
            $table->dropIfExists('paid_at');
        });
    }
}
