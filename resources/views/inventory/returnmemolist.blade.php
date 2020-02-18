<?php
use App\Helpers\ShowroomHelper;
?>
@extends('layout.mainlayout')

@section('title', 'Generated Return Memo')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  	{{ Breadcrumbs::render('inventory.returnmemolist') }}
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
  						<h5 class="border-b-light-1 pb-2 mt-0 mb-4 w-100">Return Memo List</h5>
  					</div>
  					<div class="widget-body clearfix">
  						<?php if ($returnMemoList->count() > 0): ?>
	  						<button type="button" class="btn btn-primary btn-sm export-csv-rt ripple" id="btn-export-csv">Export CSV</button>
	  					<?php endif;?>
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    <table class="table table-striped table-center" id="returnMemoListTable" >
  							<thead>
  								<tr class="bg-primary">
  									<th>Sr</th>
  									<th>Return Memo No.</th>
					                <th>Name</th>
					                <th>DMUSERCODE</th>
					                <th>Qty</th>
					                <th>Date</th>
					                <th>Grand Total</th>
					                <th>Memo No.</th>
					                <th>Action</th>
  								</tr>
  							</thead>
  							<tbody>
		  						<?php
foreach ($returnMemoList as $key => $returnMemo) {
	$currentYear = date('y', strtotime($returnMemo->created_at));
	$returnMemoNumber = isset($returnMemo->return_number) ? $returnMemo->return_number : '';
	if (date('m') > 6) {
		$fin_year = date('y') . '-' . (date('y') + 1);
	} else {
		$fin_year = (date('y') - 1) . '-' . date('y');
	}
	$returnMemoNumber = $fin_year . '/' . $returnMemoNumber;
	?>
		  						<tr>
			  						<input type="hidden" name="return_memo_id[]" class="return_memo_id" value="<?=isset($returnMemo->id) ? $returnMemo->id : ''?>">
			  						<td>{{ $key+1 }}</td>
			  						<td><?=$returnMemoNumber?></td>
			  						<td><?=isset($returnMemo->franchise_name) ? $returnMemo->franchise_name : ''?></td>
			  						<?php
$productData = isset($returnMemo->product_data) ? json_decode($returnMemo->product_data) : array();
	$grandTotalData = isset($returnMemo->grand_total_data) ? json_decode($returnMemo->grand_total_data) : '';
	$qty = isset($grandTotalData->qty) ? $grandTotalData->qty : '';
	?>
			  						<td>DML<?=isset($returnMemo->customer_id) ? $returnMemo->customer_id : ''?></td>
			  						<td><?=$qty?></td>
			  						<?php
$date = isset($returnMemo->created_at) ? date('d-m-Y', strtotime($returnMemo->created_at)) : '';
	?>
			  						<td><?=$date?></td>
			  						<?php if (!empty($grandTotalData)): ?>
			  							<td><?=ShowroomHelper::currencyFormat($grandTotalData->price)?></td>
			  						<?php endif;?>
			  						<td><?=isset($returnMemo->approval_memo_number) ? $returnMemo->approval_memo_number : ''?></td>
			  						<td>
			  							<a title="View Return Memo" target="_blank" class="color-content table-action-style1" href="{{ route('viewreturnmemo',['id'=>$returnMemo->id]) }}"><i class="list-icon fa fa-book"></i></a>

			  							<a title="Download Return Memo Excel" class="color-content table-action-style1 pointer" id="downloadqrcsv" href="{{ route('downloadreturnmemoproduct',['id'=>$returnMemo->id]) }}"><i class="list-icon fa fa-file-excel-o"></i></a>

			  							<a title="Download Excel" class="color-content table-action-style1" href="{{ route('downloadproductexcel',['id'=>$returnMemo->id]) }}"><i class="list-icon fa fa-file-excel-o"></i></a>
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
	$("#btn-export-csv").click(function(){
		window.location.href = "<?=URL::to('inventory/exportreturnmemoproductcsv');?>";
	});
	var returnMemoListTable = $('#returnMemoListTable').DataTable({
		"dom": '<"datatable_top_custom_lengthinfo"i>frti<"datatable_bottom_custom_length"l>p',"lengthChange": false,
		"language": {
			    "infoEmpty": "No matched records found",
			    "zeroRecords": "No matched records found",
			    "emptyTable": "No data available in table",
			    "search": "_INPUT_",
			    "searchPlaceholder": "Search",
			    "info": "Showing _START_ to _END_ of _TOTAL_"
			    //"sProcessing": "<div id='loader'></div>"
			},
		}); // "order": [[ 4, "asc" ]]
	$('.dataTables_filter input')
	  .unbind() // Unbind previous default bindings
	  .bind("input", function(e) { // Bind our desired behavior
	      // If the length is 3 or more characters, or the user pressed ENTER, search
	      if(this.value.length >= 3 || e.keyCode == 13) {
	          // Call the API search function
	          returnMemoListTable.search(this.value).draw();
	      }
	      // Ensure we clear the search if they backspace far enough
	      if(this.value == "") {
	          returnMemoListTable.search("").draw();
	      }
	      return;
	});
	$("#returnMemoListTable tr th").removeClass('sorting_asc');
});
</script>
@endsection