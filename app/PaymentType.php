<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentType extends Model {
	use SoftDeletes;
	protected $guarded = [];
	public $table = 'payment_types';
	protected $primaryKey = 'id';

	/*function subtypes() {
		    	$pt = DB::raw('pt');
		    	return $this->belongsToMany('App\PaymentType', 'payment_types AS '.$pt, 'id', 'parent_id');
			}

			function parent() {
		    	return $this->hasOne('App\PaymentType', 'parent_id');
	*/

	public  function parent() {
		return $this->belongsTo(static::class, 'parent_id');
	}

	public function subtypes() {
		return $this->hasMany(static::class, 'parent_id')->orderBy('name', 'asc');
	}
}
