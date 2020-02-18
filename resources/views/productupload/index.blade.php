@extends('layout.mainlayout')

@section('title', 'Product List')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('productupload.index') }}
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
                      <h5>{{'Product List'}}</h5>
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

                 
                    <!-- /.widget-heading -->
                  	<div class="widget-body clearfix">
                      <table class="table-head-box table-center table table-striped table-responsive checkbox checkbox-primary costing_product_table" id="costinglistTable">
                          	<thead>
                            	<tr class="bg-primary">
                                  
                                  <th>Image</th>
                                  <th>Sku</th>
                									<th>Batchno</th>
                              </tr>
                          	</thead>
                          	  <tbody>
	                          	
	                            @foreach ($costingdatas as $key => $costingdata)
	                            <?php $Image = URL::to('/') .'/'.$costingdata->image; 
                              	   	$costing_id = $costingdata->costingdata_id;
                                    $seive_size[$key] = explode(',',$costingdata->seive_size);
                  									$material_mm_size[$key] = explode(',',$costingdata->material_mm_size);
                  									$material_pcs[$key] = explode(',',$costingdata->material_pcs);
                  									$material_weight[$key] = explode(',',$costingdata->material_weight);
                  									$maxVal = max(count($seive_size[$key]),count($material_mm_size[$key]),count($material_pcs[$key]),count($material_weight[$key]));
								                  ?>
                                <tr>
                                  <td><img src="{{ $Image }}" class="img-fluid" height="120" width="120"/></td>
                                  <td>{{ $costingdata->sku }}</td>
                                  <td>{{ $costingdata->item }}</td>
                                  </tr>
                                @endforeach
                          	  </tbody>
                          	  <tfoot>
                                <tr>
                                  <th>Image</th>
                                  <th>Sku</th>
                                  <th>Batchno</th>
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

var dataTable = $('#costinglistTable').DataTable({
      "processing": true,
      "serverSide": true,
      "deferLoading": <?=$totalcount?>,
      "ajax":{
               "url": "<?=URL::to('/').'/productupload/indexResponse' ?>",
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
          $('.modal-body').html(data.html);
          $('.costing_showDetail').modal('show');
      }
   });
}

$('.costing_product_table').on('click','.qc_btn',function() {
  var status = $(this).attr("data-status");
  var id = $(this).attr("data-id");

  swal({
        title: "Are you sure you want to "+status+" this product?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
      }).then(result => {

      jQuery.ajax({
        type: "GET",
        url: "<?=URL::to('/').'/costing/changeQcStatus' ?>",
        data: {
        "_token": '{{ csrf_token() }}',
        "id": id,
        "status" : status,
        },
        success: function(data) {
            swal("Success!",data, "success");
            dataTable.ajax.reload();
            getqcCount();
          }
      });
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
        title: "Are you sure you want to "+status+" this product?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
      }).then(result => {

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
            swal("Success!",data.message, "success");
            dataTable.ajax.reload();
            getqcCount();
          }
      });

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