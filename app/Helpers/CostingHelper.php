<?php
namespace App\Helpers;
use Config;
use DB;
use Excel;
use PHPExcel_Worksheet_Drawing;
use App\VendorHandlingCharges;
use App\Setting;


class CostingHelper {
	public static function getImportValidateWithoutJobwork($CostingData, $postData) {

		$lineCounter = 1;
		$VendorId = $postData['vendor_id'];
		$MainReturnedArr = array();
		foreach ($CostingData as $CostingCollectionkey => $CostingCollection) {
			$CostingCollectionLabourAmount = $CostingCollection['labouramount'];
			$CertificateNumber = $CostingCollection['certificate_no'];
			$CostingCollectionItemNo = $CostingCollection['item']; 
			$MetalWeight = $CostingCollection['metal_weight'];
			$ProductCategory = $CostingCollection['product_category'];
			$Diamondtype = $CostingCollection['material_type'];
			$Color = $CostingCollection['color'];
			$StoneMaterialQuality = $CostingCollection['material_quality'];
			$Diamondtype = $CostingCollection['material_type'];
			$StoneMaterialWeight = $CostingCollection['material_weight'];
			$Count = count(explode(",", $StoneMaterialWeight));
			$StoneMaterialpcs = $CostingCollection['material_pcs'];
			$CostingCollectionStoneRate = $CostingCollection['stone_rate'];
			$MetalKarat = $CostingCollection['metal_karat'];
			$CostingCollectionMetalRate = (empty($CostingCollection['metalrate']) ? '0' : $CostingCollection['metalrate']);
			$PostDataigi = $postData['igi_charges'];
			$CostingigiCharges = $CostingCollection['igi_charges'];
			$CostingCollectionHalmarking = $CostingCollection['hallmarking'];
			$PostDataHalmarking = $postData['hallmarking'];
			$CostingCollectionMetalAmount = (empty($CostingCollection['metalamount']) ? '0' : $CostingCollection['metalamount']);
			$CostingCollectionTotalStoneAmount = $CostingCollection['total_stone_amount'];
			$CostingCollectionTotalAmount = $CostingCollection['total_amount'];
			$CostingCollectionMaterialWeight = $CostingCollection['material_weight'];
			$CostingCollectionStoneAmount = $CostingCollection['stone_amount'];
			$CostingCollectionExtraPrice = $CostingCollection['extra_price'];
			$CostingCollectionCGST = $CostingCollection['cgst'];
			$CostingCollectionSGST = $CostingCollection['sgst'];
			$postLabourChargeType = $postData['labour_charge_type'];
			$LineCounterMsg = ' at row no.'.$lineCounter;

			// * check for labour charges * //
			$MainLaberCharges = CostingHelper::GetLabourCharge($MetalWeight, $ProductCategory, $Diamondtype, $Color, $VendorId);
			if (empty($MainLaberCharges) || $MainLaberCharges == '0') {
				$MainReturnedArr['code'][] = 3;
				$MainReturnedArr['messages'][] = Config::get('constants.message.labour_charge_not_added').$LineCounterMsg;
			}

			// * check for certificates already done QC *//

			if(!empty($CertificateNumber)) {
				$Certi = CostingHelper::CertificateValidation($CertificateNumber);
				if ($Certi['status'] == 'failure') {
					$MainReturnedArr['code'][] = 4;
					$MainReturnedArr['messages'][] = $CertificateNumber . Config::get('constants.message.certificate_exsist').$LineCounterMsg;
				}
			}

			if(!empty($CostingCollectionItemNo)) {      	 						
				$Item = CostingHelper::ItemValidation($CostingCollectionItemNo);
				if ($Item['status'] == 'failure') {
					$MainReturnedArr['code'][] = 4;
					$MainReturnedArr['messages'][] = $CostingCollectionItemNo . Config::get('constants.message.item_exsist').$LineCounterMsg;
				}
			}

			// *check for vendor stone price *//
			$MainStoneRate = CostingHelper::CheckStonePriceWOJ($StoneMaterialQuality, $Diamondtype, $StoneMaterialWeight, $VendorId, $Count, $StoneMaterialpcs, $CostingCollectionStoneRate,$LineCounterMsg);
			if (is_array($MainStoneRate['data'])) {
				foreach ($MainStoneRate['data'] as $singlemsg) {
					$MainReturnedArr['messages'][] = $singlemsg;
				}
			} else {
				$MainReturnedArr['messages'][] = $MainStoneRate['data'];
			}

			
			// *check for vendor metal price *//
			$MainMetalRate = CostingHelper::CheckMetalRateWOJ($Color, $MetalKarat, $VendorId, $CostingCollectionMetalRate,$LineCounterMsg);
			if (isset($MainMetalRate['status'])) {
				if ($MainMetalRate['status'] == 'failure') {
					$MainReturnedArr['messages'][] = $MainMetalRate['data'];
				}
			}

			// *check for wrong columns *//
			$wrongColsResponse[] = CostingHelper::checkForWrongColumns($MetalWeight, $ProductCategory, $Diamondtype, $Color, $VendorId, $CostingCollectionLabourAmount, $CostingCollectionkey, $CostingCollection, $MetalKarat, $CostingCollectionMetalRate, $CostingCollectionMetalAmount, $CostingCollectionTotalStoneAmount, $CostingCollectionTotalAmount, $CostingCollectionStoneRate, $StoneMaterialQuality, $StoneMaterialWeight, $Count, $StoneMaterialpcs, $CostingCollectionMaterialWeight, $CostingCollectionStoneAmount,$CertificateNumber,$CostingCollectionItemNo,$LineCounterMsg,$CostingCollectionExtraPrice,$CostingCollectionCGST,$CostingCollectionSGST,$postLabourChargeType);
			$lineCounter++;
		}
		//exit;

		foreach ($wrongColsResponse as $wrongColsRes) {
			if (isset($wrongColsRes) && array_key_exists('msg', $wrongColsRes)) {
				if (is_array($wrongColsRes['msg'])) {
					foreach ($wrongColsRes['msg'] as $singlemsg) {
						$MainReturnedArr['messages'][] = $singlemsg;
					}
				} else {
					$MainReturnedArr['messages'][] = $wrongColsRes['msg'];
				}
			}
		}
		$wrongColsResponseArr = CostingHelper::getWrongResponseArr($wrongColsResponse);
		if (!empty($MainReturnedArr)) {
			$jobworkstatus = 0;
			$sheetname = CostingHelper::DownloadExcel($wrongColsResponseArr, $VendorId, $CostingData,$jobworkstatus);
			$MainReturnedArr['sheetname'][] = $sheetname . ".xlsx";
		}
		return $MainReturnedArr;
	}

	public static function getWrongResponseArr($wrongColsResponseCollection) {
		foreach ($wrongColsResponseCollection as $wrongColsResponseColl) {
			$ColumnDatasArr = array();
			if (isset($wrongColsResponseColl['wrong'])) {
				foreach ($wrongColsResponseColl['wrong'] as $Columnkey => $wrongColsResponse) {
					$WrongResponseDataArr[$Columnkey] = $wrongColsResponse;
					if ($Columnkey != 'rowno') {
						$ColumnDatasArr[$Columnkey] = $WrongResponseDataArr[$Columnkey];
					}

				}
				if (isset($wrongColsResponseColl) && array_key_exists('rowno', $wrongColsResponseColl['wrong'])) {
					$WrongResponseArr[$wrongColsResponseColl['wrong']['rowno']] = $ColumnDatasArr;
				}
			}
		}
		if (empty($WrongResponseArr)) {
			$WrongResponseArr = array();
			return $WrongResponseArr;
		}
		return $WrongResponseArr;
	}
	public static function DownloadExcel($wrongColsResponseArr, $VendorId, $CostingCollection,$jobworkstatus) {
		$serialnumber = 0;
	
		//start header 
			$tmparray = array();
			array_push($tmparray, "Sr No.", "Color or Hand Design");

		
			if (!empty($CostingCollection[0]['certificate_no'])) {
				$idcolumn = "Certificate";
			}
			else {
				$idcolumn = "Item #";
			}
			array_push($tmparray, $idcolumn);

			array_push($tmparray, "Sku no","Po no","Order no","Style#", "Metal Karat", "Color", "Product Category", "Gross Wt", "Net Metal Wt.", "Metal Rate", "Metal Amt", "Labour amt", "Total Diamond Pcs", "Total Diamond Wt.", "Total Color Stone Pcs", "Total Color Stone Wt", "Material Category", "Material Type", "Material Inter. Quality","Seive Size","Material MM Size", "Material pices", "Material Weight", "Stone Rate", "Total Stone amt");
			
			if (!empty($CostingCollection[0]['cgst'])) {
				$taxcolumn1 = "Cgst";
				array_push($tmparray, $taxcolumn1);
			}

			if (!empty($CostingCollection[0]['sgst'])) {
				$taxcolumn2 = "Sgst";
				array_push($tmparray, $taxcolumn2);
			}

			if($jobworkstatus == 1) {
				if (isset($CostingCollection[0]['igi'])) {
					$igi_header = "IGI";
					array_push($tmparray, $igi_header);
				}
				if (isset($CostingCollection[0]['hallmarking'])) {
					$hallmarking_header = "Hallmarking";
					array_push($tmparray, $hallmarking_header);
				}
			}
			array_push($tmparray,"Extra Price","Extra Price For");
			$totalAmt = "Total amt";
			array_push($tmparray, $totalAmt);

		//end header 

	 	
		$sheet = array($tmparray);

		foreach ($CostingCollection as $Columnkey => $result) {
			$tmparray = array();
			$serialnumber = $serialnumber + 1;
			array_push($tmparray, $serialnumber);
			$images = (!empty($result['image']) ? $result['image'] : false);
			array_push($tmparray, $images);
			
			
			if(!empty($result['item'])) {
				if (!empty($wrongColsResponseArr[$Columnkey]['item'])) {
					$item = 'WRONG_' . trim($wrongColsResponseArr[$Columnkey]['item']);
					array_push($tmparray, $item);
				} else {
					$item = trim($result['item']);
					array_push($tmparray, $item);
				}
			}
			else {
				if (!empty($wrongColsResponseArr[$Columnkey]['certificate_no'])) {
					$certificate_no = 'WRONG_' . trim($wrongColsResponseArr[$Columnkey]['certificate_no']);
					array_push($tmparray, $certificate_no);
				} else {
					$certificate_no = trim($result['certificate_no']);
					array_push($tmparray, $certificate_no);
				}
			}


			$sku = trim($result['sku']);
			array_push($tmparray, $sku);

			$po_no = trim($result['po_no']);
			array_push($tmparray, $po_no);

			$order_no = trim($result['order_no']);
			array_push($tmparray, $order_no);
			$style = trim($result['style']);
			array_push($tmparray, $style);
			$metalkarat = trim($result['metal_karat']);
			array_push($tmparray, $metalkarat);
			$color = trim($result['color']);
			array_push($tmparray, $color);
			$product_category = trim($result['product_category']);
			array_push($tmparray, $product_category);
			$gross_weight = trim($result['gross_weight']);
			array_push($tmparray, $gross_weight);
			$metalweight = trim($result['metal_weight']);
			array_push($tmparray, $metalweight);

			if (isset($wrongColsResponseArr[$Columnkey]['metalrate'])) {
				if ($wrongColsResponseArr[$Columnkey]['metalrate'] !== "") {
					$metalrate = 'WRONG_' . trim($wrongColsResponseArr[$Columnkey]['metalrate']);
					array_push($tmparray, $metalrate);
				} else {
					$resultmetalrate = $result['metalrate'];
					if (empty($resultmetalrate)) {$resultmetalrate = 0;}
					$metalrate = trim($resultmetalrate);
					array_push($tmparray, $metalrate);
				}
			} else {
				$resultmetalrate = $result['metalrate'];
				if (empty($resultmetalrate)) {$resultmetalrate = 0;}
				$metalrate = trim($resultmetalrate);
				array_push($tmparray, $metalrate);
			}

			if (isset($wrongColsResponseArr[$Columnkey]['metalamount'])) {
				if ($wrongColsResponseArr[$Columnkey]['metalamount'] !== "") {
					$metalamount = 'WRONG_' . trim($wrongColsResponseArr[$Columnkey]['metalamount']);
					array_push($tmparray, $metalamount);
				} else {
					$resultmetalamount = $result['metalamount'];
					if (empty($resultmetalamount)) {$resultmetalamount = 0;}
					$metalamount = trim($resultmetalamount);
					array_push($tmparray, $metalamount);
				}
			} else {
				$resultmetalamount = $result['metalamount'];
				if (empty($resultmetalamount)) {$resultmetalamount = 0;}
				$metalamount = trim($resultmetalamount);
				array_push($tmparray, $metalamount);
			}

			if (!empty($wrongColsResponseArr[$Columnkey]['labouramount'])) {
				$labouramount = 'WRONG_' . trim($wrongColsResponseArr[$Columnkey]['labouramount']);
				array_push($tmparray, $labouramount);
			} else {
				$labouramount = $result['labouramount'];
				array_push($tmparray, $labouramount);
			}

			$total_diamondpcs = trim($result['diamond_pcs']);
			array_push($tmparray, $total_diamondpcs);
			$total_diamondwt = trim($result['diamond_weight']);
			array_push($tmparray, $total_diamondwt);
			$colorstone_pcs = trim($result['colorstone_pcs']);
			array_push($tmparray, $colorstone_pcs);
			$colorstone_weight = trim($result['colorstone_weight']);
			array_push($tmparray, $colorstone_weight);
			$material_category = trim($result['material_category']);
			array_push($tmparray, $material_category);
			$material_type = trim($result['material_type']);
			array_push($tmparray, $material_type);
			$material_quality = trim($result['material_quality']);
			array_push($tmparray, $material_quality);
			$material_sieve_size = trim($result['seive_size']);
			array_push($tmparray, $material_sieve_size);
			$material_mm_size = trim($result['material_mm_size']);
			array_push($tmparray, $material_mm_size);
			
			$material_pcs = trim($result['material_pcs']);
			array_push($tmparray, $material_pcs);
			$metal_weight = trim($result['material_weight']);
			array_push($tmparray, $metal_weight);

			if($jobworkstatus == 1) {
				if (!empty($wrongColsResponseArr[$Columnkey]['stone_rate']) || !empty($wrongColsResponseArr[$Columnkey]['stone_rate_right'])) {

					$stonerateArr = array();

					if (!empty($wrongColsResponseArr[$Columnkey]['stone_rate'])) {
						if (strpos($wrongColsResponseArr[$Columnkey]['stone_rate'], ',') !== false) {
							$TmpArr = explode(",", $wrongColsResponseArr[$Columnkey]['stone_rate']);
							foreach ($TmpArr as $TmpAr) {
								$stonerateArr[] = 'WRONG_' . $TmpAr;
							}
						} else {
							$stonerateArr[] = 'WRONG_' . trim($wrongColsResponseArr[$Columnkey]['stone_rate']);
						}
					}

					if (!empty($wrongColsResponseArr[$Columnkey]['stone_rate_right'])) {
						if (strpos($wrongColsResponseArr[$Columnkey]['stone_rate_right'], ',') !== false) {
							$Tmp2Arr = explode(",", $wrongColsResponseArr[$Columnkey]['stone_rate_right']);
							foreach ($Tmp2Arr as $Tmp2Ar) {
								$stonerateArr[] = $Tmp2Ar;
							}
						} else {
							$stonerateArr[] = trim($wrongColsResponseArr[$Columnkey]['stone_rate_right']);
						}
					}

					$stonerate = implode(",", $stonerateArr);

					array_push($tmparray, $stonerate);
				} else {
					$stonerate = $result['stone_rate'];
					array_push($tmparray, $stonerate);
				}
			}
			else {
				//echo "<pre>"; print_r($wrongColsResponseArr[$Columnkey]['stone_rate']); 
				$stonerate = implode(",",$wrongColsResponseArr[$Columnkey]['stone_rate']);
				array_push($tmparray, $stonerate);
			}
			


			if (!empty($wrongColsResponseArr[$Columnkey]['total_stone_amount'])) {
				$totalstoneamount = 'WRONG_' . trim($wrongColsResponseArr[$Columnkey]['total_stone_amount']);
				array_push($tmparray, $totalstoneamount);
			} else {
				$totalstoneamount = $result['total_stone_amount'];
				array_push($tmparray, $totalstoneamount);
			}

			if (!empty($result['cgst'])) {
				if (!empty($wrongColsResponseArr[$Columnkey]['cgst'])) {
					$cgst = 'WRONG_' . trim($wrongColsResponseArr[$Columnkey]['cgst']);
					array_push($tmparray, $cgst);
				} else {
					$cgst = $result['cgst'];
					array_push($tmparray, $cgst);
				}
			}

			if (!empty($result['sgst'])) {
				if (!empty($wrongColsResponseArr[$Columnkey]['sgst'])) {
					$sgst = 'WRONG_' . trim($wrongColsResponseArr[$Columnkey]['sgst']);
					array_push($tmparray, $sgst);
				} else {
					$sgst = $result['sgst'];
					array_push($tmparray, $sgst);
				}
			}


			if($jobworkstatus == 1) {
				
				if ($result['igi_charges'] != "" ) {
					$igi_charges = trim($result['igi_charges']);
					array_push($tmparray, $igi_charges);
				}
				if ($result['hallmarking'] != "" ) {
					$hallmarking = trim($result['hallmarking']);
					array_push($tmparray, $hallmarking);
				}
			}


			//Extra Price
			$extra_price = $result['extra_price'];
			array_push($tmparray, $extra_price);

			$extra_price_for = $result['extra_price_for'];
			array_push($tmparray, $extra_price_for);

			if (!empty($wrongColsResponseArr[$Columnkey]['total_amount'])) {
				$total_amount = 'WRONG_' . trim($wrongColsResponseArr[$Columnkey]['total_amount']);
				array_push($tmparray, $total_amount);
			} else {
				$total_amount = $result['total_amount'];
				array_push($tmparray, $total_amount);
			}
			array_push($sheet, $tmparray);

			$newSheetArr = array();
			foreach ($sheet as $sheetkey => $rowData) {
				$newColumnsArr = array();
				$totalColumns = count($rowData);
				$commaColumnsNum = 0;
				$forcount = 0;
				$commaColumnsArr = array();
				foreach ($rowData as $colKey => $colValue) {
					if (strpos($colValue, ',') !== false) {
						$colValues = explode(',', $colValue);
						$newColumnsArr[] = $colValues[0];
						$multiple_value_key[] = $colKey;
						$commaColumnsArr[$commaColumnsNum] = $colValues;
						$commaColumnsNum++;
						$forcount = count($colValues);
					} else {
						$newColumnsArr[] = $colValue;
					}

					if ($colKey == ($totalColumns - 1)) {
						$newSheetArr[] = $newColumnsArr;
						if ($commaColumnsNum > 1) {
							for ($col_j = 0; $col_j < $forcount; $col_j++) {

								if ($col_j == 0) {
									continue;
								}

								$newDynColumnsArr = array();
								$counterCol = 0;
								for ($col_i = 0; $col_i < $totalColumns; $col_i++) {
									if (in_array($col_i, $multiple_value_key)) {
										$newDynColumnsArr[] = $commaColumnsArr[$counterCol][$col_j];
										$counterCol++;
									} else {
										$newDynColumnsArr[] = '';
									}
								}
								$newSheetArr[] = $newDynColumnsArr;
							}
						}
					}
				} //foreach over here.
			} //foreach over here.
		}
		$sheetname = 'costing_' . time();
		Excel::create($sheetname, function ($excel) use ($newSheetArr) {
			$excel->sheet('Sheet', function ($sheet) use ($newSheetArr) {
				foreach ($newSheetArr as $row => $columns) {
					foreach ($columns as $column => $data) {
						// if($row === 0){
						// 	$alpha_col = \PHPExcel_Cell::stringFromColumnIndex($column);
						// 	$sheet->cell($alpha_col.'1', function($cell) use ($data) {$cell->setValue($data); });
						// } else {
							if (strpos($data, 'img/') !== false) {
								$objDrawing = new PHPExcel_Worksheet_Drawing();
								$objDrawing->setName('costing_img');
								$objDrawing->setDescription('costing_img');
								$objDrawing->setPath($data);
								$rowNo = (int) $row + 1;
								$objDrawing->setCoordinates('B' . $rowNo);
								$objDrawing->setOffsetX(5);
								$objDrawing->setOffsetY(5);
								$objDrawing->setWidth(80);
								$objDrawing->setHeight(80);
								$objDrawing->setWorksheet($sheet);
								$sheet->getRowDimension($rowNo)->setRowHeight(70);
								$sheet->getColumnDimension('B')->setWidth(40);

								$newSheetArr[$row][1] = '';
							} elseif (strpos($data, 'WRONG_') !== false) {

								$column_sheet = \PHPExcel_Cell::stringFromColumnIndex($column);
								//var_dump($column_sheet);
								$row_sheet = $row + 1;
								$cell = $column_sheet . $row_sheet;
								$sheet->getStyle($cell)->getFill()->applyFromArray(array('type' => \PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('rgb' => 'FF0000')));
								$data_new = explode("WRONG_", $data);
								$newSheetArr[$row][$column] = $data_new[1];
								$tmpData = $data_new[1];
								$sheet->cell($cell, function($cel) use ($tmpData) {$cel->setValue($tmpData); });
							} else {

								$column_sheet = \PHPExcel_Cell::stringFromColumnIndex($column);
								$row_sheet = $row + 1;
								$cell = $column_sheet . $row_sheet;
								$sheet->cell($cell, function($cel) use ($data) {$cel->setValue($data); });
							}
						//}
					}
				}
				//$sheet->fromArray($newSheetArr);
			});
		})->store('xlsx', public_path('uploads/costing'));
		return $sheetname;
	}

	public static function checkForWrongColumns($MetalWeight, $ProductCategory, $Diamondtype, $Color, $VendorId, $CostingCollectionLabourAmount, $CostingCollectionkey, $CostingCollection, $MetalKarat, $CostingCollectionMetalRate, $CostingCollectionMetalAmount, $CostingCollectionTotalStoneAmount, $CostingCollectionTotalAmount, $CostingCollectionStoneRate, $StoneMaterialQuality, $StoneMaterialWeight, $Count, $StoneMaterialpcs, $CostingCollectionMaterialWeight, $CostingCollectionStoneAmount,$CertificateNumber,$CostingCollectionItemNo,$LineCounterMsg,$CostingCollectionExtraPrice,$CostingCollectionCGST,$CostingCollectionSGST,$postLabourChargeType) {
		$responseArr = array();
		$MainLaberCharges = CostingHelper::GetLabourCharge($MetalWeight, $ProductCategory, $Diamondtype, $Color, $VendorId);
		if ($postLabourChargeType == '0' && $MetalWeight <= 1) {
			$FnlWt = 1;
		} else {
			$FnlWt = $MetalWeight;
		}

		$CalculatedCharges = round($FnlWt * $MainLaberCharges);
		$MainMetalRate = CostingHelper::CheckMetalRateWOJ($Color, $MetalKarat, $VendorId, $CostingCollectionMetalRate,$LineCounterMsg);
		$MainMetalAmount = CostingHelper::CheckMetalAmout($MetalWeight, $CostingCollectionMetalRate);
		$validValue = CostingHelper::CheckNearestValue($CostingCollectionLabourAmount,round($CalculatedCharges));
		if(!$validValue) {
		//if ($CostingCollectionLabourAmount != round($CalculatedCharges)) {
			$responseArr['wrong']['rowno'] = $CostingCollectionkey;
			$responseArr['wrong']['labouramount'] = $CostingCollection['labouramount'];
			$responseArr['msg'][] = 'Labour amount is wrong for ' . $CostingCollectionLabourAmount.$LineCounterMsg;
		}

		if(!empty($CertificateNumber)) {
			$Certi = CostingHelper::CertificateValidation($CertificateNumber);
			if ($Certi['status'] == 'failure') {
				if ($Certi['data'] == $CertificateNumber) {
					$responseArr['wrong']['rowno'] = $CostingCollectionkey;
					$responseArr['wrong']['certificate_no'] = $CertificateNumber;
				}
			}
		}

		if(!empty($CostingCollectionItemNo)) {
			$Item = CostingHelper:: ItemValidation($CostingCollectionItemNo);
			if ($Item['status'] == 'failure') {
				if ($Item['data'] == $CostingCollectionItemNo) {
					$responseArr['wrong']['rowno'] = $CostingCollectionkey;
					$responseArr['wrong']['item'] = $CostingCollectionItemNo;
				}
			}
		}


		if (!empty($MainMetalRate)) {
		    //if(!isset($MainMetalRate['status'])) {
			   // $validValue = CostingHelper::CheckNearestValue($CostingCollectionMetalRate,$MainMetalRate);
				//if(!$validValue) {
				if ($CostingCollectionMetalRate != $MainMetalRate) {
					$responseArr['wrong']['rowno'] = $CostingCollectionkey;
					$responseArr['wrong']['metalrate'] = $CostingCollectionMetalRate;
				}
			//}
		}

		$validValue = CostingHelper::CheckNearestValue($MainMetalAmount,$CostingCollectionMetalAmount);
		if(!$validValue) {
		//if ($MainMetalAmount != $CostingCollectionMetalAmount) {
			$responseArr['wrong']['rowno'] = $CostingCollectionkey;
			$responseArr['wrong']['metalamount'] = $CostingCollectionMetalAmount;
			$responseArr['msg'][] = 'Metal Amount is wrong for ' . $CostingCollectionMetalAmount.$LineCounterMsg;
		}

		
		if (!empty($CostingCollectionStoneRate)) {
			$MainStoneRate = CostingHelper::CheckStonePriceWOJ($StoneMaterialQuality, $Diamondtype,
			$StoneMaterialWeight, $VendorId, $Count, $StoneMaterialpcs, $CostingCollectionStoneRate,$LineCounterMsg);
			if (!empty($MainStoneRate)) {
				if ($CostingCollectionStoneRate != $MainStoneRate) {
						$responseArr['wrong']['rowno'] = $CostingCollectionkey;
						$responseArr['wrong']['stone_rate'] = $MainStoneRate['stone_rate'];
				}
			}
		}

		if (!empty($CostingCollectionTotalStoneAmount)) {
			$MainStoneAmount = CostingHelper::CheckStoneAmout($CostingCollectionMaterialWeight, $CostingCollectionStoneRate);
			
			$validValue = CostingHelper::CheckNearestValue($MainStoneAmount,$CostingCollectionTotalStoneAmount);
			if(!$validValue) {
			//if ($MainStoneAmount != $CostingCollectionTotalStoneAmount) {
				$responseArr['wrong']['rowno'] = $CostingCollectionkey;
				$responseArr['wrong']['total_stone_amount'] = $CostingCollectionTotalStoneAmount;
				$responseArr['msg'][] = 'Total Stone Amount is wrong for ' . $CostingCollectionTotalStoneAmount.$LineCounterMsg;
			}
		}

		if (isset($CostingCollectionCGST) || isset($CostingCollectionSGST)) {
			$CalculatedSumGST = CostingHelper::GetSumForGSTWOJ($CostingCollectionLabourAmount,
				$CostingCollectionMetalAmount, $CostingCollectionTotalStoneAmount);
			$CalculatedCGST = CostingHelper::CheckCGSTWOJ($CostingCollectionCGST, $CalculatedSumGST);
			$CalculatedSGST = CostingHelper::CheckSGSTWOJ($CostingCollectionSGST, $CalculatedSumGST);

			$validValue = CostingHelper::CheckNearestValue($CalculatedCGST,$CostingCollectionCGST);
			if(!$validValue) {
			//if ($CalculatedCGST != $CostingCollectionCGST) {
				if(!empty($CostingCollectionCGST) || $CostingCollectionCGST != 0) {
					$responseArr['wrong']['rowno'] = $CostingCollectionkey;
					$responseArr['wrong']['cgst'] = $CostingCollectionCGST;
					$responseArr['msg'][] = 'CGST Amount is wrong for ' . $CostingCollectionCGST.$LineCounterMsg;
				}
			}

			$validValue = CostingHelper::CheckNearestValue($CalculatedSGST,$CostingCollectionSGST);
			if(!$validValue) {
			//if ($CalculatedSGST != $CostingCollectionSGST) {
				if(!empty($CostingCollectionSGST) || $CostingCollectionSGST != 0) {
					$responseArr['wrong']['rowno'] = $CostingCollectionkey;
					$responseArr['wrong']['sgst'] = $CostingCollectionSGST;
					$responseArr['msg'][] = 'SGST Amount is wrong for ' . $CostingCollectionSGST.$LineCounterMsg;
				}
			}
		}

		if (!empty($CostingCollectionTotalAmount)) {
			if (empty($CostingCollectionCGST)) {
				$CostingCollectionCGST = 0;
			}

			if (empty($CostingCollectionSGST)) {
				$CostingCollectionSGST = 0;
			}

			$CalculatedTotalAmount = CostingHelper::CheckTotalAmountWOJ($CostingCollectionLabourAmount,
				$CostingCollectionMetalAmount, $CostingCollectionTotalStoneAmount, $CostingCollectionCGST, $CostingCollectionSGST,$CostingCollectionExtraPrice);

			$validValue = CostingHelper::CheckNearestValue($CalculatedTotalAmount,$CostingCollectionTotalAmount);
			if(!$validValue) {
			//if ($CalculatedTotalAmount != $CostingCollectionTotalAmount) {
				$responseArr['wrong']['rowno'] = $CostingCollectionkey;
				$responseArr['wrong']['total_amount'] = $CostingCollectionTotalAmount;
				$responseArr['msg'][] = 'Total Amount is wrong for ' . $CostingCollectionTotalAmount.$LineCounterMsg;
			}
		}
		return $responseArr;
	}

	public static function CheckStoneAmout($CostingCollectionMaterialWeight, $CostingCollectionStoneRate) {
		$CalculatedStoneAmout = 0;
		if (strpos($CostingCollectionMaterialWeight, ",") !== false) {
			$MaterialWeightArr = explode(",", $CostingCollectionMaterialWeight);
			$StoneRateArr = explode(",", $CostingCollectionStoneRate);
			$Stone_amount_total_arr = array_map(function ($x, $y) {return $x * $y;},
				$MaterialWeightArr, $StoneRateArr);
			$CalculatedStoneAmout = round(array_sum($Stone_amount_total_arr));
		} else {
			$CalculatedStoneAmout = round($CostingCollectionMaterialWeight * $CostingCollectionStoneRate);
		}
		return $CalculatedStoneAmout;

	}
	public static function CheckTotalAmountWOJ($CostingCollectionLabourAmount, $CostingCollectionMetalAmount, $CostingCollectionTotalStoneAmount, $CostingCollectionSGST = 0, $CostingCollectionCGST = 0,$CostingCollectionExtraPrice) {

		$CalculatedTotalAmount = $CostingCollectionLabourAmount + $CostingCollectionMetalAmount + $CostingCollectionTotalStoneAmount + $CostingCollectionSGST + $CostingCollectionCGST + $CostingCollectionExtraPrice;
		return $CalculatedTotalAmount;
	}

	public static function CheckCGSTWOJ($CostingCollectionCGST, $CalculatedSumGST) {

		$CGST = 1.5;
		$CalculatedCGST = (($CalculatedSumGST * $CGST) / 100);
		return $CalculatedCGST;
	}

	public static function CheckSGSTWOJ($CostingCollectionSGST, $CalculatedSumGST) {

		$SGST = 1.5;
		$CalculatedSGST = (($CalculatedSumGST * $SGST) / 100);
		return $CalculatedSGST;
	}

	public static function CheckCGSTWJ($CostingCollectionCGST, $CalculatedSumGST) {

		$CGST = 2.5;
		$CalculatedCGST = (($CalculatedSumGST * $CGST) / 100);
		return $CalculatedCGST;
	}

	public static function CheckSGSTWJ($CostingCollectionSGST, $CalculatedSumGST) {

		$SGST = 2.5;
		$CalculatedSGST = (($CalculatedSumGST * $SGST) / 100);
		return $CalculatedSGST;
	}

	public static function GetSumForGST($CostingCollectionLabourAmount, $CostingCollectionMetalAmount,
		$CostingCollectionTotalStoneAmount, $CostingigiCharges,
		$CostingCollectionHalmarking) {
		$CalculatedSumGST = $CostingCollectionLabourAmount + $CostingCollectionMetalAmount + $CostingCollectionTotalStoneAmount + $CostingigiCharges + $CostingCollectionHalmarking;
		return $CalculatedSumGST;
	}

	public static function GetSumForGSTWOJ($CostingCollectionLabourAmount, $CostingCollectionMetalAmount,
		$CostingCollectionTotalStoneAmount) {
		$CalculatedSumGST = $CostingCollectionLabourAmount + $CostingCollectionMetalAmount + $CostingCollectionTotalStoneAmount;
		return $CalculatedSumGST;
	}

	public static function CheckHallmarkingCharges($excelhallmarking, $ProductCategory, $postdatahallmarking) {
		$ids = array();
		$_categories = DB::select(DB::raw("SELECT * FROM `catalog_category_entity_varchar` WHERE `attribute_id` = 41 AND `store_id` = 0"));
		if (count($_categories) > 0) {
			foreach ($_categories as $_category) {
				$Name = strtolower($_category->value);
				$ProductCategory = strtolower($ProductCategory);
				if ($ProductCategory === $Name) {
					$FinalProductCategory = $_category->entity_id;
					$ids[] = $FinalProductCategory;
				}
			}
		}

		if (!empty($excelhallmarking)) {
			if (in_array("6", $ids)) {
				$hallmarking_charges = $postdatahallmarking * 2;
			} elseif (in_array("287", $ids)) {
				$hallmarking_charges = $postdatahallmarking * 3;
			} else {
				$hallmarking_charges = $postdatahallmarking;
			}
		} else {
			$hallmarking_charges = 0;
		}
		return $hallmarking_charges;
	}

	public static function CheckMetalAmout($MetalWeight, $CostingCollectionMetalRate) {
		$FinalMetalAmount = $MetalWeight * $CostingCollectionMetalRate;
		return $FinalMetalAmount;
	}

	public static function GetLabourCharge($MetalWeight, $ProductCategory, $Diamondtype, $Color, $VendorId) {
		$ids = array();
		$FnlDiamond = array();
		$_categories = DB::select(DB::raw("SELECT * FROM `catalog_category_entity_varchar` WHERE `attribute_id` = 41 AND `store_id` = 0"));
		if (count($_categories) > 0) {
			foreach ($_categories as $_category) {
				$Name = strtolower($_category->value);
				$ProductCategory = strtolower($ProductCategory);
				if ($ProductCategory === $Name) {
					$FinalProductCategory = $_category->entity_id;
					$ids[] = $FinalProductCategory;
				}
			}
		}

		$_braceletcategories = DB::select(DB::raw("SELECT * FROM `catalog_category_flat_store_1` as catmaster left join catalog_category_entity_varchar as catvarchar ON catvarchar.entity_id = catmaster.entity_id WHERE catmaster.`parent_id` = 14 and catvarchar.attribute_id = 41 and catmaster.is_active = 1"));
		foreach ($_braceletcategories as $_braceletcate) {
			$BraceleteIds[] = $_braceletcate->entity_id;
		}
		$BraceleteIds[] = 124;
		$result_intersect = array_intersect($BraceleteIds, $ids);
		if (count($result_intersect) > 0) {
			$Categorybracelets = 2;
		} else {
			$Categorybracelets = 1;
		}

		$DiamondCollection = explode(",", $Diamondtype);
		foreach ($DiamondCollection as $DiamondKey => $DiamondColl) {
			if ($DiamondColl == Config::get('constants.enum.costing_types.Round') || $DiamondColl == Config::get('constants.enum.costing_types.RD') || $DiamondColl == Config::get('constants.enum.costing_types.ROUND')) {
				$FnlDiamond[$DiamondKey]['Round'] = $DiamondColl;
			}
		}
		$CountOfRound = count($FnlDiamond);
		$CountOfDiamond = count($DiamondCollection);
		if ($CountOfDiamond == $CountOfRound) {
			$FinalDiamondType = 1;
		} else {
			$FinalDiamondType = 2;
		}

		if (strpos($Color, Config::get('constants.enum.costing_types.platinum')) !== false) {
			$ColorType = Config::get('constants.enum.costing_types.platinum(950)');
			$FinalType = 3;
		} else {
			$ColorType = Config::get('constants.enum.costing_types.gold');
			$FinalType = 1;
		}
		$MetalWeight = (float) $MetalWeight;
		$LabourCharceCollection = DB::table('vendor_charges')->where([['vendor_id', '=', $VendorId], ['type', '=', $FinalType], ['from_mm', '<', $MetalWeight], ['to_mm', '>', $MetalWeight], ['product_type', '=', $Categorybracelets], ['diamond_type', '=', $FinalDiamondType]])->get();
		foreach ($LabourCharceCollection as $LabourCharceColl) {
			$FianlLabourCharge = $LabourCharceColl->labour_charge;
		}
		if (!empty($FianlLabourCharge)) {
			return round($FianlLabourCharge);
		} else {
			$FianlLabourCharge = 0;
			return round($FianlLabourCharge);
		}
	}

	public static function CertificateValidation($CertificateNumber) {
		$CostColl = DB::table('costingdatas')->get();
		if (count($CostColl) > 0) {
			foreach ($CostColl as $CostVenCollection) {
				$GetCertificate[] = $CostVenCollection->certificate_no;
			}
			if ((in_array($CertificateNumber, $GetCertificate))) {
				$igi_rejected = DB::table('costingdatas')->select('igi_rejected')->where('certificate_no',$CertificateNumber)->value('igi_rejected');
				if($igi_rejected == 0) {
					$ArrayForCerti[] = $CertificateNumber;
				}
			}
		}

		if (!empty($ArrayForCerti)) {
			$FnlCertiArray = implode(",", $ArrayForCerti);
			$ReturnFnlCertiArray = array("status" => "failure", "data" => $FnlCertiArray);
		} else {
			$ReturnFnlCertiArray = array("status" => "success");
		}

		return $ReturnFnlCertiArray;
	}

	public static function ItemValidation($CostingCollectionItemNo) {

		$CostColl = DB::table('costingdatas')->get();
		if (count($CostColl) > 0) {
			foreach ($CostColl as $CostVenCollection) {
				$GetItem[] = $CostVenCollection->item;
			}
			if ((in_array($CostingCollectionItemNo, $GetItem))) {
				$igi_rejected = DB::table('costingdatas')->select('igi_rejected')->where('item',$CostingCollectionItemNo)->value('igi_rejected');
				if($igi_rejected == 0) {
					$ArrayForItem[] = $CostingCollectionItemNo;
				}
			}
		}

		if (!empty($ArrayForItem)) {
			$FnlItemArray = implode(",", $ArrayForItem);
			$ReturnFnlItemArray = array("status" => "failure", "data" => $FnlItemArray);
		} else {
			$ReturnFnlItemArray = array("status" => "success");
		}
		return $ReturnFnlItemArray;
	}

	public static function getIDforShapeAndClarity($dataArray, $count, $attr, $attributeId = '141') {
		$MainArray = array();
		if (count($dataArray) !== $count) {
			$dataArray = $attr;
		}

		if (!empty($dataArray)) {
			foreach ($dataArray as $dataArr) {

				if ($dataArr == Config::get('constants.enum.costing_types.RD')) {
					$dataArr = Config::get('constants.enum.costing_types.ROUND');
				}

				$attrValueId = DB::table(DB::raw('eav_attribute_option'))
					->join(DB::raw('eav_attribute_option_value'), DB::raw('eav_attribute_option.option_id'), '=', DB::raw('eav_attribute_option_value.option_id'))
					->select(DB::raw('eav_attribute_option.option_id'))
					->where(DB::raw('eav_attribute_option.attribute_id'), '=', $attributeId)
					->where(DB::raw('eav_attribute_option_value.value'), '=', $dataArr)
					->first();

				if (!empty($attrValueId)) {
					$MainArray[] = $attrValueId->option_id;
				}
			}
		}

		return $MainArray;
	}

	public static function CheckStonePriceWOJ($MaterialInterQuality, $MaterialType, $MaterialWeight, $VendorId, $Count, $StoneMaterialpcs, $CostingCollectionStoneRate,$LineCounterMsg) {

		$ReturedStoneArray = array();
		$MaterialQualityArray = explode(",", $MaterialInterQuality);
		$MaterialTypeArray = explode(",", $MaterialType);
		$MaterialWeightFnl = explode(",", $MaterialWeight);
		$StoneMaterialpcsFnl = explode(",", $StoneMaterialpcs);
		$Msg = array();
		$MainArray = array();
		$Shape = array();
		$Clarity = array();
		for ($j = 0; $j < $Count; $j++) {
			$Shape[] = $MaterialType;
			$Clarity[] = $MaterialInterQuality;
			if (empty($MaterialTypeArray[$j])) {
				$MaterialTypeArray[$j] = $MaterialTypeArray[0];
			}

		}

		$MainArray['stoneshape'] = CostingHelper::getIDforShapeAndClarity($MaterialTypeArray, $Count, $Shape, '141');
		$MainArray['stoneclarity'] = CostingHelper::getIDforShapeAndClarity($MaterialQualityArray, $Count, $Clarity, '145');

		$MsgStatus = true;
		$Data = 1;
		for ($i = 0; $i < $Count; $i++) {
			$FnlForParticularWeight[$i] = ($MaterialWeightFnl[$i] / $StoneMaterialpcsFnl[$i]);
			if (empty($MainArray['stoneshape'][$i])) {
				$MainArray['stoneshape'][$i] = '0';
			}

			if (empty($MainArray['stoneclarity'][$i])) {
				$MainArray['stoneclarity'][$i] = '0';
			}


			$StoneCollectionQuery = DB::table('vendorstonemanages')->where([['stone_shape', '=', $MainArray['stoneshape'][$i]], ['stone_clarity', '=', $MainArray['stoneclarity'][$i]], ['stone_carat_from', '<=', (float) $FnlForParticularWeight[$i]], ['stone_carat_to', '>=', (float) $FnlForParticularWeight[$i]], ['vendor_id', '=', $VendorId]]);
			$StoneCollection = $StoneCollectionQuery->get();
			$StoneCollectionCount = $StoneCollectionQuery->count();
			$costingRatesArr = explode(',', $CostingCollectionStoneRate);
			if ($StoneCollectionCount > 0) {
				foreach ($StoneCollection as $StoneColl) {
					$StonePrice = $StoneColl->stone_price;
					
					
					if($costingRatesArr[$i] !== $StonePrice) {

						$ReturedStoneArrayTmp['stoneshape'] = $MaterialTypeArray[$i];
						$ReturedStoneArrayTmp['stoneclarity'] = $MaterialQualityArray[$i];
						$ReturedStoneArrayTmp['stoneweight'] = number_format((float) $FnlForParticularWeight[$i], 4, '.', '');
						$Msg[] = "Wrong Stone Price For Shape :" . $ReturedStoneArrayTmp['stoneshape'] . " Clarity :" . $ReturedStoneArrayTmp['stoneclarity'] . "  Weight Between:=" . $ReturedStoneArrayTmp['stoneweight'].$LineCounterMsg;
						$Data = 0;
						$ReturedStoneArray[$i] = 'WRONG_'.$costingRatesArr[$i];
					}
					else {
						$ReturedStoneArray[$i] = $StonePrice;
					}
				}
				
			} else {
				
				if (!empty($MaterialTypeArray[$i])) {
					$ReturedStoneArrayTmp['stoneshape'] = $MaterialTypeArray[$i];
				}

				$ReturedStoneArrayTmp['stoneclarity'] = $MaterialQualityArray[$i];
				$ReturedStoneArrayTmp['stoneweight'] = number_format((float) $FnlForParticularWeight[$i], 4, '.', '');
				$Msg[] = "Add Stone Price For Shape :" . $ReturedStoneArrayTmp['stoneshape'] . " Clarity :" . $ReturedStoneArrayTmp['stoneclarity'] . "  Weight Between:=" . $ReturedStoneArrayTmp['stoneweight'].$LineCounterMsg;
				$MsgStatus = false;
				$Data = 0;
				$ReturedStoneArray[$i] = 'WRONG_'.$costingRatesArr[$i];
			}
		}

		$ReturnFnlStoneArray = array("status" => "failure", "data" => $Msg,"stone_rate" => $ReturedStoneArray);
		return $ReturnFnlStoneArray;
	}


	public static function CheckMetalRateWOJ($Color, $MetalKarat, $VendorId, $CostingCollectionMetalRate,$LineCounterMsg) {
		$Data = 1;
		$QualityType = 0;
		if (strpos($Color, Config::get('constants.enum.costing_types.platinum')) !== false) {
			$ColorType = Config::get('constants.enum.costing_types.platinum(950)');
		} else {
			$ColorType = Config::get('constants.enum.costing_types.gold');
		}

		$metalcoll = DB::table(DB::raw('grp_metal_type'))
			->select('grp_metal_type_id')
			->where('metal_type', 'like', $ColorType . '%')
			->first();
		if (!empty($metalcoll)) {
			$metaloption = $metalcoll->grp_metal_type_id;
		}

		$FinalType = $metaloption;
		$MetalTypeFnl = (int) filter_var($MetalKarat, FILTER_SANITIZE_NUMBER_INT);
		$MetalCarat = strtolower($MetalKarat);
		if (strpos($MetalCarat, Config::get('constants.enum.costing_types.platinum')) === false) {
			if ($Color == Config::get('constants.enum.costing_types.Y') || $Color == Config::get('constants.enum.costing_types.YELLOW') || $Color == Config::get('constants.enum.costing_types.Yellow')) {
				$QualityType = Config::get('constants.enum.costing_types.Yellow_Gold');
			} else if ($Color == Config::get('constants.enum.costing_types.TWOTONE') || $Color == Config::get('constants.enum.costing_types.Twotone')) {
				$QualityType = Config::get('constants.enum.costing_types.Two_Tone');
			} else if ($Color == Config::get('constants.enum.costing_types.THREETONE') || $Color == Config::get('constants.enum.costing_types.Threetone')) {
				$QualityType = Config::get('constants.enum.costing_types.Three_Tone');
			} else if ($Color == Config::get('constants.enum.costing_types.W') || $Color == Config::get('constants.enum.costing_types.WHITE') || Config::get('constants.enum.costing_types.White')) {
				$QualityType = Config::get('constants.enum.costing_types.White_Gold');
			} else if ($Color == Config::get('constants.enum.costing_types.R') || $Color == Config::get('constants.enum.costing_types.P') || $Color == Config::get('constants.enum.costing_types.ROSE') || $Color == Config::get('constants.enum.costing_types.PINK') || $Color == Config::get('constants.enum.costing_types.Rose') || $Color == Config::get('constants.enum.costing_types.Pink')) {
				$QualityType = Config::get('constants.enum.costing_types.Rose_Gold');
			}
			$FinalMetal = $MetalTypeFnl . 'K' . ' ' . $QualityType;
		} else {
			$FinalMetal = Config::get('constants.enum.costing_types.Platinum(950)');
		}

		$MetalRatesCollection = DB::table('vendor_metalrates')->where([['vendor_id', '=', $VendorId], ['metal_type', '=', $FinalType], ['metal_quality', '=', $FinalMetal]]);
		$MetalRatesColl = $MetalRatesCollection->get();
		$MetalRatesCollCount = $MetalRatesCollection->count();
		if ($MetalRatesCollCount  > 0) {
			foreach ($MetalRatesColl as $MetalRatesColl) {
				$MainMetalRate = round($MetalRatesColl->rate);
				if ($CostingCollectionMetalRate != $MainMetalRate) {
					$Msg = "Wrong Metal Price For Metal carat :" . $MetalKarat . " Color :" . $Color.$LineCounterMsg;
					$Data = 0;
				} else {
					$MainMetalRate = $MetalRatesColl->rate;

				}
			}
		} else {
			$Msg = "Add Metal Price For Metal carat :" . $MetalKarat . " Color :" . $Color.$LineCounterMsg;
			$Data = 0;
		}

		if (!empty($MainMetalRate) && $Data != 0) {
			$ReturnFnlStoneArray = $MainMetalRate;
		} else {
			$FnlStoneArray = $Msg;
			$ReturnFnlStoneArray = array("status" => "failure", "data" => $FnlStoneArray);

		}

		return $ReturnFnlStoneArray;
	}

	public static function getVendorDiamondHandling($VendorId,$postdata_diamondhadling) {
		$rows = VendorHandlingCharges::where([['vendor_id', '=', $VendorId]  ])->first();
		if(count($rows) > 0) {
			$HandlingId= $rows->id;
            $query =VendorHandlingCharges::where('id',$HandlingId)->first();
			$diamond_handling_data = $query->diamond_handling;
			return $diamond_handling_data;
		}
		else {
			return $postdata_diamondhadling;
		}
	}

	public static function getVendorFancyDiamondHandling($VendorId,$postdata_fancydiamondhadling) {
		$rows = VendorHandlingCharges::where([['vendor_id', '=', $VendorId]  ])->first();
		if(count($rows) > 0) {
			$HandlingId= $rows->id;
            $query =VendorHandlingCharges::where('id',$HandlingId)->first();
			$fancydiamond_handling_data = $query->fancy_diamond_handling;
			return $fancydiamond_handling_data;
		}
		else {
			return $postdata_fancydiamondhadling;
		}
	}

	public static function getVendorGoldHandling($VendorId,$postdata_goldhadling) {
		$rows = VendorHandlingCharges::where([['vendor_id', '=', $VendorId]  ])->first();
		if(count($rows) > 0) {
			$HandlingId= $rows->id;
            $query =VendorHandlingCharges::where('id',$HandlingId)->first();
			$gold_handling_data = $query->gold_handling;
			return $gold_handling_data;
		}
		else {
			return $postdata_goldhadling;
		}
	}
 
	public static function getVendorHallmarking($VendorId,$post_hallmarking) {
		$rows = VendorHandlingCharges::where([['vendor_id', '=', $VendorId]  ])->first();
		if(count($rows) > 0) {
			$HandlingId= $rows->id;
            $query =VendorHandlingCharges::where('id',$HandlingId)->first();
			$hallmarking_data = $query->hallmarking;
			return $hallmarking_data;
		}
		else {
			return $post_hallmarking;
		}
	}

	public static function getVendorIGICharges($VendorId,$post_IGICharges) {
		$rows = VendorHandlingCharges::where([['vendor_id', '=', $VendorId]  ])->first();
		if(count($rows) > 0) {
			$HandlingId= $rows->id;
            $query =VendorHandlingCharges::where('id',$HandlingId)->first();
			$igi_charges_data = $query->igi_charges;
			return $igi_charges_data;
		}
		else {
			return $post_IGICharges;
		}
	}

	public static function getImportValidateWithJobwork($costingdata, $postData) {
		$lineCounter = 1;
		$VendorId = $postData['vendor_id'];
		$MainReturnedArr = array();
		foreach ($costingdata as $CostingCollectionkey => $CostingCollection) {
			$CostingCollectionLabourAmount = $CostingCollection['labouramount'];
			$MetalWeight = $CostingCollection['metal_weight'];
			$ProductCategory = $CostingCollection['product_category'];
			$Diamondtype = $CostingCollection['material_type'];
			$Color = $CostingCollection['color'];
			$CertificateNumber = $CostingCollection['certificate_no'];
			$CostingCollectionItemNo  = $CostingCollection['item'];
			$StoneMaterialQuality = $CostingCollection['material_quality'];
			$Diamondtype = $CostingCollection['material_type'];
			$StoneMaterialWeight = $CostingCollection['material_weight'];
			$Count = count(explode(",", $StoneMaterialWeight));
			$StoneMaterialpcs = $CostingCollection['material_pcs'];
			$CostingCollectionStoneRate = $CostingCollection['stone_rate'];
			$MetalKarat = $CostingCollection['metal_karat'];
			$CostingCollectionMetalRate = (empty($CostingCollection['metalrate']) ? '0' : $CostingCollection['metalrate']);
			$PostDataigi = CostingHelper::getVendorIGICharges($VendorId,$postData['igi_charges']);
			$CostingigiCharges = $CostingCollection['igi_charges'];
			$CostingCollectionHalmarking = $CostingCollection['hallmarking'];
			$PostDataHalmarking = CostingHelper::getVendorHallmarking($VendorId,$postData['hallmarking']);
			$CostingCollectionMetalAmount = (empty($CostingCollection['metalamount']) ? '0' : $CostingCollection['metalamount']);
			$CostingCollectionTotalStoneAmount = $CostingCollection['total_stone_amount'];
			$CostingCollectionTotalAmount = $CostingCollection['total_amount'];
			$CostingCollectionMaterialWeight = $CostingCollection['material_weight'];
			$CostingCollectionStoneAmount = $CostingCollection['stone_amount'];
			$postdiamond = CostingHelper::getVendorDiamondHandling($VendorId,$postData['diamond_handling']);
			$postfancydiamond = CostingHelper::getVendorFancyDiamondHandling($VendorId,$postData['fancy_diamond_handling']);
			$postGoldHandling = CostingHelper::getVendorGoldHandling($VendorId,$postData['gold_handling']);
			$LineCounterMsg = ' at row no.'.$lineCounter;
			$CostingCollectionExtraPrice = $CostingCollection['extra_price'];
			$CostingCollectionExtraPriceFor = $CostingCollection['extra_price_for'];
			$CostingCollectionCGST = $CostingCollection['cgst'];
			$CostingCollectionSGST = $CostingCollection['sgst'];
			$postLabourChargeType = $postData['labour_charge_type'];


			// * check for labour charges * //
			$MainLaberCharges = CostingHelper::GetLabourCharge($MetalWeight, $ProductCategory, $Diamondtype, $Color, $VendorId);
			if (empty($MainLaberCharges) || $MainLaberCharges == '0') {
				$MainReturnedArr['code'][] = 3;
				$MainReturnedArr['messages'][] = Config::get('constants.message.labour_charge_not_added').$LineCounterMsg;
			}

			// * check for certificates already done QC *//
			if(!empty($CertificateNumber)) {
				$Certi = CostingHelper::CertificateValidation($CertificateNumber);
				if ($Certi['status'] == 'failure') {
					$MainReturnedArr['code'][] = 4;
					$MainReturnedArr['messages'][] = $CertificateNumber . Config::get('constants.message.certificate_exsist').$LineCounterMsg;
				}
			}

			if(!empty($CostingCollectionItemNo)) {      	 						
				$Item = CostingHelper::ItemValidation($CostingCollectionItemNo);
				if ($Item['status'] == 'failure') {
					$MainReturnedArr['code'][] = 4;
					$MainReturnedArr['messages'][] = $CostingCollectionItemNo . Config::get('constants.message.item_exsist').$LineCounterMsg;
				}
			}


			$wrongColsResponse[] = CostingHelper::checkForWrongColumnsWJ($CostingCollectionStoneRate, $postdiamond, $postfancydiamond, $Diamondtype, $postGoldHandling, $CostingCollectionMetalRate, $CostingCollectionkey, $CostingCollection, $CostingCollectionMetalAmount, $MetalWeight, $ProductCategory, $Color, $VendorId, $CostingCollectionLabourAmount, $CostingCollectionStoneAmount, $CostingCollectionMaterialWeight, $CostingCollectionTotalStoneAmount, $CostingigiCharges, $CostingCollectionHalmarking, $CostingCollectionTotalAmount, $PostDataHalmarking, $CertificateNumber,$CostingCollectionItemNo,$LineCounterMsg,$CostingCollectionExtraPrice,$CostingCollectionCGST,$CostingCollectionSGST,$PostDataigi,$postLabourChargeType);
			$lineCounter++;
		}

		foreach ($wrongColsResponse as $wrongColsRes) {
			if (isset($wrongColsRes['msg'])) {
				if (is_array($wrongColsRes['msg'])) {
					foreach ($wrongColsRes['msg'] as $singlemsg) {
						$MainReturnedArr['messages'][] = $singlemsg;
					}
				} else {
					$MainReturnedArr['messages'][] = $wrongColsRes['msg'];
				}
			}
		}

		if (!empty($wrongColsResponse)) {
			$wrongColsResponseArr = CostingHelper::getWrongResponseArrWJ($wrongColsResponse);
			if (!empty($MainReturnedArr)) {
				$jobworkstatus = 1;
				$sheetname = CostingHelper::DownloadExcel($wrongColsResponseArr, $VendorId, $costingdata,$jobworkstatus);
				$MainReturnedArr['sheetname'][] = $sheetname . ".xlsx";
			}
		}
		
		return $MainReturnedArr;
	}

	public static function getWrongResponseArrWJ($wrongColsResponseCollection) {
		foreach ($wrongColsResponseCollection as $wrongColsResponseColl) {
			$ColumnDatasArr = array();
			if (isset($wrongColsResponseColl['wrong'])) {
				foreach ($wrongColsResponseColl['wrong'] as $Columnkey => $wrongColsResponse) {
					$WrongResponseDataArr[$Columnkey] = $wrongColsResponse;
					if ($Columnkey != 'rowno') {
						$ColumnDatasArr[$Columnkey] = $WrongResponseDataArr[$Columnkey];
					}
				}
				if (isset($wrongColsResponseColl) && array_key_exists('rowno', $wrongColsResponseColl['wrong'])) {
					$WrongResponseArr[$wrongColsResponseColl['wrong']['rowno']] = $ColumnDatasArr;
				}
			}
		}
		if (empty($WrongResponseArr)) {
			$WrongResponseArr = array();
			return $WrongResponseArr;
		}
		return $WrongResponseArr;
	}

	public static function checkForWrongColumnsWJ($CostingCollectionStoneRate, $postdiamond, $postfancydiamond, $Diamondtype, $postGoldHandling, $CostingCollectionMetalRate, $CostingCollectionkey, $CostingCollection, $CostingCollectionMetalAmount, $MetalWeight, $ProductCategory, $Color, $VendorId, $CostingCollectionLabourAmount, $CostingCollectionStoneAmount, $CostingCollectionMaterialWeight, $CostingCollectionTotalStoneAmount, $CostingigiCharges, $CostingCollectionHalmarking, $CostingCollectionTotalAmount, $PostDataHalmarking, $CertificateNumber,$CostingCollectionItemNo,$LineCounterMsg,$CostingCollectionExtraPrice,$CostingCollectionCGST,$CostingCollectionSGST,$PostDataigi,$postLabourChargeType) {

		$responseArr = array();
		$MainLaberCharges = CostingHelper::GetLabourCharge($MetalWeight, $ProductCategory, $Diamondtype, $Color, $VendorId);
		if ($postLabourChargeType == '0' && $MetalWeight <= 1) {
			$FnlWt = 1;
		} else {
			$FnlWt = $MetalWeight;
		}
		$CalculatedCharges = round($FnlWt * $MainLaberCharges);

		/* [[check vendor labour amount]] */
		$validValue = CostingHelper::CheckNearestValue($CostingCollectionLabourAmount,round($CalculatedCharges));
		if(!$validValue) {
		//if ($CostingCollectionLabourAmount != round($CalculatedCharges)) {
			$responseArr['wrong']['rowno'] = $CostingCollectionkey;
			$responseArr['wrong']['labouramount'] = $CostingCollection['labouramount'];
			$responseArr['msg'][] = 'Labour amount is wrong for ' . $CostingCollectionLabourAmount.$LineCounterMsg;
		}

		if(!empty($CertificateNumber)) {
			$Certi = CostingHelper::CertificateValidation($CertificateNumber);
			if ($Certi['status'] == 'failure') {
				if ($Certi['data'] == $CertificateNumber) {
					$responseArr['wrong']['rowno'] = $CostingCollectionkey;
					$responseArr['wrong']['certificate_no'] = $CertificateNumber;
				}
			}
		}

		if(!empty($CostingCollectionItemNo)) {
			$Item = CostingHelper:: ItemValidation($CostingCollectionItemNo);
			if ($Item['status'] == 'failure') {
				if ($Item['data'] == $CostingCollectionItemNo) {
					$responseArr['wrong']['rowno'] = $CostingCollectionkey;
					$responseArr['wrong']['item'] = $CostingCollectionItemNo;
				}
			}
		}

		/* [[check vendor stone rate]] */
		if (!empty($CostingCollectionStoneRate)) {
			$CostingCollectionStoneRateExplod = explode(",", $CostingCollectionStoneRate);
			$DiamondTypeExplod = explode(",", $Diamondtype);
			$CostDiamondRate = false;
			for ($cnt = 0; $cnt < count($CostingCollectionStoneRateExplod); $cnt++) {

				if ($DiamondTypeExplod[$cnt] == Config::get('constants.enum.costing_types.RD') || $DiamondTypeExplod[$cnt] == Config::get('constants.enum.costing_types.ROUND') || $DiamondTypeExplod[$cnt] == Config::get('constants.enum.costing_types.Round')) {
					//$validValue = CostingHelper::CheckNearestValue($CostingCollectionStoneRateExplod[$cnt],$postdiamond);
					if ($CostingCollectionStoneRateExplod[$cnt] != $postdiamond) {
					//if(!$validValue) {
						$responseArr['wrong']['rowno'] = $CostingCollectionkey;
						$TempArryToStoreStoneRate[] = $CostingCollectionStoneRateExplod[$cnt];
						$responseArr['wrong']['stone_rate'] = implode(",", $TempArryToStoreStoneRate);
						$responseArr['msg'][] = "Stone rate is wrong for round " . $CostingCollectionStoneRateExplod[$cnt].$LineCounterMsg;
					} else {
						$TempArryToStoreRightStoneRate[] = $CostingCollectionStoneRateExplod[$cnt];
						$responseArr['wrong']['stone_rate_right'] = implode(",", $TempArryToStoreRightStoneRate);
					}
				} else {
					//$validValue = CostingHelper::CheckNearestValue($CostingCollectionStoneRateExplod[$cnt],$postfancydiamond);
					//if(!$validValue) {
					if ($CostingCollectionStoneRateExplod[$cnt] != $postfancydiamond) {
						$responseArr['wrong']['rowno'] = $CostingCollectionkey;
						$responseArr['wrong']['stone_rate'] = $CostingCollectionStoneRateExplod[$cnt];
						$responseArr['msg'][] = "Stone rate is wrong for fancy" . $CostingCollectionStoneRateExplod[$cnt].$LineCounterMsg;
					}
				}
			}
		}

		/* [[check vendor metal rate]] */
		//$validValue = CostingHelper::CheckNearestValue($CostingCollectionMetalRate,$postGoldHandling);
		//if(!$validValue) {
		if ($CostingCollectionMetalRate != $postGoldHandling) {
			$responseArr['wrong']['rowno'] = $CostingCollectionkey;
			$responseArr['wrong']['metalrate'] = $CostingCollection['metalrate'];
			$responseArr['msg'][] = 'Metal Rate is wrong for' . $CostingCollectionMetalRate.$LineCounterMsg;
		}


		/* [[check vendor metal amount]] */
		$MainMetalAmount = CostingHelper::CheckMetalAmout($MetalWeight, $CostingCollectionMetalRate);
		$validValue = CostingHelper::CheckNearestValue($MainMetalAmount,$CostingCollectionMetalAmount);
		if(!$validValue) {
		//if ($MainMetalAmount != $CostingCollectionMetalAmount) {
			$responseArr['wrong']['rowno'] = $CostingCollectionkey;
			$responseArr['wrong']['metalamount'] = $CostingCollectionMetalAmount;
			$responseArr['msg'][] = 'Metal Amount is wrong for ' . $CostingCollectionMetalAmount.$LineCounterMsg;
		}

		/* [[check vendor stone amount]] */
		if (!empty($CostingCollectionTotalStoneAmount)) {
			$MainStoneAmount = CostingHelper::CheckStoneAmout($CostingCollectionMaterialWeight, $CostingCollectionStoneRate);
			$validValue = CostingHelper::CheckNearestValue($MainStoneAmount,$CostingCollectionTotalStoneAmount);
			if(!$validValue) {
			//if ($MainStoneAmount != $CostingCollectionTotalStoneAmount) {
				$responseArr['wrong']['rowno'] = $CostingCollectionkey;
				$responseArr['wrong']['total_stone_amount'] = $CostingCollectionTotalStoneAmount;
				$responseArr['msg'][] = 'Total Stone Amount is wrong for ' . $CostingCollectionTotalStoneAmount.$LineCounterMsg;
			}
		}

		/* [[check vendor GST amount]] */
		if (isset($CostingCollectionCGST) || isset($CostingCollectionSGST)) {
			$CalculatedSumGST = CostingHelper::GetSumForGST($CostingCollectionLabourAmount,
			$CostingCollectionMetalAmount, $CostingCollectionTotalStoneAmount,
			$CostingigiCharges, $CostingCollectionHalmarking);
			$CalculatedCGST = CostingHelper::CheckCGSTWJ($CostingCollectionCGST, $CalculatedSumGST);
			$CalculatedSGST = CostingHelper::CheckSGSTWJ($CostingCollectionSGST, $CalculatedSumGST);
			$validValue = CostingHelper::CheckNearestValue($CalculatedCGST,$CostingCollectionCGST);
			if(!$validValue) {
			//if ($CalculatedCGST != $CostingCollectionCGST) {
				if(!empty($CostingCollectionCGST) || $CostingCollectionCGST != 0) {
					$responseArr['wrong']['rowno'] = $CostingCollectionkey;
					$responseArr['wrong']['cgst'] = $CostingCollectionCGST;
					$responseArr['msg'][] = 'CGST Amount is wrong for ' . $CostingCollectionCGST.$LineCounterMsg;
				}
			}

			$validValue = CostingHelper::CheckNearestValue($CalculatedSGST,$CostingCollectionSGST);
			if(!$validValue) {
			//if ($CalculatedSGST != $CostingCollectionSGST) {
				if(!empty($CostingCollectionSGST) || $CostingCollectionSGST != 0) {
					$responseArr['wrong']['rowno'] = $CostingCollectionkey;
					$responseArr['wrong']['sgst'] = $CostingCollectionSGST;
					$responseArr['msg'][] = 'SGST Amount is wrong for ' . $CostingCollectionSGST.$LineCounterMsg;
				}
			}
		}

		/* [[check  IGI Charges]] */
		if ($CostingigiCharges != "" && $PostDataigi != "" ) {

			$validValue = CostingHelper::CheckNearestValue($PostDataigi,$CostingigiCharges);
			if(!$validValue) {
			//if ($PostDataigi != $CostingigiCharges) {
				$responseArr['wrong']['rowno'] = $CostingCollectionkey;
				$responseArr['wrong']['igi_charges'] = $CostingigiCharges;
				$responseArr['msg'][] = 'IGI Amount is wrong for ' . $CostingigiCharges.$LineCounterMsg;
			}
		}

		/* [[check  Hallmarking Charges]] */
		if ($CostingCollectionHalmarking != "") {
			$MainHalmarkingCharge = CostingHelper::CheckHallmarkingCharges($CostingCollectionHalmarking,
				$ProductCategory, $PostDataHalmarking);

			$validValue = CostingHelper::CheckNearestValue($MainHalmarkingCharge,$CostingCollectionHalmarking);
			if(!$validValue) {
			//if ($MainHalmarkingCharge != $CostingCollectionHalmarking) {
				$responseArr['wrong']['rowno'] = $CostingCollectionkey;
				$responseArr['wrong']['hallmarking'] = $CostingCollectionHalmarking;
				$responseArr['msg'][] = 'Hallmarking Amount is wrong for ' . $CostingCollectionHalmarking.$LineCounterMsg;
			}
		}

		/* [[check vendor Total amount]] */
		if ($CostingCollectionTotalAmount != "") {
			if (empty($CostingCollectionCGST)) {
				$CostingCollectionCGST = 0;
			}

			if (empty($CostingCollectionSGST)) {
				$CostingCollectionSGST = 0;
			}

			if (empty($CostingigiCharges)) {
				$CostingigiCharges = 0;
			}

			if (empty($CostingCollectionHalmarking)) {
				$CostingCollectionHalmarking = 0;
			}

			$CalculatedTotalAmount = CostingHelper::CheckTotalAmountWJ($CostingCollectionLabourAmount,
				$CostingCollectionMetalAmount, $CostingCollectionTotalStoneAmount, $CostingCollectionHalmarking, $CostingigiCharges, $CostingCollectionSGST, $CostingCollectionCGST,$CostingCollectionExtraPrice);
			$validValue = CostingHelper::CheckNearestValue($CalculatedTotalAmount,$CostingCollectionTotalAmount);
			if(!$validValue) {
			//if ($CalculatedTotalAmount != $CostingCollectionTotalAmount) {
				$responseArr['wrong']['rowno'] = $CostingCollectionkey;
				$responseArr['wrong']['total_amount'] = $CostingCollectionTotalAmount;
				$responseArr['msg'][] = 'Total Amount is wrong for ' . $CostingCollectionTotalAmount.$LineCounterMsg;
			}
		}
		return $responseArr;
	}

	public static function CheckNearestValue($sheetValue,$originValue) {
		$configData = Setting::where('key', config('constants.settings.keys.costing_nearby_amount'))->first('value');
		$diffrenceValue = $configData->value;
		$validValue = false;
		$checkValue = ($sheetValue) - ($originValue);
		if($diffrenceValue >= abs($checkValue)) {
			$validValue = true; 
		}
		return $validValue;

	}

	public static function CheckTotalAmountWJ($CostingCollectionLabourAmount, $CostingCollectionMetalAmount, $CostingCollectionTotalStoneAmount, $CostingCollectionHalmarking = 0, $CostingigiCharges = 0, $CostingCollectionSGST = 0, $CostingCollectionCGST = 0,$CostingCollectionTotalAmount) {

		$CalculatedTotalAmount = $CostingCollectionLabourAmount + $CostingCollectionMetalAmount + $CostingCollectionTotalStoneAmount + $CostingCollectionCGST + $CostingCollectionSGST + $CostingCollectionTotalAmount + $CostingigiCharges +$CostingCollectionHalmarking;
		return $CalculatedTotalAmount;
	}

	public static function getDiamondMelaLabourPrice($MetalWeight,$ProductCategory,$Diamondtype,$Color)
    {
        $ids = array();
        $FnlDiamond = array();
		$_categories = DB::select(DB::raw("SELECT * FROM `catalog_category_entity_varchar` WHERE `attribute_id` = 41 AND `store_id` = 0"));
        if (count($_categories) > 0)
        {
            foreach($_categories as $_category){
                $Name = strtolower($_category->value);
                $ProductCategory = strtolower($ProductCategory);
                if($ProductCategory === $Name) {
                    $FinalProductCategory = $_category->entity_id;
                    $ids[] = $FinalProductCategory;
                }
            }
        }

        $_braceletcategories = DB::table(DB::raw('catalog_category_flat_store_1'))
					->leftJoin(DB::raw('catalog_category_entity_varchar'), DB::raw('catalog_category_entity_varchar.entity_id'), '=', DB::raw('catalog_category_flat_store_1.entity_id'))
					->where(DB::raw('catalog_category_flat_store_1.parent_id'), '=', 14)
					->where(DB::raw('catalog_category_entity_varchar.attribute_id'), '=', 41)
					->where(DB::raw('catalog_category_flat_store_1.is_active'), '=', 1)
					->get();

		foreach ($_braceletcategories as $_braceletcate) {
            $BraceleteIds[] = $_braceletcate->entity_id;
        }
        $BraceleteIds[] = 124;
        $result_intersect = array_intersect($BraceleteIds,$ids);
        if(count($result_intersect) > 0){ 
            $Categorybracelets = 2;            
        }else{
            $Categorybracelets = 1;            
        }
        
        $DiamondCollection = explode(",",$Diamondtype);
        foreach ($DiamondCollection as $DiamondKey => $DiamondColl) {
            if ($DiamondColl == Config::get('constants.enum.costing_types.Round') || $DiamondColl == Config::get('constants.enum.costing_types.RD') || $DiamondColl == Config::get('constants.enum.costing_types.ROUND')) {

                $FnlDiamond[$DiamondKey]['Round'] = $DiamondColl;
            }
        }

        $CountOfRound = count($FnlDiamond);
        $CountOfDiamond = count($DiamondCollection);
        if($CountOfDiamond == $CountOfRound) {
            $FinalDiamondType = 1;
        }  
        else  {
            $FinalDiamondType = 2;
        }
      
        if (strpos($Color, Config::get('constants.enum.costing_types.platinum')) !== false) {
			$ColorType = Config::get('constants.enum.costing_types.platinum(950)');
			$FinalType = 3;
		} else {
			$ColorType = Config::get('constants.enum.costing_types.gold');
			$FinalType = 1;
		}

        $MetalWeight = (float)$MetalWeight;
        $query = DB::table(DB::raw('grp_custom_labour_charges'));
        $LabourCharceCollection =  $query->where([ ['type', '=', $FinalType],['from_mm', '<=', $MetalWeight],['to_mm', '>=', $MetalWeight], ['product_type', '=', $Categorybracelets], ['diamond_type', '=', $FinalDiamondType], ])->get();
        foreach($LabourCharceCollection as $LabourCharceColl)  {
            $FianlLabourCharge = $LabourCharceColl->labour_charge; 
        }

        if(!empty($FianlLabourCharge)) {
            return round($FianlLabourCharge);    
        }
        else{
            $FianlLabourCharge = 0;
            return round($FianlLabourCharge);
        }
    }

    public static function getDiamondMelaMetalPrice($Color,$MetalKarat)
    {
    	if (strpos($Color, Config::get('constants.enum.costing_types.platinum')) !== false) {
			$ColorType = Config::get('constants.enum.costing_types.platinum(950)');
		} else {
			$ColorType = Config::get('constants.enum.costing_types.gold');
		}
		
		$metalcoll = DB::table(DB::raw('grp_metal_type'))
			->select('grp_metal_type_id')
			->where('metal_type', 'like', $ColorType . '%')
			->first();
		if (!empty($metalcoll)) {
			$metaloption = $metalcoll->grp_metal_type_id;
		}
		$FinalType = $metaloption;
        
        $MetalTypeFnl = (int) filter_var($MetalKarat, FILTER_SANITIZE_NUMBER_INT);
		$MetalCarat = strtolower($MetalKarat);
		if (strpos($MetalCarat, Config::get('constants.enum.costing_types.platinum')) === false) {
			if ($Color == Config::get('constants.enum.costing_types.Y') || $Color == Config::get('constants.enum.costing_types.YELLOW') || $Color == Config::get('constants.enum.costing_types.Yellow')) {
				$QualityType = Config::get('constants.enum.costing_types.Yellow_Gold');
			} else if ($Color == Config::get('constants.enum.costing_types.TWOTONE') || $Color == Config::get('constants.enum.costing_types.Twotone')) {
				$QualityType = Config::get('constants.enum.costing_types.Two_Tone');
			} else if ($Color == Config::get('constants.enum.costing_types.THREETONE') || $Color == Config::get('constants.enum.costing_types.Threetone')) {
				$QualityType = Config::get('constants.enum.costing_types.Three_Tone');
			} else if ($Color == Config::get('constants.enum.costing_types.W') || $Color == Config::get('constants.enum.costing_types.WHITE') || Config::get('constants.enum.costing_types.White')) {
				$QualityType = Config::get('constants.enum.costing_types.White_Gold');
			} else if ($Color == Config::get('constants.enum.costing_types.R') || $Color == Config::get('constants.enum.costing_types.P') || $Color == Config::get('constants.enum.costing_types.ROSE') || $Color == Config::get('constants.enum.costing_types.PINK') || $Color == Config::get('constants.enum.costing_types.Rose') || $Color == Config::get('constants.enum.costing_types.Pink')) {
				$QualityType = Config::get('constants.enum.costing_types.Rose_Gold');
			}
			$FinalMetal = $MetalTypeFnl . 'K' . ' ' . $QualityType;
		} else {
			$FinalMetal = Config::get('constants.enum.costing_types.Platinum(950)');
		}

        $MetalRatesCollection =  DB::table(DB::raw('grp_metal_quality'))->where([ ['metal_type', '=', $FinalType],['metal_quality', '=', $FinalMetal], ])->get();
        foreach ($MetalRatesCollection as $MetalRatesColl) {
            $MainMetalRate  = $MetalRatesColl->rate;
        }
        if(!empty($MainMetalRate)) {
        	return round($MainMetalRate);
        }
        else {
        	$MainMetalRate = 0;
        	return $MainMetalRate;	
        }
		
    }

   public static function getDiamondMelaStonePrice($MaterialWeight,$MaterialInterQuality,$MaterialType,$Count,$StoneMaterialpcs)
    {
        $MaterialQualityArray = explode(",", $MaterialInterQuality);
		$MaterialTypeArray = explode(",", $MaterialType);
		$MaterialWeightFnl = explode(",", $MaterialWeight);
		$StoneMaterialpcsFnl = explode(",", $StoneMaterialpcs);

		$MainArray = array();
		$Shape = array();
		$Clarity = array();
		for ($j = 0; $j < $Count; $j++) {
			$Shape[] = $MaterialType;
			$Clarity[] = $MaterialInterQuality;
			if (empty($MaterialTypeArray[$j])) {
				$MaterialTypeArray[$j] = $MaterialTypeArray[0];
			}

		}

		$MainArray['stoneshape'] = CostingHelper::getIDforShapeAndClarity($MaterialTypeArray, $Count, $Shape, '141');
		$MainArray['stoneclarity'] = CostingHelper::getIDforShapeAndClarity($MaterialQualityArray, $Count, $Clarity, '145');

        $StoneCollection = 0;
        for($i=0;$i<$Count;$i++)
        {

        	$FnlForParticularWeight[$i] =  ($MaterialWeightFnl[$i] / $StoneMaterialpcsFnl[$i]);

        	if (empty($MainArray['stoneshape'][$i])) {
				$MainArray['stoneshape'][$i] = '0';
			}

			if (empty($MainArray['stoneclarity'][$i])) {
				$MainArray['stoneclarity'][$i] = '0';
			}

			//if (array_key_exists('stone_shape', $MainArray) ) {

			$StoneCollectionQuery = DB::table(DB::raw('grp_stone_manage'))->where([['stone_shape', '=', $MainArray['stoneshape'][$i]], ['stone_clarity', '=', $MainArray['stoneclarity'][$i]], ['stone_carat_from', '<=', (float) $FnlForParticularWeight[$i]], ['stone_carat_to', '>=', (float) $FnlForParticularWeight[$i]] ])->first();
			//}

        	if(!empty($StoneCollectionQuery)) {
				if(count($StoneCollectionQuery) > 0)
	            {
	                //foreach ($StoneCollectionQuery as $StoneColl) {
	                    $StonePrice = $StoneCollectionQuery->stone_price;
	                    $StoneAmt  = $MaterialWeightFnl[$i] * $StonePrice;
                		$ReturedStoneArray[] = $StonePrice;
                		$ReturedStoneAmtArray[] = $StoneAmt;

	                //}
	            }
        	}
        }
        if(!empty($ReturedStoneAmtArray)) {
        $totalstoneamtsum = array_sum($ReturedStoneAmtArray);
        $FnlStoneArray = implode(",",$ReturedStoneArray);
        $FnlStoneAmonutArray  = implode(",",$ReturedStoneAmtArray); 
        $FnlStoneRateOrAmount = array("dataamt"=>$FnlStoneAmonutArray, "data" => $FnlStoneArray,"totalstoneamtsum" => $totalstoneamtsum);
    	return $FnlStoneRateOrAmount;
    	}
    	return 0;	

    	









    }

}
