@extends('layout.mainlayout')

@section('title', 'Payment Header')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix"> 
    {{ Breadcrumbs::render('paymenttype.index') }}
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
            <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'Payment Header'}}</h5>
            <div class="btn-top-right2">
              <a href="{{ route('paymenttype.create') }}" class="btn btn-primary small-btn-style ripple"><i class="material-icons list-icon fs-24">playlist_add</i> Add Payment Header</a>
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
            <table class="paymenttypelist table table-striped table-center table-responsive" >
              <thead>
                <tr class="bg-primary">
                  <th>No</th>
                  <th>Name</th>
                  <th>Parent</th>
                  <th style="display: none;">Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($paymenttype as $key => $value)
                  <tr>
                    <td>{{ ++$i }}</td>
                    <td>{{ $value->name }}</td>
                    <td > 
                      @if($value->parent_id==0)
                        <label></label>
                      @else
                        <lable id="parentname" class="parentname" value="{{$value->parent_id}}">{{$value->parent->name}}</lable>
                      @endif
                    </td>
                    <td style="display: none;">{{$value->created_at}}</td>
                    <td>
                      <a class="color-content table-action-style" href="{{ route('paymenttype.show',$value->id) }}" style="display:none;"><i class="material-icons md-18">remove_red_eye</i></a>
                      <a class="color-content table-action-style" href="{{ route('paymenttype.edit',$value->id) }}"><i class="material-icons md-18">edit</i></a>
                      <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deletePaymentType({{$value->id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>
                      {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $value->id],'style'=>'display:none']) !!}
                      {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                      {!! Form::close() !!}
                    </td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr>
                  <th>No</th>
                  <th>Name</th>
                  <th>Parent</th>
                  <th style="display: none;">Created</th>
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
  
  function deletePaymentType(id, token){
    swal({
      title: 'Are you sure?',
      text: "You won't be able to recover this  Payment Header!",
      type: 'error',
      showCancelButton: true,
      confirmButtonClass: 'btn btn-danger',
      confirmButtonText: 'Yes, delete it!'
    }).then(function (data) {
      if (data.value) {
        $.ajax({ 
          url: '<?=URL::to('/');?>'+'/account/payment-types/'+id,
          type: 'DELETE',
          dataType: "JSON",
          data: {
            "id": id,
            /*"_method": 'DELETE',*/
            "_token": token,
          },success: function (data)
          {
            if (data.value) {  
              swal({
                title: data.title,
                text: data.msg, //'Selected Payment Type has been deleted.',
                type: data.type,
                confirmButtonClass: 'btn '+data.btntype, //btn-success',
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

  $(document).ready( function() {  
    var paymenttype = $('.paymenttypelist').DataTable({
      "order": [3,"desc"],
      "language": {
        "infoEmpty": "No matched records found",
        "zeroRecords": "No matched records found",
        "emptyTable": "No data available in table",
        "ordering" : true,
        //"sProcessing": "<div id='loader'></div>"
      },
      "deferLoading": <?=$count?>,
      "processing": true,
      "serverSide": true,
      "serverMethod": "GET",
      "ajax":{
        "url": "{{action('PaymentTypeController@paymentresponse')}}",
        "data": function(data, callback){
        data._token = "{{ csrf_token() }}";
        }
      },  
      "columnDefs": [ 
        {"targets": [0,2,4],"orderable": false},
        {"visible": false, "targets":3}
      ]
    });
  });
</script>
@endsection