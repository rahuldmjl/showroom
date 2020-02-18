<?php
$approvalType = Config::get('constants.approval_type');
$depositType = Config::get('constants.deposit_type');
$customerDivision = Config::get('constants.invoice_division_number');
$invoiceDivisionLimit = Config::get('constants.invoice_division_limit');
$isFromCustomerView = isset($isFromCustomerView) ? $isFromCustomerView : false;
$customerId = isset($customerId) ? $customerId : '';
//var_dump($customerId);
?>
<div class="modal-header">
    <button type="button" class="close p-0 m-0" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 class="modal-title" id="myLargeModalLabel">Exhibition Details</h5>
</div>
<div class="modal-body">
    {{ Form::hidden('product_ids', $productIds, array('id' => 'product_ids')) }}
    {!! Form::hidden('customerId', $customerId, array('class' => 'form-control','id'=>'customerId')) !!}
    {!! Form::token() !!}
    <div class="row">
        <div class="alert alert-icon alert-success border-success fade invoice-success-message hidden w-100" role="alert">
            <i class="material-icons list-icon">check_circle</i>
            <strong>Well done! </strong><span class="imvoice-message"></span>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="l30">Exhibition Title</label>
                {!! Form::text('exhibition_title', null, array('placeholder' => 'Exhibition Title','class' => 'form-control required')) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="l30">Exhibition Place</label>
                {!! Form::text('exhibition_place', null, array('placeholder' => 'Place','class' => 'form-control required')) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="l30">Address</label>
                {!! Form::textarea('exhibition_address', null, array('placeholder' => 'Exhibition Address','class' => 'form-control')) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="l30">Product Markup</label>
                {!! Form::number('exhibition_markup', null, array('placeholder' => '2.5','class' => 'form-control', 'step' => '0.01')) !!}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" id="btn-genrate-invoicememo" class="btn btn-info ripple text-left">Submit</button>
    <button type="button" class="btn btn-danger ripple text-left" data-dismiss="modal">Close</button>
</div>
<link rel="stylesheet" href="<?=URL::to('/');?>/css/autocomplete.css"/>
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css"/>
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css"/>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<script>
$(document).ready(function(){
    $("#btn-genrate-invoicememo").click(function(){
        $("#invoicememo-generate-form").validate({
            rules: {
                exhibition_title: "required",
                exhibition_place: "required",
                /*txtcontactnumber:{
                    required: true,
                    number: true,
                    maxlength: 13
                },
                txtaddress: "required",
                selectcountry: "required",
                txtstateprovince: "required",
                txtcity: "required",
                txtinvoicenumber: "required",
                txtinvoicedate: "required",
                txtmemonumber: "required",
                txtapprovaldate: "required",
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
                paymentmode: "required",
                discount_type: "required",
                txtdmusercodeemail: "required",
                //txtdiscountval: "required",
                approval_type: "required",
                deposit_type: "required"*/
            },
            messages: {
                exhibition_title: "Exhibition Title is required",
                exhibition_place: "Exhibition Place is required",
                /*txtcontactnumber:{
                    required: "Contact number is required",
                    number: "Invalid contact number",
                    maxlength: "Invalid contact number"
                },
                txtaddress: "Address is required",
                selectcountry: "Country is required",
                txtstateprovince: "State/Province is required",
                txtcity: "City is required",
                txtinvoicenumber: "Invoice number is required",
                txtinvoicedate: "Invoice date is required",
                txtmemonumber: "Approval number is required",
                txtapprovaldate: "Approval date is required",
                txtzipcode:{
                    required: "Zip code is required",
                    number: "Invalid zip code"
                },
                txtemail:{
                    required: "Email is required",
                    email: "Invalid email"
                },
                paymentmode: "Payment mode is required",
                discount_type: "Discount is required",
                txtdmusercodeemail: "DMUSERCODE or Email is required",
                //txtdiscountval: "Discount value is required",
                approval_type: "Approval type is required",
                deposit_type: "Deposit type is required"*/
            }
        });
        if($("#invoicememo-generate-form").valid())
        {
            var url = '<?=URL::to('/inventory/storeexhibitiondata');?>';

            var invoiceMemoForm=$("#invoicememo-generate-form");
            $.ajax({
                type: 'post',
                url: url,
                data: invoiceMemoForm.serialize(),
                beforeSend: function(){
                    $("#btn-genrate-invoicememo").prop("disabled",true);
                    showLoader();
                },
                success: function(response){
                    $("#btn-genrate-invoicememo").prop("disabled",false);
                    hideLoader();
                    var res = JSON.parse(response);
                    if(res.status==true)
                    {
                        inventoryProductsTable.draw();
                        var exhibitionId = res.exhibition_id;
                        $("#invoice-memo-modal").modal('hide');
                        hideLoader();
                        swal({
                          title: 'Success',
                          text: res.message,
                          type: 'success',
                          buttonClass: 'btn btn-primary'
                        });
                        setTimeout(function(){
                            window.location.href = '<?=URL::to('/inventory/generateexhibitionexcel/');?>/'+exhibitionId;
                        }, 5000);
                    }
                    else
                    {
                        $("#invoice-memo-modal").modal('hide');
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
                },
                error: function(){
                    hideLoader();
                }
            })
        }
    });
});
</script>
<style type="text/css">.ui-autocomplete{z-index: 9999;}#invoice-memo-modal .ui-autocomplete {width: 94%!important;max-height: 370px;overflow-y: auto;}.form-control:disabled, .form-control[readonly] {background-color: #fff;}</style>