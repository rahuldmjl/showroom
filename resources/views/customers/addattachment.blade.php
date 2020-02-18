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
            else
            {
                echo 'Add GSTIN';
            }
    	}
    	else if($attachmentType == 'pan_card')
    	{
            if(isset($edit) && $edit)
            {
                echo 'Edit PAN Card';
            }
            else
            {
                echo 'Add PAN Card';
            }
    	}
    	?>
    </h5>
</div>
<?php 
$attachmentNumber = !empty($attachmentNumber) ? $attachmentNumber : '';
$attachmentName = !empty($attachmentName) ? $attachmentName : '';
//echo $attachmentName;exit;
$documentType = '';
if($attachmentType == 'gstin')
{
    $documentType = 'GSTIN';
}
else
{
    $documentType = 'PAN Card';
}
?>
{!! Form::open(array('method'=>'POST','id'=>'add-customer-doc-form','class'=>'form-horizontal','autocomplete'=>'nope','enctype'=>'multipart/form-data')) !!}
<div class="modal-body">
    
    {{ Form::hidden('customer_id', $customerId, array('id' => 'customer_id')) }}
    {{ Form::hidden('attachment_type', $attachmentType, array('id' => 'attachment_type')) }}
    {{ Form::hidden('attachment_name', $attachmentName, array('id' => 'attachment_name')) }}
    <div class="row mr-b-10">
        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtfirstname"><?= ($attachmentType == 'gstin') ? 'GSTIN No:' : 'PAN Card No:'?><span class="text-danger">*</span></label>
            {!! Form::text('attachment_no', $attachmentNumber, array('class' => 'form-control required','id'=>'attachment_no','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            <label id="gstin-pan-number-error" class="error"></label>
        </div>

        <div class="col-md-4 mb-3 new-customer-field">
            <label for="txtfirstname">Upload</label>
            <input type="file" id="attachment_file" name="attachment_file" accept="image/*,application/pdf">
        </div>
    </div>
    <div class="row mr-b-10">
        <?php if(isset($edit) && $edit):?>
            <div class="col-md-12 mb-3 new-customer-field">
                <label class="mr-b-20 w-100"><?= ($attachmentType == 'gstin') ? 'GSTIN Attachment:' : 'PAN Card Attachment:'?></label>
                <?php
                $attachmentDir = '';
                if($attachmentType == 'gstin')
                {
                    $attachmentDir = 'gstin';
                }
                else
                {
                    $attachmentDir = 'pancard';
                }
                ?>
                <?php if(!empty($attachmentName)):?>
                    <?php
                    $explodedFileName = explode('.',$attachmentName);
                    $fileExtention = end($explodedFileName);
                    ?>
                    <?php if($fileExtention != 'pdf'):?>
                        <img src="<?= URL::to('uploads/'.$attachmentName)?>">
                    <?php else:?>
                        <!-- <div style="text-align: center;"> -->
                            <iframe class="w-100 h-500" src="<?= URL::to('uploads/'.$attachmentName)?>"frameborder="0"></iframe>
                        <!-- </div> -->
                    <?php endif;?>
                <?php endif;?>
            </div>
        <?php endif;?>
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
        else
        {
            requiredMessage = 'PAN Card number is required';
            invalidMessage = 'Invalid PAN Card number';
            validationRegex = /^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/;
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
                attachment_no:{
                    required: true,
                    regex : validationRegex,
                },
                attachment_file: {
                    extension: "png|jpg|jpeg|pdf"
                }
            },
            messages: {
                attachment_no:{
                    required: requiredMessage,
                    regex: invalidMessage
                },
                attachment_file:{
                    extension: 'Invalid file type'
                }
            }
        });
        if($(this).valid())
        {
            $.ajax({
                type: 'post',
                url: '<?=URL::to('/customers/addcustomerpangstin');?>',
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
                              url:'<?=URL::to('/customers/refreshgstinpancard');?>',
                              method:"post",
                              data:{customer_id: '<?= $customerId?>',_token: "{{ csrf_token() }}"},
                              beforeSend: function(){
                                showLoader();
                              },
                              success: function(response){
                                  hideLoader();
                                  if(response!='')
                                  $(".gst-pancard-container").html(response);
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