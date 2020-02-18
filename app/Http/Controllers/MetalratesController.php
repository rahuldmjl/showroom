<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\User;
use App\Metalrates;
use Validator;
use App\ManageCharges;
use App\VendorCharges;
use DB;
use Hash;
use Auth;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Config;


class MetalratesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) 
    {
        
        $vendor_id=$request->get('vendor_id');
        $name=$request->get('name');
     

        $metalrates = DB::select("SELECT grptype.grp_metal_type_id, grptype.metal_type AS mname, dmlvendor.* FROM  dml_vendor_metalrates AS dmlvendor INNER JOIN grp_metal_type AS grptype ON dmlvendor.metal_type = grptype.grp_metal_type_id where dmlvendor.vendor_id =".$vendor_id."  ORDER BY `dmlvendor`.`metalrates_id` DESC");
        
        $totalcount=count($metalrates);

        return view('vendor/metalrates/index',['vendor_id'=>$vendor_id,'name'=>$name],compact('metalrates','vendor_id','name','totalcount'))->with('i', ($request->input('page', 1) - 1) * 5);;
    }



     public function metalresponse(Request $request)
    {
        
        $columns = array( 
                        0 =>'metalrates_id',
                        1 =>'metal_quality',
                        2 =>'metal_type',
                        3 =>'gold_rate',
                        4 =>'rate');
            //$results =DB::select("SELECT grptype.grp_metal_type_id, grptype.metal_type AS mname, dmlvendor.* FROM  dml_vendor_metalrates AS dmlvendor INNER JOIN grp_metal_type AS grptype ON dmlvendor.metal_type = grptype.grp_metal_type_id ORDER BY `dmlvendor`.`metalrates_id` DESC");
        DB::setTablePrefix('');
        $results = DB::table('dml_vendor_metalrates as dmlvendor')
                    ->select('grptype.grp_metal_type_id','grptype.metal_type AS mname','dmlvendor.*')
                    ->join('grp_metal_type as grptype','grptype.grp_metal_type_id','=','dmlvendor.metal_type')->where('dmlvendor.vendor_id','=',$request->_id)
                    ->orderBy('dmlvendor.metalrates_id', 'DESC');
                    
        //echo $results;exit;
        $totalData = $results->count();
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')]; 
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
                 $resultslist = DB::table('dml_vendor_metalrates as dmlvendor')
                    ->select('grptype.grp_metal_type_id','grptype.metal_type AS mname','dmlvendor.*')
                    ->join('grp_metal_type as grptype','grptype.grp_metal_type_id','=','dmlvendor.metal_type')->where('dmlvendor.vendor_id','=',$request->_id)->offset($start)
                     ->limit($limit)
                     ->orderBy($order,$dir)
                     ->get();
        }else {
            $search = $request->input('search.value'); 
            $resultslist = $results->whereRaw('(metalrates_id like "%' . $search . '%" or metal_quality like "%' . $search . '%" or gold_rate like "%' . $search . '%" or rate like "%' . $search . '%")')->offset($start)->limit($limit)->orderBy($order,$dir)->get();
            //$results->where('metalrates_id', 'LIKE',"%{$search}%")->orWhere('metal_quality', 'LIKE',"%{$search}%")->orWhere('gold_rate', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->orderBy($order,$dir)->get();
            $totalFiltered =  $results->whereRaw('(metalrates_id like "%' . $search . '%" or metal_quality like "%' . $search . '%" or gold_rate like "%' . $search . '%" or rate like "%' . $search . '%")')->count();
            //$results->where('metalrates_id', 'LIKE',"%{$search}%")->orWhere('metal_quality', 'LIKE',"%{$search}%")->orWhere('gold_rate', 'LIKE',"%{$search}%")->count();            
        }
        $data = array();

        $i=0;
        if(!empty($resultslist))
        {
            
            foreach ($resultslist as $resultslist)
            {
                 $action='<a class="color-content table-action-style" href="'.route('metalrates.edit',$resultslist->metalrates_id).'"><i class="material-icons md-18">edit</i></a>
                                  <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleteuser('.$resultslist->metalrates_id.',\''.csrf_token().'\');" data-token="\''.csrf_token() .'\'"><i class="material-icons md-18">delete</i>';
                                  
                $id='<input type="hidden" name="vendor_id" id="user_id" value="'.$resultslist->vendor_id.'">';
                $data[] = array(++$start,$resultslist->metal_quality,$resultslist->mname,$resultslist->gold_rate,$resultslist->rate,$action,$id);
            }
                $json_data = array(
                        "draw"            => intval($request->input('draw')),  
                        "recordsTotal"    => intval($totalData),  
                        "recordsFiltered" => intval($totalFiltered), 
                        "data"            => $data   
                        );
            echo json_encode($json_data);
        }        
         DB::setTablePrefix('');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $vendor_id=$request->get('vendor_id');
        $name=$request->get('name');

        $metaltype=DB::table(DB::raw(' grp_metal_type'))->get();
        $type= Config::get('constants.Metal_Quality');
         
        return view('vendor/metalrates/create',['vendor_id'=>$vendor_id,'name'=>$name],compact('vendor_id','name','metaltype','type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       // print_r($request->all());exit;
       
        $vendor_id=$request->get('vendor_id');
        $name=$request->get('name');
        //echo $vendor_id;exit;
        $input['metal_quality'] = $request->input('metal_quality');
        $rules = array('metal_quality' => 'unique:vendor_metalrates,metal_quality');
        $name=$request->get('name');
         $this->validate($request, [
            'metal_type' => 'required',
            'metal_quality'=>'required',
            'gold_rate'=>'required|numeric|min:1|max:100',
            'rate'=>'required|max:10'
            
        ]);

        $validator = Validator::make($input, $rules);

        $vendors=User::all();
        $checkmetal = Metalrates::where('vendor_id',$vendor_id)->where('metal_quality',$request->input('metal_quality'))->get();
        //echo "<pre>";print_r($checkmetal);exit;
        //exit;
        if (count($checkmetal) > 0) {
            return redirect()->route('metalrates.create',['vendor_id'=>$vendor_id,'name'=>$name])
           ->withErrors($validator)->withInput();
        }else{
            $input = $request->all();
            $metalrates = Metalrates::create($input);
            return redirect()->route('metalrates.index',['vendor_id'=>$vendor_id,'name'=>$name])
           ->with('success',Config::get('constants.message.vendor_metal_rates_add_success'));
        }
         
        
       
    }

   

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user=User::all();
       $metalrates = Metalrates::find($id);
        return view('vendor.metalrates.show', compact('metalrates','user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
       //print_r($request->all());exit;

       $vendor_id = $request->vendor_id;
       $name = $request->name;
       $metalrates = Metalrates::find($id);
       $metaltype=DB::table(DB::raw(' grp_metal_type'))->get();
      // print_r($metalrates);exit;
       return view('vendor.metalrates.edit',['vendor_id'=>$vendor_id,'name'=>$name], compact('metalrates','metaltype','vendor_id','name'));   
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
       
        $vendor_id=$request->get('vendor_id');
        $name=$request->get('name');
   // print_r($request->all());exit;
       

        $input = $request->all();
        $this->validate($request, [
            'metal_type' => 'required',
            'metal_quality'=>'required',
            'gold_rate'=>'required|numeric|min:1|max:100',
            'rate'=>'required|numeric|min:0'
            
        ]);
        $metalrates = Metalrates::find($id);

        $metalrates->update(['metal_quality'=>$request->metal_quality,'metal_type'=>$request->metal_type,'gold_rate'=>$request->gold_rate,'rate'=>$request->rate,'vendor_id'=>$request->id]);
        return redirect()->route('metalrates.index',['vendor_id'=>$request->id ,'name'=>$name])
         ->with('success',Config::get('constants.message.vendor_metal_rates_update_success'));
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Metalrates::find($id)->delete();
        return response()->json([
            'success' => 'Record deleted successfully!',
        ]);
        $return_data = array();

        $return_data['response'] = 'success';
        echo json_encode($return_data);exit;
        return redirect()->route('metalrates.index')
            ->with('success', 'Record deleted successfully');
    }


    public function getmetaldata(Request $request )
    {
        //print_r($request->all());exit;

        $metaldata = DB::select("SELECT `gold_rate`,  `rate`  FROM `grp_metal_quality` WHERE metal_quality= '$request->id'");
        
        return response()->json(['result' => $metaldata]);
    }
}
