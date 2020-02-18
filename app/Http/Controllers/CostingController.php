<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Costing;
use App\Costingdata;
use App\Vendorstonemanage;
use App\Productupload;
use App\Vendor;
use DB;
use Excel;
use App\Helpers\CostingHelper;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet_Drawing;
use Illuminate\Support\Facades\Storage;
use Config;
use PDF;
use App\User;
use Spatie\Permission\Models\Role;
use App\Products;
use App\Helpers\ProductHelper;
use App\VendorHandlingCharges;
use Auth;
use App\ProductsMetal;
use App\ProductsStone;
use App\CatalogCategoryProduct;
use App\Setting;
use App\ShowroomOrders;
use App\ShowroomOrderProducts;

class CostingController extends Controller
{
    public function create() {
        $vendor=User::whereHas('roles', function($q){ $q->where('name', 'Vendor'); })->get();  
        return view('costing/create',compact('vendor',$vendor));
    }

    public function costinglist() {
        $costingdatas = Costingdata::whereNull('qc_status')->orderBy('id', 'DESC')->paginate(10);
        $vendor=User::whereHas('roles', function($q){ $q->where('name', 'Vendor'); })->get();
        $totalcount = Costingdata::whereNull('qc_status')->count();
        return view('costing/costinglist',array('costingdatas' => $costingdatas,'vendor' => $vendor,'totalcount' => $totalcount));
    }

    public function qcaccept() {
        $costingdatas = Costingdata::where('qc_status',1)->where('request_invoice','!=' , 1)->orderBy('id', 'DESC')->paginate(10);
        $totalcount = Costingdata::where('qc_status',1)->where('request_invoice','!=' , 1)->count();
        return view('costing/qcaccept',array('costingdatas' => $costingdatas,'totalcount' => $totalcount));
    }

    public function qcreject() {
        $costingdatas = Costingdata::where('qc_status',0)->where('return_memo','!=' , 1)->orderBy('id', 'DESC')->paginate(10);
        $totalcount = Costingdata::where('qc_status',0)->where('return_memo','!=' , 1)->count();
        return view('costing/qcreject',array('costingdatas' => $costingdatas,'totalcount' => $totalcount));
    }

    public function costinglog(request $request) {
        $costingdatas = Costing::orderBy('id', 'DESC')->paginate(10);
        $totalcount = Costing::count();
        $vendor=User::whereHas('roles', function($q){ $q->where('name', 'Vendor'); })->get();
        return view('costing/costinglog',array('costingdatas' => $costingdatas,'vendor' => $vendor,'totalcount' => $totalcount));
    }

    //Generate Certificate While IGI
    public function generateCertificate(request $request) {
        $data =  $request->all();
        $chkCostingIds = $data['chkCostingIds'];
        $certificate_petten1 = $data['certificate_petten1'];
        $certificate_petten2 = $data['certificate_petten2'];
        $certificate_petten3 = $data['certificate_petten3'];
        $yearVar = date("y");
        $month = date("m");
        $dataVar = $yearVar.$month;  
        $certificate_petten4 = $dataVar;
        $branding = $data['branding'];
        $i = 0;
        $chkCostingIds = explode(",",$chkCostingIds);
        $igiVar = 1;
        $message = "";
        $igiCertificateArry = array();
        if(!empty($chkCostingIds)) {
            if(count($chkCostingIds) > 1) {
                foreach ($chkCostingIds as $chkCostingId) {
                    $costingColl = Costingdata::find($chkCostingId);
                    if($costingColl['is_igi'] == 1) {
                        $igiVar = 0;
                        $igiCertificateArry[] = $costingColl['certificate_no'];
                    }
                }
            }
            else {
                $costingColl = Costingdata::find($chkCostingIds)->first();
                if($costingColl['is_igi'] == 1) {
                    $igiVar = 0;
                    $igiCertificateArry[] = $costingColl['certificate_no'];
                }
            }
            
            
            $igiCertificateArry = implode(",",$igiCertificateArry); 
            $igiCertificateArry = rtrim($igiCertificateArry);
            $igi_certified = $igiCertificateArry.Config::get('constants.message.igied_already');
            if(!empty($igiCertificateArry)) {
                return json_encode(array('igi_certified' =>$igi_certified,'status' => 'error'));
            }
            if($igiVar != 0) {
                if(count($chkCostingIds) > 1) {
                    foreach ($chkCostingIds as $chkCostingId) {
                        $costingColl = Costingdata::find($chkCostingId);
                        $certificate_petten_var = $certificate_petten1.($certificate_petten2 + $i).$certificate_petten3.$certificate_petten4;
                        $costingColl['certificate_no'] =$certificate_petten_var;
                        $costingColl['branding'] = $branding;
                        $costingColl['igi_by']= Auth::id();
                        $costingColl['is_igi'] = 1;
                        $costingColl->save();
                        $i++;
                    }
                }
                else {

                    $costingColl = Costingdata::find($chkCostingIds)->first();
                    $certificate_petten_var = $certificate_petten1.$certificate_petten2.$certificate_petten3.$certificate_petten4;
                    $costingColl['certificate_no'] =$certificate_petten_var;
                    $costingColl['branding'] = $branding;
                    $costingColl['igi_by']= Auth::id();
                    $costingColl['is_igi'] = 1;
                    $costingColl->save();
                }
            $message = Config::get('constants.message.IGI_added_success');
            return json_encode(array("message" => $message,'status' => 'success'));
            }
            
        }
    }


    //Generate igi popup
    public function generateIGI(request $request) {
        $data = $request->all();
        $chkCostingIds = $data['chkCostingIds'];
        $returnHTML = view('costing.generateIGI',['chkCostingIds'=>$chkCostingIds])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML)); 
    }

    //Show Costing Product Detail
    public function showDetail(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        $certificateArr = array();
        $productCollection = ShowroomOrderProducts::get();
        foreach ($productCollection as $productColl) {
            $certificateArr[] = $productColl->certificate;
        }
        $returnHTML = view('costing.showDetail',['id'=>$id,'certificateArr'=>$certificateArr])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML)); 
    }

    public function  index(Request $request)  {
        
        $getdata = $request->all();
        $data['stone_shape'] = DB::select( DB::raw("SELECT `main_table`.*, `eav_op_shape`.*, `eav_attr_stoneshape`.`value` AS `stone_shape` FROM `grp_stone_manage` AS `main_table` LEFT JOIN `eav_attribute_option` AS `eav_op_shape` ON eav_op_shape.option_id = main_table.stone_shape LEFT JOIN `eav_attribute_option_value` AS `eav_attr_stoneshape` ON eav_attr_stoneshape.option_id=main_table.stone_shape AND eav_op_shape.attribute_id=141 GROUP BY `main_table`.`stone_shape`") );

        $data['stone_clarity'] = DB::select( DB::raw("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0") );
        $data['stone_carat'] = DB::table(DB::raw('grp_stone_manage'))->groupBy("stone_carat_from")->get();
        $data['loadjobwork']= $getdata['id'];
        $data['vendor_id']= $getdata['vendor_id'];
        $returnHTML = view('costing.loadjobwork',['data'=>$data])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML));
    }

    public function exportpdf(Request $request) {

        $requestdata = $request->all();
        
        if(!empty($requestdata['estimationcatalogid'])) 
        { 
           $estimationcatalogid =  $requestdata['estimationcatalogid']; 
        } 
        else { 
           $estimationcatalogid =  "0";
        } 
        if(!empty($requestdata['chkcosting'])) { $chkcosting = $requestdata['chkcosting'];  } else { $chkcosting = "0"; } 
        if(!empty($requestdata['vendor_id'])) { $vendor_id = $requestdata['vendor_id'];  } else { $vendor_id = "0"; } 
        $data = ['estimationcatalogid' => $estimationcatalogid,'chkcosting' => $chkcosting,'vendors' => $vendor_id ];
        $customPaper = array(0,0,720,1440);
        $pdf = PDF::loadView('costing/exportpdf', $data)->setPaper($customPaper, 'landscape');
        return $pdf->download('costing.pdf');
    }

    public function invoicepdf(request $request) {
        $requestdata= $request->all();
        if(!is_array($requestdata['id'])) {
            $chkcosting[] = $requestdata['id'];
        }
        else {
            $chkcosting = $requestdata['id'];
        }
        //if(!empty($requestdata['id'])) { $chkcosting = $requestdata['id'];  } else { $chkcosting = "0"; }
        //$data = ['chkcosting' => $chkcosting];
        $customPaper = array(0,0,720,1440);
        $file_path = 'uploads/invoice';
        if (!file_exists($file_path)) {
            mkdir($file_path);
        }
        $collection = Costingdata::whereIn('id',$chkcosting)->get(); 
        $qty = 0;
        foreach ($collection as $key => $coll) {
            $costing_id = $coll->costingdata_id;
            $vendor_id[] = Costing::where('costing_id',$costing_id)->select('vendor_id')->pluck('vendor_id')->first();
            $diamond_weight[] = $coll->diamond_weight;
            $metal_weight[] = $coll->metal_weight;
            $qty++;
        }
        /*
        echo "<pre>"; print_r($product_category); 
        exit;*/

        
        $configData = Setting::where('key', config('constants.settings.keys.qc_approval_voucher_no'))->first('value');
        $qc_approval_voucher_no = $configData->value;

        $vendorArr = array_unique($vendor_id);
        foreach ($vendorArr as $key => $vendorElem) {
            $getname = User::select('name', 'gstin', 'state','address')->where('id',$vendorElem)->get();
            $name = $getname[0]->name;
            $address = $getname[0]->address;
            $gstin = $getname[0]->gstin;
            $state = $getname[0]->state;
            $date = date('Y/m/d');
            $data = ['name'=>$name,'address'=>$address,'gstin'=>$gstin,'state'=>$state,'date'=> $date,'qty' => $qty,'diamond_weight'=>array_sum($diamond_weight),'metal_weight' => array_sum($metal_weight),'qc_approval_voucher_no' => $qc_approval_voucher_no];
            $pdf = PDF::loadView('costing/invoicepdf', $data)->setPaper($customPaper, 'A4');
            $path = public_path('uploads/invoice/');
            $pdf->save($path.'costing_'.time().'.pdf');    
            
            $search_voucher = Setting::where('key', config('constants.settings.keys.qc_approval_voucher_no'))->first()->value;
            $new_voucher = (int) $search_voucher + 1;
            $nid = Setting::select('id')->where('key', config('constants.settings.keys.qc_approval_voucher_no'))->get();
            $setting = Setting::find($nid[0]->id);
            $setting->value = $new_voucher;
            $setting->update();
            return redirect('costing/qcrequestinvoice');
        }
    }

    public function memopdf(request $request) {
        /*$requestdata= $request->all();
        if(!empty($requestdata['id'])) { $chkcosting = $requestdata['id'];  } else { $chkcosting = "0"; }
        $data = ['chkcosting' => $chkcosting];
        $customPaper = array(0,0,720,1440);
        $file_path = 'uploads/memo';
        if (!file_exists($file_path)) {
            mkdir($file_path);
        }
        $pdf = PDF::loadView('costing/memopdf', $data)->setPaper($customPaper, 'landscape');
        $path = public_path('uploads/memo/');
        $pdf->save($path.'costing_'.time().'.pdf');
        return $pdf->download('costing.pdf');*/

        $data = $request->all();
        $vendors = $data['vendor_id'];
        $estimationcatalogid = 'catalog';//$data['estimationcatalogid'];
        $chkcosting = (!empty($data['chkcosting'])) ? $data['chkcosting'] : "0";
        $costingdatas = Costing::where('vendor_id',$vendors)->get();
        if(count($costingdatas) > 0) {
            foreach ($costingdatas as $costingdata) {
                $costing_ids[] =  $costingdata->costing_id;
            }
        }
        else {
            $costing_ids[] = 0;   
        }
        
        if(!empty($chkcosting) &&  (empty($vendors) || $vendors == 0) ) {
            $collection = Costingdata::whereIn('id', $chkcosting )->get();
        }
        
        if(!empty($vendors) &&  empty($chkcosting)) {
            $collection = Costingdata::whereIn('costingdata_id', $costing_ids )->get();
        }

        if(!empty($chkcosting) &&  !empty($vendors)) {
            $collection = Costingdata::whereIn('costingdata_id', $costing_ids )->whereIn('id', $chkcosting )->get();
        }
        if(empty($chkcosting) &&  (empty($vendors) || $vendors == 0)) {
            $collection = Costingdata::all();
        }

     if(count($collection) > 0) {

            if($estimationcatalogid == 'catalog')
            {
                $serialnumber=0;
                $tmparray =array("Sr No.","Color or Hand Design","Item#","Style#","Metal Karat","Color","Product Category","Gross Wt","Total Diamond Pcs","Total Diamond Wt.","Total Color Stone Pcs","Total Color Stone Wt","Material Category","Material Type","Material Inter. Quality","Material MM Size","Material pices","Material Weight");
                $sheet =array($tmparray);
                foreach ($collection as $coll) 
                {
                    $tmparray =array();
                    $serialnumber = $serialnumber + 1;
                    array_push($tmparray,$serialnumber);

                    $images =$coll->image;
                    array_push($tmparray,$images);

                    $item = $coll->item;
                    array_push($tmparray,$item);
                    $style = $coll->style;
                    array_push($tmparray,$style);   
                    $metalkarat = $coll->metal_karat;
                    array_push($tmparray,$metalkarat);   
                    $color = $coll->color;
                    array_push($tmparray,$color); 
                    $product_category = $coll->product_category;
                    array_push($tmparray,$product_category);     
                    $gross_weight = $coll->gross_weight;
                    array_push($tmparray,$gross_weight);   
                    $total_diamondpcs = $coll->diamond_pcs;
                    array_push($tmparray,$total_diamondpcs);   
                    $total_diamondwt = $coll->diamond_weight;
                    array_push($tmparray,$total_diamondwt);   
                    $colorstone_pcs = $coll->colorstone_pcs;
                    array_push($tmparray,$colorstone_pcs); 
                    $colorstone_weight = $coll->colorstone_weight;
                    array_push($tmparray,$colorstone_weight); 
                    $material_category = $coll->material_category;
                    array_push($tmparray,$material_category);       
                    $material_type = $coll->material_type;
                    array_push($tmparray,$material_type);   
                    $material_quality = $coll->material_quality;
                    array_push($tmparray,$material_quality);   

                    $material_mm_size = $coll->material_mm_size;
                    array_push($tmparray,$material_mm_size);   
                    $material_pcs = $coll->material_pcs;
                    array_push($tmparray,$material_pcs); 
                    $metal_weight = $coll->material_weight;
                    array_push($tmparray,$metal_weight);     
                    array_push($sheet,$tmparray);
                }

                $newSheetArr = array();
        
                foreach ($sheet as $sheetkey => $rowData)
                {
                    $forcount = 0;
                    $newColumnsArr = array();
                    $totalColumns = count($rowData);
                    $commaColumnsNum = 0;
                    $commaColumnsArr = array();
                    foreach ($rowData as $colKey => $colValue) 
                    {
                        if(strpos($colValue, ',') !== false){
                            $colValues = explode(',', $colValue);
                            $newColumnsArr[] = $colValues[0];
                            $multiple_value_key[] = $colKey;
                            $commaColumnsArr[$commaColumnsNum] = $colValues;
                            $commaColumnsNum++;
                            $forcount = count($colValues);
                        } else {
                            $newColumnsArr[] = $colValue;
                        }
                        
                        if($colKey == ($totalColumns-1)) {
                            if(count(array_filter($newColumnsArr)) > 0){ // for blank td
                                $newSheetArr[] = $newColumnsArr;
                            }
                            if($commaColumnsNum > 1) {
                                for($col_j=0;$col_j<$forcount;$col_j++)
                                {
                                    if($col_j == 0) continue;
                                    $newDynColumnsArr = array();
                                    $counterCol = 0;
                                    for($col_i=0;$col_i<$totalColumns;$col_i++){
                                        if(in_array($col_i, $multiple_value_key)){
                                          $newDynColumnsArr[] = $commaColumnsArr[$counterCol][$col_j];
                                          $counterCol++;
                                        } else {
                                          $newDynColumnsArr[] = '';
                                        }
                                    }
                                    if(count(array_filter($newDynColumnsArr)) > 0){ // for blank td
                                        $newSheetArr[] = $newDynColumnsArr;
                                    }
                                }
                            }
                        }
                    } 
                }
            }
        
            
            if($estimationcatalogid == 'estimation') {
                $serialnumber=0;
                $tmparray =array("Sr No.","Color or Hand Design","Item#","Style#","Metal Karat","Color","Product Category","Gross Wt","Metal Wt","Metal Rate","Metal Amount","Labour Charge","Labour Amount","Total Diamond Pcs","Total Diamond Wt.","Total Color Stone Pcs","Total Color Stone Wt","Material Category","Material Type","Material Inter. Quality","Material MM Size","Material pices","Material Weight","Stone Rate","Stone Amount","Total Stone Amount","Total Amount");
                $sheet =array($tmparray);
                foreach ($collection as $coll) {
                    $MetalWeight = $coll->metal_weight;
                    $Color = $coll->color;
                    $ProductCategory = $coll->product_category;
                    $Diamondtype = $coll->material_type;
                    $MetalKarat = $coll->metal_karat;
                    $CalculatedLabourCharge = CostingHelper::getDiamondMelaLabourPrice($MetalWeight,$ProductCategory,$Diamondtype,$Color);
                    $CalculatedMetalCharge = CostingHelper::getDiamondMelaMetalPrice($Color,$MetalKarat);
                    $MaterialWeight =$coll->material_weight;
                    $MaterialInterQuality = $coll->material_quality;
                    $MaterialType = $coll->material_type;
                    $MaterialPcs = $coll->material_pcs;
                    $SeiveSize = explode(",",$coll->seive_size);
                    $cntwt  = explode(",",$coll->material_weight);
                    $Count = count($cntwt);
                    $CalculatedStoneRate  = CostingHelper::getDiamondMelaStonePrice($MaterialWeight,$MaterialInterQuality,$MaterialType,$Count,$MaterialPcs);
                    $tmparray =array();
                    $serialnumber = $serialnumber + 1;
                    array_push($tmparray,$serialnumber);
                    $images =$coll->image;
                    array_push($tmparray,$images);
                    $item = $coll->item;
                    array_push($tmparray,$item);
                    $style = $coll->style;
                    array_push($tmparray,$style);   
                    $metalkarat = $coll->metal_karat;
                    array_push($tmparray,$metalkarat);   
                    $color = $coll->color;
                    array_push($tmparray,$color); 
                    $product_category = $coll->product_category;
                    array_push($tmparray,$product_category);     
                    $gross_weight = $coll->gross_weight;
                    array_push($tmparray,$gross_weight);
                    $metal_weight = $coll->metal_weight;
                    array_push($tmparray,$metal_weight);
                    $metalrate = $CalculatedMetalCharge;
                    array_push($tmparray,$metalrate);
                    $metalamount = round($metal_weight * $CalculatedMetalCharge);
                    array_push($tmparray,$metalamount);
                    $VendorCharge = $CalculatedLabourCharge;
                    array_push($tmparray,$VendorCharge);
                    if($metal_weight <= 1)
                    { 
                      $fnMwt = 1;
                      $labouramount = round($fnMwt * $CalculatedLabourCharge);
                    } 
                    else
                    {
                      $fnMwt = $metal_weight;
                      $labouramount = round($fnMwt * $CalculatedLabourCharge);
                    }
                    array_push($tmparray,$labouramount);
                    $total_diamondpcs = $coll->diamond_pcs;
                    array_push($tmparray,$total_diamondpcs);   
                    $total_diamondwt = $coll->diamond_weight;
                    array_push($tmparray,$total_diamondwt);   
                    $colorstone_pcs = $coll->colorstone_pcs;
                    array_push($tmparray,$colorstone_pcs); 
                    $colorstone_weight = $coll->colorstone_weight;
                    array_push($tmparray,$colorstone_weight); 
                    $material_category = $coll->material_category;
                    array_push($tmparray,$material_category);       
                    $material_type = $coll->material_type;
                    array_push($tmparray,$material_type);   
                    $material_quality = $coll->material_quality;
                    array_push($tmparray,$material_quality);   
                    $material_mm_size = $coll->material_mm_size;
                    array_push($tmparray,$material_mm_size);   
                    $material_pcs = $coll->material_pcs;
                    array_push($tmparray,$material_pcs); 
                    $material_weight = $coll->material_weight;
                    array_push($tmparray,$material_weight); 
                    $stone_rate = $CalculatedStoneRate['data'];
                    array_push($tmparray,$stone_rate);     
                    $stone_amt = $CalculatedStoneRate['dataamt'];
                    array_push($tmparray,$stone_amt);     
                    $totalstoneamtsum = $CalculatedStoneRate['totalstoneamtsum'];
                    array_push($tmparray,$totalstoneamtsum);
                    $total_amount = $metalamount + $labouramount + $totalstoneamtsum;
                    array_push($tmparray,$total_amount);
                    array_push($sheet,$tmparray);
                }
                
                $newSheetArr = array();
                foreach ($sheet as $sheetkey => $rowData) {
                    $newColumnsArr = array();
                    $totalColumns = count($rowData);
                    $commaColumnsNum = 0;
                    $commaColumnsArr = array();
                    $forcount = 0;
                    foreach ($rowData as $colKey => $colValue) {
                        if(strpos($colValue, ',') !== false){
                            $colValues = explode(',', $colValue);
                            $newColumnsArr[] = $colValues[0];
                            $multiple_value_key[] = $colKey;
                            $commaColumnsArr[$commaColumnsNum] = $colValues;
                            $commaColumnsNum++;
                            $forcount = count($colValues);
                        } 
                        else {
                            $newColumnsArr[] = $colValue;
                        }
                        if($colKey == ($totalColumns-1)){
                            if(count(array_filter($newColumnsArr)) > 0) { 
                                $newSheetArr[] = $newColumnsArr;
                            }
                            if($commaColumnsNum > 1) {
                                for($col_j=0;$col_j<$forcount;$col_j++) {
                                    if($col_j == 0) continue;
                                        $newDynColumnsArr = array();
                                        $counterCol = 0;
                                        for($col_i=0;$col_i<$totalColumns;$col_i++){
                                            if(in_array($col_i, $multiple_value_key)){
                                                $newDynColumnsArr[] = $commaColumnsArr[$counterCol][$col_j];
                                                $counterCol++;
                                            }
                                            else {
                                                $newDynColumnsArr[] = '';
                                            }
                                        }
                                        if(count(array_filter($newDynColumnsArr)) > 0){ // for blank td
                                            $newSheetArr[] = $newDynColumnsArr;
                                        }
                                }
                            }
                        }
                    } 
                }
            }
        
            $type = "xlsx";
            return Excel::create('costing', function($excel) use ($newSheetArr) {
                $excel->sheet('Sheet', function($sheet) use ($newSheetArr)
                {

                    foreach($newSheetArr as $row => $columns) {
                        foreach($columns as $column => $data) {  
                            if(strpos($data, 'img/') !== false) {
                                $objDrawing = new PHPExcel_Worksheet_Drawing();
                                $objDrawing->setName('costing_img');
                                $objDrawing->setDescription('costing_img');
                                $objDrawing->setPath($data);
                                $rowNo = (int)$row+2;
                                $objDrawing->setCoordinates('B'.$rowNo);
                                $objDrawing->setOffsetX(5); 
                                $objDrawing->setOffsetY(5);                
                                $objDrawing->setWidth(80); 
                                $objDrawing->setHeight(80); 
                                $objDrawing->setWorksheet($sheet);
                                $sheet->getRowDimension($rowNo)->setRowHeight(70);
                                $sheet->getColumnDimension('B')->setWidth(40);

                                $newSheetArr[$row][1] = '';
                            }
                        }
                    }
                    $sheet->fromArray($newSheetArr);
                });
            })->download($type);
           
        }
        else {
            $newSheetArr = "There are no products.";
            return redirect('costing/costinglist')->with('error', $newSheetArr);  
        }

    }

    public function exportexcel(Request $request) {

        $data = $request->all();
        $vendors = $data['vendor_id'];
        $estimationcatalogid = $data['estimationcatalogid'];
        $chkcosting = (!empty($data['chkcosting'])) ? $data['chkcosting'] : "0";
        $costingdatas = Costing::where('vendor_id',$vendors)->get();
        if(count($costingdatas) > 0) {
            foreach ($costingdatas as $costingdata) {
                $costing_ids[] =  $costingdata->costing_id;
            }
        }
        else {
            $costing_ids[] = 0;   
        }
        
        if(!empty($chkcosting) &&  (empty($vendors) || $vendors == 0) ) {
            $collection = Costingdata::whereIn('id', $chkcosting )->get();
        }
        
        if(!empty($vendors) &&  empty($chkcosting)) {
            $collection = Costingdata::whereIn('costingdata_id', $costing_ids )->get();
        }

        if(!empty($chkcosting) &&  !empty($vendors)) {
            $collection = Costingdata::whereIn('costingdata_id', $costing_ids )->whereIn('id', $chkcosting )->get();
        }
        if(empty($chkcosting) &&  (empty($vendors) || $vendors == 0)) {
            $collection = Costingdata::all();
        }

     if(count($collection) > 0) {

            if($estimationcatalogid == 'catalog')
            {
                $serialnumber=0;
                $tmparray =array("Sr No.","Color or Hand Design","Item#","Style#","Metal Karat","Color","Product Category","Gross Wt","Total Diamond Pcs","Total Diamond Wt.","Total Color Stone Pcs","Total Color Stone Wt","Material Category","Material Type","Material Inter. Quality","Material MM Size","Material pices","Material Weight");
                $sheet =array($tmparray);
                foreach ($collection as $coll) 
                {
                    $tmparray =array();
                    $serialnumber = $serialnumber + 1;
                    array_push($tmparray,$serialnumber);

                    $images =$coll->image;
                    array_push($tmparray,$images);

                    $item = $coll->item;
                    array_push($tmparray,$item);
                    $style = $coll->style;
                    array_push($tmparray,$style);   
                    $metalkarat = $coll->metal_karat;
                    array_push($tmparray,$metalkarat);   
                    $color = $coll->color;
                    array_push($tmparray,$color); 
                    $product_category = $coll->product_category;
                    array_push($tmparray,$product_category);     
                    $gross_weight = $coll->gross_weight;
                    array_push($tmparray,$gross_weight);   
                    $total_diamondpcs = $coll->diamond_pcs;
                    array_push($tmparray,$total_diamondpcs);   
                    $total_diamondwt = $coll->diamond_weight;
                    array_push($tmparray,$total_diamondwt);   
                    $colorstone_pcs = $coll->colorstone_pcs;
                    array_push($tmparray,$colorstone_pcs); 
                    $colorstone_weight = $coll->colorstone_weight;
                    array_push($tmparray,$colorstone_weight); 
                    $material_category = $coll->material_category;
                    array_push($tmparray,$material_category);       
                    $material_type = $coll->material_type;
                    array_push($tmparray,$material_type);   
                    $material_quality = $coll->material_quality;
                    array_push($tmparray,$material_quality);   

                    $material_mm_size = $coll->material_mm_size;
                    array_push($tmparray,$material_mm_size);   
                    $material_pcs = $coll->material_pcs;
                    array_push($tmparray,$material_pcs); 
                    $metal_weight = $coll->material_weight;
                    array_push($tmparray,$metal_weight);     
                    array_push($sheet,$tmparray);
                }

                $newSheetArr = array();
        
                foreach ($sheet as $sheetkey => $rowData)
                {
                    $forcount = 0;
                    $newColumnsArr = array();
                    $totalColumns = count($rowData);
                    $commaColumnsNum = 0;
                    $commaColumnsArr = array();
                    foreach ($rowData as $colKey => $colValue) 
                    {
                        if(strpos($colValue, ',') !== false){
                            $colValues = explode(',', $colValue);
                            $newColumnsArr[] = $colValues[0];
                            $multiple_value_key[] = $colKey;
                            $commaColumnsArr[$commaColumnsNum] = $colValues;
                            $commaColumnsNum++;
                            $forcount = count($colValues);
                        } else {
                            $newColumnsArr[] = $colValue;
                        }
                        
                        if($colKey == ($totalColumns-1)) {
                            if(count(array_filter($newColumnsArr)) > 0){ // for blank td
                                $newSheetArr[] = $newColumnsArr;
                            }
                            if($commaColumnsNum > 1) {
                                for($col_j=0;$col_j<$forcount;$col_j++)
                                {
                                    if($col_j == 0) continue;
                                    $newDynColumnsArr = array();
                                    $counterCol = 0;
                                    for($col_i=0;$col_i<$totalColumns;$col_i++){
                                        if(in_array($col_i, $multiple_value_key)){
                                          $newDynColumnsArr[] = $commaColumnsArr[$counterCol][$col_j];
                                          $counterCol++;
                                        } else {
                                          $newDynColumnsArr[] = '';
                                        }
                                    }
                                    if(count(array_filter($newDynColumnsArr)) > 0){ // for blank td
                                        $newSheetArr[] = $newDynColumnsArr;
                                    }
                                }
                            }
                        }
                    } 
                }
            }
        
            
            if($estimationcatalogid == 'estimation') {
                $serialnumber=0;
                $tmparray =array("Sr No.","Color or Hand Design","Item#","Style#","Metal Karat","Color","Product Category","Gross Wt","Metal Wt","Metal Rate","Metal Amount","Labour Charge","Labour Amount","Total Diamond Pcs","Total Diamond Wt.","Total Color Stone Pcs","Total Color Stone Wt","Material Category","Material Type","Material Inter. Quality","Material MM Size","Material pices","Material Weight","Stone Rate","Stone Amount","Total Stone Amount","Total Amount");
                $sheet =array($tmparray);
                foreach ($collection as $coll) {
                    $MetalWeight = $coll->metal_weight;
                    $Color = $coll->color;
                    $ProductCategory = $coll->product_category;
                    $Diamondtype = $coll->material_type;
                    $MetalKarat = $coll->metal_karat;
                    $CalculatedLabourCharge = CostingHelper::getDiamondMelaLabourPrice($MetalWeight,$ProductCategory,$Diamondtype,$Color);
                    $CalculatedMetalCharge = CostingHelper::getDiamondMelaMetalPrice($Color,$MetalKarat);
                    $MaterialWeight =$coll->material_weight;
                    $MaterialInterQuality = $coll->material_quality;
                    $MaterialType = $coll->material_type;
                    $MaterialPcs = $coll->material_pcs;
                    $SeiveSize = explode(",",$coll->seive_size);
                    $cntwt  = explode(",",$coll->material_weight);
                    $Count = count($cntwt);
                    $CalculatedStoneRate  = CostingHelper::getDiamondMelaStonePrice($MaterialWeight,$MaterialInterQuality,$MaterialType,$Count,$MaterialPcs);
                    $tmparray =array();
                    $serialnumber = $serialnumber + 1;
                    array_push($tmparray,$serialnumber);
                    $images =$coll->image;
                    array_push($tmparray,$images);
                    $item = $coll->item;
                    array_push($tmparray,$item);
                    $style = $coll->style;
                    array_push($tmparray,$style);   
                    $metalkarat = $coll->metal_karat;
                    array_push($tmparray,$metalkarat);   
                    $color = $coll->color;
                    array_push($tmparray,$color); 
                    $product_category = $coll->product_category;
                    array_push($tmparray,$product_category);     
                    $gross_weight = $coll->gross_weight;
                    array_push($tmparray,$gross_weight);
                    $metal_weight = $coll->metal_weight;
                    array_push($tmparray,$metal_weight);
                    $metalrate = $CalculatedMetalCharge;
                    array_push($tmparray,$metalrate);
                    $metalamount = round($metal_weight * $CalculatedMetalCharge);
                    array_push($tmparray,$metalamount);
                    $VendorCharge = $CalculatedLabourCharge;
                    array_push($tmparray,$VendorCharge);
                    if($metal_weight <= 1)
                    { 
                      $fnMwt = 1;
                      $labouramount = round($fnMwt * $CalculatedLabourCharge);
                    } 
                    else
                    {
                      $fnMwt = $metal_weight;
                      $labouramount = round($fnMwt * $CalculatedLabourCharge);
                    }
                    array_push($tmparray,$labouramount);
                    $total_diamondpcs = $coll->diamond_pcs;
                    array_push($tmparray,$total_diamondpcs);   
                    $total_diamondwt = $coll->diamond_weight;
                    array_push($tmparray,$total_diamondwt);   
                    $colorstone_pcs = $coll->colorstone_pcs;
                    array_push($tmparray,$colorstone_pcs); 
                    $colorstone_weight = $coll->colorstone_weight;
                    array_push($tmparray,$colorstone_weight); 
                    $material_category = $coll->material_category;
                    array_push($tmparray,$material_category);       
                    $material_type = $coll->material_type;
                    array_push($tmparray,$material_type);   
                    $material_quality = $coll->material_quality;
                    array_push($tmparray,$material_quality);   
                    $material_mm_size = $coll->material_mm_size;
                    array_push($tmparray,$material_mm_size);   
                    $material_pcs = $coll->material_pcs;
                    array_push($tmparray,$material_pcs); 
                    $material_weight = $coll->material_weight;
                    array_push($tmparray,$material_weight); 
                    $stone_rate = $CalculatedStoneRate['data'];
                    array_push($tmparray,$stone_rate);     
                    $stone_amt = $CalculatedStoneRate['dataamt'];
                    array_push($tmparray,$stone_amt);     
                    $totalstoneamtsum = $CalculatedStoneRate['totalstoneamtsum'];
                    array_push($tmparray,$totalstoneamtsum);
                    $total_amount = $metalamount + $labouramount + $totalstoneamtsum;
                    array_push($tmparray,$total_amount);
                    array_push($sheet,$tmparray);
                }
                
                $newSheetArr = array();
                foreach ($sheet as $sheetkey => $rowData) {
                    $newColumnsArr = array();
                    $totalColumns = count($rowData);
                    $commaColumnsNum = 0;
                    $commaColumnsArr = array();
                    $forcount = 0;
                    foreach ($rowData as $colKey => $colValue) {
                        if(strpos($colValue, ',') !== false){
                            $colValues = explode(',', $colValue);
                            $newColumnsArr[] = $colValues[0];
                            $multiple_value_key[] = $colKey;
                            $commaColumnsArr[$commaColumnsNum] = $colValues;
                            $commaColumnsNum++;
                            $forcount = count($colValues);
                        } 
                        else {
                            $newColumnsArr[] = $colValue;
                        }
                        if($colKey == ($totalColumns-1)){
                            if(count(array_filter($newColumnsArr)) > 0) { 
                                $newSheetArr[] = $newColumnsArr;
                            }
                            if($commaColumnsNum > 1) {
                                for($col_j=0;$col_j<$forcount;$col_j++) {
                                    if($col_j == 0) continue;
                                        $newDynColumnsArr = array();
                                        $counterCol = 0;
                                        for($col_i=0;$col_i<$totalColumns;$col_i++){
                                            if(in_array($col_i, $multiple_value_key)){
                                                $newDynColumnsArr[] = $commaColumnsArr[$counterCol][$col_j];
                                                $counterCol++;
                                            }
                                            else {
                                                $newDynColumnsArr[] = '';
                                            }
                                        }
                                        if(count(array_filter($newDynColumnsArr)) > 0){ // for blank td
                                            $newSheetArr[] = $newDynColumnsArr;
                                        }
                                }
                            }
                        }
                    } 
                }
            }
        
            $type = "xlsx";
            return Excel::create('costing', function($excel) use ($newSheetArr) {
                $excel->sheet('Sheet', function($sheet) use ($newSheetArr)
                {

                    foreach($newSheetArr as $row => $columns) {
                        foreach($columns as $column => $data) {  
                            if(strpos($data, 'img/') !== false) {
                                $objDrawing = new PHPExcel_Worksheet_Drawing();
                                $objDrawing->setName('costing_img');
                                $objDrawing->setDescription('costing_img');
                                $objDrawing->setPath($data);
                                $rowNo = (int)$row+2;
                                $objDrawing->setCoordinates('B'.$rowNo);
                                $objDrawing->setOffsetX(5); 
                                $objDrawing->setOffsetY(5);                
                                $objDrawing->setWidth(80); 
                                $objDrawing->setHeight(80); 
                                $objDrawing->setWorksheet($sheet);
                                $sheet->getRowDimension($rowNo)->setRowHeight(70);
                                $sheet->getColumnDimension('B')->setWidth(40);

                                $newSheetArr[$row][1] = '';
                            }
                        }
                    }
                    $sheet->fromArray($newSheetArr);
                });
            })->download($type);
           
        }
        else {
            $newSheetArr = "There are no products.";
            return redirect('costing/costinglist')->with('error', $newSheetArr);  
        }

       

    }

    public function loadvendorstonehtml(Request $request) {
        $StoneData=  array();
        $data = $request->all();
        $strVars = explode('&', $data['stonedata']);
        foreach ($strVars as $varrr) {
            $varArr = explode('=', $varrr);
            $variName  = preg_replace('/[^a-zA-Z0-9_ -]/s','',urldecode($varArr[0]));
            if($variName != '_token'){
                $variVal = $varArr[1];
                $StoneData[$variName][] = $variVal;
            } else {
                $variVal = $varArr[1];
                $StoneData[$variName] = $variVal;
            }
        }

        $vendor_id = $data['vendor_id'];
        $VendorStoneClarity = $StoneData['diamond_quality'];
        $VendorStoneFromTo =  $StoneData['stone_carat'];
        $VendorStoneShapeOption = $StoneData['stone_shape'];
        $VendorStonePrice = $StoneData['diamond_gold_price'];
        $VendorStoneColor = "";
        
        foreach ($VendorStoneFromTo as $VendorStoneFromToKey => $VendorStoneFromToColl) {
            $VendorFrom[] = explode("-",$VendorStoneFromToColl);  
        }
        $finalVenFromArr = array();
        $finalVenToArr = array();
        foreach ($VendorFrom as $VendorFromKey => $VendorFromColl) {
          $finalVenFromArr[] = $VendorFrom[$VendorFromKey][0];
          $finalVenToArr[] = $VendorFrom[$VendorFromKey][1];
         }
      
      $Count = count($VendorStonePrice);

      for($i = 0;$i<$Count;$i++)
      {
            /*Update Record if all selected criarea are same*/
            $rows = Vendorstonemanage::where([ ['stone_shape', '=', $VendorStoneShapeOption[$i] ], ['stone_clarity', '=', $VendorStoneClarity[$i] ],['stone_carat_from', '<=', $finalVenFromArr[$i] ],['stone_carat_to', '>=', $finalVenFromArr[$i] ], ['stone_carat_to', '=', $finalVenToArr[$i] ], ['vendor_id', '=', $vendor_id], ])->get();

            if(count($rows) > 0)
            {   
                $row = $rows[0];
                $StoneManageId= $row->id;
                if(!empty($VendorStonePrice[$i])) {
                    $query =Vendorstonemanage::where('id',$StoneManageId)->first();
                    $query->stone_price = $VendorStonePrice[$i];
                    $query->save();
                }
                $Msg = "Updated";
            }
            else
            {
                /* Save Record */
                $query = new Vendorstonemanage;
                $query->stone_shape = $VendorStoneShapeOption[$i];
                $query->stone_clarity = $VendorStoneClarity[$i];
                $query->stone_color = $VendorStoneColor;
                $query->stone_carat_from = $finalVenFromArr[$i];
                $query->stone_carat_to = $finalVenToArr[$i];
                $query->stone_price = $VendorStonePrice[$i];
                $query->vendor_id = $vendor_id;
                $query->save();
                $Msg = "Added";
            }
        }
        return  $Msg;
    }

    public function getStonePrice(Request $request) {
        $AllDetails = $request->all();
        $stn_clrty = $AllDetails['stn'];
        $stn_shape = $AllDetails['shp'];
        $stn_carat = $AllDetails['crt'];
        $vendor = $AllDetails['vdr'];
        $stn_carat = explode("-",$stn_carat);
        $query = DB::table('vendorstonemanages');
        $rows =  $query->where([ ['vendor_id', '=', $vendor], ['stone_shape', '=', $stn_shape],['stone_clarity', '=', $stn_clrty],['stone_carat_from', '=', $stn_carat[0] ], ['stone_carat_to', '=', $stn_carat[1]], ])->get();
        if(count($rows) > 0) {  
            $row = $rows[0];
            $StnPrice = $row->stone_price;
        }
        else {
            $StnPrice= 0;
        }
        return $StnPrice;
    }

    //*[[Qc status update]]*//
    public function changeQcStatus(request $request) {
        $data = $request->all();
        $id = $data['id'];
        $status = $data['status'];
        if($status == "accept") {
                $CostingData = Costingdata::find($id);
                $CostingData['qc_status']= 1;
                $CostingData['approved_by']= Auth::id();
                $CostingData->save();   
                $message = Config::get('constants.message.qc_status_approved');
        }
        else {
            $CostingData = Costingdata::find($id);
            $CostingData['qc_status']= 0;
            $CostingData['comment'] =  $data['comment'];
            $CostingData['rejected_by']= Auth::id();
            $CostingData->save();      
            $message = Config::get('constants.message.qc_status_rejected');
        }
        return $message;
    }

    public function addProducts(request $request) {
        $id = $request->id;
        $productDatas = Costingdata::whereIn('id',$id)->get();
        foreach ($productDatas as $key => $productData) {
             /** Start here */
            $productColl = array();
            $productsIdsArr = array();
            $stoneArr = array();
            $certificatesArr = array(); 
            $attribute_set_id = "";
            $productData = Costingdata::where('id',$productData['id'])->get();
            foreach ($productData as $rowkey => $productDataColl) {

                $metalQuality = $productDataColl['metal_karat'];
                $metalColor = $productDataColl['color'];
                $metal_quality_value = $metalQuality . " " . $metalColor;
                $metal_quality = ProductHelper::_toGetMetalQualityId($metalQuality, $metalColor);
                $attribute_set_id = ProductHelper::_toGetAttributeSetId($productDataColl['product_category']);
                $category_id = ProductHelper::_toGetCategoryId($productDataColl['product_category']);
                $certificate_no = $productDataColl['certificate_no'];
                $batch_no = $productDataColl['item'];
                
                $data = array('small_image'=>(!empty($productDataColl['image'])?$productDataColl['image']:""),'certificate_no'=>$certificate_no,'sku'=>$productDataColl['sku'],'style'=>$productDataColl['style'],'rts_ring_size'=>$productDataColl['ringsize'],'gross_weight'=>$productDataColl['gross_weight'],'rts_stone_weight'=>$productDataColl['diamond_weight'],'stone_color_value'=>$productDataColl['colorstone_weight'],'stone_carat_value'=>$productDataColl['diamond_weight'],'stone_use'=>$productDataColl['diamond_pcs'],'total_amount'=>$productDataColl['total_amount'],'po_no'=>$productDataColl['po_no'],'order_no'=>$productDataColl['order_no'],'item'=>$productDataColl['item'],'extra_price'=>$productDataColl['extra_price'],'extra_price_for'=>$productDataColl['extra_price_for'],'metal_quality_value'=>$metal_quality_value,'metal_quality'=>$metal_quality,'attribute_set_id'=>$attribute_set_id);

                $metaldata = array('metal_quality_id' => $metal_quality,'metal_weight' => $productDataColl['metal_weight'],'metal_rate' => $productDataColl['metalrate'],'metal_labour_charge' => $productDataColl['labouramount'],'metal_amount' => $productDataColl['metalamount'],'metal_type_id' => 0,'metal_actual_weight' => 0);

                $categorydata = array('category_id'=>$category_id,'position'=>0);

                //* Stone Data -- start *//
                $stoneArr['seive_size'] = str_replace('1/2', ' 1/2', str_replace(' ', '', $productDataColl['seive_size']));
                $stoneArr['seive_size'] = (!empty($stoneArr['seive_size']) ? $stoneArr['seive_size'] : 0);
                $stoneArr['stone_shape'] =  ProductHelper::_toGetDiamondShapeIdMultiple(str_replace(' ', '', $productDataColl['material_type']));
                $stoneArr['stone_clarity'] = ProductHelper::_toGetDiamondClarityIdMultiple(str_replace(' ', '', $productDataColl['material_quality']));
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
                        if (strlen($productDataCollElem) > 0) {
                            $tmpElem[$rowkey][$colkey] = $productDataCollElem;
                        }
                    }
                }
                //* Stone Data -- end *//
            }
            
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
            /** end here */
        }
       
        $message = Config::get('constants.message.qc_status_approved');
        return json_encode(array('message'=>$message));exit;
    }

    //*[[Qc Multiple Products Status Update]]*//
    public function changeQcStatusMultiple(request $request) {
        $data = $request->all();
        $Ids  = $data['chkCostingIds']; 
        $status = $data['status']; 
        
        foreach ($Ids as $id) {
            $CostingData = Costingdata::find($id);
            if($status == "accept") { 
                $CostingData['qc_status']= 1;
                $CostingData['approved_by']= Auth::id();
                $CostingData->save();
                $message = Config::get('constants.message.qc_status_approved');
            }
            if($status == "reject") { 
                $comment = $data['comment']; 
                $CostingData['qc_status']= 0;
                $CostingData['comment']= $comment;
                $CostingData['rejected_by']= Auth::id();
                $CostingData->save();
                $message = Config::get('constants.message.qc_status_rejected');
            }
        }
        return json_encode(array('message'=>$message));exit;
    }


    public function loadvendor_others_stonehtml(Request $request) {

        $data['stone_shape'] = DB::select( DB::raw("SELECT `main_table`.*, `eav_op_shape`.*, `eav_attr_stoneshape`.`value` AS `stone_shape` FROM `grp_stone_manage` AS `main_table` LEFT JOIN `eav_attribute_option` AS `eav_op_shape` ON eav_op_shape.option_id = main_table.stone_shape LEFT JOIN `eav_attribute_option_value` AS `eav_attr_stoneshape` ON eav_attr_stoneshape.option_id=main_table.stone_shape AND eav_op_shape.attribute_id=141 GROUP BY `main_table`.`stone_shape`") );

        $data['stone_clarity'] = DB::select( DB::raw("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'stone_clarity' AND EAOV.store_id = 0") );
        $data['stone_carat'] = DB::table(DB::raw('grp_stone_manage'))->groupBy("stone_carat_from")->get();
        $data['cnt']= $_GET['cnt'];
        $returnHTML = view('costing.loadvendor_others_stonehtml',['data'=>$data])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML));
    }



    public function store(Request $request) {
        
        //echo "<pre>"; print_r($request->all());exit;
        $costing_id = '1'; 
        $CostingCollection = Costing::all();
        foreach($CostingCollection as $CostColl) {
            $costing_id ++;
        }
        $postdata = $request->all();
        $file     = $postdata['name'];
        $fileName = $file->getClientOriginalName();
        $postdata['name'] = $fileName;
        $postdata['status'] = "Decline";
        $postdata['costing_id'] = $costing_id;
        $vendor_id = $postdata['vendor_id'];
        $setdata = array('status' => "Decline",'name' => $fileName,'costing_id' => $costing_id,'vendor_id' => $vendor_id);

        Costing::create($setdata);

        //start
        /*$request->validate([    
            'name' => 'required'
        ]);*/
        
        $path = $request->file('name')->getRealPath();
        $rowdata = Excel::load($path)->get();
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
                $zipReader = fopen($drawing->getPath(),'r');
                $imageContents = '';

                while (!feof($zipReader)) {
                    $imageContents .= fread($zipReader,1024);
                }
                fclose($zipReader);
                $extension = $drawing->getExtension();
            }
            $myFileName = 'img/'.'00_Image_'.++$i.'.'.$extension;
            file_put_contents($myFileName,$imageContents);
            $image[] = $myFileName;
        }

        //End code for images
        $row_i = 0;
        if($rowdata->count()){
            foreach ($rowdata as $key => $coll) {
                $col_sr_no = (string) (!empty($coll['Sr No.']) ? $coll['Sr No.'] : (!empty($coll['S No.']) ? $coll['S No.'] : (!empty($coll['Sr. No']) ? $coll['Sr. No'] : (!empty($coll['Sr. No.']) ? $coll['Sr. No.'] : false )) ) );
                
                $col_metal_color = (!empty($coll['Metel Color']) ? $coll['Metel Color'] : (!empty($coll['METAL COLOUR']) ? $coll['METAL COLOUR'] : (!empty($coll['Color']) ? $coll['Color'] : false ) ) );

                $col_order_no = (!empty($coll['Order no']) ? $coll['Order no'] : (!empty($coll['Order No.']) ? $coll['Order No.'] : false ) );

                $col_style = (!empty($coll['Style #']) ? $coll['Style #'] : (!empty($coll['Style#']) ? $coll['Style#'] : (!empty($coll['Style( Design No )']) ? $coll['Style( Design No )'] : false ) ) );
                
                $col_gross_wt = (!empty($coll['Gross Wt']) ? $coll['Gross Wt'] : (!empty($coll['Gross Wt.']) ? $coll['Gross Wt.'] : (!empty($coll['Grand Gross Wt']) ? $coll['Grand Gross Wt'] : false ) ) );

                $col_total_metal_wt = (!empty($coll['Total Metal Wt.']) ? $coll['Total Metal Wt.'] : (!empty($coll['Net Wt.']) ? $coll['Net Wt.'] : (!empty($coll['Net.Wt']) ? $coll['Net.Wt'] : (!empty($coll['Net Wt']) ? $coll['Net Wt'] : (!empty($coll['Net Metal Wt.']) ? $coll['Net Metal Wt.'] : false ) ) ) ) );

                $col_seive_size = (!empty($coll['Seive Size']) ? $coll['Seive Size'] : (!empty($coll['Sive size']) ? $coll['Sive size'] : false ) );

                $col_stone_amt = (!empty($coll['Stone amt']) ? $coll['Stone amt'] : (!empty($coll['Stone amt  (Handling charge )']) ? $coll['Stone amt  (Handling charge )'] : (!empty($coll['Total Stone amt']) ? $coll['Total Stone amt'] : (!empty($coll['TOTAL DIA COST']) ? $coll['TOTAL DIA COST'] : (!empty($coll['Tot Dia COST']) ? $coll['Tot Dia COST'] : false ) ) ) ) );

                $col_diamond_quality = (!empty($coll['Material Inter. Quality']) ? $coll['Material Inter. Quality'] : (!empty($coll['DIA QUALITY']) ? $coll['DIA QUALITY'] : (!empty($coll['Diamond Quality']) ? $coll['Diamond Quality'] : false ) ) );

                $col_diamond_weight = (!empty($coll['Total Diamond Wt.']) ? $coll['Total Diamond Wt.'] : (!empty($coll['TOTAL DIA WT']) ? $coll['TOTAL DIA WT'] : (!empty($coll['Total Diamond Wt.']) ? $coll['Total Diamond Wt.'] : false ) ) );

                $col_total_amt = (!empty($coll['Total  amt']) ? $coll['Total  amt'] : (!empty($coll['Amt']) ? $coll['Amt'] : (!empty($coll['Amount']) ? $coll['Amount'] : (!empty($coll['Total Amount']) ? $coll['Total Amount'] : false ) ) ) );

                $col_diamond_pcs = (!empty($coll['Dia Pcs']) ? $coll['Dia Pcs'] : (!empty($coll['Dia. Pcs']) ? $coll['Dia. Pcs'] : (!empty($coll['Total Diamond Pcs']) ? $coll['Total Diamond Pcs'] : false ) ) );

                $col_labour_amt = (!empty($coll['Labour Amt']) ? $coll['Labour Amt'] : (!empty($coll['Tot Labour']) ? $coll['Tot Labour'] : (!empty($coll['Labour amt']) ? $coll['Labour amt'] : (!empty($coll['TOTAL LAB COST']) ? $coll['TOTAL LAB COST'] : (!empty($coll['Labour Amount']) ? $coll['Labour Amount'] : false ) ) ) ) );
                
                $col_total_color_stone_wt = (!empty($coll['Total Color Stone Wt']) ? $coll['Total Color Stone Wt'] : (!empty($coll['Color Stone Wt']) ? $coll['Color Stone Wt'] : false ) );

                $col_material_mm_size = (!empty($coll['Material MM Size']) ? $coll['Material MM Size'] : (!empty($coll['MM']) ? $coll['MM'] : (!empty($coll['MM SIZE']) ? $coll['MM SIZE'] : false ) ) );

                $coll_po_no = (!empty($coll['Po No.']) ? $coll['Po No.'] : (!empty($coll['Order PoNo']) ? $coll['Order PoNo'] : false ) );

                $coll_sku = (!empty($coll['Sku no']) ? $coll['Sku no'] : (!empty($coll['Sku No']) ? $coll['Sku No'] : (!empty($coll['Sku']) ? $coll['Sku'] : false )));

                $col_stone_rate = (!empty($coll['Stone Rate']) ? $coll['Stone Rate'] : (!empty($coll['Stone Rate (Avg Wt / Stn)']) ?  $coll['Stone Rate (Avg Wt / Stn)'] : false ) ); 

                $col_metal_amount = (!empty($coll['Metal Amt']) ? $coll['Metal Amt'] : (!empty($coll['Metal Amount']) ?  $coll['Metal Amount'] : false ) ); 

                $col_certificate_no = (!empty($coll['Certificate No.']) ? $coll['Certificate No.'] : (!empty($coll['Certificate no']) ?  $coll['Certificate no'] : false ) ); 

                $col_cgst = (!empty($coll['CGST 2.50%']) ? $coll['CGST 2.50%'] : (!empty($coll['CGST 1.5%']) ?  $coll['CGST 1.5%'] : (!empty($coll['CGST 2.5%']) ?  $coll['CGST 2.5%'] : false ) ) ); 

                $col_sgst = (!empty($coll['SGST 2.50%']) ? $coll['SGST 2.50%'] : (!empty($coll['SGST 1.5%']) ?  $coll['SGST 1.5%'] : (!empty($coll['SGST 2.5%']) ?  $coll['SGST 2.5%'] : false ) ) ); 

                $coll_item_no = (!empty($coll['Item #']) ? $coll['Item #'] : (!empty($coll['Batch No.']) ? $coll['Batch No.'] : (!empty($coll['Batch No']) ? $coll['Batch No'] : false ) ) );

                $coll_extra_price = (!empty($coll['Extra Price']) ? $coll['Extra Price'] : (!empty($coll['extra price']) ? $coll['extra price'] : false ) );

                $coll_extra_price_for = (!empty($coll['Extra Price For']) ? $coll['Extra Price For'] : (!empty($coll['extra price for']) ? $coll['extra price for'] :  false  ) );


                if(!empty($col_sr_no)) {

                    $costingdata[$key]['image'] = $image[$row_i];
                    $costingdata[$key]['item'] = $coll_item_no;
                    $costingdata[$key]['po_no'] = $coll_po_no;
                    $costingdata[$key]['order_no'] = $col_order_no;
                    $costingdata[$key]['certificate_no'] = $col_certificate_no;
                    $costingdata[$key]['sku'] = $coll_sku;
                    $costingdata[$key]['style'] = $col_style;
                    $costingdata[$key]['metal_karat'] = (!empty($coll['Metal Karat']) ? $coll['Metal Karat'] : false );
                    $costingdata[$key]['color'] = $col_metal_color;
                    $costingdata[$key]['ringsize'] = (!empty($coll['Ring Size']) ? $coll['Ring Size'] : false );
                    $costingdata[$key]['product_category'] = (!empty($coll['Product Category']) ? $coll['Product Category'] : false ); 
                    $costingdata[$key]['gross_weight'] = $col_gross_wt;
                    $costingdata[$key]['metal_weight'] = $col_total_metal_wt;
                    $costingdata[$key]['metalrate'] = $coll['Metal Rate'];
                    $costingdata[$key]['metalamount'] = $col_metal_amount;
                    $costingdata[$key]['labouramount'] = $col_labour_amt;
                    $costingdata[$key]['diamond_pcs'] = $col_diamond_pcs;
                    $costingdata[$key]['diamond_weight'] = $col_diamond_weight;
                    $costingdata[$key]['colorstone_pcs'] = (!empty($coll['Total Color Stone Pcs']) ? $coll['Total Color Stone Pcs'] : false );
                    $costingdata[$key]['colorstone_weight'] = $col_total_color_stone_wt;
                    $costingdata[$key]['total_stone_amount'] = (!empty($coll['Total Stone amt']) ? $coll['Total Stone amt'] : false ); 
                    $costingdata[$key]['total_amount'] = $col_total_amt;
                    $costingdata[$key]['costingdata_id'] = $costing_id;
                    $costingdata[$key]['seive_size'] = $col_seive_size;
                    $costingdata[$key]['material_type'] = (!empty($coll['Material Type']) ? $coll['Material Type'] : false );  
                    $costingdata[$key]['material_quality'] = $col_diamond_quality;
                    $costingdata[$key]['material_category'] =  (!empty($coll['Material Category']) ? $coll['Material Category'] : false ); 
                    $costingdata[$key]['material_mm_size'] = $col_material_mm_size;
                    $costingdata[$key]['material_pcs'] = (!empty($coll['Material pices']) ? $coll['Material pices'] : false );
                    $costingdata[$key]['material_weight'] = (!empty($coll['Material Weight']) ? $coll['Material Weight'] : false ); 
                    $costingdata[$key]['stone_rate'] = $col_stone_rate;
                    $costingdata[$key]['hallmarking'] = (!empty($coll['Hallmarking']) ? $coll['Hallmarking'] : false ); 
                    $costingdata[$key]['igi_charges'] = (!empty($coll['IGI']) ? $coll['IGI'] : false );
                    $costingdata[$key]['stone_amount'] = $col_stone_amt;
                    $costingdata[$key]['cgst'] = $col_cgst;
                    $costingdata[$key]['sgst'] = $col_sgst;
                    $costingdata[$key]['is_igi'] = 0;
                    $costingdata[$key]['return_memo'] = 0;
                    $costingdata[$key]['request_invoice'] = 0;
                    $costingdata[$key]['extra_price'] = $coll_extra_price;
                    $costingdata[$key]['extra_price_for'] = $coll_extra_price_for;
                    
                    $prev_key = $key;
                    $row_i++;
                }
                else {

                    if(!empty($col_seive_size)){
                    $costingdata[$prev_key]['seive_size'] = (string) $costingdata[$prev_key]['seive_size'].','.$col_seive_size;
                    }
                    if(!empty($coll['Material Type'])){
                    $costingdata[$prev_key]['material_type'] = (string) $costingdata[$prev_key]['material_type'].','.$coll['Material Type'];
                    }
                    if(!empty($col_diamond_quality)){
                    $costingdata[$prev_key]['material_quality'] = (string) $costingdata[$prev_key]['material_quality'].','.$col_diamond_quality;
                    }
                    if(!empty($coll['Material Category'])){
                    $costingdata[$prev_key]['material_category'] = (string) $costingdata[$prev_key]['material_category'].','.$coll['Material Category'];
                    }

                   if(!empty($coll['Material MM Size'])){
                   $costingdata[$prev_key]['material_mm_size'] = (string) $costingdata[$prev_key]['material_mm_size'].','.$coll['Material MM Size'];
                    }

                    if(!empty($coll['Material pices'])){
                    $costingdata[$prev_key]['material_pcs'] = (string) $costingdata[$prev_key]['material_pcs'].','.$coll['Material pices'];
                    }
                    if(!empty($coll['Material Weight'])){
                    $costingdata[$prev_key]['material_weight'] = (string) $costingdata[$prev_key]['material_weight'].','.$coll['Material Weight'];
                    }
                    if(!empty($coll['Stone Rate'])) {
                    $costingdata[$prev_key]['stone_rate'] = (string) $costingdata[$prev_key]['stone_rate'].','.$coll['Stone Rate'];
                    }
                    if(!empty($col_stone_amt)){
                    $costingdata[$prev_key]['stone_amount'] = (string) $costingdata[$prev_key]['stone_amount'].','.$col_stone_amt;
                    }
                } 
                $newcosting_id = $costing_id;
            }   
        }
       
        //end


        //echo "<pre>"; print_r($costingdata);exit;
        if($postdata['jobwork_status'] == 0) {

            $ValidatedData = CostingHelper::getImportValidateWithoutJobwork($costingdata,$postdata);
        }
        else {
            $ValidatedData = CostingHelper::getImportValidateWithJobwork($costingdata,$postdata);
        }
        
        if(isset($ValidatedData['messages'])) {
            $StatusMessage = $ValidatedData['messages'];
        }
        
        if(!empty($StatusMessage)) {
        
            $sheetname = $ValidatedData['sheetname'];
            $msg = 'click on link to see error in excel <a href="'. url('uploads/costing/'.$sheetname[0]).'">Download Excel</a>';
            $request->session()->flash("failed",  $msg);
            return redirect()->back()->withErrors($StatusMessage);
        } 
        else {

            $costraw = Costing::where('costing_id','=',$newcosting_id)->first();
            $costingupdate = Costing::find($costraw->id);
            $costingupdate->status = 'Approve';
            $costingupdate->save();  
            foreach ($costingdata as  $costingdatavalue) {
                Costingdata::create($costingdatavalue);
            } 
            $StatusMessage = Config::get('constants.message.import_success');
            return redirect('costing/costinglist')->with('success',$StatusMessage);
        }
        
    }

    public function IGIlist() {
        $costingdatas = Costingdata::where('is_igi',1)->orderBy('id', 'DESC')->get();
        $totalcount = Costingdata::where('is_igi',1)->count();
        return view('costing/IGIlist',array('costingdatas' => $costingdatas,'totalcount' => $totalcount));
    }

    public function requestinvoiceByQc(request $request) {
        $data = $request->all();
        $chkCostingIds = $data['chkCostingIds'];
        $i = 1;
        
        if(!empty($chkCostingIds)) {
            if(count($chkCostingIds) > 1) {
                foreach ($chkCostingIds as $chkCostingId) {
                    $costingColl = Costingdata::find($chkCostingId);
                    $costingColl['invoice_requested_by']= Auth::id();
                    $costingColl['request_invoice'] = 1;
                    $costingColl->save();
                    $i++;
                }
            }
            else {
                $costingColl = Costingdata::where('id',$chkCostingIds)->first();
                $costingColl['invoice_requested_by']= Auth::id();
                $costingColl['request_invoice'] = 1;
                $costingColl->save();
            }
        }
        $message = Config::get('constants.message.request_invoice_success');
        return $message;
    }

    public function returnmemoByQc(request $request) {
    

        $data = $request->all();

        $chkCostingIds = $data['chkCostingIds'];
        $i = 1;
        
        if(!empty($chkCostingIds)) {
            if(count($chkCostingIds) > 1) {
                foreach ($chkCostingIds as $chkCostingId) {
                    $costingColl = Costingdata::find($chkCostingId);
                    $costingColl['memo_returned_by']= Auth::id();
                    $costingColl['return_memo'] = 1;
                    $costingColl->save();
                    $i++;
                }
            }
            else {
                
                $costingColl = Costingdata::where('id',$chkCostingIds)->first();
                $costingColl['memo_returned_by']= Auth::id();
                $costingColl['return_memo'] = 1;
                $costingColl->save();
            }
        }

        $message = Config::get('constants.message.return_memo_success');
        return $message;
    }

    public function loadvendorhandlingcharges(Request $request) {
        $hallmarkingCharges=  array();
        $data = $request->all();
        $strVars = explode('&', $data['handlingcharges']);
        foreach ($strVars as $varrr) {
            $varArr = explode('=', $varrr);
            $variName  = preg_replace('/[^a-zA-Z0-9_ -]/s','',urldecode($varArr[0]));
            if($variName != '_token'){
                $variVal = $varArr[1];
                $hallmarkingCharges[$variName][] = $variVal;
            } else {
                $variVal = $varArr[1];
                $hallmarkingCharges[$variName] = $variVal;
            }
        }

        $vendor_id = $data['vendor_id'];
        $VendorGoldHandling = $hallmarkingCharges['gold_handling'];
        $VendorDiamondHandling =  $hallmarkingCharges['diamond_handling'];
        $VendorFancyDiamondHandling = $hallmarkingCharges['fancy_diamond_handling'];
        $VendorIGICharges = $hallmarkingCharges['igi_charges'];
        $VendorHallmarking = $hallmarkingCharges['hallmarking'];
        $modified_by = Auth::id();

        $rows = VendorHandlingCharges::where([['vendor_id', '=', $vendor_id]  ])->first();

        if(count($rows) > 0) {
            $HandlingId= $rows->id;
            $query =VendorHandlingCharges::where('id',$HandlingId)->first();
            $query->gold_handling = $VendorGoldHandling[0];
            $query->diamond_handling = $VendorDiamondHandling[0];
            $query->fancy_diamond_handling = $VendorFancyDiamondHandling[0];
            $query->igi_charges = $VendorIGICharges[0];
            $query->hallmarking = $VendorHallmarking[0];
            $query->modified_by = Auth::id();
            $query->save();
            $Msg = "Updated";
        }

        else {
            /* Save Record */
            if(!empty($vendor_id)) {
                $query = new VendorHandlingCharges;
                $query->gold_handling = $VendorGoldHandling[0];
                $query->diamond_handling =  $VendorDiamondHandling[0];
                $query->fancy_diamond_handling = $VendorFancyDiamondHandling[0];
                $query->igi_charges =  $VendorIGICharges[0];
                $query->hallmarking = $VendorHallmarking[0];
                $query->vendor_id = $vendor_id;
                $query->created_by = Auth::id();
                $query->save();
                $Msg = "Added";
            }
        }
        return  $Msg;
    }

    public function getHandligCharges(request $request) {
        $data = $request->all();
        $vendor_id = $data['vendor_id'];
        $rows = VendorHandlingCharges::where([['vendor_id', '=', $vendor_id]  ])->first();
        if(count($rows) > 0) {
            $HandlingId= $rows->id;
            $query =VendorHandlingCharges::where('id',$HandlingId)->first();
            $HandTmpArr['gold_handling'] = $query->gold_handling;
            $HandTmpArr['diamond_handling'] = $query->diamond_handling;
            $HandTmpArr['fancy_diamond_handling'] = $query->fancy_diamond_handling;
            $HandTmpArr['igi_charges'] = $query->igi_charges;
            $HandTmpArr['hallmarking'] = $query->hallmarking;
            return json_encode(array('vendor_handling_arr'=>$HandTmpArr));exit;
        }

    }

    public function costinglogResponse(Request $request)
    {
        $columns = array( 
                            0 =>'name', 
                            1 =>'jobwork_status',
                            2=> 'status',
                            3=> 'vendor_name',);
        $totalData = Costing::count();
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
            
        if(empty($request->input('search.value'))) {            
        
            $costings = Costing::orderBy('id', 'DESC')->offset($start)->limit($limit)->orderBy($order,$dir)->get();
        }
        else {
        
            $search = $request->input('search.value'); 
            $costings =  Costing::where('jobwork_status','LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")->orWhere('status', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->orderBy($order,$dir)->get();
            $totalFiltered = Costing::where('jobwork_status','LIKE',"%{$search}%")->orWhere('name', 'LIKE',"%{$search}%")->orWhere('status', 'LIKE',"%{$search}%")->count();
        }

        $data = array();
        if(!empty($costings))
        {
            foreach ($costings as $costing)
            {
                $vendor=User::whereHas('roles', function($q){ $q->where('name', 'Vendor'); })->get();
                $vendor_id = $costing->vendor_id;
                $vendorColl = $vendor->where('id',$vendor_id)->first();
                $vendor_name = $vendorColl['name'];
                $jobworkstatus = ($costing->jobwork_status == 1)?'With Jobwork':'Without Jobwork';
                $date = date('d-m-Y', strtotime($costing->created_at));
                $status = ($costing->status == 'Decline')?'Declined':'Approved';
                $data[] = array($costing->name,$jobworkstatus,$status,$vendor_name,$date);
            }
        }
        
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
        echo json_encode($json_data); 
    }

  public function qcacceptResponse(request $request) {

        $totalData = Costingdata::where('qc_status',1)->where('request_invoice','!=' , 1)->count();
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        if(empty($request->input('search.value'))) {            
            $costings = Costingdata::orderBy('id', 'DESC')->where('qc_status',1)->where('request_invoice','!=' , 1)->offset($start)->limit($limit)->get();
        }
        else {
        
            $search = $request->input('search.value'); 
            $costings =  Costingdata::orderBy('id', 'DESC')->where('qc_status',1)->where('request_invoice','!=' , 1)->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('branding', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->get();
            $totalFiltered = Costingdata::orderBy('id', 'DESC')->where('qc_status',1)->where('request_invoice','!=' , 1)->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('branding', 'LIKE',"%{$search}%")->count();
        }

        $data = array();
        if(!empty($costings))
        {
            foreach ($costings as $costing)
            {
                $checkbox = "<label><input type='checkbox' class='form-check-input chkProduct' name='chk_costing' id='chk_costing' value=".$costing->id."><span class='label-text'></span></label>";
                $detail =  "<a href='javascript:void(0);'><i title='Detail' onclick='showDetail(".$costing->id.")' class='material-icons list-icon'>info</i></a>

                    <a href='javascript:void(0);'><i title='Reject' data-id =".$costing->id." class='material-icons list-icon qc_btn' id='reject'>cancel</i></a>

                    <a href='javascript:void(0);'><i title='IGI' data-id ='".$costing->id."' class='material-icons list-icon qc_btn fa fa-certificate ".(($costing->is_igi == 1) ? 'disabled' : '' )."' id='igi'></i></a>

                    <a href='javascript:void(0);'><i title='Request invoice' data-id ='".$costing->id."' class='material-icons list-icon qc_btn ".(($costing->request_invoice == 1) ? 'disabled' : '' )."' id='request_invoice'>open_in_new</i></a>";
                $data[] = array($checkbox,$costing->sku,$costing->certificate_no,$costing->branding,$detail);
            }
        }
        $json_data = array(
                    "query"           => $start,
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
        echo json_encode($json_data); 
    }

    public function qcrejectResponse(request $request) {

        $totalData = Costingdata::orderBy('id', 'DESC')->where('qc_status',0)->where('return_memo','!=' , 1)->count();
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        if(empty($request->input('search.value'))) {            
        
            $costings = Costingdata::orderBy('id', 'DESC')->where('qc_status',0)->where('return_memo','!=' , 1)->offset($start)->limit($limit)->get();
        }
        else {
        
            $search = $request->input('search.value'); 
            $costings =  Costingdata::orderBy('id', 'DESC')->where('qc_status',0)->where('return_memo','!=' , 1)->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('branding', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->get();
            $totalFiltered = Costingdata::orderBy('id', 'DESC')->where('qc_status',0)->where('return_memo','!=' , 1)->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('branding', 'LIKE',"%{$search}%")->count();
        }

        $data = array();
        if(!empty($costings))
        {
            foreach ($costings as $costing)
            {
                $checkbox = "<label><input type='checkbox' class='form-check-input chkProduct' name='chk_costing' id='chk_costing' value=".$costing->id."><span class='label-text'></span></label>";
                $detail =  "<a href='javascript:void(0);'><i title='Detail' onclick='showDetail(".$costing->id.")' class='material-icons list-icon'>info</i></a>

                    <a href='javascript:void(0);'><i title='Accept' data-id =".$costing->id." class='material-icons list-icon qc_btn' id='accept'>check_circle</i></a>

                    <a href='javascript:void(0);'><i title='Return memo' data-id ='".$costing->id."' class='material-icons list-icon qc_btn".(($costing->return_memo == 1) ? ' disabled' : '' )."' id='return_memo'>assignment</i></a>";
                $data[] = array($checkbox,$costing->sku,$costing->certificate_no,$detail);
            }
        }
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
        echo json_encode($json_data);
    }

    public function qcigiResponse(request $request) {

        $totalData = Costingdata::orderBy('id', 'DESC')->where('is_igi',1)->count();
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        if(empty($request->input('search.value'))) {            
        
            $costings = Costingdata::orderBy('id', 'DESC')->where('is_igi',1)->offset($start)->limit($limit)->get();
        }
        else {
        
            $search = $request->input('search.value'); 
            $costings =  Costingdata::orderBy('id', 'DESC')->where('is_igi',1)->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('branding', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->get();
            $totalFiltered = Costingdata::orderBy('id', 'DESC')->where('is_igi',1)->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('branding', 'LIKE',"%{$search}%")->count();
        }
        $data = array();
        if(!empty($costings))
        {
            foreach ($costings as $costing)
            {
                $detail =  "<a href='javascript:void(0);'><i title='Detail' onclick='showDetail(".$costing->id.")' class='material-icons list-icon'>info</i></a>";
                $data[] = array($costing->sku,$costing->certificate_no,$costing->branding,$detail);
            }
        }
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
        echo json_encode($json_data);
    }

    public function costinglistResponse(request $request) {

        $totalData = Costingdata::whereNull('qc_status')->count();
        $columns = array( 
                            0 => 'id',
                            2 =>'sku', 
                            4 =>'item',
                            5 => 'created_at',);
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if(empty($request->input('search.value'))) {            
            $costings = Costingdata::whereNull('qc_status')->offset($start)->limit($limit)->orderBy($order,$dir)->get();
        }
        else {
            $search = $request->input('search.value'); 
            $costings =  Costingdata::whereNull('qc_status')->where('sku','LIKE',"%{$search}%")->orWhere('item', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->orderBy($order,$dir)->get();
            $totalFiltered = Costingdata::whereNull('qc_status')->where('sku','LIKE',"%{$search}%")->orWhere('item', 'LIKE',"%{$search}%")->orderBy($order,$dir)->count();
        }
        $data = array();
        if(!empty($costings))
        {
            foreach ($costings as $costing)
            {
                $checkbox = "<label><input type='checkbox' class='form-check-input chkProduct' name='chk_costing' id='chk_costing' value=".$costing->id."><span class='label-text'></span></label>";

                $imagepath = \URL::to('/') .'/'.$costing->image;
                $image = "<img src=".$imagepath." class='img-fluid' height='120' width='120'/>";

                $costing_id = $costing->costingdata_id;
                $costraw = \App\Costing::where('costing_id',$costing_id)->first();
                $vendor_id = $costraw->vendor_id;
                $vendor= \App\User::whereHas('roles', function($q){ $q->where('name', 'Vendor'); })->get();
                $vendorColl = $vendor->where('id',$vendor_id)->first();
                $vendor_name = $vendorColl['name'];

                $detail =  "<a href='javascript:void(0);'><i title='Detail' onclick='showDetail(".$costing->id.")' class='material-icons list-icon'>info</i></a>
                    
                    <a href='javascript:void(0);'><i title='Accept' data-id ='".$costing->id."' class='material-icons list-icon qc_btn' data-status='accept' id='accept'>check_circle</i></a>

                    <a href='javascript:void(0);'><i title='Reject' data-id =".$costing->id." class='material-icons list-icon qc_btn' data-status='reject' id='reject'>cancel</i></a>";

                $date = date('d-m-Y', strtotime($costing->created_at));

                $data[] = array($checkbox,$image,$costing->sku,$vendor_name,$costing->item,$date,$detail);
            }
        }

        
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
        echo json_encode($json_data);

    }

    public function qcrequestinvoice() {
        $costingdatas = Costingdata::where('request_invoice',1)->orderBy('id', 'DESC')->paginate(10);
        $totalcount = Costingdata::where('request_invoice',1)->count();
        return view('costing/qcrequestinvoice',array('costingdatas' => $costingdatas,'totalcount' => $totalcount));
    }

    public function qcrequestinvoiceResponse(request $request) {



        $totalData = Costingdata::orderBy('id', 'DESC')->where('request_invoice',1)->count();
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');    
        if(empty($request->input('search.value'))) {            
            $costings = Costingdata::orderBy('id', 'DESC')->where('request_invoice',1)->offset($start)->limit($limit)->get();
        }
        else {
            $search = $request->input('search.value'); 
            $costings =  Costingdata::orderBy('id', 'DESC')->where('request_invoice',1)->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('item', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->get();
            $totalFiltered = Costingdata::orderBy('id', 'DESC')->where('request_invoice',1)->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('item', 'LIKE',"%{$search}%")->count();
        }

        $data = array();
        if(!empty($costings))
        {
            foreach ($costings as $costing)
            {
                if(!empty($costing->certificate_no) ||  $costing->certificate_no != 0 ){
                    
                    $certificate_no = '<td>'.$costing->certificate_no.'</td>';
                }else{
                    
                       $certificate_no = '<td>'.$costing->item.'</td>';
                  
                  }
            
                $detail =  "<a href='javascript:void(0);'><i title='Detail' onclick='showDetail(".$costing->id.")' class='material-icons list-icon'>info</i></a>";
                $data[] = array($costing->sku,$certificate_no,$detail);
            }
        }
        
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
        echo json_encode($json_data);
    }

    public function qcreturnmemo() {

        $costingdatas = Costingdata::where('return_memo',1)->orderBy('id', 'DESC')->paginate(10);
        $totalcount = Costingdata::where('return_memo',1)->count();
        return view('costing/qcreturnmemo',array('costingdatas' => $costingdatas,'totalcount' => $totalcount));
    }

    public function qcreturnmemoResponse(request $request) {

        $columns = array( 
                            0 =>'sku', 
                            1 =>'certificate_no',
                            2=> 'item',);
        $totalData = Costingdata::orderBy('id', 'DESC')->where('return_memo',1)->count();
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
            
        if(empty($request->input('search.value'))) {            
        
            $costings = Costingdata::orderBy('id', 'DESC')->where('return_memo',1)->offset($start)->limit($limit)->orderBy($order,$dir)->get();
        }
        else {
        
            $search = $request->input('search.value'); 
            $costings =  Costingdata::orderBy('id', 'DESC')->where('return_memo',1)->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('item', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->orderBy($order,$dir)->get();
            $totalFiltered = Costingdata::orderBy('id', 'DESC')->where('return_memo',1)->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('item', 'LIKE',"%{$search}%")->count();
        }

        $data = array();
        if(!empty($costings))
        {
            foreach ($costings as $costing)
            {
                $detail =  "<a href='javascript:void(0);'><i title='Detail' onclick='showDetail(".$costing->id.")' class='material-icons list-icon'>info</i></a>";
                $data[] = array($costing->sku,$costing->certificate_no,$costing->item,$detail);
            }
        }
        
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
        echo json_encode($json_data);
    }

    public function qccount() {
        $qcacceptcount    = Costingdata::where('qc_status',1)->where('request_invoice','!=' , 1)->count();
        $qcrejectcount    = Costingdata::where('qc_status',0)->where('return_memo','!=' , 1)->count();
        $qcigicount       = Costingdata::where('is_igi',1)->count();
        $qcrequestinvoice = Costingdata::where('qc_status',1)->where('request_invoice','!=' , 1)->count();
        $qcreturnmemo     = Costingdata::where('return_memo',1)->count();
        $qccostingproductcount = Costingdata::whereNull('qc_status')->count();
        $totalcount    = array('qcacceptcount'=>$qcacceptcount,'qcrejectcount'=>$qcrejectcount,'qcigicount' => $qcigicount , 'qcrequestinvoice' => $qcrequestinvoice,'qcreturnmemo' => $qcreturnmemo,'qccostingproductcount' => $qccostingproductcount);
        return $totalcount;
    }

    public function product_list(request $request) {
        
        $products = Products::with(['metals', 'stones','categorys'])->orderBy('created_at')->paginate(10);
        $data = $products->toArray(); 
        $data = $data['data'];
        //var_dump($data);exit;
        $stoneArr = array();
        $stoneElem = array();
        foreach ($data as $key => $datas) {
            foreach($datas['stones'] as $colmkey => $coll) {
                $stoneArr[$key]['stone_stone'][] = $coll['stone_stone'];
                $stoneArr[$key]['stone_shape'][] = ProductHelper::_toGetDiamondShapeValue($coll['stone_shape']);
                $stoneArr[$key]['seive_size'][] = $coll['seive_size'];
                $stoneArr[$key]['mm_size'][] = $coll['mm_size'];
                $stoneArr[$key]['carat'][] = $coll['carat'];
                $stoneArr[$key]['stone_use'][] = $coll['stone_use'];
                $stoneArr[$key]['stone_clarity'][] = ProductHelper::_toGetDiamondClarityValue($coll['stone_clarity']);
                $stoneArr[$key]['stone_rate'][] = $coll['stone_rate'];
                $stoneArr[$key]['stone_amount'][] = $coll['stone_amount'];
            }

        }     
        foreach ($stoneArr as $key => $stoneVal) {
            $stoneElem[$key]['stone_stone'] =  implode(", ",$stoneVal['stone_stone']);    
            $stoneElem[$key]['stone_shape'] =  implode(", ",$stoneVal['stone_shape']);    
            $stoneElem[$key]['seive_size'] =  implode(", ",$stoneVal['seive_size']);    
            $stoneElem[$key]['mm_size'] =  implode(", ",$stoneVal['mm_size']);    
            $stoneElem[$key]['carat'] =  implode(", ",$stoneVal['carat']);    
            $stoneElem[$key]['stone_use'] =  implode(", ",$stoneVal['stone_use']);    
            $stoneElem[$key]['stone_clarity'] =  implode(", ",$stoneVal['stone_clarity']);    
            $stoneElem[$key]['stone_rate'] =  implode(", ",$stoneVal['stone_rate']);    
            $stoneElem[$key]['stone_amount'] =  implode(", ",$stoneVal['stone_amount']);    
            

        }
       
        $totalcount = Products::with(['metals', 'stones','categorys'])->count();
        $role = Auth::user()->roles->first()->name;
        return view('costing.product_list',array('products'=> $products,'data' => $data,'totalcount' => $totalcount,'stoneElem' => $stoneElem,'role'=>$role));
    }


    public function productlistResponse(request $request) {
        
        $columns = array(
            0 => 'id',
            1 => 'style',
            2 => 'item',
            3 => 'po_no',
            4 => 'stone_use', 
            5 => 'gross_weight',
            7 => 'total_amount',
            8 => 'certificate_no',
            9 => 'sku',
            );
        $products = Products::with(['metals', 'stones','categorys'])->get();
        $data = $products->toArray(); 
        $totalData = count($data);
        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value'))) {            
            $productslist = Products::with(['metals', 'stones','categorys'])->offset($start)->limit($limit)->orderBy($order, $dir)->get(); 
        }
        else {
            $search = $request->input('search.value'); 
            $productslist =  Products::with(['metals', 'stones','categorys'])->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('style', 'LIKE',"%{$search}%")->offset($start)->limit($limit)->orderBy($order, $dir)->get();
            $totalFiltered = Products::with(['metals', 'stones','categorys'])->where('sku','LIKE',"%{$search}%")->orWhere('certificate_no', 'LIKE',"%{$search}%")->orWhere('style', 'LIKE',"%{$search}%")->count();
        }
        $data = array();
        if(!empty($productslist))
        {
            $counter = 1;
            $i=0;
            foreach ($productslist as  $rowkey => $datas)
            {
                $datastoneStrValue = array();
                $mm_size=array();
                $stone_stone = array();
                $stone_shape = array();
                $seive_size = array();
                $carat = array();
                $stone_use = array();
                $stone_rate = array();
                $stone_amount = array();
                foreach ($datas->stones as $datastonekey => $datastonevalue) {
                    $mm_size[]=$datastonevalue->mm_size;
                    $stone_stone[] =$datastonevalue->stone_stone;
                    $stone_shape[] =  ProductHelper::_toGetDiamondShapeValue($datastonevalue->stone_shape);
                    $seive_size[] = $datastonevalue->seive_size;
                    $carat[] = $datastonevalue->carat;
                    $stone_use[] = $datastonevalue->stone_use;
                    $stone_rate[] = $datastonevalue->stone_rate;
                    $stone_amount[] = $datastonevalue->stone_amount;
                    $datastoneStrValue[] = ProductHelper::_toGetDiamondClarityValue($datastonevalue->stone_clarity);
                }

                if(count($datastoneStrValue) > 0){
                    $datastoneStrVal = implode(',', $datastoneStrValue);
                } else {
                    $datastoneStrVal = '';
                }
                if(count($mm_size) > 0){
                    $mm_size = implode(',', $mm_size);
                } else {
                    $mm_size = '';
                }
                if(count($stone_stone) > 0){
                    $stone_stone = implode(',', $stone_stone);
                } else {
                    $stone_stone = '';
                } 
                if(count($stone_shape) > 0){
                    $stone_shape = implode(',', $stone_shape);
                } else {
                    $stone_shape = '';
                } 
                if(count($seive_size) > 0){
                    $seive_size = implode(',', $seive_size);
                } else {
                    $seive_size = '';
                } 
                if(count($carat) > 0){
                    $carat = implode(',', $carat);
                } else {
                    $carat = '';
                } 
                if(count($stone_use) > 0){
                    $stone_use = implode(',', $stone_use);
                } else {
                    $stone_use = '';
                } 
                if(count($stone_rate) > 0){
                    $stone_rate = implode(',', $stone_rate);
                } else {
                    $stone_rate = '';
                } 
                if(count($stone_amount) > 0){
                    $stone_amount = implode(',', $stone_amount);
                } else {
                    $stone_amount = '';
                }

           
                $category = ProductHelper::_toGetCategoryVal($datas['categorys']['category_id']);
                $val =ProductHelper::_toGetMetalQualityValue($datas['metals']['metal_quality_id']);
                $amount =(!empty($datas['metals']['metal_amount']) ? $datas['metals']['metal_amount'] : '');
                $style =(!empty($datas['style']) ? $datas['style'] : '');
                $item=(!empty($datas['item']) ? $datas['item'] : '');
                $po_no=(!empty($datas['po_no']) ? $datas['po_no'] : '');
                $stone_use=(!empty($datas['stone_use']) ? $datas['stone_use'] : '');
                $gross_weight=(!empty($datas['gross_weight']) ? $datas['gross_weight'] : '');
                $certificate_no=(!empty($datas['certificate_no']) ? $datas['certificate_no'] : '');
                $total_amount=(!empty($datas['total_amount']) ? $datas['total_amount'] : '');
                $sku =(!empty($datas['sku']) ? $datas['sku'] : '');
                $metal_weight=(!empty($datas['metals']['metal_weight']) ? $datas['metals']['metal_weight'] : '');
                $metal_rate=(!empty($datas['metals']['metal_rate']) ? $datas['metals']['metal_rate'] : '');
                $metal_labour_charge=(!empty($datas['metals']['metal_labour_charge']) ? $datas['metals']['metal_labour_charge'] : '');
                $stone_stone=(!empty($stone_stone) ? $stone_stone : '');
                $stone_shape=(!empty($stone_shape) ? $stone_shape : '');
                $seive_size=(!empty($seive_size) ? $seive_size: '');
                $mm_size =(!empty($mm_size) ? $mm_size : '');
                $carat=(!empty($carat) ? $carat : '');
                $stone_use=(!empty($stone_use) ? $stone_use : '');
                $stone_clarity=(!empty($datastoneStrVal) ? $datastoneStrVal : '');
                $stone_rate=(!empty($stone_rate) ? $stone_rate : '');
                $stone_amount=(!empty($stone_amount) ? $stone_amount : '');
                $costingid = $datas['id'];
                $chk = "<label><input type='checkbox' class='chk_product form-check-input chkProduct' name='chk_product' id='chk_product' value='".$datas['id']."'><span class='label-text'></span></label>";

                $role = Auth::user()->roles->first()->name;
                if($role == Config::get('constants.role.super_admin')) {
                $action = "<a class='color-content table-action-style' href='".route('productupload.updateproduct',['id'=> $costingid ]) ."'><i class='material-icons md-18'>edit</i></a>&nbsp";
                $action .= "<a id='deleteproduct' class='color-content table-action-style' href='javaScript:void(0)'  onclick='deleteProduct(".$costingid.")'><i class='material-icons md-18'>delete</i></a>";
                }

                if($role == Config::get('constants.role.super_admin')) {
                    $data[] = array($chk,++$start,$style,$item,$po_no,$stone_use,$gross_weight,$category,$total_amount,$certificate_no,$sku,$val,$metal_weight,$metal_rate,$metal_labour_charge,$amount,$stone_stone,$stone_shape,$seive_size,$mm_size,$carat,$stone_use,$stone_clarity,$stone_rate,$stone_amount,$action);
                }
                else {
                     $data[] = array($chk,++$start,$style,$item,$po_no,$stone_use,$gross_weight,$category,$total_amount,$certificate_no,$sku,$val,$metal_weight,$metal_rate,$metal_labour_charge,$amount,$stone_stone,$stone_shape,$seive_size,$mm_size,$carat,$stone_use,$stone_clarity,$stone_rate,$stone_amount);
                }
                
            }
            $counter++;
        }
        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
        echo json_encode($json_data);

    }

    public function costingproductlist(Request $request){

        $products = Products::with(['metals', 'stones','categorys'])->where('certificate_no',$request->id)->get();

         $data = $products->toArray(); 
        
        $stoneArr = array();
        $stoneElem = array();
        foreach ($data as $key => $datas) {
            foreach($datas['stones'] as $colmkey => $coll) {
                $stoneArr[$key]['stone_stone'][] = $coll['stone_stone'];
                $stoneArr[$key]['stone_shape'][] = ProductHelper::_toGetDiamondShapeValue($coll['stone_shape']);
                $stoneArr[$key]['seive_size'][] = $coll['seive_size'];
                $stoneArr[$key]['mm_size'][] = $coll['mm_size'];
                $stoneArr[$key]['carat'][] = $coll['carat'];
                $stoneArr[$key]['stone_use'][] = $coll['stone_use'];
                $stoneArr[$key]['stone_clarity'][] = ProductHelper::_toGetDiamondClarityValue($coll['stone_clarity']);
                $stoneArr[$key]['stone_rate'][] = $coll['stone_rate'];
                $stoneArr[$key]['stone_amount'][] = $coll['stone_amount'];
            }

        }     
        foreach ($stoneArr as $key => $stoneVal) {
            $stoneElem[$key]['stone_stone'] =  implode(", ",$stoneVal['stone_stone']);    
            $stoneElem[$key]['stone_shape'] =  implode(", ",$stoneVal['stone_shape']);    
            $stoneElem[$key]['seive_size'] =  implode(", ",$stoneVal['seive_size']);    
            $stoneElem[$key]['mm_size'] =  implode(", ",$stoneVal['mm_size']);    
            $stoneElem[$key]['carat'] =  implode(", ",$stoneVal['carat']);    
            $stoneElem[$key]['stone_use'] =  implode(", ",$stoneVal['stone_use']);    
            $stoneElem[$key]['stone_clarity'] =  implode(", ",$stoneVal['stone_clarity']);    
            $stoneElem[$key]['stone_rate'] =  implode(", ",$stoneVal['stone_rate']);    
            $stoneElem[$key]['stone_amount'] =  implode(", ",$stoneVal['stone_amount']);    
            

        }
        $returnHTML = view('costing.ProductDetail', ['data' => $products,'data' => $data,'stoneElem' => $stoneElem])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
      
    }

    public function getVendorName($vendor_id) {
        $name = User::where('id',$vendor_id) ->select('name')->pluck('name')->first();
        return $name;
    }

    public function acceptedProductExcel(request $request) {
        $collection = Costingdata::whereIn('id',$request->chkcosting)->get();
        foreach ($collection as $key => $coll) {
            $costingdataIds[] = $coll->costingdata_id;
        }
        
        $costingCollection = Costing::whereIn('id',$costingdataIds)->get();
        foreach ($costingCollection as $key => $costingColl) {
            $vendorIds[] = $this->getVendorName($costingColl->vendor_id);
            $name[] = $costingColl->name;
        }
        
        $serialnumber=0;
                $tmparray =array("Sr No.","Color or Hand Design","Item#","Style#","Metal Karat","Color","Product Category","Gross Wt","Total Diamond Pcs","Total Diamond Wt.","Total Color Stone Pcs","Total Color Stone Wt","Material Category","Material Type","Material Inter. Quality","Material MM Size","Material pices","Material Weight",'Vendor Name','Sheet Name');
                $sheet =array($tmparray);
                foreach ($collection as $key => $coll) 
                {
                    $tmparray =array();
                    $serialnumber = $serialnumber + 1;
                    array_push($tmparray,$serialnumber);

                    $images =$coll->image;
                    array_push($tmparray,$images);

                    $item = $coll->item;
                    array_push($tmparray,$item);
                    $style = $coll->style;
                    array_push($tmparray,$style);   
                    $metalkarat = $coll->metal_karat;
                    array_push($tmparray,$metalkarat);   
                    $color = $coll->color;
                    array_push($tmparray,$color); 
                    $product_category = $coll->product_category;
                    array_push($tmparray,$product_category);     
                    $gross_weight = $coll->gross_weight;
                    array_push($tmparray,$gross_weight);   
                    $total_diamondpcs = $coll->diamond_pcs;
                    array_push($tmparray,$total_diamondpcs);   
                    $total_diamondwt = $coll->diamond_weight;
                    array_push($tmparray,$total_diamondwt);   
                    $colorstone_pcs = $coll->colorstone_pcs;
                    array_push($tmparray,$colorstone_pcs); 
                    $colorstone_weight = $coll->colorstone_weight;
                    array_push($tmparray,$colorstone_weight); 
                    $material_category = $coll->material_category;
                    array_push($tmparray,$material_category);       
                    $material_type = $coll->material_type;
                    array_push($tmparray,$material_type);   
                    $material_quality = $coll->material_quality;
                    array_push($tmparray,$material_quality);   

                    $material_mm_size = $coll->material_mm_size;
                    array_push($tmparray,$material_mm_size);   
                    $material_pcs = $coll->material_pcs;
                    array_push($tmparray,$material_pcs); 
                    $metal_weight = $coll->material_weight;
                    array_push($tmparray,$metal_weight);     
                    array_push($tmparray,$vendorIds[$key]);
                    array_push($tmparray,$name[$key]);
                    array_push($sheet,$tmparray);
                }

                $newSheetArr = array();
        
                foreach ($sheet as $sheetkey => $rowData)
                {
                    $forcount = 0;
                    $newColumnsArr = array();
                    $totalColumns = count($rowData);
                    $commaColumnsNum = 0;
                    $commaColumnsArr = array();
                    foreach ($rowData as $colKey => $colValue) 
                    {
                        if(strpos($colValue, ',') !== false){
                            $colValues = explode(',', $colValue);
                            $newColumnsArr[] = $colValues[0];
                            $multiple_value_key[] = $colKey;
                            $commaColumnsArr[$commaColumnsNum] = $colValues;
                            $commaColumnsNum++;
                            $forcount = count($colValues);
                        } else {
                            $newColumnsArr[] = $colValue;
                        }
                        
                        if($colKey == ($totalColumns-1)) {
                            if(count(array_filter($newColumnsArr)) > 0){ // for blank td
                                $newSheetArr[] = $newColumnsArr;
                            }
                            if($commaColumnsNum > 1) {
                                for($col_j=0;$col_j<$forcount;$col_j++)
                                {
                                    if($col_j == 0) continue;
                                    $newDynColumnsArr = array();
                                    $counterCol = 0;
                                    for($col_i=0;$col_i<$totalColumns;$col_i++){
                                        if(in_array($col_i, $multiple_value_key)){
                                          $newDynColumnsArr[] = $commaColumnsArr[$counterCol][$col_j];
                                          $counterCol++;
                                        } else {
                                          $newDynColumnsArr[] = '';
                                        }
                                    }
                                    if(count(array_filter($newDynColumnsArr)) > 0){ // for blank td
                                        $newSheetArr[] = $newDynColumnsArr;
                                    }
                                }
                            }
                        }
                    } 
                }

            $this->dowdloadExcel($newSheetArr);
        }

        public function dowdloadExcel($newSheetArr) {
            $type = "xlsx";
            return Excel::create('costing', function($excel) use ($newSheetArr) {
            $excel->sheet('Sheet', function($sheet) use ($newSheetArr)
            {

                foreach($newSheetArr as $row => $columns) {
                    foreach($columns as $column => $data) {  
                        if(strpos($data, 'img/') !== false) {
                            $objDrawing = new PHPExcel_Worksheet_Drawing();
                            $objDrawing->setName('costing_img');
                            $objDrawing->setDescription('costing_img');
                            $objDrawing->setPath($data);
                            $rowNo = (int)$row+2;
                            $objDrawing->setCoordinates('B'.$rowNo);
                            $objDrawing->setOffsetX(5); 
                            $objDrawing->setOffsetY(5);                
                            $objDrawing->setWidth(80); 
                            $objDrawing->setHeight(80); 
                            $objDrawing->setWorksheet($sheet);
                            $sheet->getRowDimension($rowNo)->setRowHeight(70);
                            $sheet->getColumnDimension('B')->setWidth(40);

                            $newSheetArr[$row][1] = '';
                        }
                    }
                }
                $sheet->fromArray($newSheetArr);
            });
            })->download($type); 
        }

        public function changeIGIStatus(request $request) {
            $data = $request->all();
            $id = $data['id'];
            $status = $data['status'];
            if($status == "reject") {
                    $CostingData = Costingdata::find($id);
                    $CostingData['igi_rejected']= 1;
                    $CostingData->save();   
                    $message = Config::get('constants.message.igi_rejected_success');
            }
            return $message;
        }
}
