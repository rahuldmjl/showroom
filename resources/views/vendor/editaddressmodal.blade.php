<div class="modal-header text-inverse">
    <button type="button" class="close p-0 m-0" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 class="modal-title" id="myLargeModalLabel">
    	<?php echo 'Address Information'; ?>
    </h5>
</div>
<div class="modal-body">
	{{ Form::hidden('vendor_id', $vendorId, array('id' => 'vendor_id')) }}
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
            <label for="address">Address <span class="text-danger">*</span></label>
			{!! Form::textarea('address', $address , array('class' => 'form-control required','id'=>'address','rows' => 2, 'cols' => 40,'autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="state">State <span class="text-danger">*</span></label>
            {!! Form::text('state', $state, array('class' => 'form-control','id'=>'state','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
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
                address: "required",
                state: "required"
            },
            messages: {
                address: "Address is required",
                state: "state is required"
            }
        });
        if($("#edit-customer-form").valid())
        {
        	var vendorEditForm=$("#edit-customer-form");
        	$.ajax({
                type: 'post',
                url: '<?=URL::to('/vendor/updatevendoraddress');?>',
                data: vendorEditForm.serialize(),
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
					          url:'<?=URL::to('/vendor/refreshdefaultaddress');?>',
					          method:"post",
					          data:{vendor_id: '<?= $vendorId?>',_token: "{{ csrf_token() }}"},
					          beforeSend: function(){
					            showLoader();
					          },
					          success: function(response){
					              hideLoader();
					              if(response != '')
					              {
						            $(".vendor-address-container").html(response);
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
	                      text: response.message,
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
});
</script>