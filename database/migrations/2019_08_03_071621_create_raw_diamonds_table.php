<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRawDiamondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_diamonds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('vendor_name', 255);
            $table->string('packet_name', 255);
            $table->decimal('total_weight', 11, 2);
            $table->integer('cvd_status')->default(0);
            $table->decimal('cvd_weight', 11, 2)->default(0.00);
            $table->decimal('cvd_rejected', 11, 2)->default(0.00);
            $table->decimal('cvd_loss', 11, 2)->default(0.00);
            $table->text('cvd_loss_reason')->nullable();
            $table->integer('assorting_status')->default(0);
            $table->decimal('assorting_weight', 11, 2)->default(0.00);
            $table->decimal('assorting_rejected', 11, 2)->default(0.00);
            $table->decimal('assorting_loss', 11, 2)->default(0.00);
            $table->text('assorting_loss_reason')->nullable();
            $table->integer('sizing_status')->default(0);
            $table->decimal('sizing_weight', 11, 2)->default(0.00);
            $table->decimal('sizing_rejected', 11, 2)->default(0.00);
            $table->decimal('sizing_loss', 11, 2)->default(0.00);
            $table->text('sizing_loss_reason')->nullable();
            $table->decimal('total_rejected_weight', 11, 2)->default(0.00);
            $table->decimal('total_loss', 11, 2)->default(0.00);
            $table->integer('moved_to_inventory')->default(0);
            $table->integer('memo_returned')->default(0);
            $table->date('purchased_at');
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
        Schema::dropIfExists('raw_diamonds');
    }
}
