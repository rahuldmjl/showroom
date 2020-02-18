<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableConstraintToMetalTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('metal_transactions', function (Blueprint $table) {
            DB::statement('ALTER TABLE `dml_metal_transactions` CHANGE `purchased_at` `purchased_at` DATE NULL DEFAULT NULL, CHANGE `purchased_invoice` `purchased_invoice` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, CHANGE `invoice_number` `invoice_number` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, CHANGE `amount_paid` `amount_paid` DECIMAL(11,2) NULL DEFAULT NULL, CHANGE `metal_rate` `metal_rate` DECIMAL(8,2) NULL DEFAULT NULL, CHANGE `user_id` `user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('metal_transactions', function (Blueprint $table) {
            DB::statement('ALTER TABLE `dml_metal_transactions` CHANGE `purchased_at` `purchased_at` DATE NOT NULL, CHANGE `purchased_invoice` `purchased_invoice` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `invoice_number` `invoice_number` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `amount_paid` `amount_paid` DECIMAL(11,2) NOT NULL, CHANGE `metal_rate` `metal_rate` DECIMAL(8,2) NOT NULL, CHANGE `user_id` `user_id` BIGINT(20) UNSIGNED NOT NULL;');
        });
    }
}
