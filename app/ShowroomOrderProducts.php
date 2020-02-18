<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShowroomOrderProducts extends Model {
	use SoftDeletes;
	protected $guarded = [];
	public $table = 'showroom_order_products';
	protected $primaryKey = 'id';
	protected $dates = ['deleted_at'];

	/**
	 * Get the Order detail
	 */
	public function order() {
		return $this->belongsTo('App\ShowroomOrder', 'id');
	}
}
