<?php

namespace App\Http\Controllers;

use App\Payment;
use App\PaymentType;
use Config;
use Illuminate\Http\Request;
use DB;

class PaymentTypeController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$paymenttype = PaymentType::orderBy('created_at', 'DESC')->paginate(10);

		$count=PaymentType::orderBy('created_at', 'DESC')->count();
		return view('account.payment-types.index', compact('paymenttype','count'))->with('i', ($request->input('page', 1) - 1) * 5);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		$paymenttype = PaymentType::all();
		return view('account.payment-types.create', compact('paymenttype'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$this->validate($request, [
			'name' => 'required|unique:payment_types,name',

		]);
		$input = $request->all();
		$paymenttype = PaymentType::create($input);
		return redirect()->route('paymenttype.index')
			->with('success', Config::get('constants.message.Payment_Type_add_success'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {/*
		$paymenttype = PaymentType::find($id);
		return view('account.payment-types.show', compact('paymenttype'));*/
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		$paymenttype = PaymentType::find($id);
		$payment = PaymentType::orderBy('id', 'DESC')->get();
		return view('account.payment-types.edit', compact('paymenttype', 'payment'));}

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

		$input = $request->all();
		$paymenttype = PaymentType::find($id);
		$paymenttype->update($input);
		return redirect()->route('paymenttype.index')
			->with('success', Config::get('constants.message.Payment_Type_update_success'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		$payment = Payment::where('payment_sub_type', $id)->count();

		$payment_type_children = PaymentType::where('parent_id', $id)->count();

		if ($payment_type_children > 0) {
			return response()->json([
				'title' => 'Not Deleted',
				'type' => 'warning',
				'msg' => 'Payment Type can not be deleted as Payment Type is having children Types So first delete them',
				'btntype' => 'btn-warning',
			]);
		}

		if ($payment > 0) {
			return response()->json([
				'title' => 'Not Deleted',
				'type' => 'warning',
				'msg' => 'Payment Type can not be deleted as Payments added for this type',
				'btntype' => 'btn-warning',
			]);
		}

		PaymentType::find($id)->delete();
		return response()->json([
			'title' => 'Deleted',
			'type' => 'success',
			'msg' => 'Selected Payment Type has been deleted',
			'btntype' => 'btn-success',
		]);
		$return_data = array();

		$return_data['response'] = 'success';
		echo json_encode($return_data);exit;
		return redirect()->route('paymenttype.index')
			->with('success', Config::get('constants.message.Payment_Type_delete_success'));

	}
	 






	 public function paymentresponse(Request $request)
	 {
	 	$columns = array( 
                        0 =>'id',
                        1 =>'name',
                        2 =>'parent_id',
                        3 =>'created_at',
                    	4 =>'action');
		
		
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		$results = PaymentType::orderBy($order,$dir)->distinct();
		$totalData = $results->count();
		$totalFiltered = $totalData;
        if(empty($request->input('search.value')))
        {
				 $resultslist =  PaymentType::offset($start)
                     ->limit($limit)
                     ->orderBy($order,$dir)
                     ->get();
		}else {
        	$search = $request->input('search.value'); 
        	$resultslist =   PaymentType::where('id', 'LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->orderBy($order,$dir)->get();

			$totalFiltered =  PaymentType::where('id', 'LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")->count();
        }
        $data = array();

        if(!empty($resultslist))
        {	$i =0;
            foreach ($resultslist as $resultslist)
            { 
            	if($resultslist->parent_id == 0)
            	{
            		$action='<a href="'.route('paymenttype.show',$resultslist->id).'" style="display:none;"><i class="material-icons md-18">remove_red_eye</i></a>
                		<a class="color-content table-action-style" href="payment-types/'.$resultslist->id.'/edit"><i class="material-icons md-18">edit</i></a>
                		<a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deletePaymentType('.$resultslist->id.',\''.csrf_token().'\');" data-token="\''.csrf_token().'\'"><i class="material-icons md-18">delete</i></a>';
            		$parent='<label></label>';
            		$data[] = array(++$start,$resultslist->name,$parent,$resultslist->created_at,$action);
            	}else{
            		$action='<a href="'.route('paymenttype.show',$resultslist->id).'" style="display:none;"><i class="material-icons md-18">remove_red_eye</i></a>
                		<a class="color-content table-action-style" href="payment-types/'.$resultslist->id.'/edit"><i class="material-icons md-18">edit</i></a>
                		<a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deletePaymentType('.$resultslist->id.',\''.csrf_token().'\');" data-token="\''.csrf_token().'\'"><i class="material-icons md-18">delete</i></a>';
            		//$parent=' <lable id="parentname"  value="'.$resultslist->parent_id.'">'.$resultslist->parent->name.'</lable>';
            		$data[] = array(++$start,$resultslist->name,$resultslist->parent->name,$resultslist->created_at,$action);
 	          	}
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
