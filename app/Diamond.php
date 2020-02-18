<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Diamond extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'packet_id';
    public $incrementing = false;
    protected $fillable = [
      'packet_id','stone_quality', 'stone_shape', 'mm_size','sieve_size', 'total_diamond_weight','ave_rate',
	];
}

