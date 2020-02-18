<?php

namespace App\Http\Controllers;

use App;
use App\CashVoucher;
use App\CustomerWallet;
use App\DiamondInventory;
use App\DiamondTransaction;
use App\Helpers\CommonHelper;
use App\Helpers\InventoryHelper;
use App\Metal;
use App\MetalTransaction;
use App\Payment;
use App\PaymentTransaction;
use App\PaymentType;
use Auth;
use Charts;
use Config;
use DateTime;
use DB;
use Illuminate\Http\Request;
use View;

class PaymentController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$paymenttype = PaymentType::all();

		$Overdue_data = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date < DATE(NOW()) AND due_date <= TIME(NOW())")->orderBy('created_at', 'DESC');
		$Overdue = $Overdue_data->paginate(10);
		$Overdue_Count = $Overdue_data->count();

		$Pastdue_data = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >=  DATE(NOW()) && due_date <=  DATE_ADD(CURDATE(), interval 15 DAY)")->orderBy('created_at', 'DESC');
		$Pastdue = $Pastdue_data->paginate(10);
		$Pastdue_Count = $Pastdue_data->count();

		$Futuredue_data = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >= DATE_ADD(CURDATE(), interval 16 DAY)")->orderBy('created_at', 'DESC');

		$Futuredue = $Futuredue_data->paginate(10);
		$Futuredue_Count = $Futuredue->count();

		$data = array('over_due' => $Overdue, 'past_due' => $Pastdue, 'future_due' => $Futuredue);

		$paymentType = array('over_due', 'past_due', 'future_due');

		return view('/account/payments/index', compact('paymenttype', 'data', 'Overdue_Count', 'Futuredue_Count', 'Pastdue_Count', 'paymentType'));
	}

	/**
	 *Find Email From DB .
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getemail(Request $request) {

		$data = $request->all();

		$email = DB::table(DB::raw('customer_entity'))->where('email', $data['email'])->select('email', 'entity_id')->first();

		if (!empty($email)) {
			if (App::environment('local')) {
				$firstnameId = Config::get('constants.fixIds.local.customer_entity_varchar_firstname');
				$lastnameId = Config::get('constants.fixIds.local.customer_entity_varchar_lastname');
			} else {
				$firstnameId = Config::get('constants.fixIds.live.customer_entity_varchar_firstname');
				$lastnameId = Config::get('constants.fixIds.live.customer_entity_varchar_lastname');
			}
			$entity = DB::select(DB::raw("SELECT c.entity_id,c.email,CONCAT(( SELECT fn.value FROM customer_entity_varchar fn WHERE c.entity_id = fn.entity_id AND fn.attribute_id = {$firstnameId}), ' ', ( SELECT fn.value FROM customer_entity_varchar fn WHERE c.entity_id = fn.entity_id AND fn.attribute_id = {$lastnameId})) AS name FROM customer_entity AS c LEFT JOIN customer_address_entity AS ca ON c.entity_id = ca.parent_id LEFT JOIN customer_address_entity_text AS cat ON cat.entity_id = ca.entity_id WHERE c.entity_id = $email->entity_id GROUP BY entity_id"));

			$message = "Email Verified";
			$response = $entity;

		} else {
			$message = "Email does not exist";
			$response = false;
		}

		return response()->json(['message' => $message, 'result' => $response]);
	}

	/**
	 *Storing Data Of Customer .
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function customerstore(Request $request) {

		$data = $request->all();
		if ($data['radioIsFranchise'] == "yes") {
			$this->validate($request, [
				'first_name' => 'required',
				'last_name' => 'required',
				'contact_number' => 'required',
				'address' => 'required',
				'country_id' => 'required',
				'city' => 'required',
				'zip' => 'required',
				'email' => 'required|email',
				'txtfrncode' => 'required',
			]);
		} else {
			$this->validate($request, [
				'first_name' => 'required',
				'last_name' => 'required',
				'contact_number' => 'required',
				'address' => 'required',
				'country_id' => 'required',
				'city' => 'required',
				'zip' => 'required',
				'email' => 'required|email',
			]);
		}

		$frnCode = $data['txtfrncode'];
		$contactNumber = $data['contact_number'];

		//check contact number exist
		$isContactNumberExist = InventoryHelper::checkContactNumberValidation('', $contactNumber);
		if ($isContactNumberExist) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_telephone_already_exist');
			//echo json_encode($response);exit;
			return ['result' => $response];
		}
		//Check frn code exit
		$isfrnExist = InventoryHelper::checkFRNCodeValidation('', $frnCode);

		if ($isfrnExist) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_frncode_already_exist');
			//echo json_encode($response);exit;
			return ['result' => $response];
		}

		if (!empty($frnCode)) {
			$frncodeStr = '&frncode=' . $frnCode;
		} else {
			$frncodeStr = '';
		}

		if (App::environment('local')) {
			$url = Config::get('app.create_customer');
		} else {
			$url = Config::get('constants.apiurl.test.create_customer');
		}

		$myvars = 'firstname=' . $data['first_name'] . '&lastname=' . $data['last_name'] . '&email=' . $data['email'] . '&contact_number=' . $data['contact_number'] . '&community=' . '1' . '&street=' . $data['address'] . '&country_id=' . $data['country_id'] . '&region=' . $data['getstate'] . '&city=' . $data['city'] . '&entity_customer=' . '1' . '&postcode=' . $data['zip'] . '&password=' . 'test@123' . '&confirmation=' . '1' . '&franchisee_status=' . '2' . $frncodeStr;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $myvars);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$response = json_decode($result);

		return ['result' => $response];
	}

	public function getname(Request $request) {

		$customer_name = InventoryHelper::getCustomerName($request->custID);

		return response()->json(['result' => $customer_name]);
	}

	public function dropdown(Request $request) {

		$payments = PaymentType::find($request->payment_type);
		if ($payments == '0') {
			$response = false;
		} else {
			$response = $payments;
		}

		return ['parent' => $response];

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function create(Request $request) {
		$paymenttype = PaymentType::select('id', 'name', 'parent_id')->where('parent_id', '!=', '0')->get();

		return view('/account/payments/create', compact('paymenttype', $paymenttype));
	}

	/*
		     * Store a newly created resource in storage.
		     *
		     * @param  \Illuminate\Http\Request  $request
		     * @return \Illuminate\Http\Response
	*/
	public function store(Request $request) {
		//print_r($request->all());exit;
		$input = $request->all();
		$user = Auth::user();
		$i = 15;

		$payment = Payment::all();
		$current_time = new DateTime('today');
		$this->validate($request, [
			'customer_id' => 'required',
			'customer_name' => 'required',
			'invoice_number' => 'required|unique:payments,invoice_number',
			'invoice_attachment.*' => 'required|mimes:jpeg,jpg,png,pdf',
			'invoice_amount' => 'required|numeric|min:1',
			'payment_type' => 'required',
			'payment_sub_type' => 'required',
			'due_date' => 'date_format:Y-m-d|after:$current_time',
		]);

		$fileName = $user->id . '_attachment' . time() . '.' . request()->invoice_attachment->getClientOriginalExtension();
		$file = $request->invoice_attachment->move(config('constants.dir.purchased_invoices'), $fileName);
		$payment->invoice_attachment = $fileName;

		$payment = new Payment;
		$payment->customer_name = $request->input('customer_name');
		$payment->customer_id = $request->input('customer_id');
		$payment->Invoice_number = $request->input('invoice_number');
		$payment->invoice_amount = $request->input('invoice_amount');
		$payment->due_date = $request->input('due_date');
		$payment->invoice_attachment = $fileName;
		$payment->payment_form = $request->input('payment_form');
		$payment->payment_type = $request->input('payment_type');
		$payment->payment_sub_type = $request->input('payment_sub_type');
		$payment->customer_type = $request->input('customer_type');
		$payment->remarks = $request->input('remarks');
		$payment->created_by = Auth::user()->id;
		$payment->save();

		$lastCompanyId = Payment::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
		$lastCompanyId = (int) substr($lastCompanyId, -3);

		$transaction_id = '10000170' . $lastCompanyId + 1;
		$payment->transaction_id = $transaction_id;

		$payment->save();
		$datetest = new DateTime('now' . '+ 15 day');
		$date = $datetest->format('Y-m-d');
		$paymentType = array('#over_due', '#past_due', '#future_due');

		if ($date >= $request->input('due_date')) {

			return redirect()->route('accountpayment.index', $paymentType[1])->withInput(['tab' => $paymentType[1]])->with('success', Config::get('constants.message.Payment_add_success'));
		} else {

			return redirect()->route('accountpayment.index', $paymentType[1])->withInput(['tab' => $paymentType[2]])->with('success', Config::get('constants.message.Payment_add_success'));
		}

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $id) {
		//print_r($request->all());exit;
		$transaction = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->where('payment_id', $id)->orderBy('id', 'DESC')->paginate();

		$totalcount = $transaction->count();
		return view('account/payments/show', compact('transaction', 'totalcount', 'totalamount'))->with('i', ($request->input('page', 1) - 1) * 5);
	}

	public function paidtransaction(Request $request, $id) {
		//print_r($request->all());exit;
		$transaction = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->where('payment_id', $id)->orderBy('id', 'DESC')->paginate();

		$totalcount = $transaction->count();
		return view('account/payments/show', compact('transaction', 'totalcount', 'totalamount'))->with('i', ($request->input('page', 1) - 1) * 5);
	}
	/*response for paid transaction list*/
	public function paidtransactionresponse(Request $request) {

		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form',
			6 => 'name');

		$results = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date', 'payment_transaction.invoice_number', 'payment_transaction.invoice_amount')->where('payment_id', $request->_id)->orderBy('id', 'DESC')->distinct();

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		if (empty($request->input('search.value'))) {
			$resultslist = $results->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = $results->whereRaw('(customer_name LIKE "%' . $search . '%" OR dml_payment_transaction.invoice_number LIKE "%' . $search . '%" OR dml_payments.invoice_amount LIKE "%' . $search . '%"OR dml_payment_transaction.invoice_amount LIKE "%' . $search . '%" OR dml_payment_transaction.remaining_amount LIKE "%' . $search . '%" OR dml_payment_transaction.paid_at LIKE "%' . $search . '%") ')
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
			$totalFiltered = $results->whereRaw('(customer_name LIKE "%' . $search . '%" OR dml_payment_transaction.invoice_number LIKE "%' . $search . '%" OR dml_payments.invoice_amount LIKE "%' . $search . '%" OR dml_payment_transaction.invoice_amount LIKE "%' . $search . '%" OR dml_payment_transaction.remaining_amount LIKE "%' . $search . '%" OR dml_payment_transaction.paid_at LIKE "%' . $search . '%") ')->count();
		}
		$data = array();

		$i = 0;
		if (!empty($resultslist)) {

			foreach ($resultslist as $resultslist) {

				$id = '<input type="hidden" name="id" class="payment_id" value="' . $resultslist->payment_id . '">';
				$data[] = array(++$start, $resultslist->customer_name, $resultslist->invoice_number, $resultslist->invoice_amount, $resultslist->remaining, $resultslist->paid_at, $id);
			}
			$json_data = array(
				"draw" => intval($request->input('draw')),
				"recordsTotal" => intval($totalData),
				"recordsFiltered" => intval($totalFiltered),
				"data" => $data,
			);
			echo json_encode($json_data);
		}
	}
	/*response for paid transaction list*/

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \usersIlluminate\Http\Response
	 */
	public function edit($id) {
		$payment = Payment::find($id);
		$paymenttype = PaymentType::select('id', 'name', 'parent_id')->where('parent_id', '!=', '0')->get();
		$results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->get();

		return view('account.payments.edit', compact('payment', 'paymenttype', 'results'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		$input = $request->all();
		$payment = Payment::findorfail($id);
		$user = Auth::user();

		$this->validate($request, [
			'customer_name' => 'required',
			'invoice_number' => 'required|integer|min:1',
			'invoice_attachment' => 'file|mimes:jpeg,jpg,png,pdf',
			'invoice_amount' => 'required|numeric|min:1',
		]);

		if ($request->invoice_attachment != null) {
			$fileName = $user->id . '_attachment' . time() . '.' . request()->invoice_attachment->getClientOriginalExtension();
			$file = $request->invoice_attachment->move(config('constants.dir.purchased_invoices'), $fileName);
			$payment->invoice_attachment = $fileName;

			$payment->update([
				'customer_name' => $request->customer_name,
				'invoice_number' => $request->invoice_number,
				'invoice_amount' => $request->invoice_amount,
				'due_date' => $request->due_date,
				'invoice_attachment' => $fileName,
				'payment_form' => $request->payment_form,
				'payment_type' => $request->payment_type,
				'payment_sub_type' => $request->payment_sub_type,
				'customer_type' => $request->customer_type,
				'remarks' => $request->remarks,
			]);

		} else {
			$payment->update($request->all());
		}

		return redirect()->route('accountpayment.index')
			->with('success', Config::get('constants.message.Payment_update_success'));
	}

	/**
	 * Display the data of Outgoing Payments.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function outgoing(Request $request) {

		$resultdata = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')->where('payment_form', '=', 'Outgoing')->orderBy('id', 'DESC');
		$results = $resultdata->paginate(10);
		$totalcount = $resultdata->count();

		$paymenttype = PaymentType::all();

		return view('/account/payments/outgoing', compact('paymenttype', 'results', 'totalcount'))->with('i', ($request->input('page', 1) - 1) * 5);

	}

	/*response for outgoing acount payment list*/
	public function outgoingresponse(Request $request) {
		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form',
			6 => 'name');
		$results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')->where('payment_form', '=', 'Outgoing');

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		if (empty($request->input('search.value'))) {
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')->where('payment_form', '=', 'Outgoing')->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')->where('payment_form', '=', 'Outgoing')->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
			$totalFiltered = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')->where('payment_form', '=', 'Outgoing')->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
		}
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {
				$created = $resultslist->created_at->format('Y-m-d');
				$action = ' <a href="javascript:void(0);" onclick="event.preventDefault();Approvepayment(' . $resultslist->id . ',\'' . csrf_token() . '\');" data-token="\'' . csrf_token() . '\'"><i class="material-icons"  title="Approve"> check_circle</i></a>
                                      <a href="javascript:void(0);" onclick="event.preventDefault();declinepayment(' . $resultslist->id . ',\'' . csrf_token() . '\');" data-token="\'' . csrf_token() . '\'"><i class="material-icons" style="color:red;" title="Decline"> highlight_off</i></a>
                                      <a class="color-content table-action-style" href="' . route('accountpayment.pdflisting', ['id' => $resultslist->id]) . '"><i class="material-icons md-18">file_download</i></a>';
				$amount = ' <td>' . CommonHelper::covertToCurrency($resultslist->invoice_amount) . '</td>';
				$data[] = array(++$start, $resultslist->customer_name, $resultslist->invoice_number, $amount, $resultslist->due_date, $resultslist->payment_form, $resultslist->name, $created, $action);
			}
			$json_data = array(
				"draw" => intval($request->input('draw')),
				"recordsTotal" => intval($totalData),
				"recordsFiltered" => intval($totalFiltered),
				"data" => $data,
			);
			echo json_encode($json_data);
		}
	}
	/*response for outgoing acount payment list*/

	/**
	 * Display the data of Incoming Payments.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function incoming(Request $request) {
		$result_data = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')->where('payment_form', '=', 'Incoming')->orderBy('id', 'DESC');
		$results = $result_data->paginate(10);
		$totalcount = $result_data->count();
		$paymenttype = PaymentType::all();
		return view('/account/payments/incoming', compact('paymenttype', 'results', 'totalcount'))->with('i', ($request->input('page', 1) - 1) * 5);

	}

	/*response for incoming acount payment list*/
	public function incomingresponse(Request $request) {

		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form',
			6 => 'name');
		$results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')->where('payment_form', '=', 'Incoming')->distinct();

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		if (empty($request->input('search.value'))) {
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')->where('payment_form', '=', 'Incoming')->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->select('payments.*', 'payment_types.name')
				->where('account_status', '=', '0')
				->where('payment_form', '=', 'Incoming')
				->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%"  OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
			$totalFiltered = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')
				->where('payment_form', '=', 'Incoming')
				->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
		}
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {
				$created = $resultslist->created_at->format('Y-m-d');
				$action = '<a href="javascript:void(0);" onclick="event.preventDefault();Approvepayment(' . $resultslist->id . ',\'' . csrf_token() . '\');" data-token="\'' . csrf_token() . '\'"><i class="material-icons"  title="Approve"> check_circle</i></a>
                                      <a href="javascript:void(0);" onclick="event.preventDefault();declinepayment(' . $resultslist->id . ',\'' . csrf_token() . '\');" data-token="\'' . csrf_token() . '\'"><i class="material-icons" style="color:red;" title="Decline"> highlight_off</i></a>
                                      <a class="color-content table-action-style" href="' . route('accountpayment.pdflisting', ['id' => $resultslist->id]) . '"><i class="material-icons md-18">file_download</i></a>';
				$amount = ' <td>' . CommonHelper::covertToCurrency($resultslist->invoice_amount) . '</td>';
				$data[] = array(++$start, $resultslist->customer_name, $resultslist->invoice_number, $amount, $resultslist->due_date, $resultslist->payment_form, $resultslist->name, $created, $action);
			}
			$json_data = array(
				"draw" => intval($request->input('draw')),
				"recordsTotal" => intval($totalData),
				"recordsFiltered" => intval($totalFiltered),
				"data" => $data,
			);
			echo json_encode($json_data);
		}
	}
	/*response for incoming acount payment list*/

	/**
	 * Approve data from storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function out_approved(Request $request, $id) {

		$check_decline = Payment::select('id', 'account_status')->where('id', $id)->where('account_status', 2)->oRwhere('account_status', 1)->find($id);
		if ($check_decline == "") {
			$payment = Payment::find($id);
			$newData = '1';
			$payment->account_status = $newData;
			$payment->save();
			return response()->json(array('success' => true, 'message' => "Payment Approved successfully"));
		} else {
			if ($check_decline->account_status == 1) {
				return response()->json(array('errors' => false, 'message' => "This transaction allredy Approved"));

			} else {
				return response()->json(array('errors' => false, 'message' => "This transaction allredy Declined"));

			}

		}

	}

	/**
	 * Display the data of Approved Payments.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function approved(Request $request) {

		$approved_payments_with_transactions_only_id = DB::table('payments')
			->select('payments.id')
			->join('payment_transaction', 'payments.id', '=', 'payment_transaction.payment_id')
			->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->groupBy('payment_transaction.payment_id')
			->where('account_status', '=', '1')
			->orderBy('due_date', 'DESC')->pluck('payments.id')->toArray();

		//dd($approved_payments_with_transactions_only_id);

		$approved_payments_with_transactions = DB::table('payments')
			->select('payments.*', 'payment_types.name', 'payment_transaction.status', 'payment_transaction.updated_at as payment_status_updated')
			->join('payment_transaction', 'payments.id', '=', 'payment_transaction.payment_id')
			->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->groupBy('payment_transaction.payment_id')
			->where('payments.account_status', '=', '1');

		$approved_payments = DB::table("payments")
			->select("payments.*", 'payment_types.name', DB::raw('"Pending" AS status'), 'payments.updated_at AS payment_status_updated')
			->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->where('payments.account_status', '=', '1')
			->whereNotIn('payments.id', $approved_payments_with_transactions_only_id)
			->union($approved_payments_with_transactions)
			->orderBy('due_date', 'DESC');

		$results = $approved_payments->paginate(50);

		$approved_payments_transactions_only_id = DB::table('payments')
			->select('payments.id')
			->join('payment_transaction', 'payments.id', '=', 'payment_transaction.payment_id')
			->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->groupBy('payment_transaction.payment_id')
			->where('account_status', '=', '1')
			->orderBy('due_date', 'DESC')->pluck('payments.id')->toArray();

		$approved_payments_transactions = DB::table('payments')
			->select('payments.*', 'payment_types.name', 'payment_transaction.status', 'payment_transaction.updated_at as payment_status_updated')
			->join('payment_transaction', 'payments.id', '=', 'payment_transaction.payment_id')
			->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->groupBy('payment_transaction.payment_id')
			->where('payments.account_status', '=', '1');

		$approveded = DB::table("payments")
			->select("payments.*", 'payment_types.name', DB::raw('"Pending" AS status'), 'payments.updated_at AS payment_status_updated')
			->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->where('payments.account_status', '=', '1')
			->whereNotIn('payments.id', $approved_payments_transactions_only_id)
			->union($approved_payments_transactions)
			->count();

		//dd($approved_payments);

		return view('/account/payments/approved', compact('results', 'approveded'))->with('i', ($request->input('page', 1) - 1) * 10);
	}

	/*response for approved acount payment list*/
	public function approvedresponse(Request $request) {

		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form',
			6 => 'name');

		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		$approved_payments_with_transactions_only_id = DB::table('payments')
			->select('payments.id')
			->join('payment_transaction', 'payments.id', '=', 'payment_transaction.payment_id')
			->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->groupBy('payment_transaction.payment_id')
			->where('account_status', '=', '1')
			->orderBy('due_date', 'DESC')->pluck('payments.id')->toArray();

		//dd($approved_payments_with_transactions_only_id);

		$approved_payments_with_transactions = DB::table('payments')
			->select('payments.*', 'payment_types.name', 'payment_transaction.status', 'payment_transaction.updated_at as payment_status_updated')
			->join('payment_transaction', 'payments.id', '=', 'payment_transaction.payment_id')
			->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
		//->union($approved_payments)
			->groupBy('payment_transaction.payment_id')
			->where('payments.account_status', '=', '1');

		$approved_payments = DB::table("payments")
			->select("payments.*", 'payment_types.name', DB::raw('"Pending" AS status'), 'payments.updated_at AS payment_status_updated')
			->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->where('payments.account_status', '=', '1')
			->whereNotIn('payments.id', $approved_payments_with_transactions_only_id)
			->union($approved_payments_with_transactions)
			->orderBy('due_date', 'DESC');

		$results = $approved_payments;

		//$approveded = $approved_payments->count();

		$totalData = $approved_payments->count();
		$totalFiltered = $totalData;
		//$results = $approved_payments->get();

		if (empty($request->input('search.value'))) {
			$resultslist = $approved_payments->offset($start)
				->limit($limit)
				->orderBy($order, $dir);
			//print_r($search);exit;
		} else {
			$search = $request->input('search.value');
			$approved_payments_with_transactions_only_id = DB::table('payments')
				->select('payments.id')
				->join('payment_transaction', 'payments.id', '=', 'payment_transaction.payment_id')
				->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->groupBy('payment_transaction.payment_id')
				->where('account_status', '=', '1')
				->whereRaw('dml_payments.customer_name LIKE "%' . $search . '%" OR dml_payments.invoice_number LIKE "%' . $search . '%" OR dml_payments.invoice_amount LIKE "%' . $search . '%" ')
				->orderBy('due_date', 'DESC')->pluck('payments.id')->toArray();

			//dd($approved_payments_with_transactions_only_id);

			$approved_payments_with_transactions = DB::table('payments')
				->select('payments.*', 'payment_types.name', 'payment_transaction.status', 'payment_transaction.updated_at as payment_status_updated')
				->join('payment_transaction', 'payments.id', '=', 'payment_transaction.payment_id')
				->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			//->union($approved_payments)
				->groupBy('payment_transaction.payment_id')
				->where('payments.account_status', '=', '1')
				->whereRaw('customer_name LIKE "%' . $search . '%" OR dml_payments.invoice_number LIKE "%' . $search . '%" OR dml_payments.invoice_amount LIKE "%' . $search . '%" ');

			$approved_payments_with_transactions_only_id_str = implode(',', $approved_payments_with_transactions_only_id);

			$approved_payments = DB::table("payments")
				->select("payments.*", 'payment_types.name', DB::raw('"Pending" AS status'), 'payments.updated_at AS payment_status_updated')
				->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->where('payments.account_status', '=', '1')
				->whereRaw('(customer_name LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") AND (dml_payments.id NOT IN (' . $approved_payments_with_transactions_only_id_str . '))')
				->union($approved_payments_with_transactions)
				->orderBy('due_date', 'DESC');

			$resultslist = $approved_payments;
			$totalFiltered = $resultslist->count();
		}
		$resultquery = $resultslist->toSql();
		$resultbindings = $resultslist->getBindings();
		$resultslist = $resultslist->get();
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {
				if ($resultslist->status == "") {
					$payment_status = "";
				} else {
					$payment_status = $resultslist->status;
				}
				if ($resultslist->updated_at == "") {
					$payment_status_updated = "";
				} else {
					$payment_status_updated = date('Y-m-d', strtotime($resultslist->payment_status_updated));
				}

				$created = date('Y-m-d', strtotime($resultslist->created_at));
				$action = '<a class="color-content table-action-style" href="' . route('accountpayment.pdflisting', ['id' => $resultslist->id]) . '"><i class="material-icons md-18">file_download</i></a>';

				$data[] = array(++$start, $resultslist->customer_name, $resultslist->invoice_number, $resultslist->invoice_amount, $resultslist->due_date, $resultslist->payment_form, $resultslist->name, $payment_status, $payment_status_updated, $created, $action);
			}
			$json_data = array(
				//"query" => $resultquery,
				//"bindings" => $resultbindings,
				"draw" => intval($request->input('draw')),
				"recordsTotal" => intval($totalData),
				"recordsFiltered" => intval($totalFiltered),
				"data" => $data,
			);
			echo json_encode($json_data);
		}
	}
	/*response for approved acount payment list*/

	/**
	 * Decline data from storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function out_decline(Request $request, $id) {
		$check_decline = Payment::select('id', 'account_status')->where('id', $id)->where('account_status', 2)->oRwhere('account_status', 1)->find($id);
		if ($check_decline == "") {
			$payment = Payment::find($id);
			$newData = '2';
			$payment->account_status = $newData;
			$payment->save();
			return response()->json(array('success' => true, 'message' => "Payment Decline successfully"));
		} else {
			if ($check_decline->account_status == 1) {
				return response()->json(array('errors' => false, 'message' => "This Transaction Allredy Approved"));

			} else {
				return response()->json(array('errors' => false, 'message' => "This Transaction Allredy Decline"));

			}

		}

	}

	/**
	 * Display the data of Decline Payments.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */

	public function decline(Request $request) {

		$results_data = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->select('payments.*', 'payment_types.name')->where('account_status', '=', '2')
			->orderBy('due_date', 'DESC');
		$results = $results_data->paginate(10);

		$totalcount = $results_data->count();
		return view('/account/payments/decline', compact('results', 'totalcount'))->with('i');
	}

	/*response for decline acount payment list*/
	public function declineresponse(Request $request) {
		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form',
			7 => 'name');
		$results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->select('payments.*', 'payment_types.name')->where('account_status', '=', '2')
			->orderBy('id', 'asc')->distinct();

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		if (empty($request->input('search.value'))) {
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->select('payments.*', 'payment_types.name')->where('account_status', '=', '2')->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->select('payments.*', 'payment_types.name')->where('account_status', '=', '2')->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
			$totalFiltered = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
				->select('payments.*', 'payment_types.name')->where('account_status', '=', '2')->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
		}
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $key => $resultlist) {

				$created = $resultslist[$key]->created_at->format('Y-m-d');
				$action = '<a class="color-content table-action-style" href="' . route('accountpayment.pdflisting', ['id' => $resultlist->id]) . '"><i class="material-icons md-18">file_download</i></a>';
				$amount = ' <td>' . CommonHelper::covertToCurrency($resultlist->invoice_amount) . '</td>';
				$data[] = array(++$start, $resultlist->customer_name, $resultlist->invoice_number, $amount, $resultlist->due_date, $resultlist->payment_form, $resultlist->name, $created, $action);
			}
			$json_data = array(
				"draw" => intval($request->input('draw')),
				"recordsTotal" => intval($totalData),
				"recordsFiltered" => intval($totalFiltered),
				"data" => $data,
			);
			echo json_encode($json_data);
		}
	}
	/*response for decline acount payment list*/

	/*getpayment of approved payments */
	public function getpayment(Request $request) {

		$pending = Payment::select('remaining_amount', 'id')
			->where('id', $request->id)
			->where('remaining_amount', '>=', '0')
			->get();

		if ($pending->isEmpty()) {

			$data['payment'] = DB::select(DB::raw("SELECT `id`, (invoice_amount)- IFNULL(remaining_amount,0) as pending FROM `dml_payments` WHERE id=$request->id"));
			$returnHTML = view('payment/payment_process', ['data' => $data])->render();
			return response()->json(array('success' => true, 'html' => $returnHTML));

		} else if ($pending[0]->remaining_amount != 0) {

			$data['payment'] = DB::select(DB::raw("SELECT `id`,remaining_amount as pending FROM `dml_payments` WHERE id=$request->id"));

			$returnHTML = view('payment/payment_process', ['data' => $data])->render();
			return response()->json(array('success' => true, 'html' => $returnHTML));
		} else {

			$message = "Payment Could not be posible";
			$response = false;
			return redirect()->route('payment.paidpayment')->with('success', Config::get('constants.message.Payment_Decline_success'));
		}
	}
	/*getpayment of approved payments */

	/**
	 * Display the data of Incoming Payment from Aproved Payments.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function payment_incoming(Request $request) {

		$incoming_data = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_form', '=', 'Incoming')->where('account_status', '=', '1')->whereIn('payment_status', array(0, 2))->orderBy('id', 'DESC');
		$incoming = $incoming_data->paginate(10);

		$totalcount = $incoming_data->count();
		return view('payment/incoming', compact('incoming', 'totalcount'))->with('i', ($request->input('page', 1) - 1) * 5);
	}

	/*response for Incoming Approved Payment  list*/
	public function payment_incomingresponse(Request $request) {
		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form',
			6 => 'name');
		$results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_form', '=', 'Incoming')->where('account_status', '=', '1')->whereIn('payment_status', array(0, 2))->orderBy('id', 'desc');

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		if ($request->input('order.0.column') == 2) {
			$order = 'cast(' . $columns[$request->input('order.0.column')] . ' as unsigned)';
		} else {
			$order = $columns[$request->input('order.0.column')];
		}

		$dir = $request->input('order.0.dir');
		if (empty($request->input('search.value'))) {
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_form', '=', 'Incoming')->where('account_status', '=', '1')->whereIn('payment_status', array(0, 2))->orderByRaw($order . ' ' . $dir)->offset($start)
				->limit($limit)
				->orderByRaw($order . ' ' . $dir)->get();

		} else {
			$search = $request->input('search.value');
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_form', '=', 'Incoming')->where('account_status', '=', '1')->whereIn('payment_status', array(0, 2))->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
				->offset($start)
				->limit($limit)
				->orderByRaw($order . ' ' . $dir)->get();
			$totalFiltered = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_form', '=', 'Incoming')->where('account_status', '=', '1')->whereIn('payment_status', array(0, 2))->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
		}
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {
				$created = $resultslist->created_at->format('Y-m-d');
				$disableClass = ($resultslist->remaining_amount == NULL) ? 'disabled' : '';
				$action = '<a href="javascript:void(0)" class ="payment_model " data-id="' . $resultslist->id . '" onclick=" " > <i class="material-icons"  title="Paid">credit_card</i></a>
                                         <a  href="' . action('PaymentController@paidtransaction', $resultslist->id) . '"  class="' . $disableClass . '" onclick=" "><i class="material-icons"  title="View Transaction">remove_red_eye</i></a> <a class="color-content table-action-style" href="' . route('accountpayment.pdflisting', ['id' => $resultslist->id]) . '"><i class="material-icons md-18">file_download</i></a>';

				if (isset($resultslist->payment_status) && $resultslist->payment_status == 2) {
					$invoiceId = InventoryHelper::getInvoiceEntityId($resultslist->invoice_number);
					if (InventoryHelper::isCashVoucherExist($invoiceId)) {
						$disableClass = (InventoryHelper::isCashVoucherGenerated($invoiceId)) ? 'disabled' : '';
						$action .= '<a href="javascript:void(0)" class ="btn-generate-cashvoucher ' . $disableClass . '" data-id="' . $resultslist->id . '" onclick=" " ' . $disableClass . '><i class="material-icons"  title="Generate Cash Voucher">content_paste</i></a>';
					}
				}

				$data[] = array(++$start, $resultslist->customer_name, $resultslist->invoice_number, CommonHelper::covertToCurrency($resultslist->invoice_amount), $resultslist->due_date, $resultslist->payment_form, $resultslist->name, $created, $action);
			}
			$json_data = array(
				"draw" => intval($request->input('draw')),
				"recordsTotal" => intval($totalData),
				"recordsFiltered" => intval($totalFiltered),
				"data" => $data,
			);
			echo json_encode($json_data);
		}
	}
	/*response for Incoming Approved Payment  list*/

	/**
	 * Display the data of Outgoing Payment from Aproved Payments.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function payment_outgoing(Request $request) {
		$outgoing_data = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_form', '=', 'Outgoing')->where('account_status', '=', '1')->where('payment_status', '=', '0')->orderBy('id', 'DESC');
		$outgoing = $outgoing_data->paginate(10);
		$totalcount = $outgoing_data->count();
		return view('payment/outgoing', compact('outgoing', 'totalcount'))->with('i', ($request->input('page', 1) - 1) * 5);
	}

	/*response for Outgoing Approved Payment  list*/
	public function payment_outgoingresponse(Request $request) {
		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form',
			6 => 'name');
		$results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_form', '=', 'Outgoing')->where('account_status', '=', '1')->where('payment_status', '=', '0')->distinct();

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		if (empty($request->input('search.value'))) {
			$resultslist = $results->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_form', '=', 'Outgoing')->where('account_status', '=', '1')->where('payment_status', '=', '0')->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->orderBy('id', 'DESC')
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
			$totalFiltered = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_form', '=', 'Outgoing')->where('account_status', '=', '1')->where('payment_status', '=', '0')->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
		}
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {
				$disableClass = ($resultslist->remaining_amount == NULL) ? 'disabled' : '';
				$created = $resultslist->created_at->format('Y-m-d');
				$action = ' <a   href ="javascript:void(0)" id="payment_model" data-id="' . $resultslist->id . '" onclick=""><i class="material-icons" title="Paid">credit_card</i></a>

                                         <a  href="' . action('PaymentController@paidtransaction', $resultslist->id) . '" class = "' . $disableClass . '"onclick=" "><i class="material-icons"  title="View Transaction">remove_red_eye</i></a> <a class="color-content table-action-style" href="' . route('accountpayment.pdflisting', ['id' => $resultslist->id]) . '"><i class="material-icons md-18">file_download</i></a>';

				$amount = ' <td>' . CommonHelper::covertToCurrency($resultslist->invoice_amount) . '</td>';
				$data[] = array(++$start, $resultslist->customer_name, $resultslist->invoice_number, $amount, $resultslist->due_date, $resultslist->payment_form, $resultslist->name, $created, $action);
			}
			$json_data = array(
				"draw" => intval($request->input('draw')),
				"recordsTotal" => intval($totalData),
				"recordsFiltered" => intval($totalFiltered),
				"data" => $data,
			);
			echo json_encode($json_data);
		}
	}
	/*response for Outgoing Approved Payment  list*/

	/*response for paid payment update*/
	public function out_paid(Request $request, $id) {

		$payment = Payment::find($id);
		$newData = '1';
		$payment->payment_status = $newData;
		$payment->save();
		return redirect()->route('payment.paidpayment')->with('success', Config::get('constants.message.Payment_Decline_success'));
	}
	/*response for paid payment update*/

	/**
	 * Display the data of Paid Payment list.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function paid_payment(Request $request) {
		$paid_data = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_status', '=', '1')->orderBy('id', 'DESC');

		$paid = $paid_data->paginate(10);
		$totalcount = $paid_data->count();
		return view('payment/paidpayment', compact('paid', 'totalcount'))->with('i', ($request->input('page', 1) - 1) * 5);

	}

	/*response for paid payment list */
	public function paidpayment_response(Request $request) {
		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form');
		$results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_status', '=', '1');

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		if (empty($request->input('search.value'))) {
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_status', '=', '1')->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = $results->whereRaw('(customer_name LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
			$totalFiltered = $results->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
		}
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {
				$created = $resultslist->created_at->format('Y-m-d');
				$action = '<a  href="' . action('PaymentController@show', $resultslist->id) . '" onclick=" "><i class="material-icons"  title="View Transaction">remove_red_eye</i></a> <a class="color-content table-action-style" href="' . route('accountpayment.pdflisting', ['id' => $resultslist->id]) . '"><i class="material-icons md-18">file_download</i></a>';
				$amount = ' <td>' . CommonHelper::covertToCurrency($resultslist->invoice_amount) . '</td>';
				$data[] = array(++$start, $resultslist->customer_name, $resultslist->invoice_number, $amount, $resultslist->due_date, $resultslist->payment_form, $resultslist->name, $created, $action);
			}
			$json_data = array(
				"draw" => intval($request->input('draw')),
				"recordsTotal" => intval($totalData),
				"recordsFiltered" => intval($totalFiltered),
				"data" => $data,
			);
			echo json_encode($json_data);
		}
	}
	/*response for paid payment list */

	/*payment transaction update  */
	public function payment_transaction(Request $request) {
		$data = $request->all();
		$ldate = date('Y-m-d ');

		$user = Auth::user();
		$payment = Payment::all();
		$fileName = $user->id . '_attachment' . time() . '.' . request()->invoice_attachment->getClientOriginalExtension();
		$file = $request->invoice_attachment->move(config('constants.dir.paid_invoice_attachment'), $fileName);
		$paymenttransaction = new PaymentTransaction;
		$paymenttransaction->payment_id = $request->input('payment_id');
		$paymenttransaction->Invoice_number = $request->input('invoice_number');
		$paymenttransaction->invoice_amount = $request->input('paid_amount');
		$paymenttransaction->invoice_attachment = $fileName;
		$paymenttransaction->status = $request->input('payment_form');
		$remaining_amount = $request->invoice_amount - $request->paid_amount;
		$paymenttransaction->remaining_amount = $remaining_amount;
		$paymenttransaction->paid_at = $ldate;
		$paymenttransaction->created_by = Auth::user()->id;
		$paymenttransaction->save();

		if ($request->invoice_amount == $request->paid_amount) {
			$payment = Payment::find($request->payment_id);
			$payment->remaining_amount = '0';
			$newData = '1';
			$payment->payment_status = $newData;
			$payment->save();
		} else {
			$payment = Payment::find($request->payment_id);
			$remaining_amount = $request->invoice_amount - $request->paid_amount;
			$payment->remaining_amount = $remaining_amount;

			$payment->save();
		}

		if ($request->invoice_amount == $request->paid_amount) {
			$paid_data = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_status', '=', '1')->orderBy('id', 'DESC');

			$paid = $paid_data->paginate(10);
			$totalcount = $paid_data->count();
			return view('payment/paidpayment', compact('paid', 'totalcount'))->with('i', ($request->input('page', 1) - 1) * 5);
		} else {
			return redirect()->route('accountpayment.show', ['id' => $request->payment_id]);
		}

	}
	/*payment transaction update  */

	/*paid transaction list */
	public function paid_transaction(Request $request) {
		$transaction = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->orderBy('id', 'DESC')->distinct()->paginate();

		$totalcount = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->orderBy('id', 'DESC')->count(10);
		return view('payment/paidtransaction', compact('transaction', 'totalcount '))->with('i', ($request->input('page', 1) - 1) * 5);

	}
	/*paid transaction list */

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		Payment::find($id)->delete();

		return response()->json([
			'success' => 'Record deleted successfully!',
		]);
		$return_data = array();

		$return_data['response'] = 'success';
		echo json_encode($return_data);exit;
		return redirect()->route('accountpayment.index')
			->with('success', Config::get('constants.message.Payment_delete_success'));
	}

	/*Multiple Delete For Paymentlist*/
	public function multiple_delete(Request $request) {
		$delete = $request->ids;

		Payment::whereIn('id', explode(",", $delete))->delete();
		return response()->json(['status' => true, 'message' => "Payment deleted successfully."]);
	}
	/*Multiple Delete For Paymentlist*/

	/*Ajax Response for payment list */
	public function paymentresponse(Request $request) {

		$columns = array(
			0 => 'id',
			2 => 'customer_name',
			3 => 'invoice_number',
			4 => 'invoice_amount',
			5 => 'due_date',
			6 => 'payment_form',
			7 => 'payment_type',
			8 => 'created_at',
			9 => 'action');

		$Overdue = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date < DATE(NOW()) AND due_date <= TIME(NOW())")->orderBy('created_at', 'asc')->distinct();

		$Pastdue = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >=  DATE(NOW()) && due_date <=  DATE_ADD(CURDATE(), interval 15 DAY)")->orderBy('created_at', 'asc')->distinct();

		$Futuredue = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >= DATE_ADD(CURDATE(), interval 16 DAY)")->orderBy('created_at', 'asc')->distinct();

		if ($request->_id == 'over_due') {
			$totalData = $Overdue->count();
		}
		if ($request->_id == 'past_due') {
			$totalData = $Pastdue->count();
		}
		if ($request->_id == 'future_due') {
			$totalData = $Futuredue->count();
		}

		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		if ($request->input('order.0.column') == 3) {
			$order = 'cast(' . $columns[$request->input('order.0.column')] . ' as unsigned)';
		} else {
			$order = $columns[$request->input('order.0.column')];
		}
		$dir = $request->input('order.0.dir');

		if (empty($request->input('search.value'))) {
			if ($request->_id == 'over_due') {

				$paymentlist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date < DATE(NOW()) AND due_date <= TIME(NOW())")->offset($start)
					->limit($limit)
					->orderByRaw($order . ' ' . $dir)->get();
				$commonClass = 'overpayment';

			} else if ($request->_id == 'past_due') {
				$paymentlist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >=  DATE(NOW()) && due_date <=  DATE_ADD(CURDATE(), interval 15 DAY)")->offset($start)
					->limit($limit)
					->orderByRaw($order . ' ' . $dir)->get();
				$commonClass = 'pastpayment';
			} else {
				$paymentlist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >= DATE_ADD(CURDATE(), interval 16 DAY)")->offset($start)
					->limit($limit)
					->orderByRaw($order . ' ' . $dir)->get();
				$commonClass = 'futurepayment';
			}

		} else {
			$search = $request->input('search.value');
			if ($request->_id == 'over_due') {
				$paymentlist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date < DATE(NOW()) AND due_date <= TIME(NOW())")->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%"  OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
					->offset($start)
					->limit($limit)
					->orderBy($order, $dir)
					->get();
				$totalFiltered = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date < DATE(NOW()) AND due_date <= TIME(NOW())")->whereRaw('(customer_name LIKE "%' . $search . '%"OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%"  OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
				$commonClass = 'overpayment';
			} else if ($request->_id == 'past_due') {
				$paymentlist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >=  DATE(NOW()) && due_date <=  DATE_ADD(CURDATE(), interval 15 DAY)")->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%"  OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
					->offset($start)
					->limit($limit)
					->orderBy($order, $dir)
					->get();
				$totalFiltered = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >=  DATE(NOW()) && due_date <=  DATE_ADD(CURDATE(), interval 15 DAY)")->whereRaw('(customer_name LIKE "%' . $search . '%" OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%"  OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
				$commonClass = 'pastpayment';
			} else {
				$paymentlist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >= DATE_ADD(CURDATE(), interval 16 DAY)")->whereRaw('(customer_name LIKE "%' . $search . '%"OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%"  OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
					->offset($start)
					->limit($limit)
					->orderBy($order, $dir)
					->get();
				$totalFiltered = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >= DATE_ADD(CURDATE(), interval 16 DAY)")->whereRaw('(customer_name LIKE "%' . $search . '%"OR due_date LIKE "%' . $search . '%" OR dml_payments.created_at LIKE "%' . $search . '%"  OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
				$commonClass = 'futurepayment';
			}
		}

		$data = array();
		$i = 0;
		if (!empty($paymentlist)) {
			foreach ($paymentlist as $key => $paymentlist) {
				$created = $paymentlist->created_at->format('Y-m-d');
				$checkbox = '<label><input type="checkbox"  class="form-check-input checkbox ' . $commonClass . '" name="chckpayment[]" id="chkPayment_{{$paymentlist->id}}" value="' . $paymentlist->id . '"><span class="label-text"></span></label>';
				$action = '<a class= "table-action-style"href="' . route('accountpayment.show', $paymentlist->id) . '" style="display:none;"><i class="material-icons md-18">remove_red_eye</i></a>
                		<a class="color-content table-action-style" href="payments/' . $paymentlist->id . '/edit"><i class="material-icons md-18">edit</i></a>
                		<a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deletePayment(' . $paymentlist->id . ',\'' . csrf_token() . '\');" data-token="\'' . csrf_token() . '\'"><i class="material-icons md-18">delete</i></a>
                		<a class="color-content table-action-style" href="' . route('accountpayment.pdflisting', ['id' => $paymentlist->id]) . '"><i class="material-icons md-18">file_download</i></a>';

				if ($paymentlist->due_date == '0000-00-00') {
					$due_date = '';
				} else {
					$due_date = $paymentlist->due_date;
				}
				$amount = ' <td>' . CommonHelper::covertToCurrency($paymentlist->invoice_amount) . '</td>';

				$data[] = array($checkbox, ++$start, $paymentlist->customer_name, $paymentlist->invoice_number, $amount, $due_date, $paymentlist->payment_form, $paymentlist->name, $created, $action);

			}

			$json_data = array(

				"draw" => intval($request->input('draw')),
				"recordsTotal" => intval($totalData),
				"recordsFiltered" => intval($totalFiltered),
				"data" => $data,
			);
			echo json_encode($json_data);

		}

	}
	/*Ajax Response for payment list */

	/*summary of account payment*/
	public function summary() {

		$totalincoming = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('payment_form', 'Incoming')->value('total_amount');
		$totaloutgoing = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('payment_form', 'Outgoing')->value('total_amount');

		if ($totalincoming == "") {

			$incoming = 0;
		} else {
			$incoming = $totalincoming;
		}

		if ($totaloutgoing == "") {
			$outgoing = 0;
		} else {
			$outgoing = $totaloutgoing;
		}

		/*Approved statistics*/
		$approvedtotal = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '1')->value('total_amount');
		$approvedincoming = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '1')->where('payment_form', '=', 'Incoming')->value('total_amount');
		$approvedoutgoing = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '1')->where('payment_form', '=', 'Outgoing')->value('total_amount');

		if ($approvedincoming == "") {
			$approved = 0;
		} else {
			$approved = $approvedincoming;
		}

		if ($approvedoutgoing == "") {
			$outapproved = 0;
		} else {
			$outapproved = $approvedoutgoing;
		}

		/*Approved statistics*/

		/* Decline statistics*/
		$declineincoming = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '2')->where('payment_form', '=', 'Incoming')->value('total_amount');
		$declineoutgoing = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '2')->where('payment_form', '=', 'Outgoing')->value('total_amount');
		if ($declineincoming == "") {
			$decline = 0;
		} else {
			$decline = $declineincoming;
		}

		if ($declineoutgoing == "") {
			$outdecline = 0;
		} else {
			$outdecline = $declineoutgoing;
		}

		/* Decline statistics*/

		$paymentType = array('Over Due', 'Cuurent Due', 'Future Due');
		/*Approved incoming Graph statistics*/
		$over_dueapprove = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date < DATE(NOW()) AND due_date <= TIME(NOW())")->select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '1')->where('payment_form', '=', 'Incoming')->value('total_amount');
		$past_dueapprove = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >=  DATE(NOW()) && due_date <=  DATE_ADD(CURDATE(), interval 15 DAY)")->where('account_status', '=', '1')->select(DB::raw('SUM(invoice_amount) as total_amount'))->where('payment_form', '=', 'Incoming')->value('total_amount');
		$future_dueapprove = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >= DATE_ADD(CURDATE(), interval 16 DAY)")->select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '1')->where('payment_form', '=', 'Incoming')->value('total_amount');

		if ($over_dueapprove == "") {
			$overdue_approve = 0;
		} else {
			$overdue_approve = $over_dueapprove;
		}
		if ($past_dueapprove == "") {
			$pastdue_approve = 0;
		} else {
			$pastdue_approve = $past_dueapprove;
		}
		if ($future_dueapprove == "") {
			$futuredue_approve = 0;
		} else {
			$futuredue_approve = $future_dueapprove;
		}

		$datechart = Charts::create('bar', 'highcharts')
			->title('Incoming - Approved Due Amount')
			->yAxisTitle('<b>' . 'Total Amount' . '</b>')
			->xAxisTitle('<b>' . 'Due Type' . '</b>')
			->elementLabel('Incoming - Approved Due Amount')
			->labels([$paymentType[0], $paymentType[1], $paymentType[2]])
			->values([$overdue_approve, $pastdue_approve, $futuredue_approve])
			->dimensions(100, 100)
			->responsive(true);
		/*Approved incoming Graph statistics*/

		/*Decline incoming Graph statistics*/
		$over_duedecline = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date < DATE(NOW()) AND due_date <= TIME(NOW())")->select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '2')->where('payment_form', '=', 'Incoming')->value('total_amount');

		$past_duedecline = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >=  DATE(NOW()) && due_date <=  DATE_ADD(CURDATE(), interval 15 DAY)")->select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '2')->where('payment_form', '=', 'Incoming')->value('total_amount');

		$future_duedecline = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >= DATE_ADD(CURDATE(), interval 16 DAY)")->select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '2')->where('payment_form', '=', 'Incoming')->value('total_amount');

		if ($over_duedecline == "") {
			$overdue_decline = 0;
		} else {
			$overdue_decline = round($over_duedecline, 2);
		}
		if ($past_duedecline == "") {

			$pastdue_decline = 0;
		} else {
			$pastdue_decline = round($past_duedecline, 2);
		}
		if ($future_duedecline == "") {
			$futuredue_decline = 0;
		} else {
			$futuredue_decline = round($future_duedecline, 2);
		}

		$datechartdecline = Charts::create('bar', 'highcharts')
			->title('Incoming - Declined Due Amount')
			->yAxisTitle('<b>' . 'Total Amount' . '</b>')
			->xAxisTitle('<b>' . 'Due Date Type' . '</b>')
			->elementLabel('Incoming - Declined Due Amount')
			->labels([$paymentType[0], $paymentType[1], $paymentType[2]])
			->values([$overdue_decline, $pastdue_decline, $futuredue_decline])
			->dimensions(100, 100)
			->responsive(true);
		/*Decline incoming Graph statistics*/

		/*Approved outgoing Graph statistics*/
		$over_dueapproveout = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date < DATE(NOW()) AND due_date <= TIME(NOW())")->select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '1')->where('payment_form', '=', 'Outgoing')->value('total_amount');

		$past_dueapproveout = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >=  DATE(NOW()) && due_date <=  DATE_ADD(CURDATE(), interval 15 DAY)")->where('account_status', '=', '1')->where('payment_form', '=', 'Outgoing')->value('total_amount');
		$future_dueapproveout = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >= DATE_ADD(CURDATE(), interval 16 DAY)")->where('account_status', '=', '1')->where('payment_form', '=', 'Outgoing')->value('total_amount');

		if ($over_dueapproveout == "") {
			$overdue_outapprove = 0;
		} else {
			$overdue_outapprove = $over_dueapproveout;
		}
		if ($past_dueapproveout == "") {
			$pastdue_outapprove = 0;
		} else {
			$pastdue_outapprove = $past_dueapproveout;

		}
		if ($future_dueapproveout == "") {
			$futuredue_outapprove = 0;
		} else {
			$futuredue_outapprove = $future_dueapproveout;
		}

		$datechartapproveout = Charts::create('bar', 'highcharts')
			->title('Outgoing - Approved Due Amount')
			->yAxisTitle('<b>' . 'Total Amount' . '</b>')
			->xAxisTitle('<b>' . 'Due Date Type' . '</b>')
			->elementLabel('Outgoing - Approved Due Amount')
			->labels([$paymentType[0], $paymentType[1], $paymentType[2]])
			->values([$overdue_outapprove, $pastdue_outapprove, $futuredue_outapprove])
			->dimensions(100, 100)
			->responsive(true);
		/*Approved outgoing Graph statistics*/

		/*Decline outgoing Graph statistics*/
		$over_duedeclineout = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date < DATE(NOW()) AND due_date <= TIME(NOW())")->select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '2')->where('payment_form', '=', 'Outgoing')->value('total_amount');

		$past_duedeclineout = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >=  DATE(NOW()) && due_date <=  DATE_ADD(CURDATE(), interval 15 DAY)")->select(DB::raw('SUM(invoice_amount) as total_amount'))->where('account_status', '=', '2')->where('payment_form', '=', 'Outgoing')->value('total_amount');

		$future_duedeclineout = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->whereRaw("due_date >= DATE_ADD(CURDATE(), interval 16 DAY)")->where('account_status', '=', '2')->where('payment_form', '=', 'Outgoing')->value('total_amount');
		$paymentType = array('Over-Due', 'Current-Due', 'Future-Due');

		if ($over_duedeclineout == "") {
			$overdue_outdecline = 0;
		} else {
			$overdue_outdecline = $over_duedeclineout;
		}
		if ($past_duedeclineout == "") {
			$pastdue_outdecline = 0;
		} else {
			$pastdue_outdecline = $past_duedeclineout;

		}
		if ($future_duedeclineout == "") {
			$futuredue_outdecline = 0;
		} else {
			$futuredue_outdecline = $future_duedeclineout;
		}

		$datechartdeclineout = Charts::create('bar', 'highcharts')
			->title('Outgoing - Declined Due Amount')
			->yAxisTitle('<b>' . 'Total Amount' . '</b>')
			->xAxisTitle('<b>' . 'Due Date Type' . '</b>')
			->elementLabel('Outgoing - Declined Due Amount')
			->labels([$paymentType[0], $paymentType[1], $paymentType[2]])
			->values([$overdue_outdecline, $pastdue_outdecline, $futuredue_outdecline])
			->dimensions(100, 100)
			->responsive(true);
		/*Decline outgoing Graph statistics*/

		return view('account/payments/summary', compact('incoming', 'outgoing', 'approvedtotal', 'approved', 'decline', 'outapproved', 'outdecline', 'over_dueapprove', 'past_dueapprove', 'future_dueapprove', 'over_duedecline', 'past_duedecline', 'future_duedecline', 'over_dueapproveout', 'past_dueapproveout', 'future_dueapproveout', 'over_duedeclineout', 'past_duedeclineout', 'future_duedeclineout', 'datechart', 'datechartdecline', 'datechartapproveout', 'datechartdeclineout'));
	}
	/*summary of account payment*/

	/*summary of payment*/
	public function payment_summary() {

		$totalincoming = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('payment_form', 'Incoming')->get();
		$totaloutgoing = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('payment_form', 'Outgoing')->get();

		if (!empty($totalincoming)) {
			if (!empty($totalincoming[0]->total_amount)) {
				$incoming = $totalincoming[0]->total_amount;

			} else {
				$incoming = 0;

			}

		}

		if (!empty($totaloutgoing)) {
			if (!empty($totaloutgoing[0]->total_amount)) {
				$outgoing = $totaloutgoing[0]->total_amount;

			} else {
				$outgoing = 0;

			}

		}

		$paidin = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('payment_status', '=', '1')->where('payment_form', 'Incoming')->get();

		$unpaidin = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('payment_status', '=', '0')->where('payment_form', 'Incoming')->get();

		$paidoutg = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('payment_status', '=', '1')->where('payment_form', 'Outgoing')->get();

		$unpaidoutg = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'))->where('payment_status', '=', '0')->where('payment_form', 'Outgoing')->get();

		$paid = Payment::select('payment_types.name', DB::raw('SUM(invoice_amount) as total'))->join('payment_types', 'payments.payment_sub_type', '=', 'payment_types.id')->where('payment_status', '=', '1')->where('payment_form', '=', 'Incoming')->groupBy('payment_types.name')->get();

		$unpaid = Payment::select('payment_types.name', DB::raw('SUM(invoice_amount) as total'))->join('payment_types', 'payments.payment_sub_type', '=', 'payment_types.id')->where('payment_status', '=', '0')->where('payment_form', '=', 'Incoming')->groupBy('payment_types.name')->get();

		$paidout = Payment::select('payment_types.name', DB::raw('SUM(invoice_amount) as total'))->join('payment_types', 'payments.payment_sub_type', '=', 'payment_types.id')->where('payment_status', '=', '1')->where('payment_form', '=', 'Outgoing')->groupBy('payment_types.name')->get();

		$unpaidout = Payment::select('payment_types.name', DB::raw('SUM(invoice_amount) as total'))->join('payment_types', 'payments.payment_sub_type', '=', 'payment_types.id')->where('payment_status', '=', '0')->where('payment_form', '=', 'Outgoing')->groupBy('payment_types.name')->get();

		$paidouttotal = array();
		$paidoutname = array();
		if (empty($paidout)) {
			$paidouttotal = 0;
			$paidoutname = '';
		} else {
			foreach ($paidout as $value) {
				if ($value->total == '') {
					$total[] = 0;
					$paidoutname = '';
				} else {
					$paidouttotal[] = $value->total;
					$paidoutname[] = $value->name;
				}
			}
		}

		$paidchartout = Charts::create('donut', 'highcharts')
			->title('Paid Amount')
			->elementLabel("Total")
			->labels($paidoutname)
			->values($paidouttotal)
			->dimensions(100, 100)
			->responsive(true);

		$total = array();
		$name = array();
		if (empty($unpaidout)) {
			$total = 0;
			$name = '';
		} else {
			foreach ($unpaidout as $value) {
				if ($value->total == '') {
					$total[] = 0;
					$name = '';
				} else {
					$total[] = $value->total;
					$name[] = $value->name;
				}
			}
		}

		$unpaidchartout = Charts::create('donut', 'highcharts')
			->title('Unpaid Amount')
			->elementLabel("Total")
			->labels($name)
			->values($total)
			->dimensions(100, 100)
			->responsive(true);

		$paidtotal = array();
		$paidname = array();
		if (empty($paid)) {
			$paidtotal = 0;
			$paidname = '';
		} else {
			foreach ($paid as $value) {
				if ($value->total == '') {
					$paidtotal[] = 0;
					$paidname = '';
				} else {
					$paidtotal[] = $value->total;
					$paidname[] = $value->name;
				}
			}
		}

		$paidchart = Charts::create('donut', 'highcharts')
			->title('Paid Amount')
			->elementLabel("Total")
			->labels($paidname)
			->values($paidtotal)
			->dimensions(100, 100)
			->responsive(true);

		$unpaidtotal = array();
		$unpaidname = array();
		if (empty($unpaid)) {
			$unpaidtotal = 0;
			$unpaidname = '';
		} else {
			foreach ($unpaid as $value) {
				if ($value->total == '') {
					$unpaidtotal[] = 0;
					$unpaidname = '';
				} else {
					$unpaidtotal[] = $value->total;
					$unpaidname[] = $value->name;
				}
			}

		}

		$unpaidchart = Charts::create('donut', 'highcharts')
			->title('Unpaid Amount')
			->elementLabel("Total")
			->labels($unpaidname)
			->values($unpaidtotal)
			->dimensions(100, 100)
			->responsive(true);

		return view('payment/summary', compact('incoming', 'outgoing', 'paidin', 'paidunpaidtotal', 'paid', 'unpaidin', 'paidoutg', 'unpaidoutg', 'paidchart', 'unpaidchart', 'paidchartout', 'unpaidchartout'));

	}
	/*summary of payment*/

	public function show_summary($id) {

		$remaining = Payment::select('remaining_amount')->where('id', $id)->get();

		if (!empty($remaining)) {
			if (empty($remaining[0]->remaining_amount)) {
				$invoice = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'), 'invoice_amount', 'payment_form')->where('id', $id)->get();
				$total_amount = $invoice[0]->total_amount;
				$paid = 0;
				$unpaid = $invoice[0]->invoice_amount;
				$payment_form = "Incoming";

			} else if ($remaining[0]->remaining_amount == '0.00') {
				$invoice = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'), 'invoice_amount', 'payment_form')->where('id', $id)->get();
				$total_amount = $invoice[0]->total_amount;
				$paid = $invoice[0]->invoice_amount;
				$payment_form = $invoice[0]->payment_form;
				$unpaid = 0;
			} else {
				$remaining = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'), 'remaining_amount', 'payment_form')->where('id', $id)->get();
				$invoce = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'), 'invoice_amount', 'payment_form')->where('id', $id)->get();

				$total_amount = $remaining[0]->total_amount;
				$paid = $invoce[0]->invoice_amount - $remaining[0]->remaining_amount;
				$payment_form = $remaining[0]->payment_form;
				$unpaid = $remaining[0]->remaining_amount;

			}
		}
		return view('payment/show_summary', compact('paid', 'unpaid', 'payment_form', 'total_amount'));
	}

	public function account_summary($id) {

		$total = Payment::select(DB::raw('SUM(invoice_amount) as total_amount'), 'invoice_amount', 'payment_form', 'account_status')->where('id', $id)->get();

		$over_due = Payment::select(DB::raw('due_date AS OverDue'))->whereRaw("due_date <= DATE(NOW()) AND due_date <= TIME(NOW())")->where('id', $id)->get();

		$past_due = Payment::select(DB::raw('due_date AS CuurentDue'))->whereRaw("due_date >=  DATE(NOW()) && due_date <=  DATE_ADD(CURDATE(), interval 15 DAY)")->where('id', $id)->get();
		$future_due = Payment::select(DB::raw('due_date AS FutureDue'))->whereRaw("due_date >= DATE_ADD(CURDATE(), interval 16 DAY)")->where('id', $id)->get();

		if (empty($total)) {

		} else {
			if (empty($total[0]->total_amount)) {
				$due = "Over-Due";

				return view('account/payments/account_summary', compact('total', 'due'))->with('errors', 'Data not Available');
			} else {
				if (!empty($over_due)) {
					$due = "Over-Due";

					if (!empty($over_due[0]->OverDue)) {
						$due = "Over-Due";
					} else {
						$due = "Over-Due";
					}

				}

				if (!empty($past_due)) {
					$due = "Current-Due";

					if (!empty($past_due[0]->CuurentDue)) {
						$due = "Current-Due";
					} else {
						$due = "Current-Due";
					}

				}

				if (!empty($future_due)) {
					$due = "Future-Due";

					if (!empty($future_due[0]->FutureDue)) {
						$due = "Future-Due";
					} else {
						$due = "Future-Due";
					}

				}

			}

		}

		return view('account/payments/account_summary', compact('total', 'due'));
	}

	/* Gold and Diamond summary*/
	public function gold_diamond() {

		$avg_rate = Metal::select(DB::raw('SUM(avg_rate) as avg_rate'))->get();

		$tran_gold = count(Metal::select('id')->get());
		$vendorname = config::get('constants.vendor_option.vendor_name');
		$getVendorId = App\User::select('id')->where('name', $vendorname)->value('id');

		//print_r($vendorname);exit;
		$gold_purchase = MetalTransaction::select(DB::raw('SUM(metal_weight) as total_gold'))->where('transaction_type', 1)->where('metal_type', 1)->where('vendor_id', '!=', $getVendorId)->value('total_gold');

		$gold_issue = MetalTransaction::select(DB::raw('SUM(metal_weight) as total_gold'))->where('transaction_type', 2)->where('metal_type', 1)->value('total_gold');

		if (empty($avg_rate[0]->avg_rate)) {
			$gold_ave_rate = 0;
		} else {
			$gold_ave_rate = $avg_rate[0]->avg_rate;
		}

		if ($gold_purchase == "") {

			$total_gold_purchase = 0;

		} else {

			$total_gold_purchase = $gold_purchase;
		}

		if ($gold_issue == "") {

			$total_gold_issue = 0;

		} else {

			$total_gold_issue = $gold_issue;
		}

		$total_gold = $total_gold_purchase - $total_gold_issue;

		/*igst count for total gold */
		$beforgstin = round($total_gold * $gold_ave_rate, 2);
		$amount = $this->count_igst_amount($beforgstin);
		/*igst count for total gold */

		$purcahse = round($gold_purchase, 2);

		/*igst count for issue */
		$issueamount = round($total_gold_issue * $gold_ave_rate, 2);
		$issue = $this->count_igst_amount($issueamount);
		/*igst count for issue */

		/*igst count for purchase */
		$beforgstinpurchase = round($total_gold_purchase * $gold_ave_rate, 2);
		$igstpurchase = $this->count_igst_amount($beforgstinpurchase);
		$purcahsetotal = $igstpurchase - $issue;
		/*igst count for purchase */

		$gold_pending = $purcahse - $total_gold_issue;

		$name = array('Instock' . '<br>(' . $gold_pending . ' gms)', 'Issue' . '<br>(' . round($total_gold_issue, 2) . ' gms)');

		$goldchart = Charts::create('bar', 'highcharts')
			->title(ucwords("Valuation Of Gold"))
			->yAxisTitle('<b>' . 'Total Amount' . '</b>')
			->xAxisTitle('<b>' . 'Stock Type' . '</b>')
			->elementLabel('Valuation Of Gold')
			->labels($name)
			->values([$purcahsetotal, $issue])
			->dimensions(100, 100)
			->responsive(true);

		/*diamond summry*/
		$tran = count(DiamondInventory::select('id')->get());
		$daimond = DiamondInventory::select(DB::raw('SUM(ave_rate) as total_ave_rate'))->get();
		$total_diamond = DiamondInventory::select(DB::raw('SUM(total_diamond_weight) as total_diamond'))->value('total_diamond');
		$miscloss = DiamondTransaction::select(DB::raw('SUM(diamond_weight) as total_diamond'))->where('transaction_type', 7)->value('total_diamond');
		//print_r($misc);exit;
		$daimond_purchase = DiamondTransaction::select(DB::raw('SUM(diamond_weight) as total_diamond'))->where('transaction_type', 1)->value('total_diamond');

		$daimond_issue = DiamondTransaction::select(DB::raw('SUM(diamond_weight) as total_diamond'))->where('transaction_type', 2)->value('total_diamond');
		$sells = DiamondTransaction::select(DB::raw('SUM(diamond_weight) as total_diamond'))->where('transaction_type', 6)->value('total_diamond');

		if (empty($daimond[0]->total_ave_rate)) {
			$total_ave_rate = 0.00;
		} else {
			$total_ave_rate = $daimond[0]->total_ave_rate / $tran;
		}
		if (empty($miscloss)) {
			$misc = 0.00;
		} else {
			$misc = round($miscloss);
		}
		if (empty($sells)) {
			$sell = 0.00;
		} else {
			$sell = round($sells);
		}
		if (empty($daimond_issue)) {
			$total_issue = 0.00;
			$issue_diamond = 0.00;

		} else {

			$beforgstindissue = round($daimond_issue * $total_ave_rate, 2);
			$total_issue = $this->count_igst_amount($beforgstindissue);
			$issue_diamond = round($daimond_issue);
		}

		if (empty($daimond_purchase)) {
			$total_purchase = 0.00;
			$purchase_diamond = 0.00;

		} else {
			/*igst count for total diamond */
			$beforgstindpurchase = round($daimond_purchase * $total_ave_rate, 2);
			$total_purchase = $this->count_igst_amount($beforgstindpurchase);
			/*igst count for total diamond */

			$purchase_diamond = round($daimond_purchase, 2);

		}
		$diamond_total = round($total_diamond, 2);
		/*igst count for total diamond */
		$beforgstindiamond = round($total_diamond * $total_ave_rate, 2);
		$total_diamond_amount = $this->count_igst_amount($beforgstindiamond);
		/*igst count for total diamond */

		/*igst count for sell */
		$beforsell = round($sell * $total_ave_rate, 2);
		$sell_amount = $this->count_igst_amount($beforsell);
		/*igst count for sell */

		/*igst count for misc */
		$beforgstindpending = round($misc * $total_ave_rate, 2);
		$pending_amount = $this->count_igst_amount($beforgstindpending);
		/*igst count for misc */

		$dname = array('IN Stock' . '<br>(' . $diamond_total . ' cts)', 'Issue' . '<br>(' . $issue_diamond . ' cts)', 'Sell' . '<br>(' . $sell . ' cts)', 'Misc' . '<br>(' . $misc . ' cts)');

		$diamondchart = Charts::create('bar', 'highcharts')
			->title(ucwords("Valuation Of Diamond"))
			->yAxisTitle('<b>' . 'Total Amount' . '</b>')
			->xAxisTitle('<b>' . 'Stock Type' . '</b>')
			->elementLabel('Valuation Of Diamond')
			->labels($dname)
			->values([$total_diamond_amount, $total_issue, $sell_amount, $pending_amount])
			->dimensions(100, 100)
			->colors(['#3aafff', '#ff5c4f', 'yellow', 'orange'])
			->responsive(true);
		/*diamond summry*/

		return view('account/payments/gold_diamond_summary', compact('total_gold', 'amount', 'diamond_total', 'goldchart', 'diamondchart', 'total_diamond_amount'));
	}
	/* Gold and Diamond summary*/

	/*Pdf download from payment list*/
	public function pdflisting(Request $request) {

		$data = Payment::select('invoice_attachment')->where('id', $request->id)->value('invoice_attachment');

		if ($data != "") {

			$exactFilePath = base_path("public" . '/' . config('constants.dir.purchased_invoices')) . $data;
			//var_dump($exactFilePath);exit;
			if (file_exists($exactFilePath)) {
				return Response()->download($exactFilePath);
			} else {
				return back()->with('errors', "Purchase Invoice Not Found");
			}
			//
		} else {
			return back()->with('errors', "Purchase Invoice Not Found");
		}
	}
	/*Pdf download from payment list*/

	/*igst count for diamond & gold summary */
	public function count_igst_amount($amount_befor_igst) {

		$igst = $amount_befor_igst * 0.03;
		$after_igst = $amount_befor_igst + $igst;

		return $after_igst;
	}

	/*igst count for diamond & gold summary */

	public function createAdvancePayment() {
		$paymenttype = PaymentType::select('id', 'name', 'parent_id')->where('parent_id', '!=', '0')->get();
		return view('/account/payments/createadvancepayment', compact('paymenttype', $paymenttype));
	}
	//create new customer for advance payment
	public function createCustomer(Request $request) {
		$params = $request->post();
		$firstName = isset($params['first_name']) ? $params['first_name'] : '';
		$lastName = isset($params['last_name']) ? $params['last_name'] : '';
		$contactNumber = isset($params['contact_number']) ? $params['contact_number'] : '';
		$address = isset($params['address']) ? $params['address'] : '';
		$country = isset($params['country_id']) ? $params['country_id'] : '';
		$state = isset($params['getstate']) ? $params['getstate'] : '';
		$city = isset($params['city']) ? $params['city'] : '';
		$address = isset($params['address']) ? $params['address'] : '';
		$zipCode = isset($params['zip']) ? $params['zip'] : '';
		$email = isset($params['email']) ? $params['email'] : '';
		$frnCode = isset($params['txtfrncode']) ? $params['txtfrncode'] : '';
		$password = $firstName . rand(10000, 999) . '@dealer';

		//Check frn code exit
		$isfrnExist = InventoryHelper::checkFRNCodeValidation('', $frnCode);

		if ($isfrnExist) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_frncode_already_exist');
			echo json_encode($response);exit;
			//return ['result' => $response];
		}

		if (!empty($frnCode)) {
			$frncodeStr = '&frncode=' . $frnCode;
		} else {
			$frncodeStr = '';
		}

		$customerParams = 'firstname=' . $firstName . '&lastname=' . $lastName . '&email=' . $email . '&contact_number=' . $contactNumber . '&community=' . '1' . '&street=' . $address . '&country_id=' . $country . '&region=' . $state . '&city=' . $city . '&entity_customer=' . '1' . '&postcode=' . $zipCode . '&password=' . $password . '&confirmation=' . '1' . '&franchisee_status=' . '2' . $frncodeStr;
		if (App::environment('local')) {
			$url = Config::get('app.create_customer');
		} else if (App::environment('test')) {
			$url = Config::get('constants.apiurl.test.create_customer');
		} else {
			$url = Config::get('constants.apiurl.live.create_customer');
		}
		//check contact number exist
		$isContactNumberExist = InventoryHelper::checkContactNumberValidation('', $contactNumber);
		if ($isContactNumberExist) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_telephone_already_exist');
			echo json_encode($response);exit;
		}
		DB::setTablePrefix('dml_');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $customerParams);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$res = json_decode($result);
		if (isset($res->status) && $res->status == 'success') {
			$customerId = $res->customer_id;
			$response['status'] = true;
			$response['customer_id'] = $customerId;
			$response['customer_name'] = $firstName . " " . $lastName;
			$response['message'] = Config::get('constants.message.customer_created_successfully');
		} else {
			$response['status'] = false;
			$response['message'] = $res->message;
		}
		echo json_encode($response);exit;
	}
	public function storeAdvancePayment(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$amount = isset($params['invoice_amount']) ? $params['invoice_amount'] : '';
		$paymentForm = isset($params['payment_form']) ? $params['payment_form'] : '';
		$paymentSubType = isset($params['payment_sub_type']) ? $params['payment_sub_type'] : '';
		$paymentParentType = isset($params['payment_type']) ? $params['payment_type'] : '';
		$customerType = isset($params['customer_type']) ? $params['customer_type'] : '';
		$remarks = isset($params['remarks']) ? $params['remarks'] : '';
		$createdBy = Auth::user()->id;
		if (empty($customerId)) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.payment_customer_not_selected');
			echo json_encode($response);exit;
		}
		if (empty($remarks)) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.payment_customer_remark_required');
			echo json_encode($response);exit;
		}
		if (!empty($customerId)) {
			CustomerWallet::create(array('customer_id' => $customerId, 'transaction_amt' => $amount, 'transaction_type' => 'credit', 'remarks' => $remarks, 'created_by' => $createdBy, 'created_at' => date('Y-m-d'), 'updated_at' => date('Y-m-d')));
			$transactionId = DB::getPdo()->lastInsertId();

		}
		if (!empty($transactionId)) {
			$response['status'] = true;
			$response['message'] = Config::get('constants.message.payment_adv_payment_created_successfully');
		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;

	}
	//GEt payment transactions
	public function getPaymentTransaction(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$paymentId = isset($params['payment_id']) ? $params['payment_id'] : '';
		if (!empty($customerId) && !empty($paymentId)) {
			$transaction = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->where('payment_id', $paymentId)->orderBy('id', 'DESC')->get();
			$totalCount = $transaction->count();
			$transactionData = $transaction->take(10);
			$html = View::make('customers.paymenttransaction', compact('transactionData', 'totalCount', 'paymentId'))->render();
			$response['status'] = true;
			$response['html'] = $html;
		} else {
			$response['status'] = false;
		}
		echo json_encode($response);exit;
	}
	public function generateCashVoucher(Request $request) {

		$params = $request->post();
		$paymentId = isset($params['payment_id']) ? $params['payment_id'] : '';
		$status = isset($params['paymentstatus']) ? $params['paymentstatus'] : '';

		if (!empty($paymentId)) {
			//get invoice number
			$invoice = Payment::select('invoice_number')->where('id', '=', DB::raw("$paymentId"))->get()->first();
			$invoiceNumber = isset($invoice->invoice_number) ? $invoice->invoice_number : '';
			//get invoice entity_id
			DB::setTablePrefix('');
			$invoice = DB::table('sales_flat_invoice')->select('entity_id')->where('increment_id', '=', DB::raw("'$invoiceNumber'"))->get()->first();
			$invoiceId = isset($invoice->entity_id) ? $invoice->entity_id : '';
			DB::setTablePrefix('dml_');
			$result = CashVoucher::where('invoice_id', $invoiceId)->update(array('status' => $status));
			if ($result) {
				$response['status'] = true;
				$response['invoice_id'] = $invoiceId;
			} else {
				$response['status'] = false;
				$response['message'] = config('constants.message.inventory_default_failure_message');
			}
		} else {
			$response['status'] = false;
			$response['message'] = config('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}
	//download cash voucher
	public function viewCashVoucher($id) {
		if (!empty($id)) {
			$voucher = CashVoucher::select('id')->where('invoice_id', '=', DB::raw("$id"))->get()->first();
			$voucherId = isset($voucher->id) ? $voucher->id : '';
			return InventoryHelper::generateCashVoucher($voucherId);
		}
	}

	public function payment_daily_report(request $request) {
		$currentdate = date("Y-m-d");
		$paymentdatas = Payment::where('created_at', 'LIKE', "%" . $currentdate . "%")->orderBy('created_at')->paginate(10);
		$totalcount = Payment::where('created_at', 'LIKE', "%" . $currentdate . "%")->count();
		$headerdate = date("d-m-Y");

		$invoiceAmtArr = Payment::select('invoice_amount')->where('created_at', 'LIKE', "%" . $currentdate . "%")->get();
		$total_invoice_amt = 0;
		foreach ($invoiceAmtArr as $key => $invoiceAmt) {
			$total_invoice_amt += $invoiceAmt->invoice_amount;
		}

		$total_deposite_amt = 0;
		$depositeAmtArr = Payment::select('invoice_amount')->where('payment_status', 1)->get();
		foreach ($depositeAmtArr as $key => $depositeAmt) {
			$total_deposite_amt += $depositeAmt->invoice_amount;
		}

		$total_pending_amt = 0;
		$pendingAmtArr = Payment::where('payment_status', 0)->get();
		foreach ($pendingAmtArr as $key => $pendingAmt) {
			if (!empty($pendingAmt->remaining_amount)) {
				$total_pending_amt += $pendingAmt->remaining_amount;
			} else {
				$total_pending_amt += $pendingAmt->invoice_amount;
			}
		}

		return view('payment.payment_daily_report', compact('headerdate', 'paymentdatas', 'totalcount', 'total_invoice_amt', 'total_deposite_amt', 'total_pending_amt'))->with('i', ($request->input('page', 1) - 1) * 5);
	}

	public function daily_report_response(request $request) {
		$currentdate = date("Y-m-d");
		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'remaining_amount');
		$maindata = Payment::where('created_at', 'LIKE', "%" . $currentdate . "%")->orderBy('id', 'DESC');
		$totalData = $maindata->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		$data = array();
		$params = $request->post();
		$maindata = Payment::where('created_at', 'LIKE', "%" . $currentdate . "%")->orderBy($order, $dir);
		if (!empty($request['textfilter'])) {
			$maindata = Payment::where('created_at', 'LIKE', "%" . $request['textfilter'] . "%");
		}
		if (!empty($request->input('search.value'))) {
			$search = $request->input('search.value');
			$maindata = $maindata->whereRaw('(customer_name LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%" OR remaining_amount LIKE "%' . $search . '%") ');
		}
		$datacount = $maindata->count();
		$datacoll = $maindata->offset($start)->limit($limit)->orderBy($order, $dir)->get();
		$datacollection = $datacoll;
		$data["draw"] = intval($request->input('draw'));
		$data["recordsTotal"] = $datacount;
		$data["recordsFiltered"] = $datacount;
		$data['deferLoading'] = $datacount;
		if (count($datacollection) > 0) {
			$invoice_amt = 0;
			foreach ($datacollection as $key => $dailyColl) {

				$data['data'][] = array(++$start, $dailyColl->customer_name, $dailyColl->invoice_number, CommonHelper::covertToCurrency($dailyColl->invoice_amount), CommonHelper::covertToCurrency($dailyColl->remaining_amount));
				$invoice_amt += $dailyColl->invoice_amount;
				$data['invoice_amt'] = CommonHelper::covertToCurrency($invoice_amt);
			}
		} else {
			$data['data'][] = array('', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
}