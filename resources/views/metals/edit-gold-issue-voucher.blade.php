@extends('layout.mainlayout')

@section('title', 'Gold Issue')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
<link href="http://demo.expertphp.in/css/jquery.ui.autocomplete.css" rel="stylesheet">
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
                        <h5 class="box-title box-title-style mr-b-0">Edit Gold Issue</h5>
                        <p class="text-muted">You can edit issue by filling this form</p>
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

                            {!! Form::open(array('route' => 'metals.updategoldissuevoucher','method'=>'GET', 'files'=>'true','id' => 'myform', 'autocomplete'=>"off")) !!}
                            <input type="hidden" name="voucher_no" value="<?php echo $voucher_no; ?>">
                        <div class="dynamicadd" id="dynamicadd">
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Metal Type</label>
                                        {!! Form::select('metal_type_hidden', ['1' => 'GOLD 24K', '2' => 'PLATINUM 950'],[$datas['metal_type']], array('class' => 'form-control', 'id' => 'metal_type_hidden','disabled')) !!}

                                        <input type="hidden"  value="<?php echo $datas['metal_type']; ?>" name="metal_type" id="metal_type">


                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                         <label for="l30">Metal Weight (gms)</label>
                                        {!! Form::number('metal_weight', $datas['metal_weight'], array('placeholder' => 'Metal Weight','class' => 'form-control', 'step' => '0.001','min' => '0.500')) !!}
                                    </div>
                                </div>
                                 <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="vendorhid">
                                        <div class="form-group">
                                            <?php $getVendorName = App\User::where('id', $datas->vendor_id)->first();
$vendorName = $getVendorName->name;?>
                                            <label for="l30">Vendor Name</label>
                                            {!! Form::text('vendorid', $vendorName, array('class' => 'form-control','id'=>'search_text')) !!}
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
                                        {!! Form::text('po_number', $datas['po_number'], array('class' => 'form-control pono')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12" id="gold_type_input">
                                <div class="form-group">
                                    <label for="l30">Gold Type</label>
                                    {!! Form::select('gold_type', ['999' => '999', '995' => '995'],[$datas['gold_type']], array('class' => 'form-control', 'id' => 'gold_type')) !!}
                                </div>
                            </div>
                              <input type="hidden" name="created_by" value="{{Auth::user()->id}}">
                                <input type="hidden" name="rate24k" value="{{$rate24k}}" id="24k">
                               <input type="hidden" name="rateplatinum" value="{{$rateplitinum}}" id="platinum">
                         <input type="hidden" name="issue_date" value="{{Date('Y-m-d')}}">

                            <!-- <div class="col-lg-4 col-md-6 col-sm-12">

                                <div class="form-group">
                                    <label for="l30">Rate Type</label>
                                           <select class="form-control" id="rate_type">
                                                <option value="NULL">Select Rate Type</option>
                                                <option value="Existing">Existing</option>
                                                <option value="Custom">Custom</option>
                                            </select>
                                </div>
                            </div> -->


                      <!--   <div class="row"> -->

                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="form-group ">
                                    <label for="l30">Rate</label>
                                    {!! Form::number('metal_rate', $datas['metal_rate'], array('class' => 'form-control', 'id' => 'metal_rate')) !!}
                                </div>
                            </div>


                           <!--  <div class="col-lg-4 col-md-6 col-sm-12 Existing box" id="existing_rate_input">
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
                            </div> -->
                        <!-- </div> -->
                </div>


                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="l30">Comment/Reason</label>
                                    {!! Form::textarea('comment', $datas['comment'], array('class' => 'form-control', "rows"=>"3", 'autocomplete'=>'off')) !!}
                                </div>
                            </div>
                        </div>
                        <div class="form-actions btn-list">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-outline-default" type="reset">Cancel</button>
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

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="<?=URL::to('/');?>/js/autocatch.js"></script>
<script>
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

   $(document).ready(function() {
    /*  $('#rate_exist').hide();
             $('#custom_rate').hide();
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
*/
    /*function rateSelection(){
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

    rateSelection();*/

   /* $('#rate_type').change(function(){
        rateSelection();
    })*/

   /* $("select").change(function(){

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
        }).change();*/



    /*$("#metal_type").change(function(){

        if($(this).val() == "2"){
            $("#gold_type_input").hide();
        } else {
            $("#gold_type_input").show();
        }
        rateSelection();
    });*/

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