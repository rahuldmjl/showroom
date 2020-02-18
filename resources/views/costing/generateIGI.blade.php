<div class="modal-header text-inverse">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h5 class="modal-title" id="myLargeModalLabel">Generate IGI</h5>
  </div>
  <div class="modal-body igi_popup_body">
	<div id="msg"></div>
  	    <form method="get" id="form_generate_igi" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div id="generate_igi" class="generate_igi">
                <div class="row">
                	<div class="col-12 form-group py-2">
                    <!--Start to set petten -->
                     <?php
$yearVar = date("y");
$month = date("m");
$dataVar = $yearVar . $month;
?>
                    <label for="certificate_no">Certificate No : (ex:  (ABC)-(123)-(DE)- (<?php echo $dataVar ?> ) )</label>
                    <div class="row">

                      <div class="col-12 col-lg-3">
                        <input placeholder="ABC" class="form-control certificate_no required" name="certificate_no1" type="text" value="" id="certificate_no1" style="text-transform:uppercase">
                      </div>
                      <div class="col-12 col-lg-3">
                         <input placeholder="123" class="form-control certificate_no required" name="certificate_no2" type="text" value="" id="certificate_no2">
                      </div>
                      <div class="col-12 col-lg-3">
                         <input placeholder="DE" class="form-control certificate_no required" name="certificate_no3" type="text" value="" id="certificate_no3" style="text-transform:uppercase">
                      </div>
                      <div class="col-12 col-lg-3">
                        <?php
$yearVar = date("y");
$month = date("m");
$dataVar = $yearVar . $month;
?>
                        <input placeholder="<?=$dataVar;?>" class="form-control certificate_no required disabled" name="certificate_no4" type="text" value="<?=$dataVar;?>" id="certificate_no4">
                      </div>
                      <!--End to set petten -->
                    </div>

                    <!-- <input class="form-control certificate_no required" name="certificate_no" type="text" value="" id="certificate_no"> -->
                </div>
                <div class="col-12 py-2">
                    <label for="branding">Branding :</label>
                    <select class="form-control branding required" id="branding" data-elemnumber="0" name="branding">
                        <option value="" class="dropdon">Select branding</option>
          				<option value="diamondmela">DIAMONDMELA</option>
                        <option value="igi">IGI</option>
                    </select>
                </div>

           		</div>
            </div>

      <div class="modal-footer">
    <input type="submit" class="btn btn-info btn-rounded ripple text-left generate_igi_btn" value="Submit" />
    <button type="button" class="igi_popup_close btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
  </div>
</form>
</div>

 <script type="text/javascript">

function getqcCount() {
  jQuery.ajax({
    url: "<?=URL::to('/') . '/costing/qccount'?>",
    success : function(data) {
     $('.qcrejectcount').html(data.qcrejectcount);
      $('.qcacceptcount').html(data.qcacceptcount);
      $('.qcigicount').html(data.qcigicount);
      $('.qcrequestinvoice').html(data.qcrequestinvoice);
      $('.qcreturnmemo').html(data.qcreturnmemo);
      $('.qccostingproductcount').html(data.qccostingproductcount);
    }
  });
}

  $("#form_generate_igi").validate({
    rules: {
        certificate_no1: {
            required: true,
            minlength:3,
            maxlength:3
      },
      certificate_no2: {
            required: true,
            minlength:3,
            maxlength:3,
            digits: true
      },
      certificate_no3: {
            required: true,
            minlength:2,
            maxlength:2
      },
      certificate_no4: {
            required: true,
            minlength:4,
            maxlength:4

      },
        branding: {
            required: true
        }
    },
    submitHandler: function (form) {

    var branding = $("#branding option:selected").val();
    var certificate_petten1 = $("#certificate_no1").val();
    var certificate_petten2 = $("#certificate_no2").val();
    var certificate_petten3 = $("#certificate_no3").val();
    var certificate_petten4 = $("#certificate_no4").val();
    var url = "<?=URL::to('/') . '/costing/generateCertificate'?>";
    var chkCostingIds = "<?=(is_array($chkCostingIds)) ? $chkCostingIds = implode(",", $chkCostingIds) : $chkCostingIds;?>";

      $.ajax({
      type: "GET",
            url: url,
            dataType: "json",
            data: {
              "_token": '{{ csrf_token() }}',
              "branding": branding,
              "certificate_petten1": certificate_petten1,
              "certificate_petten2": certificate_petten2,
              "certificate_petten3": certificate_petten3,
              "certificate_petten4": certificate_petten4,
              "chkCostingIds" :chkCostingIds,
            },
            success: function(data) {

              if(data.status == 'error') {
                swal("Cancelled", data.igi_certified, "error");
              }
              else {
               // swal("Success!",data.message, "success");
                window.location.href = "<?=URL::to('/').'/costing/IGIlist' ?>";
              }
              setTimeout(function() {
                jQuery('.igi_popup_close').trigger('click');
              },1000);
              //dataTable.ajax.reload();
              getqcCount();
            }
          });
        }
    });



 </script>