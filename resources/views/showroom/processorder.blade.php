<?php
use App\Helpers\ShowroomHelper;
use App\Helpers\InventoryHelper;
use Illuminate\Http\Request;

//$total_products = ShowroomHelper::getProducts(1);
//$productCollection = ShowroomHelper::getProducts(false, $post_products);
/*echo "<pre>";
print_r(Session::all());exit;*/
$productCollection = ShowroomHelper::getShowroomProcessProducts($post_products);
//var_dump($productCollection);exit;
$price = ShowroomHelper::getMinMaxPriceForFilter();
$priceStart = isset($price['min_price']) ? $price['min_price'] : 0;
$priceEnd = isset($price['max_price']) ? $price['max_price'] : 0;


$total_products = $productCollection['totalCount'];
$productCollection = $productCollection['productCollection'];
?>
@extends('layout.mainlayout')

@section('title', 'Showroom Process Order')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/css/ion.rangeSlider.min.css"/>
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">

      <!-- /.page-title-right -->
  </div>
  <div class="col-md-12 widget-holder loader-area" style="display: none;">
    <div class="widget-bg text-center">
      <div class="loader"></div>
    </div>
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div class="widget-list">
        <div class="row">
          <div class="col-md-12 widget-holder">
              <div class="widget-bg">
                  <!-- /.widget-heading -->
                  <div class="widget-body clearfix">
                      @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif
                      <a href="<?=URL::to('showroom');?>" class="btn btn-secondary ripple">
                        <i class="material-icons list-icon">arrow_back</i>
                        <span>Back to Showroom</span>
                      </a>
                      {!! Form::open(array('route' => 'showroom.placeorder','method'=>'POST', 'name' => 'showrom_place_order', 'id' => 'process_order_form')) !!}
                      <div class="table mt-3">
                        <table class="showroom-process-table table table-center table-head-box" id="showroom_process_table">
                          <thead>
                                  <tr class="bg-primary">
                                      <th>&nbsp;</th>
                                      <th><?php echo 'Image'; ?></th>
                                      <th><?php echo 'Product Details'; ?></th>
                                      <th><?php echo 'Qty'; ?></th>
                                      <th><?php echo 'Metal Quality'; ?></th>
                                      <th><?php echo 'Metal Weight'; ?></th>
                                      <th><?php echo 'Diamond Quality'; ?></th>
                                      <th><?php echo 'Diamond Weight'; ?></th>
                                      <th><?php echo 'Price'; ?></th>
                                      <th><?php echo 'Criteria Status'; ?></th>
                                      <th><?php echo 'Vendor'; ?></th>
                                  </tr>
                              </thead>
                              <tbody>
                          <?php
foreach ($productCollection as $proKey => $product) {

	//var_dump($product);exit;
	$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
	$category_name = $categoryNames[0]->category_name;
	//var_dump($product->entity_id);
	$imageDirectory = config('constants.dir.website_url_for_product_image');
	$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
	$defaultProductImage = $imageDirectory . 'def_1.png';

	//var_dump($defaultProductImage);

	$metalData = ShowroomHelper::getMetalData($product->entity_id, $product);
	//var_dump($metalData);
	$metalQuality = $metalData['quality'];
	$selected_metal_quality = $metalData['metal-id'];
	$metal_gross = $metalData['weight'];

	$metalColorArray = explode(' ', $metalQuality);
  if(isset($metalColorArray[1]) && isset($metalColorArray[2]))
	$metalColor = $metalColorArray[1] . ' ' . $metalColorArray[2];
	$metalGross = $metalData['weight'];
	$metalQuality = $metalColorArray[0];
	$kt_18_wt = 0;
	$kt_14_wt = 0;
	if ($metalQuality == '18K') {
		$kt_14_wt = ($metalGross * 85) / 100;
		$kt_18_wt = $metalGross;
	} elseif ($metalQuality == '14K') {
		$kt_14_wt = $metalGross;
		$kt_18_wt = ($metalGross * 100) / 85;
	}

	$stone = $product->rts_stone_quality;
	$stoneData = ShowroomHelper::getSideStoneData($product->entity_id, $stone);

	if (!empty($stoneData['totalweight'][0])) {

		$diamond_total_weight = (!empty($stoneData['totalweight'][0]) ? $stoneData['totalweight'][0] : 0.00);

		$ratio_in_18K = $kt_18_wt / $diamond_total_weight;
		$ratio_in_14K = $kt_14_wt / $diamond_total_weight;

		if ($ratio_in_18K <= 10 && $ratio_in_14K <= 10) {
			$remarks = 'OK in both';
			$criteria_18k = true;
			$criteria_14k = true;
		} elseif ($ratio_in_18K <= 10 && $ratio_in_14K > 10) {
			$remarks = 'OK in 18K';
			$criteria_18k = true;
			$criteria_14k = false;
		} elseif ($ratio_in_18K > 10 && $ratio_in_14K <= 10) {
			$remarks = 'OK in 14K';
			$criteria_18k = false;
			$criteria_14k = true;
		} else {
			$remarks = 'Not in criteria';
			$criteria_18k = false;
			$criteria_14k = false;
		}

		if (strpos($metalData['quality'], '14K') !== false) {

			if ($criteria_14k) {

				$crit_status = 'In Criteria';

			} else {

				$crit_status = 'Not In Criteria';

			}

		} elseif (strpos($metalData['quality'], '18K') !== false) {

			if ($criteria_18k) {

				$crit_status = 'In Criteria';

			} else {

				$crit_status = 'Not In Criteria';

			}

		} else {

			if ($criteria_18k) {

				$crit_status = 'In Criteria';

			} else {

				$crit_status = 'Not In Criteria';

			}

		}
	} else {
		$crit_status = 'Not In Criteria';
	}

	$model_quality_model = ShowroomHelper::getMetalQualities();
	$stone_options = ShowroomHelper::getStoneClarities();
	$vendor_model = ShowroomHelper::getVendors();
	?>
		                            <tr>
                                  <td><span class="remove-product pointer" data-product-id="<?=$product->entity_id?>" id="remove_<?=$product->entity_id?>"><i class="fa fa-remove"></i></span></td>
                                  <td>
                                    <input type="hidden" name="product_id[]" id="hdn_product_id_<?=$product->entity_id?>" value="<?=$product->entity_id?>">
                                    <input type="hidden" name="sku[]" id="hdn_sku_<?=$product->entity_id?>" value="<?=$product->sku?>">
                                    <input type="hidden" name="certificate[]" id="hdn_certificate_<?=$product->entity_id?>" value="<?=$product->certificate_no?>">
                                    <input type="hidden" name="hdn_qty[]" id="hdn_qty_<?=$product->entity_id?>" value="1">
	<input type="hidden" name="hdn_metal_quality[]" id="hdn_metal_quality_<?=$product->entity_id?>" value="<?=$selected_metal_quality?>">
	<input type="hidden" name="metal_weight[]" id="hdn_metal_weight_<?=$product->entity_id?>" value="<?=$metal_gross?>">
	<input type="hidden" name="diamond_weight[]" id="hdn_diamond_weight_<?=$product->entity_id?>" value="<?=$diamond_total_weight?>">
	<input type="hidden" name="product_price[]" id="hdn_product_price_<?=$product->entity_id?>" value="<?=$product->custom_price?>">
	<input type="hidden" name="product_total[]" id="hdn_product_total_<?=$product->entity_id?>" value="<?=$product->custom_price?>">
	<input type="hidden" name="product_criteria[]" id="hdn_product_criteria_<?=$product->entity_id?>" value="<?=$crit_status?>">
  <input type="hidden" name="diamond_price[]" id="hdn_diamond_price_<?=$product->entity_id?>" value="">
                                    <img alt="{{$product->entity_id}}" src="{{!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
                                  <td>
<?php
echo $product->sku;
	echo '<br>';
	if (!empty($product->certificate_no)) {
		echo $product->certificate_no;
		echo '<br>';
	}
	if ($product->attribute_set_id == '14') {
		$product_size = $product->rts_ring_size;
	} elseif ($product->attribute_set_id == '17') {
		$product_size = $product->rts_bangle_size;
	} elseif ($product->attribute_set_id == '23') {
		$product_size = $product->rts_bracelet_size;
	}
	if (!empty($product_size)) {
		echo $product_size;
		echo '<br>';
	}
	if (!empty($category_name)) {
		echo $category_name;
		echo '<br>';
	}
	?>
</td>

<td>
  {!! Form::number('qty[]', 1, array('class' => 'form-control process-qty', 'data-product-id' => $product->entity_id, 'id' => 'qty_'.$product->entity_id , 'min' => '0', 'max' => '99', 'step' => '1', 'onblur'=>'this.checkValidity()')) !!}
<td>
<select name="metal_quality[]" class="form-control select-metal-quality" data-product-id="<?=$product->entity_id?>" id="metal_quality_<?=$product->entity_id?>">
<?php
foreach ($model_quality_model as $metal_quality) {
		?>
<option <?php if (strpos($metal_quality->metal_quality, '14K') !== false) {echo 'data-metal-weight="' . $kt_14_wt . '"';if ($criteria_14k) {echo 'data-criteria="In Criteria"';} else {echo 'data-criteria="Not In Criteria"';}} elseif (strpos($metal_quality->metal_quality, '18K') !== false) {echo 'data-metal-weight="' . $kt_18_wt . '"';if ($criteria_18k) {echo 'data-criteria="In Criteria"';} else {echo 'data-criteria="Not In Criteria"';}} else {echo 'data-metal-weight="' . $kt_18_wt . '"';if ($criteria_18k) {echo 'data-criteria="In Criteria"';} else {echo 'data-criteria="Not In Criteria"';}}?> <?php if ($metal_quality->metal_quality == $metalData['quality']) {echo 'selected="selected"';}?> value="<?=$metal_quality->grp_metal_quality_id?>"><?=$metal_quality->metal_quality?></option>
<?php
}
	?>
</select>
</td>
<td><span id="metal_weight_<?=$product->entity_id?>" data-gross="<?php echo $metal_gross; ?>"><?php echo round($metal_gross, 2); ?></span></td>
<td>
<select name="diamond_quality[]" class="form-control select-diamond-quality" data-product-id="<?=$product->entity_id?>" id="diamond_quality_<?=$product->entity_id?>">
<?php
foreach ($stone_options as $option) {
		?>
<option data-option_id="<?= $option->option_id?>" <?php if ($option->value == $stone) {echo 'selected="selected"';}?> value="<?=$option->value?>"><?=$option->value?></option>
<?php
}
	?>
</select>
</td>
<td><?php echo $diamond_total_weight; ?></td>
<td><span id="product_price_<?=$product->entity_id?>" data-price="<?=$product->custom_price?>"><?=CommonHelper::covertToCurrency($product->custom_price)?><?php // echo $product['custom_price'] ?></span></td>
<td><span id="criteria_<?=$product->entity_id?>"><?=$crit_status?></span></td>
<td>
<?php ?>
	<select name="vendor[]" class="form-control select-vendor" data-product-id="<?=$product->entity_id?>" id="vendor_<?=$product->entity_id?>">
	<?php
foreach ($vendor_model as $vendor) {
		?>
	<option value="<?=$vendor->id?>"><?=$vendor->name?></option>
	<?php
}
	?>
	</select>
	<?php ?>
</td>
<?php
}
?>
                          </tbody>
                        </table>
                    </div>
                        <button type="submit" name="place_order" id="place_order" value="Place Order" class="btn btn-outline-primary ripple">
                          <span>Place Order</span>
                          <i class="material-icons list-icon">forward</i>
                        </button>
                        {!! Form::close() !!}
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
<input type="hidden" id="showroomHome" value="<?=URL::to('/showroom');?>">

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/dataTables.responsive.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/js/ion.rangeSlider.min.js"></script>
<script type="text/javascript">

    jQuery(document).ready(function(){

    jQuery('.remove-product').click(function(){
      var removeButton = $(this);
      var productId = $(this).data('product-id');
      $.ajax({
            url:'<?=URL::to('/showroom/removeprocessedproductfromsession');?>',
            method:"post",
            data:{product_id: productId,_token: "{{ csrf_token() }}"},
            success: function(response){
                var res = JSON.parse(response);
                if(res.status)
                {
                	removeButton.closest('tr').remove();
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
    })

    jQuery('.process-qty').change(function(){
      var productId = jQuery(this).attr('data-product-id');
      jQuery('#hdn_qty_'+productId).val(jQuery(this).val());
      var productPrice = jQuery('#hdn_product_price_'+productId).val();
      var newTotalPrice = productPrice * jQuery(this).val();
      jQuery('#hdn_product_total_'+productId).val(newTotalPrice);
      jQuery('#product_price_'+productId).html(newTotalPrice);
    });
    jQuery(document).on('change','.select-diamond-quality', function(){
        var productId = jQuery(this).attr('data-product-id');
        var productMetalWeight = jQuery("#metal_quality_"+productId).find(':selected').attr('data-metal-weight');
        var qty = jQuery("#qty_"+productId).val();
        var metalQuality = jQuery("#metal_quality_"+productId).val();
        var diamondQuality = jQuery("#diamond_quality_"+productId).children('option:selected').data('option_id');
        var diamondWeight = jQuery("#hdn_diamond_weight_"+productId).val();
        productMetalWeight = parseFloat(productMetalWeight);
        calculateProductPrice(productId, qty, metalQuality, productMetalWeight, diamondQuality, diamondWeight);
    });
    jQuery(document).on('change','.select-metal-quality', function(){
      var productId = jQuery(this).attr('data-product-id');
      var productMetalWeight = jQuery(this).find(':selected').attr('data-metal-weight');
      var productCriteria = jQuery(this).find(':selected').attr('data-criteria');
      var qty = jQuery("#qty_"+productId).val();
      var metalQuality = jQuery(this).val();
      var diamondQuality = jQuery("#diamond_quality_"+productId).children('option:selected').data('option_id');
      var diamondWeight = jQuery("#hdn_diamond_weight_"+productId).val();
      
      productMetalWeight = parseFloat(productMetalWeight);
      //console.log(productMetalWeight)
      calculateProductPrice(productId, qty, metalQuality, productMetalWeight, diamondQuality, diamondWeight);
      
      jQuery('#metal_weight_'+productId).html(productMetalWeight.toFixed(2));
      jQuery('#hdn_metal_weight_'+productId).val(productMetalWeight.toFixed(2));
      jQuery('#criteria_'+productId).html(productCriteria);
      jQuery('#hdn_metal_quality_'+productId).val(jQuery(this).val());

    });


    var selectedProducts = [];
    jQuery(document).on('click','#chkAll',function () {
      jQuery('.chkProduct').prop('checked', this.checked);
      });

  });
  function calculateProductPrice(productId, qty, metalQuality, metalWeight, diamondQuality, diamondWeight)
  {
    $.ajax({
            type: 'POST',
            data: {product_id:productId, qty:qty, metal_quality:metalQuality, metal_weight: metalWeight, diamond_quality:diamondQuality, diamond_weight:diamondWeight,_token: "{{ csrf_token() }}"},
            url: '<?=URL::to('/showroom/calculateproductprice');?>',
            beforeSend: function()
            {
              showLoader();
            },
            success: function(response){
                var res = JSON.parse(response);
                if(res.status)
                {
                    hideLoader();
                    jQuery('#hdn_product_price_'+productId).val(res.unit_price);
                    jQuery('#hdn_product_total_'+productId).val(res.unit_price);
                    jQuery('#hdn_diamond_price_'+productId).val(res.diamond_price);
                    $("#product_price_"+productId).html('₹ '+res.unit_price);
                }
            }
      });
  }
</script>
<style>
.product-img{max-width: 40px;}
</style>
@endsection