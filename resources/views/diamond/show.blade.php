@extends('layout.mainlayout')

@section('title', 'Diamond Inventory')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/css/ion.rangeSlider.min.css"/>
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('diamond.transactions', $id) }}
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
                      <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{'Diamond Inventory'}}</h5>

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
                                      <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Diamond Weight<span class='caret'></span></button>
                                      <ul class='dropdown-menu'>
                                        <li>
                                          <div class="form-group px-2">
                                          <input type="text" id="weightStart" data_weight_init_start="{{$weightmin}}" data_start_custom="{{$weightmin}}" class="form-control" value="{{$weightmin}}"/>
                                          <input type="text" id="weightEnd" data_weight_init_to="{{$weightmax}}" data_to_custom="{{$weightmax}}" class="form-control" value="{{$weightmax}}"/>
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
                                      <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Amount Paid <span class='caret'></span></button>
                                      <ul class='dropdown-menu'>
                                        <li>
                                          <div class="form-group px-2">
                                          <input type="text" id="amtStart" data-start="{{$amount_paidmin}}" data_amt_init_start="{{$amount_paidmin}}" data_start_custom="{{$amount_paidmin}}" class="form-control" value="{{$amount_paidmin}}"/>
                                          <input type="text" id="amtEnd" data-end="{{$amount_paidmax}}" data_amt_init_to="{{$amount_paidmax}}" data_to_custom="{{$amount_paidmax}}" class="form-control" value="{{$amount_paidmax}}"/>
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
                                <div class="form-group price-filter">
                                    <div class='dropdown' id='pricerange'>
                                      <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Rate Select <span class='caret'></span></button>
                                      <ul class='dropdown-menu'>
                                        <li>
                                          <div class="form-group px-2">
                                          <input type="text" id="rateStart" data-start-rate={{$ratemin}} data_rate_init_start="{{$ratemin}}" data_start_custom="{{$ratemin}}" class="form-control" value="{{$ratemin}}"/>
                                          <input type="text" id="rateEnd" data-end-rate={{$ratemax}} data_rate_init_to="{{$ratemax}}" data_to_custom="{{$ratemax}}" class="form-control" value="{{$ratemax}}"/>
                                          </div>
                                          <div class="form-group px-3">
                                          <input type="text" id="raterange" name="raterange" value="" />
                                          </div>
                                        </li>
                                      </ul>
                                    </div>
                                   </div>
                              </div>
                            <div class="col-md-3">
                              <button class="btn btn-primary" id="searchfilter" type="button">Search</button>
                              <button class="btn btn-default" id="searchreset"  type="button" value="">Reset</button>
                            </div>
                          </div>
                    </div>
                  </div>
                      <table class="table table-striped table-responsive mt-0 diamond_inventory_table scroll-lg" id="diamond_inventory_table">

                          <thead>
                              <tr class="bg-primary">
                                 <th>No</th>
                                 <th>Stone Shape</th>
                                 <th>Stone Quality</th>
                                 <th>Diamond Weight</th>
                                 <th>Amount Paid With Gst</th>
                                 <th>Amount Paid</th>
                                 <th>Rate</th>
                                 <th>MM Size</th>
                                 <th>Sieve Size</th>
                                 <th>Transaction Type</th>
                                 <th>Transaction By</th>
                                 <th>Transaction At</th>
                                 <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                            
                          @foreach ($diamondTransactions as $key => $diamondTransaction)
                          <tr>
                            <td>{{ ++$i }}</td>
                            <td>{{ $diamondTransaction->stone_shape }} </td>
                            <td>{{ $diamondTransaction->diamond_quality }} </td>
                            <td>{{ $diamondTransaction->diamond_weight }} </td>
                            <td><?=CommonHelper::covertToCurrency( $diamondTransaction->amount_paid_with_gst );?></td>
                            <td><?=CommonHelper::covertToCurrency( $diamondTransaction->amount_paid );?></td>
                            <td><?=CommonHelper::covertToCurrency( $diamondTransaction->rate);?></td>
                            <td>{{ $diamondTransaction->mm_size }}</td>
                            <td>{{ $diamondTransaction->sieve_size }}</td>
                            <?php
                            //echo "hi".$diamondTransaction->id;
                            $transType = $diamondTransaction::find($diamondTransaction->id)->transaction_type_value->name;
                            //echo $transType; exit;
                            $transby = $diamondTransaction::find($diamondTransaction->id)->transaction_by->name;
                            if ($diamondTransaction->transaction_type == '1') {
                              $badge = 'badge-success';
                            } elseif ($diamondTransaction->transaction_type == '2' || $diamondTransaction->transaction_type == '3' || $diamondTransaction->transaction_type == '4' || $diamondTransaction->transaction_type == '5') {
                              $badge = 'badge-danger';
                            } elseif ($diamondTransaction->transaction_type == '6') {
                              $badge = 'badge-warning';
                            } else {
                              $badge = 'badge-info';
                            }
                          ?>
                          <td><span class="badge {{ $badge }} py-1 px-2">{{ $transType }}</span></td>
                          <td>{{ $transby }}</td>
                          <td>{{ $diamondTransaction->transaction_at }}</td>
                          <td>
                            <?php if($diamondTransaction->transaction_type == 1)  { ?>
                                <a class="color-content table-action-style" title="Download Invoice" href="{{ route('diamond_download_purchase_invoice',$diamondTransaction->id) }}"><i class="material-icons md-18">file_download</i></a>
                                <a class="color-content table-action-style" title="Edit" href="{{ route('diamond.edit',$diamondTransaction->id) }}"><i class="material-icons md-18">edit</i></a>
                            <?php } 
                             if($diamondTransaction->transaction_type == 2)  { ?>
                                <a class="color-content table-action-style" title="IssueVaucher" href="{{ route('diamond.diamond_issue_vaucher',['id'=>$diamondTransaction->id]) }}"><i class="material-icons md-18">file_download</i></a>
                            <?php } 
                             if($diamondTransaction->transaction_type == 6)  { ?>
                                <a class="color-content table-action-style" title="Generate Invoice" href="{{ route('diamond.diamond_invoice',['id'=>$diamondTransaction->id]) }}"><i class="material-icons md-18">file_download</i></a>
                            <?php } ?>

                          </td>
                        </tr>
                          @endforeach
                          </tbody>
                          <tfoot>
                            <tr>
                                <th>No</th>
                                <th>Stone Shape</th>
                                <th>Stone Quality</th>
                                <th>Diamond Weight</th>
                                <th>Amount Paid With Gst</th>
                                <th>Amount Paid</th>
                                <th>Rate</th>
                                <th>MM Size</th>
                                <th>Sieve Size</th>
                                <th>Transaction Type</th>
                                <th>Transaction By</th>
                                <th>Transaction At</th>
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
                  if (column === 4) {
                    data = data.replace('₹ ', '');
                  }

                  if (column === 5) {
                    data = data.replace('₹ ', '');
                  }
                  if (column === 6) {
                    data = data.replace('₹ ', '');
                  }

                  if (column === 9) {
                      data = data.replace(/(&nbsp;|<([^>]+)>)/ig, "");
                  }
                  return data;
                }
            }
        }
    };
   var table = $('#diamond_inventory_table').DataTable({
    "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B><'col'f>><'row'<'col-md-12't>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
  "language": {
    "search": "",
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
    /*"sProcessing": "<div class='spinner-border' style='width: 3rem; height: 3rem;'' role='status'><span class='sr-only'>Loading...</span></div>"*/
  },
  "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
  "deferLoading": <?=$datacount?>,
  "processing": true,
  "serverSide": true,
  "searching": false,
  "buttons": [
    $.extend( true, {}, buttonCommon, {
      extend: 'csv',
      footer: false,
      title: 'Diamond-Inventory-Individual-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3,4,5,6,7,8,9,10,11],
          orthogonal: 'export'
      }
    }),
    $.extend( true, {}, buttonCommon, {
      extend: 'excel',
      footer: false,
      title: 'Diamond-Inventory-Individual-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3,4,5,6,7,8,9,10,11],
          orthogonal: 'export'
      }
    })
  ],
  "serverMethod": "GET",
  "ajax":{
    "url": "{{action('DiamondController@filter_diamond')}}",
    "data": function(data, callback){
      showLoader();
      data._token = "{{ csrf_token() }}";

      var textfilter = $('#textfilter  option:selected').val();
       if(textfilter != ''){
        data.textfilter = textfilter;
        data.invetory_id =<?= $inventory_id?>;
       /* data.pagerequest = 1;*/
      }
  
      var textfilterid = $('#textfilterid option:selected').val();
        if(textfilterid != '')
        {
          data.textfilterid = textfilterid;
           data.invetory_id =<?= $inventory_id?>;
          /* data.pagerequest = 1;*/
        }
        //alert(textfilterquality);
        var  weightFilter=   $('#weightFilter').val();
        
        if(weightFilter != ''){
          data.weightStart =$('#weightStart').val();
          data.weightEnd= $('#weightEnd').val();
           data.invetory_id =<?= $inventory_id?>;
          /* data.pagerequest = 1;*/
        
        }

         var  amountrange=   $('#amountrange').val();
       if(weightFilter != ''){
          // data.amtStart = $('#amtStart').val();
          // data.amtEnd= $('#amtEnd').val();
           data.amtStart = $("#amtStart").val();
           data.amtEnd= $("#amtEnd").val();
         
            data.invetory_id =<?= $inventory_id?>;
           /*data.pagerequest = 1;*/
        }

      var  raterange=   $('#raterange').val();
       if(raterange != ''){
          data.rateStart = $("#rateStart").val(); //$("#rateStart").attr('data-start-rate'); //$('#rateStart').val();
          data.rateEnd= $("#rateEnd").val(); //$('#rateEnd').val();
         
           data.invetory_id =<?= $inventory_id?>;
         /* data.pagerequest = 1;*/
        }

        var reset = $('#searchreset').attr('data-reset');
        if (reset != '') {
           data.reset=   $('#searchreset').attr('data-reset');
           data.invetory_id =<?= $inventory_id?>;
        }




    },
    complete: function(response){
      hideLoader();
    }
  },"columnDefs": [ {
    "targets": [0,9,12],
    "orderable": false
    }
  ]   
});

    $('#searchfilter').click(function(){
      table.draw();
       $('#searchreset').attr("data-reset", "");
    });

    $('#searchreset').click(function(){
      var intStart = $("#weightStart").attr('data_weight_init_start');
      var intEnd = $("#weightEnd").attr('data_weight_init_to');
      var Start = $("#amtStart").attr('data_amt_init_start');
      var End = $("#amtEnd").attr('data_amt_init_to');
      var ratestart = $("#rateStart").attr('data_rate_init_start');
      var rateend = $("#rateEnd").attr('data_rate_init_to');

      $('#textfilter').val("");
      $('#textfilterid').val("");
      $('#weightFilter').val("");
      $('#amountrange').val("");
      $('#raterange').val("");
      $('#weightStart').val(intStart);
      $('#weightEnd').val(intEnd);
      $("#amtStart").val(Start);
      $("#amtEnd").val(End);
      $("#rateStart").val(ratestart);
      $("#rateEnd").val(rateend);
      $('#searchreset').attr("data-reset","reset");
      $("#rateStart").attr('data-start-rate',ratestart);
      $("#rateEnd").attr('data-end-rate',rateend);
       $("#amtStart").attr('data-start',Start);
          $("#amtEnd").attr('data-end',End);
      //To update price slider values
      weightrangeslider.update({
          from: intStart,
          to: intEnd
      });

     amtrangeslider.update({
          from: Start,
          to: End
      });

     raterangeslider.update({
          from: ratestart,
      });

      table.draw();
    });
 /* $('#gold_inventory_table').DataTable({
        "scrollX": true
  });*/

  var weightSlider = $("#weightFilter").ionRangeSlider({
      type: "double",
      skin: "round",
      grid: false,
      keyboard: true,
      force_edges: false,
      prettify_enabled: true,
      prettify_separator: ',',
      min: <?php echo $weightmin;?>,
      max: <?php echo $weightmax;?>,
      from:<?php echo $weightmin;?>,
      to: <?php echo $weightmax;?>,
      onChange: function (data) {
          $("#weightStart").val(data.from);
          $("#weightEnd").val(data.to);
          //showroomProductsTable.draw();
      },
      onFinish: function (data) {
          $("#weightStart").val(data.from);
          $("#weightEnd").val(data.to);
          //showroomProductsTable.draw();
      },
});
var weightrangeslider = $("#weightFilter").data("ionRangeSlider");

var amtSlider = $("#amountrange").ionRangeSlider({
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
        
          //$("#amtStart").val(data.from);
          //$("#amtEnd").val(data.to);
          $("#amtStart").attr('data-start',data.from);
          $("#amtEnd").attr('data-end',data.to);
          
      },
      onFinish: function (data) {
          $("#amtStart").val(data.from);
          $("#amtEnd").val(data.to);
          //showroomProductsTable.draw();
      },
});
var amtrangeslider = $("#amountrange").data("ionRangeSlider");

var rateSlider = $("#raterange").ionRangeSlider({
      type: "double",
      skin: "round",
      grid: false,
      keyboard: true,
      force_edges: false,
      prettify_enabled: true,
      prettify_separator: ',',
      min: <?php echo $ratemin; ?>,
      max: <?php echo $ratemax; ?>,
      from:<?php echo $ratemin; ?>,
      to: <?php echo $ratemax; ?>,
      onChange: function (data) {
         $("#rateStart").attr('data-start-rate',data.from);
          $("#rateEnd").attr('data-end-rate',data.to);
          /*$("#rateStart").val(data.from);
          $("#rateEnd").val(data.to);*/
          //showroomProductsTable.draw();
      },
      onFinish: function (data) {
          $("#rateStart").val(data.from);
          $("#rateEnd").val(data.to);
          //showroomProductsTable.draw();
      },
});
var raterangeslider = $("#raterange").data("ionRangeSlider");
</script>

@endsection