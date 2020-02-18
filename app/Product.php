<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];
    public $table = "vendor_product_types";
    protected $primaryKey = 'vendor_product_id';
}
