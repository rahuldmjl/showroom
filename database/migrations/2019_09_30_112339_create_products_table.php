<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');     
            $table->unsignedTinyInteger('attribute_set_id')->default('0');
            $table->string('type_id',255)->default('simple');
            $table->string('cost',255)->nullable(); 
            $table->tinyInteger('gift_message_available')->nullable(); 
            $table->tinyInteger('has_options')->default('0');
            $table->string('image_label',255)->nullable(); 
            $table->tinyInteger('is_recurring')->nullable(); 
            $table->integer('links_exist')->nullable(); 
            $table->integer('links_purchased_separately')->nullable(); 
            $table->string('links_title',255)->nullable(); 
            $table->decimal('msrp',12,4)->nullable(); 
            $table->string('msrp_display_actual_price_type',255)->nullable(); 
            $table->tinyInteger('msrp_enabled')->nullable(); 
            $table->string('name',255)->nullable(); 
            $table->datetime('news_from_date')->nullable(); 
            $table->datetime('news_to_date')->nullable(); 
            $table->decimal('price',12,4)->nullable(); 
            $table->integer('price_type')->nullable(); 
            $table->integer('price_view')->nullable(); 
            $table->datetime('recommended')->nullable(); 
            $table->text('recurring_profile')->nullable(); 
            $table->unsignedTinyInteger('required_options')->default('0');
            $table->integer('shipment_type')->nullable(); 
            $table->text('short_description')->nullable(); 
            $table->string('sku',255)->nullable(); 
            $table->integer('sku_type')->nullable(); 
            $table->string('small_image',255)->nullable(); 
            $table->string('small_image_label',255)->nullable(); 
            $table->datetime('special_from_date')->nullable(); 
            $table->decimal('special_price',12,4)->nullable(); 
            $table->datetime('special_to_date')->nullable(); 
            $table->unsignedTinyInteger('status')->nullable(); 
            $table->string('stone_solitair_carat_from',255)->nullable(); 
            $table->string('stone_solitair_carat_to',255)->nullable(); 
            $table->unsignedInteger('tax_class_id')->nullable(); 
            $table->string('thumbnail',255)->nullable(); 
            $table->string('thumbnail_label',255)->nullable(); 
            $table->string('url_key',255)->nullable(); 
            $table->string('url_path',255)->nullable(); 
            $table->unsignedTinyInteger('visibility')->nullable(); 
            $table->decimal('weight',12,4)->nullable(); 
            $table->integer('weight_type')->nullable(); 
            $table->integer('stone_carat')->nullable(); 
            $table->string('stone_carat_value',255)->nullable(); 
            $table->integer('stone_certificate')->nullable(); 
            $table->string('stone_certificate_value',255)->nullable(); 
            $table->integer('stone_color')->nullable(); 
            $table->string('stone_color_value',255)->nullable(); 
            $table->integer('stone_cut')->nullable(); 
            $table->string('stone_cut_value',255)->nullable(); 
            $table->integer('stone_diamond_quality')->nullable(); 
            $table->string('stone_diamond_quality_value',255)->nullable(); 
            $table->integer('stone_shape')->nullable(); 
            $table->string('stone_shape_value',255)->nullable(); 
            $table->string('stone_certificate_number',255)->nullable(); 
            $table->tinyInteger('view_360')->nullable(); 
            $table->string('stone_certificate_url',255)->nullable(); 
            $table->datetime('latestdesigndate')->nullable(); 
            $table->string('certificate_no',255)->nullable(); 
            $table->tinyInteger('isdmpurchase')->nullable(); 
            $table->tinyInteger('isnormal')->nullable(); 
            $table->tinyInteger('isreadytoship')->nullable(); 
            $table->tinyInteger('is_sold')->nullable(); 
            $table->string('rts_bangle_size',255)->nullable(); 
            $table->string('rts_bracelet_size',255)->nullable(); 
            $table->string('rts_ring_size',255)->nullable(); 
            $table->string('rts_stone_quality',255)->nullable(); 
            $table->string('rts_stone_weight',255)->nullable(); 
            $table->integer('virtual_store_name')->nullable(); 
            $table->string('virtual_store_name_value',255)->nullable(); 
            $table->string('rts_pendent_earring',255)->nullable(); 
            $table->integer('gender')->nullable(); 
            $table->string('gender_value',255)->nullable(); 
            $table->string('original_sku',255)->nullable(); 
            $table->integer('stone_type')->nullable(); 
            $table->string('stone_type_value',255)->nullable(); 
            $table->tinyInteger('rts_position')->nullable(); 
            $table->string('virtual_product_manager',255)->nullable(); 
            $table->tinyInteger('dml_only')->nullable(); 
            $table->string('image_1000_h',255)->nullable(); 
            $table->string('image_1000_v',255)->nullable(); 
            $table->tinyInteger('is_mystock')->nullable(); 
            $table->string('vender_id',255)->nullable(); 
            $table->decimal('custom_price',12,4)->nullable(); 
            $table->string('changeprice',255)->nullable(); 
            $table->string('bracelet_belt_type',255)->nullable(); 
            $table->string('bracelet_color',255)->nullable(); 
            $table->integer('metal_quality')->nullable(); 
            $table->string('metal_quality_value',255)->nullable(); 
            $table->decimal('belt_price',12,4)->nullable(); 
            $table->integer('inventory_status')->nullable(); 
            $table->string('inventory_status_value',255)->nullable(); 
            $table->decimal('extra_price',12,4)->nullable(); 
            $table->text('extra_price_for')->nullable();
            $table->integer('approval_invoice_generated')->nullable(); 
            $table->integer('approval_memo_generated')->nullable(); 
            $table->integer('return_memo_generated')->nullable(); 
            $table->tinyInteger('is_returned')->nullable(); 
            $table->decimal('per_gm_rate',12,4)->nullable(); 
            $table->string('style',255)->nullable(); 
            $table->string('item',255)->nullable(); 
            $table->string('po_no',255)->nullable(); 
            $table->string('order_no',255)->nullable(); 
            $table->string('gross_weight',255)->nullable();
            $table->integer('stone_use'); 
            $table->string('total_amount',255);
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
        Schema::dropIfExists('products');
    }
}
