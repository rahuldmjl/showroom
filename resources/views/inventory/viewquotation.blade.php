<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;

$shapeArr = config('constants.enum.diamond_shape'); //get stone shape for accordian
$labourChargeValue = isset($quotation->labour_charge) ? json_decode($quotation->labour_charge) : '';
$productData = isset($quotation->product_data) ? json_decode($quotation->product_data) : '';

// echo "<pre>";
// print_r($productData);exit;
?>
@extends('layout.mainlayout')

@section('title', 'Quotation')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
@section('distinct_head')
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
    {{ Breadcrumbs::render('inventory.viewquotation', $id) }}
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
  					<div class="row mx-0 label-text-pl-25">
  						<div class="w-50">
							<label class="col-form-label">Customer: {{$customerName}}</label>
						</div>
						<div class="w-50">
							<label class="col-form-label">Total: {{$totalAmount}}</label>
						</div>
  					</div>
  					<div class="row mx-0 label-text-pl-25">
  						  <div class="accordion w-100" id="quotation-accordion" role="tablist" aria-multiselectable="true">
                    <div class="card card-outline-primary">
                        <div class="card-header" role="tab" id="heading4">
                            <h5 class="m-0"><a role="button" data-toggle="collapse" data-parent="#quotation-accordion" href="#diamond_data" aria-expanded="true" aria-controls="diamond_data">Diamond Detail</a></h5>
                        </div>
                        <!-- /.card-header -->
                        <div id="diamond_data" class="card-collapse collapse show" role="tabpanel" aria-labelledby="heading4">
                            <div class="card-body">
                            	<div class="tabs w-100">
          				  							<ul class="nav nav-tabs">
          				  								<?php $activeClass = 'active';foreach ($shapeArr as $key => $shape): ?>
                                    <?php
$stringIndex = strcspn($key, '0123456789');
list($start, $end) = preg_split('/(?<=.{' . $stringIndex . '})/', $key, 2);
$endChar = !empty($end) ? '-' . $end : '';
?>
                                      <?php if (isset($diamondShapeData[$key])): ?>
          				  									<li class="nav-item <?php echo $activeClass ?>"><a class="nav-link" href="#<?php echo $key ?>_shape" data-toggle="tab"><?php echo ucfirst($start) . $endChar; ?></a></li>
                                      <?php endif;?>
          				  								<?php $activeClass = '';endforeach;?>
	                                </ul>
  						                    <div class="tab-content p-3 border border-top-0">
  						                    	<?php $activeClass = 'active';
foreach ($shapeArr as $shapekey => $shape): ?>
  						                    		<div class="tab-pane <?php echo $activeClass; ?>" id="<?php echo $shapekey; ?>_shape">
                                        <div class="row">
  						                    			<?php if (isset($diamondShapeData[$shapekey]) && count($diamondShapeData[$shapekey]) > 0): ?>
  						                    				<?php foreach ($diamondShapeData[$shapekey] as $diamond) {
	$stoneQuality = '';
	foreach ($diamond as $key => $value) {
		$stoneQuality = isset($value['stone_quality']) ? $value['stone_quality'] : '';
		?>
  								                    				<div class="col-12 col-lg-12 stone-data-container ">
                                                  <div class="form-group border-light-1">
  								                    					<h6 class="w-100 shape-title"><?php echo isset($value['diamondShape']) ? ucfirst($value['diamondShape']) : '' ?> (<?php echo isset($value['stone_quality']) ? $value['stone_quality'] : '' ?>)</h6>
                                                <div class="row m-0 py-3">
					                                        <!-- <label class="col-auto px-1 col-form-label"><?php //echo isset($value['stone_quality']) ? $value['stone_quality'] : ''?></label> -->
														
					                                        <?php
															
															foreach ($stoneRangeData[$shapekey] as $index => $stoneRange): ?>
					                                        	<?php
$diamond_shape = isset($value['diamondShape']) ? $value['diamondShape'] : '';
		$quotationData = DB::table("quotation_data")->select("*")->where("quotation_id", "=", DB::raw("$id"))->where("stone_shape", "=", DB::raw("'$diamond_shape'"))->where("stone_quality", "=", DB::raw("'$stoneQuality'"))->get()->first();
		///print_r($quotationData);exit;
		$stone_range_data = isset($quotationData->stone_range_data) ? json_decode($quotationData->stone_range_data) : '';

		$stone_range_data = isset($stone_range_data) ? $stone_range_data : array();

		$rangeData = array(
			'stone_shape' => isset($value['diamondShape']) ? $value['diamondShape'] : '',
			'stone_quality' => isset($value['stone_quality']) ? $value['stone_quality'] : '',
			'stone_range_data' => json_encode($stone_range_data),
		);
		?>
					                                        	<div class="col-md px-1">
					                                        		<label class="w-100 text-center" for="stone_range_<?=$stoneRange->stone_carat_from?>_<?=$stoneRange->stone_carat_to?>_<?=$value['stone_quality']?>"><?php echo $stoneRange->stone_carat_from; ?> - <?php echo $stoneRange->stone_carat_to; ?><?php if ($rangeData['stone_shape'] == "round") {$rangeArr = json_decode($rangeData['stone_range_data']); 
																	
																	$firstrangeval = explode('-',$rangeArr->stone_range[0]); 
																	$firstrangeval = trim($firstrangeval[0]);
																	
																	if($firstrangeval != '0.001') { echo ' (MM)'; }}?></label>
					                                        		<input type="hidden" name="stone_data[<?=$stoneQuality?>][stone_shape][]" value="<?php echo isset($value['diamondShape']) ? $value['diamondShape'] : '' ?>">
					                                        		<input type="hidden" name="stone_data[<?=$stoneQuality?>][stone_range][]" value="<?php echo isset($stoneRange->stone_carat_from) ? $stoneRange->stone_carat_from : '' ?> - <?php echo isset($stoneRange->stone_carat_to) ? $stoneRange->stone_carat_to : '' ?>">
					                                        		<input type="text" class="form-control" name="stone_data[<?=$stoneQuality?>][stone_price][]" id="stone_range_<?=$stoneRange->stone_carat_from?>_<?=$stoneRange->stone_carat_to?>_<?=$value['stone_quality']?>" value="<?php echo isset($stone_range_data->stone_price[$index]) ? $stone_range_data->stone_price[$index] : '' ?>" readonly>
						                                        </div>
					                                        <?php endforeach;?>
                                                  </div>
                                                </div>
					                                    </div>
  								                    			<?php
}
}
?>
                                          <div class="w-100"></div>
                                          <div class="col-12 col-lg-6">
                                            <div class="form-group border-light-1">
                                                <label class="w-100 shape-title" for="txtlabourcharge_<?=$shapekey?>">Metal Labour Charge: </label>
                                                <input type="text" class="form-control width-99 mx-auto my-3" id="txtlabourcharge_<?=$shapekey?>" name="txtlabourcharge[<?=$shapekey?>][]" value="<?php echo isset($labourChargeValue->$shapekey[0]) ? $labourChargeValue->$shapekey[0] : '' ?>" readonly>
                                            </div>
                                          </div>
                                        </div>
  							                    		<?php else: ?>
														  <div class="col-12">
																<p>No products!</p>
														  </div>
  						                    			<?php break; endif;?>
  						                    		</div>
  						                    	<?php $activeClass = '';endforeach;?>
  						                    </div>
        				  						</div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card-collapse -->
                    </div>
                    <!-- /.panel -->
                </div>
              </div>
            </div>
                <div class="accordion w-100" id="quotation-accordion" role="tablist" aria-multiselectable="true">
                    <div class="card card-outline-primary">
                        <div class="card-header" role="tab" id="heading4">
                            <h5 class="m-0"><a role="button" data-toggle="collapse" data-parent="#quotation-accordion" href="#product_data" aria-expanded="true" aria-controls="product_data">Product Detail</a></h5>
                        </div>
                        <!-- /.card-header -->
                        <div id="product_data" class="card-collapse collapse" role="tabpanel" aria-labelledby="heading4">
                            <div class="card-body">
                            	<table class="table table-striped word-break table-center" id="productListTable" >
                            		<thead>
        				  								<tr>
        				  									<th>Sr No.</th>
        				  									<th>SKU</th>
        				  									<th>Certificate No</th>
        				  									<th>Total Metal Weight</th>
        				  									<th>Total Stone Carat</th>
        				  									<th>Amount</th>
        				  								</tr>
        				  							</thead>
        				  							<tbody>
                            	<?php

$productData = json_decode($quotation->product_data);
$serialNumber = 0;
foreach ($productData as $key => $product) {
	$serialNumber++;
	//echo "<pre>";
	//print_r($product);exit;
	DB::setTablePrefix('');
	$productId = $product->product_id;
  $priceMarkup = isset($product->price_markup) ? $product->price_markup : 0;
	$productInfo = DB::table("catalog_product_flat_1")->select("sku", "certificate_no")->where("entity_id", "=", DB::raw("$product->product_id"))->get()->first();
	$sku = isset($productInfo->sku) ? $productInfo->sku : '';
	$certificateNo = isset($productInfo->certificate_no) ? $productInfo->certificate_no : '';
	$metalWeight = isset($product->metal_rate_data->$productId->metal_weight) ? $product->metal_rate_data->$productId->metal_weight : '';
	//Get gem stone data
	$gemStoneData = InventoryHelper::getGemStoneData($product->product_id);

	$gemStone = isset($gemStoneData['simple']) ? round($gemStoneData['simple']) : 0;
	$totalStoneCaret = 0;
	$stonePrice = 0;
	$productPrice = 0;

	foreach ($product->stone_data as $key => $stone) {
		//var_dump($stone->total_stone_caret);exit;
		$totalStoneCaret += (isset($stone->total_stone_caret) ? $stone->total_stone_caret : 0);
		$stonePrice += isset($stone->final_stone_price) ? round($stone->final_stone_price) : 0;
	}

	$labourCharge = isset($product->labour_charge_data->$productId->final_labour_charge) ? $product->labour_charge_data->$productId->final_labour_charge : 0;
	
  $metalPrice = isset($product->metal_rate_data->$productId->final_metal_rate) ? $product->metal_rate_data->$productId->final_metal_rate : 0;
	$productPrice = round($labourCharge) + round($metalPrice) + round($stonePrice) + round($gemStone);
  $markupAmount = 0;
  if(!empty($priceMarkup))
    $markupAmount = ($productPrice * $priceMarkup) / 100;
  $productPrice += $markupAmount;
	$productPrice = ShowroomHelper::currencyFormat(round($productPrice));

	?>
                            		<tr>
                            			<td>{{$serialNumber}}</td>
                            			<td>{{$sku}}</td>
                            			<td>{{$certificateNo}}</td>
                            			<td>{{$metalWeight}}</td>
                            			<td>{{$totalStoneCaret}}</td>
                            			<td>{{$productPrice}}</td>
                            		</tr>
                            	<?php
}
//echo "test";exit;
?>
                                </tbody>
                              </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card-collapse -->
                    </div>
                    <!-- /.panel -->
                </div>
  					</div>
            <div class="row mx-0 label-text-pl-25">
                <div class="checkbox checkbox-primary">
                    <label class="<?php echo ($isDefaultQuotation == '1') ? 'checkbox-checked' : '' ?>">
                        <input type="checkbox" name="chkDefaultQuotation" id="chkDefaultQuotation" <?php echo ($isDefaultQuotation == '1') ? 'checked' : '' ?> disabled> <span class="label-text">Default quotation with DML price"</span>
                    </label>
                </div>
            </div>
            <div class="row mx-0 label-text-pl-25 mt-2">
                <a href="{{ route('inventory.editquotation',$id) }}" class="btn btn-primary">Edit</a>
            </div>
  				</div>
  			</div>
  		</div>
    </div>
  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->
<input type="hidden" id="quotationId" value="{{$id}}">
<input type="hidden" id="exportQuotation" value="{{ route('exportquotationexcel',['id'=>$id]) }}">
<input type="hidden" id="excelFlag" value="{{$excelFlag}}">
@endsection
<style>
.shape-title{background-color: #f2f2f2;padding: 10px;margin: 0;color: #000;text-transform: uppercase;}
/* .stone-data-container
{
  border: 1px solid #e6e5e5;
} */
.width-99{width: 99% ! important;}
#wrapper .navbar ul.navbar-nav a i.material-icons{line-height: 5.625rem;}
#wrapper .navbar div.btn-list.dropdown a i.material-icons{line-height:1;}
#wrapper .navbar form.navbar-search{margin-bottom: 0;}
</style>
@section('distinct_footer_script')
<?php DB::setTablePrefix('dml_');?>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
	var productListTable = $('#productListTable').DataTable({
		"columnDefs": [
		      { "orderable": false, "targets": [0] }
		],
	});
	$("#productListTable tr th:first").removeClass('sorting_asc');
//export quotation excel
window.onload = function(e){
  if($("#excelFlag").val())
 	window.location.href = $("#exportQuotation").val();
}
</script>
@endsection