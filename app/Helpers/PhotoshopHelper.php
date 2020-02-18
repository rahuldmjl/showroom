<?php
namespace App\Helpers;
use Illuminate\Support\Facades\DB;
use App;
use App\productListModel;
use Auth;
use Config;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use App\photography_product;
class PhotoshopHelper
{


//Get the Status From SELECT * from photosraphy_status
public static function getStatus($id)
{
    DB::setTablePrefix('');
    $status=DB::table('photosraphy_status')
            ->where('entity_id','=',$id)
            ->get();
            DB::setTablePrefix('dml_');
   return $status;
}
//Get department from url 
public static function getDepartment($url)
{
    $url=explode('Photoshop/',$url);
    $depart=explode('/',$url[1]);
    return $depart[0];
}


public static function get_status($name)
{
    $status=DB::table('photoshop_status_types')
            ->where('status_name','=',$name)
            ->get();
            return $status;
}
public static function store_cache_table_data($cache)
{
   
   $product_id=$cache['product_id'];
   $status=$cache['status'];
   $status1=PhotoshopHelper::getStatus($status);
   $sta="";
   $dt = new DateTime();
  
 foreach($status1 as $s)
 {
     $sta.=$s->name;
 }
 $stat="";
 $name=strtoUpper($cache['url'])."_".strtoUpper($sta);
 $statusid=photoshopHelper::get_status($name);
 foreach($statusid as $sr)
 {
$stat=$sr->status_id;
 }
 
   $insert=array(
       'product_id'=>$product_id,
       'action_name'=>$stat,
       'action_by'=>$cache['action_by'],
       'action_date_time'=>date("Y-m-d H:i:s")
   );
    
 
        PhotoshopHelper::addintoCasheTable($insert);
   
   
  
}
public static function addintoCasheTable($data)
{
    $data=DB::table('photoshop_caches')
                ->insert($data);

}

}   
?>