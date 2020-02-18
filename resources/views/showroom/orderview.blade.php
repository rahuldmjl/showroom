<?php
use App\Helpers\ShowroomHelper;
use App\Helpers\InventoryHelper;
use App\User;

$minmax = ShowroomHelper::getMinMaxPriceForFilter();
DB::setTablePrefix('dml_');
?>
@extends('layout.mainlayout')

@section('title', 'Showroom')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<style type="text/css">
  .dataTables_scrollHead{
    visibility: collapse !important;
}
.dataTables_scrollFoot{
    visibility: collapse !important;
}
</style>

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('showroom.orderview', $id) }}
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
					<div class="widget-body clearfix dataTable-length-top-0 showroom-orderview">
            <table class="table table-striped table-center mt-0 thumb-sm table-head-box scroll-lg custom-scroll" id="orderViewTable">
            <thead>
                <tr class="bg-primary">
                   <th>Img</th>
                   <th>Product Details</th>
                   <th>Product Qty</th>
                   <th>Product Total</th>
                   <th>Metal Quality</th>
                   <th>Metal Weight</th>
                   <th>Diamond Quality</th>
                   <th>Diamond Weight</th>
                   <th>Criteria</th>
                   <th>Vendor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderproductsdata as $key => $product)
                  <?php
$imageDirectory = config('constants.dir.website_url_for_product_image');
$product_image = ShowroomHelper::getProductImage($product->product_id);
if (!empty($product_image)) {
	$product_image = $imageDirectory . 'def_1.png';
} else {
	$product_image = $imageDirectory . 'def_1.png';
}
$metalQuality = InventoryHelper::getMetalQualityLabel($product->metal_quality);
?>
                  <tr>
                      <td><img alt="{{$product->product_id}}" src="{{$product_image}}" class="product-img"/></td>
                      <td>{{$product->sku}}<br>{{$product->certificate}}</td>
                      <td>{{ $product->qty }}</td>
                      <td>{{ $product->product_total }}</td>
                      <td>{{ $metalQuality }}</td>
                      <td>{{ $product->metal_weight }}</td>
                      <td>{{ $product->diamond_quality }}</td>
                      <td>{{ $product->diamond_weight }}</td>
                      <td>{{ $product->criteria_status }}</td>
                      <td><?php $vendor = User::find($ordersdata->vendor);?> {{ $vendor->name }}</td>
                  </tr>
                  @endforeach
            </tbody>
            <!-- <tfoot>
                <tr>
                   <th>Img</th>
                   <th>Product Details</th>
                   <th>Product Qty</th>
                   <th>Product Total</th>
                   <th>Metal Quality</th>
                   <th>Metal Weight</th>
                   <th>Diamond Quality</th>
                   <th>Diamond Weight</th>
                   <th>Criteria</th>
                   <th>Vendor</th>
                </tr>
            </tfoot> -->
        </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->
@endsection

@section('distinct_footer_script')

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">

var table = $('#orderViewTable').DataTable({
  //"scrollX": true,
  //"scrollXInner": true //Use This (Add This Line)
});
</script>
@endsection