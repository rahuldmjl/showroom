<?php
$data;
$setting_data;
?>
{!! Form::open(array('url' => action('DiamondRawController@cvd_transaction'), 'files'=>true,'method'=>'POST','id'=>'showdetail')) !!}
<div class="modal-header text-inverse">
  <h5 class="modal-title" id="myLargeModalLabel">CVD</h5>
  <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body" >
  <div class="errors"></div>
  <div class="row">
    <div class="col-lg-4 col-md-6">
      <div class="form-group ">
        <label for="l30">Total Weight</label>
        @foreach ($data as $row)
          <input type="number" name="weight" id="total_weight" value="{{$row->total_weight}}" class="form-control  weight_total" readonly="true" >
        @endforeach
      </div>
    </div>
  </div>
  @foreach ($data as $row)
    {!! Form::hidden('id',$row->id, null, array('class' => 'form-control myform' ,'accept-charset'=>"UTF-8")) !!}
  @endforeach
  <div class="row">
    <div class="col-lg-4 col-md-4">
      <div class="form-group ">
        <label for="l30">CVD Completed</label>
        @foreach ($data as $row)
          <input type="number" name="total_weight" id ="weight" value="" step="0.001" class="form-control weightdata weightcount" min='0.000'>
        @endforeach
      </div>
    </div>
    <div class="col-lg-4 col-md-4">
      <div class="form-group ">
       <label for="l30">Rejected</label>
        <input type="number" name="rejected" value="" id="reject" step="0.001" class="form-control rejectdata weightcount" min = '0.000' >
      </div>
    </div>
    <div class="col-lg-4 col-md-4">
      <div class="form-group">
        <label for="l30">Misc Loss</label>
        <input type="number" step="0.001" name="cvd_loss" value="" id="loss" class="form-control lossdata weightcount"  min="0.000">
        <input type="hidden" name="setting" value="{{$setting_data[0]->value}}" id="loss" class="form-control settingvalue" >
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-6 col-md-6">
      <div class="form-group lossreason">
        <label for="l30">Comment/Reason</label>
        <textarea type="textarea" name="cvd_loss_reason" value=""  class="form-control "></textarea>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <input type="submit" name="Add Payment" class="btn_cvd btn btn-info btn-rounded ripple text-left">
  <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
</div>
{{ Form::close() }}
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script type="text/javascript">
  var a = $('#total_weight').val();
  //var ttlWt_val= parseInt(a.toString().split(".")[0]);
  var ttlWt_val= parseFloat(a);
  var minVal = 0;
  $(document).ready( function() {
   $('.lossreason').hide();
   $('.lossdata').change(function() {
      var loss =  CheckNan("#loss");
      if(loss >0){
        $('.lossreason').show();
        $("#showdetail").validate({
          ignore: ":hidden",
          rules: {
            total_weight: {
              required: true,
              min:minVal,
              max:ttlWt_val
            },
            rejected: {
              min:minVal,
              max:ttlWt_val

            },
            cvd_loss: {
              min:minVal,
              max:ttlWt_val

            },
            cvd_loss_reason:{
              required:true
            }
          }
        });
      }
    });
    $('.lossreason').hide();
    $('.btn_cvd').click(function() {

      var weight =  CheckNan("#weight");
      var reject =  CheckNan("#reject");
      var loss =  CheckNan("#loss");
      var totalweight =parseFloat($(".weight_total").val());
      var a = $('#total_weight').val();
      //var total_weight= parseFloat(a.toString().split(".")[0]);
      var total_weight= parseFloat(a).toFixed(3);
      console.log(total_weight);
      console.log(weight);
      console.log(reject);
      console.log(loss);
      var calcultedtotl = parseFloat(weight + parseFloat(reject) + parseFloat(loss)).toFixed(3);
      console.log(calcultedtotl);
      if(parseFloat(calcultedtotl) != total_weight ) {
         console.log(calcultedtotl);
          console.log(total_weight);
        swal({title:"You have added "+calcultedtotl+" weight , instead of  "+totalweight});
       
        event.preventDefault();
      }
      if(reject == "") {
        $("#reject").val(0);
      }
      if(loss == "") {
        $('.lossreason').hide();
        $("#loss").val(0);

      }
    });

    $(".lossdata").change(function() {
      var setting_val = $('.lossdata').val();
      var loss= $('.settingvalue').val();
      if( loss < setting_val){
        swal({
          title: 'Exceeded Limit',
          text: "You have exceeded limit of loss , can't move ahead !!",
          type: 'warning',
          showCancelButton: true,
          confirmButtonClass: 'btn btn-warning',
          confirmButtonText: 'Yes, Extend Limit!'
          }).then(function (inputvalue) {
          /*ACTION PENDING*/

          },function(dismiss){
          if(dismiss == 'cancel'){
          var loss = $('.lossdata').val();
          $('.lossdata').val(0);
          $('.lossreason').hide();
          var Weightcom= $('#weight').val()
          var weightraw = $('#reject').val()
          // var total = Number(Weightcom) + Number(loss) ;
          // $('.weightdata').val(total);
          }
        });
      }
    });
  });
</script>
