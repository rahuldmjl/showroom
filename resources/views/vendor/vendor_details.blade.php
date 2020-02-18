@extends('layout.mainlayout')

@section('title', 'Vendors')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('vendor.vendor_details') }}
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
                      <h5>{{'Vendors'}}</h5>

                     <div class="pull-right">
                       
                        <a href="{{ route('vendor.create') }}" class="btn btn-primary ripple"><i class="material-icons list-icon fs-24">playlist_add</i>Add Vendor</a>

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

                        @if (session('errors'))
                          <div class="alert alert-icon alert-danger border-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                             <i class="material-icons">highlight_off</i>
                               <strong>error</strong>: {{ session('errors') }}
                          </div>
                        @endif
                       {!! Form::open(array('route' => 'vendor.store','method'=>'POST')) !!}
                       <table class="vendorlist table table-striped table-responsive " >
                          <thead>
                              <tr>
                                  <th>No</th>
                                  <th>Vendor Name</th>
                                  <th>Vendor Email</th>
                                  <th>Vendor DMCode</th>
                                  
                                  <th>Action</th>

                              </tr>
                          </thead>
                          <tbody>
                            @foreach($vendor as $vendors)
                            <tr>
                                
                                  <td>
                                      {{$vendors->id}}
                                    </td>
                        <td >
                          {{$vendors->name}}
                        </td>
                        <td >
                          {{$vendors->email}}
                        </td>
                        <td >
                          {{$vendors->vendor_dmcode}}
                        </td>
                        <td >
                           <a class="color-content table-action-style" href="{{ action('VendorController@show',$vendors->id) }}"><i class="material-icons md-18">visibility</i></a>
                              @if($vendors->created_by == Auth::user()->id)
                                  <a class="color-content table-action-style" href="{{ route('vendor.edit',$vendors->id) }}"><i class="material-icons md-18">edit</i></a>
                                  <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleteuser({{$vendors->id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>
                              @endif
                        </td>
                      </tr>
                    @endforeach
                          </tbody>
                          <tfoot>
                              <tr>
                                  <th>No</th>
                                  <th>Vendor Name</th>
                                  <th>Vendor Email</th>
                                  <th>Vendor DM CODE</th>
                                  
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
            <h2>Users Management</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-success" href="{{ route('users.create') }}"> Create New User</a>
        </div>
    </div>
</div>

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
   
  var vendorlist = $('.vendorlist').DataTable({

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
    "url": "{{action('VendorController@vendor_detailresponse')}}",
    "data": function(data, callback){
      data._token = "{{ csrf_token() }}";
      
    },
  }  
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
     if (data.value) {
      $.ajax(
      {
          url: '<?=URL::to('/');?>'+'/vendor/'+Id,
          type: 'DELETE',
          dataType: "JSON",
          data: {
              "id": Id,
              /*"_method": 'DELETE',*/
              "_token": token,
          },
          success: function (response)
          {

            console.log(response);
            if(response == 'success')
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
            }else{
                swal({
                title: 'Sorry!',
                text: 'Cannot Delete vendor permission denied',
                type: 'warning',
                confirmButtonClass: 'btn btn-warning',
                cancelButtonText: "Cancel",
              }).then((value) => {
                location.reload();
              });
            }
             
          }

      });
     }
      
    });
  }
</script>
@endsection
