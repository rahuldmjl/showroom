<?php
namespace App\Helpers;
use App;
use App\CustomerWallet;
use App\Payment;
use App\Quotation;
use App\ReturnMemo;
use App\SalesReturn;
use App\User;
use DB;

class CustomersHelper {
	//Get all customers list
	public static function getAllCustomers() {
		DB::setTablePrefix('');
		/*$customers = DB::table('customer_entity as ce')
			->select('ce.entity_id', 'ce.email')->orderBy("entity_id", "DESC");*/

		$franchiseestatusAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='franchisee_status' order by attribute_id DESC");
		$franchiseestatusattrid = array();
		foreach ($franchiseestatusAttribute as $key => $attr) {
			$franchiseestatusattrid[] = $attr->attribute_id;
		}
		$franchiseestatusattrid = $franchiseestatusattrid[0];

		$frncodeAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='frn_code' order by attribute_id DESC");
		$frncodeattrid = array();
		foreach ($frncodeAttribute as $key => $attr) {
			$frncodeattrid[] = $attr->attribute_id;
		}
		$frncodeattrid = $frncodeattrid[0];

		$customers = DB::table('customer_entity as e')
			->join('customer_entity_int as at_franchisee_status', function ($join) use ($franchiseestatusattrid) {
				$join->on('at_franchisee_status.entity_id', '=', 'e.entity_id');
				$join->on('at_franchisee_status.attribute_id', '=', DB::raw("'" . $franchiseestatusattrid . "'"));
			})
			->leftjoin('customer_entity_varchar as cvar', function ($join) use ($frncodeattrid) {
				$join->on('cvar.entity_id', '=', 'e.entity_id');
				$join->on('cvar.attribute_id', '=', DB::raw("'" . $frncodeattrid . "'"));
			})
			->where('e.entity_type_id', '=', '1')
			->where('at_franchisee_status.value', '=', '2')
			->select('e.entity_id', 'cvar.value as frn_code', 'e.email', 'at_franchisee_status.value as franchisee_status')
			->orderBy("e.entity_id", "DESC");
		//echo $customers->toSql();exit;
		//$collection = collect($customers->get());
		$customerCount = $customers->get()->count();
		$customerCollection = $customers->take(10)->get();
		$customerData = array('totalCount' => $customerCount, 'customerCollection' => $customerCollection);
		DB::setTablePrefix('dml_');
		return $customerData;
	}
	//Get customer quotation stone range data
	public static function getCustomerStoneRangeData($shape, $quality, $customerId) {
		$stoneRangeData = DB::table("customer_quotation_rate")
			->select("*")
			->where("customer_id", "=", DB::raw("$customerId"))
			->where("stone_shape", "=", DB::raw("'$shape'"))
			->where("stone_quality", "=", DB::raw("'$quality'"))
			->get()->first();
		return $stoneRangeData;
	}
	//Get approval products by customer id
	public static function getApprovalProducts($customerId) {
		if (!empty($customerId)) {
			$approvalProductIds = array();
			DB::setTablePrefix('');
			$memoData = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.customer_id', 'memo.approval_no', 'memo.created_at', 'memo.is_for_old_data', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->rightJoin('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.customer_id', '=', DB::raw("$customerId"))->groupBy('memo_histroy.approval_memo_id')->get();

			$approvalMemoNumbers = array();
			foreach ($memoData as $key => $memo) {
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

			$prod = InventoryHelper::getAllProductsCollection(true);
			$prod = $prod->whereIn("entity_id", $approvalProductIds);

			$totalProductCount = $prod->count();

			$approvalProductCollection = $prod->take(10);
			DB::setTablePrefix('dml_');

			$approvalProducts = array('totalCount' => $totalProductCount, 'productCollection' => $approvalProductCollection, 'approvalMemoNumbers' => $approvalMemoNumbers);
			return $approvalProducts;
		}
	}
	public static function getApprovalMemo($customerId) {
		if (!empty($customerId)) {
			DB::setTablePrefix('');
			$approvalType = config('constants.enum.customer_view_approval_type');

			foreach ($approvalType as $key => $type) {
				if ($key == 'oldest_approval') {
					//Get old memo collection
					$memoList[$key] = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.customer_id', 'memo.approval_no', 'memo.is_delivered', 'memo.created_at', 'memo.is_for_old_data', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.customer_id', '=', DB::raw("$customerId"))->groupBy('memo_histroy.approval_memo_id')->orderBy('memo.created_at', 'ASC');

					$memoCount[$key] = $memoList[$key]->get()->count();
					$memoList[$key] = $memoList[$key]->take(1)->get();
				} else if ($key == 'newest_approval') {
					//Get new memo collection
					$memoList[$key] = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.customer_id', 'memo.approval_no', 'memo.created_at', 'memo.is_delivered', 'memo.is_for_old_data', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.customer_id', '=', DB::raw("$customerId"))->groupBy('memo_histroy.approval_memo_id')->orderBy('memo.created_at', 'DESC');

					$memoCount[$key] = $memoList[$key]->get()->count();
					$memoList[$key] = $memoList[$key]->take(1)->get();
				} else if ($key == 'all_approval') {
					//Get all memo collection
					$memoList[$key] = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.customer_id', 'memo.approval_no', 'memo.is_delivered', 'memo.created_at', 'memo.is_for_old_data', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.customer_id', '=', DB::raw("$customerId"))->groupBy('memo_histroy.approval_memo_id')->orderBy('memo.created_at', 'DESC');

					$memoCount[$key] = $memoList[$key]->get()->count();
					$memoList[$key] = $memoList[$key]->take(10)->get();
				}
			}
			$approvalMemoData = array('memoCount' => $memoCount, 'memoCollection' => $memoList);
			DB::setTablePrefix('dml_');
			return $approvalMemoData;
		}
	}
	//Get invoices for customer view
	public static function getInvoices($customerId) {
		if (!empty($customerId)) {
			DB::setTablePrefix('');
			$generatedInvoiceList = DB::table("sales_flat_order as main_table")
				->select("main_table.entity_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total", "main_table.status", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total", "oinv.invoice_shipping_charge", "oinv.gst_percentage")
				->where("qr_product_status", "=", DB::raw("'1'"))
				->where("main_table.customer_id", "=", DB::raw("$customerId"))
				->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
				->orderBy("oinv.created_at", "desc");

			$totalCount = $generatedInvoiceList->get()->count();
			$invoiceList = $generatedInvoiceList->take(10)->get();
			$invoiceData = array('totalCount' => $totalCount, 'invoiceCollection' => $invoiceList);
			DB::setTablePrefix('dml_');
			return $invoiceData;
		}
	}
	public static function getCustomerDetailById($customerId) {
		/* if (App::environment('local')) {
			                $getCustomerUrl = Config::get('constants.apiurl.local.get_customer');
			            } else {
			                $getCustomerUrl = Config::get('constants.apiurl.live.get_customer');
			            }

			        $searchTerm = 'term='.$customerId;

			        $ch = curl_init('http://www.diamondmela.com/index.php/dmlapi/customers/getcustomerbyid');
			        curl_setopt($ch, CURLOPT_POST, true);
			        curl_setopt($ch, CURLOPT_POSTFIELDS, $searchTerm);
			        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			        curl_setopt($ch, CURLOPT_HEADER, 0);
			        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			        $result = curl_exec($ch);
					$info = curl_getinfo($ch);
					print_r($info);exit;
			        print_r($result);exit;
			        $result = json_decode($result);
		*/
		$sql = "SELECT val.entity_id,ce.email,group_concat(VALUE SEPARATOR ' ') AS fullname
FROM customer_entity_varchar AS val
JOIN customer_entity AS ce ON ce.entity_id = val.entity_id
JOIN eav_attribute AS attr ON attr.attribute_id  = val.attribute_id
WHERE attr.attribute_code IN ( 'firstname',  'lastname' ) and ce.entity_id=" . $customerId;
		$customer = DB::select($sql);
		print_r($customer);exit;
	}
	//Get return memo product collection
	public static function getReturnMemoProducts($id) {
		if (!empty($id)) {
			$productIds = array();
			$returnMemoNumbers = array();
			$returnMemoProducts = ReturnMemo::orderBy('created_at', 'desc')->select('*')->where('customer_id', '=', DB::raw("$id"))->get();
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
			$totalCount = $prod->count();
			$returnMemoProductList = $prod->take(10);
			$returnedMemoData = array('totalCount' => $totalCount, 'returnMemoProductCollection' => $returnMemoProductList, 'returnMemoNumbers' => $returnMemoNumbers);
			return $returnedMemoData;
		}
	}
	//Get customer inventory
	public static function getCustomerInventory($customerId) {
		DB::setTablePrefix('');
		$inventoryCollection = DB::table('catalog_product_flat_1 as ce')
			->select('ce.entity_id', 'ce.name', 'ce.sku', 'ce.certificate_no', 'ce.custom_price', 'ce.rts_stone_quality', DB::raw("'purchased' as purchased_as"))
			->join('dml_invoice_products AS invoice_item', 'invoice_item.product_id', '=', 'ce.entity_id')
			->join('sales_flat_invoice AS invoice', 'invoice.entity_id', '=', 'invoice_item.invoice_id')
			->join('sales_flat_order AS ord', 'ord.entity_id', '=', 'invoice.order_id')
			->where('ord.customer_id', '=', DB::raw("$customerId"))
			->groupBy('ce.entity_id');

		$returnMemoCollection = DB::table('catalog_product_flat_1 AS ce')
			->select('ce.entity_id', 'ce.name', 'ce.sku', 'ce.certificate_no', 'ce.custom_price', 'ce.rts_stone_quality', DB::raw("'return_memo' AS purchased_as"))
			->join('dml_return_memo_products AS return_memo_products', 'return_memo_products.product_id', '=', 'ce.entity_id')
			->join('dml_return_memo AS return_memo', 'return_memo.id', '=', 'return_memo_products.return_memo_id')
		//->unionAll($inventoryCollection)
			->where('return_memo.customer_id', '=', DB::raw("$customerId"))
			->groupBy('ce.entity_id');

		$salesReturnCollection = DB::table('catalog_product_flat_1 AS ce')
			->select('ce.entity_id', 'ce.name', 'ce.sku', 'ce.certificate_no', 'ce.custom_price', 'ce.rts_stone_quality', DB::raw("'sales_return' AS purchased_as"))
			->join('dml_sales_return_products AS sales_return_product', 'sales_return_product.product_id', '=', 'ce.entity_id')
			->join('dml_sales_return AS sales_return', 'sales_return.id', '=', 'sales_return_product.sales_return_id')
		//->unionAll($inventoryCollection)
			->where('sales_return.customer_id', '=', DB::raw("$customerId"))
			->groupBy('ce.entity_id');

		$approvalCollection = DB::table('catalog_product_flat_1 AS ce')
			->select('ce.entity_id', 'ce.name', 'ce.sku', 'ce.certificate_no', 'ce.custom_price', 'ce.rts_stone_quality', DB::raw("'approval' AS purchased_as"))
			->join('dml_approval_memo_histroy AS memo_histroy', 'memo_histroy.product_id', '=', 'ce.entity_id')
			->join('dml_approval_memo AS memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')
			->union($returnMemoCollection)
			->union($inventoryCollection)
			->union($salesReturnCollection)
			->where('memo_histroy.status', '=', DB::raw("'approval'"))
			->where('memo.customer_id', '=', DB::raw("$customerId"))
			->groupBy('ce.entity_id');

		//echo $approvalCollection->toSql();exit;
		$productsCount = $approvalCollection->count();
		$productCollection = $approvalCollection->take(10)->get();

		$productData = array('totalCount' => $productsCount, 'productCollection' => $productCollection);
		return $productData;
	}
	public static function getStateList($countryId) {
		if (App::environment('local')) {
			$getStateUrl = config('constants.apiurl.local.get_state_list');
		} else {
			$getStateUrl = config('constants.apiurl.live.get_state_list');
		}
		$postParam = 'country_id=' . $countryId;
		$stateList = array();
		$ch = curl_init($getStateUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$result = json_decode($result);

		if ((isset($result->status) && $result->status == 'success') && isset($result->data)) {
			return $result->data;
		}
	}
	//Get customer gstin
	public static function getGstinByCustomer($customerId) {
		if (!empty($customerId)) {
			$gstin = DB::select("SELECT customer_entity.value AS gstin FROM customer_entity_varchar AS customer_entity JOIN eav_attribute AS eav ON eav.attribute_id = customer_entity.attribute_id WHERE eav.attribute_code = 'gstin' AND customer_entity.entity_id = " . $customerId);
			$gstin = isset($gstin[0]->gstin) ? $gstin[0]->gstin : '';
			return $gstin;
		}
	}
	//Get Gstin attachment
	public static function getGstinAttachmentByCustomer($customerId) {
		if (!empty($customerId)) {
			$gstinAttachment = DB::select("SELECT customer_entity.value AS gstin_attachment FROM customer_entity_varchar AS customer_entity JOIN eav_attribute AS eav ON eav.attribute_id = customer_entity.attribute_id WHERE eav.attribute_code = 'gst_attachment' AND customer_entity.entity_id = " . $customerId);
			$gstinAttachment = isset($gstinAttachment[0]->gstin_attachment) ? $gstinAttachment[0]->gstin_attachment : '';
			return $gstinAttachment;
		}
	}
	//Get pan card no
	public static function getPanCardNumberByCustomer($customerId) {
		if (!empty($customerId)) {
			$panCardNumber = DB::select("SELECT customer_entity.value AS pancard_number FROM customer_entity_varchar AS customer_entity JOIN eav_attribute AS eav ON eav.attribute_id = customer_entity.attribute_id WHERE eav.attribute_code = 'pancardno' AND customer_entity.entity_id = " . $customerId);
			$panCardNumber = isset($panCardNumber[0]->pancard_number) ? $panCardNumber[0]->pancard_number : '';
			return $panCardNumber;
		}
	}
	//Get pancard attachment
	public static function getPanCardAttachmentByCustomer($customerId) {
		if (!empty($customerId)) {
			$panCardNumberAttachment = DB::select("SELECT customer_entity.value AS pancard_attachment FROM customer_entity_varchar AS customer_entity JOIN eav_attribute AS eav ON eav.attribute_id = customer_entity.attribute_id WHERE eav.attribute_code = 'panattachment' AND customer_entity.entity_id = " . $customerId);
			$panCardNumberAttachment = isset($panCardNumberAttachment[0]->pancard_attachment) ? $panCardNumberAttachment[0]->pancard_attachment : '';
			return $panCardNumberAttachment;
		}
	}
	//Add customer attribute value
	public static function addCustomerAttributeValue($customerId, $attributeCode, $attributeValue) {
		if (!empty($customerId)) {
			DB::setTablePrefix('');
			//Get attribute id
			$attributeId = DB::table('eav_attribute')->select('attribute_id')->where('attribute_code', '=', DB::raw("'$attributeCode'"))->get()->first();
			$attributeId = isset($attributeId->attribute_id) ? $attributeId->attribute_id : '';

			//add attribute value
			$result = DB::statement("INSERT INTO customer_entity_varchar(entity_type_id,attribute_id,entity_id,value) VALUES (1," . DB::raw("$attributeId") . "," . DB::raw("$customerId") . "," . DB::raw("'$attributeValue'") . ")");
			DB::setTablePrefix('dml_');
			return $result;
		}
	}
	//Update customer attribute value
	public static function updateCustomerAttributeValue($customerId, $attributeCode, $attributeValue) {
		if (!empty($customerId)) {
			DB::setTablePrefix('');
			//Get attribute id
			$attributeId = DB::table('eav_attribute')->select('attribute_id')->where('attribute_code', '=', DB::raw("'$attributeCode'"))->get()->first();
			$attributeId = isset($attributeId->attribute_id) ? $attributeId->attribute_id : '';

			//add attribute value
			$result = DB::statement("UPDATE customer_entity_varchar SET value=" . DB::raw("'$attributeValue'") . " WHERE attribute_id=" . DB::raw("$attributeId") . " AND entity_id=" . DB::raw("$customerId"));
			DB::setTablePrefix('dml_');
			return $result;
		}
	}
	//Get primary/secondary contact by customer
	public static function getPrimarySecondoryContact($customerId, $contactType) {
		if (!empty($customerId)) {
			$contact = DB::select("SELECT customer_entity.value AS contact_number FROM customer_entity_varchar AS customer_entity JOIN eav_attribute AS eav ON eav.attribute_id = customer_entity.attribute_id WHERE eav.attribute_code = '" . $contactType . "' AND customer_entity.entity_id = " . $customerId);
			$contact = isset($contact[0]->contact_number) ? $contact[0]->contact_number : '';
			return $contact;
		}
	}
	public static function getCustomerFirstName($customerId) {
		if (!empty($customerId)) {
			$firstNameAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='firstname'");
			$firstnameattrid = array();
			foreach ($firstNameAttribute as $key => $attr) {
				$firstnameattrid[] = $attr->attribute_id;
			}
			$firstnameattrid = implode("','", $firstnameattrid);
			$customer = DB::select("select value from customer_entity_varchar where attribute_id IN('" . $firstnameattrid . "') and entity_id=" . $customerId);
			$firstName = isset($customer[0]->value) ? $customer[0]->value . ' ' : '';
			return $firstName;
		}
	}
	public static function getCustomerLastName($customerId) {
		if (!empty($customerId)) {
			$lastNameAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='lastname'");
			$lastnameattrid = array();
			foreach ($lastNameAttribute as $key => $attr) {
				$lastnameattrid[] = $attr->attribute_id;
			}
			$lastnameattrid = implode("','", $lastnameattrid);
			$customer = DB::select("select value from customer_entity_varchar where attribute_id IN('" . $lastnameattrid . "') and entity_id=" . $customerId);
			$lastName = isset($customer[0]->value) ? $customer[0]->value . ' ' : '';
			return $lastName;
		}
	}
	public static function getCustomerEmail($customerId) {
		DB::setTablePrefix('');
		$customerEmail = DB::table('customer_entity')->select('email')->where('entity_id', '=', DB::raw("$customerId"))->get()->first();
		DB::setTablePrefix('dml_');
		return isset($customerEmail->email) ? $customerEmail->email : '';
	}
	public static function updateCustomerName($customerId, $customerName, $attributeCode) {
		if (!empty($customerId)) {
			$nameAttribute = DB::select("select attribute_id from eav_attribute where attribute_code=" . DB::raw("'$attributeCode'"));
			$nameAttributeId = array();
			foreach ($nameAttribute as $key => $attr) {
				$nameAttributeId[] = $attr->attribute_id;
			}
			$nameAttributeId = implode("','", $nameAttributeId);
			$result = DB::statement("UPDATE customer_entity_varchar set value=" . DB::raw("'$customerName'") . " WHERE attribute_id IN(" . DB::raw("'$nameAttributeId'") . ") AND entity_id=" . DB::raw("$customerId"));
			return $result;
		}
	}
	public static function getCustomerAttrValue($customerId, $attributeCode) {
		if (!empty($customerId)) {
			$attribute = DB::select("select attribute_id from eav_attribute where attribute_code=" . DB::raw("'$attributeCode'"));
			$attributeId = isset($attribute[0]->attribute_id) ? $attribute[0]->attribute_id : '';
			$attributeValue = DB::select("select value from customer_entity_varchar where attribute_id=" . DB::raw("$attributeId") . " AND entity_id=" . DB::raw("$customerId") . "");
			$attributeValue = isset($attributeValue[0]->value) ? $attributeValue[0]->value : '';
			return $attributeValue;
		}
	}
	public static function getTotalAmount($customerId) {
		if (!empty($customerId)) {
			DB::setTablePrefix('');
			$invoiceTotalAmount = DB::select("SELECT sum(`oinv`.`grand_total`) AS invoice_total_amount FROM `sales_flat_order` AS `main_table` INNER JOIN `sales_flat_invoice` AS `oinv` ON `oinv`.`order_id` = `main_table`.`entity_id` WHERE `qr_product_status` = '1' AND `main_table`.`customer_id` = " . DB::raw("$customerId") . " GROUP BY `main_table`.`customer_id` ORDER BY `oinv`.`created_at` DESC");
			DB::setTablePrefix('dml_');
			return isset($invoiceTotalAmount[0]->invoice_total_amount) ? ShowroomHelper::currencyFormatWithoutIcon(round($invoiceTotalAmount[0]->invoice_total_amount)) : 0;
		}
	}
	public static function getTotalSoldProductsCount($customerId) {
		if (!empty($customerId)) {
			DB::setTablePrefix('');
			$totalSoldProduct = DB::select("SELECT count(`oinv_item`.product_id) AS total_sold_products FROM `sales_flat_order` AS `main_table` INNER JOIN `sales_flat_invoice` AS `oinv` ON `oinv`.`order_id` = `main_table`.`entity_id` INNER JOIN `sales_flat_invoice_item` AS `oinv_item` ON `oinv_item`.`parent_id` = `oinv`.`entity_id` WHERE `qr_product_status` = '1' AND `main_table`.`customer_id` = " . DB::raw("$customerId") . " AND `main_table`.status='complete' GROUP BY `main_table`.`customer_id` ORDER BY `oinv`.`created_at` DESC");
			DB::setTablePrefix('dml_');
			return isset($totalSoldProduct[0]->total_sold_products) ? $totalSoldProduct[0]->total_sold_products : 0;
		}
	}
	//Get total quotation count by customer
	public static function getTotalQuotationCount($customerId) {
		if (!empty($customerId)) {
			$quotation = Quotation::where('customer_id', '=', $customerId)->get();
			$quotationCount = $quotation->count();
			return $quotationCount;
		}
	}
	//Get total sales return count by customer
	public static function getSalesReturnCount($customerId) {
		if (!empty($customerId)) {
			$salesReturn = SalesReturn::where('customer_id', '=', $customerId)->get();
			$salesReturnCount = $salesReturn->count();
			return $salesReturnCount;
		}
	}
	//Get total paid amount
	public static function getTotalPaidAmount($customerId) {
		if (!empty($customerId)) {
			$paidAmount = Payment::select(DB::raw('SUM(invoice_amount) as paid_amount'))->join('payment_types', 'payments.payment_sub_type', '=', 'payment_types.id')->where('payment_status', '=', '1')->where('payment_form', '=', 'Incoming')->where('customer_id', '=', DB::raw("$customerId"))->groupBy('payment_types.name')->get()->first();
			return isset($paidAmount->paid_amount) ? $paidAmount->paid_amount : 0;
		}
	}
	//Get total un-paid amount
	public static function getTotalUnPaidAmount($customerId) {
		if (!empty($customerId)) {
			$unPaidAmount = Payment::select(DB::raw('SUM(invoice_amount) as un_paid_amount'))->join('payment_types', 'payments.payment_sub_type', '=', 'payment_types.id')->where('payment_status', '=', '0')->where('payment_form', '=', 'Incoming')->where('customer_id', '=', DB::raw("$customerId"))->groupBy('payment_types.name')->get()->first();
			return isset($unPaidAmount->un_paid_amount) ? $unPaidAmount->un_paid_amount : 0;
		}
	}
	//Get sales return list
	public static function getSalesReturnList($customerId) {
		if (!empty($customerId)) {
			$salesReturnList = SalesReturn::where('customer_id', $customerId)->get();
			$totalCount = $salesReturnList->count();
			$salesReturnList = $salesReturnList->take(10);
			$salesReturnData = array('totalCount' => $totalCount, 'salesReturnCollection' => $salesReturnList);
			return $salesReturnData;
		}
	}
	//Get sales return products count
	public static function getSalesReturnProducts($customerId) {
		if (!empty($customerId)) {
			$salesReturnList = SalesReturn::select('product_data')->where('customer_id', $customerId)->get();
			$products = array();
			foreach ($salesReturnList as $key => $salesReturn) {
				$productData = json_decode($salesReturn->product_data);
				foreach ($productData as $key => $product) {
					$products[] = isset($product->sku) ? $product->sku : '';
				}
			}
			$products = array_unique($products);
			$products = array_filter($products);
			return count($products);
		}
	}
	//Get credit note list for customer
	public static function getCreditNoteList($customerId) {
		if (!empty($customerId)) {
			$creditNoteList = SalesReturn::where('customer_id', $customerId)->where('is_credited', '=', 'yes')->get();
			$totalCount = $creditNoteList->count();
			$creditNoteList = $creditNoteList->take(10);
			$creditNoteData = array('totalCount' => $totalCount, 'creditNoteCollection' => $creditNoteList);
			return $creditNoteData;
		}
	}
	//Get username by id
	public static function getUsername($userId) {
		if (!empty($userId)) {
			$user = User::select('name')->where('id', $userId)->get()->first();
			return isset($user->name) ? $user->name : '';
		}
	}
	//Get total credit note count
	public static function getCreditNoteCount($customerId) {
		if (!empty($customerId)) {
			$creditNoteCount = SalesReturn::where('customer_id', $customerId)->where('is_credited', '=', 'yes')->get()->count();
			return $creditNoteCount;
		}
	}

	//Check primary/secondory contact validation
	public static function isContactNumberExist($customerId, $contactType, $contactNumber) {
		if (!empty($customerId)) {
			$contactData = DB::select("SELECT count(1) as total_customer FROM customer_entity_varchar AS customer_entity JOIN eav_attribute AS eav ON eav.attribute_id = customer_entity.attribute_id WHERE eav.attribute_code = '" . $contactType . "' and customer_entity.value = '" . $contactNumber . "' and customer_entity.entity_id!=" . $customerId);
		} else {
			$contactData = DB::select("SELECT count(1) as total_customer FROM customer_entity_varchar AS customer_entity JOIN eav_attribute AS eav ON eav.attribute_id = customer_entity.attribute_id WHERE eav.attribute_code = '" . $contactType . "' and customer_entity.value = '" . $contactNumber . "'");
		}
		if (isset($contactData[0]->total_customer) && $contactData[0]->total_customer > 0) {
			return true;
		} else {
			return false;
		}
	}
	//Get wallet transaction histroy
	public static function getWalletTransactions($customerId) {
		if (!empty($customerId)) {
			$walletData = CustomerWallet::where('customer_id', '=', DB::raw("$customerId"))->orderBy('id', 'DESC')->get();
			$totalCount = $walletData->count();
			$walletData = $walletData->take(10);
			$walletData = array('totalCount' => $totalCount, 'walletTransactionCollection' => $walletData);
			return $walletData;
		}
	}
	//Get wallet amount by customer
	public static function getWalletAmount($customerId) {
		$creditData = CustomerWallet::select(DB::raw("SUM(`transaction_amt`) as credit_total"))->where('customer_id', '=', DB::raw("$customerId"))->where('transaction_type', '=', DB::raw("'credit'"))->get()->first();
		$creditAmount = isset($creditData->credit_total) ? $creditData->credit_total : 0;

		$debitData = CustomerWallet::select(DB::raw("SUM(`transaction_amt`) as debit_total"))->where('customer_id', '=', DB::raw("$customerId"))->where('transaction_type', '=', DB::raw("'debit'"))->get()->first();
		$debitAmount = isset($debitData->debit_total) ? $debitData->debit_total : 0;

		$totalAmount = (float) $creditAmount - (float) $debitAmount;
		return $totalAmount;

	}
	//Get payment data
	public static function getPaymentList($customerId) {
		if (!empty($customerId)) {
			/*$paymentData = Payment::join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->select('payments.*', 'payment_types.name')->where('customer_id','=', DB::raw("$customerId"))->orderBy('id','DESC')->toSql();*/
			$paymentData = DB::table('payments')->select('payments.*', 'payment_types.name')->join('payment_types', 'payments.payment_type', '=', 'payment_types.id')->where('customer_id', '=', DB::raw("$customerId"))->whereNull('payments.deleted_at')->orderBy('id', 'DESC')->get();
			/*echo "<pre>";
			print_r($paymentData->toArray());exit;*/
			$totalCount = $paymentData->count();
			$paymentData = $paymentData->take(10);
			$paymentData = array('totalCount' => $totalCount, 'paymentCollection' => $paymentData);
			return $paymentData;
		}
	}
}