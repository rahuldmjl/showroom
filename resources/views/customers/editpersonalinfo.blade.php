<div class="modal-header text-inverse">
    <button type="button" class="close p-0 m-0" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 class="modal-title" id="myLargeModalLabel">
    	Edit Personal Information
    </h5>
</div>
{!! Form::open(array('method'=>'POST','id'=>'edit-personalinfo-form','class'=>'form-horizontal','autocomplete'=>'nope','enctype'=>'multipart/form-data')) !!}
{{ Form::hidden('customer_id', $customerId, array('id' => 'customer_id')) }}
<div class="modal-body">
    <div class="row mr-b-10">
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtfirstname">First Name <span class="text-danger">*</span></label>
            {!! Form::text('txtfirstname', $firstName , array('class' => 'form-control required','id'=>'txtfirstname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtlastname">Last Name <span class="text-danger">*</span></label>
            {!! Form::text('txtlastname', $lastName, array('class' => 'form-control','id'=>'txtlastname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="email">Email <span class="text-danger">*</span></label>
            {!! Form::email('txtemail', $email, array('disabled'=>'true','class' => 'form-control','id'=>'txtemail','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtprimarycontact">Primary Contact <span class="text-danger">*</span></label>
            {!! Form::text('txtprimarycontact', $primaryContact, array('class' => 'form-control','id'=>'txtprimarycontact','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtsecondarycontact">Secondary Contact</label>
            {!! Form::text('txtsecondarycontact', $secondaryContact, array('class' => 'form-control','id'=>'txtsecondarycontact','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtlocation">Location <span class="text-danger">*</span></label>
            {!! Form::text('txtlocation', $location, array('class' => 'form-control','id'=>'txtlocation','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
		<div class="col-md-4 mb-3">
			<label class="">Is Franchise? </label>
			<div class="radiobox radio-info">
				<label class="mr-2">
					 <input name="radioIsFranchise" type="radio" value="yes" checked="checked">
					 <span class="label-text">Yes</span>
				</label>
				<label>
					<input name="radioIsFranchise" type="radio" value="no"> <span class="label-text">No</span>
				</label>
			</div>
		</div>
		<div class="col-md-4 mb-3 new-customer-field txtfrncode-input-div">
            <label for="txtfrncode" id="frncode_label">FRN Code <span class="text-danger">*</span></label>
            {!! Form::text('txtfrncode', $frnCode, array('class' => 'form-control','id'=>'txtfrncode','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" id="btn-update-personalinfo" class="btn btn-info ripple text-left">Submit</button> 
    <button type="button" class="btn btn-danger ripple text-left" data-dismiss="modal">Close</button>
</div>
{!! Form::close() !!}
<style type="text/css">.form-control:disabled, .form-control[readonly] {background-color: #fff;}</style>
<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<script>
$(document).ready(function(){
	$(document).on('change','input[name=radioIsFranchise]',function(){
			if(this.value == 'yes')
			{
				$('#frncode_label').html('FRN Code <span class="text-danger">*</span>');
				$('#txtfrncode').attr('required', true);
			}
			else
			{
				$('#frncode_label').html('FRN Code');
				$('#txtfrncode').attr('required', false);
			}
		});
    $("#btn-update-personalinfo").click(function(){
        $("#edit-personalinfo-form").validate({
            rules: {
                txtfirstname: "required",
                txtlastname: "required",
                txtprimarycontact:{
                    required: true,
                    number: true,
                    maxlength: 13
                },
                txtlocation: "required",
                txtsecondarycontact:{
                    required: false,
                    number: true,
                    maxlength: 13
                },
                txtemail: {
                    required: true,
                    email: true
                },
				txtfrncode: {
					required: function(element){
							return $("input[name=radioIsFranchise]:checked").val()=="yes";
					}
				}
            },
            messages: {
                txtfirstname: "First name is required",
                txtlastname: "Last name is required",
                txtprimarycontact:{
                    required: "Primary Contact is required",
                    number: "Invalid primary contact",
                    maxlength: "Invalid primary contact"
                },
                txtsecondarycontact:{
                    required: "Secondary Contact is required",
                    number: "Invalid secondary contact",
                    maxlength: "Invalid secondary contact"
                },
                txtlocation: "Location is required",
                txtemail:{
                    required: "Email is required",
                    email: "Invalid email"
                },
				txtfrncode: "FRN Code is required"
            }
        });
        if($("#edit-personalinfo-form").valid())
        {
            $.ajax({
                type: 'post',
                url: '<?=URL::to('/customers/updatepersonalinfo');?>',
                data: $("#edit-personalinfo-form").serialize(),
                beforeSend: function(){
                    $(this).prop("disabled",true);
                    showLoader();
                },
                success: function(response){
                    hideLoader();
                    var res = JSON.parse(response);
                    if(res.status)
                    {
                        $("#view-attachment-modal").modal('hide');
                        refreshPersonalInfo($("#customer_id").val());
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
});
function refreshPersonalInfo(customerId)
{
    if(customerId != '')
    {
        $.ajax({
              url:'<?=URL::to('/customers/refreshpersonalinfo');?>',
              method:"post",
              data:{customer_id: customerId,_token: "{{ csrf_token() }}"},
              beforeSend: function(){
                showLoader();
              },
              success: function(response){
                hideLoader();
                if(response != '')
                {
                    $(".personal-info-container").html(response);
                }
              }
        });
    }
}
</script>