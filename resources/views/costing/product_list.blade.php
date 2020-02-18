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
    {{ Breadcrumbs::render('showroom.product_list') }}

      <!-- /.page-title-right -->
  </div>
  <div class="col-md-12 widget-holder loader-area" style="display: none;">
    <div class="widget-bg text-center">
      <div class="loader"></div>
    </div>
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
                      <h5 class="border-b-light-1 pb-1 mt-0 mb-2 w-100">{{'Product List'}}</h5>
                  </div>
                  <div class="widget-body clearfix dataTable-length-top-0">

                      @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif

                      <div class="delete_multiple">
                        <button class="btn" id="btn_delete_multiple" onclick="deleteProductMultiple()">Delete Products</button>
                      </div>

                       <table class="custom-scroll scroll-lg table table-striped table-hover table-cursor table-responsive table-head-box table-center checkbox checkbox-primary" style="overflow-x: auto;display: block;" id="productlisttable">
                          <thead>
                              <tr class="bg-primary">
                               
                               <th><label><input class="form-check-input" type="checkbox" name="chkall_product" id="chkall_product"><span class="label-text"></span></label></th>

                               <th>No</th>
                               <th>Style</th>
                               <th>item</th>
                               <th>Po no</th>
                               <th>Stone Use</th>
                               <th>Gross weight</th>
                               <th>Category</th>
                               <th>Total Amount</th>
                               <th>Certificate No</th>
                               <th>Sku</th>
                               <th>Metal Quality</th>
                               <th>Metal Weight</th>
                               <th>Metal Rate</th>
                               <th>Metal Labour Charge</th>
                               <th>Metal Amount</th>
                               <th>Stone Category</th>
                               <th>Stone Shape</th>
                               <th>Seive Size</th>
                               <th>MM size</th>
                               <th>Stone Seight</th>
                               <th>Stone Pcs</th>
                               <th>Stone Clarity</th>
                               <th>Stone Rate</th>
                               <th>Stone Amount</th>
                              <?php if( $role == "Super Admin") { ?>
                               <th>Action</th>
                               <?php } ?>
                               </tr>
                          </thead>
                          <tbody>
                           
                            <?php $counter = 1; $i=0;?>
                            @foreach($data as $rowkey => $datas)
                            <tr href="javascript:void(0)"  class="common_tr" data-id="<?php echo $datas['certificate_no']; ?>">
                              <td class="sorting_1">
                                <label><input type="checkbox" class="chk_product form-check-input chkProduct" name="chk_product" id="chk_product" value="<?= $datas['id'] ?>">
                                <span class="label-text"></span>
                                </label>
                              </td>
                              <td><?php echo ++$i; ?></td>
                              <td><?php echo (!empty($datas['style']) ? $datas['style'] : ''); ?></td>
                              <td><?php echo (!empty($datas['item']) ? $datas['item'] : ''); ?></td>
                              <td><?php echo (!empty($datas['po_no']) ? $datas['po_no'] : ''); ?></td>
                              <td><?php echo (!empty($datas['stone_use']) ? $datas['stone_use'] : ''); ?></td>
                              <td><?php echo (!empty($datas['gross_weight']) ? $datas['gross_weight'] : ''); ?></td>
                              <?php $category = App\Helpers\ProductHelper::_toGetCategoryVal($datas['categorys']['category_id']);?>
                              <td><?php echo (!empty($category) ? $category : ''); ?></td>
                              <td><?php echo (!empty($datas['total_amount']) ? $datas['total_amount'] : ''); ?></td>
                              <td><?php echo (!empty($datas['certificate_no']) ? $datas['certificate_no'] : ''); ?></td>
                              <td><?php echo (!empty($datas['sku']) ? $datas['sku'] : ''); ?></td>
                              <?php $val = App\Helpers\ProductHelper::_toGetMetalQualityValue($datas['metals']['metal_quality_id']);?>
                              <td><?php echo (!empty($val) ? $val : ''); ?></td>
                              <td><?php echo (!empty($datas['metals']['metal_weight']) ? $datas['metals']['metal_weight'] : ''); ?></td>
                              <td><?php echo (!empty($datas['metals']['metal_rate']) ? $datas['metals']['metal_rate'] : ''); ?></td>
                              <td><?php echo (!empty($datas['metals']['metal_labour_charge']) ? $datas['metals']['metal_labour_charge'] : ''); ?></td>
                              <td><?php echo (!empty($datas['metals']['metal_amount']) ? $datas['metals']['metal_amount'] : '') ?></td>
                              <td><?php echo (!empty($stoneElem[$rowkey]['stone_stone']) ? $stoneElem[$rowkey]['stone_stone'] : ''); ?></td>
                              <td><?php echo (!empty($stoneElem[$rowkey]['stone_shape']) ? $stoneElem[$rowkey]['stone_shape'] : ''); ?></td>
                              <td><?php echo (!empty($stoneElem[$rowkey]['seive_size']) ? $stoneElem[$rowkey]['seive_size'] : ''); ?></td>
                              <td><?php echo (!empty($stoneElem[$rowkey]['mm_size']) ? $stoneElem[$rowkey]['mm_size'] : ''); ?></td>
                              <td><?php echo (!empty($stoneElem[$rowkey]['carat']) ? $stoneElem[$rowkey]['carat'] : ''); ?></td>
                              <td><?php echo (!empty($stoneElem[$rowkey]['stone_use']) ? $stoneElem[$rowkey]['stone_use'] : ''); ?></td>
                              <td><?php echo (!empty($stoneElem[$rowkey]['stone_clarity']) ? $stoneElem[$rowkey]['stone_clarity'] : ''); ?></td>
                              <td><?php echo (!empty($stoneElem[$rowkey]['stone_rate']) ? $stoneElem[$rowkey]['stone_rate'] : ''); ?></td>
                              <td><?php echo (!empty($stoneElem[$rowkey]['stone_amount']) ? $stoneElem[$rowkey]['stone_amount'] : ''); ?></td>
                              <?php $costingid = $datas['id']; ?>
                              
                              <?php if( $role ==  Config::get('constants.role.super_admin')) { ?>
                              <td><a class="color-content table-action-style" href="{{ route('productupload.updateproduct',['id'=> $costingid ]) }}"><i class="material-icons md-18">edit</i></a>
                              <a id='deleteproduct' class="color-content table-action-style" href="javaScript:void(0)"  onclick="deleteProduct('<?php echo $costingid; ?>')"><i class="material-icons md-18">delete</i></a>
                              </td>
                              <?php } ?>

                              </tr>
                             <?php $counter++;?>
                            @endforeach
                          </tbody>
                      </table>
               </div>
              </div>
            </div>
          </div>
        </div>
</main>
<!-- /.main-wrappper -->
<div class="modal fade bs-modal-lg modal-color-scheme ProductDetail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
          <div class="modal-header text-inverse">
              <h5 class="modal-title" id="myLargeModalLabel">Product List</h5>
          </div>
  <div class="modal-body"> 
  </div>
  <div class="modal-footer"> 
      <button type="button" class="btn btn-danger btn-rounded ripple text-left" data-dismiss="modal">Close</button>
  </div>
</div>
<!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<div class="row" style="display: none;">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Users Management</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-success" href="{{ route('users.create') }}"> Create New User</a>
        </div>
    </div>
</div>

@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
$(document).ready( function() {

});
  var table = $('#productlisttable').DataTable({
      "columnDefs": [
        { "orderable": false, "targets": [0,6,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24] },
    ],
    'createdRow': function( row, data, dataIndex,certificate_no ) {
      $(row).addClass('common_tr');
      $(row).attr('data-id',data[8] );
    },
    "dom": 'l<"#inventory-toolbar">frtip',
    "language": {
      "infoEmpty": "No matched records found",
      "zeroRecords": "No matched records found",
      "emptyTable": "No data available in table",
      "sProcessing": "<div id='loader'></div>"
    },
    "deferLoading": <?=$totalcount?>,
    "processing": true,
    "serverSide": true,
    "pageLength": 10,
    "serverMethod": "post",
    "ajax":{
      "url": "<?=URL::to('/') . '/costing/productlistResponse'?>",
      "data": function(data, callback){
        data._token = "{{ csrf_token() }}";
        showLoader();
        $(".dropdown").removeClass('show');
        $(".dropdown-menu").removeClass('show');
      },
      complete: function(response){
        hideLoader();
      } 
    }
  });

  /*$(document).on('click','.common_tr',function() {
    var ids = $(this).attr('data-id');
    var type = 'get'; 
    var url ="{{action('CostingController@costingproductlist')}}";
    var token = '{{ csrf_token() }}';
    var id = ids;
    var modalview = "show";
    var list = ajaxdataproductlist(type,url,token,id,modalview);
  });*/

  function deleteProduct(id) {
    swal({
      title: 'Are you sure?',
      text: "<?php echo Config::get('constants.message.delete_product_confirmation'); ?>",
      type: 'info',
      showCancelButton: true,
      confirmButtonText: 'Confirm',
      confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
    }).then(function(data) {
      if (data.value) {
        jQuery.ajax({
          type: "GET",
          dataType: "json",
          url: "<?=URL::to('/') . '/productupload/deleteproduct'?>",
          data: {
          "_token": '{{ csrf_token() }}',
          "id": [id]
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
                  window.location='<?=URL::to('/');?>'+'/costing/product_list';
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

  function deleteProductMultiple(){
    var productids = [];
     $('.chk_product:checked').each(function() {
       productids.push($(this).val());
     });

     swal({
      title: 'Are you sure?',
      text: "<?php echo Config::get('constants.message.delete_product_confirmation'); ?>",
      type: 'info',
      showCancelButton: true,
      confirmButtonText: 'Confirm',
      confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
    }).then(function(data) {
      if (data.value) {
        jQuery.ajax({
          type: "GET",
          dataType: "json",
          url: "<?=URL::to('/') . '/productupload/deleteproduct'?>",
          data: {
          "_token": '{{ csrf_token() }}',
          "id": productids
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
                  window.location='<?=URL::to('/');?>'+'/costing/product_list';
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
</script>

@endsection
