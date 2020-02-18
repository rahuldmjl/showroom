@extends('layout.mainlayout')

@section('title', 'Paid Transaction')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
    {{ Breadcrumbs::render('vendor.vendorpaidtransaction') }}
      <!-- /.page-title-right -->
     
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
                  <div class="widget-heading clearfix">
                      <h5 class="border-b-light-1 w-100 pb-1 mt-0 mb-2">{{'Paid Transaction'}}</h5>
                  </div>
                  <!-- /.widget-heading -->
                    <div class="widget-body clearfix dataTable-length-top-0">

                      @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif
                      @foreach ($transaction as $value)
                      <input type="hidden" name="payment_id" class="payment_id" value="{{$value->payment_id}}">
                      @endforeach
                      <a href="{{ URL::previous() }}#unpaid-invoice" class="btn btn-info btn-top-right2 small-btn-style">Go Back</a>
                       <table class="paidtransaction table table-striped table-center table-responsive">
                          <thead>
                              <tr class="bg-primary">
                                  <th>No</th>
                                  <th>Customer Name</th>
                                  <th>Invoice Number</th>
                                  <th>Invoice Amount</th>
                                  <th>Remaining Amount</th>
                                  <th>Paid Date</th>
                                  
                                </tr>
                             </thead>
                             <tbody>
                              @foreach ($transaction as $value)
                                  <tr>  
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $value->customer_name}}</td>
                                    <td>{{ $value->invoice_number}}</td>
                                    <td class="WebRupee">&#x20B9; {{ $value->invoice_amount}}</td>
                                    <td class="WebRupee">&#x20B9;{{$value->remaining }}</td>
                                    
                                    <td>{{ $value->paid_at}}</td>
                                </tr>
                            @endforeach
                          </tbody>
                          <tfoot>
                            <tr>
                                  <th>No</th>
                                  <th>Customer Name</th>
                                  <th>Invoice Number</th>
                                  <th>Invoice Amount</th>
                                  <th>Remaining Amount</th>
                                  <th>Paid Date</th>
                            </tr>
                          </tfoot>
                      </table>
                     
                  </div>
              </div>
          </div>
      </div>
     </div>
 </main>
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
  var  paidtransaction= $('.paidtransaction').DataTable({
  "language": {
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
    //"sProcessing": "<div id='loader'></div>"
  },
  "deferLoading": <?=$totalcount?>,
  "processing": true,
  "serverSide": true,
  "serverMethod": "post",
  "ajax":{
    "url": "{{action('VendorController@vendorunpaidtransactionresponse')}}",
    "data": function(data, callback){
      data._token = "{{ csrf_token() }}";
      data._id = $('.payment_id').val();
     
    },
  } ,

      "columnDefs": [ {
    "targets": [4,5],
    "orderable": false
    }
  ]  
});
</script>
@endsection
