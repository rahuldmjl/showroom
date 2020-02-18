<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class photography_product extends Model
{
    public static function get_product_list()
    {
        return photography_product::all();
    }
   public function category()
   {
       return $this->hasOne('App\category','entity_id','categoryid');
   }

   public static function getProductbyId($id)
   {
       return photography_product::where('id','=',$id)->get();
   }


   public static function getUpdatestatusdone($productid)
   {
       $data=array('status'=>'1');
       return photography_product::where('id','=',$productid)->update($data);
   }
   public static function getUpdatestatuspending($productid)
   {
       $data=array('status'=>'0');
       return photography_product::where('id','=',$productid)->update($data);
   }
}
