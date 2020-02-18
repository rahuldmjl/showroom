<?php

namespace App;

use DB;
use vendor_diamond_types;
use vendor_product_types;

class ManageCharges {
	protected $guarded = [];
	public function vendor_product_types() {
		return DB::table('vendor_product_types')->get();
	}
	public function vendor_diamond_types() {
		return DB::table('vendor_diamond_types')->get();
	}
	public function vendor_charges() {
		return DB::table('vendor_charges')->get();
	}
	/*public $table = "vendor_diamond_types";
    protected $primaryKey = 'vendor_diamond_id';*/

}
