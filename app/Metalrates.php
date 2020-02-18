<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Metalrates extends Model
{
      protected $guarded = [];
    public $table = 'vendor_metalrates';
    protected $primaryKey = 'metalrates_id';
}
