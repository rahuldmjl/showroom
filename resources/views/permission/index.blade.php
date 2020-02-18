@extends('layout.mainlayout')

@section('title', 'Permissions')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('permissions.index') }}
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
                      <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{'Permissions Management'}}</h5>

                      <div class="btn-top-right2">
                        <a href="{{ route('permissions.create') }}" class="btn btn-primary small-btn-style ripple"><i class="material-icons list-icon fs-24">playlist_add</i> New Permission</a>
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

                      <table class="table table-striped permission-table table-center" data-toggle="datatables">
                          <thead>
                              <tr class="bg-primary">
                                 <th>No</th>
                                 <th>Name</th>
                                 <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($permissions as $key => $permission)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $permission->name }}</td>
                                    <td>
                                        @can('permission-edit')
                                            <a class="color-content table-action-style" href="{{ route('permissions.edit',$permission->id) }}"><i class="material-icons md-18">edit</i></a>
                                        @endcan
                                        @can('permission-delete')
                                            <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleteperm({{$permission->id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>

                                            {!! Form::open(['method' => 'DELETE','route' => ['permissions.destroy', $permission->id],'style'=>'display:none']) !!}
                                                {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                                            {!! Form::close() !!}
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                          </tbody>
                          <tfoot>
                              <tr>
                                 <th>No</th>
                                 <th>Name</th>
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

<script type="text/javascript">
  function deleteperm(Id, token){
    swal({
      title: 'Are you sure?',
      text: "You won't be able to recover this permission!",
      type: 'error',
      showCancelButton: true,
      confirmButtonClass: 'btn btn-danger',
      confirmButtonText: 'Yes, delete it!'
    }).then(function (data) {
      console.log(token);
      if (data.value) {
         var table = $('#DataTables_Table_0').DataTable();
      $.ajax(
      {
          url: '<?=URL::to('/');?>'+'/permissions/'+Id,
          type: 'DELETE',
          dataType: "JSON",
          data: {
              "id": Id,
              /*"_method": 'DELETE',*/
              "_token": token,
          },
          success: function ()
          {
              swal({
                title: 'Deleted!',
                text: 'Selected permission has been deleted.',
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
 // $('.permission-table').wrap('<div class="permission-main"></div>');
</script>

@endsection