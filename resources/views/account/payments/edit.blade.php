@extends('layout.mainlayout')

@section('title', 'Edit Payment')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
    <link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  {{ Breadcrumbs::render('accountpayment.edit',$payment) }}
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
            <h5 class="box-title box-title-style mr-b-0">Edit Payment</h5>
              <p class="text-muted">You can modify payment details here in this form</p>
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
                {!! Form::model($payment, ['method' => 'PATCH','route' => ['accountpayment.update', $payment->id],'files'=>'true','id'=>'edit_payment_form']) !!}
                <?php// print_r($payment);exit;?>
                <div class="row">
                  <div class="col-lg-4 col-md-4">
                    <div class="form-group">
                      <label for="l30">Customer Name</label>
                        {!! Form::text('customer_name',$payment->customer_name, array('placeholder' => 'custumer name','class' => 'form-control' ,'enctype'=>"multipart/form-data",'accept-charset'=>"UTF-8",'readonly'=>'true')) !!}
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                      <div class="form-group">
                        <label for="l30">Invoice Number </label>
                          {!! Form::text('invoice_number', null, array('placeholder' => 'invoice number ','class' => 'form-control')) !!}
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                      <div class="form-group">
                        <label for="l30">Invoice Amount</label>
                         {!! Form::number('invoice_amount', null, array('placeholder' => 'invoice amount ','class' => 'form-control' )) !!}
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                      <div class="form-group">
                        <label for="l30">Due Date</label>
                        {!! Form::text('due_date',null, array('class' => 'form-control' ,'disabled','date_format' => 'Y/m/d',)) !!}
                        <input type="hidden" name="due_date" value="{{$payment->due_date}}" >
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                      <div class="form-group">
                        <label for="l30">Payment Form</label>
                          <select name="payment_form" class="form-control">
                            <option value="Incoming"<?php if($payment->payment_form == 'Incoming') { ?> selected="selected"<?php } ?>>Incoming</option>
                            <option value="Outgoing"<?php if($payment->payment_form == 'Outgoing') { ?> selected="selected"<?php } ?>>Outgoing</option>
                          </select>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                      <div class="form-group">
                        <label for="l30">Invoice Attachment</label><br/>
                        <div class="input-group">
                            <div class="input-group-btn width-90">
                              <div class="fileUpload btn w-100 btn-default">
                                <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
                                <input id="uploadBtn" type="file" class="upload width-90"  name="invoice_attachment"/>
                              </div>
                            </div>
                            <input id="uploadFile"  class="form-control border bg-transparent" placeholder="Choose File" disabled="disabled" name="invoice_attachment">
                        </div>
                        <small>jpeg jpg png pdf can select as Attachment</small>
                        <br>
                        Selected File :
                        <a target="_blank" href="<?=URL::to(config('constants.dir.invoice_attachment').'/'.$payment->invoice_attachment)?>"><?=$payment->invoice_attachment?></a>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-4 childdropdown">
                      <div class="form-group">
                        <label for="l30">Payment Sub Header</label>
                        <select name="payment_sub_type" id="payment_child_header" class="form-control">
                          <option value="0" data-parent="0">Select Payment Sub Header</option>
                          @foreach ($paymenttype as   $value)
                          <option <?php if ($payment->payment_sub_type == $value->id) {echo 'selected="selected"';}?> data-parent="{{ $value->parent_id }}" value="{{ $value->id }}">{{ $value->name}}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-4  payment">
                      <div class="form-group">
                        <label for="l30">Payment Header</label>
                          <select name="payment_type" id="paymentvalue"  class="form-control ">
                            <option value="" >Select Parent Header</option>
                            <option value="" selected=""> </option>
                          </select>
                      </div>
                    </div>
                    <input name="customer_type" type="hidden" value="Website"/>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 col-md-12  ">
                      <div class="form-group">
                        <strong>Remarks</strong>
                        {!! Form::textarea('remarks', null, array('placeholder' => 'Remarks','class' => 'form-control' , "rows"=>"3",'accept-charset'=>"UTF-8")) !!}
                      </div>
                    </div>
                  </div>
                  <div class="form-actions btn-list">
                    <button class="btn btn-primary" type="submit">Submit</button>
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
<!-- /.main-wrapper -->

@endsection
@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.js"></script>
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready( function() {

      getparenttype();

      function getparenttype(){
        $(".payment").hide();
          var payment_type = $('#payment_child_header option:selected').attr('data-parent');

           if (payment_type != 0) {
              var request = $.ajax({
              url: '{{action("PaymentController@dropdown")}}',
              type: "GET",
              data: {payment_type:payment_type},
              dataType:"html"
            });
            request.done(function( response ) {

              if(JSON.parse(response).parent != ''){
                $('.payment_type').show();
                $(".payment").show();
                var parentdata = JSON.parse(response).parent;
                //console.log(data);
                var data = {parentdata};
                var formoption = "";
                $.each(data, function(v) {
                  var optionval = data[v];
                  formoption += "<option value='" + optionval.id + "'>" + optionval.name + "</option>";
                });
                $('#paymentvalue').html(formoption);
              }else{
                $(".payment").hide();
              }
            });
          }
      }

    $(".payment").hide();
   $('.childdropdown').change(function(){
        getparenttype();
    });

     document.getElementById("uploadBtn").onchange = function () {
      document.getElementById("uploadFile").value = this.value.substring(12);
      document.getElementByName("invoice_attachmenti").value = this.value.substring(50);
};
   $("#edit_payment_form").validate({
        ignore: ":hidden",
          rules: {
             customer_name: {
                required:true 
                
            },
            invoice_number: {
                required:true,
                min:1
                
            },
            invoice_amount: {
                required: true,
                min:1
            },
            due_date: {
                required: true
            },
            payment_form: {
                required: true
            },
            payment_sub_type: {
                required: true
            },
            payment_type: {
              required: true
            },
          },
        });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>

  });

</script>
@endsection

