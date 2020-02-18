<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAllColumnsToProductsAndCostingdatasTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
             $table->text('sku')->nullable()->change();
             $table->text('small_image')->nullable()->change();
             $table->text('stone_carat')->nullable()->change();
             $table->text('stone_shape')->nullable()->change();
             $table->text('certificate_no')->nullable()->change();
             $table->text('style')->nullable()->change();
             $table->text('item')->nullable()->change();
             $table->text('po_no')->nullable()->change();
             $table->text('order_no')->nullable()->change();
             $table->text('gross_weight')->nullable()->change();
             $table->text('total_amount')->nullable()->change();
        });

        Schema::table('products_stone', function (Blueprint $table) {
             $table->text('stone_stone')->nullable()->change();
             $table->text('stone_shape')->nullable()->change();
             $table->text('seive_size')->nullable()->change();
             $table->text('mm_size')->nullable()->change();
             $table->text('stone_type')->nullable()->change();
             $table->text('carat')->nullable()->change();
             $table->text('stone_use')->nullable()->change();
             $table->text('stone_clarity')->nullable()->change();
             $table->text('stone_rate')->nullable()->change();
             $table->text('stone_amount')->nullable()->change();
        });

         Schema::table('products_metal', function (Blueprint $table) {
             $table->text('metal_amount')->nullable()->change();
             $table->text('metal_weight')->nullable()->change();
        });

        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `image` `image` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `item` `item` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `po_no` `po_no` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `order_no` `order_no` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `certificate_no` `certificate_no` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `sku` `sku` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `style` `style` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `metal_karat` `metal_karat` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `color` `color` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `product_category` `product_category` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `gross_weight` `gross_weight` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `metal_weight` `metal_weight` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `metalrate` `metalrate` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `metalamount` `metalamount` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `labouramount` `labouramount` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `diamond_weight` `diamond_weight` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `colorstone_weight` `colorstone_weight` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `material_category` `material_category` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `material_type` `material_type` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `material_quality` `material_quality` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `colorstone_weight` `colorstone_weight` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `material_category` `material_category` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `material_type` `material_type` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `material_quality` `material_quality` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `seive_size` `seive_size` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `material_mm_size` `material_mm_size` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `material_pcs` `material_pcs` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `material_weight` `material_weight` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `stone_rate` `stone_rate` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `stone_amount` `stone_amount` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `total_stone_amount` `total_stone_amount` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `total_amount` `total_amount` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `sgst` `sgst` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `cgst` `cgst` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `igi_charges` `igi_charges` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `hallmarking` `hallmarking` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `request_invoice` `request_invoice` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `qc_status` `qc_status` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `is_igi` `is_igi` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `return_memo` `return_memo` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `batch_no` `batch_no` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `approved_by` `approved_by` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `rejected_by` `rejected_by` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `igi_by` `igi_by` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `invoice_requested_by` `invoice_requested_by` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `memo_returned_by` `memo_returned_by` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `extra_price` `extra_price` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `extra_price_for` `extra_price_for` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `comment` `comment` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `labour_charge_type` `labour_charge_type` text NULL;');
        DB::statement('ALTER TABLE `dml_costingdatas` CHANGE `igi_rejected` `igi_rejected` text NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products_and_costingdatas_tables', function (Blueprint $table) {
            //
        });
    }
}
