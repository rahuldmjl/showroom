<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiamondInventorysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diamond_inventorys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('packet_id', 255)->nullable();
            $table->string('stone_quality',255)->nullable();
            $table->string('stone_shape',255)->nullable();
            $table->decimal('mm_size', 11, 2)->nullable();
            $table->decimal('sieve_size', 11, 2)->nullable();
            $table->decimal('total_diamond_weight', 8, 2)->nullable();
            $table->decimal('ave_rate', 11, 2)->nullable();
            $table->string('created_by', 255)->nullable();
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
        Schema::dropIfExists('diamond_inventorys');
    }
}
