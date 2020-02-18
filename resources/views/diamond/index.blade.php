@extends('layout.mainlayout')

@section('title', 'Diamond Inventory')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">

<!-- Page Title Area -->
<div class="row page-title clearfix">
    {{ Breadcrumbs::render('diamond.index') }}
    <!-- /.page-title-right -->
</div>

<div class="widget-list">
      <div class="row">
          <div class="col-md-12 widget-holder">
              <div class="widget-bg">
                 <div class="progress progress-lg">
                   <div role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar bg-success">Raw</div>
                    <div role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar bg-success">CVD</div>
                   <div role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success">Assorting</div>
                    <div role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success">Sizing</div>
                    <div role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 20%" class="progress-bar progress-bar bg-success">Moved to Inventory</div>
                  </div>
                  <div class="widget-heading clearfix position-relative mt-3">
                      <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{'Diamond Inventory'}}</h5>
                       <div class="btn-top-right">
                        <a href="{{ route('diamondinventory.createnew') }}" class="btn btn-primary small-btn-style ripple"><i class="material-icons list-icon fs-24">playlist_add</i> Add Diamonds</a>
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
                          <div class="row custom-drop-style custom-select-style label-text-pl-25">
                            <div class="col-md-3">
                              <div class="form-group">
                                <select class="form-control" name="textfilter" id="textfilter">
                                  <option value="" >Select Diamond Shape</option>
                                    @foreach ($stone_shape as $shape)
                                      <option value="{{$shape}}">{{$shape}}</option>
                                    @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                 <select class="form-control" id="textfilterquality" name="quality_search">
                                  <option value="" >Select Diamond Quality</option>

                                  @foreach ($stone_clarity as $stone_clarity)
                                  <option value="{{$stone_clarity}}">{{$stone_clarity}}</option>
                                 @endforeach
                                </select>
                              </div>
                            </div>
                             <div class="col-md-3">
                              <div class="form-group">
                                 <input type="number" name="mm_size" placeholder="Search MM Size" class="form-control" value="" id="mmsize_search">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                 <input type="number" name="seive_size" placeholder="Search Seive Size" class="form-control" value=""  id="sivesize_search">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <button class="btn btn-primary" id="searchfilter" type="button">Search</button>
                              <button class="btn btn-default" id="searchreset"  type="button">Reset</button>
                            </div>
                          </div>
                    </div>
                  </div>
                      <table class="table table-striped table-center" id="diamonddatatable">
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
                                 <th>Action</th>
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
                                    <td>
                                        <a title="View transactions" class="color-content table-action-style" href="{{ route('diamond.transactions',$diamonds->id) }}"><i class="material-icons md-18">remove_red_eye</i></a>
                                         <a title="Update weight" class="color-content table-action-style" href="{{ route('diamond.diamondmiscloss',$diamonds->id) }}"><i class="material-icons md-18">remove_from_queue</i></a>
                                    </td>
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
                    if (column === 5) {
                      data = data.replace('₹ ', '');
                    }
                    return data;
                }
            }
        }
    };
  var table = $('#diamonddatatable').DataTable({
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
    "url": "{{action('DiamondController@filter_search')}}",
    "data": function(data, callback){
      showLoader();
      data._token = "{{ csrf_token() }}";

      var textfilter = $('#textfilter  option:selected').val();
       if(textfilter != ''){
        data.textfilter = textfilter;
      }

      var textfilterquality = $('#textfilterquality option:selected').val();
        if(textfilterquality != '')
        {
          data.textfilterquality = textfilterquality;
        }
        //alert(textfilterquality);
        var mmsize =   $('#mmsize_search').val();
        if(mmsize != ''){
          data.mmsize = mmsize;
        }

        var sivesize =   $('#sivesize_search').val();
         if(sivesize != ''){
            data.sivesize = sivesize;
          }

    },
    complete: function(response){
      hideLoader();
    }
  },"columnDefs": [ {
    "targets": [0,8],
    "orderable": false
    }]
});

    $('#searchfilter').click(function(){
      table.draw();
    });

    $('#searchreset').click(function(){
      $('#textfilter').val("");
      $('#textfilterquality').val("");
      $('#mmsize_search').val("");
      $('#sivesize_search').val("");

      table.draw();
    });

</script>
@endsection