<?php
use App\Helpers\CommonHelper;
use App\Helpers\ShowroomHelper;
?>
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
    {{ Breadcrumbs::render('diamond.diamond_statistics_by_mm') }}
    <!-- /.page-title-right -->
</div>

<div class="widget-list">
  <div class="row">
    <div class="col-md-12 widget-holder">
      <div class="widget-bg">
        <div class="tab-pane active" id="inventory-statistic">
          <div class="row">
            <div class="col-lg-3 col-sm-6 widget-holder widget-full-height">
              <div class="widget-bg bg-primary text-inverse">
                <div class="widget-body">
                  <div class="widget-counter">
                    <h6>Total -2 : <small class="text-inverse"></small></h6>
                    <h3 class="h1"><span class="counter total-mmsize-products"><?php echo isset($cntForMmSize) ? $cntForMmSize : 0;  ?></span></h3><i class="material-icons list-icon">remove_circle</i>
                  </div>
                  <!-- /.widget-counter -->
                </div>
                <!-- /.widget-body -->
              </div>
              <!-- /.widget-bg -->
            </div>

            <div class="col-lg-3 col-sm-6 widget-holder widget-full-height">
              <div class="widget-bg bg-color-scheme text-inverse">
                <div class="widget-body">
                  <div class="widget-counter">
                    <h6>Total Star : <small class="text-inverse"></small></h6>
                    <h3 class="h1"><span class="counter total-star-products"><?php echo isset($cntForMmSizeStar) ? $cntForMmSizeStar : 0; ?></span></h3><i class="material-icons list-icon">star</i>
                  </div>
                  <!-- /.widget-counter -->
                </div>
                <!-- /.widget-body -->
              </div>
              <!-- /.widget-bg -->
            </div>

            <div class="col-lg-3 col-sm-6 widget-holder widget-full-height">
              <div class="widget-bg bg-gray text-inverse">
                <div class="widget-body">
                  <div class="widget-counter">
                    <h6>Total Melle : <small class="text-inverse"></small></h6>
                    <h3 class="h1"><span class="counter total-melle-products"><?php echo isset($cntForMmSizeMelle) ? $cntForMmSizeMelle : 0; ?></span></h3><i class="material-icons list-icon">exposure_plus_2</i>
                  </div>
                  <!-- /.widget-counter -->
                </div>
                <!-- /.widget-body -->
              </div>
              <!-- /.widget-bg -->
            </div>
            <div class="col-lg-3 col-sm-6 widget-holder widget-full-height">
              <div class="widget-bg bg-color-scheme text-inverse">
                <div class="widget-body">
                  <div class="widget-counter">
                    <h6>Total Magic size: <small class="text-inverse"></small></h6>
                    <h3 class="h1"><span class="counter total-magic-products"><?php echo isset($cntForMmSizeMagic) ? $cntForMmSizeMagic : 0; ?></span></h3><i class="material-icons list-icon">format_size</i>
                  </div>
                  <!-- /.widget-counter -->
                </div>
                <!-- /.widget-body -->
              </div>
              <!-- /.widget-bg -->
            </div>
          </div>
        </div>

        <div class="widget-heading clearfix position-relative mt-3">
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

          <h5 class="mt-0">{{'Filters'}}</h5>
          <div class="row custom-drop-style custom-select-style label-text-pl-25">
            <div class="col-md-3">
              <div class="form-group">
                <select class="form-control" name="textfilter" id="shapetextfilter">
                  <option value="">Select Diamond Shape</option>
                  @foreach ($stone_shape as $shape)
                  <option value="{{$shape}}">{{$shape}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
               <select class="form-control" id="textfilterquality" name="quality_search">
                <option value="">Select Diamond Quality</option>
                @foreach ($stone_clarity as $stone_clarity)
                <option value="{{$stone_clarity}}">{{$stone_clarity}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group price-filter">
              <div class='dropdown' id='pricerange'>
                <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>MM Size<span class='caret'></span></button>
                <ul class='dropdown-menu'>
                  <li>
                    <div class="form-group px-2">
                      <input type="text" id="priceStart" data_price_init_start="<?php echo $min_price; ?>" data_start_custom="<?php echo ShowroomHelper::currencyFormat($min_price); ?>" class="form-control" value="<?php echo number_format((float) $min_price, 2, '.', '') ?>"/>
                      <input type="text" id="priceEnd" data_price_init_to="<?php echo $max_price; ?>" data_to_custom="<?php echo ShowroomHelper::currencyFormat($max_price); ?>" class="form-control" value="<?php echo number_format((float) $max_price, 2, '.', '') ?>"/>
                    </div>
                    <div class="form-group px-3">
                      <input type="text" id="priceFilter" name="priceFilter" value="" />
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group size-filter">
              <div class='dropdown' id='sizerange'>
                <button class='btn btn-primary dropdown-toggle' type='button' data-toggle='dropdown'>Sieve Size<span class='caret'></span></button>
                <ul class='dropdown-menu'>
                  <li>
                    <div class="form-group px-2">
                      <input type="text" id="sizeStart" data_size_init_start="<?php echo $min_size; ?>" data_start_custom="<?php echo ShowroomHelper::currencyFormat($min_size); ?>" class="form-control" value="<?php echo number_format((float) $min_size, 2, '.', '') ?>"/>
                      <input type="text" id="sizeEnd" data_size_init_to="<?php echo $max_size; ?>" data_to_custom="<?php echo ShowroomHelper::currencyFormat($max_size); ?>" class="form-control" value="<?php echo number_format((float) $max_size, 2, '.', '') ?>"/>
                    </div>
                    <div class="form-group px-3">
                      <input type="text" id="sizeFilter" name="sizeFilter" value="" />
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-auto">
            <button class="btn btn-primary" id="searchfilter" type="button">Search</button>
            <button class="btn btn-default" id="searchreset"  type="button">Reset</button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 mt-3" id="selectedfilter">
            <div class="bootstrap-tagsinput space-five-all">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="widget-bg mt-4">
      <table class="table table-striped table-center" id="diamondstatisticsTable">
        <thead>
          <tr class="bg-primary">
           <th>No.</th>
           <th>Packet ID</th>
           <th>Diamond Quality</th>
           <th>Diamond Shape</th>
           <th>Total Weight (ct)</th>
           <th>Avg. Rate (per ct)</th>
           <th>MM Size</th>
           <th>Sieve Size</th>
         </tr>
       </thead>
       <tbody>
        @foreach ($diamond as $key => $diamonds)
        <tr class="result">
          <td>{{ ++$i }}</td>
          <td>{{ $diamonds->packet_id }}</td>
          <td>{{ $diamonds->stone_quality }}</td>
          <td>{{ $diamonds->stone_shape }}</td>
          <td>{{ $diamonds->total_diamond_weight }}</td>
          <td><?=CommonHelper::covertToCurrency($diamonds->ave_rate);?></td>
          <td>{{ $diamonds->mm_size }}</td>
          <td>{{ $diamonds->sieve_size }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
         <th>No.</th>
         <th>Packet ID</th>
         <th>Diamond Quality</th>
         <th>Diamond Shape</th>
         <th>Total Weight (ct)</th>
         <th>Avg. Rate (per ct)</th>
         <th>MM Size</th>
         <th>Sieve Size</th>
       </tr>
     </tfoot>
   </table>
   <!-- /.widget-body -->
 </div>
</div>
<!-- /.widget-holder -->
</div>
<!-- /.row -->
</div>
               <!-- /.widget-list -->
</main>
<input type="hidden" name="isPaginaged" id="isPaginaged" value="true">
<input type="hidden" name="filterapplied" id="filterapplied" value="false">

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/js/ion.rangeSlider.min.js"></script>
<script src="<?=URL::to('/');?>/js/jquery.counterup.min.js"></script>
<script src="<?=URL::to('/');?>/js/jquery.waypoints.js"></script>
<script type="text/javascript">
var clearAllFilter = false;
var priceSlider = $("#priceFilter").ionRangeSlider({
      type: "double",
      skin: "round",
      grid: false,
      keyboard: true,
      force_edges: false,
      prettify_enabled: true,
      prettify_separator: ',',
      min: <?php echo $min_price ?>,
      max: <?php echo $max_price ?>,
      from: <?php echo $min_price ?>,
      to: <?php echo $max_price ?>,
      step: 0.1,
      onChange: function (data) {
          $("#priceStart").val(data.from);
          $("#priceEnd").val(data.to);
          //showroomProductsTable.draw();
      },
      onFinish: function (data) {
          $("#priceStart").val(data.from);
          $("#priceEnd").val(data.to);
          //showroomProductsTable.draw();
      },
});

// Seive Size Range Slider
var sizeSlider = $("#sizeFilter").ionRangeSlider({
      type: "double",
      skin: "round",
      grid: false,
      keyboard: true,
      force_edges: false,
      prettify_enabled: true,
      prettify_separator: ',',
      min: <?php echo $min_size ?>,
      max: <?php echo $max_size ?>,
      from: <?php echo $min_size ?>,
      to: <?php echo $max_size ?>,
      step: 0.1,
      onChange: function (data) {
          $("#sizeStart").val(data.from);
          $("#sizeEnd").val(data.to);
      },
      onFinish: function (data) {
          $("#sizeStart").val(data.from);
          $("#sizeEnd").val(data.to);
      },
});
var pricerangeslider = $("#priceFilter").data("ionRangeSlider");
var sizerangeslider = $("#sizeFilter").data("ionRangeSlider");
  var buttonCommon = {
        exportOptions: {
            format: {
                body: function ( data, row, column, node ) {
                    if (column === 5) {
                      data = data.replace('₹ ', '');
                    }
                    return data;
                }
            }
        }
    };
  var diamondstatisticsTable = $('#diamondstatisticsTable').DataTable({
  "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B>><'row'<'col-md-12' <'scroll-lg' t>>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
  "language": {
    "search": "",
    "infoEmpty": "No matched records found",
    "zeroRecords": "No matched records found",
    "emptyTable": "No data available in table",
    /*"sProcessing": "<div class='spinner-border' style='width: 3rem; height: 3rem;'' role='status'><span class='sr-only'>Loading...</span></div>"*/
  },
  "buttons": [
    $.extend( true, {}, buttonCommon, {
      extend: 'csv',
      footer: false,
      title: 'Diamond-Inventory-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3,4,5,6,7],
          orthogonal: 'export'
      }
    }),
    $.extend( true, {}, buttonCommon, {
      extend: 'excel',
      footer: false,
      title: 'Diamond-Inventory-Data',
      className: "btn btn-primary btn-sm px-3",
      exportOptions: {
          columns: [0,1,2,3,4,5,6,7],
          orthogonal: 'export'
      }
    })
  ],
  "order": [[ 0, "desc" ]],
  "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
  "deferLoading": <?=$datacount?>,
  "processing": true,
  "serverSide": true,

  "searching": false,
  "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
  "serverMethod": "GET",
  "ajax":{
    "url": "{{action('DiamondController@filter_diamond_statistics')}}",
    "data": function(data, callback){
      showLoader();
      data._token = "{{ csrf_token() }}";

      var textfilter = $('#shapetextfilter  option:selected').val();
       if(textfilter != ''){
        data.textfilter = textfilter;
      }

      var textfilterquality = $('#textfilterquality option:selected').val();
        if(textfilterquality != '')
        {
          data.textfilterquality = textfilterquality;
        }

        var mmsize =   $('#priceStart').val();
        if(mmsize != ''){
          data.mmsizemin = mmsize;
        }

        var mmsizemax =   $('#priceEnd').val();
        if(mmsizemax != ''){
          data.mmsizemax = mmsizemax;
        }

        var seivesize =   $('#sizeStart').val();
        if(seivesize != ''){
          data.seivesizemin = seivesize;
        }

        var seivesizemax =   $('#sizeEnd').val();
        if(seivesizemax != ''){
          data.seivesizemax = seivesizemax;
        }
        //alert(textfilterquality);
        /* var mmsize =   $('#mmsize_search').val();
        if(mmsize != ''){
          data.mmsize = mmsize;
        }

        var sivesize =   $('#sivesize_search').val();
         if(sivesize != ''){
            data.sivesize = sivesize;
          } */

    },
    complete: function(response){
      /* $(".h1 .total-mmsize-products").html(response.responseJSON.cntmmsize);
      $(".h1 .total-star-products").html(response.responseJSON.cntmmsizestar);
      $(".h1 .total-melle-products").html(response.responseJSON.cntmmsizemelle);
      $(".h1 .total-magic-products").html(response.responseJSON.cntmmsizemagic); */

      hideLoader();
      loadajaxfilterblock();
      //getProminentFilters();
    }
  },"columnDefs": [ {
    "targets": [0],
    "orderable": false
    }]
});

    $('#searchfilter').click(function(){
      diamondstatisticsTable.draw();
    });

    $('#searchreset').click(function(){
      $('#textfilter').val("");
      $('#textfilterquality').val("");
      $('#mmsize_search').val("");
      $('#sivesize_search').val("");

      diamondstatisticsTable.draw();
    });


var isClearAllFilter = false;
function loadajaxfilterblock()
{
  var filters = new Object();
  starter = 0;
  var start = Math.floor($("#priceStart").val());
  var orignal_start = Math.floor($("#priceStart").attr('data_price_init_start'));
  var start_custom = $("#priceStart").val();
  var to_custom = $("#priceEnd").val();
  var final_start =  start_custom.toLocaleString('en-IN', {
      maximumFractionDigits: 2,
      style: 'currency',
      currency: 'INR'
  });
  
  var final_to =  to_custom.toLocaleString('en-IN', {
      maximumFractionDigits: 2,
      style: 'currency',
      currency: 'INR'
  });

  var startsize = Math.floor($("#sizeStart").val());
  var orignal_start_size = Math.floor($("#sizeStart").attr('data_size_init_start'));
  var start_custom_size = $("#sizeStart").val();
  var to_custom_size = $("#sizeEnd").val();
  var final_start_size =  start_custom_size.toLocaleString('en-IN', {
      maximumFractionDigits: 2,
      style: 'currency',
      currency: 'INR'
  });
  
  var final_to_size =  to_custom_size.toLocaleString('en-IN', {
      maximumFractionDigits: 2,
      style: 'currency',
      currency: 'INR'
  });

  var to = Math.floor($("#priceEnd").val());
  var orignal_to = Math.floor($("#priceEnd").attr('data_price_init_to'));

  var sizeto = Math.floor($("#sizeEnd").val());
  var orignal_size_to = Math.floor($("#sizeEnd").attr('data_size_init_to'));

  if((!isClearAllFilter && $("#isPaginaged").val()=='false') || (start != orignal_start || to != orignal_to))
  {
    prstart = $("#priceStart").val();
    prto =  $("#priceEnd").val();
    filters['price'] = 'MM Size:' + start_custom + '-' + to_custom;
  }

  if((!isClearAllFilter && $("#isPaginaged").val()=='false') || (startsize != orignal_start_size || sizeto != orignal_size_to)){
    sizestart = $("#sizeStart").val();
    sizeto =  $("#sizeEnd").val();
    filters['size'] = 'Seive Size:' + start_custom_size + '-' + to_custom_size;
  }

  if($("#shapetextfilter").val() != '')
  {
    var shapeval = $("#shapetextfilter").val();
    if(shapeval != ''){
      var name = $("#shapetextfilter option:selected").text();
    }else{
      var name = $("#shapetextfilter option:selected").val();
    }
    var val =  'shapetextfilter';
    filters[val] = name;
    /* 
    var virtualvalues = 
      if(virtualvalues != ''){
        var name = jQuery( "#virtualproductmanager option:selected" ).text();
      }else{
        var name = jQuery( "#virtualproductmanager option:selected" ).val();
      }
      var val =  'virtualproductmanager';
      
    filters[val] = name.replace('Select Diamond Shape',''); */
  }

  if($("#textfilterquality").val() != '')
  {
    var shapeval = $("#textfilterquality").val();
    if(shapeval != ''){
      var name = $("#textfilterquality option:selected").text();
    }else{
      var name = $("#textfilterquality option:selected").val();
    }
    var val =  'textfilterquality';
    filters[val] = name;
    /* 
    var virtualvalues = 
      if(virtualvalues != ''){
        var name = jQuery( "#virtualproductmanager option:selected" ).text();
      }else{
        var name = jQuery( "#virtualproductmanager option:selected" ).val();
      }
      var val =  'virtualproductmanager';
      
    filters[val] = name.replace('Select Diamond Shape',''); */
  }
  $("#selectedfilter .bootstrap-tagsinput").html('');
  var filterFlag = false;
  for (var x in filters) {
    
    if(filters[x] != '')
    {
      var div = '';
      var div = "<span class='tag label label-info'>"+filters[x]+"";
    }
    if(x == 'price')
    {
      div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('price')\" data-type=" + x +"></span>";
    }else if(x == 'size')
    {
      div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('size')\" data-type=" + x +"></span>";
    }else if(x == 'shapetextfilter'){
      div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('shapetextfilter')\" data-type=" + x +"></span>";
    }else if(x == 'textfilterquality'){
      div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('textfilterquality')\" data-type=" + x +"></span>";
    }else{
      div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('"+x+"')\" data-type=" + x +"></span></span>";
    }
    //console.log(div);
    $("#selectedfilter .bootstrap-tagsinput").append(div);
    filterFlag = true;
  }
  
  if(filterFlag)
  {
      var div = "<span class='tag label label-info'>Clear All";
      div += "<span data-role='remove' class='pointer' onclick=\"clearfilter('all')\"></span></span>";
  }
  
  $("#selectedfilter .bootstrap-tagsinput").append(div);
}

function clearfilter(filterType)
{
  $('#filterapplied').val('false');
  if(filterType == "all"){
    filterdiamondstatistic("all");
    resetfilteroptions("all");
    isClearAllFilter = true;
    return;
  }else{
    var resetfilter = filterType;
    filterdiamondstatistic(resetfilter);
    resetfilteroptions(resetfilter);
    isClearAllFilter = false;  
  }

}
function filterdiamondstatistic(resetfilter){
  
  if(typeof(resetfilter) != 'undefined'){
        if(resetfilter == 'price'){
          $("#priceStart").val($("#priceStart").attr('data_price_init_start'));
          $("#priceEnd").val($("#priceEnd").attr('data_price_init_to'));
          var intStart = Math.floor($("#priceStart").attr('data_price_init_start'));
          var intEnd = Math.floor($("#priceEnd").attr('data_price_init_to'));
          //To update price slider values
          pricerangeslider.update({
              from: intStart,
              to: intEnd
          });
        }else if(resetfilter == 'size'){
          $("#sizeStart").val($("#sizeStart").attr('data_size_init_start'));
          $("#sizeEnd").val($("#sizeEnd").attr('data_size_init_to'));
          var intStartSize = Math.floor($("#sizeStart").attr('data_size_init_start'));
          var intEndSize = Math.floor($("#sizeEnd").attr('data_size_init_to'));
          //To update price slider values
          sizerangeslider.update({
              from: intStartSize,
              to: intEndSize
          });
        }else if(resetfilter == 'shapetextfilter'){
          $('#shapetextfilter').val('');
        }else if(resetfilter == 'textfilterquality'){
          $('#textfilterquality').val('');
        }else{
          $(".category_chkbox:checkbox[data-filtertype='"+resetfilter+"']").prop( "checked", false );
          $(".showroom-filter-checkbox input:checkbox[value='"+resetfilter+"']").prop( "checked", false );
        }
  }

  filters = {};
  filters['price_start'] = $("#priceStart").val();
  filters['price_to'] = $("#priceEnd").val();

  filters['size_start'] = $("#sizeStart").val();
  filters['size_to'] = $("#sizeEnd").val();
  filters['shapetextfilter'] = $('#shapetextfilter').val();
  filters['textfilterquality'] = $('#textfilterquality').val();
  
  var starter = 0;
  if(typeof(resetfilter) != 'undefined')
      {
        if(resetfilter == 'all')
        {
          $("#priceStart").val($("#priceStart").attr('data_price_init_start'));
          $("#priceEnd").val($("#priceEnd").attr('data_price_init_to'));
          var filters = {};
          var intStart = Math.floor($("#priceStart").attr('data_price_init_start'));
          var intEnd = Math.floor($("#priceEnd").attr('data_price_init_to'));
          pricerangeslider.update({
              from: intStart,
              to: intEnd
          });

          $("#sizeStart").val($("#sizeStart").attr('data_size_init_start'));
          $("#sizeEnd").val($("#sizeEnd").attr('data_size_init_to'));
          var filters = {};
          var intStartSize = Math.floor($("#sizeStart").attr('data_size_init_start'));
          var intEndSize = Math.floor($("#sizeEnd").attr('data_size_init_to'));
          sizerangeslider.update({
              from: intStartSize,
              to: intEndSize
          });

          $("#shapetextfilter").val('');
          $("#textfilterquality").val('');
          
        }
      }
      showLoader();
}
function resetfilteroptions(resetfilter)
{
    var filters = {};
    filters['price_start'] = $("#priceStart").val();
    filters['price_to'] = $("#priceEnd").val();
    /* filters['size_start'] = $("#sizeStart").val();
    filters['size_to'] = $("#sizeEnd").val(); */
    if(typeof(resetfilter) != 'undefined')
    {
      if(resetfilter == 'all')
      {
        var filters = {};
      }else if(resetfilter == 'shapetextfilter'){
        filters[resetfilter] = "";
      }else if(resetfilter == 'textfilterquality'){
        filters[resetfilter] = "";
      }else{
        filters[resetfilter] = 0;
      }
    }
    diamondstatisticsTable.draw();
}
</script>
@endsection