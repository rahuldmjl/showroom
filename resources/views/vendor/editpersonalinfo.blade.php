<div class="modal-header text-inverse">
    <button type="button" class="close p-0 m-0" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 class="modal-title" id="myLargeModalLabel">
    	Edit Personal Information
    </h5>
</div>
{!! Form::open(array('method'=>'POST','id'=>'edit-personalinfo-form','class'=>'form-horizontal','autocomplete'=>'nope','enctype'=>'multipart/form-data')) !!}
{{ Form::hidden('vendor_id', $vendorId, array('id' => 'vendor_id')) }}
<div class="modal-body">
    <div class="row mr-b-10">
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="name">Name <span class="text-danger">*</span></label>
            {!! Form::text('name', $vendorName , array('class' => 'form-control required','id'=>'name','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="email">Last Name <span class="text-danger">*</span></label>
            {!! Form::text('email', $vendorEmail, array('class' => 'form-control','id'=>'email','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="phone">Contact No <span class="text-danger">*</span></label>
            {!! Form::text('phone', $vendorPhone, array('class' => 'form-control','id'=>'phone','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
        </div>
        <div class="col-md-4 mb-3 new-customer-field validDmcode">
            <label for="vendor_dmcode">DMcode</label>
            {!! Form::text('vendor_dmcode', $vendorDMcode, array('class' => 'form-control','id'=>'vendor_dmcode','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
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
    $("#btn-update-personalinfo").click(function(){
        $("#edit-personalinfo-form").validate({
            rules: {
                name: "required",
                phone:{
                    required: true,
                    number: true,
                    maxlength: 13
                },
                vendor_dmcode: "required",
                email: {
                    required: true,
                    email: true
                }
            },
            messages: {
                name: "Name is required",
                phone:{
                    required: "Contact is required",
                    number: "Invalid Contact",
                    maxlength: "Invalid Contact"
                },
                
                vendor_dmcode: "DMcode is required",
                email:{
                    required: "Email is required",
                    email: "Invalid email"
                }
            }
        });
        if($("#edit-personalinfo-form").valid())
        {
            $.ajax({
                type: 'post',
                url: '<?=URL::to('/vendor/updatepersonalinfo');?>',
                data: $("#edit-personalinfo-form").serialize(),
                beforeSend: function(){
                    $(this).prop("disabled",true);
                    showLoader();
                },
                success: function(response){
                    hideLoader();
                    var res = JSON.parse(response);
                    //console.log(res.message.vendor_dmcode);
                    if(res.status)
                    {
                        $("#view-attachment-modal").modal('hide');
                        refreshPersonalInfo($("#vendor_id").val());
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
                    }else if(res.status_validate == false){
                        $(".validDmcode").append("<label id='vendor_dmcode-error' class='error' for='vendor_dmcode'>"+res.message.vendor_dmcode+"</label>");
                        return false;
                    }else{
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
function refreshPersonalInfo(vendorId)
{
    if(vendorId != '')
    {
        $.ajax({
              url:'<?=URL::to('/vendor/refreshpersonalinfo');?>',
              method:"post",
              data:{vendor_id: vendorId,_token: "{{ csrf_token() }}"},
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