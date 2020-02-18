@extends('layout.mainlayout')

@section('title', 'Metal Rates')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('metalrates.index') }}
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
                      <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{'Metal Rates'}}</h5>

                      <div class="btn-top-right2">
                        <a href="{{ route('metalrates.create',['vendor_id'=>$vendor_id,'name'=>$name]) }}" class="btn btn-primary small-btn-style ripple"><i class="material-icons list-icon fs-24">playlist_add</i>Add Metal Rates</a>
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
                      <input type="hidden" id="user_id" name="vendor_id" value="{{$vendor_id}}">
                       <input type="hidden" id="name" name="name" value="{{$name}}">

                      <table class="metalrateslist table table-striped" >
                      	 <thead>
                              <tr class="bg-primary">
                                  <th>No</th>
                                   <th>Metal Quality</th>
                                  <th>Metal Type</th>
                                  <th>Gold Rate(%)</th>
                                  <th>Metal Price</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>


                              		@foreach($metalrates as $metal)
                                   @if($vendor_id==$metal->vendor_id)
                              		<tr>
                              			<td>{{++$i}}</td>
                              			<td>{{$metal->metal_quality}}</td>
                              			<td>{{$metal->mname}}</td>
                              			<td>{{$metal->gold_rate}}</td>
                              			<td>{{$metal->rate}}</td>
                              			<td>  <a class="color-content table-action-style" href="{{ route('metalrates.edit',[$metal->metalrates_id,'vendor_id'=>$vendor_id,'name'=>$name]) }}"><i class="material-icons md-18">edit</i></a>
                                    <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleteuser({{$metal->metalrates_id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>
                                    </td>
                              		</tr>
                                   @endif
                              		@endforeach


                              	 </tbody>
                                 <tfoot>
                                   <tr>
                                      <th>No</th>
                                   <th>Metal Quality</th>
                                  <th>Metal Type</th>
                                  <th>Gold Rate(%)</th>
                                  <th>Metal Price</th>

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
  var metalrateslist = $('.metalrateslist').DataTable({

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
    "url": "{{action('MetalratesController@metalresponse')}}",
    "data": function(data, callback){
      data._token = "{{ csrf_token() }}";
      data._id=$('#user_id').val();

    },
  },

      "columnDefs": [ {
    "targets": [5],
    "orderable": false
    }
  ]
});
  $('.metalrateslist').wrap('<div class="metalrateslist-main"></div>');

  function deleteuser(metalrates_id, token){
    swal({
      title: 'Are you sure?',
      text: "You won't be able to recover this metal rates!",
      type: 'error',
      showCancelButton: true,
      confirmButtonClass: 'btn btn-danger',
      confirmButtonText: 'Yes, delete it!'
    }).then(function (data) {
      console.log(token);

      if (data.value) {
        $.ajax(
      {
          url: '<?=URL::to('/');?>'+'/metalrates/'+metalrates_id,
          type: 'DELETE',
          dataType: "JSON",
          data: {
              "id": metalrates_id,
              /*"_method": 'DELETE',*/
              "_token": token,
          },
          success: function ()
          {
              swal({
                title: 'Deleted!',
                text: 'Selected Record has been deleted.',
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
