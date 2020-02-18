{!! Form::open(array('method'=>'POST','id'=>'edit-exhibition-form','class'=>'form-horizontal','autocomplete'=>'nope')) !!}
<div class="modal-header">
    <button type="button" class="close p-0 m-0" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 class="modal-title" id="myLargeModalLabel">Edit Exhibition Details</h5>
</div>
<div class="modal-body">
    {{ Form::hidden('exhibition_id', $exhibitionData->id, array('id' => 'exhibition_id')) }}
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
                {!! Form::text('exhibition_title', $exhibitionData->title, array('placeholder' => 'Exhibition Title','class' => 'form-control required')) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="l30">Exhibition Place</label>
                {!! Form::text('exhibition_place', $exhibitionData->place, array('placeholder' => 'Place','class' => 'form-control required')) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="l30">Address</label>
                {!! Form::textarea('exhibition_address', $exhibitionData->address, array('placeholder' => 'Exhibition Address','class' => 'form-control')) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="l30">Product Markup</label>
                {!! Form::number('exhibition_markup', $exhibitionData->markup, array('placeholder' => '2.5','class' => 'form-control', 'step' => '0.01')) !!}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" id="btn-update-exhibitiondetail" class="btn btn-info ripple text-left">Submit</button>
    <button type="button" class="btn btn-danger ripple text-left" data-dismiss="modal">Close</button>
</div>
{!! Form::close() !!}

<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<script>
$(document).ready(function(){
    $("#btn-update-exhibitiondetail").click(function(){
        $("#edit-exhibition-form").validate({
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
        if($("#edit-exhibition-form").valid())
        {
            var url = '<?=URL::to('/inventory/updateexhibitiondata');?>';

            var editExhibitionForm=$("#edit-exhibition-form");
            $.ajax({
                type: 'post',
                url: url,
                data: editExhibitionForm.serialize(),
                beforeSend: function()
                {
                    showLoader();
                    $("#btn-update-exhibitiondetail").prop("disabled",true);
                },
                success: function(response){
                    $("#btn-genrate-invoicememo").prop("disabled",false);
                    hideLoader();
                    var res = JSON.parse(response);
                    $("#edit-exhibition-modal").modal('hide');
                    if(res.status==true)
                    {
                        hideLoader();
                        $("#exhibitionListTable tbody").html(res.content);
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
                },
                error: function(){
                    hideLoader();
                }
            })
        }
    });
});
</script>
<style type="text/css">.form-control:disabled, .form-control[readonly] {background-color: #fff;}</style>