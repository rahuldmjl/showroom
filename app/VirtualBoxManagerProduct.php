<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class VirtualBoxManagerProduct extends Model
{
    protected $guarded = [];
    public $table = "vb_products";
    protected $primaryKey = 'id';
    //public $incrementing = true;
    protected $fillable = [
      'vb_id', 'product_id', 'certificate_no','position', 'added_by','deleted_at','created_at','updated_at'
	];
}