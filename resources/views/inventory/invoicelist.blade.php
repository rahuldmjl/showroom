<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
$generatedInvoiceList = $invoiceData['invoiceCollection'];
$totalInvoice = isset($invoiceData['totalCount']) ? $invoiceData['totalCount'] : 10;
?>
@extends('layout.mainlayout')

@section('title', 'Generated Invoice')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<!-- <link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css"/> -->
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css"/>
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  	{{ Breadcrumbs::render('inventory.invoicelist') }}
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
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-4 w-100">Invoice List</h5>
  					</div>
  					<div class="widget-body clearfix">
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    <div class="row m-0 label-text-pl-25">
	                    	<div class="tabs w-100">
	                    		<ul class="nav nav-tabs">
	                    			<li class="nav-item active"><a class="nav-link" href="#inventory-filter" data-toggle="tab" aria-expanded="true">Filter</a>
			                        </li>
	                    		</ul>
	                    		<div class="tab-content p-3 border border-top-0">
	                    			<div class="tab-pane active" id="inventory-filter">
	                    				<div class="row custom-drop-style label-text-pl-25">
	                    					<div class="col-xl-3 col-sm-3">
				                                  <div class="form-group mb-0">
				                                  		<!-- <label for="invoicestatus">Invoice Status: </label> -->
				                                        <select class="mr-1 ml-1 text-uppercase form-control height-35" id="invoicestatus" name="invoicestatus">
				                                            <option value="">Invoice Status</option>
				                                            <option value="complete">Complete Invoice</option>
				                                            <option value="canceled">Canceled Invoice</option>
				                                        </select>
				                                  </div>
				                            </div>
				                            <div class="col-xl-3 col-sm-3">
				                            	<div class="form-group mb-0">
					                            	<!-- <label for="from_date">From Date:</label> -->
					                            	<input type="text" class="form-control datepicker" id="txtFromDate" name="txtFromDate" placeholder="From Date" autocomplete="off">
					                            </div>
				                            </div>
				                            <div class="col-xl-3 col-sm-3">
				                            	<div class="form-group mb-0">
					                            	<!-- <label for="from_date">To Date:</label> -->
					                            	<input type="text" class="form-control datepicker" id="txtToDate" name="txtToDate" placeholder="To Date" autocomplete="off">
					                            </div>
				                            </div>
				                            <div class="col-xl-3 col-sm-3">
				                            	<div class="form-group mb-0">
						                            <button type="button" id="btn-apply-date-filter" class="btn btn-primary height-35 ripple small-btn-style">Apply</button>
						                            <button type="button" id="btn-reset-filter" class="btn btn-default height-35 ripple small-btn-style">Reset</button>
						                        </div>
					                        </div>
	                    				</div>
	                    			</div>
	                    		</div>
	                    	</div>
	                    </div>
  					</div>
  				</div>
  			</div>
  		</div>
  		<div class="row">
  			<div class="col-md-12 widget-holder content-area">
  				<div class="widget-bg">
  					<!-- <div class="widget-header">
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-4 w-100">Invoice List</h5>
  					</div> -->
  					<div class="widget-body clearfix">
  						<table class="table table-striped table-center" id="invoiceListTable" >
  							<thead>
  								<tr class="bg-primary">
  									<th>Invoice No.</th>
  									<th>Name</th>
  									<th>DMUSERCODE</th>
  									<th>Date</th>
  									<th>Grand Total</th>
  									<th>Action</th>
  								</tr>
  							</thead>
  							<tbody>
		  						<?php

$price = 0;

foreach ($generatedInvoiceList as $key => $invoice) {
	$totalInvoiceValue = 0;
	$gstTotal = 0;
	$totalGrandTotalPrice = 0;
	$totalDiscountAmount = 0;
	$customerName = InventoryHelper::getCustomerName($invoice->customer_id);
	$customerId = isset($invoice->customer_id) ? 'DML' . $invoice->customer_id : '';
	if (isset($invoice->gst_percentage) && !empty($invoice->gst_percentage)) {
		$invoiceGstPercentage = $invoice->gst_percentage;
	} else {
		$invoiceGstPercentage = 3;
	}

	if (isset($invoice->child_customer_name) && !empty($invoice->child_customer_name)) {
		$customerName = $invoice->child_customer_name;
		$customerId = 'N/A';
	}
	$invoiceDate = date('d-m-Y', strtotime($invoice->invoice_created_date));

	?>
		  								<tr id="order_id_<?=$invoice->invoice_ent_id?>">
		  									<td>{{$invoice->invoice_number}}</td>
		  									<td>{{$customerName}}</td>
		  									<td>{{$customerId}}</td>
		  									<td >{{$invoiceDate}}</td>
		  									<?php
$invoiceItems = InventoryHelper::getInvoiceItems($invoice->invoice_ent_id);

	$shippingCharge = (isset($invoice->invoice_shipping_charge) && !empty($invoice->invoice_shipping_charge)) ? $invoice->invoice_shipping_charge : 0;
	foreach ($invoiceItems as $key => $invoiceItem) {
		$price = isset($invoiceItem->price) ? $invoiceItem->price : 0;
		$discountAmount = isset($invoiceItem->discount_amount) ? $invoiceItem->discount_amount : 0;
		$totalGrandTotalPrice += isset($invoiceItem->price) ? $invoiceItem->price : 0;
		$totalDiscountAmount += $discountAmount;
	}

	$totalInvoiceValue = ($totalGrandTotalPrice - $totalDiscountAmount);

	$totalInvoiceValue += $shippingCharge;
	$gstTotal = ($totalInvoiceValue * ($invoiceGstPercentage / 100));
	//echo $gstTotal;exit;
	$totalInvoiceValue += round($gstTotal, 2);

	?>
		  									<td><?=ShowroomHelper::currencyFormat(intval($totalInvoiceValue))?></td>
		  									<td>

		  										<a title="View Invoice" target="_blank" class="color-content table-action-style1" href="{{ route('viewinvoice',['id'=>$invoice->invoice_ent_id]) }}"><i class="list-icon fa fa-book"></i></a>

		  										<!-- <a title="View Memo" target="_blank" class="color-content table-action-style" href="{{ route('viewmemo',['id'=>$invoice->entity_id]) }}"><i class="list-icon fa fa-file-text-o"></i></a> -->
		  										<?php
		  										$createdDate = isset($invoice->invoice_created_date) ? $invoice->invoice_created_date : '';
		  										if(!empty($createdDate))
		  										{
		  												$createdMonth = date('m',strtotime($createdDate));
														$createdYear = date('Y',strtotime($createdDate));
		  												$maxInvoiceData  = date($createdYear.'-'.$createdMonth.'-t');
		  										}
		  										$currentDate = date('Y-m-d');
		  										?>
		  										<a title="Edit Invoice" target="_blank" class="color-content table-action-style1 <?= (($createdDate >= $maxInvoiceData) || ($maxInvoiceData <= $currentDate)) ? 'disabled' : ''?>" href="{{ route('inventory.editinvoice',['id'=>$invoice->invoice_ent_id]) }}" <?= (($createdDate >= $maxInvoiceData) || ($maxInvoiceData <= $currentDate)) ? 'disabled' : ''?>><i class="list-icon fa fa-pencil-square-o"></i></a>

		  										<?php if ($invoice->status != 'canceled'): ?>
		  											
		  											<a currdate="<?= $currentDate?>" created="<?= $createdDate?>" maxdate="<?= $maxInvoiceData?>" title="Cancel Invoice" class="color-content table-action-style1 btn-cancel-invoice pointer <?= (($createdDate >= $maxInvoiceData) || ($maxInvoiceData <= $currentDate)) ? 'disabled' : ''?>" data-orderid="{{$invoice->invoice_ent_id}}" data-href="{{ route('cancelinvoice',['id'=>$invoice->invoice_ent_id]) }}" <?= (($createdDate > $maxInvoiceData) || ($maxInvoiceData <= $currentDate)) ? 'disable' : ''?>><i class="list-icon fa fa-trash-o"></i></a>
		  										<?php endif;?>
		  										<?php
$orderItems = InventoryHelper::getOrderItems($invoice->entity_id);
	?>
		  										<?php if (count($orderItems) > 0): ?>
		  											<a title="Download Excel" target="_blank" class="color-content table-action-style1 pointer downloadexcel" data-id="{{$invoice->invoice_ent_id}}"><i class="list-icon fa fa-file-excel-o"></i></a>
		  										<?php endif;?>

		  										<a title="Delivery Challan" target="_blank"  class="color-content table-action-style1" href="{{ route('deliverystatus',['id'=>$invoice->invoice_ent_id]) }}" ><i class="list-icon fa fa-truck"></i></a>
		  									</td>
		  								</tr>
		  							<?php
//}
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
<!-- <script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.js"></script> -->
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">
	$('.datepicker').datepicker({autoclose: true, format: 'dd-mm-yyyy'});
	$(document).ready(function(){
		$(document).on('click','#btn-reset-filter', function(){
			$("#invoicestatus, #txtFromDate, #txtToDate").val('');
			invoiceListTable.draw();
		});
		$(document).on('click','.downloadexcel',function(event){
			event.stopPropagation();
			var orderId = $(this).data("id");
			window.location.href = "<?=URL::to('inventory/downloadexcel');?>/"+orderId;
		});
		$(document).on("click","#btn-apply-date-filter", function(){
			invoiceListTable.draw();
		});
		var invoiceListTable = $('#invoiceListTable').DataTable({
			"lengthChange": false,
			"dom": '<"datatable_top_custom_lengthinfo"i>frti<"datatable_bottom_custom_length"l>p',
			"language": {
			    "infoEmpty": "No matched records found",
			    "zeroRecords": "No matched records found",
			    "emptyTable": "No data available in table",
			    "search": "_INPUT_",
			    "searchPlaceholder": "Search",
			    "info": "Showing _START_ to _END_ of _TOTAL_"
			    //"sProcessing": "<div id='loader'></div>"
			},
			"deferLoading": <?=$totalInvoice?>,
			"processing": true,
			"serverSide": true,
			"serverMethod": "post",
			"ajax":{
			    "url": '<?=URL::to('/inventory/invoiceajaxlist');?>',
			    "data": function(data, callback){
			      // Append to data
			      data._token = "{{ csrf_token() }}";
			      data.from_date = $("#txtFromDate").val();
			      data.to_date = $("#txtToDate").val();
			      data.invoice_status = $("#invoicestatus").val();
			      showLoader();
			      $(".dropdown").removeClass('show');
			      $(".dropdown-menu").removeClass('show');
			    },
			    complete: function(response){
			      hideLoader();
			    }
			  },
			"order": [[ 3, "desc" ]],
			"columnDefs": [
			      { "orderable": false, "targets": [5] }
			],
		});
		$('.dataTables_filter input')
		  .unbind() // Unbind previous default bindings
		  .bind("input", function(e) { // Bind our desired behavior
		      // If the length is 3 or more characters, or the user pressed ENTER, search
		      if(this.value.length >= 3 || e.keyCode == 13) {
		          // Call the API search function
		          invoiceListTable.search(this.value).draw();
		      }
		      // Ensure we clear the search if they backspace far enough
		      if(this.value == "") {
		          invoiceListTable.search("").draw();
		      }
		      return;
		});
		$("#invoiceListTable tr th").removeClass('sorting_asc');

		$("#invoice-filter").change(function(){
			if(this.value == 'completed')
			{
				window.location.href = '<?=URL::to('inventory/getcompletedinvoice');?>';
			}
			else if(this.value == 'canceled')
			{
				window.location.href = '<?=URL::to('inventory/getcanceledinvoice');?>';
			}
			else
			{
				window.location.href = '<?=URL::to('inventory/invoicelist');?>';
			}
		});
		$("#invoice-filter").val('<?=isset($invoiceType) ? $invoiceType : ''?>');
		$(document).on("click",".btn-cancel-invoice",function(){
		    var cancelUrl = $(this).data('href');
		    swal({
		        title: 'Are you sure?',
		        text: "<?php echo Config::get('constants.message.invoice_cancellation_confirmation'); ?>",
		        type: 'info',
		        showCancelButton: true,
		        confirmButtonText: 'Confirm',
		        confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
		        }).then(function() {
		            window.location.href = cancelUrl;
		    });
		});
	});
</script>
@endsection