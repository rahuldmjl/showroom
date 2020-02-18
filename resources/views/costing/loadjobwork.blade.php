<?php
$loadjobwork = $data['loadjobwork'];
if ($loadjobwork == '0') {
	?>
  <div class="modal-header text-inverse">
    <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 class="modal-title" id="myLargeModalLabel">Without JobWork</h5>
</div>
<div class="modal-body">
        <div id="msg"></div>
        <form method="get" id="add-stone-jobwork" class="text-right px-2" enctype="multipart/form-data">
            <button id="addStoneDetail" class="addStoneDetail pointer add-button-style">Add<i class="material-icons fs-18">add</i></button>
            <input type="hidden" id="stone_ranges_counter" value="0" />
        </form>
        <form method="get" id="VendorStoneDetailform" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div id="MainDivForVendorStone_0" class="CostingVendorSheetData">
                <div class="row">
                <div class="col-md-3 py-2">
                    <label for="diamond_quality">Stone Clarity :</label>
                    <select class="form-control diamond_quality required" id="diamond_quality_0" data-elemnumber="0" name="diamond_quality[]">
                        <option value="" class="dropdon">Select Stone Clarity</option>
                        <?php
foreach ($data['stone_clarity'] as $row) {?>
                            <option value="<?php echo $row->option_id; ?>">
                                <?php echo $row->value; ?>
                            </option>
                            <?php }?>
                    </select>
                </div>
                <div class="col-md-3 py-2">
                    <label for="stone_carat">Shape Carat :</label>
                    <select class="form-control stone_carat required" id="stone_carat_0" name="stone_carat[]">
                        <option value="">Select Shape Carat</option>

                        <?php 
          foreach ($data['stone_carat'] as $row) { ?>
                            <option value="<?php echo $row->stone_carat_from .'-'. $row->stone_carat_to; ?>">
                                <?php echo $row->stone_carat_from .'-'. $row->stone_carat_to; ?>

                            </option>
                            <?php }?>
                    </select>
                </div>
                <div class="col-md-3 py-2">
                    <label for="stone_shape">Stone Shape :</label>
                    <select class="form-control stone_shape required" id="stone_shape_0" data-shapeid="0" name="stone_shape[]">
                        <option value="">Select Stone Shape</option>

                        <?php 
          foreach ($data['stone_shape'] as $row) { ?>

                            <option value="<?php echo $row->option_id; ?>">
                                <?php echo $row->stone_shape; ?>
                            </option>
                            <?php }?>
                    </select>
                </div>
                <div class="col-md-3 form-group py-2">
                    <label for="diamond_gold_price">Price :</label>
                    <input class="form-control vdr_price required" name="diamond_gold_price[]" type="text" value="" id="diamond_gold_price_0"  placeholder="00">
                </div>
            </div>
            </div>
            <div id="loadvendor_others_stonehtml"></div>
            <div class="modal-footer">
    <input type="submit" name="submit" class="withoutjobwork_btn btn btn-info btn-rounded ripple text-left" value="Add Stone Price">
    <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
</div>

</form>
</div>

<?php } else {?>
    <div class="modal-header text-inverse">
        <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
        <h5 class="modal-title" id="myLargeModalLabel">With Jobwork</h5>
    </div>
    <div class="modal-body">
            <div id="msg"></div>
            <form method="post" id="with-jobwork" enctype="multipart/form-data">
            <div class="row">
                <div class="form-group col-12 col-sm-6">
                    <label for="gold_handling">Gold Handling :</label>
                    <input class="form-control required" name="gold_handling" type="text" id="gold_handling" value="" placeholder="100">
                </div>
                <div class="form-group col-12 col-sm-6">
                    <label for="diamond_handling">Round Diamond Handling :</label>
                    <input class="form-control" name="diamond_handling" type="text" value="" id="diamond_handling" required="" placeholder="1000">
                </div>
                <div class="form-group col-12 col-sm-6">
                    <label for="fancy_diamond_handling">Fancy Diamond Handling :</label>
                    <input class="form-control" name="fancy_diamond_handling" type="text" value="" id="fancy_diamond_handling"  placeholder="1000">
                </div>
                <div class="form-group col-12 col-sm-6">
                    <label for="igi_charges">IGI Charges :</label>
                    <input class="form-control" name="igi_charges" type="text" value="" id="igi_charges" placeholder="100">
                </div>
                <div class="form-group col-12 col-sm-6">
                    <label for="hallmarking">Hallmarking Charges :</label>
                    <input class="form-control" name="hallmarking" type="text" value="" id="hallmarking" placeholder="40">
                </div>
                <div class="modal-footer col-12">
        <input type="submit" name="submit" class="withjobwork_btn btn btn-info btn-rounded ripple text-left" value="Submit">
        <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
      </div>
      </div>
      </form>
    </div>
<?php } ?>


<script type="text/javascript">

$(document).ready(function () {

$("#with-jobwork").validate({
    ignore: ":hidden",
    rules: {
        gold_handling: {
            required: true

        },
        diamond_handling: {
            required: true
        }
    },
    submitHandler: function (form) {
      var gold_handling =  $("#costing_popup #gold_handling").val();
      $('#gold_hadling_values').val(gold_handling);
      var diamond_handling =  $("#costing_popup #diamond_handling").val();
      $('#diamond_handling_values').val(diamond_handling);
      var fancy_diamond_handling =  $("#costing_popup #fancy_diamond_handling").val();
      $('#fancy_diamond_handling_values').val(fancy_diamond_handling);
      var igi_charges =  $("#costing_popup #igi_charges").val();
      $('#igi_charges_values').val(igi_charges);
      var hallmarking =  $("#costing_popup #hallmarking").val();
      $('#hallmarking_charges_values').val(hallmarking);

      var vendor_id = jQuery("#vendor_id").val();
      var handlingcharges = jQuery('#with-jobwork').serialize();
       jQuery.ajax({
            type:"GET",
            url:"<?=URL::to('/').'/costing/loadvendorhandlingcharges' ?>",
            data:{'handlingcharges':handlingcharges,"vendor_id" : vendor_id},
            success:function(data){
              if(data == "Added") {
                jQuery("#msg").html('<div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><i class="material-icons list-icon">check_circle</i><strong>Success</strong>: Record has been added successfully.</div>');
              }
              if(data == "Updated") {
                jQuery("#msg").html('<div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><i class="material-icons list-icon">check_circle</i><strong>Success</strong>: Record has been Updated successfully.</div>');
              }
            }
          });
       setTimeout(function(){  $('.close').trigger('click'); }, 2000);
      }
});

$("#VendorStoneDetailform").validate({
    submitHandler: function (form) {
    var count =  jQuery('#stone_ranges_counter').val();
    var vendor_id = jQuery("#vendor_id").val();
    var id = "<?php echo $data['loadjobwork']; ?>";
    var stonedata = jQuery('#VendorStoneDetailform').serialize();
    var getcount = jQuery('#diamond_quality_'+count).val();
    if(getcount != '') {
      if(vendor_id != "") {
        jQuery.ajax({
          type:"GET",
          url:"<?=URL::to('/').'/costing/loadvendorstonehtml' ?>",
          data:{'stonedata':stonedata,"id": id,"vendor_id" : vendor_id},
          success:function(data){
            if(data == "Added") {
              jQuery("#msg").html('<div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><i class="material-icons list-icon">check_circle</i><strong>Success</strong>: Record has been added successfully.</div>');
            }
            if(data == "Updated") {
              jQuery("#msg").html('<div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><i class="material-icons list-icon">check_circle</i><strong>Success</strong>: Record has been Updated successfully.</div>');
            }
          }
        });
        setTimeout(function(){  $('.close').trigger('click'); }, 2000);
      }
      else {
        swal("Please select vendor");
      }
    }
  }
});
});


//jQuery('.stone_shape').change(function()
  jQuery(document).on('change','.stone_shape',function() {
   var datashapeid = jQuery(this).attr('data-shapeid');
   var stn =  jQuery('#diamond_quality_'+datashapeid).val();
   var shp =  jQuery('#stone_shape_'+datashapeid).val();
   var crt =  jQuery('#stone_carat_'+datashapeid).val();
   var vdr =  jQuery("#vendor_id").val();
   jQuery.ajax({
    type :"GET",
    url: "<?=URL::to('/') . '/costing/getStonePrice'?>",
    data:"stn="+stn+"&shp="+shp+"&crt="+crt+"&vdr="+vdr,
    dataType: 'json',
    success:function(data)
    {
      if(data != "0") {
        jQuery("#diamond_gold_price_"+datashapeid).val(data);
      }
      else
      {
        data ="";
        jQuery("#diamond_gold_price_"+datashapeid).val(data);
      }
    }
  });
});


//$(document).on('click','#addStoneDetail',function(e){
$('#addStoneDetail').unbind('click').bind('click', function (e) {
e.preventDefault();
var stone_ranges_counter_val = parseInt(jQuery('#stone_ranges_counter').val())+1;
    jQuery('#stone_ranges_counter').val(stone_ranges_counter_val);
    jQuery.ajax({
      type: "GET",
      url: "<?=URL::to('/') . '/costing/loadvendor_others_stonehtml'?>",
      dataType:"json",
      data:"cnt="+stone_ranges_counter_val,
        success: function(data) {
          $('#loadvendor_others_stonehtml').append(data.html);
        }
    });
});

jQuery(document).on('click','#removebtn_stonedetail',function(e) {
    var id = jQuery(this).attr('data-id');
    e.preventDefault();
    jQuery('#MainDivForVendorStone_'+id).remove();
    jQuery('.btn_remove_'+id).remove();
});


</script>

 