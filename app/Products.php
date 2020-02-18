<?php 
// namespace App;
// use Illuminate\Database\Eloquent\Model;

// class Products extends Model {

// 	protected $guarded = [];

//     protected $table = 'products';

//     public function products(){
//     	return $this->hasMany('App\Products','id');
// 	}
// }


namespace App;
use Illuminate\Database\Eloquent\Model;

class Products extends Model {

	protected $guarded = [];

    protected $table = 'products';

    public function metals(){
    	return $this->hasOne('App\ProductsMetal','metal_product_id');
    }
     public function stones(){
    	return $this->hasMany('App\ProductsStone','stone_product_id');
    }

    public function categorys() {
    	return $this->hasOne('App\CatalogCategoryProduct','product_id');	
    }
    
}