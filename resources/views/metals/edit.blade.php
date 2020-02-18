@extends('layout.mainlayout')

@section('title', 'Edit Metal')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
    <link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('gold_edit_transaction',$transaction->id) }}
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
                        <h5 class="box-title box-title-style mr-b-0">Edit Metal</h5>
                        <p class="text-muted">You can edit metal by filling this form</p>

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

                        {!! Form::model($transaction, ['method' => 'PATCH','route' => ['metaltransaction.update', $transaction->id],'files'=>'true','id'=>'metaltransaction_form']) !!}
                            <div class="row ">
                               
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Metal Type</label>
                                        @if($transaction->metal_type == 1)
                                        {!! Form::select('metal_type', ['1' => 'GOLD 24K'],[], array('class' => 'form-control', 'disabled', 'id' => 'metal_type')) !!}
                                        @else
                                        {!! Form::select('metal_type', [ '2' => 'PLATINUM 950'],[], array('class' => 'form-control', 'disabled' , 'id' => 'metal_type')) !!}
                                       
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Metal Weight (gms)</label>
                                        {!! Form::number('metal_weight', null, array('placeholder' => 'Metal Weight','class' => 'form-control', 'step' => '0.001','min' => '0.5')) !!}
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="vendorhid">
                                        <div class="form-group">
                                            <label for="l30">Vendor Name</label>
                                            {!! Form::text('vendor_display', $transaction->vendor->name, array('class' => 'form-control', 'disabled','id'=>'search_text')) !!}
                                        </div>
                                    </div>
                                    {{ Form::hidden('vendor_name', $transaction->vendor->name, array('class' => 'vendorName', 'id' => 'vendorName')) }}
                                    {{ Form::hidden('vendorId', $transaction->vendor_id, array('id' => 'venID')) }}
                                    {{ Form::hidden('measurement', '2') }}
                                </div>
                                <div class="col-lg-4" style="display: none;">
                                    <label for="l30">&nbsp;</label>
                                    <!-- {!! Form::select('measurement', ['1' => 'mm', '2' =>'gm', '3' => 'kg'],[], array('class' => 'form-control')) !!} -->
                                    <!-- {!! Form::select('measurement', ['2' =>'gm'],[], array('class' => 'form-control')) !!} -->
                                </div>
                            </div>
                            <div class="row editgoldinventory">
                               <!-- <div class="col-lg-4">
                                     <div class="form-group">
                                        <label for="l30">Transaction Type</label>
                                        {!! Form::select('transaction_type', $transactionTypes,[], array('class' => 'form-control', 'disabled')) !!}
                                    </div> 
                                </div> -->
                                    <div class="col-lg-4" style="display: none;">
                                    <div class="form-group">
                                        <label for="l30">Transaction Type</label>
                                        <select class="form-control" name="transaction_type">
                                            <option value="1" selected="selected">Purchase</option>
                                        </select>
                                    </div>
                                    </div>


                                
                                <div class="col-lg-4" style="display: none;">
                                    <div class="form-group">
                                        <label for="l30">Amount Paid</label>
                                        <div class="input-group">
                                            {!! Form::number('amount_paid', null, array('placeholder' => 'Amount Paid','class' => 'form-control', 'step' => '0.001')) !!}
                                            <span class="input-group-addon"><i class="list-icon fa fa-inr"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Metal Rate (per gm)</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="list-icon fa fa-inr"></i></span>
                                            {!! Form::number('metal_rate', null, array('placeholder' => '3200','class' => 'form-control', 'step' => '0.001')) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Purchased Date</label>
                                        <br>
                                        {!! Form::text('purchased_at', null, array('class' => 'form-control datepicker', 'data-plugin-options'=>'{"autoclose": true, "maxDate": "today", "endDate": "today", "format": "yyyy-mm-dd"}')) !!}
                                    </div>
                                     </div>
                                     <div class="col-lg-4 ">
                                    <div class="form-group error-file">
                                          <label for="l30">Purchased Invoice</label><br/>
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
                                    <a target="_blank" href="<?=URL::to(config('constants.dir.purchased_invoices') . '/' . $transaction->purchased_invoice)?>"><?=$transaction->purchased_invoice?></a>
                                    </div>
                                </div>
                                
                                  

                               
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Invoice Number</label>
                                        <br>
                                        {!! Form::text('invoice_number', null, array('class' => 'form-control')) !!}
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Advance Payment</label>
                                        <br>
                                        <input type="checkbox" name="advance_payment" id="advance_payment" value="1" <?= ($transaction->advance_payment == '1')?"checked":""  ?> >
                                    </div>
                                </div>
                                <div class="col-lg-4 due_date">
                                    <div class="form-group">
                                        <label for="l30">Due Date</label>
                                        <br>
                                        {!! Form::text('due_date', null, array('autocomplete'=>'off', 'class' => 'form-control datepicker', 'data-plugin-options'=>'{"autoclose": true, "startDate": "today", "format": "yyyy-mm-dd"}')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="display: none;">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="l30">PO. No.</label>
                                        {!! Form::text('po_number', null, array('class' => 'form-control')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4" id="goldTypeInput">
                                    <div class="form-group">
                                        <label for="l30">Gold Type</label>
                                        <select name="gold_type" class="form-control">
                                            <option value="999" <?php if($transaction->gold_type =='999') { echo "selected"; }?>>999</option>
                                            <option value="995" <?php if($transaction->gold_type =='995') { echo "selected"; }?> >995</option>
                                        </select>
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
                                <button class="btn btn-primary" type="submit">Save</button>
                                <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
                            </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script type="text/javascript">
    $(document).ready( function() {

    jQuery.validator.addMethod("lettersandspace", function(value, element) {
        return this.optional(element) || /^[a-z\s]+$/i.test(value);
    }, "Only letters and space allowed");

      $("#metaltransaction_form").validate({
            ignore: ":hidden",
            rules: {
                purchased_invoice:{
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
       
           document.getElementById("uploadBtn").onchange = function () {
            document.getElementById("uploadFile").value = this.value.substring(12);
            document.getElementsByName("purchased_invoice").value = this.value.substring(50);
        }
    /*Check advance payment*/
    $('#advance_payment').on('click',function () {
        if($('#advance_payment').is(":checked")) {
            $('.due_date').hide();
        }
        else {
            $('.due_date').show();   
        }
    });

        if($('#advance_payment').is(":checked")) {
            $('.due_date').hide();
        }
        else {
            $('.due_date').show();   
        }

            if($('#metal_type').val() == "2"){
                $("#goldTypeInput").hide();
            } else {
                $("#goldTypeInput").show();
            }
        

    });
</script>
@endsection