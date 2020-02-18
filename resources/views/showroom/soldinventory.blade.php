<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
$inStatusVal = $inventoryStatus['in'];
$outStatusVal = $inventoryStatus['out'];
$soldOutVal = $inventoryStatus['soldout'];
$totalCount = isset($productCollection['totalCount']) ? $productCollection['totalCount'] : 0;
$price = InventoryHelper::getMinMaxPriceForFilter();
$priceStart = isset($price['min_price']) ? $price['min_price'] : 0;
$priceEnd = isset($price['max_price']) ? $price['max_price'] : 0;

$diamondWeight = InventoryHelper::getMinMaxDiamondWeight(NULL, $soldOutVal);
$minDiamondWeight = isset($diamondWeight[0]->min_weight) ? $diamondWeight[0]->min_weight : 0;
$maxDiamondWeight = isset($diamondWeight[0]->max_weight) ? $diamondWeight[0]->max_weight : 0;

$metalWeight = InventoryHelper::getMinMaxMetalWeight(NULL, $soldOutVal);
$minMetalWeight = isset($metalWeight[0]->min_weight) ? $metalWeight[0]->min_weight : 0;
$maxMetalWeight = isset($metalWeight[0]->max_weight) ? $metalWeight[0]->max_weight : 0;

$total_products = $productCollection['totalCount'];

$totalInProducts = InventoryHelper::getTotalinventoryInOutCount($inStatusVal);
$totalOutProducts = InventoryHelper::getTotalinventoryInOutCount($outStatusVal);
$totalSoldOutProducts = InventoryHelper::getTotalinventoryInOutCount($soldOutVal);
$productCollection = $productCollection['productCollection'];
$allVirtualProductManager = InventoryHelper::getAllVirtualProductManagers();
$imageOptions = InventoryHelper::getImageOptions($soldOutVal);
?>
@extends('layout.mainlayout')

@section('title', 'Sold Products')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<!-- <link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css"> -->
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/css/ion.rangeSlider.min.css"/>
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
	  {{ Breadcrumbs::render('showroom.soldinventory') }}
	  <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div class="col-md-12 widget-holder loader-area" style="display: none;">
	<div class="widget-bg text-center">
	  <div class="loader"></div>
	</div>
  </div>
  <div class="widget-list ">
	<div class="row">
	  <div class="col-md-12 widget-holder content-area">
		<div class="widget-bg">
		  <div class="widget-heading clearfix">
			<h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">Sold Products</h5>
		  </div>
		  <div class="widget-body clearfix">
			  <div class="row m-0 label-text-pl-25">
				  <div class="tabs w-100">
					  <ul class="nav nav-tabs">
						<li class="nav-item active"><a class="nav-link" href="#inventory-filter" data-toggle="tab" aria-expanded="true">Filter</a>
						</li>
						<li class="nav-item"><a class="nav-link" href="#inventory-bulk-upload" data-toggle="tab" aria-expanded="true">Bulk Upload</a>
						</li>
					</ul>
					<div class="tab-content p-3 border border-top-0">

						<div class="tab-pane active" id="inventory-filter">
							<div class="row custom-drop-style custom-select-style label-text-pl-25 ">
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group price-filter">
									<div class='dropdown' id='pricerange'>
									  <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Price<span class='caret'></span></button>
									  <ul class='dropdown-menu'>
										<li>
										  <div class="form-group px-2">
											<input type="text" id="priceStart" data_price_init_start="<?php echo $priceStart; ?>" data_start_custom="<?php echo ShowroomHelper::currencyFormat($priceStart); ?>" class="form-control" value="<?php echo number_format((float) $priceStart, 2, '.', '') ?>"/>
											<input type="text" id="priceEnd" data_price_init_to="<?php echo $priceEnd; ?>" data_to_custom="<?php echo ShowroomHelper::currencyFormat($priceEnd); ?>" class="form-control" value="<?php echo number_format((float) $priceEnd, 2, '.', '') ?>"/>
										  </div>
										  <div class="form-group px-3">
										  <input type="text" id="priceFilter" name="priceFilter" value="" />
										  </div>
										</li>
									  </ul>
									</div>
								   </div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group category-filter filter-section filter-section">
									  <?php
echo InventoryHelper::getCategoryFilter();
?>
								  </div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								<div class="form-group gold-purity-filter filter-section filter-section">
									<?php
echo InventoryHelper::getGoldPurity();
?>
								</div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group metalweight-filter">
									<div class='dropdown' id='metalweightrange'>
									  <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Gold Wt<span class='caret'></span></button>
									  <ul class='dropdown-menu price-filter'>
										<li>
										  <div class="form-group px-2">
										  <input type="text" id="minMetalWeight" data_metalweight_init_start="<?php echo $minMetalWeight; ?>" data_start_metalweight_custom="<?php echo $minMetalWeight; ?>" name="minMetalWeight" class="form-control" value="<?php echo $minMetalWeight; ?>"/>
										  <input type="text" id="maxMetalWeight" data_metalweight_init_to="<?php echo $maxMetalWeight; ?>" data_to_metalweight_custom="<?php echo $maxMetalWeight; ?>" class="form-control" value="<?php echo $maxMetalWeight; ?>" name="maxMetalWeight"/>
										  </div>
										  <div class="form-group px-3">
										  <input type="text" id="metalWeightFilter" name="metalWeightFilter" value="" />
										  </div>
										</li>
									  </ul>
									</div>
								  </div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								<div class="form-group gold-color-filter filter-section filter-section">
									<?php
echo InventoryHelper::getGoldColor();
?>
								</div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group diamond-quality-filter filter-section filter-section">
									  <?php
echo InventoryHelper::getDiamondQuality();
?>
								  </div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group diamondweight-filter">
									<div class='dropdown' id='diamondweightrange'>
									  <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Dia. Weight<span class='caret'></span></button>
									  <ul class='dropdown-menu price-filter'>
										<li>
										  <div class="form-group px-2">
										  <input type="text" id="minDiamondWeight" data_diaweight_init_start="<?php echo $minDiamondWeight; ?>" data_start_diaweight_custom="<?php echo $minDiamondWeight; ?>" name="minDiamondWeight" class="form-control" value="<?php echo $minDiamondWeight; ?>"/>
										  <input type="text" id="maxDiamondWeight" data_diaweight_init_to="<?php echo $maxDiamondWeight; ?>" data_to_diaweight_custom="<?php echo $maxDiamondWeight; ?>" class="form-control" value="<?php echo $maxDiamondWeight; ?>" name="maxDiamondWeight"/>
										  </div>
										  <div class="form-group px-3">
										  <input type="text" id="diamondWeightFilter" name="diamondWeightFilter" value="" />
										  </div>
										</li>
									  </ul>
									</div>
								  </div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group diamond-shape-filter filter-section filter-section">
									  <?php
echo InventoryHelper::getDiamondShape();
?>
								  </div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group virtual-product--manager-filter filter-section filter-section">
										<select class="text-uppercase virtualproductmanager" id="virtualproductmanager" name="virtualproductmanager">
											<option value="">Virtual Box</option>
											<?php
foreach ($allVirtualProductManager as $value) {

	?>
												<option value="<?php echo $value->product_manager_id ?>"><?php echo $value->product_manager_name ?></option>
											<?php
}
?>
										</select>
								  </div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group approval-person-filter filter-section filter-section">
									<?php
$approvalType = Config::get('constants.approval_type');
?>
									  <select class="text-uppercase approval_type" id="approval_type" name="approval_type">
										  <option value="">Approval Type</option>
										  <?php foreach ($approvalType as $key => $value): ?>
											  <option value="<?=$key?>"><?=$value?></option>
										  <?php endforeach;?>
									  </select>
								  </div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group approval-person-filter filter-section filter-section">
									  <input type="text" class="form-control" id="approvalPerson" name="approvalPerson" placeholder="Approval Person Name" style="height: calc(2em + .75rem + 2px);" />
								  </div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group product-image-manager-filter filter-section filter-section">
										<select class="text-uppercase productimagemanager" id="productimagemanager" name="productimagemanager">
											<option value="">Image</option>
											<?php foreach ($imageOptions as $key => $value) {?>
												<option value="<?=$key?>" id="<?=($key == 1) ? 'with_image' : 'without_image'?>"><?=$value?></option>
											<?php }?>
										</select>
								  </div>
							  </div>
							  <div class="col-xl-2 col-sm-4">
								  <div class="form-group">
										<button class="btn w-100 btn-primary" id="btn-apply-filter" type="button">Apply</button>
								  </div>
							  </div>
						</div>
					  </div>
						<div class="tab-pane" id="inventory-bulk-upload">
						  <div class="row custom-drop-style custom-select-style">
							<div class="col-xl-3 col-sm-6 mb-3 mb-md-0 px-sm-4">
							<div class="input-group">
							  <div class="input-group-btn width-90">
								<div class="fileUpload btn w-100 btn-default height-35 lineheight-16">
								  <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
								  <input id="uploadBtn" type="file" class="upload" name="certificatecsv"/>
								</div>
							  </div>
							  <input id="certificate_file" name="certificate_file" class="form-control border bg-transparent" placeholder="Choose File" disabled="disabled">
							</div>
							</div>
							<div class="col-xl-3 col-sm-6 mb-3 mb-md-0 px-sm-4 ">
								<select class="form-control height-35" id="bulk-inventory-action" name="bulk-inventory-action">
									  <option value="sales_return">Sales Return</option>
									  <option value="cancel_invoice">Cancel Invoice</option>
									  <option value="generate_pdf_wp">Generate Pdf With Price</option>
									  <option value="generate_pdf_wop">Generate Pdf Without Price</option>
									  <option value="category_pdf_with_price">Generate Category Pdf With Price</option>
                                    	<option value="category_pdf_without_price">Generate Category Pdf Without Price</option>
								</select>
							</div>
							<div class="col-xl-3 col-sm-6 px-sm-4">
								<button class="btn btn-primary small-btn-style" id="btn-bulk-operation" type="button">Submit</button>
							</div>
						  </div>
						</div>
					</div>
				  </div>
				</div>
				<div class="row">
				  <div class="col-md-12 mt-3" id="selectedfilter">
					<div class="bootstrap-tagsinput space-five-all">
					</div>
				  </div>
				</div>
					</div>
				</div>
			</div>
		</div>

		<!--Start Csv Code -->
		<!-- <button name="export_csv" onclick="getSoldInventoryCsv()">Export Csv </button> -->

		<div class="row">
		  <div class="col-md-12 widget-holder content-area">

			  <div class="widget-bg">
				  <!-- <div class="widget-heading clearfix">
					  <h5>{{'Showroom'}}</h5>
				  </div> -->
				  <!-- /.widget-heading -->
				  <div class="widget-body clearfix">
					  @if ($message = Session::get('success'))
					  <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						<i class="material-icons list-icon">check_circle</i>
						<strong>Success</strong>: {{ $message }}
					  </div>
					  @endif
					  <div class="alert alert-icon alert-danger border-danger alert-dismissible fade" role="alert" style="display:none;">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						<i class="material-icons list-icon">not_interested</i>
						<span class='message'></span>
					  </div>
					  <div class="alert alert-icon alert-success border-success alert-dismissible fade" role="alert" style="display: none;">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						<i class="material-icons list-icon">check_circle</i>
						<span class='message'></span>
					  </div>
			<?php
$tableClass = '';
if (count($productCollection) > 0) {
	$tableClass = 'display:block;';
}

?>
					  <table class="table table-striped table-center table-head-box thumb-sm checkbox checkbox-primary custom-scroll" id="inventoryProductsTable">
						  <thead>
							  <tr class="bg-primary">
								  <th class="checkboxth"><label><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct" ><span class="label-text"></span></label></th>
								  <th>Image</th>
								  <th>SKU</th>
								  <th>Certificate</th>
								  <th>Category</th>
								  <th>Diamond Quality</th>
								  <!-- <th>Name</th> -->
								  <th>Virtual Product Position</th>
								  <th>Price</th>
								  <th>Status</th>
								  <th>Name</th>
								  <th>Action</th>
							  </tr>
						  </thead>
						  <tbody>
							  @foreach ($productCollection as $key => $product)
								<?php
$startDate = '';
$endDate = '';
$orderId = '';
$invoiceData = array();
$isReturned = 0;
$imageDirectory = config('constants.dir.website_url_for_product_image');
//print_r(config('constants.dir.website_url_for_product_image'));exit;
$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
$defaultProductImage = $imageDirectory . 'def_1.png';

//$invoiceMemoDetail = ShowroomHelper::getProductInvoiceMemoDetail($product->entity_id);
$isReturned = isset($product->is_returned) ? $product->is_returned : 0;
$product_approval_memo_generated = (!empty($product->approval_memo_generated) ? 1 : 0);
$product_approval_invoice_generated = (!empty($product->approval_invoice_generated) ? 1 : 0);
$product_return_memo_generated = (!empty($product->return_memo_generated) ? 1 : 0);
$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);

$categoryName = isset($categoryNames[0]->category_name) ? $categoryNames[0]->category_name : '';
$position = strpos($product->sku, ' ');
$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
$inventoryStatusOption = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0");
//print_r($inventoryStatusOption);exit;
$inventoryStatuaArr = array();
foreach ($inventoryStatusOption as $key => $value) {
	$inventoryStatuaArr[$value->option_id] = $value->value;
}
$inventoryStatus = '';
$inventoryStatus = isset($inventoryStatuaArr[$product->inventory_status]) ? $inventoryStatuaArr[$product->inventory_status] : '-';
$pendingOrderData = InventoryHelper::getOrderByProduct($product->entity_id, 'pending');

$orderDate = '';
$firstName = '';
$lastName = '';
$customerName = '';

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
	$orderId = isset($completedOrderData[0]->order_id) ? $completedOrderData[0]->order_id : '';
	foreach ($completedOrderData as $key => $order) {
		$orderDate = $order->created_at;
		$firstName = $order->customer_firstname;
		$lastName = $order->customer_lastname;
		$customerName = $firstName . ' ' . $lastName;
	}
}
if (!empty($orderId)) {
	$invoiceData = InventoryHelper::getInvoiceData($orderId);
	$invoiceCreateDate = isset($invoiceData->created_at) ? $invoiceData->created_at : '';
	$startDate = date('Y-m-t', strtotime($invoiceCreateDate));
	$endDate = date('Y-m-d');
}
if (count($pendingOrderData) == 0 && count($completedOrderData) == 0) {
	$customerName = 'N/A';
}
?>
							  <tr  href="javascript:void(0)" >
								<td><label><input data-productid="<?=$product->entity_id;?>" class="form-check-input <?=(empty($orderId)) ? 'disabledChk' : 'chkProduct'?>" data-id="{{$orderId}}" value="{{$orderId}}" type="checkbox" name="chkProduct[]" id="chkProduct{{$orderId}}" <?=(empty($orderId)) ? 'disabled' : ''?>/><span class="label-text"></label></td>
								<td class="Product_detail"  data-id="<?php echo $product->certificate_no; ?>"><img src="{{!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
								<td class="Product_detail"  data-id="<?php echo $product->certificate_no; ?>"><?php echo $sku; ?></td>
								<td class="Product_detail"  data-id="<?php echo $product->certificate_no; ?>">{{$product->certificate_no}}</td>
								<td class="Product_detail"  data-id="<?php echo $product->certificate_no; ?>">{{$categoryName}}</td>
								<td class="Product_detail"  data-id="<?php echo $product->certificate_no; ?>"><?php echo !empty($product->rts_stone_quality) ? $product->rts_stone_quality : '-'; ?></td>
								<td class="Product_detail"  data-id="<?php echo $product->certificate_no; ?>">{{$virtualproductposition}}</td>
								<td class="Product_detail"  data-id="<?php echo $product->certificate_no; ?>">{{ShowroomHelper::currencyFormat(round($product->custom_price))}}</td>
								<td class="Product_detail"  data-id="<?php echo $product->certificate_no; ?>">{{$inventoryStatus}}</td>
								<td class="Product_detail"  data-id="<?php echo $product->certificate_no; ?>">{{$customerName}}</td>
								<td>
								  <a title="Sales Return" class="color-content table-action-style1 <?=($isReturned == 1 || empty($orderId)) ? 'disabled' : ''?>" href="{{ route('salesreturn',['id'=>$orderId]) }}"><i class="list-icon fa fa-retweet"></i></a>

								  <?php
$disabledClass = '';
if ($startDate < $endDate || empty($orderId)) {
	$disabledClass = 'disabled';
}
?><a title="Cancel Invoice" class="color-content table-action-style1 btn-cancel-invoice pointer <?=$disabledClass?>" data-productid="{{$product->entity_id}}" data-orderid="{{$orderId}}" data-href="{{ route('cancelinvoice',['id'=>$orderId]) }}"><i class="list-icon fa fa-trash-o"></i></a>
								</td>
							  </tr>
							 @endforeach
						  </tbody>
						  <tfoot>
						  <tr>
							  <th><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct"></th>
							  <th>Image</th>
							  <th>SKU</th>
							  <th>Certificate</th>
							  <th>Category</th>
							  <th>Diamond Quality</th>
							  <!-- <th>Name</th> -->
							  <th>Virtual Product Position</th>
							  <th>Price</th>
							  <th>Status</th>
							  <th>Name</th>
							  <th>Action</th>
						  </tr>
						  </tfoot>
					  </table>
				  </div>
				  <!-- /.widget-body -->
			  </div>
			  <!-- /.widget-bg -->
		  </div>
		  <!-- /.widget-holder -->
	  </div>
	  <!-- /.row -->
	  </div>

  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->

<input type="hidden" id="getInvoiceMemoModalAction" value="<?=URL::to('/inventory/getinvoicememomodalcontent');?>">
<input type="hidden" id="generateReturnMemoAction" value="<?=URL::to('/inventory/generatereturnmemo');?>">
<input type="hidden" id="exportProductExcelAction" value="<?=URL::to('/inventory/exportproductexcel');?>">
<input type="hidden" id="exportProductCsvAction" value="<?=URL::to('/inventory/exportproductcsv');?>">
<input type="hidden" id="changeInventoryStatusAction" value="<?=URL::to('/inventory/changeinventorystatus');?>">
<input type="hidden" id="exportProductPdfAction" value="<?=URL::to('/inventory/exportproductpdf');?>">
<input type="hidden" id="categorypdf" value="<?=URL::to('/inventory/categorypdf');?>">
<input type="hidden" id="getInventoryProductCountAction" value="<?=URL::to('/inventory/getinventoryproductcount');?>">
<input type="hidden" id="inventoryAjax" value="<?=URL::to('/showroom/soldinventoryajaxlist');?>">
<input type="hidden" name="filterapplied" id="filterapplied" value="false">
<input type="hidden" name="isPaginaged" id="isPaginaged" value="true">
<input type="hidden" id="getprominentfilterAction" value="<?=URL::to('/showroom/getprominentfilter');?>">
<input type="hidden" id="generateQuotationAction" value="<?=URL::to('/inventory/generatequotation');?>">
<input type="hidden" id="storeProductIds" value="<?=URL::to('/inventory/storeproductids');?>">
<input type="hidden" id="getCsvAction" value="<?=URL::to('/showroom/getSoldInventoryCsv');?>">
<!-- Large Modal -->
<div class="modal fade bs-modal-lg" id="invoice-memo-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			{!! Form::open(array('method'=>'POST','id'=>'invoicememo-generate-form','class'=>'form-horizontal','autocomplete'=>'nope','enctype'=>'multipart/form-data')) !!}

			{!! Form::close() !!}
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>

<div class="modal fade bs-modal-lg modal-color-scheme ProductDetail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-lg">
	  <div class="modal-content">
		<button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
		  <div class="modal-header text-inverse">
			  <h5 class="modal-title" id="myLargeModalLabel">Product List</h5>
		  </div>
  <div class="modal-body">
  </div>
  <div class="modal-footer">
	  <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
  </div>
</div>
<!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
			<!-- /.modal -->

			<div class="modal fade bs-modal-lg modal-color-scheme category_pdf" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-sm">
      <div class="modal-content">
          <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
          <div class="modal-header text-inverse">
              <h5 class="modal-title" id="myLargeModalLabel">Category PDF</h5>
          </div>
  <div class="modal-body"> 
  </div>
  <div class="modal-footer"> 
      <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
  </div>
</div>
<!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<!-- <script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/dataTables.responsive.min.js"></script> -->
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/js/ion.rangeSlider.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script type="text/javascript">
var clearAllFilter = false;
$(document).ready(function(){
	localStorage.clear();
	//store filter section to local storage
	$(".filter-section").each(function (index, element) {
	   var id = $(element).children().attr('id');
	   localStorage.removeItem(id+'_sold_inventory');
	   localStorage.setItem(id+'_sold_inventory', $("#"+id)[0].outerHTML);
	});
});
var appliedFilterType = '';
var inventoryProductsTable = $('#inventoryProductsTable').DataTable({
	"aLengthMenu": [[25,50,100,200,300,500,1000], [25,50,100,200,300,500,1000]],
  "iDisplayLength": 50,
  "dom": '<"datatable_top_custom_lengthinfo custom-page-length d-flex flex-wrap"i   <"datatable_top_showroom_length mx-3"l> <"#inventory-toolbar">>frtip',
  'createdRow': function( row, data, dataIndex,certificate_no ) {
	  $('td',row).addClass('Product_detail');
	  //$(row).addClass('common_tr');
	  $('td',row).eq(0).removeClass('Product_detail');
	   $('td',row).eq(10).removeClass('Product_detail');
	  $('td',row).attr('data-id',data[3] );
	  //$('.common_tr').val();
	},
  "language": {
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
    "search": "_INPUT_",
    "searchPlaceholder": "Search",
    "lengthMenu": "Show _MENU_",
    "info": "Showing _START_ to _END_ of _TOTAL_"
    //"sProcessing": "<div id='loader'></div>"
  },
  "deferLoading": <?=$totalCount?>,
  "processing": true,
  "serverSide": true,
  "serverMethod": "post",
  "ajax":{
	"url": $("#inventoryAjax").val(),
	"data": function(data, callback){
	  // Append to data
	  var categoryIds = [];
	  var goldPurity = [];
	  var goldColor = [];
	  var diamondQuality = [];
	  var diamondShape = [];
	  data._token = "{{ csrf_token() }}";
	  $.each($(".category_chkbox:checked"), function(){
		categoryIds.push($(this).val());
	  });
	  $.each($(".chk_metalquality:checked"), function(){
		  goldPurity.push($(this).val());
	  });
	  $.each($(".chk_metalcolor:checked"), function(){
		  goldColor.push($(this).val());
	  });
	  $.each($(".chk_diamondquality:checked"), function(){
		  diamondQuality.push($(this).val());
	  });
	  $.each($(".chk_diamondshape:checked"), function(){
		  diamondShape.push($(this).val());
	  });

	  var virtualproducts = $('#virtualproductmanager').val();
	  if(virtualproducts != ''){
		data.virtualproducts = virtualproducts;
	  }

	  var productimages = $('#productimagemanager').val();
	  if(productimages != '') {
		data.productimages = productimages;
	  }

	  var stockstatus = $('#stockstatus').val();
	  if(stockstatus != ''){
		data.stockstatus = stockstatus;
	  }
	  data.category = categoryIds;
	  data.gold_purity = goldPurity;
	  data.gold_color = goldColor;
	  data.diamond_quality = diamondQuality;
	  data.diamond_shape = diamondShape;
	  data.price_start = $("#priceStart").val();
	  data.price_to = $("#priceEnd").val();
	  data.diaweight_start = $("#minDiamondWeight").val();
	  data.diaweight_to = $("#maxDiamondWeight").val();
	  data.metalweight_start = $("#minMetalWeight").val();
	  data.metalweight_to = $("#maxMetalWeight").val();
	  data.approval_person = $("#approvalPerson").val();
	  data.approval_type = $("#approval_type").val();

	  showLoader();
	  $(".dropdown").removeClass('show');
	  $(".dropdown-menu").removeClass('show');
	},
	complete: function(response){

	  hideLoader();
	  if(!clearAllFilter)
	  {
		getProminentFilters();
	  }
	  else
	  {
		  var storageExistFlag = true;
		  $(".filter-section").each(function (index, element) {
			   var id = $(element).children().attr('id');
			   var html = localStorage.getItem(id+'_sold_inventory', $("#"+id).html());
			   $("#"+id).html(html);
			   if(html == '')
			   {
					storageExistFlag = false;
					return false;
			   }
		  });
		  if(!storageExistFlag)
			getProminentFilters();
	  }
	  loadajaxfilterblock();
	}
  },
  "columnDefs": [
	  { "orderable": false, "targets": [0,1,10] }
  ],
  //"responsive": true
});
$divContainer = $('<div class="inventory-action-container"/>').appendTo('#inventory-toolbar')

$select = $('<select class="mx-2 mr-3 height-35 padding-four" id="inventory-status"/>').appendTo($divContainer)
/*$('<option data-code="<?=$inStatusVal?>"/>').val('in').text('In').appendTo($select);
$('<option data-code="<?=$outStatusVal?>"/>').val('out').text('Out').appendTo($select);*/

$('<option data-code=""/>').val('sales_return').text('Sales Return').appendTo($select);
$('<option/>').val('product_excel').text('Product Excel').appendTo($select);
$('<option data-code=""/>').val('cancel_invoice').text('Cancel Invoice').appendTo($select);
/*$('<option/>').val('invoice').text('Generate Invoice').appendTo($select);
//$('<option/>').val('memo').text('Generate Memo').appendTo($select);
$('<option/>').val('return_memo').text('Return Memo').appendTo($select);*/
/*$('<option/>').val('product_excel').text('Product Excel').appendTo($select);
$('<option/>').val('export_csv').text('Export CSV').appendTo($select);
$('<option/>').val('quotation').text('Quotation').appendTo($select);*/
$('<option/>').val('generate_pdf_wp').text('Generate Pdf With Price').appendTo($select);
$('<option/>').val('generate_pdf_wop').text('Generate Pdf Without Price').appendTo($select);
$('<option/>').val('category_pdf_with_price').text('Generate Pdf Category With Price').appendTo($select);
$('<option/>').val('category_pdf_without_price').text('Generate Pdf Category Without Price').appendTo($select);
$('<button class="btn btn-primary height-35" type="button" id="btn-change-inventory-status"/>').text('Submit').appendTo($divContainer);

$('<button class="btn btn-primary height-35 export_contain lh-28" title="Download Certificate CSV" type="button" name="export_csv" onclick="getSoldInventoryCsv()"/>').html('<i class="fa fa-download fs-16"></i>').appendTo($divContainer);

$('.dataTables_filter input')
  .unbind() // Unbind previous default bindings
  .bind("input", function(e) { // Bind our desired behavior
	  // If the length is 3 or more characters, or the user pressed ENTER, search
	  if(this.value.length >= 3 || e.keyCode == 13) {
		  // Call the API search function
		  appliedFilterType = 'div';
		  inventoryProductsTable.search(this.value).draw();
	  }
	  // Ensure we clear the search if they backspace far enough
	  if(this.value == "") {
		  inventoryProductsTable.search("").draw();
	  }
	  return;
});
$("#inventory-toolbar").addClass("submit-area d-inline-block");
$("#inventoryProductsTable_length").addClass('mt-0');
$("#inventoryProductsTable_length select[name=inventoryProductsTable_length]").addClass('height-35');
$("#inventoryProductsTable tr .checkboxth").removeClass('sorting_asc');
var priceSlider = $("#priceFilter").ionRangeSlider({
	  type: "double",
	  skin: "round",
	  grid: false,
	  keyboard: true,
	  force_edges: false,
	  prettify_enabled: true,
	  prettify_separator: ',',
	  min: <?php echo $priceStart ?>,
	  max: <?php echo $priceEnd ?>,
	  from: <?php echo $priceStart ?>,
	  to: <?php echo $priceEnd ?>,
	  onChange: function (data) {
		  $("#priceStart").val(data.from);
		  $("#priceEnd").val(data.to);
		  //showroomProductsTable.draw();
	  },
	  onFinish: function (data) {
		  $("#priceStart").val(data.from);
		  $("#priceEnd").val(data.to);
		  //showroomProductsTable.draw();
	  },
});
var diaweightSlider = $("#diamondWeightFilter").ionRangeSlider({
	  type: "double",
	  skin: "round",
	  grid: false,
	  keyboard: true,
	  force_edges: false,
	  step: 0.01,
	  prettify_enabled: true,
	  prettify_separator: ',',
	  min: <?php echo $minDiamondWeight ?>,
	  max: <?php echo $maxDiamondWeight ?>,
	  from: <?php echo $minDiamondWeight ?>,
	  to: <?php echo $maxDiamondWeight ?>,
	  onChange: function (data) {
		  $("#minDiamondWeight").val(data.from);
		  $("#maxDiamondWeight").val(data.to);
	  },
	  onFinish: function (data) {
		  $("#minDiamondWeight").val(data.from);
		  $("#maxDiamondWeight").val(data.to);
	  },
});
var metalweightSlider = $("#metalWeightFilter").ionRangeSlider({
	  type: "double",
	  skin: "round",
	  grid: false,
	  keyboard: true,
	  force_edges: false,
	  step: 0.01,
	  prettify_enabled: true,
	  prettify_separator: ',',
	  min: <?php echo $minMetalWeight ?>,
	  max: <?php echo $maxMetalWeight ?>,
	  from: <?php echo $minMetalWeight ?>,
	  to: <?php echo $maxMetalWeight ?>,
	  onChange: function (data) {
		  $("#minMetalWeight").val(data.from);
		  $("#maxMetalWeight").val(data.to);
	  },
	  onFinish: function (data) {
		  $("#minMetalWeight").val(data.from);
		  $("#maxMetalWeight").val(data.to);
	  },
});
var pricerangeslider = $("#priceFilter").data("ionRangeSlider");
var diaweightrangeslider = $("#diamondWeightFilter").data("ionRangeSlider");
var metalweightrangeslider = $("#metalWeightFilter").data("ionRangeSlider");
//$(".category_chkbox,#virtualproductmanager,#stockstatus,.chk_metalquality,.chk_metalcolor,.chk_diamondquality").change(function(){
var isClearAllFilter = false;
$(document).on('change', '.category_chkbox,#virtualproductmanager,#stockstatus,.chk_metalquality,.chk_metalcolor,.chk_diamondquality,.chk_diamondshape,#productimagemanager',function(){
  $('#filterapplied').val('true');
  var inputClass = $(this).attr('class');
  inputClass = inputClass.replace('text-uppercase','');
  appliedFilterType = inputClass.trim();
  getProminentFilters();
  isClearAllFilter = false;
});
$(document).on('click','.Product_detail',function() {
	var ids = $(this).attr('data-id');
	var type = 'post';
	var url ="{{action('ShowroomController@showroomproductlist')}}";
	var token = '{{ csrf_token() }}';
	var id = ids;
	var modalview = "show";

	var list = ajaxdataproductlist(type,url,token,id,modalview);

  });
$(document).on("click",".btn-cancel-invoice",function(){
	var orderIds = new Array();
	var productIds = new Array();
	productIds.push($(this).data('productid'));
	orderIds.push($(this).data('orderid'));
	productIds = productIds.join(',');
	orderIds = orderIds.join(',');
	swal({
		title: 'Are you sure?',
		text: "<?php echo Config::get('constants.message.invoice_cancellation_confirmation'); ?>",
		type: 'info',
		showCancelButton: true,
		confirmButtonText: 'Confirm',
		confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
		}).then(function() {
			if(orderIds != '')
			{
				$.ajax({
					url:'<?=URL::to('/showroom/cancelbulkinvoice');?>',
					method:"post",
					data:{orderIds: orderIds,productIds:productIds,_token: "{{ csrf_token() }}"},
					beforeSend: function()
					{
					  showLoader();
					},
					success: function(response){
						hideLoader();
						var res = JSON.parse(response);
						if(res.status)
						{
							swal({
							  title: 'Success',
							  text: res.message,
							  type: 'success',
							  buttonClass: 'btn btn-primary'
							});
							inventoryProductsTable.draw();
						}
						else
						{
							swal({
							  title: 'Oops!',
							  text: res.message,
							  type: 'error',
							  showCancelButton: true,
							  showConfirmButton: false,
							  confirmButtonClass: 'btn btn-danger',
							  cancelButtonText: 'Ok'
							});
						}
					}
				})
			}
	});
});
//For price filter
$("#priceEnd").on("blur",function(event){
	event.stopImmediatePropagation();
	if (!this.value.match(/[a-z]/i)) {
		// alphabet letters found
	  if(parseFloat($('#priceStart').val()) > parseFloat($('#priceEnd').val()))
	  {
		$('#priceStart').val($("#priceStart").attr('data_price_init_start'));
		$('#priceEnd').val($("#priceEnd").attr('data_price_init_to')) ;
	  }
	  var lowest_price = parseFloat($('#priceStart').val());
	  var highest_price = parseFloat($('#priceEnd').val());
	  var default_highest_price = parseFloat($("#priceEnd").attr('data_price_init_to'));
	  if(highest_price > default_highest_price)
	  {
		$('#priceEnd').val(default_highest_price);
		return false;
	  }
	  pricerangeslider.update({
		  from: $("#priceStart").val(),
		  to: this.value
	  });
	  if($("#priceEnd").val() < lowest_price || $("#priceEnd").val() > highest_price){
		  $("#priceEnd").val(highest_price);
	  }
	  //loadajaxfilterblock();
	  //showroomProductsTable.draw();
	  return;
	}
});
//For diamond weight filter
$("#maxDiamondWeight").on("blur",function(event){
	event.stopImmediatePropagation();
	if (!this.value.match(/[a-z]/i)) {
		// alphabet letters found
	  if(parseFloat($('#minDiamondWeight').val()) > parseFloat($('#maxDiamondWeight').val()))
	  {
		$('#minDiamondWeight').val($("#minDiamondWeight").attr('data_diaweight_init_start'));
		$('#maxDiamondWeight').val($("#maxDiamondWeight").attr('data_diaweight_init_to')) ;
	  }
	  var lowest_diaweight = parseFloat($('#minDiamondWeight').val());
	  var highest_diaweight = parseFloat($('#maxDiamondWeight').val());
	  var default_highest_diaweight = parseFloat($("#maxDiamondWeight").attr('data_diaweight_init_to'));
	  if(highest_diaweight > default_highest_diaweight)
	  {
		$('#maxDiamondWeight').val(default_highest_diaweight);
		return false;
	  }
	  diaweightrangeslider.update({
		  from: $("#minDiamondWeightStart").val(),
		  to: this.value
	  });
	  if($("#maxDiamondWeight").val() < lowest_diaweight || $("#maxDiamondWeight").val() > highest_diaweight){
		  $("#getMinMaxDiamondWeight").val(highest_diaweight);
	  }
	  //loadajaxfilterblock();
	  //showroomProductsTable.draw();
	  return;
	}
});
//Gold weight filter
$("#maxMetalWeight").on("blur",function(event){
	event.stopImmediatePropagation();
	if (!this.value.match(/[a-z]/i)) {
		// alphabet letters found
	  if(parseFloat($('#minMetalWeight').val()) > parseFloat($('#maxMetalWeight').val()))
	  {
		$('#minMetalWeight').val($("#minMetalWeight").attr('data_metalweight_init_start'));
		$('#maxMetalWeight').val($("#maxMetalWeight").attr('data_metalweight_init_to')) ;
	  }
	  var lowest_metalweight = parseFloat($('#minMetalWeight').val());
	  var highest_metalweight = parseFloat($('#maxMetalWeight').val());
	  var default_highest_metalweight = parseFloat($("#maxMetalWeight").attr('data_metalweight_init_to'));
	  if(highest_metalweight > default_highest_metalweight)
	  {
		$('#maxMetalWeight').val(default_highest_metalweight);
		return false;
	  }
	  metalweightrangeslider.update({
		  from: $("#minMetalWeightStart").val(),
		  to: this.value
	  });
	  /*if($("#maxMetalWeight").val() < lowest_metalweight || $("#maxMetalWeight").val() > highest_metalweight){
		  $("#getMinMaxMetalWeight").val(highest_metalweight);
	  }*/
	  //loadajaxfilterblock();
	  //showroomProductsTable.draw();
	  return;
	}
});

function getProminentFilters()
{
  var categoryIds = [];
  var goldPurity = [];
  var diamondQuality = [];
  var goldColor = [];
  var diamondShape = [];
  $("#filterapplied").val('false');
  $.each($(".category_chkbox:checked"), function(){
		categoryIds.push($(this).val());
  });
  $.each($(".chk_metalquality:checked"), function(){
		goldPurity.push($(this).val());
  });
  $.each($(".chk_diamondquality:checked"), function(){
		diamondQuality.push($(this).val());
  });
  $.each($(".chk_diamondshape:checked"), function(){
		diamondShape.push($(this).val());
  });
  $.each($(".chk_metalcolor:checked"), function(){
		goldColor.push($(this).val());
  });
  var virtualproducts = $("#virtualproductmanager").val();
  var productimages = $("#productimagemanager").val();
  $.ajax({
		type: "POST",
		dataType: "json",
		data: {
		  category : categoryIds,
		  gold_purity : goldPurity,
		  gold_color : goldColor,
		  diamond_quality : diamondQuality,
		  diamond_shape : diamondShape,
		  stockstatus : $("#stockstatus").val(),
		  price_start : $("#priceStart").val(),
		  price_to : $("#priceEnd").val(),
		  diaweight_start : $("#minDiamondWeight").val(),
		  diaweight_to : $("#maxDiamondWeight").val(),
		  metalweight_start : $("#minMetalWeight").val(),
		  metalweight_to : $("#maxMetalWeight").val(),
		  filterapplied : $('#filterapplied').val(),
		  virtualproducts: virtualproducts,
		  productimages:productimages,
		  approval_person: $("#approvalPerson").val(),
		  approval_type: $("#approval_type").val(),
		  search_value: $("#inventoryProductsTable_filter input").val(),
		  stock_status: 'Sold Out',
		  _token: "{{ csrf_token() }}"
		},
		url: $("#getprominentfilterAction").val(),
		beforeSend: function()
		{
		  //showLoader();
		  $(".dropdown").removeClass('show');
		  $(".dropdown-menu").removeClass('show');
		},
		success: function(data) {
		  if(appliedFilterType == '')
			appliedFilterType = 'div';
		  if($("."+appliedFilterType).attr("name") != 'diamondQualityChkbox')
			$('#diamondquality_area').replaceWith(data.diamond_quality_filters);

		  if($("."+appliedFilterType).attr("name") != 'diamondShapeChkbox')
			$('#diamondshape_area').replaceWith(data.diamond_shape_filters);

		  if($("."+appliedFilterType).attr("name") != 'category_chkbox')
			$('#category_area').replaceWith(data.category_filters);

		  if($("."+appliedFilterType).attr("name") != 'metalQualityChkbox')
			$('#goldpurity_area').replaceWith(data.gold_purity_filters);

		  if($("."+appliedFilterType).attr("name") != 'metalColorChkbox')
			$('#goldcolor_area').replaceWith(data.gold_colors_filters);

		  if($("#stockstatus").val()=='')
			$('#stockstatus').replaceWith(data.status_filters);

		  if($('#virtualproductmanager').val()=='')
			$('#virtualproductmanager').replaceWith(data.virtual_filters);
			  var product_images = JSON.parse(data.productimage_filters);
			  if(product_images.with_image !='undefined')
			  {
				  $("#productimagemanager").find("#with_image").text("<?=config('constants.product_image_pdf.image_options.with_image')?>("+product_images.with_image+")");
			  }
			  if(product_images.without_image !='undefined')
			  {
				  $("#productimagemanager").find("#without_image").text("<?=config('constants.product_image_pdf.image_options.without_image')?>("+product_images.without_image+")");
				  //$('#without_image').text("<?=config('constants.product_image_pdf.image_options.without_image')?>("+product_images.without_image+")");
			  }
		  if(data.diamond_weight_filters[0].min_weight && data.diamond_weight_filters[0].max_weight)
		  {
			  diaweightrangeslider.update({
				  from: data.diamond_weight_filters[0].min_weight,
				  to: data.diamond_weight_filters[0].max_weight
			  });
			  //$("#minDiamondWeight").attr('data_diaweight_init_start',data.diamond_weight_filters[0].min_weight);
			  $("#minDiamondWeight").attr('data_start_diaweight_custom',data.diamond_weight_filters[0].min_weight);
			  $("#minDiamondWeight").val(data.diamond_weight_filters[0].min_weight);

			  //$("#maxDiamondWeight").attr('data_diaweight_init_to',data.diamond_weight_filters[0].max_weight);
			  $("#maxDiamondWeight").attr('data_to_diaweight_custom',data.diamond_weight_filters[0].max_weight);
			  $("#maxDiamondWeight").val(data.diamond_weight_filters[0].max_weight);
		  }
		  if(data.gold_weight_filters[0].min_weight && data.gold_weight_filters[0].max_weight)
		  {
			  metalweightrangeslider.update({
				  from: data.gold_weight_filters[0].min_weight,
				  to: data.gold_weight_filters[0].max_weight
			  });
			  //$("#minMetalWeight").attr('data_metalweight_init_start',data.gold_weight_filters[0].min_weight);
			  $("#minMetalWeight").attr('data_start_metalweight_custom',data.gold_weight_filters[0].min_weight);
			  $("#minMetalWeight").val(data.gold_weight_filters[0].min_weight);

			  //$("#maxMetalWeight").attr('data_metalweight_init_to',data.gold_weight_filters[0].max_weight);
			  $("#maxMetalWeight").attr('data_to_metalweight_custom',data.gold_weight_filters[0].max_weight);
			  $("#maxMetalWeight").val(data.gold_weight_filters[0].max_weight);
		  }
		  //hideLoader();
	  }
  });
}
function loadajaxfilterblock()
{
	var filters = new Object();

	starter = 0;

	var start = Math.floor($("#priceStart").val());
	var orignal_start = Math.floor($("#priceStart").attr('data_price_init_start'));
	var start_custom = Math.floor($("#priceStart").val());
	var to_custom = Math.floor($("#priceEnd").val());
	var final_start =  start_custom.toLocaleString('en-IN', {
		maximumFractionDigits: 2,
		style: 'currency',
		currency: 'INR'
	});
	final_start_mod = final_start.replace("₹", " Rs.");
	var final_to =  to_custom.toLocaleString('en-IN', {
		maximumFractionDigits: 2,
		style: 'currency',
		currency: 'INR'
	});
	final_to_mod = final_to.replace("₹", "Rs.");
	var to = Math.floor($("#priceEnd").val());
	var orignal_to = Math.floor($("#priceEnd").attr('data_price_init_to'));
	//if(start != orignal_start || to != orignal_to)
	if((!isClearAllFilter && $("#isPaginaged").val()=='false') || (start != orignal_start || to != orignal_to))
	{
	  prstart = $("#priceStart").val();
	  prto =  $("#priceEnd").val();
	  filters['price'] = 'Price:' + final_start_mod + '-' + final_to_mod;
	}
	//Diamond filter
	var start_diaweight = $("#minDiamondWeight").val();
	var orignal_start_diaweight = $("#minDiamondWeight").attr('data_diaweight_init_start');
	var start_custom_diaweight = $("#minDiamondWeight").val();
	var to_custom_diaweight = $("#maxDiamondWeight").val();
	var to_diaweight = $("#maxDiamondWeight").val();
	var orignal_to_diaweight = $("#maxDiamondWeight").attr('data_diaweight_init_to');
	//if(start_diaweight != orignal_start_diaweight || to_diaweight != orignal_to_diaweight)
	if((!isClearAllFilter && $("#isPaginaged").val()=='false') || (start_diaweight != orignal_start_diaweight || to_diaweight != orignal_to_diaweight))
	{
	   diaweightstart = $("#minDiamondWeight").val();
	   diaweightto =  $("#maxDiamondWeight").val();
	   filters['diaweight'] = 'Diamond Weight: ' + start_diaweight + '-' + to_diaweight;
	}
	if($("#approvalPerson").val() != '')
	{
	  filters['approval_person'] = 'Approval Person: '+$("#approvalPerson").val();
	}
	if($("#approval_type").val() != '')
	{
	  filters['approval_type'] = 'Approval Type: '+$("#approval_type option:selected" ).text();
	}

	//Gold weight filter
	var start_metalweight = $("#minMetalWeight").val();
	var orignal_start_metalweight = $("#minMetalWeight").attr('data_metalweight_init_start');
	var start_custom_metalweight = $("#minMetalWeight").val();
	var to_custom_metalweight = $("#maxMetalWeight").val();
	var to_metalweight = $("#maxMetalWeight").val();
	var orignal_to_metalweight = $("#maxMetalWeight").attr('data_metalweight_init_to');

	//if(start_metalweight != orignal_start_metalweight || to_metalweight != orignal_to_metalweight)
	if((!isClearAllFilter && $("#isPaginaged").val()=='false') || (start_metalweight != orignal_start_metalweight || to_metalweight != orignal_to_metalweight))
	{
	   metalweightstart = $("#minMetalWeight").val();
	   metalweightto =  $("#maxMetalWeight").val();
	   filters['metalweight'] = 'Gold Weight: ' + start_metalweight + '-' + to_metalweight;
	}

	$(".showroom-filter-checkbox input:checkbox:checked").each(function(){
	  var name = $(this).next().html();
	  var val = this.value;
	  filters[val] = name;
	});
	if(typeof($("#stockstatus").val()) != 'undefined')
	{
	  var name = $("#stockstatus option:selected").text();
	  var val =  'stockstatus';
	  filters[val] = name.replace('Status','');
	}
	if(typeof($("#virtualproductmanager").val()) != 'undefined')
	{
	  var virtualvalues = $("#virtualproductmanager").val();
	  $("#virtualproductmanager option[value='virtualvalues']").attr("selected","selected");
	  if(virtualvalues != ''){
		var name = jQuery( "#virtualproductmanager option:selected" ).text();
	  }else{
		var name = jQuery( "#virtualproductmanager option:selected" ).val();
	  }
	  var val =  'virtualproductmanager';
	  filters[val] = name;
	}
	if(typeof($("#productimagemanager").val()) != 'undefined')
	{
	  var productimagevalues = $("#productimagemanager").val();
	  $("#productimagemanager option[value='productimagevalues']").attr("selected","selected");
	  if(productimagevalues != ''){
		var name = jQuery( "#productimagemanager option:selected" ).text();
	  }else{
		var name = jQuery( "#productimagemanager option:selected" ).val();
	  }
	  var val =  'productimagemanager';
	  filters[val] = name;
	}
	$("#selectedfilter .bootstrap-tagsinput").html('');
	var filterFlag = false;

	for (var x in filters) {
		if(filters[x] != '')
		{
/*          console.log(x);
		  console.log(filters[x]);
*/        var div = '';
		  var div = "<span class='tag label label-info'>"+filters[x]+"";
		  if(x == 'virtualproductmanager'){
			if(filters[x] == '1'){
			  var div = "<span class='tag label label-info'>ROUND";
			} else if(filters[x] == '2'){
			  var div = "<span class='tag label label-info'>ROUND & FANCY";
			} else if(filters[x] == '3'){
			  var div = "<span class='tag label label-info'>FANCY";
			}
		  }
		  else if(x == 'stockstatus'){
			if(filters[x] == '1'){
			  var div = "<span class='tag label label-info'>DML INSTOCK";
			} else if(filters[x] == '2'){
			  var div = "<span class='tag label label-info'>DML SOLD";
			} else if(filters[x] == '3'){
			  var div = "<span class='tag label label-info'>FRANCHISE INSTOCK";
			} else if(filters[x] == '4'){
			  var div = "<span class='tag label label-info'>FRANCHISE SOLD";
			}
		  } else {
			var div = "<span class='tag label label-info'>"+filters[x]+"";
		  }
		  if(x == 'price')
		  {
			div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('price')\" data-type=" + x +"></span>";
		  }
		  else if(x == 'diaweight')
		  {
			div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('diaweight')\" data-type=" + x +"></span>";
		  }
		  else if(x == 'metalweight')
		  {
			div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('metalweight')\" data-type=" + x +"></span>";
		  }
		  else if(x=='stockstatus')
		  {
			div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('stockstatus')\" data-type=" + x +"></span>";
		  }
		  else if(x=='virtualproductmanager')
		  {
			div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('virtualproductmanager')\" data-type=" + x +"></span>";
		  }
		  else if(x=='productimagemanager')
		  {
			div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('productimagemanager')\" data-type=" + x +"></span>";
		  }
		  else if(x == 'approval_person')
		  {
			div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('approval_person')\" data-type=" + x +"></span>";
		  }
		  else
		  {
			div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('"+x+"')\" data-type=" + x +"></span></span>";
		  }
		  $("#selectedfilter .bootstrap-tagsinput").append(div);
		  filterFlag = true;
		}
	  }
	  if(filterFlag)
	  {
		  var div = "<span class='tag label label-info'>Clear All";
		  div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('all')\"></span></span>";
	  }
	  $("#selectedfilter .bootstrap-tagsinput").append(div);
}
//To clear filter
function clearfilter(filterType)
{
  $('#filterapplied').val('false');
  if(filterType == "all")
  {
	  filtershowroom("all");
	  resetfilteroptions("all");
	  isClearAllFilter = true;
	  clearAllFilter = true;
	  return;
  }
  else
  {
	var resetfilter = filterType;
	filtershowroom(resetfilter);
	resetfilteroptions(resetfilter);
	isClearAllFilter = false;
  }

}
function filtershowroom(resetfilter)
{
	  if(typeof(resetfilter) != 'undefined')
	  {
		if(resetfilter == 'virtualproductmanager'){
		  $('#virtualproductmanager').val('');
		}
		else if(resetfilter == 'productimagemanager'){
		  $('#productimagemanager').val('');
		}
		else if(resetfilter == 'stockstatus'){
		  $('#stockstatus').val('');
		}
		else if(resetfilter == 'price')
		{
		  $("#priceStart").val($("#priceStart").attr('data_price_init_start'));
		  $("#priceEnd").val($("#priceEnd").attr('data_price_init_to'));
		  var intStart = Math.floor($("#priceStart").attr('data_price_init_start'));
		  var intEnd = Math.floor($("#priceEnd").attr('data_price_init_to'));
		  //To update price slider values
		  pricerangeslider.update({
			  from: intStart,
			  to: intEnd
		  });
		}
		else if(resetfilter == 'diaweight')
		{
		  $("#minDiamondWeight").val($("#minDiamondWeight").attr('data_diaweight_init_start'));
		  $("#maxDiamondWeight").val($("#maxDiamondWeight").attr('data_diaweight_init_to'));
		  var intStart = Math.floor($("#minDiamondWeight").attr('data_diaweight_init_start'));
		  var intEnd = Math.floor($("#maxDiamondWeight").attr('data_diaweight_init_to'));
		  //To update diamondweight slider values
		  diaweightrangeslider.update({
			  from: intStart,
			  to: intEnd
		  });
		}
		else if(resetfilter == 'metalweight')
		{
		  $("#minMetalWeight").val($("#minMetalWeight").attr('data_metalweight_init_start'));
		  $("#maxMetalWeight").val($("#maxMetalWeight").attr('data_metalweight_init_to'));
		  var intStart = Math.floor($("#minMetalWeight").attr('data_metalweight_init_start'));
		  var intEnd = Math.floor($("#maxMetalWeight").attr('data_metalweight_init_to'));
		  //To update metal weight slider values
		  metalweightrangeslider.update({
			  from: intStart,
			  to: intEnd
		  });
		}
		else if(resetfilter == 'approval_person')
		{
		  $("#approvalPerson").val('');
		}
		else if(resetfilter == 'approval_type')
		{
		  $("#approval_type").val('');
		}
		else
		{
		  ///$(".category_chkbox:checkbox[data-filtertype='"+resetfilter+"']").prop( "checked", false );
		  $(".showroom-filter-checkbox input:checkbox[value='"+resetfilter+"']").prop( "checked", false );
		  $("#minMetalWeight").val($("#minMetalWeight").attr('data_metalweight_init_start'));
		  $("#maxMetalWeight").val($("#maxMetalWeight").attr('data_metalweight_init_to'));
		  var intStart = Math.floor($("#minMetalWeight").attr('data_metalweight_init_start'));
		  var intEnd = Math.floor($("#maxMetalWeight").attr('data_metalweight_init_to'));


		  //To update metal weight slider values
		  metalweightrangeslider.update({
			  from: $("#minMetalWeight").attr('data_metalweight_init_start'),
			  to: $("#maxMetalWeight").attr('data_metalweight_init_to')
		  });
		  $("#minDiamondWeight").val($("#minDiamondWeight").attr('data_diaweight_init_start'));
		  $("#maxDiamondWeight").val($("#maxDiamondWeight").attr('data_diaweight_init_to'));
		  var intStart = Math.floor($("#minDiamondWeight").attr('data_diaweight_init_start'));
		  var intEnd = Math.floor($("#maxDiamondWeight").attr('data_diaweight_init_to'));
		  //To update diamondweight slider values
		  diaweightrangeslider.update({
			  from: $("#minDiamondWeight").attr('data_diaweight_init_start'),
			  to: $("#maxDiamondWeight").attr('data_diaweight_init_to')
		  });
		}
	  }
	  filters = {};
	  filters['price_start'] = $("#priceStart").val();
	  filters['price_to'] = $("#priceEnd").val();

	  //diamond weight filter
	  filters['diaweight_start'] = $("#minDiamondWeight").val();
	  filters['diaweight_to'] = $("#maxDiamondWeight").val();

	  //gold weight filter
	  filters['metalweight_start'] = $("#minMetalWeight").val();
	  filters['metalweight_to'] = $("#maxMetalWeight").val();

	  var starter = 0;
	  $(".showroom-filter-checkbox input:checkbox:checked").each(function(){
		var name = this.name;
		var val = this.value;
		starter = parseInt(starter) + 1;
		if(typeof(filters[name]) == 'undefined')
		{
		  filters[name] = {};
		  starter = 0;
		}
		if(val.length > 0)
		{
		  filters[name][parseInt(starter)] = val;
		}
	  });

	  filters['virtualproductmanager'] = $('#virtualproductmanager').val();
	  filters['productimagemanager'] = $('#productimagemanager').val();
	  filters['stockstatus'] = $('#stockstatus').val();
	  if(typeof(resetfilter) != 'undefined')
	  {
		if(resetfilter == 'all')
		{
		  $(".category_chkbox:checkbox").prop( "checked", false );
		  $(".showroom-filter-checkbox input").prop( "checked", false );
		  $("#virtualproductmanager").val('');
		  $("#productimagemanager").val('');
		  $("#stockstatus").val('');
		  $("#approval_type").val('');
		  $("#approvalPerson").val('');
		  $("#priceStart").val($("#priceStart").attr('data_price_init_start'));
		  $("#priceEnd").val($("#priceEnd").attr('data_price_init_to'));
		  var filters = {};
		  var intStart = Math.floor($("#priceStart").attr('data_price_init_start'));
		  var intEnd = Math.floor($("#priceEnd").attr('data_price_init_to'));
		  pricerangeslider.update({
			  from: intStart,
			  to: intEnd
		  });

		  //diamond weight filter
		  $("#minDiamondWeight").val($("#minDiamondWeight").attr('data_diaweight_init_start'));
		  $("#maxDiamondWeight").val($("#maxDiamondWeight").attr('data_diaweight_init_to'));
		  var intStart = Math.floor($("#minDiamondWeight").attr('data_diaweight_init_start'));
		  var intEnd = Math.floor($("#maxDiamondWeight").attr('data_diaweight_init_to'));
		  //To update diamondweight slider values
		  diaweightrangeslider.update({
			  from: intStart,
			  to: intEnd
		  });

		  //metal weight filter
		  $("#minMetalWeight").val($("#minMetalWeight").attr('data_metalweight_init_start'));
		  $("#maxMetalWeight").val($("#maxMetalWeight").attr('data_metalweight_init_to'));
		  var intStart = Math.floor($("#minMetalWeight").attr('data_metalweight_init_start'));
		  var intEnd = Math.floor($("#maxMetalWeight").attr('data_metalweight_init_to'));
		  //To update metal weight slider values
		  metalweightrangeslider.update({
			  from: intStart,
			  to: intEnd
		  });
		}
	  }
	  showLoader();
	  var start = Math.floor($("#priceStart").val());
	  var orignal_start = Math.floor($("#priceStart").attr('data_price_init_start'));
	  var to = Math.floor($("#priceEnd").val());
	  var orignal_to = Math.floor($("#priceEnd").attr('data_price_init_to'));
}
function resetfilteroptions(resetfilter)
{
	var filters = {} ;
	filters['price_start'] = $("#priceStart").val();
	filters['price_to'] = $("#priceEnd").val();

	filters['diaweight_start'] = $("#minDiamondWeight").attr('data_diaweight_init_start');
	filters['diaweight_to'] = $("#maxDiamondWeight").attr('data_diaweight_init_to');

	filters['metalweight_start'] = $("#minMetalWeight").attr('data_metalweight_init_start');
	filters['metalweight_to'] = $("#maxMetalWeight").attr('data_metalweight_init_to');

	if(typeof(resetfilter) != 'undefined')
	{
	  if(resetfilter == 'all')
	  {
		var filters = {};
		  $("#minMetalWeight").val($("#minMetalWeight").attr('data_metalweight_init_start'));
		  $("#maxMetalWeight").val($("#maxMetalWeight").attr('data_metalweight_init_to'));

		  $("#minDiamondWeight").val($("#minDiamondWeight").attr('data_diaweight_init_start'));
		  $("#maxDiamondWeight").val($("#maxDiamondWeight").attr('data_diaweight_init_to'));


		  //To update metal weight slider values
		  metalweightrangeslider.update({
			  from: $("#minMetalWeight").attr('data_metalweight_init_start'),
			  to: $("#maxMetalWeight").attr('data_metalweight_init_to')
		  });

		  diaweightrangeslider.update({
			  from: $("#minDiamondWeight").attr('data_diaweight_init_start'),
			  to: $("#maxDiamondWeight").attr('data_diaweight_init_to')
		  });
	  }else if(resetfilter == 'virtualproductmanager'){
		filters[resetfilter] = "";
	  }else if(resetfilter == 'productimagemanager'){
		filters[resetfilter] = "";
	  }
	  else if(resetfilter == 'stockstatus'){
		filters[resetfilter] = "";
	  }else if(resetfilter == 'approval_person'){
		filters[resetfilter] = "";
	  }else{
		filters[resetfilter] = 0;
	  }
	}
	inventoryProductsTable.draw();
}
$("#uploadBtn").change(function () {
	var fileExtension = ['csv'];
	if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
		swal({
			title: 'Oops!',
			text: '<?php echo Config::get('constants.message.inventory_bulk_upload_invalid_file'); ?>',
			type: 'error',
			showCancelButton: true,
			showConfirmButton: false,
			confirmButtonClass: 'btn btn-danger',
			cancelButtonText: 'Ok'
		});
	}
});
//For bulk inventory action
$("#btn-bulk-operation").click(function(){
	var action = $('#bulk-inventory-action option:selected').val();
	var productIds = new Array();
	var file_data = $('#uploadBtn').prop('files')[0];
	var fileLength = $("#uploadBtn")[0].files.length;
	var form_data = new FormData();
	form_data.append('file', file_data);
	form_data.append('_token',"{{ csrf_token() }}");
	form_data.append('is_soldinventory',"true");

	if(fileLength>0)
	{
	  if(action=="sales_return")
	  {
		  $.ajax({
			type: 'POST',
			contentType: false,
			dataType: "text",
			data: form_data,
			processData: false,
			url: '<?=URL::to('/inventory/getproductids');?>',
			beforeSend: function()
			{
			  showLoader();
			},
			success: function(response){
			  var res = JSON.parse(response);
			  if(res.status)
			  {
				  productIds = res.product_id;
				  var orderIds = res.order_id;
				  if(productIds!='')
				  {
					$.ajax({
						url:'<?=URL::to('/showroom/generatebulksalesreturn');?>',
						method:"post",
						data:{orderIds: orderIds,productIds: productIds,_token: "{{ csrf_token() }}"},
						beforeSend: function()
						{
						  showLoader();
						},
						success: function(response){
							hideLoader();
							var res = JSON.parse(response);

							if(res.status)
							{
								window.location.href = '<?=URL::to('/showroom/bulksalesreturn');?>';
							}
							else
							{
							  swal({
								  title: 'Are you sure?',
								  text: res.message,
								  type: 'error',
								  showCancelButton: true,
								  showConfirmButton: false
								  });
							}
						}
					  })
				  }
				  else
				  {
					  swal({
						title: 'Are you sure?',
						text: "<?php echo Config::get('constants.message.inventory_generate_memo_product_not_selected'); ?>",
						type: 'error',
						showCancelButton: true,
						showConfirmButton: false
						});
				  }
			  }
			},
		  });
	  }
	  else if(action == "cancel_invoice")
	  {
		  $.ajax({
			type: 'POST',
			contentType: false,
			dataType: "text",
			data: form_data,
			processData: false,
			url: '<?=URL::to('/inventory/getproductids');?>',
			beforeSend: function()
			{
			  showLoader();
			},
			success: function(response){
			  var res = JSON.parse(response);
			  if(res.status)
			  {
				  productIds = res.product_id;
				  var orderIds = res.order_id;
				  if(productIds!='')
				  {
					  $.ajax({
						  url:'<?=URL::to('/showroom/cancelbulkinvoice');?>',
						  method:"post",
						  data:{orderIds: orderIds,productIds:productIds,_token: "{{ csrf_token() }}"},
						  beforeSend: function()
						  {
							showLoader();
						  },
						  success: function(response){
							  hideLoader();
							  var res = JSON.parse(response);
							  if(res.status)
							  {
								  swal({
									title: 'Success',
									text: res.message,
									type: 'success',
									buttonClass: 'btn btn-primary'
								  });
								  inventoryProductsTable.draw();
							  }
							  else
							  {
								  swal({
									title: 'Oops!',
									text: res.message,
									type: 'error',
									showCancelButton: true,
									showConfirmButton: false,
									confirmButtonClass: 'btn btn-danger',
									cancelButtonText: 'Ok'
								  });
							  }
						  }
						})
				  }
				  else
				  {
					  swal({
						title: 'Are you sure?',
						text: "<?php echo Config::get('constants.message.inventory_generate_memo_product_not_selected'); ?>",
						type: 'error',
						showCancelButton: true,
						showConfirmButton: false
						});
				  }
			  }
			},
		  });
	  }
	  else if(action=="return_memo")
	  {
		   $("#operation_type").val(action);
		  $.ajax({
			  type: 'POST',
			  contentType: false,
			  dataType: "text",
			  data: form_data,
			  processData: false,
			  url: '<?=URL::to('/inventory/getproductids');?>',
			  beforeSend: function()
			  {
				showLoader();
			  },
			  success: function(response){
				  var res = JSON.parse(response);
				  if(res.status)
				  {
					  productIds = res.product_id;
					  if(productIds != '')
					  {
						  $.ajax({
							url:$("#generateReturnMemoAction").val(),
							method:"post",
							data:{productIds: productIds,_token: "{{ csrf_token() }}"},
							beforeSend: function()
							{
							  showLoader();
							},
							success: function(response){

								hideLoader();
								inventoryProductsTable.draw();
								var res = JSON.parse(response);
								if(res.status)
								{
									swal({
									  title: 'Success',
									  text: res.message,
									  type: 'success',
									  buttonClass: 'btn btn-primary'
									  //showSuccessButton: true,
									  //showConfirmButton: false,
									  //successButtonClass: 'btn btn-primary',
									  //successButtonText: 'Ok'
									});
								}
								else
								{
									swal({
									  title: 'Oops!',
									  text: res.message,
									  type: 'error',
									  showCancelButton: true,
									  showConfirmButton: false,
									  confirmButtonClass: 'btn btn-danger',
									  cancelButtonText: 'Ok'
									});
								}
							}
						  })
					  }
					  else
					  {
						  swal({
							title: 'Are you sure?',
							text: "<?php echo Config::get('constants.message.inventory_generate_invoicememo_product_not_selected'); ?>",
							type: 'info',
							showCancelButton: true,
							showConfirmButton: false
						  });
					  }
				  }
			  }
		  });
	  }
		else if(action=="generate_pdf_wp") {
		 var price = 'wp';
		  $("#operation_type").val(action);
		  $.ajax({
			  type: 'POST',
			  contentType: false,
			  dataType: "text",
			  data: form_data,
			  processData: false,
			  url: '<?=URL::to('/inventory/getproductids');?>',
			  success: function(response){
				  var res = JSON.parse(response);
				  if(res.status)
				  {
					  productIds = res.product_id;
					  if(productIds != '') {

						 $.ajax({
							type: 'POST',
							data:{productIds: productIds,_token: "{{ csrf_token() }}"},
							url: '<?=URL::to('/inventory/checkcertificatelimit');?>',
							success: function(response){
								var res = JSON.parse(response);
								if(res.status) {

								  var url = $("#exportProductPdfAction").val()+'?productIds='+productIds+'&&price='+price;
								  window.location.href = url;
								}
								else {
								 swal({
									  title: 'Oops!',
									  text: res.message,
									  type: 'error',
									  showCancelButton: true,
									  showConfirmButton: false,
									  confirmButtonClass: 'btn btn-danger',
									  cancelButtonText: 'Ok'
									});
								}
							  }
							});



					  }
					  else {
						  swal({
							title: 'Are you sure?',
							text: "<?php echo Config::get('constants.message.inventory_generate_invoicememo_product_not_selected'); ?>",
							type: 'info',
							showCancelButton: true,
							showConfirmButton: false
						  });
					  }
				  }
			  }
		  });
	  }

	   else if(action=="generate_pdf_wop") {
		 var price = 'wop';
		  $("#operation_type").val(action);
		  $.ajax({
			  type: 'POST',
			  contentType: false,
			  dataType: "text",
			  data: form_data,
			  processData: false,
			  url: '<?=URL::to('/inventory/getproductids');?>',
			  success: function(response){
				  var res = JSON.parse(response);
				  if(res.status)
				  {
					  productIds = res.product_id;
					  if(productIds != '') {
						$.ajax({
							type: 'POST',
							data:{productIds: productIds,_token: "{{ csrf_token() }}"},
							url: '<?=URL::to('/inventory/checkcertificatelimit');?>',
							success: function(response){
								var res = JSON.parse(response);
								if(res.status) {

								  var url = $("#exportProductPdfAction").val()+'?productIds='+productIds+'&&price='+price;
								  window.location.href = url;
								}
								else {
								 swal({
									  title: 'Oops!',
									  text: res.message,
									  type: 'error',
									  showCancelButton: true,
									  showConfirmButton: false,
									  confirmButtonClass: 'btn btn-danger',
									  cancelButtonText: 'Ok'
									});
								}
							  }
							});
					  }
					  else {
						  swal({
							title: 'Are you sure?',
							text: "<?php echo Config::get('constants.message.inventory_generate_invoicememo_product_not_selected'); ?>",
							type: 'info',
							showCancelButton: true,
							showConfirmButton: false
						  });
					  }
				  }
			  }
		  });
	  }

	  else if(action == 'quotation')
	  {
		  $.ajax({
			type: 'POST',
			contentType: false,
			dataType: "text",
			data: form_data,
			processData: false,
			url: '<?=URL::to('/inventory/getproductids');?>',
			beforeSend: function()
			{
			  showLoader();
			},
			success: function(response){
			  var res = JSON.parse(response);
			  if(res.status)
			  {
				 var productIds = res.product_id;
				 $.ajax({
					url:$("#storeProductIds").val(),
					method:"post",
					data:{productIds: productIds,_token: "{{ csrf_token() }}"},
					beforeSend: function()
					{
					  showLoader();
					},
					success: function(response){
						hideLoader();
						var res = JSON.parse(response);
						if(res.status)
						{
							//editQuotationAction
							var url = '';
							if($("#quotationId").val()!='')
							{
							  url = $("#editQuotationAction").val();
							}
							else
							{
							  url = $("#generateQuotationAction").val();
							}
							window.location.href = url;
						}
						else
						{
							swal({
							  title: 'Oops!',
							  text: res.message,
							  type: 'error',
							  showCancelButton: true,
							  showConfirmButton: false,
							  confirmButtonClass: 'btn btn-danger',
							  cancelButtonText: 'Ok'
							});
						}
					}
				  })
			  }
			}
		  });
	  }


	  else if(action=="category_pdf_with_price") { 

        $.ajax({
          type: 'POST',
          contentType: false,
          dataType: "text",
          data: form_data,
          processData: false,
          url: '<?=URL::to('/inventory/getproductids');?>',
          beforeSend: function()
          {
            showLoader();
          },
          success: function(response){
            var res = JSON.parse(response);
             hideLoader();
            if(res.status)
            {
               var ids = res.product_id;
               var price = 'wp';
               jQuery.ajax({
                type: "GET",
                dataType: "json",
                url: $('#categorypdf').val(),
                data: {
                "_token": '{{ csrf_token() }}',
                "id": ids,
                "price":price
                },
                success: function(data) {
                    $('.modal-body').html(data.html);
                    $('.category_pdf').modal('show');
                }
              });
            }
          }
        });
      }

      else if(action=="category_pdf_without_price") { 

        $.ajax({
          type: 'POST',
          contentType: false,
          dataType: "text",
          data: form_data,
          processData: false,
          url: '<?=URL::to('/inventory/getproductids');?>',
          beforeSend: function()
          {
            showLoader();
          },
          success: function(response){
            var res = JSON.parse(response);
             hideLoader();
            if(res.status)
            {
               var ids = res.product_id;
               var price = 'wop';
               jQuery.ajax({
                type: "GET",
                dataType: "json",
                url: $('#categorypdf').val(),
                data: {
                "_token": '{{ csrf_token() }}',
                "id": ids,
                "price":price
                },
                success: function(data) {
                    $('.modal-body').html(data.html);
                    $('.category_pdf').modal('show');
                }
              });
            }
          }
        });
      }


	}


	else
	{
	  swal({
		  title: 'Oops!',
		  text: '<?php echo Config::get('constants.message.inventory_generate_invoicememo_csv_not_selected'); ?>',
		  type: 'error',
		  showCancelButton: true,
		  showConfirmButton: false,
		  confirmButtonClass: 'btn btn-danger',
		  cancelButtonText: 'Ok'
		});
	}

});
//For inventory action like change status,generate memo, etc
$("#btn-change-inventory-status").click(function(){
	var action = $('#inventory-status option:selected').val();
	var orderIds = new Array();
	var productIds = new Array();
	jQuery.each($(".chkProduct:checked"), function() {
		orderIds.push(jQuery(this).val());
		productIds.push(jQuery(this).data('productid'));
	});
	var ids = orderIds.join(",");
	productIds = productIds.join(",");

	if(action=="sales_return"){
		if(ids!='')
		{
		  $.ajax({
			  url:'<?=URL::to('/showroom/generatebulksalesreturn');?>',
			  method:"post",
			  data:{orderIds: ids,productIds: productIds,_token: "{{ csrf_token() }}"},
			  beforeSend: function()
			  {
				showLoader();
			  },
			  success: function(response){
				  hideLoader();
				  var res = JSON.parse(response);

				  if(res.status)
				  {
					  window.location.href = '<?=URL::to('/showroom/bulksalesreturn');?>';
				  }
				  else
				  {
					swal({
						title: 'Are you sure?',
						text: res.message,
						type: 'error',
						showCancelButton: true,
						showConfirmButton: false
						});
				  }
			  }
			})
		}
		else
		{
			swal({
			  title: 'Are you sure?',
			  text: "<?php echo Config::get('constants.message.inventory_generate_memo_product_not_selected'); ?>",
			  type: 'error',
			  showCancelButton: true,
			  showConfirmButton: false
			  });
		}
	}
	else if(action == "product_excel") {
		if(ids!='')
		{
		  var url = $("#exportProductExcelAction").val()+'?productIds='+ids;
		  window.location.href = url;
		}
		else
		{
			swal({
			  title: 'Are you sure?',
			  text: "<?php echo Config::get('constants.message.inventory_export_excel_product_not_selected'); ?>",
			  type: 'info',
			  showCancelButton: true,
			  confirmButtonText: 'Confirm',
			  confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
			  }).then(function() {
				  var url = $("#exportProductExcelAction").val();
				  window.location.href = url;
			  });
		}
	}
	else if(action == "cancel_invoice")
	{
		if(ids!='')
		{
		  //var url = $("#generateReturnMemoAction").val()+'?productIds='+ids;
		  //window.location.href = url;
		  $.ajax({
			  url:'<?=URL::to('/showroom/cancelbulkinvoice');?>',
			  method:"post",
			  data:{orderIds: ids,productIds:productIds,_token: "{{ csrf_token() }}"},
			  beforeSend: function()
			  {
				showLoader();
			  },
			  success: function(response){
				  hideLoader();
				  var res = JSON.parse(response);
				  if(res.status)
				  {
					  swal({
						title: 'Success',
						text: res.message,
						type: 'success',
						buttonClass: 'btn btn-primary'
					  });
					  inventoryProductsTable.draw();
				  }
				  else
				  {
					  swal({
						title: 'Oops!',
						text: res.message,
						type: 'error',
						showCancelButton: true,
						showConfirmButton: false,
						confirmButtonClass: 'btn btn-danger',
						cancelButtonText: 'Ok'
					  });
				  }
			  }
			})
		}
		else
		{
			swal({
			  title: 'Are you sure?',
			  text: "<?php echo Config::get('constants.message.inventory_generate_memo_product_not_selected'); ?>",
			  type: 'error',
			  showCancelButton: true,
			  showConfirmButton: false
			  });
		}
	}
	else if(action == "export_csv") {
		if(productIds!='')
		{
		  var url = $("#exportProductCsvAction").val()+'?productIds='+productIds;
		  window.location.href = url;
		}
		else
		{
			swal({
			  title: 'Are you sure?',
			  text: "<?php echo Config::get('constants.message.inventory_export_csv_product_not_selected'); ?>",
			  type: 'info',
			  showCancelButton: true,
			  confirmButtonText: 'Confirm',
			  confirmButtonClass: 'btn-confirm-all-productcsv btn btn-info'
			  }).then(function() {
				  var url = $("#exportProductCsvAction").val();
				  window.location.href = url;
			});
		}
	}
	 else if(action == 'generate_pdf_wp') {
		var price = 'wp';
		if(ids!='')
		{
		  var url = $("#exportProductPdfAction").val()+'?productIds='+ids+'&&price='+price;
		  window.location.href = url;
		}
		else
		{

		  swal({
			  title: 'Oops!',
			  text: "<?php echo Config::get('constants.message.inventory_export_pdf_product_not_selected'); ?>",
			  type: 'error',
			  showCancelButton: true,
			  showConfirmButton: false,
			  confirmButtonClass: 'btn btn-danger',
			  cancelButtonText: 'Ok'
			});
		}
	}
	else if(action == 'generate_pdf_wop') {
	  var price = 'wop';
	  if(ids!='')
		{
		  var url = $("#exportProductPdfAction").val()+'?productIds='+ids+'&&price='+price;
		  window.location.href = url;
		}
		else
		{

		  swal({
			  title: 'Oops!',
			  text: "<?php echo Config::get('constants.message.inventory_export_pdf_product_not_selected'); ?>",
			  type: 'error',
			  showCancelButton: true,
			  showConfirmButton: false,
			  confirmButtonClass: 'btn btn-danger',
			  cancelButtonText: 'Ok'
			});
		}
	}
	 else if(action == "category_pdf_with_price") {
      var price = 'wp';
      if(productIds!='')
      {
          jQuery.ajax({
          type: "GET",
          dataType: "json",
          url: $('#categorypdf').val(),
          data: {
          "_token": '{{ csrf_token() }}',
          "id": productIds,
          "price":price
          },
          success: function(data) {
              $('.modal-body').html(data.html);
              $('.category_pdf').modal('show');
          }
        });
      }
      else
      {

        swal({
            title: 'Oops!',
            text: "<?php echo Config::get('constants.message.inventory_export_pdf_product_not_selected'); ?>",
            type: 'error',
            showCancelButton: true,
            showConfirmButton: false,
            confirmButtonClass: 'btn btn-danger',
            cancelButtonText: 'Ok'
          });
      }
    }

    else if(action == "category_pdf_without_price") {
      var price = 'wop';
      if(productIds!='')
      {
          jQuery.ajax({
          type: "GET",
          dataType: "json",
          url: $('#categorypdf').val(),
          data: {
          "_token": '{{ csrf_token() }}',
          "id": productIds,
          "price":price
          },
          success: function(data) {
              $('.modal-body').html(data.html);
              $('.category_pdf').modal('show');
          }
        });
      }
      else
      {

        swal({
            title: 'Oops!',
            text: "<?php echo Config::get('constants.message.inventory_export_pdf_product_not_selected'); ?>",
            type: 'error',
            showCancelButton: true,
            showConfirmButton: false,
            confirmButtonClass: 'btn btn-danger',
            cancelButtonText: 'Ok'
          });
      }
    }


	else if(action == "quotation")
	{
		if(ids!='')
		{
		  /*var url = $("#generateQuotationAction").val()+'?productIds='+ids;
		  window.location.href = url;*/
		  $.ajax({
			  url:$("#storeProductIds").val(),
			  method:"post",
			  data:{productIds: ids,_token: "{{ csrf_token() }}"},
			  beforeSend: function()
			  {
				showLoader();
			  },
			  success: function(response){
				  hideLoader();
				  var res = JSON.parse(response);
				  if(res.status)
				  {
					  //editQuotationAction
					  var url = '';
					  if($("#quotationId").val()!='')
					  {
						url = $("#editQuotationAction").val();
					  }
					  else
					  {
						url = $("#generateQuotationAction").val();
					  }
					  window.location.href = url;
				  }
				  else
				  {
					  swal({
						title: 'Oops!',
						text: res.message,
						type: 'error',
						showCancelButton: true,
						showConfirmButton: false,
						confirmButtonClass: 'btn btn-danger',
						cancelButtonText: 'Ok'
					  });
				  }
			  }
			})
		}
		else
		{
			swal({
			  title: 'Are you sure?',
			  text: "<?php echo Config::get('constants.message.inventory_quotation_product_not_selected'); ?>",
			  type: 'info',
			  showCancelButton: true,
			  confirmButtonText: 'Confirm',
			  confirmButtonClass: 'btn-confirm-all-productcsv btn btn-info'
			  }).then(function() {
				  var url = $("#exportProductCsvAction").val();
				  window.location.href = url;
			});
		}
	}
});
$("#chkAllProduct").click(function(){
	$('.chkProduct').prop('checked', this.checked);
});
//For filter
$("#btn-apply-filter").click(function(){
	$("#isPaginaged").val('false');
		// alphabet letters found
	  if(parseFloat($('#priceStart').val()) > parseFloat($('#priceEnd').val()))
	  {
		$('#priceStart').val($("#priceStart").attr('data_price_init_start')) ;
		$('#priceEnd').val($("#priceEnd").attr('data_price_init_to')) ;

	  }

	  var highest_price = parseFloat($('#priceEnd').val());
	  var default_highest_price = parseFloat($("#priceEnd").attr('data_price_init_to'));
	  if(highest_price  > default_highest_price)
	  {
		$('#priceEnd').val(default_highest_price);
		return false;
	  }
	  var lowest_price = parseFloat($('#priceStart').val());
	  var highest_price = parseFloat($('#priceEnd').val());
	  var default_highest_price = parseFloat($("#priceEnd").attr('data_price_init_to'));
	  if(highest_price > default_highest_price)
	  {
		$('#priceEnd').val(default_highest_price);
		return false;
	  }
	  pricerangeslider.update({
		  from: $("#priceStart").val(),
		  to: $("#priceEnd").val()
	  });
	  if($("#priceEnd").val() < lowest_price || $("#priceEnd").val() > highest_price){
		  $("#priceEnd").val(highest_price);
	  }

	  if(parseFloat($('#minDiamondWeight').val()) > parseFloat($('#maxDiamondWeight').val()))
	  {
		$('#minDiamondWeight').val($("#minDiamondWeight").attr('data_diaweight_init_start')) ;
		$('#maxDiamondWeight').val($("#maxDiamondWeight").attr('data_diaweight_init_to')) ;

	  }
	  var highest_diaweight = parseFloat($('#maxDiamondWeight').val());
	  var default_highest_diaweight = parseFloat($("#maxDiamondWeight").attr('data_diaweight_init_to'));
	  if(highest_diaweight > default_highest_diaweight)
	  {
		$('#maxDiamondWeight').val(default_highest_diaweight);
		return false;
	  }
	  var lowest_diaweight = parseFloat($('#minDiamondWeight').val());
	  var highest_diaweight = parseFloat($('#maxDiamondWeight').val());
	  var default_highest_diaweight = parseFloat($("#maxDiamondWeight").attr('data_diaweight_init_to'));
	  diaweightrangeslider.update({
		  from: $("#minDiamondWeight").val(),
		  to: $("#maxDiamondWeight").val()
	  });
	  if($("#maxDiamondWeight").val() < lowest_diaweight || $("#maxDiamondWeight").val() > highest_diaweight){
		  $("#maxDiamondWeight").val(highest_diaweight);
	  }

	  //Gold weight filter
	  if(parseFloat($('#minMetalWeight').val()) > parseFloat($('#maxMetalWeight').val()))
	  {
		$('#minMetalWeight').val($("#minMetalWeight").attr('data_metalweight_init_start')) ;
		$('#maxMetalWeight').val($("#maxMetalWeight").attr('data_metalweight_init_to')) ;

	  }
	  var highest_metalweight = parseFloat($('#maxMetalWeight').val());
	  var default_highest_metalweight = parseFloat($("#maxMetalWeight").attr('data_metalweight_init_to'));
	  if(highest_metalweight > default_highest_metalweight)
	  {
		$('#maxMetalWeight').val(default_highest_metalweight);
		return false;
	  }
	  var lowest_metalweight = parseFloat($('#minMetalWeight').val());
	  var highest_metalweight = parseFloat($('#maxMetalWeight').val());
	  var default_highest_metalweight = parseFloat($("#maxMetalWeight").attr('data_metalweight_init_to'));

	  metalweightrangeslider.update({
		  from: $("#minMetalWeight").val(),
		  to: $("#maxMetalWeight").val()
	  });
	  if($("#maxMetalWeight").val() < lowest_metalweight || $("#maxMetalWeight").val() > highest_metalweight){
		  $("#maxMetalWeight").val(highest_metalweight);
	  }

	  jQuery('#filterapplied').val('true');

	  inventoryProductsTable.draw();
	  loadajaxfilterblock();
	  //filterinventory();
	  return;
});
//Get total in/out products count
function getTotalInventoryProductCount()
{
	$.ajax({
	  type: "GET",
	  dataType: "json",
	  url:$("#getInventoryProductCountAction").val(),
	  data: {_token: "{{ csrf_token() }}"},
	  success: function(response){
		  $(".total-in-products").html(response.in_products);
		  $(".total-out-products").html(response.out_products);
	  }
	});
}

/** custom upload file **/
document.getElementById("uploadBtn").onchange = function () {
	document.getElementById("certificate_file").value = this.value.substring(12);
};


function getSoldInventoryCsv()
{

  var productIds = new Array();
  jQuery.each(jQuery(".chkProduct:checked"), function() {
	  //productIds.push(jQuery(this).val());
	  productIds.push(jQuery(this).attr("data-productid"));
  });
  var ids = productIds.join(",");

  if(ids!='')
		{
		  var url = $("#getCsvAction").val()+'?productIds='+ids;
		  window.location.href = url;
		}
		else
		{
			swal({
			  title: 'Are you sure?',
			  text: "<?php echo Config::get('constants.message.inventory_export_csv_product_not_selected'); ?>",
			  type: 'info',
			  showCancelButton: true,
			  confirmButtonText: 'Confirm',
			  confirmButtonClass: 'btn-confirm-all-productcsv btn btn-info'
			  }).then(function() {
				  var url = $("#getCsvAction").val();
				  window.location.href = url;
			});
		}
}


</script>
<style>
#inventoryProductsTable{overflow-x: auto;display: block;}
</style>
@endsection