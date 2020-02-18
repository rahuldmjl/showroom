<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiamondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diamonds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('packet_id', 255);
            $table->integer('stone_quality');
            $table->integer('stone_shape');
            $table->decimal('mm_size', 11, 2);
            $table->decimal('sieve_size', 11, 2);
            $table->decimal('total_diamond_weight', 8, 2);
            $table->decimal('price', 11, 2);
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
        Schema::dropIfExists('diamonds');
    }
}
