<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnInDiamondInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('diamond_invoices', function (Blueprint $table) {
            //
            $table->decimal('final_price',11,2)->change();
            $table->string('invoice_attachment',255)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('diamond_invoices', function (Blueprint $table) {
            //
        });
    }
}
