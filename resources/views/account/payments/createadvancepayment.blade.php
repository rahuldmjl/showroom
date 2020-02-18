@extends('layout.mainlayout')

@section('title', 'Create Advance Payment')
@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=URL::to('/');?>/css/autocomplete.css"/>
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
	<div class="row page-title clearfix">
	    {{ Breadcrumbs::render('accountpayment.create') }}
	    <!-- /.page-title-right -->
	</div>
	<div class="col-md-12 widget-holder loader-area" style="display: none;">
	    <div class="widget-bg text-center">
	      <div class="loader"></div>
	    </div>
	  </div>
	<div class="widget-list">
	    <!--- row start --->
	    <div class="row">
	      <!-- widget holder start -->
	      <div class="col-md-12 widget-holder">
	        <!-- widget bg start -->
	        <div class="widget-bg">
	          <div class="widget-body clearfix">
	            <h5 class="box-title box-title-style mr-b-0">Create Advance Payment  </h5>
	            <p class="text-muted">You can add Payment  by filling this form</p>
	            @if ($message = Session::get('success'))
	            <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	              <button type="button" class="close alert-closebtn-style" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	              <i class="material-icons list-icon">check_circle</i>
	              <strong>Success</strong>: <span id="customer_success_message"></span>
	            </div>
	          @endif
	          @if ($message = Session::get('error'))
	            <div class="alert alert-danger alert-dismissible fade show" role="alert">
	              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	              <i class="material-icons list-icon">check_circle</i>
	              <strong>Error</strong>: <span id="customer_error_message"></span>
	            </div>
	          @endif
	            <div class="alert alert-icon alert-success border-success fade show" role="alert" id="print-success-msg" style="display: none;">
	            	<i class="material-icons list-icon">check_circle</i>
		              <strong>Success</strong>: <span id="customer_success_message"></span>
	            </div>
	            <div class="alert alert-danger fade show" role="alert" id="print-error-msg" style="display: none;">
	            	<i class="material-icons list-icon">check_circle</i>
		              <strong>Error</strong>: <span id="customer_error_message"></span>
	            </div>
	            <form name="neworexistuserform" id="neworexistuserform">
	            	<input type="hidden" name="_token" value="{{ csrf_token() }}">
	              <!--- row start --->
	              <div class="row ">
	                 <div class="col-lg-8 col-md-8">
	                    <div class="radiobox radio-info mb-3">
	                      <label class="mr-b-02">
	                        <input type="radio" name="customerType" value="new" class="form-control new"><span class="label-text">New</span>
	                      </label>
	                      <label>
	                        <input type="radio" name="customerType" value="existing" class="form-control existing" checked="checked">  <span class="label-text" >Existing</span>
	                      </label>
	                    </div>
	                 </div>
	              </div>
	              <!--- radiobox row end --->
	              <!--- newuser row start --->
	              <div class="row form-group hidden medium-input" id="new-customer-field">
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
	                <div class="col-md-4 mb-3 txtfrncode-input-div">
	                  <label for="txtfrncode" id="frncode_label">FRN Code <!-- <span class="text-danger">*</span> --></label>
	                  {!! Form::text('txtfrncode', null, array('class' => 'form-control','id'=>'txtfrncode','autocomplete'=>'nope')) !!}
	                </div>
	                <div class="col-md-4 mb-3">
	                  &nbsp;
	                </div>
	                <div class="col-lg-4 col-md-6 mb-3">
	                  <label for="first_name">First Name <span class="text-danger">*</span></label>
	                  <input placeholder="First Name" class="form-control required"  name="first_name" type="text" id="first_name" autocomplete="nope">
	                </div>
	                <div class="col-lg-4 col-md-6 mb-3 ">
	                    <label for="last_name">Last name <span class="text-danger">*</span></label>
	                    <input type="text" class="form-control required" id="last_name" placeholder="Last name" name="last_name" autocomplete="nope">
	                </div>
	                <div class="col-lg-4 col-md-6 mb-3">
	                    <label for="contact_number">Contact Number <span class="text-danger">*</span></label>
	                    <input type="text" class="form-control required" id="contact_number" placeholder="Contact Number" name="contact_number" autocomplete="nope">
	                </div>
	                <div class="col-lg-4 col-md-6 mb-3">
	                    <label for="address">Address <span class="text-danger">*</span></label>
	                    <input type="text" class="form-control required" id="address" placeholder="Address"  name="address" autocomplete="nope">
	                </div>
	                <div class="col-lg-4 col-md-6 mb-3">
	                    <label for="validationServer05">Country <span class="text-danger">*</span></label>
	                    <select class="form-control countrygetsel required" id="country_id" name="country_id">
	                          <option value="IN">India</option>
	                    </select>
	                </div>
	                <div class="col-lg-4 col-md-6 mb-3">
	                    <label for="validationServer05">State <span class="text-danger">*</span></label>
	                    <select class="form-control required" name="getstate" id="getstate">
	                      <option value="">Please select region, state or province</option>
	                      <option value="515">Andaman and Nicobar Islands</option><option value="485">Andhra Pradesh</option>
	                      <option value="486">Arunewusernachal Pradesh</option>
	                      <option value="487">Assam</option>
	                      <option value="488">Bihar</option>
	                      <option value="516">Chandigarh</option>
	                      <option value="489">Chhattisgarh</option>
	                      <option value="517">Dadra and Nagar Haveli</option>
	                      <option value="518">Daman and Diu</option>
	                      <option value="519">Delhi</option>
	                      <option value="490">Goa</option>
	                      <option value="491">Gujarat</option>
	                      <option value="492">Haryana</option>
	                      <option value="493">Himachal Pradesh</option>
	                      <option value="494">Jammu and Kashmir</option>
	                      <option value="495">Jharkhand</option>
	                      <option value="496">Karnataka</option>
	                      <option value="497">Kerala</option>
	                      <option value="520">Lakshadweep</option>
	                      <option value="498">Madhya Pradesh</option>
	                      <option value="499">Maharashtra</option>
	                      <option value="500">Manipur</option>
	                      <option value="501">Meghalaya</option>
	                      <option value="502">Mizoram</option>
	                      <option value="503">Nagaland</option>
	                      <option value="504">Orissa</option>
	                      <option value="521">Pondicherry</option>
	                      <option value="505">Punjab</option>
	                      <option value="506">Rajasthan</option>
	                      <option value="507">Sikkim</option>
	                      <option value="513">Tamil Nadu</option>
	                      <option value="508">Tamil Nadu</option>
	                      <option value="509">Tripura</option>
	                      <option value="514">Tripura</option>
	                      <option value="511">Uttar Pradesh</option>
	                      <option value="510">Uttarakhand</option>
	                      <option value="512">West Bengal</option>
	                    </select>
	                </div>
	                <div class="col-lg-4 col-md-6 mb-3">
	                  <label for="city">City <span class="text-danger">*</span></label>
	                  <input type="text" class="form-control required" id="city" placeholder="City"  name="city" autocomplete="nope">
	                </div>
	                <div class="col-lg-4 col-md-6 mb-3">
	                  <label for="zip">Zip <span class="text-danger">*</span></label>
	                  <input type="text" class="form-control required" id="zip" placeholder="Zip" maxlength="6" name="zip" autocomplete="nope">
	                </div>
	                <div class="col-lg-4 col-md-6 mb-3">
	                  <label for="email">Email <span class="text-danger">*</span></label>
	                  <input type="email" class="form-control required" id="email" placeholder="Email"  name="email" autocomplete="nope">
	                </div>
	                <div class="col-lg-4 col-md-6 mb-3 invoice_stockaction" >
	                  <div id="button_block1">
	                    <input id="btn-add-customer" class="btn btn-success btn-sm px-3" type="submit" value="Add Customer" />
	                  </div>
	                </div>
	              </div>
	              <!--- newuser row end --->
	              <!--- existing row start --->
	              <div class="row medium-input" id="existing-customer-field">
	                <div class="col-lg-4 col-md-6 mb-3  email-field ">
	                  <label class="required txtdmusercodeemail" for="email">DMUSERCODE or Email Address<span class="text-danger">*</span></label>
	                  {!! Form::text('txtdmusercodeemail', null, array('class' => 'required form-control txtdmusercodeemail','id'=>'txtdmusercodeemail','autocomplete'=>'nope')) !!}
	                  {!! Form::hidden('customerId', null, array('class' => 'form-control','id'=>'customerId')) !!}
	                  <input class="cname" type="hidden" name="customer_name" value="" class="newcustomer_id">
	                </div>
	              </div>
	              <!--- existing row end --->
	            </form>
	            {!! Form::open(array('route' => 'accountpayment.store','method'=>'POST','files'=>'true','id' => 'payment_form', 'class'=>'newuser' )) !!}
	              <!--- Payment detail form row start --->
	              <div class="row medium-input">
	                <input id="invisible_id" name="created_by" type="hidden" value="{{Auth::user()->id}}">
	                  <div class="col-lg-4 col-md-6">
	                    <div class="form-group">
	                      <label for="customer_name">Customer Name</label>
	                      {!! Form::text('customer_name', null, array('placeholder' => 'Customer Name','class' => 'form-control cname','id'=>'customer_name'  ,'readonly' => 'true','accept-charset'=>"UTF-8")) !!}
	                      {!! Form::hidden('customer_id', null, array('placeholder' => 'customer id','class' => 'form-control cid' ,'id' => 'customer_id','accept-charset'=>"UTF-8")) !!}
	                    </div>
	                  </div>
	                  <div class="col-lg-4 col-md-6">
	                    <div class="form-group">
	                        <label for="invoice_amount">Invoice Amount <span class="text-danger">*</span></label>
	                        {!! Form::number('invoice_amount', null, array('placeholder' => 'Invoice Amount','class' => 'form-control','min'=>'0.00', 'id' => 'invoice_amount', 'autocomplete'=>'nope')) !!}
	                      </div>
	                  </div>
					  <div class="col-lg-4 col-md-6">
	                    <div class="form-group">
	                        <label for="status">Status <span class="text-danger">*</span></label>
	                        {!! Form::select('status',array(''=>'Select','Cash Paid'=>'Cash Paid','Bank Paid'=>'Bank Paid'),[], array('class' => 'form-control', 'id' => 'status')) !!}
	                      </div>
	                  </div>
	                </div>
	               <!--- Payment detail form row end --->
	               <!--- Remarks row start --->
	                <div class="row">
	                    <div class="col-lg-12 col-md-12">
	                      <div class="form-group">
	                          <label for="remarks">Remarks <span class="text-danger">*</span></label>
	                              {!! Form::textarea('remarks', null, array('placeholder' => 'Remarks','class' => 'form-control', 'id' => 'remarks', "rows"=>"3",'accept-charset'=>"UTF-8", 'autocomplete'=>'nope')) !!}
	                      </div>
	                    </div>
	                </div>
	                <!--- Remarks row end --->
	                <!--- actions btn row start --->
	                <div class="row">
	                  <div class="col-12 form-actions btn-list">
	                      <input type="submit" name="submit" class="btn btn-primary disabled btn-sm px-3" id="btn-submitform" value="Submit" disabled/>
	                      <button class="btn btn-outline-default btn-sm px-3" onclick="goBack()" type="reset">Cancel</button>
	                  </div>
	                </div>
	                <!--- actions btn row end --->
	              </div>
	              <!--- row end --->
	              {!! Form::close() !!}
	        </div>
	        <!-- widget body -->
	      </div>
	      <!-- widget bg end -->
	    </div>
	    <!-- widget holder end -->
  </div>
  <!-- row end -->
  <!-- /.widget-list -->
</main>
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<script src="<?=URL::to('/');?>/js/additional-methods.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?=URL::to('/');?>/js/common.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
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
            $("#btn-submitform").addClass('disabled');
         },
          minLength:3,
          select: function (event, ui) {console.log(ui.item.value);
               $("#customer_id").val(ui.item.value);
               $(this).val(ui.item.label);
               $("#customer_name").val(ui.item.label);
               $("#customerId").val(ui.item.value);
               $("#btn-submitform").removeClass('disabled');
               $("#btn-submitform").prop('disabled',false);
               return false;
          }
    });
		$(".payment").hide();
		$("#payment_form").submit(function(event){
			event.preventDefault();
			$("#payment_form").validate({
	            rules: {
	                customer_name: "required",
					status: "required",
	                invoice_amount:{
	                    required: true,
	                    number: true,
	                    min: 1
	                },
	                remarks: "required"
	            },
	            messages: {
	                customer_name: "Name is required",
	                status: "Status is required",
	                invoice_amount:{
	                    required: "Amount is required",
	                    number: "Invalid amount",
	                    min: "Invalid amount"
	                },
	                remarks: "Remarks is required"
	            }
	        });
	        if($("#payment_form").valid())
	        {
	        	$.ajax({
	        		type: 'post',
	        		url: '<?=URL::to('account/payments/storeadvancepayment');?>',
	        		data: $("#payment_form").serialize(),
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
	                        });
	                        $('#neworexistuserform').trigger("reset");
	                        $('#payment_form').trigger("reset");
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
	        	});
	        }
		});
		$("#btn-add-customer").click(function(event){
			event.preventDefault();
	        $("#neworexistuserform").validate({
	            rules: {
	                first_name: "required",
	                last_name: "required",
	                contact_number:{
	                    required: true,
	                    number: true,
	                    maxlength: 13
	                },
	                address: "required",
	                country_id: "required",
	                getstate: "required",
	                city: "required",
	                zip:{
	                    required: true,
	                    number: true,
	                    maxlength: 6,
	                    minlength: 6
	                },
	                email: {
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
	                first_name: "First name is required",
	                last_name: "Last name is required",
	                contact_number:{
	                    required: "Contact number is required",
	                    number: "Invalid contact number",
	                    maxlength: "Invalid contact number"
	                },
	                address: "Address is required",
	                country_id: "Country is required",
	                getstate: "State/Province is required",
	                city: "City is required",
	                zip:{
	                    required: "Zip code is required",
	                    number: "Invalid zip code"
	                },
	                email:{
	                    required: "Email is required",
	                    email: "Invalid email"
	                },
	                txtfrncode: "FRN Code is required",
	            }
	        });
	        if($("#neworexistuserform").valid())
	        {
	        	$.ajax({
	        		type: 'post',
	        		url: '<?=URL::to('account/payments/createcustomer');?>',
	        		data: $("#neworexistuserform").serialize(),
	        		beforeSend: function(){
						showLoader();
					},
	        		success: function(response){
	        			hideLoader();
	        			var res = JSON.parse(response);
	        			if(res.status)
	        			{
	        				$("#customer_name").val(res.customer_name);
	        				$("#customer_id").val(res.customer_id);

	        				$("#btn-submitform").removeClass('disabled');
	        				$("#btn-submitform").removeAttr('disabled');
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
	        			setTimeout(function(){ $(".alert").hide() }, 5000);
	        		}
	        	});
	        }
		});
		$(document).on('change','input[name=customerType]',function(){
			if(this.value == 'new')
			{
				$("#new-customer-field").removeClass('hidden');
				$("#existing-customer-field").addClass('hidden');
			}
			else if(this.value=='existing')
			{
				$("#new-customer-field").addClass('hidden');
				$("#existing-customer-field").removeClass('hidden');
			}
		});
		$('.txtdmusercodeemail').blur(function(){
      var cust_id = $("#customerId").val();
      $.ajax({
        url:  "{{ action('PaymentController@getname') }}",
        type: 'POST',
        dataType: "json",
        data: {
        custID :cust_id,
        _token:"{{ csrf_token() }}"
        },
        success: function( response ) {
        console.log(response.result);
          $(".cname").val(response.result);
        }
      });
    });
  });

</script>
@endsection

