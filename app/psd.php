<?php

namespace App;
use App\EditngModel;
use Illuminate\Database\Eloquent\Model;
use App\placement;
use App\Editing;
use App\jpeg;
class psd extends Model
{
 public static function getPsdProduct()
{
      return psd::all();
}
public function getProduct()
 {
   return $this->hasOne('App\photography_product','id','product_id');
 }
 public function category()
 {
     return $this->hasOne('App\category','entity_id','category_id');
 }
 public static function update_psd_status($productid,$status)
 {
  $data=array('status'=>$status);
  return psd::where('product_id','=',$productid)->update($data);
  }

  public static function delete_from_below_department($product_id)
  {   
    
    placement::where('product_id','=',$product_id)->delete();
    EditingModel::where('product_id','=',$product_id)->delete();
    jpegModel::where('product_id','=',$product_id)->delete();
      
   
  
  
  }
  public static function getUpdatestatus_psd($productid)
  {
      $data=array('next_department_status'=>'0');
     
      return psd::where('product_id','=',$productid)->update($data);
  }
}
