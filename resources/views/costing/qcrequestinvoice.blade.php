@extends('layout.mainlayout')

@section('title', 'Costing Request Invoice')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('costing.qcrequestinvoice') }}
      <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div id="msg"></div>
  <div class="widget-list">
      <div class="row">
          <div class="col-md-12 widget-holder">
              <div class="widget-bg">
                  <div class="widget-heading clearfix">
                      <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'Costing Request Invoice'}}</h5>
                  </div>

                    @if ($message = Session::get('success'))
                          <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                          <i class="material-icons list-icon">check_circle</i>
                          <strong>Success</strong>: {{ $message }}
                          </div>
                        @endif

                        @if ($message = Session::get('error'))
                          <div class="alert alert-danger alert-dismissible fade show" role="alert">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                          <i class="material-icons list-icon">check_circle</i>
                           <strong>Error</strong>: {{ $message }} 
                          </div>
                        @endif

                    <div class="widget-body clearfix dataTable-length-top-0">
                      <table class="table-head-box table-center table table-striped table-responsive" id="qcRequestInvoiceTable">
                            <thead>
                              <tr class="bg-primary">
                                  <th>Sku</th>
                                 <th>Certificate/Betch No.</th>
                                  <th>Branding</th>
                                  <th>Detail</th>

                              </tr>
                              </thead>
                              <tbody>
                              
                              @foreach ($costingdatas as $key => $costingdata)
                              <?php 
                                    $costing_id = $costingdata->costingdata_id;
                                    $costraw = \App\Costing::where('costing_id',$costing_id)->first();
                                    $seive_size[$key] = explode(',',$costingdata->seive_size);
                                    $material_mm_size[$key] = explode(',',$costingdata->material_mm_size);
                                    $material_pcs[$key] = explode(',',$costingdata->material_pcs);
                                    $material_weight[$key] = explode(',',$costingdata->material_weight);
                                    $maxVal = max(count($seive_size[$key]),count($material_mm_size[$key]),count($material_pcs[$key]),count($material_weight[$key]));
                                  ?>
                                <tr>
                                  
                                  <td>{{ $costingdata->sku }}</td>
                                 @if(!empty($costingdata->certificate_no) ||  $costingdata->certificate_no != 0 )
                                  <td>{{ $costingdata->certificate_no }}</td>
                                  @else
                                  <td>{{ $costingdata->item }}</td>
                                  @endif
                                   <td>{{ $costingdata->branding }}</td>
                                  <td>
                                      <a href="javascript:void(0);"><i title="Detail" onclick="showDetail('<?php echo $costingdata->id;?>')" class="material-icons list-icon">info</i></a>

                                  </td>
                                </tr>
                                @endforeach
                              </tbody>
                              <tfoot>
                              <tr>
                                <th>Sku</th>
                                  <th>Certificate/Betch No.</th>
                                  <th>Branding</th>
                                  <th>Detail</th>
                              </tr>

                              </tfoot>
                      </table>
                  </div>
              </div>
          </div>
      </div>
  </div>
  <div class="modal fade bs-modal-lg modal-color-scheme generateIGI_popup" tabindex="-1" id="generateIGI_popup" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              
          </div>
      </div>
  </div>
</main>

<div class="modal fade bs-modal-lg modal-color-scheme costing_showDetail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header text-inverse">
              <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
              <h5 class="modal-title" id="myLargeModalLabel">Costing Product</h5>
          </div>
  <div class="modal-body"> 
  </div>
  <div class="modal-footer"> 
      <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close this</button>
  </div>
</div>
<!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>

@endsection
@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">

$('#qcRequestInvoiceTable').DataTable({
      "processing": true,
      "serverSide": true,
      "deferLoading": <?=$totalcount?>,
      "ajax":{
               "url": "<?=URL::to('/').'/costing/qcrequestinvoiceResponse' ?>",
               "dataType": "json",
               "type": "GET",
               "data":{ _token: "{{csrf_token()}}"}
             },
  });

function showDetail(id) {
    jQuery.ajax({
      type: "GET",
      dataType: "json",
      url: "<?=URL::to('/').'/costing/showDetail' ?>",
      data: {
      "_token": '{{ csrf_token() }}',
      "id": id
      },
      success: function(data) {
          $('.modal-body').html(data.html);
          $('.costing_showDetail').modal('show');
      }
   });
}

</script>

@endsection