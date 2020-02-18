<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExhibitionProducts extends Model
{
    protected $guarded = [];
    public $table = 'exhibition_products';
    protected $primaryKey = 'id';
}
