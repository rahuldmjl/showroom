<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\category;
class productListModel extends Model
{
    protected $guarded = [];
   
    protected $table = 'catalog_product_flat_1';

public function getPhotography()
{
    return $this->belongTo('App\photography');
}


}
