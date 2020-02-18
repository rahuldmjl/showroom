<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
?>
@extends('layout.mainlayout')

@section('title', 'Sales Return List')

@section('distinct_head')

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/css/ion.rangeSlider.min.css"/>
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('showroom.salesreturnlist') }}
      <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div class="widget-list">
         <div class="row">
          <div class="col-md-12 widget-holder loader-area" style="display: none;">
            <div class="widget-bg text-center">
              <div class="loader"></div>
            </div>
          </div>
          <div class="col-md-12 widget-holder content-area">
              <div class="widget-bg">
                  <div class="widget-heading clearfix ">
                      <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">Sales Return List</h5>
                  </div>
                  <!-- /.widget-heading -->
                  <div class="widget-body clearfix ">
                      @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif

                      <table class="table table-center table-head-box checkbox checkbox-primary nowrap" id="salesReturnListTable" >
                          <thead>
                              <tr class="bg-primary">
                                  <th>Return No.</th>
                                  <th>Invoice No.</th>
                                  <th>Name</th>
                                  <th>Created Date</th>
                                  <th>Grand Total</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                            <?php
foreach ($salesReturnData as $key => $salesReturn) {
	$customerName = isset($salesReturn->customer_id) ? InventoryHelper::getCustomerName($salesReturn->customer_id) : '';
	$returnNumber = isset($salesReturn->sales_return_no) ? $salesReturn->sales_return_no : '';
	$invoiceNumber = isset($salesReturn->invoice_no) ? $salesReturn->invoice_no : '';
	$createdDate = isset($salesReturn->created_at) ? $salesReturn->created_at : '';
	$grandTotal = isset($salesReturn->total_invoice_value) ? ShowroomHelper::currencyFormat(round($salesReturn->total_invoice_value)) : '';
	$isCreditNoteGenerated = isset($salesReturn->is_credited) ? $salesReturn->is_credited : 'no';
	$generateCreditNoteClass = '';
	$viewCreditNoteClass = 'disabled';
	if (!empty($isCreditNoteGenerated) && $isCreditNoteGenerated == 'yes') {
		$generateCreditNoteClass = 'disabled';
		$viewCreditNoteClass = '';
	}

	?>
                            	<tr>
                            		<td>{{$returnNumber}}</td>
                            		<td>{{$invoiceNumber}}</td>
                            		<td>{{$customerName}}</td>
                            		<td>{{$createdDate}}</td>
                            		<td>{{$grandTotal}}</td>
                            		<td>
                            			<!-- <a class="color-content table-action-style" href="{{ route('viewcreditpurchasenote',['id'=>$salesReturn->id]) }}">Credit Note Purchase</a> -->

                            			<a title="Generate Credit Note" class="color-content table-action-style btn-generate-creditnote pointer <?php echo $generateCreditNoteClass ?>" data-href="{{ route('generatecreditsalenote',['id'=>$salesReturn->id]) }}"><i class="material-icons">note_add</i></a>

                                  <a title="View Credit Note" class="color-content table-action-style <?php echo $viewCreditNoteClass; ?>" href="{{ route('viewcreditsalenote',['id'=>$salesReturn->id]) }}" ><i class="material-icons">remove_red_eye</i></a>

                            			<!-- <a class="color-content table-action-style" href="{{ route('viewdebitpurchasenote',['id'=>$salesReturn->id]) }}">Credit Note Purchase</a> -->

                            			<!-- <a class="color-content table-action-style" href="{{ route('viewdebitsalenote',['id'=>$salesReturn->id]) }}">Credit Note Sale</a> -->
                            		</td>
                            	</tr>
                            <?php
}
?>
                          </tbody>
                          <!-- <tfoot>
                              <tr>
                                  <th>Return No.</th>
                                  <th>Invoice No.</th>
                                  <th>Name</th>
                                  <th>Created Date</th>
                                  <th>Grand Total</th>
                                  <th>Action</th>
                              </tr>
                          </tfoot> -->
                      </table>
                  </div>
                  <!-- /.widget-body -->
              </div>
              <!-- /.widget-bg -->
          </div>
          <!-- /.widget-holder -->
      </div>
      <!-- /.row -->
      </div>
  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/dataTables.responsive.min.js"></script>
<script type="text/javascript">
var salesReturnProductTable = $('#salesReturnListTable').DataTable({
    "aLengthMenu": [[25,50,100,200,300,500], [25,50,100,200,300,500]],
    "iDisplayLength": 50,
    "dom": '<"datatable_top_custom_lengthinfo custom-page-length d-flex flex-wrap"i   <"datatable_top_showroom_length mx-3"l> <"#inventory-toolbar">>frtip',
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
</script>
<script>
  $(document).ready(function(){
      $(document).on('click','.btn-generate-creditnote', function(){
          var href = $(this).data('href');
          var generateCretidNote = $(this);
          var viewCreditNote = $(this).next();
          /*$(this).addClass('disabled');
          $(this).next().removeClass('disabled');*/
          swal({
                title: 'Are you sure?',
                text: "<?php echo Config::get('constants.message.sales_credit_note_generate_confirmation'); ?>",
                type: 'info',
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
                }).then(function() {
                    generateCretidNote.addClass('disabled');
                    viewCreditNote.removeClass('disabled');
                    window.location.href = href;
                })
      });
  });
</script>

@endsection