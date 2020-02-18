<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalStoneCaretToQuotation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotation', function (Blueprint $table) {
            $table->decimal('total_stone_caret', 8, 2)->after('total_metal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotation', function (Blueprint $table) {
            //
        });
    }
}
