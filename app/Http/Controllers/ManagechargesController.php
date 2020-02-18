<?php

namespace App\Http\Controllers;

use App\DiamondType;
use App\ManageCharges;
use App\Product;
use App\User;
use App\VendorCharges;
use Config;
use DB;
use Illuminate\Http\Request;

class ManagechargesController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function __construct(ManageCharges $Managecharges) {
		$this->ManageCharges = $Managecharges;
	}
	public function index(Request $request) {

		$vendor_id = $request->get('vendor_id');
		$name = $request->get('name');
		$vendor_charges = DB::table('vendor_charges')->leftjoin('vendor_product_types', 'vendor_charges.product_type', '=', 'vendor_product_types.vendor_product_id')->leftjoin('vendor_diamond_types', 'vendor_charges.diamond_type', '=', 'vendor_diamond_types.vendor_diamond_id')->select('vendor_charges.*', 'vendor_diamond_types.name', 'vendor_product_types.name as pname')->where('vendor_id', $vendor_id)->orderBy('id', 'DESC')->paginate();

		$totalcount = DB::table('vendor_charges')->leftjoin('vendor_product_types', 'vendor_charges.product_type', '=', 'vendor_product_types.vendor_product_id')->leftjoin('vendor_diamond_types', 'vendor_charges.diamond_type', '=', 'vendor_diamond_types.vendor_diamond_id')->select('vendor_charges.*', 'vendor_diamond_types.name', 'vendor_product_types.name as pname')->where('vendor_id', $vendor_id)->count();

		return view('vendor/managecharges/index', compact('vendor_charges', 'vendor_id', 'name', 'totalcount'))->with('i', ($request->input('page', 1) - 1) * 5);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request) {

		$vendor_id = $request->get('vendor_id');
		$name = $request->get('name');
		$metaltype = DB::table(DB::raw(' grp_metal_type'))->get();
		$user = User::where('id', '>', 1)->pluck('name', 'name');

		return view('vendor/managecharges/create', compact('vendor_id', 'name', 'metaltype'))
			->with('product', $this->ManageCharges->vendor_product_types())
			->with('diamond', $this->ManageCharges->vendor_diamond_types())
			->with(compact('user'));

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$search = VendorCharges::where(['type' => $request->type, 'to_mm' => $request->to_mm, 'from_mm' => $request->from_mm, 'product_type' => $request->product_type, 'diamond_type' => $request->diamond_type, 'vendor_id' => $request->vendor_id])->first();
		/*print_r($search);
   var_dump($search);exit;*/
		if ($search == "") {
			$this->validate($request, [
				'from_mm' => 'required|min:0',
				'to_mm' => 'required|min:0',
				'type' => 'required',
				'labour_charge' => 'required|min:1',
				'product_type' => 'required',
				'diamond_type' => 'required',
			]);
			//print_r($request->all());exit;
			$vendor_id = $request->get('vendor_id');
			$name = $request->get('name');
			$input = $request->all();
			// $vendor_charges = VendorCharges::all();

			$vendor_charges = new VendorCharges;

			$vendor_charges->vendor_id = $request->input('vendor_id');
			$vendor_charges->from_mm = $request->input('from_mm');
			$vendor_charges->to_mm = $request->input('to_mm');
			$vendor_charges->type = $request->input('type');
			$vendor_charges->labour_charge = $request->input('labour_charge');
			$vendor_charges->product_type = $request->input('product_type');
			$vendor_charges->diamond_type = $request->input('diamond_type');

			$vendor_charges->save();

			return redirect()->route('managecharges.index', compact('name', 'vendor_id'))->with('success', Config::get('constants.message.vendor_charges_add_success'));
		} else {

			return redirect()->back()->withInput()->with('errors', ['This combination alredy taken']);
		}
		/*print_r($search);exit;

			        $input['type'] = $request->input('type');
			        $rules = array('type' => 'unique:vendor_charges,type');
			          $this->validate($request, [

			            'from_mm' => 'required|integer|min:0',
			            'to_mm'=>'required|integer|min:0',
			            'type'=>'required',
			            'labour_charge'=>'required|integer|min:1',
			            'product_type'=>'required',
			            'diamond_type'=>'required',

			        ]);

			           $vendor_id=$request->get('vendor_id');
			         $name=$request->get('name');
			        $input = $request->all();
			        $vendor_charges = VendorCharges::all();

			        $vendor_charges = new VendorCharges;

			        $vendor_charges->vendor_id = $request->input('vendor_id');
			        $vendor_charges->from_mm = $request->input('from_mm');
			        $vendor_charges->to_mm = $request->input('to_mm');
			        $vendor_charges->type = $request->input('type');
			        $vendor_charges->labour_charge =$request->input('labour_charge');
			        $vendor_charges->product_type = $request->input('product_type');
			        $vendor_charges->diamond_type = $request->input('diamond_type');

			        $vendor_charges->save();

			        return redirect()->route('managecharges.index',compact('name','vendor_id'))
		*/

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$vendor_charges = VendorCharges::find($id);

		return view('vendor.managecharges.show', compact('vendor_charges'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $id) {
		$vendor_charges = VendorCharges::find($id);
		$id = VendorCharges::select('vendor_id')->where('id', $id)->get();
		$vendor_id = $id[0]->vendor_id;
		/* print_r($id[0]->vendor_id);exit;*/
		$vendorname = DB::table('users')->select('name')->where('id', $vendor_id)->get();
		$metaltype = DB::table(DB::raw(' grp_metal_type'))->get();
		$name = $vendorname[0]->name;

		$product = Product::all();
		$diamond = DiamondType::all();

		return view('vendor.managecharges.edit', compact('vendor_charges', 'product', 'diamond', 'vendor_id', 'name', 'metaltype'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		$vendor_id = $request->get('vendor_id');
		$name = $request->get('name');

		//print_r($vendor_id.','.$name);exit;
		$search = VendorCharges::where(['type' => $request->type, 'to_mm' => $request->to_mm, 'from_mm' => $request->from_mm, 'product_type' => $request->product_type, 'diamond_type' => $request->diamond_type, 'vendor_id' => $request->vendor_id, ['id', '!=', $id]])->first();
		//echo '<pre>';
		//print_r($search);exit;
		/*$input = $request->all();*/
		if (empty($search)) {
			$input = $request->all();
			$vendor_charges = VendorCharges::find($id);
			$vendor_charges->vendor_id = $request->input('vendor_id');
			$vendor_charges->from_mm = $request->input('from_mm');
			$vendor_charges->to_mm = $request->input('to_mm');
			$vendor_charges->type = $request->input('type');
			$vendor_charges->labour_charge = $request->input('labour_charge');
			$vendor_charges->product_type = $request->input('product_type');
			$vendor_charges->diamond_type = $request->input('diamond_type');

			$vendor_charges->save();
			return redirect()->route('managecharges.index', ['vendor_id' => $vendor_id, 'name' => $name])
				->with('success', Config::get('constants.message.vendor_charges_update_success'));
		} else {
			return redirect()->back()->withInput()->with('errors', ['This combination alredy taken']);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {

		VendorCharges::find($id)->delete();
		return response()->json([
			'success' => 'Record deleted successfully!',
		]);
		$return_data = array();

		$return_data['response'] = 'success';
		echo json_encode($return_data);exit;
		return redirect()->route('managecharges.index')
			->with('success', 'Product Type deleted successfully');
	}

	public function managechargesresponse(Request $request) {

		$columns = array(
			0 => 'id',
			1 => 'name',
			2 => 'from_mm',
			3 => 'to_mm',
			4 => 'type',
			5 => 'labour_charge',
			6 => 'product_type',
			7 => 'diamond_type',
			8 => 'action');
		$results = DB::table('vendor_charges')->leftjoin('vendor_product_types', 'vendor_charges.product_type', '=', 'vendor_product_types.vendor_product_id')->leftjoin('vendor_diamond_types', 'vendor_charges.diamond_type', '=', 'vendor_diamond_types.vendor_diamond_id')->select('vendor_charges.*', 'vendor_diamond_types.name as name', 'vendor_product_types.name as pname')->where('vendor_id', $request->_id);
		// print_r($results->get());exit;
		$totalData = $results->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		$name = $request->input('_name');
		if (empty($request->input('search.value'))) {
			$resultslist = $results->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');
			$resultslist = $results->where('vendor_id', $request->_id)->where('id', 'LIKE', "%{$search}%")->orWhere('from_mm', 'LIKE', "%{$search}%")->orWhere('to_mm', 'LIKE', "%{$search}%")->orWhere('labour_charge', 'LIKE', "%{$search}%")->offset($start)->limit($limit)->orderBy($order, $dir)->get();

			$totalFiltered = $results->where('id', 'LIKE', "%{$search}%")->orWhere('from_mm', 'LIKE', "%{$search}%")->orWhere('to_mm', 'LIKE', "%{$search}%")->orWhere('labour_charge', 'LIKE', "%{$search}%")->orwhere('vendor_id', 'LIKE', "%{$search}%")->count();
		}

		$data = array();

		if (!empty($resultslist)) {

			foreach ($resultslist as $resultslist) {

				$action = '<a class="color-content table-action-style" href="' . action('ManagechargesController@edit', $resultslist->id) . '"><i class="material-icons md-18">edit</i></a>
                                  <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleteuser(' . $resultslist->id . ',\'' . csrf_token() . '\');" data-token="\'' . csrf_token() . '\'"><i class="material-icons md-18">delete</i>';
				if ($resultslist->type == 1) {
					$type = '<td>Gold</td>';
				} else if ($resultslist->type == 2) {
					$type = '<td>Silver</td>';
				} else {
					$type = '<td>Platinum(950)</td>';
				}
				$diamond_type = '<td>' . $resultslist->name . '</td>';
				$product_type = '<td>' . $resultslist->pname . '</td>';

				$id = '<input type="hidden" name="vendor_id" id="user_id" value="' . $resultslist->vendor_id . '">';
				$data[] = array(++$start, $request->_name, $resultslist->from_mm, $resultslist->to_mm, $type, $resultslist->labour_charge, $product_type, $diamond_type, $action);
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
}
