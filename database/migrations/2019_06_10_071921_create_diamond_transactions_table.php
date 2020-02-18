<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiamondTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diamond_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stone_shape');
            $table->decimal('diamond_weight', 8, 2);
            $table->date('purchased_at');
            $table->string('purchased_invoice');
            $table->decimal('amount_paid', 8, 2);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->dateTime('transaction_at');
            $table->unsignedBigInteger('transaction_type');
            $table->foreign('transaction_type')->references('id')->on('transaction_types');
            $table->enum('issue_to', ['Vendor', 'IGI'])->nullable();
            $table->enum('reissue_to', ['Vendor', 'IGI'])->nullable();
            $table->string('po_number')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->foreign('vendor_id')->references('id')->on('users');
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('diamond_transactions');
    }
}
