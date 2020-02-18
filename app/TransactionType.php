<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionType extends Model {

	use SoftDeletes;
	//
	public $table = "transaction_types";

	protected $fillable = [
		'id', 'name',
	];

	/**
	 * Get the phone record associated with the user.
	 */
	public function metal_transactions() {
		return $this->hasMany('App\MetalTransaction', 'id');
	}
}
