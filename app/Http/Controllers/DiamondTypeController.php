<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DiamondType;
use App\User;
use Config;
use DB;
use Hash;
use Auth;

class DiamondTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = DiamondType::orderBy('vendor_diamond_id', 'DESC')->paginate();

        $totalcount=$data->count();
        return view('vendor.diamondtype.index', compact('data','totalcount'))
            ->with('i', ($request->input('page', 1) - 1) * 5);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('vendor/diamondtype/create');
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
        $product = DiamondType::create($input);
      
            return redirect()->route('vendor-diamond-type.index')
            ->with('success',Config::get('constants.message.vendor_DiamondType_add_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $diamond=DiamondType::find($id);
        return view('vendor.diamondtype.show', compact('diamond'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $diamond = DiamondType::find($id);
        return view('vendor/diamondtype.edit', compact('diamond'));
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
            $diamond = DiamondType::find($id);
            $diamond->update($input);
            return redirect()->route('vendor-diamond-type.index')
                 ->with('success',Config::get('constants.message.vendor_DiamondType_update_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
            DiamondType::find($id)->delete();
        return response()->json([
            'success' => 'Record deleted successfully!',
        ]);
        $return_data = array();

        $return_data['response'] = 'success';
        echo json_encode($return_data);exit;
        return redirect()->route('diamondtype.index')
            ->with('success', 'Diamond Type deleted successfully');
    }

    public function diamondrespose(Request $request){
         $columns = array( 
                        0 =>'vendor_diamond_id',
                        1 =>'name',
                        2 =>'action');
             $results= DiamondType::orderBy('vendor_diamond_id', 'DESC')->distinct();

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
            $resultslist =   $results->where('vendor_diamond_id', 'LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->orderBy($order,$dir)->get();

            $totalFiltered = $results->where('vendor_diamond_id', 'LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")->count();
        }
        $data = array();
        
        if(!empty($resultslist))
        {
             $i=0;
            foreach ($resultslist as $resultslist)
            {
               
                $action='<a class="color-content" href="'.route('vendor-diamond-type.show',$resultslist->vendor_diamond_id).'" style="display: none;"><i class="material-icons md-18">show</i></a>
                        <a class="color-content" href="'.route('vendor-diamond-type.edit',$resultslist->vendor_diamond_id) .'"><i class="material-icons md-18">edit</i></a>
                        <a class="color-content" href="javascript:void(0);" onclick="event.preventDefault();deletediamond('.$resultslist->vendor_diamond_id.',\''. csrf_token() .'\');" data-token="\''. csrf_token() .'\'"><i class="material-icons md-18">delete</i></a>  ';
                
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
