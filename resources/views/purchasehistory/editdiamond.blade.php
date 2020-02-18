<?php
use App\Helpers\CommonHelper;
?>
@extends('layout.mainlayout')

@section('title', 'Purchase History Edit')

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
        {{ Breadcrumbs::render('purchasehistory.editdiamond',$id) }}
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
                        <h5 class="box-title box-title-style mr-b-0">Purchase History Edit</h5>
                        <p class="text-muted">You can purchase history edit by filling this form</p>

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

                        {!! Form::model($daimonddata,array('route' => ['purchasehistory.updatediamond', $id],'method'=>'PATCH', 'files'=>'true','id'=>'diamond_edit')) !!}
                            <div class="dynamicadd" id="dynamicadd">
                            <?php $i = 0;
                        foreach ($daimonddata as $key => $data) {?>
                            <div class="row">
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Shape</label>
                                         {!! Form::text('stone_shape[]', $data->stone_shape, array('data-index' => '0', 'autocomplete' => 'off', 'class' => 'required form-control position-relative stone_shape autocomplete_shape_txt','id'=>'search_stone_shape_text_'.$i , 'readonly' => 'true')) !!}
                                        <input type="hidden" name="customShapeSuggestionsJson" id="customShapeSuggestionsJson" />
                                    </div>
                                </div>
                                <div  class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Quality</label>
                                        {!! Form::text('diamond_quality[]',$data->diamond_quality, array('autocomplete' => 'off', 'class' => 'required form-control diamond_quality autocomplete_quality_txt','id'=>'search_diamond_quality_text_'.$i , 'readonly' => 'true')) !!}
                                        <input type="hidden" name="customQltSuggestionsJson" id="customQltSuggestionsJson" />
                                    </div>
                                </div>
                                <div  class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">MM Size</label>
                                        <div class="input-group">
                                            {!! Form::number('mm_size[]', $data->mm_size, array('placeholder' => 'MM Size','class' => 'form-control number-error mm_size', 'step' => '0.01','id' => 'mm_size_'.$i , 'readonly' => 'true')) !!}
                                        </div>
                                    </div>
                                </div>
                                 <div  class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Sieve Size</label>
                                        <div class="input-group">
                                            {!! Form::number('sieve_size[]', $data->sieve_size, array('placeholder' => 'Sieve Size','class' => 'form-control sieve_size', 'step' => '0.01','id' => 'sieve_size_'.$i , 'readonly' => 'true' )) !!}
                                        </div>
                                    </div>
                                </div>
                                <div  class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Weight</label>
                                        {!! Form::number('diamond_weight[]', $data->diamond_weight, array('placeholder' => 'Diamond Weight','class' => 'required form-control', 'step' => '0.001','min' => '0.001','id' => 'search_diamond_weight_text_'.$i )) !!}
                                    </div>
                                </div>
                                <div  class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Rate</label>
                                        {!! Form::text('rate[]',$data->rate,array('class' => 'required form-control','id'=>'rate_'.$i)) !!}
                                    </div>
                                </div>
                                <div  class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Amount Paid With GST</label>
                                        <div class="input-group">
                                            {!! Form::number('amount_paid_with_gst[]',$data->amount_paid_with_gst, array('placeholder' => 'Amount Paid','class' => 'form-control', 'step' => '0.001','min'=>'0.000')) !!}
                                            <span class="input-group-addon"><i class="list-icon fa fa-inr"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                           
                            <hr class="w-100">
                        <?php $i++;}?>

                    </div>

                        <input type="hidden" value="<?php echo $i; ?>" class="diamondcount">
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="vendorhid">
                                        <div class="form-group">
                                             <?php $getVendorName = App\User::where('id', $data->vendor_id)->first();
                                                $vendorName = $getVendorName->name;?>

                                            <label for="l30">Vendor Name</label>

                                             {!! Form::text('vendor_name' ,$vendorName, array('autocomplete' => 'off', 'class' => 'form-control','id'=>'search_text')) !!}
                                             <input type="hidden" name="customSuggestionsJson" id="customSuggestionsJson" />
                                             {{ Form::hidden('vendorID', $data->vendor_id, array('id' => 'venID')) }}
                                              {{ Form::hidden('transaction_id', $data->transaction_id) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="pono">
                                        <div class="form-group">
                                            <label for="l30">Invoice Number</label>
                                        <br>
                                        {!! Form::text('invoice_number',  $data->invoice_number, array('class' => 'form-control' , 'readonly' => 'true')) !!}

                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Purchased Date</label>
                                        <br>
                                        {!! Form::text('purchased_at', $data->purchased_at, array('class' => 'form-control datepicker','autocomplete' => 'off', 'data-plugin-options'=>'{"autoclose": true, "maxDate": "today", "endDate": "today", "format": "yyyy-mm-dd"}')) !!}
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Due Date</label><br/>
                                         {!! Form::text('due_date', $data->due_date , array('class' => 'form-control datepicker', 'autocomplete' => 'off', 'data-plugin-options'=>'{"autoclose": true, "startDate": "today", "format": "yyyy-mm-dd"}')) !!}

                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group error-file">
                                        <label for="l30">Purchased Invoice</label>
                                     <div class="input-group ">
                                        <div class="input-group-btn width-90">
                                          <div class="fileUpload btn w-100 btn-default">
                                            <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
                                            <input id="uploadBtn" type="file" class="upload width-90"  name="purchased_invoice" accept="application/pdf,image/png,image/jpeg, image/jpg,application/msword,.doc,.docx,.xlsx"  />
                                          </div>
                                        </div>
                                        <input id="uploadFile"  class="form-control required border bg-transparent" placeholder="Choose File" disabled="true">
                                    </div>
                                      <small>jpeg jpg png pdf can select as Attachment</small><br/>
                                    <a target="_blank" href="<?=URL::to(config('constants.dir.purchased_invoices') . '/' . $data->purchased_invoice)?>"><?=$data->purchased_invoice?></a>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="oldinvoice" value="{{$data->purchased_invoice}}">
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
<script src="<?=URL::to('/');?>/js/autocatch.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script>

     $("#diamond_edit").validate({
            ignore: ":hidden",
            rules: {
                purchased_invoice: {
                    accept:"application/pdf,image/png,image/jpeg, image/jpg,application/msword,.doc,.docx,.xlsx"

                }
            }
        });
   $(document).ready(function() {
 document.getElementById("uploadBtn").onchange = function () {
      document.getElementById("uploadFile").value = this.value.substring(12);
      document.getElementsByName("purchased_invoice").value = this.value.substring(50);
  }

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

</script>
@endsection