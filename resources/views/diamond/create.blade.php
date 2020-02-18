@extends('layout.mainlayout')
@section('title', 'Create Diamond')
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
        {{ Breadcrumbs::render('diamond.create') }}
        <!-- /.page-title-right -->
    </div>
    <!-- /.page-title -->
    <!-- =================================== -->
    <!-- Different data widgets ============ -->
    <!-- =================================== -->
    <div class="widget-list creatediamond">
        <div class="row">
            <div class="col-md-12 widget-holder">
                <div class="widget-bg">
                    <div class="widget-body clearfix">
                        <div class="row">
                            <div class="col-md-3 align-self-center mr-auto"> 
                                <h5 class="box-title box-title-style mr-b-0">Add Diamond</h5>
                                <p class="text-muted mb-0">Move raw diamond to inventory</p>
                            </div>
                            <div class="col-lg-8 col-xl-6 d-flex custom-shadow mr-3">
                            <div class="col-md-4 text-center py-3 border-r">
                                <h5 class="box-title fs-14 mt-0 mr-b-0">{{$diamondraw[0]->packet_name}}</h5>
                                <p class="text-muted mb-0 fs-12">PRE PACKET NAME</p>
                            </div>
                            <div class="col-md-4 text-center py-3 border-r rawweight">
                                <h5 class="box-title fs-14 mt-0 mr-b-0">{{$diamondraw[0]->assorting_weight}} (cts)</h5>
                                <p class="text-muted mb-0 fs-12">PRE SIZING</p>

                            </div>
                            <div class="col-md-4 text-center py-3">
                                <h5 class="box-title fs-14 mt-0 mr-b-0 sizing-loss">{{$diamondraw[0]->assorting_weight}} (cts)</h5>
                                <p class="text-muted mb-0 fs-12">SIZING LOSS</p>
                            </div>
                            </div>
                        </div>

                        <hr>
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
                        {!! Form::open(array('route' => 'diamond.store','method'=>'POST', 'files'=>'true','id' => 'myform')) !!}
                        <div class="dynamicadd mt-4" id="dynamicadd">
                            <div class="row">
                                <input type="hidden" name="assorting_weight" value="{{$diamondraw[0]->assorting_weight}}">
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Shape</label>
                                        {!! Form::text('stone_shape[]', null, array('data-index' => '0', 'autocomplete' => 'off', 'class' => 'required form-control position-relative stone_shape autocomplete_shape_txt','id'=>'search_stone_shape_text_0')) !!}
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
                                <input type="hidden" name="total_weight" id="total_weight" value="{{$diamondraw[0]->assorting_weight}}">
                                <input type="hidden" name="remaining_weight" value="{{$diamondraw[0]->assorting_weight}}" id="remaining_weight">

                                <input type="hidden" name="max_loss_allow" value="{{$setting_data}}" id="max_loss_allow" >
                                 <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">MM Size</label>
                                        <div class="input-group">
                                            {!! Form::number('mm_size[]', null, array('placeholder' => 'MM Size','class' => 'form-control mm_size required','id' => 'mm_size_0','step' => '0.01','min'=>'0.000')) !!}
                                         </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Sieve Size</label>
                                        <div class="input-group">
                                            {!! Form::number('sieve_size[]', null, array('placeholder' => 'Sieve Size','class' => 'form-control sieve_size','id' => 'sieve_size_0','step' => '0.01')) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group position-relative">
                                        <label for="l30">Diamond Weight (cts)</label>
                                        {!! Form::number('diamond_weight[]', null, array('placeholder' => 'Diamond Weight','class' => 'required form-control weight_count', 'step' => '0.001','id'=>'search_diamond_weight_text_0','min'=>'0.000')) !!}
                                    </div>
                                </div>
                                

                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group position-relative">
                                        <label for="l30">Rate (per carat)</label>
                                        <div class="input-group">
                                            {!! Form::number('rate[]', null, array('placeholder' => 'Rate','class' => 'form-control required', 'step' => '0.01','id' => 'rate_0','min'=>'0.000')) !!} <span class="input-group-addon"><i class="list-icon fa fa-inr"></i></span></div>


                                    </div>
                                </div>
                                <?php //echo $packet_name;exit;?>
                                {{ Form::hidden('packet_id',$diamondraw[0]->packet_name, array('class' => 'form-group')) }}

                                {{ Form::hidden('stone_shape_id[]', '', array('class' => 'shapeID', 'id' => 'shapeID_0')) }}

                                {{ Form::hidden('diamond_quality_id[]', '', array('class' => 'qualityID', 'id' => 'qualityID_0')) }}
                            </div>
                        </div>
                            <div class="row">

                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <button type="button" name="add" id="add" class="btn btn-success">Add More</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-12" style="display:none;">
                                    <div class="form-group">
                                        <label for="l30">Transaction Type</label>
                                        <select class="form-control transactionsele" name="transaction_type">
                                        <option value="1" selected>Purchase</option>
                                    </select>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="vendorhid">
                                        <div class="form-group">
                                            <label for="l30">Vendor Name</label>
                                            <!-- {!! Form::text('vendor_id',$diamondraw[0]->vendor_name ,null, array('autocomplete' => 'off', 'class' => 'form-control','id'=>'search_text')) !!} -->
                                            <input type="text"  name="vendor_id"  value="{{$diamondraw[0]->vendor_name}}" class="form-control" id="search_text" autocomplete="false" disabled="true">
                                            <input type="hidden" name="customSuggestionsJson" id="customSuggestionsJson" />
                                        </div>
                                    </div>
                                    {{ Form::hidden('vendor_name',$diamondraw[0]->vendor_name, array('class' => 'vendorName', 'id' => 'vendorName')) }}
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Invoice Number</label>
                                        <br>
                                        {!! Form::text('invoice_number', null, array('required' => 'required', 'id' => 'invoice_number', 'class' => 'form-control' )) !!}
                                    </div>
                                </div>
                                 <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Purchased Date</label>
                                        <br>
                                        <input type="text" name="purchased_at" value="{{$diamondraw[0]->purchased_at}}" class="form-control" id="purchased_at" readonly="true">
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Due Date</label><br/>
                                         {!! Form::text('due_date', null, array('class' => 'required form-control datepicker', 'id' => 'due_date', 'autocomplete' => 'off', 'data-plugin-options'=>'{"autoclose": true, "startDate": "today", "format": "yyyy-mm-dd"}')) !!}

                                    </div>
                                </div>

                                 <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group error-file">
                                          <label for="l30">Invoice Attachment</label><br/>
                                    <div class="input-group ">
                                        <div class="input-group-btn width-90">
                                          <div class="fileUpload btn w-100 btn-default">
                                            <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
                                            <input id="uploadBtn" type="file" class="upload width-90"  name="purchased_invoice" accept="application/pdf,image/png,image/jpeg, image/jpg,application/msword"  />
                                          </div>
                                        </div>
                                        <input id="uploadFile"  class="form-control required border bg-transparent" placeholder="Choose File" disabled="true">
                                    </div>
                                    </div>
                                </div>

                                 <div class="col-lg-4 col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label for="l30">Amount Paid</label>
                                            <div class="input-group">
                                                {!! Form::number('amount_paid_with_gst', null, array('placeholder' => 'Amount Paid','class' => 'form-control required', 'step' => '0.01', 'id' => 'amount_paid_with_gst','min'=>'0.000')) !!}
                                                <span class="input-group-addon"><i class="list-icon fa fa-inr"></i></span>
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
                            <input type="hidden" name="vendorId" class="form-control" value="{{$vendor_id[0]->id}}" id="venID" />
                            <input type="hidden" name="role" value="{{$role}}" id="roleval">
                            <div class="form-actions btn-list">
                                <button class="btn btn-primary " id="btn_save" type="submit">Move To Inventory</button>
                                <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
                            </div>
                              <input type="hidden" name="diamond_combination_are_repeated" id="diamond_combination_are_repeated" value="{{Config('constants.message.diamond_combination_are_repeated')}}">
                           <!--  {{ Form::hidden('vendorId', '', array('id' => 'venID')) }} -->
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
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script src="<?=URL::to('/');?>/js/autocatch.js"></script>
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script>
     $("#myform").validate({
            ignore: ":hidden",
            rules: {
                
                purchased_invoice: {
                    required:{
                       depends: function(element){
                          
                           if( $("#roleval").val() == "Super Admin"){
                            
                               var status = false;
                           }else{

                                 var status = true;
                           }
                           //console.log("yesname is: "+status);
                           return status;
                        }
                    },
                    accept:"application/pdf,image/png,image/jpeg, image/jpg,application/msword,.doc,.docx,.xlsx"

                }
            }
        });
   $(document).ready(function() {
    var i=0;

    var total_weight = parseFloat($('#total_weight').val());
    var max_loss_allow = parseFloat($('#max_loss_allow').val());

    $(document).on('change', '.weight_count', function(){
        var total_added_weight = 0;
        var instead_of_weight = 0;
        var remaining_weight = parseFloat($('#remaining_weight').val());

        var idAttrArr = $(this).attr('id').split('search_diamond_weight_text_');
        var selected_index = idAttrArr[1];
        

        $('.weight_count').each(function(index, indexval){
            
            if(isNaN($(indexval).val()) || $(indexval).val() == '' || $(indexval).val() == undefined){
                $(indexval).val('0.000');
            }
           
            if(index == i){
                if($(indexval).val() > remaining_weight){

                    $(indexval).val();
                }
            }
            total_added_weight = parseFloat(total_added_weight) + parseFloat($(indexval).val());
            if(index != selected_index){
                if(instead_of_weight <= 0){
                    instead_of_weight = parseFloat(parseFloat(total_weight) - parseFloat($(indexval).val()));
                } else {
                    instead_of_weight = parseFloat(parseFloat(instead_of_weight) - parseFloat($(indexval).val()));
                }
            }
        });
         var remaining_weight_afterchange = parseFloat(total_weight - parseFloat(total_added_weight)).toFixed(3);

        if(remaining_weight_afterchange < 0 || remaining_weight_afterchange > total_weight ){

            swal("You have added "+parseFloat($('#search_diamond_weight_text_'+i).val()).toFixed(3)+"  weight , instead of  "+parseFloat(instead_of_weight).toFixed(3))

            $("#search_diamond_weight_text_"+i).val("");


            remaining_weight_afterchange = remaining_weight;
        } else {


            $('#remaining_weight').val(remaining_weight_afterchange);

            $('.sizing-loss').html(parseFloat(remaining_weight_afterchange) +' (cts)');
        }

        if(remaining_weight_afterchange == 0){
            $('#add').attr('disabled', 'disabled');
        } else {
            $('#add').attr('disabled', false);
        }


    });



    $('#add').click(function(){

        var isValid = false;
        var sizeValid = checkDiamondValidation(i);
        var inputValid = InputsValidation(i);
            if(!isValid) {
                if(inputValid && sizeValid){
                    isValid = true;
                } 
            }

             if(isValid) {
                i++;
                var html = getHtml(i);
                html += getOtherHtml(i);
                $('#dynamicadd').append(html);
                 $(document).on('blur', '#mm_size_'+i, function(){

                    var shapeid = $('#shapeID_'+i).val();
                    var mm_size = $('#mm_size_'+i).val();
                    var url = "{{ route('searchmmtosieveajax') }}";
                    var result =  MmToSiveAjax(shapeid ,mm_size,i,url);

                });
                $(document).on('blur', '#sieve_size_'+i, function(){
                   
                    var shapeid = $('#shapeID_'+i).val();
                    var sieve_size = $('#sieve_size_'+i).val();
                     var mm_size = $('#mm_size_'+i).val();
                    var url = "{{ route('searchsievetommajax') }}";
                    var result =  SiveToMmAjax(shapeid ,sieve_size,mm_size,i,url);
                    
                });
            }
        
    });

    $(document).on('click', '.btn_remove', function(){
        var button_id = $(this).attr("id");
        $('#row'+button_id+'').remove();
        i--;
    });

    $(document).on('blur', '#mm_size_'+i, function(){

        var shapeid = $('#shapeID_'+i).val();
        var mm_size = $('#mm_size_'+i).val();
        var url = "{{ route('searchmmtosieveajax') }}";
        var result =  MmToSiveAjax(shapeid ,mm_size,i,url);

    });
    $(document).on('blur', '#sieve_size_'+i, function(){
       
        var shapeid = $('#shapeID_'+i).val();
        var sieve_size = $('#sieve_size_'+i).val();
         var mm_size = $('#mm_size_'+i).val();
        var url = "{{ route('searchsievetommajax') }}";
        var result =  SiveToMmAjax(shapeid ,sieve_size,mm_size,i,url);
        
    });

    /* For Autocomplete Code - start */
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
            //response(data);
        }
    });

    jQuery('#search_text').autocatch({
        //'currentSelector': '#search_text',
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
            term : '', //$(this).val()
        },
        success: function(data) {
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
        //'txtElem': '#vendorName',
    });

    srcqlt = "{{ route('searchajaxquality') }}";
    $.ajax({
        url: srcqlt,
        dataType: "json",
        data: {
            term : '', //$(this).val()
        },
        success: function(data) {
            var myJSON = JSON.stringify(data);
            $('#customQltSuggestionsJson').val(myJSON);
            //response(data);
        }
    });

    jQuery('#search_diamond_quality_text_0').autocatch({
        //'currentSelector': '#search_text',
        'jsonData': '#customQltSuggestionsJson',
        'suggestionRenderer': '#customQltSuggestions',
        'idElem': '#qualityID_0',
        //'txtElem': '#vendorName',
    });
    var i=0;
    $('#btn_save').click(function(e) {
        e.preventDefault();
        var isValid = false;
        var combIsValid = true;
        var lossIsValid = true;
        var sizeValid = checkDiamondValidation(i);
        var inputValid = InputsValidation(i);
        var vendorValid = $('#myform').validate().element("#search_text");
        var invoiceValid = $('#myform').validate().element("#uploadBtn"); 
        var rateValid = $('#myform').validate().element("#rate_"+i);
        if(remaining_weight > max_loss_allow){
                lossIsValid = false;
               
            }
        if(sizeValid && inputValid &&  vendorValid  && rateValid && invoiceValid){
              if(lossIsValid === false) {
                    swal("You have Exceeded Loss Limit !");
                    swal({
                        title: 'Exceeded Limit',
                        text: "You have exceeded limit of loss , can't move ahead !!",
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonClass: 'btn btn-warning',
                        confirmButtonText: 'Yes, Extend Limit!'
                    }).then(
                        function (inputvalue) {

                        },
                        function(dismiss){
                            if(dismiss == 'cancel'){
                                $("#search_diamond_weight_text_"+i).focus(function() {
                                    alert( "Handler for .focus() called." );
                                });
                            }
                        }
                    );
                }
               if(lossIsValid && sizeValid){
                    $('#myform').submit();
                }
        }
    });

        document.getElementById("uploadBtn").onchange = function () {
            document.getElementById("uploadFile").value = this.value.substring(12);
            document.getElementsByName("purchased_invoice").value = this.value.substring(50);
        }

});

function getOtherHtml(i) {
    var Html = '<div class="col-lg-3 col-md-3 col-sm-12">'+
            '<div class="form-group">'+
            '<label for="l30">Rate (per carat)</label>'+
            '<div class="input-group">'+
            '<input placeholder="Rate" class="required form-control" step="0.01" name="rate[]" type="number" id="rate_'+i+'" >'+
            '<span class="input-group-addon"><i class="list-icon fa fa-inr"></i></span></div></div></div>'+
            '<input class="shapeID" id="shapeID_'+i+'" name="stone_shape_id[]" type="hidden" value="">'+
            '<input class="qualityID" id="qualityID_'+i+'" name="diamond_quality_id[]" type="hidden" value="">'+
            '<div class="w-100 text-right px-3">'+'<button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove px-3 py-1 fs-13">Remove</button>'+'</div><script type="text/javascript">jQuery(\'#search_stone_shape_text_'+i+'\').autocatch({\'jsonData\': \'#customShapeSuggestionsJson\',\'suggestionRenderer\': \'#customShapeSuggestions_'+i+'\',\'idElem\': \'#shapeID_'+i+'\',});jQuery(\'#search_diamond_quality_text_'+i+'\').autocatch({\'jsonData\': \'#customQltSuggestionsJson\',\'suggestionRenderer\': \'#customQltSuggestions_'+i+'\',\'idElem\': \'#qualityID_'+i+'\',});<\/script><script src="<?=URL::to('/');?>/js/common.js" ><\/script>';
             return Html;
}
</script>
@endsection
