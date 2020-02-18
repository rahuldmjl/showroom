<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\photography;
use App\productListModel;
use App\Helpers\PhotoshopHelper;
use App\photography_product;
use DB;
use Auth;
class PhotoshopController extends Controller
{
    public $product;
    public $photography;
    public function __construct()
    {
        $this->product=photography_product::all();
        $this->photography=photography::getphotographyProduct();
       
    }
    public function index()
    {

     
        $totoalproduct=count($this->product);
        $photo=collect($this->photography)->where('status',3);
        $totalphotographydone=count($photo);
        $pending=collect($this->product)->where('status',0);
        $totalphotographypending=count($pending);
        return view('Photoshop/Photography/index',compact('totoalproduct','totalphotographydone','totalphotographypending'));
    }

    /*
    Photography pending get data from this function
    */
    public function get_pending_list()
    {
       $pendinglist=array();
     
       // $pending=photography_product::all();
        $pendinglist=collect($this->product)->where('status','=',0);
      
      $totalproduct= count($pendinglist);  
      
   return view('Photoshop/Photography/photography_pending',compact('pendinglist','totalproduct'));
 
  
    }
     /*
    Photography done get data from this function
    */
    public function get_done_list()
    {
     $donelist=collect($this->photography)->where('status','=',3);
 

  return view('Photoshop/Photography/photography_done',compact('donelist'));
    }
     /*
    Photography Rework get data from this function
    */
    public function get_rework_list()
    {
     
       
         $reworklist=collect($this->photography)->where('status','=',4);
      return view('Photoshop/Photography/photography_rework',compact('reworklist'));
    }

    /*
    photography pending submit button action
    get all detail from photography pending list 

    */

    public function pending_list_submit(Request $request)
    {
        $user=Auth::user();
        
        $photoshop=new photography();
     
       
        if($request->input('status') !="1")
        {
            

            $photoshop->product_id=$request->input('product_id');

            $photoshop->category_id=$request->input('category_id');
            $photoshop->status=$request->input('status');
            $photoshop->current_status='1';
            $photoshop->next_department_status='0';
         
           //Cache table data Insert
           if($request->input('status')=='3')
           {
            $photoshop->save();
            $cache=array(
                'product_id'=>$request->input('product_id'),
                'url'=>PhotoshopHelper::getDepartment($request->url()),
                'status'=>$request->input('status'),
                'action_by'=>$user->id
    
    
            );
             PhotoshopHelper::store_cache_table_data($cache);
             photography_product::getUpdatestatusdone($request->input('product_id'));
             photography::getUpdatestatusdone($request->input('product_id'));
           }
          
        }
      
        
 return  redirect('Photoshop/Photography/pending')->with('message','Photoshop Status Change Successfull');
    }
/*
done list submit for particular product change the photography status
done to rework 
*/
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
            photography::update_photography_status($request->get('product_id'),$request->input('status'));
         if($request->input('status')=='4')
         {
             photography::delete_from_below_department($request->input('product_id'));
             photography::getUpdatestatusdone($request->input('product_id'));
             
         }
         
            return redirect()->back()->with('success', 'Photography status Change Successfull');
        }
        else{
            return redirect()->back()->with('success', 'Select the photography status');
        }
  
        
        
    }

  
}
