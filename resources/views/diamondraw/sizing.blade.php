<?php
$data;
?>
 <div class="modal-header">

            <h5 class="modal-title" id="myLargeModalLabel">Sizing</h5>
            <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body payment_process" >
           <div class="col-lg-4 col-md-6">
              <div class="form-group ">
                <label for="l30">Total Weight</label>
                  @foreach ($data as $row)
                    <input type="number" step="0.001" name="weight" id="total_weight" value="{{$row->assorting_weight}}" class="form-control  weight_total" readonly="true" >
                  @endforeach
              </div>
            </div>
        </div>
            {!! Form::open(array('url' => action('DiamondRawController@sizing_transaction'), 'files'=>true,'method'=>'POST','id'=>'sizing')) !!}


                     @foreach ($data as $row)
                     {!! Form::hidden('id',$row->id, null, array('class' => 'form-control myform' ,'accept-charset'=>"UTF-8")) !!}
                     @endforeach

                      <div class="row">
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group ">
                               <label for="l30">Remaining Weight</label>
                                 @foreach ($data as $row)
                                <input type="number" step="0.001" name="sizing_weight" id ="weight" value="" class="form-control weightdata weightcount" >
                               @endforeach
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group ">
                             <label for="l30">Rejected</label>
                                <input type="number" step="0.001" name="sizing_rejected" value="" id="reject" class="form-control rejectdata weightcount" >
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                             <label for="l30">Loss</label>
                                <input type="number" step="0.001" name="sizing_loss" value="" id="loss" class="form-control  lossdata weightcount" >
                            </div>
                        </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6 col-md-6">
                            <div class="form-group lossreason">
                             <label for="l30">Comment/Reason</label>
                                <textarea type="textarea" name="sizing_loss_reason" value=""  class="form-control"></textarea>
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
  var a = $('#total_weight').val();
    var ttlWt_val= parseInt(a.toString().split(".")[0]);
    var minVal = 0;
    $(document).ready( function() {
        $("#sizing").validate({
            ignore: ":hidden",
            rules: {
               sizing_weight: {
                    required: true,
                    min:minVal,
                    max:ttlWt_val
                },
                sizing_rejected: {
                    min:minVal,
                    max:ttlWt_val

                },
                sizing_loss: {
                    min:minVal,
                    max:ttlWt_val

                },
                sizing_loss_reason:{
                  required:true
                }

            }
    });

     $('.lossreason').hide();
   $(".weightcount").change(function() {

        var weight = $("#weight").val();
        var reject = $("#reject").val();
        var loss = $("#loss").val();
        var total_weight = $("#total_weight").val();

        if($(this).hasClass('weightdata'))
        {
          var weightType = "weight";
        }
        else if($(this).hasClass('rejectdata')) {
          var weightType = "reject";
        }
        else {
         var weightType = "loss";
        }

    if(ttlWt_val >= weight && ttlWt_val >= loss && ttlWt_val >= reject && weight >= 0 && reject >= 0 && loss >= 0) {
       jQuery.ajax({
          type: "GET",
          dataType: "json",
          url: "{{action('DiamondRawController@cvdcalculation')}}",
          data: {
          "_token": '{{ csrf_token() }}',
          "weight":weight,
          "reject":reject,
          "loss":loss,
          "weightType":weightType,
          "total_weight":total_weight,
        },
          success: function(response) {
              $('#reject').val(response.total_reject);
              $('#loss').val(response.total_loss);
              $('#weight').val(response.total_complete);

              if(response.total_loss > 0)
              {
                  $('.lossreason').show();
                   $("#sizing").validate({
                      ignore: ":hidden",
                      rules: {
                         sizing_weight: {
                              required: true,
                              min:minVal,
                              max:ttlWt_val
                          },
                          sizing_rejected: {
                              min:minVal,
                              max:ttlWt_val

                          },
                          sizing_loss: {
                              min:minVal,
                              max:ttlWt_val

                          },
                          sizing_loss_reason:{
                            required:true
                          }

                      }
              });
              }
              if(response.total_loss == 0)
              {
                  $('.lossreason').hide();
              }
          }


       });
     }
   });


});
</script>
