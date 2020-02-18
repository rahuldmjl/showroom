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
    <div class="widget-list">
        <div class="row">
            <div class="col-md-12 widget-holder">
                <div class="widget-bg">
                    <div class="widget-body clearfix">
                        <h5 class="box-title box-title-style mr-b-0">Add Diamond</h5>
                        <p class="text-muted">You can add metal by filling this form</p>

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

                        {!! Form::open(array('route' => 'diamond.storediamonds','method'=>'POST', 'files'=>'true','id' => 'myform')) !!}
                        <div class="dynamicadd" id="dynamicadd">                        
                            <div class="row">
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
                                 <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group ">
                                        <label for="l30">MM Size</label>
                                        <div class="input-group ">
                                            {!! Form::number('mm_size[]', null, array('placeholder' => 'MM Size','class' => 'form-control number-error mm_size required number-error','id' => 'mm_size_0','step' => '0.01','min'=>'0.000')) !!}
                                         </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Sieve Size</label>
                                        <div class="input-group ">
                                            {!! Form::number('sieve_size[]', null, array('placeholder' => 'Sieve Size','class' => 'form-control  sieve_size','id' => 'sieve_size_0','step' => '0.01')) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                       <label for="l30">Diamond Weight (cts)</label>
                                        {!! Form::number('diamond_weight[]', null, array('placeholder' => 'Diamond Weight','class' => 'required form-control weight_count', 'step' => '0.001','id'=>'search_diamond_weight_text_0','min'=>'0.000')) !!}
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group position-relative number-error">
                                        <label for="l30">Rate (per carat)</label>
                                        <div class="input-group">
                                            {!! Form::number('rate[]', null, array('placeholder' => 'Rate','class' => 'form-control number-error required', 'step' => '0.01','id' => 'rate_0','min'=>'0.000')) !!} <span class="input-group-addon"><i class="list-icon fa fa-inr"></i></span>
                                        </div>
                                    </div>
                                </div>

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
                                           
                                            <input type="text"  name="vendor_id" class="required form-control" id="search_text" autocomplete="off" >
                                            <input type="hidden" name="customSuggestionsJson" id="customSuggestionsJson" />
                                        </div>
                                    </div>
                                    {{ Form::hidden('vendor_name',null, array('class' => 'vendorName', 'id' => 'vendorName')) }}
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Invoice Number</label>
                                        <br>
                                        {!! Form::text('invoice_number', null, array('class' => 'form-control')) !!}
                                    </div>
                                </div>
                                 <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Purchased Date</label>
                                        <br>
                                        {!! Form::text('purchased_at', null, array('class' => 'form-control datepicker','autocomplete' => 'off', 'data-plugin-options'=>'{"autoclose": true, "maxDate": "today", "endDate": "today", "format": "yyyy-mm-dd"}')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Due Date</label><br/>
                                         {!! Form::text('due_date', null, array('class' => 'form-control datepicker', 'autocomplete' => 'off', 'data-plugin-options'=>'{"autoclose": true, "startDate": "today", "format": "yyyy-mm-dd"}')) !!}

                                    </div>
                                </div>
                                
                               <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group error-file">
                                          <label for="l30">Invoice Attachment</label><br/>
                                    <div class="input-group ">
                                        <div class="input-group-btn width-90">
                                          <div class="fileUpload btn w-100 btn-default">
                                            <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
                                            <input id="uploadBtn" type="file" class="upload width-90"  name="purchased_invoice"  accept="application/pdf,image/png,image/jpeg, image/jpg,application/msword"/>
                                          </div>
                                        </div>
                                        <input id="uploadFile"  class="form-control required border bg-transparent" placeholder="Choose File"  disabled="true">

                                    </div>
                                    </div>
                                </div>

                                 <div class="col-lg-4 col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label for="l30">Amount Paid</label>
                                            <div class="input-group">
                                                {!! Form::number('amount_paid_with_gst', null, array('placeholder' => 'Amount Paid','class' => 'form-control', 'step' => '0.01','id'=>'amount_paid','min'=>'0.000')) !!}
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
                            <input type="hidden" name="role" value="{{$role}}" id="roleval">
                            <div class="form-actions btn-list">
                                <button class="btn btn-primary" id="btn_save" type="submit">Save</button>
                                <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
                            </div>
                            {{ Form::hidden('vendorId', '', array('id' => 'venID')) }}
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script>
    $("#myform").validate({
        ignore: ":hidden",
        rules: {
            purchased_at:{
                required :true
            },
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

            },
            due_date:{
                required :true
            }
        }
    });
    var i=0;
    $(document).ready(function() {
        document.getElementById("uploadBtn").onchange = function (){
            document.getElementById("uploadFile").value = this.value.substring(12);
            document.getElementsByName("purchased_invoice").value = this.value.substring(50);
        }
        
        $('#add').click(function(){
            var isValid = false;
            var sizeValid = checkDiamondValidation(i);
            var inputValid = InputsValidation(i);
            if(!isValid) {
                if(inputValid && sizeValid){
                    isValid = true;
                } 
            } 
            /* if(!isValid) {
                if((!inputValid) && (!sizeValid)){
                    isValid = false;
                }else{
                    isValid = true;
                }
            }*/
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

        $('#btn_save').click(function(e) {
            e.preventDefault();
            var sizeValid = checkDiamondValidation(i);
            var inputValid = InputsValidation(i);
            var vendorValid = $('#myform').validate().element("#search_text");
            var invoiceValid = $('#myform').validate().element("#uploadBtn"); 
            var rateValid = $('#myform').validate().element("#rate_"+i);
            if(sizeValid && inputValid &&  vendorValid  && rateValid && invoiceValid){
                 $('#myform').submit();
            }
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

        jQuery('#search_stone_shape_text_0').autocatch({
            'jsonData': '#customShapeSuggestionsJson',
            'suggestionRenderer': '#customShapeSuggestions',
            'idElem': '#shapeID_0',
        });

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

        jQuery('#search_diamond_quality_text_0').autocatch({
            'jsonData': '#customQltSuggestionsJson',
            'suggestionRenderer': '#customQltSuggestions',
            'idElem': '#qualityID_0',
        });
        /* For Autocomplete Code - end */
    });


    function getOtherHtml(i) {
        var Html = '<div class="col-lg-3 col-md-3 col-sm-12">'+
        '<div class="form-group ">'+
        '<label for="l30">Rate (per carat)</label>'+
        '<div class="input-group number-error">'+
        '<input placeholder="Rate" class="required form-control number-error" step="0.01" name="rate[]" type="number" id="rate_'+i+'" >'+
        '<span class="input-group-addon"><i class="list-icon fa fa-inr"></i></span></div></div></div>'+
        '<input class="shapeID" id="shapeID_'+i+'" name="stone_shape_id[]" type="hidden" value="">'+
        '<input class="qualityID" id="qualityID_'+i+'" name="diamond_quality_id[]" type="hidden" value="">'+
        '<div class="w-100 text-right px-3">'+'<button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove px-3 py-1 fs-13">Remove</button>'+'</div><script type="text/javascript">jQuery(\'#search_stone_shape_text_'+i+'\').autocatch({\'jsonData\': \'#customShapeSuggestionsJson\',\'suggestionRenderer\': \'#customShapeSuggestions_'+i+'\',\'idElem\': \'#shapeID_'+i+'\',});jQuery(\'#search_diamond_quality_text_'+i+'\').autocatch({\'jsonData\': \'#customQltSuggestionsJson\',\'suggestionRenderer\': \'#customQltSuggestions_'+i+'\',\'idElem\': \'#qualityID_'+i+'\',});<\/script><script src="<?=URL::to('/');?>/js/common.js" ><\/script>';
        return Html;
    }
</script>
@endsection