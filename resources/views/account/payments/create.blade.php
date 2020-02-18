
<?php
?>@extends('layout.mainlayout')

@section('title', 'Payment Create')
@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=URL::to('/');?>/css/autocomplete.css"/>
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">


@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
    {{ Breadcrumbs::render('accountpayment.create') }}
    <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div class="widget-list">
    <!--- row start --->
    <div class="row">
      <!-- widget holder start -->
      <div class="col-md-12 widget-holder">
        <!-- widget bg start -->
        <div class="widget-bg">
          <div class="widget-body clearfix">
            <h5 class="box-title box-title-style mr-b-0">Create Payment  </h5>
            <p class="text-muted">You can add payment by filling this form</p>
            @if ($message = Session::get('success'))
            <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
              <button type="button" class="close alert-closebtn-style" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
              <i class="material-icons list-icon">check_circle</i>
              <strong>Success</strong>: {{ $message }}
            </div>
          @endif
          @if (count($errors) > 0)
                  <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                       @foreach ($errors->all() as $error)
                         <li>{{ $error }}</li>
                       @endforeach
                    </ul>
                  </div>
          @endif
          @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
              <i class="material-icons list-icon">check_circle</i>
              <strong>Error</strong>: {{ $message }}
            </div>
          @endif
            <div class="alert alert-icon alert-success border-success fade show" role="alert" id="print-success-msg" style="display: none;">
            </div>
            <div class="alert alert-icon alert-danger border-danger fade show" role="alert" id="print-error-msg" style="display: none;">
            </div>
            <form name="neworexistuserform" id="neworexistuserform">
              <!--- row start --->
              <div class="row">
                 <div class="col-lg-8 col-md-8">
                    <div class="radiobox radio-info mb-3">
                      <label class="mr-b-02">
                        <input type="radio" name="newuser" value="new" class="form-control new"><span class="label-text">New</span>
                      </label>
                      <label>
                        <input type="radio" name="newuser" value="existing" class="form-control existing" checked="checked">  <span class="label-text" >Existing</span>
                      </label>
                    </div>
                 </div>
              </div>
              <!--- radiobox row end --->
              <!--- newuser row start --->
              <div class="row form-group"  id="newuser">
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
                  <label>First Name</label>
                  <input placeholder="First Name" class="form-control required"  accept-charset="UTF-8" name="first_name" type="text">
                </div>
                <div class="col-lg-4 col-md-6 mb-3 ">
                    <label for="last_name">Last name</label>
                    <input type="text" class="form-control required" id="last_name" placeholder="Last name" name="last_name" >
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" class="form-control required" id="contact_number" placeholder="Contact Number" name="contact_number">
                    <div class="invalid-feedback">Please provide a valid city.</div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="address">Address</label>
                    <input type="text" class="form-control required" id="address" placeholder="Address"  name="address">
                    <div class="invalid-feedback">Please provide a valid state.</div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="validationServer05">Country</label>
                    <select class="form-control countrygetsel required" id="l13" name="country_id">
                          <option value="IN">India</option>
                    </select>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="validationServer05">State</label>
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
                    <input type="text" class="form-control" id="defsel" placeholder="State"  name="getintstate">
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                  <label for="city">City</label>
                  <input type="text" class="form-control required" id="city" placeholder="City" name="city">
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                  <label for="zip">Zip</label>
                  <input type="text" class="form-control required" id="zip" placeholder="Zip" name="zip">
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                  <label for="email">Email</label>
                  <input type="text" class="form-control required" id="email" placeholder="Email" name="email">
                </div>
                <div class="col-lg-4 col-md-6 mb-3 invoice_stockaction" >
                  <div id="button_block1">
                    <input id="add-customer" class="btn btn-success" type="submit" value="Add Customer" />
                  </div>
                </div>
              </div>
              <!--- newuser row end --->
              <!--- existing row start --->
              <div class="row" id="existing">
                <div class="col-lg-4 col-md-6 mb-3  email-field ">
                  <label class="required txtdmusercodeemail">DMUSERCODE or Email Address<span class="text-danger">*</span></label>
                  {!! Form::text('email', null, array('class' => 'required form-control txtdmusercodeemail','id'=>'email','autocomplete'=>'nope')) !!}
                  {!! Form::hidden('customerId', null, array('class' => 'form-control','id'=>'customerId')) !!}
                  <input class="cname" type="hidden" name="customer_name" value="" class="newcustomer_id">
                  <input class="cid" type="hidden" name="customer_id" value="">
                </div>
              </div>
              <!--- existing row end --->
            </form>
            {!! Form::open(array('route' => 'accountpayment.store','method'=>'POST','files'=>'true','id' => 'payment_form', 'class'=>'newuser' )) !!}
              <!--- Payment detail form row start --->
              <div class="row">
                <input id="invisible_id" name="created_by" type="hidden" value="{{Auth::user()->id}}">
                  <div class="col-lg-4 col-md-6">
                    <div class="form-group">
                      <label for="l30">Customer Name</label>
                      {!! Form::text('customer_name', null, array('placeholder' => 'Custumer Name','class' => 'form-control cname'  ,'readonly' => 'true','accept-charset'=>"UTF-8")) !!}
                      {!! Form::hidden('customer_id', null, array('placeholder' => 'custumer id','class' => 'form-control cid' ,'accept-charset'=>"UTF-8")) !!}
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6">
                      <div class="form-group" >
                        <label for="invoice_number">Invoice Number </label>
                        {!! Form::text('invoice_number', null,array('placeholder' => 'Invoice Number','class' => 'form-control','required')) !!}
                        <span class="text-danger">{{ $errors->first('invoice_number') }}</span>
                      </div>
                  </div>
                  <div class="col-lg-4 col-md-6">
                    <div class="form-group">
                        <label for="l30">Invoice Amount</label>
                        {!! Form::number('invoice_amount', null, array('placeholder' => 'Invoice Amount','class' => 'form-control','min'=>'0.00')) !!}
                      </div>
                  </div>
                  <div class="col-lg-4 col-md-6">
                    <div class="form-group">
                      <label for="l30">Due Date</label><br/>
                      {!! Form::text('due_date', null, array('class' => 'required form-control datepicker', 'id' => 'due_date', 'autocomplete' => 'off', 'data-plugin-options'=>'{"autoclose": true, "startDate": "tomorrow", "format": "yyyy-mm-dd"}')) !!}
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <div class="form-group">
                      <label for="l30">Payment Form</label>
                      {!! Form::select('payment_form', ['Incoming' => 'Incoming', 'Outgoing' => 'Outgoing'], null,array('class' => 'form-control ')) !!}
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6">
                    <div class="form-group">
                      <label for="l30">Invoice Attachment</label><br/>
                        <div class="input-group ">
                          <div class="input-group-btn width-90">
                            <div class="fileUpload btn w-100 btn-default">
                              <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
                              <input id="uploadBtn" type="file" class="upload width-90"  name="invoice_attachment"    accept="application/pdf,image/png,image/jpeg, image/jpg"/>
                            </div>
                          </div>
                          <input id="uploadFile"  class="form-control border bg-transparent" placeholder="Choose File" disabled="disabled">
                        </div>
                      <small>jpeg jpg png pdf can select as Attachment</small><br/>
                      </div>
                  </div>
                  <div class="col-lg-4 col-md-4 childdropdown">
                    <div class="form-group">
                      <label for="l30">Payment Sub Header</label>
                      <select name="payment_sub_type" class="form-control payment_child_header" id="sub_header">
                        <option value="" data-parent="">Select Payment Sub Header</option>
                        @foreach($paymenttype as $type)
                        <option value="{{ $type->id }}" data-parent="{{ $type->parent_id }}"> {{ $type->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6  payment">
                    <div class="form-group">
                      <label for="l30">Payment Parent Header</label>
                        <select name="payment_type" id="paymentvalue"  class="form-control " >
                          <option value="0">Select Parent Type</option>
                          <option vaue=""> </option>
                        </select>
                    </div>
                    <input name="customer_type" type="hidden" value="Website"/>
                  </div>
                </div>
               <!--- Payment detail form row end --->
               <!--- Remarks row start --->
                <div class="row">
                    <div class="col-lg-12 col-md-12  ">
                      <div class="form-group">
                          <strong>Remarks</strong>
                              {!! Form::textarea('remarks', null, array('placeholder' => 'Remarks','class' => 'form-control' , "rows"=>"3",'accept-charset'=>"UTF-8")) !!}
                      </div>
                    </div>
                </div>
                <!--- Remarks row end --->
                <!--- actions btn row start --->
                <div class="row">
                  <div class="col-12 form-actions btn-list">
                      <input type="submit" name="submit" class="btn btn-primary " id="btn-submitform" value="Submit" />
                      <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  $(document).ready( function() {
    /*$(document).on('change','input[name=radioIsFranchise]',function(){
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
    });*/

    $(".payment").hide();

    $('.emailcumdmcod').blur(function(){
      var email = $('.emailcumdmcod').val();
      $.ajax({
        url:'{{action("PaymentController@getemail")}}',
        method:"GET",
        data:{email:email},
        success:function(response)
        {

          //console.log(response.result[0].name);
          if(response.result == false){
            var errorHtml ='<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>'+
             '<i class="material-icons list-icon">highlight_off</i>'+
              '<strong>Error</strong> : '+ response.message;

              //<span aria-hidden="true">×</span>

            $("#print-error-msg").show();
            setTimeout(function(){ $("#print-error-msg").hide(); }, 3000);
            $("#print-success-msg").hide();
            $("#print-error-msg").html(errorHtml);
            $("#customer-form-container,#invoice-memo-modal .modal-footer").removeClass("hidden");
          }else{
            $(".cname").val(response.result[0].name);
            $(".cid").val(response.result[0].entity_id);
            var successHtml = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>'+
            '<i class="material-icons list-icon">check_circle</i>'+
            '<strong>Success</strong> : '+ response.message;
            // <span aria-hidden="true">×</span>
            $("#print-success-msg").show();
            setTimeout(function(){ $("#print-success-msg").hide(); }, 3000);
            $(".print-error-msg").hide();
            $("#print-success-msg").html(successHtml).show();
          }
        }
      });
    });
    $('#add-customer').click(function(e) {
      e.preventDefault();
      $.validator.addMethod("phoneno", function(phone_number, element) {
        phone_number = phone_number.replace(/\s+/g, "");
        return this.optional(element) || phone_number.length > 9 &&
        phone_number.match(/^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/);
      }, "<br />Please specify a valid phone number");
      var neworexistuserform = $("#neworexistuserform");
      neworexistuserform.validate({
        ignore: ":hidden",
        rules: {
          first_name: {
              required:true
          },
          last_name: {
              required:true
          },
          contact_number: {
              required:true,
                phoneno:true
          },
          address: {
              required: true
          },
          getstate: {
              required: true
          },
          city: {
              required: true,
              lettersonly:true
          },
          zip: {
              required: true
          },
          email: {
            required: true,
          },
          txtfrncode: {
            required: function(element){
                return $("input[name=radioIsFranchise]:checked").val()=="yes";
              }
          }
        },
        $messages: {
          contact_number:{
            required:"Contact number is Required",
            regx:"Enter only Numbers",
            minlength:"Minimum length is 10 ",
            maxlength:"Maximum length is 14"
          },
          getstate:{
            required:"State is Required"
          }
        }
      });

      var isValid = neworexistuserform.valid();
      //console.log(isValid);
      if(!isValid){
        return false;
      }

      //setting variables based on the input fields
      var first_name = $('input[name="first_name"]').val();
      var last_name = $('input[name="last_name"]').val();
      var contact_number = $('input[name="contact_number"]').val();
      var address= $('input[name="address"]').val();
      var country_id= $('select[name="country_id"]').val();
      var getstate= $('select[name="getstate"]').val();
      var getintstate= $('input[name="getintstate"]').val();
      var city= $('input[name="city"]').val();
      var zip= $('input[name="zip"]').val();
      var email= $('input[name="email"]').val();
      var radioIsFranchise= $('input[name="radioIsFranchise"]').val();
      var txtfrncode= $('#txtfrncode').val();
      var token = $('input[name="_token"]').val();
      var data = {first_name:first_name, last_name:last_name,contact_number:contact_number,address:address,country_id:country_id,getstate:getstate,getintstate:getintstate,city:city,zip:zip,email:email, radioIsFranchise:radioIsFranchise, txtfrncode:txtfrncode, token:token};
      var request = $.ajax({
        url: '{{action("PaymentController@customerstore")}}',
        type: "GET",
        data: data,
        dataType:"html"
      });
      request.done(function( response ) {
        console.log(JSON.parse(response).result.status)
        if(JSON.parse(response).result.status == false)
        {
          var errorHtml ='<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>'+
          '<i class="material-icons list-icon">highlight_off</i>'+
          '<strong>Error</strong> : '+ JSON.parse(response).result.message;
          //<span aria-hidden="true">×</span>
          $("#print-error-msg").show();
          var msgarea = $("#print-error-msg").offset().top - 100;
          $('html, body').animate({
              scrollTop: msgarea
          }, 1000);
          setTimeout(function(){ $("#print-error-msg").hide(); }, 5000);
          $("#print-error-msg").html(errorHtml);
        }else{
          $customer_id=JSON.parse(response).result.customer_id;
          $customer_name=JSON.parse(response).result.customer_name;
          var successHtml = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>'+
          '<i class="material-icons list-icon">check_circle</i>'+
          '<strong>Success</strong> : '+"User Created Successfully";
          //<span aria-hidden="true">×</span>
          $("#print-success-msg").show();
          var msgarea = $("#print-success-msg").offset().top - 100;
          $('html, body').animate({
              scrollTop: msgarea
          }, 1000);
          setTimeout(function(){ $("#print-success-msg").hide(); }, 5000);
          $("#print-success-msg").html(successHtml);
          $(".cid").val($customer_id);
          $(".cname").val($customer_name);

        }
      });
    });
    $(".payment").hide();
    $("#print-error-msg").hide();
    $("#print-success-msg").hide();
    $('#newuser').hide();
    $('#existing').hide();
    $('#amt').hide();
    $('#percent').hide();
    $('#defsel').hide();
    $('#existing').show();
    $(".existing-customer-field, .email-field").removeClass('hidden');
    $('.new').change(function(){
      if($('.new').val() == 'new')
      {
        $('#newuser').show();
        $('#existing').hide();
        $("#print-error-msg").hide();
        $("#print-success-msg").hide();
        $(".new-customer-field, .existing-customer-field").removeClass("hidden");
        $(".email-field").addClass('hidden');
        $("#btn-submitform").removeClass('disabled');
        $("#customerId").val('');
      }else{
        $('#newuser').hide();
      }
    });

    $('.existing').change(function(){
      if($('.existing').val() == 'existing')
      {
        $('#newuser').hide();
        $('#existing').show();
        $(".existing-customer-field, .email-field").removeClass('hidden');
        $(".new-customer-field").addClass('hidden');
        $("#btn-submitform").addClass('disabled');
      }else{
        $('#existing').hide();
        $("#print-error-msg").hide();
        $("#print-success-msg").hide();
      }
    });

    $('.countrygetsel').change(function(){
      if($('.countrygetsel').val() == 'IN'){
        $('#defsel').hide();
        $('#getstate').show();
      }else{
        $('#defsel').show();
        $('#getstate').hide();
      }
    });
    $('.getdiscount').change(function(){
      if($('.getdiscount').val() == 'percent'){
        $('#amt').hide();
        $('#percent').show();
      }
      if($('.getdiscount').val()==''){
        $('#amt').hide();
        $('#percent').hide();
      }
      else{

        $('#percent').hide();
        $('#amt').show();
        $('#').hide();
      }
    });

    $('.childdropdown').change(function(){
      $(".payment").hide();
      var payment_type = $('.payment_child_header option:selected', this).attr('data-parent');
     // alert(payment_type);
      if (payment_type != 0) {
        var request = $.ajax({
        url: '{{action("PaymentController@dropdown")}}',
        type: "GET",
        data: {payment_type:payment_type},
        dataType:"html"
      });
      request.done(function( response ) {

        if(JSON.parse(response).parent != ''){
          $('.payment_type').show();
          $(".payment").show();
          var parentdata = JSON.parse(response).parent;
          //console.log(data);
          var data = {parentdata};
          var formoption = "";
          $.each(data, function(v) {
            var optionval = data[v];
            formoption += "<option value='" + optionval.id + "'>" + optionval.name + "</option>";
          });
          $('#paymentvalue').html(formoption);
        }else{
          $(".payment").hide();
        }
      });
      }

    });
        /** custom upload file **/
    document.getElementById("uploadBtn").onchange = function () {
    document.getElementById("uploadFile").value = this.value.substring(12);
    document.getElementsByName("invoice_attachment").value = this.value.substring(50);
    };

    $("#payment_form").validate({
      ignore: ":hidden",
      rules: {
        customer_name: {
          required:true
        },
        invoice_number: {
          required:true
        },
        invoice_amount: {
          required: true,
          min:0
        },
        due_date: {
          required: true
        },
        payment_form: {
          required: true
        },
        invoice_attachment: {
          required: true,
          accept:"application/pdf,image/png,image/jpeg, image/jpg"
        },
        payment_sub_type: {
           required: function(element) {
                        return $("#sub_header").val() == '';
          }

        },
        payment_type: {
          required: true
        },
      },
    });

    $( ".txtdmusercodeemail" ).autocomplete({
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
      search: function(){
        $("#btn-submitform").addClass('disabled');
      },
      minLength:3,
      select: function (event, ui) {
        $("#customerId").val(ui.item.value);
        $(".cid").val(ui.item.value);
        $(this).val(ui.item.label);
        $("#btn-submitform").removeClass('disabled');
        return false;
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