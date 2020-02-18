<div class="modal-content">
 	<div class="modal-header text-inverse">
    	<button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
      	<h5 class="modal-title" id="myLargeModalLabel">Return Issue Voucher</h5>
  	</div>
  	
  		<div class="modal-body"> 
  			<?php 
  			$i = 0;
  			foreach($datas as $data ) { ?>
			<div  class="row">
				<div class="col-sm-2">
					<label  class="mr-b-02" for="l30">Shape <br><?php echo $data['stone_shape']; ?></label>
				</div>
				<div class="col-sm-2">
					<label  class="mr-b-02" for="l30">Quality <br><?php echo $data['diamond_quality']; ?></label>
				</div>
				<div class="col-sm-2">
					<label  class="mr-b-02" for="l30">MM/Sieve Size <br><?php echo  (!empty($data['mm_size'])?$data['mm_size']:'-').'/'.(!empty($data['sieve_size'])?$data['sieve_size']:'-'); ?></label>
				</div>
				<div class="col-sm-2">
					<label  class="mr-b-02" for="l30">Weight <br><?php echo $data['diamond_weight']; ?></label>
				</div>
				<div class="col-sm-4">
					<label  class="mr-b-02" for="l30">Return Weight</label><br>

					<input type="number" class="form-control" id="return_weight_<?php echo $i;?>" name="return_weight" value="" min="0.00" />
				</div>
			</div>
			<input  type="hidden" name="transaction" value="<?php echo $data['id']; ?>" id='transaction_<?php echo $i;?>'>
			
		<?php $i++; } ?>
		<input type="hidden" name="counter" id="counter" value="<?php echo $i; ?>">
		<input type="hidden" name="voucher_no" id="voucher_no" value="<?php echo $voucher_no; ?>">
		</div>

	<div class="modal-footer"> 
		<input type="submit" name="Submit" id="return_weight_btn" class="return_weight_btn btn_assorting btn btn-info btn-rounded ripple text-left">
		<button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
	</div>
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script type="text/javascript">
var counter = $('#counter').val();
$('#return_weight_btn').click(function(e){
	e.preventDefault();
	var weightArr = {};
	var newArr = [];
	for(var i=0;i<counter;i++) {
		console.log($('#return_weight_'+i).val());
		if($('#return_weight_'+i).val() != '') {
			console.log('inn');
			weightArr[$('#transaction_'+i).val()] = $('#return_weight_'+i).val();
		}
	}

	if($.isEmptyObject(weightArr) === false){
		newArr.push(weightArr);
	
	var voucher_no = $('#voucher_no').val();
	$.ajax({
	    type: "POST",
	    dataType: "JSON",
	    url: '<?=URL::to('/diamond/returndiamondIssueStore');?>',
	    data:{return_weight:newArr,voucher_no:voucher_no,_token:"{{ csrf_token() }}"},
	    success: function (response)
	      {
	      	console.log(response.message);
	      	if(response.status == "true") {
	              swal({
	                title: 'Success!',
	                text: response.message,
	                type: 'success',
	                confirmButtonClass: 'btn btn-success',
	                cancelButtonText: "Cancel",
	              }).then((value) => {
	              	if (value.value) {
	              		 window.location='<?=URL::to('/');?>'+'/diamondraw/issue_voucher_list';
	              	}
	                
	                 
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