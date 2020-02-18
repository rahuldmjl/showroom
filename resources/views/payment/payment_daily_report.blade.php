<?php
$user = Auth::user();
?>
@extends('layout.mainlayout')
@section('title', 'Daily Report')
@section('distinct_head')
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')
@section('content')

<main  class="main-wrapper clearfix">
	<div class="row page-title clearfix">
    {{ Breadcrumbs::render('payment.payment_daily_report') }}
    </div>

	<div class="widget-list">
      <div class="row">
          <div class="col-md-12 widget-holder">
              <div class="widget-bg">
	             	<div class="widget-heading clearfix">
	                  <h5 class="border-b-light-1 w-100 pb-1 mt-0 mb-2">{{'Daily Report ' . $headerdate}}</h5>
	              	</div>
              		<div class="widget-body clearfix dataTable-length-top-0">

                      @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif
                       @if (session('errors'))
	                       <div class="alert alert-icon alert-danger border-danger alert-dismissible fade show" role="alert">
	                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
	                          <i class="material-icons">highlight_off</i>
	                           <strong>error</strong>: {{ session('errors') }}
	                      </div>
                     	@endif
                      
                      <form name="daily_report_form" method="POST">
	                      <div class="row justify-content-end align-items-center daily_report_filter">
                          <div class="col-md-4">
	                      		
                            <input type="text" name="textfilter" id="textfilter" class="datepicker form-control" autocomplete = 'off' data-plugin-options="{&quot;autoclose&quot;: true,&quot;format&quot;: &quot;yyyy-mm-dd&quot;}"/>
                             <span class="add-on"><i class="icon-calendar" id="cal"></i></span>

                          </div>
                          <div class="col-auto">
                              <button class="btn btn-primary mr-2" id="searchfilter" type="button">Search</button>
                              <button class="btn btn-default" id="searchreset"  type="button" value="">Reset</button>
                          </div>
	                      </div>
                      </form>
                     	<!-- Start Summary -->
                         <div class="tab-pane active" id="inventory-statistic">
                              <div class="row">
                                  <div class="col-lg-3 col-sm-6 widget-holder widget-full-height pr-2">
                                        <div class="widget-bg bg-primary text-inverse">
                                            <div class="widget-body">
                                                <div class="widget-counter pl-0">
                                                    <h6>Total Sales <small class="text-inverse"></small></h6>
                                                    <h3 class="h1 fs-22 "><span class="total-count"><?php echo $totalcount; ?></span></h3><i class="material-icons list-icon">shopping_cart</i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 widget-holder widget-full-height px-2">
                                        <div class="widget-bg bg-color-scheme text-inverse">
                                            <div class="widget-body">
                                                <div class="widget-counter pl-0">
                                                    <h6>Total Invoice Amount <small class="text-inverse"></small></h6>
                                                    <h3 class="h1 fs-22"><span class="total-invoice-amt"><?php echo CommonHelper::covertToCurrency($total_invoice_amt); ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                                </div>
                                            </div>        
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 widget-holder widget-full-height px-2">
                                        <div class="widget-bg bg-gray text-inverse">
                                            <div class="widget-body">
                                                <div class="widget-counter pl-0">
                                                    <h6>Total Payment<small class="text-inverse"></small></h6>
                                                    <h3 class="h1 fs-22"><span class="total-deposite-amt"><?php echo CommonHelper::covertToCurrency($total_deposite_amt); ?></span></h3><i class="material-icons list-icon">shopping_basket</i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                     <div class="col-lg-3 col-sm-6 widget-holder widget-full-height pl-2">
                                        <div class="widget-bg bg-primary text-inverse">
                                            <div class="widget-body">
                                                <div class="widget-counter pl-0">
                                                    <h6>Payment Pending <small class="text-inverse"></small></h6>
                                                    <h3 class="h1 fs-22"><span class="total-pending-amt"><?php echo CommonHelper::covertToCurrency($total_pending_amt); ?></span></h3><i class="material-icons list-icon">shopping_basket</i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                        </div>
                        <!-- End Summary -->

                       <table class="table table-striped table-center mt-0" id="dailyreportlistTable">
                          <thead>
                              <tr class="bg-primary">
                                  <th>#</th>
                                  <th>Name</th>
                                  <th>Invoice No</th>
                                  <th>Amount</th>
                                  <th>Remining</th>
                                </tr>
                             </thead>
                             <tbody>
                              @foreach ($paymentdatas as $paymentdata)
                                <tr>
                                    <td>{{ ++$i }}</td>   
                                    <td>{{ $paymentdata->customer_name  }}</td>
                                    <td>{{ $paymentdata->invoice_number  }}</td> 
                                    <td><?php echo CommonHelper::covertToCurrency($paymentdata->invoice_amount); ?></td> 
                                    <td><?php echo CommonHelper::covertToCurrency($paymentdata->remaining_amount); ?></td>
                                    
                                </tr>
                            @endforeach
                          	</tbody>
                          	<!-- <tr class="bg-primary">
                                                        <th>#</th>
                                                          <th>Name</th>
                                                          <th>Invoice No</th>
                                                          <th>Amount</th>
                                                          <th>Remining</th>
                                                       </tr> -->
                      </table>
                  </div>
              </div>
               	<div class="modal fade bs-modal-lg payment_popup modal-color-scheme" tabindex="-1" id="payment_popup" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
          			</div>
			    </div>
			    </div>
</main>

@endsection
@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
 var table = $('#dailyreportlistTable').DataTable({
 	"dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B><'col'f>><'row'<'col-md-12' <'dailyreportlist' t>>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
  "language": {
    "search": "",
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
  },
  "aLengthMenu": [[25,50,100,200,300,500], [25,50,100,200,300,500]],
  "iDisplayLength": 50,
  "deferLoading": <?=$totalcount?>,
  "processing": true,
  "serverSide": true,
  "searching": true,
  "serverMethod": "GET",
  "ajax":{
    "url": "{{action('PaymentController@daily_report_response')}}",
    "data": function(data, callback){
      showLoader();
      data._token = "{{ csrf_token() }}";

      var textfilter = $('#textfilter').val();
       if(textfilter != '') {
        data.textfilter = textfilter;
      }
       
        var reset = $('#searchreset').attr('data-reset');
        if (reset != '') {
           data.reset=   $('#searchreset').attr('data-reset');
        }
    },
    complete: function(response){
      hideLoader();
      $('.total-count').html(response.responseJSON.recordsTotal);
      $('.total-invoice-amt').html(response.responseJSON.invoice_amt);
    }
  },"columnDefs": [ {
    "targets": [0],
    "orderable": false
    }
  ]   
});

    $('#searchfilter').click(function(){
    	table.draw();
    	$('#searchreset').attr("data-reset", "");
    });

    $('#searchreset').click(function(){
    	$('#textfilter').val("");
    	table.draw();
    });


  </script>
@endsection