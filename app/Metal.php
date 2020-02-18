<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Metal extends Model {
	//
	//public $table = "transaction_types";
	protected $fillable = [
		'metal_type', 'total_metal_weight', 'avg_rate',
	];

	/**
	 * Get the comments for the blog post.
	 */
	public function metalTransactions() {
		return $this->hasMany('App\MetalTransaction');
	}
}
