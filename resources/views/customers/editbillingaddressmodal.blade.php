<?php
use App\Helpers\CustomersHelper;
?>
<div class="modal-header text-inverse">
    <button type="button" class="close p-0 m-0" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 class="modal-title" id="myLargeModalLabel">
    	<?php
    	if($editType == 'billing_address')
    	{
    		echo 'Edit Billing Address';
    	}
    	else if($editType == 'shipping_address')
    	{
    		echo 'Edit Shipping Address';
    	}
    	else
    	{
    		echo 'Personal Information';
    	}
    	?>
    </h5>
</div>
<div class="modal-body">
	{{ Form::hidden('customer_id', $customerId, array('id' => 'customer_id')) }}
	{{ Form::hidden('address_id', $defaultBillingAddress['entity_id'], array('id' => 'address_id')) }}
	{!! Form::token() !!}
	<div class="row">
        <div class="alert alert-icon alert-danger border-danger fade customer-verification-alert hidden w-100" role="alert">
            <!-- <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
            </button> --> 
            <i class="material-icons list-icon">not_interested</i>  
            <span class="customer-check-message"></span>
        </div>
        <div class="alert alert-icon alert-success border-success fade invoice-success-message hidden w-100" role="alert">
            <!-- <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button> -->
            <i class="material-icons list-icon">check_circle</i>
            <strong>Well done! </strong><span class="imvoice-message"></span>
        </div>
    </div>
    <div class="row mr-b-10">
    	<div class="col-md-4 mb-3 new-customer-field">
            <label for="txtfirstname">First Name <span class="text-danger">*</span></label>
            <?php $firstName = isset($defaultBillingAddress['firstname']) ? $defaultBillingAddress['firstname'] : '' ?>
            {!! Form::text('txtfirstname', $firstName , array('class' => 'form-control required','id'=>'txtfirstname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtlastname">Last Name <span class="text-danger">*</span></label>
            <?php $lastName = isset($defaultBillingAddress['lastname']) ? $defaultBillingAddress['lastname'] : '' ?>
            {!! Form::text('txtlastname', $lastName, array('class' => 'form-control','id'=>'txtlastname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtcontactnumber">Contact Number <span class="text-danger">*</span></label>
            <?php $telephone = isset($defaultBillingAddress['telephone']) ? $defaultBillingAddress['telephone'] : '' ?>
            {!! Form::text('txtcontactnumber', $telephone, array('class' => 'form-control','id'=>'txtcontactnumber','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtaddress">Address <span class="text-danger">*</span></label>
            <?php $street = isset($defaultBillingAddress['street']) ? $defaultBillingAddress['street'] : '' ?>
            {!! Form::text('txtaddress', $street, array('class' => 'form-control','id'=>'txtaddress','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
        	<label for="selectcountry">Country <span class="text-danger">*</span></label>
            <select class="form-control" name="selectcountry" id="selectcountry" data-placeholder="Select">
                <?php foreach($countryList as $value):?>
                    <option value="<?php echo $value['country_id'];?>" <?= ($defaultBillingAddress['country_id'] == $value['country_id']) ? 'selected' : ''?>><?php echo $value['name']?></option>
                <?php endforeach;?>
            </select>
        </div>
        <div class="col-md-4 mb-3 new-customer-field customer-state">
            <label for="txtstateprovince">State/Province <span class="text-danger">*</span></label>
            <?php
            $stateList = CustomersHelper::getStateList($defaultBillingAddress['country_id']);
            ?>
            <?php if(count($stateList) > 0):?>
				<select class="form-control" id="txtstateprovince" name="txtstateprovince">
					<?php foreach($stateList as $state):?>
						<option value="<?= $state->region_id?>" <?= ($defaultBillingAddress['region'] == $state->name) ? 'selected' : ''?>><?= $state->name?></option>
					<?php endforeach;?>
				</select>            	
            <?php else:?>
            	{!! Form::text('txtstateprovince', $defaultBillingAddress['region'], array('class' => 'form-control','id'=>'txtstateprovince','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            <?php endif;?>
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtcity">City <span class="text-danger">*</span></label>
            <?php $city = isset($defaultBillingAddress['city']) ? $defaultBillingAddress['city'] : '' ?>
            {!! Form::text('txtcity', $city, array('class' => 'form-control','id'=>'txtcity','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtzipcode">Zip Code <span class="text-danger">*</span></label>
            <?php $zipCode = isset($defaultBillingAddress['postcode']) ? $defaultBillingAddress['postcode'] : '' ?>
            {!! Form::text('txtzipcode', $zipCode, array('class' => 'form-control','id'=>'txtzipcode','maxlength'=>'6','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" id="btn-update-billingaddress" class="btn btn-info ripple text-left">Submit</button> 
    <button type="button" class="btn btn-danger ripple text-left" data-dismiss="modal">Close</button>
</div>
<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<style type="text/css">.form-control:disabled, .form-control[readonly] {background-color: #fff;}</style>
<script>
$(document).ready(function(){
	$("#btn-update-billingaddress").click(function(){
		$("#edit-customer-form").validate({
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
        if($("#edit-customer-form").valid())
        {
        	var customerEditForm=$("#edit-customer-form");
        	$.ajax({
                type: 'post',
                url: '<?=URL::to('/customers/updatecustomeraddress');?>',
                data: customerEditForm.serialize()+ "&edit_type=" + '<?= $editType?>'+ "&address_id="+ $("#address_id").val(),
                beforeSend: function(){
                    $(this).prop("disabled",true);
                    showLoader();
                },
                success: function(response){
                	hideLoader();
                	var res = JSON.parse(response);
                	if(res.status)
                	{
                		$("#edit-customer-modal").modal('hide');
                		$.ajax({
					          url:'<?=URL::to('/customers/refreshdefaultaddress');?>',
					          method:"post",
					          data:{customer_id: '<?= $customerId?>',edit_type: '<?= $editType?>',_token: "{{ csrf_token() }}"},
					          beforeSend: function(){
					            showLoader();
					          },
					          success: function(response){
					              hideLoader();
					              var addressType = '<?= $editType?>';
					              if(response != '')
					              {
						            	if(addressType == 'billing_address')
						              	{
						              		$(".billing-address-container").html(response);
						              	}
						              	else
						              	{
						              		$(".shipping-address-container").html(response);
						              	}
					              }
					          }
					      })
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
            });
        }
	});
	$("#selectcountry").change(function(){
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
                var response = JSON.parse(response);
                if(response.status!='success')
                {
                    swal({
                      title: 'Oops!',
                      text: response.message,
                      type: 'error',
                      showCancelButton: true,
                      showConfirmButton: false,
                      confirmButtonClass: 'btn btn-danger',
                      cancelButtonText: 'Ok'
                    });
                }  
                else
                {
                    $("#txtstateprovince-error").remove();
                    if(response.data!='')
                    {
                        $("#txtstateprovince").remove();
                        var stateHtml = '<select class="form-control" id="txtstateprovince" name="txtstateprovince">';

                        $.each(response.data, function(index, item) {
                            console.log(item);
                            stateHtml+='<option value='+item.region_id+'>'+item.name+'</option>';
                        });
                        stateHtml+='</select>';
                        $(".customer-state").append(stateHtml);
                    }
                    else
                    {
                        $("#txtstateprovince").remove();
                        $(".customer-state").append('<input type="text" class="form-control" id="txtstateprovince" name="txtstateprovince" readonly="true" onfocus="this.removeAttribute(\'readonly\')">');
                    }
                }
            },
            error: function(){
                hideLoader();
                $("#btn-verify-customer").prop('disabled',false);
            }
        });
	});
});
</script>