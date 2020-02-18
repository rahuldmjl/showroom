<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\DiamondHelper;
use App\Http\Controllers\Controller;
use App\Metal;
use App\MetalTransaction;
use App\Payment;
use App\PaymentType;
use App\Setting;
use App\TransactionType;
use App\User;
use Auth;
use Config;
use DB;
use Illuminate\Http\Request;
use PDF;

class MetalController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	function __construct() {
		$this->middleware('permission:metals-list');
		$this->middleware('permission:metals-create', ['only' => ['create', 'store']]);
		$this->middleware('permission:metals-edit', ['only' => ['edit', 'update']]);
		$this->middleware('permission:metals-delete', ['only' => ['destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {

		$trantype = TransactionType::orderBy('id', 'DESC')->get();

		$metals = DB::table('metals')->select('id', DB::raw('IF (`metal_type`=1 , "Gold24k","Platinum 950") AS Metal_Type'), 'total_metal_weight', 'avg_rate', 'metal_type')->paginate(10);

		$datacount = Metal::orderBy('id', 'DESC')->count();
		//->paginate(5);
		//return view('metals.index', compact('metals'))->with('i', ($request->input('page', 1) - 1) * 5);
		return view('metals.index', compact('metals', 'trantype', 'datacount'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//$transactionTypes = TransactionType::get();
		$transactionTypes = TransactionType::pluck('name', 'id'); // where('id', '>', 1)->
		$metals = Metal::select();

		$role = Auth::user()->roles->first()->name;
		//print_r($role);
		return view('metals.create', compact('metals', 'transactionTypes', 'role'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {

		$transaction_type = DB::table('transaction_types')->select('id')->where('name', '=', 'Purchase')->get();
		$role = Auth::user()->roles->first()->name;
		$this->validate($request, [
			'metal_type' => 'required',
			'vendor_name' => 'required',
			'metal_weight' => 'required',
			'measurement' => 'required',
			'transaction_type' => 'required',
			'metal_rate' => 'required',
			'purchased_date' => 'required',
		]);
		$role = Auth::user()->roles->first()->name;
		if ($role == "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			$exactFilePath = base_path("public" . '/' . config('constants.dir.purchased_invoices')) . $att_file;
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
			$filename = $att_file->getClientOriginalName();

			if (!file_exists($exactFilePath)) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		} else if ($role !== "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$exactFilePath = base_path("public" . '/' . config('constants.dir.purchased_invoices')) . $att_file;
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
			$filename = $att_file->getClientOriginalName();

			if (!file_exists($exactFilePath)) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		} else {
			$filename = "";
		}

		//dd($request->input());exit;

		$user_id = Auth::user()->id;

		$amount_paid = ($request->input('metal_rate') * $request->input('metal_weight'));

		$MetalTransaction = MetalTransaction::create(['metal_type' => $request->input('metal_type'), 'metal_weight' => $request->input('metal_weight'), 'measurement' => $request->input('measurement'), 'purchased_at' => $request->input('purchased_date'), 'transaction_type' => $transaction_type[0]->id, 'invoice_number' => $request->input('invoice_number'), 'due_date' => $request->input('due_date'), 'vendor_id' => $request->input('vendorId'), 'transaction_at' => date('Y-m-d H:i:s'), 'amount_paid' => $amount_paid, 'metal_rate' => $request->input('metal_rate'), 'purchased_invoice' => $filename, 'user_id' => $user_id, 'po_number' => $request->input('po_number'), 'comment' => $request->input('comment'), 'advance_payment' => $request->input('advance_payment'), 'gold_type' => $request->input('gold_type')]);

//		echo "<pre>"; print_r($MetalTransaction->get());exit;
		$advance_payment = $request->input('advance_payment');
		$requestData = $request->input();

		switch ($request->input('transaction_type')) {
		case 1:
			$this->addgold($requestData);
			break;

		default:
			$this->addgold($requestData);
			break;
		}

		if ($role == "Super Admin" && $filename !== "") {
			$this->addPayment($requestData, $filename, $MetalTransaction->id, $amount_paid, $advance_payment);
		}

		if ($role !== "Super Admin" && $filename !== "") {
			$this->addPayment($requestData, $filename, $MetalTransaction->id, $amount_paid, $advance_payment);
		}

		return redirect()->route('metals.index')
			->with('success', 'Metal transaction added successfully');
	}

	public function show(Request $request, $id) {
		//print_r($request['chkval']);
		//print_r($id);exit;
		/*$metalTransactions = MetalTransaction::where('metal_type', $id)->orderBy('id', 'DESC')->paginate(5);
		return view('metals.show', compact('metalTransactions', 'id'))->with('i', ($request->input('page', 1) - 1) * 5);*/
		$trantype = TransactionType::orderBy('id', 'DESC')->get();

		$metalTransactions = MetalTransaction::where('metal_type', $id)->orderBy('created_at', 'DESC')->paginate(10);
		$tid = MetalTransaction::select('user_id')->orderBy('id', 'DESC')->distinct()->get();
		$weightmin = MetalTransaction::min('metal_weight');
		$weightmax = MetalTransaction::max('metal_weight');

		$amount_paidmin = MetalTransaction::min('amount_paid');
		$amount_paidmax = MetalTransaction::max('amount_paid');

		$datacount = MetalTransaction::where('metal_type', $id)->orderBy('id', 'DESC')->count();

		$name = array();

		foreach ($tid as $value) {
			$name[] = Auth::User()->select('name', 'id')->where('id', $value->user_id)->get();

		}

		return view('metals.show', compact('metalTransactions', 'id', 'trantype', 'name', 'weightmin', 'weightmax', 'amount_paidmin', 'amount_paidmax', 'datacount'));
	}

	public function addgold($requestData) {
		$metals = Metal::where('metal_type', $requestData['metal_type'])->first();
		$metalsCount = count($metals);
		$in_inventory_weight = 0;
		if ($metalsCount > 0) {
			$in_inventory_weight = (float) $metals->total_metal_weight;
		}

		if ($requestData['metal_weight'] > 0) {
			$added_metal_weight = (float) $requestData['metal_weight'];
		} else {
			$added_metal_weight = 0;
		}
		//$metalTransactions = MetalTransaction::where("metal_type", $requestData['metal_type'])->where("transaction_type", 1)->where("status", 1)->get();

		/*$metals = MetalTransaction::where("metal_type", $requestData['metal_type'])->where("issue_voucher_no", "!=", 0)->where("status", 1)->get();*/

		/*$metal_weight_notissue = 0;
			$metal_issue = 0;
			$total_amount_paid = 0;
			foreach ($metals as $metalTranKey => $metalTran) {
				//var_dump($metalTran);exit;
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

		*/

		/*foreach ($metalTransactions as $metalTranKey => $metalTran) {
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
		}*/
		$metal_weights = $in_inventory_weight + $added_metal_weight;

		$total_amount_paid_purchase = 0;
		$total_weight = 0;
		$metalamount = MetalTransaction::where("transaction_type", 1)->where('metal_type', $requestData['metal_type'])->get();
		foreach ($metalamount as $metalTranKey => $metalTran) {
			$total_amount_paid_purchase += (float) $metalTran->amount_paid;
			$total_weight += $metalTran->metal_weight;

		}
		/*var_dump($total_amount_paid_purchase);
			var_dump($total_amount_paid);
			var_dump($total_weight);
		*/

		$avg_rate = round($total_amount_paid_purchase / ($total_weight), 2);

		//var_dump($avg_rate);exit;

		if ($metalsCount > 0) {
			Metal::where('metal_type', $requestData['metal_type'])->update(['total_metal_weight' => $metal_weights, 'avg_rate' => $avg_rate]);

		} else {
			$Metal = Metal::create(['metal_type' => $requestData['metal_type'], 'total_metal_weight' => $metal_weights, 'avg_rate' => $avg_rate]);
		}

		return true;
	}

	// ADD To PAYMENT Table
	public function addPayment($requestData, $filename, $insertedIDTransaction, $payment_amt, $advance_payment) {

		$lastTransactionId = Payment::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
		if (!empty($lastTransactionId)) {
			$newTransactionId = ((int) $lastTransactionId->transaction_id) + 1;
		} else {
			$newTransactionId = (int) 100001701;
		}

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

		$data = array(
			'transaction_id' => $newTransactionId,
			'customer_id' => $requestData['vendorId'],
			'customer_name' => $requestData['vendor_name'],
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
			'remarks' => "Purchased from Gold Inventory",
		);

		$role = Auth::user()->roles->first()->name;
		if ($role == "Super Admin" && $filename !== "") {
			$Accountinsert = Payment::create($data);
		}

		if ($role !== "Super Admin" && $filename !== "") {
			$Accountinsert = Payment::create($data);
		}

		$TransactionIdUpd = MetalTransaction::find($insertedIDTransaction);
		$TransactionIdUpd->transaction_id = $Accountinsert->id;
		$TransactionIdUpd->save();

	}

	public function goldissue(Request $request) {
		$metaldata = Metal::select('total_metal_weight', 'avg_rate', 'metal_type')->get();
		$rateplitinum = 0;
		foreach ($metaldata as $value) {
			if ($value->metal_type == 1) {
				$rate24k = $value->avg_rate;
			} else {
				$rateplitinum = $value->avg_rate;
			}

		}

		//print_r($metaldata);exit;
		return view('metals.goldissue', compact('metaldata', 'rate24k', 'rateplitinum'))->render();
	}

	public function goldissuestore(Request $request) {

		if ($request->custom_rate == "") {
			$avg_rate = $request->existing_rate;

		} else {
			$avg_rate = $request->custom_rate;
		}

		$metal_weight = Metal::select('total_metal_weight', 'avg_rate', 'id', 'metal_type')->where('metal_type', $request->metal_type)->get();

		$purchase_transaction_type_data = TransactionType::select('id')->where('name', 'Purchase')->first();
		if (!empty($purchase_transaction_type_data)) {
			$purchase_transaction_type = $purchase_transaction_type_data->id;
		}

		$purchase_total_metal_weight = MetalTransaction::where('metal_type', $request->metal_type)->where('gold_type', $request->gold_type)->where('transaction_type', $purchase_transaction_type)->sum('metal_weight');

		$issue_transaction_type_data = TransactionType::select('id')->where('name', 'Issue')->first();
		if (!empty($issue_transaction_type_data)) {
			$issue_transaction_type = $issue_transaction_type_data->id;
		}
		$issue_total_metal_weight = MetalTransaction::where('metal_type', $request->metal_type)->where('gold_type', $request->gold_type)->where('transaction_type', $issue_transaction_type)->sum('metal_weight');

		//var_dump($purchase_total_metal_weight);
		//var_dump($issue_total_metal_weight);

		if ($issue_total_metal_weight >= 0 && $purchase_total_metal_weight >= 0) {
			$inventory_metal_weight = (float) $purchase_total_metal_weight - (float) $issue_total_metal_weight;
		} else {
			$inventory_metal_weight = 0;
		}
		/*$issue_voucher_no = DiamondHelper::getGoldIssueVoucherNo();
			$voucherdetail = DiamondHelper::isGoldVoucherNoExist($issue_voucher_no);
		*/

		$issue_voucher_no = app('App\Http\Controllers\DiamondController')->generateRandomString();

		$transaction_type = DB::table('transaction_types')->select('id')->where('name', '=', 'Issue')->get();

		$this->validate($request, [
			'metal_type' => 'required',
			'metal_weight' => 'required',
			'po_number' => 'required',
			'gold_type' => 'required',
		]);

		if (empty($metal_weight[0]->metal_type)) {
			$metal_type = 0;
		} else {
			$metal_type = $metal_weight[0]->metal_type;
		}
		if ($request->input('metal_type') != $metal_type) {
			$error = true;
			$message = Config('constants.message.metal_cant_issue');
			return redirect()->route('metals.goldissue')->with('error', $message);
		}
		$count_weight = $request->metal_weight;
		$amount_paid = $count_weight * $avg_rate;
		$user_id = Auth::user()->id;
		$transaction_at = date('Y-m-d H:i:s');
		$MetalTransaction['metal_type'] = $request->input('metal_type');
		$MetalTransaction['metal_weight'] = $request->input('metal_weight');
		$MetalTransaction['measurement'] = $request->input('measurement');
		$MetalTransaction['transaction_type'] = $transaction_type[0]->id;
		$MetalTransaction['user_id'] = $user_id;
		$MetalTransaction['po_number'] = $request->input('po_number');
		$MetalTransaction['vendor_id'] = $request->input('vendorId');
		$MetalTransaction['transaction_at'] = $transaction_at;
		$MetalTransaction['created_by'] = $request->input('created_by');
		$MetalTransaction['comment'] = $request->input('comment');
		$MetalTransaction['amount_paid'] = $amount_paid;
		$MetalTransaction['issue_date'] = $request->input('issue_date');
		$MetalTransaction['gold_type'] = $request->input('gold_type');
		$MetalTransaction['metal_rate'] = $avg_rate;
		$MetalTransaction['issue_voucher_no'] = $issue_voucher_no;
		$MetalTransaction['is_voucher_no_generated'] = "0";

		$mweightCal = true;
		if ($MetalTransaction['metal_weight'] > $inventory_metal_weight) {
			$error = false;
			$message = Config('constants.message.metal_weight');
			return redirect()->route('metals.goldissue')->with('error', $message);

		} else {
			$metal = Metal::where('id', $metal_weight[0]->id);
			$metal->total_metal_weight = $metal_weight[0]->total_metal_weight - $request->input('metal_weight');
			$metal_update_id = $metal->update(['total_metal_weight' => $metal->total_metal_weight]);
			$name = $this->issuevaucher($MetalTransaction, $request);
			$msg = Config('constants.message.issue_transaction');
			$msg .= "<br/>" . ' Click on link to view <a target="_blank" href="' . url('uploads/issuevaucher/' . $name) . '">Issue Voucher</a>';
			$request->session()->flash("success", $msg);
			return redirect('gold-inventory/goldissue');
		}
		/*} else {

			return redirect('gold-inventory/goldissue')->with('error', Config::get('constants.message.settings_error'));

		}*/

	}
	public function issuevaucher($MetalTransaction, $request) {

		$customPaper = array(0, 0, 1024, 1440);
		$data = $MetalTransaction;
		$getname = User::select('name', 'gstin', 'state', 'address')->where('id', $MetalTransaction['vendor_id'])->get();
		$name = $getname[0]->name;
		$gstin = $getname[0]->gstin;
		$address = $getname[0]->address;
		$state = $getname[0]->state;
		$metal_transaction_id = MetalTransaction::create($MetalTransaction);
		$pdf = PDF::loadView('metals.issuevaucher', compact('data', 'name', 'address', 'gstin', 'state'))->setPaper($customPaper, 'A4');
		$path = config('constants.dir.issue_vaucher');
		$name = 'gold_issue_voucher_' . time() . '.pdf';
		$pdf->save($path . $name);
		$Metaldata = MetalTransaction::select('id')->where('issue_date', $MetalTransaction['issue_date'])->get();

		/*$search_voucher = Setting::where('key', config('constants.settings.keys.gold_voucher_series'))->first()->value;
			$new_voucher = (int) $search_voucher + 1;
			$nid = Setting::select('id')->where('key', config('constants.settings.keys.gold_voucher_series'))->get();
			$setting = Setting::find($nid[0]->id);
			$setting->value = $new_voucher;
		*/

		$Metaldata = MetalTransaction::find($metal_transaction_id->id);
		$Metaldata->purchased_invoice = $name;
		$Metaldata->update();

		return $name;
	}
	public function goldresponse(Request $request) {
		$columns = array(
			0 => 'id',
			1 => 'metal_type',
			2 => 'total_metal_weight',
			3 => 'avg_rate',
			4 => 'action'); //DB::raw('IF(`dml_trans`.`metal_type`=1, "Gold", "Platinum(950)") AS metal_type')
		$maindata = DB::table('metals')->select('id', DB::raw('IF (`metal_type`=1 , "Gold24k","Platinum 950") AS Metal_Type'), 'total_metal_weight', 'avg_rate', 'metal_type');

		$totalData = $maindata->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		if (empty($request->input('search.value'))) {
			$resultslist = $maindata->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = $maindata->havingRaw('(metal_type LIKE "%' . $search . '%" OR total_metal_weight LIKE "%' . $search . '%" OR avg_rate LIKE "%' . $search . '%") ')
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();

			$totalFiltered = $resultslist->count();
		}
		$data = array();
		$i = 0;

		if (count($resultslist) > 0) {
			foreach ($resultslist as $gold) {
				$actions = ' ';
				//print_r($gold);exit;

				//$actions = 'sdfjn dfgkj fgk df';
				$actions .= '<a class="color-content table-action-style" title="Download Invoice" href="' . route('metals.transactions', $gold->metal_type) . '"><i class="material-icons md-18">remove_red_eye</i></a> ';

				$data[] = array(++$start, $gold->Metal_Type, $gold->total_metal_weight, ' &#8377; ' . $gold->avg_rate, $actions);
			}
		}
		$json_data = array(
			"draw" => intval($request->input('draw')),
			"recordsTotal" => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data" => $data,
		);
		echo json_encode($json_data);

	}
	public function filter_metal(Request $request) {
		$columns = array(
			0 => 'gold_type',
			1 => 'metal_weight',
			2 => 'amount_paid',
			3 => 'transaction_type',
			4 => 'user_id',
			5 => 'transaction_at',
			6 => 'po_number',
			7 => 'comment');

		$params = $request->post();
		$start = $request->input('start');
		$limit = $request->input('length');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		$data = array();
		$params = $request->post();

		$maindata = MetalTransaction::orderBy($order, $dir);

		if (!empty($request['textfilter'])) {
			$maindata = $maindata->where('transaction_type', $request['textfilter']);
		}

		if (!empty($request['MtypeID'])) {
			$maindata = $maindata->where('metal_type', $request['MtypeID']);
		}

		if (!empty($request['textfilterid'])) {
			$maindata = $maindata->where('user_id', $request['textfilterid']);
		}

		if (!empty($request['amtStart']) && !empty($request['amtEnd'])) {
			$min = $request['amtStart'];
			$max = $request['amtEnd'];
			$maindata = $maindata->whereBetween('amount_paid', [$min, $max]);

		}

		if (!empty($request['weightStart']) && !empty($request['weightEnd'])) {
			$min = $request['weightStart'];
			$max = $request['weightEnd'];
			$maindata = $maindata->whereBetween('metal_weight', [$min, $max]);

		}
		$datacount = $maindata->count();
		$datacoll = $maindata->offset($start)->limit($limit)->orderBy($order, $dir)->get();
		$datacollection = $datacoll;

		$data["draw"] = intval($request->input('draw'));
		$data["recordsTotal"] = $datacount;
		$data["recordsFiltered"] = $datacount;
		$data['deferLoading'] = $datacount;

		if (count($datacollection) > 0) {
			foreach ($datacollection as $key => $gold) {
				$actions = ' ';
				/*print_r($gold);exit;*/

				if ($gold->metal_type == '1') {
					$metaltype = $gold->gold_type;
				} else {
					$metaltype = 'Platinum 950';
				}
				//$actions = 'sdfjn dfgkj fgk df';
				$actions .= '<a class="color-content table-action-style" title="Download Invoice" href="' . route('gold_download_purchase_invoice', $gold->id) . '"><i class="material-icons md-18">file_download</i></a> ';
				if ($gold->transaction_type == 1) {
					$actions .= '<a class="color-content table-action-style" title="Edit" href="' . route('gold_edit_transaction', $gold->id) . '"><i class="material-icons md-18">edit</i></a>';
				}

				$username = Auth::User()->select('name')->where('id', $gold->user_id)->get();
				$transby = $username[0]->name;
				$trantype = TransactionType::select('name')->where('id', $gold->transaction_type)->orderBy('id', 'DESC')->get();
				if ($gold->transaction_type == '1') {
					$badge = 'badge-success';
				} elseif ($gold->transaction_type == '2' || $gold->transaction_type == '3' || $gold->transaction_type == '4' || $gold->transaction_type == '5') {
					$badge = 'badge-danger';
				} elseif ($gold->transaction_type == '6') {
					$badge = 'badge-warning';
				} else {
					$badge = 'badge-info';
				}
				$newtext = wordwrap($gold->comment, 10, "<br />\n");

				$transaction_type = '<td><span class="badge ' . $badge . ' py-1 px-2">' . $trantype[0]->name . '</span></td>';

				$data['data'][] = array($gold->gold_type, $gold->metal_weight . ' GM', CommonHelper::covertToCurrency($gold->amount_paid), $transaction_type, $transby, $gold->transaction_at, $gold->po_number, $newtext, $actions);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;

	}

	public function edit_gold_issue_voucher(Request $request) {

		$metaldata = Metal::select('total_metal_weight', 'avg_rate', 'metal_type')->get();
		$rateplitinum = 0;
		foreach ($metaldata as $value) {
			if ($value->metal_type == 1) {
				$rate24k = $value->avg_rate;
			} else {
				$rateplitinum = $value->avg_rate;
			}

		}

		$voucher_no = $this->getIssueVoucherNoFromRequest($request);
		$datas = MetalTransaction::where('issue_voucher_no', $voucher_no)->first();
		return view('metals/edit-gold-issue-voucher', compact('datas', 'voucher_no', 'metaldata', 'rate24k', 'rateplitinum'));
	}

	public function getIssueVoucherNoFromRequest($request) {
		$getArr = $request->all();
		$setArr = array();
		foreach ($getArr as $getArrKey => $getArrValue) {
			$setArr[] = $getArrKey;
		}
		if (!empty($setArr)) {
			$voucher_no = $setArr[0];
		}
		return $voucher_no;
	}

	public function updategoldissuevoucher(Request $request) {
		$voucher_no = $request->voucher_no;
		$transactionId = MetalTransaction::where('issue_voucher_no', $voucher_no)->select('id')->pluck('id')->first();
		$metal_weight = $request->metal_weight;
		$vendor_id = $request->vendor_id;
		$po_number = $request->po_number;
		$gold_type = $request->gold_type;
		$metal_rate = $request->metal_rate;
		$comment = $request->comment;
		$metal_type = $request->metal_type;
		$updated_by = Auth::User()->id;
		$amount_paid = $metal_weight * $metal_rate;

		$metalData = array('metal_weight' => $metal_weight, 'vendorid' => $vendor_id, 'po_number' => $po_number, 'gold_type' => $gold_type, 'updated_by' => $updated_by, 'metal_rate' => $metal_rate, 'comment' => $comment, 'amount_paid' => $amount_paid);
		MetalTransaction::find($transactionId)->update($metalData);
		$this->updateMetalInventory($metal_type);
		$name = $this->updateGoldIssueVoucherPdf($voucher_no);
		$msg = config('constants.message.issue_voucher_edit_success');
		$msg .= "<br/>" . ' Click on link to view <a target="_blank" href="' . url('uploads/issuevaucher/' . $name) . '">Issue Voucher</a>';
		$request->session()->flash("success", $msg);
		return redirect('metals/edit_gold_issue_voucher?' . $voucher_no);
	}

	public function returnGoldIssue(request $request) {
		$voucher_no = $request->id;
		$data = MetalTransaction::where('issue_voucher_no', $voucher_no)->first();
		$returnHTML = view('metals.returnGoldIssue', ['voucher_no' => $voucher_no, 'data' => $data])->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));
	}
	public function returngoldIssueStore(request $request) {

		$return_weight = $request->return_weight;
		$voucher_no = $request->voucher_no;
		$metalWt = MetalTransaction::where('issue_voucher_no', $voucher_no)->select('metal_weight')->pluck('metal_weight')->first();
		$metalColl = MetalTransaction::where('issue_voucher_no', $voucher_no)->first();
		if ($metalWt >= $return_weight) {
			$newWt = $metalWt - $return_weight;
			MetalTransaction::where('issue_voucher_no', $voucher_no)->update(['metal_weight' => $newWt]);
			$typeName = config('constants.enum.transaction_types.return');
			$transactionColl = TransactionType::where('name', $typeName)->first();
			$transaction_type = $transactionColl['id'];
			$metal_type = $metalColl['metal_type'];
			$metal_weight = $return_weight;
			$measurement = "gm";
			$transaction_at = date('Y-m-d H:i:s');
			$status = 1;
			$user_id = Auth::user()->id;
			$gold_type = $metalColl['gold_type'];
			$vendor_id = $metalColl['vendor_id'];
			$issue_voucher_no = $metalColl['issue_voucher_no'];
			$po_number = $metalColl['po_number'];
			$amount_paid = $metalColl['amount_paid'];
			$invoice_number = $metalColl['invoice_number'];
			$setArr = array('metal_type' => $metal_type, 'metal_weight' => $metal_weight, 'measurement' => $measurement, 'transaction_at' => $transaction_at, 'transaction_type' => $transaction_type, 'status' => $status, 'user_id' => $user_id, 'gold_type' => $gold_type, 'vendor_id' => $vendor_id, 'issue_voucher_no' => $issue_voucher_no, 'po_number' => $po_number, 'amount_paid' => $amount_paid, 'invoice_number' => $invoice_number);
			MetalTransaction::create($setArr);
			$this->updateMetalInventory($metal_type);
			$this->updateGoldIssueVoucherPdf($voucher_no);
			$response['status'] = "true";
			config(['constants.message.returned_success' => $return_weight . config('constants.message.returned_success')]);
			$response['message'] = config('constants.message.returned_success');
		} else {
			$response['status'] = "false";
			config(['constants.message.weight_more' => $return_weight . config('constants.message.weight_more')]);
			$response['message'] = config('constants.message.weight_more');
		}
		echo json_encode($response);exit;
	}

	public function updateMetalInventory($metal_type) {

		$metals = Metal::where('metal_type', $metal_type)->get();
		$metalsCount = count($metals);
		$metalTransactions = MetalTransaction::where("metal_type", $metal_type)->where("transaction_type", 1)->where("status", 1)->get();
		$metals = MetalTransaction::where("metal_type", $metal_type)->where("transaction_type", 2)->where("status", 1)->get();

		$metal_weight_notissue = 0;
		$metal_issue = 0;
		$total_amount_paid_purchase = 0;
		$total_weight = 0;
		$counterOfPurchase = 0;
		foreach ($metals as $metalTranKey => $metalTran) {
			$metal_issue += (float) $metalTran->metal_weight;
		}
		foreach ($metalTransactions as $metalTranKey => $metalTran) {
			$metal_weight_notissue += (float) $metalTran->metal_weight;
			$total_amount_paid_purchase += (float) $metalTran->metal_rate;
			$total_weight += $metalTran->metal_weight;
			$counterOfPurchase++;
		}
		$metal_weights = $metal_weight_notissue - $metal_issue;

		//$avg_rate = round($total_amount_paid_purchase / ($counterOfPurchase), 2);
		if ($metalsCount > 0) {
			Metal::where('metal_type', $metal_type)->update(['total_metal_weight' => $metal_weights]); //, 'avg_rate' => $avg_rate
		}
		return true;
	}

	public function updateGoldIssueVoucherPdf($voucher_no) {
		$metalColl = MetalTransaction::where('issue_voucher_no', $voucher_no)->first();
		$pdfOldFileName = public_path('uploads/issuevaucher/') . $metalColl['purchased_invoice'];
		if (file_exists($pdfOldFileName)) {
			unlink(public_path('uploads/issuevaucher/') . $metalColl['purchased_invoice']);
		}
		$getname = User::select('name', 'gstin', 'state', 'address')->where('id', $metalColl['vendor_id'])->get();
		$name = $getname[0]->name;
		$gstin = $getname[0]->gstin;
		$address = $getname[0]->address;
		$state = $getname[0]->state;
		$data = $metalColl;

		$customPaper = array(0, 0, 1024, 1440); //720 // 1240

		$pdf = PDF::loadView('metals.issuevaucher', compact('data', 'name', 'address', 'gstin', 'state'))->setPaper($customPaper, 'A4');
		$path = config('constants.dir.issue_vaucher');
		$name = 'gold_issue_voucher_' . time() . '.pdf';
		$pdf->save($path . $name);
		MetalTransaction::where('issue_voucher_no', $voucher_no)->update(['purchased_invoice' => $name]);
		return $name;
	}

	public function goldPreview(request $request) {

		if ($request->custom_rate == "") {
			$avg_rate = $request->existing_rate;

		} else {
			$avg_rate = $request->custom_rate;
		}
		$transaction_at = date('Y-m-d H:i:s');
		$count_weight = $request->metal_weight;
		$amount_paid = $count_weight * $avg_rate;
		$transaction_type = DB::table('transaction_types')->select('id')->where('name', '=', 'Issue')->get();
		$MetalTransaction['metal_type'] = $request->input('metal_type');
		$MetalTransaction['metal_weight'] = $request->input('metal_weight');
		$MetalTransaction['measurement'] = $request->input('measurement');
		$MetalTransaction['transaction_type'] = $transaction_type[0]->id;
		$MetalTransaction['user_id'] = Auth::user()->id;
		$MetalTransaction['po_number'] = $request->input('po_number');
		$MetalTransaction['vendor_id'] = $request->input('vendorId');
		$MetalTransaction['transaction_at'] = $transaction_at;
		$MetalTransaction['created_by'] = $request->input('created_by');
		$MetalTransaction['comment'] = $request->input('comment');
		$MetalTransaction['amount_paid'] = $amount_paid;
		$MetalTransaction['issue_date'] = $request->input('issue_date');
		$MetalTransaction['gold_type'] = $request->input('gold_type');
		$MetalTransaction['metal_rate'] = $avg_rate;

		$customPaper = array(0, 0, 1024, 1440);
		$data = $MetalTransaction;
		$getname = User::select('name', 'gstin', 'state', 'address')->where('id', $MetalTransaction['vendor_id'])->get();
		$name = $getname[0]->name;
		$gstin = $getname[0]->gstin;
		$address = $getname[0]->address;
		$state = $getname[0]->state;
		$metal_transaction_id = MetalTransaction::create($MetalTransaction);
		$returnHTML = view('metals.goldPreview')->with(compact('data', 'name', 'address', 'gstin', 'state'))->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));
	}

	public function generateGoldVoucherno(request $request) {
		$currentVoucherNo = DiamondHelper::getGoldIssueVoucherNo();
		$voucherdetail = DiamondHelper::isGoldVoucherNoExist($currentVoucherNo);
		if ($voucherdetail == true) {

			$issue_voucher_no = $request->id;
			if (!empty($issue_voucher_no)) {
				$metalTran = MetalTransaction::where('issue_voucher_no', $issue_voucher_no)->first();
				$metalTran->issue_voucher_no = $currentVoucherNo;
				$metalTran->is_voucher_no_generated = "1";
				$metalTran->update();
				$search_voucher = Setting::where('key', config('constants.settings.keys.gold_voucher_series'))->first()->value;
				$new_voucher = (int) $search_voucher + 1;
				$nid = Setting::select('id')->where('key', config('constants.settings.keys.gold_voucher_series'))->get();
				$setting = Setting::find($nid[0]->id);
				$setting->value = $new_voucher;
				$setting->update();
				$this->updateGoldIssueVoucherPdf($currentVoucherNo);
				return response()->json(array('status' => "true", 'message' => config('constants.message.voucherno_generated_sucess')));
			} else {
				return response()->json(array('status' => "false", 'message' => config('constants.message.voucherno_generated_failed')));
			}

		} else {
			return redirect('gold-inventory/goldissue')->with('error', Config::get('constants.message.settings_error'));
		}
	}

	public function goldhandover(request $request) {
		$issue_voucher_no = $request->id;
		if (!empty($issue_voucher_no)) {
			$metalTran = MetalTransaction::where('issue_voucher_no', $issue_voucher_no)->first();
			$metalTran->is_handover = "1";
			$metalTran->handover_at = date('Y-m-d H:i:s');
			$metalTran->update();
			return response()->json(array('status' => "true", 'message' => config('constants.message.handover_sucess')));
		} else {
			return response()->json(array('status' => "false", 'message' => config('constants.message.handover_failed')));
		}
	}

	public function deleteGoldVoucher(request $request) {
		$issue_voucher_no = $request->id;
		if (!empty($issue_voucher_no)) {
			$metalTranColl = MetalTransaction::where('issue_voucher_no', $issue_voucher_no)->first();
			$metal_weight = $metalTranColl->metal_weight;
			$metalTranid = $metalTranColl->id;
			$metal_type = $metalTranColl->metal_type;
			$metalColl = Metal::where('metal_type', $metal_type)->first();
			$currentWt = $metalColl->total_metal_weight;
			$fnlWt = $currentWt + $metal_weight;
			$metalColl->total_metal_weight = $fnlWt;
			$metalColl->update();
			$metalTranColl->find($metalTranid)->delete();
			return response()->json(array('status' => "true", 'message' => config('constants.message.delete_sucess')));
		} else {
			return response()->json(array('status' => "false", 'message' => config('constants.message.delete_failed')));
		}
	}
}