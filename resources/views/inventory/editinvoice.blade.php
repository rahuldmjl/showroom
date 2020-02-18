<?php
use App\Helpers\CustomersHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
$productIds = array();
$customerEmail = '';
$orderId = '';
if (!empty($id)) {
	$customerId = isset($invoiceData->customer_id) ? $invoiceData->customer_id : '';
	$orderId = isset($invoiceData->entity_id) ? $invoiceData->entity_id : '';
	$invoiceItems = InventoryHelper::getInvoiceItems($invoiceData->invoice_ent_id);
	/*$orderItems = InventoryHelper::getOrderItems($id);
		foreach ($orderItems as $key => $item) {
			$productIds[] = $item->product_id;
	*/
}
$shippingCharge = isset($invoiceData->invoice_shipping_charge) ? $invoiceData->invoice_shipping_charge : 0;
//$productIds = implode("','", $productIds);
DB::setTablePrefix('');

//$productCollection = DB::table("catalog_product_flat_1")->select("*")->whereIn('entity_id', [DB::raw("'" . $productIds . "'")])->get();

?>
@extends('layout.mainlayout')

@section('title', 'Edit Invoice')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  	{{ Breadcrumbs::render('inventory.editinvoice', $id) }}
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
  					<div class="widget-header">
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-3">Billing Address</h5>
  					</div>
  					<div class="widget-body clearfix dataTable-length-top-0">
  						{!! Form::open(array('method'=>'POST','id'=>'edit-biling-address-form','class'=>'form-horizontal','autocomplete'=>'nope')) !!}
  						<?php
$defaultBillingAddress = InventoryHelper::getAddressById($invoiceData->billing_address_id);
?>
  						{{ Form::hidden('address_id', $invoiceData->billing_address_id, array('id' => 'billing_address_id')) }}
  						{{ Form::hidden('order_id', $invoiceData->entity_id, array('id' => 'order_id')) }}
  						{{ Form::hidden('customer_id', $invoiceData->customer_id, array('id' => 'customer_id')) }}
  						{{ Form::hidden('invoice_id', $invoiceData->invoice_ent_id, array('id' => 'invoice_id')) }}
  						{{ Form::hidden('_token', csrf_token()) }}
  						{{ Form::hidden('address_type', 'billing_address') }}
  						<div class="row mr-b-10">
					    	<div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtfirstname">First Name <span class="text-danger">*</span></label>
					            <?php $firstName = isset($defaultBillingAddress->firstname) ? $defaultBillingAddress->firstname : ''?>
					            {!! Form::text('txtfirstname', $firstName , array('class' => 'form-control required','id'=>'txtfirstname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtlastname">Last Name <span class="text-danger">*</span></label>
					            <?php $lastName = isset($defaultBillingAddress->lastname) ? $defaultBillingAddress->lastname : ''?>
					            {!! Form::text('txtlastname', $lastName, array('class' => 'form-control','id'=>'txtlastname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtcontactnumber">Contact Number <span class="text-danger">*</span></label>
					            <?php $telephone = isset($defaultBillingAddress->telephone) ? $defaultBillingAddress->telephone : ''?>
					            {!! Form::text('txtcontactnumber', $telephone, array('class' => 'form-control','id'=>'txtcontactnumber','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>

					        <div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtaddress">Address <span class="text-danger">*</span></label>
					            <?php $street = isset($defaultBillingAddress->street) ? $defaultBillingAddress->street : ''?>
					            {!! Form::text('txtaddress', $street, array('class' => 'form-control','id'=>'txtaddress','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field">
					        	<label for="selectcountry">Country <span class="text-danger">*</span></label>
					            <select class="form-control height-35 selectcountry" data-statediv="billing_state" name="selectcountry" id="billing_selectcountry" data-placeholder="Select">
										<option value="">Select</option>
					                    <option value="<?php echo $countryList['country_id']; ?>" <?=($defaultBillingAddress->country_id == $countryList['country_id']) ? 'selected' : ''?>><?php echo $countryList['name'] ?></option>
					            </select>
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field customer-state" id="billing_state">
					            <label for="txtstateprovince">State/Province <span class="text-danger">*</span></label>
					            <?php
$stateList = CustomersHelper::getStateList($defaultBillingAddress->country_id);

?>
					            <?php if (count($stateList) > 0): ?>
									<select class="form-control height-35" id="txtstateprovince" name="txtstateprovince">
										<?php foreach ($stateList as $state): ?>
											<option value="<?=$state->region_id?>" <?=($defaultBillingAddress->region == $state->name) ? 'selected' : ''?>><?=$state->name?></option>
										<?php endforeach;?>
									</select>
					            <?php else: ?>
					            	{!! Form::text('txtstateprovince', $defaultBillingAddress->region, array('class' => 'form-control','id'=>'txtstateprovince','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					            <?php endif;?>
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtcity">City <span class="text-danger">*</span></label>
					            <?php $city = isset($defaultBillingAddress->city) ? $defaultBillingAddress->city : ''?>
					            {!! Form::text('txtcity', $city, array('class' => 'form-control','id'=>'txtcity','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtzipcode">Zip Code <span class="text-danger">*</span></label>
					            <?php $zipCode = isset($defaultBillingAddress->postcode) ? $defaultBillingAddress->postcode : ''?>
					            {!! Form::text('txtzipcode', $zipCode, array('class' => 'form-control','id'=>'txtzipcode','maxlength'=>'6','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
							<div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtgstin">GSTIN</label>
					            <?php 
								$gstin = CustomersHelper::getCustomerAttrValue($customerId, 'gstin');
								$gstin = empty($gstin) ? '' : $gstin;
								?>
					            {!! Form::text('txtgstin', $gstin, array('class' => 'form-control','id'=>'txtgstin','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
					        <div class="col-md-12 mb-3 new-customer-field">
					        	<button class="buttons-html5 btn btn-primary btn-sm px-3" type="submit" id="btn-update-billingaddress">Update</button>
					        </div>
					    </div>
					    {!! Form::close() !!}
  					</div>
  				</div>
  			</div>
  		</div>
  		<div class="row">
  			<div class="col-md-12 widget-holder content-area">
  				<div class="widget-bg">
  					<div class="widget-header">
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-3">Shipping Address</h5>
  					</div>
  					<div class="widget-body clearfix dataTable-length-top-0">
  						{!! Form::open(array('method'=>'POST','id'=>'edit-shipping-address-form','class'=>'form-horizontal edit_customer_form','autocomplete'=>'nope')) !!}
  						<?php
$defaultShippingAddress = InventoryHelper::getAddressById($invoiceData->shipping_address_id);
?>
  						{{ Form::hidden('address_type', 'shipping_address') }}
  						{{ Form::hidden('address_id', $invoiceData->shipping_address_id, array('id' => 'shipping_address_id')) }}
  						{{ Form::hidden('order_id', $invoiceData->entity_id, array('id' => 'order_id')) }}
  						{{ Form::hidden('invoice_id', $invoiceData->invoice_ent_id, array('id' => 'invoice_id')) }}
  						{{ Form::hidden('customer_id', $invoiceData->customer_id, array('id' => 'customer_id')) }}
  						{{ Form::hidden('_token', csrf_token()) }}
  						<div class="row mr-b-10">
					    	<div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtfirstname">First Name <span class="text-danger">*</span></label>
					            <?php $firstName = isset($defaultShippingAddress->firstname) ? $defaultShippingAddress->firstname : ''?>
					            {!! Form::text('txtfirstname', $firstName , array('class' => 'form-control required','id'=>'txtfirstname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtlastname">Last Name <span class="text-danger">*</span></label>
					            <?php $lastName = isset($defaultShippingAddress->lastname) ? $defaultShippingAddress->lastname : ''?>
					            {!! Form::text('txtlastname', $lastName, array('class' => 'form-control','id'=>'txtlastname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtcontactnumber">Contact Number <span class="text-danger">*</span></label>
					            <?php $telephone = isset($defaultShippingAddress->telephone) ? $defaultShippingAddress->telephone : ''?>
					            {!! Form::text('txtcontactnumber', $telephone, array('class' => 'form-control','id'=>'txtcontactnumber','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>

					        <div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtaddress">Address <span class="text-danger">*</span></label>
					            <?php $street = isset($defaultShippingAddress->street) ? $defaultShippingAddress->street : ''?>
					            {!! Form::text('txtaddress', $street, array('class' => 'form-control','id'=>'txtaddress','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field">
					        	<label for="selectcountry">Country <span class="text-danger">*</span></label>
					            <select class="form-control height-35 selectcountry" data-statediv="shipping_state" name="selectcountry" id="shipping_selectcountry" data-placeholder="Select">
										<option value="">Select</option>
					                    <option value="<?php echo $countryList['country_id']; ?>" <?=($defaultShippingAddress->country_id == $countryList['country_id']) ? 'selected' : ''?>><?php echo $countryList['name'] ?></option>
					            </select>
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field customer-state" id="shipping_state">
					            <label for="txtstateprovince">State/Province <span class="text-danger">*</span></label>
					            <?php
$stateList = CustomersHelper::getStateList($defaultShippingAddress->country_id);

?>
					            <?php if (count($stateList) > 0): ?>
									<select class="form-control height-35" id="txtstateprovince" name="txtstateprovince">
										<?php foreach ($stateList as $state): ?>
											<option value="<?=$state->region_id?>" <?=($defaultShippingAddress->region == $state->name) ? 'selected' : ''?>><?=$state->name?></option>
										<?php endforeach;?>
									</select>
					            <?php else: ?>
					            	{!! Form::text('txtstateprovince', $defaultShippingAddress->region, array('class' => 'form-control','id'=>'txtstateprovince','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					            <?php endif;?>
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtcity">City <span class="text-danger">*</span></label>
					            <?php $city = isset($defaultShippingAddress->city) ? $defaultShippingAddress->city : ''?>
					            {!! Form::text('txtcity', $city, array('class' => 'form-control','id'=>'txtcity','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
					        <div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtzipcode">Zip Code <span class="text-danger">*</span></label>
					            <?php $zipCode = isset($defaultShippingAddress->postcode) ? $defaultShippingAddress->postcode : ''?>
					            {!! Form::text('txtzipcode', $zipCode, array('class' => 'form-control','id'=>'txtzipcode','maxlength'=>'6','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
							<div class="col-md-4 mb-3 new-customer-field">
					            <label for="txtgstin">GSTIN</label>
					            <?php 
								$gstin = CustomersHelper::getCustomerAttrValue($customerId, 'gstin');
								$gstin = empty($gstin) ? '' : $gstin;
								?>
					            {!! Form::text('txtgstin', $gstin, array('class' => 'form-control','id'=>'txtgstin','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
					        </div>
					        <div class="col-md-12 mb-3 new-customer-field">
					        	<button class="dt-button buttons-csv buttons-html5 btn btn-primary btn-sm px-3" id="btn-update-shippingaddress">Update</button>
					        </div>
					    </div>
					    {!! Form::close() !!}
  					</div>
  				</div>
  			</div>
  		</div>
      	<div class="row">
  			<div class="col-md-12 widget-holder content-area">
  				<div class="widget-bg">
  					<div class="widget-header">
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-3">Product List</h5>
  					</div>
  					<div class="widget-body clearfix dataTable-length-top-0">
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    {!! Form::open(array('method'=>'POST','id'=>'update-invoice-form','class'=>'form-horizontal','autocomplete'=>'nope')) !!}
	                    {{ Form::hidden('invoice_id', $id, array('id' => 'invoice_id')) }}
	                    {{ Form::hidden('customer_id', $customerId, array('id' => 'customer_id')) }}
	                    {{ Form::hidden('order_id', $orderId, array('id' => 'order_id')) }}

	                    {{ Form::hidden('invoice_total', $invoiceData->invoice_total, array('id' => 'invoice_total')) }}
	                    <table class="table table-striped word-break thumb-sm table-center" id="memoListTable" >
  							<thead>
  								<tr class="bg-primary">
  									<th>Image</th>
  									<th>SKU</th>
  									<th>Cert No.</th>
  									<th>Metal Wt.</th>
  									<th>Dia. Wt.</th>
									<th>Dis. Amt.</th>
									<th>Price</th>
  									<th>F. Price</th>
  									<th>Action</th>
  								</tr>
  							</thead>
  							<tbody>
  								<?php
$imageDirectory = config('constants.dir.website_url_for_product_image');
$defaultProductImage = $imageDirectory . 'def_1.png';
?>

		  						<?php foreach ($invoiceItems as $key => $item): ?>
		  							<tr id="product_id_<?=$item->product_id?>">
		  								<?php
$product_image = $imageDirectory . ShowroomHelper::getProductImage($item->product_id);
$defaultProductImage = $imageDirectory . 'def_1.png';
$product = InventoryHelper::getInvoiceProductData($item->product_id, $item->parent_id);

$metalWeight = isset($product->metal_weight) ? $product->metal_weight : 0;
$stoneWeight = isset($product->stone_weight) ? $product->stone_weight : 0;
$certificateNo = InventoryHelper::getCertificateNo($item->product_id);
$shippingAmount = 0;
if ($key == 0) {
	$shippingAmount = $shippingCharge;
}

$isShippingChargeAdd = ($key == 0) ? 'true' : 'false';

?>
		  								<input type="hidden" class="product_ids" name="product_ids[]" value="<?=$item->product_id;?>">
										{!! Form::hidden('txtShippingCharge[]', $shippingAmount, array('class' => 'form-control h-auto invoice-shipping-charge w-50px mx-auto','class'=>'txtShippingCharge','id'=>'txtShippingCharge_'.$item->product_id)) !!}
		  								<td><img src="{{!empty(ShowroomHelper::getProductImage($item->product_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
		  								<td>
											<?php
$position = strpos($product->sku, ' ');
$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
echo $sku;
?>
										</td>
		  								<td>{{$certificateNo}}</td>
		  								<td>
		  									<?=number_format($metalWeight, 2)?>
											<?php
$metalWeight = number_format($metalWeight, 3);
?>
		  									{!! Form::number('txtmetalweight[]', $metalWeight, array('class' => 'txtmetalweight form-control h-auto w-50px mx-auto','data-id'=>$item->product_id,'data-certificate'=>$certificateNo,'id'=>'txtmetalweight_'.$item->product_id,'autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")','min'=>0,'step'=>0.1)) !!}
		  								</td>
		  								<td>
											<?=number_format($stoneWeight, 2)?>
											<?php
$stoneWeight = number_format($stoneWeight, 3);
?>
		  									{!! Form::number('Xtxtstoneweight[]', $stoneWeight, array('class' => 'form-control h-auto w-50px mx-auto','id'=>'Xtxtstoneweight_'.$item->product_id,'autocomplete'=>'nope','readonly'=>true,'min'=>0,'step'=>0.1)) !!}
		  								</td>
										<td>
		  									<span class="discount-on-label">{{ShowroomHelper::currencyFormat(round($item->discount_amount))}}</span>
		  									<?php $discountAmount = number_format((float) $item->discount_amount, 2, '.', '');
?>
		  									{!! Form::number('txtDiscount[]', round($discountAmount), array('class' => 'form-control txtDiscount h-auto w-50px mx-auto','data-initdiscount'=>round($discountAmount),'data-certificate'=>$certificateNo,'data-id'=>$item->product_id,'id'=>'txtDiscount_'.$item->product_id,'autocomplete'=>'nope','min'=>0,'step'=>0.1)) !!}
		  								</td>
										<td>
		  									<span class="discount-on-label">{{ShowroomHelper::currencyFormat(round($item->price))}}</span>
		  									<?php $unitPrice = number_format((float) $item->price, 2, '.', '');
?>
		  									{!! Form::number('txtUnitPrice[]', round($unitPrice), array('class' => 'form-control txtUnitPrice h-auto w-50px mx-auto','data-certificate'=>$certificateNo,'data-id'=>$item->product_id,'id'=>'txtUnitPrice_'.$item->product_id,'data-addshipping_charge'=>$isShippingChargeAdd,'autocomplete'=>'nope','min'=>0,'step'=>0.1,'readonly'=>true,)) !!}
		  								</td>
		  								<td>

		  									<?php
//$discount = InventoryHelper::getInvoiceItemDiscount($item->product_id,$item->parent_id);
$discount = isset($item->discount_amount) ? $item->discount_amount : 0;
$price = number_format((float) $item->price, 2, '.', '');
$price = ($key == 0) ? ($price + $shippingCharge - $discount) : ($price - $discount);

?>
											<span class="price-on-label">{{ShowroomHelper::currencyFormat(round($price))}}</span>
		  									{!! Form::number('Xtxtprice[]', round($price), array('class' => 'form-control h-auto w-50px mx-auto','data-initprice'=> round($price),'id'=>'Xtxtprice_'.$item->product_id,'data-addshipping_charge'=>$isShippingChargeAdd,'autocomplete'=>'nope','readonly'=>true,'min'=>0)) !!}
											{!! Form::hidden('txtFinalPrice[]', $price, array('class' => 'form-control h-auto w-50px mx-auto','id'=>'txtFinalPrice_'.$item->product_id,'data-addshipping_charge'=>$isShippingChargeAdd,'autocomplete'=>'nope')) !!}
		  								</td>
		  								<td>
		  									<?php if (count($invoiceItems) > 1): ?>
			  									<a title="Remove Product" target="_blank" data-productid="{{$item->product_id}}" class="color-content remove-product table-action-style1 pointer"><i class="list-icon fa fa-trash"></i></a>
		  									<?php else: ?>
		  										-
		  									<?php endif;?>
		  									{!! Form::hidden('txtstoneweight[]', $stoneWeight, array('class' => 'form-control h-auto w-50px mx-auto','class'=>'txtstoneweight','id'=>'txtstoneweight_'.$item->product_id)) !!}
		  									{!! Form::hidden('txtprice[]', $price, array('class' => 'form-control h-auto w-50px mx-auto','class'=>'txtprice','id'=>'txtprice_'.$item->product_id,'autocomplete'=>'nope','readonly'=>true,'min'=>0,'step'=>0.1)) !!}
		  								</td>
		  							</tr>
		  						<?php endforeach;?>
		  					</tbody>
	  					</table>
	  					<div class="row p-3">
	  						<button type="button" class="btn btn-primary ripple" id="btn-update-invoice">Update Invoice</button>
	  						<!-- <button type="button" class="btn btn-primary ripple ml-1" id="btn-generate-memo">Generate Memo</button> -->
	  					</div>
	  					{!! Form::close() !!}
  					</div>
  				</div>
  			</div>
  		</div>
    </div>
  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->

@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$(".selectcountry").change(function(){
		var selectedCountry = $(this).attr('id');
		var stateDivId = $(this).data('statediv');
        var countryId = this.value;
        $.ajax({
            type: 'post',
            url: '<?=URL::to('/inventory/getstatelist');?>',
            data:{country_id:countryId,_token:"{{ csrf_token() }}"},
            beforeSend: function(){
                showLoader();
            },
            success: function(response){
                hideLoader();
                //var response = JSON.parse(response);
				if(response != '')
				{
					var response = JSON.parse(response);
					if(response.status=='success')
					{
						$("#txtstateprovince-error").remove();
						if(response.data!='')
						{
							//$("#txtstateprovince").remove();
							var stateHtml = '<label for="txtstateprovince">State/Province <span class="text-danger">*</span></label><select class="form-control height-35" id="txtstateprovince" name="txtstateprovince">';

							$.each(response.data, function(index, item) {
								console.log(item);
								stateHtml+='<option value='+item.region_id+'>'+item.name+'</option>';
							});
							stateHtml+='</select>';
							console.log(stateDivId);
							$("#"+stateDivId).html(stateHtml);
						}
						else
						{
							console.log(selectedCountry);
							$("#txtstateprovince").remove();
							$("#"+selectedCountry).parent().next().html('<label for="txtstateprovince">State/Province <span class="text-danger">*</span></label><input type="text" class="form-control" id="txtstateprovince" name="txtstateprovince" readonly="true" onfocus="this.removeAttribute(\'readonly\')">');
						}
					}
				}
				
            },
            error: function(){
                hideLoader();
            }
        });
    });
		//Submit billing form
		$("#edit-biling-address-form").submit(function(event){
			event.preventDefault();
			jQuery.validator.addMethod(
			  "regex",
			   function(value, element, regexp) {
				   if (regexp.constructor != RegExp)
					  regexp = new RegExp(regexp);
				   else if (regexp.global)
					  regexp.lastIndex = 0;
					  return this.optional(element) || regexp.test(value);
			   },"erreur expression reguliere"
			);
			$("#edit-biling-address-form").validate({
	            rules: {
	                txtfirstname: "required",
	                txtlastname: "required",
	                txtcontactnumber:{
	                    required: true,
	                    number: true,
	                    maxlength: 13
	                },
					txtgstin:{
						regex : /^([0-9]){2}([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}([0-9]){1}([a-zA-Z]){1}([a-zA-Z0-9]){1}?$/,
					},
	                txtaddress: "required",
	                selectcountry: "required",
	                txtstateprovince: "required",
	                txtcity: "required",
	                txtzipcode:{
	                    required: true,
	                    number: true,
	                    maxlength: 6,
	                    minlength: 6
	                }
	            },
	            messages: {
					txtgstin:{
						regex: 'Invalid GSTIN'
					},
	                txtfirstname: "First name is required",
	                txtlastname: "Last name is required",
	                txtcontactnumber:{
	                    required: "Contact number is required",
	                    number: "Invalid contact number",
	                    maxlength: "Invalid contact number"
	                },
	                txtaddress: "Address is required",
	                selectcountry: "Country is required",
	                txtstateprovince: "State/Province is required",
	                txtcity: "City is required",
	                txtzipcode:{
	                    required: "Zip code is required",
	                    number: "Invalid zip code"
	                }
	            }
	        });
	        if($("#edit-biling-address-form").valid())
	        {
	        	$.ajax({
                    url:'<?=URL::to('inventory/updateinvoiceaddress');?>',
                    method:"post",
                    data:{form_data:$("#edit-biling-address-form").serialize(),_token: "{{ csrf_token() }}"},
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
                });
	        }
		});
		//Submit shipping form
		$("#edit-shipping-address-form").submit(function(event){
			event.preventDefault();
			jQuery.validator.addMethod(
			  "regex",
			   function(value, element, regexp) {
				   if (regexp.constructor != RegExp)
					  regexp = new RegExp(regexp);
				   else if (regexp.global)
					  regexp.lastIndex = 0;
					  return this.optional(element) || regexp.test(value);
			   },"erreur expression reguliere"
			);
			$("#edit-shipping-address-form").validate({
	            rules: {
	                txtfirstname: "required",
	                txtlastname: "required",
	                txtcontactnumber:{
	                    required: true,
	                    number: true,
	                    maxlength: 13
	                },
					txtgstin:{
						regex : /^([0-9]){2}([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}([0-9]){1}([a-zA-Z]){1}([a-zA-Z0-9]){1}?$/,
					},
	                txtaddress: "required",
	                selectcountry: "required",
	                txtstateprovince: "required",
	                txtcity: "required",
	                txtzipcode:{
	                    required: true,
	                    number: true,
	                    maxlength: 6,
	                    minlength: 6
	                }
	            },
	            messages: {
	                txtfirstname: "First name is required",
	                txtlastname: "Last name is required",
	                txtcontactnumber:{
	                    required: "Contact number is required",
	                    number: "Invalid contact number",
	                    maxlength: "Invalid contact number"
	                },
					txtgstin:{
						regex: 'Invalid GSTIN'
					},
	                txtaddress: "Address is required",
	                selectcountry: "Country is required",
	                txtstateprovince: "State/Province is required",
	                txtcity: "City is required",
	                txtzipcode:{
	                    required: "Zip code is required",
	                    number: "Invalid zip code"
	                }
	            }
	        });
	        if($("#edit-shipping-address-form").valid())
	        {
	        	$.ajax({
                    url:'<?=URL::to('inventory/updateinvoiceaddress');?>',
                    method:"post",
                    data:{form_data:$("#edit-shipping-address-form").serialize(),_token: "{{ csrf_token() }}"},
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
                });
	        }
		});
		$(document).on("change","#shippingCharge", function(){
			$shippingCharge = $("#shippingCharge").val();
			$(".invoice-shipping-charge").val($shippingCharge);
			$certificate = $("#memoListTable tbody tr:first-child .txtmetalweight").data('certificate');
			$id = $("#memoListTable tbody tr:first-child .txtmetalweight").data('id');
			$metal_weight = $("#memoListTable tbody tr:first-child .txtmetalweight").val();
			$discount_amount = $("#memoListTable tbody tr:first-child .txtDiscount").val();
			updatePrice($certificate, $metal_weight, $discount_amount, $shippingCharge, $id);
		});
		$('.txtDiscount').change(function(){
			/* $initDiscount = $(this).data('initdiscount');
			$discount = $(this).val();
			$finalDiscount = parseFloat($initDiscount) - parseFloat($discount);
			console.log($finalDiscount);
			$id = $(this).data('id');
			$price = $("#Xtxtprice_"+$id).data('initprice');
			$finalPrice = 0;
			if($finalDiscount < 0)
			{
				$finalPrice = parseFloat($price) - Math.abs($finalDiscount);
			}
			else
			{
				$finalPrice = parseFloat($price) + Math.abs($finalDiscount);
			}
			console.log($finalPrice);
			$("#Xtxtprice_"+$id).val($finalPrice);
			$("#txtFinalPrice_"+$id).val($finalPrice); */

			$certificate = $(this).data('certificate');
			$id = $(this).data('id');
			$metal_weight = $("#txtmetalweight_"+$id).val();
			$discount_amount = $("#txtDiscount_"+$id).val();
			$shippingCharge = $("#shippingCharge").val();
			updatePrice($certificate, $metal_weight, $discount_amount, $shippingCharge, $id);
		});

		$('.txtmetalweight').change(function(){
			$certificate = $(this).data('certificate');
			$id = $(this).data('id');
			$metal_weight = $(this).val();
			$discount_amount = $("#txtDiscount_"+$id).val();
			$shippingCharge = $("#txtShippingCharge_"+$id).val();
			updatePrice($certificate, $metal_weight, $discount_amount, $shippingCharge, $id);
		});

		$(".remove-product").click(function(event){
			event.preventDefault();
			var productId = $(this).data("productid");
			var invoiceId = $("#invoice_id").val();
			var orderId = $("#order_id").val();
			swal({
              title: 'Are you sure?',
              text: "<?php echo Config::get('constants.message.invoice_remove_memo_product_confirmation'); ?>",
              type: 'info',
              showCancelButton: true,
              confirmButtonText: 'Confirm',
              confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
              }).then(function(data) {
              	if (data) {
              		 $.ajax({
	                type: 'post',
	                url: '<?=URL::to('/inventory/removeinvoiceproduct');?>',
	                data: {product_id: productId,order_id:orderId,invoice_id:invoiceId,_token: "{{ csrf_token() }}"},
	                beforeSend: function(){
	                    showLoader();
	                },
	                success: function(response){
	                	hideLoader();
	                    var res = JSON.parse(response);
	                    if(res.status)
	                    {
	                    	$("#product_id_"+productId).remove();
	                    	swal({
		                      title: 'Success',
		                      text: res.message,
		                      type: 'success',
		                      buttonClass: 'btn btn-primary'
		                    });
	                    }
	                }
	            });
              	}
                 
            });
		});
		$("#btn-update-invoice").click(function(){
			$.ajax({
				type: 'post',
				data: $("#update-invoice-form").serialize(),
				url: '<?=URL::to('/inventory/updateinvoice');?>',
				beforeSend: function(){
					showLoader();
				},
				success: function(res){
					hideLoader();
					var response = JSON.parse(res);
					if(response.status)
					{
						swal({
                          title: 'Success',
                          text: response.message,
                          type: 'success',
                          buttonClass: 'btn btn-primary'
                          //showSuccessButton: true,
                          //showConfirmButton: false,
                          //successButtonClass: 'btn btn-primary',
                          //successButtonText: 'Ok'
                        }).then(function(data) {
                        	if (data.value) {
                        	window.location.href = '<?=URL::to('/inventory/invoicelist');?>';
                        	}
                            
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
		});
	var memoListTable = $('#memoListTable').DataTable({
		"dom":  '<"datatable_top_custom_lengthinfo" <"#inventory-toolbar">>frtip',
	});
	$divContainer = $('<div class="inventory-action-container"/>').appendTo('#inventory-toolbar');
	$shippingLable = $('<label>Shipping Charge:</label>').appendTo($divContainer);
	$shippingChange = $('<input type="number" name="shippingChange" class="mx-2 mr-3 height-35 padding-four" id="shippingCharge" data-initshippingcharge="<?php echo round($shippingCharge) ?>" data-shippingcharge="<?php echo round($shippingCharge) ?>" value="<?php echo round($shippingCharge) ?>"/>').appendTo($divContainer);
	$('.dataTables_filter input')
	  .unbind() // Unbind previous default bindings
	  .bind("input", function(e) { // Bind our desired behavior
	      // If the length is 3 or more characters, or the user pressed ENTER, search
	      if(this.value.length >= 3 || e.keyCode == 13) {
	          // Call the API search function
	          memoListTable.search(this.value).draw();
	      }
	      // Ensure we clear the search if they backspace far enough
	      if(this.value == "") {
	          memoListTable.search("").draw();
	      }
	      return;
	});
	$("#memoListTable tr th").removeClass('sorting_asc');
});
function updatePrice(certificate, metalWeight, discount, shippingCharge, productId)
{
	$.ajax({
        type: 'post',
        url: '<?=URL::to('/inventory/updateinvoiceprice');?>',
        data: {certificate: certificate, metal_weight: metalWeight, discount_amount:discount, shipping_charge: shippingCharge, customer_id: $("#customer_id").val(),_token: "{{ csrf_token() }}"},
        beforeSend: function(){
              showLoader();
        },
        success: function(response){
				hideLoader();
				var res = JSON.parse(response);
				//console.log(res);
				if(res.status)
				{
					$totItems = $('.txtmetalweight').length;
					var finalAmount = parseFloat(res.total_price).toFixed(0);
					var unitPrice = parseFloat(res.unit_price).toFixed(0);

					var formatter = new Intl.NumberFormat('en-IN', {
						style: 'currency',
						currency: 'INR',
					});
					//console.log(finalAmount);
					//console.log(unitPrice);
					if($("#txtFinalPrice_"+productId).data('addshipping_charge') == false)
					{
						$("#txtFinalPrice_"+productId).val(finalAmount - parseFloat($("#shippingCharge").val()));
					}
					else
					{
						$("#txtFinalPrice_"+productId).val(finalAmount);
					}
					
					if($("#Xtxtprice_"+productId).data('addshipping_charge') == false)
					{
						console.log(finalAmount);
						console.log(parseFloat($("#shippingCharge").val()));
						console.log(finalAmount - parseFloat($("#shippingCharge").val()));
						$("#Xtxtprice_"+productId).val(finalAmount - parseFloat($("#shippingCharge").val()));
					}
					else
					{
						$("#Xtxtprice_"+productId).val(finalAmount);
					}
					if($("#txtUnitPrice_"+productId).data('addshipping_charge') == false)
					{
						$("#txtUnitPrice_"+productId).val(unitPrice - parseFloat($("#shippingCharge").val()));
					}
					else
					{
						$("#txtUnitPrice_"+productId).val(unitPrice);
					}
				}
            }
    });
}
</script>
<style>
.form-control:disabled, .form-control[readonly] {background-color: #fff;}.h-15{height:15px;}.w-80{width: 80%;}
.dataTables_wrapper table.dataTable th, .dataTables_wrapper table.dataTable td{padding:7px;}
.w-50px{width:50px;}
</style>
@endsection