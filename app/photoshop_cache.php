<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class photoshop_cache extends Model
{
    
    public static function getproduct($id)
    {
        return photoshop_cache::where('product_id','=',$id)->get();
    }
    public function getProductdetail()
   {
   return $this->hasOne('App\photography_product','id','product_id');
   }

   public function getactionby()
   {
       return $this->hasOne('App\User','id','action_by');
   }
   
   public function getDepartmentStatus()
   {
       return $this->hasOne('App\photoshop_status_type','status_id','action_name');
   }

}
