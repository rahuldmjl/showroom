@extends('layout.mainlayout')

@section('title', 'Roles')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('roles.index') }}
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
                      <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{'Roles Management'}}</h5>

                      <div class="btn-top-right2">
                        <a href="{{ route('roles.create') }}" class="btn btn-primary small-btn-style ripple"><i class="material-icons list-icon fs-24">playlist_add</i> New Role</a>
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

                      <table class="table table-striped table-center table-responsive" id="rolestable" >
                          <thead>
                              <tr class="bg-primary">
                                 <th>No</th>
                                 <th>Name</th> 
                                 <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($roles as $key => $role)
                                <tr>
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        @can('role-edit')
                                            <a class="color-content table-action-style" href="{{ route('roles.edit',$role->id) }}"><i class="material-icons md-18">edit</i></a>
                                        @endcan
                                        @can('role-delete')
                                            <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleterole({{$role->id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>

                                            {!! Form::open(['method' => 'DELETE','route' => ['roles.destroy', $role->id],'style'=>'display:none']) !!}
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

<div class="row" style="display: none;">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Role Management</h2>
        </div>
        <div class="pull-right">
        @can('role-create')
            <a class="btn btn-success" href="{{ route('roles.create') }}"> Create New Role</a>
            @endcan
        </div>
    </div>
</div>


<table class="table table-bordered" style="display: none;">
  <tr>
     <th>No</th>
     <th>Name</th>
     <th width="280px">Action</th>
  </tr>
    @foreach ($roles as $key => $role)
    <tr>
        <td>{{ ++$i }}</td>
        <td>{{ $role->name }}</td>
        <td>
            <a class="btn btn-info" href="{{ route('roles.show',$role->id) }}">Show</a>
            @can('role-edit')
                <a class="btn btn-primary" href="{{ route('roles.edit',$role->id) }}">Edit</a>
            @endcan
            @can('role-delete')
                {!! Form::open(['method' => 'DELETE','route' => ['roles.destroy', $role->id],'style'=>'display:inline']) !!}
                    {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                {!! Form::close() !!}
            @endcan
        </td>
    </tr>
    @endforeach
</table>





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
  function deleterole(Id, token){
    swal({
      title: 'Are you sure?',
      text: "You won't be able to recover this role!",
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
          url: '<?=URL::to('/');?>'+'/roles/'+Id,
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
                text: 'Selected role has been deleted.',
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
  var rolelist = $('#rolestable').DataTable({
  "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B><'col'f>><'row'<'col-md-12't>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
  "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
  "buttons": [
    {
      extend: 'csv',
      footer: false,
      title: 'Role-List-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1],
          orthogonal: 'export'
      }
    },
    {
      extend: 'excel',
      footer: false,
      title: 'Role-List-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1],
          orthogonal: 'export'
      }
    }
  ],
  "language": {
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
    //"sProcessing": "<div id='loader'></div>"
  },
  "deferLoading": <?=$totalcount?>,
  "processing": true,
  "serverSide": true,
  "order": [[ 0, "asc" ]],
  "orderClasses": true,
  "serverMethod": "get",
  "ajax":{
    "url": "{{action('RoleController@roleresponse')}}",
    "data": function(data, callback){
      data._token = "{{ csrf_token() }}";
      
    }
  },
  "columnDefs": [
      { "orderable": false, "targets": [2] }
  ]
  
});
</script>

@endsection