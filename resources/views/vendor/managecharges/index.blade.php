@extends('layout.mainlayout')

@section('title', 'Managecharges')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('managecharges.index') }}
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
                      <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{'Manage Charges'}}</h5>
                      <div class="btn-top-right2">
                        <a href="{{ route('managecharges.create',['vendor_id'=>$vendor_id,'name'=>$name]) }}" class="btn btn-primary small-btn-style ripple right side"><i class="material-icons list-icon fs-24">playlist_add</i>Add Charges Details</a>
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
                       {!! Form::open(array('route' => 'managecharges.store','method'=>'POST')) !!}
                       <input type="hidden" id="username" name="name" value="{{$name}}">
                         <input type="hidden" id="user_id" name="vendor_id" value="{{$vendor_id}}">
                      <table class="managechargeslist table  table-striped table-responsive">
                          <thead>
                                <tr class="bg-primary">
                                  <th>No</th>
                                  <th>Name</th>
                                  <th>From</th>
                                  <th>To</th>
                                  <th>Type</th>
                                  <th>Labour Charge</th>
                                  <th>Product Type</th>
                                  <th>Diamond Type</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>

                              @foreach($vendor_charges as $vendors)
                               @if($vendor_id==$vendors->vendor_id)
                                <tr>
                                  <td>{{++$i }}</td>
                                  <td>{{$name}}</td>
                                  <td>{{$vendors->from_mm}}</td>
                                  <td>{{$vendors->to_mm}}</td>
                                  <td>@if($vendors->type == 1)
                                            Gold
                                      @elseif($vendors->type == 2)
                                            Silver
                                      @else
                                            Platinum(950)
                                      @endif
                                    </td>
                                  <td>{{$vendors->labour_charge}}</td>
                                  <td>{{$vendors->pname}}</td>
                                  <td>{{$vendors->name}}</td>
                                  <td>
                                  <a class="color-content table-action-style" href="{{ action('ManagechargesController@edit',[$vendors->id,'vendor_id'=>$vendor_id,'name'=>$name]) }}"><i class="material-icons md-18">edit</i></a>
                                  <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleteuser({{$vendors->id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>
                                </td>
                                </tr>
                                @endif
                                @endforeach
                              </tbody>
                              <tfoot>
                                <tr>
                                   <th>No</th>
                                   <th>Name</th>
                                  <th>From</th>
                                  <th>To</th>
                                  <th>Type</th>
                                  <th>Labour Charge</th>
                                  <th>Product Type</th>
                                  <th>Diamond Type</th>
                                   <th>Action</th>
                                </tr>
                              </tfoot>
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
   var managechargeslist = $('.managechargeslist').DataTable({

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
    "url": "{{action('ManagechargesController@managechargesresponse')}}",
    "data": function(data, callback){
      data._token = "{{ csrf_token() }}";
      data._id=$('#user_id').val();
       data._name=$('#username').val();

    },
  },

      "columnDefs": [ {
    "targets": [8],
    "orderable": false
    }
  ]
});

  function deleteuser(id, token){
    swal({
      title: 'Are you sure?',
      text: "You won't be able to recover this Product Type!",
      type: 'error',
      showCancelButton: true,
      confirmButtonClass: 'btn btn-danger',
      confirmButtonText: 'Yes, delete it!'
    }).then(function (data) {
      console.log(token);
      if (data.value) {
         $.ajax(
      {
          url: '<?=URL::to('/');?>'+'/managecharges/'+id,
          type: 'DELETE',
          dataType: "JSON",
          data: {
              "id": id,
              /*"_method": 'DELETE',*/
              "_token": token,
          },
          success: function ()
          {
              swal({
                title: 'Deleted!',
                text: 'Selected vendor_charges has been deleted.',
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


