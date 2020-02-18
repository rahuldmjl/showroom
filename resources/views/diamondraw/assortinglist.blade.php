@extends('layout.mainlayout')

@section('title', 'Assorting List')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <div class="row page-title clearfix">
    {{ Breadcrumbs::render('diamondraw.assortinglist') }}
    <!-- /.page-title-right -->
  </div>
  <div class="widget-list">
    <div class="row">
      <div class="col-md-12 widget-holder">
        <div class="widget-bg">
          <div class="progress progress-lg">
            <div role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar bg-success">Raw</div>
            <div role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar bg-success">CVD</div>
            <div role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success">Assorting</div>
            <div role="progressbar" style="display: none;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success"></div>
            <div role="progressbar" style="display: none;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success"></div>
          </div>
          <div class="widget-heading clearfix">
            <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'Assorting List'}}</h5>
          </div>
          <!-- /.widget-heading -->
          <div class="widget-body clearfix">
            @if ($message = Session::get('success'))
              <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span></button>
                <i class="material-icons list-icon">check_circle</i>
                <strong>Success</strong>: {{ $message }}
              </div>
            @endif
            <table class="table table-striped table-center diamond">
              <thead>
                <tr class="bg-primary">
                  <th>Packet Name</th>
                  <th>Weight (in cts)</th>
                  <th>Rejected From Assorting(in cts)</th>
                  <th>Total Rejected (in cts)</th>
                  <th>Loss From Assorting  (in cts)</th>
                  <th>Total Loss (in cts)</th>
                  <th>Total Purchase</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($diamondraw as $key => $diamonds)
                  <tr>
                    <td>{{ $diamonds->packet_name }}</td>
                    <td>{{ $diamonds->assorting_weight }}</td>
                    <td>{{ $diamonds->assorting_rejected }}</td>
                    <td>{{number_format($diamonds->total_rejected_weight,3)}}</td>
                    <td>{{$diamonds->assorting_loss}}</td>
                    <td>
                      {{number_format($diamonds->assorting_loss + $diamonds->cvd_loss,3)}}
                    </td>
                    <td>{{$diamonds->total_weight}}</td>
                    <td>
                      @if($diamonds->moved_to_inventory == 1)
                        <!-- <a href="{{route('diamondmovetoinventory.create',['id' => $diamonds->id])}}" style="display: none;" class="btn btn-info getid"  value ="">Move To Inventory</a> -->
                        <!-- <a class="color-content table-action-style" href="{{route('diamondmovetoinventory.create',['id' => $diamonds->id])}}"><i class="material-icons md-18">move_to_inbox</i></a> -->
                      @else
                        <!-- <a href="{{route('diamondmovetoinventory.create',['id' => $diamonds->id])}}"  class="btn btn-info getid"  value ="">Move To Inventory</a><br><br> -->
                        <a class="color-content table-action-style " id="moveinventory"  href="{{route('diamondmovetoinventory.create',['id' => $diamonds->id])}}" title="Sizing"><i class="material-icons md-18">move_to_inbox</i></a>
                      @endif
                      @if($diamonds->memo_returned == 1)
                        <a href="{{action('DiamondRawController@downloadmemo',['id' => $diamonds->id])}}" target="_blank" class="color-content table-action-style" title="Download Return Voucher"><i class="material-icons md-18">file_download</i></a>
                      @else
                        <!-- <a class="color-content btn btn-info export_pdf"  data-token="{{ csrf_token() }}">Return To Vendor</a>
                        <input type="hidden" name="diamondraw_id" class="diamondraw_id" value="<?php echo $diamonds->id; ?>"> -->
                        <a href="javascript:void(0);" class="color-content table-action-style export_pdf" data-token="{{ csrf_token() }}" data-raw-id="<?php echo $diamonds->id; ?>" title="Return to Vendor"><i class="material-icons md-18">assignment_return</i></a>
                        <input type="hidden" name="diamondraw_id" class="diamondraw_id" value="<?php echo $diamonds->id; ?>">
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr>
                  <th>Packet Name</th>
                  <th>Weight (in cts)</th>
                  <th>Rejected From Assorting(in cts)</th>
                  <th>Total Rejected (in cts)</th>
                  <th>Loss From Assorting  (in cts)</th>
                  <th>Total Loss (in cts)</th>
                  <th>Total Purchase</th>
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
<div class="modal fade bs-modal-lg Sizing" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
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
  var diamondlist = $('.diamond').DataTable({
    "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B><'col'f>><'row'<'col-md-12' <'scroll-lg' t>>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
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
        title: 'Assorting-List-Data',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
          columns: [0,1,2,3,4,5,6],
          orthogonal: 'export'
        }
      },
      {
        extend: 'excel',
        footer: false,
        title: 'Assorting-List-Data',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
            columns: [0,1,2,3,4,5,6],
            orthogonal: 'export'
        }
      }
    ],
    "deferLoading": <?=$totalcount?>,
    "processing": true,
    "serverSide": true,
    "Searchable": true,
    "serverMethod": "post",
    "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
    "ajax":{
      "url": "{{action('DiamondRawController@assorting_response')}}",
      "data": function(data, callback){
        data._token = "{{ csrf_token() }}";

      }
    },"columnDefs": [ 
      {"targets": [7],"orderable": false}
    ]
  });

  function Sizing(id) {
    if(id !='')
    {
      jQuery.ajax({
        type: "GET",
        dataType: "json",
        url: "{{action('DiamondRawController@Sizing')}}",
        data: {
        "_token": '{{ csrf_token() }}',
        "id":id,
        },
        success: function(response) {
          $('.modal-content').html(response.html);
          $('.Sizing').modal('show');
        }
     });
    }
  }
  $(document).on('click', '.export_pdf' ,function(){
    var id = $(this).attr('data-raw-id');
    swal({
    title: 'Are you sure?',
    text: "Are you sure you want to return rejected diamonds to vendor ?",
    type: 'error',
    showCancelButton: true,
    confirmButtonClass: 'btn btn-danger',
    confirmButtonText: 'Yes'
    }).then(function (data) {
      if (data.value) {
        //console.log($(this));
        //var id = $('.diamondraw_id').val();
        var query = {id: id }
        var url = "<?=URL::to('/') . '/diamondraw/returnmemo'?>?" + $.param(query);
        //alert(url);
        //return;
        window.location = url;
        //swal("Voucher Generated", "Voucher Generated Successfully !", "success");
        /*swal({
        title: 'Downloaded!',
        text: 'Selected Return Memo  has been Downloaded.',
        type: 'success',
        confirmButtonClass: 'btn btn-success',
        cancelButtonText: "Cancel",
        }).then((value) => {
        window.location = url;
        });*/
      }
    });
  });
  $('#moveinventory').on('click',function(){
    var setting_val = $('.lossdata').val();
    var loss= $('.settingvalue').val();

    if( loss  <= setting_val){
     swal({
        title: 'Exceeded Limit',
        text: "You have exceeded limit of loss , can't move ahead !!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonClass: 'btn btn-warning',
        confirmButtonText: 'Yes, Extend Limit!'
      }).then(function (inputvalue) {
        /*ACTION  PENDING*/
      },function(dismiss){
        if(dismiss == 'cancel'){
          var loss = $('.lossdata').val();
          $('.lossdata').val(0);
          $('.lossreason').hide();
          var Weightcom= $('#weight').val()
          var weightraw = $('#reject').val()
          var total = Number(Weightcom)  +  Number(loss) ;
          $('.weightdata').val(total);
        }
      });
    }
  });
</script>
@endsection