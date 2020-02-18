<?php

namespace App\Http\Controllers;

use App\CatalogCategoryProduct;
use App\Helpers\ProductHelper;
use App\Products;
use App\ProductsMetal;
use App\ProductsStone;
use App\Productupload;
use App\User;
use Config;
use Excel;
use Illuminate\Http\Request;
use PHPExcel_IOFactory;
use DB;
use App\MetalQuality;

class ProductuploadController extends Controller {
	public function create() {
		$vendor = User::whereHas('roles', function ($q) {$q->where('name', 'Vendor');})->paginate(5);
		return view('productupload/create', compact('vendor', $vendor));
	}

	public function index() {
		$costingdatas = Productupload::whereNull('qc_status')->orderBy('id', 'DESC')->get();
		$vendor = User::whereHas('roles', function ($q) {$q->where('name', 'Vendor');})->get();
		$totalcount = Productupload::whereNull('qc_status')->count();
		return view('productupload/index', array('costingdatas' => $costingdatas, 'vendor' => $vendor, 'totalcount' => $totalcount));
	}

	public function indexResponse(request $request) {

		$totalData = Productupload::whereNull('qc_status')->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		if (empty($request->input('search.value'))) {
			$costings = Productupload::whereNull('qc_status')->orderBy('id', 'DESC')->offset($start)->limit($limit)->get();
		} else {
			$search = $request->input('search.value');
			$costings = Productupload::whereNull('qc_status')->orderBy('id', 'DESC')->where('sku', 'LIKE', "%{$search}%")->orWhere('certificate_no', 'LIKE', "%{$search}%")->orWhere('item', 'LIKE', "%{$search}%")->offset($start)->limit($limit)->get();
			$totalFiltered = Productupload::whereNull('qc_status')->orderBy('id', 'DESC')->where('sku', 'LIKE', "%{$search}%")->orWhere('certificate_no', 'LIKE', "%{$search}%")->orWhere('item', 'LIKE', "%{$search}%")->count();
		}
		$data = array();
		if (!empty($costings)) {
			foreach ($costings as $costing) {
				$checkbox = "<label><input type='checkbox' class='form-check-input chkProduct' name='chk_costing' id='chk_costing' value=" . $costing->id . "><span class='label-text'></span></label>";

				$imagepath = \URL::to('/') . '/' . $costing->image;
				$image = "<img src=" . $imagepath . " class='img-fluid' height='120' width='120'/>";

				$costing_id = $costing->costingdata_id;
				$detail = "<a href='#'><i title='Detail' onclick='showDetail(" . $costing->id . ")' class='material-icons list-icon'>info</i></a>

                    <a href='#'><i title='Accept' data-id ='" . $costing->id . "' class='material-icons list-icon qc_btn' data-status='accept' id='accept'>check_circle</i></a>

                    <a href='#'><i title='Reject' data-id =" . $costing->id . " class='material-icons list-icon qc_btn' data-status='reject' id='reject'>cancel</i></a>";

				$data[] = array($image, $costing->sku, $costing->item);
			}
		}

		$json_data = array(
			"draw" => intval($request->input('draw')),
			"recordsTotal" => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data" => $data,
		);
		echo json_encode($json_data);

	}

	public function getValidBatchNo() {
		$productColl = Productupload::get();
		$productCerArr = array();
		foreach ($productColl as $productColl) {
			$productCerArr[] = $productColl['item'];
		}
		return $productCerArr;
	}

	public function getValidCertificate() {
		$productCollAr = Productupload::get();
		$productCerArr = array();
		foreach ($productCollAr as $productColl) {
			$productCerArr[] = $productColl['certificate_no'];
		}
		return $productCerArr;
	}

	public function store(Request $request) {

		$postdata = $request->all();
		$file = $postdata['name'];
		$fileName = $file->getClientOriginalName();
		$costing_id = 0;
		//start
		$request->validate([
			'name' => 'required',
		]);
		$path = $request->file('name')->getRealPath();
		$rowdataget = Excel::load($path)->get();
		//echo "<pre>"; print_r($rowdataget);exit;
		$rowDataArr = Excel::load($path)->toArray();
		$rowdata = $this->checkMasterKeyIsExist($rowDataArr);

		//Start code for images
		$i = 0;
		$objPHPExcel = PHPExcel_IOFactory::load($path);
		foreach ($objPHPExcel->getActiveSheet()->getDrawingCollection() as $drawing) {
			if ($drawing instanceof PHPExcel_Worksheet_MemoryDrawing) {
				ob_start();
				call_user_func(
					$drawing->getRenderingFunction(),
					$drawing->getImageResource()
				);

				$imageContents = ob_get_contents();
				ob_end_clean();
				$extension = 'png';
			} else {
				$zipReader = fopen($drawing->getPath(), 'r');
				$imageContents = '';

				while (!feof($zipReader)) {
					$imageContents .= fread($zipReader, 1024);
				}
				fclose($zipReader);
				$extension = $drawing->getExtension();
			}
			$myFileName = 'img/' . '00_Image_' . ++$i . '.' . $extension;
			file_put_contents($myFileName, $imageContents);
			$image[] = $myFileName;

		}

		$is_batchno_exist = false;
		$is_certino_exist = false;
		$row_i = 0;
		if ($rowdataget->count()) {

			foreach ($rowdata as $key => $coll) {

				// Sr No
				if (isset($coll['Sr No.'])) {
					$col_sr_no = (string) (($coll['Sr No.'] != "") ? $coll['Sr No.'] : '');
				} else if (isset($coll['S No.'])) {
					$col_sr_no = (string) (($coll['S No.'] != "") ? $coll['S No.'] : '');
				} else if (isset($coll['Sr. No'])) {
					$col_sr_no = (string) (($coll['Sr. No'] != "") ? $coll['Sr. No'] : '');
				} else if (isset($coll['Sr. No.'])) {
					$col_sr_no = (string) (($coll['Sr. No.'] != "") ? $coll['Sr. No.'] : '');
				} else {
					$col_sr_no = '';
				}

				// Metal Color
				if (isset($coll['Metel Color'])) {
					$col_metal_color = (($coll['Metel Color'] != "") ? $coll['Metel Color'] : '');
				} else if (isset($coll['METAL COLOUR'])) {
					$col_metal_color = (($coll['METAL COLOUR'] != "") ? $coll['METAL COLOUR'] : '');
				} else if (isset($coll['Color'])) {
					$col_metal_color = (($coll['Color'] != "") ? $coll['Color'] : '');
				} else {
					$col_metal_color = '';
				}

				// Order No
				if (isset($coll['Order no'])) {
					$col_order_no = (($coll['Order no'] != "") ? $coll['Order no'] : '');
				} else if (isset($coll['Order No.'])) {
					$col_order_no = (($coll['Order No.'] != "") ? $coll['Order No.'] : '');
				} else {
					$col_order_no = '';
				}

				// Style
				if (isset($coll['Style #'])) {
					$col_style = (($coll['Style #'] != "") ? $coll['Style #'] : '');
				} else if (isset($coll['Style#'])) {
					$col_style = (($coll['Style#'] != "") ? $coll['Style#'] : '');
				} else if (isset($coll['Style( Design No )'])) {
					$col_style = (($coll['Style( Design No )'] != "") ? $coll['Style( Design No )'] : '');
				} else {
					$col_style = '';
				}

				// Gross Wt
				if (isset($coll['Gross Wt'])) {
					$col_gross_wt = (($coll['Gross Wt'] != "") ? $coll['Gross Wt'] : '');
				} else if (isset($coll['Gross Wt.'])) {
					$col_gross_wt = (($coll['Gross Wt.'] != "") ? $coll['Gross Wt.'] : '');
				} else if (isset($coll['Grand Gross Wt'])) {
					$col_gross_wt = (($coll['Grand Gross Wt'] != "") ? $coll['Grand Gross Wt'] : '');
				} else {
					$col_gross_wt = '';
				}

				// Metal Wt
				if (isset($coll['Total Metal Wt.'])) {
					$col_total_metal_wt = (($coll['Total Metal Wt.'] != "") ? $coll['Total Metal Wt.'] : '');
				} else if (isset($coll['Net Wt.'])) {
					$col_total_metal_wt = (($coll['Net Wt.'] != "") ? $coll['Net Wt.'] : '');
				} else if (isset($coll['Net.Wt'])) {
					$col_total_metal_wt = (($coll['Net.Wt'] != "") ? $coll['Net.Wt'] : '');
				} else if (isset($coll['Net Metal Wt.'])) {
					$col_total_metal_wt = (($coll['Net Metal Wt.'] != "") ? $coll['Net Metal Wt.'] : '');
				} else {
					$col_total_metal_wt = '';
				}

				// Seive Size
				if (isset($coll['Seive Size'])) {
					$col_seive_size = ((strlen($coll['Seive Size']) > 0) ? $coll['Seive Size'] : 0);
				} else if (isset($coll['Sive size'])) {
					$col_seive_size = ((strlen($coll['Sive size']) > 0) ? $coll['Sive size'] : 0);
				} else {
					$col_seive_size = 0;
				}

				//$col_stone_amt = ($coll['Stone amt'] >= 0 ? $coll['Stone amt'] : ($coll['Stone amt  (Handling charge )'] >= 0 ? $coll['Stone amt  (Handling charge )'] : ($coll['Total Stone amt'] >= 0 ? $coll['Total Stone amt'] : ($coll['TOTAL DIA COST'] >= 0 ? $coll['TOTAL DIA COST'] : ($coll['Tot Dia COST'] >= 0 ? $coll['Tot Dia COST'] : 0)))));

				if (isset($coll['Stone amt'])) {
					$col_stone_amt = (($coll['Stone amt'] != "") ? $coll['Stone amt'] : '');
				} else if (isset($coll['Stone amt  (Handling charge )'])) {
					$col_stone_amt = (($coll['Stone amt  (Handling charge )'] != "") ? $coll['Stone amt  (Handling charge )'] : '');
				} else if (isset($coll['Total Stone amt'])) {
					$col_stone_amt = (($coll['Total Stone amt'] != "") ? $coll['Total Stone amt'] : '');
				} else if (isset($coll['TOTAL DIA COST'])) {
					$col_stone_amt = (($coll['TOTAL DIA COST'] != "") ? $coll['TOTAL DIA COST'] : '');
				} else if (isset($coll['Tot Dia COST'])) {
					$col_stone_amt = (($coll['Tot Dia COST'] != "") ? $coll['Tot Dia COST'] : '');
				} else {
					$col_stone_amt = 0;
				}



				// Diamond Quality
				if (isset($coll['Material Inter. Quality'])) {
					$col_diamond_quality = (($coll['Material Inter. Quality'] != "") ? $coll['Material Inter. Quality'] : '');
				} else if (isset($coll['DIA QUALITY'])) {
					$col_diamond_quality = (($coll['DIA QUALITY'] != "") ? $coll['DIA QUALITY'] : '');
				} else if (isset($coll['Diamond Quality'])) {
					$col_diamond_quality = (($coll['Diamond Quality'] != "") ? $coll['Diamond Quality'] : '');
				} else {
					$col_diamond_quality = '';
				}

				// Diamond Weight
				if (isset($coll['Total Diamond Wt.'])) {
					$col_diamond_weight = (($coll['Total Diamond Wt.'] != "") ? $coll['Total Diamond Wt.'] : '');
				} else if (isset($coll['TOTAL DIA WT'])) {
					$col_diamond_weight = (($coll['TOTAL DIA WT'] != "") ? $coll['TOTAL DIA WT'] : '');
				} else if (isset($coll['Total Diamond Wt.'])) {
					$col_diamond_weight = (($coll['Total Diamond Wt.'] != "") ? $coll['Total Diamond Wt.'] : '');
				} else {
					$col_diamond_weight = '';
				}

				// Total Amt
				if (isset($coll['Total  amt'])) {
					$col_total_amt = (($coll['Total  amt'] != "") ? $coll['Total  amt'] : '');
				} else if (isset($coll['Amt'])) {
					$col_total_amt = (($coll['Amt'] != "") ? $coll['Amt'] : '');
				} else if (isset($coll['Amount'])) {
					$col_total_amt = (($coll['Amount'] != "") ? $coll['Amount'] : '');
				} else if (isset($coll['Total Amount'])) {
					$col_total_amt = (($coll['Total Amount'] != "") ? $coll['Total Amount'] : '');
				} else {
					$col_total_amt = '';
				}

				// Diamond Pcs
				if (isset($coll['Dia Pcs'])) {
					$col_diamond_pcs = (($coll['Dia Pcs'] != "") ? $coll['Dia Pcs'] : '');
				} else if (isset($coll['Dia. Pcs'])) {
					$col_diamond_pcs = (($coll['Dia. Pcs'] != "") ? $coll['Dia. Pcs'] : '');
				} else if (isset($coll['Total Diamond Pcs'])) {
					$col_diamond_pcs = (($coll['Total Diamond Pcs'] != "") ? $coll['Total Diamond Pcs'] : '');
				} else {
					$col_diamond_pcs = '';
				}

				// Labour Amt
				if (isset($coll['Labour amt'])) {
					$col_labour_amt = (($coll['Labour amt'] != "") ? $coll['Labour amt'] : 0);
				} else if (isset($coll['Tot Labour'])) {
					$col_labour_amt = (($coll['Tot Labour'] != "") ? $coll['Tot Labour'] : 0);
				} else if (isset($coll['TOTAL LAB COST'])) {
					$col_labour_amt = (($coll['TOTAL LAB COST'] != "") ? $coll['TOTAL LAB COST'] : 0);
				} else if (isset($coll['Labour Amount'])) {
					$col_labour_amt = (($coll['Labour Amount'] != "") ? $coll['Labour Amount'] : 0);
				} else {
					$col_labour_amt = 0;
				}

				// Total Color Stone Wt
				if (isset($coll['Total Color Stone Wt'])) {
					$col_total_color_stone_wt = (($coll['Total Color Stone Wt'] != "") ? $coll['Total Color Stone Wt'] : '');
				} else if (isset($coll['Color Stone Wt'])) {
					$col_total_color_stone_wt = (($coll['Color Stone Wt'] != "") ? $coll['Color Stone Wt'] : '');
				} else {
					$col_total_color_stone_wt = '';
				}

				// Material MM
				if (isset($coll['Material MM Size'])) {
					$col_material_mm_size = (($coll['Material MM Size'] != "") ? $coll['Material MM Size'] : '0');
				} else if (isset($coll['MM'])) {
					$col_material_mm_size = (($coll['MM'] != "") ? $coll['MM'] : '0');
				} else if (isset($coll['MM SIZE'])) {
					$col_material_mm_size = (($coll['MM SIZE'] != "") ? $coll['MM SIZE'] : '0');
				} else {
					$col_material_mm_size = '0';
				}

				// PO NO
				if (isset($coll['Po No.'])) {
					$coll_po_no = (($coll['Po No.'] != "") ? $coll['Po No.'] : '');
				} else if (isset($coll['Order PoNo'])) {
					$coll_po_no = (($coll['Order PoNo'] != "") ? $coll['Order PoNo'] : '');
				} else {
					$coll_po_no = '';
				}

				// SKU
				if (isset($coll['Sku no'])) {
					$coll_sku = (($coll['Sku no'] != "") ? $coll['Sku no'] : '');
				} else if (isset($coll['Sku'])) {
					$coll_sku = (($coll['Sku'] != "") ? $coll['Sku'] : '');
				} else {
					$coll_sku = '';
				}

				// Stone Rate
				if (isset($coll['Stone Rate'])) {
					$col_stone_rate = (($coll['Stone Rate'] != "") ? $coll['Stone Rate'] : 0);
				} else if (isset($coll['Stone Rate (Avg Wt / Stn)'])) {
					$col_stone_rate = (($coll['Stone Rate (Avg Wt / Stn)'] != "") ? $coll['Stone Rate (Avg Wt / Stn)'] : 0);
				} else {
					$col_stone_rate = 0;
				}

				// Metal Amt
				if (isset($coll['Metal Amt'])) {
					$col_metal_amount = (($coll['Metal Amt'] != "") ? $coll['Metal Amt'] : 0);
				} else if (isset($coll['Metal Amount'])) {
					$col_metal_amount = (($coll['Metal Amount'] != "") ? $coll['Metal Amount'] : 0);
				} else {
					$col_metal_amount = 0;
				}

				// Certificate No
				if (isset($coll['Certificate No.'])) {
					$col_certificate_no = (($coll['Certificate No.'] != "") ? $coll['Certificate No.'] : '');
				} else if (isset($coll['Certificate No'])) {
					$col_certificate_no = (($coll['Certificate No'] != "") ? $coll['Certificate No'] : '');
				} else if (isset($coll['Certificate no'])) {
					$col_certificate_no = (($coll['Certificate no'] != "") ? $coll['Certificate no'] : '');
				} else {
					$col_certificate_no = '';
				}

				// CGST
				if (isset($coll['CGST 2.50%'])) {
					$col_cgst = (($coll['CGST 2.50%'] != "") ? $coll['CGST 2.50%'] : '');
				} else if (isset($coll['CGST 1.5%'])) {
					$col_cgst = (($coll['CGST 1.5%'] != "") ? $coll['CGST 1.5%'] : '');
				} else if (isset($coll['CGST 2.5%'])) {
					$col_cgst = (($coll['CGST 2.5%'] != "") ? $coll['CGST 2.5%'] : '');
				} else {
					$col_cgst = '';
				}

				// SGST
				if (isset($coll['SGST 2.50%'])) {
					$col_sgst = (($coll['SGST 2.50%'] != "") ? $coll['SGST 2.50%'] : '');
				} else if (isset($coll['SGST 1.5%'])) {
					$col_sgst = (($coll['SGST 1.5%'] != "") ? $coll['SGST 1.5%'] : '');
				} else if (isset($coll['SGST 2.5%'])) {
					$col_sgst = (($coll['SGST 2.5%'] != "") ? $coll['SGST 2.5%'] : '');
				} else {
					$col_sgst = '';
				}

				// item no
				if (isset($coll['Item #'])) {
					$coll_item_no = (($coll['Item #'] != "") ? $coll['Item #'] : '');
				} else if (isset($coll['Batch No.'])) {
					$coll_item_no = (($coll['Batch No.'] != "") ? $coll['Batch No.'] : '');
				} else if (isset($coll['Batch No'])) {
					$coll_item_no = (($coll['Batch No'] != "") ? $coll['Batch No'] : '');
				} else {
					$coll_item_no = '';
				}

				if (isset($coll['Material Type'])) {
					$col_material_type = (($coll['Material Type'] != "") ? $coll['Material Type'] : '');
				} else {
					$col_material_type = '';
				}

				//var_dump($col_material_type);

				$all_product_batchno_data = $this->getValidBatchNo();
				if (in_array($coll_item_no, $all_product_batchno_data)) {
					$is_batchno_exist = true;
				}

				$all_product_certino_data = $this->getValidCertificate();
				if (in_array($col_certificate_no, $all_product_certino_data)) {
					$is_certino_exist = true;
				}

				if (!empty($col_sr_no)) {

					if (!empty($image[$row_i])) {
						$productData[$key]['image'] = $image[$row_i];
					}

					$productData[$key]['item'] = $coll_item_no;
					$productData[$key]['po_no'] = $coll_po_no;
					$productData[$key]['order_no'] = $col_order_no;
					$productData[$key]['certificate_no'] = $col_certificate_no;
					$productData[$key]['sku'] = $coll_sku;
					$productData[$key]['style'] = $col_style;
					$productData[$key]['metal_karat'] = (!empty($coll['Metal Karat']) ? $coll['Metal Karat'] : false);
					$productData[$key]['color'] = $col_metal_color;
					$productData[$key]['ringsize'] = (!empty($coll['Ring Size']) ? $coll['Ring Size'] : false);
					$productData[$key]['product_category'] = (!empty($coll['Product Category']) ? $coll['Product Category'] : false);
					$productData[$key]['gross_weight'] = $col_gross_wt;
					$productData[$key]['metal_weight'] = $col_total_metal_wt;
					$productData[$key]['metalrate'] = $coll['Metal Rate'];
					$productData[$key]['metalamount'] = $col_metal_amount;
					$productData[$key]['labouramount'] = $col_labour_amt;
					$productData[$key]['diamond_pcs'] = $col_diamond_pcs;
					$productData[$key]['diamond_weight'] = $col_diamond_weight;
					$productData[$key]['colorstone_pcs'] = (!empty($coll['Total Color Stone Pcs']) ? $coll['Total Color Stone Pcs'] : false);
					$productData[$key]['colorstone_weight'] = $col_total_color_stone_wt;
					$productData[$key]['total_stone_amount'] = (!empty($coll['Total Stone amt']) ? $coll['Total Stone amt'] : false);
					$productData[$key]['total_amount'] = $col_total_amt;
					$productData[$key]['costingdata_id'] = $costing_id;
					$productData[$key]['seive_size'] = $col_seive_size;
					$productData[$key]['material_type'] = $col_material_type;
					$productData[$key]['material_quality'] = $col_diamond_quality;
					$productData[$key]['material_category'] = (!empty($coll['Material Category']) ? $coll['Material Category'] : false);
					$productData[$key]['material_mm_size'] = $col_material_mm_size;
					$productData[$key]['material_pcs'] = (!empty($coll['Material pices']) ? $coll['Material pices'] : false);
					$productData[$key]['material_weight'] = (!empty($coll['Material Weight']) ? $coll['Material Weight'] : false);
					$productData[$key]['stone_rate'] = $col_stone_rate;
					$productData[$key]['hallmarking'] = (!empty($coll['Hallmarking']) ? $coll['Hallmarking'] : false);
					$productData[$key]['igi_charges'] = (!empty($coll['IGI']) ? $coll['IGI'] : false);
					$productData[$key]['stone_amount'] = $col_stone_amt;
					$productData[$key]['cgst'] = $col_cgst;
					$productData[$key]['sgst'] = $col_sgst;
					$productData[$key]['extra_price'] = $coll['Extra Price'];
					$productData[$key]['extra_price_for'] = $coll['Extra Price For'];

					$prev_key = $key;
					$row_i++;
				} else {

					if (strlen($coll['Seive Size']) > 0) {
						$productData[$prev_key]['seive_size'] = $productData[$prev_key]['seive_size'] . ',' . $coll['Seive Size'];
					}
					if (strlen($coll['Material Type']) > 0) {
						$productData[$prev_key]['material_type'] = (string) $productData[$prev_key]['material_type'] . ',' . $col_material_type;
					}
					if (strlen($col_diamond_quality) > 0) {
						$productData[$prev_key]['material_quality'] = (string) $productData[$prev_key]['material_quality'] . ',' . $col_diamond_quality;
					}
					if (strlen($coll['Material Category']) > 0) {
						$productData[$prev_key]['material_category'] = (string) $productData[$prev_key]['material_category'] . ',' . $coll['Material Category'];
					}

					if (strlen($coll['Material MM Size']) > 0) {
						$productData[$prev_key]['material_mm_size'] = $productData[$prev_key]['material_mm_size'] . ',' . $coll['Material MM Size'];
					}

					if (strlen($coll['Material pices']) > 0) {
						$productData[$prev_key]['material_pcs'] = (string) $productData[$prev_key]['material_pcs'] . ',' . $coll['Material pices'];
					}
					if (strlen($coll['Material Weight']) > 0) {
						$productData[$prev_key]['material_weight'] = (string) $productData[$prev_key]['material_weight'] . ',' . $coll['Material Weight'];
					}

					if ($coll['Stone Rate'] > 0) {
						$productData[$prev_key]['stone_rate'] = (string) $productData[$prev_key]['stone_rate'] . ',' . $coll['Stone Rate'];
					}

					if ($col_stone_amt > 0) {
						$productData[$prev_key]['stone_amount'] = (string) $productData[$prev_key]['stone_amount'] . ',' . $coll['Stone amt'];
					}

				}
				$newcosting_id = $costing_id;
			}
		}

		/** Start here */
		$productColl = array();
		$productsIdsArr = array();
		$stoneArr = array();
		$certificatesArr = array();
		foreach ($productData as $rowkey => $productDataColl) {

			$metalQuality = $productDataColl['metal_karat'];
			$metalColor = $productDataColl['color'];
			$metal_quality_value = $metalQuality . " " . $metalColor;
			$metal_quality = ProductHelper::_toGetMetalQualityId($metalQuality, $metalColor);
			$attribute_set_id = ProductHelper::_toGetAttributeSetId($productDataColl['product_category']);
			$category_id = ProductHelper::_toGetCategoryId($productDataColl['product_category']);
			$certificate_no = $productDataColl['certificate_no'];
			$batch_no = $productDataColl['item'];

			$data = array('small_image' => (!empty($productDataColl['image']) ? $productDataColl['image'] : ""), 'certificate_no' => $certificate_no, 'sku' => $productDataColl['sku'], 'style' => $productDataColl['style'], 'rts_ring_size' => $productDataColl['ringsize'], 'gross_weight' => $productDataColl['gross_weight'], 'rts_stone_weight' => $productDataColl['diamond_weight'], 'stone_color_value' => $productDataColl['colorstone_weight'], 'stone_carat_value' => $productDataColl['diamond_weight'], 'stone_use' => $productDataColl['diamond_pcs'], 'total_amount' => $productDataColl['total_amount'], 'po_no' => $productDataColl['po_no'], 'order_no' => $productDataColl['order_no'], 'item' => $productDataColl['item'], 'extra_price' => $productDataColl['extra_price'], 'extra_price_for' => $productDataColl['extra_price_for'], 'metal_quality_value' => $metal_quality_value, 'metal_quality' => $metal_quality, 'attribute_set_id' => $attribute_set_id);

			$metaldata = array('metal_quality_id' => $metal_quality, 'metal_weight' => $productDataColl['metal_weight'], 'metal_rate' => $productDataColl['metalrate'], 'metal_labour_charge' => $productDataColl['labouramount'], 'metal_amount' => $productDataColl['metalamount'], 'metal_type_id' => 0, 'metal_actual_weight' => 0);

			$categorydata = array('category_id' => $category_id, 'position' => 0);

			//* Stone Data -- start *//
			$stoneArr['seive_size'] = str_replace('1/2', ' 1/2', str_replace(' ', '', $productDataColl['seive_size']));
			$stoneArr['seive_size'] = (!empty($stoneArr['seive_size']) ? $stoneArr['seive_size'] : 0);
			$stoneArr['stone_shape'] = ProductHelper::_toGetDiamondShapeIdMultiple(str_replace(' ', '', $productDataColl['material_type']));
			$stoneArr['stone_clarity'] = ProductHelper::_toGetDiamondClarityIdMultiple(str_replace(' ', '', $productDataColl['material_quality']));
			//var_dump($productDataColl['material_quality']);exit;
			$stoneArr['stone_stone'] = str_replace(' ', '', $productDataColl['material_category']);
			$stoneArr['stone_stone'] = (!empty($stoneArr['stone_stone']) ? $stoneArr['stone_stone'] : 0);
			$stoneArr['mm_size'] = str_replace(' ', '', $productDataColl['material_mm_size']);
			$stoneArr['mm_size'] = (!empty($stoneArr['mm_size']) ? $stoneArr['mm_size'] : 0);
			$stoneArr['stone_use'] = str_replace(' ', '', $productDataColl['material_pcs']);
			$stoneArr['stone_use'] = (!empty($stoneArr['stone_use']) ? $stoneArr['stone_use'] : 0);
			$stoneArr['carat'] = str_replace(' ', '', $productDataColl['material_weight']);
			$stoneArr['carat'] = (!empty($stoneArr['carat']) ? $stoneArr['carat'] : 0);
			$stoneArr['stone_rate'] = str_replace(' ', '', $productDataColl['stone_rate']);
			$stoneArr['stone_rate'] = (!empty($stoneArr['stone_rate']) ? $stoneArr['stone_rate'] : 0);
			$stoneArr['stone_amount'] = str_replace(' ', '', $productDataColl['stone_amount']);
			$stoneArr['stone_amount'] = (!empty($stoneArr['stone_amount']) ? $stoneArr['stone_amount'] : 0);

			$tmpElem = array();
			foreach ($stoneArr as $colkey => $productDataCollElem) {
				if (strpos($productDataCollElem, ",") !== false) {
					$elemArr = explode(",", $productDataCollElem);
					foreach ($elemArr as $elemArrKey => $elemArrVal) {
						$tmpElem[$elemArrKey][$colkey] = $elemArrVal;
					}
				} else {
					if ($productDataCollElem != '') {
						$tmpElem[0][$colkey] = $productDataCollElem;
					}
				}
			}

			
			//* Stone Data -- end *//
			if (in_array($certificate_no, $all_product_certino_data) && in_array($batch_no, $all_product_batchno_data)) {
				$productUpdateColl = Products::where('certificate_no', $certificate_no)->where('item', $batch_no)->first();
				$productUpdateColl->update($data);
				$id = $productUpdateColl->id;
				$last_updated_id[$rowkey] = $productUpdateColl->id;
				$metalUpdateColl = ProductsMetal::where('metal_product_id', $id)->first();
				$metalUpdateColl->where('grp_metal_id', $metalUpdateColl->grp_metal_id)->update($metaldata);
				$categoryUpdateColl = CatalogCategoryProduct::where('product_id', $id)->first();
				$categoryUpdateColl->where('id', $categoryUpdateColl->id)->update($categorydata);
				$stonUpdateColl = ProductsStone::where('stone_product_id', $last_updated_id[$rowkey])->get();
				foreach ($stonUpdateColl as $key => $value) {
					ProductsStone::where('grp_stone_id', $value->grp_stone_id)->update($tmpElem[$key]);
				}
			} else {
				$productColl = (new Products)->create($data);
				$last_intersted_id[$rowkey] = $productColl->id;
				$metaldata['metal_product_id'] = $productColl->id;
				$metalColl = (new ProductsMetal)->create($metaldata);
				$categorydata['product_id'] = $productColl->id;
				$categoryColl = (new CatalogCategoryProduct)->create($categorydata);
				foreach ($tmpElem as $key => $stoneTmpElem) {
					$stoneTmpElem['stone_product_id'] = $last_intersted_id[$rowkey];
					$stoneColl = (new ProductsStone)->create($stoneTmpElem);
				}
			}
		}
		
		/** end here */
		$StatusMessage = Config::get('constants.message.import_success');
		return redirect('productupload/create')->with('success', $StatusMessage);

	}

	public function checkMasterKeyIsExist($arr) {
		foreach ($arr as $key => $rowArr) {
			if ($key == 0) {
				if (is_array($rowArr)) {
					if (!empty($rowArr['Sr No.']) || !empty($rowArr['S No.']) || !empty($rowArr['Sr. No']) || !empty($rowArr['Sr No.']) || !empty($rowArr['Sr. No.'])) {
						return $arr;
					} else {
						$finalArr = $this->checkMasterKeyIsExist($rowArr);
						return $finalArr;
					}
				} else {
					return false;
				}
			} else {
				break;
			}
		}
	}

	public function listofproducts() {
		$dmlProductData = Products::select('sku', 'item', 'certificate_no')->get()->toArray();
		foreach ($dmlProductData as $pindex => $pro) {
			echo 'Batch no : ' . $pro['item'] . ' | ';
			echo 'SKU : ' . $pro['sku'] . ' | ';
			echo 'Certificate no : ' . $pro['certificate_no'] . ' | ';
			echo '<br>';
		}
	}

	public function updateproduct(request $request) {
	    $id = $request->id;
	    $categoriesArr = array();
	    $_categories = DB::select(DB::raw("SELECT * FROM `catalog_category_entity_varchar` WHERE `attribute_id` = 41 AND `store_id` = 0"));
	    if (count($_categories) > 0)
	    {
	        foreach($_categories as $_category){
	            $categoriesArr[$_category->entity_id] = $_category->value;
	        }
	    }

	    $metal_Arr = array();
	    DB::setTablePrefix('');
	    $metalQualityArr = MetalQuality::get();
	    DB::setTablePrefix('dml_');
	    foreach ($metalQualityArr as $key => $metalQualityElem) {
	    	$metal_Arr[$metalQualityElem->grp_metal_quality_id]	 = $metalQualityElem->metal_quality;
	    }


	    $stone_shape_arr = array();
	    $attributeId = "141";
		$stone_shape_coll = DB::table(DB::raw('eav_attribute_option'))->join(DB::raw('eav_attribute_option_value'), DB::raw('eav_attribute_option.option_id'), '=', DB::raw('eav_attribute_option_value.option_id'))->where(DB::raw('eav_attribute_option.attribute_id'), '=', $attributeId)->get();
			foreach ($stone_shape_coll as $key => $stone_shape_elem) {
				$stone_shape_arr[$stone_shape_elem->option_id] = $stone_shape_elem->value;
			}


		$stone_clarity_arr = array();
		$attributeId = "145";
		$stone_clarity_coll = DB::table(DB::raw('eav_attribute_option'))
			->join(DB::raw('eav_attribute_option_value'), DB::raw('eav_attribute_option.option_id'), '=', DB::raw('eav_attribute_option_value.option_id'))
			->where(DB::raw('eav_attribute_option.attribute_id'), '=', $attributeId)
			->get();
			foreach ($stone_clarity_coll as $key => $stone_clarity_elem) {
				$stone_clarity_arr[$stone_clarity_elem->option_id] = $stone_clarity_elem->value;
			}

		//echo "<pre>"; print_r($stone_clarity_arr);exit;
	    $productdatas = array();
	    $productCollection = Products::with(['metals', 'stones','categorys'])->where('id',$id)->get();
	    foreach ($productCollection as $key => $productColl) {
	    	$sku = $productColl->sku;
	    	$image = $productColl->small_image;
	    	$certificate_no = $productColl->certificate_no;
	    	$style = $productColl->style;
	    	$po_no = $productColl->po_no;
	    	$gross_weight = $productColl->gross_weight;
	    	$item = $productColl->item;
	    	$total_amount = $productColl->total_amount;
	    	$category_id = $productColl['categorys']->category_id;
	    	$metal_weight = $productColl['metals']->metal_weight;
	    	$metal_rate = $productColl['metals']->metal_rate;
	    	$metal_amount = $productColl['metals']->metal_amount;
	    	$metal_labour_charge = $productColl['metals']->metal_labour_charge;
	    	$metal_quality_id = $productColl['metals']->metal_quality_id;
	    	$counter = count($productColl['stones']);
	    	for($i=0;$i<$counter;$i++) {
	    		$stone_arr['stone_shape'][] = $productColl['stones'][$i]->stone_shape;
	    		$stone_arr['stone_clarity'][] = $productColl['stones'][$i]->stone_clarity;
	    		$stone_arr['mm_size'][] = $productColl['stones'][$i]->mm_size;
	    		$stone_arr['seive_size'][] = $productColl['stones'][$i]->seive_size;
	    		$stone_arr['stone_use'][] = $productColl['stones'][$i]->stone_use;
	    		$stone_arr['carat'][] = $productColl['stones'][$i]->carat;
	    		$stone_arr['stone_rate'][] = $productColl['stones'][$i]->stone_rate;
	    		$stone_arr['stone_amount'][] = $productColl['stones'][$i]->stone_amount;
	    	}
	    }

	    $productdatas[] = array('sku' => $sku,'certificate_no' => $certificate_no,'style' => $style,'po_no' => $po_no,'gross_weight' => $gross_weight,'item' => $item,'category_id'=>$category_id,'metal_weight'=>$metal_weight,'metal_rate'=>$metal_rate, 'metal_amount' => $metal_amount,'metal_labour_charge' =>$metal_labour_charge,'metal_quality_id'=>$metal_quality_id,'stone_data' =>$stone_arr,'image' => $image,'total_amount' => $total_amount);
	    return view('productupload.updateproduct',compact('productdatas','categoriesArr','metal_Arr','stone_shape_arr','stone_clarity_arr','id'));

	}

	public function updateproductstore(request $request) {
		$id = $request->id;
	    if(!empty($id)) {
		    $style = $request->style;
		    $gross_weight = $request->gross_weight;
		    $certificate = $request->certificate;
		    $sku = $request->sku;
		    $item = $request->item;
		    $metal_weight = $request->metal_weight;
		    $metal_rate = $request->metal_rate;
		    $metal_amount = $request->metal_amount;
		    $metal_labour_charge = $request->metal_labour_charge;
		    $metal_quality_id = $request->metal_quality_id;
		    $stone_shape = $request->stone_shape;
		    $stone_clarity = $request->stone_clarity;
		    $mm_size = $request->mm_size;
		    $seive_size = $request->seive_size;
		    $stone_rate = $request->stone_rate;
		    $stone_amount = $request->stone_amount;
		    $carat = $request->carat;
		    $total_amount = $request->total_amount;
		    $category_id = $request->category_id;
		    $file = $request->file('product_image');
		    if(!empty($file)) {
		    	$product_image = $file->getClientOriginalName();
				$destinationPath = config('constants.dir.product_image_path');
	    		$file->move($destinationPath, $product_image);
	    	}
	    	else {
	    		$product_image = $request->old_image;
	    	}
		    $productCollection = Products::with(['metals', 'stones','categorys'])->where('id',$id)->get();
		    $productdata = array('style'=>$style,'item'=>$item,'gross_weight'=>$gross_weight,'certificate_no'=>$certificate,'sku'=>$sku,'small_image' => $product_image,'total_amount' => $total_amount); 
		    $metaldata = array('metal_weight'=>$metal_weight,'metal_rate'=>$metal_rate,'metal_amount'=>$metal_amount,'metal_labour_charge'=>$metal_labour_charge,'metal_quality_id'=>$metal_quality_id);
		    $categorydata = array('category_id'=>$category_id);

		    $stonetempArr = array();
		    for($stonecount = 0; $stonecount < count($stone_shape); $stonecount++) {
		    	$stonetempArr['stone_shape'] = $stone_shape[$stonecount];
		    	$stonetempArr['stone_clarity'] = $stone_clarity[$stonecount];
		    	$stonetempArr['mm_size'] = $mm_size[$stonecount];
		    	$stonetempArr['seive_size'] = $seive_size[$stonecount];
		    	$stonetempArr['stone_rate'] = $stone_rate[$stonecount];
		    	$stonetempArr['stone_amount'] = $stone_amount[$stonecount];
		    	$stonetempArr['carat'] = $carat[$stonecount];
		    	$stoneArr[] = $stonetempArr;
		    }
		   	Products::find($id)->update($productdata);
		   	CatalogCategoryProduct::where('product_id',$id)->update($categorydata);
		   	ProductsMetal::where('metal_product_id',$id)->update($metaldata);
			$stoneColl = ProductsStone::where('stone_product_id',$id)->get();
			foreach($stoneColl as $stonekey => $stoneElem) {
				$setArr = ProductsStone::find($stoneElem->grp_stone_id)->update($stoneArr[$stonekey]);
			}

			$message = Config::get('constants.message.update_products_success');
			return redirect('costing/product_list')->with('success', $message);
		}
		else {
			$message = Config::get('constants.message.update_products_failure')	;
			return redirect('costing/product_list')->with('success',$message);
		}
	}

	public function deleteproduct(request $request) {

		$ids = $request->id;
		$is_deleted = false;
		foreach ($ids as $key => $id) {
			
			if(!empty($id)) {

				ProductsMetal::where('metal_product_id',$id)->delete();
				CatalogCategoryProduct::where('product_id',$id)->delete();
				$stoneColl = ProductsStone::where('stone_product_id',$id)->get();
				foreach ($stoneColl as $key => $stoneElem) {
					ProductsStone::find($stoneElem->grp_stone_id)->delete();
				}
				$productupload = Products::find($id)->delete();
				$is_deleted = true;
				
			}
			else {
				$is_deleted = false;
			}
		}

		if($is_deleted) {
			$message = Config::get('constants.message.delete_products_success');
			return json_encode(array("message" => $message,'status' => 'true'));
		} 
		else {
			$message = Config::get('constants.message.delete_products_failure')	;
			return json_encode(array("message" => $message,'status' => 'false'));
		}
	}

}