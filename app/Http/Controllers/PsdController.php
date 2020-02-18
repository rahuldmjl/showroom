<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PhotoshopHelper;
use App\photography;
use App\psd;
use App\productListModel;
use App\photography_product;
use DB;
use Auth;
class PsdController extends Controller
{
  
 
  public $photography;
  public $psd;
  public $user;
  public function __construct()
  {
      $this->photography=photography::getphotographyProduct();
      $this->psd=psd::getPsdProduct();
      $user=Auth::user();
        
  }
    public function index()
    {
        return view('Photoshop/PSD/index');
    }
    /*
    Get Pending List 
    this list come from photography done option
    */
    public function get_psd_pending_list()
    {
     
      $psdpending=collect($this->photography)->where('status','=','3')->where('next_department_status','=','0');
       return view('Photoshop/PSD/psd_pending',compact('psdpending'));
    }
    /*
    Get done List 
    this list come from psd  done option
    */
    public function get_psd_done_list()
    {
      $psd_done_list=collect($this->psd)->where('status','=','3');
      return view('Photoshop/PSD/psd_done',compact('psd_done_list'));
    }
      /*
    Get rework List 
    this list come from psd  rework option
    */
    public function get_psd_rework_list()
    {
       $psd_rework=collect($this->psd)->where('status','=','4');
        return view('Photoshop/PSD/psd_rework',compact('psd_rework'));
    }
    /* Get All Data from ppending From psd Department
    Submit Pending Data into post method
    */

    public function get_data_from_psd_pending_list(Request $request)
    {
      
      $user=Auth::user();
      $photoshop=new psd();
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
           photography::getUpdatenextdepartmentdone($request->input('product_id'));
         }
        
      }
        return redirect()->back()->with('success', 'Psd status Change Successfull');
     
    }

    public function submit_done_list(Request $request)
    {
      $user=Auth::user();
        $psd=psd::find($request->input('id'));
       if($request->input('status') !='0')
       {
        $cache=array(
          'product_id'=>$request->input('product_id'),
          'url'=>PhotoshopHelper::getDepartment($request->url()),
          'status'=>$request->input('status'),
          'action_by'=>$user->id

      );
      PhotoshopHelper::store_cache_table_data($cache);
      psd::update_psd_status($request->get('product_id'),$request->input('status'));
       }
    if($request->input('status')=='4')
    {
       psd::delete_from_below_department($request->get('product_id'));
       psd::getUpdatestatus_psd($request->input('product_id'));
    }
       return redirect()->back()->with('success', 'Psd status Change Successfull');
    }

}
