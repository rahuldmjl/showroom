<?php
namespace App\Helpers;
use App;
use App\CatalogCategoryEntityVarchar;
use App\MetalQuality;
use App\Products;
use Config;
use Illuminate\Support\Facades\DB;

class ProductHelper {
	public static function _toGetMetalQualityId($metalQuality, $metalColor) {
		$metalColor = substr($metalColor, 0, 3);
		$metalValue = $metalQuality . " " . $metalColor;
		DB::setTablePrefix('');
		$metalQualityId = MetalQuality::where('metal_quality', 'like', '%' . $metalValue . '%')->select('grp_metal_quality_id')->pluck('grp_metal_quality_id')->first();
		DB::setTablePrefix('dml_');
		return $metalQualityId;
	}

	public static function _toGetAttributeSetId($productCategory) {

		$categoryArr = array('Earrings' => '16', 'Rings' => '14', 'Pendants' => '18', 'Necklace' => '33', 'Diamond' => '15', 'Nosepin' => '36', 'Pendant with Earring' => '22', 'Bangles' => '17', 'Bracelets' => '23','PENDANTS & SETS' => '287');
		foreach ($categoryArr as $categorykey => $categoryVal) {
			if (strtolower($categorykey) == strtolower($productCategory)) {
				$attributeSetId = $categoryVal;
			}
		}
		return $attributeSetId;
	}

	public static function _toGetCategoryId($productCategory) {

		$attribute_id = Config::get('constants.product.attribute__id'); //41
		DB::setTablePrefix('');
		$productCategory = CatalogCategoryEntityVarchar::where('value', $productCategory)->where('attribute_id', $attribute_id)->select('entity_id')->pluck('entity_id')->first();
		DB::setTablePrefix('dml_');
		return $productCategory;
	}

	public static function _toGetDiamondShapeId($diamondShape) {

		if ($diamondShape == "RD") {
			$diamondShape = "ROUND";
		}
		$attributeId = "141";
		$diamondShapeId = DB::table(DB::raw('eav_attribute_option'))
			->join(DB::raw('eav_attribute_option_value'), DB::raw('eav_attribute_option.option_id'), '=', DB::raw('eav_attribute_option_value.option_id'))
			->select(DB::raw('eav_attribute_option.option_id'))
			->where(DB::raw('eav_attribute_option.attribute_id'), '=', $attributeId)
			->where(DB::raw('eav_attribute_option_value.value'), '=', $diamondShape)
			->pluck(DB::raw('eav_attribute_option.option_id'))
			->first();
		return $diamondShapeId;
	}

	public static function _toGetDiamondClarityId($diamondClarity) {

		$attributeId = "145";
		$diamondClarityId = DB::table(DB::raw('eav_attribute_option'))
			->join(DB::raw('eav_attribute_option_value'), DB::raw('eav_attribute_option.option_id'), '=', DB::raw('eav_attribute_option_value.option_id'))
			->select(DB::raw('eav_attribute_option.option_id'))
			->where(DB::raw('eav_attribute_option.attribute_id'), '=', $attributeId)
			->where(DB::raw('eav_attribute_option_value.value'), '=', $diamondClarity)
			->pluck(DB::raw('eav_attribute_option.option_id'))
			->first();
		return $diamondClarityId;
	}

	public static function _toGetMetalQualityValue($metalQualityId) {

		DB::setTablePrefix('');
		$metalQualityVal = MetalQuality::where('grp_metal_quality_id', $metalQualityId)->select('metal_quality')->pluck('metal_quality')->first();
		DB::setTablePrefix('dml_');
		return $metalQualityVal;
	}

	public static function _toGetDiamondShapeValue($diamondShapeId) {

		$attributeId = "141";
		$diamondShapeVal = DB::table(DB::raw('eav_attribute_option'))
			->join(DB::raw('eav_attribute_option_value'), DB::raw('eav_attribute_option.option_id'), '=', DB::raw('eav_attribute_option_value.option_id'))
			->select(DB::raw('eav_attribute_option_value.value'))
			->where(DB::raw('eav_attribute_option.attribute_id'), '=', $attributeId)
			->where(DB::raw('eav_attribute_option_value.option_id'), '=', $diamondShapeId)
			->pluck(DB::raw('eav_attribute_option_value.value'))
			->first();
		return $diamondShapeVal;
	}

	public static function _toGetDiamondClarityValue($diamondClarityId) {

		$attributeId = "145";
		$diamondClarityVal = DB::table(DB::raw('eav_attribute_option'))
			->join(DB::raw('eav_attribute_option_value'), DB::raw('eav_attribute_option.option_id'), '=', DB::raw('eav_attribute_option_value.option_id'))
			->select(DB::raw('eav_attribute_option_value.value'))
			->where(DB::raw('eav_attribute_option.attribute_id'), '=', $attributeId)
			->where(DB::raw('eav_attribute_option_value.option_id'), '=', $diamondClarityId)
			->pluck(DB::raw('eav_attribute_option_value.value'))
			->first();
		return $diamondClarityVal;
	}

	public static function _toGetCategoryVal($productCategoryId) {

		$attribute_id = Config::get('constants.product.attribute__id'); //41
		DB::setTablePrefix('');
		$productCategoryVal = CatalogCategoryEntityVarchar::where('entity_id', $productCategoryId)->where('attribute_id', $attribute_id)->select('value')->pluck('value')->first();

		DB::setTablePrefix('dml_');
		return $productCategoryVal;
	}

	public static function _toGetDiamondShapeIdMultiple($diamondShape) {
		$ExpdiamondShape = explode(",", $diamondShape);
		foreach ($ExpdiamondShape as $key => $ExpdiamondShapevalue) {
			if ($ExpdiamondShapevalue == "RD") {
				$ExpdiamondShapevalue = "ROUND";
			}
			$attributeId = "141";
			$diamondShapeId[] = DB::table(DB::raw('eav_attribute_option'))
				->join(DB::raw('eav_attribute_option_value'), DB::raw('eav_attribute_option.option_id'), '=', DB::raw('eav_attribute_option_value.option_id'))
				->select(DB::raw('eav_attribute_option.option_id'))
				->where(DB::raw('eav_attribute_option.attribute_id'), '=', $attributeId)
				->where(DB::raw('eav_attribute_option_value.value'), '=', $ExpdiamondShapevalue)
				->pluck(DB::raw('eav_attribute_option.option_id'))
				->first();
		}
		$ImpdiamondShapeId = implode(",", $diamondShapeId);
		return $ImpdiamondShapeId;
	}

	public static function _toGetDiamondClarityIdMultiple($diamondClarity) {
		$ExpdiamondClarity = explode(",", $diamondClarity);
		foreach ($ExpdiamondClarity as $key => $ExpdiamondClarityvalue) {
			if ($ExpdiamondClarityvalue == "RD") {
				$ExpdiamondClarityvalue = "ROUND";
			}
			$attributeId = "145";
			$diamondClarityId[] = DB::table(DB::raw('eav_attribute_option'))
				->join(DB::raw('eav_attribute_option_value'), DB::raw('eav_attribute_option.option_id'), '=', DB::raw('eav_attribute_option_value.option_id'))
				->select(DB::raw('eav_attribute_option.option_id'))
				->where(DB::raw('eav_attribute_option.attribute_id'), '=', $attributeId)
				->where(DB::raw('eav_attribute_option_value.value'), '=', $ExpdiamondClarityvalue)
				->pluck(DB::raw('eav_attribute_option.option_id'))
				->first();
		}
		$ImpdiamondClarityId = implode(",", $diamondClarityId);
		return $ImpdiamondClarityId;
	}

	//Get product information by certificate
	public static function getProductDataByCertificate($certificate) {
		DB::setTablePrefix('');
		$productData = DB::select("SELECT * from catalog_product_flat_1 where certificate_no = '" . $certificate . "'");
		if (count($productData) <= 0) {
			$productData = DB::select("SELECT * from dml_products where certificate_no = '" . $certificate . "'");
		}
		DB::setTablePrefix('dml_');
		return $productData;
	}

	public static function getMetalDataByCertificate($certificate) {
		$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->where('certificate_no', '=', DB::raw("'$certificate'"))->get()->first();

		$metal_data = array();

		if (count($dmlProductData) > 0) {

			if (count($dmlProductData->metals->toArray()) > 0) {
				$product_metal = $dmlProductData->metals->toArray();
			} else {
				$dmlProductData = DB::select("SELECT * from catalog_product_flat_1 WHERE certificate_no = '" . $certificate . "'");
				$product_id = $dmlProductData[0]->entity_id;

				if (!empty($product_id)) {
					$product_metal = DB::select("SELECT * FROM grp_metal WHERE metal_product_id = " . $product_id);
				}
			}
		} else {

			$dmlProductData = DB::select("SELECT * from catalog_product_flat_1 WHERE certificate_no = '" . $certificate . "'");
			$product_id = $dmlProductData[0]->entity_id;
			if (!empty($product_id)) {
				$product_metal = DB::select("SELECT * FROM grp_metal WHERE metal_product_id = " . $product_id);
			}
		}
		//var_dump($product_metal);exit;
		if (count($product_metal) > 0) {
			//var_dump($MetalQualityDetails);
			$product_metal_data = $product_metal[0];
			$MetalTypeDetailsData = DB::select("SELECT EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_type' AND EAOV.option_id = " . $product_metal_data->metal_type_id . " AND EAOV.store_id = 0");
			if (!empty($MetalTypeDetailsData)) {
				$MetalTypeValue = $MetalTypeDetailsData[0]->value;
			}
			//var_dump($MetalTypeValue);
			$MetalQualityDetailsData = DB::select("SELECT EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'metal_quality' AND EAOV.option_id = " . $product_metal_data->metal_quality_id . " AND EAOV.store_id = 0");
			if (!empty($MetalQualityDetailsData)) {
				$MetalQualityValue = $MetalQualityDetailsData[0]->value;
			}
			//var_dump($MetalQualityValue);

			//var_dump($product_metal_data);exit;
			$metal_data['type'] = $MetalTypeValue;
			$metal_data['quality'] = $MetalQualityValue;

			if (!empty($dmlProductData)) {
				/* echo "<pre>";
				print_r($dmlProductData[0]);exit; */
				if ($dmlProductData[0]->per_gm_rate) {
					$product_per_gm_rate = (float) $dmlProductData[0]->per_gm_rate;
					$product_metal_labour_charge = isset($dmlProductData[0]->metal_labour_charge) ? (float) $dmlProductData[0]->metal_labour_charge : 0;
					if ($product_per_gm_rate > 0) {
						$metal_data['per-gm-rate'] = round((float) $product_per_gm_rate);
					}
				}

			}

			$metal_data['metal_labour_charge'] = round((float) $product_metal_data->metal_labour_charge);

			if (empty($metal_data['per-gm-rate'])) {
				$grp_metal_quality_data = DB::select("SELECT * FROM grp_metal_quality WHERE grp_metal_quality_id = " . $product_metal_data->metal_quality_id);
				$metal_data['per-gm-rate'] = round($grp_metal_quality_data[0]->rate);
				$prodOptions = DB::select("SELECT * FROM catalog_product_option WHERE product_id = " . $product_id);
				//var_dump($prodOptions);
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

				$categoryidsData = DB::select("SELECT `ccp`.*, `ccev`.`value` FROM `catalog_category_product` as `ccp` LEFT JOIN `catalog_category_entity_varchar` as `ccev` ON `ccp`.`category_id` = `ccev`.`entity_id` WHERE `ccp`.`product_id` = " . $product_id . " and `ccev`.`attribute_id` = '41'");
				if (count($categoryidsData) > 0) {
					$categoryData = $categoryidsData[0];
					//var_dump($categoryData);
					//var_dump($dmlProductData[0]->belt_price);
					if ($categoryData->category_id == '295' && ($categoryData->value) == 'RUBBER BRACELETS') {
						$actualMetalWeight = $prodMetalWeight;
						$BeltPrice = $dmlProductData[0]->belt_price;
						$ExtraPrice = $dmlProductData[0]->extra_price;
					}

				}

			}

			return $metal_data;
		}

		exit;
	}
	
	public static function getProductPrice($params = null) {
		$productCost = 0;
		$certificate = isset($params['certificate']) ? $params['certificate'] : '';
		$mealWeight = isset($params['metal_weight']) ? $params['metal_weight'] : 0;
		$discount = isset($params['discount_amount']) ? $params['discount_amount'] : 0;
		$shippingCharge = isset($params['shipping_charge']) ? $params['shipping_charge'] : 0;
		$customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
		$productData = ProductHelper::getProductDataByCertificate($certificate);
		//Get price markup for customer
		$customerPriceMarkup = 0;
		if (!empty($customerId)) {
			$customerPriceMarkup = (float) CustomersHelper::getCustomerAttrValue($customerId, 'price_markup');
		}
		//var_dump($productData);exit;
		$productId = isset($productData[0]->entity_id) ? $productData[0]->entity_id : '';
		$perGramLabourCharge = InventoryHelper::getPerGramLabourCharge($productId);

		$metalData = (array) InventoryHelper::getMetalData($productId);
		//print_r($metalData);exit;
		//($metalData);exit;
		$labourCharge = 0;
		$perGramRate = 0;
		foreach ($metalData as $key => $metal) {
			if ($key == 'labour-charge') {
				$labourCharge = $metal;
				$labourCharge = str_replace('Rs.', '', $labourCharge);
				$labourCharge = str_replace(',', '', $labourCharge);
				$labourCharge = trim($labourCharge);
			} else if ($key == 'per-gm-rate') {
				$perGramRate = str_replace('Rs.', '', $metal);
				$perGramRate = str_replace(',', '', $perGramRate);
				$perGramRate = trim($perGramRate);
			}
		}
		//Get metal per gram rate from catalog
		$productPerGramRate = (float) InventoryHelper::getProductAttribute($productId, 'per_gm_rate');

		if (!empty($productPerGramRate)) {
			$perGramRate = (float) $productPerGramRate;
		}

		$perGramLabourCharge = (float) $labourCharge / (float) $metalData['weight'];
		$labourCharge = $perGramLabourCharge * $mealWeight;
		$productOnlyMetalCost = $perGramRate * $mealWeight;
		$productMetalCost = $productOnlyMetalCost + $labourCharge;
		//echo $perGramRate."  ".$mealWeight."   ".$productMetalCost."   ".$labourCharge;exit;
		/* echo '<pre>';
		print_r($productMetalCost);exit; */
		//var_dump($productMetalCost);

		$productStoneData = InventoryHelper::getStoneData($productId);
		//echo '<pre>';
		//print_r($productStoneData);exit;
		//$productStoneData'per_carat_rate_digit'] *
		//var_dump($productStoneData);exit;
		$totalStoneArr = $productStoneData['totalcts'];

		$productStoneCost = 0;
		foreach ($totalStoneArr as $totStoneKey => $totalStone) {
			$stoneweight = $totalStone * $productStoneData['stone_use'][$totStoneKey];
			//$stoneCost = $stoneweight * $productStoneData['stone_price'][$totStoneKey];
			$stoneCost = $productStoneData['stone_price'][$totStoneKey];
			$productStoneCost += (float) $productStoneData['stone_price'][$totStoneKey];
		}

		$productCost = (((float) $productMetalCost + (float) $productStoneCost + (float) $shippingCharge) - (float) $discount);
		$response['status'] = true;
		$unitPrice = round((float) $productMetalCost + (float) $productStoneCost, 0);
		$totalPrice = round($productCost, 0);

		//Add customer markup
		if (!empty($customerPriceMarkup)) {
			$markupAmount = ($unitPrice * (float) $customerPriceMarkup) / 100;
			$unitPrice += $markupAmount;
			$totalPrice += $markupAmount;
		}
		$productPriceMarkup = (float) InventoryHelper::getProductAttribute($productId, 'price_markup');
		$beltPrice = ProductHelper::getBeltPriceByProduct($productId);
		//$extraPrice = ProductHelper::getExtraPriceByProduct($productId);
		$gemStoneData = InventoryHelper::getGemStoneData($productId);
		
		$gemStonePrice = isset($gemStoneData['stone_price']) ? array_sum($gemStoneData['stone_price']) : 0;
		//print_r($gemStoneData);exit;
		$unitPrice += (float)$beltPrice + (float)$gemStonePrice;
		$totalPrice += (float)$beltPrice + (float)$gemStonePrice;
		if (!empty($productPriceMarkup)) {
			$markupAmount = ($unitPrice * (float) $productPriceMarkup) / 100;
			$unitPrice += $markupAmount;
			$totalPrice += $markupAmount;
		}
		/* echo "stone price  ".$productStoneCost."<br>";
		echo "metal price  ".$productMetalCost."<br>";
		echo "belt price ".$beltPrice."<br>";
		echo "extra price ".$extraPrice."<br>";
		echo "gem stone price ".$gemStonePrice."<br>";
		echo "shipping charge  ".$shippingCharge;
		echo "discount  ".$discount;exit; */
		
		$response['unit_price'] = $unitPrice;
		$response['total_price'] = $totalPrice;
		/* echo (float)$productMetalCost."<br>";
			echo (float)$productStoneCost."<br>";
			echo (float)$shippingCharge."<br>";
		*/
		echo json_encode($response);exit;
	}
	//Get belt price by product
	public static function getBeltPriceByProduct($productId)
	{
		DB::setTablePrefix('');
		$beltPrice = DB::table('catalog_product_flat_1')->select('belt_price')->where('entity_id','=',DB::raw("$productId"))->get()->first();
		$beltPrice = isset($beltPrice->belt_price) ? $beltPrice->belt_price : 0;
		DB::setTablePrefix('dml_');
		return $beltPrice;
	}
	//Get extra price by product
	public static function getExtraPriceByProduct($productId)
	{
		DB::setTablePrefix('');
		$extraPrice = DB::table('catalog_product_flat_1')->select('extra_price')->where('entity_id','=',DB::raw("$productId"))->get()->first();
		$extraPrice = isset($extraPrice->extra_price) ? $extraPrice->extra_price : 0;
		DB::setTablePrefix('dml_');
		return $extraPrice;
	}


	
}
?>