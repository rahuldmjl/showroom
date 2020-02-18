<?php
$approvalType = Config::get('constants.approval_type');
$depositType = Config::get('constants.deposit_type');
$customerDivision = Config::get('constants.invoice_division_number');
$invoiceDivisionLimit = Config::get('constants.invoice_division_limit');
$isFromCustomerView = isset($isFromCustomerView) ? $isFromCustomerView : false;
$customerId = isset($customerId) ? $customerId : '';
$user = Auth::user();
?>
<div class="modal-header text-inverse">
    <button type="button" class="close p-0 m-0" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 class="modal-title" id="myLargeModalLabel">Generate <?php echo ucfirst($operationType); ?></h5>
</div>
<div class="modal-body">
    {{ Form::hidden('operation_type', $operationType, array('id' => 'operation_type')) }}
    {{ Form::hidden('product_ids', $productIds, array('id' => 'product_ids')) }}
    {{ Form::hidden('franchisee_name', '', array('id' => 'franchisee_name')) }}
    {{ Form::hidden('approval_memo_id', '', array('id' => 'approval_memo_id')) }}
    {!! Form::hidden('customerId', $customerId, array('class' => 'form-control','id'=>'customerId')) !!}
    {{ Form::hidden('is_from_customer_view', 'false', array('id' => 'is_from_customer_view')) }}
    {!! Form::token() !!}
    <div class="row">
        <div class="alert alert-icon alert-danger border-danger fade customer-verification-alert hidden w-100" role="alert">
            <i class="material-icons list-icon">not_interested</i>
            <span class="customer-check-message"></span>
        </div>
        <div class="alert alert-icon alert-success border-success fade invoice-success-message hidden w-100" role="alert">
            <i class="material-icons list-icon">check_circle</i>
            <strong>Well done! </strong><span class="imvoice-message"></span>
        </div>
    </div>
	<div class="form-group col mr-b-0 mr-l-0 pd-l-0 <?php echo ($isFromCustomerView) ? 'hidden' : 'd-flex' ?>">
		<label class="col-form-label mr-2">Customer Type</label>
		<div class="radiobox radio-info">
            <label class="mr-2">
                 {{ Form::radio('customerType', 'new' ,false) }}
                 <span class="label-text">New</span>
            </label>

            <label>
                {{ Form::radio('customerType', 'existing' , $isFromCustomerView) }} <span class="label-text">Existing</span>
            </label>
        </div>
	</div>
	<section id="customer-form-container" class="<?php echo (!$isFromCustomerView) ? 'hidden' : '' ?>">
    	<div class="row medium-input">
            <div class="col-12 new-customer-field hidden">
                <h5 class="border-b-light-1 pb-2 mb-3 <?php echo ($isFromCustomerView) ? 'hidden' : '' ?>">Franchise Details</h5>
            </div>
            <div class="col-md-6 mb-3 input-has-value new-customer-field hidden">
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
            <div class="col-md-6 mb-3 txtfrncode-input-div new-customer-field hidden">
                <label for="txtfrncode" id="frncode_label">FRN Code <span class="text-danger">*</span></label>
                {!! Form::text('txtfrncode', null, array('class' => 'form-control','id'=>'txtfrncode','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-12">
                <h5 class="border-b-light-1 pb-2 mb-3 <?php echo ($isFromCustomerView) ? 'hidden' : '' ?>">Customer Details</h5>
            </div>
            <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtfirstname">First Name <span class="text-danger">*</span></label>
                {!! Form::text('txtfirstname', null, array('class' => 'form-control required','id'=>'txtfirstname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtlastname">Last Name <span class="text-danger">*</span></label>
                {!! Form::text('txtlastname', null, array('class' => 'form-control','id'=>'txtlastname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtcontactnumber">Contact Number <span class="text-danger">*</span></label>
                {!! Form::text('txtcontactnumber', null, array('class' => 'form-control','id'=>'txtcontactnumber','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtaddress">Address <span class="text-danger">*</span></label>
                {!! Form::text('txtaddress', null, array('class' => 'form-control','id'=>'txtaddress','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="selectcountry">Country <span class="text-danger">*</span></label>
                <select class="form-control" name="selectcountry" id="selectcountry" data-placeholder="Select">
					<option value="">Select</option>
                    <option value="<?php echo $countryList['country_id']; ?>"><?php echo $countryList['name'] ?></option>
                </select>
            </div>

            <div class="col-md-4 mb-3 new-customer-field hidden customer-state">
                <label for="txtstateprovince">State/Province <span class="text-danger">*</span></label>
                {!! Form::text('txtstateprovince', null, array('class' => 'form-control','id'=>'txtstateprovince','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtcity">City <span class="text-danger">*</span></label>
                {!! Form::text('txtcity', null, array('class' => 'form-control','id'=>'txtcity','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtzipcode">Zip Code <span class="text-danger">*</span></label>
                {!! Form::text('txtzipcode', null, array('class' => 'form-control','id'=>'txtzipcode','maxlength'=>'6','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtemail">Email <span class="text-danger">*</span></label>
                {!! Form::email('txtemail', null, array('class' => 'form-control','id'=>'txtemail','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtemail">GSTIN</label>
                {!! Form::text('txtgstin', null, array('class' => 'form-control','id'=>'txtgstin','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-md-4 mb-3 new-customer-field hidden gstin-field">
                <label for="txtemail">GSTIN Attachment </label>
                <div class="input-group">
                  <div class="input-group-btn width-90">
                    <div class="fileUpload btn w-100 btn-default">
                      <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
                      <input id="gstinattachment" type="file" class="upload width-90" name="gstinattachment" accept="image/*,application/pdf">
                    </div>
                  </div>
                  <input id="gstin_attachment_file" name="gstin_attachment_file" class="form-control border bg-transparent" placeholder="Choose File" disabled="disabled">
                </div>
            </div>
            <div class="col-md-4 mb-3 email-field hidden">
                <label for="txtdmusercodeemail">DMUSERCODE or Email or Name <span class="text-danger">*</span></label>
                {!! Form::text('txtdmusercodeemail', null, array('class' => 'form-control','id'=>'txtdmusercodeemail','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}

            </div>
            <div class="col-md-4 mb-3 invoice-field hidden">
                <label for="paymentmode">Payment Mode <span class="text-danger">*</span></label>
                {!! Form::select('paymentmode',array(''=>'Select','cash'=>'Cash','check'=>'Cheque'),[], array('class' => 'form-control')) !!}
            </div>
            <div class="col-md-4 mb-3 invoice-field">
                <label for="discount">Discount <span class="text-danger">*</span></label>
                {!! Form::select('discount_type',array(''=>'Select','percent'=>'%','amount'=>'Amount'),[], array('class' => 'form-control','id'=>'discount_type')) !!}
            </div>

            <div class="col-md-4 mb-3 invoice-field">
                <label for="discount">Transportation Mode <span class="text-danger">*</span></label>
                {!! Form::text('transportation_mode', null, array('class' => 'form-control')) !!}
            </div>
            <div class="col-md-4 mb-3 invoice-field">
                <label for="discount">Shipping Charge</label>
                {!! Form::number('txtshippingcharge', null, array('class' => 'form-control','min' => 0,'step' => 0.1)) !!}
            </div>

            <div class="col-md-4 mb-3 discount-value hidden">
                <label for="txtdiscountval"><span class="discount-val-label"></span> <span class="text-danger">*</span></label>
                {!! Form::text('txtdiscountval', null, array('class' => 'form-control','id'=>'txtdiscountval','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
        </div>
        <div class="row medium-input mr-b-10 invoiceqr-commission-field <?php echo (!$isFromCustomerView) ? 'hidden' : '' ?>">
            <div class="col-12">
                 <h5 class="border-b-light-1 pb-2 mb-3">Commission</h5>
            </div>
            <div class="col-md-4 mb-3 input-has-value ">
                <label for="txtfranchisename">Franchise Name</label>
                <select class="form-control" name="franchisee" id="franchisee" data-placeholder="Select" data-toggle="select2">
                    <option value="">Select</option>
                     <?php foreach ($franchiseeData as $value): ?>
                        <option value="<?php echo $value['entity_id']; ?>" data-name="<?php echo $value['name'] ?>"><?php echo $value['name'] ?></option>
                     <?php endforeach;?>
                </select>
            </div>
            <div class="col-md-4 mb-3 input-has-value invoice-commission-field">
                <label for="txtfranchisecommission">Franchise Commission(%)</label>
                {!! Form::text('txtfranchisecommission', null, array('class' => 'form-control','id'=>'txtfranchisecommission','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <div class="col-md-4 mb-3 input-has-value">
                <label for="txtagentname">Agent Name</label>
                {!! Form::text('txtagentname', null, array('class' => 'form-control','id'=>'txtagentname','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <?php if ($operationType == 'memo'): ?>
                <div class="col-md-4 mb-3 input-has-value ">
                    <label for="approval_type">Approval Type <span class="text-danger">*</span></label>
                    <select class="form-control" id="approval_type" name="approval_type">
                        <option value="">Select</option>
                        <?php foreach ($approvalType as $key => $value): ?>
                            <option value="<?=$key?>"><?=$value?></option>
                        <?php endforeach;?>
                    </select>
                </div>
            <?php endif;?>
            <?php if ($operationType == 'memo'): ?>
                <div class="col-md-4 mb-3 input-has-value ">
                    <label for="deposit_type">Deposit Type <span class="text-danger">*</span></label>
                    <select class="form-control" id="deposit_type" name="deposit_type">
                        <!-- <option value="">Select</option> -->
                        <?php foreach ($depositType as $key => $value): ?>
                            <option value="<?=$key?>" <?php echo ($key == 'without_deposit') ? 'selected' : '' ?>><?=$value?></option>
                        <?php endforeach;?>
                    </select>
                </div>
            <?php endif;?>
            <div class="col-md-4 mb-3 input-has-value invoice-field">
                <label for="txtagentcommission">Agent Commission(%)</label>
                {!! Form::text('txtagentcommission', null, array('class' => 'form-control','id'=>'txtagentcommission','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
            </div>
            <input type="hidden" name="invoice_grand_total" value="<?=$invoiceTotal?>">
            <?php if ($invoiceTotal > $invoiceLimit): ?>

                <div class="col-md-4 mb-3 input-has-value invoice-field">
                    <label class="">Do you want to seprate this invoice? </label>
                    <div class="radiobox radio-info">
                        <label class="mr-2">
                             <input name="radioSeperateInvoice" type="radio" value="yes">
                             <span class="label-text">Yes</span>
                        </label>
                        <label>
                            <input name="radioSeperateInvoice" type="radio" value="no"> <span class="label-text">No</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-4 mb-3 input-has-value hidden" id="invoice-division-div">
                    <label for="totalCustomer">No. of Invoice Division <span class="text-danger">*</span></label>
                    <select class="form-control" id="total-invoice-customer" name="totalCustomer">
                        <option value="">Select</option>
                        <?php foreach ($customerDivision as $key => $value): ?>
                            <option value="<?=$key?>"><?=$value?></option>
                        <?php endforeach;?>
                    </select>
                </div>
                <div class="col-md-12 mb-3 hidden" id="child-customer-container">

                </div>
            <?php endif;?>
        </div>
        <div class="row mr-b-10 invoice-olddata-field">
            <?php if ($operationType == 'invoice' || ($operationType == 'memo' && $user->hasRole('Super Admin'))): ?>
                <div class="col-md-12 mr-l-15 checkbox checkbox-primary">
                    <label><input class="form-check-input" type="checkbox" name="invoicememo_with_olddata" id="chkOlddata"><span class="label-text"><strong><?=ucfirst($operationType)?> with old data</span></strong></label>
                </div>
            <?php endif;?>
            <?php if ($operationType == 'invoice'): ?>
                <div class="col-md-4 mb-3 input-has-value invoicememo-old-data hidden">
                    <label for="txtinvoicenumber">Invoice Number <span class="text-danger">*</span></label>
                    {!! Form::text('txtinvoicenumber', null, array('class' => 'form-control','id'=>'txtinvoicenumber','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
                </div>
                <div class="col-md-4 mb-3 input-has-value invoicememo-old-data hidden">
                    <label for="txtinvoicedate">Invoice Date <span class="text-danger">*</span></label>
                    {!! Form::text('txtinvoicedate', null, array('class' => 'form-control datepicker','id'=>'txtinvoicedate','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
                </div>
            <?php elseif ($operationType == 'memo'): ?>
                <div class="col-md-4 mb-3 input-has-value invoicememo-old-data hidden">
                    <label for="txtmemonumber">Approval Number <span class="text-danger">*</span></label>
                    {!! Form::text('txtmemonumber', null, array('class' => 'form-control','id'=>'txtmemonumber','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
                </div>
                <div class="col-md-4 mb-3 input-has-value invoicememo-old-data hidden">
                    <label for="txtapprovaldate">Approval Date <span class="text-danger">*</span></label>
                    {!! Form::text('txtapprovaldate', null, array('class' => 'form-control datepicker','id'=>'txtapprovaldate','autocomplete'=>'nope','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
                </div>
            <?php endif;?>
        </div>
    </section>
</div>
<div class="modal-footer <?php echo (!$isFromCustomerView) ? 'hidden' : '' ?>">
<?php if ($operationType == 'memo'): ?>
	<button type="button" id="btn-preview-memo" class="btn btn-primary ripple text-left disabled " disabled>Preview Approval</button>
<?php endif;?>
    <button type="button" id="btn-genrate-invoicememo" class="btn btn-info ripple text-left">Submit</button>
    <button type="button" class="btn btn-danger ripple text-left" data-dismiss="modal">Close</button>
</div>
<link rel="stylesheet" href="<?=URL::to('/');?>/css/autocomplete.css"/>
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css"/>
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css"/>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<script src="<?=URL::to('/');?>/js/additional-methods.min.js"></script>
<script>
$(document).ready(function(){
    document.getElementById("gstinattachment").onchange = function () {
        document.getElementById("gstin_attachment_file").value = this.value.substring(12);
    };
    /*$("#txtinvoicenumber").blur(function(){
        if(this.value!='')
        {
            $.ajax({
                type: 'post',
                url: '<?=URL::to('/inventory/checkinvoicenumber');?>',
                data:{invoice_number:this.value,_token:"{{ csrf_token() }}"},
                beforeSend: function(){
                    showLoader();
                },
                success: function(response){
                    hideLoader();
                },
            });
        }
    });*/
    $("#chkOlddata").click(function(){
        if($(this).prop("checked") == true){
            $(".invoicememo-old-data").removeClass('hidden');
        }
        else
        {
            $(".invoicememo-old-data").addClass('hidden');
        }
    });
    $('.datepicker').datepicker({autoclose: true,endDate: "today"});
    $( "#txtdmusercodeemail" ).autocomplete({
          source: function( request, response ) {
           // Fetch data
           $.ajax({
            url:  "{{ route('searchcustomer') }}",
            type: 'POST',
            dataType: "json",
            data: {
             term: request.term,
             _token:"{{ csrf_token() }}"
            },
            success: function( data ) {
                response($.map( data, function( item ) {
                    return {
                        label: item.label,
                        value: item.value
                    }
                }));
            }
           });
          },
          appendTo: "#invoice-memo-modal .modal-content",
/*        open: function() {
            $("ul.ui-menu").width( $("#invoice-memo-modal .modal-content").width());

         }, */
         search: function(){
            $("#btn-genrate-invoicememo").addClass('disabled');
         },
          minLength:3,
          select: function (event, ui) {
               $("#customerId").val(ui.item.value);
               $(this).val(ui.item.label);
               $("#btn-genrate-invoicememo").removeClass('disabled');
               $("#btn-preview-memo").removeClass('disabled');
			   $("#btn-preview-memo").removeAttr('disabled');
               return false;
          }
    });
    $("#total-invoice-customer").change(function(){
        var totalCustomer = this.value;
        var customerHtml = '';
        for(index=1;index<=totalCustomer;index++)
        {

            customerHtml+= '<div class="row mr-b-10">';
            customerHtml+=  '<div class="col-12 customer-title">';
            customerHtml+=   '<h5 class="border-b-light-1 pb-2 mb-3">Customer-'+index+'</h5>';
            customerHtml+=  '</div>';
            customerHtml+=      '<div class="col-md-4 mb-3 email-field">';
            customerHtml+=          '<label for="txtchildcustname_'+index+'">Name <span class="text-danger">*</span></label>';
            customerHtml+=          '<input class="form-control txtchildcustname" id="txtchildcustname_'+index+'" autocomplete="off" readonly="true" onfocus="this.removeAttribute(\'readonly\')" name="txtchildcustname[]" type="text">';
            customerHtml+=      '</div>';
            customerHtml+=      '<div class="col-md-4 mb-3 email-field">';
            customerHtml+=          '<label for="txtchildcustaddress_'+index+'">Address <span class="text-danger">*</span></label>';
            customerHtml+=          '<input class="form-control txtchildcustaddress" id="txtchildcustaddress_'+index+'" autocomplete="off" readonly="true" onfocus="this.removeAttribute(\'readonly\')" name="txtchildcustaddress[]" type="text">';
            customerHtml+=      '</div>';
            customerHtml+=      '<div class="col-md-4 mb-3 email-field">';
            customerHtml+=          '<label for="txtchildcustpanno_'+index+'">PAN Card Number </label>';
            customerHtml+=          '<input class="form-control txtchildcustpanno" id="txtchildcustpanno_'+index+'" autocomplete="off" readonly="true" onfocus="this.removeAttribute(\'readonly\')" name="txtchildcustpanno[]" type="text">';
            customerHtml+=      '</div>';
            customerHtml+= '</div>';
        }
        $("#child-customer-container").html(customerHtml);
        $("#child-customer-container").removeClass('hidden');
    });
    $(document).on('change','input[name=radioSeperateInvoice]',function(){
        if(this.value == 'yes')
        {
            $("#invoice-division-div").removeClass('hidden');
            $(".invoice-olddata-field").addClass("hidden");
        }
        else
        {
            $("#invoice-division-div").addClass('hidden');
            $(".invoice-olddata-field").removeClass("hidden");
        }
    });
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
    $(document).on('change','input[name=customerType]',function(){
        $("#customer-form-container,#invoice-memo-modal .modal-footer").removeClass("hidden");
        if(this.value == 'new')
        {
          $(".new-customer-field, .existing-customer-field").removeClass("hidden");
          $(".email-field").addClass('hidden');
          if($("#operation_type").val()=='invoice')
          {
              $(".invoice-field, .invoiceqr-commission-field, .invoice-commission-field").removeClass('hidden');
          }
          else
          {
              $(".invoice-field, .invoice-commission-field").addClass('hidden');
              $(".invoiceqr-commission-field").removeClass('hidden');
          }
          $("#btn-genrate-invoicememo").removeClass('disabled');
		  $("#btn-preview-memo").removeClass('disabled');
		  $("#btn-preview-memo").removeAttr('disabled');
          $("#customerId").val('');
          $("#discount_type").html("<option value=''>Select</option><option value='default_discount'>Default Discount</option>");
        }
        else if(this.value=='existing')
        {
            $(".existing-customer-field, .email-field").removeClass('hidden');
            $(".new-customer-field").addClass('hidden');
            $("#btn-genrate-invoicememo").addClass('disabled');
            if($("#operation_type").val()=='invoice')
            {
                $(".invoice-field, .invoiceqr-commission-field, .invoice-commission-field").removeClass('hidden');
            }
            else
            {
                $(".invoice-field, .invoice-commission-field").addClass('hidden');
                $(".invoiceqr-commission-field").removeClass('hidden');
            }
            $("#discount_type").html("<option value=''>Select</option><option value='approval_discount'>Approval Product Discount</option><option value='deposit_discount'>Deposit Product Discount</option><option value='default_discount'>Default Product Discount</option>");
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
                            //console.log(item);
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
    var selectfranchisee = $('#franchisee').select2({dropdownParent: $("#invoice-memo-modal")});
    var select2Instance = $('#franchisee').data('select2');
    select2Instance.on('results:message', function(params){
      this.dropdown._resizeDropdown();
      this.dropdown._positionDropdown();
    });
    selectfranchisee.on("select2:select", function (e) {
        var selected_element = $(e.currentTarget);
        var select_val = selected_element.val();
        $("#franchisee_name").val($("#franchisee").select2().find(":selected").data("name"));
    });
    /*$("#discount_type").change(function(){
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
    });*/
	//Preview approval
	$("#btn-preview-memo").click(function(){
		$.validator.addMethod(
          "regex",
           function(value, element, regexp) {
               if (regexp.constructor != RegExp)
                  regexp = new RegExp(regexp);
               else if (regexp.global)
                  regexp.lastIndex = 0;
                  return this.optional(element) || regexp.test(value);
           },"erreur expression reguliere"
        );
		$.validator.addMethod('filesize', function (value, element, param) {
            return this.optional(element) || (element.files[0].size <= param)
        }, 'File size must be less than {0}');
		$("#invoicememo-generate-form").validate({
            rules: {
                txtfirstname: "required",
                txtlastname: "required",
                transportation_mode: "required",
                txtcontactnumber:{
                    required: true,
                    number: true,
                    maxlength: 13
                },
                txtshippingcharge:{
                    number: true,
                },
                txtaddress: "required",
                selectcountry: "required",
                txtstateprovince: "required",
                txtcity: "required",
                txtgstin:{
                    regex : /^([0-9]){2}([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}([0-9]){1}([a-zA-Z]){1}([a-zA-Z0-9]){1}?$/,
                },
                gstinattachment: {
                    extension: "png|jpg|jpeg|pdf",
                    filesize: 2097152
                },
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
                deposit_type: "required",
                txtfrncode: {
                    required: function(element){
                            return $("input[name=radioIsFranchise]:checked").val()=="yes";
                        }
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
                transportation_mode: "Transportation mode is required",
                txtshippingcharge:{
                    number: "Invalid shipping charge",
                },
                txtgstin:{
                    regex: "Invalid GSTIN"
                },
                gstinattachment:{
                    extension: 'Invalid file type',
                    filesize: 'File size must be less than 2 MB'
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
                deposit_type: "Deposit type is required",
                txtfrncode: "FRN Code is required"
            }
        });
		if($("#invoicememo-generate-form").valid())
		{
			var invoiceMemoForm=$("#invoicememo-generate-form");
			var formData = new FormData(invoiceMemoForm[0]);
			$.ajax({
                contentType: false,
                type: 'post',
                url: '<?=URL::to('/inventory/processpreviewmemo');?>',
                processData: false,
                cache: false,
                data: formData,
                beforeSend: function(){
                    $("#btn-preview-memo").prop("disabled",true);
                    showLoader();
                },
                success: function(response){
					$("#btn-preview-memo").prop("disabled",false);
                    hideLoader();
                    var res = JSON.parse(response);

                    if(res.status==true)
                    {
						window.open('<?=URL::to('/inventory/previewmemo');?>','_blank');
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
    $("#btn-genrate-invoicememo").click(function(){
        $.validator.addMethod(
          "regex",
           function(value, element, regexp) {
               if (regexp.constructor != RegExp)
                  regexp = new RegExp(regexp);
               else if (regexp.global)
                  regexp.lastIndex = 0;
                  return this.optional(element) || regexp.test(value);
           },"erreur expression reguliere"
        );
        $.validator.addMethod('filesize', function (value, element, param) {
            return this.optional(element) || (element.files[0].size <= param)
        }, 'File size must be less than {0}');
        $("#invoicememo-generate-form").validate({
            rules: {
                txtfirstname: "required",
                txtlastname: "required",
                transportation_mode: "required",
                txtcontactnumber:{
                    required: true,
                    number: true,
                    maxlength: 13
                },
                txtshippingcharge:{
                    number: true,
                },
                txtaddress: "required",
                selectcountry: "required",
                txtstateprovince: "required",
                txtcity: "required",
                txtgstin:{
                    regex : /^([0-9]){2}([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}([0-9]){1}([a-zA-Z]){1}([a-zA-Z0-9]){1}?$/,
                },
                gstinattachment: {
                    extension: "png|jpg|jpeg|pdf",
                    filesize: 2097152
                },
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
                deposit_type: "required",
                //txtfrncode: "required",
                txtfrncode: {
                    required: function(element){
                            return $("input[name=radioIsFranchise]:checked").val()=="yes";
                        }
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
                transportation_mode: "Transportation mode is required",
                txtshippingcharge:{
                    number: "Invalid shipping charge",
                },
                txtgstin:{
                    regex: "Invalid GSTIN"
                },
                gstinattachment:{
                    extension: 'Invalid file type',
                    filesize: 'File size must be less than 2 MB'
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
                deposit_type: "Deposit type is required",
                txtfrncode: "FRN Code is required"
            }
        });
        if($("#invoicememo-generate-form").valid())
        {
            var url = '<?=URL::to('/inventory/generateinvoicememo');?>';

            var invoiceMemoForm=$("#invoicememo-generate-form");
            var formData = new FormData(invoiceMemoForm[0]);
            $.ajax({
                contentType: false,
                type: 'post',
                url: url,
                processData: false,
                cache: false,
                data: formData,
                beforeSend: function(){
                    $("#btn-genrate-invoicememo").prop("disabled",true);
                    showLoader();
                },
                success: function(response){
                    //console.log('after generate invoice response...');
                    $("#btn-genrate-invoicememo").prop("disabled",false);
                    hideLoader();
                    var res = JSON.parse(response);

                    if(res.status==true)
                    {
                        /*$("#invoicememo-generate-form").trigger("reset");
                        $(".invoice-success-message").removeClass('hidden');
                        $(".invoice-success-message").addClass('show');
                        $(".imvoice-message").html(res.message);*/

                        if($("#is_from_customer_view").val() == 'true')
                        {
                            /*$(".btn-generate-invoice").each(function (index, element) {
                                if($("#approval_memo_id").val() == $(element).data('id'))
                                {
                                    $(element).addClass('disabled');
                                    $(element).attr('disabled',true);
                                }
                            });*/
                            //memoListTable_oldest_approval.draw();
                            //memoListTable_newest_approval.draw();
                            $('#memoListTable_all_approval').DataTable().draw();
                            $('#approvalProductsTable').DataTable().draw();
                            setTimeout(function(){
                                var all_approval_invoice_val = $("#memoListTable_all_approval .btn-generate-invoice[data-id='"+$('#approval_memo_id').val()+"']").attr('class');
                                    $("#memoListTable_oldest_approval .btn-generate-invoice[data-id='"+$('#approval_memo_id').val()+"'], #memoListTable_newest_approval .btn-generate-invoice[data-id='"+$('#approval_memo_id').val()+"']").attr('class',all_approval_invoice_val);
                            }, 5000);
                        }
                        else
                        {
                            //console.log(typeof inventoryProductsTable);
                            if(typeof inventoryProductsTable !== 'undefined'){
                                inventoryProductsTable.draw();
                            }
                        }
                        /*setTimeout(function(){
                            $(".invoice-success-message").removeClass('show');
                            $(".invoice-success-message").addClass('hidden');
                        }, 5000);*/
                        $("#invoice-memo-modal").modal('hide');
                        hideLoader();
                        swal({
                          title: 'Success',
                          text: res.message,
                          type: 'success',
                          buttonClass: 'btn btn-primary'
                          //showSuccessButton: true,
                          //showConfirmButton: false,
                          //successButtonClass: 'btn btn-primary',
                          //successButtonText: 'Ok'
                        }).then(function() {
                              if($("#operation_type").val() == 'invoice')
                              {
                                window.location.href = '<?=URL::to('/inventory/invoicelist');?>';
                              }
                              else if($("#operation_type").val() == 'memo')
                              {
                                window.location.href = '<?=URL::to('/inventory/memolist');?>';
                              }
                        });
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
                        /*$(".customer-verification-alert").removeClass('hidden');
                        $(".customer-verification-alert").addClass('show');
                        $(".customer-verification-alert .customer-check-message").html(res.message);*/
                        /*setTimeout(function(){
                            $(".customer-verification-alert").removeClass('show');
                            $(".customer-verification-alert").addClass('hidden');
                        }, 5000);*/
                    }
                },
                error: function(){
                    hideLoader();
                }
            })
        }
        else
        {
            if($("#gstinattachment-error").length > 0)
            {
                $("#gstinattachment-error").addClass('gstin-attachment-error');
            }
        }
    });
});
</script>
<style type="text/css">.ui-autocomplete{z-index: 9999;}#invoice-memo-modal .ui-autocomplete {width: 94%!important;max-height: 370px;overflow-y: auto;}.form-control:disabled, .form-control[readonly] {background-color: #fff;}.gstin-field .error{margin-top: 6px !important;font-size: 13px !important;}</style>