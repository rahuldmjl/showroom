<?php 
namespace App;
use Illuminate\Database\Eloquent\Model;

class ProductsStone extends Model {

	protected $guarded = [];
    protected $table = 'products_stone';
    protected $primaryKey = 'grp_stone_id';


		
    	public function products()
		{
		    return $this->belongsTo('App\Products','id');
		}
}
?>