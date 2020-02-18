@extends('layout.mainlayout')

@section('title', 'Sizing List')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">

   <div class="row page-title clearfix">
      {{ Breadcrumbs::render('diamondraw.sizinglist') }}
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
                    <div role="progressbar"  aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success">Sizing</div>
                    <div role="progressbar" style="display: none;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success"></div>
                </div>
                  <div class="widget-heading clearfix">
                      <h5>{{'Sizing List'}}</h5>


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

                      <table class="table table-striped  table-responsive diamond" >
                          <thead>
                              <tr>
                                 <th>Packet Name</th>
                                 <th>Weight (in cts)</th>
                                 <th>Rejected From Sizing (in cts)</th>
                                 <th>Total Rejected (in cts)</th>
                                 <th>Loss From Sizing  (in cts)</th>
                                 <th>Total Loss (in cts)</th>
                                 <th>Total Purchase</th>
                                 <!-- <th>Action</th> -->
                              </tr>
                          </thead>
                          <tbody>

                              @foreach ($diamondraw as $key => $diamonds)
                                <tr>
                                  <td>{{ $diamonds->packet_name }}</td>
                                  <td>{{ $diamonds->sizing_weight }}</td>
                                  <td>{{ $diamonds->sizing_rejected }}</td>
                                  <td>{{$diamonds->total_rejected_weight}}</td>
                                  <td>{{$diamonds->total_loss}}</td>
                                  <td>{{$diamonds->sizing_loss}}</td>
                                  <td>{{ $diamonds->sizing_weight }}</td>
                                  <!-- <td> -->

                                     <!--  @if($diamonds->moved_to_inventory == 1) -->
                                         <!-- <a href="{{route('diamondmovetoinventory.create',['id' => $diamonds->id])}}" style="display: none;" class="btn btn-info getid"  value ="">Move To Inventory</a> -->
                                         <!-- <a class="color-content table-action-style" href="{{route('diamondmovetoinventory.create',['id' => $diamonds->id])}}"><i class="material-icons md-18">move_to_inbox</i></a> -->
                                     <!--  @else -->
                                         <!-- <a href="{{route('diamondmovetoinventory.create',['id' => $diamonds->id])}}"  class="btn btn-info getid"  value ="">Move To Inventory</a><br><br> -->
                                        <!--  <a class="color-content table-action-style" href="{{route('diamondmovetoinventory.create',['id' => $diamonds->id])}}" title="Move to Inventory"><i class="material-icons md-18">move_to_inbox</i></a>
                                      @endif
                                      @if($diamonds->memo_returned == 1)
                                       <a href="{{action('DiamondRawController@downloadmemo',['id' => $diamonds->id])}}" target="_blank" class="color-content table-action-style" title="Download Return Voucher"><i class="material-icons md-18">file_download</i></a>
                                       @else -->
                                         <!-- <a class="color-content btn btn-info export_pdf"  data-token="{{ csrf_token() }}">Return To Vendor</a>
                                       <input type="hidden" name="diamondraw_id" class="diamondraw_id" value="<?php echo $diamonds->id; ?>"> -->
                                    <!--    <a href="javascript:void(0);" class="color-content table-action-style export_pdf" data-token="{{ csrf_token() }}" title="Return to Vendor"><i class="material-icons md-18">assignment_return</i></a>
                                       <input type="hidden" name="diamondraw_id" class="diamondraw_id" value="<?php echo $diamonds->id; ?>">
                                       @endif -->


                                   <!--  </td> -->

                                </tr>
                                @endforeach
                          </tbody>
                          <tfoot>
                              <tr>
                                <th>Packet Name</th>
                                 <th>Weight (in cts)</th>
                                 <th>Rejected From Sizing (in cts)</th>
                                 <th>Total Rejected (in cts)</th>
                                 <th>Loss From Sizing  (in cts)</th>
                                 <th>Total Loss (in cts)</th>
                                 <th>Total Purchase</th>
                                <!--  <th>Action</th> -->
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
<script type="text/javascript">
 var diamondlist = $('.diamond').DataTable({

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
    "url": "{{action('DiamondRawController@sizing_response')}}",
    "data": function(data, callback){
      data._token = "{{ csrf_token() }}";

    }
  },"columnDefs": [ {
    "targets": [4],
    "orderable": false
    }
  ]
});







  $('.export_pdf').on('click',function(){

   swal({
      title: 'Are you sure?',
      text: "Are you sure you want to return rejected diamonds to vendor ?",
      type: 'error',
      showCancelButton: true,
      confirmButtonClass: 'btn btn-danger',
      confirmButtonText: 'Yes'
    }).then(function (data) {
      if (data.value) {
         var id = $('.diamondraw_id').val();
        var query = {id: id }
        var url = "<?=URL::to('/') . '/diamondraw/returnmemo'?>?" + $.param(query);
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



</script>


@endsection