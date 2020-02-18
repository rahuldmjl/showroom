<?php
namespace App\Helpers;

use App;
use App\QrcodeScanning;
use App\User;
use Auth;
use Config;
use DB;
use Illuminate\Support\Facades\Cache;
use URL;

class ShowroomHelper {
	//get min & max price for filer
	public static function getMinMaxPriceForFilter() {
		/*DB::setTablePrefix('');
			$price = DB::select("SELECT MIN(`e`.`custom_price`) AS min_price, MAX(`e`.`custom_price`) AS max_price FROM `catalog_product_flat_1` AS `e` LEFT JOIN `franchises_order` AS `fo` ON e.entity_id = fo.product_id LEFT JOIN `grp_metal` AS `metal` ON e.entity_id = metal.metal_product_id LEFT JOIN `grp_stone` AS `stone` ON e.entity_id = stone.stone_product_id INNER JOIN `catalog_product_index_price` AS `price_index` ON price_index.entity_id = e.entity_id AND price_index.website_id = '1' AND price_index.customer_group_id = '4' WHERE (e.status = 1) AND `e`.`custom_price` != '' LIMIT 1");
			DB::setTablePrefix('dml_');
		*/

		$return_array = array();

		$prod = ShowroomHelper::getAllProductsCollection();
		//dd($prod);exit;

		$min_price = $prod->pluck(['custom_price'])->min();
		$max_price = $prod->pluck(['custom_price'])->max();

		$return_array['min_price'] = $min_price;
		$return_array['max_price'] = $max_price;

		return $return_array;

	}
	//Get RTS products
	public static function getProducts($count = false, $productIds = false) {
		DB::setTablePrefix('');
		$collection = ShowroomHelper::getAllProductsCollection($count, $productIds);
		$productCollection = $collection->take(10);
		$productData = array('totalCount' => $collection->count(), 'productCollection' => $productCollection);
		DB::setTablePrefix('dml_');
		return $productData;
	}
	public static function getShowroomProcessProducts($post_products)
	{
		$productCollection = ShowroomHelper::getShowroomProcessProductCollection();
		$productCollection = $productCollection->whereIn('entity_id',$post_products);
		$productCollection = $productCollection->take(10);
		$productData = array('totalCount' => $productCollection->count(), 'productCollection' => $productCollection);
		return $productData;
	}
	//Get showroom processed order products
	public static function getShowroomProcessProductCollection()
	{
		$productCollection = '';
		$prod = '';
		DB::setTablePrefix('');

		$all_products = Cache::get('all_showroom_process_products', '');
		
		if (!empty($all_products)) {
			return $all_products;
		}

		//$prod = ;
		$prod = DB::table('catalog_product_flat_1')->select('t.entity_id', 't.belt_price', 't.type_id', 't.attribute_set_id', 't.name', 't.sku', 't.rts_bangle_size', 't.category_id', 't.shapes', 't.qualities_ids', 't.qualities', 't.roundshape', 't.rts_stone_quality', 't.rts_bracelet_size', 't.rts_ring_size', 't.certificate_no', 't.custom_price', 't.franchises_id', 't.type', 't.product_id', DB::raw("CASE WHEN(IFNULL(t.product_id, 'DML')AND t.TYPE = '3') THEN 'FRANCHISE SOLD' WHEN(IFNULL(t.product_id, 'DML') AND t.TYPE = '0') THEN 'FRANCHISE INSTOCK' WHEN(t.product_id = '' AND t.is_sold = 1) THEN 'DML SOLD' ELSE 'DML INSTOCK' END AS stockstatus"), 't.metal_quality_id', 't.metal_weight', DB::raw("IF(t.metal_quality_id = '2879' OR t.metal_quality_id = '2878' OR t.metal_quality_id = '2880' OR t.metal_quality_id = '3170' OR t.metal_quality_id = '3120' OR t.metal_quality_id = '2900' OR t.metal_quality_id = '3091',((t.metal_weight * 85) / 100),t.metal_weight) AS metal_weight_14k"), DB::raw("IF(t.metal_quality_id = '2877' OR t.metal_quality_id = '2875' OR t.metal_quality_id = '2901' OR t.metal_quality_id = '3119' OR t.metal_quality_id = '2876',((t.metal_weight * 100) / 85),t.metal_weight) AS metal_weight_18k"), DB::raw("CAST(t.total_diamond_weight AS DECIMAL(10, 2)) AS total_diamond_weight"), DB::raw("IF(t.metal_quality_id = '2879' OR t.metal_quality_id = '2878' OR t.metal_quality_id = '2880' OR t.metal_quality_id = '3170' OR t.metal_quality_id = '3120' OR t.metal_quality_id = '2900' OR t.metal_quality_id = '3091',((t.metal_weight * 85) / 100),t.metal_weight) / CAST(t.total_diamond_weight AS DECIMAL(10, 2)) AS ratio14k"), DB::raw("IF(t.metal_quality_id = '2877' OR t.metal_quality_id = '2875' OR t.metal_quality_id = '2901' OR t.metal_quality_id = '3119' OR t.metal_quality_id = '2876',((t.metal_weight * 100) / 85),t.metal_weight) / CAST(t.total_diamond_weight AS DECIMAL(10, 2)) AS ratio18k"), 't.price AS indexed_price', 't.price', 't.final_price', DB::raw("IF(t.tier_price,LEAST(t.min_price, t.tier_price),t.min_price) AS minimal_price"), 't.min_price', 't.max_price', 't.tier_price')
			->fromSub(function ($query) {
				$query->from('catalog_product_flat_1 as e')->select('e.entity_id', 'e.belt_price', 'e.type_id', 'e.attribute_set_id', 'e.name', 'e.sku', 'e.rts_bangle_size', 'e.rts_stone_quality', 'e.rts_bracelet_size', 'e.rts_ring_size', 'e.certificate_no', 'e.custom_price', 'catalog_category_product.category_id', 'fo.franchises_id', 'fo.type', 'fo.product_id', 'e.is_sold', 'shapes', 'qualities_ids', 'qualities', DB::raw('roundshape'), 'metal.metal_quality_id', 'metal.metal_weight', 'diamond_type.total_diamond_weight', 'price_index.price', 'price_index.final_price', 'price_index.min_price', 'price_index.tier_price', 'price_index.max_price')
					->join('catalog_category_product', 'e.entity_id', '=', 'catalog_category_product.product_id')
					->leftJoin('franchises_order as fo', 'e.entity_id', '=', 'fo.product_id')
					->leftJoin('grp_metal as metal', 'e.entity_id', '=', 'metal.metal_product_id')
				//->leftJoin('grp_stone as stone', 'e.entity_id', '=', 'stone.stone_product_id')
				/*->leftJoin(DB::raw("(SELECT e.*, grp_stone.stone_product_id, SUM(grp_stone.total_carat) AS total_diamond_weight FROM catalog_product_flat_1 AS e LEFT JOIN grp_stone ON e.entity_id = grp_stone.stone_product_id WHERE (e.status = 1) AND(e.isreadytoship = 1) AND(e.type_id = 'simple') GROUP BY e.entity_id) AS stone"),
						function ($join) {
							$join->on('stone.stone_product_id', '=', 'e.entity_id');
						})*/
					->rightJoin(DB::raw("(SELECT e.*,SUM(grp_stone.total_carat) AS total_diamond_weight, GROUP_CONCAT(DISTINCT grp_stone.stone_shape) AS shapes, FIND_IN_SET('36', GROUP_CONCAT(DISTINCT grp_stone.stone_shape)) AS roundshape, GROUP_CONCAT(DISTINCT grp_stone.stone_clarity) AS qualities_ids, GROUP_CONCAT(DISTINCT EA.value) AS qualities FROM catalog_product_flat_1 AS e LEFT JOIN grp_stone ON e.entity_id = grp_stone.stone_product_id LEFT JOIN (SELECT option_id,value FROM `eav_attribute_option_value` WHERE option_id IN (SELECT option_id FROM `eav_attribute_option` WHERE `attribute_id` = (SELECT attribute_id FROM `eav_attribute` WHERE `attribute_code` = 'stone_clarity' ))) EA ON EA.option_id = grp_stone.stone_clarity WHERE  (e.status = 1) AND(e.isreadytoship = 1) AND(e.type_id = 'simple') GROUP BY e.entity_id) AS diamond_type"),
						function ($join) {
							$join->on('diamond_type.entity_id', '=', 'e.entity_id');
						})
					->join('catalog_product_index_price as price_index', function ($join) {$join->on('e.entity_id', '=', 'price_index.entity_id')->where('price_index.website_id', '=', DB::raw("'1'"))->where('price_index.customer_group_id', '=', DB::raw("'4'"));})
					->where('e.status', '=', DB::raw('1'))
					->where('e.isreadytoship', '=', DB::raw('1'))
					->where('e.custom_price', '!=', DB::raw('""'));
			}, 't')
			->groupBy('t.entity_id')
			->orderBy('t.entity_id', 'desc');
		//echo $prod->toSql();exit;
		//echo $prod->toSql();exit;

		$collection = collect($prod->get());

		//echo '<pre>';
		//print_r($collection);exit;

		Cache::put('all_showroom_process_products', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));

		DB::setTablePrefix('dml_');
		return $collection;
	}

	// Get All Products
	public static function getAllProductsCollection($count = false, $productIds = false) {
		$productCollection = '';
		$prod = '';
		DB::setTablePrefix('');

		$all_products = Cache::get('all_showroom_products', '');

		if (!empty($all_products)) {
			return $all_products;
		}

		//$prod = ;
		$prod = DB::table('catalog_product_flat_1')->select('t.entity_id', 't.belt_price', 't.type_id', 't.attribute_set_id', 't.name', 't.sku', 't.rts_bangle_size', 't.category_id', 't.shapes', 't.qualities_ids', 't.qualities', 't.roundshape', 't.rts_stone_quality', 't.rts_bracelet_size', 't.rts_ring_size', 't.certificate_no', 't.custom_price', 't.franchises_id', 't.type', 't.product_id', DB::raw("CASE WHEN(IFNULL(t.product_id, 'DML')AND t.TYPE = '3') THEN 'FRANCHISE SOLD' WHEN(IFNULL(t.product_id, 'DML') AND t.TYPE = '0') THEN 'FRANCHISE INSTOCK' WHEN(t.product_id = '' AND t.is_sold = 1) THEN 'DML SOLD' ELSE 'DML INSTOCK' END AS stockstatus"), 't.metal_quality_id', 't.metal_weight', DB::raw("IF(t.metal_quality_id = '2879' OR t.metal_quality_id = '2878' OR t.metal_quality_id = '2880' OR t.metal_quality_id = '3170' OR t.metal_quality_id = '3120' OR t.metal_quality_id = '2900' OR t.metal_quality_id = '3091',((t.metal_weight * 85) / 100),t.metal_weight) AS metal_weight_14k"), DB::raw("IF(t.metal_quality_id = '2877' OR t.metal_quality_id = '2875' OR t.metal_quality_id = '2901' OR t.metal_quality_id = '3119' OR t.metal_quality_id = '2876',((t.metal_weight * 100) / 85),t.metal_weight) AS metal_weight_18k"), DB::raw("CAST(t.total_diamond_weight AS DECIMAL(10, 2)) AS total_diamond_weight"), DB::raw("IF(t.metal_quality_id = '2879' OR t.metal_quality_id = '2878' OR t.metal_quality_id = '2880' OR t.metal_quality_id = '3170' OR t.metal_quality_id = '3120' OR t.metal_quality_id = '2900' OR t.metal_quality_id = '3091',((t.metal_weight * 85) / 100),t.metal_weight) / CAST(t.total_diamond_weight AS DECIMAL(10, 2)) AS ratio14k"), DB::raw("IF(t.metal_quality_id = '2877' OR t.metal_quality_id = '2875' OR t.metal_quality_id = '2901' OR t.metal_quality_id = '3119' OR t.metal_quality_id = '2876',((t.metal_weight * 100) / 85),t.metal_weight) / CAST(t.total_diamond_weight AS DECIMAL(10, 2)) AS ratio18k"), 't.price AS indexed_price', 't.price', 't.final_price', DB::raw("IF(t.tier_price,LEAST(t.min_price, t.tier_price),t.min_price) AS minimal_price"), 't.min_price', 't.max_price', 't.tier_price')
			->fromSub(function ($query) {
				$query->from('catalog_product_flat_1 as e')->select('e.entity_id', 'e.belt_price', 'e.type_id', 'e.attribute_set_id', 'e.name', 'e.sku', 'e.rts_bangle_size', 'e.rts_stone_quality', 'e.rts_bracelet_size', 'e.rts_ring_size', 'e.certificate_no', 'e.custom_price', 'catalog_category_product.category_id', 'fo.franchises_id', 'fo.type', 'fo.product_id', 'e.is_sold', 'shapes', 'qualities_ids', 'qualities', DB::raw('roundshape'), 'metal.metal_quality_id', 'metal.metal_weight', 'diamond_type.total_diamond_weight', 'price_index.price', 'price_index.final_price', 'price_index.min_price', 'price_index.tier_price', 'price_index.max_price')
					->join('catalog_category_product', 'e.entity_id', '=', 'catalog_category_product.product_id')
					->leftJoin('franchises_order as fo', 'e.entity_id', '=', 'fo.product_id')
					->leftJoin('grp_metal as metal', 'e.entity_id', '=', 'metal.metal_product_id')
				//->leftJoin('grp_stone as stone', 'e.entity_id', '=', 'stone.stone_product_id')
				/*->leftJoin(DB::raw("(SELECT e.*, grp_stone.stone_product_id, SUM(grp_stone.total_carat) AS total_diamond_weight FROM catalog_product_flat_1 AS e LEFT JOIN grp_stone ON e.entity_id = grp_stone.stone_product_id WHERE (e.status = 1) AND(e.isreadytoship = 1) AND(e.type_id = 'simple') GROUP BY e.entity_id) AS stone"),
						function ($join) {
							$join->on('stone.stone_product_id', '=', 'e.entity_id');
						})*/
					->rightJoin(DB::raw("(SELECT e.*,SUM(grp_stone.total_carat) AS total_diamond_weight, GROUP_CONCAT(DISTINCT grp_stone.stone_shape) AS shapes, FIND_IN_SET('36', GROUP_CONCAT(DISTINCT grp_stone.stone_shape)) AS roundshape, GROUP_CONCAT(DISTINCT grp_stone.stone_clarity) AS qualities_ids, GROUP_CONCAT(DISTINCT EA.value) AS qualities FROM catalog_product_flat_1 AS e LEFT JOIN grp_stone ON e.entity_id = grp_stone.stone_product_id LEFT JOIN (SELECT option_id,value FROM `eav_attribute_option_value` WHERE option_id IN (SELECT option_id FROM `eav_attribute_option` WHERE `attribute_id` = (SELECT attribute_id FROM `eav_attribute` WHERE `attribute_code` = 'stone_clarity' ))) EA ON EA.option_id = grp_stone.stone_clarity WHERE  (e.status = 1) AND(e.isreadytoship = 1) AND(e.type_id = 'simple') GROUP BY e.entity_id) AS diamond_type"),
						function ($join) {
							$join->on('diamond_type.entity_id', '=', 'e.entity_id');
						})
					->join('catalog_product_index_price as price_index', function ($join) {$join->on('e.entity_id', '=', 'price_index.entity_id')->where('price_index.website_id', '=', DB::raw("'1'"))->where('price_index.customer_group_id', '=', DB::raw("'4'"));})
					->where('e.status', '=', DB::raw('1'))
					->where('e.isreadytoship', '=', DB::raw('1'))
					->where('e.custom_price', '!=', DB::raw('""'));
			}, 't')
			->groupBy('t.entity_id')
			->orderBy('t.entity_id', 'desc');
		//echo $prod->toSql();exit;

		if ($productIds) {
			$prod->whereIn('t.entity_id', $productIds);
		}

		//echo $prod->toSql();exit;

		$collection = collect($prod->get());

		//echo '<pre>';
		//print_r($collection);exit;

		Cache::put('all_showroom_products', $collection, now()->addMinutes(config('constants.enum.cache_expiry_minutes')));

		DB::setTablePrefix('dml_');
		return $collection;
	}

	//Get product information by product id
	public static function getProductData($productId) {
		DB::setTablePrefix('');
		$productData = DB::select("SELECT * from catalog_product_flat_1 where entity_id=" . $productId . "");
		DB::setTablePrefix('dml_');
		return $productData;
	}
	//Get categories name by product id
	public static function getCategoryNames($productId) {
		DB::setTablePrefix('');
		$categoryNames = DB::select("SELECT c2.value as category_name FROM catalog_category_product c1 INNER JOIN catalog_category_entity_varchar c2 ON (c1.category_id = c2.entity_id) INNER JOIN catalog_product_entity c3 ON (c1.product_id = c3.entity_id) WHERE c2.attribute_id =( SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'name' AND entity_type_id = 3 ) AND c3.entity_id = " . $productId . "");
		DB::setTablePrefix('dml_');
		return $categoryNames;
	}
	//Get category name by category id
	public static function getCategoryName($categoryId) {
		DB::setTablePrefix('');
		if (!empty($categoryId)) {
			$categoryName = DB::select("SELECT name FROM catalog_category_flat_store_1 WHERE entity_id=" . $categoryId . "");
		}
		DB::setTablePrefix('dml_');
		return isset($categoryName[0]->name) ? $categoryName[0]->name : '';
	}
	//Get Categories id by product id
	public static function getCategoryIds($productId) {
		$categoryIds = DB::select("SELECT category_id FROM catalog_category_product WHERE product_id=" . $productId . "");
		return $categoryIds;
	}
	//Get metal data by product id
	public static function getMetalData($productId, $productData = null) {

		DB::setTablePrefix('');
		$metalData = DB::select("SELECT * FROM `grp_metal` WHERE metal_product_id = " . $productId . "");

		//$productData = ShowroomHelper::getProductData($productId);
		$metal_data = array();
		//Get attribute value
		$metalTypesDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_type' AND EAOV.store_id = 0");
		$metalTypes = array();

		foreach ($metalTypesDetails as $key => $metalType) {
			$metalTypes[][$metalType->option_id] = $metalType->value;
		}

		$metalQualityDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_quality' AND EAOV.store_id = 0");
		$metalQuality = array();
		//echo "<pre>";
		//print_r($metalTypesDetails);exit;
		foreach ($metalQualityDetails as $key => $metal_quality) {
			$metalQuality[][$metal_quality->option_id] = $metal_quality->value;
		}
		$productStoneShape = DB::select("SELECT stone_shape FROM `grp_stone` WHERE stone_product_id =" . $productId . "");
		$stoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
		foreach ($metalData as $key => $metal) {
			$data = '';
			$actualMetalWeight = 0;
			//print_r($metal);exit;
			$metalTypeValue = DB::select("SELECT metal_type FROM grp_metal_type WHERE grp_metal_type_id=" . $metal->metal_type_id . "");
			$metalTypeValue = $metalTypeValue[0]->metal_type;
			$metalQuality = DB::select("SELECT metal_quality,rate FROM grp_metal_quality WHERE grp_metal_quality_id=" . $metal->metal_quality_id . "");
			$metalQualityValue = $metalQuality[0]->metal_quality;
			$metal_data['type'] = $metalTypeValue;
			$metal_data['quality'] = $metalQualityValue;
			$metal_data['per-gm-rate'] = round($metalQuality[0]->rate);

			//Get product options
			$productOptions = ShowroomHelper::getProductOptions($productId);

			if (!empty($productOptions)) {
				foreach ($productOptions as $prodOption) {
					if ($prodOption->type === 'drop_down') {
						$productOptionValue = DB::select("SELECT `main_table`.*, `default_value_price`.`price` AS `default_price`, `default_value_price`.`price_type` AS `default_price_type`, `store_value_price`.`price` AS `store_price`, `store_value_price`.`price_type` AS `store_price_type`, IF(store_value_price.price IS NULL, default_value_price.price, store_value_price.price) AS `price`, IF(store_value_price.price_type IS NULL, default_value_price.price_type, store_value_price.price_type) AS `price_type`, `default_value_title`.`title` AS `default_title`, `store_value_title`.`title` AS `store_title`, IF(store_value_title.title IS NULL, default_value_title.title, store_value_title.title) AS `title` FROM `catalog_product_option_type_value` AS `main_table` LEFT JOIN `catalog_product_option_type_price` AS `default_value_price` ON default_value_price.option_type_id = main_table.option_type_id AND default_value_price.store_id = 0 LEFT JOIN `catalog_product_option_type_price` AS `store_value_price` ON store_value_price.option_type_id = main_table.option_type_id AND store_value_price.store_id = '' INNER JOIN `catalog_product_option_type_title` AS `default_value_title` ON default_value_title.option_type_id = main_table.option_type_id LEFT JOIN `catalog_product_option_type_title` AS `store_value_title` ON store_value_title.option_type_id = main_table.option_type_id AND store_value_title.store_id = '' WHERE (option_id = " . $prodOption->option_id . ") AND (default_value_title.store_id = 0)");
						if (!empty($productOptionValue)) {
							foreach ($productOptionValue as $key => $value) {
								if (!empty($productData)) {
									if ($value->title == $productData->rts_ring_size) {
										$prodMetalWeight = $value->metal_weight;
									} else {
										$prodMetalWeight = $metal->metal_weight;
									}
								} else {
									$prodMetalWeight = $metal->metal_weight;
								}
							}
						} else {
							$prodMetalWeight = $metal->metal_weight;
						}
					} else {
						$prodMetalWeight = $metal->metal_weight;

					}
				}
			} else {
				$prodMetalWeight = $metal->metal_weight;
			}

			//get metal weight from custom options if ring size
			$categoryName = ShowroomHelper::getCategoryNames($productId);
			$categoryId = ShowroomHelper::getCategoryIds($productId);
			$categoryIdArray = array();

			foreach ($categoryId as $key => $value) {
				$categoryIdArray[] = $value->category_id;
			}

			if ($categoryIdArray[0] == '293' && $categoryName[0]->category_name == 'RUBBER BRACELETS') {
				$actualMetalWeight = $metal->metal_weight;
				$beltPrice = $productData->belt_price;
				//$extraPrice = $productData->extra_price;
				$stonedata = (float) ShowroomHelper::updateRatesRubberMakingCharge($productId, $metalTypeValue, $actualMetalWeight);
				$total = ($actualMetalWeight * $metal_data['per-gm-rate']) + $stonedata + $beltPrice; // + $extraPrice;
				$labourPriceValue = $total;
				//echo $labourPriceValue;exit;
			} else {

				if (strtolower($metalTypeValue) == 'gold') {
					$categoryName = ShowroomHelper::getCategoryName(293);
					$productOptions = ShowroomHelper::getProductOptions($productId);

					foreach ($productOptions as $key => $option) {
						if ($option->type == 'drop_down') {
							$optionValue = DB::select("SELECT `main_table`.*, `default_value_price`.`price` AS `default_price`, `default_value_price`.`price_type` AS `default_price_type`, `store_value_price`.`price` AS `store_price`, `store_value_price`.`price_type` AS `store_price_type`, IF(store_value_price.price IS NULL, default_value_price.price, store_value_price.price) AS `price`, IF(store_value_price.price_type IS NULL, default_value_price.price_type, store_value_price.price_type) AS `price_type`, `default_value_title`.`title` AS `default_title`, `store_value_title`.`title` AS `store_title`, IF(store_value_title.title IS NULL, default_value_title.title, store_value_title.title) AS `title` FROM `catalog_product_option_type_value` AS `main_table` LEFT JOIN `catalog_product_option_type_price` AS `default_value_price` ON default_value_price.option_type_id = main_table.option_type_id AND default_value_price.store_id = 0 LEFT JOIN `catalog_product_option_type_price` AS `store_value_price` ON store_value_price.option_type_id = main_table.option_type_id AND store_value_price.store_id = '' INNER JOIN `catalog_product_option_type_title` AS `default_value_title` ON default_value_title.option_type_id = main_table.option_type_id LEFT JOIN `catalog_product_option_type_title` AS `store_value_title` ON store_value_title.option_type_id = main_table.option_type_id AND store_value_title.store_id = '' WHERE (option_id = " . $option->option_id . ") AND (default_value_title.store_id = 0)");

							if (!empty($optionValue) && count($optionValue) > 0) {
								foreach ($optionValue as $key => $optionVal) {
									if (!empty($productData)) {
										if ($optionVal->title == $productData->rts_ring_size) {
											$actualMetalWeight = $optionVal->metal_weight;
										} else {
											$actualMetalWeight = $metal->metal_weight;
										}
									} else {
										$actualMetalWeight = $metal->metal_weight;
									}
								}
							} else {
								$actualMetalWeight = $metal->metal_weight;
							}
						} else {
							$actualMetalWeight = $metal->metal_weight;
						}
					}

					if (in_array('293', $categoryIdArray) && $categoryName == 'RUBBER BRACELETS') {
						$actualMetalWeight = $prodMetalWeight;
						$beltPrice = (float) !empty($productData->belt_price) ? $productData->belt_price : 0;
						//$extraPrice = (float) !empty($productData->extra_price) ? $productData->extra_price : 0;
						$stonedata = (float) ShowroomHelper::updateRatesRubberMakingCharge($productId, $metalTypeValue, $actualMetalWeight);
						$total = ($actualMetalWeight * $metal_data['per-gm-rate']) + $stonedata + $beltPrice; // + $extraPrice;
						$labourPriceValue = $total;
					} else {

						//$extraPrice = (float) !empty($productData->extra_price) ? $productData->extra_price : 0;
						$stonedata = (float) ShowroomHelper::updateStoneMakingChargeForRound($productId, $actualMetalWeight);

						$total = ($actualMetalWeight * $metal_data['per-gm-rate']) + $stonedata; // + $extraPrice;
						$labourPriceValue = $total;

					}
				} else if (strtolower($metalTypeValue) == 'platinum(950)') {
					$categoryName = ShowroomHelper::getCategoryName(293);
					$productOptions = ShowroomHelper::getProductOptions($productId);
					foreach ($productOptions as $key => $option) {
						if ($option->type == 'drop_down') {
							$optionValue = DB::select("SELECT `main_table`.*, `default_value_price`.`price` AS `default_price`, `default_value_price`.`price_type` AS `default_price_type`, `store_value_price`.`price` AS `store_price`, `store_value_price`.`price_type` AS `store_price_type`, IF(store_value_price.price IS NULL, default_value_price.price, store_value_price.price) AS `price`, IF(store_value_price.price_type IS NULL, default_value_price.price_type, store_value_price.price_type) AS `price_type`, `default_value_title`.`title` AS `default_title`, `store_value_title`.`title` AS `store_title`, IF(store_value_title.title IS NULL, default_value_title.title, store_value_title.title) AS `title` FROM `catalog_product_option_type_value` AS `main_table` LEFT JOIN `catalog_product_option_type_price` AS `default_value_price` ON default_value_price.option_type_id = main_table.option_type_id AND default_value_price.store_id = 0 LEFT JOIN `catalog_product_option_type_price` AS `store_value_price` ON store_value_price.option_type_id = main_table.option_type_id AND store_value_price.store_id = '' INNER JOIN `catalog_product_option_type_title` AS `default_value_title` ON default_value_title.option_type_id = main_table.option_type_id LEFT JOIN `catalog_product_option_type_title` AS `store_value_title` ON store_value_title.option_type_id = main_table.option_type_id AND store_value_title.store_id = '' WHERE (option_id = " . $option->option_id . ") AND (default_value_title.store_id = 0)");
							foreach ($optionValue as $key => $optionVal) {
								if ($optionVal->title == $productData->rts_ring_size) {
									$actualMetalWeight = $optionVal->metal_weight;
								} else {
									$actualMetalWeight = $metal->metal_weight;
								}
							}
						} else {
							$actualMetalWeight = $metal->metal_weight;
						}
					}
					if (in_array('293', $categoryIdArray) && $categoryName == 'RUBBER BRACELETS') {
						$actualMetalWeight = $prodMetalWeight;
						$beltPrice = $productData->belt_price;
						//$extraPrice = $productData->extra_price;
						$stonedata = (float) ShowroomHelper::updateRatesRubberMakingCharge($productId, $metalTypeValue, $actualMetalWeight);
						$total = ($actualMetalWeight * $metal_data['per-gm-rate']) + $stonedata + $beltPrice; // + $extraPrice;
						$labourPriceValue = $total;
					} else {
						//$beltPrice = $productData[0]->belt_price;
						//$extraPrice = $productData->extra_price;
						$stonedata = (float) ShowroomHelper::updatePlatinumStoneMakingChargeForRound($productId, $actualMetalWeight);
						$total = ($actualMetalWeight * $metal_data['per-gm-rate']) + $stonedata; // + $extraPrice;
						$labourPriceValue = $total;
					}
				} else {
					if (in_array('293', $categoryIdArray) && $categoryName == 'RUBBER BRACELETS') {
						$beltPrice = $productData->belt_price;
					}
					$actualMetalWeight = $prodMetalWeight;
					// $extraPrice = $productData->extra_price;
					$total = ($actualMetalWeight * $metal_data['per-gm-rate']) + $metal->metal_labour_charge + $beltPrice; // + $extraPrice;
					$labourPriceValue = $metal->metal_labour_charge;
				}
			}

			//echo $actualMetalWeight."    ".$metal_data['per-gm-rate'];exit;
			$metalTotalValue = $total;
			$metalPriceValue = (int) ($actualMetalWeight * $metal_data['per-gm-rate']);
			$metalWeightPerGram = isset($metalQuality[0]->rate) ? $metalQuality[0]->rate : 0;
			$metalTotal = preg_replace('~\.0+$~', '', $metalTotalValue);
			$metalprice = preg_replace('~\.0+$~', '', $metalPriceValue);
			$labourPriceValue = preg_replace('~\.0+$~', '', $labourPriceValue);
			$metalWeightPerGram = preg_replace('~\.0+$~', '', $metalWeightPerGram);
			$metal_data['per-gm-rate'] = $metalWeightPerGram;
			$metal_data['metal-id'] = $metal->metal_quality_id;
			$metal_data['weight'] = $actualMetalWeight;
			$metal_data['metal-rate'] = $metalprice;
			$metal_data['metalprice_value'] = ($actualMetalWeight * round(isset($metalQuality[0]->rate) ? $metalQuality[0]->rate : 0));
			$metal_data['labour-charge'] = $labourPriceValue;
			$metal_data['total'] = $metalTotal;
			$metal_data['simple'] = round($total);
			$metal_data['per-gm-rate-digit-only'] = round(isset($metalQuality[0]->rate) ? $metalQuality[0]->rate : 0);
		}

		DB::setTablePrefix('dml_');
		return $metal_data;
	}
	/* Update labour charge Rubber Braclets as per stone available in product*/
	public static function updateRatesRubberMakingCharge($productId, $metalTypeValue, $actualMetalWeight) {
		//$productData = ShowroomHelper::getProductData($productId);
		$categries = DB::select("SELECT `main_table`.`entity_id`, `main_table`.`level`, `main_table`.`path`, `main_table`.`position`, `main_table`.`is_active`, `main_table`.`is_anchor`, `main_table`.`entity_id` FROM `catalog_category_flat_store_1` AS `main_table` WHERE (is_active = '1')");
		$allCatIds = array();
		foreach ($categries as $getAllCatIds) {
			$allCatIds[] = $getAllCatIds->entity_id;
		}
		$categoryId = ShowroomHelper::getCategoryIds($productId);
		$children = DB::select("SELECT catmaster.entity_id FROM `catalog_category_entity` as catmaster left join catalog_category_entity_varchar as catvarchar ON catvarchar.entity_id = catmaster.entity_id WHERE catmaster.`parent_id` = 124");
		$childCatId = array();
		foreach ($children as $key => $value) {
			$childCatId[] = $value->entity_id;
		}
		$childCatId = array_unique($childCatId);
		$rubber_category_arr = array(293);
		$rubber_arrkey = array_search('293', $childCatId);
		if (!empty($rubber_arrkey)) {
			unset($childCatId[$rubber_arrkey]);
		}

		$rubber_intersect = array_intersect($rubber_category_arr, $allCatIds);
		$result_intersect = array_intersect($childCatId, $allCatIds);
		$shapeSideStoneData = DB::select("SELECT * FROM grp_stone WHERE stone_product_id=" . $productId . "");
		$stoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
		$stoneShapeData = array();
		foreach ($stoneShapeDetails as $key => $stoneShape) {
			$stoneShapeData[$stoneShape->option_id] = $stoneShape->value;
		}
		$isRound = false;
		$isMarque = false;
		foreach ($shapeSideStoneData as $key => $shapeSideStone) {
			$shapeDetail[] = $stoneShapeData[$shapeSideStone->stone_shape];
			$label = $stoneShapeData[$shapeSideStone->stone_shape];
			if ($label == 'ROUND') {
				$isRound = true;
			} else {
				$isMarque = true;
			}
		}
		$shape = sizeof($shapeSideStoneData);
		$counts = array_count_values($shapeDetail);
		if (strtolower($metalTypeValue) == 'gold') {
			if ($isRound && !$isMarque) {
				$CustomDiamondshape = 1;
				//}else if((int)$shape < 1 && strtolower($ShapetypeData) != 'round'){
			} else if ($isMarque && !$isRound) {
				$CustomDiamondshape = 2;
			} else if ($isRound && $isMarque) {
				$CustomDiamondshape = 2;
			} else {
				$CustomDiamondshape = 2;
			}
			if (strtolower($metalTypeValue) == 'gold') {
				$Custommetal = 1;
			} else {
				$Custommetal = 3;
			}
			if (count($result_intersect) > 0) {
				$Categorybracelets = 2;
			} elseif (count($rubber_intersect) > 0) {
				$Categorybracelets = 3;
			} else {
				$Categorybracelets = 1;
			}
			$customLabourCharge = DB::select("SELECT * FROM grp_custom_labour_charges WHERE type=" . $Custommetal . " AND product_type=" . $Categorybracelets . " AND diamond_type=" . $CustomDiamondshape . " AND to_mm>=" . (float) $actualMetalWeight . " AND from_mm<=" . (float) $actualMetalWeight . " ORDER BY CAST(to_mm AS DECIMAL), CAST(from_mm AS DECIMAL )");
			foreach ($customLabourCharge as $key => $labourCharge) {
				if ($actualMetalWeight < 1) {
					$roundshape = 1 * $labourCharge->labour_charge;
				} else {
					$roundshape = $actualMetalWeight * $labourCharge->labour_charge;
				}
			}
		} else if (strtolower($metalTypeValue) == 'platinum(950)') {
			$counts = array_count_values($shapeDetail);
			if ($isRound && !$isMarque) {
				$CustomDiamondshape = 1;
				//}else if((int)$shape < 1 && strtolower($ShapetypeData) != 'round'){
			} else if ($isMarque && !$isRound) {
				$CustomDiamondshape = 2;
			} else if ($isRound && $isMarque) {
				$CustomDiamondshape = 2;
			} else {
				$CustomDiamondshape = 2;
			}
			if (strtolower($metalTypeValue) == 'platinum(950)') {
				$Custommetal = 3;
			} else {
				$Custommetal = 1;
			}
			if (count($result_intersect) > 0) {
				$Categorybracelets = 2;
			} else {
				$Categorybracelets = 1;
			}
			$customLabourCharge = DB::select("SELECT * FROM grp_custom_labour_charges WHERE type=" . $Custommetal . " AND product_type=" . $Categorybracelets . " AND diamond_type=" . $CustomDiamondshape . " AND to_mm>=" . (float) $actualMetalWeight . " AND from_mm<=" . (float) $actualMetalWeight . " ORDER BY CAST(to_mm AS DECIMAL), CAST(from_mm AS DECIMAL )");
			foreach ($customLabourCharge as $key => $labourCharge) {
				//print_r($labourCharge);exit;
				if ($actualMetalWeight < 1) {
					$roundshape = 1 * $labourCharge->labour_charge;
				} else {
					$roundshape = $actualMetalWeight * $labourCharge->labour_charge;
				}
			}
		}
		return $roundshape;
	}
	/* Update labour charge as per stone available in product*/
	public static function updatePlatinumStoneMakingChargeForRound($productId, $metalWeight) {
		//$productData = ShowroomHelper::getProductData($productId);
		$shapeSideStoneData = DB::select("SELECT * FROM grp_stone WHERE stone_product_id=" . $productId . "");
		$stoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
		$stoneShapeData = array();
		foreach ($stoneShapeDetails as $key => $stoneShape) {
			$stoneShapeData[$stoneShape->option_id] = $stoneShape->value;
		}
		$categries = DB::select("SELECT `main_table`.`entity_id`, `main_table`.`level`, `main_table`.`path`, `main_table`.`position`, `main_table`.`is_active`, `main_table`.`is_anchor`, `main_table`.`entity_id` FROM `catalog_category_flat_store_1` AS `main_table` WHERE (is_active = '1')");
		$allCatIds = array();
		foreach ($categries as $getAllCatIds) {
			$allCatIds[] = $getAllCatIds->entity_id;
		}
		$isRound = false;
		$isMarque = false;
		$categoryId = ShowroomHelper::getCategoryIds($productId);
		$children = DB::select("SELECT catmaster.entity_id FROM `catalog_category_entity` as catmaster left join catalog_category_entity_varchar as catvarchar ON catvarchar.entity_id = catmaster.entity_id WHERE catmaster.`parent_id` = 124");
		$childCatId = array();
		foreach ($children as $key => $value) {
			$childCatId[] = $value->entity_id;
		}
		$childCatId = array_unique($childCatId);
		$rubber_category_arr = array(293);
		$rubber_arrkey = array_search('293', $childCatId);
		if (!empty($rubber_arrkey)) {
			unset($childCatId[$rubber_arrkey]);
		}
		$rubber_intersect = array_intersect($rubber_category_arr, $allCatIds);
		$result_intersect = array_intersect($childCatId, $allCatIds);
		$metalTypeDetail = DB::select("SELECT * FROM `grp_metal_type` WHERE `metal_type`='Platinum(950)' LIMIT 1");
		foreach ($shapeSideStoneData as $key => $shapeSideStone) {
			$shapeDetail[] = $stoneShapeData[$shapeSideStone->stone_shape];
			$label = $stoneShapeData[$shapeSideStone->stone_shape];
			if ($label == 'ROUND') {
				$isRound = true;
			} else {
				$isMarque = true;
			}
		}
		$counts = array_count_values($shapeDetail);
		$shape = sizeof($shapeSideStoneData);
		$roundshape = '';
		if ($isRound && !$isMarque) {
			$CustomDiamondshape = 1;
			//}else if((int)$shape < 1 && strtolower($ShapetypeData) != 'round'){
		} else if ($isMarque && !$isRound) {
			$CustomDiamondshape = 2;
		} else if ($isRound && $isMarque) {
			$CustomDiamondshape = 2;
		} else {
			$CustomDiamondshape = 2;
		}
		if (strtolower($metalTypeDetail[0]->metal_type) == 'platinum(950)') {
			$Custommetal = 3;
		} else {
			$Custommetal = 1;
		}
		if (count($result_intersect) > 0) {
			$Categorybracelets = 2;
		} elseif (count($rubber_intersect) > 0) {
			$Categorybracelets = 3;
		} else {
			$Categorybracelets = 1;
		}
		$customLabourCharge = DB::select("SELECT * FROM grp_custom_labour_charges WHERE type=" . $Custommetal . " AND product_type=" . $Categorybracelets . " AND diamond_type=" . $CustomDiamondshape . " AND to_mm>=" . (float) $metalWeight . " AND from_mm<=" . (float) $metalWeight . " ORDER BY CAST(to_mm AS DECIMAL), CAST(from_mm AS DECIMAL )");
		foreach ($customLabourCharge as $key => $labourCharge) {
			if ($metalWeight < 1) {
				$roundshape = 1 * $labourCharge->labour_charge;
			} else {
				$roundshape = $metalWeight * $labourCharge->labour_charge;
			}
		}
		return $roundshape;
	}
	//Get product options by product id
	public static function getProductOptions($productId) {
		//$productId = '1256149';
		$productOptions = DB::select("SELECT `main_table`.*, `default_option_title`.`title` AS `default_title`, `store_option_title`.`title` AS `store_title`, IF(store_option_title.title IS NULL, default_option_title.title, store_option_title.title) AS `title`, `default_option_price`.`price` AS `default_price`, `default_option_price`.`price_type` AS `default_price_type`, `store_option_price`.`price` AS `store_price`, `store_option_price`.`price_type` AS `store_price_type`, IF(store_option_price.price IS NULL, default_option_price.price, store_option_price.price) AS `price`, IF(store_option_price.price_type IS NULL, default_option_price.price_type, store_option_price.price_type) AS `price_type`, `default_option_description`.`description` AS `default_description`, `store_option_description`.`description` AS `store_description`, IFNULL(`store_option_description`.description,`default_option_description`.description) AS `description` FROM `catalog_product_option` AS `main_table` INNER JOIN `catalog_product_option_title` AS `default_option_title` ON default_option_title.option_id = main_table.option_id LEFT JOIN `catalog_product_option_title` AS `store_option_title` ON store_option_title.option_id = main_table.option_id AND store_option_title.store_id = '1' LEFT JOIN `catalog_product_option_price` AS `default_option_price` ON default_option_price.option_id = main_table.option_id AND default_option_price.store_id = 0 LEFT JOIN `catalog_product_option_price` AS `store_option_price` ON store_option_price.option_id = main_table.option_id AND store_option_price.store_id = '1' LEFT JOIN `custom_options_option_description` AS `default_option_description` ON `default_option_description`.option_id = `main_table`.option_id AND `default_option_description`.store_id = 0 LEFT JOIN `custom_options_option_description` AS `store_option_description` ON `store_option_description`.option_id = `main_table`.option_id AND `store_option_description`.store_id = '1' WHERE (product_id = " . $productId . ") AND (is_enabled != 0) AND (default_option_title.store_id = 0) ORDER BY sort_order ASC, title ASC");
		//print_r($productOptions);exit;
		return $productOptions;
	}
	/* Update labour charge as per stone available in product*/
	public static function updateStoneMakingChargeForRound($productId, $metalWeight) {
		//$productData = ShowroomHelper::getProductData($productId);
		$categries = DB::select("SELECT `main_table`.`entity_id`, `main_table`.`level`, `main_table`.`path`, `main_table`.`position`, `main_table`.`is_active`, `main_table`.`is_anchor`, `main_table`.`entity_id` FROM `catalog_category_flat_store_1` AS `main_table` WHERE (is_active = '1')");
		$allCatIds = array();
		foreach ($categries as $getAllCatIds) {
			$allCatIds[] = $getAllCatIds->entity_id;
		}
		$categoryId = ShowroomHelper::getCategoryIds($productId);
		$children = DB::select("SELECT catmaster.entity_id FROM `catalog_category_entity` as catmaster left join catalog_category_entity_varchar as catvarchar ON catvarchar.entity_id = catmaster.entity_id WHERE catmaster.`parent_id` = 124");
		$childCatId = array();
		foreach ($children as $key => $value) {
			$childCatId[] = $value->entity_id;
		}
		$childCatId = array_unique($childCatId);
		$rubber_category_arr = array(293);
		$rubber_arrkey = array_search('293', $childCatId);
		if (!empty($rubber_arrkey)) {
			unset($childCatId[$rubber_arrkey]);
		}
		$rubber_intersect = array_intersect($rubber_category_arr, $allCatIds);
		// added new rubber bracelet type
		$parentCategory = array(124);
		$finalCategoryList = array_merge($allCatIds, $parentCategory);
		$result_intersect = array_intersect($finalCategoryList, $allCatIds);
		$shapeSideStoneData = DB::select("SELECT * FROM grp_stone WHERE stone_product_id=" . $productId . "");
		$stoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
		$shape = sizeof($shapeSideStoneData);
		$stoneShapeData = array();
		$isRound = false;
		$isMarque = false;
		foreach ($stoneShapeDetails as $key => $stoneShape) {
			$stoneShapeData[$stoneShape->option_id] = $stoneShape->value;
		}
		foreach ($shapeSideStoneData as $key => $shapeSideStone) {
			$shapeDetail[] = $stoneShapeData[$shapeSideStone->stone_shape];
			$label = $stoneShapeData[$shapeSideStone->stone_shape];
			if ($label == 'ROUND') {
				$isRound = true;
			} else {
				$isMarque = true;
			}
		}
		$metalTypeDetail = DB::select("SELECT * FROM `grp_metal_type` WHERE `metal_type`='Gold' LIMIT 1");
		$counts = array_count_values($shapeDetail);
		if ($isRound && !$isMarque) {
			$CustomDiamondshape = 1;
			//}else if((int)$shape < 1 && strtolower($ShapetypeData) != 'round'){
		} else if ($isMarque && !$isRound) {
			$CustomDiamondshape = 2;
		} else if ($isRound && $isMarque) {
			$CustomDiamondshape = 2;
		} else {
			$CustomDiamondshape = 2;
		}
		if (strtolower($metalTypeDetail[0]->metal_type) == 'gold') {
			$Custommetal = 1;
		} else {
			$Custommetal = 3;
		}
		if (count($result_intersect) > 0) {
			$Categorybracelets = 2;
		} elseif (count($rubber_intersect) > 0) {
			$Categorybracelets = 3;
		} else {
			$Categorybracelets = 1;
		}
		$roundshape = '';
		$customLabourCharge = DB::select("SELECT * FROM grp_custom_labour_charges WHERE type=" . $Custommetal . " AND product_type=" . $Categorybracelets . " AND diamond_type=" . $CustomDiamondshape . " AND to_mm>=" . (float) $metalWeight . " AND from_mm<=" . (float) $metalWeight . " ORDER BY CAST(to_mm AS DECIMAL), CAST(from_mm AS DECIMAL )");
		foreach ($customLabourCharge as $key => $labourCharge) {
			if ($metalWeight < 1) {
				$roundshape = 1 * $labourCharge->labour_charge;
			} else {
				$roundshape = $metalWeight * $labourCharge->labour_charge;
			}
		}
		return $roundshape;
	}
	//Get indian currency format
	public static function currencyFormat($money) {
		$len = strlen($money);
		$m = '';
		$money = strrev($money);
		for ($i = 0; $i < $len; $i++) {
			if (($i == 3 || ($i > 3 && ($i - 1) % 2 == 0)) && $i != $len) {
				$m .= ',';
			}
			$m .= $money[$i];
		}
		return html_entity_decode('&#8377;') . strrev($m);
	}
	//Get indian currency number
	public static function currencyFormatWithoutIcon($money) {
		$len = strlen($money);
		$m = '';
		$money = strrev($money);
		for ($i = 0; $i < $len; $i++) {
			if (($i == 3 || ($i > 3 && ($i - 1) % 2 == 0)) && $i != $len) {
				$m .= ',';
			}
			$m .= $money[$i];
		}
		return strrev($m);
	}

	public static function currencyFormatForProductImage($money) {
		$len = strlen($money);
		$m = '';
		$money = strrev($money);
		for ($i = 0; $i < $len; $i++) {
			if (($i == 3 || ($i > 3 && ($i - 1) % 2 == 0)) && $i != $len) {
				$m .= ',';
			}
			$m .= $money[$i];
		}
		return strrev($m);
	}
	//Get all diamond shape
	public static function getDiamondShape() {
		$shapeSideStoneData = DB::select("SELECT * FROM grp_stone");
		$sideStoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
		$stoneShapeData = array();
		$stoneShapeArr = array();
		$side_stone_data = array();
		foreach ($sideStoneShapeDetails as $key => $stoneShape) {
			$stoneShapeData[$stoneShape->option_id] = $stoneShape->value;
		}
		foreach ($shapeSideStoneData as $key => $sideStone) {
			$stoneShapeArr[$sideStone->stone_shape] = $stoneShapeData[$sideStone->stone_shape];
		}
		$diamondShapeHtml = "<div class='dropdown' id='diamondshape_area'>";
		$diamondShapeHtml .= "<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Dia. Shappe<span class='caret'></span></button>";
		$diamondShapeHtml .= "<ul class='dropdown-menu'>";
		foreach ($stoneShapeArr as $key => $shape) {
			$diamondShapeHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' name='diamondShapeChkbox' value=" . $key . " class='chk_diamondshape'/><span class='label-text'>" . $shape . "</span></label></li>";
		}
		$diamondShapeHtml .= '</ul></div>';
		return $diamondShapeHtml;
	}
	//Get side stone data
	public static function getSideStoneData($productId, $stoneQuality) {
		if (empty($stoneQuality)) {
			$stoneQuality = "SI-IJ";
		}

		$product_stone_price = array();
		$product_stone_weight = array();
		$shapeSideStoneData = DB::select("SELECT * FROM grp_stone WHERE stone_product_id=" . $productId . "");
		//print_r($shapeSideStoneData);exit;
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
		}
		foreach ($sideStoneSettingDetails as $key => $stoneShape) {
			$stoneSettingData[$stoneShape->option_id] = $stoneShape->value;
		}
		foreach ($sideStoneShapeDetails as $key => $stoneShape) {
			$stoneShapeData[$stoneShape->option_id] = $stoneShape->value;
		}
		foreach ($shapeSideStoneData as $key => $sideStone) {
			$sideStoneProductData = '';
			$sideStoneProductData = DB::select("SELECT * FROM grp_stone WHERE grp_stone_id=" . $sideStone->grp_stone_id . "");
			$sideStoneType = $stoneTypeData[$sideStone->stone_type];
			$sideStoneSubType = $stoneSubTypeData[$sideStone->stone_subtype];
			$sideStoneShape = $stoneShapeData[$sideStone->stone_shape];
			if (!empty($sideStone->stone_clarity)) {
				//$sideStone->stone_clarity = 63;
				$sideStoneClarity = $sideStone->stone_clarity;
				$sideStoneClarityText = $stoneClarityData[$sideStone->stone_clarity];
			} else {
				$sideStoneClarity = array_search($stoneQuality, $stoneClarityData);
				$sideStoneClarityText = $stoneQuality;
			}
			$sideStoneSetting = $stoneSettingData[$sideStone->stone_setting_type];
			$sideStoneCut = $stoneCutData[$sideStone->stone_cut];
			$stoneTotalCarat = $sideStone->total_carat;
			$stoneTotalCarat = number_format(floatval(($stoneTotalCarat / $sideStone->stone_use)), 3);
			$sideStone->stone_clarity = isset($sideStone->stone_clarity) ? $sideStone->stone_clarity : 63;
			$productSideStoneFianlPrice = 0;
			//echo "SELECT `main_table`.* FROM `grp_stone_manage` AS `main_table` WHERE (stone_shape = " . $sideStone->stone_shape . ") AND (stone_clarity = '" . $sideStone->stone_clarity . "') AND (stone_carat_from <= " . $stoneTotalCarat . ") AND (stone_carat_to >= " . $stoneTotalCarat . ")";exit;
			$modelSideStone = DB::select("SELECT `main_table`.* FROM `grp_stone_manage` AS `main_table` WHERE (stone_shape = " . $sideStone->stone_shape . ") AND (stone_clarity = '" . $sideStone->stone_clarity . "') AND (stone_carat_from <= " . $stoneTotalCarat . ") AND (stone_carat_to >= " . $stoneTotalCarat . ")");
			if (count($modelSideStone) > 0) {
				foreach ($modelSideStone as $key => $modelStone) {
					$stone_price = round($modelStone->stone_price);
				}

				$stoneCarat = number_format(floatval(($sideStone->carat)), 3);
				$productSideStonePrice = round($stone_price * $sideStone->total_carat);
				$productSideStoneFianlPrice = round($productSideStonePrice);
				//echo $productSideStoneFianlPrice;exit;
			}
			$side_stone_data['type'][] = $sideStoneType;
			$side_stone_data['subtype'][] = $sideStoneSubType;
			$side_stone_data['shape'][] = $sideStoneShape;
			$side_stone_data['cut'][] = $sideStoneCut;
			$side_stone_data['carat'][] = $sideStone->carat;
			$side_stone_data['stone'][] = $sideStone->stone_stone;
			$side_stone_data['stone_use'][] = $sideStone->stone_use;
			$side_stone_data['setting'][] = $sideStoneSetting;
			$side_stone_data['stoneclarity'][] = $sideStoneClarityText;
			$stoneCtTotal = $sideStone->total_carat;
			$side_stone_data['totalcts'][] = $sideStone->total_carat;
			$stoneFinalPrice = $productSideStoneFianlPrice;
			$product_stone_price[] = $productSideStoneFianlPrice;
			$product_price_per_ct = $productSideStoneFianlPrice / $sideStone->total_carat;
			$product_stone_weight[] = $stoneCtTotal;
			$roundSideStonePriceValue = ShowroomHelper::currencyFormat(round($productSideStoneFianlPrice));
			$roundSideStonePrice = preg_replace('~\.0+$~', '', $roundSideStonePriceValue);
			$roundSideStonePriceperctValue = ShowroomHelper::currencyFormat(round($product_price_per_ct));
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
		}
		return $side_stone_data;
	}
	//Get product
	public static function getProductImage($productId) {
		DB::setTablePrefix('');
		$productImage = DB::select("SELECT value FROM catalog_product_entity_media_gallery WHERE entity_id=" . $productId . "");
		DB::setTablePrefix('dml_');
		return isset($productImage[0]->value) ? $productImage[0]->value : '';
	}
	//Get total sku count by product sku
	public static function getTotalQtyBySku($sku) {
		$totalQtySku = DB::select("SELECT count(*) as total_sku from catalog_product_flat_1 WHERE sku like '%" . $sku . "%' and status = 1 and isreadytoship = 1");
		return $totalQtySku[0]->total_sku;
	}
	//Get category list for filter
	public static function getCategoryFilter($selectedCategories = null, $filterLabel = 'Category') {
		$categoryHtml = "";
		//Get root category id
		$rootCategoryId = DB::select("SELECT entity_id FROM catalog_category_flat_store_1 WHERE level=1");
		$rootCategoryId = $rootCategoryId[0]->entity_id;

		//Get Category by root category
		$categories = DB::select("SELECT DISTINCT catalog_category_flat_store_1.entity_id,catalog_category_flat_store_1.name FROM catalog_category_flat_store_1 JOIN catalog_category_product ON catalog_category_product.category_id=catalog_category_flat_store_1.entity_id WHERE catalog_category_flat_store_1.parent_id=" . $rootCategoryId);
		$categoryHtml = "<div class='dropdown' id='category_area'>";
		$categoryHtml .= "<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>" . $filterLabel . "<span class='caret'></span></button>";
		$categoryHtml .= "<ul class='dropdown-menu'>";
		if (count($categories) > 0) {
			foreach ($categories as $key => $value) {
				if (strtolower($value->name) == 'dmlstock') {
					continue;
				}
				$checked = '';
				if (isset($selectedCategories) && in_array($value->entity_id, array_values($selectedcategory))) {
					$categoryHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' data-filtertype=" . $value->entity_id . " checked name='category_chkbox' class='category_chkbox' value=" . $value->entity_id . "><span class='label-text'>" . ucwords(strtolower($value->name)) . "</span></label></li>";
				} else {
					$categoryHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' data-filtertype=" . $value->entity_id . " name='category_chkbox' class='category_chkbox' value=" . $value->entity_id . "><span class='label-text'>" . ucwords(strtolower($value->name)) . "</span></label></li>";
				}

			}
		}
		$categoryHtml .= "</ul></div>";
		return $categoryHtml;
	}
	//Get gold purity
	public static function getGoldPurity($selectedGoldPurity = null) {
		$goldPurityHtml = "";

		$metalQualityData = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_quality' AND EAOV.store_id = 0");
		$selectedPurity = array();
		if (!empty($selectedGoldPurity)) {
			foreach ($selectedGoldPurity as $key => $goldPurity) {
				$selectedPurity[] = $goldPurity;
			}
		}
		$goldPurityHtml = "<div class='dropdown' id='goldpurity_area'>";
		$goldPurityHtml .= "<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Gold Purity<span class='caret'></span></button>";
		$goldPurityHtml .= "<ul class='dropdown-menu'>";
		foreach ($metalQualityData as $key => $metalQuality) {
			if (in_array($metalQuality->value, $selectedPurity)) {
				$goldPurityHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input checked type='checkbox' name='metalQualityChkbox' value=" . $metalQuality->option_id . " class='chk_metalquality'/><span class='label-text'>" . $metalQuality->value . "</span></label></li>";
			} else {
				$goldPurityHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' name='metalQualityChkbox' value=" . $metalQuality->option_id . " class='chk_metalquality'/><span class='label-text'>" . $metalQuality->value . "</span></label></li>";
			}
		}
		$goldPurityHtml .= "</ul></div>";
		return $goldPurityHtml;
	}
	//Get diamond quality
	public static function getDiamondQuality($selectedDiamondQuality = null) {
		$diamondQualityHtml = "";
		$stoneQuality = DB::select("(SELECT prod.entity_id, op.option_id,opt.title AS title1,optt.title AS title2 FROM catalog_product_entity AS prod INNER JOIN catalog_product_option AS op ON op.product_id = prod.entity_id LEFT JOIN catalog_product_option_title AS opt ON opt.option_id = op.option_id LEFT JOIN catalog_product_option_type_value AS optv ON optv.option_id = op.option_id LEFT JOIN catalog_product_option_type_title AS optt ON optt.option_type_id = optv.option_type_id WHERE prod.has_options = 1 AND opt.title = 'STONE QUALITY' group by title2)");
		$stoneQualityLabel = array();

		$diamondQualityHtml = "<div class='dropdown' id='diamondquality_area'>";
		$diamondQualityHtml .= "<button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Dia. Quality<span class='caret'></span></button>";
		$diamondQualityHtml .= "<ul class='dropdown-menu'>";
		foreach ($stoneQuality as $key => $value) {
			$diamondQualityHtml .= "<li class='showroom-filter-checkbox checkbox checkbox-primary'><label><input type='checkbox' name='diamondQualityChkbox' value=" . $value->title2 . " class='chk_diamondquality'/><span class='label-text'>" . $value->title2 . "</span></label></li>";
		}
		$diamondQualityHtml .= "</ul></div>";
		return $diamondQualityHtml;
	}
	//Get gold color
	public static function getGoldColor($selected = null) {

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
	public static function file_get_contents_curl($url) {

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
		}

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

	//Get metal qualities
	public static function getMetalQualities() {
		$metalQltData = DB::select("SELECT * from grp_metal_quality");
		return $metalQltData;
	}

	public static function getStoneClarities() {
		$sideStoneClarityDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0");

		return $sideStoneClarityDetails;
	}
	public static function getStoneShape() {
		$sideStoneShapeDetails = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_shape' AND EAOV.store_id = 0");
		$stoneShapeData = array();
		foreach ($sideStoneShapeDetails as $key => $value) {
			$stoneShapeData[$value->value] = $value->option_id;
		}
		return $stoneShapeData;
	}

	public static function getVendors() {
		$vendors = User::whereHas('roles', function ($q) {$q->where('name', 'Vendor');})->get();
		return $vendors;
	}
	//Get showroom inventory product list
	public static function getShowroomInventoryProducts() {
		$prod = '';
		DB::setTablePrefix('');
		$collection = InventoryHelper::getAllProductsCollection();
		if (App::environment('local')) {
			$IN = config('constants.apiurl.local.get_in');
		} else if (App::environment('test')) {
			$IN = config('constants.apiurl.test.get_in');
		} else {
			$IN = config('constants.apiurl.live.get_in');
		}
		/* echo "<pre>";
			echo $IN;
			echo $collection->count();
		*/
		$collection = $collection->where("inventory_status_value", $IN);
		//echo $collection->count();exit;
		$productCollection = $collection->take(25);
		$productData = array('totalCount' => $collection->count(), 'productCollection' => $productCollection);
		DB::setTablePrefix('dml_');
		return $productData;
	}
	//Get approval inventory product list
	public static function getApprovalInventoryProducts() {
		$prod = '';
		DB::setTablePrefix('');
		$collection = InventoryHelper::getAllProductsCollection();
		$collection = $collection->where("inventory_status_value", "Out");
		/*DB::setTablePrefix('dml_');
			$approvalProducts = ApprovalMemoHistroy::select(DB::raw('GROUP_CONCAT(product_id) AS product_ids'))->where('status','=',DB::raw("'approval'"))->get()->first();
			$approvalProdIds = $approvalProducts->product_ids;
			$approvalProdIds = explode(',',$approvalProdIds);
		*/
		DB::setTablePrefix('');
		$approvalProducts = DB::table('dml_approval_memo_histroy as memo_histroy')->select('memo_histroy.product_id')->join('dml_approval_memo as memo', 'memo.id', '=', 'memo_histroy.approval_memo_id')->where('memo.is_delivered', '=', DB::raw("'1'"))->where('memo_histroy.status', '=', DB::raw("'approval'"))->get();
		$approvalPrdIds = array();
		foreach($approvalProducts as $product)
		{
			$approvalPrdIds[] = $product->product_id;
		}
		$collection = $collection->whereIn('entity_id', $approvalPrdIds);
		$productCollection = $collection->take(10);
		$productData = array('totalCount' => $collection->count(), 'productCollection' => $productCollection);
		DB::setTablePrefix('dml_');
		return $productData;
	}

	//Get sold inventory product list
	public static function getSoldInventoryProducts() {
		$prod = '';
		DB::setTablePrefix('');
		$collection = InventoryHelper::getAllProductsCollection();
		$collection = $collection->where("inventory_status_value", "Sold Out");
		$productCollection = $collection->take(10);
		$productData = array('totalCount' => $collection->count(), 'productCollection' => $productCollection);
		DB::setTablePrefix('dml_');
		return $productData;
	}
	//Get all stock product list
	public static function getAllStockProducts() {
		$prod = '';
		DB::setTablePrefix('');
		$collection = InventoryHelper::getAllProductsCollection();
		//echo "<pre>";
		//print_r($collection->pluck(['entity_id']));exit;
		$productCollection = $collection->take(10);
		$productData = array('totalCount' => $collection->count(), 'productCollection' => $productCollection);
		DB::setTablePrefix('dml_');
		return $productData;
	}

	public static function getProductInvoiceMemoDetail($productId) {
		DB::setTablePrefix('');
		$invoiceMemo = DB::table("catalog_product_flat_1")->select("approval_invoice_generated", "approval_memo_generated", "return_memo_generated")->where("entity_id", "=", DB::raw("$productId"))->get()->first();
		return $invoiceMemo;
		DB::setTablePrefix('dml_');
	}

	// Get scanning list count
	public static function isExistInScanningList($certificate_no) {
		$id = Auth::User()->id;

		$scanning = QrcodeScanning::where('certificate_no', '=', DB::raw("'$certificate_no'"))->where('created_by', $id)->orWhere('product_id', '=', DB::raw("'$certificate_no'"))->get()->first();
		if (!empty($scanning)) {
			//echo "true";exit;
			return true;
		} else {
			//echo "false";exit;
			return false;
		}
	}

	public static function getProductQRImagePathFromImage($certificate_no, $imagename, $size = false) {

		if (empty($imagename)) {
			if ($size) {
				if (file_exists(public_path(config('constants.dir.qrcode_images') . $certificate_no . '_' . $size . '.png'))) {
					return url(config('constants.dir.qrcode_images') . $certificate_no . '_' . $size . '.png');
				} else {
					return url('images/no-qr-available.jpg');
				}
			} else {
				if (file_exists(public_path(config('constants.dir.qrcode_images') . $certificate_no . '.png'))) {
					return url(config('constants.dir.qrcode_images') . $certificate_no . '.png');
				} else {
					//return false;
					return url('images/no-qr-available.jpg');
				}
			}
		} else {
			if (App::environment('local')) {
				return config('constants.local.qrcode_old_image_url_prefix') . $imagename; // $qrcode_old->qrcode_img
			} else {
				return config('constants.live.qrcode_old_image_url_prefix') . $imagename; // $qrcode_old->qrcode_img
			}
		}
	}

	public static function getProductQRImage($certificate_no, $size = false) {

		$qrcode_old = false;

		DB::setTablePrefix('');
		$qrcode_old_data = DB::select("SELECT qr.*,prods.certificate_no FROM `qrcode` as qr join catalog_product_flat_1 as prods on qr.product_id = prods.entity_id WHERE prods.certificate_no = '" . $certificate_no . "'");
		DB::setTablePrefix('dml_');

		if (!empty($qrcode_old_data)) {
			$qrcode_old = isset($qrcode_old_data[0]->qrcode_img) ? trim($qrcode_old_data[0]->qrcode_img) : false;
			/*DB::setTablePrefix('');
				$qrcode_old = DB::table("qrcode")->select("qrcode_img")->where("product_id", "=", DB::raw("$productId"))->get()->first();
			*/
			//var_dump($qrcode_old);exit;
		}

		//var_dump($qrcode_old);exit;

		if (empty($qrcode_old)) {
			if ($size) {
				if (file_exists(public_path(config('constants.dir.qrcode_images') . $certificate_no . '_' . $size . '.png'))) {
					return url(config('constants.dir.qrcode_images') . $certificate_no . '_' . $size . '.png');
				} else {
					return url('images/no-qr-available.jpg');
				}
			} else {
				if (file_exists(public_path(config('constants.dir.qrcode_images') . $certificate_no . '.png'))) {
					return url(config('constants.dir.qrcode_images') . $certificate_no . '.png');
				} else {
					//return false;
					return url('images/no-qr-available.jpg');
				}
			}
		} else {
			if (App::environment('local')) {
				return config('constants.local.qrcode_old_image_url_prefix') . $qrcode_old; // $qrcode_old->qrcode_img
			} else {
				return config('constants.live.qrcode_old_image_url_prefix') . $qrcode_old; // $qrcode_old->qrcode_img
			}
		}
	}
	public static function getMetalDataNew($productId) {

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
		if (!empty($result)) {
			return json_decode($result);
		}
	}
}