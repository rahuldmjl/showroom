@extends('layout.mainlayout')

@section('title', 'ISSUE VOUCHERS LIST')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/css/all.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">

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
                      <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">{{'ISSUE VOUCHERS LIST'}}</h5>

                      <div class="pull-right">

                      </div>
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

                      <table class="table table-striped table-center table-responsive issue_voucher_list" id="issue_voucher_list" >
                          <thead>
                              <tr class="bg-primary">
                                <th>Type</th>
                                 <th>Po Number</th>
                                 <th>Vendor Name</th>
                                 <th>Issue Date</th>
                                 <th>Voucher No</th>
                                 <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>

                          @foreach ($data as  $voucherdata)

                          <?php //echo "<pre>"; print_r($voucherdata); ?>
                          <tr>
                              <td>{{ ucwords($voucherdata->type) }}</td>
                              <td>
                                {{$voucherdata->gold_po}}
                              </td>
                              <td>{{$voucherdata->name}}
                              </td>
                              <td>{{$voucherdata->purchased_at}}</td>
                              <td><?php if ($voucherdata->is_voucher_no_generated == 1) {echo $voucherdata->gold_voucher_no;} else {echo "-";}?></td>
                              <!-- <td>{{$voucherdata->gold_voucher_no}}</td> -->

                              <?php

$filename = URL::to(Config::get('constants.dir.issue_vaucher') . $voucherdata->gold_voucher);
?>
                              <td>
                                <div class="testing" style="display: none;"><?php var_dump($voucherdata);?></div>
                                <a href="<?=$filename?>" target="_blank" class="color-content table-action-style" title="View"><i class="material-icons md-18">remove_red_eye</i></a>

                              <?php if ($voucherdata->is_voucher_no_generated == "1") {?>
                              <a href="{{route('diamondraw.voucher_download',['voucher'=>$voucherdata->gold_voucher,'voucher_type'=>$voucherdata->type])}}" class="color-content table-action-style" title="Download Voucher"><i class="material-icons md-18">file_download</i></a>
                              <?php }?>

                              <?php if ($voucherdata->type == "diamond") {

	if ($voucherdata->is_handover == "0") {?>
                              <a class="color-content table-action-style" title="Show" href="{{ route('diamond.edit_issue_voucher',$voucherdata->gold_voucher_no) }}"><i class="material-icons md-18">edit</i></a>
                            <?php }?>


                              <?php if ($voucherdata->wgt != "0") {?>
                              <a href="#" class="color-content table-action-style" title="Return"  onclick="returnDiamondIssue('<?php echo $voucherdata->gold_voucher_no; ?>')" ><i class="material-icons md-18">replay</i></a>
                              <?php }?>

                              <?php if ($voucherdata->is_voucher_no_generated == "0") {?>
                              <a href="#" class="color-content table-action-style" title="Generate Voucher no"  onclick="generateVoucherno('<?php echo $voucherdata->gold_voucher_no; ?>')" ><i class="fas fa-tag fs-16"></i></a>


                              <a href="#" class="color-content table-action-style" title="Delete Voucherno"  onclick="deleteVoucher('<?php echo $voucherdata->gold_voucher_no; ?>')" ><i class="material-icons md-18">delete</i></a>
                              <?php } elseif ($voucherdata->is_handover == "0") {?>

                              <a href="#" class="color-content table-action-style" title="Handover"  onclick="Handover('<?php echo $voucherdata->gold_voucher_no; ?>')" ><i class="fas fa-hand-holding fs-18"></i></a>
                              <?php }?>



                            <?php }if ($voucherdata->type == "Gold") {
	?>

                                <?php
if ($voucherdata->is_handover == "0") {?>
                              <a class="color-content table-action-style" title="Edit" href="{{ route('metals.edit_gold_issue_voucher',$voucherdata->gold_voucher_no) }}"><i class="material-icons md-18">edit</i></a>
                            <?php }?>

                            <?php if ($voucherdata->wgt != "0") {?>
                              <a href="#" class="color-content table-action-style" title="Return"  onclick="returnGoldIssue('<?php echo $voucherdata->gold_voucher_no; ?>')" ><i class="material-icons md-18">replay</i></a>
                              <?php }?>


                              <?php if ($voucherdata->is_voucher_no_generated == "0") {?>
                              <a href="#" class="color-content table-action-style" title="Generate Voucher no"  onclick="generateGoldVoucherno('<?php echo $voucherdata->gold_voucher_no; ?>')" ><i class="material-icons md-18">loyalty</i></a>


                              <a href="#" class="color-content table-action-style" title="Delete Voucherno"  onclick="deleteGoldVoucher('<?php echo $voucherdata->gold_voucher_no; ?>')" ><i class="material-icons md-18">delete</i></a>
                              <?php } elseif ($voucherdata->is_handover == "0") {?>

                              <a href="#" class="color-content table-action-style" title="Handover"  onclick="goldHandover('<?php echo $voucherdata->gold_voucher_no; ?>')" ><i class="fas fa-hand-holding fs-18"></i></a>
                              <?php }?>


                            <?php }?>
                            </td>
                          </tr>
                          @endforeach
                          </tbody>
                          <!-- <tfoot>
                            <tr>
                                <th>Type</th>
                                <th>Po Number</th>
                                 <th>Vendor Name</th>
                                 <th>Issue Date</th>
                                 <th>Voucher No</th>
                                 <th>Action</th>
                            </tr>
                          </tfoot> -->
                      </table>
                  </div>
                  <!-- /.widget-body -->
              </div>
              <!-- /.widget-bg -->
          </div>
          <!-- /.widget-body -->
    </div>
    <!-- /.row -->
  </div>
  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->
<div class="modal fade bs-modal-lg modal-color-scheme metals_returnGoldIssue" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog model-gold modal-lg">
  </div>
</div>

<div class="modal fade bs-modal-lg modal-color-scheme diamond_returnDiamondIssue" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog model-diamond modal-lg">
  </div>
</div>

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
  function deleteVoucher(id) {
    swal({
      title: 'Are you sure?',
      text: "<?php echo Config::get('constants.message.delete_confirmation'); ?>",
      type: 'info',
      showCancelButton: true,
      confirmButtonText: 'Confirm',
      confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
    }).then(function(data) {
      if (data.value) {
        jQuery.ajax({
          type: "GET",
          dataType: "json",
          url: "<?=URL::to('/') . '/diamond/deleteVoucher'?>",
          data: {
          "_token": '{{ csrf_token() }}',
          "id": id
          },
          success: function(response) {
            if(response.status == "true") {
              swal({
                title: 'Success!',
                text: response.message,
                type: 'success',
                confirmButtonClass: 'btn btn-success',
                cancelButtonText: "Cancel",
              }).then((value) => {
                if (value.value) {
                  window.location='<?=URL::to('/');?>'+'/diamondraw/issue_voucher_list';
                }
              });
            }
            else {
              swal({
                title: 'Oops!',
                  text: response.message,
                  type: 'error',
                  showCancelButton: true,
                  showConfirmButton: false,
                  confirmButtonClass: 'btn btn-danger',
                  cancelButtonText: 'Ok'
              }).catch(swal.noop);
            }
          }
        });
      }
    });
  }

function deleteGoldVoucher(id) {
 swal({
    title: 'Are you sure?',
    text: "<?php echo Config::get('constants.message.delete_confirmation'); ?>",
    type: 'info',
    showCancelButton: true,
    confirmButtonText: 'Confirm',
    confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
  }).then(function(data) {
    if (data.value) {
        jQuery.ajax({
          type: "GET",
          dataType: "json",
          url: "<?=URL::to('/') . '/metal/deleteGoldVoucher'?>",
          data: {
          "_token": '{{ csrf_token() }}',
          "id": id
        },
        success: function(response) {
          if(response.status == "true") {
            swal({
              title: 'Success!',
              text: response.message,
              type: 'success',
              confirmButtonClass: 'btn btn-success',
              cancelButtonText: "Cancel",
            }).then((data) => {
              if (data.value) {
                   window.location='<?=URL::to('/');?>'+'/diamondraw/issue_voucher_list';
              }
            });
           }else {
              swal({
                title: 'Oops!',
                text: response.message,
                type: 'error',
                showCancelButton: true,
                showConfirmButton: false,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonText: 'Ok'
              }).catch(swal.noop);
            }
          }
        });
      }
    });
  }
  function goldHandover(id) {
    swal({
      title: 'Are you sure?',
      text: "<?php echo Config::get('constants.message.handover_confirmation'); ?>",
      type: 'info',
      showCancelButton: true,
      confirmButtonText: 'Confirm',
      confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
    }).then(function(data) {
      if (data.value) {
        jQuery.ajax({
          type: "GET",
          dataType: "json",
          url: "<?=URL::to('/') . '/metal/goldhandover'?>",
          data: {
          "_token": '{{ csrf_token() }}',
          "id": id
          },
          success: function(response) {
            if(response.status == "true") {
              swal({
                title: 'Success!',
                text: response.message,
                type: 'success',
                confirmButtonClass: 'btn btn-success',
                cancelButtonText: "Cancel",
              }).then((value) => {
                 window.location='<?=URL::to('/');?>'+'/diamondraw/issue_voucher_list';
              });
            }else {
              swal({
                title: 'Oops!',
                text: response.message,
                type: 'error',
                showCancelButton: true,
                showConfirmButton: false,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonText: 'Ok'
              }).catch(swal.noop);
            }
          }
        });
      }
    });
  }
  function Handover(id) {
    swal({
      title: 'Are you sure?',
      text: "<?php echo Config::get('constants.message.handover_confirmation'); ?>",
      type: 'info',
      showCancelButton: true,
      confirmButtonText: 'Confirm',
      confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
    }).then(function(data) {
      if (data.value) {
        jQuery.ajax({
          type: "GET",
          dataType: "json",
          url: "<?=URL::to('/') . '/diamond/handover'?>",
          data: {
          "_token": '{{ csrf_token() }}',
          "id": id
          },
          success: function(response) {
            if(response.status == "true") {
              swal({
                title: 'Success!',
                text: response.message,
                type: 'success',
                confirmButtonClass: 'btn btn-success',
                cancelButtonText: "Cancel",
              }).then((value) => {
                window.location='<?=URL::to('/');?>'+'/diamondraw/issue_voucher_list';
              });
            }else {
              swal({
                title: 'Oops!',
                text: response.message,
                type: 'error',
                showCancelButton: true,
                showConfirmButton: false,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonText: 'Ok'
              }).catch(swal.noop);
            }
          }
        });
      }
    });
  }
  function generateGoldVoucherno(id) {
    swal({
      title:'Are you sure?',
      text:"<?php echo config::get('constants.message.voucherno_confirmation'); ?>",
      type:'info',
      showCancelButton:true,
      confirmButtonClass:'btn-confirm-all-productexcel btn btn-info'
    }).then(function(data) {
      if (data.value) {
        jQuery.ajax({
          type: "GET",
          dataType:"json",
          url:"<?=URL::to('/') . '/metal/generateGoldVoucherno'?>",
          data: {
            "_token":'{{ csrf_token() }}',
            'id':id
          },
          success: function(response) {
            if(response.status == "true") {
              swal({
                title: 'Success!',
                text: response.message,
                type: 'success',
                confirmButtonClass: 'btn btn-success',
                cancelButtonText: "Cancel",
              }).then((value) => {
                window.location='<?=URL::to('/');?>'+'/diamondraw/issue_voucher_list';
              });
            }else {
              swal({
                title: 'Oops!',
                text: response.message,
                type: 'error',
                showCancelButton: true,
                showConfirmButton: false,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonText: 'Ok'
              }).catch(swal.noop);
            }
          }
        })
      }
    })
  }

  function generateVoucherno(id) {
    swal({
      title: 'Are you sure?',
      text: "<?php echo Config::get('constants.message.voucherno_confirmation'); ?>",
      type: 'info',
      showCancelButton: true,
      confirmButtonText: 'Confirm',
      confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
    }).then(function(data) {
      if (data.value) {
        jQuery.ajax({
          type: "GET",
          dataType: "json",
          url: "<?=URL::to('/') . '/diamond/generateVoucherno'?>",
          data: {
          "_token": '{{ csrf_token() }}',
          "id": id
          },
          success: function(response) {
            if(response.status == "true") {
              swal({
                title: 'Success!',
                text: response.message,
                type: 'success',
                confirmButtonClass: 'btn btn-success',
                cancelButtonText: "Cancel",
              }).then((value) => {
                 window.location='<?=URL::to('/');?>'+'/diamondraw/issue_voucher_list';
              });
            }
            else {
              swal({
                title: 'Oops!',
                text: response.message,
                type: 'error',
                showCancelButton: true,
                showConfirmButton: false,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonText: 'Ok'
              }).catch(swal.noop);
            }
          }
        });
      }
    });
  }

  function returnGoldIssue(id) {
    jQuery.ajax({
      type: "GET",
      dataType: "json",
      url: "<?=URL::to('/') . '/metals/returnGoldIssue'?>",
      data: {
      "_token": '{{ csrf_token() }}',
      "id": id
      },
      success: function(data) {
        $('.model-gold').html(data.html);
        $('.metals_returnGoldIssue').modal('show');
      }
    });
  }

  function returnDiamondIssue(id) {
    jQuery.ajax({
      type:"GET",
      dataType:"json",
      url: "<?=URL::to('/') . '/diamond/returnDiamondIssue'?>",
      data: {
        "_token": '{{ csrf_token() }}',
        "id":id
      },
      success: function(data) {
          $('.model-diamond').html(data.html);
          $('.diamond_returnDiamondIssue').modal('show');
      }
    });
  }

  var table = $('#issue_voucher_list').DataTable({
    "dom": "<'row mb-2 align-items-center'<'col-auto dataTable-length-tb-0'l><'col'B><'col'f>><'row'<'col-md-12't>><'row'<'col-md-3'i><'col-md-6 ml-auto'p>>",
    "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
    'pageLength':10,
    "buttons": [
      {
        extend: 'csv',
        footer: false,
        title: 'Issue-Voucher-List-Data',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
            columns: [0,1,2,3,4],
            orthogonal: 'export'
        }
      },
      {
        extend: 'excel',
        footer: false,
        title: 'Issue-Voucher-List-Data',
        className: "btn btn-primary btn-sm px-3",
        exportOptions: {
            columns: [0,1,2,3,4],
            orthogonal: 'export'
        }
      }
    ],
    "lengthMenu": [[10, 50, 100, 200,500], [10, 50, 100, 200,500]],
    "order": [[ 3, "desc" ]],
    "deferLoading": <?=$TotalCountData?>,
    "serverSide": true,
    "serverMethod": "get",
    "ajax":{
      "url": "{{action('DiamondRawController@filter_issue_voucher')}}",
      "data": function(data, callback){
      data._token = "{{ csrf_token() }}";
        showLoader();
      },
      complete: function(response){
        hideLoader();
      }
    },"columnDefs": [{
      "targets": [5],
      "orderable": false
    }]
  });

  function deleterole(Id, token){
    swal({
      title: 'Are you sure?',
      text: "You won't be able to recover this role!",
      type: 'error',
      showCancelButton: true,
      confirmButtonClass: 'btn btn-danger',
      confirmButtonText: 'Yes, delete it!'
    }).then(function (data) {
      if (data.value) {
        console.log(token);
        var table = $('#DataTables_Table_0').DataTable();
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
</script>
@endsection