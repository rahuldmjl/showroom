<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMmToSieveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mm_to_sieve', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stone_shape',255)->nullable();
            $table->decimal('mm_size', 11, 2)->nullable();
            $table->decimal('sieve_size', 11, 2)->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('mm_to_sieve');
    }
}
