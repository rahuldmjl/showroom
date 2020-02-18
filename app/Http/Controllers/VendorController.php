<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;
use App\Costingdata;
use App\Payment;
use App\PaymentType;
use App\PaymentTransaction;
use App\Helpers\CommonHelper;
use Auth;
use Config;
use Spatie\Permission\Models\Role;
use Hash;
use Validator;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $vendor=User::whereHas('roles', function($q){ $q->where('name', 'Vendor'); })->orderBy('id','DESC')->paginate(10);  
      

      $vendortotal= User::whereHas('roles', function ($q) {$q->where('name', 'Vendor');});
        $totalcount=User::whereHas('roles', function($q){ $q->where('name', 'Vendor'); })->count();
      
        return view('vendor/index',compact('vendor','totalcount'));
    }

    public function view($id) {
        if (!empty($id)) {
            $vendor=User::whereHas('roles', function($q){ 
                $q->where('name', 'Vendor'); 
            })->where('id',$id)->get();

            $VendorArray = $vendor->toArray();

            $vendorName = $VendorArray[0]['name'];
            $vendorEmail = $VendorArray[0]['email'];
            $vendorDMcode = $VendorArray[0]['vendor_dmcode'];
            $vendorGstin = $VendorArray[0]['gstin'];
            $vendorState = $VendorArray[0]['state'];
            $vendorPhone = $VendorArray[0]['phone'];
            $vendorAddress = $VendorArray[0]['address'];
            /* echo "<pre>";
            print_r($VendorArray);
            exit; */
            //Given Diamond
            $VendorDiamodIssueCnt = DB::select(DB::raw("SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND USER.id = ".$id." GROUP BY issue_voucher_no ORDER BY id DESC"));
            $GivenDiamondColl = DB::select(DB::raw("SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number,SUM(diamond_weight) as total_weight, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND USER.id = ".$id." GROUP BY issue_voucher_no ORDER BY id DESC limit 10"));
            $VendorDiamodIssueCnt = count($VendorDiamodIssueCnt);

            //Return Diamond
            $VendorDiamondRetunColl = DB::select(DB::raw("SELECT stone_shape,diamond_weight,sieve_size,mm_size,issue_voucher_no,diamond.updated_at, po_number, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 12 AND USER.id = ".$id." ORDER BY diamond.id DESC"));
            $VendorDiamodReturnCnt = count($VendorDiamondRetunColl);

            //Gold Given List
            $VendorGoldGivenColl = DB::select(DB::raw("SELECT 'gold' AS TYPE,USER.name,gold.amount_paid as amount_paid,gold.id,gold.metal_weight AS metal_weight,gold.gold_type AS gold_type,gold.issue_date AS purchased_at,gold.vendor_id AS gold_vendor,gold.issue_voucher_no AS gold_voucher_no,gold.po_number AS gold_po,gold.purchased_invoice AS gold_voucher FROM dml_metal_transactions gold JOIN dml_users USER ON gold.vendor_id = USER.id WHERE gold.transaction_type = 2 AND USER.id = ".$id." GROUP BY gold_voucher ORDER BY id DESC"));
            $VendorGoldGivenCnt = count($VendorGoldGivenColl);
            
            //Gold Return List
            $VendorGoldReturnColl = DB::select(DB::raw("SELECT 'gold' AS TYPE,USER.name,gold.amount_paid as amount_paid,gold.id,gold.metal_weight AS metal_weight,gold.gold_type AS gold_type,gold.issue_date AS purchased_at,gold.vendor_id AS gold_vendor,gold.issue_voucher_no AS gold_voucher_no,gold.po_number AS gold_po,gold.purchased_invoice AS gold_voucher FROM dml_metal_transactions gold JOIN dml_users USER ON gold.vendor_id = USER.id WHERE gold.transaction_type = 12 AND USER.id = ".$id." ORDER BY id DESC"));
            $VendorGoldReturnCnt = count($VendorGoldReturnColl);

            //Costing Accepted List
            $CostingAccepted = Costingdata::where('qc_status',1)->where('request_invoice','!=' , 1)->orderBy('id', 'DESC')->get();
            $Acceptedtotalcount = Costingdata::where('qc_status',1)->where('request_invoice','!=' , 1)->count();

            //Costing Rejected List
            $CostingRejected = Costingdata::where('qc_status',0)->where('return_memo','!=' , 1)->orderBy('id', 'DESC')->get();
            $totalcountReject = Costingdata::where('qc_status',0)->where('return_memo','!=' , 1)->count();
            
            //Paid Inovice List
            $paid = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_status', '=', '1')->orderBy('id', 'DESC')->distinct()->paginate(10);
            $paidtotalcount = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_status', '=', '1')->orderBy('id', 'DESC')->count();
            $i=0;

            //Unpaid Invoice List
            $UnpaidCollList = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where(function ($query) {$query->where('payment_form', '=', 'Incoming')->orWhere('payment_form', '=', 'Outgoing');})->where('account_status', '=', '1')->where('payment_status', '=', '0')->where('customer_type', '=', 'System')->where('customer_id', '=', $id)->orderBy('id', 'DESC')->distinct()->get();
            $totalcount = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where(function ($query){$query->where('payment_form', '=', 'Incoming')->orWhere('payment_form', '=', 'Outgoing');})->where('customer_type', '=', 'System')->where('account_status', '=', '1')->where('payment_status', '=', '0')->where('customer_id', '=', $id)->orderBy('id', 'DESC')->count();
            
            //Payment History List
            $PaymentHistoryList = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where(function ($query) {$query->where('payment_form', '=', 'Incoming')->orWhere('payment_form', '=', 'Outgoing');})->where('account_status', '=', '1')->where('customer_id', '=', $id)->orderBy('created_at', 'DESC')->distinct()->get();
            $totalPaymentListCnt = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where(function ($query) {$query->where('payment_form', '=', 'Incoming')->orWhere('payment_form', '=', 'Outgoing');})->where('account_status', '=', '1')->where('customer_id', '=', $id)->orderBy('created_at', 'DESC')->distinct()->count();
            }
        return view('vendor.view', compact('id','vendorName','vendorEmail','vendorDMcode','vendorGstin','vendorState','vendorPhone','vendorAddress','VendorDiamodIssueCnt','GivenDiamondColl','VendorDiamondRetunColl','VendorDiamodReturnCnt','VendorGoldGivenColl','VendorGoldGivenCnt','VendorGoldReturnColl','VendorGoldReturnCnt','CostingAccepted','Acceptedtotalcount','CostingRejected','totalcountReject','paid','paidtotalcount','i','UnpaidCollList','totalcount','PaymentHistoryList','totalPaymentListCnt'));
    }

    public function unpaidshow(Request $request, $id) {
        $transaction = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->where('payment_id', $id)->orderBy('id', 'DESC')->paginate();
		$totalcount = $transaction->count();
		return view('vendor/vendorunpaidtransaction', compact('transaction', 'totalcount', 'totalamount'))->with('i', ($request->input('page', 1) - 1) * 5);
    }

    public function paidshow(Request $request, $id) {
        $transaction = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->where('payment_id', $id)->orderBy('id', 'DESC')->paginate();
		$totalcount = $transaction->count();
		return view('vendor/vendorpaidtransaction', compact('transaction', 'totalcount', 'totalamount'))->with('i', ($request->input('page', 1) - 1) * 5);
    }
    
    public function vendor_paid_transaction(Request $request) {
		$transaction = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->orderBy('id', 'DESC')->distinct()->paginate();

		$totalcount = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->orderBy('id', 'DESC')->count(10);
		return view('vendor/vendorpaidtransaction', compact('transaction', 'totalcount '))->with('i', ($request->input('page', 1) - 1) * 5);

    }
    
    //Ajax Call For Payment History List 
    public function vendor_paymenthistresponse(Request $request) {
		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form',
            6 => 'name');
        $vendorID = $request->input('vendor_id');
        $results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
                    ->select('payments.*', 'payment_types.name')
                    ->where(function ($query) {
                        $query->where('payment_form', '=', 'Incoming')
                        ->orWhere('payment_form', '=', 'Outgoing');
                    })
                    ->where('account_status', '=', '1')
                    ->where('customer_id', '=', $vendorID)
                    ->orderBy('created_at', 'desc')
                    ->distinct();
                    
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
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where(function ($query) {$query->where('payment_form', '=', 'Incoming')->orWhere('payment_form', '=', 'Outgoing');})->where('account_status', '=', '1')->where('customer_id', '=', $vendorID)->orderByRaw($order . ' ' . $dir)->offset($start)
				->limit($limit)
				->orderByRaw($order . ' ' . $dir)->get();

		} else {
			$search = $request->input('search.value');
            $resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
                        ->select('payments.*', 'payment_types.name')
                        ->where(function ($query) {$query->where('payment_form', '=', 'Incoming')->orWhere('payment_form', '=', 'Outgoing');})
                        ->where('account_status', '=', '1')
                        ->where('customer_id', '=', $vendorID)
                        ->whereRaw('(customer_name LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
                            ->offset($start)
                            ->limit($limit)
                            ->orderByRaw($order . ' ' . $dir)->get();
			$totalFiltered = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where(function ($query) {$query->where('payment_form', '=', 'Incoming')->orWhere('payment_form', '=', 'Outgoing');})->where('account_status', '=', '1')->whereRaw('(customer_name LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
        }
        //echo $resultslist;exit;
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {
 				$action = '<a  href="' . action('PaymentController@show', $resultslist->id) . '" onclick=" "><i class="material-icons"  title="View Transaction">remove_red_eye</i></a>';
				$data[] = array(++$start, $resultslist->customer_name, $resultslist->invoice_number,CommonHelper::covertToCurrency($resultslist->invoice_amount), $resultslist->due_date, $resultslist->payment_form, $resultslist->name, $action);
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

    //Ajax Call For Unpaid Invoice List
    public function vendor_unpaidresponse(Request $request) {
		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form',
            6 => 'name');
        $vendorID = $request->input('vendor_id');
        $results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
                    ->select('payments.*', 'payment_types.name')
                    ->where(function ($query) {
                        $query->where('payment_form', '=', 'Incoming')
                        ->orWhere('payment_form', '=', 'Outgoing');
                    })
                    ->where('account_status', '=', '1')
                    ->where('payment_status', '=', '0')
                    ->where('customer_id', '=', $vendorID)
                    ->orderBy('id', 'desc')
                    ->distinct();
                    
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
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where(function ($query) {$query->where('payment_form', '=', 'Incoming')->orWhere('payment_form', '=', 'Outgoing');})->where('account_status', '=', '1')->where('payment_status', '=', '0')->where('customer_id', '=', $vendorID)->orderByRaw($order . ' ' . $dir)->offset($start)
				->limit($limit)
				->orderByRaw($order . ' ' . $dir)->get();

		} else {
			$search = $request->input('search.value');
            $resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
            ->select('payments.*', 'payment_types.name')
            ->where(function ($query) {$query->where('payment_form', '=', 'Incoming')->orWhere('payment_form', '=', 'Outgoing');})
            ->where('account_status', '=', '1')
            ->where('payment_status', '=', '0')
            ->where('customer_id', '=', $vendorID)
            ->whereRaw('(customer_name LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
				->offset($start)
				->limit($limit)
				->orderByRaw($order . ' ' . $dir)->get();
			$totalFiltered = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where(function ($query) {$query->where('payment_form', '=', 'Incoming')->orWhere('payment_form', '=', 'Outgoing');})->where('account_status', '=', '1')->where('payment_status', '=', '0')->whereRaw('(customer_name LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
        }
        //echo $resultslist;exit;
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {
 				$action = '<a  href="' . action('PaymentController@show', $resultslist->id) . '" onclick=" "><i class="material-icons"  title="View Transaction">remove_red_eye</i></a>';
				$data[] = array(++$start, $resultslist->customer_name, $resultslist->invoice_number,CommonHelper::covertToCurrency($resultslist->invoice_amount), $resultslist->due_date, $resultslist->payment_form, $resultslist->name, $action);
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

    public function vendorpaidpayment_response(Request $request) {
		$columns = array(
			0 => 'id',
			1 => 'customer_name',
			2 => 'invoice_number',
			3 => 'invoice_amount',
			4 => 'due_date',
			5 => 'payment_form',
			6 => 'name');
		$results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_status', '=', '1')->distinct();

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		if (empty($request->input('search.value'))) {
			$resultslist = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_status', '=', '1')->orderBy('id', 'DESC')->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = $results->whereRaw('(customer_name LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
			$totalFiltered = $results->whereRaw('(customer_name LIKE "%' . $search . '%" OR invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
		}
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {

				$action = '<a  href="' . action('VendorController@paidshow', $resultslist->id) . '" onclick=" "><i class="material-icons"  title="View Transaction">remove_red_eye</i></a>';
				$data[] = array(++$start, $resultslist->customer_name, $resultslist->invoice_number, $resultslist->invoice_amount, $resultslist->due_date, $resultslist->payment_form, $resultslist->name, $action);
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
    /* public function paid_payment(Request $request) {
		$paid = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_status', '=', '1')->orderBy('id', 'DESC')->distinct()->paginate(10);
		$totalcount = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_status', '=', '1')->orderBy('id', 'DESC')->count();
		return view('payment/paidpayment', compact('paid', 'totalcount'))->with('i', ($request->input('page', 1) - 1) * 5);

    } */
    
    /* public function unpaidinvoice(Request $request,$id) {
		$results = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')->where('payment_form', '=', 'Incoming')->where('payment_form', '=', 'Outgoing')->orderBy('id', 'DESC')->paginate(10);
		$paymenttype = PaymentType::all();
		$totalcount = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->select('payments.*', 'payment_types.name')->where('account_status', '=', '0')->where('payment_form', '=', 'Incoming')->orderBy('id', 'DESC')->count();
		return view('/account/payments/incoming', compact('paymenttype', 'results', 'totalcount'))->with('i', ($request->input('page', 1) - 1) * 5);

	} */

    // Details Display in popup For Costings List
    public function showDetail(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        $returnHTML = view('costing.showDetail',['id'=>$id])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML)); 
    }
    
    public function diamondissuelist(Request $request){
        $params = $request->post();
        $issue_voucher_no = isset($params['id']) ? $params['id'] : '';
        $VendorColl = DB::select(DB::raw("SELECT 'diamond' AS TYPE, NAME, diamond.id,diamond.amount_paid,stone_shape,pieces,diamond_quality, purchased_at, vendor_id, issue_voucher_no, po_number,USER.gstin,diamond_weight, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND diamond.issue_voucher_no = '".$issue_voucher_no."' ORDER BY id DESC"));
        $returnHTML = view('vendor.DiamondDetail', ['data' => $VendorColl])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));         
    }

    //Ajax Call For DataTable Costing Accepted
    public function costingaacceptedajaxlist(Request $request){
        $data = array();
        $params = $request->post();
        $columns = array(
            0 => 'sku',
			1 => 'certificate_no',
			2 => 'branding');
        $CostingID = isset($params['id']) ? $params['id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
        $curpage = $start;
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
        
        //$CostingAcceptedList = Costingdata::where('qc_status',1)->where('request_invoice','!=' , 1)->orderBy('id', 'DESC');
        $totalcount = Costingdata::where('qc_status',1)->where('request_invoice','!=' , 1)->count();
        if (!empty($searchValue)) {
            $CostingAcceptedList = "SELECT * FROM `dml_costingdatas` WHERE `qc_status` = 1 AND `request_invoice` != 1 HAVING `sku` LIKE '%" . $searchValue . "%' OR `certificate_no` LIKE '%" . $searchValue . "%' OR `item` LIKE '%" . $searchValue . "%' OR `branding` LIKE '%" . $searchValue . "%' ORDER BY $order,$dir";
            //$CostingAcceptedList =  Costingdata::orderBy('id', 'DESC')->where('qc_status',1)->where('request_invoice','!=' , 1)->where('sku','LIKE',"%{$searchValue}%")->orWhere('certificate_no', 'LIKE',"%{$searchValue}%")->orWhere('item', 'LIKE',"%{$searchValue}%")->orWhere('branding', 'LIKE',"%{$searchValue}%")->offset($start)->limit($length)->toSql();
            $totalcount = "SELECT * FROM `dml_costingdatas` WHERE `qc_status` = 1 AND `request_invoice` != 1 HAVING `sku` LIKE '%" . $searchValue . "%' OR `certificate_no` LIKE '%" . $searchValue . "%' OR `item` LIKE '%" . $searchValue . "%' OR `branding` LIKE '%" . $searchValue . "%' ORDER BY $order $dir";
            
        }else{
            $CostingAcceptedList = "SELECT * FROM `dml_costingdatas` WHERE `qc_status` = 1 AND `request_invoice` != 1 Having `sku` LIKE '%" . $searchValue . "%' OR `certificate_no` LIKE '%" . $searchValue . "%' OR `item` LIKE '%" . $searchValue . "%' OR `branding` LIKE '%" . $searchValue . "%' ORDER BY ".$order." ".$dir."";
        }
        
        $CostingData = DB::select(DB::raw($CostingAcceptedList));
		$datacount = count($CostingData);
		$CostingAcceptedList .= " LIMIT " . $length . " OFFSET " . $start . "";
		$CostingArr = DB::select(DB::raw($CostingAcceptedList));
		$datacollection = $CostingArr;
        if(!empty($datacollection))
        {
            foreach ($datacollection as $costing)
            {
                if(!empty($costing->certificate_no) ||  $costing->certificate_no != 0 )
                {
                    $costigcerti = $costing->certificate_no;
                }else{
                    $costigcerti = $costing->item;
                }
                $detail =  "<a href='javascript:void(0)' class='color-content table-action-style'><i title='Detail' onclick='showDetail(".$costing->id.")' class='material-icons list-icon'>info</i></a>";
                $data[] = array($costing->sku,$costigcerti,$costing->branding,$detail);
            }
        }
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalcount),  
                    "recordsFiltered" => intval($totalcount), 
                    "data"            => $data   
                    );
        echo json_encode($json_data); 
    }

    //Ajax Call For DataTable Costing Rejected
    public function costingrejectedajaxlist(Request $request){
        $data = array();
        $params = $request->post();
        $columns = array(
            0 => 'sku',
			1 => 'certificate_no',
			2 => 'branding');
        $CostingID = isset($params['id']) ? $params['id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
        $curpage = $start;
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
        
        //$CostingAcceptedList = Costingdata::where('qc_status',1)->where('request_invoice','!=' , 1)->orderBy('id', 'DESC');
        $totalcount = Costingdata::where('qc_status',0)->where('return_memo','!=' , 1)->count();
        if (!empty($searchValue)) {
            $CostingRejectedList = "SELECT * FROM `dml_costingdatas` WHERE `qc_status` = 0 AND `return_memo` != 1 HAVING `sku` LIKE '%" . $searchValue . "%' OR `certificate_no` LIKE '%" . $searchValue . "%' OR `item` LIKE '%" . $searchValue . "%' OR `branding` LIKE '%" . $searchValue . "%' ORDER BY $order $dir";
            //$CostingAcceptedList =  Costingdata::orderBy('id', 'DESC')->where('qc_status',1)->where('request_invoice','!=' , 1)->where('sku','LIKE',"%{$searchValue}%")->orWhere('certificate_no', 'LIKE',"%{$searchValue}%")->orWhere('item', 'LIKE',"%{$searchValue}%")->orWhere('branding', 'LIKE',"%{$searchValue}%")->offset($start)->limit($length)->toSql();
            $totalcount = "SELECT * FROM `dml_costingdatas` WHERE `qc_status` = 1 AND `return_memo` != 1 HAVING `sku` LIKE '%" . $searchValue . "%' OR `certificate_no` LIKE '%" . $searchValue . "%' OR `item` LIKE '%" . $searchValue . "%' OR `branding` LIKE '%" . $searchValue . "%' ORDER BY $order $dir";
            
        }else{
            $CostingRejectedList = "SELECT * FROM `dml_costingdatas` WHERE `qc_status` = 0 AND `return_memo` != 1 Having `sku` LIKE '%" . $searchValue . "%' OR `certificate_no` LIKE '%" . $searchValue . "%' OR `item` LIKE '%" . $searchValue . "%' OR `branding` LIKE '%" . $searchValue . "%' ORDER BY ".$order." ".$dir."";
        }
        
        $CostingData = DB::select(DB::raw($CostingRejectedList));
		$datacount = count($CostingData);
		$CostingRejectedList .= " LIMIT " . $length . " OFFSET " . $start . "";
		$CostingArr = DB::select(DB::raw($CostingRejectedList));
		$datacollection = $CostingArr;
        if(!empty($datacollection))
        {
            foreach ($datacollection as $costing)
            {
                if(!empty($costing->certificate_no) ||  $costing->certificate_no != 0 )
                {
                    $costigcerti = $costing->certificate_no;
                }else{
                    $costigcerti = $costing->item;
                }
                $detail =  "<a href='javascript:void(0)' class='color-content table-action-style'><i title='Detail' onclick='showDetail(".$costing->id.")' class='material-icons list-icon'>info</i></a>";
                $data[] = array($costing->sku,$costigcerti,$costing->branding,$detail);
            }
        }
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalcount),  
                    "recordsFiltered" => intval($totalcount), 
                    "data"            => $data   
                    );
        echo json_encode($json_data); 
    }

    // Ajax Call For Datatable gold Given
    public function vendorgoldgivenajaxlist(Request $request){
        $data = array();
        $params = $request->post();
        $columns = array(
            0 => 'gold_po',
			1 => 'gold_voucher_no',
			2 => 'metal_weight',
            3 => 'gold_type',
            4 => 'amount_paid',
			5 => 'purchased_at');
        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
        $curpage = $start;
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');

        $VendorGoldGivenColl = DB::select(DB::raw("SELECT 'gold' AS TYPE,USER.name,gold.amount_paid as amount_paid,gold.id,gold.metal_weight AS metal_weight,gold.gold_type AS gold_type,gold.issue_date AS purchased_at,gold.vendor_id AS gold_vendor,gold.issue_voucher_no AS gold_voucher_no,gold.po_number AS gold_po,gold.purchased_invoice AS gold_voucher FROM dml_metal_transactions gold JOIN dml_users USER ON gold.vendor_id = USER.id WHERE gold.transaction_type = 2 AND USER.id = ".$vendorId." GROUP BY gold_voucher ORDER BY id DESC"));
        $VendorGoldGivenCnt = count($VendorGoldGivenColl);
        if (!empty($searchValue)) {
            $VendorGoldGivenColl = "SELECT 'gold' AS TYPE,USER.name,gold.amount_paid as amount_paid,gold.id,gold.metal_weight AS metal_weight,gold.gold_type AS gold_type,gold.issue_date AS purchased_at,gold.vendor_id AS gold_vendor,gold.issue_voucher_no AS gold_voucher_no,gold.po_number AS gold_po,gold.purchased_invoice AS gold_voucher FROM dml_metal_transactions gold JOIN dml_users USER ON gold.vendor_id = USER.id WHERE gold.transaction_type = 2 AND USER.id = ".$vendorId." GROUP BY gold_voucher HAVING gold_po  LIKE '%$searchValue%' OR gold_voucher_no LIKE '%$searchValue%' OR metal_weight LIKE '%$searchValue%' OR gold_type LIKE '%$searchValue%' OR amount_paid LIKE '%$searchValue%' OR purchased_at LIKE '%$searchValue%' ORDER BY id DESC";
        }else{
            $VendorGoldGivenColl = "SELECT 'gold' AS TYPE,USER.name,gold.amount_paid as amount_paid,gold.id,gold.metal_weight AS metal_weight,gold.gold_type AS gold_type,gold.issue_date AS purchased_at,gold.vendor_id AS gold_vendor,gold.issue_voucher_no AS gold_voucher_no,gold.po_number AS gold_po,gold.purchased_invoice AS gold_voucher FROM dml_metal_transactions gold JOIN dml_users USER ON gold.vendor_id = USER.id WHERE gold.transaction_type = 2 AND USER.id = ".$vendorId." GROUP BY gold_voucher ORDER BY ".$order." ".$dir."";
        }
        $GoldData = DB::select(DB::raw($VendorGoldGivenColl));
        $GoldCollCount = count($GoldData);
        $VendorGoldGivenColl .= " LIMIT " . $length . " OFFSET " . $curpage . "";
        $GoldArrColl = DB::select(DB::raw($VendorGoldGivenColl));
        $data["draw"] = $params['draw'];
		$data["recordsTotal"] = $GoldCollCount;
		$data["recordsFiltered"] = $GoldCollCount;
        $data['deferLoading'] = $GoldCollCount;
        if (count($GoldArrColl) > 0) {
            foreach ($GoldArrColl as $GoldIssueGiven) {
                $gold_po =  $GoldIssueGiven->gold_po;
                $gold_voucher_no = $GoldIssueGiven->gold_voucher_no;
                $metal_weight = $GoldIssueGiven->metal_weight;
                $gold_type = $GoldIssueGiven->gold_type;
                $amount_paid = $GoldIssueGiven->amount_paid;
                $purchased_at = $GoldIssueGiven->purchased_at;
                $data['data'][] = array($gold_po, $gold_voucher_no, $metal_weight, $gold_type,$amount_paid,$purchased_at);
            }
        }else {
			$data['data'][] = array('', '', '', '', '', '');
        }
        echo json_encode($data);exit;
    }

    // Ajax Call Gold Return For DataTable
    public function vendorgoldreturnajaxlist(Request $request){
        $data = array();
        $params = $request->post();
        $columns = array(
            0 => 'po_number',
			1 => 'purchased_at',
			2 => 'issue_voucher_no',
			3 => 'total_weight');
        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
        $curpage = $start;
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
        //$VendorDiamodIssueCnt = DB::select(DB::raw("SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND USER.id = ".$id." GROUP BY issue_voucher_no ORDER BY id DESC"));
        $GivenDiamondColl = DB::select(DB::raw("SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number,SUM(diamond_weight) as total_weight, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND USER.id = ".$vendorId." GROUP BY issue_voucher_no ORDER BY id DESC"));
        $VendorDiamodIssueCnt = count($GivenDiamondColl);
        if (!empty($searchValue)) {
            $GivenDiamondColl = "SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number,SUM(diamond_weight) as total_weight, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND USER.id = ".$vendorId." GROUP BY issue_voucher_no HAVING total_weight  LIKE '%$searchValue%' OR po_number LIKE '%$searchValue%' OR purchased_at LIKE '%$searchValue%' OR issue_voucher_no LIKE '%$searchValue%' ORDER BY id DESC";
        }else{
            $GivenDiamondColl = "SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number,SUM(diamond_weight) as total_weight, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND USER.id = ".$vendorId." GROUP BY issue_voucher_no ORDER BY ".$order." ".$dir."";
        }
        $DiamondsData = DB::select(DB::raw($GivenDiamondColl));
        $DiamondCollCount = count($DiamondsData);
        $GivenDiamondColl .= " LIMIT " . $length . " OFFSET " . $curpage . "";
        $DiamondsArrColl = DB::select(DB::raw($GivenDiamondColl));
        $data["draw"] = $params['draw'];
		$data["recordsTotal"] = $DiamondCollCount;
		$data["recordsFiltered"] = $DiamondCollCount;
        $data['deferLoading'] = $DiamondCollCount;
        if (count($DiamondsArrColl) > 0) {
            foreach ($DiamondsArrColl as $DiamondIssueGiven) {
                $Ponumber =  $DiamondIssueGiven->po_number;
                $pdate = $DiamondIssueGiven->purchased_at;
                $voucherNo = $DiamondIssueGiven->issue_voucher_no;
                $dweight = $DiamondIssueGiven->total_weight;
                $action = '<a title="View transactions" href="javascript:void(0)" id="btn_view"  class="color-content table-action-style" data-id="'.$DiamondIssueGiven->issue_voucher_no.'"><i class="material-icons md-18">remove_red_eye</i></a>';
                $data['data'][] = array($Ponumber, $pdate, $voucherNo, $dweight, $action);
            }
        }else {
			$data['data'][] = array('', '', '', '', '', '', '', '');
        }
        echo json_encode($data);exit;
    }

    // Ajax Call For DataTable
    public function vendorDiamondGivenAjaxList(Request $request) {
        $data = array();
        $params = $request->post();
        $columns = array(
            0 => 'po_number',
			1 => 'purchased_at',
			2 => 'issue_voucher_no',
			3 => 'total_weight');
        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
        $curpage = $start;
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
        //$VendorDiamodIssueCnt = DB::select(DB::raw("SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND USER.id = ".$id." GROUP BY issue_voucher_no ORDER BY id DESC"));
        $GivenDiamondColl = DB::select(DB::raw("SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number,SUM(diamond_weight) as total_weight, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND USER.id = ".$vendorId." GROUP BY issue_voucher_no ORDER BY id DESC"));
        $VendorDiamodIssueCnt = count($GivenDiamondColl);
        if (!empty($searchValue)) {
            $GivenDiamondColl = "SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number,SUM(diamond_weight) as total_weight, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND USER.id = ".$vendorId." GROUP BY issue_voucher_no HAVING total_weight  LIKE '%$searchValue%' OR po_number LIKE '%$searchValue%' OR purchased_at LIKE '%$searchValue%' OR issue_voucher_no LIKE '%$searchValue%' ORDER BY id DESC";
        }else{
            $GivenDiamondColl = "SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number,SUM(diamond_weight) as total_weight, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 2 AND USER.id = ".$vendorId." GROUP BY issue_voucher_no ORDER BY ".$order." ".$dir."";
        }
        $DiamondsData = DB::select(DB::raw($GivenDiamondColl));
        $DiamondCollCount = count($DiamondsData);
        $GivenDiamondColl .= " LIMIT " . $length . " OFFSET " . $curpage . "";
        $DiamondsArrColl = DB::select(DB::raw($GivenDiamondColl));
        $data["draw"] = $params['draw'];
		$data["recordsTotal"] = $DiamondCollCount;
		$data["recordsFiltered"] = $DiamondCollCount;
        $data['deferLoading'] = $DiamondCollCount;
        if (count($DiamondsArrColl) > 0) {
            foreach ($DiamondsArrColl as $DiamondIssueGiven) {
                $Ponumber =  $DiamondIssueGiven->po_number;
                $pdate = $DiamondIssueGiven->purchased_at;
                $voucherNo = $DiamondIssueGiven->issue_voucher_no;
                $dweight = $DiamondIssueGiven->total_weight;
                $action = '<a title="View transactions" href="javascript:void(0)" id="btn_view"  class="color-content table-action-style" data-id="'.$DiamondIssueGiven->issue_voucher_no.'"><i class="material-icons md-18">remove_red_eye</i></a>';
                $data['data'][] = array($Ponumber, $pdate, $voucherNo, $dweight, $action);
            }
        }else {
			$data['data'][] = array('', '', '', '', '', '', '', '');
        }
        echo json_encode($data);exit;
    }

    public function vendordiamondreturnajaxlist(Request $request){
        $data = array();
        $params = $request->post();
        $columns = array(
            0 => 'stone_shape',
			1 => 'diamond_weight',
			2 => 'sieve_size',
            3 => 'mm_size',
            4 => 'issue_voucher_no',
            5 => 'updated_at'
        );
        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
        $curpage = $start;
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
        $VendorDiamondRetunColl = DB::select(DB::raw("SELECT stone_shape,diamond_weight,sieve_size,mm_size,issue_voucher_no,diamond.updated_at, po_number, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 12 AND USER.id = ".$vendorId." ORDER BY diamond.id DESC"));
        $VendorDiamodReturnCnt = count($VendorDiamondRetunColl);
        if (!empty($searchValue)) {
            //$GivenDiamondColl = "SELECT 'diamond' AS TYPE, NAME, diamond.id, purchased_at, vendor_id, issue_voucher_no, po_number,SUM(diamond_weight) as total_weight, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 12 AND USER.id = ".$vendorId." GROUP BY issue_voucher_no HAVING total_weight  LIKE '%$searchValue%' OR po_number LIKE '%$searchValue%' OR purchased_at LIKE '%$searchValue%' OR issue_voucher_no LIKE '%$searchValue%' ORDER BY id DESC";
            $VendorDiamondRetunColl = "SELECT stone_shape,diamond_weight,sieve_size,mm_size,issue_voucher_no,diamond.updated_at, po_number, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 12 AND USER.id = ".$vendorId." HAVING stone_shape  LIKE '%$searchValue%' OR diamond_weight LIKE '%$searchValue%' OR sieve_size LIKE '%$searchValue%' OR mm_size LIKE '%$searchValue%' OR issue_voucher_no LIKE '%$searchValue%' OR diamond.updated_at LIKE '%$searchValue%' ORDER BY diamond.id DESC";
        }else{
            $VendorDiamondRetunColl = "SELECT stone_shape,diamond_weight,sieve_size,mm_size,issue_voucher_no,diamond.updated_at, po_number, issue_vaucher FROM dml_diamond_transactions diamond JOIN dml_users USER ON diamond.vendor_id = USER.id WHERE diamond.transaction_type = 12 AND USER.id = ".$vendorId." ORDER BY ".$order." ".$dir."";
        }

        $DiamondsReturnData = DB::select(DB::raw($VendorDiamondRetunColl));
        $DiamondReturnCollCount = count($DiamondsReturnData);
        $VendorDiamondRetunColl .= " LIMIT " . $length . " OFFSET " . $curpage . "";
        $DiamondsReturnArrColl = DB::select(DB::raw($VendorDiamondRetunColl));
        $data["draw"] = $params['draw'];
		$data["recordsTotal"] = $DiamondReturnCollCount;
		$data["recordsFiltered"] = $DiamondReturnCollCount;
        $data['deferLoading'] = $DiamondReturnCollCount;
        if (count($DiamondsReturnArrColl) > 0) {
            foreach ($DiamondsReturnArrColl as $DiamondIssueReturn) {
                $Sshape =  $DiamondIssueReturn->stone_shape;
                $diamondweight = $DiamondIssueReturn->diamond_weight;
                $sievesize = $DiamondIssueReturn->sieve_size;
                $mmsize = $DiamondIssueReturn->mm_size;
                $voucherNo = $DiamondIssueReturn->issue_voucher_no;
                $pdate = $DiamondIssueReturn->updated_at;
                $data['data'][] = array($Sshape, $diamondweight, $sievesize, $mmsize,$voucherNo,$pdate);
            }
        }else {
			$data['data'][] = array('', '', '', '', '', '');
        }
        echo json_encode($data);exit;
    }

    public function editPersonalInfo(Request $request) {
		$params = $request->post();
		$vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		if (!empty($vendorId)) {
			$vendor=User::whereHas('roles', function($q){ 
                $q->where('name', 'Vendor'); 
            })->where('id',$vendorId)->get();

            $VendorArray = $vendor->toArray();

            $vendorName = $VendorArray[0]['name'];
            $vendorEmail = $VendorArray[0]['email'];
            $vendorDMcode = $VendorArray[0]['vendor_dmcode'];
            $vendorGstin = $VendorArray[0]['gstin'];
            $vendorState = $VendorArray[0]['state'];
            $vendorPhone = $VendorArray[0]['phone'];
            $vendoraddress = $VendorArray[0]['address'];

			return view('vendor.editpersonalinfo')->with(array('vendorId' => $vendorId, 'vendorName' => $vendorName, 'vendorEmail' => $vendorEmail, 'vendorDMcode' => $vendorDMcode, 'vendorGstin' => $vendorGstin,'address' => $vendoraddress, 'vendorState' => $vendorState, 'vendorPhone' => $vendorPhone));
		}
    }
    
    public function updatePersonalInfo(Request $request) {
        
        $params = $request->post();
        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		$Name = isset($params['name']) ? $params['name'] : '';
		$email = isset($params['email']) ? $params['email'] : '';
		$primaryContact = isset($params['phone']) ? $params['phone'] : '';
        $vendor_dmcode = isset($params['vendor_dmcode']) ? $params['vendor_dmcode'] : '';
        $dmcodeValid = true;
		$checkDMcodsCnt = User::select('vendor_dmcode')->where('vendor_dmcode', $vendor_dmcode)->count();
		if ($checkDMcodsCnt >= 1) {
			$checkDMcod = User::select('vendor_dmcode')->where('id', $vendorId)->where('vendor_dmcode', $vendor_dmcode)->get();
			if (empty($checkDMcod->toArray())) {
				$DMcods = User::select('vendor_dmcode')->where('vendor_dmcode', $vendor_dmcode)->get();
				//var_dump($DMcods->toArray());exit;
				if (count($DMcods->toArray()) > 0) {
					if (strtolower($DMcods[0]['vendor_dmcode']) == strtolower($vendor_dmcode)) {
						$forminputcod['vendor_dmcode'] = $vendor_dmcode;
						$rulesCod = array('vendor_dmcode' => 'unique:users,vendor_dmcode');
						$validator = Validator::make($forminputcod, $rulesCod);
						$dmcodeValid = false;
					}
				} else {
					$dmcodeValid = true;
				}
			} else {
				if ($checkDMcod[0]['vendor_dmcode'] != $vendor_dmcode) {
					$forminputcod['vendor_dmcode'] = $vendor_dmcode;
					$rulesCod = array('vendor_dmcode' => 'unique:users,vendor_dmcode');
					$validator = Validator::make($forminputcod, $rulesCod);
					$dmcodeValid = false;
				}
			}

        }
        
        if ($dmcodeValid) {
            if (!empty($vendorId)) {
                $dataUpd = array(
                    "name" => $Name,
                    "email" => $email,
                    "phone" => $primaryContact,
                    "vendor_dmcode" => $vendor_dmcode
                );
                
                User::where('id',$vendorId)->update($dataUpd);
                $response['status'] = true;
                $response['message'] = config('constants.message.customer_personalinfo_updated_success');
            }else {
                $response['status'] = false;
                $response['message'] = config('constants.message.inventory_default_failure_message');
            }
        }else{
            $response['status_validate'] = false;
            $response['message'] = $validator->errors();
        }
        
		echo json_encode($response);exit;
    }

    public function refreshPersonalInfo(Request $request) {
		$params = $request->post();

		$vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		if (!empty($vendorId)) {
			$vendor=User::whereHas('roles', function($q){ 
                $q->where('name', 'Vendor'); 
            })->where('id',$vendorId)->get();

            $VendorArray = $vendor->toArray();

            $vendorName = $VendorArray[0]['name'];
            $vendorEmail = $VendorArray[0]['email'];
            $vendorDMcode = $VendorArray[0]['vendor_dmcode'];
            $vendorPhone = $VendorArray[0]['phone'];

			return view('vendor.refreshpersonalinfo')->with(array('vendorId' => $vendorId, 'vendorName' => $vendorName, 'vendorEmail' => $vendorEmail, 'vendorDMcode' => $vendorDMcode, 'vendorPhone' => $vendorPhone));
		}
    }
    
    public function addVendorAttachment(Request $request) {
		$params = $request->post();
        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
        $gstnumber = isset($params['gstnumber']) ? $params['gstnumber'] : '';
        $attachmentType = isset($params['attachment_type']) ? $params['attachment_type'] : '';
        
        if (!empty($gstnumber)) {
			$dmcodeValid = true;
			$checkDMcodsCnt = User::select('gstin')->where('gstin', $gstnumber)->count();
			if ($checkDMcodsCnt >= 1) {
				$checkDMcod = User::select('gstin')->where('gstin', $gstnumber)->get();
				//$DMcods = User::select('vendor_dmcode')->where('vendor_dmcode',$request->input('vendor_dmcode'))->get();

				if (count($checkDMcod->toArray()) > 0) {
					//echo 111;exit;
					if (strtolower($checkDMcod[0]['gstin']) == strtolower($gstnumber)) {
						$forminputcod['gstin'] = $gstnumber;
						$rulesCod = array('gstin' => 'unique:users,gstin');
						$validator = Validator::make($forminputcod, $rulesCod);
						$dmcodeValid = false;
					}
				} else {
					$dmcodeValid = true;
				}

			}
		} else {
			$dmcodeValid = true;
		}
		if (!empty($vendorId) && $dmcodeValid) {
			return view('vendor.addattachment')->with(array('vendorId' => $vendorId,'gstnumber' => $gstnumber, 'attachmentType' => $attachmentType));
		}else{
            $response['status_validate'] = false;
            $response['message'] = $validator->errors();
        }
    }
    

    public function editGstinPanCard(Request $request) {
		$params = $request->post();
		$vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		$attachmentType = isset($params['attachment_type']) ? $params['attachment_type'] : '';
		$gstnumber = '';
		if ($attachmentType == 'gstin') {
            $vendor=User::whereHas('roles', function($q){ 
                $q->where('name', 'Vendor'); 
            })->where('id',$vendorId)->get();

            $VendorArray = $vendor->toArray();
            $gstnumber = $VendorArray[0]['gstin'];
		}
		return view('vendor.addattachment')->with(array('vendorId' => $vendorId, 'attachmentType' => $attachmentType, 'gstnumber' => $gstnumber, 'edit' => true));
    }
    
    public function addVendorGstin(Request $request) {
        $params = $request->post();
		$vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		$attachmentType = isset($params['attachment_type']) ? $params['attachment_type'] : '';
        $gstnumber = isset($params['gstin']) ? $params['gstin'] : '';
        
		$gstinValid = true;
		$checkgstinCnt = User::select('gstin')->where('gstin', $gstnumber)->count();
		if ($checkgstinCnt >= 1) {
			$checkGstin = User::select('gstin')->where('id', $vendorId)->where('gstin', $gstnumber)->get();
			if (empty($checkGstin->toArray())) {
				$GstinVal = User::select('gstin')->where('gstin', $gstnumber)->get();
				//var_dump($DMcods->toArray());exit;
				if (count($GstinVal->toArray()) > 0) {
					if (strtolower($GstinVal[0]['gstin']) == strtolower($gstnumber)) {
						$forminputcod['gstin'] = $gstnumber;
						$rulesCod = array('gstin' => 'unique:users,gstin');
						$validator = Validator::make($forminputcod, $rulesCod);
						$gstinValid = false;
					}
				} else {
					$gstinValid = true;
				}
			} else {
				if ($checkGstin[0]['gstin'] != $gstnumber) {
					$forminputcod['gstin'] = $gstnumber;
					$rulesCod = array('gstin' => 'unique:users,gstin');
					$validator = Validator::make($forminputcod, $rulesCod);
					$gstinValid = false;
				}
			}
        }
        
        if (!empty($vendorId) && $gstinValid) {
            $dataUpd = array(
                "gstin" => $gstnumber,
            );            
            User::where('id',$vendorId)->update($dataUpd);
            $response['status'] = true;
			//echo "test";exit;
			if ($attachmentType == 'gstin') {
				$response['message'] = config('constants.message.customer_gstin_updated_success');
			}
        }else{
            $response['status_validate'] = false;
            $response['message'] = $validator->errors();
        }
        echo json_encode($response);exit;
    }

    public function refreshgstin(Request $request) {
		$params = $request->post();
		$vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		if (!empty($vendorId)) {
			$vendor=User::whereHas('roles', function($q){ 
                $q->where('name', 'Vendor'); 
            })->where('id',$vendorId)->get();
            $VendorArray = $vendor->toArray();
            $gstinnumber = $VendorArray[0]['gstin'];
            return view('vendor.refreshgstin')->with(array('vendorId' => $vendorId, 'gstinnumber' => $gstinnumber));
		}
    }
    
    public function getaddress(Request $request){
        $params = $request->post();
		$vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		if (!empty($vendorId)) {
            $vendorData=User::whereHas('roles', function($q){ 
                $q->where('name', 'Vendor'); 
            })->where('id',$vendorId)->get();
            $VendorArray = $vendorData->toArray();
            $vendoraddress = $VendorArray[0]['address'];
            $vendorState = $VendorArray[0]['state'];
        }

        return view('vendor.editaddressmodal')->with(array('address' => $vendoraddress,'state' => $vendorState,'vendorId' => $vendorId));
    }

    public function updatevendoraddress(Request $request){
        $params = $request->post();
		$address = isset($params['address']) ? $params['address'] : '';
        $state = isset($params['state']) ? $params['state'] : '';
        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
        if (!empty($vendorId)) {
            $dataUpdate = array(
                'address' => $address,
                'state' => $state
            );
        /* echo "<pre>";
        print_r($dataUpdate);exit; */
        User::where('id',$vendorId)->update($dataUpdate);
        $response['status'] = true;
		$response['message'] = Config::get('constants.message.customer_address_updated_success');
        }else{
            $response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_default_failure_message');
        }
        echo json_encode($response);exit;
    }

    //Refresh Vendor address after update
	public function refreshDefaultAddress(Request $request) {
		$params = $request->post();
		$vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : '';
		if (!empty($vendorId)) {
			$vendorData=User::whereHas('roles', function($q){ 
                $q->where('name', 'Vendor'); 
            })->where('id',$vendorId)->get();
            $VendorArray = $vendorData->toArray();
            $vendoraddress = $VendorArray[0]['address'];
            $vendorstate = $VendorArray[0]['state'];
			return view('vendor.refreshaddress')->with(array('vendorAddress' => $vendoraddress,'state' => $vendorstate));
		}
	}
    public function vendoresponse(Request $request)
    {
            $columns = array( 
                        0 => 'id',
                        1 =>'name',
                        2 =>'email',
                        3 =>'vendor_DMcode',
                        4 =>'action');
        $results= User::whereHas('roles', function($q){ $q->where('name', 'Vendor'); })->distinct();
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
            //echo $resultslist;exit;
        }else {
            $search = $request->input('search.value'); 
            $resultslist =  $results->whereRaw('(vendor_dmcode like "%' . $search . '%" or name like "%' . $search . '%" or email like "%' . $search . '%")')
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir)
                            ->get();
            /* $results->where('id', 'LIKE',DB::raw("'%$search%'"))->orWhere('name', 'LIKE',DB::raw("'%$search%'"))
                ->orWhere('vendor_dmcode', 'LIKE',DB::raw("'%$search%'")) */
            //echo $resultslist;exit;
            $totalFiltered = $results->whereRaw('(vendor_dmcode like "%' . $search . '%" or name like "%' . $search . '%" or email like "%' . $search . '%")')->count();
            //$results->where('id', 'LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")->orWhere('vendor_dmcode', 'LIKE',"%{$search}%")->count();
        }
        $data = array();
        if(!empty($resultslist))
        {
            foreach ($resultslist as $resultslist)
            {
                $action=  '<a title="View" href="'.route('vendor.view',['vendor_id'=>$resultslist->id]).'" class="color-content table-action-style"><i class="list-icon material-icons md-18">remove_red_eye</i></a>

                <a title="Manage Rates" href="'.route('managecharges.index',['vendor_id'=>$resultslist->id,'name'=>$resultslist->name]).'" class="color-content table-action-style"><i class="list-icon material-icons md-18">perm_data_setting</i></a>

                <a title="Metal Rates" href="'.route('metalrates.index',['vendor_id'=>$resultslist->id,'name'=>$resultslist->name]).'" id="{{$vendors->id}}" class="color-content table-action-style"><i class="list-icon material-icons md-18">build</i></a>';
                
                $data[] = array(++$start,$resultslist->name,$resultslist->email,$resultslist->vendor_dmcode,$action);
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
            $roles =   Role::where('name', '=', 'Vendor')->pluck('name', 'name')->all();

      return view('/vendor/create',compact('roles'));
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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm_password',
            'roles' => 'required'
        ]);

        // $input = $request->all();

        $input= new User();
        $input->name= $request->input('name');
        $input->email= $request->input('email');
        $input['password'] = Hash::make($input['password']);
        $input->vendor_dmcode= $request->input('vendor_dmcode');
        $input->created_by= $request->input('created_by');
        $input->save();
        
    

      
        $input->assignRole($request->input('roles'));

        return redirect()->route('vendor.vendor_details')
            ->with('success', 'vendor created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vendors=Auth::user()->find($id);

         return view('vendor/show',compact('vendors'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $vendors=Auth::user()->find($id);
        $roles = Role::where('name', '=', 'Vendor')->pluck('name', 'name')->all();
        $userRole = $vendors->roles->pluck('name', 'name')->all();
        $created_id=Auth::user()->select('created_by')->where('id',$id)->get();
        /*print_r($created_id[0]->created_by);exit;*/
        if(Auth::user()->id == $created_id[0]->created_by){
            $vendors=Auth::user()->find($id);
            $roles = Role::where('name', '=', 'Vendor')->pluck('name', 'name')->all();
            $userRole = $vendors->roles->pluck('name', 'name')->all();

            return view('vendor/edit', compact('vendors','roles','userRole'));
        }else{

            return  redirect()->back()->with('errors', 'You Could not  edit vendor' );    
        }
        
       
       
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
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'same:confirm-password',
            'roles' => 'required',
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = array_except($input, array('password'));
        }

        $user = User::find($id);

        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        $user->assignRole($request->input('roles'));

        return redirect()->route('vendor.vendor_details')
            ->with('success', 'vendor updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $created_id=Auth::user()->select('created_by')->where('id',$id)->get();
     
        if(Auth::user()->id == $created_id[0]->created_by){
            User::find($id)->delete();
            return response()->json([
                'success' => 'Record deleted successfully!',
            ]);
        }else{
            return response()->json([
                    'errors' => 'Record could not be deleted !',
                ]);
        }


        

        $return_data = array();

        $return_data['response'] = 'success';
        echo json_encode($return_data);exit;
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }


    public function vendor_details(Request $request)
    {
       /* print_r($request->all());exit;*/
       $vendor=User::whereHas('roles', function($q){ $q->where('name', 'Vendor'); })->orderBy('id','DESC')->paginate();  
    
       
        $vendortotal= User::whereHas('roles', function ($q) {$q->where('name', 'Vendor');});
        $totalcount=$vendortotal->count();
      
      return view('vendor/vendor_details',compact('vendor','totalcount'));
    }

     public function vendor_detailresponse(Request $request)
    {
            $columns = array( 
                        0 =>'id',
                        1 =>'vendor_name',
                        2 =>'vendor_email',
                        3 =>'vendor_DMcode',
                        4 =>'action');
             $results= User::whereHas('roles', function($q){ $q->where('name', 'Vendor'); })->orderBy('id','DESC')->distinct();
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
            $resultslist =   $results->where('id', 'LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")
                                    ->orWhere('vendor_dmcode', 'LIKE',"%{$search}%")
                                                ->offset($start)
                                                ->limit($limit)
                                                ->orderBy($order,$dir)
                                                ->get();
            $totalFiltered = $results->where('id', 'LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")->orWhere('vendor_dmcode', 'LIKE',"%{$search}%")->count();
        }
        $data = array();
        if(!empty($resultslist))
        {
            foreach ($resultslist as $resultslist)
            {
                $action = '<a class="color-content table-action-style" href="'. action('VendorController@show',$resultslist->id).'"><i class="material-icons">visibility</i></a> ';

                if($resultslist->created_by == Auth::user()->id){
                    $action .= '<a class="color-content table-action-style" href="'. route('vendor.edit',$resultslist->id).'"><i class="material-icons md-18">edit</i></a> ';
                }

                if($resultslist->created_by == Auth::user()->id){                
                    $action .= '<a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deletediamond('.$resultslist->id . ',\'' . csrf_token() . '\');" data-token="\'' . csrf_token() . '\'"><i class="material-icons md-18">delete</i></a>';
                }
                
                $data[] = array($resultslist->id,$resultslist->name,$resultslist->email,$resultslist->vendor_dmcode,$action);
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
