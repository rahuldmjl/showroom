<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReturnMemoProducts extends Model
{
    protected $guarded = [];
    public $table = 'return_memo_products';
    protected $primaryKey = 'id';
}
