<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiamondRaw extends Model
{
	use SoftDeletes;
    public $table = "raw_diamonds";
	protected $primaryKey = 'id';

	protected $fillable = [ 'packet_name', 'total_weight', 'vendor_name', 'total_amount','purchased_at','created_by'];

}
