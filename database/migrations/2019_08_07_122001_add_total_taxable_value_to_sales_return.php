<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalTaxableValueToSalesReturn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_return', function (Blueprint $table) {
            $table->decimal('total_taxable_value', 12, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_return', function (Blueprint $table) {
            $table->dropIfExists('total_taxable_value');
        });
    }
}
