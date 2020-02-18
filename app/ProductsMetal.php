<?php 
// namespace App;
// use Illuminate\Database\Eloquent\Model;

// class ProductsMetal extends Model {

// 	protected $guarded = [];
//     protected $table = 'products_metal';

//     	public function ProductsMetal()
// 		{
// 		    return $this->belongsTo('App\ProductsMetal','metal_product_id');
// 		}
// }



namespace App;
use Illuminate\Database\Eloquent\Model;

class ProductsMetal extends Model {

	protected $guarded = [];
    protected $table = 'products_metal';

    	public function products()
		{
		    return $this->belongsTo('App\Products','id');
		}
}
