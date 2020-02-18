<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PhotoshopHelper;
use App\jpegModel;

use App\EditingModel;
use Auth; 
class JpegController extends Controller
{
  
   public $product;
   public $jpeg;
  
   public function __construct()
   {
       $this->jpeg=jpegModel::getJpgPendignProduct();
      $this->product=jpegModel::all();
    
   }
   
   public function get_pending_list_jpeg()
   {
     $jpeg_pending_list=collect($this->jpeg)->where('status','=','3')->where('next_department_status','=','0');   
  
     return view('Photoshop/JPEG/jpeg_pending',compact('jpeg_pending_list'));
   }
//get all done list of JPEG Department

   public function get_done_list_jpeg()
   {
      $done_list=collect($this->product)->where('status',3);
      
       return view('Photoshop/JPEG/jpeg_done',compact('done_list'));

   }
   //get all Rework list of JPEG Department
   public function get_rework_list_jpeg()
   {
      $rework_list=collect($this->product)->where('status',4);
       return view('Photoshop/JPEG/jpeg_rework',compact('rework_list'));
   }

   public function submit_pending_list_jpeg(Request $request)
   {
      $user=Auth::user();
      $jpeg=new jpegModel();
      if($request->input('status') !="1")
      {
         $jpeg->product_id=$request->input('product_id');
         $jpeg->category_id=$request->input('category_id');
          $jpeg->status=$request->input('status');
          $jpeg->current_status='1';
          $jpeg->next_department_status='0';
       
         //Cache table data Insert
         if($request->input('status')=='3')
         {
          $jpeg->save();
          $cache=array(
              'product_id'=>$request->input('product_id'),
              'url'=>PhotoshopHelper::getDepartment($request->url()),
              'status'=>$request->input('status'),
              'action_by'=>$user->id
                      );
           PhotoshopHelper::store_cache_table_data($cache);
           jpegModel::getUpdatestatusdone($request->input('product_id'));
         }
        
      }
      return redirect()->back()->with('success', 'Jpeg status Change Successfull');
      
   }

   public function submit_done_list_jpeg(Request $request)
   {
     
      $user=Auth::user();
      if($request->input('status') !='0')
      {
     
         $cache=array(
            'product_id'=>$request->input('product_id'),
            'url'=>PhotoshopHelper::getDepartment($request->url()),
            'status'=>$request->input('status'),
            'action_by'=>$user->id
  
        );
        PhotoshopHelper::store_cache_table_data($cache);
        jpegModel::update_Jpeg_status($request->get('product_id'),$request->input('status'));
     $message=array(
      'success'=>'Jpeg Done Change Successfull',
      'class'=>'alert alert-success',
  );
      }
      else{
         $message=array(
            'success'=>'Jpeg Select Status',
            'class'=>'alert alert-danger',
        );
      }
      return redirect()->back()->with($message);
   }
}
