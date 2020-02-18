@extends('layout.mainlayout')

@section('title', 'Gold Issue')

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
        {{ Breadcrumbs::render('metals.goldissue') }}
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
                        <h5 class="box-title box-title-style mr-b-0">Add Gold Issue</h5>
                        <p class="text-muted">You can add issue by filling this form</p>
                            @if ($message = Session::get('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
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

                            {!! Form::open(array('route' => 'metals.goldissuestore','method'=>'POST', 'files'=>'true','id' => 'myform', 'autocomplete'=>"off")) !!}
                        <div class="dynamicadd" id="dynamicadd">
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Metal Type</label>
                                        {!! Form::select('metal_type', ['1' => 'GOLD 24K', '2' => 'PLATINUM 950'],[], array('class' => 'form-control', 'id' => 'metal_type')) !!}

                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                         <label for="l30">Metal Weight (gms)</label>
                                        {!! Form::number('metal_weight', null, array('placeholder' => 'Metal Weight','id' => 'metal_weight','class' => 'form-control', 'step' => '0.001','min' => '0.500')) !!}
                                    </div>
                                </div>
                                 <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="vendorhid">
                                        <div class="form-group">
                                            <label for="l30">Vendor Name</label>
                                            {!! Form::text('vendorid', null, array('class' => 'form-control','id'=>'search_text')) !!}
                                        </div>
                                        <input type="hidden" name="customSuggestionsJson" id="customSuggestionsJson" />
                                    </div>
                                    {{ Form::hidden('vendor_name', '', array('class' => 'vendorName', 'id' => 'vendorName')) }}
                                    {{ Form::hidden('vendorId', '', array('id' => 'venID')) }}
                                    {{ Form::hidden('measurement', '2') }}
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


                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="pono">
                                    <div class="form-group">
                                        <label for="l30">PO. No.</label>
                                        {!! Form::text('po_number', null, array('id'=>'pono','class' => 'form-control pono')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12" id="gold_type_input">
                                <div class="form-group">
                                    <label for="l30">Gold Type</label>
                                    {!! Form::select('gold_type', ['999' => '999', '995' => '995'],[], array('class' => 'form-control', 'id' => 'gold_type')) !!}
                                </div>
                            </div>
                              <input type="hidden" name="created_by" value="{{Auth::user()->id}}">
                                <input type="hidden" name="rate24k" value="{{$rate24k}}" id="24k">
                               <input type="hidden" name="rateplatinum" value="{{$rateplitinum}}" id="platinum">
                         <input type="hidden" name="issue_date" value="{{Date('Y-m-d')}}">

                            <div class="col-lg-4 col-md-6 col-sm-12">

                                <div class="form-group">
                                    <label for="l30">Rate Type</label>
                                           <select class="form-control" id="rate_type">
                                                <option value="NULL">Select Rate Type</option>
                                                <option value="Existing">Existing</option>
                                                <option value="Custom">Custom</option>
                                            </select>
                                </div>
                            </div>
                        </div>

                        <div class="row ">
                            <div class="col-lg-4 col-md-6 col-sm-12 Existing box" id="existing_rate_input">
                                <div class="form-group ">
                                    <label for="l30">Existing Rate</label>
                                    {!! Form::number('existing_rate', '', array('class' => 'form-control','readonly' => 'true', 'id' => 'existing_rate')) !!}
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 Custom box" id="custom_rate_input">
                                <div class="form-group">
                                    <label for="l30">Custom Rate</label>
                                    {!! Form::number('custom_rate', null, array('id' => 'custom_rate', 'class' => 'form-control', 'step' => '0.01','min' => '1.00')) !!}
                                </div>
                            </div>
                        </div>



                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="l30">Comment/Reason</label>
                                    {!! Form::textarea('comment', null, array('class' => 'form-control', "rows"=>"3", 'autocomplete'=>'off')) !!}
                                </div>
                            </div>
                        </div>
                        <div class="form-actions btn-list">
                            <button class="btn btn-primary" type="button" id="btn_preview">Preview</button>
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
                        </div>

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
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="<?=URL::to('/');?>/js/autocatch.js"></script>
<script>
    $('#btn_preview').click(function(){
        var wtValid = $("#myform").validate().element('#metal_weight');
        var vendorValid = $("#myform").validate().element('#search_text');
        var ponoValid = $("#myform").validate().element('#pono');
        if(wtValid && vendorValid && ponoValid)
        $.ajax({
            dataType:'json',
            data:$('#myform').serialize(),
            url:'<?= URL::to('/').'/metals/goldPreview'; ?>',
            success:function(data) {
                $('.modal-body').html(data.html);
                $('.preview_showDetail').modal('show');
            }
        });
    });

    src = "{{ route('searchajax') }}";
    $.ajax({
        url: src,
        dataType: "json",
        data: {
            term : '', //$(this).val()
        },
        success: function(data) {
            var myJSON = JSON.stringify(data);
            $('#customSuggestionsJson').val(myJSON);
        }
    });

   $(document).ready(function() {
      $('#rate_exist').hide();
             $('#custom_rate').hide();
      src = "{{ route('searchajax') }}";
    $.ajax({
        url: src,
        dataType: "json",
        data: {
            term : '', //$(this).val()
        },
        success: function(data) {
            //console.log(data);
            var myJSON = JSON.stringify(data);
            $('#customSuggestionsJson').val(myJSON);
            //response(data);
        }
    });

    function rateSelection(){
        if($('#rate_type').val() == "Existing")
        {
            $('#existing_rate_input').show();
            $('#custom_rate_input').hide();
            $('#custom_rate').hide();
        }
        else if($('#rate_type').val() == "Custom")
        {
            $('#existing_rate_input').hide();
            $('#custom_rate_input').show();
            $('#custom_rate').show();
        }
    }

    rateSelection();

    $('#rate_type').change(function(){
        rateSelection();
    })

    $("select").change(function(){

            $(this).find("option:selected").each(function(){
                var optionValue = $(this).attr("value");
               var twtfk = $("#24k").val();
               var plati =$("#platinum").val();

                if(optionValue == "NULL")
                {
                   $(".box").hide();
                }
                if(optionValue){
                    $(".box").not("." + optionValue).hide();
                    $("." + optionValue).show();

                    if($("#metal_type").val() == '1'){
                        $("#existing_rate").val(twtfk);

                    }else{
                         $('#existing_rate').val(plati);
                    }
                } else{
                    $(".box").hide();
                }
            });
        }).change();



    $("#metal_type").change(function(){
        //alert($(this).val());
        if($(this).val() == "2"){
            $("#gold_type_input").hide();
        } else {
            $("#gold_type_input").show();
        }
        rateSelection();
    });

    jQuery('#search_text').autocatch({
        //'currentSelector': '#search_text',
        'jsonData': '#customSuggestionsJson',
        'suggestionRenderer': '#customSuggestions',
        'idElem': '#venID',
        'txtElem': '#vendorName',
    });

         $("#myform").validate({
            ignore: ":hidden",
            rules: {
               metal_type: {
                    required: true

                },
                metal_weight: {
                    required: true,
                    min:0.500

                },
                vendorid: {
                    required: true

                },
                po_number:{
                    required:true
                },
                gold_type:{
                    required:true
                }
          }
    });

});
</script>
@endsection