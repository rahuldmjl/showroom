<?php

namespace App\Http\Middleware;

use App\Costing;
use App\Costingdata;
use App\Diamond;
use App\DiamondTransaction;
use App\Helpers\CustomersHelper;
use App\MetalTransaction;
use App\Payment;
use App\PaymentType;
use App\ShowroomOrders;
use App\TransactionType;
use App\User;
use Auth;
use Closure;
use DateTime;
use DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NavData {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$nav_counters = array();

		$authuser = Auth::user();

		if ($authuser->hasRole('Super Admin') || $authuser->hasRole('User Manager')) {
			$users = User::whereHas('roles', function ($q) {$q->where('name', '<>', 'Super Admin');})->orderBy('id', 'DESC');
			$customer = CustomersHelper::getAllCustomers();
			$nav_counters['total_customers'] = $customer['totalCount']; //$users->count();
		} else {
			$users = User::where('created_by', $authuser->id)->whereHas('roles', function ($q) {$q->where('name', '<>', 'Super Admin');})->orderBy('id', 'DESC');
		}

		$nav_counters['total_users'] = $users->count();

		$roles = Role::orderBy('id', 'DESC');
		$nav_counters['total_roles'] = $roles->count();

		$orderHistory = ShowroomOrders::get();
		$nav_counters['total_orders'] = $orderHistory->count();

		$permissions = Permission::orderBy('id', 'DESC');
		$nav_counters['total_permissions'] = $permissions->count();

		$vendor = User::whereHas('roles', function ($q) {$q->where('name', 'Vendor');});
		$nav_counters['total_vendor'] = $vendor->count();

		$costing_product = Costingdata::whereNull('qc_status');
		$nav_counters['total_costing_product'] = $costing_product->count();

		$costing_sheet = Costing::all();
		$nav_counters['total_costing_sheet'] = $costing_sheet->count();

		$types = TransactionType::all();
		$nav_counters['total_transaction_types'] = $types->count();

		$diamond = Diamond::all();
		$nav_counters['total_diamond'] = $diamond->count();

		\View::share('nav_counters', $nav_counters);

		//$costing_sheet=Costing::all();
		$nav_counters['total_costing_sheet'] = Costing::count();

		$costing_qc_accept = Costingdata::where('qc_status', '1')->where('request_invoice', '!=', 1);
		$nav_counters['total_costing_qc_accept'] = $costing_qc_accept->count();

		$costing_qc_reject = Costingdata::where('qc_status', '0')->where('return_memo', '!=', 1);
		$nav_counters['total_costing_qc_reject'] = $costing_qc_reject->count();

		$costing_qc_igi = Costingdata::where('is_igi', '1');
		$nav_counters['total_costing_qc_igi'] = $costing_qc_igi->count();

		$costing_qc_request_invoice = Costingdata::where('request_invoice', '1');
		$nav_counters['total_costing_qc_request_invoice'] = $costing_qc_request_invoice->count();

		$costing_qc_return_memo = Costingdata::where('return_memo', '1');
		$nav_counters['total_costing_qc_return_memo'] = $costing_qc_return_memo->count();

		//\View::share('nav_counters', $nav_counters);

		$paymenttype = PaymentType::all();
		$nav_counters['total_paymenttype'] = $paymenttype->count();

		$paymentlist = Payment::all();
		$nav_counters['total_paymentlist'] = $paymentlist->count();

		$incoming = Payment::where('payment_form', '=', 'Incoming')->where('account_status', '=', '0');
		$nav_counters['total_incoming'] = $incoming->count();

		$outgoing = Payment::where('payment_form', '=', 'Outgoing')->where('account_status', '=', '0')->where('payment_status', '=', '0');
		$nav_counters['total_outgoing'] = $outgoing->count();

		$decline = Payment::where('account_status', '=', '2')->where('payment_status', '=', '0');
		$nav_counters['total_decline'] = $decline->count();

		$payment_incoming = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('payment_form', '=', 'Incoming')->where('account_status', '=', '1')->whereIn('payment_status', array(0, 2))->orderBy('id', 'DESC');

		$nav_counters['total_payment_incoming'] = $payment_incoming->count();

		$payment_incoming = Payment::where('payment_form', '=', 'Outgoing')->where('account_status', '=', '1')->where('payment_status', '=', '0');
		$nav_counters['total_payment_outgoing'] = $payment_incoming->count();

		$paid = Payment::where('payment_status', '=', '1');
		$nav_counters['total_paidpayment'] = $paid->count();

		/* Gold Chart - start */
		$metalTransactions = MetalTransaction::where("transaction_type", 1)
			->where("metal_type", 1)
			->where("status", 1)
			->where('created_at', '>=', date('Y-m-d', strtotime("-30 days")))
			->get();
		$metalIssues = MetalTransaction::where("transaction_type", 2)
			->where("metal_type", 1)
			->where("status", 1)
			->where('created_at', '>=', date('Y-m-d', strtotime("-30 days")))
			->get();
		/* $live_place_area = array();
					foreach($metalTransactions as $metalTran){
							$dateproduct = $metalTran->created_at;
			                $createDate = new DateTime($dateproduct);
							$Productstrip = $createDate->format('Y-m-d');

						foreach($metalIssues as $Issues){
							$live_place_area[] = array( 'year' => $Productstrip , 'purchase' => floor($metalTran->metal_weight * 100) / 100, 'issue' => floor($Issues->metal_weight * 100) / 100);
						}
					}
					$nav_counters['data'] =  json_encode($live_place_area);
		*/
		$Lable = array();
		$purchase = array();
		$issue = array();
		foreach ($metalTransactions as $metalTran) {
			$dateproduct = $metalTran->created_at;
			$createDate = new DateTime($dateproduct);
			$Productstrip = $createDate->format('Y-m-d');
			$Lable[] = $Productstrip;
			$purchase[] = floor($metalTran->metal_weight * 100) / 100;
			foreach ($metalIssues as $Issues) {
				//$live_place_area[] = array( 'year' => $Productstrip , 'purchase' => floor($metalTran->metal_weight * 100) / 100, 'issue' => floor($Issues->metal_weight * 100) / 100);
				$issue[] = floor($Issues->metal_weight * 100) / 100;
			}
		}
		$nav_counters['lable'] = json_encode($Lable);
		$nav_counters['purchase'] = json_encode($purchase);
		$nav_counters['issue'] = json_encode($issue);

		/* Gold Chart - end */

		/// gold and diamond dashboard

		/* Diamond Chart - start */
		$DiamondTransactions = DiamondTransaction::where("transaction_type", 1)
			->where("status", 1)
			->where('created_at', '>=', date('Y-m-d', strtotime("-30 days")))
			->get();
		//echo "<pre>";print_r($DiamondTransactions->toArray());exit;
		$DiamondIssues = DiamondTransaction::where("transaction_type", 2)
			->where("status", 1)
			->where('created_at', '>=', date('Y-m-d', strtotime("-30 days")))
			->get();
		$DiamondLable = array();
		$Diamondpurchase = array();
		$Diamondissue = array();
		foreach ($metalTransactions as $metalTran) {
			$dateproduct = $metalTran->created_at;
			$createDate = new DateTime($dateproduct);
			$Productstrip = $createDate->format('Y-m-d');
			$DiamondLable[] = $Productstrip;
			$Diamondpurchase[] = floor($metalTran->metal_weight * 100) / 100;
			foreach ($metalIssues as $Issues) {
				$Diamondissue[] = floor($Issues->metal_weight * 100) / 100;
			}
		}
		$nav_counters['diamond_lable'] = json_encode($DiamondLable);
		$nav_counters['diamond_purchase'] = json_encode($Diamondpurchase);
		$nav_counters['diamond_issue'] = json_encode($Diamondissue);
		/* Diamond Chart - end */

		$transactionType = TransactionType::where('name', config('constants.enum.transaction_types.purchase'))->pluck('id')->first();
		$nav_counters['total_gold_purchased'] = round(MetalTransaction::where('transaction_type', $transactionType)->where('metal_type', 1)->sum('metal_weight'));

		$total = round(MetalTransaction::where('metal_type', 1)->where('transaction_type', 2)->sum('metal_weight'));

		$nav_counters['total_gold_instock'] = $nav_counters['total_gold_purchased'] - $total;

		$transactiongoldissue = TransactionType::where('name', config('constants.enum.transaction_types.issue'))->pluck('id')->first();
		$nav_counters['total_gold_approved'] = round(MetalTransaction::where('transaction_type', $transactiongoldissue)->where('metal_type', 1)->sum('metal_weight'));

		$transactiongoldmisc = TransactionType::where('name', config('constants.enum.transaction_types.misc'))->pluck('id')->first();
		$nav_counters['total_gold_loss'] = round(MetalTransaction::where('transaction_type', $transactiongoldmisc)->where('metal_type', 1)->sum('metal_weight'));
		/*$transactiondiamondpur = TransactionType::where('name', config('constants.enum.transaction_types.purchase'))->pluck('id')->first();
		$nav_counters['total_diamond_purchased'] = round(DiamondTransaction::where('transaction_type', $transactiondiamondpur)->sum('diamond_weight'));*/
		$transactionType = TransactionType::where('name', config('constants.enum.transaction_types.purchase'))->pluck('id')->first();
		$nav_counters['total_diamond_purchased'] = round(DiamondTransaction::where('transaction_type', $transactionType)->sum('diamond_weight'));
		$issue = round(DiamondTransaction::where('transaction_type', 2)->sum('diamond_weight'));
		$reissue = round(DiamondTransaction::where('transaction_type', 3)->sum('diamond_weight'));
		$purchase_with_reissue = round(DiamondTransaction::where('transaction_type', 4)->sum('diamond_weight'));
		$purchase_from_vendor = round(DiamondTransaction::where('transaction_type', 5)->sum('diamond_weight'));
		$sell = round(DiamondTransaction::where('transaction_type', 6)->sum('diamond_weight'));
		$misc = round(DiamondTransaction::where('transaction_type', 7)->sum('diamond_weight'));
		$return = round(DiamondTransaction::where('transaction_type', 8)->sum('diamond_weight'));

		$nav_counters['total_diamond_instock'] = $nav_counters['total_diamond_purchased'] - $issue - $reissue - $purchase_with_reissue - $purchase_from_vendor - $sell - $misc - $return;

		$transactiondiamondissue = TransactionType::where('name', config('constants.enum.transaction_types.issue'))->pluck('id')->first();
		$nav_counters['total_diamond_approved'] = round(DiamondTransaction::where('transaction_type', $transactiondiamondissue)->sum('diamond_weight'));

		$transactiongoldmisc = TransactionType::where('name', config('constants.enum.transaction_types.misc'))->pluck('id')->first();
		$nav_counters['total_diamond_loss'] = round(DiamondTransaction::where('transaction_type', $transactiongoldmisc)->sum('diamond_weight'));
		//$nav_counters['total_diamond_loss'] = round(DiamondRaw::sum('total_loss'));

		$customersData = CustomersHelper::getAllCustomers();

		$nav_counters['total_customers_count'] = $customersData["totalCount"];
		/// gold and diamond dashboard
		$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;
		$diamond_payments = DB::table('payments AS pay')
			->join('diamond_transactions AS trans', 'trans.transaction_id', '=', 'pay.id')
			->join('users as uservendor', 'uservendor.id', '=', 'trans.vendor_id')
			->join('users as usercreated', 'usercreated.id', '=', 'trans.user_id')
			->join('payment_types AS paytype', 'pay.payment_type', '=', 'paytype.id')->where('pay.payment_type', '=', $payment_type)
			->select('trans.id', 'paytype.name AS pay_type', DB::raw('IF(500<1000, "Diamond", "Gold") AS metal_type'), 'trans.amount_paid_with_gst', 'trans.invoice_number', 'trans.transaction_at', 'pay.transaction_id', 'usercreated.name as created_by', 'pay.payment_status', 'uservendor.name', 'trans.user_id', 'pay.account_status')->groupby('pay.transaction_id');

		$metal_payments = DB::table('payments AS pay')
			->join('metal_transactions AS trans', 'trans.transaction_id', '=', 'pay.id')
			->join('users as uservendor', 'uservendor.id', '=', 'trans.vendor_id')
			->join('users as usercreated', 'usercreated.id', '=', 'trans.user_id')
			->join('payment_types AS paytype', 'pay.payment_type', '=', 'paytype.id')->where('pay.payment_type', '=', $payment_type)
			->union($diamond_payments)
			->select('trans.id', 'paytype.name AS pay_type', DB::raw('IF(`dml_trans`.`metal_type`=1, "Gold", "Platinum(950)") AS metal_type'), 'trans.amount_paid', 'trans.invoice_number AS invoice_number', 'trans.transaction_at', 'pay.transaction_id', 'usercreated.name as created_by', 'pay.payment_status', 'uservendor.name', 'trans.user_id', 'pay.account_status')
			->orderBy('transaction_at', 'desc')
			->get();
		$nav_counters['total_purchase'] = $metal_payments->count();

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

		$approved = DB::table("payments")
			->select("payments.*", 'payment_types.name', DB::raw('"Pending" AS status'), 'payments.updated_at AS payment_status_updated')
			->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')
			->where('payments.account_status', '=', '1')
			->whereNotIn('payments.id', $approved_payments_transactions_only_id)
			->union($approved_payments_transactions);

		$nav_counters['total_approved'] = $approved->count();

		\View::share('nav_counters', $nav_counters);
		return $next($request);
	}
}