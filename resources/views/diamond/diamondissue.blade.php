@extends('layout.mainlayout')

@section('title', 'Diamond Issue')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/css/autocatch.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('diamond.diamondissue') }}
        <!-- /.page-title-right -->
    </div>
    <!-- /.page-title -->
    <!-- =================================== -->
    <!-- Different data widgets ============ -->
    <!-- =================================== -->
    <div class="widget-list">
        <div class="row">
            <div class="col-md-12 widget-holder">
                <div class="widget-bg">
                    <div class="widget-body clearfix">
                        <h5 class="box-title box-title-style mr-b-0">Add Diamond Issue</h5>
                        <p class="text-muted">You can add issue by filling this form</p>
                        <div  id="form-errors"></div>
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
                        <div class="alert alert-danger">
                         <strong>Error</strong>: {{ $message }}
                        </div>
                      @endif
         @if (Session::has('success'))
            <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <i class="material-icons list-icon">check_circle</i>
                 <strong>Success</strong> :
                {!!Session::get('success')!!}.<br><br>
            </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            @foreach ($errors->all() as $error)
            <i class="material-icons list-icon">check_circle</i>
             <strong>Error</strong>: {{ $error }}
             <br/>
            @endforeach
        </div>
        @endif

                        {!! Form::open(array('route' => 'diamond.diamondissuestore','method'=>'POST', 'files'=>'true','id' => 'myform')) !!}

                        <div class="dynamicadd" id="dynamicadd">
                            <div class="row">
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Shape</label>
                                         {!! Form::text('stone_shape[]', null, array('data-commonid' =>'0','data-index' => '0', 'autocomplete' => 'off', 'class' => 'common_input required form-control position-relative stone_shape autocomplete_shape_txt','id'=>'search_stone_shape_text_0')) !!}
                                        <input type="hidden" name="customShapeSuggestionsJson" id="customShapeSuggestionsJson" />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Quality</label>
                                        {!! Form::text('diamond_quality[]', null, array('autocomplete' => 'off', 'class' => 'required form-control diamond_quality autocomplete_quality_txt','id'=>'search_diamond_quality_text_0')) !!}
                                        <input type="hidden" name="customQltSuggestionsJson" id="customQltSuggestionsJson" />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">MM Size</label>
                                        <div class="input-group">
                                            {!! Form::number('mm_size[]', null, array('placeholder' => 'MM Size','class' => 'form-control number-error mm_size', 'step' => '0.01','id' => 'mm_size_0','min'=>'0.000')) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Sieve Size</label>
                                        <div class="input-group">
                                            {!! Form::number('sieve_size[]', null, array('placeholder' => 'Sieve Size','class' => 'form-control number-error sieve_size', 'step' => '0.01','id' => 'sieve_size_0')) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Weight</label>
                                        {!! Form::number('diamond_weight[]', null, array('placeholder' => 'Diamond Weight','class' => 'required form-control', 'step' => '0.001','min' => '0.001','id' => 'search_diamond_weight_text_0' )) !!}
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Pieces</label>
                                        <div class="input-group">
                                            {!! Form::number('pieces[]', null, array('placeholder' => 'Pieces','class' => 'form-control', 'id' => 'pieces_0','min'=>'0.000')) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Rate Type</label>
                                            <select class="required form-control selectedvalue_0" data-rate='0' id="rate_type_0">
                                                <option value="">Select Rate Type</option>
                                                <option value="Existing">Existing</option>
                                                <option value="Custom">Custom</option>
                                            </select>
                                    </div>
                                </div>

                              <input type="hidden" name="rate[]" value="" id="stonerate_0">
                              <div class="col-lg-3 col-md-3 col-sm-12 Existing box" id="existing_rate_0" style="display: none;">
                                <div class="form-group">
                                    <label for="l30">Existing Rate</label>
                                    {!! Form::number('existing_rate[]',null,array('class' => 'required form-control','readonly' => 'true','id'=>'rate_0')) !!}

                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-12 Custom box" id="custom_rate_0" style="display: none;">
                                <div class="form-group">
                                    <label for="l30">Custom Rate</label>
                                    {!! Form::number('custom_rate[]', null, array('class' => 'required form-control','id'=>'custom_0','min'=>'0.000')) !!}
                                </div>
                            </div>

                            {{ Form::hidden('stone_shape_id[]', '', array('class' => 'shapeID', 'id' => 'shapeID_0')) }}
                            {{ Form::hidden('diamond_quality_id[]', '', array('class' => 'qualityID', 'id' => 'qualityID_0')) }}
                            {{ Form::hidden('custom_diamond_quality_id[]', '', array('class' => 'qualityID', 'id' => 'custom_qualityID_0')) }}
                            </div>
                            <!-- start adjustable fields html -->
                            <div class="row">
                                 <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Adjustable</label><br/>
                                        <input type="checkbox" data-adjustableid="0" name="adjustable_chk[]" id="adjustable_chk_0" class="form-group custom_common">
                                        <input type="hidden" value="0"  name='custom_chk[]' id='custom_chk_0'>
                                    </div>
                                </div>
                                    <div class="col-lg-3 col-md-3 col-sm-12 adjustable_input_fields_0" style="display: none;">
                                        <div class="form-group">
                                            <label for="l30">Custom Diamond Quality</label>
                                            {!! Form::text('custom_diamond_quality[]', null, array('autocomplete' => 'off', 'class' => 'required form-control custom_diamond_quality autocomplete_quality_txt','id'=>'custom_diamond_quality_text_0')) !!}
                                            <input type="hidden" name="adjustableQltSuggestionsJson" id="adjustableQltSuggestionsJson" />
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-12 adjustable_input_fields_0" style="display: none;">
                                        <div class="form-group">
                                            <label for="l30">Custom MM Size</label>
                                            <div class="input-group">
                                                {!! Form::number('custom_mm_size[]', null, array('placeholder' => 'Custom MM Size','class' => 'form-control number-error custom_mm_size', 'step' => '0.01','id' => 'custom_mm_size_0','min'=>'0.000')) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-12 adjustable_input_fields_0" style="display: none;">
                                        <div class="form-group">
                                            <label for="l30">Custom Sieve Size</label>
                                            <div class="input-group">
                                                {!! Form::number('custom_sieve_size[]', null, array('placeholder' => 'Custom Sieve Size','class' => 'form-control number-error custom_sieve_size', 'step' => '0.01','id' => 'custom_sieve_size_0')) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <!-- end adjustable fields html -->
                        </div>

                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <button type="button" name="add" id="add" class="btn btn-success">Add More</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-12" style="display: none;">
                                    <div class="form-group">
                                        <label for="l30">Transaction Type</label>
                                        <select class="form-control transactionsele" name="transaction_type">
                                        <option value="10" selected>Issue</option>
                                    </select>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="vendorhid">
                                        <div class="form-group">
                                            <label for="l30">Vendor Name</label>
                                             {!! Form::text('vendor_name' ,null, array('autocomplete' => 'off', 'class' => 'form-control vendor_name required','id'=>'search_text')) !!}
                                             <input type="hidden" name="customSuggestionsJson" id="customSuggestionsJson" />
                                             {{ Form::hidden('vendorId', '', array('id' => 'venID')) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="pono">
                                        <div class="form-group">
                                            <label for="l30">PO. No.</label>
                                            {!! Form::text('po_number', null, array('class' => 'required  form-control pono','id'=>"pono")) !!}
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="l30">Comment/Reason</label>
                                        {!! Form::textarea('comment', null, array('class' => 'form-control', "rows"=>"3")) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions btn-list">
                                <button class="btn btn-primary" id="btn_preview" type="button">Preview</button>
                                <button class="btn btn-primary" id="btn_save" type="submit">Save</button>
                                <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
                            </div>
                              <input type="hidden" name="diamond_combination_are_repeated" id="diamond_combination_are_repeated" value="{{Config('constants.message.diamond_combination_are_repeated')}}">
                        {!! Form::close() !!}
                    </div>
                    <!-- /.widget-body -->
                </div>
                <!-- /.widget-bg -->
            </div>
            <!-- /.widget-holder -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.widget-list -->
</main>

<div class="modal fade bs-modal-lg modal-color-scheme preview_showDetail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog w-75 mw-100">
      <div class="modal-content">
          <div class="modal-header text-inverse">
              <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
              <h5 class="modal-title" id="myLargeModalLabel">Preview Voucher</h5>
          </div>
          <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

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
    $(document).on('blur', '#mm_size_'+i, function(){
        MmToSieve(i);
    });
    /*$(document).on('blur', '#sieve_size_'+i, function(){
        SeiveToMmA(i);
    }); */
    $(document).on('blur', '#search_diamond_weight_text_'+i, function(){
        weightValidation(i);
    });

    $(document).ready(function() {
        $('#btn_preview').click(function() {

            var inputValid = InputsValidation(i);
            if(inputValid) {
            $.ajax({
            url: "<?=URL::to('/'). '/diamond/voucherPreview'; ?>",
            dataType: "json",
            data: $("#myform").serialize(),
            success: function(data) {
                $('.modal-body').html(data.html);
                $('.preview_showDetail').modal('show');
            }
            });
        }
       });

    var i=0;
    $(document).on('change',".custom_common",function(){
            var customId = $(this).attr('data-adjustableid');
        if ($('#adjustable_chk_'+customId+':checked').length) {
            $('#custom_chk_'+customId).val('1');
            $('.adjustable_input_fields_'+customId).css({"display": "block"});
        }
        else {
            $('#custom_chk_'+customId).val('0');
            $('#custom_diamond_quality_text_'+customId).val('');
            $('#custom_mm_size_'+customId).val('');
            $('#custom_sieve_size_'+customId).val('');
            $('.adjustable_input_fields_'+customId).css({"display": "none"});   
        }
    });
    
    /* Append Code onClick of Addmore Button - start */
    //
    diamondIssueCheck(i);

    var url ="{{action('DiamondController@searchweight')}}";
    $(document).on('change','select',".selectedvalue_"+i,function() {
        getRate(i,url,$(this));
        var rateOption = $(this).val();
        $('#stonerate_'+i).val(rateOption);
    }).change();

    $('#add').click(function(){
        var isValid = false;
        var customValid = customValidation(i);
        var rateTypeValid = $('#myform').validate().element("#rate_type_"+i);

        if ($('#custom_chk_'+i).val() == '1' || $( "#adjustable_chk_"+i ).hasClass( "custom_common" )){
            var customSizeValidation = customCheckDiamondValidation(i);
            var multivalidation = CombaineCheckDiamondValidation(i);
                if(!isValid) {
                    if(customValid && rateTypeValid && customSizeValidation && multivalidation){
                        isValid = true;
                    } 
                }
        }else{
            
                    var sizeValid = checkDiamondValidation(i);
                    var inputValid = InputsValidation(i);
                    if(!isValid) {
                        if(inputValid && sizeValid && customValid && rateTypeValid){
                            isValid = true;
                        } 
                    }
                
           
        }
       
        if(isValid) {
            if ($('#custom_chk_'+i).val() == '1'  || $( "#adjustable_chk_"+i ).hasClass( "custom_common" ) ){
                i++;
                var html = getHtml(i);
                html += getOtherHtml(i);
                html += getAdjustableInputsHtml(i);
                $('#dynamicadd').append(html);
                $(document).on('blur', '#mm_size_'+i, function(){
                    MmToSieve(i);
                });
               /* $(document).on('blur', '#sieve_size_'+i, function(){
                    SeiveToMmA(i);
                });*/
                $(document).on('blur', '#search_diamond_weight_text_'+i, function(){
                   weightValidation(i);
                });
                
                var url ="{{action('DiamondController@searchweight')}}";
                diamondIssueCheck(i);
            }else{
                i++;
                var url ="{{action('DiamondController@searchweight')}}";
                var html = getHtml(i);
                html += getOtherHtml(i);
                html += getAdjustableInputsHtml(i);
                $('#dynamicadd').append(html);

                $(document).on('blur', '#mm_size_'+i, function(){
                    MmToSieve(i);
                });
                
              /*  $(document).on('blur', '#sieve_size_'+i, function(){
                    SeiveToMmA(i);
                });*/ 
                
                $(document).on('blur', '#search_diamond_weight_text_'+i, function(){
                    weightValidation(i);
                });
                
                
                diamondIssueCheck(i);
            }
            $(document).on('change','select',".selectedvalue_"+i,function() {
                getRate(i,url,$(this));
                var rateOption = $(this).val();
                $('#stonerate_'+i).val(rateOption);
            }).change();
        }
    });
    $(document).on('click', '.btn_remove', function(){
        var button_id = $(this).attr("id");
        $('#row'+button_id+'').remove();
        i--;
    });

    var i=0;
    $('#btn_save').click(function(e){
        e.preventDefault();
        var customValid = customValidation(i);
        var vendorValid = $('#myform').validate().element("#search_text");
        var poValid = $('#myform').validate().element("#pono");
        var customSizeValidation = customCheckDiamondValidation(i);
       //var multivalidation = CombaineCheckDiamondValidation(i);
        var rateTypeValid = $('#myform').validate().element("#rate_type_"+i);
        var customRateValid = $('#myform').validate().element("#custom_"+i);

        if ($('#custom_chk_'+i).val() == '1'  || $( "#adjustable_chk_"+i ).hasClass( "custom_common" )){
            if(customValid && rateTypeValid && customSizeValidation && poValid && customRateValid){
                /*alert(1);*/
                 $('#myform').submit();
            }
        }else{
            
           
                var sizeValid = checkDiamondValidation(i);
                var inputValid = InputsValidation(i);
                var sizeValid = checkDiamondValidation(i);
                var inputValid = InputsValidation(i);
                if(sizeValid && inputValid &&  vendorValid && poValid && rateTypeValid && customRateValid && customValid){
                    //alert(3);
                    $('#myform').submit();
                } 
            }
        //}
    });

    /* For Autocomplete Code - start */
    src = "{{ route('searchajax') }}";
    $.ajax({
        url: src,
        dataType: "json",
        data: {
            term : '',
        },
        success: function(data) {
            var myJSON = JSON.stringify(data);
            $('#customSuggestionsJson').val(myJSON);
        }
    });

    jQuery('#search_text').autocatch({
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
            term : '',
        },
        success: function(data) {
            var myJSON = JSON.stringify(data);
            $('#customShapeSuggestionsJson').val(myJSON);
        }
    });

    jQuery('#search_stone_shape_text_0').autocatch({
        'jsonData': '#customShapeSuggestionsJson',
        'suggestionRenderer': '#customShapeSuggestions',
        'idElem': '#shapeID_0',
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
    urlforinvoicequality = "{{ route('selectedinvoicequality') }}";
    $(document).on('blur','.common_input',function(){
        setTimeout(function(){
            var shape = $("#shapeID_"+i).val();
            $.ajax({
                url: urlforinvoicequality,
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
        }, 200);
    });

    //For adjustable Quality
    srcqlt = "{{ route('searchinvoicequality') }}";
    $.ajax({
        url: srcqlt,
        dataType: "json",
        data: {
            term : '',
        },
        success: function(data) {
            var myJSON = JSON.stringify(data);
            $('#adjustableQltSuggestionsJson').val(myJSON);
        }
    });
     jQuery('#custom_diamond_quality_text_0').autocatch({
        'jsonData': '#adjustableQltSuggestionsJson',
        'suggestionRenderer': '#newcustomQltSuggestions',
        'idElem': '#custom_qualityID_0',
    });

    /* For Autocomplete Code - end */

function getOtherHtml(i) {
var Html =  '<div class="col-lg-3 col-md-3 col-sm-12">'+
            '<div class="form-group">'+
            '<label for="l30">Pieces</label>'+
            '<input placeholder="Pieces" min ="0.000" class="form-control pieces" id="pieces_'+i+'" name="pieces[]" type="number">'+
            '</div></div>'+

            '<input type="hidden" name="rate[]" value="" id="stonerate_'+i+'">'+
            '<div class="col-lg-3 col-md-3 col-sm-12">'+
            '<div class="form-group">'+
            '<label for="l30">Rate Type</label>'+
            '<select class="required form-control selectedvalue_'+i+'" data-rate = '+i+' id="rate_type_'+i+'">'+
            '<option value="">Select Rate Type</option>'+
            '<option value="Existing">Existing</option>'+
            '<option value="Custom">Custom</option>'+
            '</select>'+
            '</div></div>'+

            '<div class="col-lg-3 col-md-3 col-sm-12 Existing box" id="existing_rate_'+i+'" style="display:none;">'+
            '<div class="form-group ">'+
            '<label for="l30">Existing Rate</label>'+
            '<input type="number"  name ="existing_rate[]"  id="rate_'+i+'" readonly=true class="form-control">'+
            '</div></div>'+

            '<div class="col-lg-3 col-md-3 col-sm-12 Custom box" id="custom_rate_'+i+'" style="display:none;">'+
            '<div class="form-group">'+
            '<label for="l30">Custom Rate</label>'+
            '<input type="number" name="custom_rate[]" min="0.000" id="custom_'+i+'" class="required form-control" >'+
            '</div></div>'+

            '<input class="shapeID" id="shapeID_'+i+'" name="stone_shape_id[]" type="hidden" value="">'+
            '<input class="qualityID" id="qualityID_'+i+'" name="diamond_quality_id[]" type="hidden" value="">'+
            '<input class="qualityID" id="custom_qualityID_'+i+'" name="custom_diamond_quality_id[]" type="hidden" value="">'+

            '<div class="w-100 text-right px-3">'+'<button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove px-3 py-1 fs-13">Remove</button>'+'</div>'+
            '<script type="text/javascript">jQuery(\'#search_stone_shape_text_'+i+'\').autocatch({\'jsonData\': \'#customShapeSuggestionsJson\',\'suggestionRenderer\': \'#customShapeSuggestions_'+i+'\',\'idElem\': \'#shapeID_'+i+'\',});jQuery(\'#search_diamond_quality_text_'+i+'\').autocatch({\'jsonData\': \'#customQltSuggestionsJson\',\'suggestionRenderer\': \'#customQltSuggestions_'+i+'\',\'idElem\': \'#qualityID_'+i+'\',});jQuery(\'#custom_diamond_quality_text_'+i+'\').autocatch({\'jsonData\': \'#adjustableQltSuggestionsJson\',\'suggestionRenderer\': \'#newcustomQltSuggestions_'+i+'\',\'idElem\': \'#custom_qualityID_'+i+'\',});<\/script>';
            //<script src="<?=URL::to('/');?>/js/common.js" ><\/script>
             return Html;
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
                     setTimeout(function(){ $("#form-errors").hide(); }, 3000);
                }
            }
        });
    });
}

function getAdjustableInputsHtml(i) {
var html = '<div class="row">'+
            '<div class="col-lg-3 col-md-3 col-sm-12">'+
                '<div class="form-group">'+
                    '<label for="l30">Adjustable</label><br/>'+
                    '<input type="checkbox"  value="0" data-adjustableid="'+i+'" name="adjustable_chk[]" id="adjustable_chk_'+i+'" class="form-group custom_common">'+
                    '<input type="hidden" value="0"  name="custom_chk[]" id="custom_chk_'+i+'">'+
                '</div>'+
            '</div>'+
            '<div class="col-lg-3 col-md-3 col-sm-12 adjustable_input_fields_'+i+'" style="display: none;">'+
                '<div class="form-group">'+
                    '<label for="l30">Custom Diamond Quality</label>'+
                    '<input type="text" id="custom_diamond_quality_text_'+i+'" name="custom_diamond_quality[]" autocomplete="off"  class="required form-control custom_diamond_quality autocomplete_quality_txt"/>'+
                '</div>'+
            '</div>'+
            '<div class="col-lg-3 col-md-3 col-sm-12 adjustable_input_fields_'+i+'" style="display: none;">'+
                '<div class="form-group">'+
                    '<label for="l30">Custom MM Size</label>'+
                    '<div class="input-group">'+
                        '<input type="number" id="custom_mm_size_'+i+'"  name="custom_mm_size[]" placeholder = "Custom MM Size" class = "form-control number-error custom_mm_size" min="0.000" step="0.01">'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-lg-3 col-md-3 col-sm-12 adjustable_input_fields_'+i+'" style="display: none;">'+
                '<div class="form-group">'+
                    '<label for="l30">Custom Sieve Size</label>'+
                    '<div class="input-group">'+
                        '<input type="number" id="custom_sieve_size_'+i+'"  name="custom_sieve_size[]" placeholder = "Custom Sieve Size" class = "form-control number-error custom_sieve_size" step="0.01">'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>';
    return html;
}

function customValidation(i) {
  
    customValid = true;
    if($('#adjustable_chk_'+i).is(':checked')) {
        var customValid = false;
        var mmSize = $("#custom_mm_size_"+i).val();
        var seiveSize = $("#custom_sieve_size_"+i).val();
        
        var qltValid = $('#myform').validate().element("#custom_diamond_quality_text_"+i);
        var MMSizeValid = $('#myform').validate().element("#custom_mm_size_"+i);
        var sieveSizeValid = $('#myform').validate().element("#custom_sieve_size_"+i);

        if(mmSize.length == 0 && seiveSize.length == 0)  {
        $("#custom_mm_size_"+i).addClass("required");
            var MMSizeValid = $('#myform').validate().element("#custom_mm_size_"+i);
        }
        else if(seiveSize.length != 0)  {

            $("#custom_mm_size_"+i).removeClass("required");
            $("#custom_mm_size_"+i).removeAttr('aria-invalid');
            $("#custom_mm_size_"+i+"-error").remove();

        }
        if(!customValid) {
            if(qltValid && MMSizeValid) {
                customValid = true;
            }
        }
    }
    return customValid;
}
function customCheckDiamondValidation(i) {

    var shape = $("#search_stone_shape_text_"+i).val();
    var qlty = $("#custom_diamond_quality_text_"+i).val();
    var mmSize = $("#custom_mm_size_"+i).val();
    var seiveSize = $("#custom_sieve_size_"+i).val();
    var combIsValid = true;
    var customcheck = $('#custom_chk_'+i).val();
    if(customcheck == 1 ){
        if(mmSize != "") {

        $('.stone_shape').each(function($key , $val) {
            if($key < i){
                $shapeprev = $("#search_stone_shape_text_"+$key).val();
                $quaprev = $("#custom_diamond_quality_text_"+$key).val();
                $mmsizeprev = $("#custom_mm_size_"+$key).val();
                if($shapeprev == shape && $quaprev == qlty && $mmsizeprev == mmSize){
                    combIsValid = false;
                }
            }
        });
        } else {

            $('.stone_shape').each(function($key , $val) {
                if($key < i){
                    $shapeprev = $("#search_stone_shape_text_"+$key).val();
                    $quaprev = $("#custom_diamond_quality_text_"+$key).val();
                    $sievesizeprev = $("#custom_sieve_size_"+$key).val();
                    if($shapeprev == shape && $quaprev == qlty && $sievesizeprev == seiveSize){
                        combIsValid = false;
                    }
                }
            });
        }
    }

    

    if(!combIsValid) {

        var repeted_value =$("#diamond_combination_are_repeated").val();
        swal(""+repeted_value+"");
        var shape = $("#search_stone_shape_text_"+i).val("");
        var qlty = $("#custom_diamond_quality_text_"+i).val("");
        var mmSize = $("#custom_mm_size_"+i).val("");
        var seiveSize = $("#custom_sieve_size_"+i).val("");
        return;
    }
    return combIsValid;
}
function CombaineCheckDiamondValidation(i) {

    var shape = $("#search_stone_shape_text_"+i).val();
    var qlty = $('#search_diamond_quality_text_'+i).val();
    var seiveSize = $('#sieve_size_'+i).val();
    var mmSize = $('#mm_size_'+i).val();
    var customcheck = $('#custom_chk_'+i).val();
    var combIsValid = true;
    if(customcheck == 1 ){
        if(mmSize != "") {

        $('.stone_shape').each(function($key, $val) {
            if($key <= i){
                $shapeprev = $("#search_stone_shape_text_"+$key).val();
                $quaprev = $("#custom_diamond_quality_text_"+$key).val();
                $mmsizeprev = $("#custom_mm_size_"+$key).val();
                if($shapeprev == shape && $quaprev == qlty && $mmsizeprev == mmSize){
                    combIsValid = false;
                }
            }
        });
    }else {

            $('.stone_shape').each(function($key,$val) {
                if($key <= i){
                    $shapeprev = $("#search_stone_shape_text_"+$key).val();
                    $quaprev = $("#custom_diamond_quality_text_"+$key).val();
                    $sievesizeprev = $("#custom_sieve_size_"+$key).val();
                    if($shapeprev == shape && $quaprev == qlty && $sievesizeprev == seiveSize){
                        combIsValid = false;
                    }
                }
            });
        }
    }else{
        if(mmSize != "") {

        $('.stone_shape').each(function($key , $val) {
            if($key < i){
                $shapeprev = $("#search_stone_shape_text_"+$key).val();
                $quaprev = $("#search_diamond_quality_text_"+$key).val();
                $mmsizeprev = $("#mm_size_"+$key).val();
                if($shapeprev == shape && $quaprev == qlty && $mmsizeprev == mmSize){
                    combIsValid = false;
                }
            }
        });
    } else {

        $('.stone_shape').each(function($key , $val) {
            if($key < i){
                $shapeprev = $("#search_stone_shape_text_"+$key).val();
                $quaprev = $("#search_diamond_quality_text_"+$key).val();
                $sievesizeprev = $("#sieve_size_"+$key).val();
                if($shapeprev == shape && $quaprev == qlty && $sievesizeprev == seiveSize){
                    combIsValid = false;
                }
            }
        });
    }
    }
    
    

    if(!combIsValid) {

        var repeted_value =$("#diamond_combination_are_repeated").val();
        swal(""+repeted_value+"");
        var shape = $("#search_stone_shape_text_"+i).val("");
        var qlty = $('#search_diamond_quality_text_'+i).val("");
        var customqlty = $("#custom_diamond_quality_text_"+i).val("");
        var custommmSize = $("#custom_mm_size_"+i).val("");
        var customseiveSize = $("#custom_sieve_size_"+i).val("");
        var seiveSize = $('#sieve_size_'+i).val("");
        var mmSize = $('#mm_size_'+i).val("");
        return;
    }
    return combIsValid;
}

});
</script>
@endsection