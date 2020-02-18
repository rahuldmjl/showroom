<?php

namespace App\Http\Controllers;

use App\DiamondRaw;
use App\DiamondTransaction;
use App\MetalTransaction;
use App\Setting;
use App\User;
use Config;
use DB;
use Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use URL;

class DiamondRawController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		$diamondraw = DiamondRaw::where('cvd_status', '0')->orderBy('id', 'desc')->paginate(10);
		$totalcount = DiamondRaw::where('cvd_status', '0')->orderBy('id', 'desc')->count();
		return view('diamondraw/index', compact('diamondraw', 'totalcount'));
	}

	public function cvd_list(Request $request) {
		$diamondraw = DiamondRaw::where('cvd_status', '1')->where('assorting_status', '0')->orderBy('updated_at', 'desc')->paginate(10);
		$totalcount = DiamondRaw::where('cvd_status', '1')->where('assorting_status', '0')->orderBy('updated_at', 'desc')->count();
		return view('diamondraw/cvd-list', compact('diamondraw', 'totalcount'));
	}

	public function assorting_list(Request $request) {

		$diamondraw = DiamondRaw::where('assorting_status', '1')->where('sizing_status', '0')->orWhere('sizing_status', '1')->orderBy('updated_at', 'desc')->paginate(10);

		$setting_data = Setting::select('key', 'value')->where('key', '=', "DIAMOND_LOSS_TOLERENCE_LIMIT")->get();

		$totalcount = DiamondRaw::where('assorting_status', '1')->where('sizing_status', '0')->orWhere('sizing_status', '1')->orderBy('updated_at', 'desc')->count();

		return view('diamondraw/assortinglist', compact('diamondraw', 'totalcount', 'setting_data'));
	}

	public function sizing_list(Request $request) {

		$diamonddata = DiamondRaw::where('sizing_status', '1')->orderBy('return_date', 'desc')->paginate();

		$diamond = DiamondRaw::where('id', $diamonddata[0]->id)->get();

		$diamondraw = DiamondRaw::findOrFail($diamond[0]->id);

		$total_weight = $diamondraw->cvd_rejected + $diamondraw->assorting_rejected + $diamondraw->sizing_rejected;

		$total_loss = $diamondraw->cvd_loss + $diamondraw->assorting_loss + $diamondraw->sizing_loss;

		$diamondraw->total_rejected_weight = $total_weight;
		$diamondraw->total_loss = $total_loss;
		$diamondraw->update();

		$diamondraw = DiamondRaw::where('sizing_status', '1')->orderBy('return_date', 'desc')->paginate();

		$totalcount = count($diamondraw);

		return view('diamondraw/sizinglist', compact('diamondraw', 'totalcount'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {

		$length = 10;
		$str = "";
		$char = "_";
		$characters = array_merge(range('A', 'Z'), range('0', '9'));
		$max = count($characters) - 1;
		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		$pos = 5;
		$add = '_';
		$begin = substr($str, 0, $pos);
		$end = substr($str, $pos);
		$new = $begin . $add . $end;

		return view('diamondraw/create', compact('new'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$input = $request->all();

		$this->validate($request, [
			'packet_name' => 'required|unique:raw_diamonds',
			'total_weight' => 'required',
			'vendor_name' => 'required',

		]);

		DiamondRaw::create($input);
		return redirect()->route('diamondraw.index')->with('success', Config::get('constants.message.DiamondRaw_Add'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\@parth choube: you have to modify in add raw inventory form Http\Response
	 */
	public function show($id) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		//
	}

	public function CVD(Request $request) {
		$data = DiamondRaw::where('id', $request->id)->get();
		$setting_data = Setting::select('key', 'value')->where('key', '=', "DIAMOND_LOSS_TOLERENCE_LIMIT")->get();

		$returnHTML = view('diamondraw.showDetail', ['data' => $data, 'setting_data' => $setting_data])->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));
	}

	public function cvd_transaction(Request $request) {

		$diamondraw = DiamondRaw::findOrFail($request->id);

		$diamondraw->cvd_weight = $request->input('total_weight');
		$diamondraw->cvd_rejected = $request->input('rejected');
		$diamondraw->cvd_loss = $request->input('cvd_loss');
		$diamondraw->cvd_loss_reason = $request->input('cvd_loss_reason');
		$diamondraw->total_rejected_weight = $request->input('rejected');
		$diamondraw->total_loss = $request->input('cvd_loss');
		$diamondraw->cvd_status = 1;
		$diamondraw->update();
		return redirect()->route('diamondraw.cvd-list')->with('success', Config::get('constants.message.DiamondRaw_CVD'));
	}

	public function assorting(Request $request) {
		$data = DiamondRaw::where('id', $request->id)->get();

		$setting_data = Setting::select('key', 'value')->where('key', '=', "DIAMOND_LOSS_TOLERENCE_LIMIT")->get();

		if (!empty($data[0]->cvd_loss)) {
			$loss = $data[0]->cvd_loss;
		} else {
			$loss = 0;
		}

		$returnHTML = view('diamondraw.assorting', ['data' => $data, 'setting_data' => $setting_data, 'loss' => $loss])->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));
	}

	public function assorting_transaction(Request $request) {
		$diamondraw = DiamondRaw::findOrFail($request->id);

		$diamondraw->assorting_weight = $request->input('assorting_weight');
		$diamondraw->assorting_rejected = $request->input('assorting_rejected');
		$diamondraw->assorting_loss = $request->input('assorting_loss');
		$diamondraw->assorting_loss_reason = $request->input('assorting_loss_reason');
		$diamondraw->total_rejected_weight = $diamondraw->total_rejected_weight + $request->input('assorting_rejected');
		$diamondraw->total_loss = $diamondraw->total_loss + $request->input('assorting_loss');
		$diamondraw->assorting_status = 1;
		$diamondraw->update();

		return redirect('/diamondraw/assortinglist')->with('success', Config::get('constants.message.DiamondRaw_Assorted'));
	}

	public function Sizing(Request $request) {
		$data = DiamondRaw::where('id', $request->id)->get();

		$returnHTML = view('diamondraw.sizing', ['data' => $data])->render();
		return response()->json(array('success' => true, 'html' => $returnHTML));
	}

	public function Sizing_transaction(Request $request) {

		$diamondraw = DiamondRaw::findOrFail($request->id);

		$total_weight = $diamondraw->cvd_rejected + $diamondraw->assorting_rejected;
		$total_loss = $diamondraw->cvd_loss + $diamondraw->assorting_loss;

		$diamondraw->sizing_weight = $request->input('sizing_weight');
		$diamondraw->sizing_rejected = $request->input('sizing_rejected');
		$diamondraw->sizing_loss = $request->input('sizing_loss');
		$diamondraw->sizing_loss_reason = $request->input('sizing_loss_reason');
		$diamondraw->total_rejected_weight = $total_weight + $request->input('sizing_rejected');
		$diamondraw->total_loss = $total_loss + $request->input('sizing_loss');
		$diamondraw->sizing_status = 1;
		$diamondraw->update();

		return redirect('/diamondraw/sizinglist')->with('success', Config::get('constants.message.DiamondRaw_Sizing'));
	}

	public function cvdcalculation(request $request) {

		$completeWt = $request->weight;
		$rejectWt = $request->reject;
		$lossWt = $request->loss;
		$totalWt = $request->total_weight;
		$calcultedtotl = $completeWt + $rejectWt + $lossWt;
		$tmpWar = "notValid";
		if ($calcultedtotl == $totalWt) {
			$tmpWar = "Valid";
		}
		return ['success' => true, 'totalValid' => $tmpWar];
	}

	public function cvdcalculation11(Request $request) {

		$completeWt = $request->weight;
		$rejectWt = $request->reject;
		$lossWt = $request->loss;
		$totalWt = $request->total_weight;
		$weightType = $request->weightType;

		if ($weightType == "weight") {
			$completeWtResponse = $completeWt;
			$rejectWtResponse = $totalWt - $completeWtResponse;
			$lossWtResponse = $totalWt - $completeWtResponse - $rejectWtResponse;
		}

		if ($weightType == "reject") {
			$rejectWtResponse = $rejectWt;
			$completeWtResponse = $totalWt - $lossWt - $rejectWt;
			$lossWtResponse = $lossWt;
		}

		if ($weightType == "loss") {
			$lossWtResponse = $lossWt;
			$completeWtResponse = $totalWt - $lossWt - $rejectWt;
			$rejectWtResponse = $rejectWt;

		}

		return ['success' => true, 'total_reject' => $rejectWtResponse, 'total_loss' => $lossWtResponse, 'total_complete' => $completeWtResponse];
	}

	public function diamondrawrespose(Request $request) {

		$columns = array(

			0 => 'packet_name',
			1 => 'total_weight',
			2 => 'purchased_at',
			3 => 'action');
		$results = DiamondRaw::where('cvd_status', '0');

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		//print_r($order);exit;
		$dir = $request->input('order.0.dir');
		if (empty($request->input('search.value'))) {
			$resultslist = DiamondRaw::where('cvd_status', '0')->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');

			$resultslist = $results->whereRaw('(packet_name LIKE "%' . $search . '%" OR total_weight LIKE "%' . $search . '%" OR purchased_at LIKE "%' . $search . '%") ')->offset($start)->limit($limit)->orderBy($order, $dir)->get();

			$totalFiltered = $results->whereRaw('(packet_name LIKE "%' . $search . '%" OR total_weight LIKE "%' . $search . '%" OR purchased_at LIKE "%' . $search . '%") ')->count();
		}
		$data = array();

		if (!empty($resultslist)) {
			$i = 0;
			foreach ($resultslist as $resultslist) {

				$action = '<button class="btn btn-success success-btn-style btn-sm btn-rounded ripple" ' . $resultslist->id . ' onclick="showDetail(' . $resultslist->id . ')"><span>CVD</span><i class="material-icons list-icon">check</i></button>';

				//$action=' <a href="#" class="btn btn-info getid"  value ="'.$resultslist->id.'" onclick="showDetail('.$resultslist->id.')">CVD</a>';

				$data[] = array($resultslist->packet_name, $resultslist->total_weight, $resultslist->purchased_at, $action);
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

	public function cvd_response(Request $request) {
		$columns = array(

			0 => 'packet_name',
			1 => 'cvd_weight',
			2 => 'cvd_rejected',
			3 => 'cvd_loss',
			4 => 'action');

		$results = DiamondRaw::where('cvd_status', '1')->where('assorting_status', '0');

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		//print_r()
		if (empty($request->input('search.value'))) {
			$resultslist = DiamondRaw::where('cvd_status', '1')->where('assorting_status', '0')->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');

			$resultslist = $results->whereRaw('(packet_name LIKE "%' . $search . '%" OR cvd_weight LIKE "%' . $search . '%" OR cvd_rejected LIKE "%' . $search . '%" OR cvd_loss LIKE "%' . $search . '%") ')->offset($start)->limit($limit)->orderBy($order, $dir)->get();

			$totalFiltered = $results->whereRaw('(packet_name LIKE "%' . $search . '%" OR cvd_weight LIKE "%' . $search . '%" OR cvd_rejected LIKE "%' . $search . '%" OR cvd_loss LIKE "%' . $search . '%") ')->count();
		}
		$data = array();

		if (!empty($resultslist)) {
			$i = 0;
			foreach ($resultslist as $resultslist) {

				$action = '<button class="btn btn-success btn-sm success-btn-style btn-rounded ripple" value ="' . $resultslist->id . '" onclick="Assorting(' . $resultslist->id . ')"><span>Assorting</span>
				 <i class="material-icons list-icon">check</i></button>';

				/*$action = ' <a href="#" class="btn btn-info getid"  value ="' . $resultslist->id . '" onclick="Assorting(' . $resultslist->id . ')">Assorting</a>';*/
				/*$action = '<button class="btn btn-success btn-rounded ripple" value ="' . $resultslist->id . '" onclick="Assorting(' . $resultslist->id . ')"><i class="material-icons list-icon">check</i><span>Assorting</span></button>';*/

				$data[] = array($resultslist->packet_name, $resultslist->cvd_weight, $resultslist->cvd_rejected, $resultslist->cvd_loss, $action);
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

	public function assorting_response(Request $request) {

		$columns = array(

			0 => 'packet_name',
			1 => 'assorting_weight',
			2 => 'assorting_rejected',
			3 => 'total_rejected_weight',
			4 => 'assorting_loss',
			5 => 'total_loss',
			6 => 'total_weight',
			7 => 'action');
		$results = DiamondRaw::where('assorting_status', '1');

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		//print_r($order);exit;
		//	print_r($order);exit;

		if (empty($request->input('search.value'))) {
			$diamonddata = DiamondRaw::where('assorting_status', '1')->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');

			$diamonddata = $results->where('packet_name', 'LIKE', "%{$search}%")->orWhere('assorting_weight', 'LIKE', "%{$search}%")->orWhere('assorting_rejected', 'LIKE', "%{$search}%")->orWhere('total_rejected_weight', 'LIKE', "%{$search}%")->orWhere('assorting_loss', 'LIKE', "%{$search}%")->orWhere('total_loss', 'LIKE', "%{$search}%")->orWhere('total_weight', 'LIKE', "%{$search}%")->offset($start)->limit($limit)->orderBy($order, $dir)->get();

			$totalFiltered = $results->where('packet_name', 'LIKE', "%{$search}%")->orWhere('assorting_weight', 'LIKE', "%{$search}%")->orWhere('assorting_rejected', 'LIKE', "%{$search}%")->orWhere('total_rejected_weight', 'LIKE', "%{$search}%")->orWhere('assorting_loss', 'LIKE', "%{$search}%")->orWhere('total_loss', 'LIKE', "%{$search}%")->orWhere('total_weight', 'LIKE', "%{$search}%")->count();

		}
		$data = array();

		if (!empty($diamonddata)) {
			$i = 0;
			foreach ($diamonddata as $resultslist) {
				if ($resultslist->moved_to_inventory == 1) {

					$action = ' <a  class="color-content table-action-style"  style="display: none;" href="' . route('diamondmovetoinventory.create', ['id' => $resultslist->id]) . '"  title="Sizing"><i class="material-icons md-18">move_to_inbox</i></a> ';

				} else {
					$action = '<a  class="color-content table-action-style" href="' . route('diamondmovetoinventory.create', ['id' => $resultslist->id]) . '"  title="Sizing"><i class="material-icons md-18">move_to_inbox</i></a> ';

				}
				if ($resultslist->memo_returned == 1) {
					$action1 = '<a href="' . action('DiamondRawController@downloadmemo', ['id' => $resultslist->id]) . '" target="_blank" class="color-content table-action-style" title="Download Return Voucher"><i class="material-icons md-18">file_download</i></a>';

				} else {

					$action1 = '<a class="color-content table-action-style export_pdf"  href="javascript:void(0);" data-token="' . csrf_token() . '" data-raw-id="' . $resultslist->id . '" title="Return to Vendor"><i class="material-icons md-18">assignment_return</i></a>
                            <input type="hidden" name="diamondraw_id" class="diamondraw_id"  value="' . $resultslist->id . '"> ';
				}
				$validaction = '' . $action . '' . $action1 . '';

				$action = '<button class="btn btn-success btn-rounded ripple" value ="' . $resultslist->id . '" onclick="Sizing(' . $resultslist->id . ')"><i class="material-icons list-icon">check</i><span>Sizing</span></button>';

				$data[] = array($resultslist->packet_name, $resultslist->assorting_weight, $resultslist->assorting_rejected, $resultslist->total_rejected_weight, $resultslist->assorting_loss, $resultslist->total_loss, $resultslist->total_weight, $validaction);
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

	public function sizing_response(Request $request) {
		$columns = array(
			0 => 'id',
			1 => 'packet_name',
			2 => 'sizing_weight',
			3 => 'sizing_rejected',
			4 => 'total_rejected_weight',
			5 => 'sizing_loss',
			6 => 'total_loss',
			7 => 'action');

		$results = DiamondRaw::where('sizing_status', '1')->orderBy('return_date', 'asc')->distinct();

		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];

		$dir = $request->input('order.0.dir');
		if (empty($request->input('search.value'))) {
			$resultslist = DiamondRaw::where('sizing_status', '1')->orderBy('return_date', 'desc')->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = DiamondRaw::where('sizing_status', '1')->where('packet_name', 'LIKE', "%{$search}%")->offset($start)->limit($limit)->orderBy($order, $dir)->get();

			$totalFiltered = DiamondRaw::where('sizing_status', '1')->where('packet_name', 'LIKE', "%{$search}%")->count();
		}
		$data = array();

		if (!empty($resultslist)) {
			$i = 0;
			foreach ($resultslist as $resultslist) {

				if ($resultslist->moved_to_inventory == 1) {

					$action = ' <a  class="color-content table-action-style"  style="display: none;" href="' . route('diamondmovetoinventory.create', ['id' => $resultslist->id]) . '"  title="Move to Inventory"><i class="material-icons md-18">move_to_inbox</i></a> ';

				} else {
					$action = '<a  class="color-content table-action-style" href="' . route('diamondmovetoinventory.create', ['id' => $resultslist->id]) . '"  title="Move to Inventory"><i class="material-icons md-18">move_to_inbox</i></a> ';

				}
				if ($resultslist->memo_returned == 1) {
					$action1 = '<a href="' . action('DiamondRawController@downloadmemo', ['id' => $resultslist->id]) . '" target="_blank" class="color-content table-action-style" title="Download Return Voucher"><i class="material-icons md-18">file_download</i></a>';
				} else {

					$action1 = '<a class="color-content table-action-style export_pdf"  href="javascript:void(0);  data-token="' . csrf_token() . '" data-raw-id="' . $resultslist->id . '" title="Return to Vendor"><i class="material-icons md-18">assignment_return</i></a>
                            <input type="hidden" name="diamondraw_id" class="diamondraw_id"  value="' . $resultslist->id . '"> ';
				}
				$validaction = '' . $action . '' . $action1 . '';

				$data[] = array($resultslist->packet_name, $resultslist->sizing_weight, $resultslist->sizing_rejected, $resultslist->total_rejected_weight, $resultslist->total_loss, $resultslist->sizing_loss, $validaction);
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

	public function returnmemo(Request $request) {
		$id = $request['id'];
		$diamondraw = DiamondRaw::find($id);
		$userdata = User::where('name', $diamondraw['vendor_name'])->get();

		$lastreturnId = DiamondRaw::select('return_id')->orderBy('return_id', 'desc')->first();
		$lastreturnId = (int) substr($lastreturnId, -3);

		$transaction_id = '10000000' . $lastreturnId + 1;
		$returnno = $transaction_id;
		$date = Date('d/m/Y');
		$format = date('Y-m-d', strtotime($date));
		$customPaper = array(0, 0, 1200, 1200);

		$total_weight = $diamondraw->cvd_rejected + $diamondraw->assorting_rejected + $diamondraw->sizing_rejected;
		$total_loss = $diamondraw->cvd_loss + $diamondraw->assorting_loss + $diamondraw->sizing_loss;

		$diamondraw->total_rejected_weight = $total_weight;
		$diamondraw->total_loss = $total_loss;
		$diamondraw->update();

		// compact('diamondraw', 'userdata', 'total_weight', 'returnno', 'date')

		//$pdf = PDF::loadView('diamondraw/Return-voucher')->setPaper($customPaper, 'landscape');

		$html = view('diamondraw.Return-voucher', compact('diamondraw', 'userdata', 'total_weight', 'returnno', 'date'))->render();

		// instantiate and use the dompdf class
		$dompdf = new Dompdf();
		$dompdf->loadHtml($html);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($customPaper, 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		//$dompdf->stream();

		//exit;
		//var_dump($pdf);exit;

		$path = public_path('uploads/return_to_vendor_receipts/');

		//$dompdf->save($path . 'return_to_vendor_' . time() . '.pdf');

		$filename = "return_to_vendor_" . time() . '.pdf';
		$newfile = $path . "return_to_vendor_" . time() . '.pdf';
		$diamondraw->memo_returned = 1;
		$diamondraw->return_id = $returnno;
		$diamondraw->return_date = $format;
		$diamondraw->return_to_vendor_receipt = $filename;
		$diamondraw->update();

		$output = $dompdf->output();
		file_put_contents($newfile, $output);

		//echo "test";exit;
		//$dompdf->stream();
		//return $dompdf->download($filename);

		/*$json_data = array(
			"code" => true,
			"msg" => Config::get('constants.message.voucher_generated_successfully'),
		);*/

		//echo json_encode($json_data);

		return redirect('/diamondraw/assortinglist')->with('success', Config::get('constants.message.voucher_generated_successfully'));

	}

	public function issue_voucher_list(Request $request) {

		$dataCount = DB::select(DB::raw("(SELECT if(gold.metal_type = 1,'Gold','Platinum 950') as type,user.name,gold.id,gold.issue_date as purchased_at ,gold.vendor_id as gold_vendor,gold.issue_voucher_no as gold_voucher_no,gold.po_number as gold_po,gold.purchased_invoice as gold_voucher,gold.is_handover,gold.is_voucher_no_generated, gold.created_at as transaction_at,metal_weight as wgt FROM dml_metal_transactions gold JOIN dml_users user   ON gold.vendor_id = user.id  WHERE gold.transaction_type =2 and gold.deleted_at IS  NULL GROUP BY gold_voucher)  UNION (SELECT 'diamond' as type,name,diamond.id,purchased_at ,vendor_id ,issue_voucher_no ,po_number ,issue_vaucher,diamond.is_handover,diamond.is_voucher_no_generated, diamond.created_at as transaction_at,diamond_weight as wgt FROM dml_diamond_transactions diamond  JOIN dml_users user   ON diamond.vendor_id = user.id WHERE diamond.transaction_type =2 and diamond.deleted_at IS  NULL GROUP BY issue_voucher_no ) ORDER BY transaction_at DESC "));

		$TotalCountData = count($dataCount);
		$data = DB::select(DB::raw("(SELECT if(gold.metal_type = 1,'Gold','Platinum 950') as type,user.name,gold.id,gold.issue_date as purchased_at ,gold.vendor_id as gold_vendor,gold.issue_voucher_no as gold_voucher_no,gold.po_number as gold_po,gold.purchased_invoice as gold_voucher,gold.is_handover,gold.is_voucher_no_generated, gold.created_at as transaction_at,metal_weight as wgt FROM dml_metal_transactions gold JOIN dml_users user   ON gold.vendor_id = user.id  WHERE gold.transaction_type =2 and gold.deleted_at IS  NULL GROUP BY gold_voucher)  UNION (SELECT 'diamond' as type,name,diamond.id,purchased_at ,vendor_id ,issue_voucher_no ,po_number ,issue_vaucher,diamond.is_handover,diamond.is_voucher_no_generated, diamond.created_at as transaction_at,diamond_weight as wgt FROM dml_diamond_transactions diamond  JOIN dml_users user   ON diamond.vendor_id = user.id WHERE diamond.transaction_type =2 and diamond.deleted_at IS NULL  GROUP BY issue_voucher_no) ORDER BY transaction_at DESC limit 10")); // limit 10

		//echo "<pre>"; print_r($data);exit;
		return view('diamondraw/issue_voucher_list', compact('data', 'TotalCountData'));
	}

	public function filter_issue_voucher(Request $request) {
		$data = DB::select(DB::raw("(SELECT if(gold.metal_type = 1,'Gold','Platinum 950') as type,user.name,gold.id,gold.issue_date as purchased_at ,gold.vendor_id as gold_vendor,gold.issue_voucher_no as gold_voucher_no,gold.po_number as gold_po,gold.purchased_invoice as gold_voucher,gold.is_handover,gold.is_voucher_no_generated, gold.created_at as transaction_at,metal_weight as wgt FROM dml_metal_transactions gold JOIN dml_users user ON gold.vendor_id = user.id  WHERE gold.transaction_type =2 and gold.deleted_at IS  NULL GROUP BY gold_voucher)  UNION (SELECT 'diamond' as type,name,diamond.id,purchased_at ,vendor_id ,issue_voucher_no ,po_number ,issue_vaucher,diamond.is_handover,diamond.is_voucher_no_generated, diamond.created_at as transaction_at,diamond_weight as wgt FROM dml_diamond_transactions diamond  JOIN dml_users user   ON diamond.vendor_id = user.id WHERE diamond.transaction_type =2 and diamond.deleted_at IS  NULL GROUP BY issue_voucher_no ) ORDER BY transaction_at DESC"));

		$Diamonds = DB::select(DB::raw("(SELECT if(gold.metal_type = 1,'Gold','Platinum 950') as type,user.name,gold.id,gold.issue_date as purchased_at ,gold.vendor_id as gold_vendor,gold.issue_voucher_no as gold_voucher_no,gold.po_number as gold_po,gold.purchased_invoice as gold_voucher,gold.is_handover,gold.is_voucher_no_generated, gold.created_at as transaction_at,metal_weight as wgt FROM dml_metal_transactions gold JOIN dml_users user   ON gold.vendor_id = user.id  WHERE gold.transaction_type =2 and gold.deleted_at IS  NULL GROUP BY gold_voucher)  UNION (SELECT 'diamond' as type,name,diamond.id,purchased_at ,vendor_id ,issue_voucher_no ,po_number ,issue_vaucher,diamond.is_handover,diamond.is_voucher_no_generated, diamond.created_at as transaction_at,diamond_weight as wgt FROM dml_diamond_transactions diamond  JOIN dml_users user   ON diamond.vendor_id = user.id WHERE diamond.transaction_type =2 and diamond.deleted_at IS  NULL GROUP BY issue_voucher_no ) ORDER BY transaction_at DESC"));
		$columns = array(
			0 => 'type',
			1 => 'gold_po',
			2 => 'name',
			3 => 'transaction_at',
			4 => 'gold_voucher_no');

		$params = $request->post();
		$start = $request->input('start');
		$limit = $request->input('length');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		$data = array();
		if (empty($request->input('search.value'))) {
			$Diamonds = "(SELECT if(gold.metal_type = 1,'Gold','Platinum 950') as type,user.name,gold.id,gold.issue_date as purchased_at ,gold.vendor_id as gold_vendor,gold.issue_voucher_no as gold_voucher_no,gold.po_number as gold_po,gold.purchased_invoice as gold_voucher,gold.is_handover,gold.is_voucher_no_generated, gold.created_at as transaction_at,metal_weight as wgt FROM dml_metal_transactions gold JOIN dml_users user ON gold.vendor_id = user.id  WHERE gold.transaction_type =2 and gold.deleted_at IS  NULL GROUP BY gold_voucher) UNION (SELECT 'Diamond' as type,name,diamond.id,purchased_at ,vendor_id ,issue_voucher_no ,po_number ,issue_vaucher,diamond.is_handover,diamond.is_voucher_no_generated, diamond.created_at as transaction_at,diamond_weight as wgt FROM dml_diamond_transactions diamond  JOIN dml_users user   ON diamond.vendor_id = user.id WHERE diamond.transaction_type =2 and diamond.deleted_at IS  NULL GROUP BY issue_voucher_no) ORDER BY " . $order . ' ' . $dir;
		} else {
			$search = $request->input('search.value');
			$Diamonds = "(SELECT if(gold.metal_type = 1,'Gold','Platinum 950') as type,user.name,gold.id,gold.issue_date as purchased_at ,gold.vendor_id as gold_vendor,gold.issue_voucher_no as gold_voucher_no,gold.po_number as gold_po,gold.purchased_invoice as gold_voucher,gold.is_handover,gold.is_voucher_no_generated, gold.created_at as transaction_at,metal_weight as wgt FROM dml_metal_transactions gold JOIN dml_users user ON gold.vendor_id = user.id  WHERE gold.transaction_type =2 and gold.deleted_at IS  NULL HAVING Type LIKE '%" . $search . "%' OR name LIKE '%" . $search . "%' OR purchased_at LIKE '%" . $search . "%' OR gold_po LIKE '%" . $search . "%' OR gold_voucher_no LIKE '%" . $search . "%') UNION (SELECT 'Diamond' as type,name,diamond.id,purchased_at ,vendor_id ,issue_voucher_no ,po_number ,issue_vaucher,diamond.is_handover,diamond.is_voucher_no_generated, diamond.created_at as transaction_at,diamond_weight as wgt FROM dml_diamond_transactions diamond  JOIN dml_users user ON diamond.vendor_id = user.id WHERE diamond.transaction_type =2 and diamond.deleted_at IS  NULL GROUP BY issue_voucher_no HAVING Type LIKE '%" . $search . "%' OR name LIKE '%" . $search . "%' OR purchased_at LIKE '%" . $search . "%' OR po_number LIKE '%" . $search . "%' OR issue_voucher_no LIKE '%" . $search . "%' ) ORDER BY " . $order . ' ' . $dir;
		}
		$DiamondsData = DB::select(DB::raw($Diamonds));
		$datacount = count($DiamondsData);
		$Diamonds .= " LIMIT " . $limit . " OFFSET " . $start . "";
		$DiamondsArr = DB::select(DB::raw($Diamonds));
		$datacollection = $DiamondsArr;

		if (count($datacollection) > 0) {

			foreach ($datacollection as $key => $issueVoucherData) {
				$type = $issueVoucherData->type;
				$vname = $issueVoucherData->name;
				$invoiceNo = $issueVoucherData->gold_po;
				$purchased_at = $issueVoucherData->purchased_at;
				$gold_voucher_no = ($issueVoucherData->is_voucher_no_generated == '1' ? $issueVoucherData->gold_voucher_no : "-");
				$filename = URL::to(Config::get('constants.dir.issue_vaucher') . $issueVoucherData->gold_voucher);

				$action = "<a target='_blank' class='color-content table-action-style' href=" . $filename . "><i class='material-icons md-18'>remove_red_eye</i></a>&nbsp";

				if ($issueVoucherData->is_voucher_no_generated == "1") {
					$action .= "<a href='" . route('diamondraw.voucher_download', ['voucher' => $issueVoucherData->gold_voucher, 'voucher_type' => $issueVoucherData->type]) . "' class='color-content table-action-style'><i class='material-icons md-18'>file_download</i></a>&nbsp";
				}

				if ($issueVoucherData->type == "Diamond") {

					if ($issueVoucherData->is_handover == "0") {
						$action .= "<a class='color-content table-action-style' title='Show' href='" . route('diamond.edit_issue_voucher', $issueVoucherData->gold_voucher_no) . "'><i class='material-icons md-18'>edit</i></a>&nbsp";
					}

					if ($issueVoucherData->wgt != "0") {
						$action .= '<a href="#" class="color-content table-action-style" title="Return"  onclick="returnDiamondIssue(' . "'" . $issueVoucherData->gold_voucher_no . "'" . ')"><i class="material-icons md-18">replay</i></a>&nbsp';
					}

					if ($issueVoucherData->is_voucher_no_generated == "0") {
						$action .= '<a href="#" class="color-content table-action-style" title="Generate Voucher no"  onclick="generateVoucherno(' . "'" . $issueVoucherData->gold_voucher_no . "'" . ')" ><i class="fas fa-tag fs-16"></i></a>&nbsp';

						$action .= '<a href="#" class="color-content table-action-style" title="Delete Voucherno"  onclick="deleteVoucher(' . "'" . $issueVoucherData->gold_voucher_no . "'" . ')" ><i class="material-icons md-18">delete</i></a>&nbsp';
					} elseif ($issueVoucherData->is_handover == "0") {

						$action .= '<a href="#" class="color-content table-action-style" title="Handover"  onclick="Handover(' . "'" . $issueVoucherData->gold_voucher_no . "'" . ')" ><i class="fas fa-hand-holding fs-18"></i></a>&nbsp';
					}

				}if ($issueVoucherData->type == "Gold") {

					/*if ($issueVoucherData->is_handover == "0") {
						$action .= "<a class='color-content table-action-style' title='Show' href='" . route('metals.edit_gold_issue_voucher', $issueVoucherData->gold_voucher_no) . "'><i class='material-icons md-18'>edit</i></a>&nbsp";*/
					if ($issueVoucherData->is_handover == "0") {
						$action .= "<a  class='color-content table-action-style' title='Show' href='" . route('metals.edit_gold_issue_voucher', $issueVoucherData->gold_voucher_no) . "'><i class='material-icons md-18'>edit</i></a>&nbsp";
					}

					if ($issueVoucherData->wgt != "0") {
						$action .= '<a href="#" class="color-content table-action-style" title="Return"  onclick="returnGoldIssue(' . "'" . $issueVoucherData->gold_voucher_no . "'" . ')"><i class="material-icons md-18">replay</i></a>&nbsp';
					}

					if ($issueVoucherData->is_voucher_no_generated == "0") {
						$action .= '<a href="#" class="color-content table-action-style" title="Generate Voucher no"  onclick="generateGoldVoucherno(' . "'" . $issueVoucherData->gold_voucher_no . "'" . ')" ><i class="material-icons md-18">playlist_add</i></a>&nbsp';

						$action .= '<a href="#" class="color-content table-action-style" title="Delete Voucherno"  onclick="deleteGoldVoucher(' . "'" . $issueVoucherData->gold_voucher_no . "'" . ')" ><i class="material-icons md-18">delete</i></a>&nbsp';
					} elseif ($issueVoucherData->is_handover == "0") {

						$action .= '<a  href="#" class="color-content table-action-style" title="Handover"  onclick="goldHandover(' . "'" . $issueVoucherData->gold_voucher_no . "'" . ')" ><i class="fas fa-hand-holding fs-18"></i></a>&nbsp';
					}

				}
				$data[] = array($type, $invoiceNo, $vname, $purchased_at, $gold_voucher_no, $action);
			}

		} else {
			$data[] = array('', '', '', '', '', '');
		}
		$json_data = array(
			"query" => $start,
			"draw" => intval($request->input('draw')),
			"recordsTotal" => intval($datacount),
			"recordsFiltered" => intval($datacount),
			"data" => $data,
		);
		echo json_encode($json_data);
	}

	public function downloadmemo(Request $request) {

		$transaction = DiamondRaw::where('id', $request->id)->first();

		$exactFilePath = base_path("public" . '/' . config('constants.dir.return_to_vendor_receipts')) . $transaction->return_to_vendor_receipt;

		return response()->download($exactFilePath);
	}

	public function voucher_download(Request $request) {
		$headers = ['Content-Type: application/pdf'];

		if ($request->voucher_type == "diamond") {

			$voucher_no = DiamondTransaction::select('issue_voucher_no')->where('issue_vaucher', $request->voucher)->value('issue_voucher_no');
			$search_voucher_diamond = substr($voucher_no, -5);

			$diamondnewfile = 'Diamond_Issue_Voucher_' . $search_voucher_diamond . '.pdf';

			$diamondfile = base_path("public" . '/' . config('constants.dir.issue_vaucher') . $request->voucher);

			return response()->download($diamondfile, $diamondnewfile, $headers);
		}

		if ($request->voucher_type == "gold") {

			$voucher_no_gold = MetalTransaction::select('issue_voucher_no')->where('purchased_invoice', $request->voucher)->where('is_voucher_no_generated', '1')->value('issue_voucher_no');
			$myFile = base_path("public" . '/' . config('constants.dir.issue_vaucher') . $request->voucher);
			$search_voucher_no = explode("/", $voucher_no_gold);
			$newName = 'Gold_Issue_Voucher_' . $search_voucher_no[1] . '.pdf';
			return response()->download($myFile, $newName, $headers);

		}
	}

}
