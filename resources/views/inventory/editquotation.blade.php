<?php
use App\Helpers\InventoryHelper;
$shapeArr = config('constants.enum.diamond_shape');
foreach ($quotationData as $key => $quotation) {
	$labourChargeValue = isset($quotation->labour_charge) ? json_decode($quotation->labour_charge) : '';
	$productData = isset($quotation->product_data) ? json_decode($quotation->product_data) : '';
}
?>
@extends('layout.mainlayout')

@section('title', 'Quotation')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/multi-select/0.9.12/css/multi-select.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/css/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  	{{ Breadcrumbs::render('inventory.editquotation', $id) }}
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
  	<div class="widget-list">
      	<div class="row">
  			<div class="col-md-12 widget-holder content-area">
  				<div class="widget-bg">
  					{!! Form::open(array('route' => 'inventory.store','method'=>'POST','id'=>'quotation-form','class'=>'form-horizontal')) !!}
  					{{ Form::hidden('product_ids', $product_ids, array('id' => 'product_ids')) }}
  					{{ Form::hidden('quotation_id', $id, array('id' => 'quotation_id')) }}
  					{{ Form::hidden('customer_id', $customerId, array('id' => 'customer_id')) }}
  					<div class="row m-0 justify-content-end">
  						<button class="btn btn-primary small-btn-style" type="button" id="btn-add-product">Add Product</button>
						<input type="text" class="form-control txtaddproduct ml-3 w-auto" id="txtaddproduct" name="txtaddproduct" placeholder="Enter Certificate">
  					</div>
  					<div class="row mx-0 my-3 quotation-product-container">
						<div class="col-auto align-self-center product-label-container">
	                        <label class="col-form-label p-0 products-label">Products:</label>
	                    </div>
	                    <div class="col bg-white py-2">
	  						<div class="bootstrap-tagsinput space-five-all" id="certificate-tag-input">
	  							<?php
$product_ids = explode(',', $product_ids);
foreach ($product_ids as $product) {
	$certificateNo = !empty(InventoryHelper::getCertificateNo($product)) ? InventoryHelper::getCertificateNo($product) : 'No Certificate';
	?>
	  								<span class="tag label label-info" id="product_{{$product}}">{{$certificateNo}}<span class="pointer" data-role="remove"  onclick="removeProduct('{{$product}}')"></span></span>
	  							<?php
}
?>
		                    </div>
		                </div>
  					</div>
  					<div class="row mx-0 label-text-pl-25">
  						<div class="w-50">
							<label class="col-form-label">Customer: {{$customerName}}</label>
						</div>
						<div class="w-50">
							<label class="col-form-label">Total: {{$totalAmount}}</label>
						</div>
  					</div>
  					<div class="">
  						<section id="diamond-calculation-container" class="w-100">
  							<div class="row mr-l-5 label-text-pl-25">
  								<div class="w-50">
	  								<div class="checkbox checkbox-primary">
	                                    <label class="<?php echo ($isDefaultQuotation == '1') ? 'checkbox-checked' : '' ?>">
	                                        <input type="checkbox" name="chkDefaultQuotation" id="chkDefaultQuotation" <?php echo ($isDefaultQuotation == '1') ? 'checked' : '' ?>> <span class="label-text">Set default quotation with DML price</span>
	                                    </label>
	                                </div>
	                            </div>
  							</div>
  							<div class="row m-0">
  								<div class="tabs w-100 stone-tab-container">
		  							<ul class="nav nav-tabs">
		  								<?php $activeClass = 'active';foreach ($shapeArr as $key => $shape): ?>
			  								<?php
$stringIndex = strcspn($key, '0123456789');
list($start, $end) = preg_split('/(?<=.{' . $stringIndex . '})/', $key, 2);
$endChar = !empty($end) ? '-' . $end : '';
?>
		  									<?php if (isset($diamondShapeData[$key])): ?>
		  									<li class="nav-item <?php echo $activeClass ?>"><a class="nav-link" href="#<?php echo $key ?>_shape" data-toggle="tab"><?php echo ucfirst($start) . $endChar; ?></a>
				                       		 </li>
				                       		<?php endif;?>
		  								<?php $activeClass = '';endforeach;?>
				                    </ul>
				                    <div class="tab-content p-3 border border-top-0">
				                    	<?php $activeClass = 'active';
foreach ($shapeArr as $shapekey => $shape): ?>
				                    		<div class="tab-pane <?php echo $activeClass; ?>" id="<?php echo $shapekey; ?>_shape">
				                    			<?php if (isset($diamondShapeData[$shapekey]) && count($diamondShapeData[$shapekey]) > 0): ?>
				                    				<?php foreach ($diamondShapeData[$shapekey] as $diamond) {
	$stoneQuality = '';
	foreach ($diamond as $key => $value) {
		$stoneQuality = isset($value['stone_quality']) ? $value['stone_quality'] : '';
		?>
						                    				<div class="form-group">
						                    					<div class="col-12 px-0 stone-data-container">
						                    					<h6 class="w-100 shape-title"><?php echo isset($value['diamondShape']) ? ucfirst($value['diamondShape']) : '' ?> (<?php echo isset($value['stone_quality']) ? $value['stone_quality'] : '' ?>)</h6>
						                    					<div class="row m-0 py-3">
						                                        	<!-- <label class="col-auto px-1 col-form-label"><?php //echo isset($value['stone_quality']) ? $value['stone_quality'] : ''?></label> -->
						                                        <?php
$rangeData = array(
			'stone_shape' => isset($value['diamondShape']) ? $value['diamondShape'] : '',
			'stone_quality' => isset($value['stone_quality']) ? $value['stone_quality'] : '',
			'stone_range_data' => json_encode($stoneRangeData),
		);
		?>
						                                        <input type="hidden" name="defaultstoneinfo[]" class="stoneRangeData" value='<?php echo json_encode($rangeData); ?>'>
						                                        <?php foreach ($stoneRangeData[$shapekey] as $index => $stoneRange): ?>
						                                        	<?php
//$stone__shape = $stoneData[trim($stoneClarity)]['stone_shape'][$index];
		$diamond_shape = isset($value['diamondShape']) ? $value['diamondShape'] : '';
		$quotationData = DB::table("quotation_data")->select("*")->where("quotation_id", "=", DB::raw("$id"))->where("stone_shape", "=", DB::raw("'$diamond_shape'"))->where("stone_quality", "=", DB::raw("'$stoneQuality'"))->get()->first();
/*echo "<pre>";
print_r($quotationData);exit;*/
		$stone_range_data = isset($quotationData->stone_range_data) ? json_decode($quotationData->stone_range_data) : '';
		$stone_range_data = isset($stone_range_data) ? $stone_range_data : array();
		?>
						                                        	<div class="w-15 col-md px-1">
						                                        		<label class="w-100 text-center" for="stone_range_<?=$stoneRange->stone_carat_from?>_<?=$stoneRange->stone_carat_to?>_<?=$value['stone_quality']?>"><?php echo isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : '' ?> - <?php echo isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : '' ?></label>
						                                        		<input type="hidden" name="stone_data[<?=$stoneQuality?>][<?=isset($value['diamondShape']) ? $value['diamondShape'] : ''?>][stone_range][]" value="<?php echo isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : '' ?> - <?php echo isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : '' ?>">
						                                        		<input type="number" class="form-control diamond-caret-val" min="1" name="stone_data[<?=$stoneQuality?>][<?=isset($value['diamondShape']) ? $value['diamondShape'] : ''?>][stone_price][]" id="stone_range_<?=isset($value['diamondShape']) ? $value['diamondShape'] : ''?>_<?=isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : ''?>_<?=isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : ''?>_<?=isset($value['stone_quality']) ? $value['stone_quality'] : ''?>" value="<?php echo isset($stone_range_data->stone_price[$index]) ? $stone_range_data->stone_price[$index] : '' ?>" step="0.1">
							                                        </div>
						                                        <?php endforeach;?>
							                                    </div>
							                                </div>
					                                    </div>
						                    			<?php
}
}
?>
						                    		<div class="form-group py-3 mb-0">
						  								<div class="w-25">
						                            		<label class="text-center" for="txtlabourcharge_<?=$shapekey?>">Metal Labour Charge: </label>
						                            		<input type="number" min="1" class="form-control labour-charge-val" id="txtlabourcharge_<?=$shapekey?>" name="txtlabourcharge[<?=$shapekey?>][]" value="<?php echo isset($labourChargeValue->$shapekey[0]) ? $labourChargeValue->$shapekey[0] : '' ?>">
											            </div>
						  							</div>
					                    		<?php else: ?>
						                    		<p>No products!</p>
				                    			<?php endif;?>

					  							<div class="row">
					  								<p class="diamond-data-error"></p>
					  							</div>
				                    		</div>
				                    	<?php $activeClass = '';endforeach;?>

				                    </div>
		  						</div>
  							</div>

  							<div class="form-group row mr-t-10 mr-l-5">
  								<button class="btn btn-primary" type="submit" id="btn-update-quotation">Submit</button>
  							</div>
  						</section>
  					</div>
  					{!! Form::close() !!}
  				</div>
  			</div>
  		</div>
    </div>
  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->
<style>
.diamond-data-error
{
	color: #ff0000;
}
.shape-title
{
	background-color: #f2f2f2;
	padding: 10px;
	margin: 0;
	color: #000;
	text-transform: uppercase;
}
.stone-data-container
{
  border: 1px solid #e6e5e5;
}
.products-label
{
	display: table-cell;vertical-align: middle;height: 100%;color: #fff;
}
.product-label-container
{
	vertical-align: middle;line-height: 30px;display: table;height: 100%;
}
.quotation-product-container
{
	border: 1px solid #ddd;
	box-sizing: border-box;
	background-color: #51d2b7;
}
</style>
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/multi-select/0.9.12/js/jquery.multi-select.min.js"></script>
<script>
$(document).ready(function(){
	$('#chkDefaultQuotation').change(function() {
        if($(this).is(":checked")) {
           	$(this).attr("checked", true);
           	//console.log($(".stoneRangeData").val());
           	var stoneInfoArr = [];
           	$(".stoneRangeData").each(function(index,value) {
           		stoneInfoArr.push(this.value);
           	});

			$.ajax({
                type: 'post',
                url: '<?=URL::to('/inventory/getdefaultstoneprice');?>',
                data:{stone_data:stoneInfoArr,_token:"{{ csrf_token() }}"},
                beforeSend: function(){
                    showLoader();
                    $("#btn-verify-customer").prop('disabled',false);
                },
                success: function(response){
                	var res = JSON.parse(response);
                	$.each( res.stone_data, function( key, value ) {
                		$(".diamond-caret-val").each(function() {
	                		var id = this.id;
	                		if(id == key)
	                		{
	                			$(this).val(value);
	                		}
	                	});
					});
					$.each(res.labour_charge, function(index, item) {
					    $(".labour-charge-val").each(function() {
	                		var id = this.id;
	                		if(id == index)
	                		{
	                			$(this).val(item);
	                		}
	                	});
					});
                    hideLoader();
				}
			});
        }
        else
        {
        	//Get stored quotation price
            $.ajax({
                type: 'post',
                url: '<?=URL::to('/inventory/getcustomerquotation');?>',
                data:{customer_id:$("#customer_id").val(),_token:"{{ csrf_token() }}"},
                beforeSend: function(){
                    showLoader();
                },
                success: function(response){
                	hideLoader();
                	var response = JSON.parse(response);
                	$.each(response.stone_data, function(index, item) {
					    $(".diamond-caret-val").each(function() {
	                		var id = this.id;
	                		if(id == index)
	                		{
	                			$(this).val(item);
	                		}
	                	});
					});
					$.each(response.labour_charge, function(index, item) {
					    $(".labour-charge-val").each(function() {
	                		var id = this.id;
	                		if(id == index)
	                		{
	                			$(this).val(item);
	                		}
	                	});
					});
                }
            });
        	$(this).removeAttr("checked");
        }
    });
	$("#btn-update-quotation").click(function(event){
		event.preventDefault();
		var validationForm = true;
		$(".diamond-caret-val").each(function() {
			var id = $(this).attr("id");
		  	if(this.value == '')
		  	{
		  		$(this).next().remove();
		  		$(this).after('<label id="'+id+'-error" class="error stone-error" for="'+id+'">This value is required</label>');
		  		$(".diamond-data-error").html("Please fill required field");
		  		validationForm = false;
		  	}
		  	else
		  	{
		  		$(this).next().remove();
		  		$(".diamond-data-error").html("");
		  	}
		});
		$(".labour-charge-val").each(function() {
			var id = $(this).attr("id");
			var parentId = $(this).parent().parent().parent().attr('id');
			var rangeInputLength = $("#"+parentId).find('.diamond-caret-val').length;
			if(rangeInputLength > 0)
			{
				if(this.value == '')
			  	{
			  		$(this).next().remove();
			  		$(this).after('<label id="'+id+'-error" class="error labour-charge-error" for="'+id+'">Labour charge is required</label>');
			  		$(".diamond-data-error").html("Please fill required field");
			  		validationForm = false;
			  	}
			  	else
			  	{
			  		$(this).next().remove();
			  		$(".diamond-data-error").html("");
			  	}
			}
		});
		if($("#diamond-calculation-container .error").length > 0)
		{
			validationForm = false;
			$(".diamond-data-error").html("Please fill required field");
		}
		else
		{
			validationForm = true;
			$(".diamond-data-error").html("");
		}
		if(validationForm)
		{
			$("#quotation-form").submit();
		}
	});
	var certificateCount = 0;

	$("#btn-add-product").click(function(event){
		event.stopPropagation();
		event.stopImmediatePropagation();
		if($("#txtaddproduct").val()!='')
		{
			$.ajax({
		        url:'<?=URL::to('/inventory/addproduct');?>',
		        method:"post",
		        data:{certificate_no: $("#txtaddproduct").val(),_token: "{{ csrf_token() }}"},
		        beforeSend: function()
		        {
		        	showLoader();
		        },
		        success: function(response){
		        	if(response!='')
		        	{
		        		var res = JSON.parse(response);
		        		if(res.status)
		        		{
		        			var productIds = $("#product_ids").val();
							var productIdArr = [];
							productIdArr = productIds.split(",");
							if ($.inArray(res.entity_id.toString(), productIdArr)!='-1') {
					            swal({
				                  title: 'Oops!',
				                  text: '<?=Config::get('constants.message.inventory_certificate_already_exist')?>',
				                  type: 'error',
				                  showCancelButton: true,
				                  showConfirmButton: false,
				                  confirmButtonClass: 'btn btn-danger',
				                  cancelButtonText: 'Ok'
				                });
					        } else {
					            $('#certificate-tag-input').append('<span class="tag label label-info" id="product_'+res.entity_id+'">'+res.certificate_no+'<span class="pointer" data-role="remove" onclick="removeProduct('+res.entity_id+')"></span></span>');
								productIdArr.push(res.entity_id.toString());
								$("#product_ids").val(productIdArr.join(","));
								$("#txtaddproduct").val('');
								refreshStoneInfo($("#product_ids").val());
					        }
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
		        		//$(".stone-tab-container").html(response);
		        	}
		        	hideLoader();
		        },
		        error: function(){
		        	hideLoader();
		        }
		    });
		}
		/*$('#certificate-tag-input').append('<span class="tag label label-info" id="product_1276024">'+$("#txtaddproduct").val()+'<span class="pointer" data-role="remove" onclick="removeProduct('+$("#txtaddproduct").val()+')"></span></span>');
		var productIds = $("#product_ids").val();
		var productIdArr = [];
		productIdArr = productIds.split(",");
		productIdArr.push($("#txtaddproduct").val());
		console.log(productIdArr);
		$("#product_ids").val(productIdArr.join(","));*/
	});

	/*var txtaddproduct = $('#txtaddproduct').select2();
    txtaddproduct.on("select2:select", function (e) {
        var selected_element = $(e.currentTarget);
        var select_val = selected_element.val();
        var productIds = $("#product_ids").val();
		var productIdArr = [];
		productIdArr = productIds.split(",");
		var mergedProductIds = $.merge(select_val, productIdArr);
		$("#product_ids").val(mergedProductIds.join(","));
		//refresh stone shape/price tab
		refreshStoneInfo($("#product_ids").val());
    });*/
});
function removeDiv(id)
{
	$("#"+id).remove();
}
function refreshStoneInfo(productIds)
{
	if(productIds!='')
	{
		$.ajax({
	        url:'<?=URL::to('/inventory/refreshstoneinfo');?>',
	        method:"post",
	        data:{productIds: productIds,quotation_id: $("#quotation_id").val(),_token: "{{ csrf_token() }}"},
	        beforeSend: function()
	        {
	        	showLoader();
	        },
	        success: function(response){
	        	if(response!='')
	        	{
	        		$(".stone-tab-container").html(response);
	        	}
	        	hideLoader();
	        },
	        error: function(){
	        	hideLoader();
	        }
	    });
	}
}
function removeProduct(productId)
{
	var productIds = $("#product_ids").val();
	var productIdArr = [];
	productIdArr = productIds.split(",");
	var index = productIdArr.indexOf(productId);
	if (index > -1) {
	  productIdArr.splice(index, 1);
	}
	$("#product_ids").val(productIdArr.join(","));
	$("#product_"+productId).remove();

	if(productId != '')
	{
		$.ajax({
	        url:'<?=URL::to('/inventory/getproductidsfornewproduct');?>',
	        method:"post",
	        data:{productId: $("#product_ids").val(),_token: "{{ csrf_token() }}"},
	        beforeSend: function()
	        {
	        	showLoader();
	        },
	        success: function(response){
	        	if(response!='')
	        	{
	        		$("#txtaddproduct").html(response);
	        		hideLoader();
	        	}
	        	refreshStoneInfo($("#product_ids").val());
	        }
	    });
	}
}
</script>

@endsection