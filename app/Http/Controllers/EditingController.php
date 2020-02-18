<?php

namespace App\Http\Controllers;
use App\Helpers\PhotoshopHelper;
use Illuminate\Http\Request;
use App\EditingModel;
use App\psd;
use Auth;


class EditingController extends Controller
{
    //get all Pending list of Editing Department
    public $product;
    public $psd;
   
    public function __construct()
    {
        $this->psd=EditingModel::getEditingProduct();
        $this->editing=EditingModel::all();
        $user=Auth::user();
    }
    
    public function get_pending_list_editing()
    {
        $psd_done=collect($this->psd)->where('status','=','3')->where('next_department_status','=','0');        
     //  $editing_pending_list=PhotoshopHelper::get_editing_pending_list();
      return view('Photoshop/Editing/editing_pending',compact('psd_done'));
    }
   //get all done list of Editing Department

    public function get_done_list_editng()
    {
       
      
         $done_list=collect($this->editing)->where('status',3);
       return view('Photoshop/Editing/editing_done',compact('done_list'));

    }
    public function get_rework_list_editing()
    {
        $editing_rework_list=collect($this->editing)->where('status',4);
       return view('Photoshop/Editing/editing_rework',compact('editing_rework_list'));
    }

    public function get_pending_submit_editing(Request $request)
    {
        $user=Auth::user();
      $editing=new EditingModel();
      if($request->input('status') !="1")
      {
          

          $editing->product_id=$request->input('product_id');

          $editing->category_id=$request->input('category_id');
          $editing->status=$request->input('status');
          $editing->current_status='1';
          $editing->next_department_status='0';
       
         //Cache table data Insert
         if($request->input('status')=='3')
         {
          $editing->save();
          $cache=array(
              'product_id'=>$request->input('product_id'),
              'url'=>PhotoshopHelper::getDepartment($request->url()),
              'status'=>$request->input('status'),
              'action_by'=>$user->id
  
  
          );
           PhotoshopHelper::store_cache_table_data($cache);
           EditingModel::getUpdatestatusdone($request->input('product_id'));
         }
        
      }
        return redirect()->back()->with('success', 'Editing status Change Successfull');
     
   
     
      //  return redirect()->back()->with($message);
    }

    public function submit_done_list_editng(Request $request)
    {
        $user=Auth::user();
     if($request->input('status')=='0')
     {
      
        $message=array(
            'success'=>'Editing Select Status',
            'class'=>'alert alert-danger'
        );
        
     }
     else{


        $cache=array(
            'product_id'=>$request->input('product_id'),
            'url'=>PhotoshopHelper::getDepartment($request->url()),
            'status'=>$request->input('status'),
            'action_by'=>$user->id
  
        );
       PhotoshopHelper::store_cache_table_data($cache);
       EditingModel::update_editing_status($request->get('product_id'),$request->input('status'));
     
      
       
        $message=array(
            'success'=>'Editing Rework Successfull',
            'class'=>'alert alert-success'
        );
       if($request->input('status')=='4')
       {
        EditingModel::getUpdatestatusrework($request->input('product_id'));
        EditingModel::delete_from_jpeg_List($request->input('product_id'));
       }
       
      
     }
    return redirect()->back()->with($message);   
    }

}
