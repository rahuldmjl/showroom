<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQcStatusToCostingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('costingdatas', function (Blueprint $table) {
            $table->string('qc_status')->nullable();
        });
        Schema::table('costingdatas', function (Blueprint $table) {
            $table->string('is_igi')->nullable();
        });
        Schema::table('costingdatas', function (Blueprint $table) {
            $table->enum('branding', ['DIAMONDMELA','IGI']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('costingdatas', function (Blueprint $table) {
            /*Schema::dropIfExists('qc_status');
            Schema::dropIfExists('is_igi');
            Schema::dropIfExists('branding');*/
           /* Schema::table('costingdatas', function (Blueprint $table) {
            $table->string('qc_status')->nullable();
            });
            Schema::table('costingdatas', function (Blueprint $table) {
                $table->string('is_igi')->nullable();
            });
            Schema::table('costingdatas', function (Blueprint $table) {
                $table->enum('branding', ['DIAMONDMELA','IGI']);
            });*/
        });
    }
}
