<!-- {!! Form::open(array('url' => action('MetalController@returngoldIssueStore'), 'files'=>true,'method'=>'POST','id'=>'returnGoldIssueForm')) !!} -->
<div class="modal-content">
 	<div class="modal-header text-inverse">
    	<button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
      	<h5 class="modal-title" id="myLargeModalLabel">Return Issue Voucher</h5>
  	</div>
  	<div class="modal-body" > 
		<div class="row">
			<div class="col-lg-4 col-md-6">
			  	<div class="form-group ">
			    	<label for="l30">Total Weight</label>
			      	<input type="number" name="weight" id="total_weight" value="<?php echo $data['metal_weight']; ?>" class="form-control  weight_total" readonly="true">
				</div>
			</div>
		</div>
		<input type="hidden" name="voucher_no" id="voucher_no" value="<?php echo $voucher_no;?>">
			<label>Return Weight : </label>
			<div id="returnDiv">
			<input type="number" name="return_weight"  class="form-control return_weight " value="" id="return_weight" >
			</div>
			<label style="display: none;" id="wieght_validation" class="error">This field is required.</label>
		</div>

	<div class="modal-footer"> 
		<input type="submit" name="Submit" class="return_weight_btn btn_assorting btn btn-info btn-rounded ripple text-left">
		<button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
	</div>
<!-- {{ Form::close() }} -->
<script type="text/javascript">
 $('.return_weight_btn').click(function(e){
	e.preventDefault();
	var return_weight = $('#return_weight').val();
	var voucher_no = $('#voucher_no').val();
	if(return_weight=='') {
        $('#wieght_validation').show();
        return false;
    }else {
       $('#wieght_validation').hide();
    }
	if(return_weight != '') {
		$.ajax({
	    type: "POST",
	    dataType: "JSON",
	    url: '<?=URL::to('/metals/returngoldIssueStore');?>',
	    data:{return_weight:return_weight,voucher_no:voucher_no,_token:"{{ csrf_token() }}"},
	    success: function (response)
          {
          	if(response.status == "true") {
	              swal({
	                title: 'Success!',
	                text: response.message,
	                type: 'success',
	                confirmButtonClass: 'btn btn-success',
	                cancelButtonText: "Cancel",
	              }).then((value) => {
	                 window.location='<?=URL::to('/');?>'+'/diamondraw/issue_voucher_list',
	                 console.log(response);
	              });
	        }
	        else {
	          		swal({
		        	title: 'Oops!',
		            text: response.message,
		          	type: 'error',
		          	showCancelButton: true,
		          	showConfirmButton: false,
		          	confirmButtonClass: 'btn btn-danger',
		          	cancelButtonText: 'Ok'
		        }).catch(swal.noop);
	          }
          	}
	  	});
	}
});
</script>