<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model {
	use SoftDeletes;
	protected $guarded = [];
	public $table = 'settings';
	protected $primaryKey = 'id';
	protected $dates = ['deleted_at'];
}
