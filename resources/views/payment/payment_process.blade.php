<?php
$payment = $data['payment'];
?> <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-header text-inverse">
            <h5 class="modal-title" id="myLargeModalLabel">Payment</h5>
        </div>
        <div class="modal-body payment_process" >
            {!! Form::open(array('url' => action('PaymentController@payment_transaction'), 'files'=>true,'method'=>'POST','id'=>'payment_process')) !!}


                     @foreach ($data['payment'] as $row)
                     {!! Form::hidden('payment_id',$row->id, null, array('class' => 'form-control' ,'accept-charset'=>"UTF-8")) !!}
                     @endforeach

                     <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                         <label for="l30">Receipt Number</label>
                         <input type="number" name="invoice_number" value="" class="form-control" >
                       </div>
                     </div>
                     <div class="col-md-6">
                      <div class="form-group">
                       <label for="l30">Invoice Amount</label>
                       @foreach ($data['payment'] as $row)
                       <input type="number" name="invoice_amount" value="{{$row->pending}}" class="form-control" readonly='true' id="pending">
                       @endforeach
                     </div>
                   </div>
                   <div class="col-md-6">
                        <div class="form-group">
                          <label for="l30">Invoice Attachment</label>
                              <div class="input-group">
                                <div class="input-group-btn width-90">
                                  <div class="fileUpload btn w-100 btn-default">
                                    <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
                                    <input id="uploadBtn" type="file" class="upload"  name="invoice_attachment"  accept="image/jpeg , image/jpg, image/gif, image/png,application/pdf" />
                                  </div>
                                </div>
                                <input id="uploadFile" name="uploadFile" class="form-control border bg-transparent" placeholder="Choose File" disabled="disabled">
                            </div>
                        </div>
                        </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="l30">Amount Pay</label>
                      <input type="number" id="paid_amount" name="paid_amount" value="" class="form-control" >
                    </div>
                  </div>
                  <div class="col-md-6" id="pending_status">
                    <div class="form-group">
                      <label for="l30">Status</label>
                      {!! Form::select('payment_form', ['Pending' => 'Pending'], null,array('id' => 'payment_form_pending', 'class' => 'form-control')) !!}
                    </div>
                  </div>
                  <div class="col-md-6" id="paid_status">
                    <div class="form-group">
                      <label for="l30">Status</label>
                      {!! Form::select('payment_form', ['Bank Paid' => 'Bank Paid', 'Cash Paid' => 'Cash Paid'], null,array('id' => 'payment_form_paid', 'class' => 'form-control', 'disabled'=>'disabled')) !!}
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <strong>Remarks</strong>
                      {!! Form::textarea('comment', null, array('placeholder' => 'Remarks','class' => 'form-control' , "rows"=>"3",'accept-charset'=>"UTF-8")) !!}
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <input type="submit" name="Add Payment" class="btn btn-info btn-rounded ripple text-left">
                  <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
                </div>
                    {{ Form::close() }}
            </div>



<script type="text/javascript">
    $(document).ready( function() {

        $('#pending_status').hide();
        $('#paid_status').hide();

        $("#payment_process").validate({
            ignore: ":hidden",
            rules: {
               invoice_number: {
                    required: true,
                    min:1

                },
                invoice_attachment: {
                    required: true

                },
                paid_amount: {
                    required: true,
                    max: function() {
                        return parseInt($('#pending').val());
                      }

                },
                comment: {
                  required: true
                }

            },
            messages: {
              invoice_number:{
                required:"Enter Invoice Number",
              },
              paid_amount:{
                required: "Enter Payable Amount",
                min:"Please enter a value greater than or equal to 1.",
                max:"Please enter a value less than or equal to Invoice Amount."
              }

        }
    });

        $('#paid_amount').change(function(){
          var paid_amount_val = parseFloat($('#paid_amount').val());
          var pending_amount_val = parseFloat($('#pending').val());

          if(paid_amount_val > 0){
            if(paid_amount_val == pending_amount_val){
              $('#paid_status').show();
              $('#pending_status').hide();
              $('#payment_form_paid').attr('disabled', false);
              $('#payment_form_pending').attr('disabled', 'disabled');
            } else {
              $('#paid_status').hide();
              $('#pending_status').show();
              $('#payment_form_paid').attr('disabled', 'disabled');
              $('#payment_form_pending').attr('disabled', false);
            }
          } else {
            $('#pending_status').hide();
            $('#paid_status').hide();
          }

        });

          document.getElementById("uploadBtn").onchange = function () {
          document.getElementById("uploadFile").value = this.value.substring(12);
              };

});
</script>
