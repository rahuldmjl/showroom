<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShowroomOrders extends Model {
	use SoftDeletes;
	protected $guarded = [];
	public $table = 'showroom_orders';
	protected $primaryKey = 'id';
	protected $dates = ['deleted_at'];

	/**
	 * Get the order products associated with the order.
	 */
	public function order_products() {
		return $this->hasMany('App\ShowroomOrderProducts', 'order_id');
	}
}
