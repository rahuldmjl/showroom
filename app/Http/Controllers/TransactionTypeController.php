<?php

namespace App\Http\Controllers;

use App\TransactionType;
use Illuminate\Http\Request;

class TransactionTypeController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$types = TransactionType::orderBy('id', 'DESC')->paginate(10);

		$totalcount = TransactionType::orderBy('id', 'DESC')->count();
		return view('transactiontype.index', compact('types','totalcount'))
			->with('i', ($request->input('page', 1) - 1) * 5);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		$types = TransactionType::select();

		return view('transactiontype.create', compact('types'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$this->validate($request, [
			'name' => 'required|unique:permissions',
		]);

		$type = TransactionType::create(['name' => $request->input('name')]);

		return redirect()->route('transactiontype.index')
			->with('success', 'Transaction Type created successfully');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		$type = TransactionType::find($id);
		return view('transactiontype.edit', compact('type'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		$this->validate($request, [
			'name' => 'required',
		]);

		$type = TransactionType::find($id);
		$type->name = $request->input('name');
		$type->save();

		return redirect()->route('transactiontype.index')
			->with('success', 'Transaction Type updated successfully');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		$type = TransactionType::find($id);
		$type->delete();
		return response()->json([
			'success' => 'Record deleted successfully!',
		]);
	}
	public function transactiontyperesponse(Request $request){

        $columns = array( 
                    0 =>'id', 
                    1 =>'name',
                    2 =>'action');

        $results =  TransactionType::orderBy('id','DESC');
       // print_r($results->get());exit;
        $totalData = $results->count();
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')]; 
        //print_r($order);exit;
        $dir = $request->input('order.0.dir');
        $name = $request->input('_name');
        if(empty($request->input('search.value')))
        {
                 $resultslist = TransactionType::offset($start)
                     ->limit($limit)
                     ->orderBy($order,$dir)
                     ->get();
        }else {
            $search = $request->input('search.value'); 
            $resultslist =TransactionType::where('name', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->orderBy($order,$dir)->get();
           
            $totalFiltered =   TransactionType::where('name', 'LIKE',"%{$search}%")->count();
        }

        $data = array();

        
        if(!empty($resultslist))
        {
            
            foreach ($resultslist as $resultslist)
            {
            	$action = '<a class="color-content table-action-style" href="' .route('transactiontype.edit',$resultslist->id) .'"><i class="material-icons md-18">edit</i></a>';
            	$action .=' <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleteperm('.$resultslist->id.',\''.csrf_token().'\');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>';
                $data[] = array(++$start,$resultslist->name,$action);
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
