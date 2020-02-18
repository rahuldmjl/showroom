<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class VirtualBoxManager extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $table = "vb";
    protected $fillable = [
      'code', 'name', 'price_from','price_to', 'category_id','products_limit','created_by','deleted_at','created_at','updated_at','remarks'
	];
}