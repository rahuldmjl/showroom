<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Product;
use App\User;
use DB;
use Hash;
use Auth;
use Config;


class ProductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $data = Product::orderBy('vendor_product_id', 'DESC')->paginate();

        $totalcount=$data->count();
        return view('vendor/producttype/index', compact('data','totalcount'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         return view('vendor/producttype/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         $this->validate($request, [
            'name' => 'required',
            
        ]);
        $input = $request->all();
        $product = Product::create($input);
      
            return redirect()->route('vendor-product-type.index')
             ->with('success',Config::get('constants.message.vendor_ProductType_add_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product=Product::find($id);
        return view('product.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
         $product = Product::find($id);
        return view('vendor/producttype/edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         $this->validate($request, [
            'name' => 'required',
        ]);
         
            $input = $request->all();
            $product = Product::find($id);
            $product->update($input);
            return redirect()->route('vendor-product-type.index')
                ->with('success',Config::get('constants.message.vendor_ProductType_update_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
          Product::find($id)->delete();
        return response()->json([
            'success' => 'Record deleted successfully!',
        ]);
        $return_data = array();

        $return_data['response'] = 'success';
        echo json_encode($return_data);exit;
        return redirect()->route('vendor-product-type.index')
            ->with('success', 'Product Type deleted successfully');
    }
    
    public function productresponse(Request $request){
        $columns = array( 
                        0 =>'vendor_product_id',
                        1 =>'name',
                        2 =>'action');
             $results= Product::orderBy('vendor_product_id', 'DESC')->distinct();

        $totalData = $results->count();
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')]; 
        $dir = $request->input('order.0.dir');
        if(empty($request->input('search.value')))
        {
                 $resultslist = $results->offset($start)
                     ->limit($limit)
                     ->orderBy($order,$dir)
                     ->get();
        }else {
            $search = $request->input('search.value'); 
            $resultslist =   $results->where('vendor_product_id', 'LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->orderBy($order,$dir)->get();

            $totalFiltered = $results->where('vendor_product_id', 'LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")->count();
        }
        $data = array();
        
        if(!empty($resultslist))
        {
             $i=0;
            foreach ($resultslist as $resultslist)
            {
               
                $action='<a class="color-content" href="'.route('vendor-product-type.show',$resultslist->vendor_product_id).'" style="display: none;"><i class="material-icons md-18">show</i></a>
                        <a class="color-content" href="'.route('vendor-product-type.edit',$resultslist->vendor_product_id) .'"><i class="material-icons md-18">edit</i></a>
                        <a class="color-content" href="javascript:void(0);" onclick="event.preventDefault();deletedproduct('.$resultslist->vendor_product_id.',\''. csrf_token() .'\');" data-token="\''. csrf_token() .'\'"><i class="material-icons md-18">delete</i></a>  ';
                
                $data[] = array(++$i,$resultslist->name,$action);
            }
                $json_data = array(
                        "draw"            => intval($request->input('draw')),  
                        "recordsTotal"    => intval($totalData),  
                        "recordsFiltered" => intval($totalFiltered), 
                        "data"            => $data   
                        );
            echo json_encode($json_data);
        }
    }
}
