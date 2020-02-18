<?php
use App\ApprovalMemoHistroy;
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
?>
@extends('layout.mainlayout')

@section('title', 'Generated Memo')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  	{{ Breadcrumbs::render('inventory.memolist') }}
      <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div class="col-md-12 widget-holder loader-area" style="display: none;">
    <div class="widget-bg text-center">
      <div class="loader"></div>
    </div>
  </div>
  	<div class="widget-list">
      	<div class="row">
  			<div class="col-md-12 widget-holder content-area">
  				<div class="widget-bg">
  					<div class="widget-header">
  						<h5 class="border-b-light-1 pb-2 mb-4 mt-0 w-100">Memo List</h5>
  					</div>
  					<div class="widget-body clearfix">
  						<?php if ($generatedMemoList->count() > 0): ?>
	  						<button type="button" class="btn btn-primary btn-sm export-csv-rt ripple" id="btn-export-csv">Export CSV</button>
	  					<?php endif;?>
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    <table class="table table-striped table-center word-break" id="memoListTable" >
  							<thead>
  								<tr class="bg-primary">
  									<th>Memo No.</th>
  									<th>Name</th>
  									<th>DMUSERCODE</th>
  									<th>Qty</th>
  									<th>Date</th>
  									<th>Grand Total</th>
  									<th>Action</th>
  								</tr>
  							</thead>
  							<tbody>
		  						<?php
foreach ($generatedMemoList as $key => $memo) {
	DB::setTablePrefix('dml_');
	$memoProductsIds = DB::table('approval_memo_histroy')->select('product_id')->where('approval_memo_id', '=', DB::raw("'$memo->id'"))->get();
	DB::setTablePrefix('');

	//$order = InventoryHelper::getOrderData($memo->entity_id);
	$orderDate = date('d-m-Y', strtotime($memo->created_at));
	//$customerName = CustomersHelper::getCustomerDetailById($memo->customer_id);

	//$customerName = $customerName->firstname.' '.$customerName->lastname; */
	$customerName = InventoryHelper::getCustomerName($memo->customer_id);

	$currentYear = date('y', strtotime($memo->created_at));
	$approvalNumber = isset($memo->approval_no) ? $memo->approval_no : '';
	if (date('m') > 6) {
		$fin_year = date('y') . '-' . (date('y') + 1);
	} else {
		$fin_year = (date('y') - 1) . '-' . date('y');
	}
	$approvalNumber = $fin_year . '/' . $approvalNumber;
	if (isset($memo->is_for_old_data) && $memo->is_for_old_data == 'yes') {
		$approvalNumber = $memo->approval_no;
	}
	$productIds = array();
	/* $productIds = isset($memo->product_ids) ? $memo->product_ids : '';
	$productIds = explode(',', $productIds); */
	foreach ($memoProductsIds as $ids) {
		$productIds[] = $ids->product_id;
	}
	//$totalProductsCount = count($productIds);
	$totalProductsCount = 0;
	$grandTotal = 0;
	//echo implode("','",$productIds);exit;
	//print_r($productIds);exit;
	foreach ($productIds as $productId) {
		DB::setTablePrefix('');
		//echo "test";exit;
		$productPrice = DB::table("catalog_product_flat_1")->select('custom_price')->where('entity_id', '=', DB::raw("$productId"))->get()->first();
		if (empty($productPrice)) {
			continue;
		}

		$grandTotal += (float) $productPrice->custom_price;
		$totalProductsCount++;
		DB::setTablePrefix('dml_');
	}
	if (empty($memo->approval_no)) {
		$approvalNumber = '';
	}
	?>
		  							<tr id="order_id_<?=$memo->id?>">
		  								<input type="hidden" name="memo_order_id[]" value="<?=$memo->id?>">
		  								<td>{{$approvalNumber}}</td>
		  								<td>{{$customerName}}</td>
		  								<td>DML{{$memo->customer_id}}</td>
		  								<td><?=$totalProductsCount?></td>
		  								<td data-sort="<?=strtotime($memo->created_at)?>">{{$orderDate}}</td>
		  								<td><?=ShowroomHelper::currencyFormat(round($grandTotal))?></td>
		  								<td>
		  									<a title="Generate Memo" target="_blank" data-memoid="{{$memo->id}}" class="color-content table-action-style1 pointer btn-generate-approval <?=(!empty($approvalNumber) || (InventoryHelper::isReturnMemoGenerated($approvalNumber) == true)) ? 'disabled' : ''?>" <?=(!empty($approvalNumber) || (InventoryHelper::isReturnMemoGenerated($approvalNumber) == true)) ? 'disabled' : ''?>><i class="list-icon fa fa-tag" ></i></a>

		  									<a title="Cancel Memo" target="_blank" data-memoid="{{$memo->id}}" class="color-content table-action-style1 pointer btn-cancel-approval <?=(!empty($approvalNumber) || (InventoryHelper::isReturnMemoGenerated($approvalNumber) == true)) ? 'disabled' : ''?>" <?=(!empty($approvalNumber) || (InventoryHelper::isReturnMemoGenerated($approvalNumber) == true)) ? 'disabled' : ''?>><i class="list-icon fa fa-trash-o" ></i></a>

		  									<a title="View Memo" target="_blank" class="color-content table-action-style1" href="{{ route('viewmemo',['id'=>$memo->id]) }}"><i class="list-icon fa fa-file-text-o"></i></a>
		  									<?php
DB::setTablePrefix('dml_');
	$isReturnMemoGenerated = ApprovalMemoHistroy::select('id')->where('approval_memo_id', '=', DB::raw("$memo->id"))->where('status', '!=', DB::raw("'return_memo'"))->where('status', '!=', DB::raw("'invoice'"))->get()->count();
	DB::setTablePrefix('');
	?>
		  									<a title="Edit Memo" target="_blank" class="color-content table-action-style1 <?php echo ($isReturnMemoGenerated == 0) ? 'disabled' : '' ?>" href="{{ route('inventory.editmemo',['id'=>$memo->id]) }}" <?php echo ($isReturnMemoGenerated == 0) ? 'disabled' : '' ?>><i class="list-icon fa fa-pencil-square-o"></i></a>

		  									<?php if ($totalProductsCount > 0): ?>
		  										<a title="Download Excel" target="_blank" data-id="<?=$memo->id?>" class="pointer color-content table-action-style1 downloadexcel"><i class="list-icon fa fa-file-excel-o"></i></a>
		  									<?php endif;?>

	  										<a title="Generate Return Memo" target="_blank" data-id="<?=$memo->id?>" class="pointer generatereturnmemo color-content table-action-style1 <?php echo ($isReturnMemoGenerated == 0) ? 'disabled' : '' ?> <?=empty($approvalNumber) ? 'disabled' : ''?>" <?php echo ($isReturnMemoGenerated == 0) ? 'disabled' : '' ?> <?=empty($approvalNumber) ? 'disabled' : ''?>><i class="list-icon fa fa-retweet"></i></a>
	  										<?php
$generateReturnMemoFlag = true;
	if (!empty($memo->is_delivered) || InventoryHelper::isReturnMemoGenerated($approvalNumber) == true) {
		$generateReturnMemoFlag = false;
	}
	/* if(InventoryHelper::isReturnMemoGenerated($memo->approval_no) == true)
		  										{
		  											$generateReturnMemoFlag = false;
	*/
	?>
	  										<a title="Delivery" id="btn-delivery-<?=$memo->id?>" target="_blank" data-id="<?=$memo->id?>" class="pointer btn-deliver-memo color-content table-action-style1 <?php echo ((!empty($memo->is_delivered)) || InventoryHelper::isReturnMemoGenerated($approvalNumber) == true) ? 'disabled' : '' ?>" <?php echo ((!empty($memo->is_delivered)) || InventoryHelper::isReturnMemoGenerated($approvalNumber) == true) ? 'disabled' : '' ?>><i class="list-icon fa fa-truck"></i></a>
		  								</td>
		  							</tr>
		  						<?php
}
?>
		  					</tbody>
	  					</table>
  					</div>
  				</div>
  			</div>
  		</div>
    </div>
  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->

@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$(".btn-deliver-memo").click(function(){
			var memoId = $(this).data('id');
			var buttonId = $(this).attr('id');
			swal({
	              title: 'Are you sure?',
	              text: "<?php echo Config::get('constants.message.inventory_deliver_approval_confirmation'); ?>",
	              type: 'info',
	              showCancelButton: true,
	              confirmButtonText: 'Confirm',
	              confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
	              }).then(function(deliverBtn) {
					  if(deliverBtn.value)
					  {
						  if(memoId != '')
							{
								$.ajax({
									  url:'<?=URL::to('inventory/deliverapprovalmemo');?>',
									  method:"post",
									  data:{memo_id: memoId,_token: "{{ csrf_token() }}"},
									  beforeSend: function()
									  {
										showLoader();
									  },
									  success: function(response){
										  hideLoader();
										  var res = JSON.parse(response);
										  if(res.status)
										  {
												swal({
													title: 'Success',
													text: res.message,
													type: 'success',
													buttonClass: 'btn btn-primary'
												  });
												$("#"+buttonId).attr('disabled','disabled');
												$("#"+buttonId).addClass('disabled');
										  }
										  else
										  {
												swal({
													title: 'Oops!',
													text: res.message,
													type: 'error',
													showCancelButton: true,
													showConfirmButton: false,
													confirmButtonClass: 'btn btn-danger',
													cancelButtonText: 'Ok'
												  });
										  }
									  }
								});
							}
							else
							{
								swal({
									title: 'Oops!',
									text: '<?php echo Config::get('constants.message.inventory_default_failure_message'); ?>',
									type: 'error',
									showCancelButton: true,
									showConfirmButton: false,
									confirmButtonClass: 'btn btn-danger',
									cancelButtonText: 'Ok'
								  });
							}
					  }

	              })
		});
		$(".btn-cancel-approval").click(function(){
			var memoId = $(this).data('memoid');
			swal({
	              title: 'Are you sure?',
	              text: "<?php echo Config::get('constants.message.inventory_cancel_approval_confirmation'); ?>",
	              type: 'info',
	              showCancelButton: true,
	              confirmButtonText: 'Confirm',
	              confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'

	              }).then(function(result) {
					  if(result.value)
					  {
						  if(memoId != '')
							{
								$.ajax({
									  url:'<?=URL::to('inventory/cancelapprovalmemo');?>',
									  method:"post",
									  data:{memo_id: memoId,_token: "{{ csrf_token() }}"},
									  beforeSend: function()
									  {
										showLoader();
									  },
									  success: function(response){
										  hideLoader();
										  var res = JSON.parse(response);
										  if(res.status)
										  {
												swal({
													title: 'Success',
													text: res.message,
													type: 'success',
													buttonClass: 'btn btn-primary'
												  });
												$("#order_id_"+memoId).remove();
										  }
										  else
										  {
												swal({
													title: 'Oops!',
													text: res.message,
													type: 'error',
													showCancelButton: true,
													showConfirmButton: false,
													confirmButtonClass: 'btn btn-danger',
													cancelButtonText: 'Ok'
												  });
										  }
									  }
								});
							}
							else
							{
								swal({
									title: 'Oops!',
									text: '<?php echo Config::get('constants.message.inventory_default_failure_message'); ?>',
									type: 'error',
									showCancelButton: true,
									showConfirmButton: false,
									confirmButtonClass: 'btn btn-danger',
									cancelButtonText: 'Ok'
								  });
							}
					  }

	              })
		});
		$(".btn-generate-approval").click(function(){
			var memoId = $(this).data('memoid');
			swal({
	              title: 'Are you sure?',
	              text: "<?php echo Config::get('constants.message.inventory_generate_approval_confirmation'); ?>",
	              type: 'info',
	              showCancelButton: true,
	              confirmButtonText: 'Confirm',
	              confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'

	              }).then(function(result) {
					   if (result.value) {
							if(memoId != '')
							{
								$.ajax({
									  url:'<?=URL::to('inventory/generateapprovalmemo');?>',
									  method:"post",
									  data:{memo_id: memoId,_token: "{{ csrf_token() }}"},
									  beforeSend: function()
									  {
										showLoader();
									  },
									  success: function(response){
										  hideLoader();
										  var res = JSON.parse(response);
										  if(res.status)
										  {
												swal({
													title: 'Success',
													text: res.message,
													type: 'success',
													buttonClass: 'btn btn-primary'
												  });
												$("#order_id_"+memoId).children('td:first').html(res.approval_number);
												$("#order_id_"+memoId+' .generatereturnmemo, #order_id_'+memoId+' .btn-deliver-memo').removeClass('disabled');
												$("#order_id_"+memoId+' .generatereturnmemo, #order_id_'+memoId+' .btn-deliver-memo').removeAttr('disabled');
												$("#order_id_"+memoId+' .btn-cancel-approval, '+"#order_id_"+memoId+' .btn-generate-approval').addClass('disabled');
												$("#order_id_"+memoId+' .btn-cancel-approval, '+"#order_id_"+memoId+' .btn-generate-approval').attr('disabled',true);
										  }
										  else
										  {
												swal({
													title: 'Oops!',
													text: res.message,
													type: 'error',
													showCancelButton: true,
													showConfirmButton: false,
													confirmButtonClass: 'btn btn-danger',
													cancelButtonText: 'Ok'
												  });
										  }
									  }
								});
							}
							else
							{
								swal({
									title: 'Oops!',
									text: '<?php echo Config::get('constants.message.inventory_default_failure_message'); ?>',
									type: 'error',
									showCancelButton: true,
									showConfirmButton: false,
									confirmButtonClass: 'btn btn-danger',
									cancelButtonText: 'Ok'
								  });
							}
					   }

	              })
		});
		$("#btn-export-csv").click(function(){
			event.stopPropagation();
			window.location.href = "<?=URL::to('inventory/exportmemoproductscsv');?>/";
		});
		$(".generatereturnmemo").click(function(event){
			event.stopPropagation();
			var orderId = $(this).data("id");
			if(orderId != '')
			{
				swal({
	              title: 'Are you sure?',
	              text: "<?php echo Config::get('constants.message.inventory_generate_returnmemo_confirmation'); ?>",
	              type: 'info',
	              showCancelButton: true,
	              confirmButtonText: 'Confirm',
	              confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
	              }).then(function(result) {
					   if (result.value) {
						   $.ajax({
			              url:'<?=URL::to('inventory/getproductidsbyorder');?>',
			              method:"post",
			              data:{orderId: orderId,_token: "{{ csrf_token() }}"},
			              beforeSend: function()
			              {
			                showLoader();
			              },
			              success: function(response){
			                  hideLoader();
			                  var res = JSON.parse(response);
			                  $.ajax({
					              url:'<?=URL::to('/inventory/generatereturnmemo');?>',
					              method:"post",
					              data:{memo_id: orderId, productIds: res.product_ids,is_from_memo_list:true, _token: "{{ csrf_token() }}"},
					              beforeSend: function()
					              {
					                showLoader();
					              },
					              success: function(response){
					                  hideLoader();
					                  var res = JSON.parse(response);
					                  if(res.status)
					                  {
					                      swal({
					                        title: 'Success',
					                        text: res.message,
					                        type: 'success',
					                        buttonClass: 'btn btn-primary'
					                      }).then(function() {
						                        window.location.href = '<?=URL::to('/inventory/returnmemolist');?>';
						                  });
					                      $("#order_id_"+orderId).remove();
					                  }
					                  else
					                  {
					                      swal({
					                        title: 'Oops!',
					                        text: res.message,
					                        type: 'error',
					                        showCancelButton: true,
					                        showConfirmButton: false,
					                        confirmButtonClass: 'btn btn-danger',
					                        cancelButtonText: 'Ok'
					                      });
					                  }
					              }
					            })
			              }
			          });
					   }

	              });
			}
		});
		$(".downloadexcel").click(function(event){
			event.stopPropagation();
			var orderId = $(this).data("id");
			window.location.href = "<?=URL::to('inventory/downloadmemoproductexcel');?>/"+orderId;
		});
	var memoListTable = $('#memoListTable').DataTable({
		"dom": '<"datatable_top_custom_lengthinfo"i>frti<"datatable_bottom_custom_length"l>p',
		"lengthChange": false,
		"order": [[ 4, "DESC" ]],
		"language": {
		    "infoEmpty": "No matched records found",
		    "zeroRecords": "No matched records found",
		    "emptyTable": "No data available in table",
		    "search": "_INPUT_",
		    "searchPlaceholder": "Search",
		    "info": "Showing _START_ to _END_ of _TOTAL_"
		    //"sProcessing": "<div id='loader'></div>"
		  },
	});
	$('.dataTables_filter input')
	  .unbind() // Unbind previous default bindings
	  .bind("input", function(e) { // Bind our desired behavior
	      // If the length is 3 or more characters, or the user pressed ENTER, search
	      if(this.value.length >= 3 || e.keyCode == 13) {
	          // Call the API search function
	          memoListTable.search(this.value).draw();
	      }
	      // Ensure we clear the search if they backspace far enough
	      if(this.value == "") {
	          memoListTable.search("").draw();
	      }
	      return;
	});
	$("#memoListTable tr th").removeClass('sorting_asc');
});
</script>
@endsection