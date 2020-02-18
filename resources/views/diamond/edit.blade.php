@extends('layout.mainlayout')

@section('title', 'Edit Diamond Transaction')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('diamond.edit',$diamondTransactions->id) }}
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
                        <h5 class="box-title box-title-style mr-b-0">Edit Diamond Transaction</h5>
                        <p class="text-muted">You can add diamond transaction by filling this form</p>

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

                        {!! Form::model($diamondTransactions,array('route' => ['diamond.update', $diamondTransactions->id],'method'=>'PATCH', 'files'=>'true','id'=>'diamond_edit')) !!}
                            <?php //echo "<pre>";print_r($diamondTransactions); ?>
                            <div class="row">
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Shape</label>
                                        <select class="form-control stone_shape disabled" id="stone_shape" name="stone_shape">
                                            <option value="">Select Shape</option>
                                            <?php 
                                            foreach ($data['stone_shape'] as $row) { ?>
                                            <option value="<?php echo $row->stone_shape; ?>" <?php if($diamondTransactions->stone_shape == $row->stone_shape){ ?>selected='selected' <?php } ?>><?php echo $row->stone_shape; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Quality</label>
                                        <select class="form-control diamond_quality disabled" id="diamond_quality" name="diamond_quality">
                                            <option value="">Select Quality</option>
                                            <?php 
                                            foreach ($data['stone_clarity'] as $row) { ?>
                                            <option value="<?php echo $row->value; ?>" <?php if($diamondTransactions->diamond_quality == $row->value){ ?>selected='selected' <?php } ?>><?php echo $row->value; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                   <div class="form-group">
                                        <label for="l30">MM Size</label>
                                        {!! Form::number('mm_size', null, array('placeholder' => 'MM Size','class' => 'form-control disabled', 'step' => '0.001','min' => '0.5')) !!}
                                    </div> 
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                  <div class="form-group">
                                        <label for="l30">Sieve Size</label>
                                        {!! Form::number('sieve_size', null, array('placeholder' => 'Sieve Size','class' => 'form-control disabled sieve_size', 'step' => '0.001')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Diamond Weight (cts)</label>
                                        {!! Form::number('diamond_weight', null, array('placeholder' => 'Diamond Weight','class' => 'form-control', 'step' => '0.001','min' => '0.5')) !!}
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Rate (per carat)</label>
                                        <div class="input-group">
                                            {!! Form::number('rate', null, array('placeholder' => 'Rate','class' => 'form-control', 'step' => '0.001','min'=>'0.000')) !!}
                                            <span class="input-group-addon"><i class="list-icon fa fa-inr"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="w-100">
                            <div class="row editgoldinventory">
                               <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="vendorhid">
                                    <div class="form-group">
                                        <label for="l30">Vendor Name</label>    
                                        <?php $getVendorName = App\User::where('id',$diamondTransactions->vendor_id)->first();
                                            $vendorName = $getVendorName->name; ?>
                                            <input type="text" value="<?php echo $vendorName; ?>" class="form-control disabled" id="search_text"  name="vendor_id" >    
                                        </div>
                                    </div>                                    
                                </div>
                                 <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Invoice Number</label>
                                        <br>
                                        {!! Form::text('invoice_number', null, array('class' => 'form-control disabled')) !!}
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Purchased Date</label>
                                        <br>
                                        {!! Form::text('purchased_at', null, array('class' => 'form-control disabled datepicker', 'data-plugin-options'=>'{"autoclose": true, "maxDate": "today", "endDate": "today", "format": "yyyy-mm-dd"}')) !!}
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                          <label for="l30">Due Date</label><br/>
                                         {!! Form::text('due_date', null, array('class' => 'form-control datepicker disabled', 'autocomplete' => 'off', 'data-plugin-options'=>'{"autoclose": true, "startDate": "today", "format": "yyyy-mm-dd"}')) !!}
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 ">
                                    <div class="form-group error-file">
                                        <label for="l30">Invoice Attachment</label>
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
                                    <a target="_blank" href="<?=URL::to(config('constants.dir.purchased_invoices') . '/' . $Transactions[0]->purchased_invoice)?>"><?=$Transactions[0]->purchased_invoice?></a>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 ">
                                    <div class="form-group">
                                        <label for="l30">Amount Paid</label>
                                        <div class="input-group">
                                            {!! Form::number('amount_paid_with_gst', null, array('placeholder' => 'Amount Paid','class' => 'form-control', 'step' => '0.001','min'=>'0.000')) !!}
                                            <span class="input-group-addon"><i class="list-icon fa fa-inr"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3" style="display: none;">
                                    <div class="form-group">
                                        <label for="l30">Transaction Type</label>
                                        <select class="form-control transactionsele" name="transaction_type">
                                        <option value="1" selected>Purchase</option>
                                        </select>
                                    </div>
                                </div> 
                                <div class="col-lg-6" style="display: none;">
                                    <div class="form-group">
                                        <label for="l30">PO. No.</label>
                                        {!! Form::text('po_number', null, array('class' => 'form-control')) !!}
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
                                <button class="btn btn-primary" type="submit">Save</button>
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
/* $(document).on('blur', '#mm_size_'+i, function(){

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
  */
    /* For Transaction Type Change Event - start */
    
    /* if($('.transactionsele').val() == 2 || $('.transactionsele').val() == 5){
        $(".vendorhid").show();
        $(".pono").show();
    }else{
        $(".pono").hide();
        $(".vendorhid").hide();
    }
    
    $('.transactionsele').on('change', function (e) {
        if($('.transactionsele').val() == 2 || $('.transactionsele').val() == 5){
            $(".vendorhid").show();
            $(".pono").show();
        }else{
            $(".pono").hide();
            $(".vendorhid").hide();
        }
    }); */
    /* For Transaction Type Change Event - end */

    src = "{{ route('searchajax') }}";
     $("#search_text").autocomplete({
        
        source: function(request, response) {
            $.ajax({
                url: src,
                dataType: "json",
                data: {
                    term : request.term
                },
                success: function(data) {
                    response(data);                   
                }
            });
        },
        select: function (event, ui) {//trigger when you click on the autocomplete item
            $("#venID").val(ui.item.id);
        },
        minLength: 3,
       
    });
});
</script>
@endsection