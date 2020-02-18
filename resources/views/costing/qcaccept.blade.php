@extends('layout.mainlayout')
@section('title', 'Costing Accepted Product')
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
                      <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'Costing Accepted Product'}}</h5>
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
                                <option value="reject">Reject</option>
                                <option value="igi">IGI</option>
                                <option value="request_invoice">Request Invoice</option>
                                <option value="move_to_product">Move to Product</option>
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
                      <table class="table-head-box table-center table table-striped Qcacceptedtable checkbox checkbox-primary" id="qcAcceptedTable">
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
                                $costing_id = $costingdata->id;
                               $costraw = \App\Costing::where('costing_id',$costing_id)->first();
                              $seive_size[$key] = explode(',', $costingdata->seive_size);
                              $material_mm_size[$key] = explode(',', $costingdata->material_mm_size);
                              $material_pcs[$key] = explode(',', $costingdata->material_pcs);
                              $material_weight[$key] = explode(',', $costingdata->material_weight);
                              $maxVal = max(count($seive_size[$key]), count($material_mm_size[$key]), count($material_pcs[$key]), count($material_weight[$key]));
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
                                      <a href="javascript:void(0);"><i title="Detail" onclick="showDetail('<?php echo $costingdata->id; ?>')" class="material-icons list-icon">info</i></a>

                                      <a href="javascript:void(0);"><i title="Reject" data-vendor='<?= $costingdata->vendor_id; ?>'  data-id ='<?php echo $costingdata->id; ?>' class="material-icons list-icon qc_btn" id="reject">cancel</i></a>

                                      <a href="javascript:void(0);"><i title="IGI" data-id ='<?php echo $costingdata->id; ?>' class="material-icons list-icon qc_btn fa fa-certificate <?php if ($costingdata->is_igi == 1) {?> disabled <?php }?>" id="igi"></i></a>

                                      <a href="javascript:void(0);"><i title="Request invoice" data-id ='<?php echo $costingdata->id; ?>' class="material-icons list-icon qc_btn <?php if ($costingdata->request_invoice == 1) {?> disabled <?php }?>" id="request_invoice">open_in_new</i></a>

                                      <a href="javascript:void(0);"><i title="Move To Product" data-id ='<?php echo $costingdata->id; ?>' class="material-icons list-icon qc_btn" id="move_to_product">move_to_inbox</i></a>
                                  </td>
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

<div class="modal fade bs-modal-lg modal-color-scheme generateIGI_popup" tabindex="-1" id="generateIGI_popup" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">

          </div>
      </div>
  </div>

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
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.12.0/additional-methods.min.js"></script>
<script type="text/javascript">

var dataTable = $('#qcAcceptedTable').DataTable({
      "processing": true,
      "serverSide": true,
      "deferLoading": <?=$totalcount?>,
      "ajax":{
               "url": "<?=URL::to('/') . '/costing/qcacceptResponse'?>",
               "dataType": "json",
               "type": "GET",
               "data":{ _token: "{{csrf_token()}}"}
             },
              "columnDefs": [
                { "orderable": false, "targets": [0] }
            ]
  });
$('.Qcacceptedtable').wrap('<div class="Qcaccepted"></div>');

jQuery(document).on("click","#chkall_costing",function(){
  jQuery('input:checkbox').prop('checked', this.checked);
});


function getqcCount() {
  jQuery.ajax({
    url: "<?=URL::to('/') . '/costing/qccount'?>",
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
      url: "<?=URL::to('/') . '/costing/showDetail'?>",
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

$(document).on('click','.qc_btn',function() {
  var status = this.id;
  var id = $(this).attr("data-id");
  if(status == 'reject') {

    swal({
        title: "Are you sure to "+status+" this product?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
      }).then(result => {

        if (result.value) {
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
                url: "<?=URL::to('/') . '/costing/changeQcStatus'?>",
                data: {
                "_token": '{{ csrf_token() }}',
                "id": id,
                "status" : status,
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
        else {

        }
      });
  }
  if(status == 'move_to_product') {

    var url = "<?=URL::to('/') . '/costing/addProducts'?>";
    swal({
        title: "Are you sure to Move to Product?",
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
                "id" :{id},
              },
              success: function(data) {
                window.location.href = "<?=URL::to('/').'/costing/product_list' ?>";
                getqcCount();
              }
        });
      }

  });
  }
  if(status == 'igi') {
    var url = "<?=URL::to('/') . '/costing/generateIGI'?>";
    swal({
        title: "Are you sure to igi?",
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
                  "chkCostingIds" :id,
                },
                success: function(data) {
                  $('.modal-content').html(data.html);
                  $('#generateIGI_popup').modal('show');
                }
          });
      }

  });
  }
  if(status == 'request_invoice') {
    var url = "<?=URL::to('/') . '/costing/requestinvoiceByQc'?>";
    if(id != "") {


      swal({
        title: "Are you sure to request invoice for this product?",
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
              "chkCostingIds" :id,
            },
            success: function(data) {
              
              var query = { id:id }
              var url = "<?=URL::to('/') . '/costing/invoicepdf'?>?" + $.param(query);
              window.location = url;
              setTimeout(function(){ 
              window.location.href = "<?=URL::to('/').'/costing/qcrequestinvoice' ?>"; 
              }, 1200);
              getqcCount();
            }
          });
      }

    });

        }
      }
  });


$("#qcstatus_form").submit(function(e) {
  e.preventDefault();
  var vendor_id = $(this).attr("data-vendor");
  var chkCostingIds = [];
  $('input[name="chk_costing"]:checked').each(function() {
    chkCostingIds.push($(this).val());
  });

  var status = $("#qcstatus_drpdwn option:selected").val();
  if(status == "igi") {
    var url = "<?=URL::to('/') . '/costing/generateIGI'?>";
    swal({
        title: "Are you sure to igi?",
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
              "chkCostingIds" :chkCostingIds,
            },
            success: function(data) {
              $('.modal-content').html(data.html);
              $('#generateIGI_popup').modal('show');
            }
      });
    }

   });
  }


  var chkCostingIds = [];
  $('input[name="chk_costing"]:checked').each(function() {
    chkCostingIds.push($(this).val());
  });
  if(chkCostingIds != "") {
    var status = $("#qcstatus_drpdwn option:selected").val();
    if(status != "") {
      if(status == "reject") {
        var form = $(this);
        var url = "<?=URL::to('/') . '/costing/changeQcStatusMultiple'?>";

        swal({
        title: "Are you sure to reject?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
      }).then(result => {
        if (result.value) {
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
        });
    }

     if(status == 'move_to_product') {

        var form = $(this);
        var url = "<?=URL::to('/') . '/costing/addProducts'?>";

        swal({
        title: "Are you sure to Move to Product?",
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
              "id" :chkCostingIds,
            },
            success: function(data) {
                window.location.href = "<?=URL::to('/').'/costing/product_list' ?>"; 
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

      if(status == "request_invoice") {

        var url = "<?=URL::to('/') . '/costing/requestinvoiceByQc'?>";

        swal({
        title: "Are you sure to request invoice?",
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
                  getqcCount();
                }
          });
        }
      });
    }


    }
    else {
      swal("Please select action");
      return false;
    }
  }
  else {
    swal("Please select checkbox");
    return false;
  }




});
</script>

@endsection