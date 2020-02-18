<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedByToRawDiamondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raw_diamonds', function (Blueprint $table) {
             $table->bigInteger('created_by')->after('return_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raw_diamonds', function (Blueprint $table) {
            $table->dropIfExists('created_by')->after('return_date');
        });
    }
}
