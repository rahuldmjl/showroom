<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class DiamondType extends Model
{
    protected $guarded = [];
    public $table = "vendor_diamond_types";
    protected $primaryKey = 'vendor_diamond_id';
  
}

