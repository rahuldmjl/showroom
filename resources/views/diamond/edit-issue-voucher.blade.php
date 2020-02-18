<?php  use App\Helpers\InventoryHelper;?>
@extends('layout.mainlayout')

@section('title', 'Diamond Issue')

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
                        <h5 class="box-title box-title-style mr-b-0">Edit Diamond Issue</h5>
                        <p class="text-muted">You can edit issue by filling this form</p>

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
        <div  id="form-errors" class="errors"></div>

                        {!! Form::open(array('route' => 'diamond.editissuevoucher','method'=>'POST', 'files'=>'true','id' => 'myform')) !!}

                        <input type="hidden" name="voucher_no" value="<?php echo $voucher_no; ?>">
                        <div class="dynamicadd" id="dynamicadd">
                            <?php $i = 0;
foreach ($datas as $key => $data) {?>
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Shape</label>
                                         {!! Form::text('stone_shape[]', $data->stone_shape, array('data-index' => '0', 'autocomplete' => 'off', 'class' => 'required form-control position-relative stone_shape autocomplete_shape_txt','id'=>'search_stone_shape_text_'.$i)) !!}
                                        <input type="hidden" name="customShapeSuggestionsJson" id="customShapeSuggestionsJson" />
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Quality</label>
                                        {!! Form::text('diamond_quality[]',$data->diamond_quality, array('autocomplete' => 'off', 'class' => 'required form-control diamond_quality autocomplete_quality_txt','id'=>'search_diamond_quality_text_'.$i)) !!}
                                        <input type="hidden" name="customQltSuggestionsJson" id="customQltSuggestionsJson" />
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Weight</label>
                                        {!! Form::number('diamond_weight[]', $data->diamond_weight, array('placeholder' => 'Diamond Weight','class' => 'required form-control', 'step' => '0.001','min' => '0.001','id' => 'search_diamond_weight_text_'.$i )) !!}
                                    </div>
                                </div>
                                 <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">MM Size</label>
                                        <div class="input-group">
                                            {!! Form::number('mm_size[]', $data->mm_size, array('placeholder' => 'MM Size','class' => 'form-control number-error mm_size', 'step' => '0.01','id' => 'mm_size_'.$i,'data-id' => $i)) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Sieve Size</label>
                                        <div class="input-group">
                                            {!! Form::number('sieve_size[]', $data->sieve_size, array('placeholder' => 'Sieve Size','class' => 'form-control sieve_size', 'step' => '0.01','id' => 'sieve_size_'.$i,'data-id' => $i )) !!}
                                        </div>
                                    </div>
                                </div>

                               

                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Pieces</label>
                                        <div class="input-group">
                                            {!! Form::number('pieces[]', $data->pieces, array('placeholder' => 'Pieces','class' => 'form-control', 'id' => 'pieces_'.$i)) !!}
                                        </div>
                                    </div>
                                </div>
                               <?php $shape_id = inventoryHelper::getStoneShapeId($data->stone_shape);?>
                                {{ Form::hidden('stone_shape_id[]',$shape_id, array('class' => 'shapeID', 'id' => 'shapeID_'.$i)) }}
                                {{ Form::hidden('diamond_quality_id[]', $data->stone_shape, array('class' => 'qualityID', 'id' => 'qualityID_0')) }}
                            </div>


                            <div class="row">

                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Rate</label>
                                        {!! Form::text('existing_rate[]',$data->rate,array('class' => 'required form-control','id'=>'rate_'.$i)) !!}
                                    </div>
                                </div>
                        </div>
                        <?php $i++;}?>
                        <input type="hidden" value="<?php echo $i; ?>" class="diamondcount">
                    </div>

                            <div class="row">
                               <!--  <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <button type="button" name="add" id="add" class="btn btn-success">Add More</button>
                                    </div>
                                </div> -->
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
                                        	 <?php $getVendorName = App\User::where('id', $data->vendor_id)->first();
$vendorName = $getVendorName->name;?>

                                            <label for="l30">Vendor Name</label>
                                             {!! Form::text('vendor_name' ,$vendorName, array('autocomplete' => 'off', 'class' => 'form-control','id'=>'search_text','disabled')) !!}
                                             <input type="hidden" name="customSuggestionsJson" id="customSuggestionsJson" />
                                             {{ Form::hidden('vendorId', '', array('id' => 'venID')) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="pono">
                                        <div class="form-group">
                                            <label for="l30">PO. No.</label>
                                            {!! Form::text('po_number', $data->po_number, array('class' => 'form-control pono','disabled')) !!}
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="l30">Comment/Reason</label>
                                        {!! Form::textarea('comment', $data->comment, array('class' => 'form-control', "rows"=>"3")) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions btn-list">
                                <button class="btn btn-primary" id="btn_save" type="submit">Save</button>
                                <button class="btn btn-outline-default" type="reset">Cancel</button>
                            </div><!--
                            {{ Form::hidden('vendorId', '', array('id' => 'venID')) }} -->
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

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?=URL::to('/');?>/js/autocatch.js"></script>
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script type="text/javascript">
 //var counter = jQuery('.diamondcount').val();
 var i = 0;

$(document).ready(function() {  

   $('.mm_size').blur(function(){
        var currentId = $(this).attr('data-id');
         
        var shapeid = $('#shapeID_'+currentId).val();
        var mm_size = $('#mm_size_'+currentId).val();
        var url = "{{ route('searchmmtosieveajax') }}";
        var result =  MmToSiveAjax(shapeid ,mm_size,currentId,url);
        
    });
 
    $('.sieve_size').blur(function(){

        var currentId = $(this).attr('data-id');
        var shapeid = $('#shapeID_'+currentId).val();
        var sieve_size = $('#sieve_size_'+currentId).val();
        var mm_size = $('#mm_size_'+currentId).val();
        var url = "{{ route('searchsievetommajax') }}";
        var result =  SiveToMmAjax(shapeid,sieve_size,mm_size,currentId,url);
    
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

    srcshpe = "{{ route('searchajaxshape') }}";
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
    var counter = jQuery('.diamondcount').val();
    for(var i =0; i <= counter;i++) {
        jQuery('#search_stone_shape_text_'+i).autocatch({
            'jsonData': '#customShapeSuggestionsJson',
            'suggestionRenderer': '#customShapeSuggestions_'+i,
            'idElem': '#shapeID_'+i,
        });
    }

    srcqlt = "{{ route('searchajaxquality') }}";
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

    for(var i =0; i <= counter;i++) {

        jQuery('#search_diamond_quality_text_'+i).autocatch({
            'jsonData': '#customQltSuggestionsJson',
            'suggestionRenderer': '#customQltSuggestions',
            'idElem': '#qualityID_0',
        });

             
    
    }
    /* For Autocomplete Code - end */


    $('#btn_save').click(function(e) {
        var isValid = false;
        var combIsValid = true;
        var lossIsValid = true;
        for(var i =0; i <= counter;i++) {

            var shape = $("#search_stone_shape_text_"+i).val();
            var qlty = $("#search_diamond_quality_text_"+i).val();
            var mmSize = $("#mm_size_"+i).val();
            var seiveSize = $("#sieve_size_"+i).val();
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
            }
            else {
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


            if(!isValid) {

                var shapeValid = $('#myform').validate().element("#search_stone_shape_text_"+i);
                var qltValid = $('#myform').validate().element("#search_diamond_quality_text_"+i);
                var wgtValid = $('#myform').validate().element("#search_diamond_weight_text_"+i);
                var MM_size = $('#myform').validate().element("#mm_size_"+i);
                var sieve_size = $('#myform').validate().element("#sieve_size_"+i);
                var rate = $('#myform').validate().element("#rate_"+i);
                if(mmSize.length == 0 && seiveSize.length == 0)  {
                    $("#mm_size_"+i).addClass("required");
                    var MM_size = $('#myform').validate().element("#mm_size_"+i);
                }
                else if(seiveSize.length != 0)  {
                    $("#mm_size_"+i).removeClass("required");
                    $("#mm_size_"+i).removeAttr('aria-invalid');
                    $("#mm_size_"+i+"-error").remove();
                }

                if(!shapeValid) {
                    $("#search_stone_shape_text_"+i).focus();
                } else if(!qltValid) {
                    $("#search_diamond_quality_text_"+i).focus();
                } else if(!wgtValid) {
                    $("#search_diamond_weight_text_"+i).focus();
                } else if(!MM_size && !sieve_size) {
                    $("#mm_size_"+i).focus();
                } else if(!rate) {
                    $("#rate_"+i).focus();
                }




            if(shapeValid && qltValid && wgtValid  && sieve_size){
              /*  if(!combIsValid) {
                    var repeted_value =$("#diamond_combination_are_repeated").val();
                    swal(""+repeted_value+"");
                    var shape = $("#search_stone_shape_text_"+i).val("");
                    var qlty = $("#search_diamond_quality_text_"+i).val("");
                    var mmSize = $("#mm_size_"+i).val("");
                    var seiveSize = $("#sieve_size_"+i).val("");
                    return;
                }*/
                //
                if(lossIsValid ){
                    $('#myform').submit();
                }
            }
        }

        }
    });
    $("#form-errors").hide();
    $(document).on('blur','.sieve_size',function(){
        var currentId = $(this).attr('data-id');
        var stshape = $('#search_stone_shape_text_'+currentId).val();
        var dmquality = $('#search_diamond_quality_text_'+currentId).val();
        var dmsieve_size = $('#sieve_size_'+currentId).val();
        var dmmm_size = $('#mm_size_'+currentId).val();
        //alert(currentId);
        $.ajax({
                url: "{{action('DiamondController@diamondIssueCheck')}}",
                data: {shape:stshape,
                quality:dmquality,
                sieve_size:dmsieve_size,
                mm_size:dmmm_size,
            },
             
            success: function(result){
                if (result.success == true) {
                    $('#search_diamond_weight_text_'+currentId).val(result.result);
                    $("#form-errors").empty();
                    console.log(result.result);
                } else{
                    console.log(result.data);
                    var contentDiv = "<div class='alert alert-danger'>";  
                    contentDiv += '<label>' + result.data + '</label>'; 
                    contentDiv += '</div>'; 
                    $('#search_diamond_weight_text_'+currentId).val('');    
                    $( '#form-errors' ).html( contentDiv ); 
                     $("#form-errors").show();
                    setTimeout(function(){ 
                        $("#form-errors").hide();
                        
                    }, 3000);
                    $("#form-errors").removeClass('div.alert alert-danger');
                 
                }
            }
        });
    });


});

    
       

   

</script>
@endsection