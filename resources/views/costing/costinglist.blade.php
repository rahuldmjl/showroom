@extends('layout.mainlayout')
@section('title', 'Costing Product')
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
                      <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'Costing Product'}}</h5>
                  </div>

                    @if ($message = Session::get('success'))
                          <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                          <button type="button" class="close alert-closebtn-style" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
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

                  <div class="row mb-lg-0 mb-4">
                      <div class="col-lg-3 col-md-6">
                          <div class="form-group">
                              <select class="form-control vendors" id="vendors" name="vendors">
                                <option value="0">Select Vendor</option>
                               <?php
                                foreach ($vendor as $vendorcoll) { ?>
                                <option value="<?php echo $vendorcoll->id; ?>"><?php echo $vendorcoll->name; ?></option>
                                <?php } ?>
                              </select>
                          </div>
                      </div>
                       <div class="col-lg-3 col-md-6">
                          <div class="form-group">
                              <select class="form-control estimation-catalog" id="estimation-catalog" name="estimation-catalog">
                                <option value="">Choose an option</option>
                                <option value="estimation">Estimation</option>
                                <option value="catalog">Catalog</option>
                              </select>
                          </div>
                       </div>
                      <div class="col-lg-6">
                            <a href="#" class="export_excel btn mr-sm-2 btn-primary ripple"><i class="material-icons list-icon fs-24">playlist_add</i> Export Excel</a>
                            <a href="#" class="export_pdf btn btn-primary ripple"><i class="material-icons list-icon fs-24">playlist_add</i> Export PDF</a>
                      </div>
                  </div>
                  <form id="qcstatus_form">
                    <div class="row">
                      <div class="col-lg-3 col-md-6">
                          <div class="form-group">
                              <select class="form-control" id="qcstatus_drpdwn" name="qcstatus_drpdwn">
                                <option value="">Choose an option</option>
                                <option value="accept">Accept</option>
                                <option value="reject">Reject</option>
                              </select>
                          </div>
                      </div>
                      <div class="col-md-3">
                        <button class="btn btn-primary btn-ripple qc_btn_multiple" type="submit">Change Status</button>
                      </div>
                    </div>
                  </form>
                    <!-- /.widget-heading -->
                  	<div class="widget-body clearfix">
                      <table class="table-head-box table-center table table-striped thumb-sm checkbox checkbox-primary costing_product_table" id="costinglistTable">
                          	<thead>
                            	<tr class="bg-primary">
                                  <th><label><input class="form-check-input" type="checkbox" name="chkall_costing" id="chkall_costing"><span class="label-text"></span></label></th>
                                  <th>Image</th>
                                  <th>Sku</th>
                									<th>Vendor</th>
                                  <th>Batchno</th>
                									<th>Date</th>
                									<th>Detail</th>
                              </tr>
                          	</thead>
                          	  <tbody>
	                          	
	                            @foreach ($costingdatas as $key => $costingdata)
	                            <?php $Image = URL::to('/') .'/'.$costingdata->image; 
                              	   	$costing_id = $costingdata->costingdata_id;
                                    $costraw = \App\Costing::where('costing_id',$costing_id)->first();
                                    $vendor_id = $costraw->vendor_id;
                                    $vendorColl = $vendor->where('id',$vendor_id)->first();
                                    $vendor_name = $vendorColl['name'];
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
                                  <td><img src="{{ $Image }}" class="img-fluid" height="120" width="120"/></td>
                                  <td>{{ $costingdata->sku }}</td>
                                  <td>{{ $vendor_name }}</td>
                                  <td>{{ $costingdata->item }}</td>
                                  <td>{{ date('d-m-Y', strtotime($costingdata->created_at)) }}</td>
                                  <td>
                                      <a href="javascript:void(0);"><i title="Detail" onclick="showDetail('<?php echo $costingdata->id;?>')"  class="material-icons list-icon">info</i></a>
                                      <a href="javascript:void(0);"><i title="Accept" data-id ='<?php echo $costingdata->id;?>' class="material-icons list-icon qc_btn" data-status="accept" id="accept">check_circle</i></a>

                                      <a href="javascript:void(0);"><i title="Reject" data-id ='<?php echo $costingdata->id;?>' class="material-icons list-icon qc_btn" data-status="reject" id='reject'>cancel</i></a>
                                  </td>
                                </tr>
                                @endforeach
                          	  </tbody>
                          	  <!-- <tfoot>
                                <tr>
                                  <th></th>
                                  <th>Image</th>
                                  <th>Sku</th>
                                  <th>Vendor</th>
                                  <th>Batchno</th>
                                  <th>Cno</th>
                                  <th>Detail</th>
                                </tr>
                              </tfoot> -->
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
  <div class="modal-body costing-model-body"> 
  </div>
  <div class="modal-footer"> 
      <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
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

var dataTable = $('#costinglistTable').DataTable({
      "processing": true,
      "serverSide": true,
      "deferLoading": <?=$totalcount?>,
      "ajax":{
               "url": "<?=URL::to('/').'/costing/costinglistResponse' ?>",
               "dataType": "json",
               "type": "GET",
               "data":{ _token: "{{csrf_token()}}"}
             },
              "columnDefs": [
                { "orderable": false, "targets": [0,1,3,6] }
            ]
             
  });
$('.costing_product_table').wrap('<div class="costing_product"></div>');

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
    if(estimationcatalogid != '') {
    var url = "<?=URL::to('/').'/costing/exportexcel' ?>?" + $.param(query);
    window.location = url;
    } else {
      swal("Please Select Option.");
    }
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
    if(estimationcatalogid != '' && typeof(estimationcatalogid)  != "undefined" ) {
     var url = "<?=URL::to('/').'/costing/exportpdf' ?>?" + $.param(query);
     window.location = url;
   } else {
     swal("Please Select Option.");
    }
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
          $('.costing-model-body').html(data.html);
          $('.costing_showDetail').modal('show');
      }
   });
}

$('.costing_product_table').on('click','.qc_btn',function() {
  var status = $(this).attr("data-status");
  var id = $(this).attr("data-id");

  swal({
        title: "Are you sure to "+status+" this product?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
      }).then(result => {

      if (result.value) {
      if(status == "accept") {
      
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

if(status == "reject"){

  const { value: comment } = Swal.fire({
  title: 'Comment',
  input: 'textarea',
  showCancelButton: true,
  inputValidator: (value) => {
    if (!value) {
      return 'This field is required!'
    }
    else {
      jQuery.ajax({
            type: "GET",
            url: "<?=URL::to('/').'/costing/changeQcStatus' ?>",
            data: {
            "_token": '{{ csrf_token() }}',
            "id": id,
            "comment" : value,
            "status" : status,
            },
            success: function(data) {
                if(status == "reject") {
                  window.location.href = "<?=URL::to('/').'/costing/qcreject' ?>"; 
                }
                getqcCount();
              }
          });
    }


  }
})

}
}
});
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
      var form = $(this);
      var url = "<?=URL::to('/').'/costing/changeQcStatusMultiple' ?>";

      swal({
        title: "Are you sure to "+status+" this product?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
      }).then(result => {

        if (result.value) {
          if(status == "accept") {
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
          if(status == "reject") {

            const { value: comment } = Swal.fire({
            title: 'Comment',
            input: 'textarea',
            showCancelButton: true,
            inputValidator: (value) => {
              if (!value) {
                return 'This field is required!'
              }
              else {
                 $.ajax({
                    type: "GET",
                    url: url,
                    dataType: "json",
                    data: {
                      "_token": '{{ csrf_token() }}',
                      "status": status,
                      "chkCostingIds" :chkCostingIds,
                      "comment":value,
                    },
                    success: function(data) {
                        window.location.href = "<?=URL::to('/').'/costing/qcreject' ?>";
                        getqcCount();
                    }
                  });
                }
               }
            })

        }
      }
    });
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