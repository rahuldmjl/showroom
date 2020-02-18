@extends('layout.mainlayout')

@section('title', 'Raw Diamond Inventory')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">

   <div class="row page-title clearfix">
      {{ Breadcrumbs::render('diamondraw.index') }}
      <!-- /.page-title-right -->
  </div>
  <div class="widget-list">
    <div class="row">
      <div class="col-md-12 widget-holder">
        <div class="widget-bg">
          <div class="progress progress-lg">
            <div role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar bg-success">Raw</div>
            <div role="progressbar" style="display: none;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar bg-success"></div>
            <div role="progressbar" style="display: none;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success"></div>
            <div role="progressbar" style="display: none;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success"></div>
            <div role="progressbar" style="display: none;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success"></div>
          </div>
          <div class="widget-heading clearfix position-relative  mt-3">
            <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'Diamond Raw Inventory'}}</h5>

            <div class="btn-top-right">
              <a href="{{ route('diamondraw.create') }}" class="btn btn-primary ripple small-btn-style"><i class="material-icons list-icon fs-24">playlist_add</i> Add Diamonds</a>
            </div>
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
            <table class="table table-striped table-center table-responsive diamond" >
              <thead>
                <tr class="bg-primary">
                  <th>Packet Name</th>
                  <th>Weight (in cts)</th>
                  <th>Purchased Date</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($diamondraw as $key => $diamonds)
                  <tr>
                    <td>{{ $diamonds->packet_name }}</td>
                    <td>{{ $diamonds->total_weight }}</td>
                    <td>{{$diamonds->purchased_at}}</td>
                    <td>
                      @if($diamonds->cvd_status == 1)
                      <!-- <a class="btn btn-outline-default ripple getid"  style="display: none;" value ="{{$diamonds->id}}"onclick="showDetail(<?php echo $diamonds->id; ?>)"><i class="material-icons list-icon">check</i>  <span>CVD</span></a> -->
                      @else
                       <!-- <a class="btn btn-success ripple getid"   value ="{{$diamonds->id}}"onclick="showDetail(<?php // echo $diamonds->id; ?>)"><i class="material-icons list-icon">check</i>  <span>CVD</span></a> -->
                        <button class="btn btn-success success-btn-style btn-sm btn-rounded ripple" onclick="showDetail(<?php echo $diamonds->id; ?>)">
                        <span>CVD</span><i class="material-icons list-icon">check</i>
                        </button>
                      @endif
                    </td>
                  </tr>
                @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th>Packet Name</th>
                    <th>Weight (in cts)</th>
                    <th>Purchased Date</th>
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
<div class="modal fade bs-modal-lg modal-color-scheme showDetail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    </div>
  </div>
  <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->

@endsection

@section('distinct_footer_script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script>
<script type="text/javascript">
  /* var buttonCommon = {
      exportOptions: {
          format: {
              body: function ( data, row, column, node ) {                    
                  if (column === 2) {
                    data = data.replace(/[$,]/g, '').replace(/&#x20b9; /g, '');
                  }
                  
                  arr = [data];
                  for (i = 0; i < arr.length; i++) { 
                    if (column === 6) {
                      if(arr[i] == null){
                        data = "";
                      }
                    }
                  }
                  
                  if (column === 3) {
                    data = data.replace(/(&nbsp;|<([^>]+)>)/ig, "");
                  }
                  return data;
              }
          }
      }
  }; */
  var diamondlist = $('.diamond').DataTable({
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
        title: 'Diamond-Raw-Inventory-Data',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
            columns: [0,1,2],
            orthogonal: 'export'
        }
      },
      {
        extend: 'excel',
        footer: false,
        title: 'Diamond-Raw-Inventory-Data',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
            columns: [0,1,2],
            orthogonal: 'export'
        }
      }
    ],
    "deferLoading": <?=$totalcount?>,
    "processing": true,
    "serverSide": true,
    "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
    "serverMethod": "post",
    "ajax":{
      "url": "{{action('DiamondRawController@diamondrawrespose')}}",
      "data": function(data, callback){
        data._token = "{{ csrf_token() }}";

      }
    },"columnDefs": [ {
      "targets": [3],
      "orderable": false
      }
    ]
  });
  function showDetail(id) {
    if(id !='')
    {
      jQuery.ajax({
        type: "GET",
        dataType: "json",
        url: "{{action('DiamondRawController@CVD')}}",
        data: {
        "_token": '{{ csrf_token() }}',
        "id":id,
        },
        success: function(response) {
          $('.modal-content').html(response.html);
          $('.showDetail').modal('show');
        }
      });
    }
  }
</script>
@endsection