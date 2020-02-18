<?php
namespace App\Helpers;

use App;
use App\ApprovalMemoHistroy;
use App\CashVoucher;
use App\Exhibition;
use App\ExhibitionProducts;
use App\InvoiceProducts;
use App\Products;
use App\ProductsStone;
use App\QrcodeScanning;
use App\Quotation;
use App\ReturnMemo;
use App\Setting;
use Auth;
use Config;
use DB;
use Excel;
use Illuminate\Support\Facades\Cache;
use PDF;
use PHPExcel_Worksheet_Drawing;
use Session;

class InventoryHelper {
	//Get RTS product list
	public static function getRtsProducts() {
		$productIds = DB::select("select pr_id from qrcode_inventory_management");
		$productCollection = '';
		$productIdArr = array();
		foreach ($productIds as $key => $value) {
			$productIdArr[] = $value->pr_id;
		}
		if (!empty($productIds)) {
			$productIds = implode("','", $productIdArr);
			$productCollection = DB::select("SELECT `e`.*, `at_isreadytoship`.`value` AS `isreadytoship`, `at_is_sold`.`value` AS `is_sold`, `at_status`.`value` AS `status`, `at_custom_price`.`value` AS `custom_price` FROM `catalog_product_entity` AS `e` INNER JOIN `catalog_product_entity_int` AS `at_isreadytoship` ON (`at_isreadytoship`.`entity_id` = `e`.`entity_id`) AND (`at_isreadytoship`.`attribute_id` = '211') AND (`at_isreadytoship`.`store_id` = 0) INNER JOIN `catalog_product_entity_int` AS `at_is_sold` ON (`at_is_sold`.`entity_id` = `e`.`entity_id`) AND (`at_is_sold`.`attribute_id` = '322') AND (`at_is_sold`.`store_id` = 0) INNER JOIN `catalog_product_entity_int` AS `at_status` ON (`at_status`.`entity_id` = `e`.`entity_id`) AND (`at_status`.`attribute_id` = '96') AND (`at_status`.`store_id` = 0) INNER JOIN `catalog_product_entity_decimal` AS `at_custom_price` ON (`at_custom_price`.`entity_id` = `e`.`entity_id`) AND (`at_custom_price`.`attribute_id` = '346') AND (`at_custom_price`.`store_id` = 0) WHERE (at_isreadytoship.value = 1) AND (`e`.`type_id` = 'simple') AND (at_is_sold.value = '0') AND (at_status.value = '1') AND (at_custom_price.value IS NOT NULL) AND (`e`.`entity_id` NOT IN('" . $productIds . "') )");
		}
	}
	//Get inventory product list
	public static function getInventoryProducts() {
		$prod = '';
		DB::setTablePrefix('');
		$collection = InventoryHelper::getAllProductsCollection();
		$productCollection = $collection->take(10);
		$productData = array('totalCount' => $collection->count(), 'productCollection' => $productCollection);
		DB::setTablePrefix('dml_');
		return $productData;
	}

	public static function getInventoryProductsTmp() {
		$prod = "";
		DB::setTablePrefix('');
		$collection = InventoryHelper::getAllProductsCollectionTmp();
		$productCollection = $collection->take(10);
		$productData = array('totalCount' => $collection->count(), 'productCollection' => $productCollection);
		DB::setTablePrefix('dml_');
		return $productData;
	}

	//Count of product image
	public static function getProductImageCount($inventoryStatus = null) {
		$prod = '';
		DB::setTablePrefix('');
		$collection = InventoryHelper::getAllProductsCollection();

		$prodImgWith = array();
		$prodImgWithout = array();
		if (!empty($inventoryStatus)) {
			$inventoryStatusArr = InventoryHelper::getInventoryStatusOptions();
			$outStatusVal = $inventoryStatusArr['out'];

			if ($inventoryStatus == 'pending') {
				$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select(DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'0'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get()->first();
				$productIds = isset($pendingStock->product_ids) ? $pendingStock->product_ids : '';
				$productIds = explode(',', $productIds);
				$collection = $collection->whereIn("entity_id", $productIds);
			} else if ($inventoryStatus == $outStatusVal) {
				$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select(DB::raw('GROUP_CONCAT(memo_histroy.product_id) AS product_ids'))->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'1'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get()->first();
				$productIds = isset($pendingStock->product_ids) ? $pendingStock->product_ids : '';
				$productIds = explode(',', $productIds);
				$collection = $collection->where("inventory_status", $outStatusVal);
				$collection = $collection->whereIn("entity_id", $productIds);
			} else {
				$collection = $collection->where("inventory_status", $inventoryStatus);
			}
		}

		foreach ($collection as $key => $prodvalue) {
			$productimages = $prodvalue->product_image;
			if (!empty($productimages)) {
				$prodImgWith[] = $prodvalue->product_image;
			} else {
				$prodImgWithout[] = $prodvalue->product_image;
			}
		}
		DB::setTablePrefix('dml_');
		$data = array('with_image' => count($prodImgWith), 'without_image' => count($prodImgWithout));
		return $data;
	}

	public static function getAllProductsCollectionTmp($isAjax = false) {

		if (App::environment('local')) {
			$our_categories_exp = config('constants.fixIds.local.category_ids');
		} else {
			$our_categories_exp = config('constants.fixIds.live.category_ids');
		}

		$our_categories_for_query = implode("','", $our_categories_exp);

		$all_products = Cache::get('all_products', '');
		$all_products_ajax = Cache::get('all_products_ajax', '');

		if ($isAjax) {

			if (!empty($all_products_ajax)) {
				//echo "true again";exit;
				return $all_products_ajax;
			}
		} else {
			if (!empty($all_products)) {
				return $all_products;
			}
		}
		//$prod = '';
		if (!$isAjax) {
			//DB::setTablePrefix('');
		}

		$prod = DB::table('dml_catalog_product_flat_1 as e')
			->select('e.entity_id', 'e.sku', 'e.name as product_name', 'e.stone_shape', 'e.certificate_no', 'e.approval_memo_generated', 'e.approval_invoice_generated', 'e.return_memo_generated', 'e.type_id', 'e.attribute_set_id', 'e.isreadytoship', 'e.rts_position', 'e.rts_stone_quality', 'e.status', 'e.custom_price', 'e.inventory_status', 'e.inventory_status_value', 'inventory_management.pr_name', 'catalog_category_product.category_id', 'e.metal_quality', 'e.virtual_product_manager', 'e.metal_quality_value', 'dml_grp_stone.total_carat', DB::raw('GROUP_CONCAT(DISTINCT dml_grp_stone.stone_shape) AS diamond_shape'), 'dml_grp_metal.metal_weight', 'e.is_returned')
		//->rightJoin('qrcode_inventory_management as inventory_management', 'e.entity_id', '=', 'inventory_management.pr_id')
			->join('dml_catalog_category_product', 'e.entity_id', '=', 'dml_catalog_category_product.product_id')
			->leftJoin('dml_grp_stone', 'dml_grp_stone.stone_product_id', '=', 'e.entity_id')
			->leftJoin('dml_grp_metal', 'dml_grp_metal.metal_product_id', '=', 'e.entity_id')
			->where('e.status', '=', DB::raw('1'))
			->where('e.isreadytoship', '=', DB::raw('1'))
			->where('e.type_id', '=', DB::raw('"simple"'))
			->where('e.custom_price', '!=', DB::raw('0'))
			->where('e.custom_price', '!=', DB::raw('""'))
			->whereIn('category_id', [DB::raw("'" . $our_categories_for_query . "'")])
			->orderBy('e.entity_id', 'desc')
			->groupBy('e.entity_id');

		//var_dump($prod->get());exit;

		$collection = collect($prod->get());
		if ($isAjax) {
			//var_dump($prod->toSql());exit;
			Cache::put('all_products_ajax', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));
			//DB::setTablePrefix('dml_');
			return $collection;
		} else {

			Cache::put('all_products', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));
			//DB::setTablePrefix('dml_');
			return $collection;
		}

	}
	//Get inventory product list
	public static function getInventoryQuery($isAjax = false) {
		//$our_categories = "14,287,6,7,8,9,124,289,290,195,43,293,165,295";
		//$our_categories_exp = explode(',', $our_categories);

		//$our_categories_for_query = '';
		//$our_categories_for_query = implode("','", $our_categories_exp);
		if (App::environment('local')) {
			$our_categories_exp = config('constants.fixIds.local.category_ids');
		} else {
			$our_categories_exp = config('constants.fixIds.live.category_ids');
		}

		$our_categories_for_query = implode("','", $our_categories_exp);

		$all_products = Cache::get('all_products', '');
		$all_products_ajax = Cache::get('all_products_ajax', '');

		if ($isAjax) {

			if (!empty($all_products_ajax)) {
				//echo "true again";exit;
				//return $all_products_ajax;
			} else if (!empty($all_products)) {
				//return $all_products;
			}
		} else {
			if (!empty($all_products)) {
				//return $all_products;
			}
		}
		//$prod = '';
		if (!$isAjax) {
			DB::setTablePrefix('');
		}

		$prod = DB::table('catalog_product_flat_1 as e')
			->select('e.entity_id', 'e.sku', 'e.name as product_name', 'e.stone_shape', 'e.certificate_no', 'e.approval_memo_generated', 'e.approval_invoice_generated', 'e.return_memo_generated', 'e.type_id', 'e.attribute_set_id', 'e.isreadytoship', 'e.rts_position', 'e.rts_stone_quality', 'e.status', 'e.custom_price', 'e.inventory_status', 'e.inventory_status_value', 'inventory_management.pr_name', 'catalog_category_product.category_id', 'e.metal_quality', 'e.virtual_product_manager', 'e.metal_quality_value', 'grp_stone.total_carat', DB::raw('GROUP_CONCAT(DISTINCT grp_stone.stone_shape) AS diamond_shape'), 'grp_metal.metal_weight', 'e.is_returned', 'e.small_image as product_image', 'e.created_at', DB::raw('1 AS qrcode_img')) //'qrcode.qrcode_img as qrcode_img'
			->rightJoin('qrcode_inventory_management as inventory_management', 'e.entity_id', '=', 'inventory_management.pr_id')
			->join('catalog_category_product', 'e.entity_id', '=', 'catalog_category_product.product_id')
			->leftJoin('grp_stone', 'grp_stone.stone_product_id', '=', 'e.entity_id')
			->leftJoin('grp_metal', 'grp_metal.metal_product_id', '=', 'e.entity_id')
			->join('qrcode', 'qrcode.product_id', '=', 'e.entity_id')
		//->leftJoin('catalog_product_entity_media_gallery', 'catalog_product_entity_media_gallery.entity_id', '=', 'e.entity_id')
			->where('e.status', '=', DB::raw('1'))
			->where('e.isreadytoship', '=', DB::raw('1'))
			->where('e.type_id', '=', DB::raw('"simple"'))
			->where('e.custom_price', '!=', DB::raw('0'))
			->where('e.custom_price', '!=', DB::raw('""'))
			->whereIn('category_id', [DB::raw("'" . $our_categories_for_query . "'")])
			->orderBy('e.entity_id', 'desc')
			->groupBy('e.entity_id');

		$collection = collect($prod->get());
		if ($isAjax) {
			//var_dump($prod->toSql());exit;
			//Cache::put('all_products_ajax', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));
			//DB::setTablePrefix('dml_');
			return $collection;
		} else {
			var_dump($prod->toSql());exit;
			//Cache::put('all_products', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));
			DB::setTablePrefix('dml_');
			return $collection;
		}

	}

	//Get inventory product list
	public static function getAllProductsCollection($isAjax = false) {
		//$our_categories = "14,287,6,7,8,9,124,289,290,195,43,293,165,295";
		//$our_categories_exp = explode(',', $our_categories);

		//$our_categories_for_query = '';
		//$our_categories_for_query = implode("','", $our_categories_exp);
		if (App::environment('local')) {
			$our_categories_exp = config('constants.fixIds.local.category_ids');
		} else {
			$our_categories_exp = config('constants.fixIds.live.category_ids');
		}

		$our_categories_for_query = implode("','", $our_categories_exp);

		$all_products = Cache::get('all_products', '');
		$all_products_ajax = Cache::get('all_products_ajax', '');

		if ($isAjax) {

			if (!empty($all_products_ajax)) {
				//echo "true again";exit;
				return $all_products_ajax;
			} else if (!empty($all_products)) {
				return $all_products;
			}
		} else {
			if (!empty($all_products)) {
				return $all_products;
			}
		}
		//$prod = '';
		if (!$isAjax) {
			DB::setTablePrefix('');
		}

		$prod = DB::table('catalog_product_flat_1 as e')
			->select('e.entity_id', 'e.sku', 'e.name as product_name', 'e.stone_shape', 'e.certificate_no', 'e.approval_memo_generated', 'e.approval_invoice_generated', 'e.return_memo_generated', 'e.type_id', 'e.attribute_set_id', 'e.isreadytoship', 'e.rts_position', 'e.rts_stone_quality', 'e.status', 'e.custom_price', 'e.inventory_status', 'e.inventory_status_value', 'inventory_management.pr_name', 'catalog_category_product.category_id', 'e.metal_quality', 'e.virtual_product_manager', 'e.metal_quality_value', 'grp_stone.total_carat', DB::raw('GROUP_CONCAT(DISTINCT grp_stone.stone_shape) AS diamond_shape'), 'grp_metal.metal_weight', 'e.is_returned', 'e.small_image as product_image', 'e.created_at', 'qrcode.qrcode_img as qrcode_img')
			->rightJoin('qrcode_inventory_management as inventory_management', 'e.entity_id', '=', 'inventory_management.pr_id')
			->join('catalog_category_product', 'e.entity_id', '=', 'catalog_category_product.product_id')
			->leftJoin('grp_stone', 'grp_stone.stone_product_id', '=', 'e.entity_id')
			->leftJoin('grp_metal', 'grp_metal.metal_product_id', '=', 'e.entity_id')
			->leftJoin('qrcode', 'qrcode.product_id', '=', 'e.entity_id')
		//->leftJoin('catalog_product_entity_media_gallery', 'catalog_product_entity_media_gallery.entity_id', '=', 'e.entity_id')
			->where('e.status', '=', DB::raw('1'))
			->where('e.isreadytoship', '=', DB::raw('1'))
			->where('e.type_id', '=', DB::raw('"simple"'))
			->where('e.custom_price', '!=', DB::raw('0'))
			->where('e.custom_price', '!=', DB::raw('""'))
			->whereIn('category_id', [DB::raw("'" . $our_categories_for_query . "'")])
			->orderBy('e.entity_id', 'desc')
			->groupBy('e.entity_id');
		//echo $prod->toSql();exit;
		$collection = collect($prod->get());
		if ($isAjax) {
			//var_dump($prod->toSql());exit;
			Cache::put('all_products_ajax', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));
			//DB::setTablePrefix('dml_');
			return $collection;
		} else {

			Cache::put('all_products', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));
			DB::setTablePrefix('dml_');
			return $collection;
		}

	}
	//Get virtual product position
	public static function getVirtualProdPosition($certificate) {
		//echo "SELECT product_manager_name FROM grp_productmanager WHERE product_manager_certificates LiKE '%" . $certificate . "%'";exit;
		$result = DB::select("SELECT product_manager_name FROM grp_productmanager WHERE product_manager_certificates LiKE '%" . $certificate . "%'");
		//print_r($result);exit;
		return isset($result[0]->product_manager_name) ? $result[0]->product_manager_name : '';
	}
	//Get order data by product id
	public static function getOrderByProduct($productId, $status) {
		//$productId='1257380';

		$orderData = DB::select("SELECT `main_table`.*, `od`.`status`,`od`.`customer_firstname`,`od`.`customer_lastname` FROM `sales_flat_order_item` AS `main_table` INNER JOIN `sales_flat_order` AS `od` ON od.entity_id = main_table.order_id WHERE (product_id = " . $productId . ") AND (od.status = '" . $status . "')");
		return $orderData;
	}
	//Get total inventory status product count
	public static function getTotalinventoryInOutCount($status) {
		$inventoryStatusOption = DB::select("SELECT EAOV.value,EA.attribute_id FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0 AND EAOV.option_id=" . $status);

		$inventory_status = '';
		$attributeId = '';
		foreach ($inventoryStatusOption as $key => $value) {
			$inventory_status = strtolower($value->value);
			$attributeId = $value->attribute_id;
		}
		$inventoryStatus = InventoryHelper::getInventoryStatusOptions();

		$outStatusVal = $inventoryStatus['out'];
		$outForExhibitionStatusVal = $inventoryStatus['outforexhibition'];
		//$totalCount = DB::select("select count(*) as total_product from qrcode_inventory_management qim left join catalog_product_entity_int cf1 on cf1.entity_id = qim.pr_id where cf1.attribute_id=" . $attributeId . " AND cf1.value = " . $status);
		if (App::environment('local')) {
			$our_categories_exp = config('constants.fixIds.local.category_ids');
		} else {
			$our_categories_exp = config('constants.fixIds.live.category_ids');
		}

		$our_categories_for_query = implode("','", $our_categories_exp);
		$approvalPrdJoin = '';
		$approvalPrdWhere = '';

		$statusValue = '';

		if ($outStatusVal == $status) {
			/* $approvalPrdJoin = 'JOIN dml_approval_memo_histroy AS memo_histroy ON memo_histroy.product_id = ce.entity_id JOIN dml_approval_memo AS memo ON memo.id = memo_histroy.approval_memo_id';
			$approvalPrdWhere = " AND memo_histroy.status = 'approval'"; */
			$statusValue = $outStatusVal . "','" . $outForExhibitionStatusVal;
		} else {
			$statusValue = $status;
		}
		//echo $statusValue;exit;
		$totalCount = DB::select("select count(*) as total_product from catalog_product_flat_1 as ce JOIN catalog_category_product ON catalog_category_product.product_id = ce.entity_id RIGHT JOIN qrcode_inventory_management AS inventory_management ON inventory_management.pr_id = ce.entity_id WHERE ce.inventory_status IN('" . $statusValue . "') AND ce.status=1 AND ce.isreadytoship=1 AND ce.type_id='simple' AND ce.custom_price!=0 AND ce.custom_price!='' AND category_id IN('" . $our_categories_for_query . "')");
		return isset($totalCount[0]->total_product) ? $totalCount[0]->total_product : '';
	}
	//get min & max price for filer
	public static function getMinMaxPriceForFilter() {
		/*DB::setTablePrefix('');
			$price = DB::select("SELECT MIN(`e`.`custom_price`) AS min_price, MAX(`e`.`custom_price`) AS max_price FROM `catalog_product_flat_1` AS `e` RIGHT JOIN `qrcode_inventory_management` AS `inventory_management` ON e.entity_id = inventory_management.pr_id WHERE (e.status = 1) AND (e.isreadytoship = 1) AND (e.type_id = 'simple') AND (e.status = '1')");
			DB::setTablePrefix('dml_');
		*/
		$return_array = array();

		$prod = InventoryHelper::getAllProductsCollection();
		//dd($prod);exit;

		$min_price = $prod->pluck(['custom_price'])->min();
		$max_price = $prod->pluck(['custom_price'])->max();

		$return_array['min_price'] = $min_price;
		$return_array['max_price'] = $max_price;

		return $return_array;
	}
	//Get min & max diamond weight
	public static function getMinMaxDiamondWeight($filteredProducts = null, $inventoryStatus = null) {
		DB::setTablePrefix('');
		if (!empty($filteredProducts)) {
			$filteredProductIds = implode("','", $filteredProducts);
			$diamondWeight = DB::select("select min(grp_stone.total_carat) as min_weight, max(grp_stone.total_carat) as max_weight from grp_stone join catalog_product_flat_1 as ce on ce.entity_id = grp_stone.stone_product_id where ce.entity_id IN('" . $filteredProductIds . "')");
		} else {
			if (!empty($inventoryStatus)) {
				if ($inventoryStatus == 'pending') {
					$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'0'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
					$pendingPrdIds = array();
					foreach ($pendingStock as $product) {
						$pendingPrdIds[] = $product->product_id;
					}
					$productIds = implode("','", $pendingPrdIds);
					$diamondWeight = DB::select("select min(grp_stone.total_carat) as min_weight, max(grp_stone.total_carat) as max_weight from grp_stone join catalog_product_flat_1 as ce on ce.entity_id = grp_stone.stone_product_id where ce.entity_id IN('" . $productIds . "')");
				} else if ($inventoryStatus == 'Out') {
					$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'1'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
					$pendingPrdIds = array();
					foreach ($pendingStock as $product) {
						$pendingPrdIds[] = $product->product_id;
					}
					$productIds = implode("','", $pendingPrdIds);
					$diamondWeight = DB::select("select min(grp_stone.total_carat) as min_weight, max(grp_stone.total_carat) as max_weight from grp_stone join catalog_product_flat_1 as ce on ce.entity_id = grp_stone.stone_product_id where ce.entity_id IN('" . $productIds . "') AND ce.inventory_status=" . $inventoryStatus);
				} else {
					$diamondWeight = DB::select("select min(grp_stone.total_carat) as min_weight, max(grp_stone.total_carat) as max_weight from grp_stone join catalog_product_flat_1 as ce on ce.entity_id = grp_stone.stone_product_id where ce.inventory_status=" . $inventoryStatus);
				}
			} else {
				$diamondWeight = DB::select("select min(grp_stone.total_carat) as min_weight, max(grp_stone.total_carat) as max_weight from grp_stone join catalog_product_flat_1 as ce on ce.entity_id = grp_stone.stone_product_id");
			}
		}
		DB::setTablePrefix('dml_');
		return $diamondWeight;
	}
	//Get min & max metal weight
	public static function getMinMaxMetalWeight($filteredProducts = null, $inventoryStatus = null) {
		DB::setTablePrefix('');

		if (!empty($filteredProducts)) {
			$filteredProductIds = implode("','", $filteredProducts);
			$metalWeight = DB::select("select min(grp_metal.metal_weight) as min_weight, max(grp_metal.metal_weight) as max_weight from grp_metal join catalog_product_flat_1 as ce on ce.entity_id = grp_metal.metal_product_id  WHERE ce.entity_id IN('" . $filteredProductIds . "')");
		} else {
			if (!empty($inventoryStatus)) {
				if ($inventoryStatus == 'pending') {
					$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'0'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
					$pendingPrdIds = array();
					foreach ($pendingStock as $product) {
						$pendingPrdIds[] = $product->product_id;
					}
					$productIds = implode("','", $pendingPrdIds);

					$metalWeight = DB::select("select min(grp_metal.metal_weight) as min_weight, max(grp_metal.metal_weight) as max_weight from grp_metal join catalog_product_flat_1 as ce on ce.entity_id = grp_metal.metal_product_id where ce.entity_id IN('" . $productIds . "')");
				} else if ($inventoryStatus == 'Out') {
					$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'1'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
					$pendingPrdIds = array();
					foreach ($pendingStock as $product) {
						$pendingPrdIds[] = $product->product_id;
					}
					$productIds = implode("','", $pendingPrdIds);
					$metalWeight = DB::select("select min(grp_metal.metal_weight) as min_weight, max(grp_metal.metal_weight) as max_weight from grp_metal join catalog_product_flat_1 as ce on ce.entity_id = grp_metal.metal_product_id where ce.entity_id IN('" . $productIds . "') AND ce.inventory_status=" . $inventoryStatus);
				} else {
					$metalWeight = DB::select("select min(grp_metal.metal_weight) as min_weight, max(grp_metal.metal_weight) as max_weight from grp_metal join catalog_product_flat_1 as ce on ce.entity_id = grp_metal.metal_product_id where ce.inventory_status=" . $inventoryStatus);
				}
			} else {
				$metalWeight = DB::select("select min(grp_metal.metal_weight) as min_weight, max(grp_metal.metal_weight) as max_weight from grp_metal join catalog_product_flat_1 as ce on ce.entity_id = grp_metal.metal_product_id");
			}

		}
		DB::setTablePrefix('dml_');
		return $metalWeight;
	}
	//Get all virtual product manager for filter
	public static function getAllVirtualProductManagers() {
		$productManager = DB::select("select product_manager_id,product_manager_name from grp_productmanager where product_manager_name!=''");
		return $productManager;
	}
	public static function getCategoryFilterCollection() {
		$all_categories = Cache::get('all_categories', '');

		if (!empty($all_categories)) {
			return $all_categories;
		}

		//Get root category id
		$rootCategoryId = DB::select("SELECT entity_id FROM catalog_category_flat_store_1 WHERE level=1");
		$rootCategoryId = $rootCategoryId[0]->entity_id;

		//Get Category by root category
		$categories = DB::select("SELECT DISTINCT catalog_category_flat_store_1.entity_id,catalog_category_flat_store_1.name FROM catalog_category_flat_store_1 JOIN catalog_category_product ON catalog_category_product.category_id=catalog_category_flat_store_1.entity_id WHERE catalog_category_flat_store_1.parent_id=" . $rootCategoryId);

		$collection = collect($categories);

		Cache::put('all_categories', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));

		return $collection;
	}

	public static function getGoldQualityCollection() {
		$all_metal_purity_master = Cache::get('all_metal_purity_master', '');
		$all_metal_purity = Cache::get('all_metal_purity', '');
		$all_metal_color_master = Cache::get('all_metal_color_master', '');
		$all_metal_color = Cache::get('all_metal_color', '');

		$result = array();

		if (!empty($all_metal_purity) && !empty($all_metal_color)) {
			$result['all_metal_purity_master'] = $all_metal_purity_master;
			$result['all_metal_color_master'] = $all_metal_color_master;
			$result['all_metal_purity'] = $all_metal_purity;
			$result['all_metal_color'] = $all_metal_color;
		} else {
			//Get Metal Ids by Purity
			$categories_purity = DB::select("SELECT grp_metal_quality_id, metal_quality FROM grp_metal_quality");

			$all_metal_purity_master = array();
			$all_metal_purity = array();
			$K14ID = 0;
			$K18ID = 3;

			$all_metal_color_master = array();
			$all_metal_color = array();
			$YG = 0;
			$WG = 1;
			$RG = 2;
			$TWT = 6;
			$P95 = 8;
			$THT = 9;

			foreach ($categories_purity as $cate_pure_key => $cate_pure) {
				if (stripos($cate_pure->metal_quality, '14K') !== false) {
					$all_metal_purity_master[] = '14K';
					$all_metal_purity[$K14ID]['ID'] = '14K';
					$all_metal_purity[$K14ID]['VALUES'][] = $cate_pure->grp_metal_quality_id;
				} elseif (stripos($cate_pure->metal_quality, '18K') !== false) {
					$all_metal_purity_master[] = '18K';
					$all_metal_purity[$K18ID]['ID'] = '18K';
					$all_metal_purity[$K18ID]['VALUES'][] = $cate_pure->grp_metal_quality_id;
				}

				if (stripos($cate_pure->metal_quality, 'Rose Gold') !== false) {
					$all_metal_color_master[] = 'Rose Gold';
					$all_metal_color[$RG]['ID'] = 'Rose Gold';
					$all_metal_color[$RG]['VALUES'][] = $cate_pure->grp_metal_quality_id;
				} elseif (stripos($cate_pure->metal_quality, 'Three Tone') !== false || stripos($cate_pure->metal_quality, 'Threetone') !== false) {
					$all_metal_color_master[] = 'Three Tone';
					$all_metal_color[$THT]['ID'] = 'Three Tone';
					$all_metal_color[$THT]['VALUES'][] = $cate_pure->grp_metal_quality_id;
				} elseif (stripos($cate_pure->metal_quality, 'Two Tone') !== false) {
					$all_metal_color_master[] = 'Two Tone';
					$all_metal_color[$TWT]['ID'] = 'Two Tone';
					$all_metal_color[$TWT]['VALUES'][] = $cate_pure->grp_metal_quality_id;
				} elseif (stripos($cate_pure->metal_quality, 'White Gold') !== false) {
					$all_metal_color_master[] = 'White Gold';
					$all_metal_color[$WG]['ID'] = 'White Gold';
					$all_metal_color[$WG]['VALUES'][] = $cate_pure->grp_metal_quality_id;
				} elseif (stripos($cate_pure->metal_quality, 'Yellow Gold') !== false) {
					$all_metal_color_master[] = 'Yellow Gold';
					$all_metal_color[$YG]['ID'] = 'Yellow Gold';
					$all_metal_color[$YG]['VALUES'][] = $cate_pure->grp_metal_quality_id;
				} elseif (stripos($cate_pure->metal_quality, 'Platinum(950)') !== false) {
					$all_metal_color_master[] = 'Platinum(950)';
					$all_metal_color[$P95]['ID'] = 'Platinum(950)';
					$all_metal_color[$P95]['VALUES'][] = $cate_pure->grp_metal_quality_id;
				}
			}

			//var_dump(array_unique($all_metal_purity));
			//var_dump(array_unique($all_metal_color));
			//exit;

			//$collection = collect($categories);
			$all_metal_purity_master = array_unique($all_metal_purity_master);
			$all_metal_color_master = array_unique($all_metal_color_master);

			Cache::put('all_metal_purity_master', $all_metal_purity_master, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));
			Cache::put('all_metal_purity', $all_metal_purity, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));
			Cache::put('all_metal_color_master', $all_metal_color_master, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));
			Cache::put('all_metal_color', $all_metal_color, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));

			$result['all_metal_purity_master'] = $all_metal_purity_master;
			$result['all_metal_color_master'] = $all_metal_color_master;
			$result['all_metal_purity'] = $all_metal_purity;
			$result['all_metal_color'] = $all_metal_color;
		}

		return $result;
	}

	public static function getMetalFilters($filtername = 'all_metal_purity', $filteredIds) {

		${$filtername} = array();

		$filterCollection = InventoryHelper::getGoldQualityCollection();
		//dd($filterCollection);
		$filterCollectionType = $filterCollection[$filtername];
		foreach ($filterCollectionType as $fctKey => $fctValue) {
			$typeValues = $fctValue['VALUES'];
			foreach ($typeValues as $typeKey => $typeValue) {
				if (in_array($typeValue, $filteredIds)) {
					${$filtername}[] = $filterCollection[$filtername . '_master'][$fctKey];
				}
			}
		}

		${$filtername} = array_unique(${$filtername});

		return ${$filtername};
	}

	public static function getVirtualBoxCollection() {
		$virtual_box_collection = Cache::get('virtual_box_collection', '');

		if (!empty($virtual_box_collection)) {
			return $virtual_box_collection;
		}

		$virtual_box_collection = DB::select("SELECT product_manager_id,product_manager_name, product_manager_certificates FROM grp_productmanager WHERE IFNULL(product_manager_certificates , '')  <> ''");

		Cache::put('virtual_box_collection', $virtual_box_collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));

		return $virtual_box_collection;
	}

	public static function getVirtualBoxFilteredCollection($filteredCertificates) {
		//$t1 = microtime(true);
		$result = array();
		$filterCollection = InventoryHelper::getVirtualBoxCollection();

		//dd($filterCollection);exit;
		foreach ($filterCollection as $fcKey => $fcValue) {
			$certicates = explode(',', $fcValue->product_manager_certificates);
			//if(in_array(, haystack))

			$matchedArr = array_intersect($certicates, $filteredCertificates);
			if (count($matchedArr) > 0) {
				$result[$fcValue->product_manager_id] = $fcValue->product_manager_name;
			}

		}

		//$t2 = microtime(true);
		//printf("DONE!\n");
		//printf("Time elapsed: %.5f \n", $t2 - $t1);
		//exit;

		return $result;
	}

	//Get category list for filter
	public static function getCategoryFilter($catgry = array(), $filtered_products = false) {
		$categoryHtml = "";
		$categoryfiltered = false;
		$options = "";
		if (isset($catgry) && $catgry != 0) {
			$categoryfiltered = true;
		}
		$selectedcategory = array();
		$selectedcategory = $catgry;
		// //Get root category id
		// $rootCategoryId = DB::select("SELECT entity_id FROM catalog_category_flat_store_1 WHERE level=1");
		// $rootCategoryId = $rootCategoryId[0]->entity_id;

		// //Get Category by root category
		// $categories = DB::select("SELECT DISTINCT catalog_category_flat_store_1.entity_id,catalog_category_flat_store_1.name FROM catalog_category_flat_store_1 JOIN catalog_category_product ON catalog_category_product.category_id=catalog_category_flat_store_1.entity_id WHERE catalog_category_flat_store_1.parent_id=" . $rootCategoryId);

		$categories = InventoryHelper::getCategoryFilterCollection();

		$categoryHtml = "<div class='dropdown' id='category_area'>";
		$categoryHtml .= "<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Category<span class='caret'></span></button>";
		$categoryHtml .= "<ul class='dropdown-menu'>";
		if (!empty($filtered_products)) {
			$filtered_products_imp = implode("','", $filtered_products);
			$exist_categories_in_results = DB::select("select distinct category_id from catalog_category_product where product_id IN ('" . $filtered_products_imp . "')");
			//	print_r($exist_categories_in_results);exit;
		}
		//if (count($categories) > 0) {
		foreach ($categories as $key => $value) {
			if (!empty($exist_categories_in_results)) {
				$categoryIds = array();
				foreach ($exist_categories_in_results as $key => $cat) {
					$categoryIds[] = $cat->category_id;
				}
				if (!empty($selectedcategory) && in_array($value->entity_id, array_values($selectedcategory)) && in_array($value->entity_id, $categoryIds)) {
					if (strtolower($value->name) != 'dmlstock') {
						$categoryHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' data-filtertype=" . $value->entity_id . " checked name='category_chkbox' class='category_chkbox' value=" . $value->entity_id . "><span class='label-text'>" . ucwords(strtolower($value->name)) . "</span></label></li>";
					}

				} elseif (in_array($value->entity_id, $categoryIds)) {
					if ($value->name != 'DML RTS') {
						if (strtolower($value->name) != 'dmlstock') {
							$categoryHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' data-filtertype=" . $value->entity_id . " name='category_chkbox' class='category_chkbox' value=" . $value->entity_id . "><span class='label-text'>" . ucwords(strtolower($value->name)) . "</span></label></li>";
						}

					}
				}
			} else {
				if (!empty($selectedcategory) && in_array($value->entity_id, array_values($selectedcategory))) {
					if (strtolower($value->name) != 'dmlstock') {
						$categoryHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' data-filtertype=" . $value->entity_id . " name='category_chkbox' checked class='category_chkbox' value=" . $value->entity_id . ">";
					}

				} else {
					if ($value->name != 'DML RTS') {
						if (strtolower($value->name) != 'dmlstock') {
							$categoryHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' data-filtertype=" . $value->entity_id . " name='category_chkbox' class='category_chkbox' value=" . $value->entity_id . ">";
						}

					}
				}
				if ($value->name != 'DML RTS') {
					if (strtolower($value->name) != 'dmlstock') {
						$categoryHtml .= "<span class='label-text'>" . ucwords(strtolower($value->name)) . "</span></label></li>";
					}

				}
			}
		}
		//}
		$categoryHtml .= "</ul></div>";
		return $categoryHtml;
	}
	//Get gold purity
	public static function getGoldPurity($selected = array(), $filtered_products_purities = null) {
		$goldPurityHtml = "";

		$goldPurityHtml = "<div class='dropdown' id='goldpurity_area'>";
		$goldPurityHtml .= "<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Gold Purity<span class='caret'></span></button>";
		$goldPurityHtml .= "<ul class='dropdown-menu'>";
		if (!empty($filtered_products_purities)) {
			//echo "test";exit;
			//dd($filtered_products_purities);
			//exit;
			foreach ($filtered_products_purities as $key => $purity) {
				$selected_str = '';
				if (!empty($selected) && in_array($purity, $selected)) {
					$selected_str = 'checked';
				}

				//var_dump($purity);
				//var_dump($selected_str);
				//echo "dfglmdflmgk";exit;
				$goldPurityHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' " . $selected_str . " name='metalQualityChkbox' value='" . $purity . "' class='chk_metalquality'/><span class='label-text'>" . $purity . "</span></label></li>";
			}
		} else {
			$goldPurityHtml .= "<li class='test showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' name='metalQualityChkbox' value='14K' class='chk_metalquality'/><span class='label-text'>14K</span></label></li>";
			$goldPurityHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' name='metalQualityChkbox' value='18K' class='chk_metalquality'/><span class='label-text'>18K</span></label></li>";
		}
		$goldPurityHtml .= "</ul></div>";
		return $goldPurityHtml;
	}
	public static function getGoldColorCollection() {

		$all_gold_color = Cache::get('all_gold_color', '');

		if (!empty($all_gold_color)) {
			return $all_gold_color;
		}

		$metalQualityData = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_quality' AND EAOV.store_id = 0");

		//dd($stoneQuality);exit;

		$goldColorHtml = '';
		foreach ($metalQualityData as $key => $option) {
			if ($option->value != "Platinum(950)") {
				$goldColorText[] = strtolower(trim(strstr($option->value, ' ')));
			} else {
				$goldColorText[] = ucwords($option->value);
			}
		}
		$goldColor = array_unique($goldColorText);

		//$collection = collect($metalQualityData);

		Cache::put('all_gold_color', $goldColor, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));

		return $goldColor;
	}
	public static function getStockType($stockType, $selected = null) {
		$stockTypeHtml = "<div class='dropdown' id='stocktype_area'>";
		$stockTypeHtml .= "<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Stock Type<span class='caret'></span></button>";
		$stockTypeHtml .= "<ul class='dropdown-menu'>";
		if (!empty($stockType)) {
			foreach ($stockType as $st_key => $st_value) {
				$selected_str = '';
				//echo $selected;exit;
				if (!empty($selected) && in_array($st_value, $selected)) {
					$selected_str = 'checked';
				}
				$stockTypeHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' " . $selected_str . " name='stockTypeChkbox' value='" . $st_value . "' class='chk_stocktype'/><span class='label-text'>" . $st_value . "</span></label></li>";
			}
		}
		$stockTypeHtml .= "</ul></div>";
		return $stockTypeHtml;
	}

	//Get gold color
	public static function getGoldColor($selected = null, $filtered_products_colors = null) {

		/*$metalQualityData = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_quality' AND EAOV.store_id = 0");*/
		$goldColor = InventoryHelper::getGoldColorCollection();

		//var_dump($filtered_products_colors);exit;

		$goldColorHtml = "<div class='dropdown' id='goldcolor_area'>";
		$goldColorHtml .= "<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Gold Color<span class='caret'></span></button>";
		$goldColorHtml .= "<ul class='dropdown-menu'>";
		if (!empty($filtered_products_colors)) {
			foreach ($filtered_products_colors as $key => $color) {
				$selected_str = '';
				//echo $selected;exit;
				if (!empty($selected) && in_array($color, $selected)) {
					$selected_str = 'checked';
				}
				$goldColorHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' " . $selected_str . " name='metalColorChkbox' value='" . $color . "' class='chk_metalcolor'/><span class='label-text'>" . $color . "</span></label></li>";
			}
		} else {
			//echo "test2";exit;
			foreach ($goldColor as $key => $colorValue) {
				$goldColorTxt = ucwords($colorValue);
				$goldColorHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' name='metalColorChkbox' value='" . $goldColorTxt . "' class='chk_metalcolor'/><span class='label-text'>" . $goldColorTxt . "</span></label></li>";
			}
		}
		$goldColorHtml .= "</ul></div>";
		return $goldColorHtml;
	}
	public static function getDiamondQualityCollection() {

		$all_diamond_qualities = Cache::get('all_diamond_qualities', '');

		if (!empty($all_diamond_qualities)) {
			return $all_diamond_qualities;
		}

		$stoneQuality = DB::select("(SELECT GROUP_CONCAT(prod.entity_id) as proIds, op.option_id,opt.title AS title1,optt.title AS title2 FROM catalog_product_entity AS prod INNER JOIN catalog_product_option AS op ON op.product_id = prod.entity_id LEFT JOIN catalog_product_option_title AS opt ON opt.option_id = op.option_id LEFT JOIN catalog_product_option_type_value AS optv ON optv.option_id = op.option_id LEFT JOIN catalog_product_option_type_title AS optt ON optt.option_type_id = optv.option_type_id WHERE prod.has_options = 1 AND opt.title = 'STONE QUALITY' group by title2)");

		//dd($stoneQuality);exit;

		$collection = collect($stoneQuality);

		Cache::put('all_diamond_qualities', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));

		return $collection;
	}
	public static function getDiamondShapeCollection() {

		$all_diamond_shape = Cache::get('all_diamond_shape', '');

		if (!empty($all_diamond_shape)) {
			return $all_diamond_shape;
		}

		$stoneShape = DB::select("select grp_stone.stone_shape,ce.entity_id,count(1) from grp_stone join catalog_product_flat_1 as ce on grp_stone.stone_shape=ce.stone_shape where ce.isreadytoship='1' and ce.type_id='simple' and ce.custom_price!=0 group by grp_stone.stone_shape");

		//dd($stoneShape);exit;

		$collection = collect($stoneShape);

		Cache::put('all_diamond_shape', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));

		return $collection;
	}
	//Get diamond shape
	public static function getDiamondShape($diamondshape = array(), $filtered_products = false) {
		$diamondShapeHtml = "";
		$diamondShapeData = array();
		/*if (!empty($filtered_products)) {
			$stoneShape = $stoneShape->filter(function ($value, $key) use ($filtered_products) {
				$all_entities = explode(',', $value->entity_id);
				foreach ($filtered_products as $filtered_product) {
					if (in_array($filtered_product, $all_entities)) {
						return $value;
					}
				}
			});
		}*/
		$diamondShapeHtml = "<div class='dropdown' id='diamondshape_area'>";
		$diamondShapeHtml .= "<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Dia. Shape<span class='caret'></span></button>";
		$diamondShapeHtml .= "<ul class='dropdown-menu'>";
		$selectedoption = array();
		if (!empty($diamondshape)) {
			foreach ($diamondshape as $key => $diamondshape_chkbox) {
				array_push($selectedoption, $diamondshape_chkbox);
			}
		}
		$sideStoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
		foreach ($sideStoneShapeDetails as $key => $stone_shape) {
			$stoneShapeData[$stone_shape->option_id] = $stone_shape->value;
		}
		if (!empty($filtered_products)) {

			$filtered_products = implode("','", $filtered_products);
			$diamondShapeData = DB::select("SELECT DISTINCT stone.stone_shape FROM `grp_stone` as stone join catalog_product_flat_1 as ce ON ce.entity_id = stone.`stone_product_id` WHERE ce.isreadytoship='1' and ce.type_id='simple' and ce.custom_price!=0 and ce.entity_id IN('" . $filtered_products . "') GROUP BY stone.stone_product_id");

		} else {
			$diamondShapeData = DB::select("SELECT DISTINCT stone.stone_shape FROM `grp_stone` as stone join catalog_product_flat_1 as ce ON ce.entity_id = stone.`stone_product_id` WHERE ce.isreadytoship='1' and ce.type_id='simple' and ce.custom_price!=0 GROUP BY stone.stone_product_id");
		}

		$stoneCount = 0;
		foreach ($diamondShapeData as $key => $stone) {
			$label = $stoneShapeData[$stone->stone_shape];
			if (!empty($label)) {
				$stoneCount++;
				if (in_array($stone->stone_shape, $selectedoption)) {
					//echo "test";exit;
					$diamondShapeHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' checked name='diamondShapeChkbox' value=" . $stone->stone_shape . " class='chk_diamondshape'/><span class='label-text'>" . $label . "</span></label></li>";
				} else {
					$diamondShapeHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' name='diamondShapeChkbox' value=" . $stone->stone_shape . " class='chk_diamondshape'/><span class='label-text'>" . $label . "</span></label></li>";
				}
			}
		}

		//var_dump($diamondQualityHtml);
		//exit;
		$diamondShapeHtml .= "</ul></div>";
		return $diamondShapeHtml;
	}
	//Get diamond quality
	public static function getDiamondQuality($diamondquality = array(), $filtered_products = false) {

		$stoneQuality = InventoryHelper::getDiamondQualityCollection();
		//var_dump($filtered_products);
		//dd($stoneQuality);exit;
		$diamondQualityHtml = "";
		$filters_pro_query_str = '';
		//print_r($filtered_products);exit;
		if (!empty($filtered_products)) {

			//echo '<pre>';
			//print_r($filtered_products);exit;

			//$stoneQuality = $stoneQuality->whereIn('entity_id', $filtered_product);
			$stoneQuality = $stoneQuality->filter(function ($value, $key) use ($filtered_products) {

				$all_entities = explode(',', $value->proIds);
				foreach ($filtered_products as $filtered_product) {
					if (in_array($filtered_product, $all_entities)) {
						return $value;
					}
				}
			});

			//dd($stoneQuality);exit;
			//}
			//$filtered_products_imp = implode("','", $filtered_products);
			//$filters_pro_query_str = " AND prod.entity_id IN ('" . $filtered_products_imp . "') ";
			//echo $filters_pro_query_str;exit;
		}

		//dd($stoneQuality);exit;

		/*$stoneQuality = DB::select("(SELECT prod.entity_id, op.option_id,opt.title AS title1,optt.title AS title2 FROM catalog_product_entity AS prod INNER JOIN catalog_product_option AS op ON op.product_id = prod.entity_id LEFT JOIN catalog_product_option_title AS opt ON opt.option_id = op.option_id LEFT JOIN catalog_product_option_type_value AS optv ON optv.option_id = op.option_id LEFT JOIN catalog_product_option_type_title AS optt ON optt.option_type_id = optv.option_type_id WHERE prod.has_options = 1 AND opt.title = 'STONE QUALITY' " . $filters_pro_query_str . "group by title2)");*/
		//$stoneQualityLabel = array();

		/*foreach ($stoneQuality as $key => $value) {
			$stoneQualityLabel[] = $value->title2;
		}*/
		//echo "<pre>";
		//print_r($stoneQuality);exit;
		$diamondQualityHtml = "<div class='dropdown' id='diamondquality_area'>";
		$diamondQualityHtml .= "<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Dia. Quality<span class='caret'></span></button>";
		$diamondQualityHtml .= "<ul class='dropdown-menu'>";
		$selectedoption = array();
		if (!empty($diamondquality)) {
			foreach ($diamondquality as $key => $diamondquality_chkbox) {
				array_push($selectedoption, $diamondquality_chkbox);
			}
		}

		//dd($stoneQuality);exit;

		$stoneCount = 0;
		foreach ($stoneQuality as $key => $value) {
			$label = $value->title2;
			//var_dump($label);
			if (!empty($label)) {
				$stoneCount++;
				if (in_array($label, $selectedoption)) {
					$diamondQualityHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' checked name='diamondQualityChkbox' value=" . $value->title2 . " class='chk_diamondquality'/><span class='label-text'>" . $value->title2 . "</span></label></li>";
				} else {
					$diamondQualityHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' name='diamondQualityChkbox' value=" . $value->title2 . " class='chk_diamondquality'/><span class='label-text'>" . $value->title2 . "</span></label></li>";
				}
			}
		}

		//var_dump($diamondQualityHtml);
		//exit;
		$diamondQualityHtml .= "</ul></div>";
		return $diamondQualityHtml;
	}
	public static function getInventoryStockTypeFilter($types, $selectedType = null) {
		$stock_type_html = '';
		$inventory_status = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0");
		$inventory_status_arr = array();
		foreach ($inventory_status as $key => $value) {
			$inventory_status_arr[$value->option_id] = $value->value;
		}
		$stock_type_html .= '<select name="stocktype" id="stocktype" class=" text-uppercase onchangereset"><option value="">STOCK TYPE</option>';
		$stockType = config('constants.enum.stock_type');
		//echo "<pre>";
		//print_r($types);exit;
		$statusArr = array();
		if (!empty($types)) {
			foreach ($types as $key => $status) {
				if (!empty($status)) {
					$selected = '';
					if ($status == $selectedType) {
						$selected = 'selected';
					}
					if (!in_array($status, $statusArr)) {
						$stocktypelabel = '';
						$stocktypevalue = '';
						if (strtolower($status) == 'in' || strtolower($status) == ' in') {
							$stocktypelabel = 'SHOWROOM';
							$stocktypevalue = 'showroom';
						} else if (strtolower($status) == 'out') {
							$stocktypelabel = 'APPROVAL';
							$stocktypevalue = 'approval';
						} else if (strtolower($status) == 'sold out') {
							$stocktypelabel = 'SOLD';
							$stocktypevalue = 'sold';
						}
						$stock_type_html .= '<option value="' . $stocktypevalue . '" ' . $selected . '>' . $stocktypelabel . '</option>';
					}

					$statusArr[] = $status;
				}
			}
		} else {

			foreach ($stockType as $key => $type) {
				$stock_type_html .= '<option  value="' . $key . '">' . $type . '</option>';
			}
		}
		$stock_type_html .= '</select>';
		//echo $stock_type_html;exit;
		return $stock_type_html;
	}
	public static function getInventoryStatusFilter($statues, $selectedStatus = null) {
		$inventory_status_html = '';
		$inventory_status = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0");
		$inventory_status_arr = array();
		foreach ($inventory_status as $key => $value) {
			$inventory_status_arr[$value->option_id] = $value->value;
		}
		$inventory_status_html .= '<select name="stockstatus" id="stockstatus" class="onchangereset text-uppercase"><option value="">Status</option>';
		//echo "<pre>";
		//print_r($selectedStatus);exit;
		$statusArr = array();
		if (!empty($statues)) {
			foreach ($statues as $key => $status) {
				if (!empty($status)) {
					$selected = '';
					if ($status == $selectedStatus) {
						$selected = 'selected';
					}
					if (!in_array($status, $statusArr)) {
						$inventory_status_html .= '<option value="' . $status . '" ' . $selected . '>' . $status . '</option>';
					}

					$statusArr[] = $status;
				}
			}
		} else {
			$inventory_status_html .= '<option  value="In">In</option>';
			$inventory_status_html .= '<option  value="Out">Out</option>';
			$inventory_status_html .= '<option  value="Sold Out">Sold Out</option>';
		}
		$inventory_status_html .= '</select>';
		return $inventory_status_html;
	}
	public static function getInventoryVirtualFilter($virtual_boxes, $selectedVirtualBox) {
		$virtual_productmanager_html = '';
		$virtual_productmanager_html .= '<select name="virtualproductmanager" id="virtualproductmanager" class="text-uppercase"><option  value="">Virtual Products</option>';
		if (!empty($virtual_boxes)) {
			foreach ($virtual_boxes as $virtual_box_id => $virtual_box_name) {
				$selected = '';
				if ($virtual_box_id == $selectedVirtualBox) {
					$selected = 'selected';
				}

				$virtual_productmanager_html .= '<option  value="' . $virtual_box_id . '" ' . $selected . '>' . $virtual_box_name . '</option>';
			}
		} else {
			$virtualProduct = DB::select("select product_manager_id,product_manager_name from grp_productmanager where product_manager_name!=''");
			foreach ($virtualProduct as $virtual_box_id => $virtual_box) {
				$virtual_productmanager_html .= '<option  value="' . $virtual_box->product_manager_id . '">' . $virtual_box->product_manager_name . '</option>';
			}

		}
		$virtual_productmanager_html .= '</select>';
		return $virtual_productmanager_html;
	}
	//Update inventory status
	public static function changeInventoryStatus($productId, $status) {
		$inventoryStatusOption = DB::select("SELECT EAOV.value,EA.attribute_id FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0 AND EAOV.option_id=" . $status);
		$inventory_status = '';
		$attributeId = '';
		foreach ($inventoryStatusOption as $key => $value) {
			$inventory_status = $value->value;
			$attributeId = $value->attribute_id;
		}

		//var_dump($attributeId);
		//echo ucwords(strtolower($inventory_status));
		//var_dump($productId);exit;

		DB::setTablePrefix('');
		//$productUpdate = DB::statement("UPDATE catalog_product_flat_1 set inventory_status=" . $status . ",inventory_status_value='" . $inventory_status . "',approval_invoice_generated='0',approval_memo_generated='0' WHERE entity_id=" . $productId);
		if (App::environment('local')) {
			$IN = config('constants.apiurl.local.get_in');
		} else if (App::environment('test')) {
			$IN = config('constants.apiurl.test.get_in');
		} else {
			$IN = config('constants.apiurl.live.get_in');
		}
		if ($inventory_status == $IN) {
			InventoryHelper::updateProductAttribute($productId, 'approval_invoice_generated', '0');
			InventoryHelper::updateProductAttribute($productId, 'is_sold', 0);
			InventoryHelper::updateProductAttribute($productId, 'approval_memo_generated', '0');
			InventoryHelper::updateProductAttribute($productId, 'return_memo_generated', '0');
		}
		$productUpdate = DB::statement("UPDATE catalog_product_flat_1 set inventory_status=" . $status . ",inventory_status_value='" . $inventory_status . "' WHERE entity_id=" . $productId);
		$productUpdate = DB::statement("UPDATE catalog_product_entity_int set value=" . $status . " WHERE entity_id=" . $productId . " AND attribute_id=" . $attributeId);
		if ($productUpdate) {
			$inventoryUpdate = DB::statement("UPDATE qrcode_inventory_management set inventory_status='" . strtolower($inventory_status) . "' WHERE pr_id=" . $productId);
		}
		Cache::forget('all_products_ajax');
		Cache::forget('all_products');
		DB::setTablePrefix('dml_');
		return $inventoryUpdate;
	}
	public static function getMetalRingSize($productId, $size) {
		$updatedPrice = '';
		$productOptions = DB::select("SELECT `main_table`.*, `default_option_title`.`title` AS `default_title`, `store_option_title`.`title` AS `store_title`, IF(store_option_title.title IS NULL, default_option_title.title, store_option_title.title) AS `title`, `default_option_price`.`price` AS `default_price`, `default_option_price`.`price_type` AS `default_price_type`, `store_option_price`.`price` AS `store_price`, `store_option_price`.`price_type` AS `store_price_type`, IF(store_option_price.price IS NULL, default_option_price.price, store_option_price.price) AS `price`, IF(store_option_price.price_type IS NULL, default_option_price.price_type, store_option_price.price_type) AS `price_type`, `default_option_description`.`description` AS `default_description`, `store_option_description`.`description` AS `store_description`, IFNULL(`store_option_description`.description,`default_option_description`.description) AS `description` FROM `catalog_product_option` AS `main_table` INNER JOIN `catalog_product_option_title` AS `default_option_title` ON default_option_title.option_id = main_table.option_id LEFT JOIN `catalog_product_option_title` AS `store_option_title` ON store_option_title.option_id = main_table.option_id AND store_option_title.store_id = '1' LEFT JOIN `catalog_product_option_price` AS `default_option_price` ON default_option_price.option_id = main_table.option_id AND default_option_price.store_id = 0 LEFT JOIN `catalog_product_option_price` AS `store_option_price` ON store_option_price.option_id = main_table.option_id AND store_option_price.store_id = '1' LEFT JOIN `custom_options_option_description` AS `default_option_description` ON `default_option_description`.option_id = `main_table`.option_id AND `default_option_description`.store_id = 0 LEFT JOIN `custom_options_option_description` AS `store_option_description` ON `store_option_description`.option_id = `main_table`.option_id AND `store_option_description`.store_id = '1' WHERE (product_id = " . $productId . ") AND (is_enabled != 0) AND (default_option_title.store_id = 0) ORDER BY sort_order ASC, title ASC");
		foreach ($productOptions as $key => $option) {
			$optionValues = DB::select("SELECT `main_table`.*, `default_value_price`.`price` AS `default_price`, `default_value_price`.`price_type` AS `default_price_type`, `store_value_price`.`price` AS `store_price`, `store_value_price`.`price_type` AS `store_price_type`, IF(store_value_price.price IS NULL, default_value_price.price, store_value_price.price) AS `price`, IF(store_value_price.price_type IS NULL, default_value_price.price_type, store_value_price.price_type) AS `price_type`, `default_value_title`.`title` AS `default_title`, `store_value_title`.`title` AS `store_title`, IF(store_value_title.title IS NULL, default_value_title.title, store_value_title.title) AS `title` FROM `catalog_product_option_type_value` AS `main_table` LEFT JOIN `catalog_product_option_type_price` AS `default_value_price` ON default_value_price.option_type_id = main_table.option_type_id AND default_value_price.store_id = 0 LEFT JOIN `catalog_product_option_type_price` AS `store_value_price` ON store_value_price.option_type_id = main_table.option_type_id AND store_value_price.store_id = '' INNER JOIN `catalog_product_option_type_title` AS `default_value_title` ON default_value_title.option_type_id = main_table.option_type_id LEFT JOIN `catalog_product_option_type_title` AS `store_value_title` ON store_value_title.option_type_id = main_table.option_type_id AND store_value_title.store_id = '' WHERE (option_id = " . $option->option_id . ") AND (default_value_title.store_id = 0)");
			if ($option->type == 'drop_down') {
				foreach ($optionValues as $key => $value) {
					if ($value->title == $size) {
						$updatedPrice = $value->metal_weight;
					}
				}
			} else {
				foreach ($optionValues as $key => $value) {
					if ($value->title == $size) {
						$updatedPrice = $value->metal_weight;
					}
				}
			}
		}
		return $updatedPrice;
	}
	//GEt product options
	public static function getProductOptions($productId) {
		$productOptions = DB::select("SELECT `main_table`.*, `default_option_title`.`title` AS `default_title`, `store_option_title`.`title` AS `store_title`, IF(store_option_title.title IS NULL, default_option_title.title, store_option_title.title) AS `title`, `default_option_price`.`price` AS `default_price`, `default_option_price`.`price_type` AS `default_price_type`, `store_option_price`.`price` AS `store_price`, `store_option_price`.`price_type` AS `store_price_type`, IF(store_option_price.price IS NULL, default_option_price.price, store_option_price.price) AS `price`, IF(store_option_price.price_type IS NULL, default_option_price.price_type, store_option_price.price_type) AS `price_type`, `default_option_description`.`description` AS `default_description`, `store_option_description`.`description` AS `store_description`, IFNULL(`store_option_description`.description,`default_option_description`.description) AS `description` FROM `catalog_product_option` AS `main_table` INNER JOIN `catalog_product_option_title` AS `default_option_title` ON default_option_title.option_id = main_table.option_id LEFT JOIN `catalog_product_option_title` AS `store_option_title` ON store_option_title.option_id = main_table.option_id AND store_option_title.store_id = '1' LEFT JOIN `catalog_product_option_price` AS `default_option_price` ON default_option_price.option_id = main_table.option_id AND default_option_price.store_id = 0 LEFT JOIN `catalog_product_option_price` AS `store_option_price` ON store_option_price.option_id = main_table.option_id AND store_option_price.store_id = '1' LEFT JOIN `custom_options_option_description` AS `default_option_description` ON `default_option_description`.option_id = `main_table`.option_id AND `default_option_description`.store_id = 0 LEFT JOIN `custom_options_option_description` AS `store_option_description` ON `store_option_description`.option_id = `main_table`.option_id AND `store_option_description`.store_id = '1' WHERE (product_id = " . $productId . ") AND (is_enabled != 0) AND (default_option_title.store_id = 0) ORDER BY sort_order ASC, title ASC");
		return $productOptions;
	}
	//Get product option value
	public static function getOptionValue($optionId) {
		$optionValues = DB::select("SELECT `main_table`.*, `default_value_price`.`price` AS `default_price`, `default_value_price`.`price_type` AS `default_price_type`, `store_value_price`.`price` AS `store_price`, `store_value_price`.`price_type` AS `store_price_type`, IF(store_value_price.price IS NULL, default_value_price.price, store_value_price.price) AS `price`, IF(store_value_price.price_type IS NULL, default_value_price.price_type, store_value_price.price_type) AS `price_type`, `default_value_title`.`title` AS `default_title`, `store_value_title`.`title` AS `store_title`, IF(store_value_title.title IS NULL, default_value_title.title, store_value_title.title) AS `title` FROM `catalog_product_option_type_value` AS `main_table` LEFT JOIN `catalog_product_option_type_price` AS `default_value_price` ON default_value_price.option_type_id = main_table.option_type_id AND default_value_price.store_id = 0 LEFT JOIN `catalog_product_option_type_price` AS `store_value_price` ON store_value_price.option_type_id = main_table.option_type_id AND store_value_price.store_id = '' INNER JOIN `catalog_product_option_type_title` AS `default_value_title` ON default_value_title.option_type_id = main_table.option_type_id LEFT JOIN `catalog_product_option_type_title` AS `store_value_title` ON store_value_title.option_type_id = main_table.option_type_id AND store_value_title.store_id = '' WHERE (option_id = " . $optionId . ") AND (default_value_title.store_id = 0)");
		return $optionValues;
	}
	//Get gem stone data
	public static function getGemStoneData($productId) {
		//$productGemstonePrice = array();
		//$productId = '1258020';
		$productGemstonePrice = '';
		$gemStoneData = DB::select("SELECT * FROM grp_main_stone WHERE gem_stone_product_id=" . $productId);
		//print_r($gemStoneData);exit;
		$sideStoneTypeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'color_stone_type' AND EAOV.store_id = 0");
		$sideStoneType = array();

		$sideStoneSubTypeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_sub_type' AND EAOV.store_id = 0");
		$sideStoneSubType = array();

		$sideStoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
		$sideStoneShape = array();

		$sideStoneSettingDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_setting' AND EAOV.store_id = 0");
		$sideStoneSetting = array();

		foreach ($sideStoneTypeDetails as $key => $value) {
			$sideStoneType[$value->option_id] = $value->value;
		}

		foreach ($sideStoneSubTypeDetails as $key => $value) {
			$sideStoneSubType[$value->option_id] = $value->value;
		}

		foreach ($sideStoneShapeDetails as $key => $value) {
			$sideStoneShape[$value->option_id] = $value->value;
		}

		foreach ($sideStoneSettingDetails as $key => $value) {
			$sideStoneSetting[$value->option_id] = $value->value;
		}
		$gemStoneDataArr = array();
		$gem_stone_price = '';
		$gem_stone_data = array();
		foreach ($gemStoneData as $key => $gemStone) {
			// echo "<pre>";
			// print_r($gemStone);exit;
			$gemStoneType = isset($sideStoneType[$gemStone->gem_stone_type]) ? $sideStoneType[$gemStone->gem_stone_type] : '';
			$gemStoneSubType = isset($sideStoneSubType[$gemStone->gem_stone_subtype]) ? $sideStoneSubType[$gemStone->gem_stone_subtype] : '';
			$gemStoneShape = isset($sideStoneShape[$gemStone->gem_stone_shape]) ? $sideStoneShape[$gemStone->gem_stone_shape] : '';
			$gemStoneSetting = isset($sideStoneSetting[$gemStone->gem_stone_setting]) ? $sideStoneSetting[$gemStone->gem_stone_setting] : '';
			$roundgemstonepriceValue = round($gemStone->gem_stone_price);
			$roundgemstoneprice = preg_replace('~\.0+$~', '', $roundgemstonepriceValue);
			$gem_stone_price = $gemStone->gem_stone_price;
			$productGemstonePrice = isset($gemStone->gem_stone_price) ? $gemStone->gem_stone_price : '';
			$gem_stone_data['type'][] = $gemStoneType;
			$gem_stone_data['subtype'][] = $gemStoneSubType;
			$gem_stone_data['shape'][] = $gemStoneShape;
			$gem_stone_data['deamination'][] = $gemStone->gem_stone_deamination;
			$gem_stone_data['carat'][] = $gemStone->gem_stone_carat;
			$gem_stone_data['stone_use'][] = $gemStone->gem_stone_use;
			$gem_stone_data['setting'][] = $gemStoneSetting;
			$stoneCtTotal = $gemStone->gem_stone_carat * $gemStone->gem_stone_use;
			$gem_stone_data['totalcts'][] = $stoneCtTotal;
			$gem_stone_data['stone_price'][] = $roundgemstoneprice;
		}
		if ($gem_stone_price != 0) {
			$gemStoneTotalValue = round(array_sum(array($productGemstonePrice)));
			$gemStonePriceTotalValue = ShowroomHelper::currencyFormat(round($gemStoneTotalValue));
			$gemStoneTotal = preg_replace('~\.0+$~', '', $gemStoneTotalValue);
			$gemStonePriceTotal = preg_replace('~\.0+$~', '', $gemStonePriceTotalValue);
			$gem_stone_data['total'] = $gemStonePriceTotal;
			$gem_stone_data['simple'] = $gemStoneTotal;
		}
		return $gem_stone_data;
	}
	//Get franchisee data
	public static function getFranchiseeData() {
		if (App::environment('local')) {
			$firstNameAttrId = Config::get('constants.fixIds.local.customer_entity_varchar_firstname');
		} else {
			$firstNameAttrId = Config::get('constants.fixIds.live.customer_entity_varchar_firstname');
		}
		if (App::environment('local')) {
			$lastNameAttrId = Config::get('constants.fixIds.local.customer_entity_varchar_lastname');
		} else {
			$lastNameAttrId = Config::get('constants.fixIds.live.customer_entity_varchar_lastname');
		}
		$franchiseeData = DB::select("SELECT DISTINCT `e`.entity_id, `at_firstname`.`value` AS `firstname`, `at_lastname`.`value` AS `lastname`, `at__isfranchisee`.`value` AS `_isfranchisee` FROM `customer_entity` AS `e` INNER JOIN `customer_entity_varchar` AS `at_firstname` ON ( `at_firstname`.`entity_id` = `e`.`entity_id` ) AND( `at_firstname`.`attribute_id` = '" . $firstNameAttrId . "' ) INNER JOIN `customer_entity_varchar` AS `at_lastname` ON ( `at_lastname`.`entity_id` = `e`.`entity_id` ) AND(`at_lastname`.`attribute_id` = '" . $lastNameAttrId . "') INNER JOIN `customer_entity_int` AS `at__isfranchisee` ON ( `at__isfranchisee`.`entity_id` = `e`.`entity_id` ) WHERE (`e`.`entity_type_id` = '1') AND(at__isfranchisee.value = '1')");
		$franchisee = array();
		foreach ($franchiseeData as $key => $value) {
			$franchisee[$key]['entity_id'] = $value->entity_id;
			$franchisee[$key]['name'] = $value->firstname . "  " . $value->lastname;
		}
		return $franchisee;
	}

	//Get cusotmer default billing address by address id
	public static function getDefaultBillingAddressById($id) {
		$billingAddress = array();
		$customerData = array();
		if (!empty($id)) {
			$customerData = DB::select("SELECT
				a.entity_id AS entity_id,
				addr_firstname.value AS firstname,
			    addr_lastname.value AS lastname,
			    addr_street.value AS street,
			    addr_city.value AS city,
			    addr_region_code.code AS stateCode,
			    addr_region.value AS region,
			    addr_zipcode.value AS postcode,
			    addr_country.value AS country_id,
			    addr_telephone.value AS telephone,
			    gstin.value as gstin,
			    pancardno.value as pancard_number,
			    addr_region_code.region_id,
			    a.parent_id
			FROM
			    customer_address_entity AS a
			LEFT JOIN customer_address_entity_varchar AS addr_zipcode
			ON
			    a.entity_id = addr_zipcode.entity_id AND addr_zipcode.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'postcode' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS pancardno
			ON
			    a.entity_id = pancardno.entity_id AND pancardno.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'pancardno' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS gstin
			ON
			    a.entity_id = gstin.entity_id AND gstin.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'gstin' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_city
			ON
			    a.entity_id = addr_city.entity_id AND addr_city.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'city' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_country
			ON
			    a.entity_id = addr_country.entity_id AND addr_country.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'country_id' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_firstname
			ON
			    a.entity_id = addr_firstname.entity_id AND addr_firstname.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'firstname' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_lastname
			ON
			    a.entity_id = addr_lastname.entity_id AND addr_lastname.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'lastname' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_text AS addr_street
			ON
			    a.entity_id = addr_street.entity_id AND addr_street.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'street' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_telephone
			ON
			    a.entity_id = addr_telephone.entity_id AND addr_telephone.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'telephone' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_region
			ON
			    a.entity_id = addr_region.entity_id AND addr_region.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'region' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_int AS addr_region_id
			ON
			    a.entity_id = addr_region_id.entity_id AND addr_region_id.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'region_id' AND eav.entity_type_id = 2
			)
			LEFT JOIN directory_country_region AS addr_region_code
			ON
			    addr_region_id.value = addr_region_code.region_id
			WHERE a.entity_id=" . $id);
		}

		//var_dump($customerData);exit;

		foreach ($customerData as $key => $customer) {
			$billingAddress = (array) $customer;
		}
		return $billingAddress;
	}

	//Get cusotmer default billing address by customer id
	public static function getDefaultBillingAddressByCustId($customerId) {
		$billingAddress = array();
		$customerData = array();
		if (!empty($customerId)) {
			$customerData = DB::select("SELECT
				a.entity_id AS entity_id,
				c.entity_id as customer_id,
			    c.entity_type_id,
			    c.attribute_set_id,
			    c.increment_id,
			    c.created_at,
			    c.updated_at,
			    c.is_active,
			    email,
			    IF(
			        def_billing_address.value = a.entity_id,
			        1,0
			    ) AS isDefaultBillingAddress,
			    addr_firstname.value AS firstname,
			    addr_lastname.value AS lastname,
			    addr_street.value AS street,
			    addr_city.value AS city,
			    addr_region_code.code AS stateCode,
			    addr_region.value AS region,
			    addr_zipcode.value AS postcode,
			    addr_country.value AS country_id,
			    addr_telephone.value AS telephone,
			    gstin.value as gstin,
			    pancardno.value as pancard_number,
			    addr_region_code.region_id,
			    a.parent_id
			FROM
			    customer_entity AS c
			INNER JOIN customer_address_entity AS a
			ON
			    a.parent_id = c.entity_id
			LEFT JOIN customer_entity_int AS def_billing_address
			ON
			    (
			        def_billing_address.entity_id = c.entity_id
			    ) AND(
			        def_billing_address.attribute_id =(
			        SELECT
			            attribute_id
			        FROM
			            eav_attribute AS eav
			        WHERE
			            eav.attribute_code = 'default_billing' AND eav.entity_type_id = 1
			    )
			    )
			LEFT JOIN customer_address_entity_varchar AS addr_zipcode
			ON
			    a.entity_id = addr_zipcode.entity_id AND addr_zipcode.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'postcode' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS pancardno
			ON
			    a.entity_id = pancardno.entity_id AND pancardno.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'pancardno' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS gstin
			ON
			    a.entity_id = gstin.entity_id AND gstin.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'gstin' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_city
			ON
			    a.entity_id = addr_city.entity_id AND addr_city.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'city' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_country
			ON
			    a.entity_id = addr_country.entity_id AND addr_country.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'country_id' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_firstname
			ON
			    a.entity_id = addr_firstname.entity_id AND addr_firstname.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'firstname' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_lastname
			ON
			    a.entity_id = addr_lastname.entity_id AND addr_lastname.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'lastname' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_text AS addr_street
			ON
			    a.entity_id = addr_street.entity_id AND addr_street.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'street' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_telephone
			ON
			    a.entity_id = addr_telephone.entity_id AND addr_telephone.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'telephone' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_region
			ON
			    a.entity_id = addr_region.entity_id AND addr_region.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'region' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_int AS addr_region_id
			ON
			    a.entity_id = addr_region_id.entity_id AND addr_region_id.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'region_id' AND eav.entity_type_id = 2
			)
			LEFT JOIN directory_country_region AS addr_region_code
			ON
			    addr_region_id.value = addr_region_code.region_id
			WHERE c.entity_id=" . $customerId . " and def_billing_address.value = a.entity_id");
		}

		foreach ($customerData as $key => $customer) {
			$billingAddress = (array) $customer;
		}
		return $billingAddress;
	}
	//Get default shipping billing address by customer id
	public static function getDefaultShippingAddresByCustId($customerId) {

		$shippingAddress = array();
		if (!empty($customerId)) {

			$customerData = DB::select("SELECT
			    a.entity_id AS entity_id,
			    c.entity_id AS customer_id,
			    c.entity_type_id,
			    c.attribute_set_id,
			    c.increment_id,
			    c.created_at,
			    c.updated_at,
			    c.is_active,
			    email,
			    IF(
			        def_shipping_address.value = a.entity_id,
			        1,
			        0
			    ) AS isDefaultShippingAddress,
			    addr_firstname.value AS firstname,
			    addr_lastname.value AS lastname,
			    addr_street.value AS street,
			    addr_city.value AS city,
			    addr_region_code.code AS stateCode,
			    addr_region.value AS region,
			    addr_zipcode.value AS postcode,
			    addr_country.value AS country_id,
			    addr_telephone.value AS telephone,
			    gstin.value as gstin,
			    pancardno.value as pancard_number,
			    addr_region_code.region_id
			FROM
			    customer_entity AS c
			INNER JOIN customer_address_entity AS a
			ON
			    a.parent_id = c.entity_id
			LEFT JOIN customer_entity_int AS def_shipping_address
			ON
			    (
			        def_shipping_address.entity_id = c.entity_id
			    ) AND(
			        def_shipping_address.attribute_id =(
			        SELECT
			            attribute_id
			        FROM
			            eav_attribute AS eav
			        WHERE
			            eav.attribute_code = 'default_shipping' AND eav.entity_type_id = 1
			    )
			    )
			LEFT JOIN customer_address_entity_varchar AS addr_zipcode
			ON
			    a.entity_id = addr_zipcode.entity_id AND addr_zipcode.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'postcode' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS pancardno
			ON
			    a.entity_id = pancardno.entity_id AND pancardno.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'pancardno' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS gstin
			ON
			    a.entity_id = gstin.entity_id AND gstin.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'gstin' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_city
			ON
			    a.entity_id = addr_city.entity_id AND addr_city.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'city' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_country
			ON
			    a.entity_id = addr_country.entity_id AND addr_country.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'country_id' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_firstname
			ON
			    a.entity_id = addr_firstname.entity_id AND addr_firstname.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'firstname' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_lastname
			ON
			    a.entity_id = addr_lastname.entity_id AND addr_lastname.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'lastname' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_text AS addr_street
			ON
			    a.entity_id = addr_street.entity_id AND addr_street.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'street' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_telephone
			ON
			    a.entity_id = addr_telephone.entity_id AND addr_telephone.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'telephone' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_varchar AS addr_region
			ON
			    a.entity_id = addr_region.entity_id AND addr_region.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'region' AND eav.entity_type_id = 2
			)
			LEFT JOIN customer_address_entity_int AS addr_region_id
			ON
			    a.entity_id = addr_region_id.entity_id AND addr_region_id.attribute_id =(
			    SELECT
			        attribute_id
			    FROM
			        eav_attribute AS eav
			    WHERE
			        eav.attribute_code = 'region_id' AND eav.entity_type_id = 2
			)
			LEFT JOIN directory_country_region AS addr_region_code
			ON
			    addr_region_id.value = addr_region_code.region_id
			WHERE
			    c.entity_id = " . $customerId . " AND def_shipping_address.value = a.entity_id");

			foreach ($customerData as $key => $customer) {
				$shippingAddress = (array) $customer;
			}
			return $shippingAddress;
		}
	}
	public static function getDefaultDiamondQuality() {
		$stoneClarityDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0 AND EAOV.value='SI-IJ'");
		return isset($stoneClarityDetails[0]->value) ? $stoneClarityDetails[0]->value : '';
	}
	public static function getStoneQualityOptionId($stone_value) {
		$stoneClarityData = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0 AND EAOV.value='" . $stone_value . "'");
		return isset($stoneClarityData[0]->option_id) ? $stoneClarityData[0]->option_id : '';
	}
	public static function getStoneQualityOption() {
		$stoneClarityDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0");
		return $stoneClarityDetails;
	}
	//Get customer full name by id
	public static function getCustomerName($customerId) {
		if (!empty($customerId)) {
			/*if (App::environment('local')) {
					$firstNameAttrId = Config::get('constants.fixIds.local.customer_entity_varchar_firstname');
				} else {
					$firstNameAttrId = Config::get('constants.fixIds.live.customer_entity_varchar_firstname');
				}
				if (App::environment('local')) {
					$lastNameAttrId = Config::get('constants.fixIds.local.customer_entity_varchar_lastname');
				} else {
					$lastNameAttrId = Config::get('constants.fixIds.live.customer_entity_varchar_lastname');
			*/
			$firstNameAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='firstname' AND entity_type_id = 1");
			$firstnameattrid = array();
			foreach ($firstNameAttribute as $key => $attr) {
				$firstnameattrid[] = $attr->attribute_id;
			}

			$lastNameAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='lastname' AND entity_type_id = 1");
			$lastnameattrid = array();
			foreach ($lastNameAttribute as $key => $attr) {
				$lastnameattrid[] = $attr->attribute_id;
			}
			$nameAttrIds = implode(',', array_merge($firstnameattrid, $lastnameattrid));

			$customer = DB::select("select value from customer_entity_varchar where attribute_id IN(" . $nameAttrIds . ") and entity_id=" . $customerId);
			//print_r($customer);exit;
			/*$customerData = DB::select("select ce.entity_id, concat(cevf.value, ' ', cevl.value) fullname from customer_entity ce inner join customer_entity_varchar cevf on ce.entity_id = cevf.entity_id inner join eav_attribute eaf on eaf.attribute_id = cevf.attribute_id inner join customer_entity_varchar cevl on ce.entity_id = cevl.entity_id inner join eav_attribute eal on eal.attribute_id = cevl.attribute_id inner join eav_entity_type eet on eet.entity_type_id = eal.entity_type_id = eaf.entity_type_id where eet.entity_type_code = 'customer' and eaf.attribute_code = 'firstname' and eal.attribute_code = 'lastname' and ce.entity_id=" . DB::raw("$customerId") . " order by ce.entity_id");*/
		}
		$firstName = isset($customer[1]->value) ? $customer[1]->value : '';
		$lastName = isset($customer[0]->value) ? $customer[0]->value . '' : '';

		$customerName = $firstName . ' ' . $lastName;
		DB::setTablePrefix('dml_');
		return ($customerName != '') ? $customerName : 'N/A';
	}
	//Get product certificate no by product id
	public static function getCertificateNo($productId) {
		DB::setTablePrefix('');
		$product = DB::table("catalog_product_flat_1")->select("certificate_no")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
		DB::setTablePrefix('dml_');
		return isset($product->certificate_no) ? $product->certificate_no : '';
	}

	// Check Certificate is exist or not
	public static function isCertificateExist($certificate_no) {
		DB::setTablePrefix('');
		$product = DB::table('catalog_product_flat_1')->select('entity_id', 'certificate_no')->where('certificate_no', '=', DB::raw("'$certificate_no'"))->get()->first();
		DB::setTablePrefix('dml_');
		if (!empty($product)) {
			//echo "true";exit;
			return $product;
		} else {
			//echo "false";exit;
			return false;
		}
	}

	// Check Certificate is exist or not
	public static function isProductExist($product_id) {
		DB::setTablePrefix('');
		$product = DB::table('catalog_product_flat_1')->select('entity_id', 'certificate_no')->where('entity_id', '=', DB::raw("'$product_id'"))->get()->first();
		DB::setTablePrefix('dml_');
		if (!empty($product)) {
			//echo "true";exit;
			return $product;
		} else {
			//echo "false";exit;
			return false;
		}
	}

	// Get scanning list count
	public static function scanningcount() {
		$id = Auth::User()->id;
		$allscannings = QrcodeScanning::where('created_by', $id);
		return $allscannings->count();
	}

	//Export excel for quotation products
	public static function exportQuotationExcel($quotationId) {
		//print_r($quotationId);
		/*$params = $request->post();
    	$quotationId = isset($params['quotationId']) ? $params['quotationId'] : '';*/
		$quotationData = Quotation::find($quotationId);

		$productData = isset($quotationData->product_data) ? json_decode($quotationData->product_data) : array();

		/*echo "hello";
		print_r($productData);exit;*/

		//print_r($mm_size);exit;
		$productCollection = '';
		$productId = '';
		$data = array();
		DB::setTablePrefix('');
		if (App::environment('local')) {
			$imageDirectory = config('constants.product_image_url.local.website_url_for_product_image_curl');
			$inventory_status_in_id = config('constants.fixIds.local.inventory_status_in_id');
		} else {
			$imageDirectory = config('constants.product_image_url.live.website_url_for_product_image_curl');
			$inventory_status_in_id = config('constants.fixIds.live.inventory_status_in_id');
		}

		$defaultProductImage = $imageDirectory . 'def_1.png';
		$serialNumber = 0;

		$customerName = InventoryHelper::getCustomerName($quotationData->customer_id);
		$shippingAddress = InventoryHelper::getDefaultShippingAddresByCustId($quotationData->customer_id);
		$street = !empty($shippingAddress['street']) ? $shippingAddress['street'] . ", " : '';
		$city = !empty($shippingAddress['city']) ? $shippingAddress['city'] . ", " : '';
		$region = !empty($shippingAddress['region']) ? $shippingAddress['region'] . ", " : '';
		$postcode = !empty($shippingAddress['postcode']) ? $shippingAddress['postcode'] . ", " : '';

		$shippingAddress = $street . $city . $region . $postcode;
		$gstinNumber = InventoryHelper::getGstinNumber($quotationData->customer_id);

		$quotationNumber = str_pad($quotationData->increment_id, 6, '0', STR_PAD_LEFT);
		$customerInfo = array('gstin_number' => 'GSTIN: ' . $gstinNumber, 'shipping_address' => 'ADDRESS: ' . $shippingAddress, 'quotation_number' => 'QUOTATION NO: ' . $quotationNumber, 'quotation_date' => 'DATE: ' . date('d-m-Y', strtotime($quotationData->created_at)), 'customer_name' => 'CUSTOMER NAME: ' . $customerName);

		Session::put('customer_info', $customerInfo);
		Session::save();
		foreach ($productData as $key => $product) {
			$tmparray = array();
			$productId = isset($product->product_id) ? $product->product_id : '';
			$priceMarkup = isset($product->price_markup) ? $product->price_markup : 0;
			if (empty($productId)) {
				continue;
			}

			$productCollection = DB::table(DB::raw("catalog_product_flat_1"))->select("inventory_status", "name", "sku", "certificate_no", "metal_quality")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
			if (empty($productCollection)) {
				continue;
			}

			$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->select('id', 'rts_stone_quality', 'certificate_no')->where('certificate_no', '=', DB::raw("'$productCollection->certificate_no'"))->get()->first();
			//var_dump($dmlProductData);exit;
			$inventoryStatus = isset($productCollection->inventory_status) ? $productCollection->inventory_status : $inventory_status_in_id;
			//var_dump($inventoryStatus);exit;

			//echo "SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0 AND EAOV.option_id=" . DB::raw("$inventoryStatus");exit;
			$inventoryStatusAttr = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0 AND EAOV.option_id=" . DB::raw("$inventoryStatus"));
			$inventoryStatus = isset($inventoryStatusAttr[0]->value) ? $inventoryStatusAttr[0]->value : '';

			$serialNumber = $serialNumber + 1;
			$product_image = $imageDirectory . ShowroomHelper::getProductImage($productId);
			$productImage = (!empty(ShowroomHelper::getProductImage($productId)) ? $product_image : $defaultProductImage);
			//$imageData = ShowroomHelper::file_get_contents_curl($productImage);
			$ext = pathinfo($productImage, PATHINFO_EXTENSION);
			if (!file_exists(public_path('img/product'))) {
				mkdir(public_path('img/product'), 0777, true);
			}
			$file = 'img/product/product' . $productId . '.' . $ext;

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

			//file_put_contents( $file, $imageData );
			$name = isset($productCollection->name) ? $productCollection->name : '';
			$sku = isset($productCollection->sku) ? $productCollection->sku : '';
			$certificateNo = isset($productCollection->certificate_no) ? $productCollection->certificate_no : $inventory_status_in_id;

			//Get metal quality
			$metalQualityOption = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_quality' AND EAOV.store_id = 0");
			$metalQuality = '';
			foreach ($metalQualityOption as $key => $metal) {
				if ($metal->option_id == $productCollection->metal_quality) {
					$metalQuality = $metal->value;
				}
			}

			$metalRateData = isset($product->metal_rate_data->$productId) ? $product->metal_rate_data->$productId : '';
			$metalWeight = isset($metalRateData->metal_weight) ? $metalRateData->metal_weight : '';
			$metalPrice = isset($metalRateData->final_metal_rate) ? $metalRateData->final_metal_rate : '';

			$labourChargeData = isset($product->labour_charge_data->$productId) ? $product->labour_charge_data->$productId : '';
			$labourCharge = isset($labourChargeData->label_charge) ? $labourChargeData->label_charge : '';
			$metalRate = isset($metalRateData->per_gm_rate) ? $metalRateData->per_gm_rate : '';
			$labourAmount = isset($labourChargeData->final_labour_charge) ? $labourChargeData->final_labour_charge : '';
			$stoneData = isset($product->stone_data) ? $product->stone_data : '';
			$stoneShape = array();
			$stoneClarity = array();
			$stonePices = array();
			$stoneWeight = array();
			$stonePrice = array();
			$stoneRate = array();
			$finalStonePrice = 0;

			foreach ($stoneData as $key => $stoneInfo) {
				$stoneShape[] = isset($stoneInfo->stone_shape) ? ucfirst($stoneInfo->stone_shape) : '';
				$stoneClarity[] = isset($stoneInfo->stone_clarity) ? $stoneInfo->stone_clarity : '';
				$stonePices[] = isset($stoneInfo->stone_use) ? $stoneInfo->stone_use : '';
				$stoneWeight[] = isset($stoneInfo->total_stone_caret) ? $stoneInfo->total_stone_caret : '';
				$stonePrice[] = isset($stoneInfo->final_stone_price) ? $stoneInfo->final_stone_price : '';
				$stoneRate[] = isset($stoneInfo->stone_price) ? $stoneInfo->stone_price : '';

				$finalStonePrice += isset($stoneInfo->final_stone_price) ? round($stoneInfo->final_stone_price) : 0;
			}
			$gemStoneData = InventoryHelper::getGemStoneData($productId);
			$gemStone = isset($gemStoneData['simple']) ? round($gemStoneData['simple']) : 0;
			$totalProductPrice = $finalStonePrice + round($metalPrice) + round($labourAmount) + $gemStone;
			$implodedStoneShape = implode(",", $stoneShape);
			$implodedStoneClarity = implode(",", $stoneClarity);
			$implodedStonePices = implode(",", $stonePices);
			$implodedStoneweight = implode(",", $stoneWeight);
			$implodedCaratPrice = implode(",", $stonePrice);
			//$mm_size = 0;
			$maxStoneCount = max(count($stoneShape), count($stoneClarity), count($stonePices), count($stoneWeight), count($stonePrice));
			$markupAmount = 0;
			if (!empty($priceMarkup)) {
				$markupAmount = ($totalProductPrice * $priceMarkup) / 100;
			}
			if (isset($dmlProductData->id)) {
				$mm_size = ProductsStone::select('mm_size')->where('stone_product_id', $dmlProductData->id)->distinct()->value('mm_size');
			}

			$totalProductPrice += $markupAmount;
			for ($index = 0; $index < $maxStoneCount; $index++) {

				$data[] = array(
					'sr_no' => ($index == 0) ? $serialNumber : '',
					'image' => ($index == 0) ? $file : '',
					'name' => ($index == 0) ? $name : '',
					'sku' => ($index == 0) ? $sku : '',
					'certificate_no' => ($index == 0) ? $certificateNo : '',
					'inventory_status' => ($index == 0) ? $inventoryStatus : '',
					'metal_quality' => ($index == 0) ? $metalQuality : '',
					'metal_weight' => ($index == 0) ? $metalWeight : '',
					'metal_price' => ($index == 0) ? $metalPrice : '',
					'metal_rate' => ($index == 0) ? $metalRate : '',
					'labour_charge' => ($index == 0) ? $labourCharge : '',
					'labour_amount' => ($index == 0) ? $labourAmount : '',
					'stone_shape' => isset($stoneShape[$index]) ? $stoneShape[$index] : '',
					'stone_clarity' => isset($stoneClarity[$index]) ? $stoneClarity[$index] : '',
					'stone_pcs' => isset($stonePices[$index]) ? $stonePices[$index] : '',
					'stone_weight' => isset($stoneWeight[$index]) ? $stoneWeight[$index] : '',
					'mm_size' => isset($mm_size) ? $mm_size : '-',
					'stone_rate' => isset($stoneRate[$index]) ? $stoneRate[$index] : '',
					'stone_price' => isset($stonePrice[$index]) ? $stonePrice[$index] : '',
					'price' => ($index == 0) ? ShowroomHelper::currencyFormatWithoutIcon((round($totalProductPrice))) : '',
				);
			}
		}
		$row = 0;

		return \Excel::create('quotation_products', function ($excel) use ($data, $quotationData) {
			$excel->sheet('Sheet', function ($sheet) use ($data, $quotationData) {
				$rowCount = 0;
				$customerName = '';
				$rowIndex = 0;
				$excelFooter = array('TERMS AND CONDITIONS', '1. Prices in Indian Rupees: The INR basic unit prices quoted above are exclusive of Government Taxes,', ' duties, clearance charges, Additional levies as and if applicable, such as GST, Service Charges, Insurance', ' and Shipping except for if pre-decided for any particular order. Any other new levies introduced by the', ' government or any increase in the present levy rates would be borne by the Partner.', '2. Freight: Freight will be charged extra as decided.', '3. Validity: This proposal and pricing is valid for deals with Diamond Mela Dealers for 180 days unless extended in writing by Diamond Mela.', '4. Payment Terms: Payment should be made in Advance by cheque/DD/RTGS in the name of Diamond Mela Jewels Limited Only.', '5. Delivery: As committed by the representative or specified in the Product Description.', '6. Purchase order to be placed with Diamond Mela Jewels Limited.', '7. The Dealer should neither create any defect, alter nor make any change in the Product without informing', ' Diamond Mela at any point of time nor alter any information regarding the same. If found the Product will', ' not be valid for Returns in any condition.', '8. Diamond Mela will use its best efforts to deliver accepted orders, any other order demands, necessity', ' from dealer as quickly as required.', '9. This is a computer generated performa and does not require Signature.');
				$sheetCol = array('sr_no' => 'B12', 'image' => 'C12', 'name' => 'D12', 'sku' => 'E12', 'certificate_no' => 'F12', 'inventory_status' => 'G12', 'metal_quality' => 'H12', 'metal_weight' => 'I12', 'metal_price' => 'J12', 'metal_rate' => 'K12', 'labour_charge' => 'L12', 'labour_amount' => 'M12', 'stone_shape' => 'N12', 'stone_clarity' => 'O12', 'stone_pcs' => 'P12', 'stone_weight' => 'Q12', 'mm_size' => 'R12', 'stone_rate' => 'S12', 'stone_price' => 'T12', 'price' => 'U12');

				//Add Header
				$sheet->mergeCells('E1:N1');
				$sheet->mergeCells('E2:N2');
				for ($colindex = 1; $colindex <= 11; $colindex++) {
					$sheet->cell('U' . $colindex, function ($cell) {
						$cell->setBorder('', 'medium', '', '');
					});
				}
				for ($colindex = 1; $colindex <= 11; $colindex++) {
					$sheet->cell('A' . $colindex, function ($cell) {
						$cell->setBorder('', 'medium', '', '');
					});
				}
				$sheet->cell('V6', function ($cell) {
					$cell->setBorder('', '', '', 'medium');
				});
				$sheet->cell('V11', function ($cell) {
					$cell->setBorder('', '', '', 'medium');
				});
				$sheet->cell('B11:U11', function ($cell) {
					$cell->setBorder('', '', 'medium', '');
				});

				$sheet->cell('B6:U6', function ($cell) {
					$cell->setBorder('', '', 'medium', '');
				});
				$sheet->cell('U6', function ($cell) {
					$cell->setBorder('', '', 'medium', '');
				});
				/*$sheet->cell('U11', function ($cell) {
					$cell->setBorder('', '', '', 'medium');
				});*/
				$sheet->cell('E1', function ($cell) {
					// manipulate the cell
					$cell->setValue('Diamond Mela Jewels Ltd');
					$cell->setFont(array(
						'family' => 'Calibri',
						'size' => '25',
						'bold' => true,
					));
					$cell->setAlignment('center');
				});
				$sheet->cell('E2', function ($cell) {
					// manipulate the cell
					$cell->setValue('2307, 23rd Floor, 21 Panchratna, Mama Parmanand Marg, Opera House, Girgaon,Mumbai :400004.');
					$cell->setFont(array(
						'family' => 'Calibri',
						'bold' => true,
					));
					$cell->setAlignment('center');
				});
				$sheet->cell('J3', function ($cell) {
					// manipulate the cell
					$cell->setValue('PROPOSED QUOTATION');
					$cell->setFont(array(
						'family' => 'Calibri',
						'bold' => true,
					));
					$cell->setAlignment('center');
				});
				$sheet->cell('J4', function ($cell) {
					// manipulate the cell
					$cell->setValue('CIN:U74999MH14PLC260329');
					$cell->setFont(array(
						'family' => 'Calibri',
						'bold' => true,
					));
					$cell->setAlignment('center');
				});
				$sheet->cell('J5', function ($cell) {
					// manipulate the cell
					$cell->setValue('GSTIN:27AAFCD2233A1ZB');
					$cell->setFont(array(
						'family' => 'Calibri',
						'bold' => true,
					));
					$cell->setAlignment('center');
				});
				$sheet->cell('B8', function ($cell) {
					$cell->setValue('CUSTOMER NAME: ' . isset(Session::get('customer_info')['customer_name']) ? Session::get('customer_info')['customer_name'] : '');
					$cell->setFont(array(
						'family' => 'Calibri',
						'bold' => true,
					));
					$cell->setAlignment('left');
				});
				$sheet->cell('B9', function ($cell) {
					$cell->setValue('ADDRESS: ' . isset(Session::get('customer_info')['shipping_address']) ? Session::get('customer_info')['shipping_address'] : '');
					$cell->setFont(array(
						'family' => 'Calibri',
						'bold' => true,
					));
					$cell->setAlignment('left');
				});
				$sheet->cell('B10', function ($cell) {
					$cell->setValue('GSTIN: ' . isset(Session::get('customer_info')['gstin_number']) ? Session::get('customer_info')['gstin_number'] : '');
					$cell->setFont(array(
						'family' => 'Calibri',
						'bold' => true,
					));
					$cell->setAlignment('left');
				});
				$sheet->cell('R8', function ($cell) {
					$cell->setValue('QUOTATION NO: ' . isset(Session::get('customer_info')['quotation_number']) ? Session::get('customer_info')['quotation_number'] : '');
					$cell->setFont(array(
						'family' => 'Calibri',
						'bold' => false,
					));
					$cell->setAlignment('left');
				});
				$sheet->cell('R9', function ($cell) {
					$cell->setValue('DATE: ' . isset(Session::get('customer_info')['quotation_date']) ? Session::get('customer_info')['quotation_date'] : '');
					$cell->setFont(array(
						'family' => 'Calibri',
						'bold' => false,
					));
					$cell->setAlignment('left');
				});

				$sheet->mergeCells('B1:C1');
				$objDrawing = new PHPExcel_Worksheet_Drawing;
				$objDrawing->setPath(public_path('img/headerlogo.png')); //your image path
				$objDrawing->setCoordinates('B1');
				$objDrawing->setOffsetX(7);
				$objDrawing->setOffsetY(5);
				$objDrawing->setWorksheet($sheet);

				//Add Content
				$sheet->setCellValue('B12', 'Sr No.');
				$sheet->setCellValue('C12', 'Image');
				$sheet->setCellValue('D12', 'Name');
				$sheet->setCellValue('E12', 'SKU');
				$sheet->setCellValue('F12', 'Certificate No');
				$sheet->setCellValue('G12', 'Inventory Status');
				$sheet->setCellValue('H12', 'Metal Quality');
				$sheet->setCellValue('I12', 'Metal Weight');
				$sheet->setCellValue('J12', 'Metal Price');
				$sheet->setCellValue('K12', 'Metal Rate');
				$sheet->setCellValue('L12', 'Labour Charge');
				$sheet->setCellValue('M12', 'Labour Amount');
				$sheet->setCellValue('N12', 'Stone Shape');
				$sheet->setCellValue('O12', 'Stone Clarity');
				$sheet->setCellValue('P12', 'Stone Pcs');
				$sheet->setCellValue('Q12', 'Stone Weight');
				$sheet->setCellValue('R12', 'MM Size');
				$sheet->setCellValue('S12', 'Stone Rate');

				$sheet->setCellValue('T12', 'Stone Price');
				$sheet->setCellValue('U12', 'Price');

				foreach ($data as $key => $value) {
					foreach ($value as $datakey => $val) {
						$rowCount++;
						$rowIndex = substr($sheetCol[$datakey], 1) + $key + 2;
						$rowChar = substr($sheetCol[$datakey], 0, 1);
						if (strpos($val, 'img/') !== false) {
							$objDrawing = new PHPExcel_Worksheet_Drawing();
							$objDrawing->setName('quotation_img');
							$objDrawing->setDescription('quotation_img');
							$objDrawing->setPath($val);
							//$rowNo = (int)$row+2;
							$objDrawing->setCoordinates('C' . $rowIndex);
							$objDrawing->setOffsetX(5);
							$objDrawing->setOffsetY(5);
							$objDrawing->setWidth(80);
							$objDrawing->setHeight(80);
							$objDrawing->setWorksheet($sheet);
							$sheet->setSize(array(
								'C1' . $rowIndex => array(
									'width' => 15,
									'height' => 15,
								),
							));
							$sheet->getRowDimension($rowIndex)->setRowHeight(70);
						}
						if (strpos($val, 'img/') !== false) {
							$sheet->setCellValue($rowChar . $rowIndex, '');
						} else {
							$sheet->setCellValue($rowChar . $rowIndex, $val);
						}
					}
				}
				//Add footer
				$excelEndIndex = 0;
				foreach ($excelFooter as $key => $footer) {
					$sheet->row($rowIndex + 4 + (int) $key, array(
						'', $footer,
					));
					$excelEndIndex = $rowIndex + 4 + (int) $key;
				}
				$columnArr = array('C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U');
				for ($index = 12; $index <= ($rowIndex + 2); $index++) {
					foreach ($columnArr as $char) {
						$sheet->cell($char . $index . ':' . $char . $index, function ($cell) {
							$cell->setBorder('', '', '', 'medium');
						});
					}
				}
				for ($index = 12; $index <= ($excelEndIndex + 1); $index++) {
					$sheet->cell('B' . $index . ':B' . $index, function ($cell) {
						$cell->setBorder('', '', '', 'medium');
					});
					$sheet->cell('V' . $index, function ($cell) {
						$cell->setBorder('', '', '', 'medium');
					});
					$sheet->cell('A' . $index, function ($cell) {
						$cell->setBorder('', 'medium', '', '');
					});
				}

				$sheet->cell('B' . ($rowIndex + 2) . ':U' . ($rowIndex + 2), function ($cell) {
					$cell->setBorder('', '', 'medium', '');
				});
				$sheet->cell('B' . ($excelEndIndex + 1) . ':U' . ($excelEndIndex + 1), function ($cell) {
					$cell->setBorder('', '', 'medium', 'medium');
				});
				Session::forget('customer_info');
			});
			$excel->sheet('Products', function ($sheet) use ($data) {
				$sheetCol = array('sr_no' => 'A1', 'image' => 'B1', 'name' => 'C1', 'sku' => 'D1', 'certificate_no' => 'E1', 'inventory_status' => 'F1', 'metal_quality' => 'G1', 'metal_weight' => 'H1', 'metal_price' => 'I1', 'metal_rate' => 'J1', 'labour_charge' => 'K1', 'labour_amount' => 'L1', 'stone_shape' => 'M1', 'stone_clarity' => 'N1', 'stone_pcs' => 'O1', 'stone_weight' => 'P1', 'mm_size' => 'Q1', 'stone_rate' => 'R1', 'stone_price' => 'S1', 'price' => 'T1');
				//Add Content
				$sheet->setCellValue('A1', 'Sr No.');
				$sheet->setCellValue('B1', 'Image');
				$sheet->setCellValue('C1', 'Name');
				$sheet->setCellValue('D1', 'SKU');
				$sheet->setCellValue('E1', 'Certificate No');
				$sheet->setCellValue('F1', 'Inventory Status');
				$sheet->setCellValue('G1', 'Metal Quality');
				$sheet->setCellValue('H1', 'Metal Weight');
				$sheet->setCellValue('I1', 'Metal Price');
				$sheet->setCellValue('J1', 'Metal Rate');
				$sheet->setCellValue('K1', 'Labour Charge');
				$sheet->setCellValue('L1', 'Labour Amount');
				$sheet->setCellValue('M1', 'Stone Shape');
				$sheet->setCellValue('N1', 'Stone Clarity');
				$sheet->setCellValue('O1', 'Stone Pcs');
				$sheet->setCellValue('P1', 'Stone Weight');
				$sheet->setCellValue('Q1', 'MM Size');
				$sheet->setCellValue('R1', 'Stone Rate');
				$sheet->setCellValue('S1', 'Stone Price');
				$sheet->setCellValue('T1', 'Price');
				$rowCount = 0;
				foreach ($data as $key => $value) {
					foreach ($value as $datakey => $val) {
						$rowCount++;
						$rowIndex = substr($sheetCol[$datakey], 1) + $key + 2;
						$rowChar = substr($sheetCol[$datakey], 0, 1);
						if (strpos($val, 'img/') !== false) {
							$objDrawing = new PHPExcel_Worksheet_Drawing();
							$objDrawing->setName('quotation_img');
							$objDrawing->setDescription('quotation_img');
							$objDrawing->setPath($val);
							//$rowNo = (int)$row+2;
							$objDrawing->setCoordinates('B' . $rowIndex);
							$objDrawing->setOffsetX(5);
							$objDrawing->setOffsetY(5);
							$objDrawing->setWidth(80);
							$objDrawing->setHeight(80);
							$objDrawing->setWorksheet($sheet);
							$sheet->setSize(array(
								'B1' . $rowIndex => array(
									'width' => 15,
									'height' => 15,
								),
							));
							$sheet->getRowDimension($rowIndex)->setRowHeight(70);
						}
						if (strpos($val, 'img/') !== false) {
							$sheet->setCellValue($rowChar . $rowIndex, '');
						} else {
							$sheet->setCellValue($rowChar . $rowIndex, $val);
						}
					}
				}
			});
		})->download('xlsx');
		DB::setTablePrefix('dml_');
	}
	//Check image is exist on server by url
	public static function isImageExist($url) {
		if (!empty($url)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return $httpCode;
		}
	}
	//Get GSTIN number by customer id
	public static function getGstinNumber($customerId) {
		$gstin = DB::select("SELECT
		    customer_entity_varchar.value AS gstin_number,
		    eav_attribute.attribute_id,
		    customer_entity.entity_id
		FROM
		    eav_attribute
		JOIN customer_entity_varchar ON customer_entity_varchar.attribute_id = eav_attribute.attribute_id
		JOIN customer_entity ON customer_entity.entity_id = customer_entity_varchar.entity_id
		WHERE
		    eav_attribute.attribute_code = 'gstin' and customer_entity.entity_id=" . $customerId);
		return isset($gstin[0]->gstin_number) ? $gstin[0]->gstin_number : '';
	}
	//Get stone shape id by label
	public static function getStoneShapeId($label) {
		$sideStoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0 AND EAOV.value='" . $label . "'");
		return isset($sideStoneShapeDetails[0]->option_id) ? $sideStoneShapeDetails[0]->option_id : '';
	}
	//Get stone clarity id by label
	public static function getStoneClarityId($label) {
		$stoneClarityDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0 AND EAOV.value='" . $label . "'");
		return isset($stoneClarityDetails[0]->option_id) ? $stoneClarityDetails[0]->option_id : '';
	}
	//Get stone range
	public static function getStoneRangeData($forShapes = array('round')) {
		//var_dump($forShapes);
		//Get stone range
		$stoneRangeData = array();
		if (App::environment('local')) {
			$stoneQualityId = Config::get('constants.fixIds.local.quotation_stone_quality_id');
			$shape_round = Config::get('constants.fixIds.local.stone_shape_round');
			$shape_taper_baguette = Config::get('constants.fixIds.local.stone_shape_taper_baguette');
			$shape_baguette = Config::get('constants.fixIds.live.stone_shape_baguette');
			$shape_taper = Config::get('constants.fixIds.local.stone_shape_taper');
		} else {
			$stoneQualityId = Config::get('constants.fixIds.live.quotation_stone_quality_id');
			$shape_round = Config::get('constants.fixIds.live.stone_shape_round');
			$shape_taper_baguette = Config::get('constants.fixIds.live.stone_shape_taper_baguette');
			$shape_baguette = Config::get('constants.fixIds.live.stone_shape_baguette');
			$shape_taper = Config::get('constants.fixIds.live.stone_shape_taper');
		}

		if (in_array('round', $forShapes)) {

			//echo "SELECT DISTINCT stone_carat_from,stone_carat_to FROM `grp_stone_manage` WHERE stone_shape=" . DB::raw("'$shape_round'") . " order by stone_carat_from,`stone_carat_to` desc limit 5";exit;

			$stoneRangeData['round'] = DB::select("SELECT DISTINCT mm_from as stone_carat_from,mm_to as stone_carat_to FROM `grp_stone_manage` WHERE stone_shape=" . DB::raw("'$shape_round'") . " order by stone_carat_from,`stone_carat_to` desc limit 5");
		}
		if (in_array('round_withoutmm', $forShapes)) {
			//echo "SELECT DISTINCT stone_carat_from,stone_carat_to FROM `grp_stone_manage` WHERE stone_shape=" . DB::raw("'$shape_round'") . " order by stone_carat_from,`stone_carat_to` desc limit 5";exit;
			$stoneRangeData['round'] = DB::select("SELECT DISTINCT stone_carat_from,stone_carat_to FROM `grp_stone_manage` WHERE stone_shape=" . DB::raw("'$shape_round'") . " order by stone_carat_from,`stone_carat_to` desc limit 5");
		}
		if (in_array('fancy2', $forShapes)) {
			$stoneRangeData['fancy2'] = DB::select("SELECT DISTINCT stone_carat_from,stone_carat_to FROM `grp_stone_manage` WHERE stone_shape IN (" . DB::raw("'$shape_taper_baguette'") . ',' . DB::raw("'$shape_taper'") . ',' . DB::raw("'$shape_baguette'") . ") order by stone_carat_from,`stone_carat_to` desc limit 5");
		}
		if (in_array('fancy1', $forShapes)) {
			//echo "SELECT DISTINCT stone_carat_from,stone_carat_to FROM `grp_stone_manage` WHERE stone_shape NOT IN (" . DB::raw("'$shape_taper_baguette'") . ',' . DB::raw("'$shape_taper'") . ',' . DB::raw("'$shape_round'") . ',' . DB::raw("'$shape_baguette'") . ") order by stone_carat_from,`stone_carat_to` desc limit 5";exit;
			$stoneRangeData['fancy1'] = DB::select("SELECT DISTINCT stone_carat_from,stone_carat_to FROM `grp_stone_manage` WHERE stone_shape NOT IN (" . DB::raw("'$shape_taper_baguette'") . ',' . DB::raw("'$shape_taper'") . ',' . DB::raw("'$shape_round'") . ',' . DB::raw("'$shape_baguette'") . ") order by stone_carat_from,`stone_carat_to` desc limit 5");
		}
		return $stoneRangeData;
	}
	//Get inventory status options
	public static function getInventoryStatusOptions() {
		$inventoryStatus = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0");
		$status = array();
		foreach ($inventoryStatus as $key => $value) {
			$status[strtolower(str_replace(' ', '', $value->value))] = $value->option_id;
		}

		return $status;
	}
	//Get order detail by id
	public static function getOrderData($orderId) {
		DB::setTablePrefix('');
		$orderData = DB::table("sales_flat_order")->select('entity_id', 'created_at', 'customer_id', 'customer_firstname', 'customer_lastname', 'customer_middlename', 'customer_email', 'approval_memo_number', 'total_qty_ordered', 'grand_total', 'custom_discount_percent', 'payment_mode', 'billing_address_id', 'shipping_address_id', 'selected_franchise_id', 'selected_franchise_commission', 'agent_name', 'agent_commission', 'status', 'dmlstore_order_increment_id', 'franchise_order_increment_id', 'shipping_amount', 'subtotal', 'isfranchisee', 'shipping_description')->where("entity_id", "=", DB::raw("$orderId"))->get()->first();
		DB::setTablePrefix('dml_');
		return $orderData;
	}
	//Get order items by order id
	public static function getOrderItems($orderId) {
		DB::setTablePrefix('');
		$orderItems = DB::table("sales_flat_order_item")->select("item_id", "product_id", "price", "qty_ordered", "discount_amount", "tax_amount")->where("order_id", "=", DB::raw("$orderId"))->get();
		DB::setTablePrefix('dml_');
		return $orderItems;
	}
	//Get customer group id
	public static function getCustomerGroup($customerId) {
		DB::setTablePrefix('');
		$group = DB::table("customer_entity as ce")->select("ce.entity_id", "cg.customer_group_id", "cg.customer_group_code")
			->join('customer_group as cg', 'ce.group_id', '=', 'cg.customer_group_id')
			->where("ce.entity_id", "=", DB::raw("$customerId"))->get()->first();
		DB::setTablePrefix('dml_');
		return $group;
	}
	//Get invoice data by order id

	public static function getInvoiceData($orderId) {

		DB::setTablePrefix('');
		$invoice = DB::table("sales_flat_invoice")->select("*")->where("order_id", "=", DB::raw("$orderId"))->get()->first();
		DB::setTablePrefix('dml_');
		return $invoice;
	}
	//Get customer adress by id
	public static function getAddressById($addressId) {
		DB::setTablePrefix('');
		$address = DB::select("select * from sales_flat_order_address where entity_id=" . DB::raw("$addressId"));
		DB::setTablePrefix('dml_');
		return isset($address[0]) ? $address[0] : '';
	}
	//Get invoice collection by order id
	public static function getInvoiceByOrder($orderId) {
		if (!empty($orderId)) {
			DB::setTablePrefix('');
			$invoice = DB::table("sales_flat_invoice")->select("*")->where("order_id", "=", DB::raw("$orderId"))->get();
			DB::setTablePrefix('dml_');
			return $invoice;
		}
	}
	//Get invoice items
	public static function getInvoiceItems($invoiceId) {
		if (!empty($invoiceId)) {
			DB::setTablePrefix('');
			$invoiceItems = DB::table("sales_flat_invoice_item")->select("*")->where("parent_id", "=", DB::raw("$invoiceId"))->get();
			DB::setTablePrefix('dml_');
			return $invoiceItems;
		}
	}
	//Get product data by id
	public static function getProductData($productId) {
		DB::setTablePrefix("");
		$product = DB::table("catalog_product_flat_1")->select("*")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
		DB::setTablePrefix("dml_");
		return $product;
	}
	public static function convertNumberToCurrency($number) {
		setlocale(LC_MONETARY, 'en_IN');
		$amount = money_format('%!i', $number);
		return $amount;
	}

	//Get country name by code
	public static function getCountryName($countryCode) {
		if (App::environment('local')) {
			$getCountryUrl = config('constants.apiurl.local.get_country_name');
		} else if (App::environment('test')) {
			$getCountryUrl = config('constants.apiurl.test.get_country_name');
		} else {
			$getCountryUrl = config('constants.apiurl.live.get_country_name');
		}
		//var_dump($getCountryUrl);exit;
		$postParam = 'country_code=' . $countryCode;
		$ch = curl_init($getCountryUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$result = json_decode($result);
		$countryName = '';
		//var_dump($result);exit;
		if (!empty($result)) {
			if ($result->status) {
				$countryName = isset($result->country_name) ? $result->country_name : '';
			}
			return $countryName;
		}
		return false;
	}
	//Get order id by item id
	public static function getOrderIdByItem($itemId) {
		DB::setTablePrefix('');
		$orderId = DB::table('sales_flat_order_item')->select('order_id')->where('item_id', '=', DB::raw("$itemId"))->get()->first();
		DB::setTablePrefix('dml_');
		return isset($orderId->order_id) ? $orderId->order_id : '';
	}
	//Get order detail by item id
	public static function getOrderDetailByItem($itemId) {
		DB::setTablePrefix('');
		$order = DB::table('sales_flat_order as order')
			->select('order.entity_id', 'order.state', 'order.status', 'ce.certificate_no')
			->join('sales_flat_order_item as order_item', 'order_item.order_id', '=', 'order.entity_id')
			->join('catalog_product_flat_1 as ce', 'ce.entity_id', '=', 'order_item.product_id')
			->where('order_item.product_id', '=', DB::raw("$itemId"))->get()->first();
		DB::setTablePrefix('dml_');
		return $order;
	}
	//Get order id by product
	public static function getOrderId($productId) {
		DB::setTablePrefix('');
		$orderIds = DB::table('sales_flat_order_item')->select('order_id')->where('product_id', '=', DB::raw("$productId"))->get()->first();
		DB::setTablePrefix('dml_');
		return isset($orderIds->order_id) ? $orderIds->order_id : '';
	}
	public static function removefromapprovaltable($reqProductId) {
		$affectedRows = ApprovalMemoHistroy::where('product_id', '=', $reqProductId)->delete();
		//$productsIds = DB::table('approval_memo')->select('product_ids')->where('product_ids', 'like', "%".$reqProductId."%"))->first();
		//$productsIds = DB::table('approval_memo')->select('product_ids')->->where('name', 'like', 'T%')whereRaw('product_ids like %' . $reqProductId . '%')->first();
		/*$productsIdsResult = DB::select("SELECT product_ids FROM dml_approval_memo WHERE " . $reqProductId . " IN (product_ids)");
		if (count($productsIdsResult) > 0) {
			$productsIds = $productsIdsResult[0]->product_ids;
			$productsIdArr = explode(',', $productsIds);
			if (count($productsIdArr) > 1) {
				$productsIdNewArr = array();
				foreach ($productsIdArr as $productId) {
					if ($productId != $reqProductId) {
						$productsIdNewArr[] = $productId;
					}
				}
				$productsIdNewStr = implode(',', $productsIdNewArr);
				//var_dump($productsIdNewStr);exit;
				/-*$productsIds = DB::table('dml_approval_memo')->select('product_ids')->where('product_ids', 'like', DB::raw("%$reqProductId%"))->update(['product_ids' => $productsIdNewStr]);*-/
				$updateResult = DB::statement("UPDATE dml_approval_memo SET product_ids = '" . $productsIdNewStr . "' WHERE " . $reqProductId . " IN (product_ids)");
			} else {
				$deleteResult = DB::statement("DELETE FROM dml_approval_memo WHERE " . $reqProductId . " IN (product_ids)");
			}
		}*/
	}
	public static function getMemoData($productId) {
		DB::setTablePrefix('');

		$memoData = DB::select("select id,customer_id from dml_approval_memo where find_in_set('" . $productId . "', cast(product_ids as char)) > 0 ORDER BY created_at DESC LIMIT 1");

		DB::setTablePrefix('dml_');
		return $memoData;
	}

	//Get product id by certificate
	public static function getProductIdByCertificate($certificate) {
		DB::setTablePrefix('');
		$product = DB::table("catalog_product_flat_1")->select("entity_id")->where("certificate_no", "=", DB::raw("'$certificate'"))->get()->first();
		DB::setTablePrefix('dml_');
		return isset($product->entity_id) ? $product->entity_id : '';
	}
	//Get default discount for customer
	public static function getDefaultDiscount($invoiceGrandTotal) {

		if ($invoiceGrandTotal < 25000) {
			$column14k = Config::get('constants.settings.keys.discount_invoice_less_25');
			$column18k = Config::get('constants.settings.keys.discount_invoice_less_25_18k');
		} else if ($invoiceGrandTotal >= 25000 && $invoiceGrandTotal <= 100000) {
			$column14k = Config::get('constants.settings.keys.discount_invoice_25_to_lakhs');
			$column18k = Config::get('constants.settings.keys.discount_invoice_25_100k_18k');
		} else if ($invoiceGrandTotal >= 100000) {

			$column14k = Config::get('constants.settings.keys.discount_invoice_above_lakhs');
			$column18k = Config::get('constants.settings.keys.discount_invoice_gt_100k_18k');
		}
		$discount14K = Setting::select('value')->where('key', $column14k)->get()->first();
		$discount18K = Setting::select('value')->where('key', $column18k)->get()->first();
		return array('14_k_discount' => $discount14K->value, '18_k_discount' => $discount18K->value);
	}
	//Get approval/deposit discount for customer

	public static function getApprovalDepositDiscount($invoiceGrandTotal, $customerId, $discountType) {
		$column14k = '';
		$column18k = '';
		if ($discountType == 'approval_discount') {
			if ($invoiceGrandTotal < 25000) {
				$column14k = 'discount_approval_less_25';
				$column18k = 'discount_approval_less_25_18k';
			} else if ($invoiceGrandTotal >= 25000 && $invoiceGrandTotal <= 100000) {
				$column14k = 'discount_approval_25_to_lakhs';
				$column18k = 'discount_approval_25_100k_18k';
			} else if ($invoiceGrandTotal >= 100000) {
				$column14k = 'discount_approval_above_lakhs';
				$column18k = 'discount_approval_gt_100k_18k';
			}
		} else if ($discountType == 'deposit_discount') {
			if ($invoiceGrandTotal < 25000) {
				$column14k = 'discount_deposit_less_25';
				$column18k = 'discount_deposit_less_25_18k';
			} else if ($invoiceGrandTotal >= 25000 && $invoiceGrandTotal <= 100000) {
				$column14k = 'discount_deposit_25_to_lakhs';
				$column18k = 'discount_deposit_25_100k_18k';
			} else if ($invoiceGrandTotal >= 100000) {
				$column14k = 'discount_deposit_above_lakhs';
				$column18k = 'discount_deposit_gt_100k_18k';
			}
		} else {
			if ($invoiceGrandTotal < 25000) {
				$column14k = Config::get('constants.settings.keys.discount_invoice_less_25');
				$column18k = Config::get('constants.settings.keys.discount_invoice_less_25_18k');
			} else if ($invoiceGrandTotal >= 25000 && $invoiceGrandTotal <= 100000) {
				$column14k = Config::get('constants.settings.keys.discount_invoice_25_to_lakhs');
				$column18k = Config::get('constants.settings.keys.discount_invoice_25_100k_18k');
			} else if ($invoiceGrandTotal >= 100000) {

				$column14k = Config::get('constants.settings.keys.discount_invoice_above_lakhs');
				$column18k = Config::get('constants.settings.keys.discount_invoice_gt_100k_18k');
			}
		}
		if ($discountType == 'approval_discount' || $discountType == 'deposit_discount') {
			DB::setTablePrefix('');
			$discount14K = DB::table('customer_entity_varchar as ce')->select('value')->join('eav_attribute as eav', 'eav.attribute_id', '=', 'ce.attribute_id')->where('eav.attribute_code', '=', DB::raw("'$column14k'"))->where('ce.entity_id', '=', DB::raw("'$customerId'"))->get()->first();
			$discount14K = isset($discount14K->value) ? $discount14K->value : 0;

			$discount18K = DB::table('customer_entity_varchar as ce')->select('value')->join('eav_attribute as eav', 'eav.attribute_id', '=', 'ce.attribute_id')->where('eav.attribute_code', '=', DB::raw("'$column18k'"))->where('ce.entity_id', '=', DB::raw("'$customerId'"))->get()->first();
			$discount18K = isset($discount18K->value) ? $discount18K->value : 0;
		} else {
			$discount14K = Setting::select('value')->where('key', $column14k)->get()->first();
			$discount18K = Setting::select('value')->where('key', $column18k)->get()->first();
			$discount14K = $discount14K->value;
			$discount18K = $discount18K->value;
		}
		return array('14_k_discount' => $discount14K, '18_k_discount' => $discount18K);
	}
	public static function convertNumberToWords($number) {
		$no = round($number);
		$decimal = round($number - ($no = floor($number)), 2) * 100;
		$digits_length = strlen($no);
		$i = 0;
		$str = array();
		$words = array(
			0 => '',
			1 => 'One',
			2 => 'Two',
			3 => 'Three',
			4 => 'Four',
			5 => 'Five',
			6 => 'Six',
			7 => 'Seven',
			8 => 'Eight',
			9 => 'Nine',
			10 => 'Ten',
			11 => 'Eleven',
			12 => 'Twelve',
			13 => 'Thirteen',
			14 => 'Fourteen',
			15 => 'Fifteen',
			16 => 'Sixteen',
			17 => 'Seventeen',
			18 => 'Eighteen',
			19 => 'Nineteen',
			20 => 'Twenty',
			30 => 'Thirty',
			40 => 'Forty',
			50 => 'Fifty',
			60 => 'Sixty',
			70 => 'Seventy',
			80 => 'Eighty',
			90 => 'Ninety');
		$digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
		while ($i < $digits_length) {
			$divider = ($i == 2) ? 10 : 100;
			$number = floor($no % $divider);
			$no = floor($no / $divider);
			$i += $divider == 10 ? 1 : 2;
			if ($number) {
				$plural = (($counter = count($str)) && $number > 9) ? 's' : null;
				$str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural;
			} else {
				$str[] = null;
			}
		}

		$Rupees = implode(' ', array_reverse($str));
		$paise = ($decimal) ? "And Paise " . ($words[$decimal - $decimal % 10]) . " " . ($words[$decimal % 10]) : '';
		return ($Rupees ? 'Rupees ' . $Rupees : '') . $paise . " Only";
	}
	//Get invoice product detail
	public static function getInvoiceProductData($productId, $invoiceId) {
		$productData = InvoiceProducts::select('*')->where('product_id', '=', DB::raw("$productId"))->where('invoice_id', '=', DB::raw("$invoiceId"))->get()->first();
		return $productData;
	}
	//Get invoice product detail
	public static function getCertificateBySku($sku) {
		DB::setTablePrefix('');
		$prod_certi = DB::table("catalog_product_flat_1")->select("certificate_no")->where("sku", "=", DB::raw('"' . $sku . '"'))->get()->first();
		DB::setTablePrefix('dml_');
		if (!empty($prod_certi->certificate_no)) {
			return $prod_certi->certificate_no;
		} else {
			return false;
		}
	}
	//Get invoice item data
	public static function getInvoiceItemData($productId, $invoiceId) {
		DB::setTablePrefix('');
		$invoiceItem = DB::table("sales_flat_invoice_item")->select("*")->where("product_id", "=", DB::raw("$productId"))->where("parent_id", "=", DB::raw("$invoiceId"))->get()->first();
		DB::setTablePrefix('dml_');
		return $invoiceItem;
	}
	//Get invoice item discount
	public static function getInvoiceItemDiscount($productId, $invoiceId) {
		DB::setTablePrefix('');
		$invoiceData = DB::table("sales_flat_invoice_item")->select("discount_amount")->where("product_id", "=", DB::raw("$productId"))->where("parent_id", "=", DB::raw("$invoiceId"))->get()->first();
		return isset($invoiceData->discount_amount) ? $invoiceData->discount_amount : 0;
	}
	//Get invoice data by id
	public static function getInvoiceById($invoiceId) {
		DB::setTablePrefix('');
		$invoice = DB::table("sales_flat_invoice")->select("*")->where("entity_id", "=", DB::raw("$invoiceId"))->get()->first();
		DB::setTablePrefix('dml_');
		return $invoice;
	}
	//Get stone data
	public static function getStoneData($productId) {

		if (!empty($productId)) {
			$certificate = InventoryHelper::getCertificateNo($productId);
			$isFromDmlProdStone = true;
			$stoneData = array();
			//$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->select('id', 'rts_stone_quality', 'certificate_no')->where('certificate_no', '=', DB::raw("'$certificate'"))->get()->first();
			DB::setTablePrefix('');
			$product = DB::table('catalog_product_flat_1')->select('entity_id', 'rts_stone_quality')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
			DB::setTablePrefix('dml_');

			//var_dump($product);exit;
			if (count($product) > 0) {

				//if (count($product->stones->toArray()) > 0) {
				$isFromDmlProdStone = false;
				//$product = DB::table('catalog_product_flat_1')->select('entity_id', 'rts_stone_quality')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
				$stoneQuality = isset($product->rts_stone_quality) ? $product->rts_stone_quality : 'SI-IJ';
				$stoneData = DB::select("SELECT * FROM grp_stone WHERE stone_product_id=" . $productId . "");

				/*} else {

					$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->select('id', 'rts_stone_quality', 'certificate_no')->where('certificate_no', '=', DB::raw("'$certificate'"))->get()->first();
					if (count($dmlProductData->stones->toArray()) > 0) {
						$stoneQuality = isset($dmlProductData->rts_stone_quality) ? $dmlProductData->rts_stone_quality : 'SI-IJ';
						$dmlProductId = isset($dmlProductData->id) ? $dmlProductData->id : '';
						$stoneData = ProductsStone::select('*')->where('stone_product_id', '=', DB::raw("'$dmlProductId'"))->get();
					}

				}*/
			} else {

				$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->select('id', 'rts_stone_quality', 'certificate_no')->where('certificate_no', '=', DB::raw("'$certificate'"))->get()->first();
				//if (count($dmlProductData->stones->toArray()) > 0) {
				$stoneQuality = isset($dmlProductData->rts_stone_quality) ? $dmlProductData->rts_stone_quality : 'SI-IJ';
				$dmlProductId = isset($dmlProductData->id) ? $dmlProductData->id : '';
				$stoneData = ProductsStone::select('*')->where('stone_product_id', '=', DB::raw("'$dmlProductId'"))->get();
				//}

				//print_r($stoneData);exit;
				//$dmlProductId = isset($dmlProductData->id) ? $dmlProductData->id : '';
			}

			//print_r($stoneData);exit;
			//if(!empty($dmlProductData))
			//{
			$sideStoneTypeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_type' AND EAOV.store_id = 0");
			$sideStoneSubTypeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_sub_type' AND EAOV.store_id = 0");
			$sideStoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
			$sideStoneCutDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_cut' AND EAOV.store_id = 0");
			$sideStoneClarityDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0");
			$sideStoneSettingDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_setting' AND EAOV.store_id = 0");
			$stoneShapeData = array();
			$stoneTypeData = array();
			$stoneSubTypeData = array();
			$stoneCutData = array();
			$stoneClarityData = array();
			$stoneClarity = array();
			$stoneSettingData = array();
			$stoneFinalPrice = 0;
			$side_stone_data = array();
			foreach ($sideStoneTypeDetails as $key => $stoneShape) {
				$stoneTypeData[$stoneShape->option_id] = $stoneShape->value;
			}
			foreach ($sideStoneSubTypeDetails as $key => $stoneShape) {
				$stoneSubTypeData[$stoneShape->option_id] = $stoneShape->value;
			}
			foreach ($sideStoneCutDetails as $key => $stoneShape) {
				$stoneCutData[$stoneShape->option_id] = $stoneShape->value;
			}
			foreach ($sideStoneClarityDetails as $key => $stoneShape) {
				$stoneClarityData[$stoneShape->option_id] = $stoneShape->value;
				$stoneClarity[$stoneShape->value] = $stoneShape->option_id;
			}
			foreach ($sideStoneSettingDetails as $key => $stoneShape) {
				$stoneSettingData[$stoneShape->option_id] = $stoneShape->value;
			}
			foreach ($sideStoneShapeDetails as $key => $stoneShape) {
				$stoneShapeData[$stoneShape->option_id] = $stoneShape->value;
			}
			$product_stone_weight = array();

			foreach ($stoneData as $key => $sideStone) {
				$sideStoneType = isset($stoneTypeData[$sideStone->stone_type]) ? $stoneTypeData[$sideStone->stone_type] : '';
				$sideStoneSubType = isset($stoneSubTypeData[$sideStone->stone_subtype]) ? $stoneSubTypeData[$sideStone->stone_subtype] : '';
				$sideStoneSeiveSize = isset($sideStone->seive_size) ? $sideStone->seive_size : '';
				$sideStoneMmSize = isset($sideStone->mm_size) ? $sideStone->mm_size : '';
				$sideStoneShape = isset($stoneShapeData[$sideStone->stone_shape]) ? $stoneShapeData[$sideStone->stone_shape] : '';

				if (!empty($sideStone->stone_clarity)) {
					//$sideStone->stone_clarity = 63;
					$sideStoneClarity = isset($sideStone->stone_clarity) ? $sideStone->stone_clarity : '';
					$sideStoneClarityText = isset($stoneClarityData[$sideStone->stone_clarity]) ? $stoneClarityData[$sideStone->stone_clarity] : '';
				} else {
					$sideStoneClarity = array_search($stoneQuality, $stoneClarityData);
					$sideStoneClarityText = $stoneQuality;
				}
				$sideStoneSetting = isset($stoneSettingData[$sideStone->stone_setting_type]) ? $stoneSettingData[$sideStone->stone_setting_type] : '';
				$sideStoneCut = isset($stoneCutData[$sideStone->stone_cut]) ? $stoneCutData[$sideStone->stone_cut] : '';
				$carat = isset($sideStone->carat) ? $sideStone->carat : 0;
				$stoneUse = isset($sideStone->stone_use) ? $sideStone->stone_use : 0;

				if ($isFromDmlProdStone) {
					$stoneTotalCarat = $carat;
				} else {
					//var_dump($sideStone);exit;
					$stoneTotalCarat = $sideStone->total_carat;
					$stoneTotalCarat = number_format(floatval(($stoneTotalCarat / $sideStone->stone_use)), 3);
				}
				$stone_clarity = isset($sideStone->stone_clarity) ? (!empty($sideStone->stone_clarity) ? $sideStone->stone_clarity : $sideStoneClarity) : $sideStoneClarity;
				$productSideStoneFianlPrice = 0;

				/* echo "SELECT `main_table`.* FROM `grp_stone_manage` AS `main_table` WHERE (stone_shape = " . $sideStone->stone_shape . ") AND (stone_clarity = '" . $stone_clarity . "') AND (stone_carat_from <= " . $stoneTotalCarat . ") AND (stone_carat_to >= " . $stoneTotalCarat . ")";exit; */
				$modelSideStone = DB::select("SELECT `main_table`.* FROM `grp_stone_manage` AS `main_table` WHERE (stone_shape = " . $sideStone->stone_shape . ") AND (stone_clarity = '" . $stone_clarity . "') AND (stone_carat_from <= " . $stoneTotalCarat . ") AND (stone_carat_to >= " . $stoneTotalCarat . ")");

				//echo "SELECT `main_table`.* FROM `grp_stone_manage` AS `main_table` WHERE (stone_shape = " . $sideStone->stone_shape . ") AND (stone_clarity = '" . $stone_clarity . "') AND (stone_carat_from <= " . $stoneTotalCarat . ") AND (stone_carat_to >= " . $stoneTotalCarat . ")";exit;

				//var_dump($modelSideStone);exit;

				if (count($modelSideStone) > 0) {
					foreach ($modelSideStone as $key => $modelStone) {
						$stone_price = round($modelStone->stone_price);
					}

					$stoneCarat = number_format(floatval(($sideStone->carat)), 3);
					//echo $stoneCarat;exit;

					//$stoneCarat = $stoneCarat * round($sideStone->stone_use,2);
					//echo $stone_price."  ".$sideStone->total_carat."<br>";
					$productSideStonePrice = round($stone_price * $sideStone->total_carat);
					//echo $productSideStonePrice."   ".$sideStone->stone_use;exit;
					$productSideStoneFianlPrice = round($productSideStonePrice);
					//echo $productSideStoneFianlPrice;exit;
				}

				//var_dump($productSideStoneFianlPrice);exit;

				$side_stone_data['type'][] = $sideStoneType;
				$side_stone_data['subtype'][] = $sideStoneSubType;
				$side_stone_data['mm_size'][] = $sideStoneMmSize;
				$side_stone_data['seive_size'][] = $sideStoneSeiveSize;
				$side_stone_data['shape'][] = $sideStoneShape;
				$side_stone_data['cut'][] = $sideStoneCut;
				$side_stone_data['carat'][] = $sideStone->carat;
				$side_stone_data['stone'][] = $sideStone->stone_stone;
				$side_stone_data['stone_use'][] = $sideStone->stone_use;
				$side_stone_data['setting'][] = $sideStoneSetting;
				$side_stone_data['stoneclarity'][] = $sideStoneClarityText;
				$stoneCtTotal = $stoneTotalCarat;
				$side_stone_data['totalcts'][] = $stoneTotalCarat;
				$stoneFinalPrice = $productSideStoneFianlPrice;
				$product_stone_price[] = $productSideStoneFianlPrice;
				if ($stoneTotalCarat < 1) {
					$product_price_per_ct = $productSideStoneFianlPrice;
				} else {
					$product_price_per_ct = $productSideStoneFianlPrice / $stoneTotalCarat;
				}
				$product_stone_weight[] = $stoneCtTotal;
				$roundSideStonePriceValue = round($productSideStoneFianlPrice);
				$roundSideStonePrice = preg_replace('~\.0+$~', '', $roundSideStonePriceValue);
				$roundSideStonePriceperctValue = round($product_price_per_ct);
				$roundSideStonePriceperct = preg_replace('~\.0+$~', '', $roundSideStonePriceperctValue);
				$side_stone_data['percts'][] = $roundSideStonePriceperct;
				$side_stone_data['stone_price'][] = $roundSideStonePrice;
			}

			if ($stoneFinalPrice != 0) {
				$stoneTotalValue = array_sum($product_stone_price);
				$stoneTotalWeight[] = array_sum($product_stone_weight);
				$stonePriceTotalValue = ShowroomHelper::currencyFormat(round($stoneTotalValue));
				$stonePriceTotal = preg_replace('~\.0+$~', '', $stonePriceTotalValue);
				$stoneTotal = preg_replace('~\.0+$~', '', $stoneTotalValue);
				$side_stone_data['total'] = $stonePriceTotal;
				$side_stone_data['simple'] = $stoneTotal;
				$side_stone_data['totalweight'] = $stoneTotalWeight;
			} else {
				$stoneTotalWeight[] = array_sum($product_stone_weight);
				$side_stone_data['totalweight'] = $stoneTotalWeight;
			}
			return $side_stone_data;
			//}
		}
	}

	//Update product attribute value
	public static function updateProductAttribute($productId, $attributeCode, $attributeValue) {
		if (!empty($productId) && !empty($attributeCode)) {
			$sql = "UPDATE catalog_product_flat_1 set " . $attributeCode . "=" . DB::raw("'$attributeValue'") . " WHERE entity_id=" . DB::raw("$productId");
			DB::statement($sql);
		}
	}
	//Get invoice increment id by invoice entity_id
	public static function getInvoiceIncIdByInvoice($invoiceId) {
		if (!empty($invoiceId)) {
			DB::setTablePrefix('');
			$invoiceIncrement = DB::table('sales_flat_invoice')->select('increment_id')->where('entity_id', '=', DB::raw("$invoiceId"))->get()->first();
			DB::setTablePrefix('dml_');
			return isset($invoiceIncrement->increment_id) ? $invoiceIncrement->increment_id : '';
		}
	}

	public static function getImageOptions($inventoryStatus = null) {
		$imageCount = InventoryHelper::getProductImageCount($inventoryStatus);
		$imageArr = array('1' => config('constants.product_image_pdf.image_options.with_image') . '(' . $imageCount['with_image'] . ')', '2' => config('constants.product_image_pdf.image_options.without_image') . '(' . $imageCount['without_image'] . ')');
		return $imageArr;
	}

	public static function getImageOptions111() {
		$imageCount = InventoryHelper::getProductImageCount();
		$imageArr = array('1' => config('constants.product_image_pdf.image_options.with_image'), '2' => config('constants.product_image_pdf.image_options.without_image'));

		return $imageArr;
	}

	public static function getInventoryProductImageFilter($filterProductImage, $imageCount) {
		$product_image_html = '';
		$product_image_html .= '<select name="productimagemanager" id="productimagemanager" class="form-control" ><option value="">Image</option>';

		/*$imageArr = array('1'=> config('constants.product_image_pdf.image_options.with_image').'('.$imageCount['with_image'].')','2'=> config('constants.product_image_pdf.image_options.without_image').'('.$imageCount['without_image'].')' );*/
		$imageArr = array('with_image' => $imageCount['with_image'], 'without_image' => $imageCount['without_image']);
		//print_r($imageArr);exit;
		/* $productImage = array();
		foreach($imageArr as $imageKey => $imageVal) {
			$selected = '';
			if ($imageKey == $filterProductImage) {
				$selected = 'selected';
			}
			if(!empty($filterProductImage)) {
				$productImage[] =
				$product_image_html .= '<option value="' . $imageKey . '" ' . $selected . ' >'.$imageVal.'</option>';
			} else {
				$product_image_html .= '<option value=' . $imageKey . '>' . $imageVal . '</option>';
			}
		}
		$product_image_html .= '</select>'; */
		return json_encode($imageArr);
	}
	//Download exhibition products excel
	public static function downloadExhibitionProductsExcel($exhibitionId) {
		if (!empty($exhibitionId)) {
			$exhibitionData = Exhibition::find($exhibitionId);
			if ($exhibitionData->count() > 0) {
				$priceMarkup = isset($exhibitionData->markup) ? $exhibitionData->markup : 0;
				$exhibitionProducts = ExhibitionProducts::where('exhibition_id', $exhibitionId)->get();
				$serialNumber = 0;
				$imageDirectory = config('constants.dir.website_url_for_product_image_curl');
				$defaultProductImage = $imageDirectory . 'def_1.png';
				foreach ($exhibitionProducts as $key => $products) {
					if (!isset($products->product_id)) {
						continue;
					}

					DB::setTablePrefix('');
					$productId = $products->product_id;

					$serialNumber++;
					$productCollection = DB::table("catalog_product_flat_1")->select("*")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
					$product = $productCollection;
					if (empty($productCollection)) {
						continue;
					}

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
					$inventoryStatus = $product->inventory_status_value;
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
					$metalData = InventoryHelper::getMetalData($productId);
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
							'Name' => ($index == 0) ? $productCollection->name : '',
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
				return \Excel::create('exhibition_products', function ($excel) use ($data) {
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
	}
	//Remove product from exhibition
	public static function removeFromExhibition($productId, $exhibitionId) {
		if (!empty($productId)) {
			$affectedRows = ExhibitionProducts::where('product_id', '=', $productId)->where(
				'exhibition_id', '=', $exhibitionId)->delete();
			$exhibitionProductsCount = ExhibitionProducts::where('exhibition_id', '=', $exhibitionId)->get()->count();
			Exhibition::where("id", $exhibitionId)->update(array('qty' => $exhibitionProductsCount));
		}
	}
	public static function getMetalData($productId) {
		if (App::environment('local')) {
			$get_metal = config('constants.apiurl.local.get_metal');
		} else if (App::environment('test')) {
			$get_metal = config('constants.apiurl.test.get_metal');
		} else {
			$get_metal = config('constants.apiurl.live.get_metal');
		}

		$postParam = 'product_id=' . $productId;
		$ch = curl_init($get_metal);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		//print_r($result);exit;
		if (!empty($result)) {
			return json_decode($result);
		}
	}
	//Get grand total by exhibition id
	public static function getExhibitionGrandTotal($exhibitionId) {
		if (!empty($exhibitionId)) {
			$productIds = DB::table('exhibition_products')->select(DB::raw('GROUP_CONCAT(product_id) AS product_ids'))->where('exhibition_id', '=', DB::raw("$exhibitionId"))->get()->first();
			$productIds = isset($productIds->product_ids) ? $productIds->product_ids : '';
			if (empty($productIds)) {
				return;
			}

			$productIds = explode(',', $productIds);
			//$productIds = implode("','",$productIds);

			DB::setTablePrefix('');
			$grandTotal = DB::table('catalog_product_flat_1')->select(DB::raw('sum(custom_price) as grand_total'))->whereIn('entity_id', $productIds)->get()->first();
			DB::setTablePrefix('dml_');
			return isset($grandTotal->grand_total) ? $grandTotal->grand_total : 0;
		}
	}
	//Get per gram labour charge
	public static function getPerGramLabourCharge($productId) {
		DB::setTablePrefix('');
		$labourCharge = DB::table('catalog_product_flat_1')->select('per_gm_rate')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
		$perGramLabourCharge = 0;
		if (isset($labourCharge->per_gm_rate) && !empty($labourCharge->per_gm_rate)) {
			$perGramLabourCharge = $labourCharge->per_gm_rate;
		} else {
			$metalData = DB::table('grp_metal')->select('metal_quality_id')->where('metal_product_id', '=', DB::raw("$productId"))->get()->first();
			$labourChargeData = DB::table('grp_metal_quality')->select('rate')->where('grp_metal_quality_id', '=', DB::raw("$metalData->metal_quality_id"))->get()->first();
			$perGramLabourCharge = isset($labourChargeData->rate) ? $labourChargeData->rate : 0;
		}
		DB::setTablePrefix('dml_');
		return $perGramLabourCharge;
	}
	//Get product attribute value
	public static function getProductAttribute($productId, $attributeCode) {
		if (!empty($productId) && !empty($attributeCode)) {
			$sql = "SELECT " . $attributeCode . " FROM catalog_product_flat_1 WHERE entity_id=" . DB::raw("$productId");
			$priceMarkup = DB::select($sql);
			return $priceMarkup[0]->$attributeCode;
		}
	}
	//Get invoice gst percentage
	public static function getInvoiceGst($invoiceId) {
		if (!empty($invoiceId)) {
			$gst = InvoiceProducts::select('sgst_percentage', 'cgst_percentage')->where('invoice_id', '=', $invoiceId)->first()->toArray();
			return $gst;
		}
	}
	//check return memo is generated
	public static function isReturnMemoGenerated($approvalNo) {
		$currentYear = date('y');
		//$approvalNo = $currentYear . '-' . ($currentYear + 1) . '/' . $approvalNo;
		DB::setTablePrefix('dml_');
		$returnMemoCount = ReturnMemo::select(DB::raw("count(1) as total_count"))->where('approval_memo_number', '=', DB::raw("'$approvalNo'"))->get()->first();
		DB::setTablePrefix('');
		if ($returnMemoCount->total_count > 0) {
			return true;
		} else {
			return false;
		}
	}
	public static function getWithGSTValue($value, $gstPercentage) {
		if (!empty($value)) {
			$value = (float) $value;
			//$gstPercentage = Setting::where('key', config('constants.settings.keys.igst_percentage'))->first('value');
			//$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			$withGstValue = $value + $gstValue;
			return $withGstValue;
		} else {
			return false;
		}
	}

	public static function getWithoutGSTValue($value, $gstPercentage) {
		if (!empty($value)) {
			$value = (float) $value;
			//$gstPercentage = Setting::where('key', config('constants.settings.keys.igst_percentage'))->first('value');
			//$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			$withoutGstValue = $value - $gstValue;
			return $withoutGstValue;
		} else {
			return false;
		}
	}

	public static function getGSTValue($value, $gstPercentage) {
		if (!empty($value)) {
			$value = (float) $value;
			//$gstPercentage = Setting::where('key', config('constants.settings.keys.igst_percentage'))->first('value');
			//$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			return $gstValue;
		} else {
			return false;
		}
	}
	public static function checkContactNumberValidation($customerId = null, $contactNumber) {
		if (!empty($customerId)) {
			$sql = "SELECT COUNT(1) AS total_customer FROM customer_entity AS c INNER JOIN customer_address_entity AS a ON a.parent_id = c.entity_id LEFT JOIN customer_address_entity_varchar AS addr_telephone ON a.entity_id = addr_telephone.entity_id AND addr_telephone.attribute_id =( SELECT attribute_id FROM eav_attribute AS eav WHERE eav.attribute_code = 'telephone' AND eav.entity_type_id = 2 ) WHERE addr_telephone.value =" . $contactNumber . " and c.entity_id!=" . $customerId;
		} else {

			$sql = "SELECT COUNT(1) AS total_customer FROM customer_entity AS c INNER JOIN customer_address_entity AS a ON a.parent_id = c.entity_id LEFT JOIN customer_address_entity_varchar AS addr_telephone ON a.entity_id = addr_telephone.entity_id AND addr_telephone.attribute_id =( SELECT attribute_id FROM eav_attribute AS eav WHERE eav.attribute_code = 'telephone' AND eav.entity_type_id = 2 ) WHERE addr_telephone.value =" . $contactNumber;
		}

		$contactNumber = DB::select($sql);
		if (isset($contactNumber[0]->total_customer) && $contactNumber[0]->total_customer > 0) {
			return true;
		} else {
			return false;
		}
	}
	//Get total products count with null status
	public static function getTotalInventoryProductWithoutStatus() {
		$inventoryStatusOption = DB::select("SELECT EAOV.value,EA.attribute_id FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0");

		$inventory_status = '';
		$attributeId = '';
		foreach ($inventoryStatusOption as $key => $value) {
			$inventory_status = strtolower($value->value);
			$attributeId = $value->attribute_id;
		}
		//$totalCount = DB::select("select count(*) as total_product from qrcode_inventory_management qim left join catalog_product_entity_int cf1 on cf1.entity_id = qim.pr_id where cf1.attribute_id=" . $attributeId . " AND cf1.value = " . $status);
		if (App::environment('local')) {
			$our_categories_exp = config('constants.fixIds.local.category_ids');
		} else {
			$our_categories_exp = config('constants.fixIds.live.category_ids');
		}

		$our_categories_for_query = implode("','", $our_categories_exp);
		//echo "select count(*) as total_product from catalog_product_flat_1 as ce JOIN catalog_category_product ON catalog_category_product.product_id = ce.entity_id WHERE ce.inventory_status IS NULL AND ce.status=1 AND ce.isreadytoship=1 AND ce.type_id='simple' AND ce.custom_price!=0 AND ce.custom_price!='' AND category_id IN('" . $our_categories_for_query . "')";exit;
		$totalCount = DB::select("SELECT DISTINCT COUNT(*) AS total_product FROM `catalog_product_flat_1` AS `e` INNER JOIN `catalog_category_product` ON `e`.`entity_id` = `catalog_category_product`.`product_id` RIGHT JOIN `qrcode_inventory_management` AS `inventory_management` ON `e`.`entity_id` = `inventory_management`.`pr_id` WHERE e.inventory_status IS NULL AND `e`.`status` = 1 AND `e`.`isreadytoship` = 1 AND `e`.`type_id` = 'simple' AND `e`.`custom_price` != 0 AND `e`.`custom_price` != '' AND `category_id` IN( '" . $our_categories_for_query . "' )");
		return isset($totalCount[0]->total_product) ? $totalCount[0]->total_product : '';
	}
	//Generate cash voucher by id
	public static function generateCashVoucher($voucherId) {
		if (!empty($voucherId)) {
			$voucherData = CashVoucher::find($voucherId);
			$customPaper = array(0, 0, 500, 800);
			/*$view = view('inventory/generatecashvoucher')->with(array('voucherData'=>$voucherData));
			echo $view->render();exit;*/
			$pdf = PDF::loadView('inventory/generatecashvoucher', array('voucherData' => $voucherData))->setPaper($customPaper, 'A4');
			return $pdf->download('CashVoucher.pdf');
		}
	}
	//Get invoice increment id by invoice entity_id
	public static function getInvoiceNumber($invoiceId) {
		if (!empty($invoiceId)) {
			DB::setTablePrefix('');
			$invoice = DB::table('sales_flat_invoice')->select('increment_id')->where('entity_id', '=', DB::raw("$invoiceId"))->get()->first();
			DB::setTablePrefix('dml_');
			return isset($invoice->increment_id) ? $invoice->increment_id : '';
		}
	}
	//Get invoice entity_id by invoice number
	public static function getInvoiceEntityId($invoiceNumber) {
		if (!empty($invoiceNumber)) {
			DB::setTablePrefix('');
			$invoice = DB::table('sales_flat_invoice')->select('entity_id')->where('increment_id', '=', DB::raw("'$invoiceNumber'"))->get()->first();
			DB::setTablePrefix('dml_');
			return isset($invoice->entity_id) ? $invoice->entity_id : '';
		}
	}
	//Check cash voucher generated or not
	public static function isCashVoucherGenerated($invoiceId) {
		if (!empty($invoiceId)) {
			$voucherData = CashVoucher::where('invoice_id', '=', DB::raw("$invoiceId"))->select('status')->get()->first();
			if (isset($voucherData->status) & !empty($voucherData->status)) {
				return true;
			} else {
				return false;
			}
		}
	}
	//Check cash voucher is generated for invoice
	public static function isCashVoucherExist($invoiceId) {
		if (!empty($invoiceId)) {
			$voucherData = CashVoucher::where('invoice_id', '=', DB::raw("$invoiceId"))->get()->count();
			if ($voucherData > 0) {
				return true;
			} else {
				return false;
			}
		}
	}
	//Get metal quality text by id
	public static function getMetalQualityLabel($metalQualityId) {
		if (!empty($metalQualityId)) {
			DB::setTablePrefix('');
			$metalQuality = DB::table('grp_metal_quality')->select('metal_quality')->where('metal_quality_id', '=', DB::raw("$metalQualityId"))->get()->first();
			DB::setTablePrefix('dml_');
			return isset($metalQuality->metal_quality) ? $metalQuality->metal_quality : '-';
		}
	}
	public static function checkFRNCodeValidation($customerId = null, $FrnCode) {
		if (!empty($customerId)) {
			$sql = "SELECT COUNT(1) AS total_customer FROM customer_entity AS c LEFT JOIN customer_entity_varchar AS addr ON c.entity_id = addr.entity_id AND addr.attribute_id =( SELECT attribute_id FROM eav_attribute AS eav WHERE eav.attribute_code = 'frn_code') WHERE addr.value ='" . $FrnCode . "' and c.entity_id!=" . $customerId;
		} else {

			$sql = "SELECT COUNT(1) AS total_customer FROM customer_entity AS c LEFT JOIN customer_entity_varchar AS addr ON c.entity_id = addr.entity_id AND addr.attribute_id =( SELECT attribute_id FROM eav_attribute AS eav WHERE eav.attribute_code = 'frn_code') WHERE addr.value ='" . $FrnCode . "'";

			//echo $sql;
		}

		$FrnCode = DB::select($sql);
		if (isset($FrnCode[0]->total_customer) && $FrnCode[0]->total_customer > 0) {
			return true;
		} else {
			return false;
		}
	}
}