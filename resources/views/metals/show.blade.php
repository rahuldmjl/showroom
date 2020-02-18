@extends('layout.mainlayout')

@section('title', 'Gold Inventory')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/css/ion.rangeSlider.min.css"/>

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('metals.transactions', $id) }}
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
                      <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{'Gold Inventory'}}</h5>
                      <div class="btn-top-right2">
                        <a href="{{ route('metals.create') }}" class="btn btn-primary small-btn-style ripple"><i class="material-icons list-icon fs-24">playlist_add</i> Add Gold</a>
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
                      <div class="row">
                      <div class="col-md-12 widget-holder">
                        <h5 class="mt-0">{{'Filters'}}</h5>
                          <div class="row custom-drop-style custom-select-style label-text-pl-25 filter-equal-h">
                            <div class="col-md-3">
                              <div class="form-group">
                                <select class="form-control" name="textfilter" id="textfilter">
                                  <option value="">Select Transaction Type</option>

                                   @foreach($trantype as $type)
                                      <option value="{{$type->id}}">{{$type->name}}</option>
                                    @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                 <select class="form-control" id="textfilterid" name="quality_search">
                                  <option value="" >Select Transaction By</option>

                                    @foreach($name as $value)
                                      @foreach($value as $name)
                                      <option value="{{$name->id}}">{{$name->name}}</option>
                                      @endforeach
                                    @endforeach

                                </select>
                              </div>
                            </div>
                             <div class="col-md-3">
                                <div class="form-group price-filter">
                                    <div class='dropdown' id='pricerange'>
                                      <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Metal Weight Range<span class='caret'></span></button>
                                      <ul class='dropdown-menu'>
                                        <li>
                                          <div class="form-group px-2">
                                          <input type="text" id="weightStart" data_price_init_start="1100" data_start_custom="1100" class="form-control" value="{{$weightmin}}"/>
                                          <input type="text" id="weightEnd" data_price_init_to="100000" data_to_custom="100000" class="form-control" value="{{$weightmax}}"/>
                                          </div>
                                          <div class="form-group px-3">
                                          <input type="text" id="weightFilter" name="priceFilter" value="" />
                                          </div>
                                        </li>
                                      </ul>
                                    </div>

                                   </div>
                              </div>
                           <div class="col-md-3">
                                <div class="form-group price-filter">
                                    <div class='dropdown' id='pricerange'>
                                      <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Amount Paid Range<span class='caret'></span></button>
                                      <ul class='dropdown-menu'>
                                        <li>
                                          <div class="form-group px-2">
                                          <input type="text" id="amtStart" data_price_init_start="{{$amount_paidmin}}" data_start_custom="{{$amount_paidmin}}" class="form-control" value="{{$amount_paidmin}}"/>
                                          <input type="text" id="amtEnd" data_price_init_to="{{$amount_paidmax}}" data_to_custom="{{$amount_paidmax}}" class="form-control" value="{{$amount_paidmax}}"/>
                                          </div>
                                          <div class="form-group px-3">
                                          <input type="text" id="amountrange" name="amountrange" value="" />
                                          </div>
                                        </li>
                                      </ul>
                                    </div>
                                   </div>
                              </div>
                            <div class="col-md-3">
                              <button class="btn btn-primary" id="searchfilter" type="button">Search</button>
                              <button class="btn btn-default" id="searchreset"  type="button">Reset</button>
                            </div>
                          </div>
                    </div>
                  </div>
                      <table class="table table-striped  table-center table-responsive mt-0 scroll-lg" id="gold_inventory_table">
                          <thead>
                              <tr class="bg-primary">
                                 <!-- <th>No</th> -->
                                 <th>Type</th>
                                 <th>Metal Weight</th>
                                 <th>Amount Paid</th>
                                 <th>Transaction Type</th>
                                 <th>Transaction By</th>
                                 <th>Transaction At</th>
                                 <th>PO Number</th>
                                 <th>Comment</th>
                                 <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($metalTransactions as $key => $metalTransaction)
                                <tr>
                                    <!-- <td>@if($metalTransaction->metal_type == '1') Gold 24K @else Platinum 950 @endif</td> -->
                                    <td>{{ $metalTransaction->gold_type }} </td>
                                    <td>{{ $metalTransaction->metal_weight }} {{ strtoupper($metalTransaction->measurement) }}</td>
                                    <td><?=CommonHelper::covertToCurrency($metalTransaction->amount_paid);?></td>
                                    <?php
$transType = $metalTransaction::find($metalTransaction->id)->transaction_type_value->name;

$transby = $metalTransaction::find($metalTransaction->id)->transaction_by;
if (!$transby) {
	$transby = "-";
} else {
	$transby = $metalTransaction::find($metalTransaction->id)->transaction_by->name;
}

if ($metalTransaction->transaction_type == '1') {
	$badge = 'badge-success';
} elseif ($metalTransaction->transaction_type == '2' || $metalTransaction->transaction_type == '3' || $metalTransaction->transaction_type == '4' || $metalTransaction->transaction_type == '5') {
	$badge = 'badge-danger';
} elseif ($metalTransaction->transaction_type == '6') {
	$badge = 'badge-warning';
} else {
	$badge = 'badge-info';
}
?>
                                    <td><span class="badge {{ $badge }} py-1 px-2">{{ $transType }}</span></td>
                                    <td>{{ $transby }}</td>
                                    <td>{{ $metalTransaction->transaction_at }}</td>
                                    <td>{{ $metalTransaction->po_number }}</td>
                                    <td>{{ $metalTransaction->comment }}</td>
                                    <td>
                                 <?php if ($metalTransaction->transaction_type == 1) {?>
                                        <a class="color-content table-action-style" title="Download Invoice" href="{{ route('gold_download_purchase_invoice',$metalTransaction->id) }}"><i class="material-icons md-18">file_download</i></a>
                                        <a class="color-content table-action-style" title="Edit" href="{{ route('gold_edit_transaction',$metalTransaction->id) }}"><i class="material-icons md-18">edit</i></a>
                                       <?php } else if ($metalTransaction->transaction_type == 2) {?>
                                        <a class="color-content table-action-style" title="Download Issue Vocher" href="{{ route('gold_download_purchase_invoice',$metalTransaction->id) }}"><i class="material-icons md-18">file_download</i></a>
                                       <?php }?>
                                    </td>
                                </tr>

                                @endforeach
                          </tbody>
                          <!-- <tfoot>
                              <tr>
                                 <th>No</th>
                                 <th>Metal Type</th>
                                 <th>Metal Weight</th>
                                 <th>Amount Paid</th>
                                 <th>Transaction Type</th>
                                 <th>Transaction By</th>
                                 <th>Transaction At</th>
                                 <th>PO Number</th>
                                 <th>Comment</th>
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
<input type="hidden" id ="MType" value="{{$id}}">


@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/js/ion.rangeSlider.min.js"></script>
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


    var buttonCommon = {
        exportOptions: {
            format: {
                body: function ( data, row, column, node ) {
                    if (column === 2) {
                      data = data.replace('₹ ', '');
                    }
                    if (column === 1) {
                      data = data.replace(' GM', '');
                    }
                   

                    arr = [data];
                    for (i = 0; i < arr.length; i++) {
                      if (column === 6) {
                        if(arr[i] == null){
                          data = "";
                        }
                      }
                    }

                    if (column === 3) {
                      data = data.replace(/(&nbsp;|<([^>]+)>)/ig, "");
                    }
                    return data;
                }
            }
        }
    };
  var table = $('#gold_inventory_table').DataTable({
    "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B>><'row'<'col-md-12't>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
  "language": {
    "search": "",
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
  },

 "buttons": [
    $.extend( true, {}, buttonCommon, {
      extend: 'csv',
      footer: false,
      title: 'Metal-Transaction-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3,4,5,6,7],
          orthogonal: 'export'
      }
    }),
    $.extend( true, {}, buttonCommon, {
      extend: 'excel',
      footer: false,
      title: 'Metal-Transaction-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3,4,5,6,7],
          orthogonal: 'export'
      }
    })
  ],
  "order" : [3, 'desc'],
  "columnDefs": [
                { "orderable": false, "targets": [0,8] }
            ],
  "deferLoading": <?=$datacount?>,
  "processing": true,
  "serverSide": true,
  "searching": false,
  "lengthChange": true,
  "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
  "serverMethod": "GET",
  "ajax":{
    "url": "{{action('MetalController@filter_metal')}}",
    "data": function(data, callback){
      showLoader();
      data._token = "{{ csrf_token() }}";

      var textfilter = $('#textfilter  option:selected').val();
       if(textfilter != ''){
        data.textfilter = textfilter;
      }

      var MtypeID = $('#MType').val();
       if(MtypeID != ''){
        data.MtypeID = MtypeID;
      }

      var textfilterid = $('#textfilterid option:selected').val();
        if(textfilterid != '')
        {
          data.textfilterid = textfilterid;
        }
        //alert(textfilterquality);
        var  weightFilter=   $('#weightFilter').val();

        if(weightFilter != ''){
          data.weightStart =$('#weightStart').val();
          data.weightEnd= $('#weightEnd').val();

        }

         var  amountrange=   $('#amountrange').val();
       if(weightFilter != ''){
          data.amtStart =$('#amtStart').val();
          data.amtEnd= $('#amtEnd').val();

        }
    },
    complete: function(response){
      hideLoader();
    }
  },

});



    $('#searchfilter').click(function(){
      table.draw();
    });

    $('#searchreset').click(function(){
      $('#textfilter').val("");
      $('#textfilterid').val("");
      $('#weightFilter').val("");
      $('#amountrange').val("");
      table.draw();
    });
 /* $('#gold_inventory_table').DataTable({
        "scrollX": true
  });*/

  var priceSlider = $("#weightFilter").ionRangeSlider({
      type: "double",
      skin: "round",
      grid: false,
      keyboard: true,
      force_edges: false,
      prettify_enabled: true,
      prettify_separator: ',',
      min: <?php echo $weightmin; ?>,
      max: <?php echo $weightmax; ?>,
      from:<?php echo $weightmin; ?>,
      to: <?php echo $weightmax; ?>,
      onChange: function (data) {
          $("#weightStart").val(data.from);
          $("#weightEnd").val(data.to);
      },
      onFinish: function (data) {
          $("#weightStart").val(data.from);
          $("#weightEnd").val(data.to);
      },
});
var pricerangeslider = $("#priceFilter").data("ionRangeSlider");

var priceSlider = $("#amountrange").ionRangeSlider({
      type: "double",
      skin: "round",
      grid: false,
      keyboard: true,
      force_edges: false,
      prettify_enabled: true,
      prettify_separator: ',',
      min: <?php echo $amount_paidmin; ?>,
      max: <?php echo $amount_paidmax; ?>,
      from:<?php echo $amount_paidmin; ?>,
      to: <?php echo $amount_paidmax; ?>,
      onChange: function (data) {
          $("#amtStart").val(data.from);
          $("#amtEnd").val(data.to);
          //showroomProductsTable.draw();
      },
      onFinish: function (data) {
          $("#amtStart").val(data.from);
          $("#amtEnd").val(data.to);
          //showroomProductsTable.draw();
      },
});
var pricerangeslider = $("#amountrange").data("ionRangeSlider");
</script>

@endsection