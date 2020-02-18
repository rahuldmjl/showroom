@extends('layout.mainlayout')

@section('title', 'Diamond Type')
@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('vendor-diamond-type.index') }}
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
                      <h5>{{'Diamond Type'}}</h5>

                      <div class="pull-right">
                        <a href="{{ route('vendor-diamond-type.create') }}" class="btn btn-primary ripple"><i class="material-icons list-icon fs-24">playlist_add</i> Add Diamond</a>
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
                       <table class="diamondlist table table-striped table-responsive">
                          <thead>
                              <tr>
                                  <th>No</th>
                                  <th>Name</th>
                                  <th>Action</th>
                             	</tr>
                             </thead>
                             <tbody>
                              @foreach ($data as $key => $value)
                              	 <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ $value->name }}</td>
                              
                                <td>
                                  <a class="color-content table-action-style" href="{{ route('vendor-diamond-type.show',$value->vendor_diamond_id) }}" style="display: none;"><i class="material-icons md-18">show</i></a>
                                  <a class="color-content table-action-style" href="{{ route('vendor-diamond-type.edit',$value->vendor_diamond_id) }}"><i class="material-icons md-18">edit</i></a>
                                  <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deletediamond({{$value->vendor_diamond_id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>
                                  
                                    {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $value->vendor_diamond_id],'style'=>'display:none']) !!}
                                        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                                    {!! Form::close() !!}
                                </td>

                              </tr>
                              @endforeach
                          </tbody>
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
  var diamondlist = $('.diamondlist').DataTable({

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
    "url": "{{action('DiamondTypeController@diamondrespose')}}",
    order: [ 1, 'desc' ],
    "data": function(data, callback){
      data._token = "{{ csrf_token() }}";
      
    },
  }  
});
  function deletediamond(vendor_diamond_id, token){
  	
    swal({
      title: 'Are you sure?',
      text: "You won't be able to recover this Diamond Type!",
      type: 'error',
      showCancelButton: true,
      confirmButtonClass: 'btn btn-danger',
      confirmButtonText: 'Yes, delete it!'
    }).then(function (data) {
     if (data.value) {
      $.ajax(
      {
          url: '<?=URL::to('/');?>'+'/vendor-diamond-type/'+vendor_diamond_id,
          type: 'DELETE',
          dataType: "JSON",
          data: {
              "id": vendor_diamond_id,
              /*"_method": 'DELETE',*/
              "_token": token,
          },
          success: function ()
          {
              swal({
                title: 'Deleted!',
                text: 'Selected Diamond Type has been deleted.',
                type: 'success',
                confirmButtonClass: 'btn btn-success',
                cancelButtonText: "Cancel",
              }).then((value) => {
                location.reload();
              });
          }
      });
     }
      
    });
  }
</script>
@endsection