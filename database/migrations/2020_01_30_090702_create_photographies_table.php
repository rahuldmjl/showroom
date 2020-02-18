<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotographiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photographies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('product_id');
            $table->Integer('category_id');
            $table->Integer('status');
            $table->Integer('current_status');
            $table->Integer('next_department_status');
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
        Schema::dropIfExists('photographies');
    }
}
