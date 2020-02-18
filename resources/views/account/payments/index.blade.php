@extends('layout.mainlayout')

@section('title', 'Payment')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
  <!-- /.page-title-right -->
  {{ Breadcrumbs::render('accountpayment.index') }}
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
            <h5 class="border-b-light-1 w-100 pb-1 mt-0 mb-2">{{'Payments'}}</h5>
            <div class="btn-top-right2">
              <button id="multidel" name="delete-all" class="btn btn-danger small-btn-style">Delete</button>
              <a href="{{ route('accountpayment.create') }}" class="btn btn-primary small-btn-style ripple"><i class="material-icons list-icon fs-24">playlist_add</i>Create Payment</a>
            </div>
          </div>
          <!-- /.widget-heading -->
          <div class="widget-body clearfix">
            @if ($message = Session::get('success'))
              <div class="alert alert-icon alert-success border-success  alert-dismissible fade show" role="alert">
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
          </div>
          <div class="row m-0">
            <div class="col-12 p-0">
              <div class="tabs w-100">
                <ul class="nav nav-tabs Date">
                  <li class="nav-item p-0  active">
                    <a class="nav-link" id="nav-link" href="#{{$paymentType[0]}}" data-toggle="tab"  aria-expanded="false">Over Due</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="nav-link" href="#{{$paymentType[1]}}" data-toggle="tab"  aria-expanded="false">Current Due</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="nav-link" href="#{{$paymentType[2]}}" data-toggle="tab" aria-expanded="false">Future Due</a>
                  </li>
                </ul>
                <div class="tab-content p-3 border border-top-0 dataTable-length-top-0">
                  <div class="tab-pane table table-striped table-center table-head-box active" id="{{$paymentType[0]}}">
                    <table class="paymentslistTable0 table table-striped table-center table-head-box checkbox checkbox-primary scroll-lg table-responsive">
                      <thead>
                        <tr class="bg-primary">
                          <th>
                          <label><input class="form-check-input checkbox over_due overpayment"  type="checkbox" name="chckpaymentoverdue[]" id="check_alloverdue"><span class="label-text "></span></label>
                          </th>
                          <th>No</th>
                          <th>Customer Name</th>
                          <th>Invoice Number</th>
                          <th>Invoice Amount</th>
                          <th>Due Date</th>
                          <th>Payment Form</th>
                          <th>Payment Header</th>
                          <th>Date</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $i = 0; $duedate = "";?>
                        @foreach ($data['over_due'] as $key => $value)
                          <tr>
                            <td><label>
                            <input type="checkbox"  class="form-check-input checkbox overpayment" name="chckpaymentoverdue[]" id="chkPayment_{{$value->id}}" value="{{$value->id}}"><span class="label-text"></span></label></td>
                            <td>{{ ++$i }}</td>
                            <td>{{ $value->customer_name}}</td>
                            <td>{{ $value->invoice_number}}</td>
                            <td><?=CommonHelper::covertToCurrency($value->invoice_amount);?></td>
                            <td>
                                <?php if($value->due_date == '0000-00-00') {?>
                                  {{$duedate}}
                                <?php }else{?>
                                  {{ $value->due_date}}
                                <?php }?>
                            </td>
                            <td>{{ $value->payment_form}}</td>
                            <td>{{ $value->name}}</td>
                            <td>{{$value->created_at->format('Y-m-d')}}</td>
                            <td>
                              <a class="color-content table-action-style" href="{{ route('accountpayment.show',$value->id) }}" style="display:none;"><i class="material-icons md-18">remove_red_eye</i></a>
                              <a class="color-content table-action-style" href="{{ route('accountpayment.edit',$value->id) }}"><i class="material-icons md-18">edit</i></a>
                              <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deletePayment({{$value->id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>
                              <a class="color-content table-action-style" href="{{ route('accountpayment.pdflisting',['id'=>$value->id]) }}"><i class="material-icons md-18">file_download</i></a>

                              {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $value->id],'style'=>'display:none']) !!}
                              {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                              {!! Form::close() !!}
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr>
                          <th></th>
                          <th>No</th>
                          <th>Customer Name</th>
                          <th>Invoice Number</th>
                          <th>Invoice Amount</th>
                          <th>Due Date</th>
                          <th>Payment Form</th>
                          <th>Payment Header</th>
                          <th>Date</th>
                          <th>Action</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  <div class="tab-pane table table-striped table-center table-head-box " id="{{$paymentType[1]}}">
                    <table class="paymentslistTable1 table table-striped table-center table-head-box checkbox checkbox-primary table-responsive scroll-lg"  >
                      <thead>
                        <tr  class="bg-primary">
                        <th>
                        <label><input class="form-check-input checkbox pastpayment"  type="checkbox" name="chckpaymentpastdue[]" id="check_allpastdue"><span class="label-text"></span></label></th>
                        <th>No</th>
                        <th>Customer Name</th>
                        <th>Invoice Number</th>
                        <th>Invoice Amount</th>
                        <th>Due Date</th>
                        <th>Payment Form</th>
                        <th>Payment Header</th>
                        <th>Date</th>
                        <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $i = 0;$duedate="";?>
                        @foreach ($data['past_due'] as $key => $value)
                          <tr>
                            <td class="firstchk">
                              <label>
                                <input type="checkbox"  class="form-check-input checkbox pastpayment" name="chckpaymentpastdue[]" id="chkPayment_{{$value->id}}" value="{{$value->id}}"><span class="label-text"></span>
                              </label>
                            </td>
                            <td>{{ ++$i }}</td>
                            <td>{{ $value->customer_name}}</td>
                            <td>{{ $value->invoice_number}}</td>
                            <td><?=CommonHelper::covertToCurrency($value->invoice_amount);?></td>
                            <td>
                                <?php if($value->due_date == '0000-00-00') {?>
                                  {{$duedate}}
                                <?php }else{?>
                                  {{ $value->due_date}}
                                <?php }?>
                            </td>
                            <td>{{ $value->payment_form}}</td>
                            <td>{{ $value->name}}</td>
                            <td>{{$value->created_at->format('Y-m-d')}}</td>
                            <td>
                              <a class="color-content table-action-style" href="{{ route('accountpayment.show',$value->id) }}" style="display:none;"><i class="material-icons md-18">remove_red_eye</i></a>
                              <a class="color-content table-action-style" href="{{ route('accountpayment.edit',$value->id) }}"><i class="material-icons md-18">edit</i></a>
                              <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deletePayment({{$value->id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>
                              <a class="color-content table-action-style" href="{{ route('accountpayment.pdflisting',['id'=>$value->id]) }}"><i class="material-icons md-18">file_download</i></a>
                              {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $value->id],'style'=>'display:none']) !!}
                              {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                              {!! Form::close() !!}
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr>
                          <th></th>
                          <th>No</th>
                          <th>Customer Name</th>
                          <th>Invoice Number</th>
                          <th>Invoice Amount</th>
                          <th>Due Date</th>
                          <th>Payment Form</th>
                          <th>Payment Header</th>
                          <th>Date</th>
                          <th>Action</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  <div class="  tab-pane " id="{{$paymentType[2]}}">
                    <table class=" paymentslistTable2 table table-striped table-center table-head-box checkbox checkbox-primary scroll-lg"  >
                      <thead>
                        <tr class="bg-primary">
                          <th>
                            <label>
                              <input class="form-check-input checkbox futurepayment"  type="checkbox" name="chckpaymentfuturedue[]" id="check_allfuturedue">
                              <span class="label-text"></span>
                            </label>
                          </th>
                          <th>No</th>
                          <th>Customer Name</th>
                          <th>Invoice Number</th>
                          <th>Invoice Amount</th>
                          <th>Due Date</th>
                          <th>Payment Form</th>
                          <th>Payment Header</th>
                          <th>Date</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $i = 0;$duedate="";?>
                        @foreach ($data['future_due'] as $key => $value)
                          <tr>
                            <td>
                              <label>
                                <input type="checkbox"  class="form-check-input checkbox futurepayment" name="chckpaymentfuturedue[]" id="chkPayment_{{$value->id}}" value="{{$value->id}}"><span class="label-text"></span>
                              </label>
                            </td>
                            <td>{{ ++$i }}</td>
                            <td>{{ $value->customer_name}}</td>
                            <td>{{ $value->invoice_number}}</td>
                            <td ><?=CommonHelper::covertToCurrency($value->invoice_amount);?></td>
                            <td>
                              <?php if($value->due_date == '0000-00-00') {?>
                                {{$duedate}}
                              <?php }else{?>
                                {{ $value->due_date}}
                              <?php }?>
                            </td>
                            <td>{{ $value->payment_form}}</td>
                            <td>{{ $value->name}}</td>
                            <td>{{$value->created_at->format('Y-m-d')}}</td>
                            <td>
                              <a class="color-content table-action-style" href="{{ route('accountpayment.show',$value->id) }}" style="display:none;"><i class="material-icons md-18">remove_red_eye</i></a>
                              <a class="color-content table-action-style" href="{{ route('accountpayment.edit',$value->id) }}"><i class="material-icons md-18">edit</i></a>
                              <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deletePayment({{$value->id}}, '{{ csrf_token() }}');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>
                              <a class="color-content table-action-style" href="{{ route('accountpayment.pdflisting',['id'=>$value->id]) }}"><i class="material-icons md-18">file_download</i></a>
                              {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $value->id],'style'=>'display:none']) !!}
                              {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                              {!! Form::close() !!}
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr>
                          <th></th>
                          <th>No</th>
                          <th>Customer Name</th>
                          <th>Invoice Number</th>
                          <th>Invoice Amount</th>
                          <th>Due Date</th>
                          <th>Payment Form</th>
                          <th>Payment Header</th>
                          <th>Date</th>
                          <th>Action</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<input type="hidden" id="gettabval" value="{{$paymentType[0]}}" />
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
  var buttonCommon = {
    exportOptions: {
      format: {
          body: function ( data, row, column, node ) {
            if (column === 3) {
              data = data.replace('₹', '');
            }
            return data;
          }
      }
    }
  };
  var OverduePaymentsTable = $('.paymentslistTable0').DataTable({
    "dom":"<'row'<'col col-lg-3'l>><'row'<'col'B><'col'f>>"+"<'row'<'col-sm-12'tr>>"
    +"<'row'<'col-sm-5'i><'col-sm-7'p>>", 
    "order": [8,"desc"],
    "language": {
      "infoEmpty": "No matched records found",
      "zeroRecords": "No matched records found",
      "emptyTable": "No data available in table",
      //"sProcessing": "<div id='loader'></div>"
    },
      
    "buttons": [
      $.extend( true, {}, buttonCommon, {
        extend: 'csv',
        footer: false,
        title: 'Over_due_payments',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
            columns: [1,2,3,4,5,6,7,8],
            orthogonal: 'export'
        }
      }),
      $.extend( true, {}, buttonCommon, {
        extend: 'excel',
        footer: false,
        title: 'Over_due_payments',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
            columns: [1,2,3,4,5,6,7,8],
            orthogonal: 'export'
        }
      })
    ],
    "deferLoading": <?=$Overdue_Count?>,
    "processing": true,
    "serverSide": true,
      "pagingType": "simple_numbers",
      ordering: true,
    "serverMethod": "GET",
    "ajax":{
      "url": "{{route('accountpayment.paymentresponse')}}",
      "data": function(data, callback){
        data._token = "{{ csrf_token() }}";
        data._id="{{$paymentType[0]}}"
      },
    },
    "columnDefs": [ 
      {"targets": [0,1,6,7,8,9],"orderable": false}
    ]
  });
  
  var PastduePaymentsTable = $('.paymentslistTable1').DataTable({
    "dom":"<'row'<'col col-lg-3'l>><'row'<'col'B><'col'f>>"+"<'row'<'col-sm-12'tr>>"
    +"<'row'<'col-sm-5'i><'col-sm-7'p>>",    
   "order": [8,"desc"],
    "language": {
      "infoEmpty": "No matched records found",
      "zeroRecords": "No matched records found",
      "emptyTable": "No data available in table",
      //"sProcessing": "<div id='loader'></div>"
    },
    "buttons": [
      $.extend( true, {}, buttonCommon,{
        extend: 'csv',
        footer: false,
        title: 'Current_due_payments',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
            columns: [1,2,3,4,5,6,7,8],
            orthogonal: 'export'
        }
      }),
      $.extend( true, {}, buttonCommon, {
        extend: 'excel',
        footer: false,
        title: 'Current_due_payments',
        className: "btn btn-primary btn-sm px-3",
        exportOptions:{
            columns: [1,2,3,4,5,6,7,8],
            orthogonal: 'export'
        }
      })
    ],

    "deferLoading": <?=$Pastdue_Count?>,
    "processing": true,
    "serverSide": true,
    "pagingType": "simple_numbers",
    "serverMethod": "GET",
    "ajax":{
      "url": "{{route('accountpayment.paymentresponse')}}",
      "data": function(data, callback){
        data._token = "{{ csrf_token() }}";
        data._id="{{$paymentType[1]}}"
      },
    },
    "columnDefs": [ 
      {"targets": [0,1,6,7,8,9],"orderable": false}
    ]
  });
  
  var FutureduePaymentsTable = $('.paymentslistTable2').DataTable({
    "dom":"<'row'<'col col-lg-3'l>><'row'<'col'B><'col'f>>"+
    "<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>", 
    "order": [8,"desc"],
    "language": {
      "infoEmpty": "No matched records found",
      "zeroRecords": "No matched records found",
      "emptyTable": "No data available in table",
      //"sProcessing": "<div id='loader'></div>"
    },
    "buttons": [
      $.extend( true, {}, buttonCommon, {
        extend: 'csv',
        footer: false,
        title: 'Future_due_payments',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
          columns: [1,2,3,4,5,6,7,8],
          orthogonal: 'export'
        }
      }),
      $.extend( true, {}, buttonCommon, {
        extend: 'excel',
        footer: false,
        title: 'Future_due_payments',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
          columns: [1,2,3,4,5,6,7,8],
          orthogonal: 'export'
        }
      })
    ],
    "deferLoading": <?=$Futuredue_Count?>,
    "processing": true,
    "serverSide": true,
      "pagingType": "simple_numbers",
    "serverMethod": "GET",
    "ajax":{
      "url": "{{route('accountpayment.paymentresponse')}}",
      "data": function(data, callback){
        data._token = "{{ csrf_token() }}";
        data._id="{{$paymentType[2]}}"
      },
    },
    "columnDefs": [ 
      {"targets": [0,1,6,7,8,9],"orderable": false}
    ]
  });

  function deletePayment(id, token){
    swal({
      title: 'Are you sure?',
      text: "You won't be able to recover this Payment Detail!",
      type: 'error',
      showCancelButton: true,
      confirmButtonClass: 'btn btn-danger',
      confirmButtonText: 'Yes, delete it!'
      }).then(function (data) {
      if (data.value) {
        $.ajax({
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
              text: 'Selected payment information has been deleted.',
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
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  var url = window.location.href;
  var activeTabnew = url.substring(url.indexOf("#") + 1);
  $(document).ready( function(){
    $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
      localStorage.setItem('activeTab', $(e.target).attr('href'));

    });
    
    var activeTab = localStorage.getItem('activeTab');
    if (activeTabnew) {
      $('a[href="' + activeTabnew + '"]').tab('show');
    }

    if (activeTab) {
      $('.nav-tabs a').on('click',function(){ window.location.hash = $(this).attr('href'); });
      $('a[href="' + activeTab + '"]').tab('show');
    }

    if ({{$paymentType[0]}}) {
      jQuery(document).on("click","#check_alloverdue",function(){

        jQuery('.overpayment:checkbox').prop('checked', this.checked);
        //jQuery('input[name="chckpaymentoverdue[]"]:checkbox').prop('checked', this.checked);    
      });
    }
    if ({{$paymentType[1]}}) {
      jQuery(document).on("click","#check_allpastdue",function(){
          jQuery('.pastpayment:checkbox').prop('checked', this.checked);
        //jQuery('input[name="chckpaymentpastdue[]"]:checkbox').prop('checked', this.checked);    
      });
    }

    if ({{$paymentType[2]}}) {
      jQuery(document).on("click","#check_allfuturedue",function(){
          jQuery('.futurepayment:checkbox').prop('checked', this.checked);
        //jQuery('input[name="chckpaymentfuturedue[]"]:checkbox').prop('checked', this.checked);    
      });
    }
    $('#multidel').on('click', function(e) {
      var values = new Array();
      var hrefs = $("#nav-link.active").attr("href");
      var tabitem = hrefs.substring(1, hrefs.length);
      if(tabitem == "over_due"){
        jQuery.each(jQuery("input[name='chckpaymentoverdue[]']:checked"), function() {
          values.push(jQuery(this).val());
        });
        var ids = values.join(",");
      }else if(tabitem == "past_due"){
        jQuery.each(jQuery("input[name='chckpaymentpastdue[]']:checked"), function() {
          values.push(jQuery(this).val());
        });
        var ids = values.join(",");
      }else{
        jQuery.each(jQuery("input[name='chckpaymentfuturedue[]']:checked"), function() {
          values.push(jQuery(this).val());
        });
       var ids = values.join(",");
      }
      if(ids.length <=0 ){
        swal({
          title: 'Select Row!',
          text: "Please select atleast one record to delete.",
          type: 'warning',
          showCancelButton: true,
          confirmButtonClass: 'btn btn-warning',
          confirmButtonText: 'OK'
        });
      }else{
        swal({
          title: 'Are you sure?',
          text: "You won't be able to recover this user!",
          type: 'error',
          showCancelButton: true,
          confirmButtonClass: 'btn btn-danger',
          confirmButtonText: 'Yes, delete it!'
        }).then(function (data) {
          if (data.value) {
            $.ajax({
            url: "{{ action('PaymentController@multiple_delete') }}",
            type: 'GET',
            data: 'ids='+ids,
            success: function (response) {
              if (response['status']==true) {
                $(".checkbox:checked").each(function() {
                  swal({
                    title: 'Deleted!',
                    text: 'Selected Payment Information has been deleted.',
                    type: 'success',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonText: "Cancel",
                  }).then((value) => {
                    location.reload();
                  });
                });
              }else{
                alert('Whoops Something went wrong!!');
              }
            },
            error: function (data) {
              alert(data.responseText);
            }
          });
          }
          
        });
      }
    });
  });
</script>
@endsection