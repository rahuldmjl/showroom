<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
?>
@extends('layout.mainlayout')

@section('title', 'Exhibition')

@section('distinct_head')
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
	<div class="col-md-12 widget-holder loader-area" style="display: none;">
	    <div class="widget-bg text-center">
	      <div class="loader"></div>
	    </div>
	  </div>
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  	{{ Breadcrumbs::render('inventory.exhibitionlist') }}
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
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-4 w-100">Exhibition List</h5>
  					</div>
  					<div class="widget-body clearfix">
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    <table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll" id="exhibitionListTable" >
  							<thead>
  								<tr class="bg-primary">
  									<th>Title</th>
  									<th>Place</th>
  									<th>Address</th>
  									<th>Markup</th>
  									<th>Qty</th>
  									<th>Grand Total</th>
  									<th>Date</th>
  									<th>Action</th>
  								</tr>
  							</thead>
  							<tbody>
		  						<?php
foreach ($exhibitionData as $key => $exhibition) {
	$title = isset($exhibition->title) ? $exhibition->title : '';
	$place = isset($exhibition->place) ? $exhibition->place : '';
	$address = isset($exhibition->address) ? $exhibition->address : '';
	$address = strlen($address) > 50 ? substr($address, 0, 50) . "..." : $address;
	$markup = isset($exhibition->markup) ? $exhibition->markup : 0;
	$qty = isset($exhibition->qty) ? $exhibition->qty : 0;
	$createdDate = isset($exhibition->created_at) ? date('d/m/Y h:i', strtotime($exhibition->created_at)) : '';
	$grandTotal = ShowroomHelper::currencyFormat(round(InventoryHelper::getExhibitionGrandTotal($exhibition->id)));
	?>
									<tr>
										<td>{{$title}}</td>
										<td>{{$place}}</td>
										<td>{{$address}}</td>
										<td>{{$markup}}</td>
										<td>{{$qty}}</td>
										<td>{{$grandTotal}}</td>
										<td>{{$createdDate}}</td>
										<td>
											<a target="_blank" class="color-content table-action-style" href="{{ route('viewexhibition',['id'=>$exhibition->id]) }}" alt="View Products" title="View Products"><i class="material-icons md-18">visibility</i></a>

											<a title="Edit Exhibition Detail" target="_blank" class="color-content table-action-style pointer btn-editexhibition" data-id="{{$exhibition->id}}"><i class="material-icons md-18">edit</i></a>

											<a title="Export Exhibition" class="color-content table-action-style <?php echo ($qty < 1) ? 'disabled' : '' ?>" href="{{ route('generateexhibitionexcel',['id'=>$exhibition->id]) }}"><i class="material-icons md-18">insert_drive_file</i></a>


										</td>
									</tr>
			  						<?php
}
?>
		  					</tbody>
		  					<!-- <tfoot>
		  						<tr>
  									<th>Title</th>
  									<th>Place</th>
  									<th>Address</th>
  									<th>Markup</th>
  									<th>Qty</th>
  									<th>Grand Total</th>
  									<th>Date</th>
  									<th>Action</th>
		  						</tr>
		  					</tfoot> -->
	  					</table>
  					</div>
  				</div>
  			</div>
  		</div>
    </div>

  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->
<div class="modal fade bs-modal-lg" id="edit-exhibition-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$(document).on('click','.btn-editexhibition', function(){
			var exhibitionId = $(this).data('id');
			$.ajax({
				type: 'post',
				url: '<?=URL::to('/inventory/getexhibitiondetail');?>',
				data: {_token: "{{ csrf_token() }}", exhibition_id: exhibitionId},
				beforeSend: function(){
					showLoader();
				},
				success: function(response){
					if(response != '')
					{
						$("#edit-exhibition-modal .modal-content").html(response);
	                	$("#edit-exhibition-modal").modal("show");
	                	hideLoader();
					}
				},
				error: function(){
					hideLoader();
				}
			})
		});
	});
	var exhibitionListTable = $('#exhibitionListTable').DataTable({
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
	$("#exhibitionListTable tr th:first").removeClass('sorting_asc');
</script>
@endsection