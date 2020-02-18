<?php

namespace App;
use App\placement;
use App\EditingModel;
use App\psd;
use App\jpegModel;
use App\photography_product;
use Illuminate\Database\Eloquent\Model;

class photography extends Model
{
    
 public static function getphotographyProduct()
 {
   return photography::all();
 }
   
 public function getProduct()
 {
   return $this->hasOne('App\photography_product','id','product_id');
 }
 public function category()
 {
     return $this->hasOne('App\category','entity_id','category_id');
 }

 public static function update_photography_status($productid,$status)
 {
  $data=array('status'=>$status);
  return photography::where('product_id','=',$productid)->update($data);
 }
  public static function getUpdatestatusdone($productid)
  {
      $data=array('next_department_status'=>'0');
      return photography::where('product_id','=',$productid)->update($data);
  }
  public static function getUpdatenextdepartmentdone($productid)
  {
      $data=array('next_department_status'=>'1');
      return photography::where('product_id','=',$productid)->update($data);
  }
  public static function updateprodtographystatus($productid)
  {
      $data=array('status'=>'0');
      return photography_product::where('id','=',$productid)->update($data);
  }
  public static function delete_from_below_department($product_id)
  {   
    
    placement::where('product_id','=',$product_id)->delete();
    EditingModel::where('product_id','=',$product_id)->delete();
    jpegModel::where('product_id','=',$product_id)->delete();
    psd::where('product_id','=',$product_id)->delete(); 
   
  
  
  }
}
