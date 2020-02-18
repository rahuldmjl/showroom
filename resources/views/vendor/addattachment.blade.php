<div class="modal-header text-inverse">
    <button type="button" class="close p-0 m-0" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 class="modal-title" id="myLargeModalLabel">
    	<?php
        if($attachmentType == 'gstin')
    	{
            if(isset($edit) && $edit)
            {
                echo 'Edit GSTIN';
            }
    	}
    	?>
    </h5>
</div>
<?php 
$gstnumber = !empty($gstnumber) ? $gstnumber : '';
//echo $attachmentName;exit;
$documentType = '';
if($attachmentType == 'gstin'){
    $documentType = 'GSTIN';
}
?>
{!! Form::open(array('method'=>'POST','id'=>'add-customer-doc-form','class'=>'form-horizontal','autocomplete'=>'nope','enctype'=>'multipart/form-data')) !!}
<div class="modal-body">
    
    {{ Form::hidden('vendor_id', $vendorId, array('id' => 'vendor_id')) }}
    {{ Form::hidden('attachment_type', $attachmentType, array('id' => 'attachment_type')) }}
    {{ Form::hidden('gstnumber', $gstnumber, array('id' => 'gstnumber')) }}
    <div class="row mr-b-10">
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="gstin"><?= ($attachmentType == 'gstin') ? 'GSTIN No:' : ''?><span class="text-danger">*</span></label>
            {!! Form::text('gstin', $gstnumber, array('class' => 'form-control required','id'=>'gstin','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            <label id="gstin-pan-number-error" class="error"></label>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="submit" id="btn-submit-document" class="btn btn-info ripple text-left">Submit</button> 
    <button type="button" class="btn btn-danger ripple text-left" data-dismiss="modal">Close</button>
</div>
{!! Form::close() !!}

<style type="text/css">.form-control:disabled, .form-control[readonly] {background-color: #fff;}.h-500{height:500px;}</style>
<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<script src="<?=URL::to('/');?>/js/additional-methods.min.js"></script>
<script>
$(document).ready(function(){
    $("#add-customer-doc-form").submit(function(e){
        e.preventDefault();
        var docType = '<?= $attachmentType?>';
        var requiredMessage = '';
        var invalidMessage = '';
        var validationRegex = null;

        if(docType == 'gstin')
        {
            requiredMessage = 'GSTIN is required';
            invalidMessage = 'Invalid GSTIN';
            validationRegex = /^([0-9]){2}([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}([0-9]){1}([a-zA-Z]){1}([a-zA-Z0-9]){1}?$/;
        }
        
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
        $(this).validate({
            rules: {
                gstin:{
                    required: true,
                    regex : validationRegex,
                }
            },
            messages: {
                gstin:{
                    required: requiredMessage,
                    regex: invalidMessage
                }
            }
        });
        if($(this).valid())
        {
            $.ajax({
                type: 'post',
                url: '<?=URL::to('/vendor/addvendorgstin');?>',
                contentType: false,
                //dataType: 'json',
                data: new FormData(this),
                processData: false,
                cache: false,
                beforeSend: function(){
                    $(this).prop("disabled",true);
                    showLoader();
                },
                success: function(response){
                    hideLoader();
                    $("#view-attachment-modal").modal('hide');
                    var res = JSON.parse(response);
                    //console.log(res.status);
                    if(res.status)
                    {
                        $.ajax({
                              url:'<?=URL::to('/vendor/refreshgstin');?>',
                              method:"post",
                              data:{vendor_id: '<?= $vendorId?>',_token: "{{ csrf_token() }}"},
                              beforeSend: function(){
                                showLoader();
                              },
                              success: function(response){
                                  hideLoader();
                                  if(response!='')
                                  $(".vendor-address-container").html(response);
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
                    }else if(res.status_validate == false){
                        $("#gstin-pan-number-error").html(res.message.gstin);
                        return false;
                    }
                    else
                    {
                        swal({
                            title: 'Oops!',
                            text: res.message,
                            type: 'error',
                            buttonClass: 'btn btn-primary'
                            //showSuccessButton: true,
                            //showConfirmButton: false,
                            //successButtonClass: 'btn btn-primary',
                            //successButtonText: 'Ok'
                        });
                    }
                }
            });
        }
    });
});
</script>