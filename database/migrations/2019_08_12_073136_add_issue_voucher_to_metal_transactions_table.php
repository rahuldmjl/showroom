<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIssueVoucherToMetalTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('metal_transactions', function (Blueprint $table) {
            $table->bigInteger('issue_voucher_no')->after('issue_to')->nullable();
            $table->date('issue_date')->after('status')->nullable();
            $table->bigInteger('created_by')->after('status')->nullable();
            $table->bigInteger('updated_by')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('metal_transactions', function (Blueprint $table) {
             $table->dropIfExists('issue_voucher_no')->after('issue_to')->nullable();
              $table->dropIfExists('issue_date')->after('status')->nullable();
              $table->dropIfExists('created_by')->after('status')->nullable();
              $table->dropIfExists('updated_by')->after('status')->nullable();

        });
    }
}
