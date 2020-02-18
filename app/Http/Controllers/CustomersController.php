<?php

namespace App\Http\Controllers;

use App;
use App\ApprovalMemoHistroy;
use App\CustomerWallet;
use App\Helpers\CommonHelper;
use App\Helpers\CustomersHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
use App\Http\Controllers\Controller;
use App\Payment;
use App\PaymentTransaction;
use App\PaymentType;
use App\Quotation;
use App\ReturnMemo;
use App\SalesReturn;
use Auth;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use URL;

class CustomersController extends Controller {
	//Display customers listing
	public function index(Request $Request) {
		$customersData = CustomersHelper::getAllCustomers();

		return view('customers.index', compact('customersData'));
	}
	//For server side datatable
	public function ajaxlist(Request $request) {
		$data = array();
		$params = $request->post();
		//echo "<pre>";
		//print_r($params);exit;
		$order = $params['order'][0]['column'];
		$order_direc = strtoupper($params['order'][0]['dir']);
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);

		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');

		//$stalen = $start / $length;
		$curpage = $start;

		DB::setTablePrefix('');

		$franchiseestatusAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='franchisee_status' order by attribute_id DESC");
		$franchiseestatusattrid = array();
		foreach ($franchiseestatusAttribute as $key => $attr) {
			$franchiseestatusattrid[] = $attr->attribute_id;
		}
		$franchiseestatusattridsingle = $franchiseestatusattrid[0];

		$frncodeAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='frn_code' order by attribute_id DESC");
		$frncodeattrid = array();
		foreach ($frncodeAttribute as $key => $attr) {
			$frncodeattrid[] = $attr->attribute_id;
		}
		$frncodeattridsingle = $frncodeattrid[0];

		if (empty($searchValue)) {
			/* $customers = DB::table('customer_entity as ce')
				->select('ce.entity_id', 'ce.email'); */
			$customers = DB::table('customer_entity as e')
				->join('customer_entity_int as at_franchisee_status', function ($join) use ($franchiseestatusattridsingle) {
					$join->on('at_franchisee_status.entity_id', '=', 'e.entity_id');
					$join->on('at_franchisee_status.attribute_id', '=', DB::raw("'" . $franchiseestatusattridsingle . "'"));
				})
				->leftjoin('customer_entity_varchar as cvar', function ($join) use ($frncodeattridsingle) {
					$join->on('cvar.entity_id', '=', 'e.entity_id');
					$join->on('cvar.attribute_id', '=', DB::raw("'" . $frncodeattridsingle . "'"));
				})
				->where('e.entity_type_id', '=', '1')
				->where('at_franchisee_status.value', '=', '2')
				->select('e.entity_id', 'cvar.value as frn_code', 'e.email', 'at_franchisee_status.value as franchisee_status')
				->orderBy("e.entity_id", "DESC");
		} else {
			$firstNameAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='firstname'");
			$firstnameattrid = array();
			foreach ($firstNameAttribute as $key => $attr) {
				$firstnameattrid[] = $attr->attribute_id;
			}
			$firstnameattrid = implode(',', $firstnameattrid);

			$lastNameAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='lastname'");

			$ContactNumberAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='primary_contact'");

			$lastnameattrid = array();
			foreach ($lastNameAttribute as $key => $attr) {
				$lastnameattrid[] = $attr->attribute_id;
			}
			$lastnameattrid = implode(',', $lastnameattrid);

			if (strpos($searchValue, 'DML') !== false) {
				$searchValue = str_replace('DML', '', $searchValue);
			}

			if (preg_match("/[a-z]/i", $searchValue)) {
				$customerCollection = DB::select("select * from (SELECT `e`.*,`at_firstname`.`value` AS `firstname`,`at_lastname`.`value` AS `lastname`,`frndata`.`value` AS `frn_code`,CONCAT(`at_firstname`.`value`, ' ', `at_lastname`.`value`) as customer_name FROM `customer_entity` AS `e` JOIN `customer_entity_varchar` AS `at_firstname` ON (`at_firstname`.`entity_id` = `e`.`entity_id`) AND(`at_firstname`.`attribute_id` IN(" . $firstnameattrid . ")) JOIN `customer_entity_varchar` AS `at_lastname` ON(`at_lastname`.`entity_id` = `e`.`entity_id`) AND(`at_lastname`.`attribute_id` IN(" . $lastnameattrid . ")) LEFT JOIN `customer_entity_varchar` AS `frndata` ON(`frndata`.`entity_id` = `e`.`entity_id`) AND(`frndata`.`attribute_id` IN(" . implode(',', $frncodeattrid) . ")) WHERE(`e`.`entity_type_id` ='1')) a WHERE customer_name LIKE '%" . $searchValue . "%' OR email LIKE '%" . $searchValue . "%' OR frn_code LIKE '%" . $searchValue . "%'");
			} else {
				/*echo "select * from (SELECT `e`.*,`at_firstname`.`value` AS `firstname`,`at_lastname`.`value` AS `lastname`,`cn`.`value` AS `contact_number`,CONCAT(`at_firstname`.`value`, ' ', `at_lastname`.`value`) as customer_name FROM `customer_entity` AS `e` JOIN `customer_entity_varchar` AS `at_firstname` ON (`at_firstname`.`entity_id` = `e`.`entity_id`) AND(`at_firstname`.`attribute_id` IN(" . $firstnameattrid . ")) JOIN `customer_entity_varchar` AS `at_lastname` ON(`at_lastname`.`entity_id` = `e`.`entity_id`) AND(`at_lastname`.`attribute_id` IN(" . $lastnameattrid . ")) JOIN `customer_entity_varchar` AS `cn` ON(`cn`.`entity_id` = `e`.`entity_id`) AND(`cn`.`attribute_id` IN(" . $ContactNumberAttribute[0]->attribute_id . "))WHERE(`e`.`entity_type_id` ='1') AND e.entity_id LIKE '%" . $searchValue . "%' OR cn.value LIKE '%" . $searchValue . "%') a";exit;*/

				$customerCollection = DB::select("select * from (SELECT `e`.*,`at_firstname`.`value` AS `firstname`,`at_lastname`.`value` AS `lastname`,`cn`.`value` AS `contact_number`,`frndata`.`value` AS `frn_code`, CONCAT(`at_firstname`.`value`, ' ', `at_lastname`.`value`) as customer_name FROM `customer_entity` AS `e` JOIN `customer_entity_varchar` AS `at_firstname` ON (`at_firstname`.`entity_id` = `e`.`entity_id`) AND(`at_firstname`.`attribute_id` IN(" . $firstnameattrid . ")) JOIN `customer_entity_varchar` AS `at_lastname` ON(`at_lastname`.`entity_id` = `e`.`entity_id`) AND(`at_lastname`.`attribute_id` IN(" . $lastnameattrid . "))
					LEFT JOIN `customer_entity_varchar` AS `frndata` ON(`frndata`.`entity_id` = `e`.`entity_id`) AND(`frndata`.`attribute_id` IN(" . implode(',', $frncodeattrid) . ")) JOIN `customer_entity_varchar` AS `cn` ON(`cn`.`entity_id` = `e`.`entity_id`) AND(`cn`.`attribute_id` IN(" . $ContactNumberAttribute[0]->attribute_id . ")) WHERE(`e`.`entity_type_id` ='1') AND e.entity_id LIKE '%" . $searchValue . "%' OR frndata.value LIKE '%" . $searchValue . "%' OR cn.value LIKE '%" . $searchValue . "%') a");
			}
			$customerIds = array();
			//echo "<pre>";print_r($customerCollection);exit;
			foreach ($customerCollection as $key => $customerData) {
				$customerIds[] = $customerData->entity_id;
			}
			$customerIds = implode("','", $customerIds);
			$customers = DB::table('customer_entity as ce')
				->leftjoin('customer_entity_varchar as cvar', function ($join) use ($frncodeattridsingle) {
					$join->on('cvar.entity_id', '=', 'ce.entity_id');
					$join->on('cvar.attribute_id', '=', DB::raw("'" . $frncodeattridsingle . "'"));
				})
				->select('ce.entity_id', 'ce.email', 'cvar.value as frn_code')
				->whereIn('ce.entity_id', [DB::raw("'" . $customerIds . "'")]);

		}
		//echo $customers->take($length)->offset($curpage)->toSql();exit;
		//$collection = collect($customers->get());
		$custCount = $customers->get()->count();
		$customerCollection = $customers->take($length)->offset($curpage)->orderBy('entity_id', 'desc')->get();
		DB::setTablePrefix('dml_');
		$data["draw"] = (int) $params['draw'];
		$data["recordsTotal"] = $custCount;
		$data["recordsFiltered"] = $custCount;
		$data['deferLoading'] = $custCount;

		if (count($customerCollection) > 0) {
			foreach ($customerCollection as $key => $customer) {
				$frnCode = isset($customer->frn_code) ? $customer->frn_code : '';
				$dmlUserCode = isset($customer->entity_id) ? 'DML' . $customer->entity_id : '';
				$customerName = InventoryHelper::getCustomerName($customer->entity_id);
				$contactNumber = !empty(CustomersHelper::getCustomerAttrValue($customer->entity_id, 'primary_contact')) ? CustomersHelper::getCustomerAttrValue($customer->entity_id, 'primary_contact') : '-';
				$location = !empty(CustomersHelper::getCustomerAttrValue($customer->entity_id, 'location')) ? CustomersHelper::getCustomerAttrValue($customer->entity_id, 'location') : '-';
				$totalApprovalProducts = DB::select("select count(1) as total_approval FROM dml_approval_memo_histroy as memo_histroy JOIN dml_approval_memo as memo ON memo.id=memo_histroy.approval_memo_id WHERE memo.customer_id=" . $customer->entity_id . " AND memo_histroy.status='approval'");
				$totalApprovalProducts = isset($totalApprovalProducts[0]->total_approval) ? $totalApprovalProducts[0]->total_approval : '-';
				$user = Auth::user();
				$action = '<a class="color-content table-action-style" href="' . route('customers.view', ['id' => $customer->entity_id]) . '"><i class="material-icons md-18">remove_red_eye</i></a>';
				if ($user->hasRole('Super Admin')) {
					$action .= '&nbsp;<a class="color-content table-action-style btn-delete-customer" style="cursor:pointer;" data-href="' . route('customers.delete', ['id' => $customer->entity_id]) . '"><i class="material-icons md-18">delete</i></a>';
				}

				$data['data'][] = array(!empty($customerName) ? $customerName : '-', $frnCode, $dmlUserCode, $contactNumber, $location, $totalApprovalProducts, $action);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	//View customer quotation
	public function viewCustomerQuotation($id) {
		if (!empty($id)) {
			$stoneRateData = DB::select('select * from dml_customer_quotation_rate where customer_id=' . $id);
			$customerName = InventoryHelper::getCustomerName($id);
			$diamondShape = '';
			$diamondQuality = '';
			$stoneData = array();
			$diamondShapeData = array();
			$diamondShapeData['round'] = array();
			$diamondShapeData['fancy2'] = array();
			$diamondShapeData['fancy1'] = array();
			$roundShape = config('constants.enum.diamond_shape.round'); //to check stone shape
			$fancy2Shape = config('constants.enum.diamond_shape.fancy2'); //to check stone shape
			$diamondIndex = 0;
			foreach ($stoneRateData as $key => $stone) {
				$diamondShape = $stone->stone_shape;
				$diamondQuality = $stone->stone_quality;
				$key = max($key - 1, 0);
				if (in_array($diamondShape, $roundShape)) {
					$diamondShapeData['round'][$key]['stone_quality'] = $diamondQuality;
					$diamondShapeData['round'][$key]['diamondShape'] = $diamondShape;
				} else if (in_array($diamondShape, $fancy2Shape)) {
					$diamondShapeData['fancy2'][$key]['stone_quality'] = $diamondQuality;
					$diamondShapeData['fancy2'][$key]['diamondShape'] = $diamondShape;
				} else {
					$diamondShapeData['fancy1'][$key]['stone_quality'] = $diamondQuality;
					$diamondShapeData['fancy1'][$key]['diamondShape'] = $diamondShape;
				}
				$diamondIndex++;
			}
			//echo "<pre>";
			//print_r($diamondShapeData);exit;
			DB::setTablePrefix('dml_');
			$shapesWeGot = array();
			if (count($diamondShapeData['round']) > 0) {
				$shapesWeGot[] = 'round';
			}
			if (count($diamondShapeData['fancy1']) > 0) {
				$shapesWeGot[] = 'fancy1';
			}
			if (count($diamondShapeData['fancy2']) > 0) {
				$shapesWeGot[] = 'fancy2';
			}
			$stoneRangeData = InventoryHelper::getStoneRangeData($shapesWeGot);
			return view('customers.viewcustomerquotation', compact('diamondShapeData', 'stoneRangeData', 'customerName', 'id'));
		}
	}
	//Get total quotation count by customer id
	public function getQuotationCount(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$quotationCount = '';
		if (!empty($customerId)) {
			$quotationCount = Quotation::where("customer_id", "=", DB::raw("$customerId"))->get()->count();
			if ($quotationCount > 0) {
				$response['status'] = true;
				$response['message'] = '';
			} else {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.quotation_not_created_for_customer');
			}
		}

		echo json_encode($response);exit;
	}
	//View customer detail
	public function view($id) {

		if (!empty($id)) {
			DB::setTablePrefix('');
			$customerData = DB::table('customer_entity')->select('entity_id', 'email')->where('entity_id', '=', DB::raw("$id"))->get()->first();

			$defaultBillingAddress = InventoryHelper::getDefaultBillingAddressByCustId($id);
			$defaultShippingAddress = InventoryHelper::getDefaultShippingAddresByCustId($id);

			$customerName = InventoryHelper::getCustomerName($id);
			$customerEmail = isset($customerData->email) ? $customerData->email : '';
			$panCardNumber = CustomersHelper::getPanCardNumberByCustomer($id);
			$gstinNumber = CustomersHelper::getGstinByCustomer($id);
			$gstinAttachment = CustomersHelper::getGstinAttachmentByCustomer($id);
			$panCardAtttachment = CustomersHelper::getPanCardAttachmentByCustomer($id);
			$primaryContact = CustomersHelper::getPrimarySecondoryContact($id, 'primary_contact');
			$secondaryContact = CustomersHelper::getPrimarySecondoryContact($id, 'secondary_contact');
			$location = CustomersHelper::getCustomerAttrValue($id, 'location');
			$priceMarkup = CustomersHelper::getCustomerAttrValue($id, 'price_markup');
			$frnCode = CustomersHelper::getCustomerAttrValue($id, 'frn_code');

			$stoneRateData = DB::select('select * from dml_customer_quotation_rate where customer_id=' . $id);

			$customerName = InventoryHelper::getCustomerName($id);
			$diamondShape = '';
			$diamondQuality = '';
			$stoneData = array();
			$diamondShapeData = array();
			$diamondShapeData['round'] = array();
			$diamondShapeData['fancy2'] = array();
			$diamondShapeData['fancy1'] = array();
			$roundShape = config('constants.enum.diamond_shape.round'); //to check stone shape
			$fancy2Shape = config('constants.enum.diamond_shape.fancy2'); //to check stone shape
			$diamondIndex = 0;
			foreach ($stoneRateData as $key => $stone) {
				$diamondShape = $stone->stone_shape;
				$diamondQuality = $stone->stone_quality;
				$key = max($key - 1, 0);
				if (in_array($diamondShape, $roundShape)) {
					$diamondShapeData['round'][$key]['stone_quality'] = $diamondQuality;
					$diamondShapeData['round'][$key]['diamondShape'] = $diamondShape;
				} else if (in_array($diamondShape, $fancy2Shape)) {
					$diamondShapeData['fancy2'][$key]['stone_quality'] = $diamondQuality;
					$diamondShapeData['fancy2'][$key]['diamondShape'] = $diamondShape;
				} else {
					$diamondShapeData['fancy1'][$key]['stone_quality'] = $diamondQuality;
					$diamondShapeData['fancy1'][$key]['diamondShape'] = $diamondShape;
				}
				$diamondIndex++;
			}

			$shapesWeGot = array();
			if (count($diamondShapeData['round']) > 0) {
				$shapesWeGot[] = 'round';
			}
			if (count($diamondShapeData['fancy1']) > 0) {
				$shapesWeGot[] = 'fancy1';
			}
			if (count($diamondShapeData['fancy2']) > 0) {
				$shapesWeGot[] = 'fancy2';
			}
			$stoneRangeData = InventoryHelper::getStoneRangeData($shapesWeGot);
			$paymentdata = Payment::where('customer_id', $id)->orderBy('id', 'desc')->paginate(10);
			$totalcount = Payment::where('customer_id', $id)->count();
			DB::setTablePrefix('dml_');
			$quotationCount = Quotation::where("customer_id", "=", DB::raw("$id"))->get()->count();

			/*Get approval products start*/
			$approvalProductCollection = CustomersHelper::getApprovalProducts($id);

			//Get No. of approval
			$approvalMemoCollection = CustomersHelper::getApprovalMemo($id);

			//Get invoices for customer
			$invoiceCollection = CustomersHelper::getInvoices($id);

			//Get returned products list
			$returnProductCollection = CustomersHelper::getReturnMemoProducts($id);

			//Get customer inventory
			$customerInventory = CustomersHelper::getCustomerInventory($id);

			//Get total amount
			$totalInvoiceAmount = CustomersHelper::getTotalAmount($id);

			//Get total sold products count
			$totalSoldProductsCount = CustomersHelper::getTotalSoldProductsCount($id);

			//Get total quotation count
			$totalQuotationCount = CustomersHelper::getTotalQuotationCount($id);

			//Get total sales return count
			$totalSalesReturnCount = CustomersHelper::getSalesReturnCount($id);

			//Get total paid amount
			$totalPaidAmount = CustomersHelper::getTotalPaidAmount($id);

			//Get total un paid amount
			$totalUnPaidAmount = CustomersHelper::getTotalUnPaidAmount($id);
			//print_r($paymentdata->count());exit;

			//Get sales return
			$salesReturnList = CustomersHelper::getSalesReturnList($id);

			//Get Credit note list
			$creditNoteList = CustomersHelper::getCreditNoteList($id);

			//Get total sales return products count
			$salesReturnProductsCount = CustomersHelper::getSalesReturnProducts($id);

			//Get total credit note count
			$totalCreditNoteCount = CustomersHelper::getCreditNoteCount($id);

			//Get wallet amount
			$walletAmount = CustomersHelper::getWalletAmount($id);

			//Get wallet transaction data
			$walletTransactionData = CustomersHelper::getWalletTransactions($id);

			//Get payment list
			$paymentData = CustomersHelper::getPaymentList($id);

			//Get all payent type
			$paymentType = PaymentType::select('id', 'name', 'parent_id')->where('parent_id', '!=', '0')->get();

			return view('customers.view', compact('diamondShapeData', 'stoneRangeData', 'customerName', 'id', 'customerEmail', 'defaultBillingAddress', 'defaultShippingAddress', 'quotationCount', 'approvalProductCollection', 'approvalMemoCollection', 'invoiceCollection', 'returnProductCollection', 'customerInventory', 'panCardNumber', 'gstinNumber', 'gstinAttachment', 'panCardAtttachment', 'primaryContact', 'secondaryContact', 'location', 'priceMarkup', 'paymentdata', 'totalcount', 'totalInvoiceAmount', 'totalSoldProductsCount', 'totalQuotationCount', 'totalSalesReturnCount', 'totalPaidAmount', 'totalUnPaidAmount', 'salesReturnList', 'salesReturnProductsCount', 'creditNoteList', 'totalCreditNoteCount', 'walletTransactionData', 'walletAmount', 'paymentData', 'paymentType', 'frnCode'));
		}
	}
	public function payment_detailresponse(Request $request) {

		$totalData = Payment::where('customer_id', $request->nid)->count();

		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		if (empty($request->input('search.value'))) {
			$resultslist = Payment::where('customer_id', $request->nid)->offset($start)
				->limit($limit)
				->orderBy('id', 'desc')
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = Payment::where('customer_id', $request->nid)->whereRaw('(invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')
				->offset($start)
				->limit($limit)
				->orderBy('id', 'desc')
				->get();
			$totalFiltered = Payment::where('customer_id', $request->nid)->whereRaw('(invoice_number LIKE "%' . $search . '%" OR invoice_amount LIKE "%' . $search . '%") ')->count();
		}
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {
				if ($resultslist->remaining_amount == "") {
					$paid_amount = CommonHelper::covertToCurrency(0.00);
				} else {
					$paid_amount = CommonHelper::covertToCurrency($resultslist->invoice_amount - $resultslist->remaining_amount);
				}

				if ($resultslist->remaining_amount == "") {
					$amount = CommonHelper::covertToCurrency($resultslist->invoice_amount);
				} else {
					$amount = CommonHelper::covertToCurrency($resultslist->remaining_amount);
				}
				$action = '<a href="javascript:void(0)" class ="payment_model " data-id="' . $resultslist->id . '" onclick=" " > <i class="material-icons"  title="Paid">credit_card</i></a>
                                 <a  href="' . action('PaymentController@show', $resultslist->id) . '" onclick=" "><i class="material-icons"  title="View Transaction">remove_red_eye</i></a>';
				$data[] = array(++$start, $resultslist->invoice_number, $resultslist->due_date, CommonHelper::covertToCurrency($resultslist->invoice_amount), $paid_amount, $amount, $action);
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

	public function approvalProductsAjaxList(Request $request) {
		$data = array();
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $stalen + 1;
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
		$approvalType = isset($params['approval_type']) ? $params['approval_type'] : '';

		$approvalProductIds = array();
		DB::setTablePrefix('');
		$approvalOrders = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.customer_id', 'memo.approval_no', 'memo.created_at', 'memo.is_for_old_data', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->rightJoin('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.customer_id', '=', DB::raw("$customerId"))->groupBy('memo_histroy.approval_memo_id');

		if (!empty($approvalType)) {
			$approvalOrders = $approvalOrders->where("memo.approval_type", "=", DB::raw("'$approvalType'"));
		}
		$approvalOrders = $approvalOrders->get();

		$approvalMemoNumbers = array();
		foreach ($approvalOrders as $key => $memo) {

			if (isset($memo->product_ids)) {
				$productIds = explode(',', $memo->product_ids);
				foreach ($productIds as $key => $productId) {
					$approvalProductIds[] = $productId;
					$currentYear = date('y', strtotime($memo->created_at));
					if (isset($memo->is_for_old_data) && $memo->is_for_old_data == 'yes') {
						$memoNumber = isset($memo->approval_no) ? $memo->approval_no : '';
					} else {
						$memoNumber = isset($memo->approval_no) ? $currentYear . '-' . ($currentYear + 1) . '/' . $memo->approval_no : '';
					}
					$approvalMemoNumbers[$productId] = $memoNumber;
				}
			}
		}
		$approvalProductIds = array_unique($approvalProductIds);
		/*echo "<pre>";
		print_r($approvalProductIds);exit;*/
		$prod = InventoryHelper::getAllProductsCollection(true);
		$prod = $prod->whereIn('entity_id', $approvalProductIds);
		if (!empty($searchValue)) {

			$prod = $prod->filter(function ($value, $key) use ($searchValue) {
				if (stripos($value->sku, $searchValue) !== false) {
					return $value;
				} else if (stripos($value->certificate_no, $searchValue) !== false) {
					return $value;
				} else if ($value->custom_price == $searchValue) {
					return $value;
				} else if (stripos($value->rts_stone_quality, $searchValue) !== false) {
					return $value;
				} else if (stripos($value->product_name, $searchValue) !== false) {
					return $value;
				} else {
					$searchValue = substr($searchValue, 6);
					$approvalMemoProducts = DB::table('dml_approval_memo as memo')->select('memo_histroy.product_id')->join('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.approval_no', 'LIKE', DB::raw("'%$searchValue%'"))->get();
					$productIds = array();
					$approvalProductIds = array();
					foreach ($approvalMemoProducts as $key => $approvalMemo) {
						if (isset($approvalMemo->product_id)) {
							$productIds[] = $approvalMemo->product_id;
						}
					}
					if (in_array($value->entity_id, $productIds)) {
						return $value;
					}

				}
			});
		}
		$productCount = $prod->count();
		$approvalProductCollection = $prod->forPage($curpage, $length);

		$data["draw"] = $params['draw'];
		$data["recordsTotal"] = $productCount;
		$data["recordsFiltered"] = $productCount;
		$data['deferLoading'] = $productCount;

		$imageDirectory = config('constants.dir.website_url_for_product_image');
		$defaultProductImage = $imageDirectory . 'def_1.png';

		if (count($approvalProductCollection) > 0) {
			foreach ($approvalProductCollection as $key => $product) {
				$productName = isset($product->product_name) ? $product->product_name : '';
				$certificateNo = isset($product->certificate_no) ? $product->certificate_no : '';
				$rtsStoneQuality = !isset($product->rts_stone_quality) ? $product->rts_stone_quality : '-';
				$productPrice = isset($product->custom_price) ? ShowroomHelper::currencyFormat(round($product->custom_price)) : '';
				$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				$productImage = '<img alt="' . $product->sku . '" class="product-img" src="' . (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage) . '">';
				$position = strpos($product->sku, ' ');
				$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
				$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
				$categoryName = isset($categoryNames[0]->category_name) ? $categoryNames[0]->category_name : '';
				$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
				$approvalMemoNumber = isset($approvalMemoNumbers[$product->entity_id]) ? $approvalMemoNumbers[$product->entity_id] : '';
				$product_approval_memo_generated = (!empty($product->approval_memo_generated) ? $product->approval_memo_generated : 0);
				$product_approval_invoice_generated = (!empty($product->approval_invoice_generated) ? $product->approval_invoice_generated : 0);
				$product_return_memo_generated = (!empty($product->return_memo_generated) ? $product->return_memo_generated : 0);

				if ($product_approval_invoice_generated == '1') {
					$action = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                            <option value="">Select</option>
                            <option value="invoice" data-productid=' . $product->entity_id . ' disabled>Generate Invoice</option>
                            <option value="returnmemo" data-productid=' . $product->entity_id . '" disabled>Generate Return Memo</option>
                        </select>';
				} else if ($product_approval_memo_generated == '1' && $product_return_memo_generated == '0') {
					$action = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                            <option value="">Select</option>
                            <option value="invoice" data-productid=' . $product->entity_id . '>Generate Invoice</option>
                            <option value="returnmemo" data-productid=' . $product->entity_id . ' >Generate Return Memo</option>
                        </select>';
				} else if ($product_return_memo_generated == '1') {
					$action = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                            <option value="">Select</option>
                            <option value="invoice" data-productid=' . $product->entity_id . '>Generate Invoice</option>
                            <option value="returnmemo" data-productid=' . $product->entity_id . ' disabled>Generate Return Memo</option>
                        </select>';
				} else {
					$action = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                            <option value="">Select</option>
                            <option value="invoice" data-productid=' . $product->entity_id . '>Generate Invoice</option>
                            <option value="returnmemo" data-productid=' . $product->entity_id . '">Generate Return Memo</option>
                        </select>';
				}
				$checkbox = '<td><label><input class="form-check-input chkProduct" data-id="' . $product->entity_id . '" value="' . $product->entity_id . '" type="checkbox" name="chkProduct[]" id="chkProduct' . $product->entity_id . '"><span class="label-text"></label></td>';
				$data['data'][] = array($checkbox, $productImage, $productName, $sku, $certificateNo, $approvalMemoNumber, $categoryName, $rtsStoneQuality, $virtualproductposition, $productPrice, $action);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	public function approvalMemoAjaxList(Request $request) {
		$data = array();
		$params = $request->post();
		$approvalType = isset($params['approval_type']) ? $params['approval_type'] : '';
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$curpage = $start;
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');

		DB::setTablePrefix('');
		$generatedMemoList = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.customer_id', 'memo.approval_no', 'memo.is_delivered', 'memo.created_at', 'memo.is_for_old_data', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.customer_id', '=', DB::raw("$customerId"))->groupBy('memo_histroy.approval_memo_id')->orderBy('memo.created_at', 'DESC');

		if (!empty($searchValue)) {
			if (strpos($searchValue, '-') !== false || strpos($searchValue, '/') !== false) {
				$searchValue = substr($searchValue, 6);
			} else if (strpos(strtolower($searchValue), 'dml') !== false) {
				$searchValue = str_replace('dml', '', strtolower($searchValue));
			}
			$generatedMemoList = $generatedMemoList->where("memo.approval_no", "LIKE", DB::raw("'%$searchValue%'"))->orWhere('memo.customer_id', 'LIKE', DB::raw("'%$searchValue%'"));
		}
		if (!empty($approvalType)) {
			$generatedMemoList = $generatedMemoList->where("memo.approval_type", "=", DB::raw("'$approvalType'"));
		}
		$totalCount = $generatedMemoList->get()->count();
		//echo $generatedMemoList->toSql();exit;
		$generatedMemoList = $generatedMemoList->offset($curpage)->take($length)->get();
		//echo $generatedMemoList;exit;
		//print_r($generatedMemoList);exit;
		$data["draw"] = isset($params['draw']) ? $params['draw'] : 1;
		$data["recordsTotal"] = $totalCount;
		$data["recordsFiltered"] = $totalCount;
		$data['deferLoading'] = $totalCount;
		DB::setTablePrefix('dml_');
		if (count($generatedMemoList) > 0) {
			foreach ($generatedMemoList as $key => $memo) {
				$orderDate = date('d-m-Y', strtotime($memo->created_at));
				$customerName = InventoryHelper::getCustomerName($memo->customer_id);
				$productIds = explode(',', $memo->product_ids);
				$currentYear = date('y', strtotime($memo->created_at));
				if (isset($memo->is_for_old_data) && $memo->is_for_old_data == 'yes') {
					$approvalNumber = isset($memo->approval_no) ? $memo->approval_no : '';
				} else {
					$approvalNumber = isset($memo->approval_no) ? $currentYear . '-' . ($currentYear + 1) . '/' . $memo->approval_no : '';
				}

				$customerId = isset($memo->customer_id) ? $memo->customer_id : '';
				$grandTotal = 0;
				foreach ($productIds as $key => $productId) {
					DB::setTablePrefix('');
					$product = DB::table('catalog_product_flat_1')->select('custom_price')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
					$grandTotal += (float) $product->custom_price;
				}
				$grandTotal = ShowroomHelper::currencyFormat(round($grandTotal));
				$memoAction = '';
				$generateMemoClass = (!empty($memo->approval_no) || (InventoryHelper::isReturnMemoGenerated($approvalNumber) == true)) ? 'disabled' : '';
				$memoAction .= '<a title="Generate Memo" data-memoid="' . $memo->id . '" target="_blank" class="mr-1 ml-1 color-content table-action-style1 pointer btn-generate-approval ' . $generateMemoClass . '" ' . $generateMemoClass . '><i class="list-icon fa fa-file-text-o"></i></a>';
				$cancelMemoClass = (!empty($memo->approval_no) || (InventoryHelper::isReturnMemoGenerated($approvalNumber) == true)) ? 'disabled' : '';
				$memoAction .= '<a title="Cancel Memo" data-memoid="' . $memo->id . '" target="_blank" class="mr-1 ml-1 color-content table-action-style1 pointer btn-cancel-approval ' . $cancelMemoClass . '" ' . $cancelMemoClass . '><i class="list-icon fa fa-trash-o"></i></a>';

				$memoAction .= '<a title="View Memo" target="_blank" class="mr-1 ml-1 color-content table-action-style1" href="' . route('viewmemo', ['id' => $memo->id]) . '"><i class="list-icon fa fa-file-text-o"></i></a>';
				if (count($productIds) > 0) {
					$memoAction .= '<a title="Download Excel" target="_blank" data-id="' . $memo->id . '" class="mr-1 ml-1 pointer color-content table-action-style1 downloadmemoexcel"><i class="list-icon fa fa-file-excel-o"></i></a>';
				}
				//check if invoice is generated
				DB::setTablePrefix('dml_');
				$isInvoiceGenerate = ApprovalMemoHistroy::select('id')->where('approval_memo_id', '=', DB::raw("$memo->id"))->where('status', '!=', DB::raw("'invoice'"))->get()->count();
				$isInvoiceGenerate = ($isInvoiceGenerate == 0) ? 'disabled' : '';
				DB::setTablePrefix('');
				$memoAction .= '<a title="Generate Invoice" target="_blank" data-id="' . $memo->id . '" class="mr-1 ml-1 pointer color-content table-action-style1 btn-generate-invoice ' . $isInvoiceGenerate . '" ' . $isInvoiceGenerate . '><i class="list-icon fa fa-file-excel-o"></i></a>';
				DB::setTablePrefix('dml_');
				$isReturnMemoGenerated = ApprovalMemoHistroy::select('id')->where('approval_memo_id', '=', DB::raw("$memo->id"))->where('status', '!=', DB::raw("'return_memo'"))->where('status', '!=', DB::raw("'invoice'"))->get()->count();
				//$isReturnMemoGenerated = ($isReturnMemoGenerated == 0) ? 'disabled' : '';
				DB::setTablePrefix('');
				$generateReturnMemoClass = (empty($memo->approval_no) || ($isReturnMemoGenerated == 0)) ? 'disabled' : '';
				$memoAction .= '<a title="Generate Return Memo" target="_blank" data-id="' . $memo->id . '" class="mr-1 ml-1 pointer color-content table-action-style1 btn-generate-returnmemo ' . $generateReturnMemoClass . '" ' . $generateReturnMemoClass . '><i class="list-icon fa fa-retweet"></i></a>';
				$generateReturnMemoFlag = true;
				if (!empty($memo->is_delivered) && empty($memo->approval_no) || ($memo->is_for_old_data == 'yes')) {
					$generateReturnMemoFlag = false;
				}
				if (InventoryHelper::isReturnMemoGenerated($memo->approval_no) == true) {
					$generateReturnMemoFlag = false;
				}
				$deliveryClass = ((!empty($memo->is_delivered)) || InventoryHelper::isReturnMemoGenerated($approvalNumber) == true) ? 'disabled' : '';
				$memoAction .= '<a title="Delivery" id="btn-delivery-' . $memo->id . '" target="_blank" data-id="' . $memo->id . '" class="pointer btn-deliver-memo color-content table-action-style1 ' . $deliveryClass . '" ' . $deliveryClass . '><i class="list-icon fa fa-truck"></i></a>';
				$checkbox = '<label><input class="form-check-input chkApproval" data-id="' . $memo->id . '" value="' . $memo->id . '" type="checkbox" name="chkApproval[]" id="chkApproval' . $memo->id . '"><span class="label-text"></label>';

				if (empty($memo->approval_no)) {
					$approvalNumber = '-';
				}

				$data['data'][] = array($checkbox, $approvalNumber, 'DML' . $customerId, round(count($productIds)), $orderDate, $grandTotal, $memoAction);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	public function invoiceAjaxList(Request $request) {
		$data = array();
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $stalen;
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');

		DB::setTablePrefix('');
		$generatedInvoiceList = DB::table("sales_flat_order as main_table")
			->select("main_table.entity_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total", "main_table.status", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total", "oinv.invoice_shipping_charge", "oinv.gst_percentage")
			->where("qr_product_status", "=", DB::raw("'1'"))
			->where("main_table.customer_id", "=", DB::raw("$customerId"))
			->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
			->orderBy("oinv.created_at", "desc");
		if (!empty($searchValue)) {
			$generatedInvoiceList = $generatedInvoiceList->where("oinv.increment_id", "LIKE", DB::raw("'%$searchValue%'"));
		}
		//echo $generatedInvoiceList->toSql();exit;
		$totalCount = $generatedInvoiceList->get()->count();
		$generatedInvoiceList = $generatedInvoiceList->offset($curpage)->take($length)->get();
		//print_r($generatedMemoList);exit;
		$data["draw"] = $params['draw'];
		$data["recordsTotal"] = $totalCount;
		$data["recordsFiltered"] = $totalCount;
		$data['deferLoading'] = $totalCount;
		DB::setTablePrefix('dml_');
		if (count($generatedInvoiceList) > 0) {
			$totalGrandTotalPrice = 0;
			$totalDiscountAmount = 0;
			$price = 0;
			foreach ($generatedInvoiceList as $key => $invoice) {
				$inventoryAction = '';
				if (isset($invoice->gst_percentage) && !empty($invoice->gst_percentage)) {
					$invoiceGstPercentage = $invoice->gst_percentage;
				} else {
					$invoiceGstPercentage = 3;
				}
				$shippingCharge = isset($invoice->invoice_shipping_charge) ? $invoice->invoice_shipping_charge : 0;
				$customerName = InventoryHelper::getCustomerName($invoice->customer_id);
				$invoiceDate = isset($invoice->invoice_created_date) ? date('d-m-Y', strtotime($invoice->invoice_created_date)) : '';
				$invoiceNumber = isset($invoice->invoice_number) ? $invoice->invoice_number : '';
				$customerId = isset($invoice->customer_id) ? $invoice->customer_id : '';
				$finalGrandTotal = 0;
				$orderGrandTotal = $invoice->invoice_total;
				$invoiceItems = InventoryHelper::getInvoiceItems($invoice->invoice_ent_id);
				foreach ($invoiceItems as $key => $invoiceItem) {
					$price = $invoiceItem->price;
					$totalGrandTotalPrice += isset($invoiceItem->price) ? $invoiceItem->price : 0;
					$discountAmount = isset($invoiceItem->discount_amount) ? $invoiceItem->discount_amount : 0;
					$totalDiscountAmount += $discountAmount;
				}
				$totalInvoiceValue = ($totalGrandTotalPrice - $totalDiscountAmount);
				$totalInvoiceValue += $shippingCharge;
				$gstTotal = ($totalInvoiceValue * ($invoiceGstPercentage / 100));
				//echo $gstTotal;exit;
				$totalInvoiceValue += $gstTotal;
				$finalGrandTotal = $totalGrandTotalPrice;

				$orderItems = InventoryHelper::getOrderItems($invoice->entity_id);
				$inventoryAction .= "<a title='View Invoice' target='_blank'  class='color-content table-action-style' href='" . route('viewinvoice', ['id' => $invoice->invoice_ent_id]) . "'><i class='list-icon fa fa-book'></i></a>";
				if (count($orderItems) > 0) {
					$inventoryAction .= "<a title='Download Excel' target='_blank'  class='color-content table-action-style pointer downloadexcel' data-id='" . $invoice->invoice_ent_id . "'><i class='list-icon fa fa-file-excel-o'></i></a>";
				}
				$inventoryAction .= '';
				$data['data'][] = array($invoiceNumber, $customerName, 'DML' . $customerId, $invoiceDate, ShowroomHelper::currencyFormat(round($totalInvoiceValue)), $inventoryAction);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	public function returnedProductAjaxList(Request $request) {
		$data = array();
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $stalen + 1;
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');

		$productIds = array();
		$returnMemoNumbers = array();
		$returnMemoProducts = ReturnMemo::orderBy('created_at', 'desc')->select('*')->where('customer_id', '=', DB::raw("$customerId"))->get();
		foreach ($returnMemoProducts as $key => $returnMemo) {
			$productData = isset($returnMemo->product_data) ? json_decode($returnMemo->product_data) : '';
			foreach ($productData as $key => $product) {
				$productIds[] = isset($product->productid) ? $product->productid : '';
				$currentYear = date('y', strtotime($returnMemo->created_at));
				$returnMemoNumber = isset($returnMemo->return_number) ? $returnMemo->return_number : '';
				$returnMemoNumber = $currentYear . '-' . ($currentYear + 1) . '/' . $returnMemoNumber;

				$returnMemoNumbers[$product->productid] = $returnMemoNumber;
			}
		}
		$productIds = array_unique($productIds);
		$prod = InventoryHelper::getAllProductsCollection(true);
		$prod = $prod->whereIn("entity_id", $productIds);
		if (!empty($searchValue)) {
			$prod = $prod->filter(function ($value, $key) use ($searchValue) {
				if (stripos($value->sku, $searchValue) !== false) {
					return $value;
				} else if (stripos($value->certificate_no, $searchValue) !== false) {
					return $value;
				} else if ($value->custom_price == $searchValue) {
					return $value;
				} else if (stripos($value->rts_stone_quality, $searchValue) !== false) {
					return $value;
				} else if (stripos($value->product_name, $searchValue) !== false) {
					return $value;
				} else {
					$searchValue = substr($searchValue, 6);
					$returnMemoProducts = ReturnMemo::select('product_ids')->where('return_number', 'LIKE', DB::raw("'%$searchValue%'"))->get();

					$productIds = array();
					$returnedProductIds = array();
					foreach ($returnMemoProducts as $key => $returnMemo) {
						if (isset($returnMemo->product_ids)) {
							$ids = explode(',', $returnMemo->product_ids);
							foreach ($ids as $key => $id) {
								$productIds[] = $id;
							}
						}
					}
					if (in_array($value->entity_id, $productIds)) {
						return $value;
					}

				}
			});
		}
		$productCount = $prod->count();
		$returnMemoProductCollection = $prod->forPage($curpage, $length);

		$data["draw"] = $params['draw'];
		$data["recordsTotal"] = $productCount;
		$data["recordsFiltered"] = $productCount;
		$data['deferLoading'] = $productCount;

		$imageDirectory = config('constants.dir.website_url_for_product_image');
		$defaultProductImage = $imageDirectory . 'def_1.png';
		if (count($returnMemoProductCollection) > 0) {
			foreach ($returnMemoProductCollection as $key => $product) {
				$productName = isset($product->product_name) ? $product->product_name : '';
				$certificateNo = isset($product->certificate_no) ? $product->certificate_no : '';
				$rtsStoneQuality = !isset($product->rts_stone_quality) ? $product->rts_stone_quality : '-';
				$productPrice = isset($product->custom_price) ? ShowroomHelper::currencyFormat(round($product->custom_price)) : '';
				$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				$productImage = '<img alt="' . $product->sku . '" class="product-img" src="' . (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage) . '">';
				$position = strpos($product->sku, ' ');
				$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
				$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
				$categoryName = isset($categoryNames[0]->category_name) ? $categoryNames[0]->category_name : '';
				$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
				$returnMemoNumber = isset($returnMemoNumbers[$product->entity_id]) ? $returnMemoNumbers[$product->entity_id] : '';
				$data['data'][] = array($productImage, $productName, $sku, $certificateNo, $returnMemoNumber, $categoryName, $rtsStoneQuality, $virtualproductposition, $productPrice);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	public function customerInventoryAjaxList(Request $request) {
		$data = array();
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $start;
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
		DB::setTablePrefix('');

		/*$inventoryCollection = DB::table('catalog_product_flat_1 as ce')
			->select('*')
			->from(DB::raw("(select `ce`.`entity_id`, `ce`.`name`, `ce`.`sku`, `ce`.`certificate_no`, `ce`.`custom_price`, `ce`.`rts_stone_quality`, 'approval' AS purchased_as from `catalog_product_flat_1` as `ce` inner join `dml_approval_memo_histroy` as `memo_histroy` on `memo_histroy`.`product_id` = `ce`.`entity_id` inner join `dml_approval_memo` as `memo` on `memo`.`id` = `memo_histroy`.`approval_memo_id` where `memo_histroy`.`status` = 'approval' and `memo`.`customer_id` = ".DB::raw("$customerId")." group by `ce`.`entity_id`) union (select `ce`.`entity_id`, `ce`.`name`, `ce`.`sku`, `ce`.`certificate_no`, `ce`.`custom_price`, `ce`.`rts_stone_quality`, 'return_memo' AS purchased_as from `catalog_product_flat_1` as `ce` inner join `dml_return_memo_products` as `return_memo_products` on `return_memo_products`.`product_id` = `ce`.`entity_id` inner join `dml_return_memo` as `return_memo` on `return_memo`.`id` = `return_memo_products`.`return_memo_id` where `return_memo`.`customer_id` = ".DB::raw("$customerId")." group by `ce`.`entity_id`) union (select `ce`.`entity_id`, `ce`.`name`, `ce`.`sku`, `ce`.`certificate_no`, `ce`.`custom_price`, `ce`.`rts_stone_quality`, 'purchased' as purchased_as from `catalog_product_flat_1` as `ce` inner join `sales_flat_invoice_item` as `invoice_item` on `invoice_item`.`product_id` = `ce`.`entity_id` inner join `sales_flat_invoice` as `invoice` on `invoice`.`entity_id` = `invoice_item`.`parent_id` inner join `sales_flat_order` as `ord` on `ord`.`entity_id` = `invoice`.`order_id` where `ord`.`customer_id` = ".DB::raw("$customerId")." group by `ce`.`entity_id`) union (select `ce`.`entity_id`, `ce`.`name`, `ce`.`sku`, `ce`.`certificate_no`, `ce`.`custom_price`, `ce`.`rts_stone_quality`, 'sales_return' AS purchased_as from `catalog_product_flat_1` as `ce` inner join `dml_sales_return_products` as `sales_return_product` on `sales_return_product`.`product_id` = `ce`.`entity_id` inner join `dml_sales_return` as `sales_return` on `sales_return`.`id` = `sales_return_product`.`sales_return_id` where `sales_return`.`customer_id` = ".DB::raw("$customerId")." group by `ce`.`entity_id`)"));*/
		$inventoryProdCollection = DB::table('catalog_product_flat_1 as ce')
			->select('ce.entity_id', 'ce.name', 'ce.sku', 'ce.certificate_no', 'ce.custom_price', 'ce.rts_stone_quality', DB::raw("'purchased' as purchased_as"))
			->join('dml_invoice_products AS invoice_item', 'invoice_item.product_id', '=', 'ce.entity_id')
			->join('sales_flat_invoice AS invoice', 'invoice.entity_id', '=', 'invoice_item.invoice_id')
			->join('sales_flat_order AS ord', 'ord.entity_id', '=', 'invoice.order_id')
			->where('ord.customer_id', '=', DB::raw("$customerId"))
			->groupBy('ce.entity_id');
		if (!empty($searchValue)) {
			if (preg_match("/sale/i", $searchValue) || preg_match("/retur/i", $searchValue) || preg_match("/appr/i", $searchValue)) {
				$inventoryProdCollection = $inventoryProdCollection->where('ce.certificate_no', 'LIKE', DB::raw("''"))->orWhere('ce.sku', 'LIKE', DB::raw("''"))->orWhere('ce.rts_stone_quality', 'LIKE', DB::raw("''"));
			} else {
				if (!preg_match("/sale/i", $searchValue) && !preg_match("/retur/i", $searchValue) && !preg_match("/appr/i", $searchValue) && !preg_match("/purch/i", $searchValue)) {
					$inventoryProdCollection = $inventoryProdCollection->where('ce.certificate_no', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('ce.sku', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('ce.rts_stone_quality', 'LIKE', DB::raw("'%$searchValue%'"));
				}
			}
		}

		$returnMemoCollection = DB::table('catalog_product_flat_1 AS ce')
			->select('ce.entity_id', 'ce.name', 'ce.sku', 'ce.certificate_no', 'ce.custom_price', 'ce.rts_stone_quality', DB::raw("'return_memo' AS purchased_as"))
			->join('dml_return_memo_products AS return_memo_products', 'return_memo_products.product_id', '=', 'ce.entity_id')
			->join('dml_return_memo AS return_memo', 'return_memo.id', '=', 'return_memo_products.return_memo_id')
		//->unionAll($inventoryCollection)
			->where('return_memo.customer_id', '=', DB::raw("$customerId"))
			->groupBy('ce.entity_id');
		if (!empty($searchValue)) {
			if (preg_match("/sale/i", $searchValue) || preg_match("/purc/i", $searchValue) || preg_match("/appr/i", $searchValue)) {
				$returnMemoCollection = $returnMemoCollection->where('ce.certificate_no', 'LIKE', DB::raw("''"))->orWhere('ce.sku', 'LIKE', DB::raw("''"))->orWhere('ce.rts_stone_quality', 'LIKE', DB::raw("''"));
			} else {
				if (!preg_match("/sale/i", $searchValue) && !preg_match("/retur/i", $searchValue) && !preg_match("/appr/i", $searchValue) && !preg_match("/purch/i", $searchValue)) {
					$returnMemoCollection = $returnMemoCollection->where('ce.certificate_no', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('ce.sku', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('ce.rts_stone_quality', 'LIKE', DB::raw("'%$searchValue%'"));
				}
			}
		}

		$salesReturnCollection = DB::table('catalog_product_flat_1 AS ce')
			->select('ce.entity_id', 'ce.name', 'ce.sku', 'ce.certificate_no', 'ce.custom_price', 'ce.rts_stone_quality', DB::raw("'sales_return' AS purchased_as"))
			->join('dml_sales_return_products AS sales_return_product', 'sales_return_product.product_id', '=', 'ce.entity_id')
			->join('dml_sales_return AS sales_return', 'sales_return.id', '=', 'sales_return_product.sales_return_id')
		//->unionAll($inventoryCollection)
			->where('sales_return.customer_id', '=', DB::raw("$customerId"))
			->groupBy('ce.entity_id');
		if (!empty($searchValue)) {
			if (preg_match("/retur/i", $searchValue) || preg_match("/purc/i", $searchValue) || preg_match("/appr/i", $searchValue)) {
				$salesReturnCollection = $salesReturnCollection->where('ce.certificate_no', 'LIKE', DB::raw("''"))->orWhere('ce.sku', 'LIKE', DB::raw("''"))->orWhere('ce.rts_stone_quality', 'LIKE', DB::raw("''"));
			} else {
				if (!preg_match("/sale/i", $searchValue) && !preg_match("/retur/i", $searchValue) && !preg_match("/appr/i", $searchValue) && !preg_match("/purch/i", $searchValue)) {
					$salesReturnCollection = $salesReturnCollection->where('ce.certificate_no', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('ce.sku', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('ce.rts_stone_quality', 'LIKE', DB::raw("'%$searchValue%'"));
				}
			}
		}

		$inventoryCollection = DB::table('catalog_product_flat_1 AS ce')
			->select('ce.entity_id', 'ce.name', 'ce.sku', 'ce.certificate_no', 'ce.custom_price', 'ce.rts_stone_quality', DB::raw("'approval' AS purchased_as"))
			->join('dml_approval_memo_histroy AS memo_histroy', 'memo_histroy.product_id', '=', 'ce.entity_id')
			->join('dml_approval_memo AS memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')
			->union($returnMemoCollection)
			->union($inventoryProdCollection)
			->union($salesReturnCollection)
			->where('memo_histroy.status', '=', DB::raw("'approval'"))
			->where('memo.customer_id', '=', DB::raw("$customerId"))
			->groupBy('ce.entity_id');
		if (!empty($searchValue)) {
			if (preg_match("/retur/i", $searchValue) || preg_match("/purc/i", $searchValue) || preg_match("/sale/i", $searchValue)) {
				$inventoryCollection = $inventoryCollection->where('ce.certificate_no', 'LIKE', DB::raw("''"))->orWhere('ce.sku', 'LIKE', DB::raw("''"))->orWhere('ce.rts_stone_quality', 'LIKE', DB::raw("''"));
			} else {
				if (!preg_match("/sale/i", $searchValue) && !preg_match("/retur/i", $searchValue) && !preg_match("/appr/i", $searchValue) && !preg_match("/purch/i", $searchValue)) {
					$inventoryCollection = $inventoryCollection->where('ce.certificate_no', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('ce.sku', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('ce.rts_stone_quality', 'LIKE', DB::raw("'%$searchValue%'"));
				}
			}
		}
		//echo $inventoryCollection->toSql();exit;
		/*$inventoryCollection = DB::table('catalog_product_flat_1 as ce')
			                      ->select('ce.entity_id','ce.name','ce.sku','ce.certificate_no','ce.custom_price','ce.rts_stone_quality','invoice.increment_id AS invoice_approval_number',DB::raw("'invoice' as purchased_as"))
			                      ->join('sales_flat_invoice_item AS invoice_item', 'invoice_item.product_id', '=', 'ce.entity_id')
			                      ->join('sales_flat_invoice AS invoice', 'invoice.entity_id', '=', 'invoice_item.parent_id')
			                      ->join('sales_flat_order AS ord', 'ord.entity_id', '=', 'invoice.order_id')
		*/

		//echo $inventoryCollection->toSql();exit;
		$productCount = $inventoryCollection->count();
		$productCollection = $inventoryCollection->offset($curpage)->take($length)->get();
		$data["draw"] = $params['draw'];
		$data["recordsTotal"] = $productCount;
		$data["recordsFiltered"] = $productCount;
		$data['deferLoading'] = $productCount;
		if (count($productCollection) > 0) {
			$imageDirectory = config('constants.dir.website_url_for_product_image');
			$defaultProductImage = $imageDirectory . 'def_1.png';
			foreach ($productCollection as $key => $product) {
				$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				$productImage = '<img alt="' . $product->sku . '" class="product-img" src="' . (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage) . '">';
				$position = strpos($product->sku, ' ');
				$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
				$certificateNo = isset($product->certificate_no) ? $product->certificate_no : '';
				$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
				$categoryName = isset($categoryNames[0]->category_name) ? $categoryNames[0]->category_name : '';
				$rtsStoneQuality = !empty($product->rts_stone_quality) ? $product->rts_stone_quality : '-';
				$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
				$productPrice = isset($product->custom_price) ? ShowroomHelper::currencyFormat(round($product->custom_price)) : '';
				$status = 'N/A';
				if (isset($product->purchased_as) && $product->purchased_as == 'purchased') {
					$status = 'Purchased';
				} else if (isset($product->purchased_as) && $product->purchased_as == 'approval') {
					$status = 'Approval';
				} else if (isset($product->purchased_as) && $product->purchased_as == 'return_memo') {
					$status = 'Return';
				} else if (isset($product->purchased_as) && $product->purchased_as == 'sales_return') {
					$status = 'Sales Return';
				}
				$data['data'][] = array($productImage, $sku, $certificateNo, $categoryName, $rtsStoneQuality, $virtualproductposition, $productPrice, $status);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}

	//Refresh personal information

	//Get default billing address for edit
	public function getDefaultBillingAddress(Request $request) {
		$params = $request->post();

		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$editType = isset($params['edit_type']) ? $params['edit_type'] : '';
		if (!empty($customerId)) {
			if ($editType == 'billing_address') {
				$defaultAddress = InventoryHelper::getDefaultBillingAddressByCustId($customerId);
			} else if ($editType == 'shipping_address') {
				$defaultAddress = InventoryHelper::getDefaultShippingAddresByCustId($customerId);
			}

			$get_country_list = '';

			if (App::environment('local')) {
				$get_country_list = Config::get('constants.apiurl.local.get_country_list');
			} else {
				$get_country_list = Config::get('constants.apiurl.live.get_country_list');
			}

			$countryList = array();
			$ch = curl_init($get_country_list);
			curl_setopt($ch, CURLOPT_POST, true);
			//curl_setopt($ch, CURLOPT_POSTFIELDS, $returnMemoParams);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$info = curl_getinfo($ch);
			if (curl_errno($ch)) {
				$error_msg = curl_error($ch);
			}
			//print_r($result);exit;
			if (!empty($result)) {
				$countryList = json_decode($result);
			}
			$countryListArr = array();

			foreach ($countryList->data as $key => $countryitem) {
				$countryListArr[$key]['country_id'] = $countryitem->country_id;
				$countryListArr[$key]['name'] = $countryitem->name;
			}
			usort($countryListArr, function ($item1, $item2) {
				return $item1['name'] <=> $item2['name'];
			});
			return view('customers.editbillingaddressmodal')->with(array('defaultBillingAddress' => $defaultAddress, 'countryList' => $countryListArr, 'editType' => $editType, 'customerId' => $customerId));
		}
	}
	public function updateCustomerAddress(Request $request) {
		$params = $request->post();

		$firstName = isset($params['txtfirstname']) ? $params['txtfirstname'] : '';
		$lastName = isset($params['txtlastname']) ? $params['txtlastname'] : '';
		$contactNumber = isset($params['txtcontactnumber']) ? $params['txtcontactnumber'] : '';
		$country = isset($params['selectcountry']) ? $params['selectcountry'] : '';
		$state = isset($params['txtstateprovince']) ? $params['txtstateprovince'] : '';
		$street = isset($params['txtaddress']) ? $params['txtaddress'] : '';
		$city = isset($params['txtcity']) ? $params['txtcity'] : '';
		$zipCode = isset($params['txtzipcode']) ? $params['txtzipcode'] : '';
		$editType = isset($params['edit_type']) ? $params['edit_type'] : '';
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$addressId = isset($params['address_id']) ? $params['address_id'] : '';
		$updateAddressUrl = '';
		if (App::environment('local')) {
			$updateAddressUrl = Config::get('constants.apiurl.local.update_customer_address');
		} else if (App::environment('test')) {
			$updateAddressUrl = Config::get('constants.apiurl.test.update_customer_address');
		} else {
			$updateAddressUrl = Config::get('constants.apiurl.live.update_customer_address');
		}

		$updateAddressParam = 'customer_id=' . $customerId . '&address_id=' . $addressId . '&address_type=' . $editType . '&first_name=' . $firstName . '&last_name=' . $lastName . '&contact_number=' . $contactNumber . '&country=' . $country . '&state=' . $state . '&city=' . $city . '&zip_code=' . $zipCode . '&street=' . $street;

		//check contact number exist
		$isContactNumberExist = InventoryHelper::checkContactNumberValidation($customerId, $contactNumber);
		if ($isContactNumberExist) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_telephone_already_exist');
			echo json_encode($response);exit;
		}
		DB::setTablePrefix('dml_');

		if (!empty($customerId) && !empty($addressId)) {
			$ch = curl_init($updateAddressUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $updateAddressParam);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$result = json_decode($result);
			//print_r($result);exit;
			$info = curl_getinfo($ch);
			if (curl_errno($ch)) {
				$error_msg = curl_error($ch);
			}

			if (isset($result->status) && $result->status) {
				$response['status'] = true;
				$response['message'] = Config::get('constants.message.customer_address_updated_success');
			} else {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_default_failure_message');
			}
		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}
	//Refresh customer address after update
	public function refreshDefaultAddress(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$addressType = isset($params['edit_type']) ? $params['edit_type'] : '';
		if (!empty($customerId)) {
			if ($addressType == 'billing_address') {
				$customerAddress = InventoryHelper::getDefaultBillingAddressByCustId($customerId);
			} else {
				$customerAddress = InventoryHelper::getDefaultShippingAddresByCustId($customerId);
			}
			return view('customers.refreshaddress')->with(array('customerAddress' => $customerAddress));
		}
	}
	public function getCustomerAttachment(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$attachmentType = isset($params['attachment_type']) ? $params['attachment_type'] : '';
		$attachmentName = '';
		$attachmentDir = '';
		if (!empty($customerId)) {
			if ($attachmentType == 'gstin') {
				$attachmentName = CustomersHelper::getGstinAttachmentByCustomer($customerId);
				$attachmentDir = 'gstin/';
			} else if ($attachmentType == 'pan_card') {
				$attachmentName = CustomersHelper::getPanCardAttachmentByCustomer($customerId);
				$attachmentDir = 'pancard/';
			}
			$attachment = URL::to('/uploads/') . '/' . $attachmentName;
			return view('customers.viewattachment')->with(array('attachment' => $attachment, 'attachmentType' => $attachmentType));
		}
	}
	//Add new pan/gstin
	public function addCustomerAttachment(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$attachmentType = isset($params['attachment_type']) ? $params['attachment_type'] : '';
		if (!empty($customerId)) {
			return view('customers.addattachment')->with(array('customerId' => $customerId, 'attachmentType' => $attachmentType));
		}
	}
	public function addCustomerPanGstin(Request $request) {
		$params = $request->post();
		$file = $request->file('attachment_file');
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$attachmentType = isset($params['attachment_type']) ? $params['attachment_type'] : '';
		$attachmentNumber = isset($params['attachment_no']) ? $params['attachment_no'] : '';
		$attachmentFileName = isset($params['attachment_name']) ? $params['attachment_name'] : '';
		$attachmentDir = '';
		if (!empty($customerId)) {
			if ($request->hasFile('attachment_file')) {
				$attachmentDir = '';
				if ($attachmentType == 'gstin') {
					$attachmentDir = 'gstin';
				} else if ($attachmentType == 'pan_card') {
					$attachmentDir = 'pancard';
				}
				$file = $request->file('attachment_file');
				$fileName = $file->getClientOriginalName();
				$fileExt = $file->getClientOriginalExtension();
				$fileSize = $file->getSize();
				$fileName = pathinfo($fileName, PATHINFO_FILENAME);
				$attachmentFileName = $fileName . '_' . time() . '.' . $fileExt;
				if (!file_exists('uploads/' . $attachmentDir)) {
					mkdir('uploads/' . $attachmentDir, 0777, true);
				}
				$destinationPath = 'uploads/' . $attachmentDir;
				$file->move($destinationPath, $attachmentFileName);
				//Upload to website directory
				$websitePath = '';
				if (App::environment('local')) {
					$websitePath = Config::get('constants.apiurl.local.website_url_for_gst_pan_attachment');
				} else {
					$websitePath = Config::get('constants.apiurl.live.website_url_for_gst_pan_attachment');
				}

				//$fromPath = URL::to('/uploads/'.$attachmentDir).'/'.$attachmentFileName;
				$fromPath = public_path('uploads' . DIRECTORY_SEPARATOR . $attachmentDir) . DIRECTORY_SEPARATOR . $attachmentFileName;
				//echo $fromPath;exit;
				$toPath = $websitePath . $attachmentDir . '/' . $attachmentFileName;
				//upload to site
				$remoteData = array(
					'file_path' => $fromPath,
					'fileData' => base64_encode($fromPath),
					'dirName' => $attachmentDir,
					'file_name' => $attachmentFileName,
				);
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $websitePath);
				curl_setopt($curl, CURLOPT_TIMEOUT, 30);
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $remoteData);
				$res = curl_exec($curl);
				$info = curl_getinfo($curl);
				curl_close($curl);

			}

			if ($attachmentType == 'gstin') {
				$gstinNumber = CustomersHelper::getGstinByCustomer($customerId);
				$gstinAttachment = CustomersHelper::getGstinAttachmentByCustomer($customerId);

				//update gstin number
				if (empty($gstinNumber) && !empty($attachmentNumber)) {
					$result = CustomersHelper::addCustomerAttributeValue($customerId, 'gstin', $attachmentNumber);
				} else {
					if (!empty($attachmentNumber)) {
						$result = CustomersHelper::updateCustomerAttributeValue($customerId, 'gstin', $attachmentNumber);
					}

				}
				if (empty($gstinAttachment) && !empty($attachmentFileName)) {
					$result = CustomersHelper::addCustomerAttributeValue($customerId, 'gst_attachment', $attachmentDir . '/' . $attachmentFileName);
				} else {
					if (!empty($attachmentFileName)) {
						$result = CustomersHelper::updateCustomerAttributeValue($customerId, 'gst_attachment', $attachmentDir . '/' . $attachmentFileName);
					}

				}
			} else if ($attachmentType == 'pan_card') {
				$panCardNumber = CustomersHelper::getPanCardNumberByCustomer($customerId);
				$panCardAtttachment = CustomersHelper::getPanCardAttachmentByCustomer($customerId);
				//update pan card
				if (empty($panCardNumber) && !empty($attachmentNumber)) {
					$result = CustomersHelper::addCustomerAttributeValue($customerId, 'pancardno', $attachmentNumber);
				} else {
					if (!empty($attachmentNumber)) {
						$result = CustomersHelper::updateCustomerAttributeValue($customerId, 'pancardno', $attachmentNumber);
					}

				}
				if (empty($panCardAtttachment) && !empty($attachmentFileName)) {
					$result = CustomersHelper::addCustomerAttributeValue($customerId, 'panattachment', $attachmentDir . '/' . $attachmentFileName);
				} else {
					if (!empty($attachmentFileName)) {
						$result = CustomersHelper::updateCustomerAttributeValue($customerId, 'panattachment', $attachmentDir . '/' . $attachmentFileName);
					}

				}
			}

			$response['status'] = true;
			//echo "test";exit;
			if ($attachmentType == 'gstin') {
				$response['message'] = config('constants.message.customer_gstin_updated_success');
			} else {
				$response['message'] = config('constants.message.customer_pancard_updated_success');
			}
		} else {
			$response['status'] = false;
			$response['message'] = config('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}
	public function editGstinPanCard(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$attachmentType = isset($params['attachment_type']) ? $params['attachment_type'] : '';
		$attachmentNumber = '';
		$attachmentName = '';
		if ($attachmentType == 'gstin') {
			$attachmentNumber = CustomersHelper::getGstinByCustomer($customerId);
			$attachmentName = CustomersHelper::getGstinAttachmentByCustomer($customerId);
		} else if ($attachmentType == 'pan_card') {
			$attachmentNumber = CustomersHelper::getPanCardNumberByCustomer($customerId);
			$attachmentName = CustomersHelper::getPanCardAttachmentByCustomer($customerId);
		}
		return view('customers.addattachment')->with(array('customerId' => $customerId, 'attachmentType' => $attachmentType, 'attachmentNumber' => $attachmentNumber, 'attachmentName' => $attachmentName, 'edit' => true));
	}
	//Refresh GSTIN/PAN after update
	public function refreshGstinPancard(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		if (!empty($customerId)) {
			$gstinNumber = CustomersHelper::getGstinByCustomer($customerId);
			$gstinAttachment = CustomersHelper::getGstinAttachmentByCustomer($customerId);

			$panCardNumber = CustomersHelper::getPanCardNumberByCustomer($customerId);
			$panCardAtttachment = CustomersHelper::getPanCardAttachmentByCustomer($customerId);

			return view('customers.refreshgstinpancard')->with(array('customerId' => $customerId, 'gstinNumber' => $gstinNumber, 'gstinAttachment' => $gstinAttachment, 'panCardNumber' => $panCardNumber, 'panCardAtttachment' => $panCardAtttachment));
		}
	}
	public function editPersonalInfo(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		if (!empty($customerId)) {
			$firstName = CustomersHelper::getCustomerFirstName($customerId);
			$lastName = CustomersHelper::getCustomerLastName($customerId);
			$email = CustomersHelper::getCustomerEmail($customerId);
			$primaryContact = CustomersHelper::getPrimarySecondoryContact($customerId, 'primary_contact');
			$secondaryContact = CustomersHelper::getPrimarySecondoryContact($customerId, 'secondary_contact');
			$location = CustomersHelper::getCustomerAttrValue($customerId, 'location');
			$location = !empty($location) ? $location : '';
			$frnCode = CustomersHelper::getCustomerAttrValue($customerId, 'frn_code');
			$frnCode = !empty($frnCode) ? $frnCode : '';
			return view('customers.editpersonalinfo')->with(array('customerId' => $customerId, 'firstName' => $firstName, 'lastName' => $lastName, 'email' => $email, 'primaryContact' => $primaryContact, 'secondaryContact' => $secondaryContact, 'location' => $location, 'frnCode' => $frnCode));
		}
	}
	public function updatePersonalInfo(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$firstName = isset($params['txtfirstname']) ? $params['txtfirstname'] : '';
		$lastName = isset($params['txtlastname']) ? $params['txtlastname'] : '';
		$primaryContact = isset($params['txtprimarycontact']) ? $params['txtprimarycontact'] : '';
		$secondaryContact = isset($params['txtsecondarycontact']) ? $params['txtsecondarycontact'] : '';
		$location = isset($params['txtlocation']) ? $params['txtlocation'] : '';
		$frnCode = isset($params['txtfrncode']) ? $params['txtfrncode'] : '';

		if (!empty($customerId)) {
			if (!empty($primaryContact)) {
				$isPrimaryContactExist = CustomersHelper::isContactNumberExist($customerId, 'primary_contact', $primaryContact);
				if ($isPrimaryContactExist) {
					$response['status'] = false;
					$response['message'] = Config::get('constants.message.customer_primary_contact_already_exist');
					echo json_encode($response);exit;
				}
			}
			if (!empty($secondaryContact)) {
				$isSecondaryContactExist = CustomersHelper::isContactNumberExist($customerId, 'secondary_contact', $secondaryContact);
				if ($isSecondaryContactExist) {
					$response['status'] = false;
					$response['message'] = Config::get('constants.message.customer_secondary_contact_already_exist');
					echo json_encode($response);exit;
				}
			}
			$isFrnCodeExist = InventoryHelper::checkFRNCodeValidation($customerId, $frnCode);
			if ($isFrnCodeExist) {
				if ($isFrnCodeExist) {
					$response['status'] = false;
					$response['message'] = Config::get('constants.message.inventory_frncode_already_exist');
					echo json_encode($response);exit;
				}
			}
			CustomersHelper::updateCustomerName($customerId, $firstName, 'firstname');
			CustomersHelper::updateCustomerName($customerId, $lastName, 'lastname');
			$customerPrimaryContact = CustomersHelper::getPrimarySecondoryContact($customerId, 'primary_contact');
			$customerSecondaryContact = CustomersHelper::getPrimarySecondoryContact($customerId, 'secondary_contact');
			$customerLocation = CustomersHelper::getCustomerAttrValue($customerId, 'location');
			$customerFrnCode = CustomersHelper::getCustomerAttrValue($customerId, 'frn_code');

			//print_r($customerLocation);exit;
			if (empty($customerLocation)) {
				CustomersHelper::addCustomerAttributeValue($customerId, 'location', $location);
			} else {
				CustomersHelper::updateCustomerAttributeValue($customerId, 'location', $location);
			}
			if (empty($customerPrimaryContact)) {
				CustomersHelper::addCustomerAttributeValue($customerId, 'primary_contact', $primaryContact);
			} else {
				CustomersHelper::updateCustomerAttributeValue($customerId, 'primary_contact', $primaryContact);
			}
			if (empty($customerSecondaryContact) && !empty($secondaryContact)) {
				CustomersHelper::addCustomerAttributeValue($customerId, 'secondary_contact', $secondaryContact);
			} else {
				CustomersHelper::updateCustomerAttributeValue($customerId, 'secondary_contact', $secondaryContact);
			}

			if (empty($customerFrnCode) && !empty($frnCode)) {
				try
				{
					CustomersHelper::addCustomerAttributeValue($customerId, 'frn_code', $frnCode);
				} catch (Exception $e) {
					CustomersHelper::updateCustomerAttributeValue($customerId, 'frn_code', $frnCode);
				}
			} else {
				CustomersHelper::updateCustomerAttributeValue($customerId, 'frn_code', $frnCode);
			}
			$response['status'] = true;
			$response['message'] = config('constants.message.customer_personalinfo_updated_success');
		} else {
			$response['status'] = false;
			$response['message'] = config('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}
	//Refresh personal information
	public function refreshPersonalInfo(Request $request) {
		$params = $request->post();

		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		if (!empty($customerId)) {
			$customerName = InventoryHelper::getCustomerName($customerId);
			DB::setTablePrefix('');
			$customerData = DB::table('customer_entity')->select('entity_id', 'email')->where('entity_id', '=', DB::raw("$customerId"))->get()->first();
			$customerEmail = isset($customerData->email) ? $customerData->email : '';
			$primaryContact = CustomersHelper::getPrimarySecondoryContact($customerId, 'primary_contact');
			$secondaryContact = CustomersHelper::getPrimarySecondoryContact($customerId, 'secondary_contact');
			$location = CustomersHelper::getCustomerAttrValue($customerId, 'location');
			$frnCode = CustomersHelper::getCustomerAttrValue($customerId, 'frn_code');
			$frnCode = !empty($frnCode) ? $frnCode : '';
			DB::setTablePrefix('dml_');
			return view('customers.refreshpersonalinfo')->with(array('customerId' => $customerId, 'customerName' => $customerName, 'customerEmail' => $customerEmail, 'primaryContact' => $primaryContact, 'secondaryContact' => $secondaryContact, 'location' => $location, 'frnCode' => $frnCode));
		}
	}

	public function discountstore(Request $request) {

		$customerId = $request->customerId;

		$discount_approval_less_25 = (!empty($request->discount_approval_less_25) ? ($request->discount_approval_less_25) : 0);
		$discount_approval_25_to_lakhs = (!empty($request->discount_approval_25_to_lakhs) ? ($request->discount_approval_25_to_lakhs) : 0);
		$discount_approval_above_lakhs = (!empty($request->discount_approval_above_lakhs) ? ($request->discount_approval_above_lakhs) : 0);

		$approvaldiscountless25 = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.approval_less25'));

		$approvaldiscount25to100 = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.approval_25tolakhs'));

		$approvaldiscountabovelaks = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.approval_abovelakhs'));

		if ($approvaldiscountless25 == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_less25'), $discount_approval_less_25);

		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_less25'), $discount_approval_less_25);
		}

		if ($approvaldiscount25to100 == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_25tolakhs'), $discount_approval_25_to_lakhs);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_25tolakhs'), $discount_approval_25_to_lakhs);
		}
		if ($approvaldiscountabovelaks == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_abovelakhs'), $discount_approval_above_lakhs);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_abovelakhs'), $discount_approval_above_lakhs);
		}

		/*for 18k*/

		$discount_approval_less_25_18k = (!empty($request->discount_approval_less_25_18k) ? ($request->discount_approval_less_25_18k) : 0);
		$discount_approval_25_to_lakhs_18k = (!empty($request->discount_approval_25_100k_18k) ? ($request->discount_approval_25_100k_18k) : 0);
		$discount_approval_above_lakhs_18k = (!empty($request->discount_approval_gt_100k_18k) ? ($request->discount_approval_gt_100k_18k) : 0);

		$approvaldiscountless25_18k = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.approval_less25_18K'));

		$approvaldiscount25to100_18k = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.approval_25tolakhs_18K'));

		$approvaldiscountabovelaks_18k = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.approval_abovelakhs_18K'));

		if ($approvaldiscountless25_18k == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_less25_18K'), $discount_approval_less_25_18k);

		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_less25_18K'), $discount_approval_less_25_18k);
		}

		if ($approvaldiscount25to100_18k == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_25tolakhs_18K'), $discount_approval_25_to_lakhs_18k);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_25tolakhs_18K'), $discount_approval_25_to_lakhs_18k);
		}
		if ($approvaldiscountabovelaks_18k == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_abovelakhs_18K'), $discount_approval_above_lakhs_18k);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.approval_abovelakhs_18K'), $discount_approval_above_lakhs_18k);
		}

		return redirect()->route('customers.view', $customerId)->with('success', Config::get('constants.message.Discount_approval'));

	}

	public function productdiscountstore(Request $request) {

		$customerId = $request->customerId;

		$discount_deposit_less_25 = (!empty($request->discount_deposit_less_25) ? ($request->discount_deposit_less_25) : 0);
		$discount_deposit_25_to_lakhs = (!empty($request->discount_deposit_25_to_lakhs) ? ($request->discount_deposit_25_to_lakhs) : 0);
		$discount_deposit_above_lakhs = (!empty($request->discount_deposit_above_lakhs) ? ($request->discount_deposit_above_lakhs) : 0);

		$depositdiscountless25 = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.deposit_less25'));

		$depositdiscount25to100 = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.deposit_25tolakhs'));

		$depositdiscountabovelaks = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.deposit_abovelakhs'));

		if ($depositdiscountless25 == "") {
			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_less25'), $discount_deposit_less_25);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_less25'), $discount_deposit_less_25);
		}

		if ($depositdiscount25to100 == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_25tolakhs'), $discount_deposit_25_to_lakhs);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_25tolakhs'), $discount_deposit_25_to_lakhs);
		}
		if ($depositdiscountabovelaks == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_abovelakhs'), $discount_deposit_above_lakhs);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_abovelakhs'), $discount_deposit_above_lakhs);
		}

		/* 18k */
		$discount_deposit_less_25_18k = (!empty($request->discount_deposit_less_25_18k) ? ($request->discount_deposit_less_25_18k) : 0);
		$discount_deposit_25_to_lakhs_18k = (!empty($request->discount_deposit_25_100k_18k) ? ($request->discount_deposit_25_100k_18k) : 0);
		$discount_deposit_above_lakhs_18k = (!empty($request->discount_deposit_gt_100k_18k) ? ($request->discount_deposit_gt_100k_18k) : 0);

		$depositdiscountless25_18k = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.deposit_less25_18K'));

		$depositdiscount25to100_18k = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.deposit_25tolakhs_18K'));

		$depositdiscountabovelaks_18k = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.deposit_abovelakhs_18K'));

		if ($depositdiscountless25_18k == "") {
			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_less25_18K'), $discount_deposit_less_25_18k);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_less25_18K'), $discount_deposit_less_25_18k);
		}

		if ($depositdiscount25to100_18k == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_25tolakhs_18K'), $discount_deposit_25_to_lakhs_18k);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_25tolakhs_18K'), $discount_deposit_25_to_lakhs_18k);
		}
		if ($depositdiscountabovelaks_18k == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_abovelakhs_18K'), $discount_deposit_above_lakhs_18k);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.deposit_abovelakhs_18K'), $discount_deposit_above_lakhs_18k);
		}
		return redirect()->route('customers.view', $customerId)->with('success', Config::get('constants.message.Discount_deposit'));
	}

	public function invoicediscountstore(Request $request) {

		$customerId = $request->customerId;

		$discount_invoice_less_25 = (!empty($request->discount_invoice_less_25) ? ($request->discount_invoice_less_25) : 0);
		$discount_invoice_25_to_lakhs = (!empty($request->discount_invoice_25_to_lakhs) ? ($request->discount_invoice_25_to_lakhs) : 0);
		$discount_invoice_above_lakhs = (!empty($request->discount_invoice_above_lakhs) ? ($request->discount_invoice_above_lakhs) : 0);

		$invoicediscountless25 = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.invoice_less25'));
		$invoicediscount25to100 = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.invoice_25tolakhs'));

		$invoicediscountabovelaks = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.invoice_abovelakhs'));

		if ($invoicediscountless25 == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_less25'), $discount_invoice_less_25);
		} else {

			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_less25'), $discount_invoice_less_25);
		}

		if ($invoicediscount25to100 == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_25tolakhs'), $discount_invoice_25_to_lakhs);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_25tolakhs'), $discount_invoice_25_to_lakhs);
		}

		if ($invoicediscountabovelaks == "") {
			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_abovelakhs'), $discount_invoice_above_lakhs);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_abovelakhs'), $discount_invoice_above_lakhs);
		}

		/* 18k */
		$discount_invoice_less_25_18k = (!empty($request->discount_invoice_less_25_18k) ? ($request->discount_invoice_less_25_18k) : 0);
		$discount_invoice_25_to_lakhs_18k = (!empty($request->discount_invoice_25_100k_18k) ? ($request->discount_invoice_25_100k_18k) : 0);
		$discount_invoice_above_lakhs_18k = (!empty($request->discount_invoice_gt_100k_18k) ? ($request->discount_invoice_gt_100k_18k) : 0);

		$invoicediscountless25_18k = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.invoice_less25_18K'));
		$invoicediscount25to100_18k = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.invoice_25tolakhs_18K'));

		$invoicediscountabovelaks_18k = CustomersHelper::getCustomerAttrValue($customerId, Config::get('constants.Discount_type.invoice_abovelakhs_18K'));

		if ($invoicediscountless25_18k == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_less25_18K'), $discount_invoice_less_25_18k);
		} else {

			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_less25_18K'), $discount_invoice_less_25_18k);
		}

		if ($invoicediscount25to100_18k == "") {

			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_25tolakhs_18K'), $discount_invoice_25_to_lakhs_18k);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_25tolakhs_18K'), $discount_invoice_25_to_lakhs_18k);
		}

		if ($invoicediscountabovelaks_18k == "") {
			CustomersHelper::addCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_abovelakhs_18K'), $discount_invoice_above_lakhs_18k);
		} else {
			CustomersHelper::updateCustomerAttributeValue($customerId, Config::get('constants.Discount_type.invoice_abovelakhs_18K'), $discount_invoice_above_lakhs_18k);
		}

		return redirect()->route('customers.view', $customerId)->with('success', Config::get('constants.message.Discount_invoice'));
	}
	//Store customer price markup
	public function storePriceMarkup(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$priceMarkup = isset($params['price_markup']) ? $params['price_markup'] : 0;
		if (!empty($customerId)) {
			if (is_numeric($priceMarkup)) {
				if ($priceMarkup >= 0 && $priceMarkup <= 100) {
					$customerPriceMarkup = CustomersHelper::getCustomerAttrValue($customerId, 'price_markup');
					//echo $customerPriceMarkup;exit;
					if ((string) $customerPriceMarkup == '') {
						CustomersHelper::addCustomerAttributeValue($customerId, 'price_markup', $priceMarkup);
					} else {

						CustomersHelper::updateCustomerAttributeValue($customerId, 'price_markup', $priceMarkup);
					}
					$response['status'] = true;
					$response['message'] = Config::get('constants.message.customer_pricemarkup_saved_success');
				} else {
					$response['status'] = false;
					$response['message'] = Config::get('constants.message.customer_pricemarkup_range_error');
				}
			} else {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.customer_pricemarkup_number_validation_error');
			}
		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.customer_pricemarkup_number_validation_error');
		}
		echo json_encode($response);exit;
	}
	//Delete customer
	public function delete($id) {
		if (!empty($id)) {
			$isDeleted = DB::statement("DELETE FROM customer_entity WHERE entity_id=" . DB::raw("$id") . "");
			if ($isDeleted) {
				return redirect()->back()->with('success', config('constants.message.customer_delete_success'));
			} else {
				return redirect()->back()->with('error', config('constants.message.customer_delete_failure'));
			}
		} else {
			return redirect()->back()->with('error', config('constants.message.customer_delete_failure'));
		}
	}
	public function salesreturnajaxlist(Request $request) {
		$params = $request->post();
		$data = array();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $start;
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');

		$salesReturnList = $salesReturnList = SalesReturn::where('customer_id', DB::raw("$customerId"));

		if (!empty($searchValue)) {
			$salesReturnList = $salesReturnList->where('sales_return_no', 'LIKE', DB::raw("'%{$searchValue}%'"))->orWhere('invoice_no', 'LIKE', DB::raw("'%{$searchValue}%'"));
		}

		$salesReturnCount = $salesReturnList->count();
		$salesReturnList = $salesReturnList->take($length)->offset($curpage)->orderBy('created_at', 'desc')->get();
		$data["draw"] = (int) $params['draw'];
		$data["recordsTotal"] = $salesReturnCount;
		$data["recordsFiltered"] = $salesReturnCount;
		$data['deferLoading'] = $salesReturnCount;
		//echo $salesReturnCount;exit;
		if ($salesReturnCount > 0) {
			foreach ($salesReturnList as $key => $salesReturn) {
				$returnNumber = isset($salesReturn->sales_return_no) ? $salesReturn->sales_return_no : '';
				$invoiceNumber = isset($salesReturn->invoice_no) ? $salesReturn->invoice_no : '';
				$createdDate = isset($salesReturn->created_at) ? date('Y-m-d H:i:s', strtotime($salesReturn->created_at)) : '';
				$grandTotal = isset($salesReturn->total_invoice_value) ? ShowroomHelper::currencyFormat(round($salesReturn->total_invoice_value)) : '';
				$isCreditNoteGenerated = isset($salesReturn->is_credited) ? $salesReturn->is_credited : 'no';
				$generateCreditNoteClass = '';
				$viewCreditNoteClass = 'disabled';
				if (!empty($isCreditNoteGenerated) && $isCreditNoteGenerated == 'yes') {
					$generateCreditNoteClass = 'disabled';
					$viewCreditNoteClass = '';
				}
				$action = '<a title="Generate Credit Note" class="color-content table-action-style btn-generate-creditnote pointer ' . $generateCreditNoteClass . '" data-href="' . route('generatecreditsalenote', ['id' => $salesReturn->id]) . '"><i class="material-icons">note_add</i></a>';
				$action .= '<a title="View Credit Note" class="color-content table-action-style ' . $viewCreditNoteClass . '" href="' . route('viewcreditsalenote', ['id' => $salesReturn->id]) . '"><i class="material-icons">remove_red_eye</i></a>';
				$data['data'][] = array($returnNumber, $invoiceNumber, $createdDate, $grandTotal, $action);
			}
		} else {
			$data['data'][] = array('', '', '', '', '');
		}

		echo json_encode($data);exit;
	}
	public function creditNoteAjaxList(Request $request) {
		$params = $request->post();
		$data = array();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $start;
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');

		$creditNoteList = SalesReturn::where('customer_id', DB::raw("$customerId"))->where('is_credited', '=', 'yes');

		if (!empty($searchValue)) {
			$creditNoteList = $creditNoteList->where('sales_return_no', 'LIKE', DB::raw("'%{$searchValue}%'"))->orWhere('invoice_no', 'LIKE', DB::raw("'%{$searchValue}%'"));
		}

		$creditNoteCount = $creditNoteList->count();
		$creditNoteList = $creditNoteList->take($length)->offset($curpage)->orderBy('created_at', 'desc')->get();
		$data["draw"] = (int) $params['draw'];
		$data["recordsTotal"] = $creditNoteCount;
		$data["recordsFiltered"] = $creditNoteCount;
		$data['deferLoading'] = $creditNoteCount;
		//echo $salesReturnCount;exit;
		if ($creditNoteCount > 0) {
			foreach ($creditNoteList as $key => $salesReturn) {
				$returnNumber = isset($salesReturn->sales_return_no) ? $salesReturn->sales_return_no : '';
				$invoiceNumber = isset($salesReturn->invoice_no) ? $salesReturn->invoice_no : '';
				$createdDate = isset($salesReturn->created_at) ? date('Y-m-d H:i:s', strtotime($salesReturn->created_at)) : '';
				$grandTotal = isset($salesReturn->total_invoice_value) ? ShowroomHelper::currencyFormat(round($salesReturn->total_invoice_value)) : '';
				$creditedBy = isset($salesReturn->credited_by) ? CustomersHelper::getUsername($salesReturn->credited_by) : '';
				$isCreditNoteGenerated = '';
				if (isset($salesReturn->is_credited) && $salesReturn->is_credited == 'no') {
					$isCreditNoteGenerated = 'disabled';
				}
				$action = '<a class="color-content table-action-style ' . $isCreditNoteGenerated . '" href="' . route('viewcreditsalenote', ['id' => $salesReturn->id]) . '">View Credit Note</a>';
				$data['data'][] = array($returnNumber, $invoiceNumber, $createdDate, $grandTotal, $creditedBy, $action);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '');
		}

		echo json_encode($data);exit;

	}
	public function walletAjaxList(Request $request) {
		$params = $request->post();
		$data = array();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $start;
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
		$walletData = CustomerWallet::where('customer_id', '=', DB::raw("$customerId"))->orderBy('id', 'DESC');
		if (!empty($searchValue)) {
			$walletData = $walletData->where('transaction_amt', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('transaction_type', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('created_at', 'LIKE', DB::raw("'%$searchValue%'"))->orWhere('ref_number', 'LIKE', DB::raw("'%$searchValue%'"));
		}
		//echo $walletData->toSql();exit;
		$walletTransactionCount = $walletData->get()->count();
		$walletData = $walletData->take($length)->offset($curpage)->orderBy('created_at', 'desc')->get();
		$data["draw"] = (int) $params['draw'];
		$data["recordsTotal"] = $walletTransactionCount;
		$data["recordsFiltered"] = $walletTransactionCount;
		$data['deferLoading'] = $walletTransactionCount;

		if ($walletTransactionCount > 0) {
			foreach ($walletData as $key => $transaction) {
				$refNumber = !empty($transaction->ref_number) ? $transaction->ref_number : 'N/A';
				$amount = isset($transaction->transaction_amt) ? ShowroomHelper::currencyFormat(round($transaction->transaction_amt)) : 0;
				$transactionType = !empty($transaction->transaction_type) ? ucfirst($transaction->transaction_type) : '';
				$createdDate = isset($transaction->created_at) ? date('d-m-Y', strtotime($transaction->created_at)) : '';
				$data['data'][] = array($amount, $transactionType, $refNumber, $createdDate);
			}
		} else {
			$data['data'][] = array('', '', '', '');
		}
		echo json_encode($data);exit;
	}
	//Payment list ajax
	public function paymentAjaxList(Request $request) {
		$params = $request->post();

		$data = array();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $start;
		$searchValue = (!empty($params['payment_search_value']) ? $params['payment_search_value'] : '');
		$paymentData = DB::table('payments')->select('payments.*', 'payment_types.name')->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->where('customer_id', '=', DB::raw("$customerId"))->whereNull('payments.deleted_at')->orderBy('id', 'DESC');
		if ($searchValue != '') {
			/*$paymentData = $paymentData->where('invoice_amount','LIKE',DB::raw("'%$searchValue%'"))->orWhere('payment_form','LIKE',DB::raw("'%$searchValue%'"))->orWhere('payments.created_at','LIKE',DB::raw("'%$searchValue%'"))->orWhere('due_date','LIKE',DB::raw("'%$searchValue%'"))->orWhere('name','LIKE',DB::raw("'%$searchValue%'"))->orWhere('payment_status','=',DB::raw("'$searchValue'"));*/
			if ($searchValue == '0' || $searchValue == '1') {
				$paymentData = $paymentData->where(function ($q) use ($searchValue) {
					$q->where(function ($query) use ($searchValue) {
						$query->where('payment_status', "like", DB::raw("'%$searchValue%'"));
					});
				});
			} else {
				$paymentData = $paymentData->where(function ($q) use ($searchValue) {
					$q->where(function ($query) use ($searchValue) {
						$query->where('invoice_amount', "like", DB::raw("'%$searchValue%'"));
					})->orWhere(function ($query) use ($searchValue) {
						$query->where('payment_form', "like", DB::raw("'%$searchValue%'"));
					})->orWhere(function ($query) use ($searchValue) {
						$query->where('payments.created_at', "like", DB::raw("'%$searchValue%'"));
					})->orWhere(function ($query) use ($searchValue) {
						$query->where('due_date', "like", DB::raw("'%$searchValue%'"));
					})->orWhere(function ($query) use ($searchValue) {
						$query->where('name', "like", DB::raw("'%$searchValue%'"));
					});
				});
			}
		}

		$sql = $paymentData->toSql();
		//echo $paymentData->toSql();exit;
		$paymentCount = $paymentData->get()->count();
		$paymentData = $paymentData->take($length)->offset($curpage)->orderBy('created_at', 'desc')->get();

		$data["draw"] = (int) $params['draw'];
		$data["recordsTotal"] = $paymentCount;
		$data["recordsFiltered"] = $paymentCount;
		$data['deferLoading'] = $paymentCount;
		$data['sql'] = $sql;

		if ($paymentCount > 0) {
			foreach ($paymentData as $payment) {
				$amount = isset($payment->invoice_amount) ? CommonHelper::covertToCurrency($payment->invoice_amount) : '';
				$paymentForm = isset($payment->payment_form) ? $payment->payment_form : '';
				$billedDate = isset($payment->created_at) ? date('Y-m-d', strtotime($payment->created_at)) : '';
				$dueDate = isset($payment->due_date) ? $payment->due_date : '';
				$paymentType = isset($payment->name) ? $payment->name : '';
				$paymentStatus = (isset($payment->payment_status) && $payment->payment_status) ? 'Paid' : 'Unpaid';
				$downloadClass = empty($payment->invoice_attachment) ? 'disabled' : '';
				$action = '<a class="ml-1 mr-1 color-content table-action-style ' . $downloadClass . '" href="' . route('accountpayment.pdflisting', ['id' => $payment->id]) . '" ' . $downloadClass . '><i class="material-icons md-18">file_download</i></a>';
				$action .= '<a class="ml-1 mr-1 pointer view-transaction" onclick="showPaymentTransaction(' . $payment->id . ')"><i class="material-icons"  title="View Transaction">remove_red_eye</i></a>';
				$data['data'][] = array($amount, $paymentForm, $billedDate, $dueDate, $paymentType, $paymentStatus, $action);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	public function paymentTransactionAjaxlist(Request $request) {
		$params = $request->post();
		$data = array();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$paymentId = isset($params['payment_id']) ? $params['payment_id'] : '';
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $stalen + 1;
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
		$transactionData = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->where('payment_id', $paymentId)->orderBy('id', 'DESC');

		if (empty($searchValue)) {
			$transactionData = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->where('payment_id', $paymentId)->offset($start)
				->limit($length)
				->orderBy('id', 'DESC')
				->get();
			$transactionCount = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->where('payment_id', $paymentId)->count();
		} else {
			$transactionData = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->where('payment_id', $paymentId)->whereRaw('(customer_name LIKE "%' . $searchValue . '%" OR dml_payment_transaction.invoice_number LIKE "%' . $searchValue . '%" OR dml_payment_transaction.invoice_amount LIKE "%' . $searchValue . '%") ')
				->offset($start)
				->limit($length)
				->orderBy('id', 'DESC')
				->get();
			$transactionCount = PaymentTransaction::join('payments', 'payment_transaction.payment_id', '=', 'payments.id')->select('payment_transaction.*', 'payment_transaction.remaining_amount as remaining', 'payments.remaining_amount', 'payments.customer_name', 'payments.due_date')->where('payment_id', $paymentId)->whereRaw('(customer_name LIKE "%' . $searchValue . '%" OR dml_payment_transaction.invoice_number LIKE "%' . $searchValue . '%" OR dml_payment_transaction.invoice_amount LIKE "%' . $searchValue . '%") ')->count();
		}
		//$transactionCount = $transactionData->get()->count();
		//$transactionData = $transactionData->take($length)->offset($curpage);
		//$sql = $transactionData->toSql();
		//$transactionData = $transactionData->get();
		$data["draw"] = (int) $params['draw'];
		$data["recordsTotal"] = $transactionCount;
		$data["recordsFiltered"] = $transactionCount;
		$data['deferLoading'] = $transactionCount;
		//$data['sql'] = $sql;

		if ($transactionCount > 0) {
			$index = (int) $params['draw'];
			//print_r($transactionData);exit;
			foreach ($transactionData as $key => $transaction) {
				$rowIndex = ++$index;
				$customerName = isset($transaction->customer_name) ? $transaction->customer_name : '';
				$invoiceNumber = isset($transaction->invoice_number) ? $transaction->invoice_number : '';
				$transactionAmount = isset($transaction->invoice_amount) ? $transaction->invoice_amount : '';
				$remainAmount = isset($transaction->remaining) ? $transaction->remaining : '';
				$paidDate = isset($transaction->paid_at) ? $transaction->paid_at : '';
				$data['data'][] = array(++$start, $customerName, $invoiceNumber, $transactionAmount, $remainAmount, $paidDate);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	//Get payment parent type
	public function getPaymentParentHeader(Request $request) {
		$params = $request->post();

		$paymentSubType = isset($params['payment_type']) ? $params['payment_type'] : '';
		if (!empty($paymentSubType)) {
			$payments = PaymentType::where('id', $paymentSubType);
			$response['status'] = true;
			$response['data'] = json_encode($payments->get()->toArray());
		} else {
			$response['status'] = false;
		}
		echo json_encode($response);exit;
	}
	public function createPayment(Request $request) {
		$params = $request->post();
		$user = Auth::user();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$customerName = isset($params['txtCustomerName']) ? $params['txtCustomerName'] : '';
		$invoiceNumber = isset($params['txtInvoiceNumber']) ? $params['txtInvoiceNumber'] : '';
		$invoiceAmount = isset($params['txtInvoiceAmount']) ? $params['txtInvoiceAmount'] : '';
		$dueDate = isset($params['txtDueDate']) ? $params['txtDueDate'] : '';
		$paymentForm = isset($params['payment_form']) ? $params['payment_form'] : '';
		$paymentSubType = isset($params['paymentSubType']) ? $params['paymentSubType'] : '';
		$paymentParentType = isset($params['payment_type']) ? $params['payment_type'] : '';
		$customerType = isset($params['customer_type']) ? $params['customer_type'] : '';
		$remarks = isset($params['txtRemarks']) ? $params['txtRemarks'] : '';
		$fileName = $user->id . '_attachment' . time() . '.' . $request->invoice_attachment->getClientOriginalExtension();
		$file = $request->invoice_attachment->move(config('constants.dir.purchased_invoices'), $fileName);
		$payment = new Payment;
		$payment->customer_name = $customerName;
		$payment->customer_id = $customerId;
		$payment->Invoice_number = $invoiceNumber;
		$payment->invoice_amount = $invoiceAmount;
		$payment->due_date = $dueDate;
		$payment->invoice_attachment = $fileName;
		$payment->payment_form = $paymentForm;
		$payment->payment_type = $paymentParentType;
		$payment->payment_sub_type = $paymentSubType;
		$payment->customer_type = $customerType;
		$payment->remarks = $remarks;
		$payment->created_by = Auth::user()->id;
		$lastCompanyId = Payment::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
		$lastCompanyId = (int) substr($lastCompanyId, -3);

		$transaction_id = '10000170' . $lastCompanyId + 1;
		$payment->transaction_id = $transaction_id;
		$result = $payment->save();
		if ($result) {
			$response['status'] = true;
			$response['message'] = config('constants.message.customer_payment_created_successfully');
		} else {
			$response['status'] = false;
			$response['message'] = config('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}
	//Create new customer
	public function createNewCustomer(Request $request) {
		$params = $request->post();
		$firstName = isset($params['txtfirstname']) ? $params['txtfirstname'] : '';
		$lastName = isset($params['txtlastname']) ? $params['txtlastname'] : '';
		$contactNumber = isset($params['txtcontactnumber']) ? $params['txtcontactnumber'] : '';
		$street = isset($params['txtaddress']) ? $params['txtaddress'] : '';
		$countryId = isset($params['selectcountry']) ? $params['selectcountry'] : '';
		$region = isset($params['txtstateprovince']) ? $params['txtstateprovince'] : '';
		$city = isset($params['txtcity']) ? $params['txtcity'] : '';
		$postcode = isset($params['txtzipcode']) ? $params['txtzipcode'] : '';
		$email = isset($params['txtemail']) ? $params['txtemail'] : '';
		$gstinNumber = isset($params['txtgstin']) ? $params['txtgstin'] : '';
		$gstinAttachment = $request->file('gstinattachment');
		$frnCode = isset($params['txtfrncode']) ? $params['txtfrncode'] : '';
		$password = $firstName . rand(10000, 999) . '@dealer';

		if (App::environment('local')) {
			$url = Config::get('constants.apiurl.local.create_customer');
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
		//Check frn code exit
		$isfrnExist = InventoryHelper::checkFRNCodeValidation('', $frnCode);

		if ($isfrnExist) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_frncode_already_exist');
			echo json_encode($response);exit;
		}
		if (!empty($frnCode)) {
			$frncodeStr = '&frncode=' . $frnCode;
		} else {
			$frncodeStr = '';
		}
		$customerParams = 'firstname=' . $firstName . '&lastname=' . $lastName . '&email=' . $email . '&contact_number=' . $contactNumber . '&community=' . '1' . '&street=' . $street . '&country_id=' . $countryId . '&region=' . $region . '&city=' . $city . '&entity_customer=' . '1' . '&postcode=' . $postcode . '&password=' . $password . '&confirmation=' . '1' . '&franchisee_status=' . '2' . $frncodeStr;
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
			if (isset($res->customer_id) && !empty($res->customer_id)) {
				$customerId = $res->customer_id;
				if ($request->hasFile('gstinattachment') && !empty($gstinNumber)) {
					$attachmentDir = 'gstin';
					$fileName = $gstinAttachment->getClientOriginalName();
					$fileExt = $gstinAttachment->getClientOriginalExtension();
					$fileSize = $gstinAttachment->getSize();
					$fileName = pathinfo($fileName, PATHINFO_FILENAME);
					$attachmentFileName = $fileName . '_' . time() . '.' . $fileExt;
					if (!file_exists('uploads/' . $attachmentDir)) {
						mkdir('uploads/' . $attachmentDir, 0777, true);
					}
					$destinationPath = 'uploads/' . $attachmentDir;
					$gstinAttachment->move($destinationPath, $attachmentFileName);
					$websitePath = '';
					if (App::environment('local')) {
						/*$websitePath = Config::get('constants.apiurl.local.website_url_for_gst_pan_attachment');*/
						$websitePath = Config::get('app.website_url_for_gst_pan_attachment');
					} else if (App::environment('test')) {
						$websitePath = Config::get('constants.apiurl.test.website_url_for_gst_pan_attachment');
					} else {
						$websitePath = Config::get('constants.apiurl.live.website_url_for_gst_pan_attachment');
					}
					$fromPath = public_path('uploads' . DIRECTORY_SEPARATOR . $attachmentDir) . DIRECTORY_SEPARATOR . $attachmentFileName;
					$toPath = $websitePath . $attachmentDir . '/' . $attachmentFileName;
					$remoteData = array(
						'file_path' => $fromPath,
						'fileData' => base64_encode($fromPath),
						'dirName' => $attachmentDir,
						'file_name' => $attachmentFileName,
					);
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $websitePath);
					curl_setopt($curl, CURLOPT_TIMEOUT, 30);
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $remoteData);
					$res = curl_exec($curl);
					$info = curl_getinfo($curl);
					curl_close($curl);

					$result = CustomersHelper::addCustomerAttributeValue($customerId, 'gstin', $gstinNumber);
					$result = CustomersHelper::addCustomerAttributeValue($customerId, 'gst_attachment', $attachmentDir . '/' . $attachmentFileName);
				} else if (!empty($gstinNumber) && !$request->hasFile('gstinattachment')) {
					if (App::environment('local')) {
						/*$websitePath = Config::get('constants.apiurl.local.website_url_for_gst_pan_attachment');*/
						$websitePath = Config::get('app.website_url_for_gst_pan_attachment');
					} else if (App::environment('test')) {
						$websitePath = Config::get('constants.apiurl.test.website_url_for_gst_pan_attachment');
					} else {
						$websitePath = Config::get('constants.apiurl.live.website_url_for_gst_pan_attachment');
					}
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $websitePath);
					curl_setopt($curl, CURLOPT_TIMEOUT, 30);
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					//curl_setopt($curl, CURLOPT_POSTFIELDS, $remoteData);
					$res = curl_exec($curl);
					$info = curl_getinfo($curl);
					curl_close($curl);

					$result = CustomersHelper::addCustomerAttributeValue($customerId, 'gstin', $gstinNumber);
				} else if (empty($gstinNumber) && $request->hasFile('gstinattachment')) {
					$attachmentDir = 'gstin';
					$fileName = $gstinAttachment->getClientOriginalName();
					$fileExt = $gstinAttachment->getClientOriginalExtension();
					$fileSize = $gstinAttachment->getSize();
					$fileName = pathinfo($fileName, PATHINFO_FILENAME);
					$attachmentFileName = $fileName . '_' . time() . '.' . $fileExt;
					if (!file_exists('uploads/' . $attachmentDir)) {
						mkdir('uploads/' . $attachmentDir, 0777, true);
					}
					$destinationPath = 'uploads/' . $attachmentDir;
					$gstinAttachment->move($destinationPath, $attachmentFileName);
					$websitePath = '';
					if (App::environment('local')) {
						/*$websitePath = Config::get('constants.apiurl.local.website_url_for_gst_pan_attachment');*/
						$websitePath = Config::get('app.website_url_for_gst_pan_attachment');
					} else if (App::environment('test')) {
						$websitePath = Config::get('constants.apiurl.test.website_url_for_gst_pan_attachment');
					} else {
						$websitePath = Config::get('constants.apiurl.live.website_url_for_gst_pan_attachment');
					}
					$fromPath = public_path('uploads' . DIRECTORY_SEPARATOR . $attachmentDir) . DIRECTORY_SEPARATOR . $attachmentFileName;
					$toPath = $websitePath . $attachmentDir . '/' . $attachmentFileName;
					$remoteData = array(
						'file_path' => $fromPath,
						'fileData' => base64_encode($fromPath),
						'dirName' => $attachmentDir,
						'file_name' => $attachmentFileName,
					);
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $websitePath);
					curl_setopt($curl, CURLOPT_TIMEOUT, 30);
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $remoteData);
					$res = curl_exec($curl);
					$info = curl_getinfo($curl);
					curl_close($curl);

					$result = CustomersHelper::addCustomerAttributeValue($customerId, 'gst_attachment', $attachmentDir . '/' . $attachmentFileName);
				}
			}
			$response['status'] = true;
			$response['message'] = Config::get('constants.message.customer_created_successfully');
		} else {
			$response['status'] = false;
			$response['message'] = $res->message;
		}
		echo json_encode($response);exit;
	}
}
