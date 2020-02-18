<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReturnMemo extends Model
{
    protected $guarded = [];
    public $table = 'return_memo';
    protected $primaryKey = 'id';
}
