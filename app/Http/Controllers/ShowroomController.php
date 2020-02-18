<?php

namespace App\Http\Controllers;

use App;
use App\ApprovalMemoHistroy;
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
use App\Http\Controllers\Controller;
use App\Productupload;
use App\QrcodeScanning;
use App\SalesReturn;
use App\SalesReturnProducts;
use App\Setting;
use App\ShowroomOrderProducts;
use App\ShowroomOrders;
use Auth;
use Config;
use Excel;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDF;
use PHPExcel_Worksheet_Drawing;
use Session;
use URL;

class ShowroomController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	function __construct() {
		$this->middleware('permission:showroom-inventory');
		$this->middleware('permission:approval-inventory');
		$this->middleware('permission:sold-inventory');
		$this->middleware('permission:showroom-allstock');
		$this->middleware('permission:showroom-salesreturnlist');
	}

	//For RTS product listing
	public function index(Request $request) {
		///$request->session()->flush();
		$productCollection = ShowroomHelper::getProducts();

		return view('showroom.index')->with('productCollection', $productCollection);
	}

	public function getFilteredShowroomCollection($params) {

		DB::setTablePrefix('');

		$prod = ShowroomHelper::getAllProductsCollection();

		if (isset($params['category'])) {
			$prod = $prod->whereIn('category_id', $params['category']);
		}

		if (isset($params['diamondtype'])) {

			if ($params['diamondtype'] == "1") {
				$prod = $prod->where('roundshape', "1");
			} else if ($params['diamondtype'] == "3") {
				$prod = $prod->where('roundshape', "0");
			} else {
				$fancyProds = $prod->where('roundshape', "0");
				$fancyProdsEntities = $fancyProds->pluck(['entity_id']);
				$roundProds = $prod->where('roundshape', "1");
				$roundProdsEntities = $roundProds->pluck(['entity_id']);
				$concatenated = $fancyProdsEntities->concat($roundProdsEntities);
				$prod = $prod->whereNotIn('entity_id', $concatenated);
			}

		}

		if ((!empty($params['price_start'])) && (!empty($params['price_to']))) {
			$prod = $prod->whereBetween('custom_price', [$params['price_start'], $params['price_to']]);
		}

		if (isset($params['criteria'])) {

			if ($params['criteria'] == "1") {
				$prod = $prod->filter(function ($value, $key) {

					$ratio14k = (float) $value->ratio14k;
					$ratio18k = (float) $value->ratio18k;

					if ($ratio14k <= 10 && $ratio18k <= 10) {
						return $value;
					}
				});
			} elseif ($params['criteria'] == "2") {

				$prod = $prod->filter(function ($value, $key) {

					$ratio14k = (float) $value->ratio14k;
					$ratio18k = (float) $value->ratio18k;

					if ($ratio14k <= 10 && $ratio18k > 10) {
						return $value;
					}
				});
			} elseif ($params['criteria'] == "3") {

				$prod = $prod->filter(function ($value, $key) {

					$ratio14k = (float) $value->ratio14k;
					$ratio18k = (float) $value->ratio18k;

					if ($ratio14k > 10 && $ratio18k <= 10) {
						return $value;
					}
				});
			} else {

				$prod = $prod->filter(function ($value, $key) {

					$ratio14k = (float) $value->ratio14k;
					$ratio18k = (float) $value->ratio18k;

					if ($ratio14k > 10 && $ratio18k > 10) {
						return $value;
					}
				});
			}
		}

		if (isset($params['stockstatus'])) {
			if ($params['stockstatus'] == '1') {
				$prod = $prod->where('stockstatus', 'DML INSTOCK');
			} else if ($params['stockstatus'] == '2') {
				$prod = $prod->where('stockstatus', 'DML SOLD');
			} else if ($params['stockstatus'] == '3') {
				$prod = $prod->where('stockstatus', 'FRANCHISE INSTOCK');
			} else {
				$prod = $prod->where('stockstatus', 'FRANCHISE SOLD');
			}
		}

		if (isset($params['gold_purity'])) {
			$prod = $prod->whereIn('metal_quality_id', $params['gold_purity']);
		}

		if (isset($params['diamond_quality'])) {
			//$prod = $prod->whereIn('qualities', $params['gold_purity']);
			$prod = $prod->filter(function ($value, $key) use ($params) {

				if (!empty($value->qualities)) {
					$qualities = explode(',', $value->qualities);

					foreach ($qualities as $quality) {
						if (in_array($quality, $params['diamond_quality'])) {
							return $value;
						}
					}
				} else {

					//var_dump($value->rts_stone_quality);
					//var_dump($params['diamond_quality']);exit;
					foreach ($params['diamond_quality'] as $qlty) {
						if ($value->rts_stone_quality == $qlty) {
							return $value;
						}
					}
				}
			});

		}

		//dd($prod->toArray());exit;

		return $prod;

		DB::setTablePrefix('');
		$prod = DB::table('catalog_product_flat_1 as e');
		$prod->select('e.entity_id', 'e.rts_stone_quality', 'e.belt_price', 'e.type_id', 'e.attribute_set_id', 'e.name', 'e.entity_id', 'e.sku', 'e.rts_bangle_size', 'e.rts_bracelet_size', 'e.rts_ring_size', 'e.attribute_set_id', 'e.certificate_no', 'e.type_id', 'e.custom_price', 'e.metal_quality', 'e.metal_quality_value', 'e.rts_stone_quality', 'fo.franchises_id', 'fo.type', 'fo.product_id', DB::raw("CASE WHEN (IFNULL(fo.product_id, 'DML') AND fo.type = '3') THEN 'FRANCHISE SOLD' WHEN (IFNULL(fo.product_id, 'DML') AND fo.type = '0') THEN 'FRANCHISE INSTOCK' WHEN (fo.product_id = '' AND e.is_sold = 1) THEN 'DML SOLD' ELSE 'DML INSTOCK' END AS stockstatus"), 'catalog_category_product.category_id'); // , 'e.extra_price'
		$prod->leftJoin('franchises_order as fo', 'e.entity_id', '=', 'fo.product_id');
		//if not selected any category
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
		if (isset($params['diamondtype'])) {
			$prod->addSelect('e.original_sku', 'diamond_type.*');
			if ($params['diamondtype'] == "1") {
				$prod->rightJoin(DB::raw("(SELECT e.*,GROUP_CONCAT(DISTINCT grp_stone.stone_shape) AS roundshape FROM catalog_product_flat_1 AS e LEFT JOIN franchises_order AS fo ON e.entity_id = fo.product_id LEFT JOIN grp_stone ON e.entity_id = grp_stone.stone_product_id WHERE (e.status = 1) AND(e.isreadytoship = 1) AND(e.type_id = 'simple') GROUP BY e.entity_id) AS diamond_type"),
					function ($join) {
						$join->on('diamond_type.entity_id', '=', 'e.entity_id');
					});
				$prod->where('roundshape', '=', DB::raw("'36'"));
			} else if ($params['diamondtype'] == "3") {
				$prod->rightJoin(DB::raw("(SELECT e.*, FIND_IN_SET('36', GROUP_CONCAT(DISTINCT grp_stone.stone_shape)) AS `roundshape` FROM `catalog_product_flat_1` AS `e` LEFT JOIN `franchises_order` AS `fo` ON e.entity_id = fo.product_id LEFT JOIN `grp_stone` ON e.entity_id = grp_stone.stone_product_id WHERE (e.status = 1) AND (e.isreadytoship = 1) AND (e.type_id = 'simple') GROUP BY `e`.`entity_id`) AS `diamond_type`"),
					function ($join) {
						$join->on('diamond_type.entity_id', '=', 'e.entity_id');
					});
				$prod->where('roundshape', '=', DB::raw("'0'"));
			} else {
				$prod->rightJoin(DB::raw("(SELECT e.*, GROUP_CONCAT(DISTINCT grp_stone.stone_shape) as shapes, FIND_IN_SET('36', GROUP_CONCAT(DISTINCT grp_stone.stone_shape)) AS roundshape FROM catalog_product_flat_1 AS e LEFT JOIN franchises_order AS fo ON e.entity_id = fo.product_id LEFT JOIN grp_stone ON e.entity_id = grp_stone.stone_product_id WHERE (e.status = 1) AND (e.isreadytoship = 1) AND (e.type_id = 'simple') GROUP BY e.entity_id) AS diamond_type"),
					function ($join) {
						$join->on('diamond_type.entity_id', '=', 'e.entity_id');
					});
				$prod->where('shapes', '!=', DB::raw("'36'"));
				$prod->where('roundshape', '!=', DB::raw("'0'"));
			}
		}
		//For criteria filter
		$criteriaJoinQuery = '';
		$criteriaWhereQuery = '';
		if (isset($params['criteria'])) {
			$prod->rightJoin(DB::raw("(SELECT e.*, IF(metal.metal_quality_id = '2879' OR metal.metal_quality_id = '2878' OR metal.metal_quality_id = '2880' OR metal.metal_quality_id = '3170' OR metal.metal_quality_id = '3120' OR metal.metal_quality_id = '2900' OR metal.metal_quality_id = '3091', ((metal.metal_weight*85)/100), metal.metal_weight)/sum(cast(stone.total_carat AS decimal(10,2))) AS `ratio14k`, IF(metal.metal_quality_id = '2877' OR metal.metal_quality_id = '2875' OR metal.metal_quality_id = '2901' OR metal.metal_quality_id = '3119' OR metal.metal_quality_id = '2876', ((metal.metal_weight*100)/85), metal.metal_weight)/sum(cast(stone.total_carat AS decimal(10,2))) AS `ratio18k` FROM `catalog_product_flat_1` AS `e` LEFT JOIN `franchises_order` AS `fo` ON e.entity_id = fo.product_id LEFT JOIN `grp_metal` AS `metal` ON e.entity_id = metal.metal_product_id LEFT JOIN `grp_stone` AS `stone` ON e.entity_id = stone.stone_product_id WHERE (e.status = 1) AND (e.isreadytoship = 1) AND (e.type_id = 'simple') GROUP BY `stone`.`stone_product_id`) AS `criteria_t`"),
				function ($join) {
					$join->on('criteria_t.entity_id', '=', 'e.entity_id');
				});
			if ($params['criteria'] == "1") {
				$prod->where('ratio14k', '<=', DB::raw("'10'"));
				$prod->where('ratio18k', '<=', DB::raw("'10'"));
			} elseif ($params['criteria'] == "2") {
				$prod->where('ratio14k', '<=', DB::raw("'10'"));
				$prod->where('ratio18k', '>', DB::raw("'10'"));
			} elseif ($params['criteria'] == "3") {
				$prod->where('ratio14k', '>', DB::raw("'10'"));
				$prod->where('ratio18k', '<=', DB::raw("'10'"));
			} else {
				$prod->where('ratio14k', '>', DB::raw("'10'"));
				$prod->where('ratio18k', '>', DB::raw("'10'"));
			}
		}
		//For stock status filter
		$stockstatusQueryColumn = "";
		$stockstatusJoinQuery = "";
		$stockstatusWhereQuery = "";
		if (isset($params['stockstatus'])) {
			$prod->rightJoin(DB::raw("(SELECT e.*,CASE WHEN(IFNULL(fo.product_id, 'DML') AND fo.type = '3') THEN 'FRANCHISE SOLD' WHEN(IFNULL(fo.product_id, 'DML') AND fo.type = '0') THEN 'FRANCHISE INSTOCK' WHEN(fo.product_id = '' AND e.is_sold = 1) THEN 'DML SOLD' ELSE 'DML INSTOCK' END AS stockstatus FROM catalog_product_flat_1 AS e LEFT JOIN franchises_order AS fo ON e.entity_id = fo.product_id WHERE (e.status = 1) AND(e.isreadytoship = 1) AND(e.type_id = 'simple')) AS t"),
				function ($join) {
					$join->on('t.entity_id', '=', 'e.entity_id');
				});
			if ($params['stockstatus'] == '1') {
				$prod->where('stockstatus', '=', DB::raw("'DML INSTOCK'"));
			} else if ($params['stockstatus'] == '2') {
				$prod->where('stockstatus', '=', DB::raw("'DML SOLD'"));
			} else if ($params['stockstatus'] == '3') {
				$prod->where('stockstatus', '=', DB::raw("'FRANCHISE INSTOCK'"));
			} else {
				$prod->where('stockstatus', '=', DB::raw("'FRANCHISE SOLD'"));
			}
		}
		$priceStart = '';
		$priceTo = '';

		//For price filter
		if (($params['price_start'] != '') && ($params['price_to'] != '')) {
			$priceStart = $params['price_start'];
			$priceTo = $params['price_to'];
		}
		//For search by certificate/sku
		if (!empty($searchValue)) {
			$prod->where('e.sku', 'like', DB::raw("'%$searchValue%'"));
			$prod->orWhere('e.certificate_no', 'like', DB::raw("'%$searchValue%'"));}
		//For price filter
		if ((isset($priceStart)) && (isset($priceTo))) {
			$prod->where('e.custom_price', '>=', DB::raw("$priceStart"));
			$prod->where('e.custom_price', '<=', DB::raw("$priceTo"));
		}
		//For gold purity filter
		if (isset($params['gold_purity'])) {
			$gold_purity = implode("','", $params['gold_purity']);
			$prod->whereIn('e.metal_quality', [DB::raw("'" . $gold_purity . "'")]);
		}
		//For diamond quality filter
		if (isset($params['diamond_quality'])) {
			$diamond_quality = implode("','", $params['diamond_quality']);
			$prod->whereIn('e.rts_stone_quality', [DB::raw("'" . $diamond_quality . "'")]);
		}
		$prod->orderBy('e.entity_id', 'desc');

	}

	//For server side datatable
	public function ajaxlist(Request $request) {
		$data = array();
		$params = $request->post();

		$prod = $this->getFilteredShowroomCollection($params);

		/*echo "<pre>";
		print_r($prod);exit;*/
		//$our_categories = "14,287,6,7,8,9,124,289,290,195,43,293,165";
		//$our_categories_exp = explode(',', $our_categories);
		//print_r(implode("','", $our_categories_exp));exit;
		//$our_categories_for_query = '';
		//$our_categories_for_query = implode("','", $our_categories_exp);
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
		$stalen = $start / $length;
		$curpage = $stalen + 1;
		$where = '';
		$offset = '';
		$limit = '';
		$order = '';

		//$collection = collect($prod->get());
		$productCount = $prod->count();
		$min_price = $prod->pluck(['custom_price'])->min();
		$max_price = $prod->pluck(['custom_price'])->max();
		//$productCollection = $prod->take($length)->offset($curpage)->get();
		$productCollection = $prod->forPage($curpage, $length);
		//$productCollection = $collection->take($length)->offset($curpage);
		DB::setTablePrefix('dml_');
		$data["draw"] = $params['draw'];
		$data["recordsTotal"] = $productCount;
		$data["recordsFiltered"] = $productCount;
		$data['deferLoading'] = $productCount;
		$data['min_price'] = $min_price;
		$data['max_price'] = $max_price;
		$imageDirectory = config('constants.dir.website_url_for_product_image');
		$defaultProductImage = $imageDirectory . 'def_1.png';
		if (count($productCollection) > 0) {
			foreach ($productCollection as $key => $product) {
				//$productData = ShowroomHelper::getProductData($product->entity_id);
				$checkbox = '<label><input type="checkbox" value="' . $product->entity_id . '" data-id="' . $product->entity_id . '" id="chk_product_' . $product->entity_id . '" class="form-check-input chkProduct"><span class="label-text"></span></label>';
				$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				$productImage = '<img alt="' . $product->sku . '" class="product-img" src="' . (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage) . '">';
				$sku = $product->sku;
				$orgskuarr = explode(' ', $product->sku);
				$orgsku = '';
				$orgsku2 = '';
				$orgsku = isset($orgskuarr[0]) ? $orgskuarr[0] : '';
				if (isset($orgskuarr[0]) && isset($orgskuarr[1])) {
					$orgsku2 = $orgskuarr[0] . ' ' . $orgskuarr[1];
				}

				$qty = ShowroomHelper::getTotalQtyBySku($orgsku);
				$qtyWithQuality = ShowroomHelper::getTotalQtyBySku($orgsku2);
				$certificateNo = (!empty($product->certificate_no) ? $product->certificate_no : 'N/A');
				$productSize = 'N/A';
				if ($product->attribute_set_id == '14') {
					$productSize = $product->rts_ring_size;
				} elseif ($product->attribute_set_id == '17') {
					$productSize = $product->rts_bangle_size;
				} elseif ($product->attribute_set_id == '23') {
					$productSize = $product->rts_bracelet_size;
				}

				$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);

				$categoryName = $categoryNames[0]->category_name;
				$metalData = ShowroomHelper::getMetalData($product->entity_id, $product);
				$metalColor = '';
				$metalGross = '';
				$metalQuality = '';
				$metalQuality = isset($metalData['quality']) ? $metalData['quality'] : '';

				$metalColorArray = array();
				if (!empty($metalQuality)) {
					$metalColorArray = explode(' ', $metalQuality);
					if (isset($metalColorArray[1]) && isset($metalColorArray[2])) {
						$metalColor = $metalColorArray[1] . ' ' . $metalColorArray[2];
					}

					$metalGross = $metalData['weight'];
					$metalQuality = isset($metalColorArray[0]) ? $metalColorArray[0] : '';
				} else {
					$metalColor = 'N/A';
					$metalGross = 'N/A';
					$metalQuality = 'N/A';
				}

				$kt_14_wt = 0;
				$kt_18_wt = 0;
				if ($metalQuality == '18K') {
					$kt_14_wt = ($metalGross * 85) / 100;
					$kt_18_wt = $metalGross;
				} elseif ($metalQuality == '14K') {
					$kt_14_wt = $metalGross;
					$kt_18_wt = ($metalGross * 100) / 85;
				}
				$stone = $product->rts_stone_quality;
				$stoneData = ShowroomHelper::getSideStoneData($product->entity_id, $stone);
				$totalStone = isset($stoneData['stoneclarity']) ? count($stoneData['stoneclarity']) : 0;
				$diamondQualities = '';
				$roundCount = 0;
				$fancyCount = 0;
				for ($stoneIndex = 0; $stoneIndex < $totalStone; $stoneIndex++) {
					if ($stoneIndex == ($totalStone - 1)) {
						$diamondQualities .= $stoneData['stoneclarity'][$stoneIndex];
					} else {
						$diamondQualities .= $stoneData['stoneclarity'][$stoneIndex] . ' | ';
					}
					if ($stoneData['shape'][$stoneIndex] == 'ROUND') {
						$roundCount++;
					} else {
						$fancyCount++;
					}
				}

				if (empty($diamondQualities)) {
					$diamondQualities = 'N/A';
				}
				if ($roundCount == $totalStone) {
					$diamondType = 'ROUND';
				} elseif ($fancyCount == $totalStone) {
					$diamondType = 'FANCY';
				} else {
					$diamondType = 'FANCY WITH ROUND';
				}
				$diamondTotalWeight = isset($stoneData['totalweight'][0]) ? $stoneData['totalweight'][0] : 0;
				if (empty($diamondTotalWeight)) {
					$diamondTotalWeight = 'N/A';
				}
				$isSold = $product->stockstatus;

				/*if (is_numeric($kt_18_wt) && is_numeric($diamondTotalWeight)) {
						$ratio_in_18K = $kt_18_wt / (float) $diamondTotalWeight;
						$ratio_in_14K = $kt_14_wt / (float) $diamondTotalWeight;
					} else {
						$ratio_in_18K = '';
						$ratio_in_14K = '';
				*/

				/*if (is_numeric($kt_18_wt) && is_numeric($diamondTotalWeight)) {
						$ratio_in_18K_real = $kt_18_wt / $diamondTotalWeight;
					}
					if (is_numeric($kt_14_wt) && is_numeric($diamondTotalWeight)) {
						$ratio_in_14K_real = $kt_14_wt / $diamondTotalWeight;
				*/

				$ratio_in_14K = $product->ratio14k;
				$ratio_in_18K = $product->ratio18k;

				if ($ratio_in_18K <= 10 && $ratio_in_14K <= 10) {
					$remarks = 'OK in both';
				} elseif ($ratio_in_18K <= 10 && $ratio_in_14K > 10) {
					$remarks = 'OK in 18K';
				} elseif ($ratio_in_18K > 10 && $ratio_in_14K <= 10) {
					$remarks = 'OK in 14K';
				} else if ($ratio_in_18K > 10 && $ratio_in_14K > 10) {
					$remarks = 'Not in criteria';
				} else {
					$remarks = 'NA';
				}

				$customPrice = ShowroomHelper::currencyFormat(round($product->custom_price));
				$data['data'][] = array($checkbox, $productImage, $certificateNo, $sku, $qty, $qtyWithQuality, $productSize, $categoryName, $metalColor, $metalGross, $metalQuality, round($kt_18_wt, 2), round($kt_14_wt, 2), $diamondQualities, round($diamondTotalWeight, 2), $customPrice, $isSold, $diamondType, round($ratio_in_18K, 2), round($ratio_in_14K, 2), $remarks);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	public function storeProductIds(Request $request) {
		$params = $request->post();
		$productIds = isset($params['chkProductIds']) ? $params['chkProductIds'] : '';
		if (!empty($productIds)) {
			Session::put('export_productids', $productIds);
			Session::save();
			$response['status'] = true;
		} else {
			$response['status'] = false;
		}
		echo json_encode($response);exit;
	}
	//to export product csv
	public function exportcsv(Request $request) {
		$params = $request->post();
		$imageDirectory = config('constants.dir.website_url_for_product_image_curl');
		$defaultProductImage = $imageDirectory . 'def_1.png';
		$productIds = Session::get('export_productids');

		if (count($productIds) > 0) {
			$productIds = implode("','", $productIds);
			DB::setTablePrefix('');
			$productCollection = DB::table('catalog_product_flat_1 as e')->whereIn('e.entity_id', [DB::raw("'" . $productIds . "'")])->get();
			DB::setTablePrefix('dml_');
			$data = array();
			foreach ($productCollection as $key => $product) {
				//echo \URL::to('/');exit;
				$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				//echo $product_image;exit;
				$productImage = (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage);
				//$imageData = ShowroomHelper::file_get_contents_curl($productImage);
				$ext = pathinfo($productImage, PATHINFO_EXTENSION);
				if (!file_exists(public_path('img/product'))) {
					mkdir(public_path('img/product'), 0777, true);
				}
				$file = 'img/product/product' . $product->entity_id . '.' . $ext;
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
				//file_put_contents($file, $imageData);

				$sku = $product->sku;
				$certificateNo = (!empty($product->certificate_no) ? $product->certificate_no : 'N/A');
				$product_size = 'N/A';
				if ($product->attribute_set_id == '14') {
					$product_size = $product->rts_ring_size;
				} elseif ($product->attribute_set_id == '17') {
					$product_size = $product->rts_bangle_size;
				} elseif ($product->attribute_set_id == '23') {
					$product_size = $product->rts_bracelet_size;
				}
				$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
				$categoryName = $categoryNames[0]->category_name;
				$metalData = ShowroomHelper::getMetalData($product->entity_id);
				$metalQuality = $metalData['quality'];
				if (!empty($metalQuality)) {
					$metalColorArray = explode(' ', $metalQuality);
					$metalColor = $metalColorArray[1] . ' ' . $metalColorArray[2];
					$metalGross = $metalData['weight'];
					$metalQuality = $metalColorArray[0];
				} else {
					$metalColor = 'N/A';
					$metalGross = 'N/A';
					$metalQuality = 'N/A';
				}
				$kt_14_wt = 'N/A';
				$kt_18_wt = 'N/A';
				if ($metalQuality == '18K') {
					$kt_14_wt = ($metalGross * 85) / 100;
					$kt_18_wt = $metalGross;
				} elseif ($metalQuality == '14K') {
					$kt_14_wt = $metalGross;
					$kt_18_wt = ($metalGross * 100) / 85;
				}
				$stone = isset($product->rts_stone_quality) ? $product->rts_stone_quality : '';
				$stoneData = ShowroomHelper::getSideStoneData($product->entity_id, $stone);
				$totalStone = count($stoneData['stoneclarity']);
				$diamondQualities = '';
				$roundCount = 0;
				$fancyCount = 0;
				for ($stoneIndex = 0; $stoneIndex < $totalStone; $stoneIndex++) {
					if ($stoneIndex == ($totalStone - 1)) {
						$diamondQualities .= $stoneData['stoneclarity'][$stoneIndex];
					} else {
						$diamondQualities .= $stoneData['stoneclarity'][$stoneIndex] . ' | ';
					}
					if ($stoneData['shape'][$stoneIndex] == 'ROUND') {
						$roundCount++;
					} else {
						$fancyCount++;
					}
				}
				if (empty($diamondQualities)) {
					$diamondQualities = 'N/A';
				}
				if ($roundCount == $totalStone) {
					$diamondType = 'ROUND';
				} elseif ($fancyCount == $totalStone) {
					$diamondType = 'FANCY';
				} else {
					$diamondType = 'FANCY WITH ROUND';
				}
				$diamondTotalWeight = $stoneData['totalweight'][0];
				if (empty($diamondTotalWeight)) {
					$diamondTotalWeight = 'N/A';
				}
				$isSold = isset($product->is_sold) ? $product->is_sold : '';
				$ratio_in_18K = 'N/A';
				$ratio_in_14K = 'N/A';
				if (is_numeric($kt_18_wt)) {
					$ratio_in_18K = $kt_18_wt / $diamondTotalWeight;
				}

				if (is_numeric($kt_14_wt)) {
					$ratio_in_14K = $kt_14_wt / $diamondTotalWeight;
				}

				if ($ratio_in_18K <= 10 && $ratio_in_14K <= 10) {
					$remarks = 'OK in both';
				} elseif ($ratio_in_18K <= 10 && $ratio_in_14K > 10) {
					$remarks = 'OK in 18K';
				} elseif ($ratio_in_18K > 10 && $ratio_in_14K <= 10) {
					$remarks = 'OK in 14K';
				} elseif ($ratio_in_18K > 10 && $ratio_in_14K > 10) {
					$remarks = 'Not in criteria';
				} else {
					$remarks = 'N/A';
				}
				$customPrice = isset($product->custom_price) ? $product->custom_price : '';
				$data[] = array(
					'Image' => $file,
					'Certificate No' => $certificateNo,
					'SKU' => $sku,
					'Size' => $product_size,
					'Category' => $categoryName,
					'Metal Color' => $metalColor,
					'Metal Gross Wt' => $metalGross,
					'Metal Quality' => $metalQuality,
					'18KT' => round($kt_18_wt, 2),
					'14KT' => round($kt_14_wt, 2),
					'DIAMOND QUALITY' => $diamondQualities,
					'TOTAL DIAMOND WT' => round($diamondTotalWeight, 2),
					'FINAL PRICE' => $customPrice,
					'SELLING STATUS' => $isSold,
					'PRO_TYPE' => $diamondType,
					'RATIO IN 18K' => round($ratio_in_18K, 2),
					'RATIO IN 14K' => round($ratio_in_14K, 2),
					'REMARKS' => $remarks,
				);
			}
		}
		DB::setTablePrefix('dml_');
		Session::forget('export_productids');
		$row = 0;
		return \Excel::create('showroom', function ($excel) use ($data) {
			$excel->sheet('Sheet', function ($sheet) use ($data) {
				foreach ($data as $row => $columns) {
					foreach ($columns as $column => $value) {
						if (strpos($value, 'img/') !== false) {
							$objDrawing = new PHPExcel_Worksheet_Drawing();
							$objDrawing->setName('showroom_img');
							$objDrawing->setDescription('showroom_img');
							$objDrawing->setPath($value);
							$rowNo = (int) $row + 2;
							$objDrawing->setCoordinates('A' . $rowNo);
							$objDrawing->setOffsetX(5);
							$objDrawing->setOffsetY(5);
							$objDrawing->setWidth(80);
							$objDrawing->setHeight(80);
							$objDrawing->setWorksheet($sheet);
							//$sheet->setSize('A1', 50);
							//$sheet->setWidth('A', 0.5);
							$sheet->setSize(array(
								'A1' . $rowNo => array(
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

	}

	public function processorder(Request $request) {

		$params = $request->post();
		$post_products = json_decode($params['post_product_data']);
		//retrive session product ids

		$sessionProductIds = array();
		$sessionProductIds = session()->get('showroom_processed_products');
		if (!empty($sessionProductIds)) {
			$post_products = array_merge($sessionProductIds, $post_products);
			$post_products = array_unique($post_products);
		}
		session()->put('showroom_processed_products', $post_products);
		session()->save();
		return view('showroom.processorder')->with('post_products', $post_products);
	}
	//remove processed product from session
	public function removeProcessedProductFromSession(Request $request) {
		$params = $request->post();
		$productId = isset($params['product_id']) ? $params['product_id'] : '';
		if (!empty($productId)) {
			$sessionProductIds = $request->session()->get('showroom_processed_products');

			if (!empty($sessionProductIds)) {
				if (($key = array_search($productId, $sessionProductIds)) !== false) {
					unset($sessionProductIds[$key]);
					session()->forget('showroom_processed_products');
					session()->save();
					session()->put('showroom_processed_products', $sessionProductIds);
					session()->save();
					//print_r(Session::get('showroom_processed_products_'.$userId));exit;
					$response['status'] = true;
				}
			}
		} else {
			$response['status'] = false;
			$response['message'] = config('constants.message.inventory_default_failure_message');
		}
		echo json_encode($response);exit;
	}
	public function placeorder(Request $request) {
		$postData = $request->post();
		$user = Auth::user();
		/*echo '<pre>';
		print_r($postData);exit;*/
		if (!empty($postData)) {

			$order_total = 0;
			$total_qty = 0;
			$showroom_orders_products_data = array();
			//$showroom_orders_products_data = new ShowroomOrders;
			foreach ($postData['product_id'] as $postKey => $postValue) {
				$order_total += $postData['product_total'][$postKey];
				$productId = (int) $postData['product_id'][$postKey];
				$showroom_orders_products_data[$postKey]['product_id'] = (int) $postData['product_id'][$postKey];
				$showroom_orders_products_data[$postKey]['sku'] = $postData['sku'][$postKey];
				$showroom_orders_products_data[$postKey]['certificate'] = $postData['certificate'][$postKey];
				$showroom_orders_products_data[$postKey]['qty'] = (int) $postData['qty'][$postKey];
				$total_qty += (int) $postData['qty'][$postKey];
				$showroom_orders_products_data[$postKey]['metal_quality'] = (int) $postData['metal_quality'][$postKey];
				$showroom_orders_products_data[$postKey]['metal_weight'] = (float) $postData['metal_weight'][$postKey];
				$showroom_orders_products_data[$postKey]['diamond_quality'] = $postData['diamond_quality'][$postKey];
				$showroom_orders_products_data[$postKey]['diamond_weight'] = (float) $postData['diamond_weight'][$postKey];
				$showroom_orders_products_data[$postKey]['product_price'] = (float) $postData['product_price'][$postKey];
				$showroom_orders_products_data[$postKey]['product_total'] = (float) $postData['product_total'][$postKey];
				$showroom_orders_products_data[$postKey]['criteria_status'] = $postData['product_criteria'][$postKey];

				$stoneData = InventoryHelper::getStoneData($productId);
				$diamondPrice = json_decode($postData['diamond_price'][$postKey]);
				//print_r($showroom_orders_products_data[$postKey]['diamond_weight']);exit;
				$totalStonePrice = 0;
				$totalStoneWeight = 0;
				foreach ($stoneData['carat'] as $key => $value) {
					//echo $key;exit;
					$stoneData['carat'][$key] = $showroom_orders_products_data[$postKey]['diamond_weight'];
					$stoneData['stoneclarity'][$key] = $showroom_orders_products_data[$postKey]['diamond_quality'];
					$stoneData['totalcts'][$key] = $showroom_orders_products_data[$postKey]['diamond_weight'];
					$stoneData['stone_price'][$key] = $diamondPrice[$key];
					$totalStonePrice += (float) $diamondPrice[$key];
					$totalStoneWeight += (float) $showroom_orders_products_data[$postKey]['diamond_weight'];
				}
				$stoneData['total'] = $totalStonePrice;
				$stoneData['totalweight'] = $totalStoneWeight;
				unset($stoneData['simple']);
				$showroom_orders_products_data[$postKey]['diamond_data'] = json_encode($stoneData);

				//$showroom_orders_products_data[$postKey]['vendor'] = (int) $postData['vendor'][$postKey];
			}

			/*echo '<pre>';
			print_r($showroom_orders_products_data);exit;*/

			//$showroom_orders_model = Mage::getModel("showroom/orders");

			/*$lastIDData = $showroom_orders_model->getCollection()->addFieldToSelect('order_number')->setOrder('id', 'DESC');
				$lastIDData = $lastIDData->getFirstItem();
			*/
			$lastData = ShowroomOrders::orderBy('created_at', 'desc')->select('order_number')->first();
			if (empty($lastData->order_number)) {
				$OrderNumberConfigData = Setting::where('key', config('constants.settings.keys.showroom_order_number'))->first('value');

				$lastOrderNumber = $OrderNumberConfigData->value;
			} else {
				$lastOrderNumber = $lastData->order_number;
			}
			//var_dump($lastOrderNumber);
			//exit;

			$order_const = $lastOrderNumber;
			$order_const++;

			$showroomOrder = new ShowroomOrders;

			$showroom_orders_data = array();
			$showroomOrder->order_number = $order_const;
			$showroomOrder->po_number = 'DM-OL-' . date('Y-m-d') . '-' . $order_const;
			$showroomOrder->total_qty = $total_qty;
			$showroomOrder->order_total = $order_total;
			$showroomOrder->vendor = (int) $postData['vendor'][$postKey];
			$showroomOrder->created_by = $user->id;
			$showroomOrder->save();

			$order_id = $showroomOrder->id;
			//var_dump($showroomOrder->id);
			//exit;

			//var_dump($order_id);exit;

			//$showroom_orders_products_model = Mage::getModel("showroom/orderproducts");
			foreach ($showroom_orders_products_data as $pr_key => $pr_val) {
				$showroom_orders_products_data[$pr_key]['order_id'] = (int) $order_id;
				//$showroom_orders_products_model->setData($showroom_orders_products_data[$pr_key]);
			}

			ShowroomOrderProducts::insert($showroom_orders_products_data);
			//exit;
			//echo '<pre>';
			//print_r($showroom_orders_products_data);exit;

			return redirect()->route('showroom.orderhistory')
				->with('success', config('constants.message.order_placed'));

		}
	}

	public function orderhistory(Request $request) {
		$ordersCollection = ShowroomOrders::get();
		return view('showroom.history')->with('orders', $ordersCollection);
	}

	public function changeorderstatus(Request $request) {
		$postData = $request->post();

		$return_data = array();
		if (!empty($postData)) {
			$order = ShowroomOrders::find($postData['id']);
			$order->order_status = $postData['status'];
			$order->save();
			$return_data['success'] = true;
			$return_data['message'] = config('constants.message.showroom_status_changed');
		} else {
			$return_data['success'] = false;
			$return_data['message'] = config('constants.message.showroom_status_not_changed');
		}

		echo json_encode($return_data);exit;
	}

	public function orderview($id) {
		$ordersdata = ShowroomOrders::find($id);
		$orderproductsdata = ShowroomOrders::find($id)->order_products()->get();
		return view('showroom.orderview')->with(['id' => $id, 'ordersdata' => $ordersdata, 'orderproductsdata' => $orderproductsdata]);
	}
	//Display showroom inventory listing
	public function showroomInventory() {
		$productCollection = ShowroomHelper::getShowroomInventoryProducts();
		$quotationId = '';

		return view('showroom.showroominventory', compact('productCollection'));
	}

	//For server side datatable
	public function showroomInventoryAjaxList(Request $request) {
		$data = array();
		$params = $request->post();

		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $stalen + 1;

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

		if (count($productCollection) > 0) {
			foreach ($productCollection as $key => $product) {
				$now = date('Y-m-d');
				$from = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 5, date('Y')));
				if (($now >= $product->created_at) && ($from <= $product->created_at)) {
					$product_Lable = '<label class="badge badge-success">New</label>';
				} else {
					$product_Lable = '';
				}
				$checkbox = '<label><input type="checkbox" value="' . $product->entity_id . '" data-id="' . $product->entity_id . '" id="chk_product_' . $product->entity_id . '" class="form-check-input chkProduct" name="chkProduct[]"><span class="label-text"></span></label>' . '' . $product_Lable . '';
				//$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				//$productImage = '<img alt="' . $product->sku . '" class="product-img" src="' . (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage) . '">';
				$product_image = $imageDirectory . $product->product_image;
				$defaultProductImage = $imageDirectory . 'def_1.png';
				$productImage = '<img  class="product-img" src="' . (!empty($product->product_image) ? $product_image : $defaultProductImage) . '">';
				$position = strpos($product->sku, ' ');
				$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
				$certificateNo = $product->certificate_no;
				$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
				$categoryName = $categoryNames[0]->category_name;
				$rtsStoneQuality = !empty($product->rts_stone_quality) ? $product->rts_stone_quality : '-';
				$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
				//$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
				$price = ShowroomHelper::currencyFormat(round($product->custom_price));
				/*$inventoryStatusOption = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0");
					$inventoryStatuaArr = array();
					foreach ($inventoryStatusOption as $key => $value) {
						$inventoryStatuaArr[$value->option_id] = $value->value;
				*/
				$inventoryStatus = '';
				$inventoryStatus = ucwords(strtolower($product->inventory_status_value));
				//$invoiceMemoDetail = ShowroomHelper::getProductInvoiceMemoDetail($product->entity_id);
				$product_approval_memo_generated = (!empty($product->approval_memo_generated) ? 1 : 0);
				$product_approval_invoice_generated = (!empty($product->approval_invoice_generated) ? 1 : 0);
				$product_return_memo_generated = (!empty($product->return_memo_generated) ? 1 : 0);
				$customerName = 'N/A';
				if ($product_approval_invoice_generated == '1') {
					$inventoryAction = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                                        <option value="">Select</option>
                                        <option value="invoice" disabled data-productid="' . $product->entity_id . '">Generate Invoice</option>
										<option value="memo" disabled data-productid="' . $product->entity_id . '">Generate Memo</option>
										<!--<option value="returnmemo" disabled data-productid="' . $product->entity_id . '">Generate Return Memo</option>-->
                                    </select>';
				} else if ($product_approval_memo_generated == '1' && $product_return_memo_generated == '0') {
					$inventoryAction = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                                        <option value="">Select</option>
                                        <option value="invoice" data-productid="' . $product->entity_id . '">Generate Invoice</option>
										<option value="memo" disabled data-productid="' . $product->entity_id . '">Generate Memo</option>
										<!--<option value="returnmemo" data-productid="' . $product->entity_id . '">Generate Return Memo</option>-->
                                    </select>';
				} else if ($product_return_memo_generated == '1') {
					$inventoryAction = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                                        <option value="">Select</option>
                                        <option value="invoice" data-productid="' . $product->entity_id . '">Generate Invoice</option>
										<option value="memo"  data-productid="' . $product->entity_id . '">Generate Memo</option>
										<!--<option value="returnmemo" disabled data-productid="' . $product->entity_id . '">Generate Return Memo</option>-->
                                    </select>';
				} else {
					$inventoryAction = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                                        <option value="">Select</option>
                                        <option value="invoice" data-productid="' . $product->entity_id . '">Generate Invoice</option>
										<option value="memo"  data-productid="' . $product->entity_id . '">Generate Memo</option>
										<!--<option value="returnmemo" disabled data-productid="' . $product->entity_id . '">Generate Return Memo</option>-->
                                    </select>';
				}

				$data['data'][] = array($checkbox, $productImage, $sku, $certificateNo, $categoryName, $rtsStoneQuality, $virtualproductposition, $price, $inventoryStatus, $customerName, $inventoryAction);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}

	public function getFilteredProductCollection($params, $inventoryStatus = 'In') {
		/*echo "<pre>";
		print_r($params);exit;*/
		if ($inventoryStatus == 'In') {
			if (App::environment('local')) {
				$inventoryStatus = config('constants.apiurl.local.get_in');
			} else if (App::environment('test')) {
				$inventoryStatus = config('constants.apiurl.test.get_in');
			} else {
				$inventoryStatus = config('constants.apiurl.live.get_in');
			}
		}

		DB::setTablePrefix('');
		if ($inventoryStatus == 'pending') {
			$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'0'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
			$pendingPrdIds = array();
			foreach ($pendingStock as $product) {
				$pendingPrdIds[] = $product->product_id;
			}
			$prod = InventoryHelper::getAllProductsCollection(true);
			$prod = $prod->whereIn('entity_id', $pendingPrdIds);
		} else if ($inventoryStatus == 'Out') {
			$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'1'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
			$pendingPrdIds = array();
			foreach ($pendingStock as $product) {
				$pendingPrdIds[] = $product->product_id;
			}

			$prod = InventoryHelper::getAllProductsCollection(true);
			$prod = $prod->whereIn('entity_id', $pendingPrdIds);

		} else {
			$prod = InventoryHelper::getAllProductsCollection(true);
		}

		//$prod = $prod->where("inventory_status_value","In");
		if (isset($params['category'])) {
			$prod = $prod->whereIn('category_id', $params['category']);
		}

		//For search by certificate/sku
		$searchValue = (!empty($params['search']['value']) ? $params['search']['value'] : '');
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
		if ((!empty($priceStart)) && (!empty($priceTo))) {
			$prod = $prod->filter(function ($value, $key) use ($priceStart, $priceTo) {
				if ($value->custom_price >= $priceStart && $value->custom_price <= $priceTo) {
					return $value;
				}
			});
		}

		//var_dump($prod);exit;
		//For diamond weight filter
		$diaWeightStart = '';
		$diaWeightTo = '';
		if ((isset($params['diaweight_start']) && !empty($params['diaweight_start'])) && (isset($params['diaweight_to']) && !empty($params['diaweight_to']))) {
			$diaWeightStart = $params['diaweight_start'];
			$diaWeightTo = $params['diaweight_to'];
		}

		if ((isset($diaWeightStart) && !empty($diaWeightStart)) && (isset($diaWeightTo) && !empty($diaWeightTo))) {
			$prod = $prod->filter(function ($value, $key) use ($diaWeightStart, $diaWeightTo) {
				if ($value->total_carat >= $diaWeightStart && $value->total_carat <= $diaWeightTo) {
					return $value;
				}
			});
		}

		//For metal weight filter
		$metalWeightStart = '';
		$metalWeightTo = '';
		if ((isset($params['metalweight_start']) && !empty($params['metalweight_start'])) && (isset($params['metalweight_to']) && !empty($params['metalweight_to']))) {
			$metalWeightStart = $params['metalweight_start'];
			$metalWeightTo = $params['metalweight_to'];
		}
		if ((!empty($metalWeightStart)) && (!empty($metalWeightTo))) {
			$prod = $prod->filter(function ($value, $key) use ($metalWeightStart, $metalWeightTo) {
				if ($value->metal_weight >= $metalWeightStart && $value->metal_weight <= $metalWeightTo) {
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

			$status = (($params['stockstatus'] == $IN) ? $IN : $params['stockstatus']);
			$prod = $prod->where('inventory_status_value', $status);
		} else {
			if (!isset($params['stocktype']) && empty($params['stocktype'])) {
				if (!empty($inventoryStatus)) {
					if ($inventoryStatus != 'pending') {
						$status = $inventoryStatus;
						$prod = $prod->where('inventory_status_value', $status);
					}
				} else {
					if (App::environment('local')) {
						$IN = config('constants.apiurl.local.get_in');
					} else if (App::environment('test')) {
						$IN = config('constants.apiurl.test.get_in');
					} else {
						$IN = config('constants.apiurl.live.get_in');
					}
					$prod = $prod->whereIn('inventory_status_value', array('In', ' In', 'Out', 'Sold Out'));
				}
			}
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

		//For diamond shape filter
		if (!empty($params['diamond_shape'])) {

			$prod = $prod->filter(function ($value, $key) use ($params) {
				$shapes = explode(',', $value->diamond_shape);

				foreach ($shapes as $curshape) {
					//echo $curshape."   ";
					//print_r($params['diamond_shape']);exit;
					if (in_array($curshape, $params['diamond_shape'])) {
						return $value;
					}
				}
			});

		}

		//For approval person filter
		if (isset($params['approval_person']) && !empty($params['approval_person'])) {
			/*$orderData = DB::select("select ordertbl.entity_id as order_id, items.product_id from sales_flat_order as ordertbl join sales_flat_order_item as items on items.order_id = ordertbl.entity_id where ordertbl.customer_firstname like '%" . $params['approval_person'] . "%' OR ordertbl.customer_lastname like '%" . $params['approval_person'] . "%'");*/
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

			$orderData = DB::select("SELECT product_ids FROM `dml_approval_memo` where customer_id IN (SELECT entity_id FROM `customer_entity_varchar` WHERE `value` like '%" . $params['approval_person'] . "%' AND (attribute_id = '" . $firstNameAttrId . "' OR attribute_id = '" . $lastNameAttrId . "'))");

			$productIds = array();
			foreach ($orderData as $key => $order) {
				$product_ids = explode(',', $order->product_ids);
				foreach ($product_ids as $prodId) {
					$productIds[] = $prodId;
				}
			}
			//var_dump($productIds);
			//var_dump($prod->toArray());exit;
			$prod = $prod->whereIn('entity_id', $productIds);
		}

		//For approval type filter
		if (isset($params['approval_type']) && !empty($params['approval_type'])) {
			$approvalType = $params['approval_type'];
			$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'0'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->where('memo.approval_type', '=', DB::raw("'$approvalType'"))->get();
			$pendingPrdIds = array();
			foreach ($pendingStock as $product) {
				$pendingPrdIds[] = $product->product_id;
			}
			if ($inventoryStatus == 'pending') {
				$prod = $prod->whereIn('entity_id', $pendingPrdIds);
			} else if ($inventoryStatus == 'Out') {
				$prod = $prod->whereNotIn('entity_id', $pendingPrdIds);
			}

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
		if (!empty($params['stocktype']) && isset($params['stocktype'])) {
			$productIds = array();
			$showroomProducts = array();
			$approvalProducts = array();
			$pendingProducts = array();
			$soldProducts = array();
			if (in_array('Showroom', $params['stocktype'])) {
				$stocktype = false;
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
				$pendingPrdIds = array();
				foreach ($pendingStock as $product) {
					$pendingPrdIds[] = $product->product_id;
				}
				$approvalProducts = $approvalProducts->whereIn('entity_id', $pendingPrdIds);
				$approvalProducts = $approvalProducts->unique('entity_id')->pluck(['entity_id'])->toArray();
			}
			if (in_array('Pending', $params['stocktype'])) {
				$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'0'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
				$pendingPrdIds = array();
				foreach ($pendingStock as $product) {
					$pendingPrdIds[] = $product->product_id;
				}
				$pendingProducts = $prod->whereIn('entity_id', $pendingPrdIds);
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

		return $prod;
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

		if (App::environment('local')) {
			$our_categories_exp = config('constants.fixIds.local.category_ids');
		} else {
			$our_categories_exp = config('constants.fixIds.live.category_ids');
		}

		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$searchValue = (!empty($params['search_value']) ? $params['search_value'] : '');
		$stalen = $start / $length;
		$curpage = $stalen + 1;
		DB::setTablePrefix('');
		$stock_status = !empty($params['stock_status']) ? $params['stock_status'] : '';

		$prod = $this->getFilteredProductCollection($params, $stock_status);

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
		//print_r($filtered_products_gold_colors);exit;
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

		$filtered_products = $prod->unique('entity_id')->pluck(['entity_id'])->toArray();
		/*print_r($filtered_products);exit;*/
		// unique('entity_id')->
		//dd($prod->toArray());
		$filtered_certificates = $prod->pluck(['certificate_no'])->toArray();
		//echo "<pre>"; print_r($prod); exit;
		// unique('certificate_no')->
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
		//print_r($filtered_products_gold_colors);exit;
		$filtered_products_gold_quality = array_unique($filtered_products_gold_quality);
		$filtered_products_gold_colors = array_unique($filtered_products_gold_colors);
		$filtered_inventory_status = array_unique($filtered_inventory_status);
		//}
		// echo "<pre>";
		// print_r($filtered_products);exit;

		$response = array();
		$imageCount = $this->getProductImageCountAjax($prod);

		$response['diamond_quality_filters'] = InventoryHelper::getDiamondQuality(isset($params['diamond_quality']) ? $params['diamond_quality'] : '', $filtered_products);

		$response['diamond_shape_filters'] = InventoryHelper::getDiamondShape(isset($params['diamond_shape']) ? $params['diamond_shape'] : '', $filtered_products);

		$response['category_filters'] = InventoryHelper::getCategoryFilter(isset($params['category']) ? $params['category'] : '', $filtered_products);

		$response['gold_purity_filters'] = InventoryHelper::getGoldPurity(isset($params['gold_purity']) ? $params['gold_purity'] : '', $filtered_products_gold_quality);

		$response['gold_colors_filters'] = InventoryHelper::getGoldColor(isset($params['gold_color']) ? $params['gold_color'] : '', $filtered_products_gold_colors);

		$response['status_filters'] = InventoryHelper::getInventoryStatusFilter($filtered_inventory_status, isset($params['status']) ? $params['status'] : '');

		$response['stock_type_filters'] = InventoryHelper::getInventoryStockTypeFilter($filtered_inventory_status, isset($params['stocktype']) ? $params['stocktype'] : '');

		$response['virtual_filters'] = InventoryHelper::getInventoryVirtualFilter($filtered_virtual_position, isset($params['virtualproducts']) ? $params['virtualproducts'] : '');

		$response['productimage_filters'] = InventoryHelper::getInventoryProductImageFilter(isset($params['productimages']) ? $params['productimages'] : '', $imageCount);

		$response['diamond_weight_filters'] = InventoryHelper::getMinMaxDiamondWeight($filtered_products);

		$response['gold_weight_filters'] = InventoryHelper::getMinMaxMetalWeight($filtered_products);
		//echo "<pre>"; print_r($response); exit;
		echo json_encode($response);exit;
	}

	public function getProductImageCountAjax($prod) {
		$prodImgWith = array();
		$prodImgWithout = array();
		//echo "<pre>";
		//dd($prod);exit;
		/* $collection = InventoryHelper::getAllProductsCollection();
		$collection = $collection->whereIn("entity_id", $prod); */
		//echo "sdfsdfsdfdsf";exit;

		//echo $collection->count();exit;
		foreach ($prod as $key => $prodvalue) {
			$productimages = $prodvalue->product_image;
			if (!empty($productimages)) {
				$prodImgWith[] = $prodvalue->product_image;
			} else {
				$prodImgWithout[] = $prodvalue->product_image;
			}
		}

		/* echo "<pre>";
			print_r($prodImgWith);
			echo "<br><br>";
		*/
		$data = array('with_image' => count($prodImgWith), 'without_image' => count($prodImgWithout));
		return $data;
	}
	//Display approval inventory
	public function approvalInventory() {
		$productCollection = ShowroomHelper::getApprovalInventoryProducts();
		$quotationId = '';
		return view('showroom.approvalinventory', compact('productCollection'));
	}
	public function inventoryquery() {
		$productCollection = InventoryHelper::getInventoryQuery();exit;
	}
	public function approvalInventoryAjaxList(Request $request) {
		$data = array();
		$params = $request->post();

		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $stalen + 1;

		DB::setTablePrefix('');

		$prod = $this->getFilteredProductCollection($params, 'Out');
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
				$checkbox = '<label><input type="checkbox" value="' . $product->entity_id . '" data-id="' . $product->entity_id . '" id="chk_product_' . $product->entity_id . '" class="form-check-input chkProduct" name="chkProduct[]"><span class="label-text"></span></label>';

				/*$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				$productImage = '<img alt="' . $product->sku . '" class="product-img" src="' . (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage) . '">';*/

				$product_image = $imageDirectory . $product->product_image;
				$defaultProductImage = $imageDirectory . 'def_1.png';
				$productImage = '<img  class="product-img" src="' . (!empty($product->product_image) ? $product_image : $defaultProductImage) . '">';

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
				*/
				$inventoryStatus = '';
				$inventoryStatus = ucwords(strtolower($product->inventory_status_value));
				/* $pendingOrderData = InventoryHelper::getOrderByProduct($product->entity_id, 'pending');
					$orderId = isset($pendingOrderData[0]->order_id) ? $pendingOrderData[0]->order_id : '';
					$orderDate = '';
					$firstName = '';
					$lastName = '';
				*/
				$invoiceMemoDetail = ShowroomHelper::getProductInvoiceMemoDetail($product->entity_id);
				$product_approval_memo_generated = (!empty($invoiceMemoDetail->approval_memo_generated) ? 1 : 0);
				$product_approval_invoice_generated = (!empty($invoiceMemoDetail->approval_invoice_generated) ? 1 : 0);
				$product_return_memo_generated = (!empty($invoiceMemoDetail->return_memo_generated) ? 1 : 0);

				$memoData = InventoryHelper::getMemoData($product->entity_id);
				$memoCustomerId = isset($memoData[0]->customer_id) ? $memoData[0]->customer_id : '';
				$customerName = InventoryHelper::getCustomerName($memoCustomerId);
				/* if (count($pendingOrderData) > 0) {
						foreach ($pendingOrderData as $key => $order) {
							$orderDate = $order->created_at;
							$firstName = $order->customer_firstname;
							$lastName = $order->customer_lastname;
							$customerName = $firstName . ' ' . $lastName;
						}
					} else {
						$completedOrderData = InventoryHelper::getOrderByProduct($product->entity_id, 'complete');
						$orderId = isset($completedOrderData[0]->order_id) ? $completedOrderData[0]->order_id : '';
						foreach ($completedOrderData as $key => $order) {
							$orderDate = $order->created_at;
							$firstName = $order->customer_firstname;
							$lastName = $order->customer_lastname;
							$customerName = $firstName . ' ' . $lastName;
						}
					}
					if (count($pendingOrderData) == 0 && count($completedOrderData) == 0) {
						$customerName = 'N/A';
				*/
				if ($product_approval_invoice_generated == '1') {
					$inventoryAction = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                                        <option value="">Select</option>
                                        <option value="invoice" disabled data-productid="' . $product->entity_id . '">Generate Invoice</option>
										<!--<option value="memo" disabled data-productid="' . $product->entity_id . '">Generate Memo</option>-->
										<option value="returnmemo" disabled data-productid="' . $product->entity_id . '">Generate Return Memo</option>
                                    </select>';
				} else if ($product_approval_memo_generated == '1' && $product_return_memo_generated == '0') {
					$inventoryAction = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                                        <option value="">Select</option>
                                        <option value="invoice" data-productid="' . $product->entity_id . '">Generate Invoice</option>
										<!--<option value="memo" disabled data-productid="' . $product->entity_id . '">Generate Memo</option>-->
										<option value="returnmemo" data-productid="' . $product->entity_id . '">Generate Return Memo</option>
                                    </select>';
				} else if ($product_return_memo_generated == '1') {
					$inventoryAction = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                                        <option value="">Select</option>
                                        <option value="invoice" data-productid="' . $product->entity_id . '">Generate Invoice</option>
										<!--<option value="memo"  data-productid="' . $product->entity_id . '">Generate Memo</option>-->
										<option value="returnmemo" disabled data-productid="' . $product->entity_id . '">Generate Return Memo</option>
                                    </select>';
				} else {
					$inventoryAction = '<select class="form-control h-auto w-auto mx-auto inventory_action">
                                        <option value="">Select</option>
                                        <option value="invoice" data-productid="' . $product->entity_id . '">Generate Invoice</option>
										<!--<option value="memo"  data-productid="' . $product->entity_id . '">Generate Memo</option>-->
										<option value="returnmemo" data-productid="' . $product->entity_id . '">Generate Return Memo</option>
                                    </select>';
				}

				$data['data'][] = array($checkbox, $productImage, $sku, $certificateNo, $categoryName, $rtsStoneQuality, $virtualproductposition, $price, $inventoryStatus, $customerName, $inventoryAction);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	//Display sold products
	public function soldInventory() {
		Session::forget('order_ids');
		Session::forget('order_items');
		$productCollection = ShowroomHelper::getSoldInventoryProducts();
		$quotationId = '';
		return view('showroom.soldinventory', compact('productCollection'));
	}
	public function soldInventoryAjaxList(Request $request) {
		/*print_r($request->all());exit;*/
		$data = array();
		$params = $request->post();

		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $stalen + 1;

		DB::setTablePrefix('');
		$prod = $this->getFilteredProductCollection($params, 'Sold Out');
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
				$inventoryAction = '';
				$startDate = '';
				$endDate = '';
				$isReturned = 0;
				$disabledClass = (empty($orderId)) ? 'disabledChk' : 'chkProduct';
				$pendingOrderData = InventoryHelper::getOrderByProduct($product->entity_id, 'pending');
				//$orderId = isset($pendingOrderData[0]->order_id) ? $pendingOrderData[0]->order_id : '';
				$orderDate = '';
				$firstName = '';
				$lastName = '';
				$customerName = '';
				$orderId = '';
				$invoiceMemoDetail = ShowroomHelper::getProductInvoiceMemoDetail($product->entity_id);
				$product_approval_memo_generated = (!empty($invoiceMemoDetail->approval_memo_generated) ? 1 : 0);
				$product_approval_invoice_generated = (!empty($invoiceMemoDetail->approval_invoice_generated) ? 1 : 0);
				$product_return_memo_generated = (!empty($invoiceMemoDetail->return_memo_generated) ? 1 : 0);

				if (count($pendingOrderData) > 0) {
					foreach ($pendingOrderData as $key => $order) {
						$orderDate = $order->created_at;
						$firstName = $order->customer_firstname;
						$lastName = $order->customer_lastname;
						$customerName = $firstName . ' ' . $lastName;
						$orderId = isset($order->order_id) ? $order->order_id : '';
					}
				} else {
					$completedOrderData = InventoryHelper::getOrderByProduct($product->entity_id, 'complete');

					foreach ($completedOrderData as $key => $order) {
						$orderDate = $order->created_at;
						$firstName = $order->customer_firstname;
						$lastName = $order->customer_lastname;
						$customerName = $firstName . ' ' . $lastName;
						$orderId = isset($order->order_id) ? $order->order_id : '';
					}
				}
				$disabledChk = (empty($orderId)) ? 'disabled' : '';
				$checkbox = '<label><input data-productid="' . $product->entity_id . '" class="form-check-input chkProduct" data-id="' . $orderId . '" value="' . $orderId . '" type="checkbox" name="chkProduct[]" id="chkProduct' . $orderId . '" ' . $disabledChk . '/><span class="label-text"></label>';
				/*$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				$productImage = '<img alt="' . $product->sku . '" class="product-img" src="' . (!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image : $defaultProductImage) . '">';*/

				$product_image = $imageDirectory . $product->product_image;
				$defaultProductImage = $imageDirectory . 'def_1.png';
				$productImage = '<img  class="product-img" src="' . (!empty($product->product_image) ? $product_image : $defaultProductImage) . '">';

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
				*/
				$inventoryStatus = '';
				$inventoryStatus = ucwords(strtolower($product->inventory_status_value));

				if (!empty($orderId)) {
					$invoiceData = InventoryHelper::getInvoiceData($orderId);
					$invoiceCreateDate = isset($invoiceData->created_at) ? $invoiceData->created_at : '';
					$startDate = date('Y-m-t', strtotime($invoiceCreateDate));
					$endDate = date('Y-m-d');
				}

				if (count($pendingOrderData) == 0 && count($completedOrderData) == 0) {
					$customerName = 'N/A';
				}

				$disabledCancelClass = '';
				if ($startDate < $endDate || empty($orderId)) {
					$disabledCancelClass = 'disabled';
				}
				$returnMemoDisabledClass = '';
				if ($isReturned || empty($orderId)) {
					$returnMemoDisabledClass = 'disabled';
				}
				$inventoryAction .= "<a title='Sales Return' class='color-content table-action-style1 mr-1 " . $returnMemoDisabledClass . "' href='" . route('salesreturn', ['id' => $orderId]) . "'><i class='list-icon fa fa-retweet'></i></a>";
				$inventoryAction .= "<a " . $orderId . " title='Cancel Invoice' class='color-content table-action-style1 btn-cancel-invoice pointer " . $disabledCancelClass . "' data-productid=" . $product->entity_id . " data-orderid=" . $orderId . " data-href=" . route('cancelinvoice', ['id' => $orderId]) . "><i class='list-icon fa fa-trash-o'></i></a>";

				$data['data'][] = array($checkbox, $productImage, $sku, $certificateNo, $categoryName, $rtsStoneQuality, $virtualproductposition, $price, $inventoryStatus, $customerName, $inventoryAction);
			}
			//print_r($data);exit;
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	public function salesReturn($id) {
		if (!empty($id)) {
			$orderItems = InventoryHelper::getOrderItems($id);
			if (App::environment('local')) {
				$getStateUrl = config('constants.apiurl.local.get_state_list');
			} else if (App::environment('test')) {
				$getStateUrl = config('constants.apiurl.test.get_state_list');
			} else {
				$getStateUrl = config('constants.apiurl.live.get_state_list');
			}
			$postParam = 'country_id=IN';
			$stateList = array();
			$ch = curl_init($getStateUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$result = json_decode($result);

			if ($result->status == 'success') {
				$stateList = isset($result->data) ? $result->data : '';
			}
			return view('showroom.generatesalesreturn')->with(array('orderId' => array($id), 'orderItems' => array($orderItems), 'stateList' => json_encode($stateList)));

		}
	}
	public function generateSalesReturn(Request $request) {
		$params = $request->post();
		$orderId = isset($params['order_id']) ? explode(',', $params['order_id']) : '';
		//$orderId = array_unique($orderId);
		$orderIdArr = array();
		//print_r($orderId);exit;
		foreach ($orderId as $key => $id) {
			if (in_array($id, $orderIdArr)) {
				continue;
			}

			$order = InventoryHelper::getOrderData($id);
			$productIds = isset($params['product_ids']) ? explode(',', $params['product_ids']) : array();
			$state = isset($params['state']) ? $params['state'] : '';
			//$salesReturnNumber = config('constants.sales_return_number');
			//$salesReturnNumber = str_pad($salesReturnNumber[0], 6, '0', STR_PAD_LEFT);
			$lastData = SalesReturn::orderBy('created_at', 'desc')->select('sales_return_no')->first();
			$returnNumberConfigData = Setting::where('key', config('constants.settings.keys.sales_return_number'))->first('value');
			if (empty($lastData->sales_return_no)) {
				$returnNumberConfigData = Setting::where('key', config('constants.settings.keys.sales_return_number'))->first('value');
				$salesReturnNumber = isset($returnNumberConfigData->value) ? $returnNumberConfigData->value : '';
			} else {
				$salesReturnNumber = isset($lastData->sales_return_no) ? $lastData->sales_return_no + 1 : '';
			}

			$invoiceCollection = InventoryHelper::getInvoiceByOrder($id);
			foreach ($invoiceCollection as $key => $invoice) {
				$invoiceNumber = '';
				$hsnCode = '';
				$productData = array();
				$grandTotalPrice = 0;
				$cgst = 0;
				$sgst = 0;
				$igst = 0;
				$gstArr = array();
				$totalTaxAmount = 0;
				$finalGrandTotalPrice = 0;
				$finaRawTotal = 0;
				$finalDiscount = 0;
				$grandTotalPrice = 0;
				$invoiceNumber = isset($invoice->increment_id) ? $invoice->increment_id : '';
				$orderItems = InventoryHelper::getOrderItems($id);

				foreach ($orderItems as $key => $item) {
					$sutTotal = 0;
					$taxAmount = 0;
					$discountAmount = 0;
					if (in_array($item->product_id, $productIds)) {
						$product = InventoryHelper::getProductData($item->product_id);
						$metalData = ShowroomHelper::getMetalData($item->product_id, $product);
						$metalType[$key] = isset($metalData['type']) ? $metalData['type'] : '';
						$metalWeight[$key] = isset($metalData['weight']) ? $metalData['weight'] : '';
						$stone = isset($product->rts_stone_quality) ? $product->rts_stone_quality : '';
						$stoneData = ShowroomHelper::getSideStoneData($product->entity_id, $stone);
						//echo "<pre>";
						//print_r($stoneData);exit;
						$gemStoneData = InventoryHelper::getGemStoneData($product->entity_id);
						if (!empty($gemStoneData)) {
							$hsnCode = '71131940';
						} else if (!empty($metalData) && !empty($stoneData)) {
							$hsnCode = '71131930';
						} else if (empty($metalData) && !empty($stoneData)) {
							$hsnCode = '7102';
						} else if (!empty($metalData) && empty($stoneData) && empty($gemStoneData)) {
							$hsnCode = '7113';
						}
						$sutTotal = isset($item->price) ? (float) $item->price : 0;
						$qty = isset($item->qty_ordered) ? (int) $item->qty_ordered : 0;
						$taxAmount = isset($item->tax_amount) ? (float) $item->tax_amount : 0;
						$discountAmount = isset($item->discount_amount) ? (float) $item->discount_amount : 0;
						if (floatval($order->custom_discount_percent) != 0) {
							$finalDiscount = $discountAmount;
						} else {
							$finalDiscount += $discountAmount;
						}
						$grandTotalPrice += $sutTotal;
						$totalTaxAmount += $taxAmount;
						$finalGrandTotalPrice = round($grandTotalPrice);
						$finaRawTotal = $grandTotalPrice - $finalDiscount;

						$productData[$key] = array(
							'entity_id' => isset($product->entity_id) ? $product->entity_id : '',
							'sku' => isset($product->sku) ? $product->sku : '',
							'metal_type' => isset($metalData['type']) ? $metalData['type'] : '',
							'metal_weight' => isset($metalData['weight']) ? $metalData['weight'] : '',
							'diamond_weight' => isset($stoneData['totalcts'][0]) ? $stoneData['totalcts'][0] : 0,
							'diamond_clarity' => isset($stoneData['stoneclarity']) ? implode(',', $stoneData['stoneclarity']) : '',
							'qty' => isset($item->qty_ordered) ? (int) $item->qty_ordered : 0,
							'hsn_code' => $hsnCode,
							'unit_price' => isset($item->price) ? round($item->price) : 0,
							'total' => $sutTotal + $taxAmount - $discountAmount,
						);
						//Update product
						DB::statement("update catalog_product_flat_1 set is_returned=1 where entity_id=" . DB::raw("$product->entity_id"));
						Cache::forget('all_products_ajax');
						Cache::forget('all_products');
						//print_r($result);exit;
					}
				}
			}
			if ($state == 'MH') {
				$cgst = $grandTotalPrice * 0.015;
				$sgst = $grandTotalPrice * 0.015;
				$gstArr['cgst'] = $cgst;
				$gstArr['sgst'] = $sgst;
			} else {
				$gstPercentage = Setting::where('key', config('constants.settings.keys.gst_percentage'))->first('value');
				$gstPercentage = isset($gstPercentage->value) ? $gstPercentage->value : 0;

				$igst = $grandTotalPrice * ($gstPercentage / 100);
				$gstArr['igst'] = $igst;
			}
			$finalGrandTotalPrice = $totalTaxAmount + $grandTotalPrice - $finalDiscount;
			$grandTotal = ShowroomHelper::currencyFormat($finalGrandTotalPrice);
			$totalInvoiceValue = $finalGrandTotalPrice + $cgst + $sgst + $igst;
			$sales_return_no = DB::table('sales_return')->select('sales_return_no')->orderBy('id', 'desc')->limit(1)->get()->first();
			/*if (empty($sales_return_no)) {
					$salesReturnNumber = config('constants.sales_return_number');
					$salesReturnNumber = isset($salesReturnNumber[0]) ? $salesReturnNumber[0] : 1;
				} else {
					$salesReturnNumber = (int) $sales_return_no->sales_return_no + 1;
			*/
			$insertData = array('customer_id' => $order->customer_id, 'order_id' => $id, 'sales_return_no' => $salesReturnNumber, 'invoice_no' => $invoiceNumber, 'product_data' => json_encode($productData), 'supply_place' => $state, 'payment_mode' => isset($order->payment_mode) ? $order->payment_mode : '', 'total_invoice_value' => $totalInvoiceValue, 'total_taxable_value' => $finalGrandTotalPrice, 'gst_data' => json_encode($gstArr), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'));
			SalesReturn::create($insertData);
			$salesReturnId = DB::getPdo()->lastInsertId();
			//insert into sales_return_products

			foreach ($productData as $key => $product) {
				SalesReturnProducts::create(array('product_id' => $product['entity_id'], 'sales_return_id' => $salesReturnId, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));
			}

			if (!empty($salesReturnId)) {
				$response['status'] = true;
				$response['message'] = config('constants.message.showroom_sales_return_generate_success_message');
			} else {
				$response['status'] = false;
				$response['message'] = config('constants.message.showroom_sales_return_generate_failed_message');
			}
			$orderIdArr[] = $id;
		}

		echo json_encode($response);exit;
	}
	public function salesReturnList() {
		$salesReturnData = SalesReturn::all();
		return view('showroom.salesreturnlist')->with(array('salesReturnData' => $salesReturnData));
	}
	public function generateBulkSalesReturn(Request $request) {
		$params = $request->post();
		$orderIds = isset($params['orderIds']) ? explode(',', $params['orderIds']) : array();
		$productIds = isset($params['productIds']) ? explode(',', $params['productIds']) : array();

		$orderIds = array_unique($orderIds);
		$orderItemData = array();
		$alreadyReturnedCertificate = array();
		DB::setTablePrefix('');
		foreach ($orderIds as $key => $orderId) {
			$orderItems = InventoryHelper::getOrderItems($orderId);
			foreach ($orderItems as $key => $item) {
				DB::setTablePrefix('');

				$product = DB::table('catalog_product_flat_1')->select('entity_id', 'is_returned', 'certificate_no')->where('entity_id', '=', DB::raw("$item->product_id"))->get()->first();
				if ($product->is_returned) {
					if (!in_array($product->entity_id, $productIds)) {
						continue;
					}

					$alreadyReturnedCertificate[] = $product->certificate_no;
				}
			}
		}
		if (count($alreadyReturnedCertificate) > 0) {
			$response['status'] = false;
			$response['message'] = config('constants.message.sales_return_already_generated') . implode(',', $alreadyReturnedCertificate);
		} else {
			foreach ($orderIds as $key => $orderId) {
				if (!empty($orderId)) {
					$orderItemData[] = InventoryHelper::getOrderItems($orderId);
					if (App::environment('local')) {
						$getStateUrl = config('constants.apiurl.local.get_state_list');
					} else if (App::environment('test')) {
						$getStateUrl = config('constants.apiurl.test.get_state_list');
					} else {
						$getStateUrl = config('constants.apiurl.live.get_state_list');
					}
					//print_r($getStateUrl);
					$postParam = 'country_id=IN';
					$stateList = array();
					$ch = curl_init($getStateUrl);

					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$result = curl_exec($ch);

					$result = json_decode($result);

					if ($result->status == 'success') {
						$stateList = isset($result->data) ? $result->data : '';
					}
				}
			}
			if (!empty($orderIds) && !empty($orderItemData)) {
				$response['status'] = true;
				$response['order_ids'] = $orderIds;
				$response['order_items'] = $orderItemData;
				Session::put('order_ids', $orderIds);
				Session::put('order_items', $orderItemData);
				Session::save();
			} else {
				$response['status'] = false;
			}
		}
		echo json_encode($response);exit;
	}
	public function bulkSalesReturn(Request $request) {
		$params = $request->post();
		$orderIds = Session::get('order_ids');
		$orderItemsData = Session::get('order_items');
		if (App::environment('local')) {
			$getStateUrl = config('constants.apiurl.local.get_state_list');
		} else if (App::environment('test')) {
			$getStateUrl = config('constants.apiurl.test.get_state_list');
		} else {
			$getStateUrl = config('constants.apiurl.live.get_state_list');
		}
		$postParam = 'country_id=IN';
		$stateList = array();
		$ch = curl_init($getStateUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$result = json_decode($result);

		if ($result->status == 'success') {
			$stateList = isset($result->data) ? $result->data : '';
		}
		return view('showroom.generatesalesreturn')->with(array('orderId' => $orderIds, 'orderItems' => $orderItemsData, 'stateList' => json_encode($stateList)));
	}
	//View credit purchase note
	public function viewCreditPurchaseNote($id) {
		if (!empty($id)) {
			$salesReturnData = SalesReturn::find($id);
			$customPaper = array(0, 0, 720, 1200);
			$pdf = PDF::loadView('showroom/viewcreditpurchasenote', array('salesReturnData' => $salesReturnData))->setPaper($customPaper, 'A4');
			return $pdf->download('creditpurchasenote.pdf');
			//return view('showroom.viewcreditpurchasenote')->with(array('salesReturnData'=>$salesReturnData));
		}
	}
	//Generate credit sales note
	public function generateCreditSaleNote($id) {
		if (!empty($id)) {
			$salesReturnData = SalesReturn::find($id);
			SalesReturn::where("id", $id)->update(array('credited_by' => Auth::user()->id, 'is_credited' => 'yes'));
			$customPaper = array(0, 0, 720, 1200);
			$pdf = PDF::loadView('showroom/viewcreditsalenote', array('salesReturnData' => $salesReturnData))->setPaper($customPaper, 'A4');
			return $pdf->download('creditsalenote.pdf');
			//return view('showroom.viewcreditsalenote')->with(array('salesReturnData'=>$salesReturnData));
		}
	}
	//View credit sales note
	public function viewCreditSaleNote($id) {
		if (!empty($id)) {
			$salesReturnData = SalesReturn::find($id);
			$customPaper = array(0, 0, 720, 1200);
			$pdf = PDF::loadView('showroom/viewcreditsalenote', array('salesReturnData' => $salesReturnData))->setPaper($customPaper, 'A4');
			return $pdf->download('creditsalenote.pdf');
			//return view('showroom.viewcreditsalenote')->with(array('salesReturnData'=>$salesReturnData));
		}
	}
	//View debit purchase note
	public function viewDebitPurchaseNote($id) {
		if (!empty($id)) {
			$salesReturnData = SalesReturn::find($id);
			$customPaper = array(0, 0, 720, 1200);
			$pdf = PDF::loadView('showroom/viewdebitpurchasenote', array('salesReturnData' => $salesReturnData))->setPaper($customPaper, 'A4');
			return $pdf->download('debitpurchasenote.pdf');
		}
	}
	//View debit sale note
	public function viewDebitSaleNote($id) {
		if (!empty($id)) {
			$salesReturnData = SalesReturn::find($id);
			$customPaper = array(0, 0, 720, 1200);
			$pdf = PDF::loadView('showroom/viewdebitsalenote', array('salesReturnData' => $salesReturnData))->setPaper($customPaper, 'A4');
			return $pdf->download('debitsalenote.pdf');
		}
	}
	public function cancelBulkInvoice(Request $request) {
		$params = $request->post();
		$orderIds = isset($params['orderIds']) ? explode(',', $params['orderIds']) : '';

		$productIds = isset($params['productIds']) ? explode(',', $params['productIds']) : '';

		if (is_array($orderIds)) {
			$orderIds = array_unique($orderIds);
			$orderIds = array_filter($orderIds);
		} else {
			$orderIds = array($orderIds);
		}
		if (!is_array($productIds)) {
			$productIds = array($productIds);
		}

		DB::setTablePrefix("");
		$inventoryStatusOption = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0 AND EAOV.value='In'");
		$inventoryStatuaArr = array();
		foreach ($inventoryStatusOption as $key => $value) {
			$inventoryStatuaArr[strtolower($value->value)] = $value->option_id;
		}
		$cancelledInvoiceCertificate = array();
		foreach ($orderIds as $key => $orderId) {
			$orderItems = InventoryHelper::getOrderItems($orderId);
			//print_r($orderItems);exit;
			foreach ($orderItems as $key => $item) {
				if (isset($item->product_id) && in_array($item->product_id, $productIds)) {
					$orderDetail = InventoryHelper::getOrderDetailByItem($item->product_id);
					if ($orderDetail->status == 'canceled') {
						$cancelledInvoiceCertificate[] = $orderDetail->certificate_no;
					}
				}
			}
		}
		if (count($cancelledInvoiceCertificate) > 0) {
			$response['status'] = false;
			$response['message'] = config('constants.message.invoice_cancellation_already_done') . implode(',', $cancelledInvoiceCertificate);
		} else {

			foreach ($orderIds as $key => $orderId) {
				$orderItems = InventoryHelper::getOrderItems($orderId);
				foreach ($orderItems as $key => $item) {
					$result = DB::statement("UPDATE sales_flat_order set state='canceled',status='canceled' WHERE entity_id=" . DB::raw("$orderId"));
					$updateStatus = DB::statement("UPDATE catalog_product_flat_1 SET inventory_status='" . $inventoryStatuaArr['in'] . "',inventory_status_value='In',is_sold=0 WHERE entity_id=" . DB::raw("$item->product_id"));
					$updateQrProduct = DB::statement("UPDATE qrcode_inventory_management SET inventory_status='in' WHERE pr_id=" . DB::raw("$item->product_id"));
				}
			}
			if ($result) {
				Cache::forget('all_products_ajax');
				Cache::forget('all_products');
				$response['status'] = true;
				$response['message'] = config('constants.message.invoice_cancellation_success');
			} else {
				$response['status'] = false;
				$response['message'] = config('constants.message.invoice_cancellation_failure');
			}
		}
		DB::setTablePrefix("dml_");
		echo json_encode($response);exit;
	}
	//Display all stock
	public function allStock() {
		$productCollection = ShowroomHelper::getAllStockProducts();
		return view('showroom.allstock', compact('productCollection'));
	}

	public function product_list() {
		$data = Productupload::orderBy('id', 'DESC')->get();
		$totalcount = count($data);
		/* print_r($data);exit;*/
		/* print_r($data[0]);print_r($data[1]);exit;*/
		return view('showroom.product_list', compact('data', 'totalcount'));
	}
	public function showroom_response(Request $request) {

		$columns = array(0 => 'id',
			1 => 'image',
			2 => 'item',
			3 => 'po_no',
			4 => 'order_no',
			5 => 'certificate_no',
			6 => 'sku',
			7 => 'style',
			8 => 'metal_karat',
			9 => 'color',
			10 => 'ringsize',
			11 => 'product_category',
			12 => 'gross_weight',
			13 => 'metal_weight',
			14 => 'metalrate',
			15 => 'metalamount',
			16 => 'labouramount',
			17 => 'diamond_pcs',
			18 => 'diamond_weight',
			19 => 'colorstone_pcs',
			20 => 'colorstone_weight',
			21 => 'material_category',
			22 => 'material_type',
			23 => 'material_quality',
			24 => 'seive_size',
			25 => 'material_mm_size',
			26 => 'material_pcs',
			27 => 'material_weight',
			28 => 'stone_rate',
			29 => 'stone_amount',
			30 => 'total_stone_amount',
			31 => 'total_amount',
			32 => 'costingdata_id',
			33 => 'sgst',
			34 => 'cgst',
			35 => 'igi_charges',
			36 => 'hallmarking',
			37 => 'qc_status',
			38 => 'is_igi',
			39 => 'branding',
			40 => 'request_invoice',
			41 => 'return_memo',
			42 => 'batch_no',
			43 => 'approved_by',
			44 => 'rejected_by',
			45 => 'igi_by',
			46 => 'invoice_requested_by',
			47 => 'memo_returned_by',
			48 => 'extra_price',
			49 => 'extra_price_for',
			50 => 'created_at',
			51 => 'updated_at');
		$results = Productupload::orderBy('id', 'asc');
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
			$resultslist = $results->where('sku', 'LIKE', "%{$search}%")->orWhere('item', 'LIKE', "%{$search}%")->orWhere('po_no', 'LIKE', "%{$search}%")->orWhere('order_no', 'LIKE', "%{$search}%")->orWhere('certificate_no', 'LIKE', "%{$search}%")->orWhere('style', 'LIKE', "%{$search}%")->orWhere('metal_weight', 'LIKE', "%{$search}%")->orWhere('diamond_weight', 'LIKE', "%{$search}%")->orWhere('material_mm_size', 'LIKE', "%{$search}%")->offset($start)->limit($limit)->orderBy($order, $dir)->get();

			$totalFiltered = $results->where('sku', 'LIKE', "%{$search}%")->orWhere('item', 'LIKE', "%{$search}%")->orWhere('po_no', 'LIKE', "%{$search}%")->orWhere('order_no', 'LIKE', "%{$search}%")->orWhere('certificate_no', 'LIKE', "%{$search}%")->orWhere('style', 'LIKE', "%{$search}%")->orWhere('metal_weight', 'LIKE', "%{$search}%")->orWhere('diamond_weight', 'LIKE', "%{$search}%")->orWhere('material_mm_size', 'LIKE', "%{$search}%")->count();
		}
		$imagepath = config::get('constants.dir.product_list_img');
		/*$defimagepath= '<?=URL::to('/');?>/';*/
		$data = array();
		if (!empty($resultslist)) {
			foreach ($resultslist as $resultslist) {

				if (File::exists($resultslist->image)) {
					$image = '<img  class="product-img" src="' . URL::to('/') . '/' . $resultslist->image . '">';
				} else {
					$image = '<img  class="product-img" src="' . URL::to('/') . '/img/def_img.png' . '">';
				}

				$data[] = array($resultslist->id, $image, $resultslist->item, $resultslist->po_no, $resultslist->order_no, $resultslist->certificate_no, $resultslist->sku, $resultslist->style, $resultslist->metal_karat, $resultslist->color, $resultslist->ringsize, $resultslist->product_category, $resultslist->gross_weight, $resultslist->metal_weight, $resultslist->metalrate, $resultslist->metalamount, $resultslist->labouramount, $resultslist->diamond_pcs, $resultslist->diamond_weight, $resultslist->colorstone_pcs, $resultslist->colorstone_weight, $resultslist->material_category, $resultslist->material_type, $resultslist->material_quality, $resultslist->seive_size, $resultslist->material_mm_size, $resultslist->material_pcs, $resultslist->material_weight, $resultslist->stone_rate, $resultslist->stone_amount, $resultslist->total_stone_amount, $resultslist->total_amount, $resultslist->costingdata_id, $resultslist->sgst, $resultslist->cgst, $resultslist->igi_charges, $resultslist->hallmarking, $resultslist->qc_status, $resultslist->is_igi, $resultslist->branding, $resultslist->request_invoice, $resultslist->return_memo, $resultslist->batch_no, $resultslist->approved_by, $resultslist->rejected_by, $resultslist->igi_by, $resultslist->invoice_requested_by, $resultslist->memo_returned_by, $resultslist->extra_price, $resultslist->extra_price_for, date($resultslist->created_at), date($resultslist->updated_at));
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
	public function searchCustomer(Request $request) {
		$params = $request->post();
		$term = isset($params['term']) ? $params['term'] : '';

		$customerData = array();
		if (!empty($term)) {
			if (filter_var($term, FILTER_VALIDATE_EMAIL)) {
				DB::setTablePrefix('');
				$customerId = DB::table("customer_entity")->select("entity_id")->where("email", "like", DB::raw("'$term'"))->get();
				foreach ($customerId as $key => $id) {
					$customer = InventoryHelper::getDefaultBillingAddressByCustId($id->entity_id);
					$firstName = isset($customer['firstname']) ? $customer['firstname'] : '';
					$lastName = isset($customer['lastname']) ? $customer['lastname'] : '';
					$city = isset($customer['city']) ? ' - ' . $customer['city'] : '';
					$street = isset($customer['street']) ? ' - ' . $customer['street'] : '';
					$gstin = isset($customer['gstin']) ? ' - ' . $customer['gstin'] : '';
					$customerDetail = $firstName . " " . $lastName . $street . $city . $gstin;
					$customerData[$key]['value'] = $id->entity_id;
					$customerDetail = strlen($customerDetail) > 120 ? substr($customerDetail, 0, 120) . "..." : $customerDetail;
					$customerData[$key]['label'] = $customerDetail;

					//print_r($customerData);exit;
				}
			} else if ((strpos($term, 'DML') !== false)) {
				DB::setTablePrefix('');
				$userId = str_replace('dml', '', strtolower($term));
				$customerId = DB::table("customer_entity")->select('entity_id')->where('entity_id', '=', DB::raw('"' . $userId . '"'))->get();
				foreach ($customerId as $key => $id) {
					$customer = InventoryHelper::getDefaultBillingAddressByCustId($id->entity_id);
					$firstName = isset($customer['firstname']) ? $customer['firstname'] : '';
					$lastName = isset($customer['lastname']) ? $customer['lastname'] : '';
					$city = isset($customer['city']) ? ' - ' . $customer['city'] : '';
					$gstin = isset($customer['gstin']) ? ' - ' . $customer['gstin'] : '';
					$customerDetail = $firstName . " " . $lastName . $city . $gstin;
					$customerData[$key]['value'] = $id->entity_id;
					$customerDetail = strlen($customerDetail) > 120 ? substr($customerDetail, 0, 120) . "..." : $customerDetail;
					$customerData[$key]['label'] = $customerDetail;
				}
			} else {

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
				$firstNameAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='firstname'");
				$firstnameattrid = array();
				foreach ($firstNameAttribute as $key => $attr) {
					$firstnameattrid[] = $attr->attribute_id;
				}
				$firstnameattrid = implode(',', $firstnameattrid);

				$lastNameAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='lastname'");
				$lastnameattrid = array();
				foreach ($lastNameAttribute as $key => $attr) {
					$lastnameattrid[] = $attr->attribute_id;
				}
				$lastnameattrid = implode(',', $lastnameattrid);

				$frncodeAttribute = DB::select("select attribute_id from eav_attribute where attribute_code='frn_code'");
				$frncodeattrid = array();
				foreach ($frncodeAttribute as $key => $attr) {
					$frncodeattrid[] = $attr->attribute_id;
				}
				$frncodeattrid = implode(',', $frncodeattrid);

				$customerCollection = DB::select("SELECT `e`.*, `at_firstname`.`value` AS `firstname`, `at_lastname`.`value` AS `lastname`, `frn_code`.`value` AS `frn_code` FROM `customer_entity` AS `e`
 INNER JOIN `customer_entity_varchar` AS `at_firstname` ON (`at_firstname`.`entity_id` = `e`.`entity_id`) AND (`at_firstname`.`attribute_id` IN(" . $firstnameattrid . "))
 INNER JOIN `customer_entity_varchar` AS `at_lastname` ON (`at_lastname`.`entity_id` = `e`.`entity_id`) AND (`at_lastname`.`attribute_id` IN(" . $lastnameattrid . "))
 LEFT JOIN `customer_entity_varchar` AS `frn_code` ON (`frn_code`.`entity_id` = `e`.`entity_id`) AND (`frn_code`.`attribute_id` IN(" . $frncodeattrid . ")) WHERE (`e`.`entity_type_id` = '1') AND ((`e`.`email` LIKE '%" . $term . "%') OR (at_firstname.value LIKE '%" . $term . "%') OR (at_lastname.value LIKE '%" . $term . "%') OR (frn_code.value LIKE '%" . $term . "%') OR (`e`.`entity_id` LIKE '%" . $term . "%'))");
				if (count($customerCollection) > 0) {
					foreach ($customerCollection as $key => $customer) {
						$customerInfo = InventoryHelper::getDefaultBillingAddressByCustId($customer->entity_id);
						$firstName = isset($customer->firstname) ? $customer->firstname : '';
						$lastName = isset($customer->lastname) ? $customer->lastname : '';
						$street = isset($customerInfo['street']) ? ' - ' . $customerInfo['street'] : '';
						$city = isset($customerInfo['city']) ? ' - ' . $customerInfo['city'] : '';
						$gstin = isset($customerInfo['gstin']) ? ' - ' . $customerInfo['gstin'] : '';
						$customerDetail = $firstName . " " . $lastName . $street . $city . $gstin;
						$customerData[$key]['value'] = $customer->entity_id;
						$customerDetail = strlen($customerDetail) > 120 ? substr($customerDetail, 0, 120) . "..." : $customerDetail;
						$customerData[$key]['label'] = $customerDetail;
					}
				}
			}
			echo json_encode($customerData);exit;
		}
	}

	//Export product CSV
	public function getCsv(Request $request) {

		if (App::environment('local')) {
			$IN = config('constants.apiurl.local.get_in');
		} else if (App::environment('test')) {
			$IN = config('constants.apiurl.test.get_in');
		} else {
			$IN = config('constants.apiurl.live.get_in');
		}

		$params = $request->post();
		$productIds = isset($params['productIds']) ? $params['productIds'] : array();
		if (!empty($productIds) && !is_array($productIds)) {
			$productIds = explode(',', $productIds);
		}

		if (count($productIds) > 0) {
			$sql = $prod = '';
			DB::setTablePrefix('');
			$collection = InventoryHelper::getAllProductsCollection();
			$collection = $collection->where("inventory_status_value", $IN)->whereIn('entity_id', $productIds);
			$productData = array('totalCount' => $collection->count(), 'productCollection' => $collection);
			DB::setTablePrefix('dml_');
		} else {

			$sql = $prod = '';
			DB::setTablePrefix('');
			$collection = InventoryHelper::getAllProductsCollection();
			$collection = $collection->where("inventory_status_value", $IN);
			$productData = array('totalCount' => $collection->count(), 'productCollection' => $collection);
			DB::setTablePrefix('dml_');
		}

		$inventoryProducts = $productData['productCollection'];

		foreach ($inventoryProducts as $key => $inventory) {
			$id = $inventory->certificate_no;
			$data[] = array(
				'Certificate No' => $id,
			);
		}
		$row = 0;
		return \Excel::create('showroom_products', function ($excel) use ($data) {
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
							$sheet->setSize(array(
								'B1' . $rowNo => array(
									'width' => 15,
									'height' => 15,
								),
							));
							$sheet->getRowDimension($rowNo)->setRowHeight(70);
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

	public function getSoldInventoryCsv(Request $request) {

		if (App::environment('local')) {
			$our_categories_exp = config('constants.fixIds.local.category_ids');
		} else {
			$our_categories_exp = config('constants.fixIds.live.category_ids');
		}

		$params = $request->post();
		$productIds = isset($params['productIds']) ? $params['productIds'] : array();
		if (!empty($productIds) && !is_array($productIds)) {
			$productIds = explode(',', $productIds);
		}

		DB::setTablePrefix('');

		$inv_products = array();
		if (count($productIds) > 0) {
			$check_products = $productIds;
		} else {
			$check_products = array();
			$check_sold_data = DB::table('catalog_product_flat_1 as cpf1')
				->select('cpf1.entity_id')
				->where('cpf1.inventory_status_value', '=', DB::raw('"Sold Out"'))->get();
			//$check_sold_data = collect($check_sold_data)->forget('entity_id')->toArray();
			foreach ($check_sold_data as $check_sold_data_key => $check_sold_data_value) {
				$check_products[] = $check_sold_data_value->entity_id;
			}

		}

		//var_dump($check_products);exit;

		if (count($check_products) > 0) {

			foreach ($check_products as $pridkey => $pridvalue) {
				$invoice_prod = DB::table('sales_flat_invoice_item as invoiceitem')
					->select('invoiceitem.parent_id')
					->where('invoiceitem.product_id', '=', $pridvalue)
					->orderBy('invoiceitem.entity_id', 'desc')->first();

				if (!empty($invoice_prod)) {
					if (!empty($invoice_prod->parent_id)) {
						$inv_products[] = $invoice_prod->parent_id;
					}
				}
				//var_dump($invoice_prod);
			}

		}

		$our_categories_for_query = implode("','", $our_categories_exp);
		$prod = DB::table('catalog_product_flat_1 as e')
			->select('e.entity_id', 'e.sku', 'e.name as product_name', 'e.stone_shape', 'e.certificate_no', 'e.approval_memo_generated', 'e.approval_invoice_generated', 'e.return_memo_generated', 'e.type_id', 'e.attribute_set_id', 'e.isreadytoship', 'e.rts_position', 'e.rts_stone_quality', 'e.status', 'e.custom_price', 'e.inventory_status', 'e.inventory_status_value', 'inventory_management.pr_name', 'catalog_category_product.category_id', 'e.metal_quality', 'e.virtual_product_manager', 'e.metal_quality_value', 'grp_stone.total_carat', DB::raw('GROUP_CONCAT(DISTINCT grp_stone.stone_shape) AS diamond_shape'), 'grp_metal.metal_weight', 'e.is_returned', "sales_flat_invoice.entity_id as invoice_ent_id", "sales_flat_invoice.increment_id as invoice_number", "sales_flat_invoice.created_at as invoice_created_date", "sales_flat_invoice.increment_id as invoice_inc_id"
				, DB::raw('CONCAT(sales_flat_order.customer_lastname," ",sales_flat_order.customer_firstname) AS Name'))

			->rightJoin('qrcode_inventory_management as inventory_management', 'e.entity_id', '=', 'inventory_management.pr_id')
			->join('catalog_category_product', 'e.entity_id', '=', 'catalog_category_product.product_id')
			->leftJoin('grp_stone', 'grp_stone.stone_product_id', '=', 'e.entity_id')
			->leftJoin('grp_metal', 'grp_metal.metal_product_id', '=', 'e.entity_id')
			->leftJoin('sales_flat_invoice_item', 'sales_flat_invoice_item.product_id', '=', 'e.entity_id')
			->leftJoin('sales_flat_invoice', 'sales_flat_invoice.entity_id', '=', 'sales_flat_invoice_item.parent_id')
			->leftJoin('sales_flat_order', 'sales_flat_invoice.order_id', '=', 'sales_flat_order.entity_id')
			->where('e.status', '=', DB::raw('1'))
			->where('e.inventory_status_value', '=', DB::raw('"Sold Out"'))
			->where('e.isreadytoship', '=', DB::raw('1'))
			->where('e.type_id', '=', DB::raw('"simple"'))
			->where('e.custom_price', '!=', DB::raw('0'))
			->where('e.custom_price', '!=', DB::raw('""'))
			->whereIn('category_id', [DB::raw("'" . $our_categories_for_query . "'")])
			->orderBy('sales_flat_invoice_item.entity_id', 'desc')
		//->orderBy('e.entity_id', 'desc')
			->groupBy('e.entity_id');

		if (count($inv_products) > 0) {
			//var_dump($inv_products);exit;
			$prod = $prod->whereIN('sales_flat_invoice.entity_id', $inv_products);
		}

		if (count($productIds) > 0) {
			//echo $prod->whereIN('e.entity_id', $productIds)->toSql();exit;
			$sql = $prod->whereIN('e.entity_id', $productIds)->get();
		} else {
			$sql = $prod->get();
		}

		$productCollection = '';
		$data = array();
		foreach ($sql as $key => $inventory) {

			$certificateNo = $inventory->certificate_no;
			$data[] = array(
				'Certificate No' => $certificateNo,
				'Invice No' => $inventory->invoice_number,
				'Invoice Date' => $inventory->invoice_created_date,
				'Name' => $inventory->Name,
			);
		}
		$row = 0;
		return \Excel::create('showroom_products', function ($excel) use ($data) {
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
							$sheet->setSize(array(
								'B1' . $rowNo => array(
									'width' => 15,
									'height' => 15,
								),
							));
							$sheet->getRowDimension($rowNo)->setRowHeight(70);
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

	public function movetomemohistory() {
		$approvals = DB::select("select * from dml_approval_memo order by id desc");

		//echo '<pre>';
		//print_r($approvals);
		$counter = 0;
		foreach ($approvals as $apkey => $approval) {
			$productIdsStr = $approval->product_ids;
			$approvalNumber = $approval->approval_no;
			$approvalMemoId = $approval->id;
			$approvalCreatedAt = $approval->created_at;
			$productIds = explode(',', $productIdsStr);
			foreach ($productIds as $key => $productId) {
				$approval_history = DB::select("select * from dml_approval_memo_histroy where product_id = " . $productId . " order by id desc limit 1");
				//var_dump($approval_history);
				if (count($approval_history) == 0) {
					$counter++;
					echo $productId . ' => ' . $counter . '<br>';
					$approvalHistroyData = array(
						'approval_no' => $approvalNumber,
						'product_id' => $productId,
						'status' => 'approval',
						'date' => $approvalCreatedAt,
						'approval_memo_id' => $approvalMemoId,
					);
					//var_dump($approvalHistroyData);
					ApprovalMemoHistroy::create($approvalHistroyData);
				}
			}
		}

		echo "movetomemohistory";exit;
	}

	public function qrcodescanning() {

		$id = Auth::User()->id;
		$allscannings = QrcodeScanning::where('created_by', $id)->orderBy('id', 'desc')->paginate(10);
		//print_r($allscannings);exit;
		//remove qr productids from session which is stored for export product excel
		Session::forget('qr_product_ids');
		$totalScanning = QrcodeScanning::where('created_by', $id)->count();
		/*	$allscannings = QrcodeScanning::all()->take(10);*/
		//var_dump($allscannings);exit;
		return view('qrcodescanning.index', compact('allscannings', 'totalScanning'));
	}

	public function viewdetail(Request $request) {

		$collection = InventoryHelper::getAllProductsCollection();
		$collection = $collection->where("certificate_no", $request->id);

		foreach ($collection as $value) {

			$shape = $value->stone_shape;
			$quality = $value->metal_quality;

		}
		if (!empty($quality)) {

			$metalQualityData = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_quality' AND EAOV.store_id = 0 AND EAOV.option_id= $quality");
		}
		if (!empty($shape)) {

			$shape = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0 AND EAOV.option_id=$shape");
		}

		$returnHTML = view('qrcodescanning.view', ['data' => $collection, 'shape' => $shape, 'quality' => $metalQualityData])->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));

	}

	public function addtoscanninglist(Request $request) {
		$user = Auth::user();
		$data = array();
		$params = $request->post();

		$responseData = array();

		if (!empty($params['certificate_no'])) {
			$product = InventoryHelper::isCertificateExist($params['certificate_no']);
			if ($product) {
				$productAlreadyAdded = ShowroomHelper::isExistInScanningList($params['certificate_no']);
				if (!$productAlreadyAdded) {
					$productId = InventoryHelper::getProductIdByCertificate($params['certificate_no']);
					$scanningData = array(
						'certificate_no' => $params['certificate_no'],
						'created_by' => $user->id,
						'product_id' => $productId,
					);
					$id = QrcodeScanning::create($scanningData);

					$responseData['status'] = true;

					$msg = "Certificate : " . "<u>" . $product->certificate_no . "</u>" . "   ";
					$msg .= Config::get('constants.message.inventory_certificate_added');
					$responseData['message'] = $msg;
				} else {
					$responseData['status'] = false;
					$responseData['message'] = Config::get('constants.message.inventory_certificate_already_exist');
				}
			} else {
				$responseData['status'] = false;
				$responseData['message'] = Config::get('constants.message.inventory_certificate_not_found');
			}
		} elseif (!empty($params['product_id'])) {
			$product = InventoryHelper::isProductExist($params['product_id']);
			if ($product) {
				$productAlreadyAdded = ShowroomHelper::isExistInScanningList($params['product_id']);
				if (!$productAlreadyAdded) {
					$productId = $params['product_id'];
					$scanningData = array(
						'certificate_no' => $product->certificate_no,
						'created_by' => $user->id,
						'product_id' => $productId,
					);
					$id = QrcodeScanning::create($scanningData);

					$responseData['status'] = true;

					$msg = "Certificate : " . "<u>" . $product->certificate_no . "</u>" . "   ";
					$msg .= Config::get('constants.message.inventory_certificate_added');
					$responseData['message'] = $msg;
				} else {
					$responseData['status'] = false;
					$responseData['message'] = Config::get('constants.message.inventory_certificate_already_exist');
				}
			} else {
				$responseData['status'] = false;
				$responseData['message'] = Config::get('constants.message.inventory_certificate_not_found');
			}
		}

		echo json_encode($responseData);exit;

	}

	public function deletecertfromscanning(Request $request) {
		$user = Auth::user();
		$data = array();
		$params = $request->post();

		$responseData = array();
		$productAlreadyAdded = ShowroomHelper::isExistInScanningList($params['certificate_no']);
		if ($productAlreadyAdded) {
			$affectedRows = QrcodeScanning::where('id', '=', $params['scanning_id'])->delete();
			$responseData['status'] = true;
			$responseData['message'] = Config::get('constants.message.inventory_certificate_deleted');
		} else {
			$responseData['status'] = false;
			$responseData['message'] = Config::get('constants.message.inventory_certificate_doesnt_exist');
		}
		echo json_encode($responseData);exit;
	}

	public function bulkdeletecertfromscanning(Request $request) {
		$user = Auth::user();
		$data = array();
		$params = $request->post();

		$scannings = explode(',', $params['scannings']);
		$certificates = explode(',', $params['certificates']);
		$scanningsCount = count($scannings);
		$successCount = 0;
		$notDeletedCertificates = array();
		foreach ($scannings as $scankey => $scanning) {
			//if ($scanning == 23) {
			//	$deletedResult = false;
			//} else {
			$deletedResult = QrcodeScanning::where('id', '=', $scanning)->delete();
			//}
			if ($deletedResult) {
				$successCount++;
			} else {
				$notDeletedCertificates[] = $certificates[$scankey];
			}
		}

		if ($successCount == 0) {
			$notDeletedCertificates = implode(',', $notDeletedCertificates);
			$responseData['status'] = false;
			$responseData['type'] = 'error';
			$responseData['message'] = $notDeletedCertificates . ' ' . Config::get('constants.message.inventory_certificate_not_deleted');
		} elseif (count($notDeletedCertificates) > 0 && $successCount != $scanningsCount) {
			$notDeletedCertificates = implode(',', $notDeletedCertificates);
			$responseData['status'] = false;
			$responseData['type'] = 'warning';
			$responseData['message'] = $notDeletedCertificates . ' ' . Config::get('constants.message.inventory_certificate_not_deleted');
		} else {
			$responseData['status'] = true;
			$responseData['type'] = 'sucess';
			$responseData['message'] = Config::get('constants.message.inventory_certificate_deleted');
		}

		echo json_encode($responseData);exit;
	}

	public function qrcodescanningajax(Request $request) {
		$id = Auth::User()->id;
		//$allscannings = QrcodeScanning::where('created_by', $id)->orderBy('id', 'desc')->get();

		//$params = $request->post();
		$allscannings = QrcodeScanning::where('created_by', $id)->orderBy('id', 'desc');

		$totalData = $allscannings->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		//$order = $request->input('order.0.column');
		//$dir = $request->input('order.0.dir');

		if (empty($request->input('search.value'))) {
			$resultslist = QrcodeScanning::where('created_by', $id)->offset($start)
				->limit($limit)
				->orderBy('id', 'desc')
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = QrcodeScanning::where('created_by', $id)->whereRaw('(certificate_no LIKE "%' . $search . '%" ) ')
				->offset($start)
				->limit($limit)
				->orderBy('id', 'desc')
				->get();
			$totalFiltered = QrcodeScanning::where('created_by', $id)->whereRaw('(certificate_no LIKE "%' . $search . '%" ) ')->count();
		}

		$data = array();
		//print_r($resultslist);exit;
		$i = 0;
		if (!empty($resultslist)) {
			foreach ($resultslist as $scanning) {

				$checkbox = '<label><input type="checkbox" data-productid="' . $scanning->product_id . '" value="' . $scanning->id . '" data-id="' . $scanning->id . '" id="chk_scanning_' . $scanning->id . '" class="form-check-input chkScanning"><span class="label-text"></span></label>';
				$certificateNo = $scanning->certificate_no;

				$action = ' <a href="#"><i title="Detail" id="btn_detail" value ="' . $certificateNo . '" data-id="' . $certificateNo . '" class="material-icons list-icon">info</i></a>';

				$action .= ' <a title="Delete Certificate" data-scanningid="' . $scanning->id . '" data-certificate="' . $scanning->certificate_no . '" class="color-content table-action-style1 btn-delete-certificate pointer"><i class="list-icon fa fa-trash-o"></i></a>';
				//$remarks = '' . $remark1 . ' ' . $remark2 . '';

				$data[] = array($checkbox, $certificateNo, $action);
			}

		} else {
			$data[] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
		}
		$json_data = array(
			"draw" => intval($request->input('draw')),
			"recordsTotal" => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data" => $data,
		);
		echo json_encode($json_data);exit;

	}
	public function showroomproductlist(Request $request) {
		//print_r($request->all());exit;
		$collection = InventoryHelper::getAllProductsCollection();
		//print_r($collection);exit;
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

//print_r($collection);exit;

		$returnHTML = view('showroom.ProductDetail', ['data' => $collection, 'quality' => $metalQualityData])->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));

	}
	//Get pending deliver products list
	public function pendingStock() {
		$pendingStock = DB::table('approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id', 'memo.approval_no', 'memo.is_for_old_data')->join('approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'0'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
		$productIds = array();
		$approvalMemoNumbers = array();
		foreach ($pendingStock as $key => $product) {
			$productIds[] = $product->product_id;
			$approvalNumber = '';
			if (isset($product->is_for_old_data) && $product->is_for_old_data == 'yes') {
				$approvalNumber = $product->approval_no;
			} else {
				if (!empty($product->approval_no)) {
					$currentYear = date('y');
					$approvalNumber = $currentYear . '-' . ($currentYear + 1) . '/' . $product->approval_no;
				} else {
					$approvalNumber = 'N/A';
				}
			}
			$approvalMemoNumbers[$product->product_id] = $approvalNumber;

		}
		$collection = InventoryHelper::getAllProductsCollection();
		$collection = $collection->whereIn('entity_id', $productIds);
		$productCollection = $collection->take(10);
		$productCollection = array('totalCount' => $collection->count(), 'productCollection' => $productCollection);
		return view('showroom.pendingstock', compact('productCollection', 'approvalMemoNumbers'));
	}
	public function pendingStockAjaxList(Request $request) {
		$data = array();
		$params = $request->post();

		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $stalen + 1;
		$collection = $this->getFilteredProductCollection($params, 'pending');

		$pendingStock = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id', 'memo.approval_no', 'memo.is_for_old_data')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'0'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
		$productIds = array();
		$approvalMemoNumbers = array();
		foreach ($pendingStock as $key => $product) {
			$productIds[] = $product->product_id;
			$approvalNumber = '';
			if (isset($product->is_for_old_data) && $product->is_for_old_data == 'yes') {
				$approvalNumber = $product->approval_no;
			} else {
				if (!empty($product->approval_no)) {
					$currentYear = date('y');
					$approvalNumber = $currentYear . '-' . ($currentYear + 1) . '/' . $product->approval_no;
				} else {
					$approvalNumber = 'N/A';
				}
			}
			$approvalMemoNumbers[$product->product_id] = $approvalNumber;

		}
		$productCount = $collection->count();
		$productCollection = $collection->forPage($curpage, $length);

		$data["draw"] = $params['draw'];
		$data["recordsTotal"] = $productCount;
		$data["recordsFiltered"] = $productCount;
		$data['deferLoading'] = $productCount;
		$imageDirectory = config('constants.dir.website_url_for_product_image');
		$defaultProductImage = $imageDirectory . 'def_1.png';
		$inventoryStatusOption = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0");
		if ($productCount > 0) {
			foreach ($productCollection as $key => $product) {
				$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
				$product_approval_memo_generated = (!empty($product->approval_memo_generated) ? $product->approval_memo_generated : 0);
				$product_approval_invoice_generated = (!empty($product->approval_invoice_generated) ? $product->approval_invoice_generated : 0);
				$product_return_memo_generated = (!empty($product->return_memo_generated) ? $product->return_memo_generated : 0);
				$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);

				$categoryName = isset($categoryNames[0]->category_name) ? $categoryNames[0]->category_name : '';

				$checkbox = '<label><input class="form-check-input chkProduct" data-id="' . $product->entity_id . '" value="' . $product->entity_id . '" type="checkbox" name="chkProduct[]" id="chkProduct' . $product->entity_id . '"><span class="label-text"></label>';
				$productImage = '<img  class="product-img" src="' . (!empty($product->product_image) ? $product_image : $defaultProductImage) . '">';
				$position = strpos($product->sku, ' ');
				$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
				$certificateNo = isset($product->certificate_no) ? $product->certificate_no : '';
				$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
				$categoryName = isset($categoryNames[0]->category_name) ? $categoryNames[0]->category_name : '';
				$stoneQuality = !empty($product->rts_stone_quality) ? $product->rts_stone_quality : '-';
				$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
				$productPrice = ShowroomHelper::currencyFormat(round($product->custom_price));
				$inventoryStatuaArr = array();
				foreach ($inventoryStatusOption as $key => $value) {
					$inventoryStatuaArr[$value->option_id] = $value->value;
				}
				$inventoryStatus = '';
				$inventoryStatus = isset($inventoryStatuaArr[$product->inventory_status]) ? $inventoryStatuaArr[$product->inventory_status] : '-';
				$memoData = InventoryHelper::getMemoData($product->entity_id);
				$memoCustomerId = isset($memoData[0]->customer_id) ? $memoData[0]->customer_id : '';
				$customerName = InventoryHelper::getCustomerName($memoCustomerId);
				$approvalNo = isset($approvalMemoNumbers[$product->entity_id]) ? $approvalMemoNumbers[$product->entity_id] : '';
				$data['data'][] = array($checkbox, $productImage, $sku, $certificateNo, $categoryName, $stoneQuality, $virtualproductposition, $productPrice, $inventoryStatus, $customerName);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}
	public function checkproducts() {
		$prod = DB::select("select entity_id from catalog_product_flat_1 as e WHERE e.entity_id NOT IN( SELECT product_id FROM dml_approval_memo_histroy where status='approval' ) and e.inventory_status=3172");
		$productIds = array();
		foreach ($prod as $value) {
			$productIds[] = $value->entity_id;
		}
		$ids = implode("','", $productIds);
		$sql = "update catalog_product_flat_1 set inventory_status = 3171, inventory_status_value = 'In' where entity_id in('" . $ids . "')";
		DB::statement($sql);
		$sql = "update qrcode_inventory_management set inventory_status='in' where pr_id in('" . $ids . "')";
		DB::statement($sql);
		echo $ids;exit;
		/* $prod = InventoryHelper::getAllProductsCollection();
			$prod = $prod->where('inventory_status_value','=','Sold Out');
		*/
	}
	//remove old order \
	public function removeOldOrders() {
		if (App::environment('local')) {
			$inventoryStatus = config('constants.apiurl.local.get_in');
		} else if (App::environment('test')) {
			$inventoryStatus = config('constants.apiurl.test.get_in');
		} else {
			$inventoryStatus = config('constants.apiurl.live.get_in');
		}
		$collection = InventoryHelper::getAllProductsCollection();
		$showroomProducts = $collection->where("inventory_status_value", $inventoryStatus);
		$showroomProductIds = $showroomProducts->pluck(['entity_id']);
		$invoiceIds = array();

		$dmlInvoiceProducts = DB::table('invoice_products')->select('invoice_id')->distinct()->get();
		$dmlInvoiceIds = array();
		foreach ($dmlInvoiceProducts as $invoice) {
			$dmlInvoiceIds[] = $invoice->invoice_id;
		}
		$dmlInvoiceIds = implode("','", $dmlInvoiceIds);
		$orderIds = array();
		$productIds = array();
		foreach ($showroomProductIds as $ids) {
			$productIds[] = $ids;
		}
		$productIds = array_unique($productIds);
		$productIds = array_chunk($productIds, 5);
		$orderIds = array();
		foreach ($productIds as $productId) {
			$ids = implode("','", $productId);

			$invoice = DB::select("SELECT
ORD.entity_id AS order_id,
ORD.status,
invoice.entity_id AS invoice_id,
invoice_item.product_id
FROM
sales_flat_invoice AS invoice
JOIN
sales_flat_invoice_item AS invoice_item
JOIN
sales_flat_order AS ORD ON ORD.entity_id = invoice.order_id
WHERE
invoice_item.product_id in('" . $ids . "') and invoice.entity_id not in('" . $dmlInvoiceIds . "')");
			foreach ($invoice as $item) {
				if (!in_array($item->order_id, $orderIds)) {
					$orderIds[] = $item->order_id;
				}

			}
		}
		/* echo "<pre>";
		print_r($orderIds);exit; */
		$orderParam = 'order_ids=' . json_encode($orderIds);
		$ch = curl_init('http://10.12.40.16/index.php/dmlapi/inventory/removeorders/');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $orderParam);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		print_r($result);exit;
	}
	//Calculate showroom order product price
	public function calculateProductPrice(Request $request) {
		$params = $request->post();
		$productId = isset($params['product_id']) ? $params['product_id'] : '';
		if (!empty($productId)) {
			$qty = isset($params['qty']) ? $params['qty'] : 0;
			$metalQuality = isset($params['metal_quality']) ? $params['metal_quality'] : '';
			$metalWeight = isset($params['metal_weight']) ? $params['metal_weight'] : '';
			$diamondQuality = isset($params['diamond_quality']) ? $params['diamond_quality'] : '';
			$diamondWeight = isset($params['diamond_weight']) ? $params['diamond_weight'] : '';
			$productCriteria = isset($params['product_criteria']) ? $params['product_criteria'] : '';
			$stoneShapeOption = ShowroomHelper::getStoneShape();
			//Calculate metal price start
			$metalData = (array) InventoryHelper::getMetalData($productId);
			$labourCharge = 0;
			foreach ($metalData as $key => $metal) {
				if ($key == 'labour-charge') {
					$labourCharge = $metal;
					$labourCharge = str_replace('Rs.', '', $labourCharge);
					$labourCharge = str_replace(',', '', $labourCharge);
					$labourCharge = trim($labourCharge);
				}
			}
			DB::setTablePrefix('');
			$perGramMetalRate = 0;
			//get per gram metal rate if exist in catalog flat table
			$dmlProductData = DB::select("SELECT per_gm_rate,belt_price,rts_stone_quality from catalog_product_flat_1 WHERE entity_id = " . $productId . "");
			if (!empty($productId)) {
				$product_metal = DB::select("SELECT * FROM grp_metal WHERE metal_product_id = " . $productId);
			}
			if (!empty($dmlProductData)) {
				$product_metal_data = $product_metal[0];
				if (isset($dmlProductData[0]->per_gm_rate)) {
					$product_per_gm_rate = (float) $dmlProductData[0]->per_gm_rate;
					if ($product_per_gm_rate > 0) {
						$perGramMetalRate = round((float) $product_per_gm_rate);
					}
				}
			}
			$perGramLabourCharge = (float) $labourCharge / (float) $metalWeight;
			$beltPrice = 0;
			$extraPrice = 0;
			if (empty($perGramMetalRate)) {
				$grp_metal_quality_data = DB::select("SELECT * FROM grp_metal_quality WHERE grp_metal_quality_id = " . $product_metal_data->metal_quality_id);
				$perGramMetalRate = round($grp_metal_quality_data[0]->rate);
				$prodOptions = DB::select("SELECT * FROM catalog_product_option WHERE product_id = " . $productId);
				if (!empty($prodOptions)) {
					foreach ($prodOptions as $prodOption) {
						if ($prodOption->type === 'drop_down') {
							$prodValuesData = DB::select("SELECT `tv`.*,`tt`.`title` FROM catalog_product_option_type_value as tv left join catalog_product_option_title as tt on `tv`.`option_id` = `tt`.`option_id` WHERE `tv`.`option_id` = " . $prodOption->option_id);
							if (count($prodValuesData) > 0) {
								$prodValue = $prodValuesData[0];
								//var_dump($prodValue);exit;
								//foreach ($prodValues as $prodValue) {
								if ($prodValue->title == 'RING SIZE') {
									$prodMetalWeight = $prodValue->metal_weight;
								} else {
									$prodMetalWeight = $product_metal_data->metal_weight;
								}
								//}
							} else {
								$prodMetalWeight = $product_metal_data->metal_weight;
							}
						} else {
							$prodMetalWeight = $product_metal_data->metal_weight;
						}
					}

					//var_dump($prodMetalWeight);
				}
				//Get belt price & extra price
				$categoryidsData = DB::select("SELECT `ccp`.*, `ccev`.`value` FROM `catalog_category_product` as `ccp` LEFT JOIN `catalog_category_entity_varchar` as `ccev` ON `ccp`.`category_id` = `ccev`.`entity_id` WHERE `ccp`.`product_id` = " . $productId . " and `ccev`.`attribute_id` = '41'");
				if (count($categoryidsData) > 0) {
					$categoryData = $categoryidsData[0];
					//var_dump($categoryData);
					//var_dump($dmlProductData[0]->belt_price);
					if ($categoryData->category_id == '295' && ($categoryData->value) == 'RUBBER BRACELETS') {
						$actualMetalWeight = $prodMetalWeight;
						$beltPrice = $dmlProductData[0]->belt_price;
						//$extraPrice = $dmlProductData[0]->extra_price;
					}
				}
			}

			$metalPrice = (float) $metalWeight * (float) $perGramMetalRate;
			$metalLabourCharge = (float) $metalWeight * (float) $perGramLabourCharge;

			//echo $metalPrice."  ".$metalLabourCharge."  ".$metalWeight."  ".$perGramMetalRate."---".$labour_charge."  ".$beltPrice."  ".$extraPrice;exit;
			$metalCost = (float) $metalPrice + (float) $metalLabourCharge;
			//Calculate metal price end

			//Calculate diamond price start
			$productStonePrice = array();
			$totalStonePrice = 0;
			$certificate = InventoryHelper::getCertificateNo($productId);
			$isFromDmlProdStone = true;
			DB::setTablePrefix('');
			$product = DB::table('catalog_product_flat_1')->select('entity_id', 'rts_stone_quality')->where('entity_id', '=', DB::raw("$productId"))->get()->first();

			DB::setTablePrefix('dml_');
			if (count($product) > 0) {
				$isFromDmlProdStone = false;
				$stoneQuality = isset($product->rts_stone_quality) ? $product->rts_stone_quality : 'SI-IJ';
				$stoneData = DB::select("SELECT * FROM grp_stone WHERE stone_product_id=" . $productId . "");
			} else {
				$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->select('id', 'rts_stone_quality', 'certificate_no')->where('certificate_no', '=', DB::raw("'$certificate'"))->get()->first();
				$stoneQuality = isset($dmlProductData->rts_stone_quality) ? $dmlProductData->rts_stone_quality : 'SI-IJ';
				$dmlProductId = isset($dmlProductData->id) ? $dmlProductData->id : '';
				$stoneData = ProductsStone::select('*')->where('stone_product_id', '=', DB::raw("'$dmlProductId'"))->get();
			}
			foreach ($stoneData as $key => $stone) {
				$carat = isset($stone->carat) ? $stone->carat : 0;
				$stoneUse = isset($stone->stone_use) ? $stone->stone_use : 0;
				if ($isFromDmlProdStone) {
					$stoneTotalCarat = $carat;
				} else {
					$stoneTotalCarat = $stone->total_carat;
					$stoneTotalCarat = number_format(floatval(($stoneTotalCarat / $stone->stone_use)), 3);
				}
				$modelSideStone = DB::select("SELECT `main_table`.* FROM `grp_stone_manage` AS `main_table` WHERE (stone_shape = " . $stone->stone_shape . ") AND (stone_clarity = '" . $diamondQuality . "') AND (stone_carat_from <= " . $stoneTotalCarat . ") AND (stone_carat_to >= " . $stoneTotalCarat . ")");
				if (count($modelSideStone) > 0) {
					foreach ($modelSideStone as $key => $modelStone) {
						$stone_price = round($modelStone->stone_price);
					}
					$stoneCarat = number_format(floatval(($stone->carat)), 3);
					$productSideStonePrice = round($stone_price * $stone->total_carat);
					$totalStonePrice += $productSideStonePrice;
					$productStonePrice[] = $productSideStonePrice;
				}
			}
			//Calculate diamond price end

			//Calculate gem stone price
			$gemStoneData = InventoryHelper::getGemStoneData($productId);
			$gemStonePrice = isset($gemStoneData['stone_price']) ? array_sum($gemStoneData['stone_price']) : 0;

			$productPriceMarkup = (float) InventoryHelper::getProductAttribute($productId, 'price_markup');
			$unitPrice = (float) $metalCost + (float) $totalStonePrice + (float) $productPriceMarkup + (float) $beltPrice + (float) $extraPrice + (float) $gemStonePrice;

			DB::setTablePrefix('');
			$response['status'] = true;
			$response['unit_price'] = round((float) $unitPrice * $qty);
			$response['diamond_price'] = json_encode($productStonePrice);
		} else {
			$response['status'] = false;
		}
		echo json_encode($response);exit;
	}
	public function testdata() {
		$stoneData = InventoryHelper::getStoneData(1432381);
		$metalData = (array) InventoryHelper::getMetalData(1432381);
		echo "<pre>";
		print_r($metalData);exit;
	}
	public function insertfrncode() {
		$all_gstin_arr = array('24AZKPM0711B1Z0', '27ARVPJ4010B1Z2', '27AFOPJ1646N1ZX', '27AAIPS1582K1Z7', '27AFXPJ4859E1ZU', '27AAMPK9461H1ZB', '27ABPFA1005D1ZA', '27ACSFS2060E1ZO', '27AAHPJ9628G1ZF', '27ANXPS4780K1ZV', '27AEGPJ7552Q1ZS', '27AYJJI702HIZN', '07AAFFI7775P1ZI', '27AACPJ1576C1Z1', '27AAAPA5882G1ZV', '27BEKPS6401L1Z2', '27AAYPM9177A1Z3', '27AFSPJ6881R1Z6', '27AORPD1602C1ZM', '27AABPJ3349L1ZK', '27AFDPJ2582G1ZI', '27AKZPD5361J1ZL', '27AAEPJ2418D1Z6', '27ACZPJO916H1ZA', '27AABPB2074L1ZW', '27ARJPJ3787B1ZO', '27AAACD8748D1ZM', '27ADMFS7924E1ZP', '27AQSPM5312M1ZA', '27ABZPV0683J1ZN', '24AAPPL5232N1ZC', '27ACXPJ9920F1Z2', '27AADPP8668E1ZB', '27AAHPR0945G1ZL', '27ABAPS8363F1ZF', '27AAEPJ2418D1Z6', '27AEKPM4442G1ZF', '27AAAPJ5433A1ZB', '27AABPB5937E1Z1', '27AADPP4938M1Z5', '27BCQPP2203M1Z9', '27AAAFJ2684F1ZF', '27CCRPM8271L1ZO', '27ADIPJ1585E1ZK', '27AABPJ3534F1Z2', '27BHQPM7646L1ZF', '27AXSPR4745C1Z1', '24ABUPJ7510K1ZC', '27AMZPJ9687M1ZK', '27AAOPJ7330E1ZP', '27AEVPK2751F1Z8', '27AAFCK3159G1ZH', '27AATPJ2970P1ZT', '24AAPFK5014MIZ4', '07AADCK5276E1ZJ', '27AACPB6195K1ZJ', '27AAFCK1266J1ZE', '27ABBPP4735D1ZT', '27ASOPJ7683A1ZG', '27ANGPB2432C1ZP', '27APCPJ8279F1ZM', '27AFLPR5894P1Z6', '27ACBPG2191H1ZU', '27ACOPR4122L1Z6', '27AKGPJ9881J1ZH', '27AACPS8377A1ZJ', '27BXCPK1581N2Z3', '27ABIPJ2911J1ZP', '27AABPC0064F1ZE', '27ABAPS7713H1ZJ', '27AFBPB0184Q1ZD', '27ALXPP8881E1Z5', '27AGUPP3358G1ZT', '27AMQPS0779L1Z3', '27AAVPS3103M1Z5', '27AAMFM2290E1Z8', '27ADLPC1878N1Z1', '24CCPPS5938A1ZD', '27ADIPJ9504B1ZR', '27BJBPS6026H1Z4', '27AAJPJ5002E1Z5', '27ANGPS7786Q1ZO', '27AELPJ8393N1ZK', '27AAIPJ6488P1ZR', '27AADPJ1945NIZO', '27AAUFM6621P1ZE', '27AACPJ5992N1Z2', '27AGBPJ5795J1Z4', '27AAFFM5521R1ZS', '27AAXFM8809Q1ZZ', '27AECPD1290D1Z2', '27AACPJ6648P1Z3', '27AJNPS9312D1ZY', '27AABPI0399B1Z2', '24AAFF08823J1Z1', '08AILPK5856F1ZX', '27AJCPJ4577G1Z3', '27AABPB4381L1ZQ', '27AEWPJ6392L1ZI', '27AESPJ5762J2ZT', '27AAAPJ6943R1Z3', '27AAIPS3850L1Z6', '27AAXPT2624O1ZF', '27AAIPJ4686M1Z1', '27ACNPJ9619F1Z8', '27AAXPK5671Q1ZI', '27AIUPK3096N1ZB', '27AARPJ2451R1ZZ', '27AABPC7425J1ZU', '27AACPJ0595K1ZJ', '27AFJPB8338N1ZZ', '27AABFP9000A1ZU', '27AARPJ2348F1ZK', '27AACPS4696J1Z2', '27AGMPP1566G1Z3', '27ASVPR2428J1Z1', '27ACWPC3926H1ZC', '27AADPJ2083R1Z9', '36AICPR4060M1ZZ', '27AAPFR5003H1Z5', '27AEWPB2504G1ZN', '27AATPJ4660L1Z2', '27ANNPJ9334N1Z8', '27AABPJ6589Q1ZU', '27AABPJ0855B1Z8', '27ACKPS5031M1Z5', '24ALTPS2327N1ZG', '27AABHH1077B1ZR', '27ADQPA5308Q1Z3', '27AFFPP5141P1ZY', '27AAIPJ5416D1ZY', '27AAHPK4229J1ZK', '27ABUPL7744B1ZB', '27AAEPJ2418D1Z6', '27ACWPJ6509M1ZT', '24AFUPS6691G1ZO', '27AF2PP5584N1Z4', '27AAJPC5504E1Z5', '27AAAPJ6533D1Z2', '27ABNFS6698G1ZB', '27BAZPP1674D1Z6', '27BTVPK9826KLZN', '27AWQPS1288L1ZL', '27AVXPD2407L1Z8', '27ACYFS9983L1ZL', '27AACPJ0413C1ZJ', '27AHGPM2177D1ZF', '27AANPV7641K1ZV', '27ALOPT6272G1ZH', '27AJGPC3721R1ZZ', '27AJMPM6010D1ZG', '27AALFS2463C1Z8', '24CJVPS0831D1Z4', '27AECPJ0956N1Z8', '27AAHCS0742F1Z1', '27AVHPJ1571B1ZW', '27AODPS7641Q1Z3', '24ADLFS3587N1ZA', '27AACPJ9408P1Z7', '27CENPK9011E1ZH', '27AAFCS3082A1ZN', '27ACJFS4148J1ZP', '27AAFHB8342B1ZN', '27ABFFS0635R1ZN', '27AFXPS8870E1ZI', '27AKEPJ1847N1ZT', '27AIQPJ1292D127', '27AZVPJ2060P1ZN', '27AGAPC3702B1ZC', '27ACWPP0095J1ZW', '27AACCV0198D1ZE', '27AAECV7621H1Z6', '27AFEPJ6552P1ZW', '27AAIFV3669G1ZQ', '27ALGPP0853P2ZI', '27AVUPS1812RIZL', '27AAPPT2497K1ZR', '27AALPM7290F1ZC', '27AGTPS2202H1Z8', '27AOSPS6419P1ZS', '27AABPJ4873K1ZH', '27AACPC0530B1ZQ', '27ADQPJ3122KIZE', '27BVKPP8293A1Z7', '27AABCJ670A1ZZ', '27CDPPR2596E2ZV', '27AAKCS4947D1Z4', '27ANKPJ9441K1ZH', '27AAQFKH132P1ZQ', '27ABRPJ1604P1Z8', '27AAKFM6277F1ZX', '27AJGPJ8270L1ZQ', '27AKMPJ8683Q1ZZ', '27BBOPS6832A1ZG', '27AGDPJ0768P1ZX', '27ANJPC222G11ZB', '27BPRPK8294R1ZH', '27APEPM7859J1Z8', '27AABPJ5838L1ZE', '27AAJPS2605L1ZE', '27AHLPR3027NIZ7', '27AADCM3118KAZH', '27AEZPC5175M1ZP', '27ACKPJ5789MIZP', '27AESPU8693J1ZI', '27AARC50362HIZ4', '27BOWPM1646D1ZN', '27AAECD0783A1Z1', '27ADPK9375Q1Z2', '27ADIPJ4699K1ZU', '27AADPP7299M1ZT', '27AABPK0400DIZM', '27AXAPS0217J1ZK', '27BORPS0300K1ZK', '27AAFFO8260R1ZG', '27AQSPS6627H1Z3', '27AAGPM4692L1Z4', '24AAJFS9305F1Z7', '27AEHPR0981A1ZL', '27AHHPM6963A1ZA', '27APEPK1717R1ZH', '27ACZPJ9162B1Z6', '27ADCPJ7993G1Z6', '27AACPJ3912E1Z5', '27BEKPR4932K1ZX', '27AIQPJ1292D1Z7', '27ADPFS5370M1Z9', '27AEWPJ1052M1Z1', '27AAGPS9819P1ZN', '27AACPJ7134J1Z0', '27AHPPM6779C1ZS', '27AAEPK2376B120', '27AACPJ2148L1ZO', '27AACPJ8661J1ZE', '27AAPPI6102A1Z5', '27AAEPJ8283C1ZP', '27AA1PJ3874D1ZO', '27AAOPJ7351D1ZM', '27AAAPZ1045J1ZK', '27AABPP2635F', '27AAIPS3305C1ZZ', '27ASEPK7323C1ZO', '24ABWPB2758DIZP', '27AANFD5618E1ZE', '27BBNPD4612G1ZU', '27BIAPP324Q12V', '27AXLPP2158A1ZJ', '27AABPN2710R1ZH', '27AICPJ4852H1Z9', '27AACCK9744J1Z2', '27DPM3117F1Z2', '27AADPK6684P1ZX', '27AAHPL143M1Z9', '27AABHB2113G1Z0', '27AHLPR0499Q1Z9', '27AAAPL2860M1Z', '27AAIPS99QOP12V', '27AEYPJ9198N1Z2', '24CTVPP7339B1ZA', '24AABFZ1957C1ZC', '24AADCA5068L', '24AAHFG9807G1ZC', '27AHJPT3373R1ZC', '27AANPI909P1ZV', '27AGPPL4076OIZK', '27ANYPS4932LIZY', '24AOSPS7658J1ZZ', '24ALKPP0551E1ZD', '24BZLPS9422D1Z6', '24ADQFS8386N1ZY', '24ABHFS0910K1ZB', '24ABPFS4419M1ZN', '24AALFV6172D1Z3', '27AGFPK301OK12N', '27AHEPB1162D1Z1', '27ACFPJ1043B1AZ', '27AJMPJ3J1JJ1Z4', '27ADNPJ9990J1ZN', '27AISP5521OQ1ZF', '27AANFK0766G1Z4', '27AFZPK5035K1ZS', '27BEUPK6116G1Z7', '27AATPJ46241ZA', '27JAPMPB7594R1ZV', '27JAAACY1306L1ZC', '27AOTPB9215R1Z4', '27ACMPV7501P1ZR', '27AAICC0803C1Z9', '27AGZPK5104E1Z9', '27ADEPJ8352B1ZJ', '24AAOFC3224E1ZU', '27FHAPS5150P1ZQ', '27AGZPJ8020E1Z5', '27AFRPJ2528J1Z3', '27ABJPJ5056B1ZV', '24AAACZ2842E1ZL', '27AIVPR2576B1ZT', '27ADHPJ1026B1Z7', '27AAPFA2853C1ZK', '27AENPJ4328P1ZV', '27AAMFD1528J1ZB', '27ACUP7948N1Z2', '27AAAPJ9347H1ZK', '24AIQPS0458J1ZT', '27ABVPJ6530G1ZC', '27AABPJ6953A1ZZ', '27AJVPC756R1Z3', '27AASFS9146H1ZH', '27AGQPR6715D1Z2', '27AGMPP1567H1Z0', '27AVEFPK5770K1Z5', '08AADCK5276E1ZH', '27AADCK5276E1ZH', '27AALPD3571G1ZR', '27AALFA5325K1ZA', '27AFDP7848D1ZD', '27ALEPJ5959NIZE', '27AABPD8164D1ZO', '27AVKPS6304G1ZD', '27AFKPJ9589G1ZQ', '27AFVPJ8650H1ZT', '27AMFPB7072C1ZE', '27AOKPD4585R2Z3', '27ADJPJ0244R1ZA', '27CIDPM9871Q1Z7', '27AARPT2364D1Z0', '27AABPJ6569L1Z8', '27AOTPK5779H1Z3', '27AALFM4270H1Z3', '27AADPK6903DZO', '27AACPD2023L1Z4', '27AAEPJ5645G1ZP', '27AATPJ3075F1ZG', '27AAYPS4353A1ZD', '27AACPJ7348A1ZZ', '27ANOPS8628R1ZN', '27AOLPJ3486F1ZR', '27AANFP1489P1ZA', '27AATPJ1418L1ZC', '27AANFB7764R1ZE', '27ACCPS1781JIZA', '27ABKPJ4785CIZI', '27AABFP4075B1ZJ', '27AAFPK3486C1ZT', '27BCIPS3326J1ZA', '27AAWPJ7863N1ZK', '27BHVPK4877L1Z9', '27AAFFP2478N1ZN', '27AIGPB0284P1Z2', '27AXMPP5980D1ZO', '27AACPJ1710G1Z9', '27ACTPS3284M1ZL', '27AARPJ2344K1ZD', '27AAMFT4292M1ZE', '27AFGPC4617J1ZJ', '27AETPB2756H1ZA', '27AAVFP9518R1ZW', '27AEBPT0781L1Z4', '27AAGFG0646N1Z5', '27AAAPP7155R1ZZ', '27ALNPD3448E1ZP', '27ADCPJ7375C1ZM', '27ALQPR9157G1ZB', '27ADGPJ1194F1ZN', '27AABPN0392H1ZS', '27AFSPK8905K1ZQ', '27AAKPS5484Q1ZK', '27AYZPS5483M1ZW', '27AACCE5828R1ZZ', '27AARPJ2242A1Z1', '27AFPPJ6993K1ZI', '27AACPJ1440R1ZJ', '27AAAPD8567B1ZY', '27AUSPK8332M1ZU', '27ABAPY3408F1ZH', '27AFTPT0246D1Z9', '27AAEPD1445N1ZQ', '27AAEPS5987N1ZO', '27AEUPJ9581R23', '27AARPJ2241D1ZW', '27AKI997097Z1Z0', '27AGBPM7956Q1ZI', '27ADRPJ7999K1ZC', '27ACKPJ6567B1Z1', '27ABJPJ5070D1ZT', '27AAAFD9148G1ZE', '27AAFFO8260R1ZG', '27AACCJ7810N1Z8', '27BVZPS8063C1ZT', '24ABHFA2110P1ZN', '27ADFPJ9233L1Z7', '27AABHL3585J1ZX', '27AAFF0848941Z0', '27AADPO9366N1ZW', '27AAEPB8082H2ZP', '27AADCP5420B1ZW', '27AARCS8221R1ZD', '27AAIFD7261N1ZX', '27AFDPJ9082P1Z8', '27ABJPJ179K1Z4', '27AASFP4562A1Z4', '27AAAPL843A1Z3', '27AACPJ6959M1Z3', '27AAHFLI762O1Z', '27AFBPJ9829CIZ9', '27ADMPJ5625M2Z', '27ABKFM4617Q1ZK', '27AEWPL3799PL23', '27AIEPB6636I21ZY', '27ADPPJ0840D1ZR', '27ACBPP626B1ZQ', '27AVOPS9082J1ZN', '27ACGPB2493N1ZC', '27AABFS2290R1ZM', '27ABAFR329G1Z3', '27AAZPJ6681P1ZF', '27ADAPJ2993A1ZU', '27AFWPJ4603BIZJ', '27A01PL7622N2Z1', '27ADHPJ6431G1ZM', '24ABJFM8082Z1Z3', '27AATFP2939A1Z2', '27AYCPK5218L1Z9', '27CZYPK6882K1Z5', '27AATPJ3093R1ZP', '32AOMPJ458N1ZE', '27ABVFS5429H1ZI', '27AB0PJ753E1ZE', '27AAEPJ9856H1ZA', '27ADLPR4102NZ7', '27AAAFM2032J1ZL', '07AADCK6975C1ZF', '27AADPS8182Q1ZQ', '27AFDPJ2614K1ZK', '27ACTPS3275J1ZS', '27AONPS9414FZH', '27AAEPS4386F1ZE', '27AADPP5298C1ZJ', '27ACQFS8823H1ZH', '27CWNPS1356NIZP', '27ATYPM6C636P1ZE', '27ADVPJ0329H1ZD', 'CHJPS2667B', '27ACLPCS711D1Z0\/ACLPCS11D', '27AADFR4176PIZK\/AADFR4176P', '27BEYPK6185D1ZW', '27ANBPP2558A1Z9', '27AABPS6901M1ZD');

		$all_frn_codes = array('FRN-5', 'FRN-6', 'FRN-7', 'FRN-8', 'FRN-9', 'FRN-10', 'FRN-11', 'FRN-12', 'FRN-13', 'FRN-14', 'FRN-16', 'FRN-17', 'FRN-21', 'FRN-22', 'FRN-23', 'FRN-24', 'FRN-25', 'FRN-26', 'FRN-27', 'FRN-28', 'FRN-31', 'FRN-32', 'FRN-33', 'FRN-36', 'FRN-37', 'FRN-39', 'FRN-40', 'FRN-41', 'FRN-42', 'FRN-43', 'FRN-44', 'FRN-45', 'FRN-47', 'FRN-50', 'FRN-51', 'FRN-52', 'FRN-53', 'FRN-54', 'FRN-55', 'FRN-56', 'FRN-58', 'FRN-59', 'FRN-60', 'FRN-61', 'FRN-62', 'FRN-65', 'FRN-66', 'FRN-68', 'FRN-69', 'FRN-70', 'FRN-71', 'FRN-72', 'FRN-73', 'FRN-75', 'FRN-76', 'FRN-77', 'FRN-78', 'FRN-79', 'FRN-80', 'FRN-83', 'FRN-84', 'FRN-85', 'FRN-86', 'FRN-87', 'FRN-88', 'FRN-89', 'FRN-90', 'FRN-92', 'FRN-93', 'FRN-94', 'FRN-97', 'FRN-98', 'FRN-99', 'FRN-100', 'FRN-101', 'FRN-103', 'FRN-104', 'FRN-105', 'FRN-106', 'FRN-107', 'FRN-108', 'FRN-111', 'FRN-112', 'FRN-113', 'FRN-116', 'FRN-117', 'FRN-121', 'FRN-124', 'FRN-125', 'FRN-126', 'FRN-127', 'FRN-129', 'FRN-130', 'FRN-131', 'FRN-135', 'FRN-137', 'FRN-138', 'FRN-141', 'FRN-142', 'FRN-144', 'FRN-145', 'FRN-146', 'FRN-149', 'FRN-150', 'FRN-151', 'FRN-152', 'FRN-153', 'FRN-154', 'FRN-156', 'FRN-157', 'FRN-158', 'FRN-160', 'FRN-161', 'FRN-163', 'FRN-164', 'FRN-166', 'FRN-168', 'FRN-169', 'FRN-170', 'FRN-173', 'FRN-174', 'FRN-175', 'FRN-177', 'FRN-178', 'FRN-181', 'FRN-182', 'FRN-183', 'FRN-185', 'FRN-187', 'FRN-188', 'FRN-190', 'FRN-193', 'FRN-194', 'FRN-196', 'FRN-197', 'FRN-198', 'FRN-199', 'FRN-200', 'FRN-203', 'FRN-204', 'FRN-205', 'FRN-206', 'FRN-210', 'FRN-211', 'FRN-214', 'FRN-215', 'FRN-216', 'FRN-219', 'FRN-220', 'FRN-222', 'FRN-224', 'FRN-225', 'FRN-226', 'FRN-229', 'FRN-230', 'FRN-231', 'FRN-232', 'FRN-233', 'FRN-234', 'FRN-236', 'FRN-237', 'FRN-242', 'FRN-243', 'FRN-244', 'FRN-247', 'FRN-248', 'FRN-251', 'FRN-259', 'FRN-261', 'FRN-263', 'FRN-264', 'FRN-266', 'FRN-267', 'FRN-268', 'FRN-271', 'FRN-272', 'FRN-274', 'FRN-277', 'FRN-96', 'FRN-239', 'FRN-278', 'FRN-279', 'FRN-280', 'FRN-281', 'FRN-282', 'FRN-283', 'FRN-284', 'FRN-285', 'FRN-290', 'FRN-291', 'FRN-292', 'FRN-293', 'FRN-294', 'FRN-295', 'FRN-296', 'FRN-299', 'FRN-300', 'FRN-302', 'FRN-303', 'FRN-304', 'FRN-305', 'FRN-306', 'FRN-307', 'FRN-297', 'FRN-288', 'FRN-309', 'FRN-310', 'FRN-311', 'FRN-312', 'FRN-314', 'FRN-315', 'FRN-317', 'FRN-318', 'FRN-320', 'FRN-322', 'FRN-325', 'FRN-326', 'FRN-328', 'FRN-329', 'FRN-330', 'FRN-331', 'FRN-332', 'FRN-333', 'FRN-334', 'FRN-335', 'FRN-336', 'FRN-337', 'FRN-338', 'FRN-340', 'FRN-341', 'FRN-342', 'FRN-343', 'FRN-345', 'FRN-346', 'FRN-347', 'FRN-348', 'FRN-349', 'FRN-350', 'FRN-351', 'FRN-352', 'FRN-353', 'FRN-355', 'FRN-358', 'FRN-359', 'FRN-360', 'FRN-361', 'FRN-362', 'FRN-363', 'FRN-364', 'FRN-365', 'FRN-366', 'FRN-370', 'FRN-371', 'FRN-372', 'FRN-373', 'FRN-374', 'FRN-375', 'FRN-378', 'FRN-379', 'FRN-382', 'FRN-383', 'FRN-384', 'FRN-386', 'FRN-387', 'FRN-388', 'FRN-389', 'FRN-392', 'FRN-393', 'FRN-394', 'FRN-395', 'FRN-396', 'FRN-397', 'FRN-398', 'FRN-399', 'FRN-400', 'FRN-401', 'FRN-403', 'FRN-404', 'FRN-405', 'FRN-406', 'FRN-407', 'FRN-408', 'FRN-409', 'FRN-410', 'FRN-411', 'FRN-412', 'FRN-413', 'FRN-414', 'FRN-415', 'FRN-416', 'FRN-417', 'FRN-418', 'FRN-419', 'FRN-420', 'FRN-421', 'FRN-423', 'FRN-424', 'FRN-425', 'FRN-430', 'FRN-432', 'FRN-433', 'FRN-434', 'FRN-435', 'FRN-436', 'FRN-437', 'FRN-439', 'FRN-440', 'FRN-442', 'FRN-443', 'FRN-444', 'FRN-445', 'FRN-447', 'FRN-448', 'FRN-449', 'FRN-450', 'FRN-451', 'FRN-453', 'FRN-457', 'FRN-458', 'FRN-459', 'FRN-461', 'FRN-462', 'FRN-463', 'FRN-464', 'FRN-465', 'FRN-468', 'FRN-470', 'FRN-471', 'FRN-472', 'FRN-473', 'FRN-474', 'FRN-475', 'FRN-476', 'FRN-477', 'FRN-478', 'FRN-480', 'FRN-481', 'FRN-482', 'FRN-483', 'FRN-484', 'FRN-485', 'FRN-486', 'FRN-487', 'FRN-488', 'FRN-489', 'FRN-490', 'FRN-491', 'FRN-492', 'FRN-493', 'FRN-494', 'FRN-495', 'FRN-497', 'FRN-498', 'FRN-499', 'FRN-500', 'FRN-501', 'FRN-502', 'FRN-503', 'FRN-504', 'FRN-505', 'FRN-506', 'FRN-507', 'FRN-508', 'FRN-509', 'FRN-510', 'FRN-511', 'FRN-512', 'FRN-513', 'FRN-514', 'FRN-515', 'FRN-516', 'FRN-517', 'FRN-518', 'FRN-519', 'FRN-520', 'FRN-521', 'FRN-522', 'FRN-523', 'FRN-524', 'FRN-525', 'FRN-532', 'FRN-533', 'FRN-534', 'FRN-535', 'FRN-536', 'FRN-537', 'FRN-538', 'FRN-539', 'FRN-540', 'FRN-542', 'FRN-543', 'FRN-544', 'FRN-547', 'FRN-549', 'FRN-550', 'FRN-552', 'FRN-553', 'FRN-555', 'FRN-556', 'FRN-557', 'FRN-558', 'FRN-559', 'FRN-560', 'FRN-561', 'FRN-562', 'FRN-563', 'FRN-565', 'FRN-566', 'FRN-567', 'FRN-568', 'FRN-569', 'FRN-570', 'FRN-571', 'FRN-573', 'FRN-574', 'FRN-575', 'FRN-576', 'FRN-577', 'FRN-578', 'FRN-580', 'FRN-582', 'FRN-583', 'FRN-584', 'FRN-585', 'FRN-586', 'FRN-589', 'FRN-590', 'FRN-591', 'FRN-592', 'FRN-593', 'FRN-594', 'FRN-595', 'FRN-596', 'FRN-597', 'FRN-600', 'FRN-601', 'FRN-602', 'FRN-603', 'FRN-604', 'FRN-605', 'FRN-606', 'FRN-607', 'FRN-608', 'FRN-609', 'FRN-610', 'FRN-611', 'FRN-612', 'FRN-613');

		$frn_counter = 0;

		foreach ($all_gstin_arr as $gstkey => $gstin) {
			$get_this_customer_entity_id = "select entity_id from customer_entity_varchar where attribute_id = 335 AND value = '" . $gstin . "'";

			$entities = DB::select($get_this_customer_entity_id);
			if (count($entities) > 0) {
				foreach ($entities as $entity) {
					$entity_id = $entity->entity_id;
					$frn_code = $all_frn_codes[$gstkey];

					$get_this_customer_exist_data = "select value_id from customer_entity_varchar where attribute_id = 383 AND entity_id = '" . $entity_id . "'";

					$exists = DB::select($get_this_customer_exist_data);
					if (count($exists) > 0) {
						$val_id = $exists[0]->value_id;
						$update_frn_query = "UPDATE customer_entity_varchar SET value = '" . $frn_code . "' WHERE value_id = " . $val_id . ";";
						$frn_counter++;
						echo $frn_counter . ' | ' . $update_frn_query . '<br>';
						$updatefrncode = DB::statement($update_frn_query);
					} else {
						$insert_frn_query = "INSERT INTO customer_entity_varchar VALUES (null, 1, 383, " . $entity_id . ", '" . $frn_code . "');";
						$frn_counter++;
						echo $frn_counter . ' | ' . $insert_frn_query . '<br>';
						$insertfrncode = DB::statement($insert_frn_query);
					}
				}
			}
		}
		exit;

	}
}
