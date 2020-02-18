<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\psd;
use Auth;
use DB;
use App\Placement;
use App\Helpers\PhotoshopHelper;
class PlacementController extends Controller
{
   
    public $psd;
    public $user;
    public $placement;
    public function __construct()
    {
       
        $this->psd=psd::getPsdProduct();
        $user=Auth::user();
        $this->placement=Placement::all();
          
    }
   //get Placement Pending List
    public function get_placement_pending_list(){
       
         $list=collect($this->psd)->where('status','=','3')->where('next_department_status','=','0');
       return View('Photoshop/Placement/placement_pending',compact('list'));

    }
    public function get_placement_done_list()
    {
        $done_list=collect($this->placement)->where('status',3);
       return View('Photoshop/Placement/placement_done',compact('done_list'));
    }

    public function get_placement_rework_list()
    {
        $rework_list=collect($this->placement)->where('status',4);
        return View('Photoshop/Placement/placement_rework',compact('rework_list'));
    }

    public function get_pending_list_data_submit(Request $request)
    {
        $user=Auth::user();
        $placement_data=new Placement();
        if($request->input('status') !="1")
        {
            
  
            $placement_data->product_id=$request->input('product_id');
           $placement_data->category_id=$request->input('category_id');
            $placement_data->status=$request->input('status');
            $placement_data->current_status='1';
            $placement_data->next_department_status='0';
         
           //Cache table data Insert
           if($request->input('status')=='3')
           {
            $placement_data->save();
            $cache=array(
                'product_id'=>$request->input('product_id'),
                'url'=>PhotoshopHelper::getDepartment($request->url()),
                'status'=>$request->input('status'),
                'action_by'=>$user->id
    
    
            );
             PhotoshopHelper::store_cache_table_data($cache);
             placement::getUpdatestatusdone($request->input('product_id'));
           }
          
        }
        return redirect()->back()->with('success', 'Psd status Change Successfull');
     
    }

    public function submit_done_list(Request $request)
    {
        $user=Auth::user();
       
        if($request->input('status') !='0')
        {
            //cache table data insert 
            $cache=array(
                'product_id'=>$request->input('product_id'),
                'url'=>PhotoshopHelper::getDepartment($request->url()),
                'status'=>$request->input('status'),
                'action_by'=>$user->id
    
            );
           
           PhotoshopHelper::store_cache_table_data($cache);
         placement::update_placement_status($request->input('product_id'),$request->input('status'));
    if($request->input('status')=='4')
    {
         placement::delete_from_editing($request->input('product_id'));
   
         placement::delete_from_jpeg($request->input('product_id'));
          placement::getUpdatestatus_JPEG($request->input('product_id'));
    
      
    }
    
        }
return redirect()->back()->with('success', 'Psd status Change Successfull');
    
}
}
