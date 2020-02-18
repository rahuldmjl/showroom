<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
//$total_products = ShowroomHelper::getProducts(1);
$price = InventoryHelper::getMinMaxPriceForFilter();
$priceStart = isset($price['min_price']) ? $price['min_price'] : 0;
$priceEnd = isset($price['max_price']) ? $price['max_price'] : 0;

$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
$inStatusVal = $inventoryStatus['in'];
$outStatusVal = $inventoryStatus['out'];
$soldOutVal = $inventoryStatus['soldout'];
$total_products = $productCollection['totalCount'];

$totalInProducts = InventoryHelper::getTotalinventoryInOutCount($inStatusVal);
$totalOutProducts = InventoryHelper::getTotalinventoryInOutCount($outStatusVal);
$totalSoldOutProducts = InventoryHelper::getTotalinventoryInOutCount($soldOutVal);
$productCollection = $productCollection['productCollection'];
$allVirtualProductManager = InventoryHelper::getAllVirtualProductManagers();
?>
@extends('layout.mainlayout')

@section('title', 'Inventory')

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
      {{ Breadcrumbs::render('inventory.index') }}
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
					<div class="widget-body clearfix">
              <div class="row m-0 label-text-pl-25">
                  <div class="tabs w-100">
                      <ul class="nav nav-tabs">
                        <li class="nav-item active"><a class="nav-link" href="#inventory-statistic" data-toggle="tab" aria-expanded="true">Statistics</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#inventory-filter" data-toggle="tab" aria-expanded="true">Filter</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#inventory-bulk-upload" data-toggle="tab" aria-expanded="true">Bulk Upload</a>
                        </li>
                    </ul>
                    <div class="tab-content p-3 border border-top-0">
                        <div class="tab-pane active" id="inventory-statistic">
                              <div class="row">
                                  <div class="col-lg-4 col-sm-6 widget-holder widget-full-height">
                                        <div class="widget-bg bg-primary text-inverse">
                                            <div class="widget-body">
                                                <div class="widget-counter">
                                                    <h6>Total IN Products: <small class="text-inverse"></small></h6>
                                                    <h3 class="h1"><span class="counter total-in-products"><?php echo $totalInProducts; ?></span></h3><i class="material-icons list-icon">shopping_cart</i>
                                                </div>
                                                <!-- /.widget-counter -->
                                            </div>
                                            <!-- /.widget-body -->
                                        </div>
                                        <!-- /.widget-bg -->
                                    </div>

                                    <div class="col-lg-4 col-sm-6 widget-holder widget-full-height">
                                        <div class="widget-bg bg-color-scheme text-inverse">
                                            <div class="widget-body">
                                                <div class="widget-counter">
                                                    <h6>Total OUT Products: <small class="text-inverse"></small></h6>
                                                    <h3 class="h1"><span class="counter total-out-products"><?php echo $totalOutProducts; ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                                </div>
                                                <!-- /.widget-counter -->
                                            </div>
                                            <!-- /.widget-body -->
                                        </div>
                                        <!-- /.widget-bg -->
                                    </div>

                                    <div class="col-lg-4 col-sm-6 widget-holder widget-full-height">
                                        <div class="widget-bg bg-gray text-inverse">
                                            <div class="widget-body">
                                                <div class="widget-counter">
                                                    <h6>Total Sold Out Products: <small class="text-inverse"></small></h6>
                                                    <h3 class="h1"><span class="counter total-soldout-products"><?php echo $totalSoldOutProducts; ?></span></h3><i class="material-icons list-icon">shopping_basket</i>
                                                </div>
                                                <!-- /.widget-counter -->
                                            </div>
                                            <!-- /.widget-body -->
                                        </div>
                                        <!-- /.widget-bg -->
                                    </div>
                                </div>
                        </div>
                        <div class="tab-pane" id="inventory-filter">
                            <div class="row custom-drop-style custom-select-style label-text-pl-25">
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
                                  <div class="form-group category-filter">
                                      <?php
$collection = InventoryHelper::getAllProductsCollection();
$filtered_products = $collection->unique('entity_id')->pluck(['entity_id'])->toArray();
echo InventoryHelper::getCategoryFilter(false, $filtered_products);
?>
                                  </div>
                              </div>
                              <div class="col-xl-2 col-sm-4">
                                <div class="form-group gold-purity-filter">
                                    <?php
echo InventoryHelper::getGoldPurity();
?>
                                </div>
                              </div>
                              <div class="col-xl-2 col-sm-4">
                                <div class="form-group gold-color-filter">
                                    <?php
echo InventoryHelper::getGoldColor();
?>
                                </div>
                              </div>
                              <div class="col-xl-2 col-sm-4">
                                  <div class="form-group diamond-quality-filter">
                                      <?php
echo InventoryHelper::getDiamondQuality();
?>
                                  </div>
                              </div>
                              <div class="col-xl-2 col-sm-4">
                                  <div class="form-group status-filter">
                                        <select class="text-uppercase" id="stockstatus" name="stockstatus">
                                            <option value="">Status</option>
                                            <option value="In">In</option>
                                            <option value="Out">Out</option>
                                            <option value="Sold Out">Sold Out</option>
                                        </select>
                                  </div>
                              </div>
                              <div class="col-xl-2 col-sm-4">
                                  <div class="form-group virtual-product--manager-filter">
                                        <select class="text-uppercase" id="virtualproductmanager" name="virtualproductmanager">
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
                                  <div class="form-group">
                                        <button class="btn w-100 btn-primary" id="btn-apply-filter" type="button">Apply</button>
                                  </div>
                              </div>
                        </div>
                      </div>
                        <div class="tab-pane" id="inventory-bulk-upload">
                          <div class="row custom-drop-style custom-select-style label-text-pl-25">
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
                            <div class="col-xl-3 col-sm-6 mb-3 mb-md-0 px-sm-4 custom-select-style">
                                <select class="form-control height-35" id="bulk-inventory-action" name="bulk-inventory-action">
                                    <!-- <option data-code="<?=$inStatusVal?>" value="in">In</option> -->
                                    <!-- <option data-code="<?=$outStatusVal?>" value="out">Out</option> -->
                                    <option value="invoice">Generate Invoice</option>
                                    <option value="memo">Generate Memo</option>
                                    <!-- <option value="return_memo">Return Memo</option> -->
                                    <!-- <option value="product_excel">Product Excel</option> -->
                                    <!-- <option value="export_csv">Export CSV</option> -->
                                    <option value="quotation">Quotation</option>
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


                      <table class="scroll-lg table table-striped table-center table-head-box checkbox checkbox-primary " id="inventoryProductsTable">

                          <thead>
                              <tr class="bg-primary">
	                                <th class="checkboxth"><label><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct"><span class="label-text"></span></label></th>
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
$imageDirectory = config('constants.dir.website_url_for_product_image');
//print_r(config('constants.dir.website_url_for_product_image'));exit;
$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
$defaultProductImage = $imageDirectory . 'def_1.png';
$product_approval_memo_generated = (!empty($product->approval_memo_generated) ? 1 : 0);
$product_approval_invoice_generated = (!empty($product->approval_invoice_generated) ? 1 : 0);
$product_return_memo_generated = (!empty($product->return_memo_generated) ? 1 : 0);
$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);

$categoryName = $categoryNames[0]->category_name;

?>
                              <tr>
                                <td><label><input class="form-check-input chkProduct" data-id="{{$product->entity_id}}" value="{{$product->entity_id}}" type="checkbox" name="chkProduct[]" id="chkProduct{{$product->entity_id}}"><span class="label-text"></label></td>
                                <td><img src="{{!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
                                <?php
$position = strpos($product->sku, ' ');
$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
?>
					            <td><?php echo $sku; ?></td>
					            <td>{{$product->certificate_no}}</td>
					            <td>{{$categoryName}}</td>
					            <td><?php echo !empty($product->rts_stone_quality) ? $product->rts_stone_quality : '-'; ?></td>
					            <?php
$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
?>
					            <td>{{$virtualproductposition}}</td>
					            <td>{{ShowroomHelper::currencyFormat(round($product->custom_price))}}</td>
                      <?php

$inventoryStatusOption = DB::select("SELECT EAOV.option_id,EAOV.value FROM eav_attribute EA LEFT JOIN eav_attribute_option EAO ON EAO.attribute_id = EA.attribute_id LEFT JOIN eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id WHERE EA.attribute_code = 'inventory_status' AND EAOV.store_id = 0");
//print_r($inventoryStatusOption);exit;
$inventoryStatuaArr = array();
foreach ($inventoryStatusOption as $key => $value) {
	$inventoryStatuaArr[$value->option_id] = $value->value;
}

$inventoryStatus = '';

$inventoryStatus = isset($inventoryStatuaArr[$product->inventory_status]) ? $inventoryStatuaArr[$product->inventory_status] : '-';

?>
					            <td>{{$inventoryStatus}}</td>
					            <?php
$pendingOrderData = InventoryHelper::getOrderByProduct($product->entity_id, 'pending');
$orderId = isset($pendingOrderData[0]->order_id) ? $pendingOrderData[0]->order_id : '';
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
//$orderId = isset($pendingOrderData[0]->order_id) ? $pendingOrderData[0]->order_id : '';
if (count($pendingOrderData) == 0 && count($completedOrderData) == 0) {
	$customerName = 'N/A';
}

?>
					            <td>{{$customerName}}</td>
					            <td>
                        <?php if ($product_approval_invoice_generated == '1' && !empty($orderId)): ?>
    					            	<select class="form-control h-auto w-auto mx-auto inventory_action">
                                <option value="">Select</option>
                                <option value="invoice" disabled data-productid="<?=$product->entity_id?>">Generate Invoice</option>
            										<option value="memo" disabled data-productid="<?=$product->entity_id?>">Generate Memo</option>
            										<!-- <option value="returnmemo" disabled data-productid="<?=$product->entity_id?>">Generate Return Memo</option> -->
                            </select>
                        <?php elseif ($product_approval_memo_generated == '1' && $product_return_memo_generated == '0' && !empty($orderId)): ?>
                            <select class="form-control h-auto w-auto mx-auto inventory_action">
                                <option value="">Select</option>
                                <option value="invoice" data-productid="<?=$product->entity_id?>">Generate Invoice</option>
                                <option value="memo" disabled data-productid="<?=$product->entity_id?>">Generate Memo</option>
                                <!-- <option value="returnmemo" data-productid="<?=$product->entity_id?>">Generate Return Memo</option> -->
                            </select>
                        <?php elseif ($product_return_memo_generated == '1' && !empty($orderId)): ?>
                            <select class="form-control h-auto w-auto mx-auto inventory_action">
                                <option value="">Select</option>
                                <option value="invoice" data-productid="<?=$product->entity_id?>">Generate Invoice</option>
                                <option value="memo" data-productid="<?=$product->entity_id?>">Generate Memo</option>
                                <!-- <option value="returnmemo" disabled data-productid="<?=$product->entity_id?>">Generate Return Memo</option> -->
                            </select>
                        <?php else: ?>
                            <select class="form-control h-auto w-auto mx-auto inventory_action">
                                <option value="">Select</option>
                                <option value="invoice" data-productid="<?=$product->entity_id?>">Generate Invoice</option>
                                <option value="memo" data-productid="<?=$product->entity_id?>">Generate Memo</option>
                                <!-- <option value="returnmemo" disabled data-productid="<?=$product->entity_id?>">Generate Return Memo</option> -->
                            </select>
                        <?php endif;?>
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
<input type="hidden" id="getInventoryProductCountAction" value="<?=URL::to('/inventory/getinventoryproductcount');?>">
<input type="hidden" id="inventoryAjax" value="<?=URL::to('/inventory/ajaxlist');?>">
<input type="hidden" name="filterapplied" id="filterapplied" value="false">
<input type="hidden" id="getprominentfilterAction" value="<?=URL::to('/inventory/getprominentfilter');?>">
<input type="hidden" id="generateQuotationAction" value="<?=URL::to('/inventory/generatequotation');?>">
<input type="hidden" id="storeProductIds" value="<?=URL::to('/inventory/storeproductids');?>">
<input type="hidden" id="quotationId" name="quotation_id" value="{{$quotationId}}">
<input type="hidden" id="editQuotationAction" name="editQuotation" value="{{ route('inventory.editquotation',$quotationId) }}">
<!-- Large Modal -->
<div class="modal fade bs-modal-lg modal-color-scheme" id="invoice-memo-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
    <div class="modal-dialog modal-lg">
        <div class="modal-content ">
            {!! Form::open(array('method'=>'POST','id'=>'invoicememo-generate-form','class'=>'form-horizontal','autocomplete'=>'nope','enctype'=>'multipart/form-data')) !!}

            {!! Form::close() !!}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
            <!-- /.modal -->
@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<!-- <script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/dataTables.responsive.min.js"></script> -->
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/js/ion.rangeSlider.min.js"></script>

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.15.1/jquery.validate.min.js"></script>
<script type="text/javascript">

//$('#virtualproductmanager').tagsinput();

var inventoryProductsTable = $('#inventoryProductsTable').DataTable({
  "dom": 'l<"#inventory-toolbar">frtip',
  "language": {
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
    //"sProcessing": "<div id='loader'></div>"
  },
  "deferLoading": <?=$total_products?>,
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

      var virtualproducts = $('#virtualproductmanager').val();
      if(virtualproducts != ''){
        data.virtualproducts = virtualproducts;
      }
      var stockstatus = $('#stockstatus').val();
      if(stockstatus != ''){
        data.stockstatus = stockstatus;
      }
      data.category = categoryIds;
      data.gold_purity = goldPurity;
      data.gold_color = goldColor;
      data.diamond_quality = diamondQuality;
      data.price_start = $("#priceStart").val();
      data.price_to = $("#priceEnd").val();

      showLoader();
      $(".dropdown").removeClass('show');
      $(".dropdown-menu").removeClass('show');
    },
    complete: function(response){
      loadajaxfilterblock();
      hideLoader();
      getProminentFilters();
    }
  },
  "columnDefs": [
      { "orderable": false, "targets": [0,1] }
  ],
  //"responsive": true
});
$divContainer = $('<div class="inventory-action-container"/>').appendTo('#inventory-toolbar')

$select = $('<select class="mx-2 mr-3 height-35 padding-four" id="inventory-status"/>').appendTo($divContainer)
/*$('<option data-code="<?=$inStatusVal?>"/>').val('in').text('In').appendTo($select);
$('<option data-code="<?=$outStatusVal?>"/>').val('out').text('Out').appendTo($select);*/
$('<option/>').val('invoice').text('Generate Invoice').appendTo($select);
$('<option/>').val('memo').text('Generate Memo').appendTo($select);
//$('<option/>').val('return_memo').text('Return Memo').appendTo($select);
/*$('<option/>').val('product_excel').text('Product Excel').appendTo($select);
$('<option/>').val('export_csv').text('Export CSV').appendTo($select);*/
$('<option/>').val('quotation').text('Quotation').appendTo($select);
$('<button class="btn btn-primary height-35" type="button" id="btn-change-inventory-status"/>').text('Submit').appendTo($divContainer);
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
var pricerangeslider = $("#priceFilter").data("ionRangeSlider");
//$(".category_chkbox,#virtualproductmanager,#stockstatus,.chk_metalquality,.chk_metalcolor,.chk_diamondquality").change(function(){
$(document).on('change', '.category_chkbox,#virtualproductmanager,#stockstatus,.chk_metalquality,.chk_metalcolor,.chk_diamondquality',function(){
	$('#filterapplied').val('true');
	getProminentFilters();
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
$(document).on('change','.inventory_action',function(){
  var productId = $('option:selected', this).attr('data-productid');
  $("#product_ids").val(productId);
  if(this.value == 'invoice' || this.value == 'memo')
  {
      $("#operation_type").val(this.value);
      if(productId!='')
      {
          $.ajax({
            url:$("#getInvoiceMemoModalAction").val(),
            method:"post",
            data:{productIds: productId, action: this.value,_token: "{{ csrf_token() }}"},
            success: function(response){
                $("#invoice-memo-modal #invoicememo-generate-form").html(response);
                $("#invoice-memo-modal").modal("show");
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
  else if(this.value == 'returnmemo')
  {
      if(productId!='')
      {
        //var url = $("#generateReturnMemoAction").val()+'?productIds='+ids;
        //window.location.href = url;
        $.ajax({
            url:$("#generateReturnMemoAction").val(),
            method:"post",
            data:{productIds: productId,_token: "{{ csrf_token() }}"},
            beforeSend: function()
            {
                showLoader();
            },
            success: function(response){
                hideLoader();
                var res = JSON.parse(response);
                console.log(res);
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
            text: "<?php echo Config::get('constants.message.inventory_generate_memo_product_not_selected'); ?>",
            type: 'info',
            showCancelButton: true,
            showConfirmButton: false
            });
      }
  }
});
function getProminentFilters()
{
	var categoryIds = [];
	var goldPurity = [];
	var diamondQuality = [];
	var goldColor = [];

	$.each($(".category_chkbox:checked"), function(){
        categoryIds.push($(this).val());
  });
	$.each($(".chk_metalquality:checked"), function(){
        goldPurity.push($(this).val());
  });
	$.each($(".chk_diamondquality:checked"), function(){
        diamondQuality.push($(this).val());
  });
	$.each($(".chk_metalcolor:checked"), function(){
        goldColor.push($(this).val());
  });
	var virtualproducts = $("#virtualproductmanager").val();
	$.ajax({
        type: "POST",
        dataType: "json",
        data: {
        	category : categoryIds,
    			gold_purity : goldPurity,
    			gold_color : goldColor,
    			diamond_quality : diamondQuality,
    			status : $("#stockstatus").val(),
    			price_start : $("#priceStart").val(),
    			price_to : $("#priceEnd").val(),
    			filterapplied : $('#filterapplied').val(),
    			virtualproducts: virtualproducts,
    			search_value: $("#inventoryProductsTable_filter input").val(),
    			_token: "{{ csrf_token() }}"
        },
        url: $("#getprominentfilterAction").val(),
        beforeSend: function()
        {
        	showLoader();
		      $(".dropdown").removeClass('show');
		      $(".dropdown-menu").removeClass('show');
        },
        success: function(data) {
			//if(data.success = 1) {
		      //jQuery('.inventory_popup').html(data.content);
		      //jQuery('.inventory_popup').simplePopup();
		        $('#diamondquality_area').replaceWith(data.diamond_quality_filters);
            $('#category_area').replaceWith(data.category_filters);
            $('#goldpurity_area').replaceWith(data.gold_purity_filters);
            $('#goldcolor_area').replaceWith(data.gold_colors_filters);
            $('#stockstatus').replaceWith(data.status_filters);
            $('#virtualproductmanager').replaceWith(data.virtual_filters);
            hideLoader();
		  	//}
		  }
	});
}
function loadajaxfilterblock()
{
    var filters = new Object();
    $(".resetfilter").each(function() {
      filters["'" + $(this).data('filtertype') + "'"] = $(this).html();
    });
    starter = 0;
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
    if(start != orignal_start || to != orignal_to)
    {
      prstart = $("#priceStart").val();
      prto =  $("#priceEnd").val();
      filters['price'] = 'Price:' + final_start_mod + '-' + final_to_mod;
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
          else if(x=='stockstatus')
          {
            div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('stockstatus')\" data-type=" + x +"></span>";
          }
          else if(x=='virtualproductmanager')
          {
            div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('virtualproductmanager')\" data-type=" + x +"></span>";
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
      return;
  }
  else
  {
    var resetfilter = filterType;
    filtershowroom(resetfilter);
    resetfilteroptions(resetfilter);
  }

}
function filtershowroom(resetfilter,prstart,prend)
{
      if(typeof(resetfilter) != 'undefined')
      {
        if(resetfilter == 'virtualproductmanager'){
          $('#virtualproductmanager').val('');
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
          priceSlider.update({
              from: intStart,
              to: intEnd
          });
        }
        else
        {
          $(".category_chkbox:checkbox[data-filtertype='"+resetfilter+"']").prop( "checked", false );
          $(".showroom-filter-checkbox input:checkbox[value='"+resetfilter+"']").prop( "checked", false );
        }
      }
      filters = {};
      filters['price_start'] = $("#priceStart").val();
      filters['price_to'] = $("#priceEnd").val();
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
      filters['stockstatus'] = $('#stockstatus').val();
      if(typeof(resetfilter) != 'undefined')
      {
        if(resetfilter == 'all')
        {
          $(".category_chkbox:checkbox").prop( "checked", false );
          $(".showroom-filter-checkbox input").prop( "checked", false );
          $("#virtualproductmanager").val('');
          $("#stockstatus").val('');
          $("#priceStart").val($("#priceStart").attr('data_price_init_start'));
          $("#priceEnd").val($("#priceEnd").attr('data_price_init_to'));
          var filters = {};
          var intStart = Math.floor($("#priceStart").attr('data_price_init_start'));
          var intEnd = Math.floor($("#priceEnd").attr('data_price_init_to'));
          pricerangeslider.update({
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
    if(typeof(resetfilter) != 'undefined')
    {
      if(resetfilter == 'all')
      {
        var filters = {};
      }else if(resetfilter == 'virtualproductmanager'){
        filters[resetfilter] = "";
      }else if(resetfilter == 'stockstatus'){
        filters[resetfilter] = "";
      }else{
        filters[resetfilter] = 0;
      }
    }
    inventoryProductsTable.draw();
}
//For bulk inventory action
$("#btn-bulk-operation").click(function(){
    var action = $('#bulk-inventory-action option:selected').val();
    var productIds = new Array();
    var file_data = $('#uploadBtn').prop('files')[0];
    var fileLength = $("#uploadBtn")[0].files.length;
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('_token',"{{ csrf_token() }}");
    var inventoryCode = '';
    if($('#bulk-inventory-action option:selected').data('code'))
    {
      inventoryCode = $('#bulk-inventory-action option:selected').data('code');
    }
    if(fileLength>0)
    {
      if(action=="in" || action=="out")
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

                  $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                      status:action,productIds:productIds,inventoryCode:inventoryCode,_token: "{{ csrf_token() }}"
                    },
                    url: $("#changeInventoryStatusAction").val(),
                    beforeSend: function()
                    {
                      showLoader();
                    },
                    success: function(data) {
                      if(data.status)
                      {
                          //Get total in/out products count
                          getTotalInventoryProductCount();
                          swal({
                            title: 'Success',
                            text: data.message,
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
                          text: data.message,
                          type: 'error',
                          showCancelButton: true,
                          showConfirmButton: false,
                          confirmButtonClass: 'btn btn-danger',
                          cancelButtonText: 'Ok'
                        });
                      }
                      hideLoader();
                  }
              });
              }
            },
          });
      }
      else if(action == "invoice" || action == "memo")
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
                        url:$("#getInvoiceMemoModalAction").val(),
                        method:"post",
                        data:{productIds: productIds, action: action,_token: "{{ csrf_token() }}"},
                        success: function(response){
                            $("#invoice-memo-modal #invoicememo-generate-form").html(response);
                            $("#invoice-memo-modal").modal("show");
                        }
                      });
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
    var inventoryCode = '';
    if($('#inventory-status option:selected').data('code'))
    {
      inventoryCode = $('#inventory-status option:selected').data('code');
    }
    var productIds = new Array();
    jQuery.each(jQuery(".chkProduct:checked"), function() {
        productIds.push(jQuery(this).val());
    });
    var ids = productIds.join(",");

    if(action=='in' || action=='out'){
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
              status:action,productIds:productIds,inventoryCode:inventoryCode,_token: "{{ csrf_token() }}"
            },
            url: $("#changeInventoryStatusAction").val(),
            beforeSend: function()
            {
              showLoader();
            },
            success: function(data) {
              if(data.status)
              {
                  //Get total in/out products count
                  getTotalInventoryProductCount();
                  inventoryProductsTable.draw();
                  swal({
                    title: 'Success',
                    text: data.message,
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
                  text: data.message,
                  type: 'error',
                  showCancelButton: true,
                  showConfirmButton: false,
                  confirmButtonClass: 'btn btn-danger',
                  cancelButtonText: 'Ok'
                });
              }
              hideLoader();
          }
      });
    }
    else if(action == "invoice" || action == "memo")
    {
        $("#operation_type").val(action);
        if(ids!='')
        {
            $.ajax({
              url:$("#getInvoiceMemoModalAction").val(),
              method:"post",
              data:{productIds: ids, action: action,_token: "{{ csrf_token() }}"},
              success: function(response){
                  $("#invoice-memo-modal #invoicememo-generate-form").html(response);
                  $("#invoice-memo-modal").modal("show");
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
    else if(action=="return_memo"){
        if(ids!='')
        {
          //var url = $("#generateReturnMemoAction").val()+'?productIds='+ids;
          //window.location.href = url;
          $.ajax({
              url:$("#generateReturnMemoAction").val(),
              method:"post",
              data:{productIds: ids,_token: "{{ csrf_token() }}"},
              beforeSend: function()
              {
                showLoader();
              },
              success: function(response){
                  hideLoader();
                  var res = JSON.parse(response);
                  console.log(res);
                  if(res.status)
                  {
                      swal({
                        title: 'Success',
                        text: res.message,
                        type: 'success',
                        buttonClass: 'btn btn-primary'
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
              text: "<?php echo Config::get('constants.message.inventory_generate_memo_product_not_selected'); ?>",
              type: 'info',
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
    else if(action == "export_csv") {
        if(ids!='')
        {
          var url = $("#exportProductCsvAction").val()+'?productIds='+ids;
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
$(document).on('change','input[name=customerType]',function(){
    $("#customer-form-container,#invoice-memo-modal .modal-footer").removeClass("hidden");
    if(this.value == 'new')
    {
      $(".new-customer-field").removeClass("hidden");
      $(".email-field").addClass('hidden');
      if($("#operation_type").val()=='invoice')
      {
          $(".invoice-field").removeClass('hidden');
          $(".invoiceqr-commission-field").removeClass('hidden');
          $(".invoice-commission-field").removeClass('hidden');
      }
      else
      {
          $(".invoice-field").addClass('hidden');
          $(".invoiceqr-commission-field").removeClass('hidden');
          $(".invoice-commission-field").addClass('hidden');
      }
      $(".existing-customer-field").removeClass('hidden');

    }
    else if(this.value=='existing')
    {
        $(".existing-customer-field").removeClass('hidden');
        $(".new-customer-field").addClass('hidden');
        $(".email-field").removeClass('hidden');
        if($("#operation_type").val()=='invoice')
        {
            $(".invoice-field").removeClass('hidden');
            $(".invoiceqr-commission-field").removeClass('hidden');
            $(".invoice-commission-field").removeClass('hidden');
        }
        else
        {
            $(".invoice-field").addClass('hidden');
            $(".invoiceqr-commission-field").removeClass('hidden');
            $(".invoice-commission-field").addClass('hidden');
        }
    }
});
$("#chkAllProduct").click(function(){
    $('.chkProduct').prop('checked', this.checked);
});
//For filter
$("#btn-apply-filter").click(function(){
    if (!this.value.match(/[a-z]/i)) {
        // alphabet letters found
      if(parseFloat($('#priceStart').val()) > parseFloat($('#priceEnd').val()))
      {
        $('#priceStart').val($("#priceStart").attr('data_price_init_start')) ;
        $('#priceEnd').val($("#priceEnd").attr('data_price_init_to')) ;

      }
      var highest_price = parseFloat($('#priceEnd').val());
      var default_highest_price = parseFloat($("#priceEnd").attr('data_price_init_to'));
      if(highest_price > default_highest_price)
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
      jQuery('#filterapplied').val('true');
      inventoryProductsTable.draw();
      loadajaxfilterblock();
      //filterinventory();
      return;
    }
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
</script>
<style>
.product-img{max-width: 40px;}
</style>
@endsection