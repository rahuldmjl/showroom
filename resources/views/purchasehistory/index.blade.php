@extends('layout.mainlayout')

@section('title', 'Purchase History List')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">

 <div class="row page-title clearfix">
        {{ Breadcrumbs::render('purchasehistory.index') }}
        <!-- /.page-title-right -->
    </div>

<div class="widget-list">
      <div class="row">
          <div class="col-md-12 widget-holder">
              <div class="widget-bg">
                  <div class="widget-heading clearfix">
                      <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'Purchase History'}}</h5>
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

                      <table class="table table-striped table-center table-responsive " id = "purchase_history">
                          <thead>
                              <tr class="bg-primary">
                                 <th>Material Type</th>
                                 <th>Vendor Name</th>
                                 <th>Purchase Invoice</th>
                                 <th>Purchase Amount</th>
                                 <th>Purchase Status</th>
                                 <th>Created By</th>
                                 <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                          
                              @foreach ($metal_payments as $key => $purchasehistorylist)
                                                         
                                <tr>
                                    <td>{{ $purchasehistorylist->metal_type }}</td>
                                    <td>{{ $purchasehistorylist->name }}</td>
                                    <td>{{ $purchasehistorylist->invoice_number }}</td>
                                    <td>{{ $purchasehistorylist->amount_paid }}</td>
                                    <td><label class="badge badge-success">{{ $purchasehistorylist->pay_type }}</label></td>
                                    <td>{{ $purchasehistorylist->created_by }}</td>
                                    <td><?php if(Auth::user()->id){

                                    }?>
                                        <?php if($purchasehistorylist->metal_type == "Diamond"){ ?>
                                        <a class="color-content table-action-style "  href="{{ route('purchasehistory.diamonddetails',$purchasehistorylist->id) }}"title="View"><i class="material-icons md-18" >remove_red_eye</i></a>
                                        <a class="color-content table-action-style <?=(Auth::User()->id != $purchasehistorylist->user_id ||  $purchasehistorylist->account_status == 1 ) ? 'disabled" href="#" ' : '' ?> " href="{{ action('PurchaseHistoryController@editdiamond',$purchasehistorylist->id) }}" title="Edit"><i class="material-icons md-18">edit</i></a>
                                        <?php } else{ ?>
                                            <a class="color-content table-action-style" href="{{ action('PurchaseHistoryController@golddetails',$purchasehistorylist->id) }}" title="View"><i class="material-icons md-18">remove_red_eye</i></a>
                                            <a class="color-content table-action-style  <?=(Auth::User()->id != $purchasehistorylist->user_id || $purchasehistorylist->account_status == 1 ) ? 'disabled" href="#" ' : ''?>" href="{{ action('PurchaseHistoryController@editgold',$purchasehistorylist->id) }}" title="Edit"><i class="material-icons md-18">edit</i></a>
                                        <?php } ?>
                                        
                                    </td>                               
                                </tr>
                                @endforeach
                            <?php //exit; ?>
                          </tbody>
                          <tfoot>
                              <tr>
                                <th>Material Type</th>
                                <th>Vendor Name</th>
                                <th>Purchase Invoice</th>
                                <th>Purchase Amount</th>
                                <th>Purchase Status</th>
                                <th>Created By</th>
                                <th>Action</th>

                              </tr>
                          </tfoot>
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
var buttonCommon = {
        exportOptions: {
            format: {
                body: function ( data, row, column, node ) {                    
                    if (column === 4) {
                      data = data.replace(/(&nbsp;|<([^>]+)>)/ig, "");
                    }
                    return data;
                }
            }
        }
    };
var table = $('#purchase_history').DataTable({
  "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B><'col'f>><'row'<'col-md-12't>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
  "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
  "buttons": [
    $.extend( true, {}, buttonCommon, {
      extend: 'csv',
      footer: false,
      title: 'Purchase-History-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3,4],
          orthogonal: 'export'
      }
    }),
    $.extend( true, {}, buttonCommon, {
      extend: 'excel',
      footer: false,
      title: 'Purchase-History-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3,4],
          orthogonal: 'export'
      }
    })
  ],
 // "order": [[ 0, "desc" ]],
  "deferLoading": <?=$datacount?>,
  "processing": true,
  "serverSide": true,
  "searching": true,
  "lengthChange": true,
  "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
  "serverMethod": "GET",
  "ajax":{
    "url": "{{action('PurchaseHistoryController@filter_history')}}",
    "data": function(data, callback){
      data._token = "{{ csrf_token() }}";
    },
    complete: function(response){
      //hideLoader();
    }
  },  
   "columnDefs": [ {
    "targets": [4,5,6],
    "orderable": false,
    }
  ]
});
</script>
@endsection