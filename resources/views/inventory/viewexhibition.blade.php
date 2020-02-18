<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
$inStatusVal = $inventoryStatus['in'];
$total_products = $exhibitionProducts->count();
?>
@extends('layout.mainlayout')

@section('title', 'View Exhibition')

@section('distinct_head')
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<input type="hidden" id="exhibition_id" value="<?php echo $exhibitionData->id; ?>">
<main class="main-wrapper clearfix">
	<div class="col-md-12 widget-holder loader-area" style="display: none;">
	    <div class="widget-bg text-center">
	      <div class="loader"></div>
	    </div>
	  </div>
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  	{{ Breadcrumbs::render('inventory.exhibitionlist') }}
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
  					<div class="widget-header">
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-4 w-100">Exhibition Detail</h5>
  					</div>
  					<div class="widget-body clearfix">
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    <div class="row">
	                    <div class="col-md-6 exibition-detail">
	                    	<table class="table table-bordered table-striped">
	                    	<tbody>
	                    	 <tr>
	                          	<th>Title:</th>
	                          	<td>
	                                <?php echo isset($exhibitionData->title) ? $exhibitionData->title : '' ?>
	                          	</td>
                             </tr>
                             <tr>
	                          	<th>Place:</th>
	                          	<td>
	                                <?php echo isset($exhibitionData->place) ? $exhibitionData->place : '' ?>
	                          	</td>
                             </tr>
                             <tr>
	                          	<th>Address:</th>
	                          	<td>
	                                <?php echo isset($exhibitionData->address) ? $exhibitionData->address : '' ?>
	                          	</td>
                             </tr>
                             <tr>
	                          	<th>Markup:</th>
	                          	<td>
	                                <?php echo isset($exhibitionData->markup) ? $exhibitionData->markup : '' ?>
	                          	</td>
                             </tr>
                             <tr>
	                          	<th>Total Product:</th>
	                          	<td id="total-products">
	                                <?php echo isset($exhibitionData->qty) ? $exhibitionData->qty : '' ?>
	                          	</td>
                             </tr>
                             <tr>
	                          	<th>Grand Total:</th>
	                          	<td id="grand-total">
	                                <?php
$grandTotal = ShowroomHelper::currencyFormat(round(InventoryHelper::getExhibitionGrandTotal($exhibitionData->id)));
echo $grandTotal;
?>
	                          	</td>
                             </tr>
                          	</tbody>
                          </table>
                      	</div>
                      </div>
  					</div>
  				</div>
  			</div>
  		</div>
  		<div class="row">
  			<div class="col-md-12 widget-holder">
  				<div class="widget-bg">
  					<div class="widget-header">
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-4 w-100">Exhibition Products</h5>
  					</div>
  					<div class="widget-body clearfix">
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    <table class="table table-striped  thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll <?php echo ($total_products > 0) ? 'scroll-lg' : '' ?>" id="exhibitionProductsTable">
	                    	<thead>
                              <tr class="bg-primary">
                                  <th class="checkboxth"><label><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct"><span class="label-text"></span></label></th>
                                  <th>Image</th>
                                  <th>SKU</th>
                                  <th>Certificate</th>
                                  <th>Metal Weight</th>
                                  <th>Diamond Weight</th>
                                  <th>Price</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                          		<?php
$imageDirectory = config('constants.dir.website_url_for_product_image');
$defaultProductImage = $imageDirectory . 'def_1.png';
foreach ($exhibitionProducts as $key => $productId) {
	DB::setTablePrefix('');
	$product = DB::table('catalog_product_flat_1')->select('*')->where('entity_id', '=', DB::raw("$productId->product_id"))->get()->first();
	//$product = isset($product[0]) ? $product[0] : '';
	$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
	$sku = isset($product->sku) ? $product->sku : '';
	$certificateNo = isset($product->certificate_no) ? $product->certificate_no : '';
	$metalData = ShowroomHelper::getMetalData($product->entity_id, $product);
	//$metalData = InventoryHelper::getMetalData($productId);
	//print_r($metalData);exit;
	$stoneData = InventoryHelper::getStoneData($product->entity_id);
	$stoneWeight = isset($stoneData['totalweight'][0]) ? $stoneData['totalweight'][0] : '';

	$metalWeight = isset($metalData['weight']) ? $metalData['weight'] : '';
	$price = isset($product->custom_price) ? ShowroomHelper::currencyFormat(round($product->custom_price)) : 0;
	?>
                          			<tr>
	                          			<td><label><input class="form-check-input chkProduct" data-id="{{$product->entity_id}}" value="{{$product->entity_id}}" type="checkbox" name="chkProduct[]" id="chkProduct{{$product->entity_id}}"><span class="label-text"></span></label></td>
		                      			<td class="img-cum-lable"><img src="{{!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
		                      			<td>{{$sku}}</td>
		                      			<td>{{$certificateNo}}</td>
		                      			<td>{{$metalWeight}}</td>
		                      			<td>{{$stoneWeight}}</td>
		                      			<td>{{$price}}</td>
		                      			<td>
		                      				<select class="form-control h-auto w-auto mx-auto exhibition_action">
			                                    <option value="">Select</option>
			                                    <option value="<?=$inStatusVal;?>" data-productid="<?=$product->entity_id?>">Move to Showroom</option>
			                                </select>
		                      			</td>
		                      		</tr>
                          		<?php
}
?>
                          </tbody>
                          <tfoot>
                          	<tr>
                          		<th><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct"></th>
                      			<th>Image</th>
	                            <th>SKU</th>
	                            <th>Certificate</th>
                                <th>Metal Weight</th>
                                <th>Diamond Weight</th>
                                <th>Price</th>
                                <th>Action</th>
                          	</tr>
                          </tfoot>
	                    </table>
  					</div>
  				</div>
  			</div>
  		</div>
    </div>

  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->

<form target="_blank" method="POST" action="<?=URL::to('/inventory/printqrcode');?>" accept-charset="UTF-8" id="printqrcode_form" class="form-horizontal input-has-value" autocomplete="nope"><input name="_token" type="hidden" value="<?=csrf_token()?>" /><input name="productIds" id="productIds" type="hidden" value="" /><input name="without_qr" id="without_qr" type="hidden" value="1" />
</form>

@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$(document).on('click', '#btn-moveto-showroom', function(){
			var action = $('#exhibition-status option:selected').val();
			if(action=='print_qr'){

				var productIds = new Array();
			    $.each($(".chkProduct:checked"), function() {
			        productIds.push($(this).val());
			    });
			    var ids = productIds.join(",");

				$('#productIds').val(ids);
				$('#printqrcode_form').submit();

			} else {
				var inventoryCode = '';
			    if($('#exhibition-status option:selected').data('code'))
			    {
			      inventoryCode = $('#exhibition-status option:selected').data('code');
			    }
			    var productIds = new Array();
			    $.each($(".chkProduct:checked"), function() {
			        productIds.push($(this).val());
			    });
			    var ids = productIds.join(",");
			    if(action=='in'){
			        $.ajax({
			            type: "POST",
			            dataType: "json",
			            data: {
			              status:action,productIds:productIds,inventoryCode:inventoryCode,exhibition_id: $("#exhibition_id").val(),_token: "{{ csrf_token() }}"
			            },
			            url: '<?=URL::to('/inventory/changeinventorystatusandremovefromexhibition');?>',
			            beforeSend: function()
			            {
			              showLoader();
			            },
			            success: function(data) {
			            	refreshExhibitionDetail($("#exhibition_id").val());
			            	if(data.status)
				              {
				                  exhibitionProductsTable.draw();

				                  swal({
				                    title: 'Success',
				                    text: '<?php echo Config::get('constants.message.exhibition_product_moved_to_showroom_success'); ?>',
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
			}
		});
		$("#chkAllProduct").click(function(){
		    $('.chkProduct').prop('checked', this.checked);
		});
		$(document).on('change', '.exhibition_action', function(){
			var productId = $('option:selected', this).attr('data-productid');
			if(this.value == '<?=$inStatusVal?>')
			{
			      var inventoryCode = '<?php echo $inStatusVal ?>';
			      $.ajax({
			            type: "POST",
			            dataType: "json",
			            data: {
			              status:this.value,productIds:productId,inventoryCode:inventoryCode,exhibition_id: $("#exhibition_id").val(),_token: "{{ csrf_token() }}"
			            },
			            url: '<?=URL::to('/inventory/changeinventorystatusandremovefromexhibition');?>',
			            beforeSend: function()
			            {
			              showLoader();
			            },
			            success: function(data) {
			            	refreshExhibitionDetail($("#exhibition_id").val());
			              if(data.status)
			              {
			                  exhibitionProductsTable.draw();

			                  swal({
			                    title: 'Success',
			                    text: '<?php echo Config::get('constants.message.exhibition_product_moved_to_showroom_success'); ?>',
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
		});
		var exhibitionProductsTable = $('#exhibitionProductsTable').DataTable({
			"dom": '<"datatable_top_custom_lengthinfo"i <"#exhibition-toolbar">>frti<"datatable_bottom_custom_length"l>p',
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
			    "url": '<?=URL::to('/inventory/exhibitionproductajaxlist');?>',
			    "data": function(data, callback){
			      data.exhibition_id = $("#exhibition_id").val();
			      data._token = "{{ csrf_token() }}";
			      showLoader();
			      $(".dropdown").removeClass('show');
			      $(".dropdown-menu").removeClass('show');
			    },
			    complete: function(response){
			      hideLoader();
			    }
			  },
			"columnDefs": [
			      { "orderable": false, "targets": [0] }
			  ]
		});
		$("#exhibitionProductsTable tr th:first").removeClass('sorting_asc');
		$divContainer = $('<div class="inventory-action-container"/>').appendTo('#exhibition-toolbar');
		$select = $('<select class="mx-2 mr-3 height-35 padding-four" id="exhibition-status"/>').appendTo($divContainer);
		$('<option data-code="<?=$inStatusVal?>"/>').val('in').text('Move to Showroom').appendTo($select);
		$('<option data-code="print_qr"/>').val('print_qr').text('Print QR').appendTo($select);
		$('<button class="btn btn-primary height-35" type="button" id="btn-moveto-showroom"/>').text('Submit').appendTo($divContainer);
		$("#exhibitionProductsTable tr .checkboxth").removeClass('sorting_asc');
	});
	function refreshExhibitionDetail(exhibitionId)
	{
		$.ajax({
	        type: "POST",
	        dataType: "json",
	        data: {
	          exhibition_id: exhibitionId,_token: "{{ csrf_token() }}"
	        },
	        url: '<?=URL::to('/inventory/refreshexhibitiondetail');?>',
	        beforeSend: function()
	        {
	          showLoader();
	        },
	        success: function(data) {
	        	hideLoader();
	        	if(data.status)
	        	{
	        		$("#total-products").html(data.product_count);
	        		$("#grand-total").html(data.grand_total);
	        	}
	        }
	    });
	}
</script>
@endsection