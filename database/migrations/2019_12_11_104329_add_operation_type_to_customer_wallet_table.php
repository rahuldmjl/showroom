<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOperationTypeToCustomerWalletTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_wallet', function (Blueprint $table) {
            $table->enum('operation_type', array('invoice','approval','return_memo'))->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_wallet', function (Blueprint $table) {
            //
        });
    }
}
