<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesReturnProducts extends Model
{
    protected $guarded = [];
    public $table = 'sales_return_products';
    protected $primaryKey = 'id';
}
