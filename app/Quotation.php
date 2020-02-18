<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $guarded = [];
    public $table = 'quotation';
    protected $primaryKey = 'id';
}
