<?php
//$data;
?>
{!! Form::open(array('url' => action('DiamondRawController@assorting_transaction'), 'files'=>true,'method'=>'POST','id'=>'assorting')) !!}
<div class="modal-header text-inverse">
  <h5 class="modal-title" id="myLargeModalLabel">Assorting</h5>
  <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body" >
  <div class="errors"></div>
  <div class="row">
    <div class="col-lg-4 col-md-6">
      <div class="form-group ">
        <label for="l30">Total Weight</label>
        @foreach ($data as $row)
          <input type="number" name="weight" id="total_weight" value="{{$row->cvd_weight}}" class="form-control  weight_total" readonly="true" >
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
        <label for="l30">Remaining Weight</label>
        @foreach ($data as $row)
          <input type="number" name="assorting_weight" id ="weight" step="0.001" value="" class="form-control weightdata weightcount" min="0.000">
        @endforeach
      </div>
    </div>
    <div class="col-lg-4 col-md-4">
      <div class="form-group ">
        <label for="l30">Rejected</label>
        <input type="number" name="assorting_rejected" value="" id="reject" step="0.001" class="form-control rejectdata weightcount" min="0.000" >
      </div>
    </div>
    <div class="col-lg-4 col-md-4">
      <div class="form-group">
        <label for="l30">Misc Loss</label>
        <input type="number" name="assorting_loss" value="" id="loss" step="0.001" class="form-control  lossdata weightcount" min="0.000" >
        <input type="hidden" name="setting" value="{{$setting_data[0]->value}}" id="loss" class="form-control settingvalue" >
      </div>
    </div>
  </div>
  <input type="hidden" name="loss" value="{{$loss}}" class="diamondloss">
  <div class="row">
    <div class="col-lg-6 col-md-6">
      <div class="form-group lossreason">
        <label for="l30">Comment/Reason</label>
        <textarea type="textarea" name="assorting_loss_reason" value=""  class="form-control"></textarea>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <input type="submit" name="Add Payment" class="btn_assorting btn btn-info btn-rounded ripple text-left">
  <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
</div>
{{ Form::close() }}
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script type="text/javascript">
  var a = $('#total_weight').val();
  var ttlWt_val= parseFloat(a);
  var minVal = 0;
  $(document).ready( function() {
    var validateAssorting = function(){
      $("#assorting").validate({
        ignore: ":hidden",
        rules: {
         assorting_weight: {
          required: true,
            min:minVal,
            max:ttlWt_val

          },
          assorting_rejected: {
              min:minVal,
              max:ttlWt_val
          },
          assorting_loss: {
              min:minVal,
              max:ttlWt_val

          },
          assorting_loss_reason:{
            required:true
          }
        }
      });
    }
    $('.lossreason').hide();
    var validateAssortingData = function(){
      var loss = CheckNan("#loss");
        if(loss >0){
          $('.lossreason').show();
          //validate function for assorting blade
          validateAssorting();
        }
    }

    $('.lossreason').hide();
    $(".lossdata").change(function() {
      //validate assorting data
      validateAssortingData();
      var setting_val = parseFloat($('.lossdata').val()) + parseFloat($('.diamondloss').val());
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
    
    $('.btn_assorting').click(function() {
      var weight = CheckNan("#weight");
      var reject = CheckNan("#reject");
      var loss = CheckNan("#loss");
      var totalweight =parseFloat($(".weight_total").val());
      var a = $('#total_weight').val();
      var total_weight= parseFloat(a);
      console.log(weight);
      console.log(reject);
      console.log(loss);
      var calcultedtotl = parseFloat(weight + parseFloat(reject) + parseFloat(loss));
      console.log(calcultedtotl);
      if((calcultedtotl).toFixed(3) != total_weight) {
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
      console.log("dsfkldfgmndfg");
    });
  });
</script>
