<?php
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;
?>
@extends('layout.mainlayout')

@section('title', 'Sales Return')

@section('distinct_head')

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.1.7/css/ion.rangeSlider.min.css"/>
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('showroom.bulksalesreturn') }}
      <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div class="widget-list">
         <div class="row">
          <div class="col-md-12 widget-holder loader-area" style="display: none;">
            <div class="widget-bg text-center">
              <div class="loader"></div>
            </div>
          </div>
          <div class="col-md-12 widget-holder content-area">
              <div class="widget-bg">
                  <div class="widget-heading clearfix">
                      <h5 class="border-b-light-1 w-100 pb-1 mt-0 mb-2">Sales Return</h5>
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

                      <table class="table table-center table-head-box checkbox checkbox-primary nowrap" id="salesReturnProductTable" >
                          <thead>
                              <tr class="bg-primary">
                                  <th class="checkboxth"><label><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct"><span class="label-text"></span></label></th>
                                  <th>Image</th>
                                  <th>SKU</th>
                                  <th>Name</th>
                                  <th>Certificate</th>
                                  <th>Price</th>
                              </tr>
                          </thead>
                          <tbody>
                            @foreach ($orderItems as $key => $order)
                              @foreach ($order as $key => $item)
                              <?php
$orderId =  InventoryHelper::getOrderIdByItem($item->item_id);
$collection = InventoryHelper::getAllProductsCollection();
$product = InventoryHelper::getProductData($item->product_id);
$isReturned = isset($product->is_returned) ? $product->is_returned : 0;
$imageDirectory = config('constants.dir.website_url_for_product_image');
$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
$defaultProductImage = $imageDirectory . 'def_1.png';
?>
                              <tr>
                                <td><label><input class="form-check-input <?= ($isReturned) ? 'disabledChk' : 'chkProduct'?>" data-orderid="{{$orderId}}" data-id="{{$product->entity_id}}" value="{{$product->entity_id}}" type="checkbox" name="chkProduct[]" id="chkProduct{{$product->entity_id}}" <?= ($isReturned) ? 'disabled' : ''?>><span class="label-text"></label></td>
                                <td><img src="{{!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
                                <td>{{$product->certificate_no}}</td>
                                <td>{{$product->sku}}</td>
                                <td>{{$product->name}}</td>
                                <td>{{ShowroomHelper::currencyFormat(round($product->custom_price))}}</td>
                              </tr>
                             @endforeach
                             @endforeach
                          </tbody>
                          <tfoot>
                              <tr>
                                  <th><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct"></th>
                                  <th>Image</th>
                                  <th>SKU</th>
                                  <th>Name</th>
                                  <th>Certificate</th>
                                  <th>Price</th>
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
<!-- /.main-wrappper -->

<style>
.product-img{max-width: 40px;}
</style>
@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/dataTables.responsive.min.js"></script>
<script type="text/javascript">
var salesReturnProductTable = $('#salesReturnProductTable').DataTable({
  "dom": 'l<"#salesreturn-toolbar">frtip',
  "columnDefs": [
      { "orderable": false, "targets": [0,1] }
  ]
});
$divContainer = $('<div class="salesreturn-action-container"/>').appendTo('#salesreturn-toolbar');
$button = $('<button id="btn-generate-salesreturn" class="btn btn-primary ripple small-btn-style" type="button">Geneate Sales Return</button>').appendTo($divContainer);
$stateDropdown = $('<select class="mx-2 mr-3 height-35 float-left padding-four" id="state-list"/>').appendTo($divContainer);
$('<option />').val('').text('Select State').appendTo($stateDropdown);
var stateList = JSON.parse('<?= $stateList?>');
$.each(stateList, function( key, value ) {
  $('<option />').val(value.code).text(value.name).appendTo($stateDropdown);
});
$("#salesreturn-toolbar").addClass("submit-area d-inline-block");
$('.dataTables_filter input')
  .unbind() // Unbind previous default bindings
  .bind("input", function(e) { // Bind our desired behavior
      // If the length is 3 or more characters, or the user pressed ENTER, search
      if(this.value.length >= 3 || e.keyCode == 13) {
          // Call the API search function
          salesReturnProductTable.search(this.value).draw();
      }
      // Ensure we clear the search if they backspace far enough
      if(this.value == "") {
          salesReturnProductTable.search("").draw();
      }
      return;
});
$("#salesReturnProductTable .checkboxth").removeClass('sorting_asc');
$("#btn-generate-salesreturn").click(function(){
    var state = $('#state-list').find(":selected").val();
    var stateName = $('#state-list').find(":selected").text();
    var productIds = new Array();
    var orderIds = new Array();
    $.each(jQuery(".chkProduct:checked"), function() {
        productIds.push($(this).val());
        orderIds.push($(this).data('orderid'));
    });
    var ids = productIds.join(",");
    orderIds = orderIds.join(",");
    if(state == '')
    {
        swal({
            title: 'Are you sure?',
            text: "<?php echo Config::get('constants.message.showroom_state_not_selected_for_salesreturn'); ?>",
            type: 'error',
            showCancelButton: true,
            showConfirmButton: false
        });
    }
    if(ids == '')
    {
        swal({
            title: 'Are you sure?',
            text: "<?php echo Config::get('constants.message.inventory_generate_invoicememo_product_not_selected'); ?>",
            type: 'error',
            showCancelButton: true,
            showConfirmButton: false
        });
    }
    if(state != '' && ids != '')
    {
        $.ajax({
            type: 'post',
            url: '<?=URL::to('/showroom/generatesalesreturn');?>',
            data:{product_ids:ids,state:stateName,order_id:orderIds,_token:"{{ csrf_token() }}"},
            beforeSend: function(){
                showLoader();
                $("#btn-generate-salesreturn").prop('disabled',false);
            },
            success: function(response){
                hideLoader();
                $("#btn-generate-salesreturn").prop('disabled',true);
                var res = JSON.parse(response);
                if(res.status)
                {
                   swal({
                      title: 'Success',
                      text: res.message,
                      type: 'success',
                      buttonClass: 'btn btn-primary'
                    }).then(function() {
                       window.location.href = '<?=URL::to('showroom/salesreturnlist');?>';
                    });
                }
                else
                {
                  swal({
                      title: 'Oops!',
                      text: res.message,
                      type: 'error',
                      showCancelButton: true,
                      showConfirmButton: false,
                      confirmButtonClass: 'btn btn-danger',
                      cancelButtonText: 'Ok'
                    });
                }
            }
        });
    }
});
$("#chkAllProduct").click(function(){
    $('.chkProduct').prop('checked', this.checked);
});
</script>

@endsection