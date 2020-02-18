<?php

namespace App\Http\Controllers;

use App;
use App\Diamond;
use App\DiamondInventory;
use App\DiamondInvoice;
use App\DiamondRaw;
use App\DiamondTransaction;
use App\Helpers\CommonHelper;
use App\Helpers\DiamondHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ProductHelper;
use App\Invoiceattachment;
use App\MmToSieve;
use App\Payment;
use App\PaymentType;
use App\Setting;
use App\TransactionType;
use App\User;
use Auth;
//use Dompdf\Options;
use Config;
use DateTime;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use PDF;
use PHPExcel_IOFactory;
use URL;

class DiamondController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {

		$diamond = DiamondInventory::orderBy('created_at', 'DESC')->paginate(10);
		$datacount = DiamondInventory::orderBy('created_at', 'DESC')->count();
		$stone_shape = DiamondInventory::select('stone_shape')->distinct()->pluck('stone_shape');

		$stone_clarity = DiamondInventory::select('stone_quality')->distinct()->pluck('stone_quality');

		//print_r($stone_shape);print_r($stone_clarity);exit;
		//dd($diamond);
		//echo "fdglfghflgkhm";exit;
		return view('diamond.index', compact('diamond', 'amountPaids', 'stone_shape', 'stone_clarity', 'datacount'))->with('i', ($request->input('page', 1) - 1) * 10);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create($id) {

		$diamondraw = DiamondRaw::where('id', $id)->get();
		$role = Auth::user()->roles->first()->name;
		$vendor_id = User::select('id')->where('name', $diamondraw[0]->vendor_name)->get();

		$data['stone_shape'] = DB::select(DB::raw("SELECT `main_table`.*, `eav_op_shape`.*, `eav_attr_stoneshape`.`value` AS `stone_shape` FROM `grp_stone_manage` AS `main_table` LEFT JOIN `eav_attribute_option` AS `eav_op_shape` ON eav_op_shape.option_id = main_table.stone_shape LEFT JOIN `eav_attribute_option_value` AS `eav_attr_stoneshape` ON eav_attr_stoneshape.option_id=main_table.stone_shape AND eav_op_shape.attribute_id=141 GROUP BY `main_table`.`stone_shape`"));

		$data['transactionTypes'] = TransactionType::pluck('name', 'id');
		$setting_data = Setting::select('key', 'value')->where('key', '=', "DIAMOND_LOSS_TOLERENCE_LIMIT")->get(); // where('id', '>', 1)->
		if (!empty($setting_data[0]->value)) {
			$setting_data = $setting_data[0]->value;

		} else {
			$setting_data = 0;
		}
		$data['diamond'] = Diamond::select();
		//Super Admin
		return view('diamond.create', ['data' => $data, 'diamondraw' => $diamondraw, 'vendor_id' => $vendor_id, 'setting_data' => $setting_data, 'role' => $role])->render();
	}

	public function createnew(Request $request) {

		$data['stone_shape'] = DB::select(DB::raw("SELECT `main_table`.*, `eav_op_shape`.*, `eav_attr_stoneshape`.`value` AS `stone_shape` FROM `grp_stone_manage` AS `main_table` LEFT JOIN `eav_attribute_option` AS `eav_op_shape` ON eav_op_shape.option_id = main_table.stone_shape LEFT JOIN `eav_attribute_option_value` AS `eav_attr_stoneshape` ON eav_attr_stoneshape.option_id=main_table.stone_shape AND eav_op_shape.attribute_id=141 GROUP BY `main_table`.`stone_shape`"));

		$data['stone_clarity'] = DB::select(DB::raw("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0"));

		$data['transactionTypes'] = TransactionType::pluck('name', 'id'); // where('id', '>', 1)->
		$setting_data = Setting::select('key', 'value')->where('key', '=', "DIAMOND_LOSS_TOLERENCE_LIMIT")->get(); // where('id', '>', 1)->
		$role = Auth::user()->roles->first()->name;

		if (!empty($setting_data[0]->value)) {
			$setting_data = $setting_data[0]->value;

		} else {
			$setting_data = 0;
		}
		$data['diamond'] = Diamond::select();
		return view('diamond.createnew', ['data' => $data, 'setting_data' => $setting_data, 'role' => $role])->render();

	}

	public function UpdateDiamondInventory($requestData) {

		$returndata = array();
		$returndata['code'] = 'false';
		$amount_paid_for_transcation = array();
		$CheckTmp = false;
		$querys = array();
		$queryCounter = 0;
		$CheckTmpCounter = 0;
		foreach ($requestData as $key => $DiamondPost) {

			$diamond_weights = $DiamondPost['diamond_weight'];
			$dm_sieve_size = $DiamondPost['sieve_size'];
			$dm_mm_size = $DiamondPost['mm_size'];
			$diamondQuality = $DiamondPost['diamond_quality'];
			$diamondShape = $DiamondPost['stone_shape'];
			$avg_rate = $DiamondPost['rate'];

			$diamondsColl = DB::table('diamond_inventorys')->where(
				'stone_quality', $diamondQuality)->where(
				'stone_shape', $diamondShape)->where(
				'mm_size', $dm_mm_size)->count();

			$diamondsColl2 = DB::table('diamond_inventorys')->where(
				'stone_quality', $diamondQuality)->where(
				'stone_shape', $diamondShape)->where(
				'sieve_size', $dm_sieve_size)->count();
			if ($diamondsColl > 0) {
				$diamonds = DB::table('diamond_inventorys')->where(
					'stone_quality', $diamondQuality)->where(
					'stone_shape', $diamondShape)->where(
					'mm_size', $dm_mm_size)->first();
			} else if ($diamondsColl2 > 0) {
				$diamonds = DB::table('diamond_inventorys')->where(
					'stone_quality', $diamondQuality)->where(
					'stone_shape', $diamondShape)->where(
					'sieve_size', $dm_sieve_size)->first();
			} else {
				$diamonds = array();
			}
			//var_dump($diamonds);exit;
			$diamondsToCollection = (array) $diamonds;
			/* echo "<pre>";
			print_r($diamonds);exit; */
			$diamondsCount = count($diamondsToCollection);

			if ($diamondsCount > 0) {
				/*$diamonds = DB::table('diamond_inventorys')->where(
					'stone_quality', $diamondQuality)->where(
					'stone_shape', $diamondShape)->where(
					'sieve_size', $dm_sieve_size)->first();*/

				if ($diamondsColl > 0) {
					$matchThese = $this->matchTheseMM($diamondQuality, $diamondShape, $dm_mm_size);
				} else {
					$matchThese = $this->matchTheseSeive($diamondQuality, $diamondShape, $dm_sieve_size);
				}

				$actual_diamond_weight = $diamonds->total_diamond_weight;
				if ($actual_diamond_weight >= $diamond_weights) {

					$calculated_diamond_weight = $actual_diamond_weight - $diamond_weights;
					$CheckTmp = true;
					$CheckTmpCounter++;

				} else {

					$CheckTmp = false;
					$msg = "You can't issue due to diamond weight is more than inventory !";
				}

			} else {
				$CheckTmp = false;
				$msg = "You can't issue due to combination of diamond you don't have in inventory !";

			}

			if ($CheckTmp) {

				if ($diamond_weights == $actual_diamond_weight) {
					$id = $diamonds->id;
					//$querys[] = "DELETE FROM dml_diamond_inventorys WHERE id='" . $id . "' ";
					$amount_paid_for_transcation[] = $avg_rate * $diamond_weights;
					$querys[] = "UPDATE dml_diamond_inventorys SET total_diamond_weight = 0 WHERE id='" . $id . "' ";

				} else {

					if ($diamondsColl > 0) {
						$amount_paid_for_transcation[] = $avg_rate * $diamond_weights;
						$querys[] = "UPDATE dml_diamond_inventorys SET total_diamond_weight = '" . $calculated_diamond_weight . "'  where stone_quality = '" . $DiamondPost['diamond_quality'] . "' AND stone_shape ='" . $DiamondPost['stone_shape'] . "' AND mm_size='" . $DiamondPost['mm_size'] . "' ";
					} else {
						if (!empty($DiamondPost['sieve_size'])) {

							$amount_paid_for_transcation[] = $avg_rate * $diamond_weights;

							$diamondsCheckSieve = DB::table('diamond_inventorys')->where(
								'stone_quality', $DiamondPost['diamond_quality'])->where(
								'stone_shape', $DiamondPost['stone_shape'])->where(
								'sieve_size', $DiamondPost['sieve_size'])->first();
							if (count($diamondsCheckSieve) > 0) {
								$querys[] = "UPDATE dml_diamond_inventorys SET total_diamond_weight = '" . $calculated_diamond_weight . "' where stone_quality = '" . $DiamondPost['diamond_quality'] . "' AND stone_shape ='" . $DiamondPost['stone_shape'] . "' AND sieve_size='" . $DiamondPost['sieve_size'] . "' ";
							} else {
								$msg = "You can't issue due to combination of diamond you don't have in inventory !";
							}
						} else {

							$msg = "You can't issue due to combination of diamond you don't have in inventory !";
						}
					}
				}
			}
			$queryCounter++;
		}

		if ($CheckTmpCounter == $queryCounter) {
			foreach ($querys as $query) {
				try {
					DB::unprepared($query);
					DB::commit();
				} catch (\Exception $e) {
					DB::rollback();
				}
			}
		}
		if (isset($msg)) {
			$returndata['msg'] = $msg;
			return $returndata;
		} else {
			$returndata['code'] = 'true';
			$returndata['data'] = $amount_paid_for_transcation;
			return $returndata;
		}
	}

	public function diamondissue(Request $request) {

		$data['stone_shape'] = DB::select(DB::raw("SELECT `main_table`.*, `eav_op_shape`.*, `eav_attr_stoneshape`.`value` AS `stone_shape` FROM `grp_stone_manage` AS `main_table` LEFT JOIN `eav_attribute_option` AS `eav_op_shape` ON eav_op_shape.option_id = main_table.stone_shape LEFT JOIN `eav_attribute_option_value` AS `eav_attr_stoneshape` ON eav_attr_stoneshape.option_id=main_table.stone_shape AND eav_op_shape.attribute_id=141 GROUP BY `main_table`.`stone_shape`"));
		$data['stone_clarity'] = DB::select(DB::raw("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0"));
		$data['transactionTypes'] = TransactionType::pluck('name', 'id'); // where('id', '>', 1)->
		$data['diamond'] = Diamond::select();
		$daimond_data = DiamondInventory::select('ave_rate', 'stone_quality', 'packet_id', 'mm_size', 'sieve_size')->get();

		return view('diamond.diamondissue', ['data' => $data])->render();
	}

	public function searchweight(Request $request) {

		if (empty($request->sieve_size)) {
			$ave_rate = DiamondInventory::select('ave_rate')->where('stone_quality', $request->quality)->where('stone_shape', $request->shape)->where('mm_size', $request->mm_size)->pluck('ave_rate')->first();
		}
		if (empty($request->mm_size)) {
			$ave_rate = DiamondInventory::select('ave_rate')->where('stone_quality', $request->quality)->where('stone_shape', $request->shape)->where('sieve_size', $request->sieve_size)->pluck('ave_rate')->first();
		}

		if (!empty($request->sieve_size) && !empty($request->mm_size)) {
			$ave_rate = DiamondInventory::select('ave_rate')->where('stone_quality', $request->quality)->where('stone_shape', $request->shape)->where('sieve_size', $request->sieve_size)->where('mm_size', $request->mm_size)->pluck('ave_rate')->first();
		}

		if (!empty($ave_rate)) {
			return response()->json(array('success' => true, 'result' => $ave_rate));
		} else {
			return response()->json(array('error' => false, 'data' => "Average Rate Not Found"));
		}

	}
	public function invoiceattachment(Request $request) {
		$invoicedatas = Invoiceattachment::orderBy('id', 'DESC')->paginate(10);
		$totalcount = Invoiceattachment::count();
		return view('diamond.invoiceattachment', ['invoicedatas' => $invoicedatas, 'totalcount' => $totalcount])->render();
	}

	public function invoiceattachmentResponse(Request $request) {
		$columns = array(

			0 => 'id',
			1 => 'name',
			2 => 'invoice_attachment_path');

		$maindata = Invoiceattachment::orderBy('id', 'DESC');

		$totalData = $maindata->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		if (empty($request->input('search.value'))) {
			$invoice_attachments = Invoiceattachment::offset($start)->limit($limit)->orderBy($order, $dir)->get();
		} else {
			$search = $request->input('search.value');
			$invoice_attachments = Invoiceattachment::where('name', 'LIKE', "%{$search}%")->orWhere('invoice_attachment_path', 'LIKE', "%{$search}%")->offset($start)->limit($limit)->orderBy($order, $dir)->get();
			$totalFiltered = Invoiceattachment::where('name', 'LIKE', "%{$search}%")->orWhere('invoice_attachment_path', 'LIKE', "%{$search}%")->count();
		}

		$data = array();
		if (!empty($invoice_attachments)) {
			$count = 1;
			foreach ($invoice_attachments as $invoice_attachment) {
				$data[] = array($count, $invoice_attachment->name, $invoice_attachment->invoice_attachment_path);
				$count++;
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

	public function multiplefileupload(Request $request) {

		$this->validate($request, [

			'images.*' => 'required|mimes:jpeg,jpg,png,pdf',
		]);
		$images = array();
		if ($files = $request->file('images')) {
			foreach ($files as $file) {
				$name = $file->getClientOriginalName();
				$file->move(config('constants.dir.diamond_invoice_attachment'), $name);
				$images[] = $name;
				$path = url('/') . '/' . config('constants.dir.diamond_invoice_attachment') . '/' . $name;
				/*Insert your data*/
				$data = array('name' => $name, 'invoice_attachment_path' => $path);
				Invoiceattachment::create($data);
				/*Insert your data*/
			}
		}
		return redirect()->back()->with('message', 'constants.message.Diamond_invoiceattachment_add_success');
	}

	public function getSortNameofShape() {
		$sortform = array('ROUND' => 'RD', 'MARQUISE' => 'MQ', 'PEAR' => 'PE',
			'PRINCESS' => 'PRI', 'EMERALD' => 'EMD',
			'OVAL' => 'OV', 'Cushion' => 'CUS', 'ASSCHER' => 'ASS',
			'RADIANT' => 'RAD', 'HEART' => 'HRT', 'TRILLION' => 'TRIN',
			'BAGUETTE' => 'BAG', 'TRIANGULAR' => 'TRI', 'SQUARE' => 'SQR',
			'TAPER' => 'TAP', 'TAPER BAGUETTE' => 'TAB',
		);
		return $sortform;
	}

	public function getSortNameofQuality() {
		$sortformquality = array('SI-IJ' => 'SIIJ', 'VS-IJ' => 'VSIJ', 'VS-HI' => 'VSHI',
			'VVS-GH' => 'VVSGH', 'VS-GH' => 'VSGH', 'VVS-EF' => 'VVSEF', 'SI2-I1-HI' => 'SI2I1HI', 'SI2-I1-IJ' => 'SI2I1IJ',
			'VVS-FG' => 'VVSFG', 'VVS-IJ' => 'VVSIJ', 'VVS-VS-IJ' => 'VVSVSIJ', 'SI-HI' => 'SIHI', 'I-IJ' => 'IIJ', 'VS-EF' => 'VSEF',
			'I-GH' => 'IGH', 'I-HI' => 'IHI', 'VS-SI-HI' => 'VSSIHI', 'VS-VVS-EF' => 'VSVVSEF', 'VS-FG' => 'VSFG', 'VS-VVS-HI' => 'VSVVSHI',
			'VVS-VS-GH' => 'VVSVSGH', 'VS-VVS-FG' => 'VSVVSFG', 'VS-VVS-I-GH' => 'VSVVSIGH', 'SI-GH' => 'SIGH', 'SI-JK' => 'SIJK', 'VVS-VS-I-GH' => 'VVSVSIGH',
			'SI-FG' => 'SIFG', 'VVS-VS-FG' => 'VVSVSFG', 'SI2-I1-GH' => 'SI2I1GH', 'SI-GH-I' => 'SIGHI', 'VS-SI-I-GH' => 'VSSIIGH', 'VS-SI-GH' => 'VSSIGH',
			'VS-SI-IJ' => 'VSSIIJ', 'VS-GH-I' => 'VS-GH-I', 'VVS-VS-JK' => 'VVSVSJK', 'VVS-VS-FGH' => 'VVSVSFGH', 'VVS-FGH' => 'VVSFGH', 'H-I-JSI' => 'HIJSI',
			'HI-J-SI' => 'HIJSI', 'VS-SI-FG' => 'VSSIFG', 'FG-VS-SI' => 'FGVSSI', 'VS-JK' => 'VSJK', 'JK-VS' => 'JKVS', 'GH-I2' => 'GHI2',
			'VVS-HI' => 'VVSHI', 'GH-SI-I1' => 'GHSII1', 'GH-I-VVS-VS' => 'GHIVVSVS', 'G-H-ISI' => 'GHISI', 'GH-SI-2' => 'GHSI2', 'SI-HI-IJ' => 'SIHIIJ',
			'VS-SI-HI-J' => 'VSSIHIJ', 'I1-GH' => 'I1GH');
		return $sortformquality;
	}

	public function importexcel(Request $request) {
		return view('diamond.importexcel')->render();
	}

	public function getAllVendorIds() {
		$vendorColl = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'Vendor');
		})->get();
		foreach ($vendorColl as $vendor) {
			$ids[] = $vendor['id'];
		}
		return $ids;
	}

	public function getAllPacketIds() {
		$pids = array();
		$packetcall = DiamondRaw::select('packet_name')->get();
		foreach ($packetcall as $packet) {
			$pids[] = $packet['packet_name'];

		}
		return $pids;
	}

	public function importexceldata(request $request) {

		$current_time = new DateTime('today');
		$sortform = $this->getSortNameofShape();
		$sortformquality = $this->getSortNameofQuality();
		//$path = $request->file('diamond_importexcel')->getRealPath();
		//$rowdata = Excel::load($path)->get();
		if (!empty($request->file('diamond_importexcel'))) {
			$extension = File::extension($request->file('diamond_importexcel')->getClientOriginalName());
			if ($extension == "xlsx" || $extension == "xls") {
				$path = $request->file('diamond_importexcel')->getRealPath();
				$rowdata = Excel::load($path)->get();
				//$objPHPExcel = PHPExcel_IOFactory::load($path);
			}
		} else {
			$rules = [
				'diamond_importexcel' => 'required|mimes:xlsx,xls',
			];

			$customMessages = [
				'required' => 'The :attribute field is required.',
			];
			$this->validate($request, $rules, $customMessages);
		}
		//Start code for images
		$i = 0;
		$objPHPExcel = PHPExcel_IOFactory::load($path);

		$row_i = 0;
		$error = true;
		$calculatedTotalWeight = array();
		if ($rowdata->count()) {
			foreach ($rowdata as $key => $coll) {

				$transactionType = 1;
				$diamond_weight = $coll['Diamond weight'];
				$diamond_quality = $coll['Diamond quality'];
				$stone_shape = $coll['Diamond shape'];
				$rate = $coll['Rate'];
				$mm_size = $coll['MM Size'];
				$amount_paid = $coll['Amount paid'];
				$purchased_at = $coll['Purchase date'];
				$due_date = $coll['Due date'];
				$invoice_no = $coll['Invoice no'];

				$diamondColl[$key]['vendor_id'] = $coll['Vendor name'];
				$diamondColl[$key]['invoice_number'] = $coll['Invoice no'];
				$diamondColl[$key]['stone_shape'] = $stone_shape;
				$diamondColl[$key]['diamond_quality'] = $diamond_quality;
				$diamondColl[$key]['diamond_weight'] = $diamond_weight;
				$diamondColl[$key]['rate'] = $rate;
				$diamondColl[$key]['purchased_at'] = $purchased_at;
				$diamondColl[$key]['due_date'] = $due_date;
				$invoicepath = $coll['Purchase invoice'];
				$purchase_invoice = substr($invoicepath, strrpos($invoicepath, '/') + 1);
				$diamondColl[$key]['purchased_invoice'] = $purchase_invoice;
				$diamondColl[$key]['comment'] = $coll['Comment'];
				$abbreviationsort = $sortform[$stone_shape];
				$abbreviationsortquality = $sortformquality[$diamond_quality];
				//$amount_without_gst = $amount_paid / 1.0025;
				$amount_without_gst = CommonHelper::getWithoutGSTValue($amount_paid);
				$amount_without_gst = sprintf("%.2f", $amount_without_gst);
				$diamondColl[$key]['amount_paid'] = $amount_without_gst;
				$diamondColl[$key]['amount_paid_with_gst'] = $amount_paid;
				//$diamondColl[$key]['packet_id'] = $coll['Packet Name'];
				$diamondColl[$key]['sieve_size'] = $coll['Sieve Size'];
				$diamondColl[$key]['mm_size'] = $mm_size;
				$user_id = Auth::user()->id;
				$diamondColl[$key]['user_id'] = $user_id;
				$diamondColl[$key]['transaction_at'] = date('Y-m-d H:i:s');
				$diamondColl[$key]['transaction_type'] = $transactionType;
				$filename = $diamondColl[$key]['purchased_invoice'];

				$ids = $this->getAllVendorIds();
				if (!in_array($diamondColl[$key]['vendor_id'], $ids)) {
					$error = false;
					$message = Config::get('constants.message.Diamond_vendor_exist');
				}

				if (!empty($coll['Packet Name'])) {
					$pids = $this->getAllPacketIds();
					if (!in_array($coll['Packet Name'], $pids)) {
						$error = false;
						$message = Config::get('constants.message.Diamond_packet_exist');
					}
					$calculatedTotalWeight[$coll['Packet Name']][] = $diamond_weight;
				}

				if ($purchased_at > $due_date) {
					$error = false;
					$message = Config::get('constants.message.Diamond_date_check');
				}

				$matchArr[] = array('stone_shape' => $stone_shape, 'diamond_quality' => $diamond_quality, 'mm_size' => $mm_size);
				if (!empty($mm_size)) {

					$abbreviationsort = $sortform[$stone_shape];
					$abbreviationsortquality = $sortformquality[$diamond_quality];
					$mmsizeintval = filter_var($mm_size, FILTER_SANITIZE_NUMBER_INT);
					$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $mmsizeintval;
				} else {

					$abbreviationsort = $sortform[$stone_shape];
					$abbreviationsortquality = $sortformquality[$diamond_quality];
					$seivesizeintval = filter_var($sieve_size, FILTER_SANITIZE_NUMBER_INT);
					$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $sievesizeintval;

				}

				$diamondColl[$key]['packet_id'] = $packetID;
				$insert_data[] = $diamondColl[$key];
			}

		}

		foreach ($calculatedTotalWeight as $key => $tmpWeight) {
			if (!empty($tmpWeight['Packet Name'])) {
				$sizingWeightByPacket = $this->sizingWeightByPacket($key);
				$wgt = 0;
				foreach ($tmpWeight as $key => $value) {
					$wgt += $value;
				}
				if ($wgt != $sizingWeightByPacket) {
					$error = false;
					$message = Config::get('constants.message.Diamond_weight_mismatch');
				}
			}
		}

		$unique = array_unique($matchArr, SORT_REGULAR);
		$diffCellUniq = array_diff_key($matchArr, $unique);
		if (count($diffCellUniq) > 0) {
			$error = false;
			$message = Config::get('constants.message.Diamond_combination_repeat');
		}

		if ($error) {

			$total_amount_paid = 0;
			$tempArr = array_unique(array_column($insert_data, 'invoice_number'));
			$requestDatas = array_intersect_key($insert_data, $tempArr);
			$transactions = array();
			foreach ($requestDatas as $key => $requestData) {

				$transactions[$requestData['invoice_number']] = $this->addtoaccountByExcel($requestData, $filename);

			}

			foreach ($diamondColl as $sheetkey => $result) {

				$result['transaction_id'] = $transactions[$result['invoice_number']];
				$DiamondTransaction = DiamondTransaction::create($result);
				$insertedIDTransaction[] = DB::getPdo()->lastInsertId();
			}

			$this->adddiamond($insert_data);
			$message = Config::get('constants.message.Diamond_Transaction_add_success');
			return redirect()->route('diamond.importexcel')
				->with('success', $message);

		} else {

			return redirect()->route('diamond.importexcel')->with('error', $message);
		}
	}

	public function sizingWeightByPacket($packId) {

		$sizingWeight = DiamondRaw::where('packet_name', $packId)->first()->sizing_weight;
		return $sizingWeight;
	}
	public function store(Request $request) {

		$pending_weight = $request->assorting_weight - $request->diamond_weight[0];

		$setting_data = Setting::select('key', 'value')->where('key', '=', "DIAMOND_LOSS_TOLERENCE_LIMIT")->get();
		$max_loss_allow = $setting_data[0]->value;
		if ($request->remaining_weight > $max_loss_allow) {
			$mesaage = Config::get('constants.message.exceeded_loss_limit');
			return redirect::back()->withErrors($mesaage)->withInput();
		}

		$current_time = new DateTime('today');
		$sortform = $this->getSortNameofShape();
		$sortformquality = $this->getSortNameofQuality();
		$transaction_type = DB::table('transaction_types')->select('id')->where('name', '=', 'Purchase')->get();
		$this->validate($request, [
			'stone_shape.*' => 'required',
			'diamond_weight.*' => 'required',
			'diamond_quality.*' => 'required',
			'transaction_type.*' => 'required',
			'amount_paid_with_gst.*' => 'required',
			'purchased_at.*' => 'required',
			'sieve_size.*' => 'required_without:mm_size',
			'due_date' => 'date_format:Y-m-d|after:$current_time',
		]);
		$role = Auth::user()->roles->first()->name;
		if ($role == "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
			$filename = $att_file->getClientOriginalName();

			if (!$filename) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		} else if ($role !== "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
			$filename = $att_file->getClientOriginalName();

			if (!$filename) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		} else {
			$filename = "";
		}
		$att_file = $request->file('purchased_invoice');
		$destinationPath = config('constants.dir.purchased_invoices');
		if (!is_dir($destinationPath)) {
			mkdir($destinationPath);
		}

		$user_id = Auth::user()->id;
		$requestData = $request->input();
		$assorting_weight = $request->assorting_weight;
		$stone_shape = $request->stone_shape;
		$diamond_quality = $request->diamond_quality;
		$diamond_weight = $request->diamond_weight;
		$sieve_size = $request->sieve_size;
		$mm_size = $request->mm_size;
		$amount_paid = $request->amount_paid_with_gst;
		$rate = $request->rate;
		$count_stone_shape = 1;
		if (is_array($stone_shape) || is_object($stone_shape)) {
			$count_stone_shape = count($stone_shape);
		}
		$total_diamond_weight = 0;

		$req_diamond_weight_arr = array();
		$req_diamond_rate_arr = array();
		$req_amount_paid = array();
		for ($countpre = 0; $countpre < $count_stone_shape; $countpre++) {
			$req_diamond_weight_arr[] = (float) $diamond_weight[$countpre];
			$req_diamond_rate_arr[] = (float) $rate[$countpre];
			$req_amount_paid[] = (float) $diamond_weight[$countpre] * (float) $rate[$countpre];
		}
		$real_total_amount_paid = array_sum($req_amount_paid);

		for ($count = 0; $count < $count_stone_shape; $count++) {

			$abbreviationsort = $sortform[$stone_shape[$count]];
			$abbreviationsortquality = $sortformquality[$diamond_quality[$count]];

			if (!empty($mm_size[$count])) {
				$mmsizeintval = filter_var(round($mm_size[$count], 2), FILTER_SANITIZE_NUMBER_INT);
				$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $mmsizeintval;

			} else {
				$sievesizeintval = filter_var(round($sieve_size[$count], 2), FILTER_SANITIZE_NUMBER_INT);
				$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $sievesizeintval;
			}

			$actual_amount_paid = ($amount_paid * $diamond_weight[$count]) / $real_total_amount_paid;

			//var_dump($actual_amount_paid);exit;

			//$amount_without_gst = $amount_paid / 1.0025;
			$amount_without_gst = CommonHelper::getWithoutGSTValue($amount_paid);
			$amount_without_gst = sprintf("%.2f", $amount_without_gst);

			//$actual_amount_without_gst = $actual_amount_paid / 1.0025;
			$actual_amount_without_gst = CommonHelper::getWithoutGSTValue($actual_amount_paid);
			$actual_amount_without_gst = sprintf("%.2f", $actual_amount_without_gst);

			$data = array(
				'stone_shape' => $stone_shape[$count],
				'diamond_quality' => $diamond_quality[$count],
				'diamond_weight' => $diamond_weight[$count],
				'sieve_size' => $sieve_size[$count],
				'mm_size' => $mm_size[$count],
				'amount_paid_with_gst' => $actual_amount_paid,
				'packet_id' => $packetID,
				'user_id' => $user_id,
				'purchased_at' => $request->input('purchased_at'),
				'transaction_type' => $transaction_type[0]->id,
				'transaction_at' => date('Y-m-d H:i:s'),
				'vendor_id' => $request->input('vendorId'),
				'purchased_invoice' => $filename,
				'comment' => $request->input('comment'),
				'invoice_number' => $request->input('invoice_number'),
				'due_date' => $request->input('due_date'),
				'amount_paid' => $actual_amount_without_gst,
				'rate' => $rate[$count],

			);

			$insert_data[] = $data;
			$total_diamond_weight += $diamond_weight[$count];
		}

		if ($total_diamond_weight > $assorting_weight) {

			$mesaage = Config::get('constants.message.Diamond_weight_mismatch');
			return redirect::back()->withErrors($mesaage);
		}

		/* For Add Account Entry - Start  */
		$transactions[$requestData['invoice_number']] = $this->addtoaccount($requestData, $filename);
		/* For Add Account Entry - End  */

		foreach ($insert_data as $TransentryData) {
			$TransentryData['transaction_id'] = $transactions[$TransentryData['invoice_number']];
			$DiamondTransaction = DiamondTransaction::create($TransentryData);
			$insertedIDTransaction[] = DB::getPdo()->lastInsertId();
		}

		switch ($request->input('transaction_type')) {
		case 1:
			$this->adddiamond($insert_data);
			break;

		default:
			$this->adddiamond($insert_data);
			break;
		}
		$diamond = DiamondRaw::select('id')->where('packet_name', $request->packet_id)->get();
		$diamondraw = DiamondRaw::findOrFail($diamond[0]->id);
		$total_weight = $diamondraw->cvd_rejected + $diamondraw->assorting_rejected + $diamondraw->sizing_rejected;

		$total_loss = $diamondraw->cvd_loss + $diamondraw->assorting_loss + $pending_weight;

		$diamondraw->total_rejected_weight = $total_weight;
		$diamondraw->total_loss = $total_loss;
		$diamondraw->sizing_weight = $total_diamond_weight;
		$diamondraw->sizing_rejected = 0;
		$diamondraw->sizing_loss = $pending_weight;
		$diamondraw->sizing_status = 1;
		$diamondraw->moved_to_inventory = 1;
		$diamondraw->update();

		$mesaage = Config::get('constants.message.Diamond_Transaction_add_success');
		return redirect()->route('diamond.index')
			->with('success', $mesaage);
	}

	public function storediamonds(Request $request) {
		//print_r($request->all());exit;
		$this->validate($request, [
			'stone_shape.*' => 'required',
			'diamond_weight.*' => 'required',
			'diamond_quality.*' => 'required',
			'transaction_type.*' => 'required',
			'amount_paid_with_gst.*' => 'required',
			'purchased_at.*' => 'required',
			'sieve_size.*' => 'required_without:mm_size',
			'due_date' => 'date_format:Y-m-d|after:$current_time',

		]);

		$role = Auth::user()->roles->first()->name;
		if ($role == "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
			$filename = $att_file->getClientOriginalName();

			if (!$filename) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		} else if ($role !== "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
			$filename = $att_file->getClientOriginalName();

			if (!$filename) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		} else {
			$filename = "";
		}
		//echo "fgsghfdg";exit;
		//print_r($request->all());exit;
		$current_time = new DateTime('today');
		$sortform = $this->getSortNameofShape();
		$sortformquality = $this->getSortNameofQuality();

		$user_id = Auth::user()->id;
		$requestData = $request->input();
		$stone_shape = $request->stone_shape;
		$diamond_quality = $request->diamond_quality;
		$diamond_weight = $request->diamond_weight;
		$sieve_size = $request->sieve_size;
		$mm_size = $request->mm_size;
		$amount_paid = $request->amount_paid_with_gst;
		$rate = $request->rate;
		$count_stone_shape = 1;
		if (is_array($stone_shape) || is_object($stone_shape)) {
			$count_stone_shape = count($stone_shape);
		}
		//echo "<pre>"; print_r($request->all());exit;

		$req_diamond_weight_arr = array();
		$req_diamond_rate_arr = array();
		$req_amount_paid = array();
		for ($countpre = 0; $countpre < $count_stone_shape; $countpre++) {
			$req_diamond_weight_arr[] = (float) $diamond_weight[$countpre];
			$req_diamond_rate_arr[] = (float) $rate[$countpre];
			$req_amount_paid[] = (float) $diamond_weight[$countpre] * (float) $rate[$countpre];
		}

		//var_dump($req_amount_paid);
		$real_total_amount_paid = array_sum($req_amount_paid);
		//var_dump($real_total_amount_paid);
		//exit;

		for ($count = 0; $count < $count_stone_shape; $count++) {
			//echo $amount_paid[$count];
			$abbreviationsort = $sortform[$stone_shape[$count]];
			$abbreviationsortquality = $sortformquality[$diamond_quality[$count]];
			if (!empty($mm_size[$count])) {
				$mmsizeintval = filter_var(round($mm_size[$count], 2), FILTER_SANITIZE_NUMBER_INT);
				$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $mmsizeintval;
			} else {
				$sievesizeintval = filter_var(round($sieve_size[$count], 2), FILTER_SANITIZE_NUMBER_INT);
				$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $sievesizeintval;
			}

			$amount_paid = (float) $amount_paid;
			//var_dump($amount_paid);
			//var_dump($diamond_weight[$count]);
			//exit;

			$real_amount_paid = (float) ((float) $rate[$count] * (float) $diamond_weight[$count]);
			$actual_amount_paid = ($amount_paid * (float) $real_amount_paid) / (float) $real_total_amount_paid;

			/*var_dump($actual_amount_paid);

				$a = CommonHelper::getWithGSTValue($actual_amount_paid);
				$b = CommonHelper::getWithoutGSTValue($actual_amount_paid);
				$c = CommonHelper::getGSTValue($actual_amount_paid);

				var_dump($a);
				var_dump($b);
				var_dump($c);
			*/

			//$actual_amount_without_gst = $actual_amount_paid / 1.0025;
			$actual_amount_without_gst = CommonHelper::getWithoutGSTValue($actual_amount_paid);
			$actual_amount_without_gst = sprintf("%.2f", $actual_amount_without_gst);

			//var_dump($actual_amount_without_gst);exit;

			//$amount_without_gst = $amount_paid / 1.0025;
			$amount_without_gst = CommonHelper::getWithoutGSTValue($amount_paid);
			$amount_without_gst = sprintf("%.2f", $amount_without_gst);
			$data = array(
				'stone_shape' => $stone_shape[$count],
				'diamond_quality' => $diamond_quality[$count],
				'diamond_weight' => $diamond_weight[$count],
				'sieve_size' => $sieve_size[$count],
				'mm_size' => $mm_size[$count],
				'amount_paid_with_gst' => $actual_amount_paid,
				'packet_id' => $packetID,
				'user_id' => $user_id,
				'purchased_at' => $request->input('purchased_at'),
				'transaction_type' => $request->input('transaction_type'),
				'transaction_at' => date('Y-m-d H:i:s'),
				'vendor_id' => $request->input('vendorId'),
				'purchased_invoice' => $filename,
				'comment' => $request->input('comment'),
				'invoice_number' => $request->input('invoice_number'),
				'due_date' => $request->input('due_date'),
				'amount_paid' => $actual_amount_without_gst,
				'rate' => $rate[$count],
			);
			$insert_data[] = $data;
		}

		/* For Add Account Entry - Start  */
		$transactions[$requestData['invoice_number']] = $this->addtoaccount($requestData, $filename);
		/* For Add Account Entry - End  */
		foreach ($insert_data as $TransentryData) {
			$TransentryData['transaction_id'] = $transactions[$TransentryData['invoice_number']];
			$DiamondTransaction = DiamondTransaction::create($TransentryData);
			$insertedIDTransaction[] = DB::getPdo()->lastInsertId();
		}
		switch ($request->input('transaction_type')) {
		case 1:
			$this->adddiamond($insert_data);
			break;
		default:
			$this->adddiamond($insert_data);
			break;
		}
		$mesaage = Config::get('constants.message.Diamond_Transaction_add_success');
		return redirect()->route('diamond.index')
			->with('success', $mesaage);
	}

	public function show(Request $request, $id) {
		$Diamondall = DiamondInventory::find($id);
		$DiamondInfo = DiamondInventory::where('id', $id)->get();
		$inventory_id = $id;

		foreach ($DiamondInfo as $Diamondsallinfo) {
			$DiamondQualitys = $Diamondsallinfo->stone_quality;
			$DiamondShapes = $Diamondsallinfo->stone_shape;
			$DiamondMMSize = $Diamondsallinfo->mm_size;
			$DiamondSieveSize = $Diamondsallinfo->sieve_size;

			$diamondTransactions = $this->getSizeDiffFliter($DiamondShapes, $DiamondQualitys, $DiamondMMSize, $DiamondSieveSize);

			$datacount = $diamondTransactions->count();
			$diamondTransactions = $diamondTransactions->paginate();
		}
		$trantype = TransactionType::orderBy('id', 'DESC')->get();

		$tid = DiamondTransaction::select('user_id')->orderBy('id', 'DESC')->distinct()->get();

		$name[] = Auth::User()->select('name', 'id')->where('id', $tid[0]->user_id)->get();

		$weightmin = $diamondTransactions->min('diamond_weight');
		$weightmax = $diamondTransactions->max('diamond_weight');

		$amount_paidmin = $diamondTransactions->min('amount_paid');
		$amount_paidmax = $diamondTransactions->max('amount_paid');

		$ratemin = $diamondTransactions->min('rate');
		$ratemax = $diamondTransactions->max('rate');

		return view('diamond.show', compact('diamondTransactions', 'id', 'datacount', 'trantype', 'name', 'weightmin', 'weightmax', 'amount_paidmin', 'amount_paidmax', 'ratemin', 'ratemax', 'inventory_id'))->with('i', ($request->input('page', 1) - 1) * 5);
	}

	public function edit($id) {
		$diamondTransactions = DiamondTransaction::find($id);
		$Transactions = DiamondTransaction::where("id", $id)->get();
		$data['stone_shape'] = DB::select(DB::raw("SELECT `main_table`.*, `eav_op_shape`.*, `eav_attr_stoneshape`.`value` AS `stone_shape` FROM `grp_stone_manage` AS `main_table` LEFT JOIN `eav_attribute_option` AS `eav_op_shape` ON eav_op_shape.option_id = main_table.stone_shape LEFT JOIN `eav_attribute_option_value` AS `eav_attr_stoneshape` ON eav_attr_stoneshape.option_id=main_table.stone_shape AND eav_op_shape.attribute_id=141 GROUP BY `main_table`.`stone_shape`"));
		$data['stone_clarity'] = DB::select(DB::raw("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0"));
		$data['transactionTypes'] = TransactionType::pluck('name', 'id'); // where('id', '>', 1)->
		$data['vendor_name'] = User::join('diamond_transactions', 'diamond_transactions.vendor_id', '=', 'users.id')->select('users.name')->limit(1)->get();
		return view('diamond.edit', ['data' => $data], compact('diamondTransactions', 'Transactions'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */

	public function update(Request $request, $id) {

		$input = $request->all();
		$name = Config::get('constants.enum.transaction_types.purchase');
		$transaction_id = TransactionType::select('id')->where('name', $name)->value('id');

		/*$this->validate($request, [
			'stone_shape.*' => 'required',
			'diamond_weight.*' => 'required',
			'diamond_quality.*' => 'required',
			'transaction_type.*' => 'required',
			'amount_paid_with_gst.*' => 'required',
			'purchased_at.*' => 'required',
			'sieve_size.*' => 'required_without:mm_size',
			'purchased_invoice.*' => 'required|mimes:jpeg,jpg,png,pdf,csv,xlsx,doc,docx',
			'due_date' => 'date_format:Y-m-d|after:$current_time',
		]);*/

		$role = Auth::user()->roles->first()->name;
		if ($role == "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
			$filename = $att_file->getClientOriginalName();

			if (!$filename) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		} else if ($role !== "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
			$filename = $att_file->getClientOriginalName();

			if (!$filename) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		} else {
			$filename = "";
		}
		if ($request->purchased_invoice != null) {

			$DiamondTransaction = DiamondTransaction::find($id);
			$prev_diamond_quality = $DiamondTransaction->diamond_quality;
			$prev_stone_shape = $DiamondTransaction->stone_shape;
			$prev_sieve_size = $DiamondTransaction->sieve_size;
			$prev_wt = $DiamondTransaction->diamond_weight; // prev transaction weight
			$new_wt = $input['diamond_weight']; // new transaction weight
			$amount_paid = $input['amount_paid_with_gst'];
			$rate = $input['rate'];
			$purchased_invoice = $filename;
			//$amout_paid_without_gst = $amount_paid / 1.0025;
			$amout_paid_without_gst = CommonHelper::getWithoutGSTValue($amount_paid);

			$user_id = Auth::user()->id;

			$matchTheseInput = $this->matchTheseMM($input['diamond_quality'], $input['stone_shape'], $input['mm_size']);
			$diamondMaster = DiamondInventory::where($matchTheseInput);
			$diamondMasterCount = $diamondMaster->count();
			$diamondMasterRecord = $diamondMaster->first();

		} else {
			$DiamondTransaction = DiamondTransaction::find($id);
			$prev_diamond_quality = $DiamondTransaction->diamond_quality;
			$prev_stone_shape = $DiamondTransaction->stone_shape;
			$prev_sieve_size = $DiamondTransaction->sieve_size;
			$filename = "";
			$prev_wt = $DiamondTransaction->diamond_weight; // prev transaction weight
			$new_wt = $input['diamond_weight']; // new transaction weight
			$amount_paid = $input['amount_paid_with_gst'];
			$rate = $input['rate'];
			//$amout_paid_without_gst = $amount_paid / 1.0025;
			$amout_paid_without_gst = CommonHelper::getWithoutGSTValue($amount_paid);
			$user_id = Auth::user()->id;

			$matchTheseInput = $this->matchTheseMM($input['diamond_quality'], $input['stone_shape'], $input['mm_size']);
			$diamondMaster = DiamondInventory::where($matchTheseInput);
			$diamondMasterCount = $diamondMaster->count();
			$diamondMasterRecord = $diamondMaster->first();

		}
		//	if ($prev_wt != $new_wt) {

		//var_dump($request->purchased_invoice);exit;

		if ($request->purchased_invoice != null) {
			$DiamondTransaction->update(['diamond_weight' => $new_wt, 'rate' => $rate, 'amount_paid_with_gst' => $amount_paid, 'amount_paid' => $amout_paid_without_gst, 'purchased_invoice' => $purchased_invoice]);

			if (!empty($input['mm_size'])) {

				$matchTheseInput = $this->matchTheseMMForTransaction($input['diamond_quality'], $input['stone_shape'], $input['mm_size']);
				$NewCollOfTransaction = DiamondTransaction::where($matchTheseInput)->where('transaction_type', $transaction_id)->get();
				$totalRate = 0;
				$count = 0;
				$total_weight = 0;

				//echo '<pre>';
				//print_r($NewCollOfTransaction);exit;

				$traposted_diamond_weight_arr = array();
				$traposted_diamond_rate_arr = array();
				$traposted_amount_paid = array();

				foreach ($NewCollOfTransaction as $key => $ntransCol) {
					$traposted_diamond_weight_arr[] = (float) $ntransCol->diamond_weight;
					$traposted_diamond_rate_arr[] = (float) $ntransCol->rate;
					$traposted_amount_paid[] = (float) $ntransCol->diamond_weight * (float) $ntransCol->rate;
					$totalRate += (float) $ntransCol->rate;
					$total_weight += (float) $ntransCol->diamond_weight;
					$count++;
				}

				$real_total_amount_paid = array_sum($traposted_amount_paid);
				$avg_rate = (float) round(($real_total_amount_paid / $total_weight), 2);
				//var_dump($real_total_amount_paid);exit;

				/*foreach ($NewCollOfTransaction as $key => $transColl) {
					$totalRate += $transColl->rate;
					$total_weight += (float) $transColl->diamond_weight;
					$count++;
				}*/

				//exit;
			}

		} else {

			$DiamondTransaction->update(['diamond_weight' => $new_wt, 'rate' => $rate, 'amount_paid_with_gst' => $amount_paid, 'amount_paid' => $amout_paid_without_gst]);

			if (!empty($input['mm_size'])) {

				$matchTheseInput = $this->matchTheseMMForTransaction($input['diamond_quality'], $input['stone_shape'], $input['mm_size']);
				$NewCollOfTransaction = DiamondTransaction::where($matchTheseInput)->where('transaction_type', $request->transaction_type)->get();
				$totalRate = 0;
				$count = 0;
				$total_weight = 0;

				$traposted_diamond_weight_arr = array();
				$traposted_diamond_rate_arr = array();
				$traposted_amount_paid = array();

				foreach ($NewCollOfTransaction as $key => $transColl) {
					$traposted_diamond_weight_arr[] = (float) $transColl->diamond_weight;
					$traposted_diamond_rate_arr[] = (float) $transColl->rate;
					$traposted_amount_paid[] = (float) $transColl->diamond_weight * (float) $transColl->rate;
					$total_weight += (float) $transColl->diamond_weight;
					$totalRate += $transColl->rate;
					$count++;
				}

				$real_total_amount_paid = array_sum($traposted_amount_paid);
				$avg_rate = (float) round(($real_total_amount_paid / $total_weight), 2);
			}

		}

		//var_dump($avg_rate);
		//exit;

		if ($diamondMasterCount > 0) {

			$new_master_wt = $diamondMasterRecord->total_diamond_weight - $prev_wt + $new_wt;
			$diamondMasterRecord->update(['total_diamond_weight' => $new_master_wt]);
			//$avg_rate = ($totalRate / $count);
			$diamondMasterRecord->update([
				'ave_rate' => $avg_rate,
			]);
		} else {

			$matchTheseInput = $this->matchTheseSeive($input['diamond_quality'], $input['stone_shape'], $input['sieve_size']);
			$diamondMaster = DiamondInventory::where($matchTheseInput);
			//print_r($diamondMaster->get());exit;
			$diamondMasterCount = $diamondMaster->count();
			$diamondMasterRecord = $diamondMaster->first();
			$matchTheseInputtra = $this->matchTheseSieveForTransaction($input['diamond_quality'], $input['stone_shape'], $input['sieve_size']);
			$NewCollOfTransaction = DiamondTransaction::where($matchTheseInputtra)->where('transaction_type', $transaction_id)->get();
			$totalRate = 0;
			$count = 0;

			$rate = array();
			$weight = array();
			$total = array();

			foreach ($NewCollOfTransaction as $key => $transColl) {
				$rate[] = $transColl->rate;
				$weight[] = $transColl->diamond_weight;
				$total[] = $rate[$key] * $weight[$key];
				$totalRate += $transColl->rate;
				$count++;
			}
			//print_r($rate);print_r($weight);print_r($total);exit;
			$real_total_amount_paid = array_sum($total);
			$total_weight = array_sum($weight);
			$newavg_rate = round($real_total_amount_paid / $total_weight, 2);

			$new_master_wt = $diamondMasterRecord->total_diamond_weight - $prev_wt + $new_wt;
			$diamondMasterRecord->update(['total_diamond_weight' => $new_master_wt]);

			$diamondMasterRecord->update([
				'ave_rate' => $newavg_rate,
			]);
		}

		$role = Auth::user()->roles->first()->name;
		if ($role == "Super Admin" && $DiamondTransaction->transaction_id == "" && $filename !== "") {

			$id = $this->addtoaccount($DiamondTransaction, $filename);

		}
		if ($role == "Super Admin" && !empty($DiamondTransaction->transaction_id) || $filename !== "") {

			$this->updatetoaccount($DiamondTransaction);

		}
		if ($role !== "Super Admin" && $filename !== "") {

			$this->updatetoaccount($DiamondTransaction);

		}

		if ($role == "Super Admin" && $DiamondTransaction->transaction_id !== "" && $filename !== "") {
			$DiamondTransaction->update(['transaction_id' => $id]);
		}
		$mesaage = Config::get('constants.message.Diamond_Transaction_update_success');
		return redirect()->route('diamond.index')->with('success', $mesaage);
	}

	public function adddiamond($requestData) {

		$sortform = $this->getSortNameofShape();
		$sortformquality = $this->getSortNameofQuality();

		$posted_diamond_weight_arr = array();
		$posted_diamond_rate_arr = array();
		$posted_amount_paid = array();
		//var_dump($requestData);
		foreach ($requestData as $key => $postedDiamond) {
			$posted_diamond_weight_arr[] = (float) $postedDiamond['diamond_weight'];
			$posted_diamond_rate_arr[] = (float) $postedDiamond['rate'];
			$posted_amount_paid[] = (float) $postedDiamond['diamond_weight'] * (float) $postedDiamond['rate'];
		}

		//var_dump($posted_amount_paid);exit;
		$real_total_amount_paid = array_sum($posted_amount_paid);

		$actual_amount_paid = 0;

		foreach ($requestData as $key => $DiamondPost) {

			$stoneShape = $DiamondPost['stone_shape'];
			$diamondQuality = $DiamondPost['diamond_quality'];
			$mmSize = $DiamondPost['mm_size'];
			$sieveSize = $DiamondPost['sieve_size'];
			$packetID = $DiamondPost['packet_id'];
			$rate = $DiamondPost['rate'];

			$diamondTransactions = $this->getSizeDiffFliter($stoneShape, $diamondQuality, $mmSize, $sieveSize);
			$name = Config::get('constants.enum.transaction_types.purchase');
			$transaction_id = TransactionType::select('id')->where('name', $name)->value('id');
			$diamondTransactions = $diamondTransactions->where('transaction_type', '=', $transaction_id)->get();

			$diamond_weights = 0;
			$total_amount_paid = 0;
			$real_amount_paid = 0;
			$count = 0;
			$rates = 0;

			foreach ($diamondTransactions as $diamondTranKey => $diamondTran) {

				$diamond_weights += (float) $diamondTran->diamond_weight;
				$total_amount_paid += (float) $diamondTran->amount_paid_with_gst;
				$real_amount_paid += (float) $diamondTran->rate * $diamondTran->diamond_weight;
				$actual_amount_paid += (float) (($total_amount_paid * $real_amount_paid) / $real_total_amount_paid);
				$transaction_type = $diamondTran->transaction_type;
				$packet_id = $diamondTran->packet_id;
				$sieve_sizes = (float) $diamondTran->sieve_size;
				$mm_sizes = (float) $diamondTran->mm_size;
				$rates += (float) $diamondTran->rate;
				$count++;
			}

			//$avg_rate = round($rates / $count);
			$avg_rate = round($real_amount_paid / $diamond_weights);
			//var_dump($avg_rate);exit;
			$user_id = Auth::user()->id;
			if (!empty($mmSize)) {

				$diamondSeiveColl = DB::table('diamond_inventorys')->where(
					'stone_quality', $diamondQuality)->where(
					'stone_shape', $stoneShape)->where(
					'mm_size', $mmSize)->get();
				if (count($diamondSeiveColl) > 0) {

					$matchThese = $this->matchTheseMM($diamondQuality, $stoneShape, $mmSize);

					$weightpurchaseTrans = TransactionType::where('name', "Purchase")->first();
					$weightpurchaseTransType = $weightpurchaseTrans->id;
					$weightpurchase = DiamondTransaction::select('diamond_weight')->where('transaction_type', $weightpurchaseTransType)->where('packet_id', $packet_id)->sum('diamond_weight');

					$miscweightTrans = TransactionType::where('name', "Misc")->first();
					$miscweightTransType = $miscweightTrans->id;
					$miscweight = DiamondTransaction::select('diamond_weight')->where('transaction_type', $miscweightTransType)->where('packet_id', $packet_id)->sum('diamond_weight');

					$issueTrans = TransactionType::where('name', "Issue")->first();
					$issueTransType = $issueTrans->id;
					$issueweight = DiamondTransaction::select('diamond_weight')->where('transaction_type', $issueTransType)->where('packet_id', $packet_id)->sum('diamond_weight');

					$sellTrans = TransactionType::where('name', "Sell")->first();
					$sellTransType = $sellTrans->id;
					$sellweight = DiamondTransaction::select('diamond_weight')->where('transaction_type', $sellTransType)->where('packet_id', $packet_id)->sum('diamond_weight');

					$total_weight = $weightpurchase - $issueweight - $miscweight - $sellweight;

					$upd = DiamondInventory::where($matchThese)->update(array('total_diamond_weight' => $total_weight, 'ave_rate' => $avg_rate));
				} else {

					$abbreviationsort = $sortform[$DiamondPost['stone_shape']];
					$abbreviationsortquality = $sortformquality[$DiamondPost['diamond_quality']];
					$mmsizeintval = filter_var($mm_sizes, FILTER_SANITIZE_NUMBER_INT);
					$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $mmsizeintval;

					$Diamond = DiamondInventory::create([
						'packet_id' => $packetID,
						'mm_size' => $mm_sizes,
						'sieve_size' => $sieve_sizes,
						'stone_shape' => $DiamondPost['stone_shape'],
						'stone_quality' => $DiamondPost['diamond_quality'],
						'total_diamond_weight' => $DiamondPost['diamond_weight'],
						'ave_rate' => $avg_rate,
						'created_by' => $user_id,
					]);

				}

			} else {

				$diamondSeiveColl = DB::table('diamond_inventorys')->where(
					'stone_quality', $diamondQuality)->where(
					'stone_shape', $stoneShape)->where(
					'sieve_size', $sieveSize)->get();
				if (count($diamondSeiveColl) > 0) {

					$matchThese = $this->matchTheseSeive($diamondQuality, $stoneShape, $sieveSize);
					$weightpurchaseTrans = TransactionType::where('name', "Purchase")->first();
					$weightpurchaseTransType = $weightpurchaseTrans->id;
					$weightpurchase = DiamondTransaction::select('diamond_weight')->where('transaction_type', $weightpurchaseTransType)->where('packet_id', $packet_id)->sum('diamond_weight');

					$miscweightTrans = TransactionType::where('name', "Misc")->first();
					$miscweightTransType = $miscweight->id;
					$miscweight = DiamondTransaction::select('diamond_weight')->where('transaction_type', $miscweightTransType)->where('packet_id', $packet_id)->sum('diamond_weight');

					$issueTrans = TransactionType::where('name', "Issue")->first();
					$issueTransType = $issueTrans->id;
					$issueweight = DiamondTransaction::select('diamond_weight')->where('transaction_type', $issueTransType)->where('packet_id', $packet_id)->sum('diamond_weight');

					$sellTrans = TransactionType::where('name', "Sell")->first();
					$sellTransType = $sellTrans->id;
					$sellweight = DiamondTransaction::select('diamond_weight')->where('transaction_type', $sellTransType)->where('packet_id', $packet_id)->sum('diamond_weight');

					$total_weight = $weightpurchase - $issueweight - $miscweight - $sellweight;

					$upd = DiamondInventory::where($matchThese)->update(array('total_diamond_weight' => $total_weight, 'ave_rate' => $avg_rate));

				} else {

					$abbreviationsort = $sortform[$DiamondPost['stone_shape']];
					$abbreviationsortquality = $sortformquality[$DiamondPost['diamond_quality']];
					$seivesizeintval = filter_var($sieveSize, FILTER_SANITIZE_NUMBER_INT);
					$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $seivesizeintval;

					$Diamond = DiamondInventory::create([
						'packet_id' => $packetID,
						'mm_size' => $mm_sizes,
						'sieve_size' => $sieveSize,
						'stone_shape' => $DiamondPost['stone_shape'],
						'stone_quality' => $DiamondPost['diamond_quality'],
						'total_diamond_weight' => $DiamondPost['diamond_weight'],
						'ave_rate' => $avg_rate,
						'created_by' => $user_id,
					]);
				}
			}
		}
		return true;
	}

	/* ADD To Account Table - start */
	public function addtoaccountByExcel($requestData, $filename) {

		$total_amount_paid = $requestData['amount_paid_with_gst'];

		$AccountlastId = Payment::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
		if (!empty($AccountlastId)) {
			$AccountlastId = (int) substr($AccountlastId, -3);
			$AccountlastId++;
		} else {
			$AccountlastId = 71;
		}
		$transaction_id = '100001' . (string) $AccountlastId;

		$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;
		$payment_sub_type = PaymentType::select('id')->where('parent_id', '=', $payment_type)->first()->id;
		$vendorId = $requestData['vendor_id'];
		$vendorColl = $vendors = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'Vendor');
		})->where('id', '=', $vendorId)->first();
		$vendorName = $vendorColl->name;
		$user_id = Auth::user()->id;
		$data = array(
			'transaction_id' => $transaction_id,
			'customer_id' => $requestData['vendor_id'],
			'customer_name' => $vendorName,
			'invoice_number' => $requestData['invoice_number'],
			'invoice_attachment' => $filename,
			'invoice_amount' => $total_amount_paid,
			'due_date' => $requestData['due_date'],
			'account_status' => '0',
			'payment_status' => '0',
			'payment_form' => 'Outgoing',
			'payment_type' => $payment_type,
			'payment_sub_type' => $payment_sub_type,
			'customer_type' => "System",
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			'created_by' => $user_id,
			'remarks' => "Purchased from Diamond inventory",
		);

		$role = Auth::user()->roles->first()->name;
		if ($role !== "Super Admin" && $filename !== "") {
			$Accountinsert = Payment::create($data);
			return $Accountinsert->id;
		}
		if ($role !== "Super Admin" && $filename !== "") {
			$Accountinsert = Payment::create($data);
			return $Accountinsert->id;
		}

	}
	/* ADD To Account Table - end */

	/* ADD To Account Table - start */
	public function addtoaccount($requestData, $filename) {

		$total_amount_paid = 0;
		$total_amount_paid = $requestData['amount_paid_with_gst'];

		$AccountlastId = Payment::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
		if ($AccountlastId == "") {
			$transaction_id = 1000001;
		} else {
			$transaction_id = (int) $AccountlastId->transaction_id;
			$transaction_id++;
		}

		$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;
		$payment_sub_type = PaymentType::select('id')->where('name', '=', 'Purchase')->first()->id;
		$user_id = Auth::User()->id;

		if (!empty($requestData['vendorId'])) {
			$vendor_id = $requestData['vendorId'];
			$name = $requestData['vendor_name'];
		} else {
			$vendor_id = $requestData->vendor_id;
			$name = Auth::User()->select('name')->where('id', $requestData->vendor_id)->value('name');
		}

		$data = array(
			'transaction_id' => $transaction_id,
			'customer_id' => $vendor_id,
			'customer_name' => $name,
			'invoice_number' => (isset($requestData['invoice_number']) ? $requestData['invoice_number'] : 0),
			'invoice_attachment' => $filename,
			'invoice_amount' => $total_amount_paid,
			'due_date' => (isset($requestData['due_date']) ? $requestData['due_date'] : 0),
			'account_status' => '0',
			'payment_status' => '0',
			'payment_form' => (isset($requestData['payment_form']) ? $requestData['payment_form'] : 'Outgoing'),
			'payment_type' => $payment_type,
			'payment_sub_type' => $payment_sub_type,
			'customer_type' => "System",
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			'created_by' => $user_id,
			'remarks' => "Purchased from Diamond inventory",
		);
		$role = Auth::user()->roles->first()->name;
		if ($role == "Super Admin" && $filename !== "") {
			$Accountinsert = Payment::create($data);

			return $Accountinsert->id;
		}

		if ($role !== "Super Admin" && $filename !== "") {
			$Accountinsert = Payment::create($data);

			return $Accountinsert->id;
		}

	}
	/* ADD To Account Table - end */
	public function updatetoaccount($requestData) {

		$total_amount_paid = 0;
		$total_amount_paid = $requestData['amount_paid_with_gst'];
		$AccountlastId = Payment::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
		$transaction_id = (int) $AccountlastId->transaction_id;
		$transaction_id++;
		$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;
		$payment_sub_type = PaymentType::select('id')->where('name', '=', 'Purchase')->first()->id;
		$name = Auth::User()->select('name')->where('id', $requestData->vendor_id)->value('name');

		$user_id = Auth::User()->id;
		Payment::where('id', $requestData->transaction_id)->update([
			'customer_id' => $requestData->vendor_id,
			'customer_name' => $name,
			'invoice_number' => (isset($requestData->invoice_number) ? $requestData->invoice_number : 0),
			'invoice_attachment' => $requestData->purchased_invoice,
			'invoice_amount' => $total_amount_paid,
			'due_date' => (isset($requestData->due_date) ? $requestData->due_date : 0),
			'account_status' => '0',
			'payment_status' => '0',
			'payment_form' => (isset($requestData->payment_form) ? $requestData->payment_form : 'Outgoing'),
			'payment_type' => $payment_type,
			'payment_sub_type' => $payment_sub_type,
			'customer_type' => "System",
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			'created_by' => $user_id,
			'remarks' => "Purchased from Diamond inventory"]);
	}

	public function autoComplete(Request $request) {
		$querysData = $request->get('term', '');

		$vendors = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'Vendor');
		});
		if (!empty($querysData)) {
			$vendors = $vendors->where('name', 'like', '%' . $querysData . '%');
		}
		$vendors = $vendors->orderBy('id', 'DESC')->get();
		$data = array();
		foreach ($vendors as $vendor) {
			$data[] = array('value' => $vendor->name, 'id' => $vendor->id);
		}

		if (count($data)) {
			return $data;
		} else {
			return ['value' => 'No Result Found', 'id' => ''];
		}

	}

	public function autoCompleteShape(Request $request) {
		$querysData = $request->get('term', '');

		$shapeWhere = '';
		if (!empty($querysData)) {
			$shapeWhere = "WHERE `eav_attr_stoneshape`.`value` LIKE '%" . $querysData . "%'";
		}

		$shape = DB::select(DB::raw("SELECT `main_table`.*, `eav_op_shape`.*, `eav_attr_stoneshape`.`value` AS `stone_shape` FROM `grp_stone_manage` AS `main_table` LEFT JOIN `eav_attribute_option` AS `eav_op_shape` ON eav_op_shape.option_id = main_table.stone_shape LEFT JOIN `eav_attribute_option_value` AS `eav_attr_stoneshape` ON eav_attr_stoneshape.option_id=main_table.stone_shape AND eav_op_shape.attribute_id=141 " . $shapeWhere . " GROUP BY `main_table`.`stone_shape`"));

		$data = array();
		foreach ($shape as $shapes) {
			if (!empty($shapes->stone_shape) && !empty($shapes->option_id)) {
				$data[] = array('value' => $shapes->stone_shape, 'id' => $shapes->option_id);
			}
		}

		if (count($data)) {
			return $data;
		} else {
			return ['value' => 'No Result Found', 'id' => ''];
		}

	}

	public function autoCompleteInvoiceQuality(Request $request) {

		$querysData = $request->get('term', '');

		$shapeWhere = '';
		if (!empty($querysData)) {
			$shapeWhere = "WHERE `dml_diamond_invoices`.`stone_quality` LIKE '%" . $querysData . "%'";
		}
		$diamondShape = DiamondInventory::select('stone_quality')->orWhere('stone_quality', 'like', '%' . $querysData . '%')->groupBy('stone_quality')->get();

		foreach ($diamondShape as $diamondShapeVal) {
			//echo "<pre>";print_r($diamondShapeVal->toArray());
			$shapeid['id'][] = ProductHelper::_toGetDiamondClarityId($diamondShapeVal['stone_quality']);
			$shapename['value'][] = $diamondShapeVal['stone_quality'];
		}
		//exit;

		foreach ($shapeid['id'] as $rowkey => $shapeValue) {
			$shape[$shapeid['id'][$rowkey]] = $shapename['value'][$rowkey];
		}

		foreach ($shape as $key => $value) {
			$stnshp['value'] = $value;
			$stnshp['id'] = $key;
			$fnlstnshp[] = $stnshp;
		}

		if (count($fnlstnshp)) {
			return $fnlstnshp;
		} else {
			return ['value' => 'No Result Found', 'id' => ''];
		}

	}

	public function SelectedAutoCompleteInvoiceQuality(Request $request) {
		//print_r($request->all());exit;
		$querysData = $request->get('term', '');
		$shapetxt = ProductHelper::_toGetDiamondShapeValue($request->shape);

		//print_r($shapetxt);exit;
		$shapeWhere = '';
		if (!empty($querysData)) {
			$shapeWhere = "WHERE `dml_diamond_invoices`.`stone_quality` LIKE '%" . $querysData . "%'";
		}
		$diamondShape = DiamondInventory::select('stone_quality')->where('stone_shape', '=', $shapetxt)->where('stone_quality', 'like', '%' . $querysData . '%')->groupBy('stone_quality')->get();
		$shapeID = array();
		//print_r($diamondShape);exit;
		foreach ($diamondShape as $diamondShapeVal) {

			$shapeID['id'][] = ProductHelper::_toGetDiamondClarityId($diamondShapeVal['stone_quality']);
			$shapename['value'][] = $diamondShapeVal['stone_quality'];
		}
		//print_r($shapeID['id']);exit;
		foreach ($shapeID['id'] as $rowkey => $shapeValue) {

			$shape[$shapeID['id'][$rowkey]] = $shapename['value'][$rowkey];
		}

		foreach ($shape as $key => $value) {
			$stnshp['value'] = $value;
			$stnshp['id'] = $key;
			$fnlstnshp[] = $stnshp;
		}

		if (count($fnlstnshp)) {
			return $fnlstnshp;
		} else {
			return ['value' => 'No Result Found', 'id' => ''];
		}
	}

	public function autoCompleteInvoiceShape(Request $request) {

		$querysData = $request->get('term', '');

		$shapeWhere = '';
		if (!empty($querysData)) {
			$shapeWhere = "WHERE `dml_diamond_invoices`.`stone_shape` LIKE '%" . $querysData . "%'";
		}

		$diamondShape = DiamondInventory::select('stone_shape')->orWhere('stone_shape', 'like', '%' . $querysData . '%')->groupBy('stone_shape')->get();
		foreach ($diamondShape as $diamondShapeVal) {
			$shapeid['id'][] = ProductHelper::_toGetDiamondShapeId($diamondShapeVal['stone_shape']);
			$shapename['value'][] = $diamondShapeVal['stone_shape'];
		}

		foreach ($shapeid['id'] as $rowkey => $shapeValue) {
			$shape[$shapeid['id'][$rowkey]] = $shapename['value'][$rowkey];
		}

		foreach ($shape as $key => $value) {
			$stnshp['value'] = $value;
			$stnshp['id'] = $key;
			$fnlstnshp[] = $stnshp;
		}

		if (count($fnlstnshp)) {
			return $fnlstnshp;
		} else {
			return ['value' => 'No Result Found', 'id' => ''];
		}

	}

	public function autoCompleteQuality(Request $request) {
		$querysData = $request->get('term', '');

		$qltWhere = '';
		if (!empty($querysData)) {
			$qltWhere = " AND `EAOV`.`value` LIKE '%" . $querysData . "%'";
		}

		$quality = DB::select(DB::raw("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0" . $qltWhere));
		$data = array();
		foreach ($quality as $qualitys) {
			$data[] = array('value' => $qualitys->value, 'id' => $qualitys->option_id);
		}

		if (count($data)) {
			return $data;
		} else {
			return ['value' => 'No Result Found', 'id' => ''];
		}

	}

	public function diamondissuestore(Request $request) {

		$issueGotFailed = false;
		$msgs = array();
		$paid_amounts = array();
		$avg_rate = array();
		$current_time = new DateTime('today');
		$sortform = $this->getSortNameofShape();
		$sortformquality = $this->getSortNameofQuality();

		$this->validate($request, [
			'stone_shape.*' => 'required',
			'diamond_weight.*' => 'required',
			'diamond_quality.*' => 'required',
			'vendor_name.*' => 'required',
			'po_no.*' => 'required',
		]);

		$transaction_type = DB::table('transaction_types')->select('id')->where('name', '=', 'Issue')->get();
		$user_id = Auth::user()->id;
		$requestData = $request->input();
		$stone_shape = $request->stone_shape;
		$diamond_quality = $request->diamond_quality;
		$diamond_weight = $request->diamond_weight;
		$sieve_size = $request->sieve_size;
		$mm_size = $request->mm_size;
		$pieces = $request->pieces;
		$rate = $request->rate;
		$custom_rate = $request->custom_rate;
		$existing_rate = $request->existing_rate;
		$custom_stone_quality = $request->custom_diamond_quality;
		$custom_sieve_size = $request->custom_sieve_size;
		$custom_mm_size = $request->custom_mm_size;
		$is_adjustable = $request->custom_chk;
		$count_stone_shape = array();

		if (is_array($stone_shape) || is_object($stone_shape)) {
			$count_stone_shape = count($stone_shape);
		}
		$customCounter = 0;
		$existingCounter = 0;
		for ($count = 0; $count < $count_stone_shape; $count++) {
			$abbreviationsort = $sortform[$stone_shape[$count]];
			$abbreviationsortquality = $sortformquality[$diamond_quality[$count]];

			if (!empty($mm_size[$count])) {
				$mmsizeintval = filter_var(round($mm_size[$count], 2), FILTER_SANITIZE_NUMBER_INT);
				$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $mmsizeintval;
			} else {
				$sievesizeintval = filter_var(round($sieve_size[$count], 2), FILTER_SANITIZE_NUMBER_INT);
				$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $sievesizeintval;
			}

			if ($rate[$count] == 'Custom') {
				if (isset($custom_rate[$customCounter])) {
					$avg_rate[] = $custom_rate[$customCounter];
					$customCounter++;
				}
			}

			if ($rate[$count] == 'Existing') {
				if (isset($existing_rate[$existingCounter])) {
					$avg_rate[] = $existing_rate[$existingCounter];
					$existingCounter++;
				}
			}

			//start blank entry
			$amount_paid_with_gst = 0;
			$invoice_number = 0;
			$due_date = null;
			$filename = 0;
			$amount_without_gst = 0;
			//end blank entry

			$data = array(
				'stone_shape' => $stone_shape[$count],
				'diamond_quality' => $diamond_quality[$count],
				'diamond_weight' => $diamond_weight[$count],
				'sieve_size' => $sieve_size[$count],
				'mm_size' => $mm_size[$count],
				'pieces' => $pieces[$count],
				'packet_id' => $packetID,
				'user_id' => $user_id,
				'purchased_at' => date('Y-m-d H:i:s'),
				'transaction_type' => $transaction_type[0]->id,
				'transaction_at' => date('Y-m-d H:i:s'),
				'vendor_id' => $request->input('vendorId'),
				'purchased_invoice' => $filename,
				'po_number' => $request->input('po_number'),
				'comment' => $request->input('comment'),
				'invoice_number' => $invoice_number,
				'due_date' => $request->input('due_date'),
				'rate' => $avg_rate[$count],
				'custom_mm_size' => $custom_mm_size[$count],
				'custom_sieve_size' => $custom_sieve_size[$count],
				'custom_stone_quality' => $custom_stone_quality[$count],
				'is_adjustable' => $is_adjustable[$count],
			);

			$insert_data[] = $data;
			$voucher_data[] = $data;

		}

		//start
		$counter_data = count($insert_data);
		$match_combination = array();
		$diamondSum = 0;
		foreach ($insert_data as $key => $value) {
			$stoneShape = $value['stone_shape'];
			$diamond_weight = $value['diamond_weight'];
			$diamond_quality = $value['diamond_quality'];
			$diamond_sievesize = $value['sieve_size'];
			$diamond_mmsize = $value['mm_size'];

			if ($diamond_mmsize != "") {
				$match_combination[$key] = $this->checkStoneCombination($stoneShape, $diamond_quality, $diamond_mmsize, $diamond_sievesize);
			} else {
				$match_combination[$key] = $this->checkStoneCombination($diamond_weight, $diamond_quality, $diamond_mmsize, $diamond_sievesize);
			}

			if ($key > 0) {
				$check_for_same_arr = array();
				$check_for_same_arr[] = $match_combination[$key - 1];
				$check_for_same_arr[] = $match_combination[$key];
				if ($match_combination[$key] === $match_combination[$key - 1]) {
					$insert_data[$key]['diamond_weight'] = $insert_data[$key - 1]['diamond_weight'] + $insert_data[$key]['diamond_weight'];
					unset($insert_data[$key - 1]);

				}

			}

		}

		//echo "<pre>"; print_r($insert_data);

		$updateResult = $this->UpdateDiamondInventory($insert_data);
		if ($updateResult['code'] == 'false') {
			$issueGotFailed = true;
			$msgs[] = $updateResult['msg'];
		} else {
			$paid_amounts = $updateResult['data'];
		}

		//print_r($insert_data);exit;

		if (!empty($voucher_data)) {
			if ($issueGotFailed) {
				$updatemsg = $msgs[0];
				return redirect('diamond/diamondissue')->with('error', $updatemsg);
			} else {
				$name = $this->issuevaucher($voucher_data, $avg_rate);

				if ($name['msg'] == "done") {
					$msg = Config::get('constants.message.diamond_issue_added_success');
					$msg .= "<br/>" . ' Click on link to download <a target="_blank" href="' . url('uploads/issuevaucher/' . $name['name']) . '"> Issue Voucher </a>';
					$request->session()->flash("success", $msg);
					return redirect('diamond/diamondissue');
				} else {
					return redirect('diamond/diamondissue')->with('error', Config::get('constants.message.settings_error'));
				}

			}
		}
	}

	public function issuevaucher($insert_data, $paid_amounts) {

		$issue_voucher_no = $this->generateRandomString();
		//print_r($insert_data);exit;
		foreach ($insert_data as $idKey => $TransentryData) {

			$TransentryData['issue_voucher_no'] = $issue_voucher_no;
			$TransentryData['transaction_id'] = 0;
			$amount_paid = CommonHelper::getWithoutGSTValue($paid_amounts[$idKey]);
			$TransentryData['amount_paid'] = $paid_amounts[$idKey] * $TransentryData[
				'diamond_weight'];
			$TransentryData['amount_paid_with_gst'] = $amount_paid;
			$DiamondTransaction[] = DiamondTransaction::create($TransentryData);
		}

		foreach ($DiamondTransaction as $key => $value) {
			$DiamondIds[$key] = $value->id;
		}
		$customPaper = array(0, 0, 1024, 1440); //720 // 1240
		$data = ['data' => $DiamondTransaction];
		$dt = new DateTime($data['data'][0]->purchased_at);
		$date = $dt->format('Y-m-d');

		$getname = User::select('name', 'gstin', 'state', 'address')->where('id', $data['data'][0]->vendor_id)->get();

		$name = $getname[0]->name;
		$address = $getname[0]->address;
		$gstin = $getname[0]->gstin;
		$state = $getname[0]->state;
		$pdf = PDF::loadView('diamond.issuevaucher', compact('data', 'name', 'address', 'gstin', 'state', 'issue_voucher_no', 'date'))->setPaper($customPaper, 'A4');
		$path = public_path('uploads/issuevaucher/');
		$name = 'diamondisse_' . time() . '.pdf';
		$pdf->save($path . $name);

		foreach ($DiamondIds as $id) {

			$diamond = DiamondTransaction::find($id);
			$diamond->issue_vaucher = $name;
			$diamond->issue_voucher_no = $issue_voucher_no;
			$diamond->update();
		}
		/*$search_voucher = Setting::where('key', config('constants.settings.keys.diamond_voucher_series'))->first()->value;
			$new_voucher = (int) $search_voucher + 1;
			$nid = Setting::select('id')->where('key', config('constants.settings.keys.diamond_voucher_series'))->get();
			$setting = Setting::find($nid[0]->id);
			$setting->value = $new_voucher;
			$setting->update();*/
		return ['msg' => 'done', 'name' => $name];
		//}
		//exit;

		/* else {

			return ['msg' => 'not done', 'name' => ""];
		}*/

	}

	public function diamondmiscloss(request $request, $id) {
		$inventoryId = $id;
		$diamondColl = DiamondInventory::where('id', $inventoryId)->first();
		$weight = $diamondColl['total_diamond_weight'];
		$misc_loss_limit = Setting::where('key', config('constants.settings.keys.misc_loss_limit'))->first('value');
		$max_misc_limit = $weight + $misc_loss_limit->value;
		$min_misc_limit = $weight - $misc_loss_limit->value;
		return view('diamond.diamondmiscloss', compact('id', 'weight', 'max_misc_limit', 'min_misc_limit'));
	}

	public function countMiscLoss(request $request) {
		$inventoryId = $request['id'];
		$postweight = $request['weight'];
		$diamondColl = DiamondInventory::where('id', $inventoryId)->first();
		$weightPrev = $diamondColl->total_diamond_weight;
		$weightCal = $weightPrev - $postweight;
		return $weightCal;

	}
	public function diamondmisclossstore(request $request) {

		$max_misc_limit = $request->max_misc_limit;
		$min_misc_limit = $request->min_misc_limit;
		$this->validate($request, [
			'remaining_weight' => 'required|numeric|between:' . $min_misc_limit . ',' . $max_misc_limit,
			'misc_loss' => 'required',
			'comment' => 'required',
		]);

		$miscLoss = $request->misc_loss;
		if ($miscLoss < 0) {
			$sign = '-';
		} else {
			$sign = "";
		}
		$weight = $request->remaining_weight;
		$inventoryId = $request->inventory_id;
		$postComment = $request->comment;
		$this->addTransactions($inventoryId, abs($miscLoss), $weight, $postComment, $sign);
		$this->updateWeightInventory($inventoryId, $weight);
		$msg = config::get('constants.message.Diamond_weight_update_success');
		$request->session()->flash("success", $msg);
		return redirect('diamond-inventory');
	}

	public function updateWeightInventory($inventoryId, $weight) {
		$diamondColl = DiamondInventory::where('id', $inventoryId)->first();
		$diamondColl->update(['total_diamond_weight' => $weight]);
	}

	public function addTransactions($inventoryId, $miscLoss, $postWeight, $postComment, $sign) {
		$transaction = new DiamondTransaction();
		$diamondColl = DiamondInventory::where('id', $inventoryId)->first();
		$diamondInventoryWeight = $diamondColl['total_diamond_weight'];
		$transaction->diamond_weight = $miscLoss;
		$transaction->packet_id = $diamondColl['packet_id'];
		$transaction->diamond_quality = $diamondColl['stone_quality'];
		$transaction->stone_shape = $diamondColl['stone_shape'];
		$transaction->mm_size = $diamondColl['mm_size'];
		$transaction->sieve_size = $diamondColl['sieve_size'];
		$transactionColl = TransactionType::where('name', "Misc")->first();
		$transaction->transaction_type = $transactionColl->id;
		$transaction->transaction_at = date('Y-m-d H:i:s');
		$transaction->comment = $postComment;
		$transaction->user_id = Auth::user()->id;
		$transaction->transaction_id = $this->getTransactionId();
		$transaction->purchased_at = date('Y-m-d H:i:s');
		$transaction->purchased_invoice = "0";
		$transaction->amount_paid = "0.0";
		$transaction->amount_paid_with_gst = "0";
		$transaction->weight_sign = $sign;
		$transaction->rate = "0.00";
		$transaction->save();
	}

	public function getTransactionId() {

		$AccountlastId = Payment::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
		if (!empty($AccountlastId)) {
			$AccountlastId = (int) substr($AccountlastId, -3);
			$AccountlastId++;
		} else {
			$AccountlastId = 71;
		}
		$transaction_id = '100001' . (string) $AccountlastId;
		return $transaction_id;
	}

	public function diamond_issue_vaucher(Request $request) {

		$customPaper = array(0, 0, 740, 1440);

		$data = DiamondTransaction::where('id', $request->id)->first();

		$exactFilePath = base_path("public" . '/' . config('constants.dir.issue_vaucher')) . $data['issue_vaucher'];
		/*print_r($exactFilePath);exit;*/
		return response()->download($exactFilePath);
	}

	public function diamond_invoice(Request $request) {

		$invoice_number = DiamondTransaction::where('id', $request->id)->select('invoice_number')->pluck('invoice_number')->first();
		$id = DiamondInvoice::where('invoice_number', $invoice_number)->select('id')->pluck('id')->first();
		$customPaper = array(0, 0, 740, 1440);

		//$data = DiamondInvoice::where('id', $id)->first();

		$exactFilePath = base_path("public" . '/' . config('constants.dir.diamond_invoice')) . $id . '.pdf';
		/*print_r($exactFilePath);exit;*/
		return response()->download($exactFilePath);
	}

	public function diamond_invoice_download(request $request) {
		$diamondid = $request->id;
		$id = DiamondInvoice::where('id', $diamondid)->select('id')->pluck('id')->first();
		$customPaper = array(0, 0, 740, 1440);

		//$data = DiamondInvoice::where('id', $id)->first();

		$exactFilePath = base_path("public" . '/' . config::get('constants.dir.diamond_invoice')) . $id . '.pdf';

		return response()->download($exactFilePath);
	}

	public function dimondissuerecipt(Request $request) {
		return view('diamond.dimondissuerecipt', compact('data', 'userdata', 'name'));
	}

	public function filter_search(Request $request) {

		$columns = array(

			0 => 'id',
			1 => 'packet_id',
			2 => 'stone_quality',
			3 => 'stone_shape',
			4 => 'total_diamond_weight',
			5 => 'ave_rate',
			6 => 'mm_size',
			7 => 'sieve_size',
			8 => 'action');

		$maindata = DiamondInventory::orderBy('id', 'DESC');

		$totalData = $maindata->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		//print_r($order);exit;
		$data = array();
		$params = $request->post();

		$maindata = DiamondInventory::orderBy($order, $dir);

		if (!empty($request['textfilter'])) {
			$maindata = $maindata->where('stone_shape', $request['textfilter']);
		}

		if (!empty($request['textfilterquality'])) {

			$maindata = $maindata->where('stone_quality', $request['textfilterquality']);
		}

		if (!empty($request['mmsize'])) {
			$maindata = $maindata->where('mm_size', 'like', $request['mmsize']);
		}

		if (!empty($request['sivesize'])) {
			$maindata = $maindata->where('sieve_size', $request['sivesize']);
		}

		$datacount = $maindata->count();
		$datacoll = $maindata->offset($start)->limit($limit)->orderBy($order, $dir)->get();

		$datacollection = $datacoll;

		$data["draw"] = intval($request->input('draw'));
		$data["recordsTotal"] = $datacount;
		$data["recordsFiltered"] = $datacount;
		$data['deferLoading'] = $datacount;

		if (count($datacollection) > 0) {
			foreach ($datacollection as $key => $diamond) {

				$srno = $key + 1 + $start;

				//$actions = 'sdfjn dfgkj fgk df';
				$actions = '   <a class="color-content table-action-style" href="' . route('diamond.transactions', $diamond->id) . '"><i class="material-icons md-18">remove_red_eye</i></a>';
				$actions .= '  <a title="Update weight" class="color-content table-action-style" href="' . route('diamond.diamondmiscloss', $diamond->id) . '"><i class="material-icons md-18">remove_from_queue</i></a>';

				$data['data'][] = array($srno, $diamond->packet_id, $diamond->stone_quality, $diamond->stone_shape, $diamond->total_diamond_weight, '&#x20b9;' . ' ' . $diamond->ave_rate, $diamond->mm_size, $diamond->sieve_size, $actions);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;

	}

	public function getSizeDiffFliter($DiamondShapes, $DiamondQualitys, $DiamondMMSize, $DiamondSieveSize) {

		$Coll = DiamondTransaction::where('stone_shape', $DiamondShapes)->where('diamond_quality', $DiamondQualitys)->where('mm_size', $DiamondMMSize)->where('transaction_type', '!=', 11)->get();
		if (count($Coll) > 0) {
			$maindata = DiamondTransaction::where('stone_shape', $DiamondShapes)->where('diamond_quality', $DiamondQualitys)->where('mm_size', $DiamondMMSize);
		} else {
			$maindata = DiamondTransaction::where('stone_shape', $DiamondShapes)->where('diamond_quality', $DiamondQualitys)->where('sieve_size', $DiamondSieveSize);
		}
		return $maindata;
	}

	public function getSizeDiffCounterFilter($DiamondShapes, $DiamondQualitys, $DiamondMMSize, $DiamondSieveSize) {

		$Coll = DiamondTransaction::where('stone_shape', $DiamondShapes)->where('diamond_quality', $DiamondQualitys)->where('mm_size', $DiamondMMSize)->get();
		if (count($Coll) > 0) {
			$datacount = DiamondTransaction::where('stone_shape', $DiamondShapes)->where('diamond_quality', $DiamondQualitys)->where('mm_size', $DiamondMMSize)->orderBy('id', 'DESC');
		} else {
			$datacount = DiamondTransaction::where('stone_shape', $DiamondShapes)->where('diamond_quality', $DiamondQualitys)->where('sieve_size', $DiamondSieveSize)->orderBy('id', 'DESC');
		}

		return $datacount;
	}

	public function filter_diamond(Request $request) {

		$columns = array(
			0 => 'id',
			1 => 'stone_shape',
			2 => 'packet_id',
			3 => 'diamond_weight',
			4 => 'amount_paid_with_gst',
			5 => 'amount_paid',
			6 => 'rate',
			7 => 'mm_size',
			8 => 'sieve_size',
			9 => 'transaction_type',
			10 => 'user_id',
			11 => 'purchased_at',
			12 => 'action');

		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		$start = $request->input('start');
		$limit = $request->input('length');
		$data = array();

		$params = $request->post();
		$Diamondall = DiamondInventory::find($request->invetory_id);

		$DiamondInfo = DiamondInventory::where('id', $request->invetory_id)->get();

		//print_r($DiamondInfo);exit;
		foreach ($DiamondInfo as $Diamondsallinfo) {
			$DiamondQualitys = $Diamondsallinfo->stone_quality;
			$DiamondShapes = $Diamondsallinfo->stone_shape;
			$DiamondMMSize = $Diamondsallinfo->mm_size;
			$DiamondSieveSize = $Diamondsallinfo->sieve_size;
			$maindata = $this->getSizeDiffFliter($DiamondShapes, $DiamondQualitys, $DiamondMMSize, $DiamondSieveSize);

		}

		if (!empty($request['reset'])) {
			$maindata = $maindata;
		} else {

			if (!empty($request['textfilter'])) {

				$maindata->where('transaction_type', $request['textfilter']);

			}

			if (!empty($request['textfilterid'])) {

				$maindata->where('user_id', $request['textfilterid']);

			}

			if (is_numeric($request['weightStart']) && is_numeric($request['weightEnd'])) {
				$minWet = $request['weightStart'];
				$maxWet = $request['weightEnd'];
				$maindata->whereBetween('diamond_weight', [$minWet, $maxWet]);

			}

			if ($request['amtStart'] >= 0 && $request['amtEnd'] >= 0) {
				$minAmt = $request['amtStart'];
				$maxAmt = $request['amtEnd'];

				$maindata->whereBetween('amount_paid', [$minAmt, $maxAmt]);

			}

			if ($request['rateStart'] >= 0 && $request['rateEnd'] >= 0) {
				$minrate = (float) $request['rateStart'];
				$maxrate = (float) $request['rateEnd'];

				$maindata->whereBetween('rate', [$minrate, $maxrate]);

			}
		}

		$datacount = $maindata->count();
		$maindata = $maindata->offset($start)->limit($limit)->orderBy($order, $dir)->get();

		//End here
		$datacoll = $maindata;
		$datacollection = $maindata;
		$data["draw"] = intval($request->input('draw'));
		$data["recordsTotal"] = $datacount;
		$data["recordsFiltered"] = $datacount;
		if (count($datacollection) > 0) {
			foreach ($datacollection as $key => $diamond) {

				$srno = $key + 1 + $start;

				$username = Auth::User()->select('name')->where('id', $diamond->user_id)->get();
				$transby = $username[0]->name;
				$trantype = TransactionType::select('name')->where('id', $diamond->transaction_type)->orderBy('id', 'DESC')->get();
				if ($diamond->transaction_type == '1') {
					$badge = 'badge-success';
				} elseif ($diamond->transaction_type == '2' || $diamond->transaction_type == '3' || $diamond->transaction_type == '4' || $diamond->transaction_type == '5') {
					$badge = 'badge-danger';
				} elseif ($diamond->transaction_type == '6') {
					$badge = 'badge-warning';
				} else {
					$badge = 'badge-info';
				}
				$transaction_type = '<td><span class="badge ' . $badge . ' py-1 px-2">' . $trantype[0]->name . '</span></td>';
				if ($diamond->transaction_type == 1) {
					$actions = '  <a class="color-content table-action-style" title="Download Invoice" href="' . route('diamond_download_purchase_invoice', $diamond->id) . '"><i class="material-icons md-18">file_download</i></a>';

					$actions .= '    <a class="color-content table-action-style" title="Edit" href="' . route('diamond.edit', $diamond->id) . '"><i class="material-icons md-18">edit</i></a>';
				}

				if ($diamond->transaction_type == 2) {
					$actions = '<a class="color-content table-action-style" title="IssueVaucher" href="' . route('diamond.diamond_issue_vaucher', ['id' => $diamond->id]) . '"><i class="material-icons md-18">file_download</i></a>';
				}
				if ($diamond->transaction_type == 6) {
					$actions = '<a class="color-content table-action-style" title="Generate Invoice" href="' . route('diamond.diamond_invoice', ['id' => $diamond->id]) . '"><i class="material-icons md-18">file_download</i></a>';
				}
				if ($diamond->transaction_type == 7) {
					$actions = '';
				}

				$quality = DiamondInventory::select('stone_quality')->where('packet_id', $diamond->packet_id)->value('stone_quality');

				$stone_quality = $quality;
				$data['data'][] = array($srno, $diamond->stone_shape, $stone_quality, $diamond->diamond_weight, CommonHelper::covertToCurrency($diamond->amount_paid_with_gst), CommonHelper::covertToCurrency($diamond->amount_paid), CommonHelper::covertToCurrency($diamond->rate), $diamond->mm_size, $diamond->sieve_size, $transaction_type, $transby, $diamond->transaction_at, $actions);
			}
		} else {

			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
		}

		echo json_encode($data);exit;
	}

	public function matchTheseMMForTransaction($diamond_weight, $stone_shape, $size) {
		$matchThese = [
			'diamond_quality' => $diamond_weight,
			'stone_shape' => $stone_shape,
			'mm_size' => $size,
		];
		return $matchThese;
	}

	public function matchTheseSieveForTransaction($diamond_weight, $stone_shape, $size) {
		$matchThese = [
			'diamond_quality' => $diamond_weight,
			'stone_shape' => $stone_shape,
			'sieve_size' => $size,
		];
		return $matchThese;
	}

	public function matchTheseMM($diamond_weight, $stone_shape, $size) {
		$matchThese = [
			'stone_quality' => $diamond_weight,
			'stone_shape' => $stone_shape,
			'mm_size' => $size,
		];
		return $matchThese;
	}

	public function matchTheseSeive($diamond_weight, $stone_shape, $size) {
		$matchThese = [
			'stone_quality' => $diamond_weight,
			'stone_shape' => $stone_shape,
			'sieve_size' => $size,
		];
		return $matchThese;
	}

	public function generatediamondinvoice(request $request) {

		$qualityid['id'] = array();
		$shapeid['id'] = array();
		$shape = array();
		$quality = array();
		$diamondQuality = DiamondInventory::select('stone_quality')->groupBy('stone_quality')->get();
		foreach ($diamondQuality as $diamondQualityVal) {
			$qualityid['id'][] = ProductHelper::_toGetDiamondClarityId($diamondQualityVal['stone_quality']);
			$qualityname['name'][] = $diamondQualityVal['stone_quality'];
		}

		foreach ($qualityid['id'] as $rowkey => $qualityValue) {
			$quality[$qualityid['id'][$rowkey]] = $qualityname['name'][$rowkey];
		}

		$diamondShape = DiamondInventory::select('stone_shape')->groupBy('stone_shape')->get();
		foreach ($diamondShape as $diamondShapeVal) {
			$shapeid['id'][] = ProductHelper::_toGetDiamondShapeId($diamondShapeVal['stone_shape']);
			$shapename['name'][] = $diamondShapeVal['stone_shape'];
		}

		foreach ($shapeid['id'] as $rowkey => $shapeValue) {
			$shape[$shapeid['id'][$rowkey]] = $shapename['name'][$rowkey];
		}

		$params = $request->post();
		$productIds = isset($params['productIds']) ? $params['productIds'] : '';
		//Get state list by cuontry id
		$get_country_list = '';
		if (App::environment('local')) {
			$get_country_list = Config::get('app.get_country_list');

		} else {
			$get_country_list = Config::get('constants.apiurl.live.get_country_list');
		}
		//print_r($get_country_list);exit;
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
		//var_dump($result);exit;
		if (!empty($result)) {
			$countryList = json_decode($result);
		}
		//print_r($countryList);exit;
		$countryListArr = array();
		foreach ($countryList->data as $key => $countryitem) {
			$countryListArr[$key]['country_id'] = $countryitem->country_id;
			$countryListArr[$key]['name'] = $countryitem->name;
		}
		usort($countryListArr, function ($item1, $item2) {
			return $item1['name'] <=> $item2['name'];
		});

		return view('diamond.generatediamondinvoice', ['countryList' => $countryListArr, 'productIds' => $productIds, 'shape' => $shape, 'quality' => $quality])->render();
	}
	public function createCustomerByDiamondInvoice($params) {

		$emailAddress = !empty($params['txtemail']) ? $params['txtemail'] : $params['txtdmusercodeemail'];
		$firstName = isset($params['txtfirstname']) ? $params['txtfirstname'] : '';
		$lastName = isset($params['txtlastname']) ? $params['txtlastname'] : '';
		$contactNumber = isset($params['txtcontactnumber']) ? $params['txtcontactnumber'] : '';
		$street = isset($params['txtaddress']) ? $params['txtaddress'] : '';
		$countryId = isset($params['selectcountry']) ? $params['selectcountry'] : '';
		$region = isset($params['txtstateprovince']) ? $params['txtstateprovince'] : '';
		$city = isset($params['txtcity']) ? $params['txtcity'] : '';
		$postcode = isset($params['txtzipcode']) ? $params['txtzipcode'] : '';
		$password = $firstName . rand(10000, 999) . '@dealer';
		$frnCode = isset($params['txtfrncode']) ? $params['txtfrncode'] : '';

		if ($params['customerType'] == 'new') {

			//Check frn code exit
			$isfrnExist = InventoryHelper::checkFRNCodeValidation('', $frnCode);

			if ($isfrnExist) {
				$response['status'] = false;
				$response['message'] = Config::get('constants.message.inventory_frncode_already_exist');
				//var_dump((object) $response);exit;
				return (object) $response;
				//echo json_encode($response);exit;
			}
		}

		if (!empty($frnCode)) {
			$frncodeStr = '&frncode=' . $frnCode;
		} else {
			$frncodeStr = '';
		}

		$customerParams = 'firstname=' . $firstName . '&lastname=' . $lastName . '&email=' . $emailAddress . '&contact_number=' . $contactNumber . '&community=' . '1' . '&street=' . $street . '&country_id=' . $countryId . '&region=' . $region . '&city=' . $city . '&entity_customer=' . '1' . '&postcode=' . $postcode . '&password=' . $password . '&confirmation=' . '1' . '&franchisee_status=' . '2' . $frncodeStr;
		DB::setTablePrefix('');

		if ($params['customerType'] == 'new') {
			if (App::environment('local')) {
				$url = Config::get('app.create_customer');
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
				//echo json_encode($response);exit;
				return (object) $response;
			}
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $customerParams);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$info = curl_getinfo($ch);
			$response = json_decode($result);
			return $response;
		}
	}

	//create new customer for advance payment
	public function createCustomer(Request $request) {
		$params = $request->post();
		//var_dump($params);exit;
		$firstName = isset($params['txtfirstname']) ? $params['txtfirstname'] : '';
		$lastName = isset($params['txtlastname']) ? $params['txtlastname'] : '';
		$contactNumber = isset($params['txtcontactnumber']) ? $params['txtcontactnumber'] : '';
		$address = isset($params['txtaddress']) ? $params['txtaddress'] : '';
		$country = isset($params['selectcountry']) ? $params['selectcountry'] : '';
		$state = isset($params['txtstateprovince']) ? $params['txtstateprovince'] : '';
		$city = isset($params['txtcity']) ? $params['txtcity'] : '';
		$zipCode = isset($params['txtzipcode']) ? $params['txtzipcode'] : '';
		$email = isset($params['txtemail']) ? $params['txtemail'] : '';
		$frnCode = isset($params['txtfrncode']) ? $params['txtfrncode'] : '';
		$password = $firstName . rand(10000, 999) . '@dealer';

		//Check frn code exit
		$isfrnExist = InventoryHelper::checkFRNCodeValidation('', $frnCode);
		if ($isfrnExist) {
			$response['status'] = false;
			$response['message'] = Config::get('constants.message.inventory_frncode_already_exist');
			echo json_encode($response);exit;
			//return ['result' => $response];
		}

		if (!empty($frnCode)) {
			$frncodeStr = '&frncode=' . $frnCode;
		} else {
			$frncodeStr = '';
		}

		$customerParams = 'firstname=' . $firstName . '&lastname=' . $lastName . '&email=' . $email . '&contact_number=' . $contactNumber . '&community=' . '1' . '&street=' . $address . '&country_id=' . $country . '&region=' . $state . '&city=' . $city . '&entity_customer=' . '1' . '&postcode=' . $zipCode . '&password=' . $password . '&confirmation=' . '1' . '&franchisee_status=' . '2' . $frncodeStr;
		if (App::environment('local')) {
			$url = Config::get('app.create_customer');
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

		DB::setTablePrefix('dml_');
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
			$customerId = $res->customer_id;
			$response['status'] = true;
			$response['customer_id'] = $customerId;
			$response['customer_name'] = $firstName . " " . $lastName;
			$response['message'] = Config::get('constants.message.customer_created_successfully');
		} else {
			$response['status'] = false;
			$response['message'] = $res->message;
		}
		echo json_encode($response);exit;
	}

	public function generatediamondinvoicestore(request $request) {

		/*echo "<pre>";
		print_r($request->all());exit;*/
		/*if ($request['customerType'] == 'new') {
				$response = $this->createCustomerByDiamondInvoice($request->post());
				if ($response->status == 'success') {
					$customerIdNew = $response->customer_id;
				} else {
					return redirect('diamond/generatediamondinvoice')->with('error', $response->message);
				}
			} else {
				$customerIdNew = $request->customerId;
		*/

		$customerIdNew = $request->customer_id;
		$issueGotFailed = false;
		$params = $request->post();
		$sortform = $this->getSortNameofShape();
		$sortformquality = $this->getSortNameofQuality();
		$stone_shape = array_filter($request->stone_shape_id);
		$diamond_quality = array_filter($request->diamond_quality_id);
		$diamond_weight = array_filter($request->diamond_weight);
		$sieve_size = (!empty(array_filter($request->sieve_size)) ? array_filter($request->sieve_size) : 0);
		$mm_size = (!empty(array_filter($request->mm_size)) ? array_filter($request->mm_size) : 0);
		$price = array_filter($request->price);
		$count_stone_shape = 1;

		if (is_array($stone_shape) || is_object($stone_shape)) {
			$count_stone_shape = count($stone_shape);
		}
		for ($count = 0; $count < $count_stone_shape; $count++) {

			$abbreviationsort = $sortform[ProductHelper::_toGetDiamondShapeValue($stone_shape[$count])];
			$abbreviationsortquality = $sortformquality[ProductHelper::_toGetDiamondClarityValue($diamond_quality[$count])];

			// $abbreviationsort = $sortform[$stone_shape[$count]];
			// $abbreviationsortquality = $sortformquality[$diamond_quality[$count]];

			if (!empty($mm_size[$count])) {
				$mmsizeintval = filter_var(round($mm_size[$count], 2), FILTER_SANITIZE_NUMBER_INT);
				$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $mmsizeintval;
			} else {
				$sievesizeintval = filter_var(round($sieve_size[$count], 2), FILTER_SANITIZE_NUMBER_INT);
				$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $sievesizeintval;
			}

			$filename = 0;
			$user_id = Auth::user()->id;
			DB::setTablePrefix('dml_');
			$transaction_type = DB::table('transaction_types')->select('id')->where('name', '=', 'Sell')->get();
			//echo $stone_shape[$count];
			//echo "<br/>";
			ProductHelper::_toGetDiamondClarityValue($diamond_quality[$count]);
			//echo $mm_size[$count];

			$data_sieve_size = '';
			if (!empty($sieve_size)) {
				if (!empty($sieve_size[$count])) {
					$data_sieve_size = $sieve_size[$count];
				}
			}

			$data_mm_size = '';
			if (!empty($mm_size)) {
				if (!empty($mm_size[$count])) {
					$data_mm_size = $mm_size[$count];
				}
			}

			$data_diamond_weight = '';
			if (!empty($diamond_weight)) {
				if (!empty($diamond_weight[$count])) {
					$data_diamond_weight = $diamond_weight[$count];
				}
			}

			$data = array(
				'stone_shape' => ProductHelper::_toGetDiamondShapeValue($stone_shape[$count]),
				'diamond_quality' => ProductHelper::_toGetDiamondClarityValue($diamond_quality[$count]),

				//'stone_shape' => $stone_shape[$count],
				//'diamond_quality' => $diamond_quality[$count],

				'diamond_weight' => $data_diamond_weight,
				'sieve_size' => (!empty($data_sieve_size) ? $data_sieve_size : 0),
				'mm_size' => (!empty($data_mm_size) ? $data_mm_size : 0),
				'packet_id' => $packetID,
				'user_id' => $user_id,
				'purchased_at' => date('Y-m-d H:i:s'),
				'transaction_type' => $transaction_type[0]->id,
				'transaction_at' => date('Y-m-d H:i:s'),
				'purchased_invoice' => $filename,

			);
			$insert_data[] = $data;
		}
		$updateResult = $this->UpdateDiamondInventoryByDiamondInvoice($insert_data);
		if ($updateResult['code'] == 'false') {
			$issueGotFailed = true;
			$msgs[] = $updateResult['msg'];
		} else {
			$paid_amounts = $updateResult['data'];
		}

		if (!empty($insert_data)) {
			if ($issueGotFailed) {
				$updatemsg = $msgs[0];
				return redirect('diamond/generatediamondinvoice')->with('error', $updatemsg);
			} else {
				$total_amount_paid = 0;
				$name = $request->txtdmusercodeemail;
				$arr = explode(' ', trim($name));
				$customerName = $arr[0];
				$totalPrice = 0;
				$totaldiscount = 0;
				/*var_dump($price);exit;*/
				for ($i = 0; $i < count($price); $i++) {
					$totalPrice = $totalPrice + ($price[$i] * $diamond_weight[$i]);
					$totaldiscount = $totaldiscount + $request->discount[$i];
				}
				$configData = Setting::where('key', config('constants.settings.keys.diamond_invoice_number'))->first('value');
				$invoice_number = $configData->value;
				$filename = "";
				$customerid = (isset($request->customer_id) ? $request->customer_id : $customerIdNew);
				$this->addtoaccountByDiamondInvoice($invoice_number, $insert_data, $filename, $totalPrice, $request, $customerid, $customerName);
				$AccountlastId = DiamondTransaction::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
				$transaction_id = (int) $AccountlastId->transaction_id;
				$paid_amount = $price;

				/*/ save in transaction /*/
				foreach ($insert_data as $idKey => $TransentryData) {
					$transaction_id++;
					$amount_paid_pre = (float) ($price[$idKey] * $diamond_weight[$idKey]);
					$amount_paid = CommonHelper::getWithoutGSTValue($amount_paid_pre);
					$TransentryData['amount_paid'] = $amount_paid;
					$TransentryData['amount_paid_with_gst'] = ($price[$idKey] * $diamond_weight[$idKey]);
					$TransentryData['transaction_id'] = $transaction_id;
					$TransentryData['invoice_number'] = $invoice_number;
					$TransentryData['rate'] = $price[$idKey];
					$DiamondTransaction[] = DiamondTransaction::create($TransentryData);

				}

				/*/ save in diamond_invoices  /*/
				$diamondInvoice = new DiamondInvoice;
				$diamondInvoice->customer_id = (isset($request->customer_id) ? $request->customer_id : $customerIdNew);
				$diamondInvoice->created_by = Auth::user()->id;
				$diamondInvoice->final_price = $totalPrice;
				$diamondInvoice->discount = $totaldiscount;
				$diamondInvoice->invoice_number = $invoice_number;
				$diamondInvoice->quantity = count($request->price);
				$diamondInvoice->status = "1";
				$diamondInvoice->description = (!empty($request->description) ? $request->description : 'Cut & Polish diamond');
				$countOfDiamond = 1;
				for ($i = 0; $i < count(array_filter($request->price)); $i++) {
					$diamondArrTmp[] = array('stone_shape' => $stone_shape[$i], 'stone_clarity' => $diamond_quality[$i], 'diamond_weight' => $diamond_weight[$i], 'mm_size' => $mm_size[$i], 'sieve_size' => $sieve_size[$i], 'discount' => $request->discount[$i], 'final_price' => $request->price[$i]);
					$countOfDiamond++;
				}
				$diamondArr = json_encode($diamondArrTmp);
				$diamondInvoice->diamond_data = $diamondArr;
				$diamondInvoice->save();
				/*/ create pdf for diamondinvoice /*/
				$invoiceData = $this->diamondinvoicepdf($diamondInvoice, $invoice_number);
				if ($invoiceData['msg'] == "done") {
					$invoiceattachmentColl = DiamondInvoice::where('id', $diamondInvoice->id)->first();
					$invoiceattachmentColl->update(['invoice_attachment' => $invoiceData['name']]);

				}
				$message = "diamond invoice created succesfully.";
				//return redirect('diamond/generatediamondinvoice')->with('success', $message);
				return redirect('diamond/diamondinvoice')->with('success', $message);
			}
		}
	}

	public function diamondinvoicepdf($diamondInvoice, $invoice_number) {

		//echo "<pre>"; print_r($diamondInvoice)

		if (!empty($diamondInvoice)) {
			$diamondColl = json_decode($diamondInvoice->diamond_data);
			$invoiceData = $diamondInvoice;
			//$customPaper = array(0, 0, 740, 1440);
			$customPaper = array(0, 0, 1024, 1440); //720 // 1240
			$pdf = PDF::loadView('diamond.diamondinvoicepdf', compact('invoiceData', 'diamondColl'))->setPaper($customPaper, 'A4');
			$path = public_path('uploads/diamondinvoice/');
			$name = $diamondInvoice->id . '.pdf';
			$pdf->save($path . $name);

			//exit;

			$setting = Setting::where('key', config('constants.settings.keys.diamond_invoice_number'))->first();
			$setting->value = $invoice_number + 1;
			$setting->update();
			return ['msg' => 'done', 'name' => $name];
		} else {
			return ['msg' => 'not done', 'name' => ""];
		}

	}

	public function addtoaccountByDiamondInvoice($invoice_number, $requestData, $filename, $totalPrice, $request, $customerIdNew, $customerName) {

		$insert_data['vendorId'] = (isset($request->customerId) ? $request->customerId : $customerIdNew);
		$insert_data['vendor_name'] = (isset($request->txtfirstname) ? $request->txtfirstname : $customerName);
		$insert_data['payment_form'] = "Incoming";
		$insert_data['invoice_number'] = $invoice_number;

		//echo "<pre>"; print_r($requestData); exit;
		$total_amount_paid = $totalPrice; //$requestData['amount_paid_with_gst'];
		$AccountlastId = Payment::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
		$transaction_id = (int) $AccountlastId->transaction_id;
		$transaction_id++;
		$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;
		$payment_sub_type = PaymentType::select('id')->where('name', '=', 'Purchase')->first()->id;
		$user_id = Auth::User()->id;
		$data = array(
			'transaction_id' => $transaction_id,
			'customer_id' => $insert_data['vendorId'],
			'customer_name' => $insert_data['vendor_name'],
			'invoice_number' => (isset($insert_data['invoice_number']) ? $insert_data['invoice_number'] : 0),
			'invoice_attachment' => $filename,
			'invoice_amount' => $total_amount_paid,
			'due_date' => (isset($requestData['due_date']) ? $requestData['due_date'] : 0),
			'account_status' => '0',
			'payment_status' => '0',
			'payment_form' => (isset($insert_data['payment_form']) ? $insert_data['payment_form'] : 'Outgoing'),
			'payment_type' => $payment_type,
			'payment_sub_type' => $payment_sub_type,
			'customer_type' => "System",
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			'created_by' => $user_id,
			'remarks' => "Purchased from Diamond inventory",
		);

		$Accountinsert = Payment::create($data);

		return $Accountinsert->id;

	}

	public function UpdateDiamondInventoryByDiamondInvoice($requestData) {

		$returndata = array();
		$returndata['code'] = 'false';
		$amount_paid_for_transcation = array();
		$CheckTmp = false;
		$querys = array();
		$queryCounter = 0;
		$CheckTmpCounter = 0;
		$diamondColl = array();
		foreach ($requestData as $key => $DiamondPost) {

			$diamond_weights = $DiamondPost['diamond_weight'];
			$dm_sieve_size = $DiamondPost['sieve_size'];
			$dm_mm_size = $DiamondPost['mm_size'];
			$diamondQuality = $DiamondPost['diamond_quality'];
			$diamondShape = $DiamondPost['stone_shape'];

			$diamondsColl[$diamondQuality] = DB::table('diamond_inventorys')->where(
				'stone_quality', $diamondQuality)->where(
				'stone_shape', $diamondShape)->where(
				'mm_size', $dm_mm_size)->first();
			if (empty(array_filter($diamondsColl))) {
				$diamondsColl = array();
			}
			if (count($diamondsColl) > 0) {
				$diamonds = DB::table('diamond_inventorys')->where(
					'stone_quality', $diamondQuality)->where(
					'stone_shape', $diamondShape)->where(
					'mm_size', $dm_mm_size)->first();
			} else {
				$diamonds = DB::table('diamond_inventorys')->where(
					'stone_quality', $diamondQuality)->where(
					'stone_shape', $diamondShape)->where(
					'sieve_size', $dm_sieve_size)->first();
			}

			$diamondsCount = count($diamonds);
			//this will work in 7.2
			//count((array)$diamonds)
			//count((array)$diamondsColl)*/
			if ($diamondsCount > 0) {

				if (count($diamondsColl) > 0) {
					$matchThese = $this->matchTheseMM($diamondQuality, $diamondShape, $dm_mm_size);
				} else {
					$matchThese = $this->matchTheseSeive($diamondQuality, $diamondShape, $dm_sieve_size);
				}

				$actual_diamond_weight = $diamonds->total_diamond_weight;
				if ($actual_diamond_weight >= $diamond_weights) {

					$calculated_diamond_weight = $actual_diamond_weight - $diamond_weights;
					$CheckTmp = true;
					$CheckTmpCounter++;

				} else {

					$CheckTmp = false;
					$msg = "You can't generate invoice due to diamond weight is more than inventory !";
				}

			} else {
				$CheckTmp = false;
				$msg = "You can't generate invoice due to combination of diamond you don't have in inventory !";

			}

			if ($CheckTmp) {

				if ($diamond_weights == $actual_diamond_weight) {
					//var_dump($diamonds);
					//echo "dfgngfhkjgh";exit;
					$id = $diamonds->id;
					//$querys[] = "DELETE FROM dml_diamond_inventorys WHERE id='" . $id . "' ";
					$amount_paid_for_transcation[] = $diamonds->ave_rate * $diamond_weights;
					$querys[] = "UPDATE dml_diamond_inventorys SET total_diamond_weight = 0 WHERE id='" . $id . "' ";

				} else {

					if (count($diamondsColl) > 0) {
						$amount_paid_for_transcation[] = $diamonds->ave_rate * $diamond_weights;
						$querys[] = "UPDATE dml_diamond_inventorys SET total_diamond_weight = '" . $calculated_diamond_weight . "'  where stone_quality = '" . $DiamondPost['diamond_quality'] . "' AND stone_shape ='" . $DiamondPost['stone_shape'] . "' AND mm_size='" . $DiamondPost['mm_size'] . "' ";
					} else {
						if (!empty($DiamondPost['sieve_size'])) {

							$amount_paid_for_transcation[] = $diamonds->ave_rate * $diamond_weights;
							$diamondsCheckSieve = DB::table('diamond_inventorys')->where(
								'stone_quality', $DiamondPost['diamond_quality'])->where(
								'stone_shape', $DiamondPost['stone_shape'])->where(
								'sieve_size', $DiamondPost['sieve_size'])->first();
							if (count($diamondsCheckSieve) > 0) {
								$querys[] = "UPDATE dml_diamond_inventorys SET total_diamond_weight = '" . $calculated_diamond_weight . "' where stone_quality = '" . $DiamondPost['diamond_quality'] . "' AND stone_shape ='" . $DiamondPost['stone_shape'] . "' AND sieve_size='" . $DiamondPost['sieve_size'] . "' ";
							} else {
								$msg = "You can't generate invoice due to combination of diamond you don't have in inventory !";
							}
						} else {

							$msg = "You can't generate invoice due to combination of diamond you don't have in inventory !";
						}
					}
				}

			}
			$queryCounter++;
		}

		if ($CheckTmpCounter == $queryCounter) {
			foreach ($querys as $query) {
				try {
					DB::unprepared($query);
					DB::commit();
				} catch (\Exception $e) {
					DB::rollback();
				}
			}
		}
		if (isset($msg)) {
			$returndata['msg'] = $msg;
			return $returndata;
		} else {
			$returndata['code'] = 'true';
			$returndata['data'] = $amount_paid_for_transcation;
			return $returndata;
		}
	}

	public function getdiamondprice(request $request) {
		$shape = ProductHelper::_toGetDiamondShapeValue($request->shape);
		$quality = ProductHelper::_toGetDiamondClarityValue($request->quality);
		$mm_size = $request->mm_size;
		$sieve_size = $request->sieve_size;
		$discount = $request->discount;
		$priceTmp = "";
		if (!empty($shape) && !empty($quality)) {

			if (!empty($mm_size)) {
				$priceTmp = DiamondInventory::select('ave_rate')->where('stone_shape', $shape)->where('stone_quality', $quality)->where('mm_size', $mm_size)->pluck('ave_rate')->first();
			} else if (!empty($sieve_size)) {

				$priceTmp = DiamondInventory::select('ave_rate')->where('stone_shape', $shape)->where('stone_quality', $quality)->where('sieve_size', $sieve_size)->pluck('ave_rate')->first();
			} else {
				$priceTmp = "";
			}

		}
		if (empty($priceTmp)) {
			$price = "";
		} else {
			$discountPrice = ($priceTmp * $discount) / 100;
			$price = round($priceTmp - $discountPrice, 2);
		}
		return ['status' => '1', 'data' => $price];
	}

	public function diamondinvoice(request $request) {

		$diamonds = DiamondInvoice::groupBy('invoice_number')->orderBy('created_at', 'DESC')->paginate(10);
		$counter = DiamondInvoice::groupBy('invoice_number')->orderBy('created_at', 'DESC')->get();
		$totalcount = count($counter);
		//$totalcount = DiamondInvoice::orderBy('created_at', 'DESC')->count();
		return view('diamond.diamondinvoice', compact('diamonds', 'diamond_data', 'totalcount'))->with('i', ($request->input('page', 1) - 1) * 10);
	}

	public function diamondinvoicedata(Request $request) {

		$diamonds = DiamondInvoice::where('id', $request->id)->get();
		$returnHTML = view('diamond.showDetail', ['data' => $diamonds])->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));
	}

	public function diamondinvoicelist(Request $request) {

		$columns = array(
			0 => 'invoice_number',
			1 => 'customer_id',
			2 => 'created_at',
			3 => 'final_price',
			4 => 'action');

		//$results = DiamondInvoice::orderBy('created_at', 'DESC');

		$counter = DiamondInvoice::groupBy('invoice_number')->orderBy('created_at', 'DESC')->get();
		$totalData = count($counter);

		//$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		$customername = array();
		$custdata = DiamondInvoice::groupBy('invoice_number')->get();
		foreach ($custdata as $name) {
			$customername[] = InventoryHelper::getCustomerName($name->customer_id);
		}
		$data = array();
		if (empty($request->input('search.value'))) {

			//$diamonds = DiamondInvoice::offset($start)->limit($limit)->groupBy('invoice_number')->orderBy($order, 'DESC')->get();

			$diamonds = DB::select(DB::raw('select invoice.*,concat_ws(" ",custfirst.value,custlast.value) as custname from dml_diamond_invoices as invoice LEFT JOIN customer_entity_varchar as custfirst ON invoice.customer_id=custfirst.entity_id LEFT JOIN customer_entity_varchar as custlast ON invoice.customer_id=custlast.entity_id where custfirst.attribute_id IN (select attribute_id from eav_attribute where attribute_code="firstname") and custlast.attribute_id IN (select attribute_id from eav_attribute where attribute_code="lastname") GROUP BY invoice.invoice_number ORDER BY "created_at","DESC"  LIMIT ' . $limit . ' OFFSET ' . $start . ' '));

		} else {

			$search = $request->input('search.value');
			/*$diamonds = DiamondInvoice::where('invoice_number', 'LIKE', "%{$search}%")->orWhere('customer_id', 'LIKE', "%{$search}%")->orWhere('final_price', 'LIKE', "%{$search}%")->orWhere('created_at', 'LIKE', "%{$search}%")->offset($start)->limit($limit)->orderBy($order, $dir)->get();*/

			$diamonds = DB::select(DB::raw('select invoice.*,concat_ws(" ",custfirst.value,custlast.value) as custname from dml_diamond_invoices as invoice LEFT JOIN customer_entity_varchar as custfirst ON invoice.customer_id=custfirst.entity_id LEFT JOIN customer_entity_varchar as custlast ON invoice.customer_id=custlast.entity_id where custfirst.attribute_id IN (select attribute_id from eav_attribute where attribute_code="firstname") and custlast.attribute_id IN (select attribute_id from eav_attribute where attribute_code="lastname") AND (invoice.invoice_number LIKE "%' . $search . '%" OR concat(custfirst.value, " ", custlast.value) LIKE "%' . $search . '%" OR invoice.final_price LIKE "%' . $search . '%" OR invoice.created_at LIKE "%' . $search . '%") GROUP BY invoice.invoice_number ORDER BY "' . $order . '","' . $dir . '"  LIMIT ' . $limit . ' OFFSET ' . $start . ' '));

			/*$totalFiltered =  DiamondInvoice::where('invoice_number', 'LIKE', "%{$search}%")->orWhere('customer_id', 'LIKE', "%{$search}%")->orWhere('final_price', 'LIKE', "%{$search}%")->orWhere('created_at', 'LIKE', "%{$search}%")->count();*/

			$totalFilteredRes = DB::select(DB::raw('select invoice.*,concat_ws(" ",custfirst.value,custlast.value) as custname from dml_diamond_invoices as invoice LEFT JOIN customer_entity_varchar as custfirst ON invoice.customer_id=custfirst.entity_id LEFT JOIN customer_entity_varchar as custlast ON invoice.customer_id=custlast.entity_id where custfirst.attribute_id IN (select attribute_id from eav_attribute where attribute_code="firstname" AND entity_type_id = 1) and custlast.attribute_id IN (select attribute_id from eav_attribute where entity_type_id = 1 AND attribute_code="lastname") AND (invoice.invoice_number LIKE "%' . $search . '%" OR concat(custfirst.value, " ", custlast.value) LIKE "%' . $search . '%" OR invoice.final_price LIKE "%' . $search . '%" OR invoice.created_at LIKE "%' . $search . '%") GROUP BY invoice.invoice_number'));

			$totalFiltered = count($totalFilteredRes);
		}
		$data = array();
		if (!empty($diamonds)) {
			foreach ($diamonds as $diamond) {
				$customername = InventoryHelper::getCustomerName($diamond->customer_id);

				$action = "<a href='JavaScript:Void(0)'><i title='Detail' onclick='showDetail(" . $diamond->id . ")' class='material-icons list-icon'>info</i></a>";
				$action .= ' <a class="color-content table-action-style" title="Generate Invoice" href="' . route('diamond.diamond_invoice_download', ['id' => $diamond->id]) . '"><i class="material-icons md-18">file_download</i></a>';
				//echo "<pre>";print_r($diamond->created_at->format('Y-m-d H:i:s'));exit;
				$date = $diamond->created_at;
				$data[] = array($diamond->invoice_number, $customername, $date, $diamond->final_price, $action);
			}
		}
		$json_data = array(
			"query" => $start,
			"draw" => intval($request->input('draw')),
			"recordsTotal" => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data" => $data,
		);
		echo json_encode($json_data);
	}

	public function edit_issue_voucher(Request $request) {
		$getArr = $request->all();
		$setArr = array();
		foreach ($getArr as $getArrKey => $getArrValue) {
			$setArr[] = $getArrKey;
		}
		if (!empty($setArr)) {
			$voucher_no = $setArr[0];
			$datas = DiamondTransaction::where('issue_voucher_no', $voucher_no)->get();
			return view('diamond/edit-issue-voucher', compact('datas', 'voucher_no'));
		}
	}

	public function editissuevoucher(Request $request) {

		//var_dump($request->all());
		$response = array();
		$voucher_no = $request['voucher_no'];
		$stone_shape = $request->stone_shape;
		$diamond_weight = $request->diamond_weight;
		$diamond_quality = $request->diamond_quality;
		$sieve_size = $request->sieve_size;
		$rate = $request->existing_rate;
		$mm_size = $request->mm_size;
		$pieces = $request->pieces;
		$counter = count($stone_shape);
		$error_messages = array();
		//var_dump($rate);
		$success_message = false;
		$transactionId = $this->getIdFromTransaction($voucher_no);
		$sortform = $this->getSortNameofShape();
		$sortformquality = $this->getSortNameofQuality();
		$transactionDataArr = array();
		$inventoryDataArr = array();
		$oldInventoryId = $this->getOldRecordFromInventory($voucher_no);

		//var_dump($counter);exit;
		for ($i = 0; $i < $counter; $i++) {

			$inventoryId = $this->checkStoneCombination($stone_shape[$i], $diamond_quality[$i], $mm_size[$i], $sieve_size[$i]);
			if (!empty($inventoryId)) {

				$transData = $this->getDiamondTransDataFromTransactionId($transactionId[$i]);

				//var_dump($transData);exit;
				//var_dump($i);
				$trans_diamond_weight = $transData[0];

				if ($diamond_weight[$i] > $trans_diamond_weight) {
					$isAvailable = $this->checkStoneIsAvailable($inventoryId, $diamond_weight[$i]);
				} else {
					$isAvailable = true;
				}

				if ($isAvailable) {

					$abbreviationsort = $sortform[$stone_shape[$i]];
					$abbreviationsortquality = $sortformquality[$diamond_quality[$i]];

					if (!empty($mm_size[$i])) {
						$mmsizeintval = filter_var(round($mm_size[$i], 2), FILTER_SANITIZE_NUMBER_INT);
						$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $mmsizeintval;
					} else {
						$sievesizeintval = filter_var(round($sieve_size[$i], 2), FILTER_SANITIZE_NUMBER_INT);
						$packetID = $abbreviationsort . '-' . $abbreviationsortquality . '-' . $sievesizeintval;
					}

					$getArr = array('stone_shape' => $stone_shape[$i], 'diamond_quality' => $diamond_quality[$i], 'diamond_weight' => $diamond_weight[$i], 'sieve_size' => $sieve_size[$i], 'mm_size' => $mm_size[$i], 'pieces' => $pieces[$i], 'packet_id' => $packetID, 'rate' => $rate[$i]);
					$inventoryDataArr[$i]['inventoryId'] = $inventoryId;
					$inventoryDataArr[$i]['oldInventoryId'] = $oldInventoryId[$i];
					$inventoryDataArr[$i]['transactionId'] = $transactionId[$i];
					$inventoryDataArr[$i]['combinationData'] = $getArr;
					$success_message = true;
				} else {
					$error_messages[] = Config::get('constants.message.Diamond_weight_more');
					$success_message = false;
				}
			} else {
				$error_messages[] = Config::get('constants.message.Diamond_not_exists');
				$success_message = false;
			}
		}

		if ($success_message && empty($error_messages)) {

			$newWeightArr = $this->updateInventoryAndTransaction($inventoryDataArr);
			//Start update Issue voucher pdf

			$name = $this->updateDiamondIssueVoucherPDF($inventoryDataArr);
			//End update Issue voucher pdf

			$msg = config('constants.message.issue_voucher_edit_success');
			$msg .= "<br/>" . ' Click on link to view <a target="_blank" href="' . url('uploads/issuevaucher/' . $name) . '">Issue Voucher</a>';
			$request->session()->flash("success", $msg);
			return redirect('diamond/edit_issue_voucher?' . $voucher_no);

		} else {
			$message = implode('<br>', $error_messages);
			return redirect('diamond/edit_issue_voucher?' . $voucher_no)->with('error', $message);
		}
	}

	public function updateDiamondIssueVoucherPDF($inventoryDataArr) {

		$update_voucher = array();
		$paid_amounts = array();
		$transaction_data = array();
		$oldvoucher_no = array();
		foreach ($inventoryDataArr as $key => $inventoryData) {
			$update_voucher[] = DiamondTransaction::find($inventoryData['transactionId']);
			$file = public_path('uploads/issuevaucher/' . $update_voucher[$key]->issue_vaucher);
			if (!empty($update_voucher[$key]->issue_vaucher)) {
				if (File::exists($file)) {

					unlink($file);

				}
			}
			$update_voucher[$key]->amount_paid = $inventoryData['combinationData']['rate'] * $inventoryData['combinationData']['diamond_weight'];
			$update_voucher[$key]->issue_vaucher = NULL;
			$update_voucher[$key]->update();

			$paid_amounts[] = $update_voucher[$key]['amount_paid'];

		}
		//print_r($update_voucher);exit;
		foreach ($update_voucher as $key => $value) {
			$DiamondIds[$key] = $value->id;
		}
		$customPaper = array(0, 0, 1024, 1440);
		$data = ['data' => $update_voucher];
		$dt = new DateTime($data['data'][0]->purchased_at);
		$date = $dt->format('Y-m-d');

		$getname = User::select('name', 'gstin', 'state', 'address')->where('id', $data['data'][0]->vendor_id)->get();

		$name = $getname[0]->name;
		$gstin = $getname[0]->gstin;
		$state = $getname[0]->state;
		$address = $getname[0]->address;
		$issue_voucher_no = $update_voucher[0]->issue_voucher_no;

		$pdf = PDF::loadView('diamond.issuevaucher', compact('data', 'name', 'address', 'gstin', 'state', 'issue_voucher_no', 'date'))->setPaper($customPaper, 'A4');
		$path = public_path('uploads/issuevaucher/');
		$name = 'diamondisse_' . time() . '.pdf';
		$pdf->save($path . $name);

		foreach ($DiamondIds as $id) {

			$diamond = DiamondTransaction::find($id);
			$diamond->issue_vaucher = $name;
			$diamond->update();
		}
		return $name;
	}

	public function getIdFromTransaction($voucher_no) {
		$tranColls = DiamondTransaction::where('issue_voucher_no', $voucher_no)->select('id')->pluck('id');
		return $tranColls;
	}

	public function getDiamondTransDataFromTransactionId($trans_id) {
		$tranColls = DiamondTransaction::where('id', $trans_id)->select('diamond_weight')->pluck('diamond_weight')->toArray();
		return $tranColls;
	}

	public function checkStoneIsAvailable($inventoryId, $diamond_weight) {
		$diamondOldWgt = DiamondInventory::where('id', $inventoryId)->pluck('total_diamond_weight')->first();
		if ($diamondOldWgt > $diamond_weight) {
			return true;
		} else if ($diamondOldWgt == $diamond_weight) {
			return true;
		} else {
			return false;
		}
	}

	public function updateInventoryAndTransaction($inventoryDataArr) {

		$updated = false;
		$inventoryArr = array();
		foreach ($inventoryDataArr as $key => $inventoryData) {

			$inventoryId = $inventoryData['inventoryId'];
			$oldInventoryId = $inventoryData['oldInventoryId'];
			$transactionId = $inventoryData['transactionId'];
			$diamond_weight = (float) $inventoryData['combinationData']['diamond_weight'];
			$transactionWt = 0;
			$transactionWt = (float) DiamondTransaction::where('id', $transactionId)->pluck('diamond_weight')->first();
			$diamondCurWgt = 0;
			$diamondCurWgt = DiamondInventory::where('id', $inventoryId)->pluck('total_diamond_weight')->first();
			$test = DiamondInventory::where('id', $inventoryId)->pluck('total_diamond_weight')->first();
			if ($oldInventoryId != $inventoryId) {
				sleep(2);

				$diamondOldInveWgt = DiamondInventory::where('id', $oldInventoryId)->pluck('total_diamond_weight')->first();

				$diamondNewInveWgt = DiamondInventory::where('id', $inventoryId)->pluck('total_diamond_weight')->first();
				$newTotalDiamondWeight = $diamondNewInveWgt - $transactionWt;
				$oldTotalDiamondWeight = $diamondOldInveWgt + $diamond_weight;
				$oldupdated = DiamondInventory::where('id', $oldInventoryId)->update(['total_diamond_weight' => $oldTotalDiamondWeight]);
				$updated = DiamondInventory::where('id', $inventoryId)->update(['total_diamond_weight' => $newTotalDiamondWeight]);

			} else if ($transactionWt != $diamond_weight) {
				$differenceWeight = 0;
				$differenceWeight = (float) $transactionWt - (float) $diamond_weight;
				sleep(2);

				if ($differenceWeight < 0) {

					$abs_differenceWeight = (float) abs($differenceWeight);
					$newTotalDiamondWeight = $diamondCurWgt - $abs_differenceWeight;
					$updated = DiamondInventory::where('id', $inventoryId)->update(['total_diamond_weight' => $newTotalDiamondWeight]);

				} else {
					$abs_differenceWeight = (float) abs($differenceWeight);
					$newTotalDiamondWeight = $diamondCurWgt + $abs_differenceWeight;
					$updated = DiamondInventory::where('id', $inventoryId)->update(['total_diamond_weight' => $newTotalDiamondWeight]);
				}
			}

			$updateOrder = DiamondTransaction::find($transactionId)->update($inventoryData['combinationData']);
		}
		return $inventoryArr;
	}

	public function getOldRecordFromInventory($voucher_no) {
		$tranColls = DiamondTransaction::where('issue_voucher_no', $voucher_no)->get();
		foreach ($tranColls as $key => $tranColl) {
			$prev_stone_shape = $tranColl->stone_shape;
			$prev_diamond_weight = $tranColl->diamond_weight;
			$prev_diamond_quality = $tranColl->diamond_quality;
			$prev_sieve_size = $tranColl->sieve_size;
			$prev_mm_size = $tranColl->mm_size;
			$oldInventoryId[] = $this->checkStoneCombination($prev_stone_shape, $prev_diamond_quality, $prev_mm_size, $prev_sieve_size);
		}
		return $oldInventoryId;
	}

	public function checkStoneCombination($stone_shape, $diamond_quality, $mm_size, $sieve_size) {
		if ($mm_size != "") {

			$matchTheseMM = [
				'stone_quality' => $diamond_quality,
				'stone_shape' => $stone_shape,
				'mm_size' => $mm_size,
			];

			$diamondMaster = DiamondInventory::where($matchTheseMM);
			$diamondMasterCount = $diamondMaster->count();
			$diamondMasterRecord = $diamondMaster->first();

			if ($diamondMasterCount > 0) {
				$id = $diamondMasterRecord->id;
				return $id;
			}
		} else {

			$matchTheseSieve = [
				'stone_quality' => $diamond_quality,
				'stone_shape' => $stone_shape,
				'sieve_size' => $sieve_size,
			];

			$diamondMaster = DiamondInventory::where($matchTheseSieve);
			$diamondMasterCount = $diamondMaster->count();
			$diamondMasterRecord = $diamondMaster->first();

			if ($diamondMasterCount > 0) {
				$id = $diamondMasterRecord->id;
				return $id;
			}
		}
	}

	public function returnDiamondIssue(request $request) {
		$voucher_no = $request->id;
		$datas = DiamondTransaction::where('issue_voucher_no', $voucher_no)->get();
		$returnHTML = view('diamond.returnDiamondIssue', ['voucher_no' => $voucher_no, 'datas' => $datas])->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));
	}

	public function returndiamondIssueStore(request $request) {
		$returnWtArr = $request->return_weight[0];
		$voucher_no = $request->voucher_no;
		$transactionIds = array_keys($returnWtArr);
		$isUpdateSuccess = false;
		$diamond_weight_error = array();
		$diamond_weight_success = array();
		foreach ($transactionIds as $key => $transactionId) {
			$transactionColls = DiamondTransaction::where('id', $transactionId)->first();
			$stone_shape = $transactionColls['stone_shape'];
			$diamond_quality = $transactionColls['diamond_quality'];
			$mm_size = $transactionColls['mm_size'];
			$sieve_size = $transactionColls['sieve_size'];
			$packet_id = $transactionColls['packet_id'];
			$pieces = $transactionColls['pieces'];
			$sieve_size = $transactionColls['sieve_size'];
			$vendor_id = $transactionColls['vendor_id'];
			$typeName = config('constants.enum.transaction_types.return');
			$transactionColl = TransactionType::where('name', $typeName)->first();
			$transaction_type = $transactionColl['id'];
			$transaction_at = date('Y-m-d H:i:s');
			$user_id = Auth::user()->id;
			$created_at = date('Y-m-d H:i:s');
			$purchased_at = date('Y-m-d H:i:s');
			$rate = $transactionColls['rate'];
			$issue_voucher_no = $transactionColls['issue_voucher_no'];
			$amount_paid = $transactionColls['amount_paid'];
			$amount_paid_with_gst = $transactionColls['amount_paid_with_gst'];
			$purchased_invoice = $transactionColls['purchased_invoice'];
			if ($transactionColls['diamond_weight'] >= $returnWtArr[$transactionId]) {

				//save in diamond transaction for return
				$setArr = array('packet_id' => $packet_id, 'stone_shape' => $stone_shape, 'diamond_weight' => $returnWtArr[$transactionId], 'sieve_size' => $sieve_size, 'mm_size' => $mm_size, 'pieces' => $pieces, 'transaction_type' => $transaction_type, 'transaction_at' => $transaction_at, 'user_id' => $user_id, 'created_at' => $created_at, 'vendor_id' => $vendor_id, 'diamond_quality' => $diamond_quality, 'purchased_invoice' => $purchased_invoice, 'purchased_at' => $purchased_at, 'amount_paid' => $amount_paid, 'amount_paid_with_gst' => $amount_paid_with_gst, 'rate' => $rate, 'issue_voucher_no' => $issue_voucher_no);
				DiamondTransaction::create($setArr);

				//update diamond transaction issue
				$transactionCurrWt = $transactionColls['diamond_weight'];
				$tranUpdatedWt = $transactionCurrWt - $returnWtArr[$transactionId];
				$transactionColls->update(['diamond_weight' => $tranUpdatedWt]);

				//update diamond inventory
				$inventoryId = $this->checkStoneCombination($stone_shape, $diamond_quality, $mm_size, $sieve_size);
				$inventoryWt = DiamondInventory::where('id', $inventoryId)->select('total_diamond_weight')->pluck('total_diamond_weight')->first();
				$newWt = $inventoryWt + $returnWtArr[$transactionId];
				DiamondInventory::where('id', $inventoryId)->update(['total_diamond_weight' => $newWt]);
				$isUpdateSuccess = true;
				$diamond_weight_success[] = $returnWtArr[$transactionId];
			} else {
				$diamond_weight_error[] = $returnWtArr[$transactionId];
				$isUpdateSuccess = false;
			}
		}
		if ($isUpdateSuccess) {
			$response['status'] = "true";
			/*config(['constants.message.returned_success' => rtrim(implode(",", $diamond_weight_success)) . config('constants.message.returned_success')]);
			$response['message'] = config('constants.message.returned_success');*/

			$message = "Weight (" . implode(",", $diamond_weight_success) . ") returned succesfully";
			$response['message'] = $message;

		} else {
			$response['status'] = "false";
			//config(['constants.message.weight_more' => implode(",", $diamond_weight_error) . config('constants.message.weight_more')]);

			$message = "Weight (" . implode(",", $diamond_weight_error) . ") is more than Issue";
			$response['message'] = $message;
		}
		echo json_encode($response);exit;
	}

	public function diamondIssueCheck(Request $request) {
		if (empty($request->sieve_size)) {
			$checkDiamonIssue = DiamondInventory::select('total_diamond_weight')->where('stone_quality', $request->quality)->where('stone_shape', $request->shape)->where('mm_size', $request->mm_size)->pluck('total_diamond_weight')->first();
		}
		if (empty($request->mm_size)) {
			$checkDiamonIssue = DiamondInventory::select('total_diamond_weight')->where('stone_quality', $request->quality)->where('stone_shape', $request->shape)->where('sieve_size', $request->sieve_size)->pluck('total_diamond_weight')->first();
		}

		if (!empty($request->sieve_size) && !empty($request->mm_size)) {
			$checkDiamonIssue = DiamondInventory::select('total_diamond_weight')->where('stone_quality', $request->quality)->where('stone_shape', $request->shape)->where('sieve_size', $request->sieve_size)->where('mm_size', $request->mm_size)->pluck('total_diamond_weight')->first();
		}

		if (!empty($checkDiamonIssue)) {
			return response()->json(array('success' => true, 'result' => $checkDiamonIssue));
		} else {
			$message = Config::get('constants.message.diamond_cant_issue');
			return response()->json(array('error' => false, 'data' => $message));
		}
	}

	public function diamondstatisticsbymm(Request $request) {
		$diamond = DiamondInventory::orderBy('created_at', 'DESC')->paginate(10);
		$datacount = DiamondInventory::orderBy('created_at', 'DESC')->count();
		$stone_shape = DiamondInventory::select('stone_shape')->distinct()->pluck('stone_shape');
		$cntForMmSize = DiamondInventory::select('mm_size')->orderBy('created_at', 'DESC')->where('mm_size', '<=', DB::raw('1.25'))->count();
		$cntForMmSizeStar = DiamondInventory::select('mm_size')->orderBy('created_at', 'DESC')->where('mm_size', '>=', DB::raw('1.25'))->where('mm_size', '<=', DB::raw('1.80'))->count();
		$cntForMmSizeMelle = DiamondInventory::select('mm_size')->orderBy('created_at', 'DESC')->where('mm_size', '>=', DB::raw('1.80'))->where('mm_size', '<=', DB::raw('2.70'))->count();
		$cntForMmSizeMagic = DiamondInventory::select('mm_size')->orderBy('created_at', 'DESC')->where('mm_size', '>', DB::raw('2.70'))->count();
		//echo "<pre>";print_r($cntForMmSize);exit;
		$min_price = $diamond->pluck(['mm_size'])->min();
		$max_price = $diamond->pluck(['mm_size'])->max();

		$min_size = $diamond->pluck(['sieve_size'])->min();
		$max_size = $diamond->pluck(['sieve_size'])->max();

		$stone_clarity = DiamondInventory::select('stone_quality')->distinct()->pluck('stone_quality');
		return view('diamond.diamond_statistics_by_mm', compact('diamond', 'amountPaids', 'stone_shape', 'stone_clarity', 'datacount', 'cntForMmSize', 'cntForMmSizeStar', 'cntForMmSizeMelle', 'cntForMmSizeMagic', 'min_price', 'max_price', 'min_size', 'max_size'))->with('i', ($request->input('page', 1) - 1) * 10);
	}

	public function filter_diamond_statistics(Request $request) {

		$columns = array(

			0 => 'id',
			1 => 'packet_id',
			2 => 'stone_quality',
			3 => 'stone_shape',
			4 => 'total_diamond_weight',
			5 => 'ave_rate',
			6 => 'mm_size',
			7 => 'sieve_size');

		$maindata = DiamondInventory::orderBy('id', 'DESC');
		//echo "<pre>";print_r($request->input('mmsizemin'));exit;

		$minsize = $request->input('mmsizemin');
		$maxsize = $request->input('mmsizemax');

		$seiveminsize = $request->input('seivesizemin');
		$seivemaxsize = $request->input('seivesizemax');
		$totalData = $maindata->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		//print_r($order);exit;
		$data = array();
		$params = $request->post();

		$maindata = DiamondInventory::orderBy($order, $dir);

		if (!empty($request['textfilter'])) {
			$maindata = $maindata->where('stone_shape', $request['textfilter']);
			$cnt = $maindata->count();
		}

		if (!empty($request['textfilterquality'])) {

			$maindata = $maindata->where('stone_quality', $request['textfilterquality']);
		}

		if (!empty($minsize) && !empty($maxsize)) {
			//$maindata = $maindata->where('mm_size', 'like', $request['mmsize']);
			$maindata = $maindata->where('mm_size', '>=', DB::raw("$minsize"))->where('mm_size', '<=', DB::raw("$maxsize"));
		}

		if (!empty($seiveminsize) && !empty($seivemaxsize)) {
			//$maindata = $maindata->where('mm_size', 'like', $request['mmsize']);
			$maindata = $maindata->where('sieve_size', '>=', DB::raw("$seiveminsize"))->where('sieve_size', '<=', DB::raw("$seivemaxsize"));
		}

		/* if (!empty($request['sivesize'])) {
			$maindata = $maindata->where('sieve_size', $request['sivesize']);
		} */

		$datacount = $maindata->count();
		$datacoll = $maindata->offset($start)->limit($limit)->orderBy($order, $dir)->get();
		$datacollection = $datacoll;

		/* $cntForMmSize = $datacollection->where('mm_size', '<=', DB::raw('1.25'))->count();
			$cntForMmSizeStar = $datacollection->where('mm_size', '>=', DB::raw('1.25'))->where('mm_size', '<=', DB::raw('1.80'))->count();
			$cntForMmSizeMelle = $datacollection->where('mm_size', '>=', DB::raw('1.80'))->where('mm_size', '<=', DB::raw('2.70'))->count();
		*/

		$data["draw"] = intval($request->input('draw'));
		$data["recordsTotal"] = $datacount;
		$data["recordsFiltered"] = $datacount;
		$data['deferLoading'] = $datacount;
		/* $data['cntmmsize'] = $cntForMmSize;
			$data['cntmmsizestar'] = $cntForMmSizeStar;
			$data['cntmmsizemelle'] = $cntForMmSizeMelle;
		*/

		if (count($datacollection) > 0) {
			foreach ($datacollection as $key => $diamond) {

				$srno = $key + 1 + $start;

				//$actions = 'sdfjn dfgkj fgk df';
				/* $actions = '   <a class="color-content table-action-style" href="' . route('diamond.transactions', $diamond->id) . '"><i class="material-icons md-18">remove_red_eye</i></a>';
				$actions .= '  <a title="Update weight" class="color-content table-action-style" href="' . route('diamond.diamondmiscloss', $diamond->id) . '"><i class="material-icons md-18">remove_from_queue</i></a>'; */

				$data['data'][] = array($srno, $diamond->packet_id, $diamond->stone_quality, $diamond->stone_shape, $diamond->total_diamond_weight, '&#x20b9;' . ' ' . $diamond->ave_rate, $diamond->mm_size, $diamond->sieve_size);
			}
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;

	}

	public function voucherPreview(request $request) {

		//echo "<pre>"; print_r($request->all()); exit;
		$count_stone_shape = count($request->stone_shape);
		$inputData = array();
		$avg_rate = array();
		$stone_shape = $request->stone_shape;
		$diamond_quality = $request->diamond_quality;
		$diamond_weight = $request->diamond_weight;
		$sieve_size = $request->sieve_size;
		$mm_size = $request->mm_size;
		$pieces = $request->pieces;
		$user_id = Auth::user()->id;
		$custom_mm_size = $request->custom_mm_size;
		$custom_sieve_size = $request->custom_sieve_size;
		$is_adjustable = $request->custom_chk;
		$custom_stone_quality = $request->custom_diamond_quality;
		$rate = $request->rate;
		$custom_rate = $request->custom_rate;
		$existing_rate = $request->existing_rate;

		for ($count = 0; $count < $count_stone_shape; $count++) {

			$customCounter = 0;
			$existingCounter = 0;
			if ($rate[$count] == 'Custom') {
				if (isset($custom_rate[$customCounter])) {
					$avg_rate[] = $custom_rate[$customCounter];
					$customCounter++;
				}
			}

			if ($rate[$count] == 'Existing') {
				if (isset($existing_rate[$existingCounter])) {
					$avg_rate[] = $existing_rate[$existingCounter];
					$existingCounter++;
				}
			}

			$data = array(
				'stone_shape' => $stone_shape[$count],
				'diamond_quality' => $diamond_quality[$count],
				'diamond_weight' => $diamond_weight[$count],
				'sieve_size' => $sieve_size[$count],
				'mm_size' => $mm_size[$count],
				'pieces' => $pieces[$count],
				'user_id' => $user_id,
				'purchased_at' => date('Y-m-d H:i:s'),
				'transaction_at' => date('Y-m-d H:i:s'),
				'vendor_id' => $request->input('vendorId'),
				'po_number' => $request->input('po_number'),
				'comment' => $request->input('comment'),
				'custom_mm_size' => $custom_mm_size[$count],
				'custom_sieve_size' => $custom_sieve_size[$count],
				'custom_stone_quality' => $custom_stone_quality[$count],
				'is_adjustable' => $is_adjustable[$count],
				'rate' => (isset($avg_rate[$count]) ? $avg_rate[$count] : ''),
			);

			$inputData[] = $data;
		}
		//echo "<pre>"; print_r($inputData);exit;
		$customPaper = array(0, 0, 1024, 1440);
		$data = ['data' => $inputData];
		$dt = new DateTime($inputData[0]['purchased_at']);
		$date = $dt->format('Y-m-d');

		$getname = User::select('name', 'gstin', 'state', 'address')->where('id', $inputData[0]['vendor_id'])->get();
		$name = (!empty($getname[0]->name) ? $getname[0]->name : "");
		$address = (!empty($getname[0]->address) ? $getname[0]->address : "");
		$gstin = (!empty($getname[0]->gstin) ? $getname[0]->gstin : "");
		$state = (!empty($getname[0]->state) ? $getname[0]->state : "");

		$returnHTML = view('diamond.voucherPreview')->with(compact('data', 'name', 'address', 'gstin', 'state', 'date'))->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));
	}

	public function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function generateVoucherno(request $request) {

		$issue_voucher_no[] = $request->id;
		//echo  "<pre>"; print_r($issue_voucher_no);
		if (!empty($issue_voucher_no)) {
			$ids = array();
			$currentIssueVoucherNo = DiamondHelper::getCurrentIssueVoucherNo();
			$voucherdetail = DiamondHelper::isIssueVoucherNoExist($currentIssueVoucherNo);
			if ($voucherdetail == true) {
				$is_generated = false;
				$transactionIds = DiamondTransaction::whereIn('issue_voucher_no', $issue_voucher_no)->get();
				foreach ($transactionIds as $key => $idVal) {
					$id = $idVal->id;
					$diamond = DiamondTransaction::find($id);
					if ($diamond->is_voucher_no_generated == 0) {
						$diamond->issue_voucher_no = $currentIssueVoucherNo;
						$diamond->is_voucher_no_generated = "1";
						$diamond->update();
						$is_generated = true;
						$inventoryDataArr[$key]['transactionId'] = $id;
						$inventoryDataArr[$key]['combinationData']['diamond_weight'] = $diamond->diamond_weight;
						$inventoryDataArr[$key]['combinationData']['rate'] = $diamond->rate;
					}
				}
				if ($is_generated) {
					$search_voucher = Setting::where('key', config('constants.settings.keys.diamond_voucher_series'))->first()->value;
					$new_voucher = (int) $search_voucher + 1;
					$nid = Setting::select('id')->where('key', config('constants.settings.keys.diamond_voucher_series'))->get();
					$setting = Setting::find($nid[0]->id);
					$setting->value = $new_voucher;
					$setting->update();
					$this->updateDiamondIssueVoucherPDF($inventoryDataArr);
					return response()->json(array('status' => "true", 'message' => config('constants.message.voucherno_generated_sucess')));
				} else {

					return response()->json(array('status' => "false", 'message' => config('constants.message.voucherno_generated_failed')));
				}

			} else {
				return response()->json(array('status' => "false", 'message' => config('constants.message.voucherno_generated_failed')));
			}
		}
	}

	public function handover(Request $request) {
		$issue_voucher_no[] = $request->id;

		if (!empty($issue_voucher_no)) {
			$transactionColl = DiamondTransaction::whereIn('issue_voucher_no', $issue_voucher_no)->get();
			foreach ($transactionColl as $key => $idVal) {
				$id = $idVal->id;
				$diamond = DiamondTransaction::find($id);
				if ($diamond->is_handover == 0) {
					$diamond->is_handover = "1";
					$diamond->handover_at = date('Y-m-d H:i:s');
					$diamond->update();
				}
			}
			return response()->json(array('status' => "true", 'message' => config('constants.message.handover_sucess')));
		} else {
			return response()->json(array('status' => "false", 'message' => config('constants.message.handover_failed')));
		}
	}

	function deleteVoucher(Request $request) {
		$issue_voucher_no[] = $request->id;
		if (!empty($issue_voucher_no)) {
			$is_success = false;
			$transactionColl = DiamondTransaction::whereIn('issue_voucher_no', $issue_voucher_no)->get();
			$fnlWt = 0;
			$inventoryCurrWt = 0;
			foreach ($transactionColl as $key => $idVal) {
				$id = $idVal->id;
				$sieve_size = $idVal->sieve_size;
				$mm_size = $idVal->mm_size;
				$sieve_size = $idVal->sieve_size;
				$stone_shape = $idVal->stone_shape;
				$diamond_quality = $idVal->diamond_quality;
				$transactionWt = $idVal->diamond_weight;
				$inventoryId = $this->checkStoneCombination($stone_shape, $diamond_quality, $mm_size, $sieve_size);
				$inventoryCurrWt = DiamondInventory::where('id', $inventoryId)->select('total_diamond_weight')->pluck('total_diamond_weight')->first();
				$fnlWt = $inventoryCurrWt + $transactionWt;
				$diamond = DiamondInventory::find($inventoryId);
				$diamond->total_diamond_weight = $fnlWt;
				$diamond->update();
				DiamondTransaction::find($id)->delete();
				$is_success = true;

			}

			if ($is_success) {
				return response()->json(array('status' => "true", 'message' => config('constants.message.delete_sucess')));
			} else {
				return response()->json(array('status' => "false", 'message' => config('constants.message.delete_failed')));
			}
		}
	}

	public function mmToSeive(Request $request) {
		$sieve_size = MmToSieve::select('sieve_size')->where('mm_size', $request->Mm_size)->value('sieve_size');

		if ($sieve_size == "") {
			return ['status' => 'error', 'data' => $sieve_size];
		} else {
			return ['status' => 'success', 'data' => $sieve_size];
		}

	}
	public function seiveToMm(Request $request) {
		//print_r($request->all());exit;
		$mm_size = MmToSieve::select('mm_size')->where('sieve_size', $request->Sieve_size)->value('mm_size');

		if ($mm_size == "") {
			return ['status' => 'error', 'data' => $mm_size];
		} else {
			return ['status' => 'success', 'data' => $mm_size];
		}
	}
}
