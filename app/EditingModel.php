<?php

namespace App;
use App\psd;
use App\productListModel;
use App\placement;
use App\jpegModel;
use DB;

use Illuminate\Database\Eloquent\Model;

class EditingModel extends Model
{

 public static function getEditingProduct()
    {
        return placement::all();
     }
    public function category()
    {
        return $this->hasOne('App\category','entity_id','category_id');
    }
   
   
 public function getProduct()
 {
   return $this->hasOne('App\photography_product','id','product_id');
 }
 public static function getUpdatestatusdone($productid)
 {
     $data=array('next_department_status'=>'1');
     return placement::where('product_id','=',$productid)->update($data);
 }
 public static function getUpdatestatusrework($productid)
 {
     $data=array('next_department_status'=>'0');
     return EditingModel::where('product_id','=',$productid)->update($data);
 }
public static function update_editing_status($productid,$status)
{
    $data=array('status'=>$status);
    return EditingModel::where('product_id','=',$productid)->update($data);
}
public static function delete_from_jpeg_List($productid)
{

    return jpegModel::where('product_id','=',$productid)->delete();
  
}
}
