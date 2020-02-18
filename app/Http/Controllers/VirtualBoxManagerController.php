<?php

namespace App\Http\Controllers;

use App;
use App\VirtualBoxManager;
use App\VirtualBoxManagerProduct;
use App\VirtualBoxManagerLog;
use App\Helpers\InventoryHelper;
use App\Setting;
use Auth;
use Config;
use DateTime;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Session;
use URL;
use Excel;
use Validator;
use PHPExcel_IOFactory;


class VirtualBoxManagerController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	function __construct() {
		
	}
	
	public function index(Request $request) {
		return view('virtualboxmanager.index');
	}
	
	public function create(Request $request) {
		$categories = InventoryHelper::getCategoryFilterCollection();
		foreach ($categories as $key => $value) {
			if(strtolower($value->name) == "dmlstock" || strtolower($value->name) == 'DML RTS'){continue;}
			$categorys[] = $value;
		}
		return view('virtualboxmanager.create',['categories' => $categorys])->render();

	}

	//Store vb data into database
	public function store(Request $request) {
		$params = $request->post();
		$VBname = $params['name'];
		$VBcode = $params['code'];
		$to = $params['price_to'];
		$from = $params['price_from'];
		$postCategoryId = $params['category_id'];
		$VBChkPrices = true;
		if (!empty($VBcode)) { //Check Code For Duplication
			$VBcodeChk = true;
			$chkCode = VirtualBoxManager::select('code')->where('code',$VBcode)->get();
			if(count($chkCode->toArray()) > 0){
				$forminputcod['code'] = $VBcode;
				$rulesCod = array('code' => 'unique:vb,code');
				$validator = Validator::make($forminputcod, $rulesCod);
				$VBcodeChk = false;
			}
		}

		if (!empty($VBname)) { //Check Code For Duplication
			$VBnameChk = true;
			$chkName = VirtualBoxManager::select('name')->where('name',$VBname)->get();
			if(count($chkCode->toArray()) > 0){
				$forminputcod['name'] = $VBname;
				$rulesCod = array('name' => 'unique:vb,name');
				$validator = Validator::make($forminputcod, $rulesCod);
				$VBnameChk = false;
			}
		}
		
		if((!isset($to) || is_numeric($to)) && (!isset($from) || is_numeric($from))){
			$VBChkPrice = true;
			$VBFromPrice = true;
			$chkPrice = VirtualBoxManager::where('price_from',$from)->where('price_to',$to)->where('category_id','=',$postCategoryId)->get();
			//echo "<pre>";print_r(count($chkPrice->toArray()));exit;
			if(count($chkPrice->toArray()) == 0){
				/* $forminputcod['price_from'] = $from;
				$forminputcod['price_to'] = $to;
				$rulesCod = array('price_from' => 'unique:vb,price_from','price_to' => 'unique:vb,price_to');
				$validator = Validator::make($forminputcod, $rulesCod); */
				
				$VBChkPrice = true;
			}else{
				$chkbtwPrice = VirtualBoxManager::select('price_to','price_from','category_id')->get();
				foreach($chkbtwPrice as $valchkbtw){
					$getprice = $valchkbtw->toArray();
					$priceTo = $getprice['price_to'];
					$priceFrom = $getprice['price_from'];
					$CategoryId = $getprice['category_id'];
					if($postCategoryId == $CategoryId){
						if (($from > $priceFrom && $to < $priceTo)){
							$VBChkPrice = false;
						}else if($from < $priceTo){
							$VBChkPrice = false;
						}
						$chkbtwPriceTo = VirtualBoxManager::where('price_to',$priceTo)->get();					
						foreach($chkbtwPriceTo as $valchkbtwTo){
							
							$getpriceTo = $valchkbtwTo->toArray();
							$Priceto = $getpriceTo['price_to'];						
							if (($from == $Priceto)){
								$VBChkPrice = false;
								$VBFromPrice = false;
							}
						}
					}else{
						$VBChkPrice = true;
					}			
				}
				//echo "<pre>";print_r($validator);exit;
			}
		}
		//echo $VBFromPrice;exit;
		//var_dump($VBcodeChk);exit;
		if($VBChkPrice == false){
			if($VBFromPrice == false){
				$forminputcod['price_from'] = $from;
				$VBChkPrice = false;
				$validator = Config::get('constants.message.Price_Greter_than').$from;
			}else{
				$forminputcod['price_from'] = $from;
				$forminputcod['price_to'] = $to;
				$VBChkPrice = false;
				$validator = Config::get('constants.message.Price_Taken').$from.'-'.$to;
			}
		}
		
		if(!$VBcodeChk){
			$validator = Config::get('constants.message.Code_taken');
		}
		if ($VBcodeChk && $VBChkPrice && $VBnameChk) {
			$data = array(
				"name" => $params['name'],
				"code" => $params['code'],
				"products_limit" => $params['products_limit'],
				"price_from" => $params['price_from'],
				"price_to" => $params['price_to'],
				"category_id" => $params['category_id'],
				"remarks" => $params['remarks'],
				"created_by" => Auth::user()->id,
				"created_at" => date('Y-m-d'),
				"updated_at" => date('Y-m-d')
			);
			$VBCreated = VirtualBoxManager::create($data);
			$mesaage = Config::get('constants.message.added_vb');
			return redirect()->route('virtualboxmanager.create')->with('success', $mesaage);
		}else{
			return redirect()->route('virtualboxmanager.create')->withErrors($validator)->withInput();
		}
	}

	public function edit(Request $request){
		//return view('virtualboxmanager.moveproducts')->render();
	}

	public function editvb($id){
		$vbcoll = VirtualBoxManager::find($id);

		$categories = InventoryHelper::getCategoryFilterCollection();
		foreach ($categories as $key => $value) {
			if(strtolower($value->name) == "dmlstock" || strtolower($value->name) == 'DML RTS'){continue;}
			$categorys[] = $value;
		}

		$findproductcnt = VirtualBoxManagerProduct::find($id);
		//echo $findproductcnt;exit;
		if(empty($findproductcnt)){
			$findproduct = FALSE;	
		}else{
			$findproduct = TRUE;
		}
		return view('virtualboxmanager.editvb',compact('vbcoll', 'categorys','findproduct'));
	}

	public function update(Request $request,$id){
		$params = $request->post();
		$VBname = $params['name'];
		$VBcode = $params['code'];
		$to = $params['price_to'];
		$from = $params['price_from'];
		$postCategoryId = $params['category_id'];
		$findproduct = VirtualBoxManagerProduct::find($id);
		if(empty($findproduct)){
			$VBChkPrices = true;
			if (!empty($VBcode)) { //Check Code For Duplication
				$VBcodeChk = true;
				$chkCode = VirtualBoxManager::select('code')->where('code',$VBcode)->where('id','!=',$id)->get();
				if(count($chkCode->toArray()) > 0){
					$forminputcod['code'] = $VBcode;
					$rulesCod = array('code' => 'unique:vb,code');
					$validator = Validator::make($forminputcod, $rulesCod);
					$VBcodeChk = false;
				}
			}

			if (!empty($VBname)) { //Check Code For Duplication
				$VBnameChk = true;
				$chkName = VirtualBoxManager::select('name')->where('name',$VBname)->where('id','!=',$id)->get();
				if(count($chkCode->toArray()) > 0){
					$forminputcod['name'] = $VBname;
					$rulesCod = array('name' => 'unique:vb,name');
					$validator = Validator::make($forminputcod, $rulesCod);
					$VBnameChk = false;
				}
			}
			
			if((!isset($to) || is_numeric($to)) && (!isset($from) || is_numeric($from))){
				$VBChkPrice = true;
				$VBFromPrice = true;
				$chkPrice = VirtualBoxManager::where('price_from',$from)->where('price_to',$to)->where('id','!=',$id)->get();
				
				if(count($chkPrice->toArray()) > 0){
					$forminputcod['price_from'] = $from;
					$forminputcod['price_to'] = $to;
					$rulesCod = array('price_from' => 'unique:vb,price_from','price_to' => 'unique:vb,price_to');
					$validator = Validator::make($forminputcod, $rulesCod);
					$VBChkPrice = false;
				}else{
					$chkbtwPrice = VirtualBoxManager::select('price_to','price_from','category_id')->where('id','!=',$id)->get();
					foreach($chkbtwPrice as $valchkbtw){
						$getprice = $valchkbtw->toArray();
						$priceTo = $getprice['price_to'];
						$priceFrom = $getprice['price_from'];
						$CategoryId = $getprice['category_id'];
						if($postCategoryId == $CategoryId){
							if (($from > $priceFrom && $to < $priceTo)){
								$VBChkPrice = false;
							}else if($from < $priceTo){
								$VBChkPrice = false;
							}
							$chkbtwPriceTo = VirtualBoxManager::where('price_to',$priceTo)->where('id','!=',$id)->get();					
							foreach($chkbtwPriceTo as $valchkbtwTo){
								
								$getpriceTo = $valchkbtwTo->toArray();
								$Priceto = $getpriceTo['price_to'];						
								if (($from == $Priceto)){
									$VBChkPrice = false;
									$VBFromPrice = false;
								}
							}
						}					
					}
					//echo "<pre>";print_r($validator);exit;
				}
			}
			
			if($VBChkPrice == false){
				if($VBFromPrice == false){
					$forminputcod['price_from'] = $from;
					$VBChkPrice = false;
					$validator = Config::get('constants.message.Price_Greter_than').$from;
				}else{
					$forminputcod['price_from'] = $from;
					$forminputcod['price_to'] = $to;
					$VBChkPrice = false;
					$validator = Config::get('constants.message.Price_Taken').$from.'-'.$to;
				}
			}
			
			if ($VBcodeChk && $VBChkPrice && $VBnameChk) {
				$data = array(
					"name" => $params['name'],
					"code" => $params['code'],
					"products_limit" => $params['products_limit'],
					"price_from" => $params['price_from'],
					"price_to" => $params['price_to'],
					"category_id" => $params['category_id'],
					"remarks" => $params['remarks'],
					"modify_by" => Auth::user()->id,
					"updated_at" => date('Y-m-d')
				);
				$VBCreated = VirtualBoxManager::where('id', $id)->update($data);
				$mesaage = Config::get('constants.message.VB_Edit');
				return redirect()->route('virtualboxmanager.vbboxlist')->with('success', $mesaage);
			}else{
				return redirect()->route('virtualboxmanager.editvb',$id)->withErrors($validator)->withInput();
			}
		}else{

			if (!empty($VBcode)) { //Check Code For Duplication
				$VBcodeChk = true;
				$chkCode = VirtualBoxManager::select('code')->where('code',$VBcode)->where('id','!=',$id)->get();
				if(count($chkCode->toArray()) > 0){
					$forminputcod['code'] = $VBcode;
					$rulesCod = array('code' => 'unique:vb,code');
					$validator = Validator::make($forminputcod, $rulesCod);
					$VBcodeChk = false;
				}
			}
	
			if (!empty($VBname)) { //Check Code For Duplication
				$VBnameChk = true;
				$chkName = VirtualBoxManager::select('name')->where('name',$VBname)->where('id','!=',$id)->get();
				if(count($chkCode->toArray()) > 0){
					$forminputcod['name'] = $VBname;
					$rulesCod = array('name' => 'unique:vb,name');
					$validator = Validator::make($forminputcod, $rulesCod);
					$VBnameChk = false;
				}
			}

			if ($VBcodeChk && $VBnameChk) {
				$upddata = array(
					"name" => $params['name'],
					"code" => $params['code'],
					"modify_by" => Auth::user()->id,
					"updated_at" => date('Y-m-d')
				);
				$VBCreated = VirtualBoxManager::where('id', $id)->update($upddata);
				$mesaage = Config::get('constants.message.VB_Edit');
				return redirect()->route('virtualboxmanager.vbboxlist')->with('success', $mesaage);
			}else{
				return redirect()->route('virtualboxmanager.editvb',$id)->withErrors($validator)->withInput();
			}

		}
		//echo "<pre>";print_r($findproduct);exit;
		//return view('virtualboxmanager.vbboxlist')->render();
	}

	public function show(Request $request){
		//return view('virtualboxmanager.moveproducts')->render();
	}

	public function destroy(Request $request){
		//return view('virtualboxmanager.moveproducts')->render();
	}

	// Move Products Code - Start
	public function moveproducts(Request $request){ // View for moveproducts
		$vbinsertedCat = VirtualBoxManager::select('category_id')->orderBy('created_at', 'DESC')->get();
		$rootCategoryId = DB::select("SELECT entity_id FROM catalog_category_flat_store_1 WHERE level=1");
		$rootCategoryId = $rootCategoryId[0]->entity_id;
		
		foreach($vbinsertedCat->toArray() as $InsertedCat){
			$CatIDs[] = $InsertedCat['category_id'];
		}
		$impCat = implode(",",$CatIDs);
		//Get Category by root category
		$categories = DB::select("SELECT DISTINCT catalog_category_flat_store_1.entity_id,catalog_category_flat_store_1.name FROM catalog_category_flat_store_1 JOIN catalog_category_product ON catalog_category_product.category_id=catalog_category_flat_store_1.entity_id WHERE catalog_category_flat_store_1.parent_id=" . $rootCategoryId ." AND catalog_category_flat_store_1.entity_id IN  (".$impCat.")");
		$collection = collect($categories);
		
		return view('virtualboxmanager.moveproducts',['categories' => $collection])->render();
	}
	
	public function ajaxgetrange(Request $request){ // Option append dependent selectbox
		$cateoryIDS = $request->input('category');
		$vbCollection = VirtualBoxManager::where('category_id',$cateoryIDS)->get();
		
		$htmltxt = "<option value=''>------Select------</option>";
		$inputhidn = array();
		foreach($vbCollection->toArray() as $InsertedVal){
			//echo "<pre>";print_r($InsertedVal['id']);
			$htmltxt .= "<option value =".$InsertedVal['price_from'].'-'.$InsertedVal['price_to'].'('.$InsertedVal['id'].')'.">".$InsertedVal['price_from'].' - '.$InsertedVal['price_to']."</option>";
			$inputhidn[] = $InsertedVal['id'];
		}
		//exit;
		return response()->json(array($htmltxt,"vbids" => $inputhidn));
	}

	public function storemoveproducts(Request $request){
		$datarange = $request->input('rangeselected');
		$datacat = $request->input('catselectrange'); 
		
		$exprange = explode("-",$datarange);
		
		$priceFrom = $exprange[0];
		$priceTo = $exprange[1];
		$expvbid = explode("(",$priceTo);
		$selvbid = substr($expvbid[1], 0, strpos($expvbid[1],')'));
		
		$importcsv = $request->input('vb_importcsv');
		if (!empty($request->file('vb_importcsv'))) {
				$file = isset($_FILES['vb_importcsv']) ? $_FILES['vb_importcsv'] : '';
				$tmpName = $file['tmp_name'];
				$extension = File::extension($request->file('vb_importcsv')->getClientOriginalName());				
				if ($extension == "csv") { // If CSV file extension
					if (($handle = fopen($tmpName, 'r')) !== FALSE) {
						set_time_limit(0);
						$row = 0;
						while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
							if($data[0] == "Certificate No" || $data[0] == ''){continue;}
							$getData = $data;
							$imlod[] = implode(",",$getData);
						}
						fclose($handle);
					}
					//echo "<pre>";print_r($getData);
					// Check Product Limit
					$vbCollectionById = VirtualBoxManager::select('products_limit')->where('id',$selvbid)->first();					
					$productLimit = $vbCollectionById->products_limit;
					$finalImpld = implode("','",$imlod);
					if(count($imlod) <= $productLimit){ // Check Product Limit
						// Check Existing Certificate No
						$existCertificte = VirtualBoxManagerProduct::select('certificate_no')->whereIn('certificate_no', [DB::raw("'" . $finalImpld . "'")])->get();
						if(!empty($existCertificte->toArray())){					
							$expldarray = explode("','", $finalImpld);
							foreach($existCertificte->toArray() as $existarr){
								$getexistcerti[] = $existarr['certificate_no'];
							}
							
							$finldiff = array_diff($expldarray, $getexistcerti);
							$finalImpld = implode("','",$finldiff);
						}
						DB::setTablePrefix('');
						$prod = DB::table('catalog_product_flat_1 as e')
								->select('entity_id','certificate_no','category_id')
								->join('catalog_category_product', 'e.entity_id', '=', 'catalog_category_product.product_id')
								->where('e.status', '=', DB::raw('1'))
								->where('e.isreadytoship', '=', DB::raw('1'))
								->where('e.type_id', '=', DB::raw('"simple"'))
								->whereBetween('e.custom_price', [$priceFrom, $priceTo])
								->whereIn('e.certificate_no', [DB::raw("'" . $finalImpld . "'")])
								->orderBy('e.entity_id', 'desc')
								->groupBy('e.entity_id');
						$collection = collect($prod->get());
						$data = array();
						$i = 0;
						$user_id = Auth::user()->id;
						DB::setTablePrefix('dml_');
						
						// For Position Counter
						$vbinsertedid = VirtualBoxManagerProduct::select('position')->orderBy('position', 'DESC')->first();
						
						if(!empty($vbinsertedid)){
							$positionval = (int) $vbinsertedid->position;
						}else{
							$positionval = 0;
						}
						if(count($collection->toArray()) > 0){
							foreach($collection->toArray() as $key => $InsertProduct){
								if($InsertProduct->category_id == $datacat){
									$srno = $positionval+1;
									$data[] = array(
										"vb_id" => $selvbid,
										"product_id" => $InsertProduct->entity_id,
										"certificate_no" => $InsertProduct->certificate_no,
										"position" => $srno,
										"added_by" => $user_id,
										"created_at" => date('Y-m-d'),
										"updated_at" => date('Y-m-d')
									);
								
									$datalog[] = array(
										"vb_id" => $selvbid,
										"product_id" => $InsertProduct->entity_id,
										"certificate_no" => $InsertProduct->certificate_no,
										"action" => "ADD",
										"transaction_by" => $user_id,
										"created_at" => date('Y-m-d'),
										"updated_at" => date('Y-m-d')
									);
									$positionval++;
								}else{
									$mesaage = Config::get('constants.message.Product_Cat_Not_Match');
									return redirect()->route('virtualboxmanager.moveproducts')->withErrors($mesaage)->withInput();
								}
								
								//echo "<pre>";print_r($datalog);exit;
							}
							
							$InsertDataProduct = VirtualBoxManagerProduct::insert($data);
							$InsertDataLog = VirtualBoxManagerLog::insert($datalog);
							if(!empty($getexistcerti)){
								$displayexist = implode("','",$getexistcerti);
								$mesaage = "Remaining ".config('constants.message.CSV_Record_Added').' !!! '.config('constants.message.Certificate_No_Exist').'...'.'<a href="#" class="show_hide" data-content="toggle-text">More</a><div class="viewall"><span>'.$displayexist.'</span></div>';//$displayexist;
								
								return redirect()->route('virtualboxmanager.moveproducts')->withErrors($mesaage)->withInput();
							}else{
								$mesaage = Config::get('constants.message.CSV_Record_Added');
								return redirect()->route('virtualboxmanager.moveproducts')->with('success', $mesaage);
							}
						}else{
							$validator = Config::get('constants.message.CSV_Data_Not_Found');
							return redirect()->route('virtualboxmanager.moveproducts')->withErrors($validator)->withInput();
						}
					}else{
						$mesaage = Config::get('constants.message.CSV_data_Limit');
						return redirect()->route('virtualboxmanager.moveproducts')->withErrors($mesaage)->withInput();
					}
					
				}else{
					$rules = [
						'vb_importcsv' => 'required|mimes:csv',
					];
		
					$customMessages = [
						'required' => 'The :attribute field is required.',
					];
					$this->validate($request, $rules, $customMessages);
				}
				
		}else {
			$rules = [
				'vb_importcsv' => 'required|mimes:csv',
			];

			$customMessages = [
				'required' => 'The :attribute field is required.',
			];
			$this->validate($request, $rules, $customMessages);
		}
		
	}
	
	public function vbboxlist(){
		$vbCollections = VirtualBoxManager::orderBy('id', 'DESC')->get();
		$vbCount = VirtualBoxManager::orderBy('id', 'DESC')->count();

		return view('virtualboxmanager.vbboxlist', compact('vbCollections','vbCount'));
		
	}

	public function ajaxvb(Request $request){
		$columns = array(
			0 => 'code',
			1 => 'name',
			2 => 'price_from',
			3 => 'price_to',
			4 => 'categorie',
			5 => 'products_limit',
			6 => 'add_',
			7 => 'created_at',
			8 => 'action');
		//$vbCollectionsCount = VirtualBoxManager::orderBy('id', 'DESC')->count();
		$vbCollections =DB::select(DB::raw('SELECT vb.*,catalog_category_flat_store_1.entity_id,catalog_category_flat_store_1.name as cat_name FROM dml_vb as vb JOIN catalog_category_flat_store_1 ON vb.category_id = catalog_category_flat_store_1.entity_id 
							WHERE catalog_category_flat_store_1.parent_id = 2'));
		$totalCnt = count($vbCollections);
		$vbCollectionsCount = $totalCnt;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');
		$data = array();
		if (empty($request->input('search.value'))) {
			$vbCollections = DB::select(DB::raw('SELECT vb.*,catalog_category_flat_store_1.entity_id,catalog_category_flat_store_1.name as cat_name FROM dml_vb as vb JOIN catalog_category_flat_store_1 ON vb.category_id = catalog_category_flat_store_1.entity_id 
							WHERE catalog_category_flat_store_1.parent_id = 2 ORDER BY ' . $order . ' ' . $dir . '  LIMIT ' . $limit . ' OFFSET ' . $start . ''));
			/* echo 'SELECT vb.*,catalog_category_flat_store_1.entity_id,catalog_category_flat_store_1.name as cat_name FROM dml_vb as vb JOIN catalog_category_flat_store_1 ON vb.category_id = catalog_category_flat_store_1.entity_id 
			WHERE catalog_category_flat_store_1.parent_id = 2 ORDER BY ' . $order . ' ' . $dir . '  LIMIT ' . $limit . ' OFFSET ' . $start . '';exit; */
			$vbCollectionsCount = count($vbCollections);
		}else{
			$search = $request->input('search.value');
			$vbCollections =DB::select(DB::raw('SELECT vb.*,catalog_category_flat_store_1.entity_id,catalog_category_flat_store_1.name as cat_name FROM dml_vb as vb JOIN catalog_category_flat_store_1 ON vb.category_id = catalog_category_flat_store_1.entity_id 
							WHERE catalog_category_flat_store_1.parent_id = 2 AND catalog_category_flat_store_1.name like "%' . $search . '%" OR vb.name like "%' . $search . '%" OR vb.code like "%' . $search . '%" OR vb.price_from like "%' . $search . '%" OR vb.price_to like "%' . $search . '%" OR vb.products_limit like "%' . $search . '%" OR vb.products_limit like "%' . $search . '%" ORDER BY "' . $order . '","' . $dir . '"  LIMIT ' . $limit . ' OFFSET ' . $start . ''));
			$vbCollectionsCount = count($vbCollections);
			
		}
		//echo "<pre>";print_r($vbCollections);exit;
		
		if (!empty($vbCollections)) {
			foreach ($vbCollections as $vbvalues) {
				$vbProducts = VirtualBoxManagerProduct::where('vb_id',$vbvalues->id)->count();
				$action = "<a class='color-content table-action-style' href='javascript:void(0);'><i title='Detail' onclick='showDetail(" . $vbvalues->id . ")' class='material-icons list-icon md-18'>info</i></a>";
				$action .= ' <a class="color-content table-action-style" title="Edit" href="' . route('virtualboxmanager.editvb', ['id' => $vbvalues->id]) . '"><i class="material-icons list-icon md-18">edit</i></a>';
				$data[] = array($vbvalues->code, $vbvalues->name, $vbvalues->price_from, $vbvalues->price_to,$vbvalues->cat_name,$vbvalues->products_limit,$vbProducts,$vbvalues->created_at,$action);
			}
		}else{
			$data[] = array('', '', '', '', '', '', '', '','');
		}

		$json_data = array(
			"draw" => intval($request->input('draw')),
			"recordsTotal" => intval($totalCnt),
			"recordsFiltered" => intval($vbCollectionsCount),
			"data" => $data,
		);
		echo json_encode($json_data);
	}

	public function showDetail(Request $request)
    {
		$data = $request->all();
		$id = $data['id'];
		$i = 1;
		$DataProduct = VirtualBoxManagerProduct::where('vb_id',$id)->orderBy('id', 'DESC')->get();
		//echo "<pre>";print_r($DataProduct);exit;
		if(count($DataProduct) > 0){
			foreach($DataProduct->toArray() as $getProductIds){
				$vbprodIds[] = $getProductIds['product_id'];
			}
			
			$finlVBproId = implode("','",$vbprodIds);
			
			DB::setTablePrefix('');
			$prod = DB::table('catalog_product_flat_1 as e')
								->leftJoin('grp_stone', 'grp_stone.stone_product_id', '=', 'e.entity_id')
								->leftJoin('grp_metal', 'grp_metal.metal_product_id', '=', 'e.entity_id')
								->where('e.status', '=', DB::raw('1'))
								->where('e.isreadytoship', '=', DB::raw('1'))
								->where('e.type_id', '=', DB::raw('"simple"'))
								->whereIn('e.entity_id', [DB::raw("'" . $finlVBproId . "'")])
								->orderBy('e.entity_id', 'desc')
								->groupBy('e.entity_id');
			$collection = collect($prod->get());
			$returnHTML = view('virtualboxmanager.showDetailVb',['data'=>$collection,"i" => $i])->render();
        	return response()->json(array('success' => true, 'html'=>$returnHTML)); 
		}else{
			$mesaage = Config::get('constants.message.Product_Not_Fount');
			return response()->json(array('success' => false, 'message'=>$mesaage)); 
		}
		//echo "<pre>";print_r($collection->toArray());exit;
		
    }
}
