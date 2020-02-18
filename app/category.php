<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    protected $guarded = [];
    
    protected $table = 'categories';
    
    public function product()
    {
        return $this->belongsTo('App\photography_product');
    }

}
