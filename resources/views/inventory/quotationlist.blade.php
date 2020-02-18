<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;

?>
@extends('layout.mainlayout')

@section('title', 'Quotation')

@section('distinct_head')
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  	{{ Breadcrumbs::render('inventory.quotationlist') }}
      <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  	<div class="widget-list">
      	<div class="row">
  			<div class="col-md-12 widget-holder">
  				<div class="widget-bg">
  					<div class="widget-header">
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-4 w-100">Quotation List</h5>
  					</div>
  					<div class="widget-body clearfix">
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    <table class="table table-striped table-center" id="quotationListTable" >
  							<thead>
  								<tr class="bg-primary">
  									<th>Sr No.</th>
  									<th>Customer Name</th>
  									<th>Total Products</th>
  									<th>Total Metal Weight</th>
  									<th>Total Stone Carat</th>
  									<th>Total Amount</th>
  									<th>Date</th>
  									<th>Action</th>
  								</tr>
  							</thead>
  							<tbody>
		  						<?php
$serialNo = 0;
foreach ($quotationData as $key => $quotation) {

	$serialNo++;
	$productData = json_decode($quotation->product_data);
	$customerName = InventoryHelper::getCustomerName($quotation->customer_id);
	$totalProducts = isset($quotation->total_products) ? $quotation->total_products : '-';
	$totalMetalWeight = isset($quotation->total_metal_weight) ? $quotation->total_metal_weight : '-';
	$totalStoneCaret = isset($quotation->total_stone_caret) ? $quotation->total_stone_caret : '-';
	$totalPrice = isset($quotation->total_amount) ? ShowroomHelper::currencyFormat(round($quotation->total_amount)) : '-';
	//echo $quotation->created_at;exit;
	$createdDate = isset($quotation->created_at) ? date("d/m/Y h:i", strtotime($quotation->created_at)) : '-';
	// $createdDate = isset($quotation->created_at) ? date('d F Y h:i:s A',$quotation->created_at) : '-';
	?>
									<tr>
										<td>{{$serialNo}}</td>
										<td>{{$customerName}}</td>
										<td>{{$totalProducts}}</td>
										<td>{{$totalMetalWeight}}</td>
										<td>{{$totalStoneCaret}}</td>
										<td>{{$totalPrice}}</td>
										<td>{{$createdDate}}</td>
										<td>
											<a title="View Quotation" target="_blank" class="color-content table-action-style" href="{{ route('inventory.viewquotation',['id'=>$quotation->id]) }}"><i class="material-icons md-18">visibility</i></a>
											<a title="Edit Quotation" target="_blank" class="color-content table-action-style" href="{{ route('inventory.editquotation',$quotation->id) }}"><i class="material-icons md-18">edit</i></a>
											<a title="Export Quotation" class="color-content table-action-style" href="{{ route('exportquotationexcel',['id'=>$quotation->id]) }}"><i class="material-icons md-18">insert_drive_file</i></a>
											<a title="Delete Quotation" class="color-content pointer table-action-style delete-quotation" data-href="{{ route('deletequotation',['id'=>$quotation->id]) }}" ><i class="material-icons md-18">delete</i></a>

							                <!-- <a class="color-content table-action-style" href="{{ route('inventory.deletequotation',$quotation->id) }}"><i class="material-icons md-18">delete</i></a> -->
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
		$(".delete-quotation").click(function(){
			var href = $(this).data('href');
			swal({
              title: 'Are you sure?',
              text: "<?php echo Config::get('constants.message.inventory_delete_quotation_confirmation'); ?>",
              type: 'info',
              showCancelButton: true,
              confirmButtonText: 'Confirm',
              confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
              }).then(function() {
                  var url = $("#exportProductExcelAction").val();
                  window.location.href = href;
              });
		});
	});
	var quotationListTable = $('#quotationListTable').DataTable({
		"columnDefs": [
		      { "orderable": false, "targets": [0] }
		  ],
		  "aLengthMenu": [[25,50,100,200,300,500], [25,50,100,200,300,500]],
		  "iDisplayLength": 50,
		  "language": {
	          "infoEmpty": "No matched records found",
	          "zeroRecords": "No matched records found",
	          "emptyTable": "No data available in table",
	          "search": "_INPUT_",
	          "searchPlaceholder": "Search",
	          "lengthMenu": "Show _MENU_",
	          "info": "Showing _START_ to _END_ of _TOTAL_"
	          //"sProcessing": "<div id='loader'></div>"
	      }
	});
	$("#quotationListTable tr th:first").removeClass('sorting_asc');
</script>
@endsection