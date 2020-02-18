@extends('layout.mainlayout')

@section('title', 'Users')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<?php
$user = Auth::user();
?>

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('users.index') }}
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
          <h5 class="border-b-light-1 w-100 pb-1 mt-0 mb-2">{{'Users Management'}}</h5>

          <div class="btn-top-right2">
            <a href="{{ route('users.create') }}" class="btn btn-primary small-btn-style ripple"><i class="material-icons list-icon fs-24">playlist_add</i> New User</a>
          </div>
          </div>
        </div>
      </div>
    </div>
      <div class="row">
          <div class="col-md-12 widget-holder">
              <div class="widget-bg">
                <div class="widget-body clearfix">
                    <h5 class="border-b-light-1 w-100 pb-2 mt-0 mb-4">{{'Filters'}}</h5>
                    <div class="row custom-drop-style custom-select-style label-text-pl-25">
                      <div class="col-md-3">
                          <div class="form-group">
                                <input type="text" class="form-control" name="textfilter" id="textfilter" value="" placeholder="Filter by Name/Email">
                          </div>
                      </div>
                      <?php
if ($user->hasRole('Super Admin') || $user->hasRole('User Manager')) {
	?>
                      <div class="col-md-3">
                          <div class="form-group">
                                <select class="text-uppercase" id="rolefilter" name="rolefilter">
                                    <option value="">Filter by Roles</option>
                                    @foreach($roles as $roleid => $role)
                                      <?php //var_dump($role);exit;?>
                                      <option value="<?=$role?>"><?=$role?></option>
                                    @endforeach
                                </select>
                          </div>
                      </div>
                      <div class="col-md-3">
                          <div class="form-group">
                                <select class="text-uppercase" id="adminfilter" name="adminfilter">
                                    <option value="">Filter by Admin</option>
                                    <option value="1">Admin</option>
                                    <option value="2">Non-Admin</option>
                                </select>
                          </div>
                      </div>
                      <?php
}
?>
                      <div class="col-md-3">
                        <button class="btn btn-primary" id="searchfilter" type="button">Search</button>
                        <button class="btn btn-default" id="searchreset" type="button">Reset</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
          <div class="col-md-12 widget-holder">
              <div class="widget-bg">
                  <!-- /.widget-heading -->
                  <div class="widget-body clearfix">

                      @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif
                      <table class="table table-striped table-center user-roles user-roles-mr word-break" id="userdatatable">
                          <thead>
                              <tr class="bg-primary">
                                  <th>No</th>
                                  <th>Name</th>
                                  <th>Email</th>
                                  <th>Roles</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($data as $key => $user)
                              <tr class="filter">
                                <td>{{ ++$i }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                  @if(!empty($user->getRoleNames()))
                                    @foreach($user->getRoleNames() as $v)
                                       <label class="badge badge-success">{{ $v }}</label>
                                    @endforeach
                                    @if($user->is_admin)<i class="list-icon material-icons" title="Admin of Dept" style="cursor: default;">verified_user</i>@endif
                                  @endif
                                </td>
                                <td>
                                  <a class="color-content table-action-style" href="{{ route('users.show',$user->id) }}" style="display: none;"><i class="material-icons md-18">show</i></a>
                                  <a class="color-content table-action-style" href="{{ route('users.edit',$user->id) }}"><i class="material-icons md-18">edit</i></a>
                                  <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleteuser({{$user->id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>
                                  <!--
                                  document.getElementById('logout-form').submit();
                                  -->
                                    {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $user->id],'style'=>'display:none']) !!}
                                        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                                    {!! Form::close() !!}
                                </td>
                              </tr>
                             @endforeach
                          </tbody>
                          <!-- <tfoot>
                              <tr>
                                  <th>No</th>
                                  <th>Name</th>
                                  <th>Email</th>
                                  <th>Roles</th>
                                  <th>Action</th>
                              </tr>
                          </tfoot> -->
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

<input type="hidden" id="userAjax" value="<?=URL::to('/users/ajaxlist');?>">

@endsection

@section('distinct_footer_script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>

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
                      data = data.replace(/(&nbsp;|<([^>]+)>)/ig, "");
                    }
                    return data;
                }
            }
        }
    };
  var table = $('#userdatatable').DataTable({
    "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B>><'row'<'col-md-12' <'user-roles-main' t>>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
  "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
  "buttons": [
    $.extend( true, {}, buttonCommon, {
      extend: 'csv',
      footer: false,
      title: 'User-List-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3],
          orthogonal: 'export'
      }
    }),
    $.extend( true, {}, buttonCommon, {
      extend: 'excel',
      footer: false,
      title: 'User-List-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3],
          orthogonal: 'export'
      }
    })
  ],
  "language": {
    "search": "",
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
    /*"sProcessing": "<div class='spinner-border' style='width: 3rem; height: 3rem;'' role='status'><span class='sr-only'>Loading...</span></div>"*/
  },
  "order": [[ 0, "desc" ]],
  "deferLoading": <?=$datacount?>,
  "processing": true,
  "serverSide": true,
  "searching": false,
  "serverMethod": "post",
  "ajax":{
    "url": $("#userAjax").val(),
    "data": function(data, callback){
      showLoader();
      data._token = "{{ csrf_token() }}";

      var textfilter = $('#textfilter').val();
      if(textfilter != ''){
        data.textfilter = textfilter;
      }

      var rolefilter = $('#rolefilter').children("option:selected").val();
      if(rolefilter != ''){
        data.rolefilter = rolefilter;
      }

      var adminfilter = $('#adminfilter').children("option:selected").val();
      if(adminfilter != ''){
        data.adminfilter = adminfilter;
      }

    },
    complete: function(response){
      hideLoader();
    }
  },
  "columnDefs": [
      { "orderable": false, "targets": [3,4] }
  ]
  //"scrollX": true
});

  $('#searchfilter').click(function(){
    table.draw();
  });

  $('#searchreset').click(function(){
    $('#textfilter').val('');
    $('#rolefilter option[value=""]').attr('selected','selected');
    $('#adminfilter option[value=""]').attr('selected','selected');
    $('#rolefilter').on('change', function() {
      if(this.value == ''){
        $('#rolefilter option[value=""]').attr('selected','selected');
      }else{
        $('#rolefilter option[value=""]').removeAttr('selected','selected');
      }
    });

    $('#adminfilter').on('change', function() {
      if(this.value == ''){
        $('#adminfilter option[value=""]').attr('selected','selected');
      }else{
        $('#adminfilter option[value=""]').removeAttr('selected','selected');
      }
    });

    table.draw();
  });



  function deleteuser(Id, token){
    swal({
      title: 'Are you sure?',
      text: "You won't be able to recover this user!",
      type: 'error',
      showCancelButton: true,
      confirmButtonClass: 'btn btn-danger',
      confirmButtonText: 'Yes, delete it!'
    }).then(function (data) {
      console.log(token);
      if (data.value) {
         //var table = $('#DataTables_Table_0').DataTable();
      $.ajax(
      {
          url: '<?=URL::to('/');?>'+'/users/'+Id,
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
                text: 'Selected user has been deleted.',
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