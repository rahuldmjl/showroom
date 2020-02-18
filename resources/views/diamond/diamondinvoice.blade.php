<?php
use App\Helpers\InventoryHelper;
?>
@extends('layout.mainlayout')
@section('title', 'Diamond Invoice')
@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  	{{ Breadcrumbs::render('diamond.diamondinvoice') }}
      <!-- /.page-title-right -->
  </div>
    <div class="col-md-12 widget-holder loader-area" style="display: none;">
    <div class="widget-bg text-center">
      <div class="loader"></div>
    </div>
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
  						<h5 class="border-b-light-1 pb-2 mb-4 mt-0">Invoice List</h5>
  					</div>
  					<div class="widget-body clearfix dataTable-length-top-0">
  						@if ($message = Session::get('success'))
	                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                        <i class="material-icons list-icon">check_circle</i>
	                        <strong>Success</strong>: {{ $message }}
	                      </div>
	                    @endif
	                    <table class="table table-striped table-center" id="invoiceListTable" >
  							<thead>
  								<tr class="bg-primary">
  									<th>Invoice No.</th>
  									<th>Name</th>
  									<th>Date</th>
  									<th>Grand Total</th>
                   
  									<th>Action</th>
  								</tr>
  							</thead>
  							<tbody>
                  <?php foreach($diamonds as $key => $diamond) { ?>
                  <tr>
                    <td><?=$diamond->invoice_number?></td>
                    <td><?php echo InventoryHelper::getCustomerName($diamond->customer_id); ?></td>
                    <td><?=$diamond->created_at?></td>
                    <td><?=$diamond->final_price?></td>

                    <td> <a href="JavaScript:void(0)"><i title="Detail" onclick="showDetail('<?php echo $diamond->id;?>')"  class="material-icons list-icon">info</i></a>
                      <a class="color-content table-action-style" title="Generate Invoice" href="{{ route('diamond.diamond_invoice_download',['id'=>$diamond->id]) }}"><i class="material-icons md-18">file_download</i></a>
                    </td>


                   <!--  <td><a title="View transactions" class="color-content table-action-style" href="#"><i class="material-icons md-18">remove_red_eye</i></a></td> -->
                  </tr>
                <?php } ?>
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
<div class="modal fade bs-modal-lg modal-color-scheme costing_showDetail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
          <div class="modal-header text-inverse">
              <h5 class="modal-title" id="myLargeModalLabel">Diamond Invoice Detail</h5>
          </div>
  <div class="modal-body"> 
  </div>
  <div class="modal-footer"> 
      <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
  </div>
</div>
<!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script>
<script type="text/javascript">
  function showDetail(id) {
    jQuery.ajax({
      type: "GET",
      dataType: "json",
      url: "{{action('DiamondController@diamondinvoicedata')}}",
      data: {
      "_token": '{{ csrf_token() }}',
      "id": id
      },
      success: function(data){
        $('.modal-body').html(data.html);
        $('.costing_showDetail').modal('show');
      }
   });
  }
  var diamondinvoicelist = $('#invoiceListTable').DataTable({
    "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B><'col'f>><'row'<'col-md-12't>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
    "language": {
      "infoEmpty": "No matched records found",
      "zeroRecords": "No matched records found",
      "emptyTable": "No data available in table",
      //"sProcessing": "<div id='loader'></div>"
    },
    "buttons": [
    {
      extend: 'csv',
      footer: false,
      title: 'Invoice-List-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3],
          orthogonal: 'export'
      }
    },
    {
      extend: 'excel',
      footer: false,
      title: 'Invoice-List-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3],
          orthogonal: 'export'
      }
    }
  ],
  "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
  "deferLoading": <?=$totalcount?>,
   "processing": true,
  "serverSide": true,
  "serverMethod": "get",
  "ajax":{
    "url": "{{action('DiamondController@diamondinvoicelist')}}",
    "data": function(data, callback){
    data._token = "{{ csrf_token() }}";
     
    },
    complete: function(response){
    
    }
  },"columnDefs": [{
    "targets": [4],
    "orderable": false
  }]
  });
</script>


@endsection