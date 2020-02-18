<?php
use App\Helpers\ShowroomHelper;
use App\Helpers\InventoryHelper;
$productIds = array();
$customerEmail = '';
if(!empty($id))
{
	$customerId = isset($memoData->customer_id) ? $memoData->customer_id : '';
	DB::setTablePrefix('');
	$customer = DB::table('customer_entity')->select('email')->where('entity_id','=',DB::raw("$customerId"))->get()->first();
	$customerEmail = isset($customer->email) ? $customer->email : '';
	$customerBillingAddress = InventoryHelper::getDefaultBillingAddressByCustId($customerId);
	$customerShippingAddress = InventoryHelper::getDefaultShippingAddresByCustId($customerId);
	//$firstName = isset($order->customer_firstname) ? $order->customer_firstname : '';
	//$lastName = isset($order->customer_lastname) ? $order->customer_lastname : '';
	$customerName = InventoryHelper::getCustomerName($customerId);
	$telephone = isset($customerShippingAddress['telephone']) ? $customerShippingAddress['telephone'] : $customerBillingAddress['telephone'];
	$street = isset($customerShippingAddress['street']) ? $customerShippingAddress['street'] : $customerBillingAddress['street'];
	$countryCode = isset($customerShippingAddress['country_id']) ? $customerShippingAddress['country_id'] : $customerBillingAddress['country_id'];
	$region = isset($customerShippingAddress['region']) ? $customerShippingAddress['region'] : $customerBillingAddress['region'];
	$city = isset($customerShippingAddress['city']) ? $customerShippingAddress['city'] : $customerBillingAddress['city'];
	$postcode = isset($customerShippingAddress['postcode']) ? $customerShippingAddress['postcode'] : $customerBillingAddress['postcode'];
	$email = isset($customerShippingAddress['email']) ? $customerShippingAddress['email'] : $customerBillingAddress['email'];
	
    
	$franchiseId = isset($memoData->franchisee_id) ? $memoData->franchisee_id : '';
	$agentName = isset($memoData->agent_name) ? $memoData->agent_name : '';
    
	$productIds = explode(',',$memoData->product_ids);

	$currentYear = date('y');
	$approvalNumber = $currentYear.'-'.($currentYear+1).'/'.$memoData->approval_no;
}
$productIds = implode("','", $productIds);
DB::setTablePrefix('');
$productCollection = DB::table("catalog_product_flat_1")->select("*")->whereIn('entity_id', [DB::raw("'" . $productIds . "'")])->get();
$countryList = $countryList->data;
?>
@extends('layout.mainlayout')

@section('title', 'Edit Approval Memo')

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
  	{{ Breadcrumbs::render('inventory.editmemo', $id) }}
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
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-4 w-100">Product List</h5>
  					</div>
  					<div class="widget-body clearfix dataTable-length-top-0">
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    <table class="table table-striped table-center" id="memoListTable" >
  							<thead>
  								<tr class="bg-primary">
  									<th>Image</th>
  									<th>SKU</th>
  									<th>Certificate Number</th>
  									<th>Price</th>
  									<th>Action</th>
  								</tr>
  							</thead>
  							<tbody>
  								<?php 
  								$imageDirectory = config('constants.dir.website_url_for_product_image');
  								$defaultProductImage = $imageDirectory . 'def_1.png';
  								?>
		  						<?php foreach ($productCollection as $product):?>
		  							<tr id="product_id_<?= $product->entity_id?>">
		  								<?php 
		  								$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
		  								$defaultProductImage = $imageDirectory . 'def_1.png';
		  								?>
		  								<input type="hidden" class="product_ids" name="product_ids[]" value="<?= $product->entity_id;?>">
		  								<td><img src="{{!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
		  								<td>{{$product->sku}}</td>
		  								<td>{{$product->certificate_no}}</td>
		  								<td>{{ShowroomHelper::currencyFormat(round($product->custom_price))}}</td>
		  								<td>
		  									<?php if(count($productCollection) > 1):?>
			  									<a title="Remove Product" target="_blank" data-productid="{{$product->entity_id}}" class="color-content remove-product table-action-style1 pointer"><i class="list-icon fa fa-trash"></i></a>
		  									<?php else:?>
		  										-
		  									<?php endif;?>
		  								</td>
		  							</tr>
		  						<?php endforeach;?>
		  					</tbody>
	  					</table>
	  					<div class="row">
	  						<div class="col-md-12">
	  						 <button type="button" class="btn btn-primary ripple" id="btn-generate-invoice">Generate Invoice</button>
	  					    </div>
	  						<!-- <button type="button" class="btn btn-primary ripple ml-1" id="btn-generate-memo">Generate Memo</button> -->
	  					</div>
	  					<div class="row m-0">
	  						{!! Form::open(array('method'=>'POST','id'=>'invoicememo-regenerate-form','class'=>'form-horizontal w-100')) !!}
	  						{{ Form::hidden('operation_type', '', array('id' => 'operation_type')) }}
	  						{{ Form::hidden('order_id', $id, array('id' => 'order_id')) }}
	  						{{ Form::hidden('franchisee_name', '', array('id' => 'franchisee_name')) }}
	  						{{ Form::hidden('approval_no', $approvalNumber, array('id' => 'order_id')) }}
	  						
	  						<div class="form-group row customer-type-row hidden">
								<label class="col-form-label">Customer Type</label>
								<div class="radiobox radio-info">
						            <label>
						                 {{ Form::radio('customerType', 'new' ,false) }}
						                 <span class="label-text">New</span>
						            </label>
						        
						            <label>
						                {{ Form::radio('customerType', 'existing' ,false) }} <span class="label-text">Existing</span>
						            </label>
						        </div>
							</div>
							<section id="customer-form-container" class="hidden">
						    	<h5 class="border-b-light-1 pb-2 mt-4 mb-4 w-100">Customer Add</h5>
						    	<div class="row mr-b-5">
						            <div class="col-md-4 mb-3 new-customer-field hidden">
						                <label for="txtfirstname">First Name <span class="text-danger">*</span></label>
						                {!! Form::text('txtfirstname', '', array('class' => 'form-control required','id'=>'txtfirstname')) !!}
						            </div>
						            <div class="col-md-4 mb-3 new-customer-field hidden">
						                <label for="txtlastname">Last Name <span class="text-danger">*</span></label>
						                {!! Form::text('txtlastname', '', array('class' => 'form-control','id'=>'txtlastname')) !!}
						            </div>
						            <div class="col-md-4 mb-3 new-customer-field hidden">
						                <label for="txtcontactnumber">Contact Number <span class="text-danger">*</span></label>
						                {!! Form::text('txtcontactnumber', '', array('class' => 'form-control','id'=>'txtcontactnumber')) !!}
						            </div>
						            <div class="col-md-4 mb-3 new-customer-field hidden">
						                <label for="txtaddress">Address <span class="text-danger">*</span></label>
						                {!! Form::text('txtaddress', '', array('class' => 'form-control','id'=>'txtaddress')) !!}
						            </div>
						            <div class="col-md-4 mb-3 new-customer-field hidden">
						            	<label for="selectcountry">Country <span class="text-danger">*</span></label>
						                <select class="form-control height-35" name="selectcountry" id="selectcountry" data-placeholder="Select">
						                     <?php foreach($countryList as $value):?>
						                        <option value="<?php echo $value->country_id;?>"><?php echo $value->name?></option>
						                     <?php endforeach;?>
						                </select>
						            </div>
						            <div class="col-md-4 mb-3 new-customer-field hidden customer-state">
						                <label for="txtstateprovince">State/Province <span class="text-danger">*</span></label>
						                {!! Form::text('txtstateprovince', '', array('class' => 'form-control','id'=>'txtstateprovince')) !!}
						            </div>
						            <div class="col-md-4 mb-3 new-customer-field hidden">
						                <label for="txtcity">City <span class="text-danger">*</span></label>
						                {!! Form::text('txtcity', $city, array('class' => 'form-control','id'=>'txtcity')) !!}
						            </div>
						            <div class="col-md-4 mb-3 new-customer-field hidden">
						                <label for="txtzipcode">Zip Code <span class="text-danger">*</span></label>
						                {!! Form::text('txtzipcode', '', array('class' => 'form-control','id'=>'txtzipcode','maxlength'=>'6')) !!}
						            </div>
						            <div class="col-md-4 mb-3 new-customer-field hidden">
						                <label for="txtemail">Email <span class="text-danger">*</span></label>
						                {!! Form::email('txtemail', '', array('class' => 'form-control','id'=>'txtemail')) !!}
						            </div>
						            <div class="col-md-4 mb-3 email-field hidden">
						                <label for="txtdmusercodeemail">Email <span class="text-danger">*</span></label>
						                {!! Form::text('txtdmusercodeemail', $email, array('class' => 'form-control','id'=>'txtdmusercodeemail','readonly')) !!}
						                <input type="hidden" name="customerId" value="<?= $customerId;?>">
						            </div>
						            <div class="col-md-4 mb-3 invoice-field hidden">
						            	<label for="paymentmode">Payment Mode <span class="text-danger">*</span></label>
						                {!! Form::select('paymentmode',array(''=>'Select','cash'=>'Cash','check'=>'Check'),'', array('class' => 'form-control height-35')) !!}
						            </div>
						            <div class="col-md-4 mb-3 invoice-field">
						                <label for="discount">Discount <span class="text-danger">*</span></label>
						                {!! Form::select('discount_type',array(''=>'Select','percent'=>'%','amount'=>'Amount'),'', array('class' => 'form-control height-35','id'=>'discount_type')) !!}
						            </div>
						            <div class="col-md-4 mb-3 discount-value hidden">
						                <label for="txtdiscountval"><span class="discount-val-label"></span> <span class="text-danger">*</span></label>
						                {!! Form::text('txtdiscountval', '', array('class' => 'form-control','id'=>'txtdiscountval')) !!}
						            </div>
						        </div>
						        <h5 class="border-b-light-1 pb-2 mb-4 w-100">Commission</h5>
						        <div class="row mr-b-20 invoiceqr-commission-field hidden">
						            <div class="col-md-4 mb-3 input-has-value ">
						                <label for="txtfranchisename">Franchise Name</label>
						                <select class="form-control height-35" name="franchisee" id="franchisee" data-toggle="select2">
						                	<option value="">Select</option>
						                     <?php foreach($franchiseeData as $key=>$value):?>
						                        <option value="<?php echo $value['entity_id'];?>" data-name="<?php echo $value['name']?>" <?= ($franchiseId==$value['entity_id']) ? 'selected' : '' ?>><?php echo $value['name']?></option>
						                     <?php endforeach;?>
						                </select>
						            </div>
						            <div class="col-md-4 mb-3 input-has-value invoice-commission-field">
						                <label for="txtfranchisecommission">Franchise Commission(%)</label>
						                {!! Form::text('txtfranchisecommission', '', array('class' => 'form-control','id'=>'txtfranchisecommission')) !!}
						            </div>
						            <div class="col-md-4 mb-3 input-has-value">
						                <label for="txtagentname">Agent Name</label>
						                {!! Form::text('txtagentname', $agentName, array('class' => 'form-control','id'=>'txtagentname')) !!}
						            </div>
						            <div class="col-md-4 mb-3 input-has-value invoice-field">
						                <label for="txtagentcommission">Agent Commission(%)</label>
						                {!! Form::text('txtagentcommission', '', array('class' => 'form-control','id'=>'txtagentcommission')) !!}
						            </div>
						        </div>
						        <div class="row">
						        	<div class="col-12">
						        	 <button type="button" class="btn btn-primary" id="btn-submit-memo">Submit</button>
						            </div>
						        </div> 
						    </section>
				            {!! Form::close() !!}
	  					</div>
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
<script src="https://cdn.jsdelivr.net/jquery.validation/1.15.1/jquery.validate.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#discount_type").change(function(){
	        $(".discount-value").removeClass('hidden');
	        if(this.value=='percent')
	        {
	            $('.discount-val-label').html('%');
	        }
	        else if(this.value=='amount')
	        {
	            $('.discount-val-label').html('Amount');
	        }
	        else
	        {
	            $(".discount-value").addClass('hidden');
	        }
	    });
		var selectfranchisee = $('#franchisee').select2();
	    selectfranchisee.on("select2:select", function (e) {
	        var selected_element = $(e.currentTarget);
	        var select_val = selected_element.val();
	        $("#franchisee_name").val($("#franchisee").select2().find(":selected").data("name"));
	    });
		$("#btn-submit-memo").click(function(){
			$("#invoicememo-regenerate-form").validate({
		        rules: {
		            txtfirstname: "required",
		            txtlastname: "required",
		            txtcontactnumber:{
		            	required: true,
		            	number: true,
		            	maxlength: 13
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
		            },
		            txtemail: {
		            	required: true,
		            	email: true
		            },
		            txtdmusercodeemail: "required",
		            paymentmode: "required",
		            discount_type: "required"
		        },
		        messages: {
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
		            },
		            txtemail:{
		            	required: "Email is required",
		            	email: "Invalid email"
		            },
		            txtdmusercodeemail : "DMUSERCODE/Email is required",
		            paymentmode: "Payment mode is required",
		            discount_type: "Discount type is required"
		        }
		    });
		    if($("#invoicememo-regenerate-form").valid())
		    {
		    	var url = '<?=URL::to('/inventory/generateinvoicememo');?>';
				var invoiceMemoForm=$("#invoicememo-regenerate-form");
				
				var productIds = new Array();
			    $.each(jQuery(".product_ids"), function() {
			        productIds.push($(this).val());
			    });
			    productIds = productIds.join(',');
				var formData = invoiceMemoForm.serialize();
				
				$.ajax({
	                type: 'post',
	                url: url,
	                data: formData + "&product_ids=" + productIds + "&customerType=existing&operation_type=invoice",
	                beforeSend: function(){
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
			                }).then(function() {
		                        window.location.href = '<?=URL::to('/inventory/invoicelist');?>';
		                    });
	                    }
	                    else
	                    {
	                    	swal({
			                    title: 'Oops!',
			                    text: res.message,
			                    type: 'error',
			                    buttonClass: 'btn btn-primary'
			                });
	                    }
	                }
	            });
		    }
			
		});
		$(".remove-product").click(function(event){
			event.preventDefault();
			var productId = $(this).data("productid");
			swal({
              title: 'Are you sure?',
              text: "<?php echo Config::get('constants.message.invoice_remove_memo_product_confirmation'); ?>",
              type: 'info',
              showCancelButton: true,
              confirmButtonText: 'Confirm',
              confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
              }).then(function() {
                  $.ajax({
	                type: 'post',
	                url: '<?=URL::to('/inventory/removememoproduct');?>',
	                data: {productId: productId,orderId:$("#order_id").val(),_token: "{{ csrf_token() }}"},
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
            });
		});
		$(document).on('change','input[name=customerType]',function(){
		    if($('input[name=customerType]:checked').val() == 'new')
		    {
		    	$("#customer-form-container").removeClass("hidden");
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
		    else if($('input[name=customerType]:checked').val()=='existing')
		    {
		    	$("#customer-form-container").removeClass("hidden");
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
		$("#btn-generate-invoice").click(function(){
			//$(".customer-type-row").removeClass("hidden");
			$("#operation_type").val('invoice');
			//$('input[name=customerType]').trigger('change');
			$("#customer-form-container").removeClass("hidden");
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
		});
		$("#btn-generate-memo").click(function(){
			$(".customer-type-row").removeClass("hidden");
			$("#operation_type").val('memo');
			$('input[name=customerType]').trigger('change');
		});
		$("#generatereturnmemo").click(function(event){
			event.stopPropagation();
			var orderId = $(this).data("id");
			if(orderId != '')
			{
				swal({
	              title: 'Are you sure?',
	              text: "<?php echo Config::get('constants.message.inventory_generate_returnmemo_confirmation');?>",
	              type: 'info',
	              showCancelButton: true,
	              confirmButtonText: 'Confirm',
	              confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
	              }).then(function() {
	                  $.ajax({
			              url:'<?=URL::to('inventory/getproductidsbyorder');?>',
			              method:"post",
			              data:{orderId: orderId,_token: "{{ csrf_token() }}"},
			              beforeSend: function()
			              {
			                showLoader();
			              },
			              success: function(response){
			                  hideLoader();
			                  var res = JSON.parse(response);
			                  $.ajax({
					              url:'<?=URL::to('/inventory/generatereturnmemo');?>',
					              method:"post",
					              data:{productIds: res.product_ids,_token: "{{ csrf_token() }}"},
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
					                      $("#order_id_"+orderId).remove();
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
			}
		});
		$("#downloadexcel").click(function(event){
			event.stopPropagation();
			var orderId = $(this).data("id");
			window.location.href = "<?=URL::to('inventory/downloadexcel');?>/"+orderId;
		});
	var memoListTable = $('#memoListTable').DataTable({});
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
</script>
<style>
.product-img{max-width: 50px;}
</style>
@endsection