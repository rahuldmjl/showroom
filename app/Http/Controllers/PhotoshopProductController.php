<?php

namespace App\Http\Controllers;
use App\photography_product;
use App\category;
use Illuminate\Http\Request;

class PhotoshopProductController extends Controller
{
    public $list_prpduct;
    public $category;
    public function __construct()
    {
        $this->list_prpduct=collect(photography_product::get_product_list());
        $this->category=collect(category::all());
       
    }
    public function list_of_product()
    {
        $list=$this->list_prpduct;
        $category=$this->category;
        $color=$this->list_prpduct;
       return view('Photoshop/Product/list',compact('list','category','color'));
    }

    public function add_of_product()
    {
        return view('Photoshop/Product/add');
    }

    public function list_of_product_filter(Request $request)
    {
        $category=$request->input('category');
        $color=$request->input('color');
        $status=$request->input('status');
        $sku=$request->input('sku');
        $filter=array(
            'category'=>$category,
            'color'=>$color,
            'status'=>$status,
            'sku'=>$sku
        );
        
        
         if($category !=="null")
        {
          
            $list=$this->list_prpduct->where('categoryid',$category);
         
        }
        else if($color !=="null")
        {
            $list=$this->list_prpduct->where('color',$color);
           
        }
        else if($status !=="null")
        {
            $list=$this->list_prpduct->where('status',$status);
         
        }
        else if($sku !=="null"){
            $list=$this->list_prpduct->where('sku',$sku);

        }else{
            $list=$this->list_prpduct;
        }
        $category=$this->category;
        $color=$this->list_prpduct;
        return view('Photoshop/Product/list',compact('list','category','color','filter'));
    }

   
}
