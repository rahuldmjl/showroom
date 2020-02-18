@extends('layout.mainlayout')

@section('title', 'Gold Inventory')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('metals.index') }}
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
                      <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'Gold Inventory'}}</h5>
                      <div class="btn-top-right2">
                        <a href="{{ route('metals.create') }}" class="btn btn-primary small-btn-style ripple"><i class="material-icons list-icon fs-24">playlist_add</i> Add Gold</a>
                      </div>
                  </div>
                  <!-- /.widget-heading -->
                  <div class="widget-body clearfix">

                      @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif
                      
                      <table class="table table-striped table-responsive" id = "goldresponse">
                          <thead>
                              <tr class="bg-primary">
                                 <th>No</th>
                                 <th>Metal Type</th>
                                 <th>Total Weight (gms)</th>
                                 <th>Avg. Rate (per gm)</th>
                                 <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($metals as $key => $metal)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{$metal->Metal_Type}}</td>
                                    <td>{{ round($metal->total_metal_weight,2) }}</td>
                                    <td><?=CommonHelper::covertToCurrency($metal->avg_rate);?></td>
                                    <td>
                                        <a class="color-content table-action-style"  title="Show" href="{{ route('metals.transactions',$metal->metal_type) }}"><i class="material-icons md-18">remove_red_eye</i></a>
                                    </td>
                                </tr>
                                @endforeach
                          </tbody>
                          <tfoot>
                              <tr>
                                 <th>No</th>
                                 <th>Metal Type</th>
                                 <th>Total Weight (gms)</th>
                                 <th>Avg. Rate (per gm)</th>
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
<!-- /.main-wrappper -->



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
                    if (column === 3) {
                      data = data.replace('₹ ', '');
                    }
                    return data;
                }
            }
        }
    };
    
  var table = $('#goldresponse').DataTable({


    "dom": 'Bfrtip',

  "language": {
    "search": "Search:",
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
  
  },
  "buttons": [
  $.extend( true, {}, buttonCommon, {
      extend: 'csv',
      footer: false,
      title: 'Gold-Inventory-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3],
          orthogonal: 'export'
      }
    }),
    $.extend( true, {}, buttonCommon, {
      extend: 'excel',
      footer: false,
      title: 'Gold-Inventory-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3],
          orthogonal: 'export'
      }
    })
    
  ] ,
  "order": [ 0, "desc" ],
  "deferLoading": <?=$datacount?>,
  "processing": true,
  "serverSide": true,
  "searching": true,
  "lengthChange": false,
  "serverMethod": "GET",
   "ajax":{
        "url": "{{action('MetalController@goldresponse')}}",
        "data": function(data, callback){
          data._token = "{{ csrf_token() }}";
         
        },
      },
  "columnDefs": [ {
    "targets": [0,4],
    "orderable": false,
  }]
});
$( table.table().container() )
    .addClass('goldInventory');
</script>
@endsection