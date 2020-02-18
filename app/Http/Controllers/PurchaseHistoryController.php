<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Diamond;
use App\DiamondInventory;
use App\TransactionType;
use App\MetalTransaction;
use App\DiamondTransaction;
use App\Helpers\CommonHelper;
use App\Payment;
use App\PaymentType;
use App\Metal;
use App\User;
use DateTime;
use Spatie\Permission\Models\Role;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Config;

class PurchaseHistoryController extends Controller {
    /**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		DB::enableQueryLog();
		$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;
		//print_r($payment_type);exit;
		$diamond_payments = DB::table('payments AS pay')
							->join('diamond_transactions AS trans', 'trans.transaction_id', '=', 'pay.id')
							->join('users as uservendor', 'uservendor.id', '=', 'trans.vendor_id')
							->join('users as usercreated', 'usercreated.id', '=', 'trans.user_id')
							->join('payment_types AS paytype', 'pay.payment_type', '=', 'paytype.id')->where('pay.payment_type','=',$payment_type)
							->select('trans.id','paytype.name AS pay_type', DB::raw('IF(500<1000, "Diamond", "Gold") AS metal_type'), 'trans.amount_paid_with_gst','trans.invoice_number','trans.transaction_at','pay.transaction_id','usercreated.name as created_by','pay.payment_status','uservendor.name','trans.user_id','pay.account_status')->groupby('pay.transaction_id');
		
		$metal_payments = DB::table('payments AS pay')
						->join('metal_transactions AS trans', 'trans.transaction_id', '=', 'pay.id')
						->join('users as uservendor', 'uservendor.id', '=', 'trans.vendor_id')
						->join('users as usercreated', 'usercreated.id', '=', 'trans.user_id')
						->join('payment_types AS paytype', 'pay.payment_type', '=', 'paytype.id')->where('pay.payment_type','=',$payment_type)
						->union($diamond_payments)
						->select('trans.id','paytype.name AS pay_type', DB::raw('IF(`dml_trans`.`metal_type`=1, "Gold", "Platinum(950)") AS metal_type'), 'trans.amount_paid','trans.invoice_number AS invoice_number','trans.transaction_at','pay.transaction_id','usercreated.name as created_by','pay.payment_status','uservendor.name','trans.user_id','pay.account_status')
						->orderBy('transaction_at', 'desc')						
						->get();
		//print_r($metal_payments);exit;
		$datacount = $metal_payments->count();
		$metal_payments = $metal_payments->take(10);
		//dd(DB::getQueryLog());
		//echo "<pre>";print_r($metal_payments);exit;
		return view('purchasehistory.index', compact('metal_payments','datacount'))->with('i', ($request->input('page', 1) - 1) * 10);		
	}

	public function golddetails(Request $request, $id) {

		$TransactionInfo = MetalTransaction::find($id);
		$Transaction = DB::table('metal_transactions')
					->join('transaction_types', 'transaction_types.id', '=', 'metal_transactions.transaction_type')
					->join('users', 'users.id', '=', 'metal_transactions.vendor_id')
					->join('users AS username', 'username.id', '=', 'metal_transactions.user_id')
					->select('users.name AS vendor_name','username.name AS user','transaction_types.name AS transaction_name','metal_transactions.*')
					->where('metal_transactions.id','=',$id)->get();

		return view('purchasehistory.showgold', compact('Transaction', 'id','metal_type => Metal'))->with('i', ($request->input('page', 1) - 1) * 5);
	}

	public function diamonddetails(Request $request, $id) {

		$TransactionInfo = DiamondTransaction::find($id);
		//print_r($TransactionInfo->transaction_id);exit;
		$Transaction = DB::table('diamond_transactions')
					->join('transaction_types', 'transaction_types.id', '=', 'diamond_transactions.transaction_type')
					->join('users', 'users.id', '=', 'diamond_transactions.vendor_id')
					->join('users AS username', 'username.id', '=', 'diamond_transactions.user_id')
					->select('users.name AS vendor_name','username.name AS user','transaction_types.name AS transaction_name','diamond_transactions.*')
					->where('diamond_transactions.id','=',$id)->get();
		$daimonddata = DiamondTransaction::where('transaction_id',$Transaction[0]->transaction_id)->get();
		
		return view('purchasehistory.show', compact('Transaction', 'id','daimonddata'))->with('i', ($request->input('page', 1) - 1) * 5);
	}
	

	public function filter_history(Request $request) {
		$columns = array(
			0 => 'metal_type',
			1 => 'name',
			2 => 'invoice_number',
			3 => 'amount_paid',
			4 => 'pay_type');

		$params = $request->post();
		$start = $request->input('start');
		$limit = $request->input('length');
		$order =$columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		
		$data = array();
		$params = $request->post();
		$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;
		$diamond_payments = DB::table('payments AS pay')
							->join('diamond_transactions AS trans', 'trans.transaction_id', '=', 'pay.id')
							->join('users as uservendor', 'uservendor.id', '=', 'trans.vendor_id')
							->join('users as usercreated', 'usercreated.id', '=', 'trans.user_id')
							->join('payment_types AS paytype', 'pay.payment_type', '=', 'paytype.id')->where('pay.payment_type','=',$payment_type)
							->select('trans.id','paytype.name AS pay_type', DB::raw('IF(500<1000, "Diamond", "Gold") AS metal_type'), 'trans.amount_paid_with_gst','trans.invoice_number','trans.transaction_at','pay.transaction_id','usercreated.name as created_by','pay.payment_status','uservendor.name','trans.user_id','pay.account_status')->groupby('pay.transaction_id');
		
		$metal_payments = DB::table('payments AS pay')
						->join('metal_transactions AS trans', 'trans.transaction_id', '=', 'pay.id')
						->join('users as uservendor', 'uservendor.id', '=', 'trans.vendor_id')
						->join('users as usercreated', 'usercreated.id', '=', 'trans.user_id')
						->join('payment_types AS paytype', 'pay.payment_type', '=', 'paytype.id')->where('pay.payment_type','=',$payment_type)
						->union($diamond_payments)
						->select('trans.id','paytype.name AS pay_type', DB::raw('IF(`dml_trans`.`metal_type`=1, "Gold", "Platinum(950)") AS metal_type'), 'trans.amount_paid','trans.invoice_number AS invoice_number','trans.transaction_at','pay.transaction_id','usercreated.name as created_by','pay.payment_status','uservendor.name','trans.user_id','pay.account_status');
		
		//->orderBy('transaction_at' ,$dir);
		//$maindata = MetalTransaction::orderBy($order ,$dir);
		if (empty($request->input('search.value'))) {
			$diamond_payments = DB::table('payments AS pay')
							->join('diamond_transactions AS trans', 'trans.transaction_id', '=', 'pay.id')
							->join('users as uservendor', 'uservendor.id', '=', 'trans.vendor_id')
							->join('users as usercreated', 'usercreated.id', '=', 'trans.user_id')
							->join('payment_types AS paytype', 'pay.payment_type', '=', 'paytype.id')->where('pay.payment_type','=',$payment_type)
							->select('trans.id','paytype.name AS pay_type', DB::raw('IF(500<1000, "Diamond", "Gold") AS metal_type'), 'trans.amount_paid_with_gst','trans.invoice_number','trans.transaction_at','pay.transaction_id','usercreated.name as created_by','pay.payment_status','uservendor.name','trans.user_id','pay.account_status')->groupby('pay.transaction_id');
		
		$metal_payments = DB::table('payments AS pay')
						->join('metal_transactions AS trans', 'trans.transaction_id', '=', 'pay.id')
						->join('users as uservendor', 'uservendor.id', '=', 'trans.vendor_id')
						->join('users as usercreated', 'usercreated.id', '=', 'trans.user_id')
						->join('payment_types AS paytype', 'pay.payment_type', '=', 'paytype.id')->where('pay.payment_type','=',$payment_type)
						->union($diamond_payments)
						->select('trans.id','paytype.name AS pay_type', DB::raw('IF(`dml_trans`.`metal_type`=1, "Gold", "Platinum(950)") AS metal_type'), 'trans.amount_paid','trans.invoice_number AS invoice_number','trans.transaction_at','pay.transaction_id','usercreated.name as created_by','pay.payment_status','uservendor.name','trans.user_id','pay.account_status');
						
		}else{
			$search = $request->input('search.value');
			$diamond_payments = DB::table('payments AS pay')
							->join('diamond_transactions AS trans', 'trans.transaction_id', '=', 'pay.id')
							->join('users as uservendor', 'uservendor.id', '=', 'trans.vendor_id')
							->join('users as usercreated', 'usercreated.id', '=', 'trans.user_id')
							->join('payment_types AS paytype', 'pay.payment_type', '=', 'paytype.id')->where('pay.payment_type','=',$payment_type)
							->select('trans.id','paytype.name AS pay_type', DB::raw('IF(500<1000, "Diamond", "Gold") AS metal_type'), 'trans.amount_paid_with_gst','trans.invoice_number','trans.transaction_at','pay.transaction_id','usercreated.name as created_by','pay.payment_status','uservendor.name','trans.user_id','pay.account_status')->groupby('pay.transaction_id');

			$diamond_payments->havingRaw('metal_type LIKE "%' . $search . '%" OR dml_trans.amount_paid_with_gst LIKE "%' . $search . '%" OR dml_trans.invoice_number LIKE "%' . $search . '%" OR transaction_at LIKE "%' . $search . '%" OR payment_status LIKE "%' . $search . '%" OR dml_uservendor.name LIKE "%' . $search . '%" OR dml_usercreated.name LIKE "%' . $search . '%"');
		
			$metal_payments = DB::table('payments AS pay')
						->join('metal_transactions AS trans', 'trans.transaction_id', '=', 'pay.id')
						->join('users as uservendor', 'uservendor.id', '=', 'trans.vendor_id')
						->join('users as usercreated', 'usercreated.id', '=', 'trans.user_id')
						->join('payment_types AS paytype', 'pay.payment_type', '=', 'paytype.id')->where('pay.payment_type','=',$payment_type)
						->union($diamond_payments)
						->select('trans.id','paytype.name AS pay_type', DB::raw('IF(`dml_trans`.`metal_type`=1, "Gold", "Platinum(950)") AS metal_type'), 'trans.amount_paid','trans.invoice_number AS invoice_number','trans.transaction_at','pay.transaction_id','usercreated.name as created_by','pay.payment_status','uservendor.name','trans.user_id','pay.account_status');
			
			$metal_payments->havingRaw('metal_type LIKE "%' . $search . '%" OR amount_paid LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR transaction_at LIKE "%' . $search . '%" OR payment_status LIKE "%' . $search . '%" OR dml_uservendor.name LIKE "%' . $search . '%" OR dml_usercreated.name  LIKE "%' . $search . '%"');
						
		}
		
		$datacount = $metal_payments->count();
		$datacoll = $metal_payments->offset($start)->limit($limit)->orderBy($order, $dir)->get();
		//echo $datacoll;exit;
		$datacollection = $datacoll;

		
		//echo "<pre>";print_r($datacollection->toArray());exit;
		if (count($datacollection) > 0) {
			foreach ($datacollection as $key => $purchaseHis) {
				//echo "<pre>";print_r($purchaseHis);exit;
				$mtype = $purchaseHis->metal_type;
				$vname = $purchaseHis->name;
				$invoiceNo = $purchaseHis->invoice_number;
				$amtpaid = $purchaseHis->amount_paid;
				$created_by = $purchaseHis->created_by;
				$ptype = "<label class='badge badge-success'>".$purchaseHis->pay_type."</label>";
				$disabledCancelClass = '';
				$disabledClass = '';
				if (Auth::user()->id != $purchaseHis->user_id ) {
					$disabledCancelClass = 'disabled" href="#" ';
				}
				if($purchaseHis->account_status == 1){
					$disabledClass = 'disabled" href="#" '; 
				}

					if($purchaseHis->metal_type == "Diamond"){
				
					$action = '<a class="color-content table-action-style" href="'. route('purchasehistory.diamonddetails',[$purchaseHis->id]) .'"><i class="material-icons md-18">remove_red_eye</i></a> <a class="color-content table-action-style  '.$disabledCancelClass.''.$disabledClass.'" href="'. action('PurchaseHistoryController@editdiamond',$purchaseHis->id).'" title="Edit"><i class="material-icons md-18">edit</i></a>';
				}else{
						$action =	'<a class="color-content table-action-style" href="'. route('purchasehistory.metaldetails',[$purchaseHis->id]) .'"><i class="material-icons md-18">remove_red_eye</i></a> <a class="color-content table-action-style  '.$disabledCancelClass.''.$disabledClass.'" href="'. action('PurchaseHistoryController@editgold',$purchaseHis->id) .'" title="Edit"><i class="material-icons md-18">edit</i></a>';
				}
					
					
				
				
				$data[] = array($mtype, $vname, $invoiceNo, $amtpaid, $ptype,$created_by ,$action);
			}
			
		} else {
			$data[] = array('', '', '', '', '', '');
		}
		$json_data = array(
			"query" => $start,
			"draw" => intval($request->input('draw')),
			"recordsTotal" => intval($datacount),
			"recordsFiltered" => intval($datacount),
			"data" => $data,
		);
		echo json_encode($json_data);
		//echo json_encode($data);exit;

	}

	 public function editgold($id){
	 	
	 	$transaction = MetalTransaction::where('id', $id)->first();
		$transactionTypes = TransactionType::pluck('name', 'id'); // where('id', '>', 1)->
		$metals = Metal::select();
		$role =Auth::user()->roles->first()->name;
		 return view('purchasehistory.editgold', compact('transaction', 'transactionTypes', 'metals','role'));
	 }

	  public function editdiamond($id){

	  	$TransactionInfo = DiamondTransaction::find($id);
		//print_r($TransactionInfo->transaction_id);exit;
		$Transaction = DB::table('diamond_transactions')
					->join('transaction_types', 'transaction_types.id', '=', 'diamond_transactions.transaction_type')
					->join('users', 'users.id', '=', 'diamond_transactions.vendor_id')
					->join('users AS username', 'username.id', '=', 'diamond_transactions.user_id')
					->select('users.name AS vendor_name','username.name AS user','transaction_types.name AS transaction_name','diamond_transactions.*')
					->where('diamond_transactions.id','=',$id)->get();
		$daimonddata = DiamondTransaction::where('transaction_id',$Transaction[0]->transaction_id)->get();
		$data['stone_shape'] = DB::select(DB::raw("SELECT `main_table`.*, `eav_op_shape`.*, `eav_attr_stoneshape`.`value` AS `stone_shape` FROM `grp_stone_manage` AS `main_table` LEFT JOIN `eav_attribute_option` AS `eav_op_shape` ON eav_op_shape.option_id = main_table.stone_shape LEFT JOIN `eav_attribute_option_value` AS `eav_attr_stoneshape` ON eav_attr_stoneshape.option_id=main_table.stone_shape AND eav_op_shape.attribute_id=141 GROUP BY `main_table`.`stone_shape`"));
		$data['stone_clarity'] = DB::select(DB::raw("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0"));
		$data['transactionTypes'] = TransactionType::pluck('name', 'id'); // where('id', '>', 1)->
		$data['vendor_name'] = User::join('diamond_transactions', 'diamond_transactions.vendor_id', '=', 'users.id')->select('users.name')->limit(1)->get();
	 	
		//print_r($daimonddata);exit;
		return view('purchasehistory.editdiamond', compact('daimonddata', 'Transaction','id','data'));
	 }


	 public function updategold(Request $request,$id){
	 	//print_r($request->all());exit;
	 	$transaction = MetalTransaction::find($id);
		$prevData = MetalTransaction::find($id);
		$user_id = Auth::user()->id;
		$this->validate($request, [
			'vendor_name' => 'required',
			'metal_weight' => 'required',
			'measurement' => 'required',
			'transaction_type' => 'required',
			'metal_rate' => 'required',
			'purchased_at' => 'required',
		]);

		$role =Auth::user()->roles->first()->name;
		if ($role == "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
			
			$filename = $att_file->getClientOriginalName();
			
			if (!$filename) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		}else if($role !== "Super Admin" && $request->has('purchased_invoice')){
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
			
			$filename = $att_file->getClientOriginalName();
			
			if (!$filename) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		}else{
			$filename = "";
		}

		if ($request->purchased_invoice == "") {
			$filename =  $request->oldinvoice;
		}else{
			$filename = $filename;
			
		}

			$this->validate($request, [
				//'metal_type' => 'required',
				'metal_weight' => 'required',
				'measurement' => 'required',
				//'transaction_type' => 'required',
				'metal_rate' => 'required',
				//'purchased_invoice' => 'file|mimes:jpeg,jpg,png,pdf',
				'purchased_at' => 'required',
			]);
			$due_date = null;
			$amount_paid = ($request->input('metal_rate') * $request->input('metal_weight'));
			$advance_payment = $request->input('advance_payment');
			
				if(!$advance_payment == 1){
					$due_date = $request->input('due_date');
				}	

			$transaction->update(['metal_weight' => $request->input('metal_weight'), 'measurement' => $request->input('measurement'), 'purchased_at' => $request->input('purchased_at'), 'amount_paid' => $amount_paid, 'metal_rate' => $request->input('metal_rate'), 'purchased_invoice' => $filename, 'po_number' => $request->input('po_number'), 'comment' => $request->input('comment'),'advance_payment' => $request->input('advance_payment'),'gold_type' => $request->input('gold_type'),'invoice_number' => $request->input('invoice_number'),'due_date' => $due_date ,'vendor_id'=>$request->input('vendorID') ]);

			$requestData = $request->input();
			$requestData['metal_type'] = $transaction->metal_type;

			switch ($request->input('transaction_type')){
				case 1:
					$this->update($requestData, $prevData);
					break;

				default:
					$this->update($requestData, $prevData);
					break;
			}


		$role =Auth::user()->roles->first()->name;
		
		if($role == "Super Admin"  && !empty($transaction->transaction_id) || $filename !== "" ){
			
			$this->updatepayment($transaction, $filename, $transaction->transaction_id, $amount_paid,$advance_payment);
			
		}
		if($role !== "Super Admin"  && $filename !== ""){
			
			$this->updatepayment($transaction, $filename,$transaction->transaction_id, $amount_paid,$advance_payment);
		
		}	
		$message = Config::get('constants.message.purchase_history_update');
	
		return redirect()->route('purchasehistory.index')
			->with('success',$message);

	}	
	public function update($requestData, $prevData) {

		$metals = DB::table('metals')->where('metal_type', $requestData['metal_type'])->get();
		$metalsCount = count($metals);
		$metalTransactions = DB::table("metal_transactions")->where("metal_type", $requestData['metal_type'])->where("transaction_type", 1)->where("status", 1)->get();

		$metals = DB::table("metal_transactions")->where("metal_type", $requestData['metal_type'])->where("issue_voucher_no","!=",0)->where("status", 1)->get();
		
		$metal_weight_notissue	 = 0;
		$metal_issue = 0;
		$total_amount_paid = 0;
		foreach ($metals as $metalTranKey => $metalTran) {
			//var_dump($metalTran->metal_weight);
			if ($metalTran->measurement == 'mm') {
				$mweight = (float) $metalTran->metal_weight;
				$mweight = $mweight / 1000;
				$metal_weights += $mweight;
			} elseif ($metalTran->measurement == 'kg') {
				$mweight = (float) $metalTran->metal_weight;
				$mweight = $mweight * 1000;
				$metal_weights += $mweight;
			} else {
				$metal_issue += (float) $metalTran->metal_weight;
			}
			
		}
		foreach ($metalTransactions as $metalTranKey => $metalTran) {
			//var_dump($metalTran->metal_weight);
			if ($metalTran->measurement == 'mm') {
				$mweight = (float) $metalTran->metal_weight;
				$mweight = $mweight / 1000;
				$metal_weights += $mweight;
			} elseif ($metalTran->measurement == 'kg') {
				$mweight = (float) $metalTran->metal_weight;
				$mweight = $mweight * 1000;
				$metal_weights += $mweight;
			} else {
				$metal_weight_notissue += (float) $metalTran->metal_weight;
			}
			$total_amount_paid += (float) $metalTran->amount_paid;
		}
		$metal_weights = $metal_weight_notissue - $metal_issue;
		//var_dump($metal_weights);
		//var_dump($total_amount_paid);
		$avg_rate = round($total_amount_paid / ($metal_weights), 2);
		//var_dump($avg_rate);exit;

		if ($metalsCount > 0) {
			Metal::where('metal_type', $requestData['metal_type'])->update(['total_metal_weight' => $metal_weights, 'avg_rate' => $avg_rate]);

		} else {
			$Metal = Metal::create(['metal_type' => $requestData['metal_type'], 'total_metal_weight' => $metal_weights, 'avg_rate' => $avg_rate]);
		}

		return true;
	}
	public function updatepayment($requestData, $filename, $insertedIDTransaction, $payment_amt, $advance_payment) {
//print_r($requestData);exit;
		$payment_type_coll = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first();
		//$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;

		if ($payment_type_coll) {
			$payment_type = $payment_type_coll->id;
		} else {
			return false;
		}

		$payment_sub_type_coll = PaymentType::select('id')->where('name', '=', 'Purchase')->first();
		if ($payment_sub_type_coll) {
			$payment_sub_type = $payment_sub_type_coll->id;
		} else {
			return false;
		}
		//$payment_sub_type = PaymentType::select('id')->where('name', '=', 'Purchase')->first()->id;

		if ($advance_payment == 1) {
			$account_status = 1;
		} else {
			$account_status = 0;
		}
		if(!empty($requestData['vendorId']))
		{
			$vendor_id = $requestData['vendorId'];
			$name = $requestData['vendor_name'];
		}else{
			$vendor_id = $requestData->vendor_id;
			$name = Auth::User()->select('name')->where('id',$requestData->vendor_id)->value('name');
		}
		 Payment::where('id',$insertedIDTransaction)->update([
			'customer_id' => $vendor_id,
			'customer_name' => $name,
			'invoice_number' => $requestData['invoice_number'],
			'invoice_attachment' => $filename,
			'invoice_amount' => $payment_amt,
			'due_date' => $requestData['due_date'],
			'account_status' => $account_status,
			'payment_status' => '0',
			'payment_form' => 'Outgoing',
			'payment_type' => $payment_type,
			'payment_sub_type' => $payment_sub_type,
			'customer_type' => "System",
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			'created_by' => Auth::User()->id,
			'remarks' => "Purchased from Gold Inventory"
		]);
	}
	public function updatediamond(Request $request,$id){
	
		$input = $request->all();
	 	$name = Config::get('constants.enum.transaction_types.purchase');
		$trans_id = TransactionType::select('id')->where('name', $name)->value('id');

			$role =Auth::user()->roles->first()->name;
		if ($role == "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
				
			}
				$filename = $att_file->getClientOriginalName();
			
			if (!$filename) {
				
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
			
		}else if($role !== "Super Admin" && $request->has('purchased_invoice')){
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
				$filename = $att_file->getClientOriginalName();
			
			if (!$filename) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		}else{
			$filename = "";
		}
		
	
		$oldInventoryId =array();
	
			$transaction_id = $request->transaction_id;
			//print_r($transaction_id);exit;
			$transactionId = DiamondTransaction::where('transaction_id',$transaction_id)->pluck('id')->toArray();
			//print_r($transactionId);exit;
			//$diamond_transactions = DiamondTransaction::where('transaction_id',$transaction_id)->get();

			$stone_shape = $request->stone_shape;
			$diamond_weight = $request->diamond_weight;
			$diamond_quality = $request->diamond_quality;
			$sieve_size = $request->sieve_size;
			$rate = $request->rate;
			$mm_size = $request->mm_size;
			$counter = count($stone_shape);
			$error_messages = array();
			//var_dump($rate);
			$success_message = false;
			//$transactionId = $this->getIdFromTransaction($voucher_no);
			$sortform = $this->getSortNameofShape();
			$sortformquality = $this->getSortNameofQuality();
			$transactionDataArr = array();
			$inventoryDataArr = array();
			$oldInventoryId = $this->getOldRecordFromInventory($transaction_id);

				
			for ($i = 0; $i < $counter; $i++) {

				$inventoryId = $this->checkStoneCombination($stone_shape[$i], $diamond_quality[$i], $mm_size[$i], $sieve_size[
					$i]);


			//$avg_rate =
				if (!empty($inventoryId)) {
					$transData = $this->getDiamondTransDataFromTransactionId($transactionId);

					//var_dump($transaction_id);exit;
					//var_dump($i);

					$trans_diamond_weight = $transData[0];

					if ($diamond_weight[$i] > $trans_diamond_weight) {
						$isAvailable = $this->checkStoneIsAvailable($inventoryId, $diamond_weight[$i]);
					} else {
						$isAvailable = true;
					}

					if ($isAvailable) {

						$abbreviationsort = $sortform[$stone_shape[$i]];
						$abbreviationsortquality = $sortformquality[$diamond_quality[$i]];

						if (!empty($mm_size[$i])) {
							$mmsizeintval = filter_var(round($mm_size[$i], 2), FILTER_SANITIZE_NUMBER_INT);
							$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $mmsizeintval;
						} else {
							$sievesizeintval = filter_var(round($sieve_size[$i], 2), FILTER_SANITIZE_NUMBER_INT);
							$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $sievesizeintval;
						}

						$getArr = array('stone_shape' => $stone_shape[$i], 'diamond_quality' => $diamond_quality[$i], 'diamond_weight' => $diamond_weight[$i], 'sieve_size' => $sieve_size[$i], 'mm_size' => $mm_size[$i], 'packet_id' => $packetID, 'rate' => $rate[$i],'vendor_id'=>$request->vendorID,'purchased_invoice'=>$filename);
						//print_r($getArr);
						$inventoryDataArr[$i]['inventoryId'] = $inventoryId;
						$inventoryDataArr[$i]['oldInventoryId'] = $oldInventoryId[$i];
						$inventoryDataArr[$i]['transaction_id'] = $transactionId[$i];
						$inventoryDataArr[$i]['vendor_id'] = $request->vendorID;
						$inventoryDataArr[$i]['purchased_invoice']=$filename;
						$inventoryDataArr[$i]['combinationData'] = $getArr;
						
						$success_message = true;
					} else {
						$error_messages[] = Config::get('constants.message.Diamond_weight_more');
						$success_message = false;
					}
				} else {
					$error_messages[] = Config::get('constants.message.Diamond_not_exists');
					$success_message = false;
				}
			}
			if ($success_message && empty($error_messages)) {

				$newWeightArr = $this->updateInventoryAndTransaction($inventoryDataArr);

				
				
		
				$updateacount = $this->updatetoaccount($request);
				//print_r($updateacount);exit;
				$msg = config('constants.message.purchase_history_update');
				//print_r($msg);exit;
				return redirect('purchase-history')->with('success',$msg);

			} else {
				$error_messages = Config::get('constants.message.Diamond_weight_more');
				return redirect('purchase-history')->with('errors',$error_messages);
				//return redirect('diamond/edit_issue_voucher?' . $voucher_no)->with('error', $message);
			}
		
		
	}
	public function checkStoneIsAvailable($inventoryId, $diamond_weight) {
		$diamondOldWgt = DiamondInventory::where('id', $inventoryId)->pluck('total_diamond_weight')->first();
		if ($diamondOldWgt > $diamond_weight) {
			return true;
		} else if ($diamondOldWgt == $diamond_weight) {
			return true;
		} else {
			return false;
		}
	}
	public function updateInventoryAndTransaction($inventoryDataArr) {
		
		$updated = false;
		$inventoryArr = array();
		foreach ($inventoryDataArr as $key => $inventoryData) {

			$inventoryId = $inventoryData['inventoryId'];
			$oldInventoryId = $inventoryData['oldInventoryId'];
			$transactionId = $inventoryData['transaction_id'];
			$diamond_weight = (float) $inventoryData['combinationData']['diamond_weight'];
			$transactionWt = 0;
			$transactionWt = (float) DiamondTransaction::where('id', $transactionId)->pluck('diamond_weight')->first();
			$diamondCurWgt = 0;
			$diamondCurWgt = DiamondInventory::where('id', $inventoryId)->pluck('total_diamond_weight')->first();
			$test = DiamondInventory::where('id', $inventoryId)->pluck('total_diamond_weight')->first();
		/*	var_dump($transactionId);
			var_dump($diamond_weight);
			var_dump($transactionWt);
			var_dump($diamondCurWgt);exit;*/
			//print_r($inventoryData);exit;
			if ($oldInventoryId != $inventoryId) {
				sleep(2);
				$diamondOldInveWgt = DiamondInventory::where('id', $oldInventoryId)->pluck('total_diamond_weight')->first();

				$diamondNewInveWgt = DiamondInventory::where('id', $inventoryId)->pluck('total_diamond_weight')->first();
				$newTotalDiamondWeight = $diamondNewInveWgt - $transactionWt;
				$oldTotalDiamondWeight = $diamondOldInveWgt + $diamond_weight;
				$oldupdated = DiamondInventory::where('id', $oldInventoryId)->update(['total_diamond_weight' => $oldTotalDiamondWeight]);
				$updated = DiamondInventory::where('id', $inventoryId)->update(['total_diamond_weight' => $newTotalDiamondWeight]);


			} else if ($transactionWt != $diamond_weight) {
				$differenceWeight = 0;
				$differenceWeight = (float) $transactionWt - (float) $diamond_weight;
				sleep(2);
				if ($differenceWeight < 0) {
				
					$abs_differenceWeight = (float) abs($differenceWeight);
					$newTotalDiamondWeight = $diamondCurWgt + $abs_differenceWeight;
					$updated = DiamondInventory::where('id', $inventoryId)->update(['total_diamond_weight' => $newTotalDiamondWeight]);

				} else {
					
					$abs_differenceWeight = (float) abs($differenceWeight);
					$newTotalDiamondWeight = $diamondCurWgt - $abs_differenceWeight;
					$updated = DiamondInventory::where('id', $inventoryId)->update(['total_diamond_weight' => $newTotalDiamondWeight]);
				}
				
			}

	
			$updateOrder = DiamondTransaction::find($transactionId)->update($inventoryData['combinationData']);
			$rate = array();
				$weight = array();
				$total = array();
				
			$diamond_transactions = DiamondTransaction::where('packet_id',$inventoryData['combinationData']['packet_id'])->where('transaction_type',1)->get();
			//print_r($diamond_transactions);exit;
				foreach ($diamond_transactions as $key => $value) {
					
						$rate[] =$value->rate;
						$weight[] =$value->diamond_weight;
						$total[] = $rate[$key] * $weight[$key];
				}
				
				
					
				
				/*var_dump($total);exit;*/
				$real_total_amount_paid = array_sum($total);
				$total_weight = array_sum($weight);
				$newavg_rate = round($real_total_amount_paid / $total_weight,2);
				DiamondInventory::where('id',$inventoryId)->update(['ave_rate'=>$newavg_rate]);
			
				
				
		}
		
		//print_r(DiamondTransaction::find($transactionId)->update($inventoryData['combinationData']);exit;
		return $updateOrder;
	}
	public function updatetoaccount($requestData){
		
		$stone_shape = $requestData['stone_shape'];
		$counter = count($stone_shape);
		
		if ($requestData->purchased_invoice == "") {
			$filename =  $requestData->oldinvoice;
		}else{
			$filename = $requestData->purchased_invoice;
			
		}
//print_r($requestData->all());exit;
			for ($i = 0; $i < $counter; $i++) {
				$total_amount_paid = 0;
				$total_amount_paid += $requestData['amount_paid_with_gst'][$i];
				
			}
		//print_r($total_amount_paid);exit;
		$AccountlastId = Payment::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
		$transaction_id = (int) $AccountlastId->transaction_id;
		$transaction_id++;
		$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;
		$payment_sub_type = PaymentType::select('id')->where('name', '=', 'Purchase')->first()->id;
		
		$user_id = Auth::User()->id;
		 Payment::where('id',$requestData->transaction_id)->update([
			'customer_id' => $requestData->vendorID,
			'customer_name' => $requestData->vendor_name,
			'invoice_number' => (isset($requestData->invoice_number) ? $requestData->invoice_number : 0),
			'invoice_attachment' =>$filename,
			'invoice_amount' => $total_amount_paid,
			'due_date' => (isset($requestData->due_date) ? $requestData->due_date : 0),
			'account_status' => '0',
			'payment_status' => '0',
			'payment_form' => (isset($requestData->payment_form) ? $requestData->payment_form : 'Outgoing'),
			'payment_type' => $payment_type,
			'payment_sub_type' => $payment_sub_type,
			'customer_type' => "System",
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			'created_by' => $user_id,
			'remarks' => "Purchased from Diamond inventory"]);
		 return true;
	}

	public function autoComplete(Request $request) {
		$querysData = $request->get('term', '');

		$vendors = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'Vendor');
		});
		if (!empty($querysData)) {
			$vendors = $vendors->where('name', 'like', '%' . $querysData . '%');
		}
		$vendors = $vendors->orderBy('id', 'DESC')->get();
		$data = array();
		foreach ($vendors as $vendor) {
			$data[] = array('value' => $vendor->name, 'id' => $vendor->id);
		}

		if (count($data)) {
			return $data;
		} else {
			return ['value' => 'No Result Found', 'id' => ''];
		}

	}
	public function getDiamondTransDataFromTransactionId($trans_id) {
		$tranColls = DiamondTransaction::where('id', $trans_id)->select('diamond_weight')->pluck('diamond_weight')->toArray();
		//print_r($tranColls);exit;
		return $tranColls;
	}
	public function checkStoneCombination($stone_shape, $diamond_quality, $mm_size, $sieve_size) {
		if ($mm_size != "") {

			$matchTheseMM = [
				'stone_quality' => $diamond_quality,
				'stone_shape' => $stone_shape,
				'mm_size' => $mm_size,
			];

			$diamondMaster = DiamondInventory::where($matchTheseMM);
			$diamondMasterCount = $diamondMaster->count();
			$diamondMasterRecord = $diamondMaster->first();

			if ($diamondMasterCount > 0) {
				$id = $diamondMasterRecord->id;
				return $id;
			}
		} else {

			$matchTheseSieve = [
				'stone_quality' => $diamond_quality,
				'stone_shape' => $stone_shape,
				'sieve_size' => $sieve_size,
			];

			$diamondMaster = DiamondInventory::where($matchTheseSieve);
			$diamondMasterCount = $diamondMaster->count();
			$diamondMasterRecord = $diamondMaster->first();

			if ($diamondMasterCount > 0) {
				$id = $diamondMasterRecord->id;
				return $id;
			}
		}
	}
	public function getOldRecordFromInventory($transaction_id) {
		$tranColls = DiamondTransaction::where('transaction_id', $transaction_id)->get();
		foreach ($tranColls as $key => $tranColl) {
			$prev_stone_shape = $tranColl->stone_shape;
			$prev_diamond_weight = $tranColl->diamond_weight;
			$prev_diamond_quality = $tranColl->diamond_quality;
			$prev_sieve_size = $tranColl->sieve_size;
			$prev_mm_size = $tranColl->mm_size;
			$oldInventoryId[] = $this->checkStoneCombination($prev_stone_shape, $prev_diamond_quality, $prev_mm_size, $prev_sieve_size);
		}
		return $oldInventoryId;
	}
	public function getSortNameofShape() {
		$sortform = array('ROUND' => 'RD', 'MARQUISE' => 'MQ', 'PEAR' => 'PE',
			'PRINCESS' => 'PRI', 'EMERALD' => 'EMD',
			'OVAL' => 'OV', 'Cushion' => 'CUS', 'ASSCHER' => 'ASS',
			'RADIANT' => 'RAD', 'HEART' => 'HRT', 'TRILLION' => 'TRIN',
			'BAGUETTE' => 'BAG', 'TRIANGULAR' => 'TRI', 'SQUARE' => 'SQR',
			'TAPER' => 'TAP', 'TAPER BAGUETTE' => 'TAB',
		);
		return $sortform;
	}
	public function getSortNameofQuality() {
		$sortformquality = array('SI-IJ' => 'SIIJ', 'VS-IJ' => 'VSIJ', 'VS-HI' => 'VSHI',
			'VVS-GH' => 'VVSGH', 'VS-GH' => 'VSGH', 'VVS-EF' => 'VVSEF', 'SI2-I1-HI' => 'SI2I1HI', 'SI2-I1-IJ' => 'SI2I1IJ',
			'VVS-FG' => 'VVSFG', 'VVS-IJ' => 'VVSIJ', 'VVS-VS-IJ' => 'VVSVSIJ', 'SI-HI' => 'SIHI', 'I-IJ' => 'IIJ', 'VS-EF' => 'VSEF',
			'I-GH' => 'IGH', 'I-HI' => 'IHI', 'VS-SI-HI' => 'VSSIHI', 'VS-VVS-EF' => 'VSVVSEF', 'VS-FG' => 'VSFG', 'VS-VVS-HI' => 'VSVVSHI',
			'VVS-VS-GH' => 'VVSVSGH', 'VS-VVS-FG' => 'VSVVSFG', 'VS-VVS-I-GH' => 'VSVVSIGH', 'SI-GH' => 'SIGH', 'SI-JK' => 'SIJK', 'VVS-VS-I-GH' => 'VVSVSIGH',
			'SI-FG' => 'SIFG', 'VVS-VS-FG' => 'VVSVSFG', 'SI2-I1-GH' => 'SI2I1GH', 'SI-GH-I' => 'SIGHI', 'VS-SI-I-GH' => 'VSSIIGH', 'VS-SI-GH' => 'VSSIGH',
			'VS-SI-IJ' => 'VSSIIJ', 'VS-GH-I' => 'VS-GH-I', 'VVS-VS-JK' => 'VVSVSJK', 'VVS-VS-FGH' => 'VVSVSFGH', 'VVS-FGH' => 'VVSFGH', 'H-I-JSI' => 'HIJSI',
			'HI-J-SI' => 'HIJSI', 'VS-SI-FG' => 'VSSIFG', 'FG-VS-SI' => 'FGVSSI', 'VS-JK' => 'VSJK', 'JK-VS' => 'JKVS', 'GH-I2' => 'GHI2',
			'VVS-HI' => 'VVSHI', 'GH-SI-I1' => 'GHSII1', 'GH-I-VVS-VS' => 'GHIVVSVS', 'G-H-ISI' => 'GHISI', 'GH-SI-2' => 'GHSI2', 'SI-HI-IJ' => 'SIHIIJ',
			'VS-SI-HI-J' => 'VSSIHIJ', 'I1-GH' => 'I1GH');
		return $sortformquality;
	}
	

}