@extends('layout.mainlayout')

@section('title', 'Diamond Invoice')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=URL::to('/');?>/css/autocomplete.css"/>
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css"/>
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css"/>
<link href="<?=URL::to('/');?>/css/autocatch.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
    {{ Breadcrumbs::render('diamond.generatediamondinvoicestore') }}
    <!-- /.page-title-right -->
    </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div id="msg"></div>
  <div class="widget-list">
    <div class="row">
      <div class="col-md-12 widget-holder">
        <div class="widget-bg">
          <div class="widget-heading clearfix">
            <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{' Generate Invoice'}}</h5>
          </div>

          @if ($message = Session::get('success'))
            <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
              <button type="button" class="close alert-closebtn-style" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
              <i class="material-icons list-icon">check_circle</i>
              <strong>Success</strong>: {{ $message }}
            </div>
          @endif
          @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
              <i class="material-icons list-icon">check_circle</i>
              <strong>Error</strong>: {{ $message }}
            </div>
          @endif

          <form name="neworexistuserform" id="neworexistuserform">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <div class="form-group col d-flex mr-b-0 mr-l-0 pd-l-0">
            <label class="col-form-label mr-2">Customer Type</label>
            <div class="radiobox radio-info">
              <label class="mr-2">
                {{ Form::radio('customerType', 'new' ,false) }}
                <span class="label-text">New</span>
              </label>
              <label>
                {{ Form::radio('customerType', 'existing' ,false) }} <span class="label-text">Existing</span>
              </label>
            </div>
          </div>
          <section id="customer-form-container" class="hidden">
            <h5 class="border-b-light-1 pb-2 mb-3">Customer Add</h5>
            <div class="row">
              <div class="col-md-4 mb-3 new-customer-field">
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
              <div class="col-md-4 mb-3 txtfrncode-input-div new-customer-field">
                <label for="txtfrncode" id="frncode_label">FRN Code <span class="text-danger">*</span></label>
                {!! Form::text('txtfrncode', null, array('class' => 'form-control required','id'=>'txtfrncode','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field">
                &nbsp;
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtfirstname">First Name <span class="text-danger">*</span></label>
                {!! Form::text('txtfirstname', null, array('class' => 'form-control required','id'=>'txtfirstname','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtlastname">Last Name <span class="text-danger">*</span></label>
                {!! Form::text('txtlastname', null, array('class' => 'form-control required','id'=>'txtlastname','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtcontactnumber">Contact Number <span class="text-danger">*</span></label>
                {!! Form::text('txtcontactnumber', null, array('class' => 'form-control required','id'=>'txtcontactnumber','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtaddress">Address <span class="text-danger">*</span></label>
                {!! Form::text('txtaddress', null, array('class' => 'required form-control','id'=>'txtaddress','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="selectcountry">Country <span class="text-danger">*</span></label>
                <select class="required form-control required" name="selectcountry" id="selectcountry" data-placeholder="Select">
                  <option value=""><?php echo "Select Country"; ?></option>
                    <?php foreach ($countryList as $value): ?>
                  <option value="<?php echo $value['country_id']; ?>"><?php echo $value['name'] ?></option>
                    <?php endforeach;?>
                </select>
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden customer-state">
                <label for="txtstateprovince">State/Province <span class="text-danger">*</span></label>
                {!! Form::text('txtstateprovince', null, array('class' => 'required form-control','id'=>'txtstateprovince','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtcity">City <span class="text-danger">*</span></label>
                {!! Form::text('txtcity', null, array('class' => 'required form-control','id'=>'txtcity','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtzipcode">Zip Code </label>
                {!! Form::text('txtzipcode', null, array('class' => 'form-control','id'=>'txtzipcode','maxlength'=>'6','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtemail">Email <span class="text-danger">*</span></label>
                {!! Form::email('txtemail', null, array('class' => 'required form-control','id'=>'txtemail','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                  <label for="txtgstin">GSTIN </label>
                  {!! Form::text('txtgstin', null, array('class' => 'form-control','id'=>'txtgstin','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                <label for="txtpancardnumber">Pancard Number </label>
                {!! Form::text('txtpancardnumber', null, array('class' => 'form-control','id'=>'txtpancardnumber','autocomplete'=>'nope')) !!}
              </div>
              <div class="col-md-4 mb-3 new-customer-field hidden">
                &nbsp;
              </div>
              <div class="col-lg-4 col-md-6 new-customer-field mb-3 invoice_stockaction" >
                <div id="button_block1">
                  <input id="btn-add-customer" class="btn btn-success btn-sm px-3" type="submit" value="Add Customer" />
                </div>
              </div>
              <div class="col-md-4 mb-3 email-field hidden">
                <label for="txtdmusercodeemail">DMUSERCODE or Email or Name <span class="text-danger">*</span></label>
                {!! Form::text('txtdmusercodeemail', null, array('class' => 'form-control','id'=>'txtdmusercodeemail','autocomplete'=>'nope')) !!}
                {!! Form::hidden('customerId', null, array('class' => 'form-control','id'=>'customerId')) !!}
              </div>
            </div>
        </form>
        {!! Form::open(array('route' => 'diamond.generatediamondinvoicestore','method'=>'POST', 'files'=>'true','id' => 'myform')) !!}
            <div class="row">
              <div class="col-lg-4 col-md-6">
                <div class="form-group">
                  <label for="customer_name">Customer Name</label>
                  {!! Form::text('customer_name', null, array('placeholder' => 'Customer Name','class' => 'required form-control cname','id'=>'customer_name'  ,'readonly' => 'true','accept-charset'=>"UTF-8")) !!}
                  {!! Form::hidden('customer_id', null, array('placeholder' => 'customer id','class' => 'required form-control cid' ,'id' => 'customer_id','accept-charset'=>"UTF-8")) !!}
                </div>
              </div>
              <div class="col-md-8 mb-12">
                <label for="txtpancardnumber">Description </label>
                {!! Form::textarea('description', null, array('class' => 'form-control','autocomplete'=>'nope','placeholder' => 'Cut & Polish diamond', 'rows'=>'2' )) !!}
              </div>
            </div>
            <h5 class="border-b-light-1 pb-2 mb-3">Invoice Detail</h5>
            <div  id="form-errors"></div>
            <div class="dynamicadd" id="dynamicadd">
              <div class="row mr-b-10 ">
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="form-group">
                    <!-- selected data over here $shape -->
                    <label for="l30">Diamond Shape</label>
                    {!! Form::text('stone_shape[]', null, array('data-commonid' =>'0','data-index' => '0', 'autocomplete' => 'off', 'class' => 'common_input required form-control position-relative stone_shape autocomplete_shape_txt','id'=>'search_stone_shape_text_0')) !!}
                    <input type="hidden" name="customShapeSuggestionsJson" id="customShapeSuggestionsJson" />
                  </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="form-group">
                    <!-- selected data over here $quality -->
                    <label for="l30">Diamond Quality</label>
                    {!! Form::text('diamond_quality[]', null, array('data-commonid' =>'0','autocomplete' => 'off', 'class' => 'common_input required form-control diamond_quality position-relative  autocomplete_quality_txt','id'=>'search_diamond_quality_text_0')) !!}
                    <input type="hidden" name="customQltSuggestionsJson" id="customQltSuggestionsJson" />
                  </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="form-group">
                    <label for="l30">MM Size</label>
                    <div class="input-group">
                      {!! Form::number('mm_size[]', null, array('placeholder' => 'MM Size','class' => 'form-control number-error mm_size required common_input','id' => 'mm_size_0','step' => '0.001','data-commonid'=>'0')) !!}
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="form-group">
                    <label for="l30">Sieve Size</label>
                    <div class="input-group">
                      {!! Form::number('sieve_size[]', null, array('placeholder' => 'Sieve Size','class' => 'form-control sieve_size common_input','id' => 'sieve_size_0','step' => '0.001','data-commonid'=>'0')) !!}
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="form-group position-relative">
                    <label for="l30">Diamond Weight (cts)<span class="text-danger">*</span></label>
                    {!! Form::number('diamond_weight[]', null, array('placeholder' => 'Diamond Weight','class' => 'required form-control weight_count common_input', 'step' => '0.001','id'=>'search_diamond_weight_text_0','data-commonid'=>'0')) !!}
                  </div>
                </div>
                <!-- <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="form-group">
                        <label for="l30">Pieces</label>
                        <div class="input-group">
                            {!! Form::number('pieces[]', null, array('placeholder' => 'Pieces','class' =>'form-control pieces','id' => 'pieces_0','readonly'=>true,'onfocus'=>'this.removeAttribute("readonly")')) !!}
                        </div>
                    </div>
                </div> -->
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="form-group">
                    <label for="l30">Discount</label>
                    <div class="input-group">
                      {!! Form::number('discount[]', null, array('placeholder' => 'Discount','class' => 'common_input form-control discount','id' => 'discount_0','data-commonid'=>'0')) !!}
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="form-group">
                      <label for="l30">Rate Type</label>
                      <select class="form-control selectedvalue_0" data-rate='0' id="rate_type_0">
                        <option value="">Select Rate Type</option>
                        <option value="Existing">Existing</option>
                        <option value="Custom">Custom</option>
                      </select>
                      <label style="display: none;" id="rate_validation" class="error">This field is required.</label>
                  </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 Existing box" id="existing_rate_0" style="display: none;">
                  <div class="form-group">
                    <label for="l30">Existing Rate</label>
                    {!! Form::number('price[]',null,array('class' => 'required form-control','readonly' => 'true','id'=>'rate_0')) !!}
                  </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 Custom box" id="custom_rate_0" style="display: none;">
                  <div class="form-group">
                    <label for="l30">Custom Rate</label>
                    {!! Form::number('price[]', null, array('class' => 'required form-control','id'=>'custom_0','min'=>'0.000')) !!}
                  </div>
                </div>
              </div>
              {{ Form::hidden('stone_shape_id[]', '', array('class' => 'shapeID', 'id' => 'shapeID_0')) }}
              {{ Form::hidden('diamond_quality_id[]', '', array('class' => 'qualityID', 'id' => 'qualityID_0')) }}
            </div>
            <div class="row">
              <div class="col-lg-3 col-md-3 col-sm-12">
                <div class="form-group">
                  <button type="button" name="add" id="add" class="btn btn-success">Add More</button>
                </div>
              </div>
            </div>
            <button class="btn btn-primary " id="btn_save" type="submit">Generate invoice</button>
            <input type="hidden" name="diamond_combination_are_repeated" id="diamond_combination_are_repeated" value="{{Config('constants.message.diamond_combination_are_repeated')}}">
            {!! Form::close() !!}
          </section>
        </div>
      </div>
    </div>
  </div>
</main>

@endsection
@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script src="<?=URL::to('/');?>/js/autocatch.js"></script>
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<script type="text/javascript">
  var i = 0;
  var MmToSieve = function (rowNumber)
  {
    var shapeid = $('#shapeID_'+rowNumber).val();
    var mm_size = $('#mm_size_'+rowNumber).val();
    var url = "{{ route('searchmmtosieveajax') }}";
    var result =  MmToSiveAjax(shapeid ,mm_size,rowNumber,url);
  }

  var SeiveToMmA = function (rowNumber)
  {
    var shapeid = $('#shapeID_'+rowNumber).val();
    var sieve_size = $('#sieve_size_'+rowNumber).val();
    var mm_size = $('#mm_size_'+rowNumber).val();
    var url = "{{ route('searchsievetommajax') }}";
    var result =  SiveToMmAjax(shapeid ,sieve_size,mm_size,rowNumber,url);
  }
  var weightValidation = function(rowNumber){
    var url ="{{action('DiamondController@diamondIssueCheck')}}";
    var stshape = $('#search_stone_shape_text_'+rowNumber).val();
    var dmquality = $('#search_diamond_quality_text_'+rowNumber).val();
    var dmsieve_size = $('#sieve_size_'+rowNumber).val();
    var dmmm_size = $('#mm_size_'+rowNumber).val();
    var totalwieght = $('#search_diamond_weight_text_'+rowNumber).val()
    var serverValidationWeight = validationWeight(stshape,dmquality,dmsieve_size,dmmm_size,totalwieght,url,rowNumber);
  }
  $(document).on('blur', '#search_diamond_weight_text_'+i, function(){
    weightValidation(i);
  });
  $(document).on('blur', '#mm_size_'+i, function(){
    MmToSieve(i);
  });
  $(document).on('blur', '#sieve_size_'+i, function(){
    SeiveToMmA(i);
  });
  $(document).on('blur','.common_input',function(){
    var id = $(this).attr('data-commonid');
    var shape = $("#shapeID_"+id).val();
    var qlty = $("#qualityID_"+id).val();
    var mmSize = $("#mm_size_"+id).val();
    var discount = $("#discount_"+id).val();
    var sieveSize = $("#sieve_size_"+id).val();
    if(sieveSize == "") { sieveSize = 0; }
    if(mmSize == "") { mmSize = 0; }
    $.ajax({
      type: 'post',
      url: '<?=URL::to('/diamond/getdiamondprice');?>',
      data:{shape:shape,quality:qlty,mm_size:mmSize,sieve_size:sieveSize,discount:discount,_token:"{{ csrf_token() }}"},
      success: function(response){
        if(response.status == 1) {
          //console.log(response.data);
          $('#rate_'+id+'').val(response.data);
        }
      }
    });
    /*setTimeout(function(){
      srcqlt = "{{ route('selectedinvoicequality') }}";
      $.ajax({
          url: srcqlt,
          dataType: "json",
          data: {
              term : '',
              shape:shape
          },
          success: function(data) {
              var myJSON = JSON.stringify(data);
              $('#customQltSuggestionsJson').val(myJSON);
          }
      });
   }, 10);*/
  });

  function getQualitiesFromShape($selectedShape = false){
    console.log('1111');
    console.log($selectedShape);
    var shape = $($selectedShape).val();
    console.log(shape);
    srcqlt = "{{ route('selectedinvoicequality') }}";
    $.ajax({
      url: srcqlt,
      dataType: "json",
      data: {
        term : '',
        shape:shape
      },
      success: function(data) {
        var myJSON = JSON.stringify(data);
        $('#customQltSuggestionsJson').val(myJSON);
      }
    });
  }

  $(document).ready(function(){

    $(document).on('change','input[name=radioIsFranchise]',function(){
      if(this.value == 'yes')
      {
        $('#frncode_label').html('FRN Code <span class="text-danger">*</span>');
        $('#txtfrncode').attr('required', true);
        $('#txtfrncode').addClass('required');
      }
      else
      {
        $('#frncode_label').html('FRN Code');
        $('#txtfrncode').attr('required', false);
        $('#txtfrncode').removeClass('required');
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
              url: '<?=URL::to('diamond/createcustomer');?>',
              data: $("#neworexistuserform").serialize(),
              beforeSend: function(){
            showLoader();
          },
              success: function(response){
                //console.log(response);
                //return false;
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

    $('#myform').validate({
      rules: {
        'discount[]':{
          min:0,
          max:99

        }
      }
    });
    $(document).on('change','input[name=customerType]',function(){
      $(document).on('change','select',".selectedvalue_"+i,function(){
        var id = $(this).attr('data-rate');
        $(this).find("option:selected").each(function(){
          var optionValue = $(this).attr("value");
          if(optionValue == "") {
            $('#custom_rate_'+i).hide();
            $('#existing_rate_'+i).hide();
            $('#custom_rate_'+i+' input').attr('disabled', 'disabled');
            $('#existing_rate_'+i+' input').attr('disabled', 'disabled');
            $('#rate_validation').show();
          }
          if(optionValue == "Existing"){
            $('#existing_rate_'+id).show();
            $('#custom_rate_'+id).hide();
            console.log($('#existing_rate_'+i+' input'));
            $('#existing_rate_'+i+' input').attr('disabled', false);
            $('#custom_rate_'+i+' input').attr('disabled', 'disabled');
            $('#custom_'+i).attr('disabled','disabled');
            $('#rate_'+i).attr('required', 'required');
            $('#rate_'+i).removeAttr("disabled");
            $('#rate_validation').hide();
          }
          if(optionValue == "Custom"){
            $('#custom_rate_'+id).show();
            $('#custom_rate_'+i+' input').attr('disabled', false);
            $('#rate_'+i).attr('disabled','disabled');
            $('#rate_'+i).removeAttr('required');
            $('#custom_'+i).removeAttr("disabled");
            $('#existing_rate_'+id).hide();
            $('#existing_rate_'+i+' input').attr('disabled', 'disabled');
            if($("#custom_"+i).val() == '') {
              $("#rate"+i).val("");
              $('#rate_validation').hide();
              $('#existing_rate_'+id).hide();
              if($("#custom_"+i).val() == "" ){
                $("#rate"+i).val("");
              }
            }
          }
        });
      }).change();
      $("#customer-form-container,#invoice-memo-modal .modal-footer").removeClass("hidden");
      if(this.value == 'new')
      {
        $(".new-customer-field, .existing-customer-field").removeClass("hidden");
        $(".email-field").addClass('hidden');
        $("#btn-genrate-invoicememo").removeClass('disabled');
        $("#customerId").val('');
        $("#customer_id").val('');
      }
      else if(this.value=='existing')
      {
        $(".existing-customer-field, .email-field").removeClass('hidden');
        $(".new-customer-field").addClass('hidden');
        $("#btn-genrate-invoicememo").addClass('disabled');
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
          }else{
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
            }else{
              $("#txtstateprovince").remove();
              $(".customer-state").append('<input type="text" class="form-control" id="txtstateprovince" name="txtstateprovince">');
            }
          }
        },
        error: function(){
          hideLoader();
          $("#btn-verify-customer").prop('disabled',false);
        }
      });
    });
    diamondIssueCheck(i);
    var url ="{{action('DiamondController@searchweight')}}";
    $('#add').click(function(){
      var isValid = false;
      var sizeValid = checkDiamondValidation(i);
      var inputValid = InputsValidation(i);
      var rateValid = $('#myform').validate().element("#rate_"+i);
      if(!isValid) {
        if(inputValid && sizeValid){
          isValid = true;
        }
      }

      if(isValid) {

        if(i == 1)
        {
          $('#add').attr('disabled', 'disabled');
        } else {
          $('#add').attr('disabled', false);
        }
        i++;
        diamondIssueCheck(i);
        var html = getHtml(i);
        html += getOtherHtml(i);
        $('#dynamicadd').append(html);
        $(document).on('blur', '#mm_size_'+i, function(){
          MmToSieve(i);
        });
        $(document).on('blur', '#sieve_size_'+i, function(){
          SeiveToMmA(i);
        });
        $(document).on('blur', '#search_diamond_weight_text_'+i, function(){
          weightValidation(i);
        });
      }
    });

    $(document).on('click', '.btn_remove', function(){
      var button_id = $(this).attr("id");
      $('#row'+button_id+'').remove();
      $('#add').attr('disabled', false);
      i--;
    });

   /* Append Code onClick of Addmore Button - end */

    $('#btn_save').click(function(e){
      e.preventDefault();
      var isValid = false;
      var combIsValid = true;
      var sizeValid = checkDiamondValidation(i);
      var inputValid = InputsValidation(i);
      var rateTypeValid = $('#myform').validate().element("#rate_type_"+i);
      // console.log($(".selectedvalue_"+i).val());
      if ($(".selectedvalue_"+i).val() == "") {
        $('#rate_validation').show();
      }else{
        $('#rate_validation').hide();
      }
      if ($("#rate_"+i).val() == "") {
        $('#custom_validation').show();
      }else{
        $('#custom_validation').hide();
      }
      if(!isValid) {
        $('#myform').validate({
          rules: {
            txtemail: {
              required: true,
              email: true
            },
            txtzipcode:{
              required: true,
              number: true,
              maxlength: 6,
              minlength: 6
            },
            txtcontactnumber:{
              required: true,
              number: true,
              maxlength: 13
            },
            customer_name:{
              required: true,
            },
            customer_id:{
              required: true,
            },
            txtfrncode: {
              required: function(element){
                  return $("input[name=radioIsFranchise]:checked").val()=="yes";
                }
            },
          }
        });
        var rateValid = $('#myform').validate().element("#rate_"+i);
        var txtfirstname = $('#myform').validate().element("#txtfirstname");
        var txtlastname = $('#myform').validate().element("#txtlastname");
        var txtcontactnumber = $('#myform').validate().element("#txtcontactnumber");
        var txtfrncode = $('#myform').validate().element("#txtfrncode");
        var customer_name = $('#myform').validate().element("#customer_name");
        var txtaddress = $('#myform').validate().element("#txtaddress");
        var selectcountry = $('#myform').validate().element("#selectcountry");
        var txtstateprovince = $('#myform').validate().element("#txtstateprovince");
        var txtcity = $('#myform').validate().element("#txtcity");
        var txtemail = $('#myform').validate().element("#txtemail");
        //var txtdmusercodeemail= $('#myform').validate().element('#txtdmusercodeemail');
        if(!txtfirstname) {
          $("#txtfirstname").focus();
        } else if(!txtlastname){
          $("#txtlastname").focus();
        } else if(!customer_name){
          $("#customer_name").focus();
        } else if(!txtcontactnumber){
          $("#txtcontactnumber").focus();
        } else if(!txtfrncode){
          $("#txtfrncode").focus();
        } else if(!txtaddress){
          $("#txtaddress").focus();
        } else if(!selectcountry){
          $("#selectcountry").focus();
        } else if(!txtstateprovince){
          $("#txtstateprovince").focus();
        } else if(!txtcity){
          $("#txtcity").focus();
        } else if(!txtemail){
          $("#txtemail").focus();
        }/* else if(!txtdmusercodeemail){
          $("#txtdmusercodeemail").focus();
        }*/
        if(sizeValid && inputValid && txtfirstname && txtlastname && txtcontactnumber && txtfrncode && txtaddress && selectcountry && txtstateprovince && txtcity && txtemail && customer_name){
            if(inputValid) {
              $('#myform').submit();
            }
        }
      }
    });

    jQuery('#search_text').autocatch({
        //'currentSelector': '#search_text',
        'jsonData': '#customSuggestionsJson',
        'suggestionRenderer': '#customSuggestions',
        'idElem': '#venID',
        'txtElem': '#vendorName',
    });
    srcshpe = "{{ route('searchinvoiceshape') }}";
    $.ajax({
        url: srcshpe,
        dataType: "json",
        data: {
            term : '', //$(this).val()
        },
        success: function(data) {
            //console.log(data);
            var myJSON = JSON.stringify(data);
            $('#customShapeSuggestionsJson').val(myJSON);
            //response(data);
        }
    });

    jQuery('#search_stone_shape_text_0').autocatch({
      //'currentSelector': '#search_text',
      'jsonData': '#customShapeSuggestionsJson',
      'suggestionRenderer': '#customShapeSuggestions',
      'idElem': '#shapeID_0',
      'callback': 'getQualitiesFromShape',
      'callbackParam1': '#shapeID_0',
    });

    srcqlt = "{{ route('searchinvoicequality') }}";
    $.ajax({
        url: srcqlt,
        dataType: "json",
        data: {
            term : '',
        },
        success: function(data) {
            var myJSON = JSON.stringify(data);
            $('#customQltSuggestionsJson').val(myJSON);
        }
    });

    jQuery('#search_diamond_quality_text_0').autocatch({
        'jsonData': '#customQltSuggestionsJson',
        'suggestionRenderer': '#customQltSuggestions',
        'idElem': '#qualityID_0',
    });

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
      search: function(){
        $("#btn-genrate-invoicememo").addClass('disabled');
      },
      minLength:3,
      select: function (event, ui) {
      $("#customerId").val(ui.item.value);
      $("#customer_id").val(ui.item.value);
      $(this).val(ui.item.label);
      $('#customer_name').val(ui.item.label);
      $("#btn-genrate-invoicememo").removeClass('disabled');
      return false;
      }
    });
    function getOtherHtml(i) {
      var Html =
      '<div class="col-lg-3 col-md-3 col-sm-12">'+
      '<div class="form-group">'+
      '<label for="l30">Discount(%)</label>'+
      '<input data-commonid="'+i+'" placeholder="Discount" class="common_input form-control discount" step="0.001" id="discount_'+i+'" name="discount[]" min="0.000" max="99" type="number" >'+
      '</div></div>'+
      '<div class="col-lg-3 col-md-3 col-sm-12">'+
      '<div class="form-group">'+
      '<label for="l30">Rate Type</label>'+
      '<select class="form-control selectedvalue_'+i+'" data-rate = '+i+' id="rate_type_'+i+'">'+
      '<option value="">Select Rate Type</option>'+
      '<option value="Existing">Existing</option>'+
      '<option value="Custom">Custom</option>'+
      '</select>'+
      '</div></div>'+
      '<div class="col-lg-3 col-md-3 col-sm-12 Existing box" id="existing_rate_'+i+'" style="display:none;">'+
      '<div class="form-group ">'+
      '<label for="l30">Existing Rate</label>'+
      '<input placeholder="Price" class="required form-control" step="0.001" name="price[]" type="number" id="rate_'+i+'" disabled="disabled" readonly=true >'+
      '</div></div>'+
      '<div class="col-lg-3 col-md-3 col-sm-12 Custom box" id="custom_rate_'+i+'" style="display:none;">'+
      '<div class="form-group">'+
      '<label for="l30">Custom Rate</label>'+
      '<input type="number" name="price[]" min="0.000" id="custom_'+i+'" class="required form-control" >'+
      '</div></div>'+
      '<input class="shapeID" id="shapeID_'+i+'" name="stone_shape_id[]" type="hidden" value="">'+
      '<input class="qualityID" id="qualityID_'+i+'" name="diamond_quality_id[]" type="hidden" value="">'+
      '<div class="w-100 text-right px-3">'+'<button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove px-3 py-1 fs-13">Remove</button>'+'</div><script type="text/javascript">jQuery(\'#search_stone_shape_text_'+i+'\').autocatch({\'jsonData\': \'#customShapeSuggestionsJson\',\'suggestionRenderer\': \'#customShapeSuggestions_'+i+'\',\'idElem\': \'#shapeID_'+i+'\',});jQuery(\'#search_diamond_quality_text_'+i+'\').autocatch({\'jsonData\': \'#customQltSuggestionsJson\',\'suggestionRenderer\': \'#customQltSuggestions_'+i+'\',\'idElem\': \'#qualityID_'+i+'\',});\'function imposeMinMax(el){if(el.value != ""){if(parseInt(el.value)< parseInt(el.min)){el.value = el.min;}if(parseInt(el.value) > parseInt(el.max)){el.value = el.max;}}}\'<\/script><script src="<?=URL::to('/');?>/js/common.js" ><\/script>';
      return Html;
    }
    function imposeMinMax(el){
      if(el.value != ""){
        if(parseInt(el.value) < parseInt(el.min)){
          el.value = el.min;
        }
        if(parseInt(el.value) > parseInt(el.max)){
          el.value = el.max;
        }
      }
    }
    function diamondIssueCheck(i) {

      $(document).on('blur','#sieve_size_'+i,function(){
        var stshape = $('#search_stone_shape_text_'+i).val();
        var dmquality = $('#search_diamond_quality_text_'+i).val();
        var dmsieve_size = $('#sieve_size_'+i).val();
        var dmmm_size = $('#mm_size_'+i).val();
        $.ajax({
          url: "{{action('DiamondController@diamondIssueCheck')}}",
          data: {shape:stshape,
            quality:dmquality,
            sieve_size:dmsieve_size,
            mm_size:dmmm_size,
          },
          success: function(result){
            if (result.success == true) {
              $('#search_diamond_weight_text_'+i).val(result.result);
              $("#form-errors").empty();
            } else{
              var contentDiv = "<div class='alert alert-danger'>";
              contentDiv += '<label>' + result.data + '</label>';
              contentDiv += '</div>';
              $('#search_diamond_weight_text_'+i).val('');
              $( '#form-errors' ).html( contentDiv );
              $("#form-errors").show();
              setTimeout(function(){ $("#form-errors").hide(); }, 3500);
            }
          }
        });
      });
    }
  });
</script>
@endsection