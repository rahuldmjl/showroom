<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnNullableToReturnMemo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('return_memo', function (Blueprint $table) {
            $table->bigInteger('franchise_id')->nullable(true)->change();
            $table->string('franchise_name')->nullable(true)->change();
            $table->bigInteger('franchise_address')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('return_memo', function (Blueprint $table) {
            //
        });
    }
}
