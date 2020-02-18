@extends('layout.mainlayout')

@section('title', 'Costing Log')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('costing.costinglog') }}
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
                      <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'Costing Log'}}</h5>
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

                      <table class="table-head-box table-center table table-striped costing-log-table checkbox checkbox-primary" id="example">
                             <thead>
                              <tr class="bg-primary">
                                <th>Name</th>
                                <th>Jobwork status</th>
                                <th>File Status</th>
                                <th>Vendor</th>
                                <th>Date</th>
                              </tr>
                             </thead>
                              <tbody>
                              @foreach ($costingdatas as $key => $costingdata)
                              <?php 
                                    $vendor_id = $costingdata->vendor_id;
                                    $vendorColl = $vendor->where('id',$vendor_id)->first();
                                    $vendor_name = $vendorColl['name']; 
                                    $jobworkstatus = ($costingdata->jobwork_status == 1)?'With Jobwork':'Without Jobwork';
                                    $date = date('d-m-Y', strtotime($costingdata->created_at)); //Y:m:d
                                    $status = ($costingdata->status == 'Decline')?'Declined':'Approved';
                              ?>
                              <tr>
                                  <td>{{ $costingdata->name }}</td>
                                  <td>{{ $jobworkstatus }}</td>
                                  <td>{{ $status }}</td>
                                  <td>{{ $vendor_name }}</td>
                                  <td>{{ $date }}</td>
                              </tr>
                              @endforeach
                              </tbody>
                              <tfoot>
                              <tr>
                                  <th>Name</th>
                                    <th>Jobwork status</th>
                                    <th>File Status</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
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

  $('#example').DataTable({
      "processing": true,
      "serverSide": true,
      "deferLoading": <?=$totalcount?>,
      "ajax":{
               "url": "<?=URL::to('/').'/costing/costinglogResponse' ?>",
               "dataType": "json",
               "type": "GET",
               "data":{ _token: "{{csrf_token()}}"}
             },
             "columnDefs": [
                { "orderable": false, "targets": [0,1] }
            ]
  });
  $('.costing-log-table').wrap('<div class="costing-log"></div>');
  
jQuery("body").on("click","#chkall_costing",function(){
    jQuery('input:checkbox').prop('checked', this.checked);    
});

$('.export_excel').on('click',function(){
  var estimationcatalogid = jQuery('#estimation-catalog option:selected').val();
  var vendor_id = jQuery('#vendors option:selected').val();
  var chkcosting = [];
  
  jQuery('input[name="chk_costing"]:checked').each(function() {
     chkcosting.push(this.value);
  });
    var query = {
        estimationcatalogid: estimationcatalogid,
        vendor_id: vendor_id,
        chkcosting:chkcosting
    }
    var url = "<?=URL::to('/').'/costing/exportexcel' ?>?" + $.param(query)

   window.location = url;
});


$('.export_pdf').on('click',function(){
  var estimationcatalogid = jQuery('#estimation-catalog option:selected').val();
  var vendor_id = jQuery('#vendors option:selected').val();
  var chkcosting = [];
  
  jQuery('input[name="chk_costing"]:checked').each(function() {
     chkcosting.push(this.value);
  });
    var query = {
        estimationcatalogid: estimationcatalogid,
        vendor_id: vendor_id,
        chkcosting:chkcosting
    }
    var url = "<?=URL::to('/').'/costing/exportpdf' ?>?" + $.param(query);

   window.location = url;
});




</script>

@endsection