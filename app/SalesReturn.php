<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $guarded = [];
    public $table = 'sales_return';
    protected $primaryKey = 'id';
}
