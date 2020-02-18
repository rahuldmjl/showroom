@extends('layout.mainlayout')

@section('title', 'Costing Rejected Product')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('costing.costinglist') }}
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
                      <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'Costing Rejected Product'}}</h5>
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

                    <form id="qcstatus_form">
                    <div class="row">
                      <div class="col-lg-3">
                          <div class="form-group">
                              <select class="form-control" id="qcstatus_drpdwn" name="qcstatus_drpdwn">
                                <option value="">Choose an option</option>
                                <option value="accept">Accept</option>
                                <option value="return_memo">Return memo</option>
                                <option value="downaload_excel">Download Excel</option>
                              </select>
                          </div>
                      </div>
                      <div class="col-md-3">
                        <button class="btn btn-primary btn-ripple qc_btn_multiple" type="submit">Change Status</button>
                      </div>
                    </div>
                  </form>

                    <div class="widget-body clearfix">
                      <table class="table-head-box table-center table table-striped table-responsive checkbox checkbox-primary" id="qcRejectedTable">
                            <thead>
                              <tr class="bg-primary">
                                  <th><label><input class="form-check-input" type="checkbox" name="chkall_costing" id="chkall_costing"><span class="label-text"></span></label></th>
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
                                  <td class="sorting_1">
                                    <label><input type="checkbox" class="form-check-input chkProduct" name="chk_costing" id="chk_costing" value="<?php echo $costingdata->id; ?>">
                                    <span class="label-text"></span>
                                    </label>
                                  </td>
                                  <td>{{ $costingdata->sku }}</td>
                                   @if(!empty($costingdata->certificate_no) ||  $costingdata->certificate_no != 0 )
                                  <td>{{ $costingdata->certificate_no }}</td>
                                  @else
                                  <td>{{ $costingdata->item }}</td>
                                  @endif
                                  <td>{{ $costingdata->branding }}</td>
                                 
                                  <td>
                                      <a href="javascript:void(0);"><i title="Detail" onclick="showDetail('<?php echo $costingdata->id;?>')" class="material-icons list-icon">info</i></a>

                                      <a href="javascript:void(0);"><i title="Accept" data-id ='<?php echo $costingdata->id;?>' class="material-icons list-icon qc_btn" id="accept">check_circle</i></a>

                                      <a href="javascript:void(0);"><i title="Return memo" data-id ='<?php echo $costingdata->id;?>' data-vendor='<?= $costraw->vendor_id; ?>' class="material-icons list-icon qc_btn <?php if($costingdata->return_memo == 1) { ?> disabled <?php } ?>" id="return_memo">assignment</i></a>

                                      
                                </tr>
                                @endforeach
                              </tbody>
                              <tfoot>
                              <tr>
                                <th></th>
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
        <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@endsection
@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">

var dataTable = $('#qcRejectedTable').DataTable({
      "processing": true,
      "serverSide": true,
      "deferLoading": <?=$totalcount?>,
      "ajax":{
               "url": "<?=URL::to('/').'/costing/qcrejectResponse' ?>",
               "dataType": "json",
               "type": "GET",
               "data":{ _token: "{{csrf_token()}}"}
             },
             "columnDefs": [
                { "orderable": false, "targets": [0] }
            ] 
  });

jQuery("body").on("click","#chkall_costing",function(){
    jQuery('input:checkbox').prop('checked', this.checked);    
});

function getqcCount() {
  jQuery.ajax({
    url: "<?=URL::to('/').'/costing/qccount' ?>",
    success : function(data) {
      $('.qcrejectcount').html(data.qcrejectcount);
      $('.qcacceptcount').html(data.qcacceptcount);
      $('.qcigicount').html(data.qcigicount);
      $('.qcrequestinvoice').html(data.qcrequestinvoice);
      $('.qcreturnmemo').html(data.qcreturnmemo);
      $('.qccostingproductcount').html(data.qccostingproductcount);
    }
  });
}

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


//$('.qc_btn').click(function() {
$(document).on('click','.qc_btn',function() {
  var status = this.id;
  var id = $(this).attr("data-id");

  var vendor_id = $(this).attr("data-vendor");
  if(status == 'accept') {

      swal({
        title: "Are you sure to "+status+" this product?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
      }).then(result => {
        if (result.value) {
      jQuery.ajax({
        type: "GET",
        url: "<?=URL::to('/').'/costing/changeQcStatus' ?>",
        data: {
        "_token": '{{ csrf_token() }}',
        "id": id,
        "status" : status,
        },
        success: function(data) {
          window.location.href = "<?=URL::to('/').'/costing/qcaccept' ?>";
          getqcCount();
        }
      });
  }
    });
  }

  else{

    swal({
        title: "Are you sure to return memo for this product?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
    }).then(result => {
    
      if (result.value) {
         jQuery.ajax({
            type: "GET",
            url: "<?=URL::to('/').'/costing/returnmemoByQc' ?>",
            data: {
            "_token": '{{ csrf_token() }}',
            "chkCostingIds": id,
            },
            success: function(data) {
                var query = { chkcosting:[id],vendor_id:vendor_id}
                var url = "<?=URL::to('/').'/costing/memopdf' ?>?" + $.param(query);
                window.location = url;
                setTimeout(function(){ 
                window.location.href = "<?=URL::to('/').'/costing/qcreturnmemo' ?>"; 
                }, 1200);
                getqcCount();
              }
          });
       }
   });
  }
 });

$("#qcstatus_form").submit(function(e) {
  e.preventDefault();
  var chkCostingIds = [];
  $('input[name="chk_costing"]:checked').each(function() {
    chkCostingIds.push($(this).val());
  });

  if(chkCostingIds != "") {
    var status = $("#qcstatus_drpdwn option:selected").val();
    if(status != "") {

      if(status == "accept") { 

        var form = $(this);
        var url = "<?=URL::to('/').'/costing/changeQcStatusMultiple' ?>";

        swal({
        title: "Are you sure to "+status+" this product?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        }).then(result => {
      if (result.value) {
        $.ajax({
            type: "GET",
            url: url,
            dataType: "json",
            data: {
              "_token": '{{ csrf_token() }}',
              "status": status,
              "chkCostingIds" :chkCostingIds,
            },
            success: function(data) {
              window.location.href = "<?=URL::to('/').'/costing/qcaccept' ?>"; 
              getqcCount();
            }
        });
      }
      });
      }

      if(status == 'downaload_excel') {
          var query = { chkcosting:chkCostingIds}
          var url = "<?=URL::to('/').'/costing/acceptedProductExcel' ?>?" + $.param(query);
          window.location = url;
      }


     if(status == "return_memo") {
        var url = "<?=URL::to('/').'/costing/returnmemoByQc' ?>";

        swal({
        title: "Are you sure to return memo?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        }).then(result => {

          if (result.value) {
          $.ajax({
            type: "GET",
                url: url,
                data: {
                  "_token": '{{ csrf_token() }}',
                  "chkCostingIds" :chkCostingIds,
                },
                success: function(data) {
                  var query = { id:[chkCostingIds],vendor_id:vendor_id }
                  var url = "<?=URL::to('/').'/costing/memopdf' ?>?" + $.param(query);
                  window.location = url;
                  setTimeout(function(){ 
                  window.location.href = "<?=URL::to('/').'/costing/qcreturnmemo' ?>"; 
                  }, 1200);
                  getqcCount();
                }
          });
        }

        });
      }
     
    }
    else {
      swal("Please select status");
      return false;
    }
  }
  else {
    swal("Please select checkbox.");
    return false;
  }

});

</script>

@endsection