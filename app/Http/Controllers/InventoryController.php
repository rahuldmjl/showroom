<?php

namespace App\Http\Controllers;

use App;
use App\ApprovalMemo;
use App\ApprovalMemoHistroy;
use App\CashVoucher;
use App\CustomerQuotationRate;
use App\CustomerWallet;
use App\Exhibition;
use App\ExhibitionProducts;
use App\Helpers\CustomersHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ProductHelper;
use App\Helpers\ShowroomHelper;
use App\Http\Controllers\Controller;
use App\InvoiceCustomerDivision;
use App\InvoiceLogs;
use App\InvoiceProducts;
use App\Payment;
use App\PaymentTransaction;
use App\PaymentType;
use App\Products;
use App\Quotation;
use App\QuotationData;
use App\ReturnMemo;
use App\ReturnMemoProducts;
use App\SalesReturn;
use App\SalesReturnProducts;
use App\Setting;
use Auth;
use Config;
use DateTime;
use Excel;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Image;
use PDF;
use PHPExcel_Worksheet_Drawing;
use QrCode;
use Session;
use URL;

class InventoryController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	function __construct() {
		$this->middleware('permission:inventory-stocktally');
		$this->middleware('permission:inventory-quotationlist');
		$this->middleware('permission:inventory-returnmemolist');
		//$this->middleware('permission:inventory-stocktmp');
	}
	//Get inventory products list
	public function index(Request $request, $id = null) {

		$params = $request->post();
		$quotationId = !empty($id) ? $id : '';
		$productCollection = InventoryHelper::getInventoryProducts();
		//return view('inventory.index')->with('productCollection',$inventoryProducts,'quotationId',$quotationId);
		if (empty($quotationId)) {
			Session::forget('edit_product_ids');
		}

		Session::forget('quotation_product_ids');
		return view('inventory.index', compact('productCollection', 'quotationId'));
	}
	//Get rts product list
	public function qrinventorymanagement(Request $request) {
		$inventoryProducts = InventoryHelper::getRtsProducts();
	}
	//Change inventory status & other actions
	public function changeinventorystatusandremovefromorder(Request $request) {
		//echo "in new method";exit;
		$params = $request->post();

		$inventoryStatus = isset($params['inventoryCode']) ? $params['inventoryCode'] : '';
		$productIds = isset($params['productIds']) ? $params['productIds'] : array();
		//print_r($productIds);exit;
		if (!empty($productIds) && !is_array($productIds)) {
			$productIds = explode(',', $productIds);
		}

		if (!empty($productIds)) {
			try
			{
				foreach ($productIds as $productId) {
					InventoryHelper::removefromapprovaltable($productId);
					InventoryHelper::changeInventoryStatus($productId, $inventoryStatus);
					InventoryHelper::updateProductAttribute($productId, 'approval_invoice_generated', '0');
					InventoryHelper::updateProductAttribute($productId, 'is_sold', 0);
					InventoryHelper::updateProductAttribute($productId, 'approval_memo_generated', '0');
					InventoryHelper::updateProductAttribute($productId, 'return_memo_generated', '0');
				}

				/*if (App::environment('local')) {
						$generateReturnMemoUrl = Config::get('constants.apiurl.local.generate_return_memo');
					} else {
						$generateReturnMemoUrl = Config::get('constants.apiurl.live.generate_return_memo');
					}

					$returnMemoParams = 'productIds=' . implode(',', $productIds);
					$ch = curl_init($generateReturnMemoUrl);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $returnMemoParams);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$result = curl_exec($ch);
					$info = curl_getinfo($ch);
					//print_r($result);exit;
					Cache::forget('all_products_ajax');
				*/
				//echo $result;exit;

				$response['status'] = true;
				$response['message'] = Config::get('constants.message.inventory_status_changed');
			} catch (Exception $e) {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_status_not_changed');
			}
		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_status_product_not_selected');
		}
		echo json_encode($response);exit;
	}
	public function getFilteredProductCollection($params) {

		DB::setTablePrefix('');

		$prod = InventoryHelper::getAllProductsCollection(true);
		if (isset($params['category'])) {
			$prod = $prod->whereIn('category_id', $params['category']);
		}

		//For search by certificate/sku
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
		if (!empty($searchValue)) {
			$prod = $prod->filter(function ($value, $key) use ($searchValue) {
				//var_dump($value);
				//var_dump($searchValue);
				if (stripos($value->sku, $searchValue) !== false) {
					return $value;
				}
				if (stripos($value->certificate_no, $searchValue) !== false) {
					return $value;
				}
				if ($value->custom_price == $searchValue) {
					return $value;
				}
				if (stripos($value->rts_stone_quality, $searchValue) !== false) {
					return $value;
				}
			});
		}
		//For virtual product manager filter
		if (!empty($params['virtualproducts'])) {
			$virtualProduct = DB::select("select product_manager_certificates from grp_productmanager where product_manager_id=" . $params['virtualproducts']);
			$virtualProduct = explode(',', $virtualProduct[0]->product_manager_certificates);

			$prod = $prod->whereIn('certificate_no', $virtualProduct);
		}
		//For price filter
		$priceStart = '';
		$priceTo = '';
		if (($params['price_start'] != '') && ($params['price_to'] != '')) {
			$priceStart = $params['price_start'];
			$priceTo = $params['price_to'];
		}
		if ((isset($priceStart)) && (isset($priceTo))) {
			$prod = $prod->filter(function ($value, $key) use ($priceStart, $priceTo) {
				if ($value->custom_price >= $priceStart && $value->custom_price <= $priceTo) {
					return $value;
				}
			});
		}
		//For status filter
		if (!empty($params['stockstatus'])) {
			//dd($prod->toArray());exit;
			if (App::environment('local')) {
				$IN = config('constants.apiurl.local.get_in');
			} else if (App::environment('test')) {
				$IN = config('constants.apiurl.test.get_in');
			} else {
				$IN = config('constants.apiurl.live.get_in');
			}

			//var_dump($params['stockstatus']);
			//var_dump($IN);
			//echo "<pre>";
			//print_r($prod->toArray());
			//exit;

			$status = (($params['stockstatus'] == trim($IN)) ? $IN : $params['stockstatus']);
			$prod = $prod->where('inventory_status_value', $status);
		}

		//For gold purity filter
		if (isset($params['gold_purity']) && isset($params['gold_color'])) {
			$gold_purity = $params['gold_purity'];
			$goldcolor = $params['gold_color'];
			foreach ($gold_purity as $value) {
				if ($value === '14K') {
					foreach ($goldcolor as $goldcolorval) {
						$values[] = $value . ' ' . $goldcolorval;
					}
				} else if ($value === '18K') {
					foreach ($goldcolor as $goldcolorval) {
						$values[] = $value . ' ' . $goldcolorval;
					}
				}
			}
			$mainGoldQuality = implode(",", $values);
			$goldQuality = explode(',', $mainGoldQuality);
			$goldQuality = implode("','", $goldQuality);
			$prod = $prod->filter(function ($value, $key) use ($values) {

				foreach ($values as $metal_key => $metal_comb) {
					if (stripos($value->metal_quality_value, $metal_comb) !== false) {
						return $value;
					}
				}
			});

		} else if (isset($params['gold_color'])) {
			$gold_color = $params['gold_color'];
			$prod = $prod->filter(function ($value, $key) use ($gold_color) {

				foreach ($gold_color as $col_key => $color) {
					if (stripos($value->metal_quality_value, $color) !== false) {
						return $value;
					}
				}
			});

		} else if (isset($params['gold_purity'])) {
			$gold_purity = $params['gold_purity'];
			if (count($gold_purity) > 0) {
				$prod = $prod->filter(function ($value, $key) use ($gold_purity) {

					foreach ($gold_purity as $pur_key => $purity) {
						if (stripos($value->metal_quality_value, $purity) !== false) {
							return $value;
						}
					}
				});
				// print_r($gold_purity_arr);exit;

			}
		}
		//For diamond quality filter
		if (isset($params['diamond_quality'])) {
			$prod = $prod->whereIn('rts_stone_quality', $params['diamond_quality']);
		}
		if (isset($params['productimages']) && !empty($params['productimages']) && $params['productimages'] == '1') {
			$prod = $prod->filter(function ($value, $key) use ($params) {
				$productimages = $value->product_image;
				if (!empty($productimages)) {
					return $value;

				}
			});
		}if (isset($params['productimages']) && !empty($params['productimages']) && $params['productimages'] == '2') {
			$prod = $prod->filter(function ($value, $key) use ($params) {
				$productimages = $value->product_image;
				if (empty($productimages)) {
					return $value;
				}
			});
		}

		//var_dump($params['stocktype']);exit;

		if (!empty($params['stocktype']) && isset($params['stocktype'])) {
			$productIds = array();
			$showroomProducts = array();
			$approvalProducts = array();
			$pendingProducts = array();
			$soldProducts = array();
			if (in_array('Showroom', $params['stocktype'])) {
				if (App::environment('local')) {
					$stocktype = config('constants.apiurl.local.get_in');
				} else if (App::environment('test')) {
					$stocktype = config('constants.apiurl.test.get_in');
				} else {
					$stocktype = config('constants.apiurl.live.get_in');
				}
				$showroomProducts = $prod->where('inventory_status_value', $stocktype);
				$showroomProducts = $showroomProducts->unique('entity_id')->pluck(['entity_id'])->toArray();
			}
			if (in_array('Approval', $params['stocktype'])) {
				$approvalProducts = $prod->where('inventory_status_value', 'Out');
				$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'1'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
				//print_r($pendingStock);exit;

				$approvalPrdIds = array();
				foreach ($pendingStock as $approvalPrd) {
					$approvalPrdIds[] = $approvalPrd->product_id;
				}
				/* $productId = isset($pendingStock->product_ids) ? $pendingStock->product_ids : '';
				$productId = explode(',', $productId); */
				$approvalProducts = $approvalProducts->whereIn('entity_id', $approvalPrdIds);
				$approvalProducts = $approvalProducts->unique('entity_id')->pluck(['entity_id'])->toArray();
			}
			if (in_array('Pending', $params['stocktype'])) {
				$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'0'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
				$pendingProductIds = array();
				foreach ($pendingStock as $product) {
					$pendingProductIds[] = $product->product_id;
				}
				$pendingProducts = $prod->whereIn('entity_id', $pendingProductIds);
				$pendingProducts = $pendingProducts->unique('entity_id')->pluck(['entity_id'])->toArray();
			}
			if (in_array('Sold', $params['stocktype'])) {
				$soldProducts = $prod->where('inventory_status_value', 'Sold Out');
				$soldProducts = $soldProducts->unique('entity_id')->pluck(['entity_id'])->toArray();
			}
			$productIds = array_merge($showroomProducts, $approvalProducts, $pendingProducts, $soldProducts);
			$productIds = array_unique($productIds);
			$prod = $prod->whereIn('entity_id', $productIds);
		}
		/*echo "<pre>";
		print_r($prod);exit;*/
		return $prod;
	}
	//For server side datatable
	public function ajaxlist(Request $request) {
		$data = array();
		$params = $request->post();

		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$searchValue = (!empty($params['search_value']) ? $params['search_value'] : '');
		$stalen = $start / $length;
		$curpage = $stalen + 1;
		$isAllStock = isset($params['is_allstock']) ? $params['is_allstock'] : 0;

		DB::setTablePrefix('');
		$prod = $this->getFilteredProductCollection($params);

		$productCount = $prod->count();
		//$productCollection = $prod->take($length)->offset($curpage)->get();
		$productCollection = $prod->forPage($curpage, $length);
		DB::setTablePrefix('dml_');
		$data["draw"] = $params['draw'];
		$data["recordsTotal"] = $productCount;
		$data["recordsFiltered"] = $productCount;
		$data['deferLoading'] = $productCount;
		$imageDirectory = config('constants.dir.website_url_for_product_image');
		$defaultProductImage = $imageDirectory . 'def_1.png';
		if (count($productCollection) > 0) {
			foreach ($productCollection as $key => $product) {
				$dateproduct = $product->created_at;
				$createDate = new DateTime($dateproduct);
				$Productstrip = $createDate->format('Y-m-d');

				$now = date('Y-m-d');
				$from = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 5, date('Y')));
				if (($now >= $Productstrip) && ($from <= $Productstrip)) {
					$product_Lable = '<label class="badge badge-success px-2 fs-11">New</label>';
				} else {
					$product_Lable = '';
				}
				$checkbox = '<label><input type="checkbox" value="' . $product->entity_id . '" data-id="' . $product->entity_id . '" id="chk_product_' . $product->entity_id . '" class="form-check-input chkProduct" name="chkProduct[]"><span class="label-text"></span></label>';
				$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				$productImage = '<img alt="' . $product->sku . '" class="product-img" src="' . (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage) . '">';
				$productImage .= $product_Lable;
				/*$qrImage50 = ShowroomHelper::getProductQRImage($product->certificate_no, '50');
				$qrImageOrg = ShowroomHelper::getProductQRImage($product->certificate_no);*/

				$qrImage50 = ShowroomHelper::getProductQRImagePathFromImage($product->certificate_no, $product->qrcode_img, '50');
				$qrImageOrg = ShowroomHelper::getProductQRImagePathFromImage($product->certificate_no, $product->qrcode_img);

				$productQRImage = '<img src="' . (!empty($qrImage50) ? $qrImage50 : '') . '" data-orgsrc="' . (!empty($qrImageOrg) ? $qrImageOrg : '') . '" data-certificate="' . $product->certificate_no . '" class="qrcode-img" />';
				$position = strpos($product->sku, ' ');
				$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
				$certificateNo = $product->certificate_no;
				$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
				$categoryName = $categoryNames[0]->category_name;
				$rtsStoneQuality = !empty($product->rts_stone_quality) ? $product->rts_stone_quality : '-';
				$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
				$price = ShowroomHelper::currencyFormat(round($product->custom_price));
				/*$inventoryStatusOption = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0");
					$inventoryStatuaArr = array();
					foreach ($inventoryStatusOption as $key => $value) {
						$inventoryStatuaArr[$value->option_id] = $value->value;
					}
				*/
				$inventoryStatus = '';
				$inventoryStatus = ucwords(strtolower($product->inventory_status_value));
				$pendingOrderData = InventoryHelper::getOrderByProduct($product->entity_id, 'pending');

				$orderId = isset($pendingOrderData[0]->order_id) ? $pendingOrderData[0]->order_id : '';
				$orderDate = '';
				$firstName = '';

				$customerName = '';
				$product_approval_memo_generated = (!empty($product->approval_memo_generated) ? 1 : 0);
				$product_approval_invoice_generated = (!empty($product->approval_invoice_generated) ? 1 : 0);
				$product_return_memo_generated = (!empty($product->return_memo_generated) ? 1 : 0);

				if (count($pendingOrderData) > 0) {
					foreach ($pendingOrderData as $key => $order) {
						$orderDate = $order->created_at;
						$firstName = $order->customer_firstname;
						$lastName = $order->customer_lastname;
						$customerName = $firstName . ' ' . $lastName;
					}
				} else {
					$completedOrderData = InventoryHelper::getOrderByProduct($product->entity_id, 'complete');
					//print_r($completedOrderData);exit;
					$orderId = isset($completedOrderData[0]->order_id) ? $completedOrderData[0]->order_id : '';
					foreach ($completedOrderData as $key => $order) {
						$orderDate = $order->created_at;
						$firstName = $order->customer_firstname;
						$lastName = $order->customer_lastname;
						$customerName = $firstName . ' ' . $lastName;
					}
				}

				if (count($pendingOrderData) == 0 && count($completedOrderData) == 0) {
					$memoData = InventoryHelper::getMemoData($product->entity_id);
					//print_r($memoData);exit;
					$memoCustomerId = isset($memoData[0]->customer_id) ? $memoData[0]->customer_id : '';
					if (!empty($memoCustomerId)) {
						$customerName = InventoryHelper::getCustomerName($memoCustomerId);
					} else {
						$customerName = 'N/A';
					}
				}
				$inventoryStatusData = InventoryHelper::getInventoryStatusOptions();
				$inStatusVal = $inventoryStatusData['in'];
				$outStatusVal = $inventoryStatusData['out'];
				if (!$isAllStock) {
					$inventoryAction = '<select class="form-control h-auto box-sizing inventory_action">
                              <option value="">Select</option>
                              <option data-code="' . $inStatusVal . '" data-productid="' . $product->entity_id . '" value="in">In</option>
                              <option data-code="' . $outStatusVal . '" data-productid="' . $product->entity_id . '" value="out">Out</option>
                          </select>';
				}
				if ($inventoryStatus == 'In' || $inventoryStatus == ' In') {
					$customerName = 'N/A';
				}

				if (!$isAllStock) {
					$data['data'][] = array($checkbox, $productImage, $productQRImage, $sku, $certificateNo, $categoryName, $rtsStoneQuality, $virtualproductposition, $price, $inventoryStatus, $customerName, $inventoryAction);
				} else {
					$inventoryStatusData = InventoryHelper::getInventoryStatusOptions();
					$inStatusVal = $inventoryStatusData['in'];
					$inventoryAction = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                            <option value="">Select</option>
                            <option value="' . $inStatusVal . '" data-productid="' . $product->entity_id . '">Move to Showroom</option>
                          </select>';
					if (strtolower($inventoryStatus) == 'in') {
						$customerName = 'N/A';
					}

					$data['data'][] = array($checkbox, $productImage, $productQRImage, $sku, $certificateNo, $categoryName, $rtsStoneQuality, $virtualproductposition, $price, $inventoryStatus, $customerName, '&nbsp;');
				}
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	//For prominent filter
	public function getProminentFilter(Request $request) {
		//$t1 = microtime(true);
		$data = array();
		$params = $request->post();
		//$our_categories = "14,287,6,7,8,9,124,289,290,195,43,293,165,295";
		//$our_categories_exp = explode(',', $our_categories);

		//$our_categories_for_query = '';
		//$our_categories_for_query = implode("','", $our_categories_exp);

		$our_categories_exp = config('constants.fixIds.live.category_ids');

		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$searchValue = (!empty($params['search_value']) ? $params['search_value'] : '');
		$stalen = $start / $length;
		$curpage = $stalen + 1;
		DB::setTablePrefix('');

		$prod = $this->getFilteredProductCollection($params);

		$filtered_products = array();
		$filtered_products_gold_quality = array();
		$filtered_products_gold_colors = array();
		$filtered_inventory_status = array();
		$filtered_virtual_position = array();
		//$productCollection = $prod;
		//print_r($params['filterapplied']);exit;
		//if ($params['filterapplied'] == 'true') {
		//echo "<pre>";
		//print_r($productCollection);exit;

		//$prod2 = $prod->get(['sku'])->toArray();
		$prodMetalQualities = $prod->unique('metal_quality')->pluck(['metal_quality']);

		//$prod2 = $prod->only(['sku'])->all(); // ->unique('metal_quality')
		//dd($prod2);
		//$golds = InventoryHelper::getGoldQualityCollection();
		$filtered_products_gold_quality = InventoryHelper::getMetalFilters('all_metal_purity', $prodMetalQualities->toArray());
		$filtered_products_gold_colors = InventoryHelper::getMetalFilters('all_metal_color', $prodMetalQualities->toArray());
		//dd($colors);
		//exit;

		$filtered_inventory_status = $prod->unique('inventory_status')->pluck(['inventory_status_value'])->toArray();

		//dd($filtered_inventory_status);exit;

		/*$prod->filter(function ($value, $key) {
			if (stripos($value->sku, '14K') !== false) {
				$filtered_products_gold_quality[] = '14K';
			} elseif (stripos($value->sku, '18K') !== false) {
				$filtered_products_gold_quality[] = '18K';
			}

			if (stripos($value->sku, 'Rose Gold') !== false) {
				$filtered_products_gold_colors[] = 'Rose Gold';
			} elseif (stripos($value->sku, 'Three Tone') !== false || stripos($value->sku, 'Threetone') !== false) {
				$filtered_products_gold_colors[] = 'Three Tone';
			} elseif (stripos($value->sku, 'Two Tone') !== false) {
				$filtered_products_gold_colors[] = 'Two Tone';
			} elseif (stripos($value->sku, 'White Gold') !== false) {
				$filtered_products_gold_colors[] = 'White Gold';
			} elseif (stripos($value->sku, 'Yellow Gold') !== false) {
				$filtered_products_gold_colors[] = 'Yellow Gold';
			} elseif (stripos($value->sku, 'Platinum(950)') !== false) {
				$filtered_products_gold_colors[] = 'Platinum(950)';
			}

			$filtered_inventory_status[] = ucwords($value->inventory_status);

			$productManager = DB::select("SELECT product_manager_id,product_manager_name FROM grp_productmanager WHERE product_manager_certificates LIKE '%" . $value->certificate_no . "%'");

			if (count($productManager) > 0) {
				$filtered_virtual_position[$productManager[0]->product_manager_id] = $productManager[0]->product_manager_name;
			}
		});*/

		$filtered_products = $prod->unique('entity_id')->pluck(['entity_id'])->toArray(); // unique('entity_id')->
		//dd($prod->toArray());
		$filtered_certificates = $prod->pluck(['certificate_no'])->toArray(); // unique('certificate_no')->
		//$t2 = microtime(true);
		//printf("DONE!\n");
		//printf("Time elapsed: %.5f \n", $t2 - $t1);
		//exit;
		$filtered_virtual_position = InventoryHelper::getVirtualBoxFilteredCollection($filtered_certificates);

		//dd($virtualCollection);exit;

		//foreach ($prod as $key => $product) {
		//$filtered_products[] = $product->entity_id;
		/*if (stripos($product->sku, '14K') !== false) {
					$filtered_products_gold_quality[] = '14K';
				} elseif (stripos($product->sku, '18K') !== false) {
					$filtered_products_gold_quality[] = '18K';
			*/
		/*if (stripos($product->sku, 'Rose Gold') !== false) {
					$filtered_products_gold_colors[] = 'Rose Gold';
				} elseif (stripos($product->sku, 'Three Tone') !== false || stripos($product->sku, 'Threetone') !== false) {
					$filtered_products_gold_colors[] = 'Three Tone';
				} elseif (stripos($product->sku, 'Two Tone') !== false) {
					$filtered_products_gold_colors[] = 'Two Tone';
				} elseif (stripos($product->sku, 'White Gold') !== false) {
					$filtered_products_gold_colors[] = 'White Gold';
				} elseif (stripos($product->sku, 'Yellow Gold') !== false) {
					$filtered_products_gold_colors[] = 'Yellow Gold';
				} elseif (stripos($product->sku, 'Platinum(950)') !== false) {
					$filtered_products_gold_colors[] = 'Platinum(950)';
			*/
		//echo "<pre>";
		//print_r($filtered_products_gold_colors);exit;
		//echo ucwords($product->inventory_status);exit;
		//$filtered_inventory_status[] = ucwords(trim($product->inventory_status_value));

		/*$productManager = DB::select("SELECT product_manager_id,product_manager_name FROM grp_productmanager WHERE product_manager_certificates LIKE '%" . $product->certificate_no . "%'");

			if (count($productManager) > 0) {
				$filtered_virtual_position[$productManager[0]->product_manager_id] = $productManager[0]->product_manager_name;
			}*/
		//}
		$filtered_products_gold_quality = array_unique($filtered_products_gold_quality);
		$filtered_products_gold_colors = array_unique($filtered_products_gold_colors);
		$filtered_inventory_status = array_unique($filtered_inventory_status);
		//}
		$response = array();
		/*echo "<pre>";
		print_r($params);exit;*/

		$response['diamond_quality_filters'] = InventoryHelper::getDiamondQuality(isset($params['diamond_quality']) ? $params['diamond_quality'] : '', $filtered_products);
		$response['category_filters'] = InventoryHelper::getCategoryFilter(isset($params['category']) ? $params['category'] : '', $filtered_products);
		$response['gold_purity_filters'] = InventoryHelper::getGoldPurity(isset($params['gold_purity']) ? $params['gold_purity'] : '', $filtered_products_gold_quality);
		$response['gold_colors_filters'] = InventoryHelper::getGoldColor(isset($params['gold_color']) ? $params['gold_color'] : '', $filtered_products_gold_colors);
		$response['status_filters'] = InventoryHelper::getInventoryStatusFilter($filtered_inventory_status, isset($params['status']) ? $params['status'] : '');
		$response['virtual_filters'] = InventoryHelper::getInventoryVirtualFilter($filtered_virtual_position, isset($params['virtualproducts']) ? $params['virtualproducts'] : '');
		echo json_encode($response);exit;
	}
	//For prominent filter
	public function getProminentFilterX(Request $request) {
		$data = array();
		$params = $request->post();
		//echo "<pre>";
		//print_r($params);exit;
		$our_categories = "14,287,6,7,8,9,124,289,290,195,43,293,165,295";
		$our_categories_exp = explode(',', $our_categories);
		//print_r(implode("','", $our_categories_exp));exit;
		$our_categories_for_query = '';
		$our_categories_for_query = implode("','", $our_categories_exp);

		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$searchValue = (!empty($params['search_value']) ? $params['search_value'] : '');
		$stalen = $start / $length;
		$curpage = $stalen + 1;
		DB::setTablePrefix('');
		$prod = DB::table('catalog_product_flat_1 as e')
			->select('e.entity_id', 'e.sku', 'e.certificate_no', 'e.approval_memo_generated', 'e.approval_invoice_generated', 'e.return_memo_generated', 'e.type_id', 'e.attribute_set_id', 'e.isreadytoship', 'e.rts_position', 'e.rts_stone_quality', 'e.status', 'e.custom_price', 'inventory_management.inventory_status', 'inventory_management.pr_name')
			->rightJoin('qrcode_inventory_management as inventory_management', 'e.entity_id', '=', 'inventory_management.pr_id');
		if (!isset($params['category'])) {
			$prod->join('catalog_category_product', 'e.entity_id', '=', 'catalog_category_product.product_id');
			$prod->whereIn('category_id', [DB::raw("'" . $our_categories_for_query . "'")]);
		} else {
			$category = implode("','", $params['category']);
		}
		//when category is selected
		if (isset($category)) {
			$prod->join('catalog_category_product', 'e.entity_id', '=', 'catalog_category_product.product_id');
			$prod->whereIn('category_id', [DB::raw("'" . $category . "'")]);
		}
		if (!empty($params['virtualproducts'])) {
			$virtualProduct = DB::select("select product_manager_certificates from grp_productmanager where product_manager_id=" . $params['virtualproducts']);
			$virtualProduct = explode(',', $virtualProduct[0]->product_manager_certificates);
			$virtualProduct = implode("','", $virtualProduct);
			//$products_collection->addFieldToFilter('certificate_no', array('in' => $exploads));
			$prod->whereIn('certificate_no', [DB::raw("'" . $virtualProduct . "'")]);
			//echo $prod->toSql();exit;
		}
		$priceStart = '';
		$priceTo = '';
		if (($params['price_start'] != '') && ($params['price_to'] != '')) {
			$priceStart = $params['price_start'];
			$priceTo = $params['price_to'];
		}
		if ((isset($priceStart)) && (isset($priceTo))) {
			$prod->where('e.custom_price', '>=', DB::raw("$priceStart"));
			$prod->where('e.custom_price', '<=', DB::raw("$priceTo"));
		}
		if (!empty($params['status'])) {
			//$products_collection->getSelect()->where("inventory_management.inventory_status='" . strtolower($params['status']) . "'");
			$status = strtolower($params['status']);
			$prod->where('inventory_management.inventory_status', '=', DB::raw("'$status'"));
		}

		//For gold purity filter
		if (isset($params['gold_purity']) && isset($params['gold_color'])) {
			$gold_purity = $params['gold_purity'];
			$goldcolor = $params['gold_color'];
			foreach ($gold_purity as $value) {
				if ($value === '14K') {
					foreach ($goldcolor as $goldcolorval) {
						$values[] = $value . ' ' . $goldcolorval;
					}
				} else if ($value === '18K') {
					foreach ($goldcolor as $goldcolorval) {
						$values[] = $value . ' ' . $goldcolorval;
					}
				}
			}
			$mainGoldQuality = implode(",", $values);
			$goldQuality = explode(',', $mainGoldQuality);
			$goldQuality = implode("','", $goldQuality);
			$prod->whereIn('metal_quality_value', [DB::raw("'" . $goldQuality . "'")]);
			//echo $prod->toSql();exit;
		} else if (isset($params['gold_color'])) {
			$goldColor = implode(",", $params['gold_color']);
			//echo $goldColor;exit;
			$prod->where('e.metal_quality_value', 'like', DB::raw("'%$goldColor%'"));
			$prod->orWhere('e.metal_quality_value', 'like', DB::raw("'%$goldColor'"));
			$prod->orWhere('e.metal_quality_value', 'like', DB::raw("'$goldColor%'"));

		} else if (isset($params['gold_purity'])) {
			$gold_purity = $params['gold_purity'];
			if (count($gold_purity) > 0) {
				$gold_purity_arr = array();
				foreach ($gold_purity as $pur_key => $purity) {
					$gold_purity_arr[] = array('like' => '%' . $purity . '%');
					$prod->where('e.metal_quality_value', 'like', DB::raw("'%$purity%'"));
				}
				// print_r($gold_purity_arr);exit;

			}
		}
		//For diamond quality filter
		if (isset($params['diamond_quality'])) {
			$diamond_quality = implode("','", $params['diamond_quality']);
			$prod->whereIn('e.rts_stone_quality', [DB::raw("'" . $diamond_quality . "'")]);
		}
		//echo $prod->toSql();exit;
		if (!empty($searchValue)) {
			$prod->where('e.metal_quality', 'like', DB::raw("'%$searchValue%'"));
			$prod->orWhere('e.rts_stone_quality', 'like', DB::raw("'%$searchValue%'"));
			$prod->orWhere('e.sku', 'like', DB::raw("'%$searchValue%'"));
			$prod->orWhere('e.certificate_no', 'like', DB::raw("'%$searchValue%'"));
			$prod->orWhere('e.custom_price', 'like', DB::raw("'%$searchValue%'"));
			$prod->orWhere('e.virtual_product_manager', 'like', DB::raw("'%$searchValue%'"));
		}
		//echo $prod->toSql();exit;
		$filtered_products = array();
		$filtered_products_gold_quality = array();
		$filtered_products_gold_colors = array();
		$filtered_inventory_status = array();
		$filtered_virtual_position = array();
		$productCollection = $prod->get();
		//print_r($params['filterapplied']);exit;
		//if ($params['filterapplied'] == 'true') {
		//echo "<pre>";
		//print_r($productCollection);exit;
		foreach ($productCollection as $key => $product) {
			$filtered_products[] = $product->entity_id;
			if (strpos($product->sku, '14K') !== false) {
				$filtered_products_gold_quality[] = '14K';
			} elseif (strpos($product->sku, '18K') !== false) {
				$filtered_products_gold_quality[] = '18K';
			}
			if (strpos($product->sku, 'Rose Gold') !== false) {
				$filtered_products_gold_colors[] = 'Rose Gold';
			} elseif (strpos($product->sku, 'Three Tone') !== false) {
				$filtered_products_gold_colors[] = 'Three Tone';
			} elseif (strpos($product->sku, 'Two Tone') !== false) {
				$filtered_products_gold_colors[] = 'Two Tone';
			} elseif (strpos($product->sku, 'Three Tone') !== false) {
				$filtered_products_gold_colors[] = 'Three Tone';
			} elseif (strpos($product->sku, 'White Gold') !== false) {
				$filtered_products_gold_colors[] = 'White Gold';
			} elseif (strpos($product->sku, 'Yellow Gold') !== false) {
				$filtered_products_gold_colors[] = 'Yellow Gold';
			} elseif (strpos($product->sku, 'Platinum(950)') !== false) {
				$filtered_products_gold_colors[] = 'Platinum(950)';
			}
			//echo "<pre>";
			//print_r($filtered_products_gold_colors);exit;
			//echo ucwords($product->inventory_status);exit;
			$filtered_inventory_status[] = ucwords($product->inventory_status);

			$productManager = DB::select("SELECT product_manager_id,product_manager_name FROM grp_productmanager WHERE product_manager_certificates LIKE '%" . $product->certificate_no . "%'");

			if (count($productManager) > 0) {
				$filtered_virtual_position[$productManager[0]->product_manager_id] = $productManager[0]->product_manager_name;
			}
		}
		$filtered_products_gold_quality = array_unique($filtered_products_gold_quality);
		$filtered_products_gold_colors = array_unique($filtered_products_gold_colors);
		$filtered_inventory_status = array_unique($filtered_inventory_status);
		//}
		$response = array();
		/*echo "<pre>";
		print_r($params);exit;*/
		$response['diamond_quality_filters'] = InventoryHelper::getDiamondQuality(isset($params['diamond_quality']) ? $params['diamond_quality'] : '', $filtered_products);
		$response['category_filters'] = InventoryHelper::getCategoryFilter(isset($params['category']) ? $params['category'] : '', $filtered_products);
		$response['gold_purity_filters'] = InventoryHelper::getGoldPurity(isset($params['gold_purity']) ? $params['gold_purity'] : '', $filtered_products_gold_quality);
		$response['gold_colors_filters'] = InventoryHelper::getGoldColor(isset($params['gold_color']) ? $params['gold_color'] : '', $filtered_products_gold_colors);
		$response['status_filters'] = InventoryHelper::getInventoryStatusFilter($filtered_inventory_status, isset($params['status']) ? $params['status'] : '');
		$response['virtual_filters'] = InventoryHelper::getInventoryVirtualFilter($filtered_virtual_position, isset($params['virtualproducts']) ? $params['virtualproducts'] : '');
		echo json_encode($response);exit;
	}
	//Change inventory status & other actions
	public function changeInventoryStatus(Request $request) {
		$params = $request->post();

		$inventoryStatus = isset($params['inventoryCode']) ? $params['inventoryCode'] : '';
		$productIds = isset($params['productIds']) ? $params['productIds'] : array();
		//print_r($productIds);exit;
		if (!empty($productIds) && !is_array($productIds)) {
			$productIds = explode(',', $productIds);
		}

		if (!empty($productIds)) {
			try
			{
				foreach ($productIds as $productId) {
					InventoryHelper::changeInventoryStatus($productId, $inventoryStatus);

				}

				$response['status'] = true;
				$response['message'] = Config::get('constants.message.inventory_status_changed');
			} catch (Exception $e) {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_status_not_changed');
			}
		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_status_product_not_selected');
		}
		echo json_encode($response);exit;
	}
	//Get exhibition modal content
	public function getexhibitionmodalcontent(Request $request) {

		$params = $request->post();
		$productIds = isset($params['productIds']) ? $params['productIds'] : '';
		$customerId = $createdBy = Auth::user()->id;

		return view('inventory.exhibitionmodal')->with(array('productIds' => $productIds, 'customerId' => $customerId));
	}
	// Generate Exhibition Excel
	public function storeExhibitionData(Request $request) {
		$params = $request->post();
		$all = Exhibition::get();
		$productIds = isset($params['product_ids']) ? explode(',', $params['product_ids']) : '';
		$productIds = array_unique($productIds);
		$exhibition = new Exhibition;
		$exhibition->title = isset($params['exhibition_title']) ? $params['exhibition_title'] : '';
		$exhibition->place = isset($params['exhibition_place']) ? $params['exhibition_place'] : '';
		$exhibition->address = isset($params['exhibition_address']) ? $params['exhibition_address'] : '';
		$exhibition->markup = isset($params['exhibition_markup']) ? $params['exhibition_markup'] : 0;
		$exhibition->qty = count($productIds);
		$exhibition->created_by = isset($params['customerId']) ? $params['customerId'] : 0;
		$exhibition->save();
		$exhibitionId = DB::getPdo()->lastInsertId();

		foreach ($productIds as $key => $productId) {
			ExhibitionProducts::create(array('product_id' => $productId, 'exhibition_id' => $exhibitionId, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));
			$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
			$exhibitionStatusVal = isset($inventoryStatus['outforexhibition']) ? $inventoryStatus['outforexhibition'] : '';
			InventoryHelper::changeInventoryStatus($productId, $exhibitionStatusVal);
		}
		Cache::forget('all_products_ajax');
		Cache::forget('all_products');
		if (!empty($exhibitionId)) {
			$response['status'] = true;
			$response['exhibition_id'] = $exhibitionId;
			$response['message'] = config('constants.message.exhibition_created_successfully');
		} else {
			$response['status'] = false;
			$response['exhibition_id'] = '';
			$response['message'] = config('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}
	//Generate exhibition excel
	public function generateExhibitionExcel($id) {
		if (!empty($id)) {
			InventoryHelper::downloadExhibitionProductsExcel($id);
		}
	}
	//Get total in/out products count
	public function getInventoryProductCount(Request $request) {
		$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
		$inStatusVal = $inventoryStatus['in'];
		$outStatusVal = $inventoryStatus['out'];

		$totalInProducts = InventoryHelper::getTotalinventoryInOutCount($inStatusVal);
		$totalOutProducts = InventoryHelper::getTotalinventoryInOutCount($outStatusVal);
		$totalProductCount = array('in_products' => $totalInProducts, 'out_products' => $totalOutProducts);
		echo json_encode($totalProductCount);exit;
	}
	//Export product excel
	public function exportProductExcel(Request $request) {
		$params = $request->post();
		$serialNumber = 0;
		if (isset($params['csvname']) || !empty($params['csvname'])) {
			$csvname = $params['csvname'];
		} else {
			$csvname = "inventory_products";
		}
		//$productIds = isset($params['productIds']) ? $params['productIds'] : array();
		//$productIds = Session::get('qr_product_ids');
		$productIds = $params['productIds'];

		if (!empty($productIds)) {
			$productIds = explode(',', $productIds);
		}

		$imageDirectory = config('constants.dir.website_url_for_product_image_curl');
		$defaultProductImage = $imageDirectory . 'def_1.png';

		if (count($productIds) > 0) {
			$productIds = implode("','", $productIds);
			$sql = "SELECT `qr`.*,`cata`.`certificate_no` FROM qrcode_inventory_management AS `qr` inner join catalog_product_entity AS `en` ON `en`.`entity_id` = `qr`.`pr_id` left join catalog_product_flat_1 AS `cata` ON `cata`.`entity_id` = `qr`.`pr_id` WHERE (`qr`.`pr_id` IN('" . $productIds . "')) order by pr_id";
		} else {
			$sql = "SELECT `qr`.*,`cata`.`certificate_no` FROM qrcode_inventory_management AS `qr` inner join catalog_product_entity AS `en` ON `en`.`entity_id` = `qr`.`pr_id` left join catalog_product_flat_1 AS `cata` ON `cata`.`entity_id` = `qr`.`pr_id`";
		}
		$inventoryProducts = DB::select($sql);
		DB::setTablePrefix('');
		$productCollection = '';
		$data = array();

		foreach ($inventoryProducts as $key => $inventory) {
			$id = $inventory->pr_id;
			$serialNumber++;
			$productCollection = DB::select("SELECT * from catalog_product_flat_1 WHERE entity_id=" . $id);
			if (empty($productCollection)) {
				continue;
			}

			$product = $productCollection[0];
			//$product = InventoryHelper::getAllProductsCollection(true);
			//$product = $product->where('entity_id', $id);
			$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
			$productImage = (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage);
			//$imageData = ShowroomHelper::file_get_contents_curl($productImage);
			$ext = pathinfo($productImage, PATHINFO_EXTENSION);
			if (!file_exists(public_path('img/product'))) {
				mkdir(public_path('img/product'), 0777, true);
			}
			$file = 'img/product/product' . $product->entity_id . '.' . $ext;

			//file_put_contents( $file, $imageData );
			if (InventoryHelper::isImageExist($productImage) == '200') {
				$curlCh = curl_init();
				curl_setopt($curlCh, CURLOPT_URL, $productImage);
				curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curlCh, CURLOPT_BINARYTRANSFER, 1);
				curl_setopt($curlCh, CURLOPT_SSLVERSION, 3);
				$curlData = curl_exec($curlCh);
				curl_close($curlCh);

				$imgfile = fopen($file, "w+");
				fputs($imgfile, $curlData);
				fclose($imgfile);
			} else {
				$file = 'img/def_img.png';
			}

			$sku = $product->sku;
			$certificateNo = (!empty($product->certificate_no) ? $product->certificate_no : 'N/A');
			$inventoryStatus = $inventory->inventory_status;
			$metalQualityOption = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_quality' AND EAOV.store_id = 0");
			$metalQuality = '';
			foreach ($metalQualityOption as $key => $metal) {
				if ($metal->option_id == $product->metal_quality) {
					$metalQuality = $metal->value;
				}
			}
			$metalProductData = DB::select("SELECT * FROM `grp_metal` WHERE metal_product_id=" . $product->entity_id);
			$metalWeight = isset($metalProductData[0]->metal_weight) ? $metalProductData[0]->metal_weight : '';
			$updatedPrice = InventoryHelper::getMetalRingSize($product->entity_id, $product->rts_ring_size);

			$productOptions = InventoryHelper::getProductOptions($product->entity_id);
			$labour = "0.00";
			foreach ($productOptions as $key => $option) {
				if ($option->type != 'drop_down') {
					$values = InventoryHelper::getOptionValue($option->option_id);
					foreach ($values as $key => $value) {
						if ($value->title == $product->rts_ring_size) {
							$labour = $value->metal_labour_charge + $value->metal_product_price;
						}
					}
				}
			}
			$stone = $product->rts_stone_quality;
			$stoneData = InventoryHelper::getStoneData($product->entity_id);
			//echo "<pre>";
			//echo "Product--".$product->entity_id;
			//print_r($stoneData);exit;
			//$stoneData = ShowroomHelper::getSideStoneData($product->entity_id, $stone);
			$gemStoneData = InventoryHelper::getGemStoneData($product->entity_id);
			$stoneCaret = isset($stoneData['stone']) ? $stoneData['stone'] : 0;
			$stoneTotal = isset($stoneData['totalcts']) ? $stoneData['totalcts'] : 0;
			$gemStone = isset($gemStoneData['simple']) ? $gemStoneData['simple'] : 0;
			$metalData = InventoryHelper::getMetalData($product->entity_id);
			$metalData = (array) $metalData;
			$labourCharge = 0;
			foreach ($metalData as $key => $metal) {
				if ($key == 'labour-charge') {
					$labourCharge = $metal;
					$labourCharge = str_replace('Rs.', '', $labourCharge);
				}
			}

			$metalRate = isset($metalData['simple']) ? $metalData['simple'] : 0;
			$metalWeight = isset($metalData['weight']) ? $metalData['weight'] : 0;
			//$estimateValue = (float) $labour_charge + (float) $matel_rate; // + (float) $extraPrice;

			$stonePrice = str_replace('Rs', '', preg_replace('/[^A-Za-z0-9]/', "", $stoneData['stone_price']['0']));

			$quality = $product->rts_stone_quality;

			$productWithOption = array();

			foreach ($stoneData as $key => $stone) {
				if ($key == "type" || $key == "shape" || $key == "setting" || $key == "stone_use" || $key == "totalcts" || $key == "percts" || $key == "stone_price" || $key == "stoneclarity") {
					$sizeof = count($stone);
					$productWithOption[0][$key] = $stoneData[$key][0];
					for ($st = 0; $st < $sizeof; $st++) {
						$productWithOption[$st][$key] = $stoneData[$key][$st];
					}
				}
			}
			$stoneShape = array();
			$stoneClarity = array();
			$stonePices = array();
			$stoneWeight = array();
			$caratPrice = array();
			foreach ($productWithOption as $optionData) {
				$stoneShape[] = ucwords(strtolower($optionData['shape']));
				if (!empty($optionData['stoneclarity'])) {
					$stoneClarity[] = $optionData['stoneclarity'];
				} else {
					$stoneClarity[] = $quality;
				}
				$stonePices[] = $optionData['stone_use'];
				$stoneWeight[] = round((float) $optionData['totalcts'] * (float) $optionData['stone_use'], 2);
				$caratPrice[] = preg_replace("/[^0-9]/", "", $optionData['stone_price']);
			}
			$implodedStoneShape = implode(",", $stoneShape);
			$implodedStoneClarity = implode(",", $stoneClarity);
			$implodedStonePices = implode(",", $stonePices);
			$implodedStoneweight = implode(",", $stoneWeight);
			$implodedCaratPrice = implode(",", $caratPrice);
			$price = $product->custom_price;

			$maxStoneCount = max(count($stoneShape), count($stoneClarity), count($stonePices), count($stoneWeight), count($caratPrice));
			if (!empty($priceMarkup)) {
				$markupAmount = ($price * $priceMarkup) / 100;
				$price += $markupAmount;
			}
			for ($index = 0; $index < $maxStoneCount; $index++) {
				$data[] = array(
					'Sr No.' => ($index == 0) ? $serialNumber : '',
					'Image' => ($index == 0) ? $file : '',
					'Name' => ($index == 0) ? $product->name : '',
					'SKU' => ($index == 0) ? $sku : '',
					'Certificate No' => ($index == 0) ? $certificateNo : '',
					'Status' => ($index == 0) ? ucwords(strtolower($inventoryStatus)) : '',
					'Metal Quality' => ($index == 0) ? $metalQuality : '',
					'Metal Weight' => ($index == 0) ? $metalWeight : '',
					'Metal Price' => ($index == 0) ? ShowroomHelper::currencyFormatWithoutIcon($metalRate) : '',
					'Labour Amount' => ($index == 0) ? $labourCharge : '',
					'Stone Shape' => isset($stoneShape[$index]) ? $stoneShape[$index] : '',
					'Stone Clarity' => isset($stoneClarity[$index]) ? $stoneClarity[$index] : '',
					'Stone Pcs' => isset($stonePices[$index]) ? $stonePices[$index] : '',
					'Stone Weight' => isset($stoneWeight[$index]) ? ($stoneWeight[$index]) : '',
					'Stone Price' => isset($caratPrice[$index]) ? ShowroomHelper::currencyFormatWithoutIcon($caratPrice[$index]) : '',
					'Price' => ($index == 0) ? ShowroomHelper::currencyFormatWithoutIcon(round($price)) : '',
				);
			}
		}
		$row = 0;
		return \Excel::create($csvname, function ($excel) use ($data) {
			$excel->sheet('Sheet', function ($sheet) use ($data) {
				foreach ($data as $row => $columns) {
					foreach ($columns as $column => $value) {
						if (strpos($value, 'img/') !== false) {
							$objDrawing = new PHPExcel_Worksheet_Drawing();
							$objDrawing->setName('inventory_img');
							$objDrawing->setDescription('inventory_img');
							$objDrawing->setPath($value);
							$rowNo = (int) $row + 2;
							$objDrawing->setCoordinates('B' . $rowNo);
							$objDrawing->setOffsetX(5);
							$objDrawing->setOffsetY(5);
							$objDrawing->setWidth(80);
							$objDrawing->setHeight(80);
							$objDrawing->setWorksheet($sheet);
							//$sheet->setSize('A1', 50);
							//$sheet->setWidth('A', 0.5);
							$sheet->setSize(array(
								'B1' . $rowNo => array(
									'width' => 15,
									'height' => 15,
								),
							));
							$sheet->getRowDimension($rowNo)->setRowHeight(70);
							//$sheet->getColumnDimension('A')->setWidth(60);
							//$sheet->getColumnDimension('A')->setAutoSize(true);
							if ($data[$row]['Image'] == $value) {
								$data[$row]['Image'] = '';
							}
						}
					}
				}
				$sheet->fromArray($data);
			});
		})->download('xlsx');
		DB::setTablePrefix('dml_');
	}
	//Export product CSV
	public function exportProductCsv(Request $request) {
		$params = $request->post();
		$productIds = isset($params['productIds']) ? $params['productIds'] : array();
		if (!empty($productIds) && !is_array($productIds)) {
			$productIds = explode(',', $productIds);
		}
		if (count($productIds) > 0) {
			$productIds = implode("','", $productIds);
			$sql = "SELECT `qr`.*,`cata`.`certificate_no` FROM qrcode_inventory_management AS `qr` inner join catalog_product_entity AS `en` ON `en`.`entity_id` = `qr`.`pr_id` left join catalog_product_flat_1 AS `cata` ON `cata`.`entity_id` = `qr`.`pr_id` WHERE (`qr`.`pr_id` IN('" . $productIds . "')) order by pr_id";
		} else {
			$sql = "SELECT `qr`.*,`cata`.`certificate_no` FROM qrcode_inventory_management AS `qr` inner join catalog_product_entity AS `en` ON `en`.`entity_id` = `qr`.`pr_id` left join catalog_product_flat_1 AS `cata` ON `cata`.`entity_id` = `qr`.`pr_id`";
		}
		$inventoryProducts = DB::select($sql);
		$productCollection = '';
		$data = array();
		foreach ($inventoryProducts as $key => $inventory) {
			$id = $inventory->pr_id;
			$sku = $inventory->pr_sku;
			$name = $inventory->pr_name;
			$status = $inventory->inventory_status;
			$certificateNo = $inventory->certificate_no;
			$releaseDate = $inventory->release_date;
			$data[] = array(
				'Name' => $name,
				'Sku' => $sku,
				'Certificate No' => $certificateNo,
				'Status' => ucwords(strtolower($status)),
				'Release Date' => $releaseDate,
			);
		}
		$row = 0;
		return \Excel::create('inventory_products', function ($excel) use ($data) {
			$excel->sheet('Sheet', function ($sheet) use ($data) {
				foreach ($data as $row => $columns) {
					foreach ($columns as $column => $value) {
						if (strpos($value, 'img/') !== false) {
							$objDrawing = new PHPExcel_Worksheet_Drawing();
							$objDrawing->setName('inventory_img');
							$objDrawing->setDescription('inventory_img');
							$objDrawing->setPath($value);
							$rowNo = (int) $row + 2;
							$objDrawing->setCoordinates('B' . $rowNo);
							$objDrawing->setOffsetX(5);
							$objDrawing->setOffsetY(5);
							$objDrawing->setWidth(80);
							$objDrawing->setHeight(80);
							$objDrawing->setWorksheet($sheet);
							//$sheet->setSize('A1', 50);
							//$sheet->setWidth('A', 0.5);
							$sheet->setSize(array(
								'B1' . $rowNo => array(
									'width' => 15,
									'height' => 15,
								),
							));
							$sheet->getRowDimension($rowNo)->setRowHeight(70);
							//$sheet->getColumnDimension('A')->setWidth(60);
							//$sheet->getColumnDimension('A')->setAutoSize(true);
							if ($data[$row]['Image'] == $value) {
								$data[$row]['Image'] = '';
							}
						}
					}
				}
				$sheet->fromArray($data);
			});
		})->download('csv');
	}
	public function getInvoiceMemoModalContent(Request $request) {
		$params = $request->post();
		$productIds = isset($params['productIds']) ? $params['productIds'] : '';
		$operationType = isset($params['action']) ? $params['action'] : '';

		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$isFromCustomerView = isset($params['is_from_customer_view']) ? $params['is_from_customer_view'] : false;

		$countryListArr = array();
		if (!$isFromCustomerView) {
			/* //Get state list by cuontry id
				$get_country_list = '';
				if (App::environment('local')) {
					$get_country_list = Config::get('constants.apiurl.local.get_country_list');
				} else if (App::environment('test')) {
					$get_country_list = Config::get('constants.apiurl.test.get_country_list');
				} else {
					$get_country_list = Config::get('constants.apiurl.live.get_country_list');
				}
				//echo $get_country_list;exit;
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
				if (!empty($result)) {
					$countryList = json_decode($result);
				}
				foreach ($countryList->data as $key => $countryitem) {
					$countryListArr[$key]['country_id'] = $countryitem->country_id;
					$countryListArr[$key]['name'] = $countryitem->name;
				}
				usort($countryListArr, function ($item1, $item2) {
					return $item1['name'] <=> $item2['name'];
			*/
		}
		$countryListArr = array('country_id' => 'IN', 'name' => 'India');
		$franchiseeData = InventoryHelper::getFranchiseeData();

		$grandTotal = 0;

		//Get invoice total
		if ($operationType == 'invoice') {
			$invoiceProductIds = explode(',', $productIds);
			foreach ($invoiceProductIds as $key => $productId) {
				$collection = InventoryHelper::getAllProductsCollection();
				$collection = $collection->where('entity_id', $productId);
				$price = 0;
				foreach ($collection as $key => $product) {
					$price = isset($product->custom_price) ? $product->custom_price : 0;
				}
				$grandTotal += (float) $price;
			}
		}
		$invoiceLimitConfigData = Setting::where('key', config('constants.settings.keys.invoice_limit'))->first('value');
		$invoiceLimit = isset($invoiceLimitConfigData->value) ? $invoiceLimitConfigData->value : 0;
		//print_r($invoiceLimitConfigData->value);exit;

		return view('inventory.invoicememomodal')->with(array('operationType' => $operationType, 'countryList' => $countryListArr, 'franchiseeData' => $franchiseeData, 'productIds' => $productIds, 'invoiceTotal' => $grandTotal, 'invoiceLimit' => $invoiceLimit, 'isFromCustomerView' => $isFromCustomerView, 'customerId' => $customerId));
	}
	//Generate invoice/memo
	public function generateInvoiceMemo(Request $request) {
		$params = $request->post();
		$paymentData = array(
			"discount_value" => isset($params['txtdiscountval']) ? $params['txtdiscountval'] : '',
			"discount_type" => isset($params['discount_type']) ? $params['discount_type'] : '',
			"franchise_id" => isset($params['franchisee']) ? $params['franchisee'] : '',
			"franchise_name" => isset($params['franchisee_name']) ? $params['franchisee_name'] : '',
			"franchise_commision" => isset($params['txtfranchisecommission']) ? $params['txtfranchisecommission'] : '',
			'agent_name' => isset($params['txtagentname']) ? $params['txtagentname'] : '',
			"agent_commision" => isset($params['txtagentcommission']) ? $params['txtagentcommission'] : '',
			"payment_mode" => isset($params['paymentmode']) ? $params['paymentmode'] : '',
			"approval_type" => isset($params['approval_type']) ? $params['approval_type'] : '',
			"deposit_type" => isset($params['deposit_type']) ? $params['deposit_type'] : '',
			"transportation_mode" => isset($params['transportation_mode']) ? $params['transportation_mode'] : '',
			"shipping_charge" => isset($params['txtshippingcharge']) ? $params['txtshippingcharge'] : '',
		);
		$gstinAttachment = $request->file('gstinattachment');
		$gstinNumber = isset($params['txtgstin']) ? $params['txtgstin'] : '';
		$emailAddress = !empty($params['txtemail']) ? $params['txtemail'] : $params['txtdmusercodeemail'];
		$firstName = isset($params['txtfirstname']) ? $params['txtfirstname'] : '';
		$lastName = isset($params['txtlastname']) ? $params['txtlastname'] : '';
		$frncode = isset($params['txtfrncode']) ? $params['txtfrncode'] : '';
		$contactNumber = isset($params['txtcontactnumber']) ? $params['txtcontactnumber'] : '';
		$street = isset($params['txtaddress']) ? $params['txtaddress'] : '';
		$countryId = isset($params['selectcountry']) ? $params['selectcountry'] : '';
		$region = isset($params['txtstateprovince']) ? $params['txtstateprovince'] : '';
		$city = isset($params['txtcity']) ? $params['txtcity'] : '';
		$postcode = isset($params['txtzipcode']) ? $params['txtzipcode'] : '';
		$password = $firstName . rand(10000, 999) . '@dealer';
		$operationType = isset($params['operation_type']) ? $params['operation_type'] : '';
		$customerId = isset($params['customerId']) ? $params['customerId'] : '';
		$invoiceMemoWithOldData = isset($params['invoicememo_with_olddata']) ? $params['invoicememo_with_olddata'] : '';

		$invoiceNumber = isset($params['txtinvoicenumber']) ? $params['txtinvoicenumber'] : '';
		$invoiceDate = isset($params['txtinvoicedate']) ? $params['txtinvoicedate'] : '';

		$approvalNumber = isset($params['txtmemonumber']) ? $params['txtmemonumber'] : '';
		//var_dump($approvalNumber);exit;
		$approvalDate = isset($params['txtapprovaldate']) ? $params['txtapprovaldate'] : '';
		$response = array();
		$approval_no = isset($params['approval_no']) ? $params['approval_no'] : '';
		$isSeperateInvoice = (isset($params['radioSeperateInvoice']) && $params['radioSeperateInvoice'] == 'yes') ? true : false;

		$totalInvoiceCustomer = 0;
		$invoiceGrandTotal = 0;
		$invoiceGrandTotal = isset($params['invoice_grand_total']) ? $params['invoice_grand_total'] : 0;
		$discount14K = 0;
		$discount18K = 0;
		$column14K = '';
		$column18K = '';

		//get default discount
		$discount = InventoryHelper::getDefaultDiscount($invoiceGrandTotal);

		if ($params['customerType'] == 'new') {
			$discount14K = isset($discount['14_k_discount']) ? $discount['14_k_discount'] : 0;
			$discount18K = isset($discount['18_k_discount']) ? $discount['18_k_discount'] : 0;
		} else {
			$default14KDiscount = isset($discount['14_k_discount']) ? $discount['14_k_discount'] : 0;
			$default18KDiscount = isset($discount['18_k_discount']) ? $discount['18_k_discount'] : 0;
			//Get customer discount
			$customerDiscount = InventoryHelper::getApprovalDepositDiscount($invoiceGrandTotal, $customerId, $params['discount_type']);

			$discount14K = !empty($customerDiscount['14_k_discount']) ? $customerDiscount['14_k_discount'] : $default14KDiscount;
			$discount18K = !empty($customerDiscount['18_k_discount']) ? $customerDiscount['18_k_discount'] : $default14KDiscount;
			///echo $discount14K."  ".$discount18K;exit;
		}
		$childCustomerData = array();
		if ($isSeperateInvoice) {
			$totalInvoiceCustomer = isset($params['totalCustomer']) ? $params['totalCustomer'] : 0;
			$childCustomerData = array();
			for ($index = 0; $index < $totalInvoiceCustomer; $index++) {
				$childCustName = isset($params['txtchildcustname'][$index]) ? $params['txtchildcustname'][$index] : '';
				$childCustAddress = isset($params['txtchildcustaddress'][$index]) ? $params['txtchildcustaddress'][$index] : '';
				$childCustPanNo = isset($params['txtchildcustpanno'][$index]) ? $params['txtchildcustpanno'][$index] : '';
				$childCustomerData[$index]['name'] = $childCustName;
				$childCustomerData[$index]['address'] = $childCustAddress;
				$childCustomerData[$index]['pan_no'] = $childCustPanNo;
			}
		}

		DB::setTablePrefix('');
		if ($params['customerType'] == 'new') {
			/*Create Customer using API*/
			if (App::environment('local')) {
				//$url = Config::get('constants.apiurl.local.create_customer');
				$url = Config::get('app.create_customer');
			} else if (App::environment('test')) {
				$url = Config::get('constants.apiurl.test.create_customer');
			} else {
				$url = Config::get('constants.apiurl.live.create_customer');
			}

			//var_dump($url);exit;
			//check contact number exist
			//$isContactNumberExist = InventoryHelper::checkContactNumberValidation('', $contactNumber);
			$isContactNumberExist = CustomersHelper::isContactNumberExist('', 'primary_contact', $contactNumber);
			if ($isContactNumberExist) {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_telephone_already_exist');
				echo json_encode($response);exit;
			}
			$isfrnExist = InventoryHelper::checkFRNCodeValidation('', $frncode);
			if ($isfrnExist) {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_frncode_already_exist');
				echo json_encode($response);exit;
			}
			DB::setTablePrefix('dml_');

			if (!empty($frncode)) {
				$frncodeStr = '&frncode=' . $frncode;
			} else {
				$frncodeStr = '';
			}

			//echo $url;exit;
			$customerParams = 'firstname=' . $firstName . '&lastname=' . $lastName . '&email=' . $emailAddress . '&contact_number=' . $contactNumber . '&community=' . '1' . '&street=' . $street . '&country_id=' . $countryId . '&region=' . $region . '&city=' . $city . '&entity_customer=' . '1' . '&postcode=' . $postcode . '&password=' . $password . '&confirmation=' . '1' . '&franchisee_status=' . '2' . $frncodeStr;
			//echo "<pre>";
			//print_r($customerParams);exit;
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $customerParams);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$info = curl_getinfo($ch);
			$response = json_decode($result);

			if ($response->status == 'success') {
				$customerId = $response->customer_id;
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
					$websitePath = '';
					if (App::environment('local')) {
						/*$websitePath = Config::get('constants.apiurl.local.website_url_for_gst_pan_attachment');*/
						$websitePath = Config::get('app.website_url_for_gst_pan_attachment');
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
						//$websitePath = Config::get('constants.apiurl.local.website_url_for_gst_pan_attachment');
						$websitePath = Config::get('app.website_url_for_gst_pan_attachment');
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
			} else {
				echo $result;exit;
			}

			if (!empty($operationType) && $operationType == 'invoice') {
				if ($isSeperateInvoice) {
					$response = $this->generateSeperateInvoice($params['product_ids'], $customerId, $paymentData, $invoiceMemoWithOldData, $invoiceNumber, $invoiceDate, $approval_no, $isSeperateInvoice, $totalInvoiceCustomer, $invoiceGrandTotal, $childCustomerData, $discount14K, $discount18K);
				} else {
					$response = $this->generateInvoice($params['product_ids'], $customerId, $paymentData, $invoiceMemoWithOldData, $invoiceNumber, $invoiceDate, '', $invoiceGrandTotal, $discount14K, $discount18K);
				}

			} else if (!empty($operationType) && $operationType == 'memo') {
				//$response = $this->generateMemo($params['product_ids'], $customerId, $paymentData,$invoiceMemoWithOldData,$approvalNumber,$approvalDate);

				$response = $this->generateMemoNew($params['product_ids'], $customerId, $paymentData, $invoiceMemoWithOldData, $approvalNumber, $approvalDate);
			}
		} else if (!empty($customerId) && $params['customerType'] == 'existing') {

			if (!empty($operationType) && $operationType == 'invoice') {
				if ($isSeperateInvoice) {
					$response = $this->generateSeperateInvoice($params['product_ids'], $customerId, $paymentData, $invoiceMemoWithOldData, $invoiceNumber, $invoiceDate, $approval_no, $isSeperateInvoice, $totalInvoiceCustomer, $invoiceGrandTotal, $childCustomerData, $discount14K, $discount18K);
				} else {

					$response = $this->generateInvoice($params['product_ids'], $customerId, $paymentData, $invoiceMemoWithOldData, $invoiceNumber, $invoiceDate, '', $invoiceGrandTotal, $discount14K, $discount18K);
				}
			} else if (!empty($operationType) && $operationType == 'memo') {
				//$response = $this->generateMemo($params['product_ids'], $customerId, $paymentData,$invoiceMemoWithOldData,$approvalNumber,$approvalDate);

				$response = $this->generateMemoNew($params['product_ids'], $customerId, $paymentData, $invoiceMemoWithOldData, $approvalNumber, $approvalDate);
			}
		}
		DB::setTablePrefix('dml_');
		echo $response;exit;
	}
	//Check customer exist
	public function checkCustomerExist(Request $request) {
		$params = $request->post();
		$customerId = '';
		DB::setTablePrefix('');
		if (!empty(trim($params['dmcode_email']))) {
			if (strpos($params['dmcode_email'], 'DML') !== false || filter_var(trim($params['dmcode_email']), FILTER_VALIDATE_EMAIL)) {
				$customer = DB::table("customer_entity")->select('entity_id')->where('email', '=', DB::raw('"' . trim($params['dmcode_email']) . '"'))->get()->first();
				if (count($customer) > 0) {
					$customerId = $customer->entity_id;
				} else {
					$userId = str_replace('dml', '', strtolower(trim($params['dmcode_email'])));
					$customer = DB::table("customer_entity")->select('entity_id')->where('entity_id', 'LIKE', DB::raw('"' . $userId . '"'))->get()->first();
					//echo $customer;exit;
					if (count($customer) > 0) {
						$customerId = $customer->entity_id;
					}

				}
			} else {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_generate_invoicememo_customer_not_exist');
			}
		}
		$response = array();
		if (empty($customerId)) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_generate_invoicememo_customer_not_exist');
		} else {
			$response['status'] = true;
			$response['message'] = '';
			$response['customer_id'] = $customerId;
		}
		DB::setTablePrefix('dml_');
		echo json_encode($response);exit;
	}
	//Generate Seperate Invoice
	public function generateSeperateInvoice($productId, $customerId, $paymentData, $invoiceMemoWithOldData, $invoiceNumber, $invoiceDate, $approval_no = null, $isSeperateInvoice = false, $totalInvoiceCustomer = null, $invoiceGrandTotal = null, $childCustomerData = null, $discount14K, $discount18K) {
		if (strpos($productId, ',') !== false) {
			$productIds = explode(',', $productId);
		} else {
			$productIds = array($productId);
		}

		$generatedInvoiceCert = array();
		$fourteenKProducts = array();
		$eighteenKProducts = array();
		foreach ($productIds as $key => $productId) {
			DB::setTablePrefix('');
			$productData = DB::table('catalog_product_flat_1')->select('approval_invoice_generated', 'certificate_no', 'metal_quality_value')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
			$metalQuality = explode(' ', $productData->metal_quality_value);
			$metalQuality = isset($metalQuality[0]) ? $metalQuality[0] : '';
			if ($metalQuality == '14K') {
				$fourteenKProducts[] = $productId;
			} else if ($metalQuality == '18K') {
				$eighteenKProducts[] = $productId;
			}
			if ($productData->approval_invoice_generated) {
				$generatedInvoiceCert[] = $productData->certificate_no;
			}
		}
		$orderDiscount = 0;
		if (count($fourteenKProducts) > 0 && count($eighteenKProducts) > 0) {
			$orderDiscount = ($discount14K + $discount18K) / 2;
		} else if (count($fourteenKProducts) > 0 && count($eighteenKProducts) == 0) {
			$orderDiscount = $discount14K;
		} else if (count($fourteenKProducts) == 0 && count($eighteenKProducts) > 0) {
			$orderDiscount = $discount18K;
		}
		if (count($generatedInvoiceCert) > 0) {

			$response['status'] = 0;
			$response['message'] = Config::get('constants.message.invoice_already_generated') . implode(", ", $generatedInvoiceCert);
			echo json_encode($response);exit;
		} else {
			//echo $customerId;exit;
			if (App::environment('local')) {
				$generateInvoiceUrl = Config::get('constants.apiurl.local.generate_seperate_invoice');
			} else if (App::environment('test')) {
				$generateInvoiceUrl = Config::get('constants.apiurl.test.generate_seperate_invoice');
			} else {
				$generateInvoiceUrl = Config::get('constants.apiurl.live.generate_seperate_invoice');
			}
			//Get price markup for customer
			$priceMarkup = CustomersHelper::getCustomerAttrValue($customerId, 'price_markup');
			$paymentData = json_encode($paymentData);
			//print_r(implode(',', $productIds));exit;
			$invoiceParams = 'productIds=' . implode(',', $productIds) . '&customerId=' . $customerId . '&paymentData=' . $paymentData . '&invoice_number=' . $invoiceNumber . '&invoice_date=' . $invoiceDate . '&invoice_memo_with_old_data=' . $invoiceMemoWithOldData . '&approval_no=' . $approval_no . '&is_seperate_invoice=' . $isSeperateInvoice . '&total_customer=' . $totalInvoiceCustomer . '&child_customer_data=' . json_encode($childCustomerData) . '&invoice_grand_total=' . $invoiceGrandTotal . '&order_discount=' . $orderDiscount . '&fourteen_k_discount=' . $discount14K . '&eighteen_k_discount=' . $discount18K . '&price_markup=' . $priceMarkup;

			$ch = curl_init($generateInvoiceUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $invoiceParams);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$info = curl_getinfo($ch);
			$response = json_decode($result);
			$memoIds = array();
			if (isset($response->status) && $response->status) {
				DB::setTablePrefix('dml_');
				$childCustomerData = isset($response->child_customer_data) ? json_decode($response->child_customer_data) : array();

				foreach ($childCustomerData as $key => $childCustomer) {
					$data = array(
						'parent_customer_id' => $customerId,
						'child_customer_name' => $childCustomer->name,
						'child_customer_address' => $childCustomer->address,
						'child_customer_pan' => $childCustomer->pan_no,
						'invoice_id' => $childCustomer->invoice_id,
						'order_id' => $childCustomer->order_id,
						'created_at' => date('Y-m-d H:i:s'),
						'updated_at' => date('Y-m-d H:i:s'),
					);
					//insert into payment module
					$invoiceNumber = isset($childCustomer->invoice_number) ? $childCustomer->invoice_number : '';
					$invoiceTotal = isset($childCustomer->invoice_total) ? $childCustomer->invoice_total : 0;
					$customerName = isset($childCustomer->name) ? $childCustomer->name : '';
					$paymentTypeData = PaymentType::select('id', 'parent_id')->where('name', '=', 'Sales')->first();

					$paymentType = isset($paymentTypeData->id) ? $paymentTypeData->id : '';
					$paymentSubType = isset($paymentTypeData->parent_id) ? $paymentTypeData->parent_id : '';
					$createdBy = Auth::user()->id;
					$paymentData = array(
						'customer_name' => $customerName,
						'invoice_number' => $invoiceNumber,
						'invoice_amount' => $invoiceTotal,
						'account_status' => 1,
						'payment_status' => 0,
						'payment_form' => 'Incoming',
						'payment_sub_type' => $paymentType,
						'payment_type' => $paymentSubType,
						'customer_type' => 'Website',
						'created_at' => date('Y-m-d H:i:s'),
						'updated_at' => date('Y-m-d H:i:s'),
						'created_by' => $createdBy,
						'remarks' => 'Order Invoice',
					);
					Payment::create($paymentData);
					InvoiceCustomerDivision::create($data);
					$invoiceProductData = isset($childCustomer->product_data) ? json_decode($childCustomer->product_data) : '';
					foreach ($invoiceProductData as $key => $invoiceProduct) {
						InvoiceProducts::create(
							array(
								'product_id' => $invoiceProduct->product_id,
								'sku' => $invoiceProduct->sku,
								'metal_weight' => $invoiceProduct->metal_weight,
								'stone_weight' => $invoiceProduct->stone_weight,
								'unit_price' => $invoiceProduct->unit_price,
								'invoice_id' => $childCustomer->invoice_id,
								'created_at' => date('Y-m-d H:i:s'),
								'updated_at' => date('Y-m-d H:i:s'),
							)
						);
					}
					//Insert into invoice_logs
					InvoiceLogs::create(
						array(
							'user_id' => Auth::user()->id,
							'invoice_id' => $childCustomer->invoice_id,
							'product_data' => json_encode($invoiceProductData),
							'comment' => config('constants.message.invoice_created'),
							'created_at' => date('Y-m-d H:i:s'),
							'updated_at' => date('Y-m-d H:i:s'),
						)
					);
				}

				//echo "<pre>";
				//print_r($childCustomerData);exit;
				Cache::forget('all_products_ajax');
				Cache::forget('all_products');

				$invoiceNumber = isset($response->invoice_number) ? $response->invoice_number : '';
				foreach ($productIds as $key => $productId) {
					$data = array('status' => 'invoice', 'date' => date('Y-m-d H:i:s'));
					ApprovalMemoHistroy::where("product_id", $productId)->update($data);
				}
			}
			return $result;
		}
	}
	//Generate invoice
	public function generateInvoice($productId, $customerId, $paymentData, $invoiceMemoWithOldData, $invoiceNumber, $invoiceDate, $approval_no = null, $invoiceGrandTotal, $discount14K, $discount18K) {

		if (strpos($productId, ',') !== false) {
			$productIds = explode(',', $productId);
		} else {
			$productIds = array($productId);
		}

		$generatedInvoiceCert = array();
		$fourteenKProducts = array();
		$eighteenKProducts = array();
		foreach ($productIds as $key => $productId) {
			DB::setTablePrefix('');
			$productData = DB::table('catalog_product_flat_1')->select('approval_invoice_generated', 'certificate_no', 'metal_quality_value')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
			$metalQuality = explode(' ', $productData->metal_quality_value);
			$metalQuality = isset($metalQuality[0]) ? $metalQuality[0] : '';
			if ($metalQuality == '14K') {
				$fourteenKProducts[] = $productId;
			} else if ($metalQuality == '18K') {
				$eighteenKProducts[] = $productId;
			}
			if ($productData->approval_invoice_generated) {
				$generatedInvoiceCert[] = $productData->certificate_no;
			}
		}
		$orderDiscount = 0;

		if (count($fourteenKProducts) > 0 && count($eighteenKProducts) > 0) {
			$orderDiscount = ($discount14K + $discount18K) / 2;
		} else if (count($fourteenKProducts) > 0 && count($eighteenKProducts) == 0) {
			$orderDiscount = $discount14K;
		} else if (count($fourteenKProducts) == 0 && count($eighteenKProducts) > 0) {
			$orderDiscount = $discount18K;
		}
		if (count($generatedInvoiceCert) > 0) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.invoice_already_generated') . implode(", ", $generatedInvoiceCert);
			echo json_encode($response);exit;
		} else {

			//Check invoice number already exist
			$invoices = DB::table('sales_flat_invoice')->select(DB::raw('COUNT(1) as total_invoice'))->where('increment_id', '=', DB::raw("'$invoiceNumber'"))->get()->first();
			if ($invoices->total_invoice > 0) {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.invoice_number_already_generated');
				echo json_encode($response);exit;
			}
			if (App::environment('local')) {
				//$generateInvoiceUrl = Config::get('constants.apiurl.local.generate_invoice');
				$generateInvoiceUrl = Config::get('app.generate_invoice');
				//var_dump($generateInvoiceUrl);exit;
			} else if (App::environment('test')) {
				$generateInvoiceUrl = Config::get('constants.apiurl.test.generate_invoice');
			} else {
				$generateInvoiceUrl = Config::get('constants.apiurl.live.generate_invoice');
			}

			//var_dump($generateInvoiceUrl);exit;
			//Get price markup for customer
			$priceMarkup = CustomersHelper::getCustomerAttrValue($customerId, 'price_markup');

			$paymentData = json_encode($paymentData);
			$invoiceParams = 'productIds=' . implode(',', $productIds) . '&customerId=' . $customerId . '&paymentData=' . $paymentData . '&invoice_number=' . $invoiceNumber . '&invoice_date=' . $invoiceDate . '&invoice_memo_with_old_data=' . $invoiceMemoWithOldData . '&approval_no=' . $approval_no . '&invoice_grand_total=' . $invoiceGrandTotal . '&order_discount=' . $orderDiscount . '&fourteen_k_discount=' . $discount14K . '&eighteen_k_discount=' . $discount18K . '&price_markup=' . $priceMarkup;

			$ch = curl_init($generateInvoiceUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $invoiceParams);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$info = curl_getinfo($ch);
			$response = json_decode($result);
			$memoIds = array();

			//var_dump($response);exit;

			if (isset($response->status) && $response->status) {
				Cache::forget('all_products_ajax');
				Cache::forget('all_products');
				DB::setTablePrefix('dml_');

				$invoiceNumber = isset($response->invoice_number) ? $response->invoice_number : '';
				$invoiceId = isset($response->invoice_id) ? $response->invoice_id : 0;
				$productData = isset($response->product_data) ? json_decode($response->product_data) : '';
				$products_total = 0;
				foreach ($productData as $productKey => $invoiceProd) {

					$product_shipping = 0;
					if (isset($invoiceProd->shipping_charge)) {
						if (!empty($invoiceProd->shipping_charge)) {
							$product_shipping = $invoiceProd->shipping_charge;
						} else {
							$product_shipping = 0;
						}
					} else {
						$product_shipping = 0;
					}

					$product_discount = 0;
					if (isset($invoiceProd->product_discount)) {
						if (!empty($invoiceProd->product_discount)) {
							$product_discount = round($invoiceProd->product_discount);
						}
					}
					//var_dump($product_discount);
					$product_raw_price = round($invoiceProd->unit_price) - $product_discount;
					//var_dump($product_raw_price);
					if (isset($invoiceProd->shipping_charge)) {
						if (!empty($invoiceProd->shipping_charge)) {
							$product_final_price = $product_raw_price + round($invoiceProd->shipping_charge);
						} else {
							$product_final_price = $product_raw_price;
						}
					} else {
						$product_final_price = $product_raw_price;
					}
					//var_dump($product_final_price);
					if (!empty($invoiceProd->sgst_percentage) && !empty($invoiceProd->cgst_percentage)) {
						$gst_per = $invoiceProd->sgst_percentage + $invoiceProd->cgst_percentage;
					} else {
						$gst_per = $invoiceProd->gst_percentage;
					}
					//var_dump($gst_per);

					$products_total += $product_final_price;

					InvoiceProducts::create(array(
						'product_id' => $invoiceProd->product_id,
						'sku' => $invoiceProd->sku,
						'metal_weight' => $invoiceProd->metal_weight,
						'stone_weight' => $invoiceProd->stone_weight,
						'unit_price' => $invoiceProd->unit_price,
						'sgst_percentage' => $invoiceProd->sgst_percentage,
						'cgst_percentage' => $invoiceProd->cgst_percentage,
						'shipping_charge' => $product_shipping,
						'discount' => $product_discount,
						'grand_total' => $product_final_price,
						'invoice_id' => $invoiceId,
						'created_at' => date('Y-m-d H:i:s'),
						'updated_at' => date('Y-m-d H:i:s'),
					)
					);

				}
				//Insert into invoice_logs
				InvoiceLogs::create(
					array(
						'user_id' => Auth::user()->id,
						'invoice_id' => $invoiceId,
						'product_data' => json_encode($productData),
						'comment' => config('constants.message.invoice_created'),
						'created_at' => date('Y-m-d H:i:s'),
						'updated_at' => date('Y-m-d H:i:s'),
					)
				);

				$gstAmt = (($products_total * $gst_per) / 100);
				$invoice_final_total_for_payment = round($products_total + $gstAmt);
				//var_dump($gstAmt);
				//var_dump($invoice_final_total_for_payment);
				//exit;
				$customerName = InventoryHelper::getCustomerName($customerId);
				$paymentTypeData = PaymentType::select('id', 'parent_id')->where('name', '=', 'Sales')->first();
				$creditData = CustomerWallet::select(DB::raw("SUM(`transaction_amt`) as credit_total"))->where('customer_id', '=', DB::raw("$customerId"))->where('transaction_type', '=', DB::raw("'credit'"))->get()->first();

				$creditAmount = isset($creditData->credit_total) ? $creditData->credit_total : 0;

				$debitData = CustomerWallet::select(DB::raw("SUM(`transaction_amt`) as debit_total"))->where('customer_id', '=', DB::raw("$customerId"))->where('transaction_type', '=', DB::raw("'debit'"))->get()->first();
				$debitAmount = isset($debitData->debit_total) ? $debitData->debit_total : 0;
				$finalAmount = (float) $creditAmount - (float) $debitAmount;
				//print_r($paymentTypeData);exit;
				$paymentType = isset($paymentTypeData->id) ? $paymentTypeData->id : '';
				$paymentSubType = isset($paymentTypeData->parent_id) ? $paymentTypeData->parent_id : '';
				$createdBy = Auth::user()->id;

				//insert into payment
				$paymentData = array(
					'customer_id' => $customerId,
					'customer_name' => $customerName,
					'invoice_number' => $invoiceNumber,
					'invoice_amount' => $invoice_final_total_for_payment,
					'account_status' => 1,
					'payment_status' => 0,
					'payment_form' => 'Incoming',
					'payment_sub_type' => $paymentType,
					'payment_type' => $paymentSubType,
					'customer_type' => 'Website',
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
					'created_by' => $createdBy,
					'remarks' => 'Order Invoice',
				);

				//print_r($paymentData);exit;
				Payment::create($paymentData);
				$paymentId = DB::getPdo()->lastInsertId();
				//Insert into payment_transactions
				$paymentTransactionAmount = 0;
				$remainAmount = 0;

				if ($invoice_final_total_for_payment < $finalAmount) {
					$remainAmount = 0;
					$paymentTransactionAmount = $invoice_final_total_for_payment;
				} else {
					$remainAmount = (float) $invoice_final_total_for_payment - (float) $finalAmount;
					$paymentTransactionAmount = $finalAmount;
				}
				if ($finalAmount > 0) {
					$paymenttransaction = new PaymentTransaction;
					$paymenttransaction->payment_id = $paymentId;
					$paymenttransaction->invoice_number = $invoiceNumber;
					$paymenttransaction->invoice_attachment = NULL;
					$paymenttransaction->invoice_amount = $paymentTransactionAmount;
					$paymenttransaction->remaining_amount = $remainAmount;
					$paymenttransaction->created_by = $createdBy;
					$paymenttransaction->paid_at = date('Y-m-d');
					//$paymenttransaction->created_at = date('Y-m-d');
					$paymenttransaction->status = 'Cash Paid';
					$paymenttransaction->comment = Config::get('constants.message.inventory_new_invoice_created');
					$paymenttransaction->save();
				}

				//Insert into customer_wallet
				$walletAmount = 0;
				if ($finalAmount >= (float) $invoice_final_total_for_payment) {
					$walletAmount = (float) $invoice_final_total_for_payment;
				} else {
					$walletAmount = $finalAmount;
				}

				CustomerWallet::create(array('customer_id' => $customerId, 'transaction_amt' => $walletAmount, 'transaction_type' => 'debit', 'remarks' => Config::get('constants.message.inventory_new_invoice_created'), 'created_by' => $createdBy, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'operation_type' => 'invoice', 'ref_number' => $invoiceNumber));
				foreach ($productIds as $key => $productId) {
					$data = array('status' => 'invoice', 'date' => date('Y-m-d H:i:s'));
					ApprovalMemoHistroy::where("product_id", $productId)->update($data);
				}
			}
			return $result;
		}
	}
	public function generateMemoNew($productId, $customerId, $paymentData, $invoiceMemoWithOldData, $approvalNo, $approval_Date) {

		if (strpos($productId, ',') !== false) {
			$productIds = explode(',', $productId);
		} else {
			$productIds = array($productId);
		}
		$generatedMemoCert = array();
		$generatedInvoiceCert = array();
		foreach ($productIds as $key => $productId) {
			DB::setTablePrefix('dml_');
			$orderData = ApprovalMemoHistroy::select('id')->where('product_id', '=', DB::raw("$productId"))->where('status', '=', DB::raw("'approval'"))->get();
			if ($orderData->count() > 0) {
				DB::setTablePrefix('');
				$productData = DB::table('catalog_product_flat_1')->select('approval_memo_generated', 'certificate_no')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
				if ($productData->approval_memo_generated) {
					$generatedMemoCert[] = $productData->certificate_no;
				}

			}
			DB::setTablePrefix('');
			$productData = DB::table('catalog_product_flat_1')->select('approval_invoice_generated', 'certificate_no')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
			DB::setTablePrefix('dml_');
			if (!empty($productData)) {
				if ($productData->approval_invoice_generated) {
					$generatedInvoiceCert[] = $productData->certificate_no;
				}
			}

		}
		//Check invoice already generated for product
		if (count($generatedInvoiceCert) > 0) {
			$response['status'] = 0;
			$response['message'] = Config::get('constants.message.invoice_already_generated') . implode(", ", $generatedInvoiceCert);
			echo json_encode($response);exit;
		}
		if (count($generatedMemoCert) > 0) {
			$response['status'] = 0;
			$response['message'] = Config::get('constants.message.memo_already_generated') . implode(", ", $generatedMemoCert);
			echo json_encode($response);exit;
		} else {
			DB::setTablePrefix('dml_');

			$approvalDate = date('Y-m-d H:i:s');
			$isForOldData = 'no';
			if (!empty($invoiceMemoWithOldData)) {
				$approvalNumber = $approvalNo;
				$approvalDate = date('Y-m-d H:i:s', strtotime($approval_Date));
				$isForOldData = 'yes';

				$lastData = ApprovalMemo::select('approval_no')->where('approval_no', '=', DB::raw("'$approvalNumber'"))->get();
				if ($lastData->count() > 0) {
					$response['status'] = false;
					$response['message'] = Config::get('constants.message.inventory_approval_number_already_exist');
					echo json_encode($response);exit;
				}
			}

			if (!empty($isForOldData) && $isForOldData == 'no') {
				$approvalNumber = 0;
			}

			$data = array(
				'customer_id' => $customerId,
				'approval_no' => $approvalNumber,
				'product_ids' => implode(',', $productIds),
				'franchisee_id' => isset($paymentData['franchise_id']) ? $paymentData['franchise_id'] : null,
				'agent_name' => isset($paymentData['agent_name']) ? $paymentData['agent_name'] : '',
				'approval_type' => isset($paymentData['approval_type']) ? $paymentData['approval_type'] : '',
				'deposit_type' => isset($paymentData['deposit_type']) ? $paymentData['deposit_type'] : '',
				'status' => 'pending',
				'created_at' => $approvalDate,
				'updated_at' => $approvalDate,
				'is_for_old_data' => $isForOldData,
			);
			if (empty($data['franchisee_id'])) {
				$data['franchisee_id'] = null;
			}

			//var_dump($data);exit;
			ApprovalMemo::create($data);
			$approvalId = DB::getPdo()->lastInsertId();
			if (!empty($approvalId)) {
				//$approvalId = DB::select("select id from dml_approval_memo order by created_at desc limit 1");
				//print_r($approvalId);exit;
				//$approvalMemoId = isset($approvalId[0]->id) ? $approvalId[0]->id : '0';
				$response['status'] = true;
				$response['message'] = Config::get('constants.message.inventory_memo_generated_success');
				$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
				$outStatusVal = isset($inventoryStatus['out']) ? $inventoryStatus['out'] : '';
				//print_r($productIds);exit;
				foreach ($productIds as $key => $productId) {
					$approvalHistroyData = array(
						'approval_no' => $approvalNumber,
						'product_id' => $productId,
						'status' => 'approval',
						'date' => date('Y-m-d H:i:s'),
						'approval_memo_id' => $approvalId,
					);
					//print_r($approvalHistroyData);exit;
					ApprovalMemoHistroy::create($approvalHistroyData);
					$query = "UPDATE catalog_product_flat_1 SET `approval_memo_generated` = '1',`return_memo_generated` = '0',`approval_invoice_generated`='0',is_sold=0 WHERE entity_id=" . DB::raw("$productId");
					//echo $query;exit;
					DB::statement($query);
					InventoryHelper::changeInventoryStatus($productId, $outStatusVal);
				}
				Cache::forget('all_products_ajax');
				Cache::forget('all_products');
			} else {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_memo_generated_failure');
			}
		}
		echo json_encode($response);exit;
	}
	//Generate Memo
	public function generateMemo($productId, $customerId, $paymentData, $invoiceMemoWithOldData, $approvalNumber, $approvalDate) {

		if (strpos($productId, ',') !== false) {
			$productIds = explode(',', $productId);
		} else {
			$productIds = array($productId);
		}
		//print_r($productIds);exit;
		$generatedMemoCert = array();
		foreach ($productIds as $key => $productId) {
			$orderData = DB::select("SELECT `main_table`.*, `od`.`status` FROM `sales_flat_order_item` AS `main_table` INNER JOIN `sales_flat_order` AS `od` ON od.entity_id = main_table.order_id WHERE (product_id = '" . $productId . "') AND (od.status = 'pending')");
			$orderData = isset($orderData[0]) ? $orderData[0] : array();
			if (count($orderData) > 0) {
				$productData = DB::table('catalog_product_flat_1')->select('approval_memo_generated', 'certificate_no')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
				if ($productData->approval_memo_generated) {
					$generatedMemoCert[] = $productData->certificate_no;
				}
			}
		}
		if (count($generatedMemoCert) > 0) {
			$response['status'] = 0;
			$response['message'] = Config::get('constants.message.memo_already_generated') . implode(", ", $generatedMemoCert);
			echo json_encode($response);exit;
		} else {
			if (App::environment('local')) {
				$generateMemoUrl = Config::get('constants.apiurl.local.generate_memo');
			} else if (App::environment('test')) {
				$generateMemoUrl = Config::get('constants.apiurl.test.generate_memo');
			} else {
				$generateMemoUrl = Config::get('constants.apiurl.live.generate_memo');
			}

			$paymentData = json_encode($paymentData);
			$memoParams = 'productIds=' . implode(',', $productIds) . '&customerId=' . $customerId . '&paymentData=' . $paymentData . '&approval_number=' . $approvalNumber . '&approval_date=' . $approvalDate . '&invoice_memo_with_old_data=' . $invoiceMemoWithOldData;
			//echo $generateMemoUrl;exit;
			$ch = curl_init($generateMemoUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $memoParams);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			//print_r($result);exit;
			$info = curl_getinfo($ch);
			Cache::forget('all_products_ajax');
			Cache::forget('all_products');
			return $result;
		}
	}
	//Generate return memo
	public function generateReturnMemo(Request $request) {
		$params = $request->post();
		$productIds = isset($params['productIds']) ? $params['productIds'] : '';
		$isFromMemoList = isset($params['is_from_memo_list']) ? $params['is_from_memo_list'] : '';

		$approvalIds = isset($params['memo_id']) ? $params['memo_id'] : array();

		$approvalFetchedIds = array();
		if (!empty($approvalIds)) {
			if (strpos($approvalIds, ',') !== false) {
				$approvalIds = explode(',', $approvalIds);
			} else {
				$approvalIds = array($approvalIds);
			}
		}

		if (strpos($productIds, ',') !== false) {
			$productIds = explode(',', $productIds);
		} else {
			$productIds = array($productIds);
		}
		$productIds = array_unique($productIds);
		$productIds = array_filter($productIds);
		$productIdArray = array_unique($productIds);

		if (empty($productIds)) {
			//$productIdsArr = array();
			foreach ($approvalIds as $memoId) {
				$productIdsData = ApprovalMemoHistroy::select('product_id')->where('approval_memo_id', '=', DB::raw("$memoId"))->get();
				foreach ($productIdsData as $productId) {
					$productIds[] = $productId->product_id;
				}
			}
		}
		if (!empty($productIds)) {
			///$productIds = implode("','",$productIds);
			DB::setTablePrefix('');
			//print_r($productIds);exit;
			$generatedReturnMemoCert = array();
			foreach ($productIds as $key => $productId) {
				DB::setTablePrefix('');
				$productData = DB::table('catalog_product_flat_1')->select('return_memo_generated', 'certificate_no')->where('entity_id', '=', DB::raw("$productId"))->get()->first();

				if ($productData->return_memo_generated) {
					$generatedReturnMemoCert[] = $productData->certificate_no;
				}
			}
			if (count($generatedReturnMemoCert) > 0) {
				$response['status'] = false;
				$response['message'] = config('constants.message.return_memo_already_generated_for_products') . implode(",", $generatedReturnMemoCert);
				echo json_encode($response);exit;
			}
			//Check approval number is generated
			$implodedProductIds = implode("','", $productIds);

			$approvals = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.approval_no', '=', DB::raw("0"))->whereIn('memo_histroy.product_id', [DB::raw("'" . $implodedProductIds . "'")])->where('memo.is_for_old_data', '=', DB::raw("'no'"))->get();
			$approvalNumberNotGenerated = array();
			if ($approvals->count() > 0) {
				foreach ($approvals as $key => $approval) {
					DB::setTablePrefix('');
					$product = DB::table('catalog_product_flat_1')->select('certificate_no')->where('entity_id', '=', DB::raw("$approval->product_id"))->get()->first();
					$approvalNumberNotGenerated[] = isset($product->certificate_no) ? $product->certificate_no : '';
				}
			}
			DB::setTablePrefix('dml_');
			if (count($approvalNumberNotGenerated) > 0) {
				$approvalNumberNotGenerated = array_unique($approvalNumberNotGenerated);
				$response['status'] = false;
				if (!empty($isFromMemoList) && $isFromMemoList) {
					$response['message'] = config('constants.message.inventory_approval_number_not_generated_for_memo');
				} else {
					$response['message'] = config('constants.message.inventory_approval_number_not_generated') . implode(',', $approvalNumberNotGenerated);
				}
				echo json_encode($response);exit;
			}

			$franchiseeName = array();
			$orderCustomerFranchiseName = array();
			$orderCustomerID = array();
			$memoNumber = array();
			$totQty = 0;
			$totDiamondWeight = 0;
			$totalMetalWeight = 0;
			$totalGrandTotalPrice = 0;
			$finalGrandTotalPrice = '';
			$returnMemoData = array();
			$index = 0;
			$memoNotGeneratedCertificate = array();
			$memoids = implode("','", $approvalIds);
			//print_r($productIds);exit;
			foreach ($productIds as $key => $productId) {
				DB::setTablePrefix('');

				$memoData = DB::table('dml_approval_memo_histroy as histroy')->select('id', 'approval_memo_id')->where('product_id', '=', DB::raw("$productId"))->where('status', '=', DB::raw("'approval'"))->get()->first();
				if (empty($memoData)) {
					continue;
				}

				/* if (count($memoData) > 1) {
					$product = DB::table('catalog_product_flat_1')->select('certificate_no')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
					$memoNotGeneratedCertificate[] = $product->certificate_no;
				} */
				if (count($approvalIds) == 0) {
					$approvalFetchedIds[] = $memoData->approval_memo_id;
				}
			}
			if (count($memoNotGeneratedCertificate) > 0) {
				$memoNotGeneratedCertificate = array_unique($memoNotGeneratedCertificate);
				$response['status'] = false;
				$response['message'] = config('constants.message.inventory_memo_not_generated') . implode(',', $memoNotGeneratedCertificate);
				echo json_encode($response);exit;
			}

			if (count($approvalIds) == 0) {
				$approvalIds = array_unique($approvalFetchedIds);
			}

			//var_dump($approvalIds);exit;

			foreach ($approvalIds as $approvalId) {
				foreach ($productIds as $key => $productId) {
					DB::setTablePrefix('dml_');
					$memoData = DB::table('approval_memo_histroy as histroy')->select('histroy.approval_memo_id', 'memo.customer_id', 'memo.franchisee_id', 'memo.id', 'memo.approval_no', 'memo.is_for_old_data')->join('approval_memo as memo', 'memo.id', '=', 'histroy.approval_memo_id')->where('product_id', '=', DB::raw("$productId"))->where('histroy.approval_memo_id', '=', DB::raw("$approvalId"))->get()->first();
					if (count($memoData) == 0) {
						continue;
					}

					$orderCustomerID[$index] = isset($memoData->customer_id) ? $memoData->customer_id : '';
					if (isset($memoData->is_for_old_data) && $memoData->is_for_old_data == 'no') {
						$currentYear = date('y');
						if (date('m') > 6) {
							$fin_year = date('y') . '-' . (date('y') + 1);
						} else {
							$fin_year = (date('y') - 1) . '-' . date('y');
						}
						$memoNumber[$approvalId] = $fin_year . '/' . $memoData->approval_no;
					} else {
						$memoNumber[$approvalId] = isset($memoData->approval_no) ? $memoData->approval_no : '';
					}
					$franchiseeName[$index] = isset($memoData->customer_id) ? InventoryHelper::getCustomerName($memoData->customer_id) : '';
					$orderCustomerFranchiseName[$index] = isset($memoData->franchisee_id) ? $memoData->franchisee_id : '';
					$defaultBillingAddress = InventoryHelper::getDefaultBillingAddressByCustId($memoData->customer_id);
					$orderdefaultBilling[$index] = isset($defaultBillingAddress['entity_id']) ? $defaultBillingAddress['entity_id'] : '';
					$orderFranchiseID[$index] = $memoData->customer_id;
					DB::setTablePrefix('');
					$product = DB::table("catalog_product_flat_1")->select("*")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
					if (empty($product)) {
						continue;
					}

					$attributeSetId = $product->attribute_set_id;
					$attributeSetData = DB::table("eav_attribute_set")->select("attribute_set_name")->where("attribute_set_id", "=", DB::raw("$attributeSetId"))->get()->first();
					$attributeSetName = isset($attributeSetData->attribute_set_name) ? $attributeSetData->attribute_set_name : '';
					$metalQuality = $product->metal_quality;
					$productMetalList = DB::table('grp_metal_quality')->select('metal_quality', 'weight')->where('grp_metal_quality_id', '=', DB::raw("$product->metal_quality"))->get()->first();
					$productMetalQuality = isset($productMetalList->metal_quality) ? $productMetalList->metal_quality : '';
					$productQty = 1;
					$price = isset($product->custom_price) ? $product->custom_price : 0;
					$stone = isset($product->rts_stone_quality) ? $product->rts_stone_quality : '';
					$stoneData = InventoryHelper::getStoneData($productId);
					$productWithOption = array();
					$stoneWeight = array();

					foreach ($stoneData as $key => $stone) {
						if ($key == "type" || $key == "shape" || $key == "setting" || $key == "stone_use" || $key == "totalcts" || $key == "percts" || $key == "stone_price" || $key == "stoneclarity") {
							$sizeof = count($stone);
							$productWithOption[0][$key] = $stoneData[$key][0];
							for ($st = 0; $st < $sizeof; $st++) {
								$productWithOption[$st][$key] = $stoneData[$key][$st];
							}
						}
					}

					foreach ($productWithOption as $optionData) {
						$stoneWeight[] = round((float) $optionData['totalcts'] * (float) $optionData['stone_use'], 2);
					}

					$metalData = (array) InventoryHelper::getMetalData($productId);

					//print_r($metalData);exit;
					$gemStoneData = InventoryHelper::getGemStoneData($productId);
					//$diamondWeight = isset($stoneData['totalweight'][0]) ? $stoneData['totalweight'][0]: 0;
					$diamondWeight = array_sum($stoneWeight);
					/* echo "<pre>";
						print_r($productMetalList);exit;
					*/
					$metalWeight = isset($metalData['weight']) ? $metalData['weight'] : 0;
					$totQty += $productQty;
					$totDiamondWeight += $diamondWeight;
					$totalMetalWeight += $metalWeight;
					$totalGrandTotalPrice += $price;
					$finalGrandTotalPrice = ShowroomHelper::currencyFormat(round($totalGrandTotalPrice));

					$returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['productid'] = $productId;
					$returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['product_types'] = $attributeSetName;

					$metalquality = isset($returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['kt']) ? $returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['kt'] : "";
					$returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['kt'] = $productMetalQuality . "/" . $metalquality;

					$qty = isset($returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['qty']) ? (int) $returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['qty'] : 0;
					$returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['qty'] = $qty + round($productQty, 0);

					//$diamondweight =
					$returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['diamond_weight'] = floatval($diamondWeight) + floatval(isset($returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['diamond_weight']) ? $returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['diamond_weight'] : 0);

					//$metalweight = isset($returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['metal_weight']) ? $returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['metal_weight'] : 0;
					$returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['metal_weight'] = floatval($metalWeight) + floatval(isset($returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['metal_weight']) ? $returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['metal_weight'] : 0);

					$priceData = isset($returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['price']) ? (float) $returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['price'] : 0;
					$returnMemoData[$orderFranchiseID[$index]][$approvalId][$index]['price'] = $priceData + (float) $price;

					$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
					$inStatusVal = $inventoryStatus['in'];
					InventoryHelper::changeInventoryStatus($productId, $inStatusVal);
					//DB::statement("UPDATE catalog_product_flat_1 SET `approval_invoice_generated` = '0',`approval_memo_generated` = '0' WHERE entity_id=" . DB::raw("$productId"));

					$index++;
				}
			}
		}
		$arrayIndex = 0;
		$franchiseid = '';
		$productIds = array();
		$franchiseID = '';
		$returnMemoIds = array();
		$orderFranchiseID = array_unique($orderFranchiseID);
		//echo "<pre>";
		//print_r($orderCustomerID);exit;
		/*foreach ($orderFranchiseID as $key => $value) {
			$productidsArray = array();
			if ($franchiseID != $value) {

				foreach ($returnMemoData[$value] as $finalKey => $product) {
					foreach ($product as $key => $productData) {
						$productidsArray[] = $productData['productid'];
					}
				}
				$productIds[$value] = implode(',', $productidsArray);
			}
			$franchiseID = $value;
		}*/

		$insertData = array();
		$productidsArray = array();
		foreach ($orderFranchiseID as $key => $value) {
			if (empty($value)) {
				continue;
			}
			$returnMemo = array();
			$returnMemo['customer_id'] = $orderCustomerID[$arrayIndex];
			$returnMemo['franchise_id'] = $value;
			$returnMemo['franchise_name'] = InventoryHelper::getCustomerName($value);
			$returnMemo['franchise_address'] = $orderdefaultBilling[$arrayIndex];
			$returnMemoProductData = array();
			$returnCounter = 0;

			foreach ($returnMemoData[$value] as $finalKey => $memodataRow) {
				//echo $finalKey;exit;
				//print_r($memodataRow);exit;
				$productIds = array();
				$returnMemo = array();
				$grandTotalData = array();
				$insertData = array();
				$productData = array();
				$returnMemoProductData[$returnCounter] = $memodataRow;
				foreach ($memodataRow as $productKey => $memodata) {

					$productIds[] = $memodata['productid'];
					$productidsArray[] = $memodata['productid'];
					$qty = isset($grandTotalData['qty']) ? (int) $grandTotalData['qty'] : 0;
					$grandTotalData['qty'] = $qty + $memodata['qty'];
					$grandTotalData['diamond_weight'] = floatval($memodata['diamond_weight']) + floatval(isset($grandTotalData['diamond_weight']) ? $grandTotalData['diamond_weight'] : 0);
					$grandTotalData['metal_weight'] = floatval($memodata['metal_weight']) + floatval(isset($grandTotalData['metal_weight']) ? $grandTotalData['metal_weight'] : 0);
					$priceData = isset($grandTotalData['price']) ? (float) $grandTotalData['price'] : 0;
					$grandTotalData['price'] = $priceData + (float) $memodata['price'];
					$productData[] = $memodata;
					$returnMemo[$finalKey]['product_data'][] = $memodata;
					$returnMemo[$finalKey]['grand_total_data'] = json_encode($grandTotalData);
				}

				$lastData = ReturnMemo::orderBy('created_at', 'desc')->select('return_number')->get()->first();
				$returnMemoNumber = '';
				$returnMemoNumberConfigData = Setting::where('key', config('constants.settings.keys.return_memo_number'))->first('value');
				if (empty($lastData->return_number)) {
					$returnMemoNumberConfigData = Setting::where('key', config('constants.settings.keys.return_memo_number'))->first('value');
					$returnMemoNumber = isset($returnMemoNumberConfigData->value) ? $returnMemoNumberConfigData->value : '';
				} else {
					$returnMemoNumber = isset($lastData->return_number) ? $lastData->return_number + 1 : '';
				}
				$insertData = array(
					'customer_id' => $orderCustomerID[$arrayIndex],
					'franchise_id' => $value,
					'franchise_name' => $franchiseeName[$key],
					'franchise_address' => !empty($orderdefaultBilling[$arrayIndex]) ? $orderdefaultBilling[$arrayIndex] : null,
					'product_data' => json_encode($returnMemo[$finalKey]['product_data']),
					'product_ids' => implode(',', $productIds),
					'grand_total_data' => $returnMemo[$finalKey]['grand_total_data'],
					'return_number' => $returnMemoNumber,
					'approval_memo_number' => $memoNumber[$finalKey],
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				);
				//print_r($insertData);exit;
				ReturnMemo::create($insertData);
				$returnMemoId = DB::getPdo()->lastInsertId();
				$returnMemoIds[] = DB::getPdo()->lastInsertId();
				//Insert into return_memo_products
				foreach ($productIds as $key => $id) {
					ReturnMemoProducts::create(array('product_id' => $id, 'return_memo_id' => $returnMemoId, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));
				}

				$arrayIndex++;
				$returnCounter++;
			}
		}
		if (!empty($returnMemoIds)) {
			foreach ($productIdArray as $key => $productId) {
				$result = DB::statement("UPDATE `dml_approval_memo_histroy` set `status`='return_memo', `date`='" . date('Y-m-d H:i:s') . "' WHERE product_id=" . $productId);
				$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
				$inStatusVal = $inventoryStatus['in'];
				$result = InventoryHelper::changeInventoryStatus($productId, $inStatusVal);
				$result = DB::statement("UPDATE catalog_product_flat_1 SET `return_memo_generated` = '1',`inventory_status`='" . $inStatusVal . "',inventory_status_value='In',is_sold=0  WHERE entity_id=" . $productId);

			}
			$response['status'] = true;
			$response['message'] = config('constants.message.inventory_return_memo_generated_success');

			Cache::forget('all_products_ajax');
			Cache::forget('all_products');
		} else {
			$response['status'] = false;
			$response['message'] = '';
		}
		echo json_encode($response);exit;
	}
	//Generate quotation
	public function generateQuotation(Request $request) {
		$params = $request->post();
		$productIds = Session::get('quotation_product_ids'); //Get product ids which is selected from inventory page

		$diamonds = array();
		$diamondShapeData = array();
		$diamondShapeData['round'] = array();
		$diamondShapeData['fancy2'] = array();
		$diamondShapeData['fancy1'] = array();
		$roundDiamonds = array();
		$fancy1Diamonds = array();
		$fancy2Diamonds = array();
		$roundShape = config('constants.enum.diamond_shape.round'); //to check diamond shape
		$fancy2Shape = config('constants.enum.diamond_shape.fancy2'); //to check diamond shape
		///$stoneShape = ShowroomHelper::getDiamondShape();

		$qualityArray = array();
		$shapeArray = array();
		DB::setTablePrefix('');
		if (!empty($productIds)) {
			$productIds = explode(',', $productIds);
			foreach ($productIds as $key => $productId) {
				//get product info
				DB::setTablePrefix('');
				$productData = DB::table("catalog_product_flat_1")->select("rts_stone_quality", "certificate_no")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
				DB::setTablePrefix('dml_');
				//$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->select('id', 'rts_stone_quality', 'certificate_no')->where('certificate_no', '=', DB::raw("'$productData->certificate_no'"))->get()->first();
				$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->select('id', 'rts_stone_quality', 'certificate_no')->join('products_stone as prd_stone', 'prd_stone.stone_product_id', '=', 'products.id')->where('certificate_no', '=', DB::raw("'$productData->certificate_no'"))->get()->first();
				//print_r($dmlProductData);exit;
				//echo $dmlProductData;exit;
				DB::setTablePrefix('');
				$stone = isset($productData->rts_stone_quality) ? $productData->rts_stone_quality : '';
				//Get stone detail
				$stoneData = InventoryHelper::getStoneData($productId);
				$diamondIndex = 0;
				//to differentiate stone shape
				for ($row = 0; $row < sizeof($stoneData['type']); $row++) {
					$diamondShape = strtolower($stoneData['shape'][$row]);
					$diamondQuality = $stoneData['stoneclarity'][$row];
					if (!in_array($diamondQuality, $qualityArray) || !in_array($diamondShape, $shapeArray)) {
						if (in_array($diamondShape, $roundShape)) {
							$diamondShapeData['round'][$key][$diamondIndex]['product_id'] = $productId;
							$diamondShapeData['round'][$key][$diamondIndex]['stone_quality'] = $diamondQuality;
							$diamondShapeData['round'][$key][$diamondIndex]['diamondShape'] = $diamondShape;
							$qualityArray[] = $diamondQuality;
							$shapeArray[] = $diamondShape;
						} else if (in_array($diamondShape, $fancy2Shape)) {
							$diamondShapeData['fancy2'][$key][$diamondIndex]['product_id'] = $productId;
							$diamondShapeData['fancy2'][$key][$diamondIndex]['stone_quality'] = $diamondQuality;
							$diamondShapeData['fancy2'][$key][$diamondIndex]['diamondShape'] = $diamondShape;
							$qualityArray[] = $diamondQuality;
							$shapeArray[] = $diamondShape;
						} else {
							$diamondShapeData['fancy1'][$key][$diamondIndex]['product_id'] = $productId;
							$diamondShapeData['fancy1'][$key][$diamondIndex]['stone_quality'] = $diamondQuality;
							$diamondShapeData['fancy1'][$key][$diamondIndex]['diamondShape'] = $diamondShape;
							$qualityArray[] = $diamondQuality;
							$shapeArray[] = $diamondShape;
						}
					}
					$diamondIndex++;
				}
			}

			//Get stone range

			//Get state list by cuontry id
			$get_country_list = '';
			if (App::environment('local')) {
				$get_country_list = Config::get('constants.apiurl.local.get_country_list');
			} else if (App::environment('test')) {
				$get_country_list = Config::get('constants.apiurl.test.get_country_list');
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
			/*echo "<pre>";
			print_r($countryListArr);exit;*/

			DB::setTablePrefix('dml_');
			$shapesWeGot = array();
			if (count($diamondShapeData['round']) > 0 && count($dmlProductData) > 0) {
				$shapesWeGot[] = 'round';
			}
			if (count($diamondShapeData['round']) > 0 && count($dmlProductData) < 1) {
				$shapesWeGot[] = 'round_withoutmm';
			}
			if (count($diamondShapeData['fancy1']) > 0) {
				$shapesWeGot[] = 'fancy1';
			}
			if (count($diamondShapeData['fancy2']) > 0) {
				$shapesWeGot[] = 'fancy2';
			}

			$stoneRangeData = InventoryHelper::getStoneRangeData($shapesWeGot);
			//print_r($countryList);exit;
			return view('inventory.quotation')->with(array('productIds' => implode(',', $productIds), 'countryList' => $countryListArr, 'diamondShapeData' => $diamondShapeData, 'stonerangedata' => $stoneRangeData, 'totalProducts' => count($productIds)));
		}

		//print_r($params['productIds']);exit;
	}
	//Store quotation data into database
	public function store(Request $request) {
		$params = $request->post();
		//print_r($params);exit;
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$isDefaultQuotation = isset($params['chkDefaultQuotation']) ? $params['chkDefaultQuotation'] : '';
		$stoneData = isset($params['stone_data']) ? $params['stone_data'] : '';
		$productIds = isset($params['product_ids']) ? explode(",", $params['product_ids']) : '';
		$stoneShape = array();
		$stoneRange = array();
		$stonePrice = array();
		$productStonePrice = array();
		$metalRate = array();
		$stoneTotalCarat = array();
		$stone_shape = '';
		$stoneTotalPrice = 0;
		$totalProductPrice = 0;
		$totalMetalWeight = 0;
		$totalStoneCaret = 0;
		$productDataArr = array();
		$quotationDataArr = array();
		DB::setTablePrefix('');
		$product_id = array();
		$stone_data_array = array();
		$roundCount = 0;
		$fancy2Count = 0;
		$fancy1Count = 0;
		$labourChargeValue = array();
		$stone_clarity = InventoryHelper::getDefaultDiamondQuality();
		$roundShape = config('constants.enum.diamond_shape.round');
		$fancy2Shape = config('constants.enum.diamond_shape.fancy2');
		$stonedataarr = array();
		$sideStoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
		$stoneShapeData = array();
		foreach ($sideStoneShapeDetails as $key => $stoneShape) {
			$stoneShapeData[$stoneShape->option_id] = isset($stoneShape->value) ? strtolower($stoneShape->value) : '';
		}
		$priceMarkup = CustomersHelper::getCustomerAttrValue($customerId, 'price_markup');
		if (empty(trim($priceMarkup))) {
			$priceMarkup = 0;
		}

		if (!empty($productIds)) {
			//To check labour charge priority
			foreach ($productIds as $index => $productId) {
				DB::setTablePrefix('');
				$productData = DB::table('catalog_product_flat_1')->select('rts_stone_quality', 'rts_ring_size', 'belt_price')->where("entity_id", "=", DB::raw("$productId"))->get()->first(); // , 'extra_price'
				$stoneClarity = !empty($productData->rts_stone_quality) ? $productData->rts_stone_quality : $stone_clarity;
				$sideStoneData = InventoryHelper::getStoneData($productId);
				//For labour charge priority
				for ($row = 0; $row < sizeof($sideStoneData['type']); $row++) {
					$diamondShape = strtolower($sideStoneData['shape'][$row]);
					$diamondQuality = $sideStoneData['stoneclarity'][$row];
					if (in_array($diamondShape, $roundShape)) {
						$roundCount++;
					}
					if (in_array($diamondShape, $fancy2Shape)) {
						$fancy2Count++;
					}
					if (!in_array($diamondShape, $fancy2Shape) && !in_array($diamondShape, $roundShape)) {
						$fancy1Count++;
					}
				}
			}

			foreach ($productIds as $index => $productId) {

				//var_dump($productId);
				//exit;

				$metal_data_array = array();
				$labour_charge_data_array = array();
				$stone_data_array = array();
				$stone__shape = '';
				//get stone info. ex, total caret,price,stone use
				$shapeSideStoneData = DB::table(DB::raw("grp_stone"))->where("stone_product_id", "=", DB::raw("$productId"))->get();

				//var_dump($shapeSideStoneData);

				$productData = DB::table(DB::raw('catalog_product_flat_1'))->select('certificate_no', 'rts_stone_quality', 'rts_ring_size', 'belt_price')->where("entity_id", "=", DB::raw("$productId"))->get()->first(); //, 'extra_price'
				//Get default stone quality if product don't have any quality

				//var_dump($productData->certificate_no);

				$dmlproductData = DB::table(DB::raw('dml_products'))->select('id')->where("certificate_no", $productData->certificate_no)->get()->first();

				//var_dump($dmlproductData);exit;
				if (!empty($dmlproductData)) {
					$shapeMmData = DB::table(DB::raw("dml_products_stone"))->where("stone_product_id", "=", DB::raw("$dmlproductData->id"))->get();
					$shapeSideStoneData = $shapeMmData;
				}

				//var_dump($shapeMmData);exit;

				$stoneClarity = !empty($productData->rts_stone_quality) ? $productData->rts_stone_quality : $stone_clarity;
				$stoneShapeIndex = 0;
				$stoneClarityIndex = 0;
				$stone_price = 0;
				$productSideStonePrice = 0;
				$rangePriceData = array();
				$total_stone_caret = array();

				//print_r($shapeSideStoneData);
				//exit;

				foreach ($stoneData[trim($stoneClarity)] as $key => $stone) {
					foreach ($shapeSideStoneData as $shapeKey => $sideStone) {

						$stone__shape = isset($sideStone->stone_shape) ? $sideStone->stone_shape : '';
						$stoneCarat = isset($sideStone->total_carat) ? $sideStone->total_carat : '';
						if ($sideStone->stone_shape == '36' && !empty($dmlproductData) && !empty($shapeMmData)) {
							//calculate stone caret for range price
							//echo "<br>___________nnn______________<br>";
							$stone_mm_size = $shapeMmData[$shapeKey]->mm_size;
							$stone_mm_size_formatted = preg_replace("/[^0-9.]/", "", $stone_mm_size);
							$stoneTotalCarat = number_format(((float) $stone_mm_size_formatted), 3);
							$total_stone_caret = (float) $shapeMmData[$shapeKey]->carat;

						} else {
							//$stone_mm_size = $shapeMmData[$shapeKey]->mm_size;
							$stoneTotalCarat = number_format(((float) $stoneCarat / (float) $sideStone->stone_use), 3);
							$total_stone_caret = (float) $stoneCarat;

						}
						//var_dump($shapeSideStoneData);exit;
						//echo "<br>";
						//exit;
						//var_dump($stone);
						$price = array();
						foreach ($stone['stone_range'] as $rangeKey => $rangeVal) {
							$range = explode('-', $rangeVal);
							$stoneindex = 0;

							if ($stoneTotalCarat >= (float) $range[0] && $stoneTotalCarat <= (float) $range[1]) {
								$stoneindex = $rangeKey;
								/*echo "<pre>";
								echo $stoneShapeData[$stone__shape] . "<br><br>";*/
								if (isset($stoneData[trim($stoneClarity)][$stoneShapeData[$stone__shape]])) {
									$price = $stoneData[trim($stoneClarity)][$stoneShapeData[$stone__shape]]['stone_price'][$stoneindex];
									//var_dump($price);exit;
									$rangePriceData[$shapeKey]['stone_shape'] = strtolower($stoneShapeData[$sideStone->stone_shape]);
									$rangePriceData[$shapeKey]['stone_clarity'] = $stoneClarity;
									$rangePriceData[$shapeKey]['total_stone_caret'] = $total_stone_caret;
									$rangePriceData[$shapeKey]['range_total_carat'] = $total_stone_caret;
									$rangePriceData[$shapeKey]['stone_price'] = $stoneData[$stoneClarity][$stoneShapeData[$stone__shape]]['stone_price'][$stoneindex];
									$rangePriceData[$shapeKey]['stone_use'] = $sideStone->stone_use;
									$rangePriceData[$shapeKey]['final_stone_price'] = ((float) $price * (float) $total_stone_caret);

									//Calculate Product Metal Rate
									$metalData = ShowroomHelper::getMetalData($productId, $productData);
									$metalRate[][$productId] = isset($metalData['metalprice_value']) ? $metalData['metalprice_value'] : '';
									//Calculate labour charge

									//$sideStoneData = ShowroomHelper::getSideStoneData($productId, $stoneClarity);
									$diamondIndex = 0;
									$qualityArray = array();

									$labourChargeValue = '';
									//For labour charge priority
									if ($fancy2Count > 0) {
										$labourChargeValue = isset($params['txtlabourcharge']['fancy2'][0]) ? $params['txtlabourcharge']['fancy2'][0] : 1;
									} else if ($fancy1Count > 0) {
										$labourChargeValue = isset($params['txtlabourcharge']['fancy1'][0]) ? $params['txtlabourcharge']['fancy1'][0] : 1;
									} else {
										$labourChargeValue = isset($params['txtlabourcharge']['round'][0]) ? $params['txtlabourcharge']['round'][0] : 1;
									}
									//echo $fancy2Count."  ".$fancy1Count."  ".$roundCount;exit;
									//echo $labourChargeValue;exit;
									$labourCharge = round($metalData['weight'] * $labourChargeValue);
									//$totalStoneCaret += isset($sideStone->total_carat) ? $sideStone->total_carat : '';
									$totalStoneCaret += (float) $total_stone_caret;
									//Get gem stone detail
									$gemStoneData = InventoryHelper::getGemStoneData($productId);
									$gemStone = isset($gemStoneData['simple']) ? round($gemStoneData['simple']) : 0;

									//Stone into array for insert into DB
									$labour_charge_data_array[$productId] = array(
										'metal_weight' => isset($metalData['weight']) ? $metalData['weight'] : '',
										'label_charge' => $labourChargeValue,
										'final_labour_charge' => $labourCharge,
									);

									$metal_data_array[$productId] = array(
										'metal_weight' => isset($metalData['weight']) ? $metalData['weight'] : '',
										'per_gm_rate' => isset($metalData['per-gm-rate']) ? $metalData['per-gm-rate'] : '',
										'final_metal_rate' => isset($metalData['metalprice_value']) ? $metalData['metalprice_value'] : '',
									);
									$metalPriceValue = !empty($metalData['metalprice_value']) ? round($metalData['metalprice_value']) : 0;
								}
							}
						}
					}

					//Total quotation amount
					$stoneTotalValue = array_sum($productStonePrice);
					$product_id[] = $productId;
					$stoneShapeIndex++;
					$stoneClarityIndex++;
				}
				$productDataArr[] = array(
					'product_id' => $productId,
					'labour_charge_data' => $labour_charge_data_array,
					'metal_rate_data' => $metal_data_array,
					'stone_data' => $rangePriceData,
					'price_markup' => $priceMarkup,
				);
			} //end productIds loop
		}
		//echo "<pre>";
		//print_r($productDataArr);exit;
		$total_amount = 0;
		$metal_rate = 0;
		$stonePrice = 0;
		$gemStone = 0;
		$labout_charge = 0;
		//For insert quotation options data
		foreach ($productDataArr as $key => $productData) {
			$totalMetalWeight += isset($productData['metal_rate_data'][$productData['product_id']]['metal_weight']) ? $productData['metal_rate_data'][$productData['product_id']]['metal_weight'] : 0;

			//Get gem stone data
			$gemStoneData = InventoryHelper::getGemStoneData($productData['product_id']);

			$gemStone += isset($gemStoneData['simple']) ? round($gemStoneData['simple']) : 0;

			$labout_charge += isset($productData['labour_charge_data'][$productData['product_id']]['final_labour_charge']) ? $productData['labour_charge_data'][$productData['product_id']]['final_labour_charge'] : 0;

			$metal_rate += isset($productData['metal_rate_data'][$productData['product_id']]['final_metal_rate']) ? $productData['metal_rate_data'][$productData['product_id']]['final_metal_rate'] : 0;
			foreach ($productData['stone_data'] as $key => $stone_data) {
				$stonePrice += isset($stone_data['final_stone_price']) ? $stone_data['final_stone_price'] : '';
			}
		}
		$total_amount = $gemStone + $labout_charge + $metal_rate + $stonePrice;
		$markupAmount = 0;
		if (!empty($priceMarkup)) {
			$markupAmount = ($total_amount * $priceMarkup) / 100;
		}

		$labourChargeValue = isset($params['txtlabourcharge']) ? json_encode($params['txtlabourcharge']) : '';
		$total_amount = $total_amount + $markupAmount;
		//count total quotation products
		$totalProducts = count($productIds);
		DB::setTablePrefix('dml_');
		$is_default = '0';
		if (!empty($isDefaultQuotation)) {
			$is_default = '1';
		}
		$quotationNumber = DB::table('quotation')->select('increment_id')->orderBy('id', 'desc')->limit(1)->get()->first();
		$quotationSerialNo = '';
		//Get quotation number
		$lastData = Quotation::orderBy('created_at', 'desc')->select('increment_id')->first();
		$quotationNumberConfigData = Setting::where('key', config('constants.settings.keys.quotation_number'))->first('value');
		if (empty($lastData->increment_id)) {
			$quotationNumberConfigData = Setting::where('key', config('constants.settings.keys.quotation_number'))->first('value');
			$quotationSerialNo = isset($quotationNumberConfigData->value) ? $quotationNumberConfigData->value : '';
		} else {
			$quotationSerialNo = isset($lastData->increment_id) ? $lastData->increment_id + 1 : '';
		}
		/*if (!empty($quotationNumber->increment_id)) {
				$quotationSerialNo = $quotationNumber->increment_id + 1;
			} else {
				$quotationSerialNo = 1;
		*/
		$data = array('customer_id' => $customerId, 'total_products' => $totalProducts, 'total_metal_weight' => $totalMetalWeight, 'total_stone_caret' => $totalStoneCaret, 'product_data' => json_encode($productDataArr), 'total_amount' => $total_amount, 'labour_charge' => $labourChargeValue, 'updated_at' => date('Y-m-d H:i:s'), 'is_default_quotation' => $is_default, 'increment_id' => $quotationSerialNo);

		$quotation_id = isset($params['quotation_id']) ? $params['quotation_id'] : '';
		$quotationInsertedId = '';
		if (empty($quotation_id)) //insert new quotation data
		{
			Quotation::create($data);
			$quotationInsertedId = DB::getPdo()->lastInsertId();
		} else //update quotation
		{
			Quotation::find($quotation_id)->update($data);
			$quotationInsertedId = $quotation_id;
		}
		$priceRangeData = array();
		$stoneShape = '';
		$stoneRangeData = array();
		$quotationDataInsertedId = array();
		$customerQuotationRateId = array();
		$shape_data_array = array();

		$customerQuotationRate = '';
		if (!empty($quotationInsertedId)) //insert quotation option data if quotation is inserted
		{
			QuotationData::where("quotation_id", $quotation_id)->delete(); //delete all quotation options data for given quotation id

			//CustomerQuotationRate::where("customer_id",$customerId)->delete();//delete all quotation stone info for given customer id
			$availableRateIds = array();
			foreach ($stoneData as $stone_clarity => $stone) {
				foreach ($stone as $key => $stonevalue) {
					//insert quotation options
					$insertData = array('stone_shape' => $key, 'stone_quality' => $stone_clarity, 'stone_range_data' => json_encode(array('stone_range' => $stonevalue['stone_range'], 'stone_price' => $stonevalue['stone_price'])), 'quotation_id' => $quotationInsertedId, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'));
					QuotationData::create($insertData);
					$quotationDataInsertedId[] = DB::getPdo()->lastInsertId();

					//Store stone price information for individual customer
					$customerQuotationRate = CustomerQuotationRate::where("customer_id", DB::raw("$customerId"))->where("stone_shape", DB::raw("'$key'"))->where("stone_quality", DB::raw("'$stone_clarity'"))->get();
					if (count($customerQuotationRate) > 0) {

						foreach ($customerQuotationRate as $key => $stoneRate) {
							$stoneRateData = array('stone_range_data' => json_encode(array('stone_range' => $stonevalue['stone_range'], 'stone_price' => $stonevalue['stone_price'])), 'updated_at' => date('Y-m-d H:i:s'), 'quotation_id' => $quotationInsertedId);
							CustomerQuotationRate::where("id", $stoneRate->id)->update($stoneRateData);
							$customerQuotationRateId[] = $stoneRate->id;
							$availableRateIds[] = $stoneRate->id;
						}
					} else {
						$insertData = array('customer_id' => $customerId, 'stone_shape' => $key, 'stone_quality' => $stone_clarity, 'stone_range_data' => json_encode(array('stone_range' => $stonevalue['stone_range'], 'stone_price' => $stonevalue['stone_price'])), 'quotation_id' => $quotationInsertedId, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'));

						CustomerQuotationRate::create($insertData);
						$customerQuotationRateId[] = DB::getPdo()->lastInsertId();
						$availableRateIds[] = DB::getPdo()->lastInsertId();
					}
				}
			}
			//print_r($availableRateIds);exit;
			//For delete removed product stone info from customer stone rate table
			if (count($availableRateIds) > 0) {
				$availableRateIds = implode("','", $availableRateIds);
				CustomerQuotationRate::whereNotIn('id', [DB::raw("'" . $availableRateIds . "'")])->delete();
			}
		}
		if (!empty($quotationInsertedId) && count($quotationDataInsertedId) > 0 && count($customerQuotationRateId) > 0) //check quotatinon saved successfully
		{
			$request->session()->flash('success', config('constants.message.inventory_quotation_update_success_message'));
			Session::forget('quotation_product_ids');
			Session::forget('edit_product_ids');
			Session::put('export_quotation', 'true');
			Session::save();
			return redirect()->route('inventory.viewquotation', ['id' => $quotationInsertedId]);
			//return redirect("{{route('inventory.viewquotation',$quotationInsertedId) }}");
		} else {
			$request->session()->flash('error', config('constants.message.inventory_quotation_save_failure_message'));
			return redirect('inventory/generatequotation');
			// /return redirect()->route("inventory.quotationlist");
		}
	}
	public function getStateList(Request $request) {
		$params = $request->post();
		if (isset($params['country_id']) && !empty($params['country_id'])) {
			if (App::environment('local')) {
				//$getStateUrl = config('constants.apiurl.local.get_state_list');
				$getStateUrl = Config::get('app.get_states_list');
			} else if (App::environment('test')) {
				$getStateUrl = config('constants.apiurl.test.get_state_list');
			} else {
				$getStateUrl = config('constants.apiurl.live.get_state_list');
			}

			$postParam = 'country_id=' . $params['country_id'];
			$stateList = array();
			$ch = curl_init($getStateUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$info = curl_getinfo($ch);
			if (!empty($result)) {
				echo $result;
			}
			exit;
		}
	}
	//Export excel for quotation products
	public function exportQuotationExcel($id) {
		//$params = $request->post();
		//$quotationId = isset($params['quotationId']) ? $params['quotationId'] : '';
		if (!empty($id)) {
			InventoryHelper::exportQuotationExcel($id);
		}
	}
	//Get quotation list
	public function quotationList(Request $request) {
		$quotationData = Quotation::orderBy('id', 'DESC')->get(); //->paginate(5);
		//return view('metals.index', compact('metals'))->with('i', ($request->input('page', 1) - 1) * 5);
		return view('inventory.quotationlist', compact('quotationData'));
	}
	//Update quotation
	public function edit($id) {
		//$refferer = URL::previous();//to check prev url
		$productIds = array();
		$quotationProductIds = array();
		$quotationData = Quotation::where('id', $id)->get();
		$quotationOptions = QuotationData::where('quotation_id', $id)->get();
		$totalAmount = 0;
		$isDefaultQuotation = '';
		foreach ($quotationData as $key => $quotation) {
			$productData = isset($quotation->product_data) ? json_decode($quotation->product_data) : '';
			$isDefaultQuotation = isset($quotation->is_default_quotation) ? $quotation->is_default_quotation : '';
			foreach ($productData as $key => $product) {
				$quotationProductIds[] = isset($product->product_id) ? $product->product_id : '';
			}
			$totalAmount = ShowroomHelper::currencyFormat(round($quotation->total_amount));
			$customerId = isset($quotation->customer_id) ? $quotation->customer_id : '';
			$customerName = InventoryHelper::getCustomerName($quotation->customer_id);
		}
		$productIds = $quotationProductIds;

		$qualityArray = array();
		$diamonds = array();
		$diamondShapeData = array();
		$diamondShapeData['round'] = array();
		$diamondShapeData['fancy2'] = array();
		$diamondShapeData['fancy1'] = array();
		$roundDiamonds = array();
		$fancy1Diamonds = array();
		$fancy2Diamonds = array();
		$roundShape = config('constants.enum.diamond_shape.round');
		$fancy2Shape = config('constants.enum.diamond_shape.fancy2');
		//$stoneShape = ShowroomHelper::getDiamondShape();
		$productIds = array_filter($productIds);
		$shapeArray = array();
		foreach ($productIds as $key => $productId) {
			DB::setTablePrefix('');
			//Get product stone quality
			$productData = DB::table("catalog_product_flat_1")->select("rts_stone_quality", "certificate_no")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
			DB::setTablePrefix('dml_');
			$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->select('id', 'rts_stone_quality', 'certificate_no')->where('certificate_no', '=', DB::raw("'$productData->certificate_no'"))->get()->first();
			$stone = isset($productData->rts_stone_quality) ? $productData->rts_stone_quality : '';
			DB::setTablePrefix('');
			$stoneData = InventoryHelper::getStoneData($productId);
			$diamondIndex = 0;
			for ($row = 0; $row < sizeof($stoneData['type']); $row++) {
				$diamondShape = strtolower($stoneData['shape'][$row]);
				$diamondQuality = $stoneData['stoneclarity'][$row];
				if (!in_array($diamondQuality, $qualityArray) || !in_array($diamondShape, $shapeArray)) {
					$key = max($key - 1, 0);
					if (in_array($diamondShape, $roundShape)) {
						$diamondShapeData['round'][$productId][$diamondIndex]['product_id'] = $productId;
						$diamondShapeData['round'][$productId][$diamondIndex]['stone_quality'] = $diamondQuality;
						$diamondShapeData['round'][$productId][$diamondIndex]['diamondShape'] = $diamondShape;
						$qualityArray[] = $diamondQuality;
						$shapeArray[] = $diamondShape;
					} else if (in_array($diamondShape, $fancy2Shape)) {
						$diamondShapeData['fancy2'][$productId][$diamondIndex]['product_id'] = $productId;
						$diamondShapeData['fancy2'][$productId][$diamondIndex]['stone_quality'] = $diamondQuality;
						$diamondShapeData['fancy2'][$productId][$diamondIndex]['diamondShape'] = $diamondShape;
						$qualityArray[] = $diamondQuality;
						$shapeArray[] = $diamondShape;
					} else {
						$diamondShapeData['fancy1'][$productId][$diamondIndex]['product_id'] = $productId;
						$diamondShapeData['fancy1'][$productId][$diamondIndex]['stone_quality'] = $diamondQuality;
						$diamondShapeData['fancy1'][$productId][$diamondIndex]['diamondShape'] = $diamondShape;
						$qualityArray[] = $diamondQuality;
						$shapeArray[] = $diamondShape;
					}
				}
				$diamondIndex++;
			}
		}

		$product_ids = implode(',', $productIds);
		$product_id = implode("','", $productIds);
		//Get product ids for search certificate
		DB::setTablePrefix('');
		$productIdCollection = DB::table('catalog_product_flat_1')
			->select('entity_id', 'certificate_no')
			->where('type_id', DB::raw("'simple'"))
			->where('custom_price', '!=', DB::raw("''"))
			->where('isreadytoship', '=', DB::raw("1"))
			->where('status', '=', DB::raw("1"))
			->where('custom_price', '!=', DB::raw("0"))
			->whereNotIn('entity_id', [DB::raw("'" . $product_id . "'")])
			->get();
		DB::setTablePrefix('dml_');
		//Get stone range data
		$shapesWeGot = array();
		if (count($diamondShapeData['round']) > 0 && count($dmlProductData) > 0) {
			$shapesWeGot[] = 'round';
		}
		if (count($diamondShapeData['round']) > 0 && count($dmlProductData) < 1) {
			$shapesWeGot[] = 'round_withoutmm';
		}
		if (count($diamondShapeData['fancy1']) > 0) {
			$shapesWeGot[] = 'fancy1';
		}
		if (count($diamondShapeData['fancy2']) > 0) {
			$shapesWeGot[] = 'fancy2';
		}
		$stoneRangeData = InventoryHelper::getStoneRangeData($shapesWeGot);
		$totalProducts = count($productIds);
		return view('inventory.editquotation', compact('quotationData', 'quotationOptions', 'product_ids', 'diamondShapeData', 'stoneRangeData', 'customerName', 'customerId', 'id', 'totalAmount', 'productIdCollection', 'isDefaultQuotation', 'totalProducts'));
	}
	//Store product ids into session for quotation
	public function storeProductIds(Request $request) {
		$params = $request->post();
		//print_r($params);exit;
		$session_product_ids = array();
		if (isset($params['productIds']) && !empty($params['productIds'])) {
			$product_ids = Session::get('quotation_product_ids');
			if (!empty($product_ids)) {
				$session_product_ids = explode(',', $product_ids);
			}

			$session_product_ids = array_filter($session_product_ids);
			$productIds = explode(',', $params['productIds']);
			$productIds = array_merge($session_product_ids, $productIds);
			$productIds = array_unique($productIds);
			Session::put('quotation_product_ids', implode(',', $productIds));
			Session::save();

			$response['status'] = true;
		} else {
			$response['status'] = false;
		}
		echo json_encode($response);exit;
	}
	//View quotation
	public function viewQuotation($id) {
		$quotationData = Quotation::where('id', $id)->get(); //get quotation data
		$quotationOptions = QuotationData::where('quotation_id', $id)->get(); //get quotation options data
		$productIds = array();
		$totalAmount = 0;
		$refferer = URL::previous(); //to check prev url
		//echo $refferer;exit;
		$excelFlag = false;
		$isDefaultQuotation = '';
		$isExportQuotation = Session::get('export_quotation');
		//export excel only if new quotation is created
		if (strpos($refferer, 'inventory/generatequotation') !== false && $isExportQuotation == true) {

			$excelFlag = true;
		}
		Session::forget('export_quotation'); //remove session for export excel
		foreach ($quotationData as $key => $quotation) {
			$productData = isset($quotation->product_data) ? json_decode($quotation->product_data) : '';
			$isDefaultQuotation = isset($quotation->is_default_quotation) ? $quotation->is_default_quotation : '';
			foreach ($productData as $key => $product) {
				$productIds[] = isset($product->product_id) ? $product->product_id : '';
			}
			$totalAmount = ShowroomHelper::currencyFormat(round($quotation->total_amount));
		}
		$customerId = isset($quotation->customer_id) ? $quotation->customer_id : '';
		//echo $customerId;exit;
		$customerName = InventoryHelper::getCustomerName($quotation->customer_id);
		$qualityArray = array();
		$diamonds = array();
		$diamondShapeData = array();
		$diamondShapeData['round'] = array();
		$diamondShapeData['fancy2'] = array();
		$diamondShapeData['fancy1'] = array();
		$roundDiamonds = array();
		$fancy1Diamonds = array();
		$fancy2Diamonds = array();
		$roundShape = config('constants.enum.diamond_shape.round'); //to check stone shape
		$fancy2Shape = config('constants.enum.diamond_shape.fancy2'); //to check stone shape
		//$stoneShape = ShowroomHelper::getDiamondShape();
		$productIds = array_filter($productIds);
		$shapeArray = array();
		$shapeIndex = 0;
		foreach ($productIds as $key => $productId) {
			DB::setTablePrefix('');
			$productData = DB::table("catalog_product_flat_1")->select("rts_stone_quality", "certificate_no")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
			DB::setTablePrefix('dml_');
			$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->select('id', 'rts_stone_quality', 'certificate_no')->where('certificate_no', '=', DB::raw("'$productData->certificate_no'"))->get()->first();
			$stone = isset($productData->rts_stone_quality) ? $productData->rts_stone_quality : '';
			//Get stone detail
			DB::setTablePrefix('');
			$stoneData = InventoryHelper::getStoneData($productId);
			$diamondIndex = 0;

			if (!empty($stoneData)) {
				//to differentiate stone shape ex,round,fancy1,fancy2
				for ($row = 0; $row < sizeof($stoneData['type']); $row++) {
					$diamondShape = isset($stoneData['shape'][$row]) ? strtolower($stoneData['shape'][$row]) : '';
					$diamondQuality = isset($stoneData['stoneclarity'][$row]) ? $stoneData['stoneclarity'][$row] : '';
					//To check stone shape. Ex, round,fancy1,fancy2
					if (!in_array($diamondQuality, $qualityArray) || !in_array($diamondShape, $shapeArray)) {
						$key = max($key - 1, 0);
						if (in_array($diamondShape, $roundShape)) {
							$diamondShapeData['round'][$productId][$diamondIndex]['product_id'] = $productId;
							$diamondShapeData['round'][$productId][$diamondIndex]['stone_quality'] = $diamondQuality;
							$diamondShapeData['round'][$productId][$diamondIndex]['diamondShape'] = $diamondShape;
							$qualityArray[] = $diamondQuality;
							$shapeArray[] = $diamondShape;
							$shapeIndex++;
						} else if (in_array($diamondShape, $fancy2Shape)) {
							$diamondShapeData['fancy2'][$productId][$diamondIndex]['product_id'] = $productId;
							$diamondShapeData['fancy2'][$productId][$diamondIndex]['stone_quality'] = $diamondQuality;
							$diamondShapeData['fancy2'][$productId][$diamondIndex]['diamondShape'] = $diamondShape;
							$qualityArray[] = $diamondQuality;
							$shapeArray[] = $diamondShape;
							$shapeIndex++;
						} else {
							$diamondShapeData['fancy1'][$productId][$diamondIndex]['product_id'] = $productId;
							$diamondShapeData['fancy1'][$productId][$diamondIndex]['stone_quality'] = $diamondQuality;
							$diamondShapeData['fancy1'][$productId][$diamondIndex]['diamondShape'] = $diamondShape;
							$qualityArray[] = $diamondQuality;
							$shapeArray[] = $diamondShape;
							$shapeIndex++;
						}
					}
					$diamondIndex++;
				}
			}
		}
		DB::setTablePrefix('dml_');
		$product_ids = implode(',', $productIds);
		//get stone range data
		//$stoneRangeData = DB::select("SELECT DISTINCT stone_carat_from,stone_carat_to FROM `grp_stone_manage` order by stone_carat_from,`stone_carat_to` desc limit 5");
		$shapesWeGot = array();
		if (count($diamondShapeData['round']) > 0 && count($dmlProductData) > 0) {
			$shapesWeGot[] = 'round';
		}
		if (count($diamondShapeData['round']) > 0 && count($dmlProductData) < 1) {
			$shapesWeGot[] = 'round_withoutmm';
		}
		if (count($diamondShapeData['fancy1']) > 0) {
			$shapesWeGot[] = 'fancy1';
		}
		if (count($diamondShapeData['fancy2']) > 0) {
			$shapesWeGot[] = 'fancy2';
		}
		//var_dump($shapesWeGot);
		$stoneRangeData = InventoryHelper::getStoneRangeData($shapesWeGot);

		//echo '<pre>';
		//print_r($diamondShapeData);
		//print_r($stoneRangeData);
		//exit;

		return view('inventory.viewquotation', compact('quotation', 'product_ids', 'diamondShapeData', 'stoneRangeData', 'customerName', 'customerId', 'id', 'totalAmount', 'excelFlag', 'isDefaultQuotation'));
	}
	//Store edit product ids into session
	public function storeEditProductIds(Request $request) {
		$params = $request->post();

		$session_product_ids = array();
		if (isset($params['productIds']) && !empty($params['productIds'])) {
			Session::forget('edit_product_ids');
			$productIds = explode(',', $params['productIds']);
			$productIds = array_unique($productIds);
			Session::put('edit_product_ids', implode(',', $productIds));
			Session::save();
			$response['status'] = true;
		} else {
			$response['status'] = false;
		}
		echo json_encode($response);exit;
	}
	//Get product ids of uploaded csv certificate
	public function getProductIds(Request $request) {
		$params = $request->post();
		$isSalesReturn = isset($params['is_soldinventory']) ? $params['is_soldinventory'] : 0;
		$file = isset($_FILES['file']) ? $_FILES['file'] : '';
		//print_r($file['name']);exit;
		if (count($_FILES) > 0) {
			$fileName = $file['name'];
			$fileInfo = pathinfo($fileName);
			$ext = $fileInfo['extension'];
			//$error = $fileInfo['error'];
			$type = $file['type'];
			$tmpName = $file['tmp_name'];
			if ($ext === 'csv') {
				if (($handle = fopen($tmpName, 'r')) !== FALSE) {
					set_time_limit(0);
					$row = 0;
					while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
						// number of fields in the csv
						$col_count = count($data);
						// get the values from the csv
						$csvData[$row] = $data[0];
						// inc the row
						$row++;
					}
					fclose($handle);
				}
			}
			array_shift($csvData);
			DB::setTablePrefix('');
			$certificateNo = implode("','", $csvData);
			//echo $certificateNo;exit;
			$productsCollection = DB::table('catalog_product_flat_1')
				->select('entity_id')
				->where('isreadytoship', '=', DB::raw("1"))
				->where('type_id', '=', DB::raw("'simple'"))
				->where('status', '=', DB::raw("1"))
				->where('custom_price', '!=', DB::raw("0"))
				->where('custom_price', '!=', DB::raw("''"))
				->whereIn('certificate_no', [DB::raw("'" . $certificateNo . "'")])
				->get();
			$productIds = array();
			$orderIds = array();
			//echo "<pre>"; print_r($productsCollection);exit;
			foreach ($productsCollection as $product) {
				$productIds[] = $product->entity_id;
				if ($isSalesReturn) {
					$orderIds[] = InventoryHelper::getOrderId($product->entity_id);
				}
			}
			$productId = implode(',', $productIds);

			$response['status'] = true;
			$response['product_id'] = $productId;
			if ($isSalesReturn) {
				$response['order_id'] = implode(',', $orderIds);
			}
		} else {
			$response['status'] = false;
		}
		echo json_encode($response);exit;
	}
	//Get stored quotation price for existing customer
	public function getCustomerQuotation(Request $request) {
		$params = $request->post();
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		if (!empty($customerId)) {
			$quotationData = CustomerQuotationRate::where("customer_id", $customerId)->get();
			//echo "<pre>";
			//print_r($quotationData);exit;
			$stoneData = array();
			$stone = array();
			$labourChargeData = array();
			$labourCharge = array();
			foreach ($quotationData as $key => $quotation) {
				$stoneShapeData = isset($quotation->stone_range_data) ? json_decode($quotation->stone_range_data) : array();

				//$labourChargeData = isset($quotation->labour_charge) ? json_decode($quotation->labour_charge) : array();
				foreach ($stoneShapeData->stone_range as $shapekey => $stoneRange) {
					$stone_range = explode('-', $stoneRange);
					//print_r($stone_range);exit;
					$from = isset($stone_range[0]) ? trim($stone_range[0]) : '';
					$to = isset($stone_range[1]) ? trim($stone_range[1]) : '';
					$stone_range_val[$shapekey] = $from . '_' . $to;
					$stone['stone_range_' . $quotation->stone_shape . '_' . $stone_range_val[$shapekey] . '_' . $quotation->stone_quality] = $stoneShapeData->stone_price[$shapekey];
				}
				//}
			}
			$labourChargeData = array(
				'round' => config('constants.fix_labour_charge.ROUND'),
				'fancy1' => config('constants.fix_labour_charge.FANCY1'),
				'fancy2' => config('constants.fix_labour_charge.FACNY2'),
			);
			foreach ($labourChargeData as $key => $labour) {
				$labourCharge['txtlabourcharge_' . $key] = isset($labour) ? $labour : '';
			}
			echo json_encode(array('stone_data' => $stone, 'labour_charge' => $labourCharge));exit;
		}
		//print_r($params);exit;
	}
	//delete quotation
	public function deletequotation($id) {
		//delete quotation data
		QuotationData::where("quotation_id", $id)->delete();

		//Delete customer quotation rate
		CustomerQuotationRate::where("quotation_id", $id)->delete();

		//delete quotation
		$quotation = Quotation::find($id);
		$quotation->delete();
		return redirect('inventory/quotationlist')->with('success', config('constants.message.inventory_quotation_delete_success_message'));
	}
	//Create new customer
	public function createCustomer(Request $request) {
		$params = $request->post();
		$formData = array();

		parse_str($params['form_data'], $formData);
		$firstName = isset($formData['txtfirstname']) ? $formData['txtfirstname'] : '';
		$lastName = isset($formData['txtlastname']) ? $formData['txtlastname'] : '';
		$contactNumber = isset($formData['txtcontactnumber']) ? $formData['txtcontactnumber'] : '';
		$emailAddress = isset($formData['txtemail']) ? $formData['txtemail'] : '';
		$street = isset($formData['txtaddress']) ? $formData['txtaddress'] : '';
		$countryId = isset($formData['selectcountry']) ? $formData['selectcountry'] : '';
		$region = isset($formData['txtstateprovince']) ? $formData['txtstateprovince'] : '';
		$city = isset($formData['txtcity']) ? $formData['txtcity'] : '';
		$zipCode = isset($formData['txtzipcode']) ? $formData['txtzipcode'] : '';
		$password = $firstName . rand(10000, 999) . '@dealer';
		$customerId = '';

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
		$customerParams = 'firstname=' . $firstName . '&lastname=' . $lastName . '&email=' . $emailAddress . '&contact_number=' . $contactNumber . '&community=' . '1' . '&street=' . $street . '&country_id=' . $countryId . '&region=' . $region . '&city=' . $city . '&entity_customer=' . '1' . '&postcode=' . $zipCode . '&password=' . $password . '&confirmation=' . '1' . '&franchisee_status=' . '2';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $customerParams);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$response = json_decode($result);

		if ($response->status == 'success') {
			$customerId = $response->customer_id;
		}

		echo $result;exit;
	}
	//refresh product ids for new product
	public function getproductidsfornewproduct(Request $request) {
		$params = $request->post();
		$productIds = isset($params['productId']) ? explode(',', $params['productId']) : '';
		if (!empty($productIds)) {
			$product_id = implode("','", $productIds);
			DB::setTablePrefix('');
			$productIdCollection = DB::table('catalog_product_flat_1')
				->select('entity_id', 'certificate_no')
				->where('type_id', DB::raw("'simple'"))
				->where('custom_price', '!=', DB::raw("''"))
				->where('isreadytoship', '=', DB::raw("1"))
				->where('status', '=', DB::raw("1"))
				->where('custom_price', '!=', DB::raw("0"))
				->whereNotIn('entity_id', [DB::raw("'" . $product_id . "'")])
				->get();
			DB::setTablePrefix('dml_');
			return view('inventory.getproductidhtml')->with(array('productIdCollection' => $productIdCollection));
		}
	}
	//Refresh stone tab content
	public function refreshStoneInfo(Request $request) {
		$params = $request->post();
		$productIds = isset($params['productIds']) ? $params['productIds'] : array();
		$quotationId = isset($params['quotation_id']) ? $params['quotation_id'] : '';
		$quotationData = Quotation::where('id', $quotationId)->get();
		$quotationOptions = QuotationData::where('quotation_id', $quotationId)->get();
		/*echo "<pre>";
    	print_r($productIds);exit;*/
		foreach ($quotationData as $key => $quotation) {
			$productData = json_decode($quotation->product_data);
			$isDefaultQuotation = $quotation->is_default_quotation;
			foreach ($productData as $key => $product) {
				$quotationProductIds[] = $product->product_id;
			}
			$totalAmount = ShowroomHelper::currencyFormat(round($quotation->total_amount));
			$customerId = isset($quotation->customer_id) ? $quotation->customer_id : '';
			$customerName = InventoryHelper::getCustomerName($quotation->customer_id);
		}
		/*$sessionProductIds = Session::get('quotation_product_ids');
			    	$sessionProductIds = explode(',',$sessionProductIds);
		*/
		$productIds = explode(',', $productIds);
		$qualityArray = array();
		$diamonds = array();
		$diamondShapeData = array();
		$diamondShapeData['round'] = array();
		$diamondShapeData['fancy2'] = array();
		$diamondShapeData['fancy1'] = array();
		$roundDiamonds = array();
		$fancy1Diamonds = array();
		$fancy2Diamonds = array();
		$roundShape = config('constants.enum.diamond_shape.round');
		$fancy2Shape = config('constants.enum.diamond_shape.fancy2');
		//$stoneShape = ShowroomHelper::getDiamondShape();
		//$productIds = array_filter($productIds);
		$shapeArray = array();
		//print_r($productIds);exit;
		foreach ($productIds as $key => $productId) {
			//echo $productId."<br>";
			DB::setTablePrefix('');
			$productData = DB::table("catalog_product_flat_1")->select("rts_stone_quality")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
			$stone = $productData->rts_stone_quality;
			$stoneData = InventoryHelper::getStoneData($productId);
			//echo "<pre>";
			//print_r($stoneData);
			$diamondIndex = 0;
			for ($row = 0; $row < sizeof($stoneData['type']); $row++) {
				$diamondShape = strtolower($stoneData['shape'][$row]);
				$diamondQuality = $stoneData['stoneclarity'][$row];
				if (!in_array($diamondQuality, $qualityArray) || !in_array($diamondShape, $shapeArray)) {
					$key = max($key - 1, 0);
					if (in_array($diamondShape, $roundShape)) {
						$diamondShapeData['round'][$productId][$diamondIndex]['product_id'] = $productId;
						$diamondShapeData['round'][$productId][$diamondIndex]['stone_quality'] = $diamondQuality;
						$diamondShapeData['round'][$productId][$diamondIndex]['diamondShape'] = $diamondShape;
						$qualityArray[] = $diamondQuality;
						$shapeArray[] = $diamondShape;
					} else if (in_array($diamondShape, $fancy2Shape)) {
						$diamondShapeData['fancy2'][$productId][$diamondIndex]['product_id'] = $productId;
						$diamondShapeData['fancy2'][$productId][$diamondIndex]['stone_quality'] = $diamondQuality;
						$diamondShapeData['fancy2'][$productId][$diamondIndex]['diamondShape'] = $diamondShape;
						$qualityArray[] = $diamondQuality;
						$shapeArray[] = $diamondShape;
					} else {
						$diamondShapeData['fancy1'][$productId][$diamondIndex]['product_id'] = $productId;
						$diamondShapeData['fancy1'][$productId][$diamondIndex]['stone_quality'] = $diamondQuality;
						$diamondShapeData['fancy1'][$productId][$diamondIndex]['diamondShape'] = $diamondShape;
						$qualityArray[] = $diamondQuality;
						$shapeArray[] = $diamondShape;
					}
				}
				$diamondIndex++;
			}
		}
		/*echo "<pre>";
    	print_r($diamondShapeData);exit;*/

		$product_ids = implode(',', $productIds);
		$product_id = implode("','", $productIds);
		$productIdCollection = DB::table('catalog_product_flat_1')
			->select('entity_id', 'certificate_no')
			->where('type_id', DB::raw("'simple'"))
			->where('custom_price', '!=', DB::raw("''"))
			->where('isreadytoship', '=', DB::raw("1"))
			->where('status', '=', DB::raw("1"))
			->where('custom_price', '!=', DB::raw("0"))
			->whereNotIn('entity_id', [DB::raw("'" . $product_id . "'")])
			->get();
		DB::setTablePrefix('dml_');
		//Get stone range data
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
		return view('inventory.refreshstoneinfo', compact('quotationData', 'quotationOptions', 'product_ids', 'diamondShapeData', 'stoneRangeData', 'customerName', 'customerId', 'quotationId', 'totalAmount', 'productIdCollection', 'isDefaultQuotation'));
	}
	//Add new product for quotation
	public function addProduct(Request $request) {
		$params = $request->post();
		$certificateNo = isset($params['certificate_no']) ? $params['certificate_no'] : '';
		$product = InventoryHelper::isCertificateExist($certificateNo);
		if (!empty($product)) {
			$response['status'] = 1;
			$response['entity_id'] = $product->entity_id;
			$response['certificate_no'] = $product->certificate_no;
		} else {
			$response['status'] = 0;
			$response['message'] = Config::get('constants.message.inventory_certificate_not_found');
		}
		echo json_encode($response);exit;
	}
	//Get default stone price from website
	public function getDefaultStonePrice(Request $request) {
		$params = $request->post();
		$stoneData = isset($params['stone_data']) ? $params['stone_data'] : '';
		$stoneDataArr = array();

		foreach ($stoneData as $key => $stone) {
			$stoneInfo = json_decode($stone);

			$stoneShape = InventoryHelper::getStoneShapeId($stoneInfo->stone_shape);

			$stoneQualityId = InventoryHelper::getStoneClarityId($stoneInfo->stone_quality);
			$stoneRange = json_decode($stoneInfo->stone_range_data);

			foreach ($stoneRange as $stonekey => $stoneval) {
				foreach ($stoneval as $value) {

					$stoneRangeData = DB::select("SELECT stone_price,stone_carat_from,stone_carat_to FROM `grp_stone_manage` WHERE stone_shape=" . DB::raw("'$stoneShape'") . " AND stone_clarity=" . DB::raw("'$stoneQualityId'") . " AND ((stone_carat_from=" . DB::raw("'$value->stone_carat_from'") . " AND stone_carat_to=" . DB::raw("'$value->stone_carat_to'") . ") OR (mm_from=" . DB::raw("'$value->stone_carat_from'") . " AND mm_to=" . DB::raw("'$value->stone_carat_to'") . "))");
					//$stoneDataArr[$stoneInfo->stone_shape][$stoneInfo->stone_quality][$stonekey] = isset($stoneRangeData[0]->stone_price) ? $stoneRangeData[0]->stone_price : '';
					$stoneDataArr['stone_range_' . $stoneInfo->stone_shape . '_' . $value->stone_carat_from . '_' . $value->stone_carat_to . '_' . $stoneInfo->stone_quality] = isset($stoneRangeData[0]->stone_price) ? $stoneRangeData[0]->stone_price : '';
				}
			}
		}

		$labourChargeData = array(
			'round' => config('constants.fix_labour_charge.ROUND'),
			'fancy1' => config('constants.fix_labour_charge.FANCY1'),
			'fancy2' => config('constants.fix_labour_charge.FACNY2'),
		);
		foreach ($labourChargeData as $key => $labour) {
			$labourCharge['txtlabourcharge_' . $key] = isset($labour) ? $labour : '';
		}

		//echo json_encode($stoneDataArr);exit;
		echo json_encode(array('stone_data' => $stoneDataArr, 'labour_charge' => $labourCharge));exit;
	}
	//Upload bulk stone rate
	public function bulkRateUpload(Request $request) {
		$params = $request->post();
		$file = isset($_FILES['file']) ? $_FILES['file'] : '';
		if (count($_FILES) > 0) {
			$fileName = $file['name'];
			$fileInfo = pathinfo($fileName);
			$ext = $fileInfo['extension'];
			$type = $file['type'];
			$tmpName = $file['tmp_name'];
			$stoneRangeData = array();
			if ($ext === 'csv') {
				$file = fopen($tmpName, 'r') or die('Unable to open file!');
				$csvData = array();
				$header = null;
				while (($row = fgetcsv($file)) !== false) {
					if ($header === null) {
						$header = $row;
						continue;
					}
					$newRow = array();
					for ($i = 0; $i < count($row); $i++) {
						$newRow[$header[$i]] = $row[$i];
					}
					$csvData[] = $newRow;
				}
				fclose($file);
				foreach ($csvData as $key => $data) {
					foreach ($data as $key => $value) {
						if ($key != 'shape' && $key != 'quality') {
							$range = explode('-', $key);
						}

						if (!empty($range)) {
							$stoneRangeData['stone_range_' . strtolower(trim($data['shape'])) . '_' . trim($range[0]) . '_' . trim($range[1]) . '_' . $data['quality']] = $data[$range[0] . '-' . $range[1]];
						}

					}
				}
			}
			echo json_encode($stoneRangeData);exit;
		}
	}
	//Display generated memo list
	public function memoList() {
		DB::setTablePrefix('');
		/*DB::setTablePrefix('');
			$generatedMemoList = DB::table("sales_flat_order as main_table")
				->select("*")
				->where("status", "=", DB::raw("'pending'"))
				->where("qr_product_status", "=", DB::raw("'1'"))
				->orderBy("created_at", "DESC")
				->get();
		*/
		/*$generatedMemoList = ApprovalMemo::orderBy('created_at', 'desc')->select('*')->where('status','=','pending')->get();*/
		$generatedMemoList = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.is_for_old_data', 'memo.customer_id', 'memo.approval_no', 'memo.is_delivered', 'memo.created_at', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join(DB::raw('dml_approval_memo_histroy as memo_histroy'), 'memo.id', '=', 'memo_histroy.approval_memo_id')->join('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'memo_histroy.product_id')->groupBy('memo_histroy.approval_memo_id')->get();
		DB::setTablePrefix('dml_');
		return view('inventory.memolist', compact('generatedMemoList'));
	}
	//Display generated invoice list
	public function invoiceList() {
		DB::setTablePrefix('');
		$generatedInvoiceList = DB::table("sales_flat_order as main_table")
			->select("main_table.entity_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total as order_total", "main_table.status", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total", "oinv.gst_percentage", "invoice_division.parent_customer_id", "invoice_division.child_customer_name", "invoice_division.child_customer_address", "invoice_division.child_customer_pan", "invoice_division.invoice_id", "oinv.invoice_shipping_charge")
			->where("qr_product_status", "=", DB::raw("'1'"))
			->where('main_table.status', '=', DB::raw("'complete'"))
			->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
			->leftJoin('dml_invoice_customer_division as invoice_division', 'invoice_division.invoice_id', '=', 'oinv.entity_id')
			->join('sales_flat_invoice_item as oinv_item', 'oinv_item.parent_id', '=', 'oinv.entity_id')
			->leftJoin('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'oinv_item.product_id')
			->orderBy("oinv.created_at", "desc")
			->groupBy('oinv_item.parent_id');

		$invoiceCount = $generatedInvoiceList->get()->count();
		$invoiceCollection = $generatedInvoiceList->take(10)->get();
		$invoiceData = array('totalCount' => $invoiceCount, 'invoiceCollection' => $invoiceCollection);
		DB::setTablePrefix('dml_');
		return view('inventory.invoicelist', compact('invoiceData'));
	}
	public function invoiceAjaxList(Request $request) {
		$params = $request->post();
		$data = array();
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $start;
		$fromDate = isset($params['from_date']) ? $params['from_date'] : '';
		$toDate = isset($params['to_date']) ? $params['to_date'] : '';
		$invoiceStatus = isset($params['invoice_status']) ? $params['invoice_status'] : 'complete';
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');

		DB::setTablePrefix('');
		$generatedInvoiceList = DB::table("sales_flat_order as main_table")
			->select("main_table.entity_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total as order_total", "main_table.status", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total", "oinv.gst_percentage", "invoice_division.parent_customer_id", "invoice_division.child_customer_name", "invoice_division.child_customer_address", "invoice_division.child_customer_pan", "invoice_division.invoice_id", "oinv.invoice_shipping_charge")
			->where("qr_product_status", "=", DB::raw("'1'"))
			->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
			->leftJoin('dml_invoice_customer_division as invoice_division', 'invoice_division.invoice_id', '=', 'oinv.entity_id')
			->join('sales_flat_invoice_item as oinv_item', 'oinv_item.parent_id', '=', 'oinv.entity_id')
			->leftJoin('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'oinv_item.product_id')
			->orderBy("oinv.created_at", "desc")
			->groupBy('oinv_item.parent_id');
		if (!empty($fromDate)) {
			$date = date('Y-m-d', strtotime($fromDate));
			$generatedInvoiceList = $generatedInvoiceList->whereDate("oinv.created_at", ">=", DB::raw("'$date'"));
		}
		if (!empty($toDate)) {
			$date = date('Y-m-d', strtotime($toDate));
			$generatedInvoiceList = $generatedInvoiceList->whereDate("oinv.created_at", "<=", DB::raw("'$date'"));
		}
		if (!empty($invoiceStatus)) {
			$generatedInvoiceList = $generatedInvoiceList->where('main_table.status', '=', DB::raw("'$invoiceStatus'"));
		}
		if (!empty($searchValue)) {
			$generatedInvoiceList = $generatedInvoiceList->where("oinv.increment_id", "LIKE", DB::raw("'%$searchValue%'"))->orWhere("main_table.customer_id", "LIKE", DB::raw("'%$searchValue%'"))->orWhere("main_table.customer_firstname", "LIKE", DB::raw("'%$searchValue%'"))->orWhere("main_table.customer_lastname", "LIKE", DB::raw("'%$searchValue%'"));
		}
		//echo $generatedInvoiceList->toSql();exit;
		$invoiceCount = $generatedInvoiceList->get()->count();
		$invoiceCollection = $generatedInvoiceList->take($length)->offset($curpage)->get();
		$data["draw"] = $params['draw'];
		$data["recordsTotal"] = $invoiceCount;
		$data["recordsFiltered"] = $invoiceCount;
		$data['deferLoading'] = $invoiceCount;
		if ($invoiceCount > 0) {
			$price = 0;
			foreach ($invoiceCollection as $key => $invoice) {
				//print_r($invoice);exit;
				$totalInvoiceValue = 0;
				$gstTotal = 0;
				$totalGrandTotalPrice = 0;
				$totalDiscountAmount = 0;
				$customerName = InventoryHelper::getCustomerName($invoice->customer_id);
				$customerId = isset($invoice->customer_id) ? 'DML' . $invoice->customer_id : '';
				if (isset($invoice->gst_percentage) && !empty($invoice->gst_percentage)) {
					$invoiceGstPercentage = $invoice->gst_percentage;
				} else {
					$invoiceGstPercentage = 3;
				}
				if (isset($invoice->child_customer_name) && !empty($invoice->child_customer_name)) {
					$customerName = $invoice->child_customer_name;
					$customerId = 'N/A';
				}
				$invoiceDate = date('d-m-Y', strtotime($invoice->invoice_created_date));
				$invoiceNumber = isset($invoice->invoice_number) ? $invoice->invoice_number : '';
				$invoiceItems = InventoryHelper::getInvoiceItems($invoice->invoice_ent_id);
				$shippingCharge = (isset($invoice->invoice_shipping_charge) && !empty($invoice->invoice_shipping_charge)) ? $invoice->invoice_shipping_charge : 0;
				foreach ($invoiceItems as $key => $invoiceItem) {
					$price = isset($invoiceItem->price) ? $invoiceItem->price : 0;
					$discountAmount = isset($invoiceItem->discount_amount) ? $invoiceItem->discount_amount : 0;
					$totalGrandTotalPrice += isset($invoiceItem->price) ? $invoiceItem->price : 0;
					$totalDiscountAmount += $discountAmount;
				}
				$totalInvoiceValue = ($totalGrandTotalPrice - $totalDiscountAmount);
				$totalInvoiceValue += $shippingCharge;
				$gstTotal = ($totalInvoiceValue * ($invoiceGstPercentage / 100));
				$totalInvoiceValue += round($gstTotal, 2);
				$invoiceGrandTotal = ShowroomHelper::currencyFormat(intval($totalInvoiceValue));
				$action = '';
				$action .= '<a title="View Invoice" target="_blank" class="mr-1 ml-1 color-content table-action-style1" href="' . route('viewinvoice', ['id' => $invoice->invoice_ent_id]) . '"><i class="list-icon fa fa-book"></i></a>';
				$createdDate = isset($invoice->invoice_created_date) ? $invoice->invoice_created_date : '';

				if (!empty($createdDate)) {
					$createdMonth = date('m', strtotime($createdDate));
					$createdYear = date('Y', strtotime($createdDate));
					$maxInvoiceData = date($createdYear . '-' . $createdMonth . '-t');
				}
				$currentDate = date('Y-m-d');
				$disableInvoiceCancelClass = (($createdDate >= $maxInvoiceData) || ($maxInvoiceData <= $currentDate)) ? 'disabled' : '';
				$action .= '<a title="Edit Invoice" target="_blank" class="mr-1 ml-1 color-content table-action-style1 ' . $disableInvoiceCancelClass . '" href="' . route('inventory.editinvoice', ['id' => $invoice->invoice_ent_id]) . '" ' . $disableInvoiceCancelClass . '><i class="list-icon fa fa-pencil-square-o"></i></a>';
				if ($invoice->status != 'canceled') {
					/* $createdDate = isset($invoice->invoice_created_date) ? $invoice->invoice_created_date : '';

						if (!empty($createdDate)) {
							$createdMonth = date('m', strtotime($createdDate));
							$maxInvoiceData = date('Y-' . $createdMonth . '-t');
						}
					*/
					$disableInvoiceCancelClass = (($createdDate >= $maxInvoiceData) || ($maxInvoiceData <= $currentDate)) ? 'disabled' : '';
					$action .= '<a title="Cancel Invoice" class="mr-1 ml-1 color-content table-action-style1 btn-cancel-invoice pointer ' . $disableInvoiceCancelClass . '" data-orderid="' . $invoice->invoice_ent_id . '" data-href="' . route('cancelinvoice', ['id' => $invoice->invoice_ent_id]) . '" ' . $disableInvoiceCancelClass . '><i class="list-icon fa fa-trash-o"></i></a>';
				}
				$orderItems = InventoryHelper::getOrderItems($invoice->entity_id);
				if (count($orderItems) > 0) {
					$action .= '<a title="Download Excel" target="_blank" class="mr-1 ml-1 color-content table-action-style1 pointer downloadexcel" data-id="' . $invoice->invoice_ent_id . '"><i class="list-icon fa fa-file-excel-o"></i></a>';
				}

				$action .= '<a title="Delivery Challan" target="_blank"  class="mr-1 ml-1 color-content table-action-style1" href="' . route('deliverystatus', ['id' => $invoice->invoice_ent_id]) . '"><i class="list-icon fa fa-truck"></i></a>';

				$data['data'][] = array($invoiceNumber, $customerName, $customerId, $invoiceDate, $invoiceGrandTotal, $action);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	//Edit Memo
	public function editMemo($id) {
		if (!empty($id)) {
			//Get state list by cuontry id
			$get_country_list = '';
			if (App::environment('local')) {
				$get_country_list = Config::get('constants.apiurl.local.get_country_list');
			} else if (App::environment('test')) {
				$get_country_list = Config::get('constants.apiurl.test.get_country_list');
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
			if (!empty($result)) {
				$countryList = json_decode($result);
			}
			$franchiseeData = InventoryHelper::getFranchiseeData();
			DB::setTablePrefix('');
			$memoData = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.customer_id', 'memo.approval_no', 'memo.created_at', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.id', '=', DB::raw("$id"))->groupBy('memo_histroy.approval_memo_id')->get()->first();
			DB::setTablePrefix('dml_');
			return view('inventory.editmemo', compact('id', 'countryList', 'franchiseeData', 'memoData'));
		}
	}
	//Edit Invoice
	public function editInvoice($id) {

		if (!empty($id)) {
			/* //Get state list by cuontry id
				$get_country_list = '';
				if (App::environment('local')) {
					$get_country_list = Config::get('constants.apiurl.local.get_country_list');
				} else if (App::environment('test')) {
					$get_country_list = Config::get('constants.apiurl.test.get_country_list');
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
				if (!empty($result)) {
					$countryList = json_decode($result);
			*/
			$franchiseeData = InventoryHelper::getFranchiseeData();
			DB::setTablePrefix('');
			$countryList = array('country_id' => 'IN', 'name' => 'India');
			$invoiceData = DB::table("sales_flat_order as main_table")
				->select("main_table.entity_id", "main_table.custom_discount_percent", "main_table.shipping_address_id", "main_table.isfranchisee", "main_table.dmlstore_order_increment_id", "main_table.franchise_order_increment_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total as order_total", "main_table.status", "main_table.customer_email", "main_table.billing_address_id", "main_table.shipping_address_id", "main_table.payment_mode", "main_table.selected_franchise_id", "main_table.selected_franchise_commission", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total", "invoice_division.parent_customer_id", "invoice_division.child_customer_name", "invoice_division.child_customer_address", "invoice_division.child_customer_pan", "invoice_division.invoice_id", "oinv.invoice_shipping_charge")
				->where("qr_product_status", "=", DB::raw("'1'"))
				->where('main_table.status', '=', DB::raw("'complete'"))
				->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
				->leftJoin('dml_invoice_customer_division as invoice_division', 'invoice_division.invoice_id', '=', 'oinv.entity_id')
				->join('sales_flat_invoice_item as oinv_item', 'oinv_item.parent_id', '=', 'oinv.entity_id')
				->leftJoin('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'oinv_item.product_id')
				->where("oinv.entity_id", "=", DB::raw("$id"))
				->orderBy("oinv.created_at", "desc")
				->groupBy('oinv_item.parent_id')
				->get()
				->first();
			DB::setTablePrefix('dml_');

			return view('inventory.editinvoice', compact('id', 'countryList', 'franchiseeData', 'invoiceData'));
		}
	}
	//View memo
	public function viewMemo($id) {
		if (!empty($id)) {
			//$memoData = ApprovalMemo::find($id);
			DB::setTablePrefix('');
			$memoData = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.is_for_old_data', 'memo.customer_id', 'memo.approval_no', 'memo.created_at', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.id', '=', DB::raw("$id"))->groupBy('memo_histroy.approval_memo_id')->get()->first();
			DB::setTablePrefix('dml_');
			return view('inventory.viewmemo', compact('memoData'));
		}
	}
	//Download memo products excel
	public function downloadMemoProductExcel($id) {
		if (!empty($id)) {
			$totalMetalWeight = 0;
			$totalStoneWeight = 0;
			$totalPrice = 0;
			$data = array();
			DB::setTablePrefix('');
			$memoData = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.customer_id', 'memo.approval_no', 'memo.created_at', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.id', '=', DB::raw("$id"))->groupBy('memo_histroy.approval_memo_id')->get()->first();
			$memoProductsIds = DB::table('dml_approval_memo_histroy')->select('product_id')->where('approval_no', '=', DB::raw("'$memoData->approval_no'"))->get();
			foreach ($memoProductsIds as $ids) {
				$productIds[] = $ids->product_id;
			}
			foreach ($productIds as $key => $productId) {
				$product = DB::table("catalog_product_flat_1")->select("*")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
				$metalData = InventoryHelper::getMetalData($productId);
				$metalData = (array) $metalData;
				$metalRate = isset($metalData['simple']) ? $metalData['simple'] : 0;
				$metalWeight = isset($metalData['weight']) ? $metalData['weight'] : 0;
				$stone = isset($product->rts_stone_quality) ? $product->rts_stone_quality : 0;
				$stoneData = InventoryHelper::getStoneData($product->entity_id);
				$productWithOption = array();
				foreach ($stoneData as $key => $stone) {
					if ($key == "type" || $key == "shape" || $key == "setting" || $key == "stone_use" || $key == "totalcts" || $key == "percts" || $key == "stone_price" || $key == "stoneclarity") {
						$sizeof = count($stone);
						$productWithOption[0][$key] = $stoneData[$key][0];
						for ($st = 0; $st < $sizeof; $st++) {
							$productWithOption[$st][$key] = $stoneData[$key][$st];
						}
					}
				}
				$stoneShape = array();
				$stoneClarity = array();
				$stonePices = array();
				$stoneWeight = array();
				$caratPrice = array();
				foreach ($productWithOption as $optionData) {
					$stoneShape[] = ucwords(strtolower($optionData['shape']));
					if (!empty($optionData['stoneclarity'])) {
						$stoneClarity[] = $optionData['stoneclarity'];
					} else {
						$stoneClarity[] = $quality;
					}
					$stonePices[] = $optionData['stone_use'];
					$stoneWeight[] = round((float) $optionData['totalcts'] * (float) $optionData['stone_use'], 2);
					$caratPrice[] = preg_replace("/[^0-9]/", "", $optionData['stone_price']);
				}
				$maxStoneCount = max(count($stoneShape), count($stoneClarity), count($stonePices), count($stoneWeight), count($caratPrice));
				$diaweight = $stoneData['totalcts'];
				$totalDiaWeight = 0;
				//$diamondQuality = array();
				foreach ($diaweight as $weight) {
					$totalDiaWeight += $weight;
				}
				$diamondWeight = $totalDiaWeight;
				$sku = isset($product->sku) ? $product->sku : '';
				$skuArr = explode(' ', $sku);
				$metalQuality = $skuArr[1];
				$certificateNo = isset($product->certificate_no) ? $product->certificate_no : '';
				$rtsStoneQuality = isset($product->rts_stone_quality) ? $product->rts_stone_quality : '';
				$price = isset($product->custom_price) ? $product->custom_price : 0;
				$attributeSetId = isset($product->attribute_set_id) ? $product->attribute_set_id : '';
				DB::setTablePrefix('');
				$attributeSetData = DB::table("eav_attribute_set")->select("attribute_set_name")->where("attribute_set_id", "=", DB::raw("$attributeSetId"))->get()->first();
				$productType = isset($attributeSetData->attribute_set_name) ? $attributeSetData->attribute_set_name : '';
				$totalMetalWeight += $metalWeight;
				$totalStoneWeight += $diamondWeight;
				$totalPrice += $price;
				$data[] = array(
					'CERTIFICATE NO.' => $certificateNo,
					'SKU' => $sku,
					'PRODUCT TYPE' => $productType,
					'METAL GROSS' => $metalWeight,
					'METAL QUALITY' => $metalQuality,
					'TOTAL DIAMOND WT' => $totalDiaWeight,
					'DIAMOND QUALITY' => implode('|', $stoneClarity),
					'FINAL PRICE' => round($price),
				);
				/* for ($index = 0; $index < $maxStoneCount; $index++) {
					$totalStoneWeight += isset($stoneWeight[$index]) ? ($stoneWeight[$index]) : 0 ;
					$data[] = array(
						'CERTIFICATE NO.' => ($index == 0) ? $certificateNo : '',
						'SKU' => ($index == 0) ? $sku : '',
						'PRODUCT TYPE' => ($index == 0) ? $productType : '',
						'METAL GROSS' => ($index == 0) ? $metalWeight : '',
						'METAL QUALITY' => ($index == 0) ? $metalQuality : '',
						'TOTAL DIAMOND WT' => isset($stoneWeight[$index]) ? ($stoneWeight[$index]) : '',
						'DIAMOND QUALITY' => isset($stoneClarity[$index]) ? $stoneClarity[$index] : '',
						'FINAL PRICE' => ($index == 0) ? round($price) : '',
					);
				} */
			}
			$row = 0;
			$totalItems = count($data);
			Session::put('totalItems', $totalItems);
			Session::put('totalMetalWeight', $totalMetalWeight);
			Session::put('totalStoneWeight', $totalStoneWeight);
			Session::put('totalPrice', $totalPrice);
			Session::save();
			DB::setTablePrefix('dml_');
			return \Excel::create('products', function ($excel) use ($data) {
				$excel->sheet('Sheet', function ($sheet) use ($data) {
					$sheet->cell('B' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue('Grand Total');
					});
					$sheet->cell('C' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalItems'));
					});
					$sheet->cell('D' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalMetalWeight'));
					});
					$sheet->cell('F' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalStoneWeight'));
					});
					$sheet->cell('H' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalPrice'));
					});
					$sheet->cell('G' . ((int) Session::get('totalItems') + 3), function ($cell) {
						$cell->setValue('TOTAL PRODUCTS-');
					});
					$sheet->cell('H' . ((int) Session::get('totalItems') + 3), function ($cell) {
						$cell->setValue(Session::get('totalItems'));
					});
					$sheet->cell('F' . ((int) Session::get('totalItems') + 4), function ($cell) {
						$cell->setValue('INCLUDING 3 % GST');
					});
					$sheet->fromArray($data);
				});
			})->download('xlsx');
		}
	}
	//download excel
	public function downloadExcel($id) {

		if (!empty($id)) {
			$invoiceItems = InventoryHelper::getInvoiceItems($id);
			$totalMetalWeight = 0;
			$totalStoneWeight = 0;
			$totalPrice = 0;
			$data = array();

			foreach ($invoiceItems as $key => $item) {
				$productId = isset($item->product_id) ? $item->product_id : '';
				DB::setTablePrefix('');
				$product = DB::table("catalog_product_flat_1")->select("entity_id", "rts_stone_quality", "sku", "certificate_no", "attribute_set_id")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
				DB::setTablePrefix('dml_');

				$invoiceProductData = InventoryHelper::getInvoiceProductData($item->product_id, $id);
				if (empty($product)) {
					continue;
				}
				$metalWeight = isset($invoiceProductData->metal_weight) ? number_format($invoiceProductData->metal_weight, 2) : 0;
				$stoneWeight = isset($invoiceProductData->stone_weight) ? number_format($invoiceProductData->stone_weight, 2) : 0;
				$totalStoneWeight += $stoneWeight;
				$totalMetalWeight += $metalWeight;
				$stoneData = InventoryHelper::getStoneData($product->entity_id);
				$stoneClarity = array();
				for ($stoneIndex = 0; $stoneIndex < count($stoneData['stoneclarity']); $stoneIndex++) {
					$stoneClarity[] = $stoneData['stoneclarity'][$stoneIndex];
				}
				//$diamondWeight = isset($stoneData['totalweight'][0]) ? $stoneData['totalweight'][0] : '';
				$sku = isset($product->sku) ? $product->sku : '';
				$skuArr = explode(' ', $sku);
				$metalQuality = $skuArr[1];
				$certificateNo = isset($product->certificate_no) ? $product->certificate_no : '';
				$rtsStoneQuality = isset($product->rts_stone_quality) ? $product->rts_stone_quality : '';
				$unitPrice = isset($item->price) ? intval($item->price) : 0;
				$discountAmount = isset($item->discount_amount) ? $item->discount_amount : 0;
				$attributeSetId = isset($product->attribute_set_id) ? $product->attribute_set_id : '';
				DB::setTablePrefix('');
				$attributeSetData = DB::table("eav_attribute_set")->select("attribute_set_name")->where("attribute_set_id", "=", DB::raw("$attributeSetId"))->get()->first();
				$productType = isset($attributeSetData->attribute_set_name) ? $attributeSetData->attribute_set_name : '';
				$totalPrice += ($unitPrice);
				$data[] = array(
					'CERTIFICATE NO.' => $certificateNo,
					'SKU' => $sku,
					'PRODUCT TYPE' => $productType,
					'METAL GROSS' => $metalWeight,
					'METAL QUALITY' => $metalQuality,
					'TOTAL DIAMOND WT' => $stoneWeight,
					'DIAMOND QUALITY' => implode('|', $stoneClarity),
					'FINAL PRICE' => $unitPrice,
				);
			}
			$gstAmount = ($totalPrice * 0.03);
			//$totalPrice += $gstAmount;
			$row = 0;
			$totalItems = count($data);
			Session::put('totalItems', $totalItems);
			Session::put('totalMetalWeight', $totalMetalWeight);
			Session::put('totalStoneWeight', $totalStoneWeight);
			Session::put('totalPrice', round($totalPrice));
			Session::save();
			return \Excel::create('products', function ($excel) use ($data) {
				$excel->sheet('Sheet', function ($sheet) use ($data) {
					$sheet->cell('B' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue('Grand Total');
					});
					$sheet->cell('C' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalItems'));
					});
					$sheet->cell('D' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalMetalWeight'));
					});
					$sheet->cell('F' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalStoneWeight'));
					});
					$sheet->cell('H' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalPrice'));
					});
					$sheet->cell('G' . ((int) Session::get('totalItems') + 3), function ($cell) {
						$cell->setValue('TOTAL PRODUCTS-');
					});
					$sheet->cell('H' . ((int) Session::get('totalItems') + 3), function ($cell) {
						$cell->setValue(Session::get('totalItems'));
					});
					$sheet->cell('F' . ((int) Session::get('totalItems') + 4), function ($cell) {
						$cell->setValue('INCLUDING 3 % GST');
					});

					$sheet->fromArray($data);
				});
			})->download('xlsx');
		}
	}
	//GenerateMemo
	public function getProductIdsByOrder(Request $request) {
		$params = $request->post();
		$orderId = isset($params['orderId']) ? $params['orderId'] : '';
		$memoProducts = ApprovalMemoHistroy::select('product_id')->where('approval_memo_id', '=', DB::raw("$orderId"))->where('status', '=', DB::raw("'approval'"))->get();
		//$orderItems = InventoryHelper::getOrderItems($orderId);
		$productId = array();
		foreach ($memoProducts as $key => $memo) {
			$productId[] = $memo->product_id;
		}
		if (count($productId) > 0) {
			$response['status'] = true;
			$response['product_ids'] = implode(',', $productId);
		} else {
			$response['status'] = false;
		}
		echo json_encode($response);exit;
	}
	//Update invoice memo
	public function updateInvoiceMemo(Request $request) {
		$params = $request->post();
		$orderId = isset($params['order_id']) ? $params['order_id'] : '';
		$paymentData = array(
			"discount_value" => isset($params['txtdiscountval']) ? $params['txtdiscountval'] : '',
			"discount_type" => isset($params['discount_type']) ? $params['discount_type'] : '',
			"franchise_id" => isset($params['franchisee']) ? $params['franchisee'] : '',
			"franchise_name" => isset($params['franchisee_name']) ? $params['franchisee_name'] : '',
			"franchise_commision" => isset($params['txtfranchisecommission']) ? $params['txtfranchisecommission'] : '',
			'agent_name' => isset($params['txtagentname']) ? $params['txtagentname'] : '',
			"agent_commision" => isset($params['txtagentcommission']) ? $params['txtagentcommission'] : '',
			"payment_mode" => isset($params['paymentmode']) ? $params['paymentmode'] : '',
			"product_ids" => isset($params['productIds']) ? $params['productIds'] : '',
		);
		$emailAddress = !empty($params['txtemail']) ? $params['txtemail'] : $params['txtdmusercodeemail'];
		$firstName = $params['txtfirstname'];
		$lastName = $params['txtlastname'];
		$contactNumber = $params['txtcontactnumber'];
		$street = $params['txtaddress'];
		$countryId = $params['selectcountry'];
		$region = $params['txtstateprovince'];
		$city = $params['txtcity'];
		$postcode = $params['txtzipcode'];
		$password = $firstName . rand(10000, 999) . '@dealer';
		$operationType = $params['operation_type'];
		$customerId = '';
		$response = '';
		DB::setTablePrefix('');
		if (!empty($emailAddress)) {
			$customer = DB::table("customer_entity")->select('entity_id')->where('email', '=', DB::raw('"' . $emailAddress . '"'))->get()->first();
			if (count($customer)) {
				$customerId = isset($customer->entity_id) ? $customer->entity_id : '';
			} else {
				$userId = str_replace('dml', '', strtolower($emailAddress));
				$customer = DB::table("customer_entity")->select('entity_id')->where('entity_id', '=', DB::raw('"' . $userId . '"'))->get()->first();
				$customerId = isset($customer->entity_id) ? $customer->entity_id : '';
			}
		}

		if ($params['customerType'] == 'new') {
			/*Create Customer using API*/
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
			$customerParams = 'firstname=' . $firstName . '&lastname=' . $lastName . '&email=' . $emailAddress . '&contact_number=' . $contactNumber . '&community=' . '1' . '&street=' . $street . '&country_id=' . $countryId . '&region=' . $region . '&city=' . $city . '&entity_customer=' . '1' . '&postcode=' . $postcode . '&password=' . $password . '&confirmation=' . '1' . '&franchisee_status=' . '2';
			//echo "<pre>";
			//print_r($customerParams);exit;

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $customerParams);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$info = curl_getinfo($ch);

			$response = json_decode($result);

			if ($response->status == 'success') {
				$customerId = $response->customer_id;
			} else {
				echo $result;exit;
			}
			if (!empty($operationType) && $operationType == 'invoice') {
				$response = $this->generateInvoice($orderId, $customerId, $paymentData, $operationType);
			}
			/*else if (!empty($operationType) && $operationType == 'memo') {
				$response = $this->reGenerateMemo($orderId, $customerId, $paymentData,$operationType);
			}*/
		} else if (!empty($customerId) && $params['customerType'] == 'existing') {
			if (!empty($operationType) && $operationType == 'invoice') {
				$response = $this->reGenerateInvoice($orderId, $customerId, $paymentData, $operationType);
			}
			/*else if (!empty($operationType) && $operationType == 'memo') {
				$response = $this->reGenerateMemo($orderId, $customerId, $paymentData,$operationType);
			}*/
		} else if (empty($customerId) && $params['customerType'] == 'existing') {
			$response = json_encode(array('status' => false, 'message' => Config::get('constants.message.inventory_generate_invoicememo_customer_not_exist')));
		}
		DB::setTablePrefix('dml_');
		echo $response;exit;
	}
	public function removeMemoProduct(Request $request) {

		$params = $request->post();
		$productId = isset($params['productId']) ? $params['productId'] : '';
		if (!empty($productId)) {
			$affectedRows = ApprovalMemoHistroy::where('product_id', '=', $productId)->delete();
			$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
			$inStatusVal = $inventoryStatus['in'];
			$result = InventoryHelper::changeInventoryStatus($productId, $inStatusVal);
			if ($result) {
				$response['status'] = true;
				$response['message'] = config('constants.message.inventory_product_removed_from_memo');
			} else {
				$response['status'] = false;
				$response['message'] = config('constants.message.inventory_default_failure_message');
			}
		} else {
			$response['status'] = false;
			$response['message'] = config('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
		/*$orderId = isset($params['orderId']) ? $params['orderId'] : '';
			if (!empty($productId) && !empty($orderId)) {
				if (App::environment('local')) {
					$removeMemoProductUrl = Config::get('constants.apiurl.local.remove_memo_product');
				} else {
					$removeMemoProductUrl = Config::get('constants.apiurl.live.remove_memo_product');
				}

				$memoParams = 'productId=' . $productId . '&orderId=' . $orderId;
				$ch = curl_init($removeMemoProductUrl);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $memoParams);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				//$result = json_decode($result);

				Cache::forget('all_products_ajax');
				Cache::forget('all_products');
				echo $result;exit;
		*/
	}
	//Update generated invoice
	public function reGenerateInvoice($orderId, $customerId, $paymentData, $operationType) {
		$paymentData = json_encode($paymentData);

		$memoParams = 'orderId=' . $orderId . '&customerId=' . $customerId . '&paymentData=' . $paymentData . '&operationType=' . $operationType;
		if (App::environment('local')) {
			$reGenerateMemoUrl = Config::get('constants.apiurl.local.regenerate_invoice');
		} else if (App::environment('test')) {
			$reGenerateMemoUrl = Config::get('constants.apiurl.test.regenerate_invoice');
		} else {
			$reGenerateMemoUrl = Config::get('constants.apiurl.live.regenerate_invoice');
		}
		$ch = curl_init($reGenerateMemoUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $memoParams);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		//print_r($result);exit;
		$info = curl_getinfo($ch);
		return $result;
	}
	//Update generated memo
	public function reGenerateMemo($orderId, $customerId, $paymentData, $operationType) {
		$paymentData = json_encode($paymentData);
		$memoParams = 'orderId=' . $orderId . '&customerId=' . $customerId . '&paymentData=' . $paymentData . '&operationType=' . $operationType;
		if (App::environment('local')) {
			$reGenerateMemoUrl = Config::get('constants.apiurl.local.regenerate_memo');
		} else {
			$reGenerateMemoUrl = Config::get('constants.apiurl.live.regenerate_memo');
		}
		$ch = curl_init($reGenerateMemoUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $memoParams);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		//print_r($result);exit;
		$info = curl_getinfo($ch);
		return $result;
	}
	//View invoice
	public function viewInvoice($id) {
		if (!empty($id)) {
			DB::setTablePrefix('');
			$invoiceData = DB::table("sales_flat_order as main_table")
				->select("main_table.entity_id", "main_table.custom_discount_percent", "main_table.shipping_address_id", "main_table.billing_address_id", "main_table.isfranchisee", "main_table.dmlstore_order_increment_id", "main_table.franchise_order_increment_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total as order_total", "main_table.status", "main_table.transportation_mode", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total", "oinv.gst_percentage", "invoice_division.parent_customer_id", "invoice_division.child_customer_name", "invoice_division.child_customer_address", "invoice_division.child_customer_pan", "invoice_division.invoice_id", "oinv.invoice_shipping_charge")
				->where("qr_product_status", "=", DB::raw("'1'"))
			//->where('main_table.status', '=', DB::raw("'complete'"))
				->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
				->leftJoin('dml_invoice_customer_division as invoice_division', 'invoice_division.invoice_id', '=', 'oinv.entity_id')
				->join('sales_flat_invoice_item as oinv_item', 'oinv_item.parent_id', '=', 'oinv.entity_id')
				->leftJoin('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'oinv_item.product_id')
				->where("oinv.entity_id", "=", DB::raw("$id"))
				->orderBy("oinv.created_at", "desc")
				->groupBy('oinv_item.parent_id')
				->get()
				->first();

			//$order = InventoryHelper::getOrderData($id);
			return view('inventory.viewinvoice', compact('invoiceData'));
		}
	}
	//Cancel invoice
	public function cancelInvoice($id) {
		DB::setTablePrefix("");

		$request = new \Illuminate\Http\Request();
		$inventoryStatusOption = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0 AND EAOV.value='In'");
		$inventoryStatuaArr = array();
		foreach ($inventoryStatusOption as $key => $value) {
			$inventoryStatuaArr[strtolower($value->value)] = $value->option_id;
		}
		//Get order id from invoice
		$order = DB::select("SELECT order_id FROM sales_flat_invoice WHERE entity_id=" . DB::raw("$id") . "");
		$orderId = isset($order[0]->order_id) ? $order[0]->order_id : '';
		$result = DB::statement("UPDATE sales_flat_order set state='canceled',status='canceled' WHERE entity_id=" . DB::raw("$orderId"));
		if ($result) {
			$invoiceIncrementId = InventoryHelper::getInvoiceIncIdByInvoice($id);
			Payment::where('invoice_number', '=', DB::raw("'$invoiceIncrementId'"))->update(array('payment_status' => 2));

			//
		}
		//print_r($result);exit;
		if (App::environment('local')) {
			$IN = config('constants.apiurl.local.get_in');
		} else if (App::environment('test')) {
			$IN = config('constants.apiurl.test.get_in');
		} else {
			$IN = config('constants.apiurl.live.get_in');
		}
		$orderItems = InventoryHelper::getOrderItems($orderId);
		foreach ($orderItems as $key => $item) {
			if (isset($item->product_id)) {
				$instatusvalue = '';
				$inventorystatus = '';
				$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
				$inventorystatus = $inventoryStatus['in'];
				if (isset($inventoryStatuaArr['in'])) {
					$instatusvalue = $IN;
				} else {
					$instatusvalue = $IN;
				}
				$updateStatus = DB::statement("UPDATE catalog_product_flat_1 SET inventory_status='" . $inventorystatus . "',inventory_status_value='" . $instatusvalue . "', approval_invoice_generated=0,approval_memo_generated=0,return_memo_generated=0,is_sold=0 WHERE entity_id=" . DB::raw("$item->product_id"));
				$updateQrProduct = DB::statement("UPDATE qrcode_inventory_management SET inventory_status='in' WHERE pr_id=" . DB::raw("$item->product_id"));
				Cache::forget('all_products_ajax');
				Cache::forget('all_products');
			}
		}
		//insert into invoice_logs
		$invoiceProducts = InvoiceProducts::select('*')->where("invoice_id", "=", DB::raw("$id"))->get();
		$products = array();
		foreach ($invoiceProducts as $key => $product) {
			$products[]['product_id'] = $product->product_id;
			$products[]['metal_weight'] = $product->metal_weight;
			$products[]['stone_weight'] = $product->stone_weight;
			$products[]['unit_price'] = $product->unit_price;
		}
		InvoiceLogs::create(
			array(
				'user_id' => Auth::user()->id,
				'invoice_id' => $id,
				'product_data' => json_encode($products),
				'comment' => config('constants.message.invoice_cancelled'),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			)
		);
		DB::setTablePrefix("dml_");
		if ($result) {
			//Get invoice amount
			$invoiceAmount = PaymentTransaction::select('invoice_amount')->where('invoice_number', '=', DB::raw("'$invoiceIncrementId'"))->get()->first();

			$invoiceAmount = isset($invoiceAmount->invoice_amount) ? $invoiceAmount->invoice_amount : 0;
			//Get customer id by invoice
			DB::setTablePrefix('');
			$customer = DB::table('sales_flat_order as ord')->select('ord.customer_id')->join('sales_flat_invoice as invoice', 'invoice.order_id', '=', 'ord.entity_id')->where('invoice.entity_id', '=', DB::raw("$id"))->get()->first();
			$customerId = isset($customer->customer_id) ? $customer->customer_id : '';
			DB::setTablePrefix('dml_');

			if ($invoiceAmount > 0) {
				$lastData = CashVoucher::orderBy('created_at', 'desc')->select('voucher_number')->get()->first();

				$voucherNumberConfigData = Setting::where('key', config('constants.settings.keys.voucher_number'))->first('value');
				$voucherSerialNo = isset($voucherNumberConfigData->value) ? $voucherNumberConfigData->value : '';

				if (empty($lastData->voucher_number) || ((int) $voucherSerialNo) > (int) $lastData->voucher_number) {
					$voucherNumberConfigData = Setting::where('key', config('constants.settings.keys.voucher_number'))->first('value');

					$voucherSerialNo = isset($voucherNumberConfigData->value) ? $voucherNumberConfigData->value : '';

				} else {
					$voucherSerialNo = isset($lastData->voucher_number) ? $lastData->voucher_number + 1 : '';
				}

				//Generate cash voucher
				CashVoucher::create(array(
					'customer_id' => $customerId,
					'invoice_id' => $id,
					'invoice_amount' => $invoiceAmount,
					'voucher_number' => $voucherSerialNo,
					'created_at' => date('Y-m-d'),
					'updated_at' => date('Y-m-d'),
				));
				Setting::where('key', '=', config('constants.settings.keys.voucher_number'))->update(array('value' => $voucherSerialNo));
				$voucherId = DB::getPdo()->lastInsertId();
			}

			//InventoryHelper::generateCashVoucher($voucherId);
			return redirect()->back()->with('success', config('constants.message.invoice_cancellation_success'));
		} else {
			return redirect()->back()->with('error', config('constants.message.invoice_cancellation_failure'));
		}
	}
	//Export return memo product csv
	public function exportReturnmemoProductCsv() {
		$returnMemoData = ReturnMemo::select('*')->where('status', '=', DB::raw("1"))->orderBy('created_at', 'DESC')->get();
		$csvData = array();
		//DB::setTablePrefix('');
		foreach ($returnMemoData as $key => $returnMemo) {
			$qty = 0;

			$customerBillingAddress = InventoryHelper::getDefaultBillingAddressByCustId($returnMemo->customer_id);
			$customerName = '';
			if (isset($customerBillingAddress['firstname']) && isset($customerBillingAddress['lastname'])) {
				$customerName = $customerBillingAddress['firstname'] . $customerBillingAddress['lastname'];
			}

			$street = isset($customerBillingAddress['street']) ? $customerBillingAddress['street'] : '';
			$city = isset($customerBillingAddress['city']) ? ", " . $customerBillingAddress['city'] : '';
			$region = isset($customerBillingAddress['region']) ? ", " . $customerBillingAddress['region'] : '';
			$postcode = isset($customerBillingAddress['postcode']) ? ", " . $customerBillingAddress['postcode'] : '';
			$telephone = isset($customerBillingAddress['telephone']) ? $customerBillingAddress['telephone'] : '';
			$gstin = isset($customerBillingAddress['gstin']) ? $customerBillingAddress['gstin'] : '';
			$customerAddress = $street . $city . $region . $postcode;

			$franchiseName = isset($returnMemo->franchise_name) ? $returnMemo->franchise_name : '';
			$dmlUserCode = 'DML' . isset($returnMemo->customer_id) ? $returnMemo->customer_id : '';

			$currentYear = date('y', strtotime($returnMemo->created_at));
			$returnMemoNumber = isset($returnMemo->return_number) ? $returnMemo->return_number : '';
			if (date('m') > 6) {
				$fin_year = date('y') . '-' . (date('y') + 1);
			} else {
				$fin_year = (date('y') - 1) . '-' . date('y');
			}
			$returnMemoNumber = $fin_year . '/' . $returnMemoNumber;
			$productData = isset($returnMemo->product_data) ? json_decode($returnMemo->product_data) : '';
			foreach ($productData as $key => $product) {
				$qty += isset($product->qty) ? $product->qty : 0;
			}
			$createdDate = date("d-M-y", strtotime($returnMemo->created_at));
			$grandTotalData = isset($returnMemo->grand_total_data) ? json_decode($returnMemo->grand_total_data) : array();
			if (!empty($grandTotalData)) {
				$grandTotal = isset($grandTotalData->price) ? ShowroomHelper::currencyFormat(round($grandTotalData->price)) : '';
			}
			$memoNumber = isset($returnMemo->approval_memo_number) ? $returnMemo->approval_memo_number : '';
			$csvData[] = array(
				'Invoice No.' => $returnMemoNumber,
				'Name' => $customerName,
				'DMUSERCODE' => $dmlUserCode,
				'Address' => $customerAddress,
				'GSTIN' => $gstin,
				'Qty' => $qty,
				'Date' => $createdDate,
				'Grand Total' => $grandTotal,
				'Memo No.' => $memoNumber,
			);
		}
		$row = 0;
		return \Excel::create('Return_Memo_Products', function ($excel) use ($csvData) {
			$excel->sheet('Sheet', function ($sheet) use ($csvData) {
				$sheet->fromArray($csvData);
			});
		})->download('csv');
		DB::setTablePrefix('dml_');
	}
	//Export memo products csv
	public function exportMemoProductsCsv() {
		DB::setTablePrefix('');
		$memoData = DB::table('dml_approval_memo as memo')->select('memo.id', 'memo.customer_id', 'memo.approval_no', 'memo.created_at', DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join('dml_approval_memo_histroy as memo_histroy', 'memo.id', '=', 'memo_histroy.approval_memo_id')->join('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'memo_histroy.product_id')->where('memo_histroy.status', '=', DB::raw("'approval'"))->groupBy('memo_histroy.approval_memo_id')->get();
		$csvData = array();

		foreach ($memoData as $key => $memo) {
			$customerId = isset($memo->customer_id) ? $memo->customer_id : '';
			$customerBillngAddress = InventoryHelper::getDefaultBillingAddressByCustId($customerId);

			$street = isset($customerBillngAddress['street']) ? $customerBillngAddress['street'] : '';
			$city = isset($customerBillngAddress['city']) ? ", " . $customerBillngAddress['city'] : '';
			$region = isset($customerBillngAddress['region']) ? ", " . $customerBillngAddress['region'] : '';
			$postcode = isset($customerBillngAddress['postcode']) ? ", " . $customerBillngAddress['postcode'] : '';
			$gstin = isset($customerBillngAddress['gstin']) ? $customerBillngAddress['gstin'] : '';
			$customerAddress = $street . $city . $region . $postcode;
			$orderId = isset($memo->id) ? $memo->id : '';
			$currentYear = date('y');
			if (date('m') > 6) {
				$fin_year = date('y') . '-' . (date('y') + 1);
			} else {
				$fin_year = (date('y') - 1) . '-' . date('y');
			}
			$memoNumber = isset($memo->approval_no) ? $fin_year . '/' . $memo->approval_no : '';
			$customerName = InventoryHelper::getCustomerName($customerId);
			$dmlUserCode = 'DML' . $customerId;
			$memoProductsIds = DB::table('approval_memo_histroy')->select('product_id')->where('approval_no', '=', DB::raw("'$memo->approval_no'"))->get();
			$productIds = array();

			foreach ($memoProductsIds as $productId) {
				$productIds[] = $productId->product_id;
			}
			$totalProductCount = count($productIds);
			//echo $totalProductCount;exit;
			$qty = $totalProductCount;
			$orderDate = isset($memo->created_at) ? date('d-M-y', strtotime($memo->created_at)) : '';
			$productIds = explode(',', $memo->product_ids);

			$grandTotal = 0;
			$productIds = array_filter($productIds);
			/* echo "<pre>";
			print_r($productIDs);exit; */
			foreach ($productIds as $key => $productId) {
				DB::setTablePrefix('');
				$product = DB::table('catalog_product_flat_1')->select('custom_price')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
				//echo $product->custom_price;exit;
				$grandTotal += (float) $product->custom_price;
			}
			//$grandTotal = isset($order->grand_total) ? ShowroomHelper::currencyFormat(round($order->grand_total)) : '';
			$csvData[] = array(
				'Memo No.' => $memoNumber,
				'Name' => $customerName,
				'DMUSERCODE' => $dmlUserCode,
				'Address' => $customerAddress,
				'GSTIN' => $gstin,
				'Qty' => $qty,
				'Date' => $orderDate,
				'Grand Total' => $grandTotal,
			);
		}
		$row = 0;
		DB::setTablePrefix("dml_");
		return \Excel::create('Approval_Memo_Products', function ($excel) use ($csvData) {
			$excel->sheet('Sheet', function ($sheet) use ($csvData) {
				$sheet->fromArray($csvData);
			});
		})->download('csv');

	}
	//Export product csv
	public function exportCsv() {
		DB::setTablePrefix("");
		$orderCollection = DB::table("sales_flat_order as main_table")
			->select("*")
			->where("status", "=", DB::raw("'pending'"))
			->where("qr_product_status", "=", DB::raw("'1'"))
			->orderBy("entity_id", "DESC")
			->get();
		$csvData = array();
		foreach ($orderCollection as $key => $order) {
			$customerId = isset($order->customer_id) ? $order->customer_id : '';
			$customerBillngAddress = InventoryHelper::getDefaultBillingAddressByCustId($customerId);

			$street = isset($customerBillngAddress['street']) ? $customerBillngAddress['street'] : '';
			$city = isset($customerBillngAddress['city']) ? ", " . $customerBillngAddress['city'] : '';
			$region = isset($customerBillngAddress['region']) ? ", " . $customerBillngAddress['region'] : '';
			$postcode = isset($customerBillngAddress['postcode']) ? ", " . $customerBillngAddress['postcode'] : '';
			$gstin = isset($customerBillngAddress['gstin']) ? $customerBillngAddress['gstin'] : '';
			$customerAddress = $street . $city . $region . $postcode;
			$orderId = isset($order->entity_id) ? $order->entity_id : '';
			$memoNumber = isset($order->approval_memo_number) ? $order->approval_memo_number : '';
			$customerName = $order->customer_firstname . ' ' . $order->customer_lastname;
			$dmlUserCode = 'DML' . $customerId;
			$qty = isset($order->total_qty_ordered) ? $order->total_qty_ordered : 0;
			$orderDate = isset($order->created_at) ? date('d-M-y', strtotime($order->created_at)) : '';
			$grandTotal = isset($order->grand_total) ? ShowroomHelper::currencyFormat(round($order->grand_total)) : '';
			$csvData[] = array(
				'Memo No.' => $memoNumber,
				'Name' => $customerName,
				'DMUSERCODE' => $dmlUserCode,
				'Address' => $customerAddress,
				'GSTIN' => $gstin,
				'Qty' => $qty,
				'Date' => $orderDate,
				'Grand Total' => $grandTotal,
			);
		}
		$row = 0;
		return \Excel::create('Approval_Memo_Products', function ($excel) use ($csvData) {
			$excel->sheet('Sheet', function ($sheet) use ($csvData) {
				$sheet->fromArray($csvData);
			});
		})->download('csv');
		DB::setTablePrefix("dml_");
	}
	//Get canceled invoice list
	public function getCanceledInvoice() {
		DB::setTablePrefix('');
		$generatedInvoiceList = DB::table("sales_flat_order as main_table")
			->select("main_table.entity_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total as order_total", "main_table.status", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total", "invoice_division.parent_customer_id", "invoice_division.child_customer_name", "invoice_division.child_customer_address", "invoice_division.child_customer_pan", "invoice_division.invoice_id")
			->where("qr_product_status", "=", DB::raw("'1'"))
			->where('main_table.status', '=', DB::raw("'canceled'"))
			->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
			->leftJoin('dml_invoice_customer_division as invoice_division', 'invoice_division.invoice_id', '=', 'oinv.entity_id')
			->join('sales_flat_invoice_item as oinv_item', 'oinv_item.parent_id', '=', 'oinv.entity_id')
			->leftJoin('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'oinv_item.product_id')
			->orderBy("oinv.created_at", "desc")
			->groupBy('oinv_item.parent_id')
			->get();
		DB::setTablePrefix('dml_');
		$invoiceType = 'canceled';
		return view('inventory.invoicelist', compact('generatedInvoiceList', 'invoiceType'));
	}
	//Get completed invoice list
	public function getCompletedInvoice() {
		DB::setTablePrefix('');
		$generatedInvoiceList = DB::table("sales_flat_order as main_table")
			->select("main_table.entity_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total as order_total", "main_table.status", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total", "invoice_division.parent_customer_id", "invoice_division.child_customer_name", "invoice_division.child_customer_address", "invoice_division.child_customer_pan", "invoice_division.invoice_id")
			->where("qr_product_status", "=", DB::raw("'1'"))
			->where('main_table.status', '=', DB::raw("'complete'"))
			->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
			->leftJoin('dml_invoice_customer_division as invoice_division', 'invoice_division.invoice_id', '=', 'oinv.entity_id')
			->join('sales_flat_invoice_item as oinv_item', 'oinv_item.parent_id', '=', 'oinv.entity_id')
			->leftJoin('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'oinv_item.product_id')
			->orderBy("oinv.created_at", "desc")
			->groupBy('oinv_item.parent_id')
			->get();
		DB::setTablePrefix('dml_');
		$invoiceType = 'completed';
		return view('inventory.invoicelist', compact('generatedInvoiceList', 'invoiceType'));
	}
	//Get return memo list
	public function returnMemoList() {
		$returnMemoList = ReturnMemo::select('*')->where('status', '=', DB::raw("1"))->orderBy('created_at', 'DESC')->get();
		return view('inventory.returnmemolist', compact('returnMemoList'));
	}
	public function viewReturnMemo($id) {
		$returnMemo = ReturnMemo::select('*')->where('status', '=', DB::raw("1"))->where('id', '=', $id)->orderBy('created_at', 'DESC')->get()->first();
		return view('inventory.viewreturnmemo', compact('returnMemo'));
	}
	//Download return memo product excel
	public function downloadReturnMemoProduct($id) {
		if (!empty($id)) {
			//DB::setTablePrefix('');
			$returnMemo = ReturnMemo::select('*')->where('id', '=', DB::raw("$id"))->get()->first();
			$productData = isset($returnMemo->product_data) ? json_decode($returnMemo->product_data) : array();
			$totalMetalWeight = 0;
			$totalStoneWeight = 0;
			$totalPrice = 0;
			$data = array();
			foreach ($productData as $key => $product) {
				$productId = isset($product->productid) ? $product->productid : '';
				$productInfo = InventoryHelper::getProductData($productId);
				$metalData = ShowroomHelper::getMetalData($productId, $productInfo);
				$metalWeight = isset($metalData['weight']) ? $metalData['weight'] : 0;
				$stone = isset($productInfo->rts_stone_quality) ? $productInfo->rts_stone_quality : '';
				$stoneData = InventoryHelper::getStoneData($productId);
				$stoneWeight = isset($stoneData['totalweight'][0]) ? $stoneData['totalweight'][0] : 0;
				$sku = isset($productInfo->sku) ? $productInfo->sku : '';
				$skuArr = explode(" ", $sku);
				$metalQuality = isset($skuArr[1]) ? $skuArr[1] : '';
				$certificateNo = isset($productInfo->certificate_no) ? $productInfo->certificate_no : '';
				$rtsStoneQuality = isset($productInfo->rts_stone_quality) ? $productInfo->rts_stone_quality : '';
				$price = isset($productInfo->custom_price) ? $productInfo->custom_price : 0;
				$attributeSetId = isset($productInfo->attribute_set_id) ? $productInfo->attribute_set_id : '';
				DB::setTablePrefix('');
				$attributeSetData = DB::table("eav_attribute_set")->select("attribute_set_name")->where("attribute_set_id", "=", DB::raw("$attributeSetId"))->get()->first();
				$productType = isset($attributeSetData->attribute_set_name) ? $attributeSetData->attribute_set_name : '';
				$totalMetalWeight += $metalWeight;
				$totalStoneWeight += $stoneWeight;
				$totalPrice += $price;
				$data[] = array(
					'CERTIFICATE NO.' => $certificateNo,
					'SKU' => $sku,
					'PRODUCT TYPE' => $productType,
					'METAL GROSS' => $metalWeight,
					'METAL QUALITY' => $metalQuality,
					'TOTAL DIAMOND WT' => $stoneWeight,
					'DIAMOND QUALITY' => $rtsStoneQuality,
					'FINAL PRICE' => round($price),
				);
			}
			$row = 0;
			$totalItems = count($data);
			Session::put('totalItems', $totalItems);
			Session::put('totalMetalWeight', $totalMetalWeight);
			Session::put('totalStoneWeight', $totalStoneWeight);
			Session::put('totalPrice', $totalPrice);
			Session::save();
			return \Excel::create('products', function ($excel) use ($data) {
				$excel->sheet('Sheet', function ($sheet) use ($data) {
					$sheet->cell('B' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue('Grand Total');
					});
					$sheet->cell('C' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalItems'));
					});
					$sheet->cell('D' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalMetalWeight'));
					});
					$sheet->cell('F' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalStoneWeight'));
					});
					$sheet->cell('H' . ((int) Session::get('totalItems') + 2), function ($cell) {
						$cell->setValue(Session::get('totalPrice'));
					});
					$sheet->cell('G' . ((int) Session::get('totalItems') + 3), function ($cell) {
						$cell->setValue('TOTAL PRODUCTS-');
					});
					$sheet->cell('H' . ((int) Session::get('totalItems') + 3), function ($cell) {
						$cell->setValue(Session::get('totalItems'));
					});
					$sheet->cell('F' . ((int) Session::get('totalItems') + 4), function ($cell) {
						$cell->setValue('INCLUDING 3 % GST');
					});
					$sheet->fromArray($data);
				});
			})->download('xlsx');
			DB::setTablePrefix('dml_');
		}
	}
	//Download return memo product excel
	public function downloadProductExcel($id) {
		if (!empty($id)) {
			//DB::setTablePrefix('');
			$returnMemo = ReturnMemo::select('*')->where('id', '=', DB::raw("$id"))->get()->first();
			$productData = isset($returnMemo->product_data) ? json_decode($returnMemo->product_data) : array();
			$serialNumber = 0;
			$imageDirectory = config('constants.dir.website_url_for_product_image_curl');
			$defaultProductImage = $imageDirectory . 'def_1.png';

			foreach ($productData as $key => $products) {
				DB::setTablePrefix('');
				$productId = $products->productid;
				$serialNumber++;
				$productCollection = DB::table("catalog_product_flat_1")->select("*")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
				$product = $productCollection;
				$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				$productImage = (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage);
				//$imageData = ShowroomHelper::file_get_contents_curl($productImage);
				$ext = pathinfo($productImage, PATHINFO_EXTENSION);
				if (!file_exists(public_path('img/product'))) {
					mkdir(public_path('img/product'), 0777, true);
				}
				$file = 'img/product/product' . $product->entity_id . '.' . $ext;
				//file_put_contents( $file, $imageData );
				if (InventoryHelper::isImageExist($productImage) == '200') {
					$curlCh = curl_init();
					curl_setopt($curlCh, CURLOPT_URL, $productImage);
					curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curlCh, CURLOPT_BINARYTRANSFER, 1);
					curl_setopt($curlCh, CURLOPT_SSLVERSION, 3);
					$curlData = curl_exec($curlCh);
					curl_close($curlCh);

					$imgfile = fopen($file, "w+");
					fputs($imgfile, $curlData);
					fclose($imgfile);
				} else {
					$file = 'img/def_img.png';
				}
				/* $ch = curl_init($productImage);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
					$fileraw = curl_exec($ch);
					curl_close ($ch);
					if(file_exists($file)){
					    unlink($file);
					}
					$fp = fopen($file,'x');
					fwrite($fp, $fileraw);
				*/
				$sku = $product->sku;
				$certificateNo = (!empty($product->certificate_no) ? $product->certificate_no : 'N/A');
				$inventoryStatus = isset($product->inventory_status) ? $product->inventory_status : '';
				$inventoryStatusAttr = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0 AND EAOV.option_id=" . DB::raw("$inventoryStatus"));
				$inventoryStatus = isset($inventoryStatusAttr[0]->value) ? $inventoryStatusAttr[0]->value : '';
				$metalQualityOption = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_quality' AND EAOV.store_id = 0");
				$metalQuality = '';
				foreach ($metalQualityOption as $key => $metal) {
					if ($metal->option_id == $product->metal_quality) {
						$metalQuality = $metal->value;
					}
				}
				DB::setTablePrefix('');
				$metalProductData = DB::table("grp_metal")->select("*")->where("metal_product_id", "=", DB::raw("$product->entity_id"))->get()->first();
				$metalWeight = isset($metalProductData->metal_weight) ? $metalProductData->metal_weight : '';
				$updatedPrice = InventoryHelper::getMetalRingSize($product->entity_id, $product->rts_ring_size);
				$productOptions = InventoryHelper::getProductOptions($product->entity_id);
				$labour = "0.00";
				foreach ($productOptions as $key => $option) {
					if ($option->type != 'drop_down') {
						$values = InventoryHelper::getOptionValue($option->option_id);
						foreach ($values as $key => $value) {
							if ($value->title == $product->rts_ring_size) {
								$labour = $value->metal_labour_charge + $value->metal_product_price;
							}
						}
					}
				}
				$stone = $product->rts_stone_quality;
				$stoneData = InventoryHelper::getStoneData($product->entity_id);
				$gemStoneData = InventoryHelper::getGemStoneData($product->entity_id);
				$stoneCaret = isset($stoneData['stone']) ? $stoneData['stone'] : '';
				$stoneTotal = isset($stoneData['totalcts']) ? $stoneData['totalcts'] : 0;
				$gemStone = isset($gemStoneData['simple']) ? $gemStoneData['simple'] : 0;
				if ($labour == '0.00') {
					$metalData = ShowroomHelper::getMetalData($product->entity_id, $product);
					$productPrice = isset($metalData['simple']) ? $metalData['simple'] : 0;
				} else {
					$productPrice = $labour;
				}
				$mainPrice = $productPrice + $metalData['simple'] + $gemStone;
				$vars = array('price' => $mainPrice, 'caratname' => $stoneCaret, 'totalcarets' => $stoneTotal);
				//$stonedata = ShowroomHelper::getSideStoneData($product->entity_id, '');
				$updatedPrice = InventoryHelper::getMetalRingSize($product->entity_id, $product->rts_ring_size);
				if ($updatedPrice) {
					$updatedWeight = $updatedPrice;
				} else {
					$updatedWeight = $metalData['weight'];
				}
				$metalPrice = isset($metalData->metalprice_value) ? $metalData->metalprice_value : 0;
				$matelRate = round($updatedWeight * str_replace('Rs', '', preg_replace('/[^A-Za-z0-9]/', "", $metalData['per-gm-rate'])));
				$metal_data = array();
				$metalTypeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_type' AND EAOV.store_id = 0");
				$metalType = array();
				foreach ($metalTypeDetails as $key => $metal) {
					$metalType[$metal->option_id] = $metal->value;
				}

				$metalTypeValue = $metalType[$metalProductData->metal_type_id];
				$metal_data['type'] = $metalTypeValue;
				$shapeSideStoneData = DB::select("SELECT * FROM `grp_stone` WHERE stone_product_id=" . $product->entity_id);
				$shape = sizeof($shapeSideStoneData);
				$stoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
				$stoneShape = array();
				foreach ($stoneShapeDetails as $key => $stone) {
					$stoneShape[$stone->option_id] = $stone->value;
				}
				$isRound = false;
				$isMarque = false;
				foreach ($shapeSideStoneData as $key => $stone_data) {
					$shapeTypeData[] = $stoneShape[$stone_data->stone_shape];
					$label = $stoneShape[$stone_data->stone_shape];
					if ($label == 'ROUND') {
						$isRound = true;
					} else {
						$isMarque = true;
					}
				}
				$categries = DB::select("SELECT `main_table`.`entity_id`, `main_table`.`level`, `main_table`.`path`, `main_table`.`position`, `main_table`.`is_active`, `main_table`.`is_anchor`, `main_table`.`entity_id` FROM `catalog_category_flat_store_1` AS `main_table` WHERE (is_active = '1')");
				$allCatIds = array();
				foreach ($categries as $getAllCatIds) {
					$allCatIds[] = $getAllCatIds->entity_id;
				}
				$categoryId = ShowroomHelper::getCategoryIds($product->entity_id);
				$children = DB::select("SELECT catmaster.entity_id FROM `catalog_category_entity` as catmaster left join catalog_category_entity_varchar as catvarchar ON catvarchar.entity_id = catmaster.entity_id WHERE catmaster.`parent_id` = 124");
				$childCatId = array();
				foreach ($children as $key => $value) {
					$childCatId[] = $value->entity_id;
				}
				$childCatId = array_unique($childCatId);
				//var_dump($childCatId);exit;
				//var_dump($categoryId);exit;
				$rubber_category_arr = array(293);
				$rubber_arrkey = array_search('293', $childCatId);
				if (!empty($rubber_arrkey)) {
					unset($childCatId[$rubber_arrkey]);
				}
				$rubber_intersect = array_intersect($rubber_category_arr, $allCatIds);
				$parentCategory = array(124);
				$finalCategoryList = array_merge($childCatId, $parentCategory);
				$result_intersect = array_intersect($finalCategoryList, $allCatIds);
				$counts = array_count_values($shapeTypeData);
				$category = DB::select("SELECT catmaster.entity_id FROM `catalog_category_entity` as catmaster left join catalog_category_entity_varchar as catvarchar ON catvarchar.entity_id = catmaster.entity_id WHERE catmaster.`parent_id` = 293");
				//var_dump($allCatIds);exit;
				$categoryName = ShowroomHelper::getCategoryName(isset($category[0]->entity_id) ? $category[0]->entity_id : '');
				$beltPrice = 0;
				$extraPrice = 0;
				$matelRate = 0;
				$labour_charge = 0;
				if (in_array("293", $allCatIds) && ($categoryName) == 'RUBBER BRACELETS') {
					$metalTypeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_type' AND EAOV.store_id = 0");
					$metalType = array();
					foreach ($metalTypeDetails as $key => $metal) {
						$metalType[$metal->option_id] = $metal->value;
					}
					$customDiamondShape = '';
					$categoryBracelets = '';
					$customMetal = '';
					foreach ($metalProductData as $key => $value) {
						$metalTypeValue = $metalType[$value->metal_type_id];
						if (strtolower($metalTypeValue) == 'gold') {
							if ($isRound && !$isMarque) {
								$customDiamondShape = 1;
							} else if ($isMarque && !$isRound) {
								$customDiamondShape = 2;
							} else if ($isRound && $isMarque) {
								$customDiamondShape = 2;
							} else {
								$customDiamondShape = 2;
							}
							if (strtolower($metalTypeValue) == 'gold') {
								$customMetal = 1;
							} else {
								$customMetal = 3;
							}
							if (count($result_intersect) > 0) {
								$categoryBracelets = 2;
							} elseif (count($rubber_intersect) > 0) {
								$categoryBracelets = 3;
							} else {
								$categoryBracelets = 1;
							}
							$customLabourChargeData = DB::select("SELECT * FROM grp_custom_labour_charges WHERE type=" . $customMetal . " AND product_type=" . $categoryBracelets . " AND diamond_type=" . $customDiamondShape . " AND to_mm>=" . (float) $updatedWeight . " AND from_mm<=" . (float) $updatedWeight . " ORDER BY CAST(to_mm AS DECIMAL), CAST(from_mm AS DECIMAL )");
							foreach ($customLabourChargeData as $labourChargedata) {
								$goldDefaultMetalRate = $labourChargedata->labour_charge;
							}
						} else if (strtolower($metalTypeValue) == 'platinum(950)') {
							if ($isRound && !$isMarque) {
								$customDiamondShape = 1;
							} else if ($isMarque && !$isRound) {
								$customDiamondShape = 2;
							} else if ($isRound && $isMarque) {
								$customDiamondShape = 2;
							} else {
								$customDiamondShape = 2;
							}

							if (strtolower($MetalTypeValue) == 'platinum(950)') {
								$customMetal = 3;
							} else {
								$customMetal = 1;
							}

							if (count($result_intersect) > 0) {
								$categoryBracelets = 2;
							} elseif (count($rubber_intersect) > 0) {
								$categoryBracelets = 3;
							} else {
								$categoryBracelets = 1;
							}
							$customLabourChargeData = DB::select("SELECT * FROM grp_custom_labour_charges WHERE type=" . $customMetal . " AND product_type=" . $categoryBracelets . " AND diamond_type=" . $customDiamondShape . " AND to_mm>=" . (float) $updatedWeight . " AND from_mm<=" . (float) $updatedWeight . " ORDER BY CAST(to_mm AS DECIMAL), CAST(from_mm AS DECIMAL )");
							foreach ($customLabourChargeData as $labourChargedata) {
								$goldDefaultMetalRate = $labourChargedata->labour_charge;
							}
						}
					}
					$beltPrice = isset($prduct->belt_price) ? $prduct->belt_price : '';
				} elseif (strtolower($metalTypeValue) == 'gold') {
					$metalTypeData = DB::select("SELECT * FROM grp_metal_type WHERE metal_type='Gold'");
					if ($isRound && !$isMarque) {
						$customDiamondShape = 1;
					} else if ($isMarque && !$isRound) {
						$customDiamondShape = 2;
					} else if ($isRound && $isMarque) {
						$customDiamondShape = 2;
					} else {
						$customDiamondShape = 2;
					}
					if (isset($metalTypeData[0]->metal_type)) {
						if (strtolower($metalTypeData[0]->metal_type) == 'gold') {
							$customMetal = 1;
						} else {
							$customMetal = 3;
						}
					}
					if (count($result_intersect) > 0) {
						$categoryBracelets = 2;
					} elseif (count($rubber_intersect) > 0) {
						$categoryBracelets = 3;
					} else {
						$categoryBracelets = 1;
					}
					$customLabourChargeData = DB::select("SELECT * FROM grp_custom_labour_charges WHERE type=" . $customMetal . " AND product_type=" . $categoryBracelets . " AND diamond_type=" . $customDiamondShape . " AND to_mm>=" . (float) $updatedWeight . " AND from_mm<=" . (float) $updatedWeight . " ORDER BY CAST(to_mm AS DECIMAL), CAST(from_mm AS DECIMAL )");
					foreach ($customLabourChargeData as $labourChargedata) {
						$goldDefaultMetalRate = $labourChargedata->labour_charge;
					}

				} else if (strtolower($metalTypeValue) == 'platinum(950)') {
					$metalTypeData = DB::select("SELECT * FROM grp_metal_type WHERE metal_type='Platinum(950)'");
					if ($isRound && !$isMarque) {
						$customDiamondShape = 1;
					} else if ($isMarque && !$isRound) {
						$customDiamondShape = 2;
					} else if ($isRound && $isMarque) {
						$customDiamondShape = 2;
					} else {
						$customDiamondShape = 2;
					}
					if (isset($metalTypeData[0]->metal_type)) {
						if (strtolower($metalTypeData[0]->metal_type) == 'platinum(950)') {
							$customMetal = 3;
						} else {
							$customMetal = 1;
						}
					}
					if (count($result_intersect) > 0) {
						$categoryBracelets = 2;
					} elseif (count($rubber_intersect) > 0) {
						$categoryBracelets = 3;
					} else {
						$categoryBracelets = 1;
					}
					$customLabourChargeData = DB::select("SELECT * FROM grp_custom_labour_charges WHERE type=" . $customMetal . " AND product_type=" . $categoryBracelets . " AND diamond_type=" . $customDiamondShape . " AND to_mm>=" . (float) $updatedWeight . " AND from_mm<=" . (float) $updatedWeight . " ORDER BY CAST(to_mm AS DECIMAL), CAST(from_mm AS DECIMAL )");
					foreach ($customLabourChargeData as $labourChargedata) {
						$goldDefaultMetalRate = $labourChargedata->labour_charge;
					}
				}
				if ($updatedWeight < 1) {
					$labour_charge = round($goldDefaultMetalRate * 1);
				} else {
					$labour_charge = round($goldDefaultMetalRate * $updatedWeight);
				}
				// $extraPrice = isset($product->extra_price) ? $product->extra_price : '';
				$estimateValue = (float) $labour_charge + (float) $matelRate + (float) $beltPrice; // + (float) $extraPrice;

				$stonePrice = str_replace('Rs', '', preg_replace('/[^A-Za-z0-9]/', "", $stoneData['stone_price']['0']));
				$finaltotal = $mainPrice + $stonePrice;
				$quality = $product->rts_stone_quality;
				$finalTotal = array_sum(str_replace('Rs', '', preg_replace('/[^A-Za-z0-9]/', "", $stoneData['stone_price'])));
				$productWithOption = array();
				foreach ($stoneData as $key => $stone) {
					if ($key == "type" || $key == "shape" || $key == "setting" || $key == "stone_use" || $key == "totalcts" || $key == "percts" || $key == "stone_price" || $key == "stoneclarity") {
						$sizeof = count($stone);
						$productWithOption[0][$key] = $stoneData[$key][0];
						for ($st = 0; $st < $sizeof; $st++) {
							$productWithOption[$st][$key] = $stoneData[$key][$st];
						}
					}
				}
				$stoneShape = array();
				$stoneClarity = array();
				$stonePices = array();
				$stoneWeight = array();
				$caratPrice = array();
				foreach ($productWithOption as $optionData) {
					$stoneShape[] = ucwords(strtolower($optionData['shape']));
					if (!empty($optionData['stoneclarity'])) {
						$stoneclarity[] = $optionData['stoneclarity'];
					} else {
						$stoneclarity[] = $quality;
					}
					$stonePices[] = $optionData['stone_use'];
					$stoneWeight[] = $optionData['totalcts'];
					$caratPrice[] = preg_replace("/[^0-9]/", "", $optionData['stone_price']);
				}
				$totalProductPrice = $stonePrice + round($metalPrice) + round($labour_charge) + $gemStone;
				$implodedStoneShape = implode(",", $stoneShape);
				$implodedStoneClarity = implode(",", $stoneClarity);
				$implodedStonePices = implode(",", $stonePices);
				$implodedStoneweight = implode(",", $stoneWeight);
				$implodedCaratPrice = implode(",", $caratPrice);
				$price = $product->custom_price;

				$maxStoneCount = max(count($stoneShape), count($stoneClarity), count($stonePices), count($stoneWeight), count($caratPrice));
				for ($index = 0; $index < $maxStoneCount; $index++) {
					$data[] = array(
						'Sr No.' => ($index == 0) ? $serialNumber : '',
						'Image' => ($index == 0) ? $file : '',
						'Name' => ($index == 0) ? $product->name : '',
						'SKU' => ($index == 0) ? $sku : '',
						'Certificate No' => ($index == 0) ? $certificateNo : '',
						'Status' => ($index == 0) ? ucwords(strtolower($inventoryStatus)) : '',
						'Metal Quality' => ($index == 0) ? $metalQuality : '',
						'Metal Weight' => ($index == 0) ? $metalWeight : '',
						'Metal Price' => ($index == 0) ? $estimateValue : '',
						'Labour Amount' => ($index == 0) ? $labour_charge : '',
						'Stone Shape' => isset($stoneShape[$index]) ? $stoneShape[$index] : '',
						'Stone Clarity' => isset($stoneClarity[$index]) ? $stoneClarity[$index] : '',
						'Stone Pcs' => isset($stonePices[$index]) ? $stonePices[$index] : '',
						'Stone Weight' => isset($stoneWeight[$index]) ? $stoneWeight[$index] : '',
						'Stone Price' => isset($caratPrice[$index]) ? $caratPrice[$index] : '',
						'Price' => ($index == 0) ? ShowroomHelper::currencyFormat($totalProductPrice) : '',
					);
				}
			}
			$row = 0;
			return \Excel::create('inventory_products', function ($excel) use ($data) {
				$excel->sheet('Sheet', function ($sheet) use ($data) {
					foreach ($data as $row => $columns) {
						foreach ($columns as $column => $value) {
							if (strpos($value, 'img/') !== false) {
								$objDrawing = new PHPExcel_Worksheet_Drawing();
								$objDrawing->setName('inventory_img');
								$objDrawing->setDescription('inventory_img');
								$objDrawing->setPath($value);
								$rowNo = (int) $row + 2;
								$objDrawing->setCoordinates('B' . $rowNo);
								$objDrawing->setOffsetX(5);
								$objDrawing->setOffsetY(5);
								$objDrawing->setWidth(80);
								$objDrawing->setHeight(80);
								$objDrawing->setWorksheet($sheet);
								//$sheet->setSize('A1', 50);
								//$sheet->setWidth('A', 0.5);
								$sheet->setSize(array(
									'B1' . $rowNo => array(
										'width' => 15,
										'height' => 15,
									),
								));
								$sheet->getRowDimension($rowNo)->setRowHeight(70);
								//$sheet->getColumnDimension('A')->setWidth(60);
								//$sheet->getColumnDimension('A')->setAutoSize(true);
								if ($data[$row]['Image'] == $value) {
									$data[$row]['Image'] = '';
								}
							}
						}
					}
					$sheet->fromArray($data);
				});
			})->download('xlsx');
			DB::setTablePrefix('dml_');
		}
	}
	//Display stocktally products
	public function stockTally(Request $request) {
		$params = $request->post();
		$quotationId = !empty($id) ? $id : '';
		$productCollection = InventoryHelper::getInventoryProducts();
		//return view('inventory.index')->with('productCollection',$inventoryProducts,'quotationId',$quotationId);
		if (empty($quotationId)) {
			Session::forget('edit_product_ids');
		}

		Session::forget('quotation_product_ids');
		DB::setTablePrefix('dml_');
		return view('inventory.stocktally', compact('productCollection', 'quotationId'));
	}
	//Search franchise
	public function getFranchisee(Request $request) {
		$params = $request->post();
		$franchiseName = isset($params['term']) ? $params['term'] : '';
		if (!empty($franchiseName)) {
			DB::setTablePrefix('');
			$franchiseData = DB::select("SELECT DISTINCT `e`.entity_id, `at_firstname`.`value` AS `firstname`, `at_lastname`.`value` AS `lastname`, `at__isfranchisee`.`value` AS `_isfranchisee` FROM `customer_entity` AS `e` INNER JOIN `customer_entity_varchar` AS `at_firstname` ON ( `at_firstname`.`entity_id` = `e`.`entity_id` ) AND(`at_firstname`.`attribute_id` = '5') INNER JOIN `customer_entity_varchar` AS `at_lastname` ON ( `at_lastname`.`entity_id` = `e`.`entity_id` ) AND(`at_lastname`.`attribute_id` = '7') INNER JOIN `customer_entity_int` AS `at__isfranchisee` ON ( `at__isfranchisee`.`entity_id` = `e`.`entity_id` ) WHERE (`e`.`entity_type_id` = '1') AND(at__isfranchisee.value = '1') AND (`at_firstname`.`value` like '%" . $franchiseName . "%' OR `at_lastname`.`value` like '%" . $franchiseName . "%')");
			DB::setTablePrefix('dml_');
			$franchisee = array();
			foreach ($franchiseData as $key => $value) {
				$franchisee[$key]['entity_id'] = $value->entity_id;
				$franchisee[$key]['name'] = $value->firstname . "  " . $value->lastname;
			}
			echo json_encode($franchisee);exit;
		}
	}
	//Check invoice number
	public function checkInvoiceNumber(Request $request) {
		/*$params = $request->post();
			$invoiceNumber = isset($params['invoice_number']) ? $params['invoice_number'] : '';
			if(!empty($invoiceNumber))
			{
				DB::setTablePrefix('');
				$invoice = DB::table("sales_flat_invoice")->select('entity_id')->where('increment_id','=',DB::raw("'$invoiceNumber'"))->get();
				if($invoice->count() > 0)
				{
					$response['status'] = false;
					$response['message'] =
				}
				DB::setTablePrefix('dml_');
		*/
	}
	//Download approval product certificate csv
	public function downloadApprovalCertificate() {
		$productIds = Session::get('approval_product_ids');
		Session::forget('approval_product_ids');
		Session::save();
		$where = '';
		if (!empty($productIds)) {
			$productIds = implode("','", $productIds);
			$where = " WHERE memo_histroy.product_id IN('" . $productIds . "')";
		}

		$approvalProducts = DB::select("SELECT memo.customer_id, memo.approval_no, memo.approval_type, memo.created_at, memo.is_for_old_data, ce.certificate_no FROM dml_approval_memo_histroy as memo_histroy JOIN dml_approval_memo as memo ON memo.id = memo_histroy.approval_memo_id JOIN catalog_product_flat_1 as ce ON ce.entity_id = memo_histroy.product_id" . $where);
		$csvData = array();
		$approvalTypeOptions = config('constants.approval_type');
		foreach ($approvalProducts as $key => $product) {
			$certificateNo = isset($product->certificate_no) ? $product->certificate_no : '';
			$approvalType = isset($product->approval_type) ? $approvalTypeOptions[$product->approval_type] : '';
			$customerId = isset($product->customer_id) ? $product->customer_id : '';
			$approvalName = InventoryHelper::getCustomerName($customerId);
			$currentYear = date('y');
			$approvalNumber = isset($product->approval_no) ? $product->approval_no : '';
			if (date('m') > 6) {
				$fin_year = date('y') . '-' . (date('y') + 1);
			} else {
				$fin_year = (date('y') - 1) . '-' . date('y');
			}
			$approvalNumber = $fin_year . '/' . $approvalNumber;
			if (isset($product->is_for_old_data) && $product->is_for_old_data == 'yes') {
				$approvalNumber = isset($product->approval_no) ? $product->approval_no : '';
			}

			$approvalDate = isset($product->created_at) ? date('d-m-Y', strtotime($product->created_at)) : '';
			$csvData[] = array(
				'Certificate No' => $certificateNo,
				'Approval Type' => $approvalType,
				'Approval Name' => $approvalName,
				'Approval No' => $approvalNumber,
				'Approval Date' => $approvalDate,
			);
		}
		$row = 0;
		return \Excel::create('approval_certificate', function ($excel) use ($csvData) {
			$excel->sheet('Sheet', function ($sheet) use ($csvData) {
				foreach ($csvData as $row => $columns) {
					foreach ($columns as $column => $value) {
						if (strpos($value, 'img/') !== false) {
							$objDrawing = new PHPExcel_Worksheet_Drawing();
							$objDrawing->setName('inventory_img');
							$objDrawing->setDescription('inventory_img');
							$objDrawing->setPath($value);
							$rowNo = (int) $row + 2;
							$objDrawing->setCoordinates('B' . $rowNo);
							$objDrawing->setOffsetX(5);
							$objDrawing->setOffsetY(5);
							$objDrawing->setWidth(80);
							$objDrawing->setHeight(80);
							$objDrawing->setWorksheet($sheet);
							//$sheet->setSize('A1', 50);
							//$sheet->setWidth('A', 0.5);
							$sheet->setSize(array(
								'B1' . $rowNo => array(
									'width' => 15,
									'height' => 15,
								),
							));
							$sheet->getRowDimension($rowNo)->setRowHeight(70);
							//$sheet->getColumnDimension('A')->setWidth(60);
							//$sheet->getColumnDimension('A')->setAutoSize(true);
							if ($csvData[$row]['Image'] == $value) {
								$csvData[$row]['Image'] = '';
							}
						}
					}
				}
				$sheet->fromArray($csvData);
			});
		})->download('csv');
	}
	public function storeApprovalProductIds(Request $request) {
		$params = $request->post();
		$productId = isset($params['product_ids']) ? $params['product_ids'] : '';
		if (empty($productId)) {
			return;
		}

		if (strpos($productId, ',') !== false) {
			$productIds = explode(',', $productId);
		} else {
			$productIds = array($productId);
		}

		Session::put('approval_product_ids', $productIds);
		Session::save();
	}

	public function stocktmp(Request $request) {

		$params = $request->post();
		$quotationId = !empty($id) ? $id : '';
		$productCollection = InventoryHelper::getInventoryProductsTmp();
		if (empty($quotationId)) {
			Session::forget('edit_product_ids');
		}
		Session::forget('quotation_product_ids');

		DB::setTablePrefix('dml_');
		return view('inventory.stocktmp', compact('productCollection', 'quotationId'));
	}

	public function generateqrcode(Request $request) {
		$params = $request->post();
		$productIds = isset($params['productIds']) ? $params['productIds'] : array();
		if (!empty($productIds) && !is_array($productIds)) {
			$productIds = explode(',', $productIds);
		}
		if (count($productIds) > 0) {
			foreach ($productIds as $productId) {
				$certificate_no = InventoryHelper::getCertificateNo($productId);
				if (!empty($certificate_no)) {
					$qrUrl = Config::get('constants.enum.qrcode_url') . $certificate_no;
					$generateResult = QrCode::format('png')->size(250)->generate($qrUrl, public_path(Config::get('constants.dir.qrcode_images') . $certificate_no . '.png'));
					$generateResult2 = QrCode::format('png')->size(50)->generate($qrUrl, public_path(Config::get('constants.dir.qrcode_images') . $certificate_no . '_50.png'));
					$generateResult2 = QrCode::format('png')->size(100)->generate($qrUrl, public_path(Config::get('constants.dir.qrcode_images') . $certificate_no . '_100.png'));
				}
			}
			$responseData['status'] = true;
			$responseData['message'] = Config::get('constants.message.qrcode_generated');
		} else {
			$responseData['status'] = false;
			$responseData['message'] = Config::get('constants.message.qrcode_not_generated');
		}
		echo json_encode($responseData);exit;
	}

	public function printqrcode(Request $request) {
		$params = $request->post();
		$qrData = array();
		$productIds = isset($params['productIds']) ? $params['productIds'] : array();
		if (!empty($productIds) && !is_array($productIds)) {
			$productIds = explode(',', $productIds);
		}
		if (count($productIds) > 0) {
			foreach ($productIds as $productIdsKey => $productId) {
				//var_dump($productId);
				$productData = InventoryHelper::getProductData($productId);
				$qrData[$productIdsKey]['sku'] = $productData->sku;
				$qrData[$productIdsKey]['certificate_no'] = $productData->certificate_no;

				$qrData[$productIdsKey]['diamond_quality'] = $productData->rts_stone_quality;
				$stoneData = InventoryHelper::getStoneData($productId);
				$metalData = ShowroomHelper::getMetalData($productId);
				if (!empty($stoneData)) {
					if (!empty($stoneData['totalweight'])) {
						if (!empty($stoneData['totalweight'][0])) {
							$qrData[$productIdsKey]['diamond_total_weight'] = $stoneData['totalweight'][0];
						} else {
							$qrData[$productIdsKey]['diamond_total_weight'] = 0;
						}
					} else {
						$qrData[$productIdsKey]['diamond_total_weight'] = 0;
					}
				} else {
					$qrData[$productIdsKey]['diamond_total_weight'] = 0;
				}

				$qrData[$productIdsKey]['metal_quality'] = $metalData['quality'];
				$qrData[$productIdsKey]['metal_weight'] = $metalData['weight'];

				$qrData[$productIdsKey]['price'] = $productData->custom_price;

				$stonesArr = $stoneData['type'];
				//var_dump($stoneData);
				$total_diamond_weight = 0;
				foreach ($stonesArr as $stoneDataKey => $stoneDataVal) {
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['type'] = $stoneData['type'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['subtype'] = $stoneData['subtype'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['shape'] = $stoneData['shape'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['cut'] = $stoneData['cut'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['carat'] = $stoneData['carat'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['mm_size'] = $stoneData['mm_size'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['stone'] = $stoneData['stone'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['stone_use'] = $stoneData['stone_use'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['setting'] = $stoneData['setting'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['stoneclarity'] = $stoneData['stoneclarity'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['totalcts'] = $stoneData['totalcts'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['percts'] = $stoneData['percts'][$stoneDataKey];
					$qrData[$productIdsKey]['diamonds'][$stoneDataKey]['stone_price'] = $stoneData['stone_price'][$stoneDataKey];
					if ($qrData[$productIdsKey]['diamond_total_weight'] <= 0) {
						$total_diamond_weight += $stoneData['totalcts'][$stoneDataKey] * $stoneData['stone_use'][$stoneDataKey];
					}
				}

				if ($qrData[$productIdsKey]['diamond_total_weight'] <= 0 && $total_diamond_weight > 0) {
					$qrData[$productIdsKey]['diamond_total_weight'] = $total_diamond_weight;
				}
				//var_dump($productData->certificate_no);
				//var_dump($productData->sku);
				//var_dump($productData->custom_price);
				//var_dump($productData->rts_stone_quality);
				//DiamondHelper::getSideStoneData($productId, $productData->certificate_no, $productData->rts_stone_quality);
				//var_dump($stoneData);
				//exit;
				/*$certificate_no = InventoryHelper::getCertificateNo($productId);
					if (!empty($certificate_no)) {
						$qrUrl = Config::get('constants.enum.qrcode_url') . $certificate_no;
						$generateResult = QrCode::format('png')->size(250)->generate($qrUrl, public_path(Config::get('constants.dir.qrcode_images') . $certificate_no . '.png'));
						$generateResult2 = QrCode::format('png')->size(50)->generate($qrUrl, public_path(Config::get('constants.dir.qrcode_images') . $certificate_no . '_50.png'));
						$generateResult2 = QrCode::format('png')->size(100)->generate($qrUrl, public_path(Config::get('constants.dir.qrcode_images') . $certificate_no . '_100.png'));
				*/
			}
			//echo '<pre>';
			//print_r($qrData);exit;
			/*	$responseData['status'] = true;
			*/
		} else {
			/*$responseData['status'] = false;
			$responseData['message'] = Config::get('constants.message.qrcode_not_generated');*/
		}
		if (!empty($params['without_qr'])) {
			return view('inventory.printqrwithoutqr')->with('qrData', $qrData);
		} else {
			return view('inventory.printqr')->with('qrData', $qrData);
		}

		//exit;
		//echo json_encode($responseData);exit;
	}
	public function storeQrProductids(Request $request) {
		$params = $request->post();
		if (isset($params['productIds']) && !empty($params['productIds'])) {
			$productIds = implode(',', $params['productIds']);
			Session::put('qr_product_ids', $productIds);
			Session::save();

			$response['status'] = true;
		} else {
			$response['status'] = false;
		}
		echo json_encode($response);exit;
	}
	//Update invoice
	public function updateInvoice(Request $request) {
		$params = $request->post();
		$shippingCharge = isset($params['shippingChange']) ? $params['shippingChange'] : 0;
		$invoiceId = isset($params['invoice_id']) ? $params['invoice_id'] : '';
		$productIds = isset($params['product_ids']) ? $params['product_ids'] : '';
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$metalWeight = isset($params['txtmetalweight']) ? $params['txtmetalweight'] : '';
		$stoneWeight = isset($params['txtstoneweight']) ? $params['txtstoneweight'] : '';
		$discountAmount = isset($params['txtDiscount']) ? $params['txtDiscount'] : '';
		$finalAmount = isset($params['txtUnitPrice']) ? $params['txtUnitPrice'] : '';
		$unitPrice = isset($params['txtprice']) ? $params['txtprice'] : '';
		$priceMarkup = CustomersHelper::getCustomerAttrValue($customerId, 'price_markup');
		$invoiceTotal = 0;
		$discountTotal = 0;
		$fourteenKProducts = array();
		$eighteenKProducts = array();
		$productDataArr = array();
		//Get discount
		foreach ($productIds as $key => $productId) {
			DB::setTablePrefix('');
			$productData = DB::table('catalog_product_flat_1')->select('metal_quality_value', 'sku')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
			$metalQuality = explode(' ', $productData->metal_quality_value);
			$metalQuality = isset($metalQuality[0]) ? $metalQuality[0] : '';
			if ($metalQuality == '14K') {
				$fourteenKProducts[] = $productId;
			} else if ($metalQuality == '18K') {
				$eighteenKProducts[] = $productId;
			}
			$productDataArr[]['product_id'] = $productId;
			$productDataArr[]['sku'] = $productData->sku;
			$productDataArr[]['metal_weight'] = $metalWeight[$key];
			$productDataArr[]['stone_weight'] = $stoneWeight[$key];
			$productDataArr[]['unit_price'] = $unitPrice[$key];
		}
		$invoiceData = InventoryHelper::getInvoiceById($invoiceId);
		foreach ($productIds as $key => $productId) {
			$invoiceItem = InventoryHelper::getInvoiceItemData($productId, $invoiceId);
			DB::setTablePrefix('');
			$metalData = DB::table("catalog_product_flat_1")->select("metal_quality_value")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
			$metalQuality = explode(' ', $metalData->metal_quality_value);

			if (!empty($invoiceItem)) {
				$discount_amount = $invoiceItem->discount_amount;
				$tax_amount = $invoiceItem->tax_amount;

				$new_unit_price = $finalAmount[$key];
				$new_discount_amount = $discountAmount[$key];
				//Update sales_flat_invoice_item
				$sql = "UPDATE sales_flat_invoice_item set base_price=" . $new_unit_price . ",base_row_total=" . $new_unit_price . ",discount_amount=" . $new_discount_amount . ",row_total=" . $new_unit_price . ",base_discount_amount=" . $new_discount_amount . ",price_incl_tax=" . ($new_unit_price + $tax_amount) . ",base_price_incl_tax=" . ($new_unit_price + $tax_amount) . ",price=" . $new_unit_price . ",base_row_total_incl_tax=" . ($new_unit_price + $tax_amount + $tax_amount) . ",row_total_incl_tax=" . ($new_unit_price + $tax_amount + $tax_amount) . " WHERE product_id=" . $productId . " AND parent_id=" . $invoiceId;

				DB::statement($sql);
				$invoiceTotal += $new_unit_price;
				$discountTotal += $new_discount_amount;

				//Update dml_invoice_products
				$data = array(
					'metal_weight' => !empty($metalWeight[$key]) ? $metalWeight[$key] : 0,
					'stone_weight' => !empty($stoneWeight[$key]) ? $stoneWeight[$key] : 0,
					'unit_price' => !empty($finalAmount[$key]) ? $finalAmount[$key] : 0,
					'updated_at' => date('Y-m-d H:i:s'),
				);
				DB::setTablePrefix('dml_');
				InvoiceProducts::where('product_id', '=', DB::raw("$productId"))->where('invoice_id', '=', DB::raw("$invoiceId"))->update($data);
			}
		}
		DB::setTablePrefix('');
		//Update sales_flat_invoice
		$oldInvoiceTotal = $invoiceData->base_grand_total;
		$oldInvoiceDiscount = $invoiceData->base_discount_amount;
		$newInvoiceDiscount = ($invoiceTotal * $discountTotal) / $oldInvoiceTotal;

		$sql = "UPDATE sales_flat_invoice set invoice_shipping_charge=" . $shippingCharge . ",base_grand_total=" . $invoiceTotal . ",base_discount_amount=" . $newInvoiceDiscount . ",grand_total=" . $invoiceTotal . ",subtotal_incl_tax=" . ($invoiceTotal + $discountTotal) . ",base_subtotal_incl_tax=" . ($invoiceTotal + $discountTotal) . ",subtotal=" . ($invoiceTotal + $discountTotal - $invoiceData->tax_amount) . ",base_subtotal=" . ($invoiceTotal + $discountTotal - $invoiceData->tax_amount) . ",discount_amount=" . $newInvoiceDiscount . " WHERE entity_id=" . $invoiceId;
		DB::statement($sql);

		//Update sales_flat_invoice_grid
		$invoiceIncrementId = $invoiceData->increment_id;
		$invoiceGridData = DB::table("sales_flat_invoice_grid")->select("*")->where("increment_id", "=", DB::raw("'$invoiceIncrementId'"))->get()->first();

		$sql = "UPDATE sales_flat_invoice_grid set base_grand_total=" . $invoiceTotal . ",grand_total=" . $invoiceTotal . " WHERE increment_id='" . $invoiceIncrementId . "'";

		//Update payment transaction
		DB::setTablePrefix('dml_');
		Payment::where("invoice_number", $invoiceIncrementId)->update(array('invoice_amount' => $invoiceTotal));

		//DB::statement($sql);
		DB::setTablePrefix('dml_');

		//Insert into invoice_logs
		InvoiceLogs::create(
			array(
				'user_id' => Auth::user()->id,
				'invoice_id' => $invoiceId,
				'product_data' => json_encode($productDataArr),
				'comment' => config('constants.message.invoice_updated'),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			)
		);

		$response['status'] = true;
		$response['message'] = Config::get('constants.message.invoice_updated_successfully');
		echo json_encode($response);exit;
	}
	//Remove invoice product
	public function removeInvoiceProduct(Request $request) {
		$params = $request->post();
		$invoiceId = isset($params['invoice_id']) ? $params['invoice_id'] : '';
		$orderId = isset($params['order_id']) ? $params['order_id'] : '';
		$productId = isset($params['product_id']) ? $params['product_id'] : '';

		if (!empty($productId) && !empty($orderId)) {
			if (App::environment('local')) {
				$removeInvoiceProductUrl = Config::get('constants.apiurl.local.remove_invoice_product');
			} else if (App::environment('test')) {
				$removeInvoiceProductUrl = Config::get('constants.apiurl.test.remove_invoice_product');
			} else {
				$removeInvoiceProductUrl = Config::get('constants.apiurl.live.remove_invoice_product');
			}
			$invoiceParams = 'productId=' . $productId . '&orderId=' . $orderId . '&invoiceId=' . $invoiceId;
			$ch = curl_init($removeInvoiceProductUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $invoiceParams);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$result = json_decode($result);
			if (isset($result->status) && $result->status) {
				$updatedInvoiceTotal = isset($result->updated_grand_total) ? $result->updated_grand_total : 0;
				$invoiceIncrementId = isset($result->invoice_increment_id) ? $result->invoice_increment_id : '';
				Payment::where("invoice_number", $invoiceIncrementId)->update(array('invoice_amount' => $updatedInvoiceTotal));
			}
			//insert into invoice_logs
			$invoiceProducts = InvoiceProducts::select('*')->where('product_id', '=', DB::raw("$productId"))->where("invoice_id", "=", DB::raw("$invoiceId"))->first();
			InvoiceLogs::create(
				array(
					'user_id' => Auth::user()->id,
					'invoice_id' => $invoiceId,
					'product_data' => json_encode(array('product_id' => $invoiceProducts->product_id, 'sku' => $invoiceProducts->sku, 'metal_weight' => $invoiceProducts->metal_weight, 'stone_weight' => $invoiceProducts->stone_weight, 'unit_price' => $invoiceProducts->unit_price)),
					'comment' => config('constants.message.invoice_product_removed'),
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				)
			);
			Cache::forget('all_products_ajax');
			Cache::forget('all_products');
			echo json_encode($result);exit;
		}
	}
	public function insertinvoiceproducts() {
		DB::setTablePrefix('');
		$generatedInvoiceList = DB::table("sales_flat_order as main_table")
			->select("main_table.entity_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total as order_total", "main_table.status", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total")
			->where("qr_product_status", "=", DB::raw("'1'"))
			->where("oinv.entity_id", "=", DB::raw("1736"))
		//->where('main_table.status', '=', DB::raw("'complete'"))
			->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
		//->leftJoin('dml_invoice_customer_division as invoice_division', 'invoice_division.invoice_id', '=', 'oinv.entity_id')
			->join('sales_flat_invoice_item as oinv_item', 'oinv_item.parent_id', '=', 'oinv.entity_id')
			->leftJoin('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'oinv_item.product_id')
			->orderBy("oinv.created_at", "desc")
			->groupBy('oinv_item.parent_id')
			->get();

		foreach ($generatedInvoiceList as $key => $invoice) {
			DB::setTablePrefix('');
			$invoiceItems = DB::table("sales_flat_invoice_item")->select("product_id")->where("parent_id", "=", DB::raw("'$invoice->invoice_ent_id'"))->get();
			foreach ($invoiceItems as $key => $item) {
				DB::setTablePrefix('');
				$product = DB::table("catalog_product_flat_1")->select("*")->where("entity_id", "=", DB::raw("$item->product_id"))->get()->first();

				$stoneQuality = isset($product->rts_stone_quality) ? $product->rts_stone_quality : '';
				$metalData = ShowroomHelper::getMetalData($item->product_id, $product);
				$metalWeight = isset($metalData['weight']) ? $metalData['weight'] : 0;
				$stoneData = InventoryHelper::getStoneData($item->product_id);
				//print_r($stoneData);exit;
				$stoneWeight = isset($stoneData['totalcts'][0]) ? $stoneData['totalcts'][0] : 0;
				$unitPrice = isset($product->custom_price) ? $product->custom_price : 0;
				$productId = $item->product_id;
				$sku = isset($product->sku) ? $product->sku : '';
				DB::setTablePrefix('dml_');
				InvoiceProducts::create(
					array(
						'product_id' => $productId,
						'sku' => $sku,
						'metal_weight' => $metalWeight,
						'stone_weight' => $stoneWeight,
						'unit_price' => $unitPrice,
						'invoice_id' => $invoice->invoice_ent_id,
						'created_at' => date('Y-m-d H:i:s'),
						'updated_at' => date('Y-m-d H:i:s'),
					)
				);
			}
		}
	}

	public function exportProductPdf(Request $request) {
		$name = $this->getImageWithText($request);
		if (!empty($name)) {
			$customPaper = array(0, 0, 500.00, 502.5);
			//$customPaper = $pdf->setPaper('A4');
			$data = ['name' => $name];
			$pdf = PDF::loadView('inventory/exportpdf', $data)->setPaper($customPaper);
			$pdf->setOptions(['dpi' => 96, 'images' => true, 'isRemoteEnabled' => false]);
			$path = public_path('images/');
			$pdfnameWithExt = 'product_image_' . time() . '.pdf';
			$pdf->save($path . $pdfnameWithExt);
			$pdf->stream();
			return $pdf->download($pdfnameWithExt);
		} else {
			$message = "Selected Product does not having Images";
			return redirect()->back()->with('error', $message);
		}
	}

	public function checkcertificatelimit(Request $request) {
		$productIds = $request->productIds;
		$expoProd = explode(",", $productIds);
		$countProduct = count($expoProd);
		$downloadlimit = Setting::where('key', config('constants.settings.keys.download_image_limit'))->first('value');
		$countLimit = $downloadlimit->value;
		if ($countProduct <= $countLimit) {
			$response['status'] = true;
			$response['message'] = "success";
		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.product_image_download_limit');
		}
		echo json_encode($response);exit;
	}

	public function getImageWithText($request) {

		$name = array();
		DB::setTablePrefix('');
		$prodcutIds = $request->productIds;
		$categoryArr = array('14', '6', '287', '7', '9', '43', '295', '290', '289');
		$paramPrice = $request->price;
		$expoProdcutIds = explode(",", $prodcutIds);
		foreach ($categoryArr as $categoryElem) {

			$prod = InventoryHelper::getAllProductsCollection(true);
			$prodColl = $prod->where('category_id', $categoryElem);
			$prodColl = $prodColl->whereIn('entity_id', $expoProdcutIds);
			foreach ($prodColl as $prodCollkey => $prodCollVal) {
				if ($prodCollVal->product_image != "") {

					$metalweight = $prodCollVal->metal_weight;
					$certificate_no = $prodCollVal->certificate_no;
					$entityId = $prodCollVal->entity_id;
					$metalquality = explode(" ", $prodCollVal->metal_quality_value);
					$total_carat = $prodCollVal->total_carat;
					$quality = $prodCollVal->rts_stone_quality;
					$price = explode(".", $prodCollVal->custom_price);
					$pricefnl = ShowroomHelper::currencyFormatForProductImage(round($price[0]));
					if ($quality == '') {
						$quality = 'SI-IJ';
					}
					$sku = explode(" ", $prodCollVal->sku);
					$certified = "IGI CERTIFIED";

					if ($paramPrice == 'wop') {

						$text = $certified . '|' . "GWT     " . $metalweight . ' ' . $metalquality[0] . '|' . 'DWT     ' . $total_carat . ' ' . $quality . '|' . 'SKU      ' . $sku[0];
					} else {
						$text = $certified . '|' . "GWT     " . $metalweight . ' ' . $metalquality[0] . '|' . 'DWT     ' . $total_carat . ' ' . $quality . '|' . 'PRICE   ' . $pricefnl . '|' . 'SKU      ' . $sku[0];
					}
					$lines = explode("|", $text);

					//Get image by curl from server
					$productImage_old = $prodCollVal->product_image;
					$productImage = $this->getImageByCurlFromServer($productImage_old, $certificate_no);
					if ($paramPrice == 'wop') {
						$certi_no = 'wop_' . substr($certificate_no, 0, 3);
					} else {
						$certi_no = 'wp_' . substr($certificate_no, 0, 3);
					}
					$actualPath = public_path('images/' . $certi_no . '/');
					$fullimagename = $actualPath . $certificate_no . '.jpg';
					if (file_exists($fullimagename)) {
						$name[] = $fullimagename;
					} else {
						if (!file_exists($actualPath)) {
							File::makeDirectory($actualPath, 0777, true);
						}
						$img = Image::make(public_path($productImage));
						$xPosition = 20;
						$line_height = 30;
						$i = 850;
						foreach ($lines as $line) {
							$img->text($line, $xPosition, $i, function ($font) {
								$font->size(22);
								$font->file(public_path() . '/' . 'Myriad_Pro_Regular.ttf');
								$font->color('#e1e1e1');
								$font->valign('bottom');
								$font->angle(0);
							});
							$i += $line_height;
						}
						$newImg = $img->save($actualPath . '/' . $certificate_no . '.jpg');
						$name[] = $actualPath . '/' . $newImg->basename;
					}
				}

			}
			//exit;
		}
		return $name;
	}

	public function getImageByCurlFromServer($product_image, $certificate_no) {
		if (App::environment('local')) {
			$imageDirectory = config('constants.dir.website_url_for_product_image');
		} else {
			$imageDirectory = config('constants.dir.website_url_for_product_image_curl');
		}

		$defaultProductImage = $imageDirectory . 'def_1.png';
		$product_imagefull = $imageDirectory . $product_image;
		$productImage = (!empty($product_image) ? $product_imagefull : $defaultProductImage);
		$ext = pathinfo($productImage, PATHINFO_EXTENSION);
		$certi_no = substr($certificate_no, 0, 3);
		$actualPath = 'images/product/' . $certi_no . '/';
		$file = $actualPath . '/product' . $certificate_no . '.' . $ext;
		if (!file_exists(public_path($actualPath))) {
			File::makeDirectory(public_path($actualPath), 0777, true);
		}
		if (InventoryHelper::isImageExist($productImage) == '200') {
			$curlCh = curl_init();
			curl_setopt($curlCh, CURLOPT_URL, $productImage);
			curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curlCh, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($curlCh, CURLOPT_SSLVERSION, 3);
			$curlData = curl_exec($curlCh);
			curl_close($curlCh);

			$imgfile = fopen($file, "w+");
			fputs($imgfile, $curlData);
			fclose($imgfile);
		} else {
			$file = 'img/def_1.png';
		}
		return $file;
	}
	//get productids by approval memo id
	public function getProductIdsByApproval(Request $request) {
		$params = $request->post();
		$approvalMemoId = isset($params['memo_id']) ? $params['memo_id'] : '';

		if (!empty($approvalMemoId)) {
			$approvalProducts = DB::table('approval_memo_histroy as approval_histroy')->select(DB::raw('GROUP_CONCAT(dml_approval_histroy.product_id) AS product_ids'))->join('approval_memo as approval', 'approval.id', '=', 'approval_histroy.approval_memo_id')->whereIn('approval.id', [DB::raw("$approvalMemoId")])->where('approval_histroy.status', '!=', DB::raw("'invoice'"))->get();
			$productIds = isset($approvalProducts[0]->product_ids) ? $approvalProducts[0]->product_ids : '';
			if (!empty($productIds)) {
				$response['status'] = true;
				$response['product_ids'] = $productIds;
				$response['message'] = '';
			} else {
				$approvalNumberData = DB::table('approval_memo')->select('approval_no')->whereIn('id', [DB::raw("$approvalMemoId")])->get();
				$approvalNumbers = array();
				foreach ($approvalNumberData as $key => $approval) {
					$currentYear = date('y');
					if (date('m') > 6) {
						$fin_year = date('y') . '-' . (date('y') + 1);
					} else {
						$fin_year = (date('y') - 1) . '-' . date('y');
					}
					$approvalNumbers[] = $fin_year . '/' . $approval->approval_no;
				}
				$approvalNumbers = implode(', ', $approvalNumbers);
				$response['status'] = false;
				$response['product_ids'] = '';
				$response['message'] = config('constants.message.invoice_already_generated_for_approval') . $approvalNumbers;
			}
		} else {
			$response['status'] = false;
			$response['product_ids'] = '';
			$response['message'] = config('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}
	//Get product ids for return memo
	public function getProductIdsForReturnmemo(Request $request) {
		$params = $request->post();
		$approvalMemoId = isset($params['memo_id']) ? $params['memo_id'] : '';
		if (!empty($approvalMemoId)) {
			$approvalProducts = DB::table('approval_memo_histroy as approval_histroy')->select(DB::raw('GROUP_CONCAT(dml_approval_histroy.product_id) AS product_ids'))->join('approval_memo as approval', 'approval.id', '=', 'approval_histroy.approval_memo_id')->whereIn('approval.id', [DB::raw("$approvalMemoId")])->where('approval_histroy.status', '!=', DB::raw("'return_memo'"))->where('approval_histroy.status', '!=', DB::raw("'invoice'"))->get();
			$productIds = isset($approvalProducts[0]->product_ids) ? $approvalProducts[0]->product_ids : '';
			if (!empty($productIds)) {
				$response['status'] = true;
				$response['product_ids'] = $productIds;
				$response['message'] = '';
			} else {
				$approvalNumberData = DB::table('approval_memo')->select('approval_no')->whereIn('id', [DB::raw("$approvalMemoId")])->get();
				$approvalNumbers = array();
				foreach ($approvalNumberData as $key => $approval) {
					$currentYear = date('y');
					if (date('m') > 6) {
						$fin_year = date('y') . '-' . (date('y') + 1);
					} else {
						$fin_year = (date('y') - 1) . '-' . date('y');
					}
					$approvalNumbers[] = $fin_year . '/' . $approval->approval_no;
				}
				$approvalNumbers = implode(', ', $approvalNumbers);
				$response['status'] = false;
				$response['product_ids'] = '';
				$response['message'] = config('constants.message.return_memo_already_generated_for_approval') . $approvalNumbers;
			}
		} else {
			$response['status'] = false;
			$response['product_ids'] = '';
			$response['message'] = config('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}

	public function getApprovalIdByProduct(Request $request) {
		$params = $request->post();
		$productIds = isset($params['productIds']) ? $params['productIds'] : '';
		$approvalIdArr = array();
		if (!empty($productIds)) {
			$approvalIds = DB::table('approval_memo_histroy')->select(DB::raw('GROUP_CONCAT(approval_memo_id) AS memo_ids'))->whereIn('product_id', [DB::raw("$productIds")])->get();

			foreach ($approvalIds as $key => $approval) {
				$approvalIdArr = explode(',', $approval->memo_ids);
			}
			$approvalIdArr = array_unique($approvalIdArr);
			$response['status'] = true;
			$response['memo_ids'] = implode(',', $approvalIdArr);
		} else {
			$response['status'] = false;
			$response['memo_ids'] = '';
		}
		echo json_encode($response);exit;
	}

	public function deliverystatus($id) {

		$search_voucher = Setting::where('key', config('constants.settings.keys.Delivery_Challan_No'))->first()->value;
		$new_voucher = (int) $search_voucher + 1;
		$nid = Setting::select('id')->where('key', config('constants.settings.keys.Delivery_Challan_No'))->get();
		$setting = Setting::find($nid[0]->id);
		$setting->value = $new_voucher;
		$setting->update();
		DB::setTablePrefix('');
		$invoiceData = DB::table("sales_flat_order as main_table")
			->select("main_table.entity_id", "main_table.custom_discount_percent", "main_table.shipping_address_id", "main_table.isfranchisee", "main_table.dmlstore_order_increment_id", "main_table.transportation_mode", "main_table.franchise_order_increment_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total as order_total", "main_table.status", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total", "invoice_division.parent_customer_id", "invoice_division.child_customer_name", "invoice_division.child_customer_address", "invoice_division.child_customer_pan", "invoice_division.invoice_id", "oinv.gst_percentage", "oinv.invoice_shipping_charge")
			->where("qr_product_status", "=", DB::raw("'1'"))
		//->where('main_table.status', '=', DB::raw("'complete'"))
			->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
			->leftJoin('dml_invoice_customer_division as invoice_division', 'invoice_division.invoice_id', '=', 'oinv.entity_id')
			->join('sales_flat_invoice_item as oinv_item', 'oinv_item.parent_id', '=', 'oinv.entity_id')
			->leftJoin('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'oinv_item.product_id')
			->where("oinv.entity_id", "=", DB::raw("$id"))
			->orderBy("oinv.created_at", "desc")
			->groupBy('oinv_item.parent_id')
			->get()
			->first();

		$invoiceItems = DB::table("sales_flat_invoice_item")->select("product_id")->where("parent_id", "=", DB::raw("'$id'"))->get();

		return view('inventory.deliverychallan', compact('invoiceData', 'invoiceItems', 'new_voucher'));
	}

	//Update edit invoice price
	public function updateEditInvoiceProductPrice(Request $request) {
		$params = $request->post();
		ProductHelper::getProductPrice($params);exit;
	}
	public function insertReturnMemoProducts() {
		$returnMemo = ReturnMemo::get();
		foreach ($returnMemo as $key => $returnmemo) {
			$productData = json_decode($returnmemo->product_data);
			foreach ($productData as $key => $product) {
				ReturnMemoProducts::create(array('product_id' => $product->productid, 'return_memo_id' => $returnmemo->id, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));
			}
		}
	}
	public function insertSalesReturnMemoProducts() {
		$salesReturn = SalesReturn::get();
		foreach ($salesReturn as $key => $salesreturn) {
			$productData = json_decode($salesreturn->product_data);
			foreach ($productData as $key => $prdData) {
				DB::setTablePrefix('');
				$entityId = DB::table('catalog_product_flat_1')->select('entity_id')->where('sku', '=', DB::raw("'$prdData->sku'"))->get()->first();
				DB::setTablePrefix('dml_');
				SalesReturnProducts::create(array('product_id' => $entityId->entity_id, 'sales_return_id' => $salesreturn->id, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));
			}
		}
	}

	public function generateproductimagepdf(request $request) {
		//echo "fkjk"; exit;
		//$params = $request->post();
		//$productIds = isset($params['productIds']) ? $params['productIds'] : '';
		//echo "<pre>"; print_r($params);exit;
		/*	if (strpos($productIds, ',') !== false) {
			$productIds = explode(',', $productIds);
		} else {
			$productIds = array($productIds);
		}
		$productIds = array_unique($productIds);
		$productIdArray = array_unique($productIds);

		if (!empty($productIds)) {

		}*/
	}

	public function exhibitionList() {
		$exhibitionData = Exhibition::orderBy('created_at', 'DESC')->get(); //->paginate(5);
		//return view('metals.index', compact('metals'))->with('i', ($request->input('page', 1) - 1) * 5);
		return view('inventory.exhibitionlist', compact('exhibitionData'));
	}
	//Get exhibition detail for edit
	public function getExhibitionDetail(Request $request) {
		$params = $request->post();
		$exhibitionId = isset($params['exhibition_id']) ? $params['exhibition_id'] : '';
		if (!empty($exhibitionId)) {
			$exhibitionData = Exhibition::find($exhibitionId);
			return view('inventory.editexhibitionmodal')->with(array('exhibitionData' => $exhibitionData));
		}
	}
	//Update exhibition detail
	public function updateExhibitionData(Request $request) {
		$params = $request->post();
		$exhibitionId = isset($params['exhibition_id']) ? $params['exhibition_id'] : '';
		$title = isset($params['exhibition_title']) ? $params['exhibition_title'] : '';
		$place = isset($params['exhibition_place']) ? $params['exhibition_place'] : '';
		$address = isset($params['exhibition_address']) ? $params['exhibition_address'] : '';
		$markup = isset($params['exhibition_markup']) ? $params['exhibition_markup'] : 0;

		if (!empty($exhibitionId)) {
			$isUpdated = Exhibition::where("id", $exhibitionId)->update(array('title' => $title, 'place' => $place, 'address' => $address, 'markup' => $markup, 'updated_at' => date('Y-m-d H:i:s')));
			if ($isUpdated) {
				$exhibitionData = Exhibition::orderBy('id', 'DESC')->get();
				$exhibitionListHtml = '';
				$view = view('inventory.refreshexhibitiontable')->with(array('exhibitionData' => $exhibitionData));
				$exhibitionListHtml = $view->render();
				$response['status'] = true;
				$response['content'] = $exhibitionListHtml;
				$response['message'] = Config::get('constants.message.exhibition_update_successfully');
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
	public function viewExhibition($id) {
		if (!empty($id)) {
			$exhibitionData = Exhibition::find($id);
			$exhibitionProducts = ExhibitionProducts::where('exhibition_id', $id)->get();
			return view('inventory.viewexhibition', compact('exhibitionData', 'exhibitionProducts'));
		}
	}
	//Change inventory status to in & remove from exhibition
	public function changeinventorystatusandremovefromexhibition(Request $request) {
		$params = $request->post();
		$inventoryStatus = isset($params['inventoryCode']) ? $params['inventoryCode'] : '';
		$productIds = isset($params['productIds']) ? $params['productIds'] : array();
		$exhibitionId = isset($params['exhibition_id']) ? $params['exhibition_id'] : '';
		//print_r($productIds);exit;
		if (!empty($productIds) && !is_array($productIds)) {
			$productIds = explode(',', $productIds);
		}

		if (!empty($productIds)) {
			try
			{
				foreach ($productIds as $productId) {
					InventoryHelper::removeFromExhibition($productId, $exhibitionId);
					InventoryHelper::changeInventoryStatus($productId, $inventoryStatus);
				}
				$response['status'] = true;
				$response['message'] = Config::get('constants.message.exhibition_product_moved_to_showroom_success');
			} catch (Exception $e) {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_status_not_changed');
			}
			Cache::forget('all_products_ajax');
			Cache::forget('all_products');
		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_status_product_not_selected');
		}
		echo json_encode($response);exit;
	}
	public function exhibitionProductAjaxList(Request $request) {
		$params = $request->post();
		$data = array();
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
		$stalen = $start / $length;
		$curpage = $start;

		$exhibitionId = isset($params['exhibition_id']) ? $params['exhibition_id'] : '';
		$exhibitionProducts = ExhibitionProducts::where('exhibition_id', $exhibitionId);

		if (!empty($searchValue)) {
			DB::setTablePrefix('');
			$exhibitionProducts = DB::table('dml_exhibition_products')
				->select('*')
				->join('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'dml_exhibition_products.product_id')
				->where('dml_exhibition_products.exhibition_id', '=', DB::raw("$exhibitionId"))
				->where(function ($q) use ($searchValue) {
					$q->where(function ($query) use ($searchValue) {
						$query->where('ce.sku', "like", DB::raw("'%$searchValue%'"));
					})
						->orWhere(function ($query) use ($searchValue) {
							$query->where('ce.certificate_no', "like", DB::raw("'%$searchValue%'"));
						});
				});
			/*->where("ce.sku","like",DB::raw("'%$searchValue%'"))->orWhere("ce.certificate_no","like",DB::raw("'%$searchValue%'"));*/
		}
		$productCount = $exhibitionProducts->count();
		$productCollection = $exhibitionProducts->take($length)->offset($curpage)->get();
		//echo $productCollection;exit;

		$data["draw"] = $params['draw'];
		$data["recordsTotal"] = $productCount;
		$data["recordsFiltered"] = $productCount;
		$data['deferLoading'] = $productCount;

		$imageDirectory = config('constants.dir.website_url_for_product_image');
		$defaultProductImage = $imageDirectory . 'def_1.png';
		if (count($productCollection) > 0) {
			foreach ($productCollection as $key => $productId) {
				DB::setTablePrefix('');
				$product = DB::table('catalog_product_flat_1')->select('*')->where('entity_id', '=', DB::raw("$productId->product_id"))->get()->first();
				$checkbox = '<label><input type="checkbox" value="' . $product->entity_id . '" data-id="' . $product->entity_id . '" id="chk_product_' . $product->entity_id . '" class="form-check-input chkProduct" name="chkProduct[]"><span class="label-text"></span></label>';
				$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				$productImage = '<img alt="' . $product->sku . '" class="product-img" src="' . (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage) . '">';
				$sku = isset($product->sku) ? $product->sku : '';
				$certificateNo = isset($product->certificate_no) ? $product->certificate_no : '';
				$metalData = ShowroomHelper::getMetalData($product->entity_id, $product);
				$stoneData = InventoryHelper::getStoneData($product->entity_id);
				$stoneWeight = isset($stoneData['totalweight'][0]) ? $stoneData['totalweight'][0] : '';
				$metalWeight = isset($metalData['weight']) ? $metalData['weight'] : '';
				$price = isset($product->custom_price) ? ShowroomHelper::currencyFormat(round($product->custom_price)) : 0;
				$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
				$inStatusVal = $inventoryStatus['in'];
				$action = '<select class="form-control h-auto w-auto mx-auto exhibition_action">
		                        <option value="">Select</option>
		                        <option value="' . $inStatusVal . '" data-productid="' . $product->entity_id . '">Move to Showroom</option>
		                    </select>';
				$data['data'][] = array($checkbox, $productImage, $sku, $certificateNo, $metalWeight, $stoneWeight, $price, $action);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	//Get total exhibition products count
	public function refreshExhibitionDetail(Request $request) {
		$params = $request->post();
		$exhibitionId = isset($params['exhibition_id']) ? $params['exhibition_id'] : '';
		if (!empty($exhibitionId)) {
			$productsCount = ExhibitionProducts::where('exhibition_id', '=', $exhibitionId)->get()->count();
			$grandTotal = ShowroomHelper::currencyFormat(round(InventoryHelper::getExhibitionGrandTotal($exhibitionId)));
			$response['status'] = true;
			$response['product_count'] = $productsCount;
			$response['grand_total'] = $grandTotal;
			echo json_encode($response);exit;
		}

	}
	public function showroomproductlist(Request $request) {
		//print_r($request->all());exit;
		$collection = InventoryHelper::getAllProductsCollection();
		$collection = $collection->where("certificate_no", $request->id);
		//print_r($collection);exit;
		foreach ($collection as $value) {

			$shape = $value->diamond_shape;
			$quality = $value->metal_quality;

		}
		//print_r($shape);exit;
		if (!empty($quality)) {

			$metalQualityData = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_quality' AND EAOV.store_id = 0 AND EAOV.option_id= $quality");
		}
		if (!empty($shape)) {

			$shape = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0 AND EAOV.option_id=$shape");
		}
//print_r($shape);exit;

		$returnHTML = view('inventory.ProductDetail', ['data' => $collection, 'shape' => $shape, 'quality' => $metalQualityData])->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));

	}
	//update invoice products stone weight
	public function updateInvoiceProductStoneWeight() {
		DB::setTablePrefix('');
		$generatedInvoiceList = DB::table("sales_flat_order as main_table")
			->select("main_table.entity_id", "main_table.customer_firstname", "main_table.customer_lastname", "main_table.customer_id", "main_table.grand_total as order_total", "main_table.status", "oinv.entity_id as invoice_ent_id", "oinv.increment_id as invoice_number", "oinv.created_at as invoice_created_date", "oinv.increment_id as invoice_inc_id", "oinv.grand_total as invoice_total", "oinv.gst_percentage", "invoice_division.parent_customer_id", "invoice_division.child_customer_name", "invoice_division.child_customer_address", "invoice_division.child_customer_pan", "invoice_division.invoice_id", "oinv.invoice_shipping_charge")
			->where("qr_product_status", "=", DB::raw("'1'"))
			->where('main_table.status', '=', DB::raw("'complete'"))
			->join('sales_flat_invoice as oinv', 'oinv.order_id', '=', 'main_table.entity_id')
			->leftJoin('dml_invoice_customer_division as invoice_division', 'invoice_division.invoice_id', '=', 'oinv.entity_id')
			->join('sales_flat_invoice_item as oinv_item', 'oinv_item.parent_id', '=', 'oinv.entity_id')
			->leftJoin('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'oinv_item.product_id')
		//->where('ce.certificate_no', '15J263921803')
			->orderBy("oinv.created_at", "desc")
			->groupBy('oinv_item.parent_id')
			->get();

		//echo '<pre>';
		//print_r($generatedInvoiceList);exit;
		DB::setTablePrefix('dml_');
		foreach ($generatedInvoiceList as $key => $invoice) {
			//$invoiceItems = InventoryHelper::getInvoiceItems($invoice->invoice_ent_id);
			$invoiceProducts = InvoiceProducts::select('id', 'product_id')->where('invoice_id', '=', DB::raw("$invoice->invoice_ent_id"))->get();
			/* echo "<pre>";
			print_r($invoiceProducts);exit; */
			foreach ($invoiceProducts as $itemKey => $item) {
				$stoneData = InventoryHelper::getStoneData($item->product_id);
				if (empty($stoneData['type'])) {
					continue;
				}
				DB::setTablePrefix('dml_');
				$totalStoneWeight = 0;
				for ($index = 0; $index < count($stoneData['carat']); $index++) {
					$carat = isset($stoneData['carat'][$index]) ? $stoneData['carat'][$index] : 0;
					$stoneUse = isset($stoneData['stone_use'][$index]) ? $stoneData['stone_use'][$index] : 0;
					$totaldmdweight = (float) isset($stoneData['totalweight'][$index]) ? $stoneData['totalweight'][$index] : 0;
					//$totalStoneWeight += $carat * $stoneUse;
					$totalStoneWeight += $totaldmdweight;
				}

				//echo $totalStoneWeight;exit;
				InvoiceProducts::where("product_id", $item->product_id)->where('invoice_id', $invoice->invoice_ent_id)->update(array('stone_weight' => $totalStoneWeight));

			}
		}
	}
	//Generate approval memo number
	public function generateApprovalMemo(Request $request) {
		$params = $request->post();
		$approvalMemoId = isset($params['memo_id']) ? $params['memo_id'] : '';
		if (!empty($approvalMemoId)) {
			//check if approval number is already generated
			$approvalData = ApprovalMemo::select('approval_no')->where('id', '=', DB::raw("$approvalMemoId"))->first();

			if (isset($approvalData->approval_no) && !empty($approvalData->approval_no)) {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_approval_number_already_generated');
				echo json_encode($response);exit;
			}
			$lastData = ApprovalMemo::orderBy(DB::raw("CAST(approval_no AS SIGNED)"), 'desc')->select(DB::raw("approval_no"))->where('is_for_old_data', '=', 'no')->where('approval_no', '!=', DB::raw("0"))->first();
			//var_dump($lastData);exit;
			$approvalNumber = '';
			$approvalNumberConfigData = Setting::where('key', config('constants.settings.keys.approval_number'))->first('value');

			if (empty($lastData->approval_no)) {
				$approvalNumberConfigData = Setting::where('key', config('constants.settings.keys.approval_number'))->first('value');
				$approvalNumber = isset($approvalNumberConfigData->value) ? $approvalNumberConfigData->value : '';
			} else {
				if (isset($approvalNumberConfigData->value) && $approvalNumberConfigData->value > $lastData->approval_no) {
					$approvalNumber = $approvalNumberConfigData->value;
				} else {
					$approvalNumber = isset($lastData->approval_no) ? $lastData->approval_no + 1 : '';
				}
			}
			$result = ApprovalMemo::where("id", $approvalMemoId)->update(array('approval_no' => $approvalNumber, 'updated_at' => date('Y-m-d H:i:s')));
			$result = ApprovalMemoHistroy::where("approval_memo_id", $approvalMemoId)->update(array('approval_no' => $approvalNumber));
			if ($result) {
				$response['status'] = true;
				$response['message'] = Config::get('constants.message.inventory_approval_generated_success_message');
				if (date('m') > 6) {
					$fin_year = date('y') . '-' . (date('y') + 1);
				} else {
					$fin_year = (date('y') - 1) . '-' . date('y');
				}
				$response['approval_number'] = $fin_year . '/' . $approvalNumber;
			}
		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}
	//Cancel approval memo
	public function cancelApprovalMemo(Request $request) {
		$params = $request->post();
		$approvalMemoId = isset($params['memo_id']) ? $params['memo_id'] : '';

		if (!empty($approvalMemoId)) {
			$approvalData = ApprovalMemo::select('approval_no')->where('id', '=', DB::raw("$approvalMemoId"))->first();

			if (isset($approvalData->approval_no) && !empty($approvalData->approval_no)) {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_approval_number_already_cancelled');
				echo json_encode($response);exit;
			}
			//move products to showroom
			$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
			$inStatusVal = $inventoryStatus['in'];
			$approvalMemoProducts = ApprovalMemoHistroy::select('product_id')->where('approval_memo_id', '=', DB::raw("$approvalMemoId"))->get();
			foreach ($approvalMemoProducts as $key => $memoProduct) {
				InventoryHelper::changeInventoryStatus($memoProduct->product_id, $inStatusVal);
			}
			$result = ApprovalMemoHistroy::where("approval_memo_id", $approvalMemoId)->where('status', '=', DB::raw("'approval'"))->delete();
			$result = ApprovalMemo::where("id", $approvalMemoId)->delete();
			if ($result) {
				$response['status'] = true;
				$response['message'] = Config::get('constants.message.inventory_approval_cancellation_success_message');
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
	//Deliver approval products
	public function deliverApprovalMemo(Request $request) {
		$params = $request->post();
		$approvalMemoId = isset($params['memo_id']) ? $params['memo_id'] : '';

		if (!empty($approvalMemoId)) {
			$result = ApprovalMemo::where("id", $approvalMemoId)->update(array('is_delivered' => '1', 'updated_at' => date('Y-m-d H:i:s')));
			if ($result) {
				$response['status'] = true;
				$response['message'] = Config::get('constants.message.inventory_approval_delivery_success_message');
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
	//Update invoice billing/shipping address
	public function updateInvoiceAddress(Request $request) {
		$params = $request->post();
		$formData = array();
		parse_str($params['form_data'], $formData);
		$customerId = isset($formData['customer_id']) ? $formData['customer_id'] : '';
		$orderId = isset($formData['order_id']) ? $formData['order_id'] : '';
		$invoiceId = isset($formData['invoice_id']) ? $formData['invoice_id'] : '';
		$addressId = isset($formData['address_id']) ? $formData['address_id'] : '';
		$addressType = isset($formData['address_type']) ? $formData['address_type'] : '';
		$firstName = isset($formData['txtfirstname']) ? $formData['txtfirstname'] : '';
		$lastName = isset($formData['txtlastname']) ? $formData['txtlastname'] : '';
		$contactNumber = isset($formData['txtcontactnumber']) ? $formData['txtcontactnumber'] : '';
		$countryId = isset($formData['selectcountry']) ? $formData['selectcountry'] : '';
		$region = isset($formData['txtstateprovince']) ? $formData['txtstateprovince'] : '';
		$street = isset($formData['txtaddress']) ? $formData['txtaddress'] : '';
		$city = isset($formData['txtcity']) ? $formData['txtcity'] : '';
		$zipCode = isset($formData['txtzipcode']) ? $formData['txtzipcode'] : '';
		$gstin = isset($formData['txtgstin']) ? $formData['txtgstin'] : '';

		//check contact number exist
		//$isContactNumberExist = InventoryHelper::checkContactNumberValidation($customerId, $contactNumber);
		$isContactNumberExist = false;
		if ($isContactNumberExist) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_telephone_already_exist');
			echo json_encode($response);exit;
		}
		DB::setTablePrefix('dml_');

		//Update customer entity address start
		$updateAddressUrl = '';
		$updateOrderAddressUrl = '';
		if (App::environment('local')) {
			$updateAddressUrl = Config::get('constants.apiurl.local.update_customer_address');
			$updateOrderAddressUrl = Config::get('constants.apiurl.local.update_order_address');
		} else if (App::environment('test')) {
			$updateAddressUrl = Config::get('constants.apiurl.test.update_customer_address');
			$updateOrderAddressUrl = Config::get('constants.apiurl.test.update_order_address');
		} else {
			$updateAddressUrl = Config::get('constants.apiurl.live.update_customer_address');
			$updateOrderAddressUrl = Config::get('constants.apiurl.live.update_order_address');
		}
		$updateAddressParam = 'order_id=' . $orderId . '&invoice_id=' . $invoiceId . '&customer_id=' . $customerId . '&address_id=' . $addressId . '&address_type=' . $addressType . '&first_name=' . $firstName . '&last_name=' . $lastName . '&contact_number=' . $contactNumber . '&country=' . $countryId . '&state=' . $region . '&city=' . $city . '&zip_code=' . $zipCode . '&street=' . $street;

		//update customer address

		if (!empty($customerId) && !empty($addressId)) {
			$ch = curl_init($updateAddressUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $updateAddressParam);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$result = json_decode($result);

			$info = curl_getinfo($ch);
			if (curl_errno($ch)) {
				$error_msg = curl_error($ch);
			}
			if ($result->status) {
				$response['status'] = true;
				$response['message'] = $result->message;
			}
		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_default_failure_message');
		}
		//update order address

		if (!empty($customerId) && !empty($addressId)) {
			$ch = curl_init($updateOrderAddressUrl);
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
			if ($result->status) {
				//update Gstin number
				$gstinNumber = CustomersHelper::getGstinByCustomer($customerId);
				if (empty($gstinNumber) && !empty($gstin)) {
					$rs = CustomersHelper::addCustomerAttributeValue($customerId, 'gstin', $gstin);
				} else {
					if (!empty($gstin)) {
						$rs = CustomersHelper::updateCustomerAttributeValue($customerId, 'gstin', $gstin);
					}
				}

				//update gst
				if (!empty($region)) {
					DB::setTablePrefix('');
					$state = DB::table('directory_country_region')->select('default_name')->where('region_id', '=', DB::raw("'$region'"))->get()->first();
					$state = isset($state->default_name) ? $state->default_name : '';
					$gstPercentage = 0;
					$sgstPercentage = 0;
					$cgstPercentage = 0;
					DB::setTablePrefix('dml_');
					if (!empty($state) && $state == 'Maharashtra') {
						//Get SGST
						$sgst = DB::table('settings')->select('value')->where('key', '=', DB::raw("'SGST_PERCENTAGE'"))->get()->first();
						$sgstPercentage = isset($sgst->value) ? $sgst->value : 0;

						//Get CGST
						$cgst = DB::table('settings')->select('value')->where('key', '=', DB::raw("'CGST_PERCENTAGE'"))->get()->first();
						$cgstPercentage = isset($cgst->value) ? $cgst->value : 0;

						$gstPercentage = (float) $sgstPercentage + (float) $cgstPercentage;

					} else {
						//Get IGST
						$igst = DB::table('settings')->select('value')->where('key', '=', DB::raw("'IGST_PERCENTAGE'"))->get()->first();
						$igstPercentage = isset($igst->value) ? $igst->value : 0;

						$gstPercentage = $igstPercentage;
					}
					//update invoice
					$sql = "update sales_flat_invoice set gst_percentage=" . $gstPercentage . " WHERE entity_id=" . $invoiceId;
					DB::statement($sql);
					$inviceItems = InventoryHelper::getInvoiceItems($invoiceId);
					foreach ($inviceItems as $key => $item) {
						$data = array('cgst_percentage' => $cgstPercentage, 'sgst_percentage' => $sgstPercentage);
						InvoiceProducts::where('product_id', '=', DB::raw("$item->product_id"))->where('invoice_id', '=', DB::raw("$invoiceId"))->update($data);
					}
					InvoiceLogs::create(
						array(
							'user_id' => Auth::user()->id,
							'invoice_id' => $invoiceId,
							'product_data' => json_encode($inviceItems),
							'comment' => config('constants.message.invoice_address_updated'),
							'created_at' => date('Y-m-d H:i:s'),
							'updated_at' => date('Y-m-d H:i:s'),
						)
					);
				}
				$response['status'] = true;
				$response['message'] = $result->message;
			}

		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}

	//store preview approval data
	public function processPreviewMemo(Request $request) {
		$params = $request->post();
		//print_r($params);exit;
		$productIds = isset($params['product_ids']) ? $params['product_ids'] : '';
		$customerId = isset($params['customerId']) ? $params['customerId'] : '';
		$firstName = isset($params['txtfirstname']) ? $params['txtfirstname'] : '';
		$lastName = isset($params['txtlastname']) ? $params['txtlastname'] : '';
		$telephone = isset($params['txtcontactnumber']) ? $params['txtcontactnumber'] : '';
		$street = isset($params['txtaddress']) ? $params['txtaddress'] : '';
		$countryId = isset($params['selectcountry']) ? $params['selectcountry'] : '';
		$region = isset($params['txtstateprovince']) ? $params['txtstateprovince'] : '';
		$city = isset($params['txtcity']) ? $params['txtcity'] : '';
		$postcode = isset($params['txtzipcode']) ? $params['txtzipcode'] : '';
		$gstinNumber = isset($params['txtgstin']) ? $params['txtgstin'] : '';
		$isForOldData = isset($params['invoicememo_with_olddata']) ? $params['invoicememo_with_olddata'] : '';
		$panCardNumber = '';
		$approvalNumber = '';
		$approvalDate = '';
		$customerBillingAddress = array();
		if (!empty($productIds)) {
			if (strpos($productIds, ',') !== false) {
				$productIds = explode(',', $productIds);
			} else {
				$productIds = array($productIds);
			}
			if (!empty($customerId)) {
				$firstName = CustomersHelper::getCustomerFirstName($customerId);
				$lastName = CustomersHelper::getCustomerLastName($customerId);
				$customerBillingAddress = InventoryHelper::getDefaultBillingAddressByCustId($customerId);
				$street = isset($customerBillingAddress['street']) ? $customerBillingAddress['street'] : '';
				$city = isset($customerBillingAddress['city']) ? $customerBillingAddress['city'] : '';
				$region = isset($customerBillingAddress['region']) ? $customerBillingAddress['region'] : '';
				$postcode = isset($customerBillingAddress['postcode']) ? $customerBillingAddress['postcode'] : '';
				$telephone = isset($customerBillingAddress['telephone']) ? $customerBillingAddress['telephone'] : '';
				$panCardNumber = CustomersHelper::getCustomerAttrValue($customerId, 'pancardno');
				$gstinNumber = CustomersHelper::getCustomerAttrValue($customerId, 'gstin');
				$gstinNumber = !empty($gstinNumber) ? $gstinNumber : '';
			} else {
				DB::setTablePrefix('');
				$state = DB::table('directory_country_region')->select('default_name')->where('region_id', '=', DB::raw("'$region'"))->get()->first();
				$state = isset($state->default_name) ? $state->default_name : '';
				$customerBillingAddress['street'] = $street;
				$customerBillingAddress['city'] = $city;
				$customerBillingAddress['region'] = $state;
				$customerBillingAddress['postcode'] = $postcode;
				$customerBillingAddress['telephone'] = $telephone;
				$customerBillingAddress['pancard_number'] = '';
			}
			/* echo $firstName."<br>";
				echo $lastName."<br>";
			*/
			if (!empty($isForOldData)) {
				$approvalNumber = isset($params['txtmemonumber']) ? $params['txtmemonumber'] : '';
				$approvalDate = isset($params['txtapprovaldate']) ? date("M d, Y", strtotime($params['txtapprovaldate'])) : '';
			} else {
				$approval = ApprovalMemo::select('approval_no')->orderBy('approval_no', 'DESC')->limit(1)->get()->first();
				$currentYear = date('y');
				if (date('m') > 6) {
					$fin_year = date('y') . '-' . (date('y') + 1);
				} else {
					$fin_year = (date('y') - 1) . '-' . date('y');
				}
				$approvalNumber = $fin_year . '/' . ($approval->approval_no + 1);
				$approvalDate = date("M d, Y", strtotime(date('Y-m-d')));
			}
			$approvalData = array(
				'customer_id' => $customerId,
				'first_name' => $firstName,
				'last_name' => $lastName,
				'billing_address' => $customerBillingAddress,
				'gstin' => $gstinNumber,
				'approval_number' => $approvalNumber,
				'approval_date' => $approvalDate,
				'product_ids' => $productIds,
			);
			session()->put('preview_memo_date_' . Auth::user()->id, json_encode($approvalData));
			session()->save();
			$response['status'] = true;
		} else {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_generate_memo_product_not_selected');
		}
		DB::setTablePrefix('dml_');
		echo json_encode($response);exit;
	}
	//preview approval
	public function previewMemo() {
		$previewMemoData = session()->get('preview_memo_date_' . Auth::user()->id);
		$previewMemoData = json_decode($previewMemoData);
		return view('inventory.previewmemo', compact('previewMemoData'));
	}

	public function categorypdf(Request $request) {
		$data = $request->all();
		$id = $data['id'];
		$price = $data['price'];
		$expoProdcutIds = explode(",", $id);
		$prod = InventoryHelper::getAllProductsCollection(true);
		$prodColl = $prod->whereIn('entity_id', $expoProdcutIds);
		foreach ($prodColl as $prodCollkey => $prodCollVal) {
			$categoryId = $prodCollVal->category_id;
			$_categories = DB::select(DB::raw("SELECT value FROM `catalog_category_entity_varchar` WHERE `attribute_id` = 41 AND `store_id` = 0 AND `entity_id` = '" . $categoryId . "' "))[0];
			$category = $_categories->value;
			$tempArr[$category][] = $prodCollVal->entity_id;
		}
		$returnHTML = view('inventory.categorypdf', ['datas' => $tempArr, 'price' => $price])->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));
	}
}
