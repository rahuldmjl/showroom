<?php

use App\Helpers\ShowroomHelper;
use App\Helpers\InventoryHelper;
//$total_products = ShowroomHelper::getProducts(1);
$price = ShowroomHelper::getMinMaxPriceForFilter();
//var_dump($price);exit;
//$priceStart = $price[0]->min_price;
//$priceEnd = $price[0]->max_price;
$priceStart = $price['min_price'];
$priceEnd = $price['max_price'];

$total_products = $productCollection['totalCount'];
$productCollection = $productCollection['productCollection'];
?>
@extends('layout.mainlayout')

@section('title', 'Showroom')

@section('distinct_head')

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/css/ion.rangeSlider.min.css"/>
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('showroom.index') }}
      <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div class="widget-list">
      <div class="row">
			<div class="col-md-12 widget-holder">
				<div class="widget-bg">
					<div class="widget-body clearfix">
                      <div class="row custom-drop-style custom-select-style label-text-pl-25">
                        <div class="col-xl-2 col-sm-4 px-sm-2">
							<div class="form-group price-filter">
							  <div class='dropdown' id='pricerange'>
								  <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Price<span class='caret'></span></button>
								  <ul class='dropdown-menu custom-scroll'>
									  <li>
										  <div class="form-group px-3">
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
                        <div class="col-xl-2 col-sm-4 px-sm-2">
                            <div class="form-group">
                                <?php
echo ShowroomHelper::getCategoryFilter();
?>
                            </div>
                        </div>
                        <div class="col-xl-2 col-sm-4 px-sm-2">
                          <div class="form-group">
                              <?php
echo ShowroomHelper::getGoldPurity();
?>
                          </div>
                        </div>
                        <div class="col-xl-2 col-sm-4 px-sm-2">
                            <div class="form-group">
                                <?php
echo ShowroomHelper::getDiamondQuality();
?>
                            </div>
                        </div>
                        <div class="col-xl-2 col-sm-4 px-sm-2">
                            <div class="form-group">
                                  <select class="text-uppercase" id="diamondType" name="diamondType">
                                      <option value="">Diamond Type</option>
                                      <option value="1">ROUND</option>
                                      <option value="2">ROUND & FANCY</option>
                                      <option value="3">FANCY</option>
                                  </select>
                            </div>
                        </div>
                        <div class="col-xl-2 col-sm-4 px-sm-2">
                            <div class="form-group">
                                  <select class="text-uppercase" id="criteria" name="criteria">
                                      <option value="">Criteria</option>
                                      <option value="1">BOTH IN CRITERIA</option>
                                      <option value="2">OK IN 14KT</option>
                                      <option value="3">OK IN 18KT</option>
                                      <option value="4">BOTH NOT IN CRITERIA</option>
                                  </select>
                            </div>
                        </div>
                        <div class="col-xl-2 col-sm-4 px-sm-2">
                            <div class="form-group">
                                  <select class="text-uppercase" id="stockstatus" name="stockstatus">
                                      <option value="">Status</option>
                                      <option value="1">DML INSTOCK</option>
                                      <option value="2">DML SOLD</option>
                                      <option value="3">FRANCHISE INSTOCK</option>
                                      <option value="4">FRANCHISE SOLD</option>
                                  </select>
                            </div>
                        </div>
                      </div>
                      <div class="row empty-div">
                        <div class="col-md-12 mt-3" id="selectedfilter">
                          <div class="bootstrap-tagsinput space-five-all">
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
            </div>
        </div>
        <!--<div class="row">
              <div class="col-md-12 widget-holder">
                  <div class="widget-bg p-0 ">
                      <div class="widget-body clearfix">
                          <div id="select_columns_for_visibility">
                            <h5 class="fs-16 fw-600 p-2 m-0 border-b-light-1">Toggle Columns</h5>
                            <div class="toggle-Columns-style d-flex flex-wrap py-3 px-2">
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="1">Image</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="2">Certificate</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="3">SKU</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="4">QTY</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="5">QTY With Quality</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="6">Size</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="7">Product Type</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="8">Metal Color</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="9">Metal Gross</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="10">Metal Quality</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="11">18KT</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="12">14KT</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="13">Diamond Quality</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="14">Total Diamond Weight</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="15">Final Price</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="16">Selling Status</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="17">Diamond Type</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="18">Ratio in 18K</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="19">Ratio in 14K</a>
                            <a href="javascript:void(0);" class="toggle-vis active" data-column="20">Remarks</a>
                         </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
        -->
         <div class="row">
          <div class="col-md-12 widget-holder loader-area" style="display: none;">
            <div class="widget-bg text-center">
              <div class="loader"></div>
            </div>
          </div>
          <div class="col-md-12 widget-holder content-area">
              <div class="widget-bg">
                  <!-- <div class="widget-heading clearfix">
                      <h5>{{'Showroom'}}</h5>
                  </div> -->
                  <!-- /.widget-heading -->
                  <div class="process-area">
                    {!! Form::open(array('route' => 'showroom.processorder','method'=>'POST', 'name' => 'process-order-form', 'id' => 'process_order_form' , 'class' => 'pull-left')) !!}
                      <input type="hidden" name="post_product_data" id="post_product_data" value="" />
                      <button class="btn btn-outline-default ripple process-order" id="process_order">
                        <span>Process Order</span>
                        <i class="material-icons list-icon">forward</i>
                      </button>
                    {!! Form::close() !!}

                    <div class="showroom-toolbar pull-right position-relative" title="Columns">
                      <button class="btn btn-secondary small-btn-style mr-2" id="btn-export-csv" type="button" title="Export Excel">
                        <i class="fa fa-file-excel-o"></i>
                        <span class="caret"></span>
                      </button>
                      <button class="btn btn-secondary small-btn-style showroom-toolbar-button" type="button" title="Columns">
                        <i class="fa fa-th-list"></i>
                        <span class="caret"></span>
                      </button>
                      <div class="showroom-toolbar-dropdown">
                          <div class="toolbar-dropdown-inner custom-scroll" id="select_columns_for_visibility">
                              <div class="toggle-Columns-style flex-column d-flex py-3 px-2">
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="1">Image</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="2">Certificate</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="3">SKU</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="4">QTY</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="5">QTY With Quality</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="6">Size</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="7">Product Type</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="8">Metal Color</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="9">Metal Gross</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="10">Metal Quality</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="11">18KT</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="12">14KT</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="13">Diamond Quality</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="14">Total Diamond Weight</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="15">Final Price</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="16">Selling Status</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="17">Diamond Type</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="18">Ratio in 18K</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="19">Ratio in 14K</a>
                              <a href="javascript:void(0);" class="toggle-vis active" data-column="20">Remarks</a>
                           </div>
                      </div>
                    </div>
                  </div>
                </div>

                  <div class="widget-body clearfix">
                      @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif

                      <table class="table table-center table-head-box checkbox checkbox-primary nowrap" id="showroomProductsTable" >
                          <thead>
                              <tr class="bg-primary">
                                  <th class="checkboxth"><label><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct"><span class="label-text"></span></label></th>
                                  <th>Image</th>
                                  <th>Certificate</th>
                                  <th>SKU</th>
                                  <th>QTY</th>
                                  <th>QTY With Quality</th>
                                  <th>Size</th>
                                  <th>Product Type</th>
                                  <th>Metal Color</th>
                                  <th>Metal Gross</th>
                                  <th>Metal Quality</th>
                                  <th>18KT</th>
                                  <th>14KT</th>
                                  <th>Diamond Quality</th>
                                  <th>Total Diamond Weight</th>
                                  <th>Final Price</th>
                                  <th>Selling Status</th>
                                  <th>Diamond Type</th>
                                  <th>Ratio in 18K</th>
                                  <th>ratio_in_18K in 14K</th>
                                  <th>Remarks</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($productCollection as $key => $product)
                              <?php
//echo "<pre>";
//print_r($product);exit;
//$productData = ShowroomHelper::getProductData('1276017');
$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
$categoryName = $categoryNames[0]->category_name;

$metalData = ShowroomHelper::getMetalData($product->entity_id, $product);

$metalQuality = $metalData['quality'];
$metalColorArray = explode(' ', $metalQuality);
//print_r($metalColorArray[1]);exit;
if (isset($metalColorArray[1]) && isset($metalColorArray[2])) {
	$metalColor = $metalColorArray[1] . ' ' . $metalColorArray[2];
} else {
	$metalColor = '';
}
$metalGross = $metalData['weight'];
if (isset($metalColorArray[0])) {
	$metalQuality = isset($metalColorArray[0]) ? $metalColorArray[0] : '';
} else {
	$metalQuality = '';
}
if ($metalQuality == '18K') {
	$kt_14_wt = (float) ($metalGross * 85) / 100;
	$kt_18_wt = (float) $metalGross;
} elseif ($metalQuality == '14K') {
	$kt_14_wt = (float) $metalGross;
	$kt_18_wt = (float) ($metalGross * 100) / 85;
}
$stone = $product->rts_stone_quality;
$stoneData = InventoryHelper::getStoneData($product->entity_id);
$totalStone = count($stoneData['stoneclarity']);
$diamondQualities = '';
$roundCount = 0;
$fancyCount = 0;
for ($stoneIndex = 0; $stoneIndex < $totalStone; $stoneIndex++) {
	if ($stoneIndex == ($totalStone - 1)) {
		$diamondQualities .= $stoneData['stoneclarity'][$stoneIndex];
	} else {
		$diamondQualities .= $stoneData['stoneclarity'][$stoneIndex] . ' | ';
	}
	if ($stoneData['shape'][$stoneIndex] == 'ROUND') {
		$roundCount++;
	} else {
		$fancyCount++;
	}
}
if ($roundCount == $totalStone) {
	$diamondType = 'ROUND';
} elseif ($fancyCount == $totalStone) {
	$diamondType = 'FANCY';
} else {
	$diamondType = 'FANCY WITH ROUND';
}
$diamondTotalWeight = (float) isset($stoneData['totalweight'][0]) ? $stoneData['totalweight'][0] : '';
$isSold = $product->stockstatus;
//var_dump(is_numeric($kt_14_wt));
//var_dump(is_numeric($kt_18_wt));
//var_dump(is_numeric($diamondTotalWeight));exit;
//var_dump($kt_18_wt);
//var_dump($diamondTotalWeight);exit;
//$ratio_in_18K = ($kt_18_wt) / ($diamondTotalWeight);
$ratio_in_18K = $product->ratio18k;
//$ratio_in_14K = ($kt_14_wt) / ($diamondTotalWeight);
$ratio_in_14K = $product->ratio14k;
if ($ratio_in_18K <= 10 && $ratio_in_14K <= 10) {
	$remarks = 'OK in both';
} elseif ($ratio_in_18K <= 10 && $ratio_in_14K > 10) {
	$remarks = 'OK in 18K';
} elseif ($ratio_in_18K > 10 && $ratio_in_14K <= 10) {
	$remarks = 'OK in 14K';
} else {
	$remarks = 'Not in criteria';
}
$imageDirectory = config('constants.dir.website_url_for_product_image');
//print_r(config('constants.dir.website_url_for_product_image'));exit;
$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
$defaultProductImage = $imageDirectory . 'def_1.png';
?>
                              <tr>
                                <td><label><input class="form-check-input chkProduct" data-id="{{$product->entity_id}}" value="{{$product->entity_id}}" type="checkbox" name="chkProduct[]" id="chkProduct{{$product->entity_id}}"><span class="label-text"></label></td>
                                <td><img src="{{!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
                                <td>{{$product->certificate_no}}</td>
                                <td>{{$product->sku}}</td>
                                <?php
$product_size = '';
if ($product->attribute_set_id == '14') {
	$product_size = !empty($product->rts_ring_size) ? $product->rts_ring_size : '-';
} elseif ($product->attribute_set_id == '17') {
	$product_size = !empty($product->rts_bangle_size) ? $product->rts_bangle_size : '-';
} elseif ($product->attribute_set_id == '23') {
	$product_size = !empty($product->rts_bracelet_size) ? $product->rts_bracelet_size : '-';
}
$orgskuarr = explode(' ', $product->sku);
$orgsku = $orgskuarr[0];
$orgsku2 = $orgskuarr[0] . ' ' . $orgskuarr[1];
?>
                                <td><?php echo ShowroomHelper::getTotalQtyBySku($orgsku); ?></td>
                                <td><?php echo ShowroomHelper::getTotalQtyBySku($orgsku2); ?></td>
                                <td>{{!empty($product_size) ? $product_size : '-'}}</td>
                                <td>{{$categoryName}}</td>
                                <td>{{$metalColor}}</td>
                                <td>{{$metalGross}}</td>
                                <td>{{$metalQuality}}</td>
                                <td>{{round($kt_18_wt, 2)}}</td>
                                <td>{{round($kt_14_wt, 2)}}</td>
                                <td>{{$diamondQualities}}</td>
                                <td>{{round($diamondTotalWeight, 2)}}</td>
                                <td>{{ShowroomHelper::currencyFormat(round($product->custom_price))}}</td>
                                <td>{{$isSold}}</td>
                                <td>{{$diamondType}}</td>
                                <td>{{round($ratio_in_18K, 2)}}</td>
                                <td>{{round($ratio_in_14K, 2)}}</td>
                                <td>{{$remarks}}</td>
                              </tr>
                             @endforeach
                          </tbody>
                          <!-- <tfoot>
                              <tr>
                                  <th><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct"></th>
                                  <th>Image</th>
                                  <th>Certificate</th>
                                  <th>SKU</th>
                                  <th>QTY</th>
                                  <th>QTY With Quality</th>
                                  <th>Size</th>
                                  <th>Product Type</th>
                                  <th>Metal Color</th>
                                  <th>Metal Gross</th>
                                  <th>Metal Quality</th>
                                  <th>18KT</th>
                                  <th>14KT</th>
                                  <th>Diamond Quality</th>
                                  <th>Total Diamond Weight</th>
                                  <th>Final Price</th>
                                  <th>Selling Status</th>
                                  <th>Diamond Type</th>
                                  <th>Ratio in 18K</th>
                                  <th>Ratio in 14K</th>
                                  <th>Remarks</th>
                              </tr>
                          </tfoot> -->
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
<input type="hidden" id="showroomAjax" value="<?=URL::to('/showroom/ajaxlist');?>">
@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/dataTables.responsive.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/js/ion.rangeSlider.min.js"></script>
<script type="text/javascript">

$('.showroom-toolbar-dropdown').hide();
$('.showroom-toolbar-button').click(function(){
  $('.showroom-toolbar-dropdown').toggle();
})

var showroomProductsTable = $('#showroomProductsTable').DataTable({
  "language": {
    "search": "",
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
    "sProcessing": "<div class='spinner-border' style='width: 3rem; height: 3rem;'' role='status'><span class='sr-only'>Loading...</span></div>"
  },
  "deferLoading": <?=$total_products?>,
  "processing": true,
  "serverSide": true,
  "searching": false,
  "lengthChange": false,
  "serverMethod": "post",
  "ajax":{
    "url": $("#showroomAjax").val(),
    "data": function(data, callback){
      // Append to data
      var categoryIds = [];
      var goldPurity = [];
      var diamondQuality = [];
      $.each($(".category_chkbox:checked"), function(){
        categoryIds.push($(this).val());
      });
      $.each($(".chk_metalquality:checked"), function(){
          goldPurity.push($(this).val());
      });
      $.each($(".chk_diamondquality:checked"), function(){
          diamondQuality.push($(this).val());
      });
      var diamondtype = $('#diamondType').val();
      if(diamondtype != ''){
        data.diamondtype = diamondtype;
      }
      var criteria = $('#criteria').val();
      if(criteria != ''){
        data.criteria = criteria;
      }
      var stockstatus = $('#stockstatus').val();
      if(stockstatus != ''){
        data.stockstatus = stockstatus;
      }
      data.category = categoryIds;
      data.gold_purity = goldPurity;
      data.diamond_quality = diamondQuality;
      data.price_start = $("#priceStart").val();
      data.price_to = $("#priceEnd").val();
      data._token = "{{ csrf_token() }}";
      //$(".pace").removeClass("pace-inactive");
      //$(".pace").addClass("pace-active");
      showLoader();
      $(".dropdown").removeClass('show');
      $(".dropdown-menu").removeClass('show');
    },
    complete: function(response){
      //console.log(response.responseJSON.min_price);
      loadajaxfilterblock();
      //$(".pace").removeClass("pace-active");
      //$(".pace").addClass("pace-inactive");
      hideLoader();
      $('#priceStart').val(response.responseJSON.min_price);
      $('#priceEnd').val(response.responseJSON.max_price);
      //$('#priceStart').attr('data_price_init_start', response.responseJSON.min_price);
      //$('#priceEnd').attr('data_price_init_to', response.responseJSON.max_price);
      pricerangeslider.update({
          from: response.responseJSON.min_price,
          to: response.responseJSON.max_price,
          //min: response.responseJSON.min_price,
          //max: response.responseJSON.max_price,
      });
    }
  },
  "columnDefs": [
      { "orderable": false, "targets": [0,1] }
  ],
  "scrollX": true
});
$('.dataTables_filter input')
  .unbind() // Unbind previous default bindings
  .bind("input", function(e) { // Bind our desired behavior
      // If the length is 3 or more characters, or the user pressed ENTER, search
      if(this.value.length >= 3 || e.keyCode == 13) {
          // Call the API search function
          showroomProductsTable.search(this.value).draw();
      }
      // Ensure we clear the search if they backspace far enough
      if(this.value == "") {
          showroomProductsTable.search("").draw();
      }
      return;
});
$("#showroomProductsTable tr .checkboxth").removeClass('sorting_asc');
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
          showroomProductsTable.draw();
      },
});
$("#chkAllProduct").click(function(){
    if($(this).is(":checked"))
    {
      jQuery('.chkProduct').prop('checked', this.checked);
    }
    else
    {
      jQuery('.chkProduct').prop('checked', this.checked);
    }
});
$("#btn-export-csv").click(function(){
    var chkProductIds = [];
    $('.chkProduct:checked').each(function() {
       chkProductIds.push(this.value);
    });
    var query = {
        chkProductIds:chkProductIds
    }
    if(chkProductIds!='')
    {
      $.ajax({
            type: 'POST',
            data: {chkProductIds:chkProductIds,_token: "{{ csrf_token() }}"},
            url: '<?=URL::to('/showroom/storeproductids');?>',
            beforeSend: function()
            {
              showLoader();
            },
            success: function(response){
                var res = JSON.parse(response);
                if(res.status)
                {
                    hideLoader();
                    var url = "<?=URL::to('/showroom/exportcsv')?>";
                    window.location = url;
                }
            }
      });
    } else {
        swal({
            title: 'Oops!',
            text: 'Please select product!',
            type: 'error',
            showCancelButton: true,
            showConfirmButton: false,
            confirmButtonClass: 'btn btn-danger',
            cancelButtonText: 'Ok'
          });
    }
    console.log(chkProductIds);
});
var pricerangeslider = $("#priceFilter").data("ionRangeSlider");
//For category filter
$(".category_chkbox").change(function(){
    showroomProductsTable.draw();
});
//For gold purity filter
$(".chk_metalquality").change(function(){
    showroomProductsTable.draw();
});
//For diamond quality filter
$('.chk_diamondquality').change(function(){
  showroomProductsTable.draw();
});
//For diamond type filter
$("#diamondType").change(function(){
  showroomProductsTable.draw();
});
//For criteria filter
$("#criteria").change(function(){
  showroomProductsTable.draw();
});
//For stockstatus filter
$("#stockstatus").change(function(){
  showroomProductsTable.draw();
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
      loadajaxfilterblock();
      showroomProductsTable.draw();
      return;
    }
});

var selectedProducts = [];
$(document).on('click','#chkAll',function () {
  $('.chkProduct').prop('checked', this.checked);
});

$('#process_order').click(function(){
  /*return false;*/
  var productCount = 0;
  $('.chkProduct:checked').each(function() {
    //console.log($(this).attr('data-id'));
    //console.log($productVal);
      selectedProducts.push($(this).attr('data-id'));
      productCount++;
  });
  var jsonSelectedProducts = JSON.stringify(selectedProducts);
  //console.log(jsonSelectedProducts);
  $('#post_product_data').val(jsonSelectedProducts);
	  if(productCount > 0){
		$('#process_order_form').submit();
	  } else {
		alert("Please select atleast one product.")
	  }
  });

//For column toggle
$('a.toggle-vis').on( 'click', function (e) {
    e.preventDefault();
    $(this).toggleClass('deactive');
    $(this).toggleClass('active');
    // Get the column API object
    var column = showroomProductsTable.column($(this).attr('data-column'));
    // Toggle the visibility
    column.visible( ! column.visible() );
});

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
    if(typeof($("#diamondType").val()) != 'undefined')
    {
      var name = $("#diamondType").val();
      var val =  'diamondType';
      filters[val] = name;
    }
    if(typeof($("#criteria").val()) != 'undefined')
    {
      var name = $("#criteria").val();
      var val =  'criteria';
      filters[val] = name;
    }
    if(typeof($("#stockstatus").val()) != 'undefined')
    {
      var name = $("#stockstatus").val();
      var val =  'stockstatus';
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
          if(x == 'diamondType'){
            if(filters[x] == '1'){
              var div = "<span class='tag label label-info'>ROUND";
            } else if(filters[x] == '2'){
              var div = "<span class='tag label label-info'>ROUND & FANCY";
            } else if(filters[x] == '3'){
              var div = "<span class='tag label label-info'>FANCY";
            }
          } else if(x == 'criteria'){
            if(filters[x] == '1'){
              var div = "<span class='tag label label-info'>BOTH IN CRITERIA";
            } else if(filters[x] == '2'){
              var div = "<span class='tag label label-info'>OK IN 14KT";
            } else if(filters[x] == '3'){
              var div = "<span class='tag label label-info'>OK IN 18KT";
            } else if(filters[x] == '4'){
              var div = "<span class='tag label label-info'>BOTH NOT IN CRITERIA";
            }
          } else if(x == 'stockstatus'){
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
          else
          {
            div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('"+x+"')\"></span></span>";
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
  if(filterType == "all")
  {
      filtershowroom("all");
      resetfilteroptions("all");
      return;
  }
  var resetfilter = filterType;
  filtershowroom(resetfilter);
  resetfilteroptions(resetfilter);
}
function filtershowroom(resetfilter,prstart,prend)
{
      if(typeof(resetfilter) != 'undefined')
      {
        if(resetfilter == 'diamondType'){
          $('#diamondType').val('');
        }
        else if(resetfilter == 'criteria'){
          $('#criteria').val('');
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
      filters['diamondtype'] = $('#diamondType').val();
      filters['criteria'] = $('#criteria').val();
      filters['stockstatus'] = $('#stockstatus').val();
      if(typeof(resetfilter) != 'undefined')
      {
        if(resetfilter == 'all')
        {
          $(".category_chkbox:checkbox").prop( "checked", false );
          $(".showroom-filter-checkbox input").prop( "checked", false );
          $("#diamondType").val('');
          $("#stockstatus").val('');
          $("#criteria").val('');
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
      }else if(resetfilter == 'diamondtype'){
        filters[resetfilter] = "";
      }else if(resetfilter == 'stockstatus'){
        filters[resetfilter] = "";
      }else if(resetfilter == 'criteria'){
        filters[resetfilter] = "";
      }else{
        filters[resetfilter] = 0;
      }
    }
    showroomProductsTable.draw();
}
</script>
<style>
.product-img{max-width: 40px;}
.empty-div::empty{display: none;}
</style>
@endsection