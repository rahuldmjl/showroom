<?php 
namespace App;
use Illuminate\Database\Eloquent\Model;

class CatalogCategoryProduct extends Model {

	protected $guarded = [];
    protected $table = 'products_category';


		public function products()
		{
		    return $this->belongsTo('App\Products','id');
		}
}
?>