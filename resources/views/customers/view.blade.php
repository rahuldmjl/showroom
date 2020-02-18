<?php
use App\ApprovalMemoHistroy;
use App\Helpers\CustomersHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ShowroomHelper;

$shapeArr = config('constants.enum.diamond_shape'); //get stone shape for accordian
$approvalType = config('constants.enum.customer_view_approval_type');

$inventoryStatus = InventoryHelper::getInventoryStatusOptions();
$inStatusVal = $inventoryStatus['in'];
$outStatusVal = $inventoryStatus['out'];
?>
@extends('layout.customerlayout')

@section('title', 'View Customer')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
    {{ Breadcrumbs::render('customers.view', $id) }}
    <!-- /.page-title-right -->
  </div>
  <!-- /.page-title -->
  <!-- =================================== -->
  <!-- Different data widgets ============ -->
  <!-- =================================== -->
  <div class="col-md-12 widget-holder loader-area" style="display: none;">
    <div class="widget-bg text-center">
      <div class="loader"></div>
    </div>
  </div>
  <div class="widget-list">
   <div class="row">
     <div class="col-md-12 widget-holder content-area view-customer-detail">
      <div class="widget-bg">
       <div class="widget-body clearfix">
        @if ($message = Session::get('success'))
        <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
          <i class="material-icons list-icon">check_circle</i>
          <strong>Success</strong>: {{ $message }}
        </div>
        @endif
        <input type="hidden" id="customer_id" value="<?=$id;?>">
          <!-- row start -->
          <div class="row customer-info-container hide-menu hidden" id="customer_detail">
          <!-- title column start -->
          <div class="col-md-12 title">
            <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Customer Information</h4>
          </div>
          <!-- content column start -->
          <div class="col-md-12 tab-content-style paragraph-m-zero d-flex">
            <div class="col">
              <h5>Personal Info <a class="pointer float-right text-primary" onclick="showEditPersonalInfoModal('<?=$id?>')">Edit</a></h5>
              <div class="col-inner personal-info-container">
                <p>Customer ID: <?=$id?></p>
                <p>Name: <?=$customerName?></p>
                <p>Email: <?=$customerEmail?></p>
                <p>Primary Contact: <?=$primaryContact?></p>
                <p>Secondary Contact: <?=$secondaryContact?></p>
                <p>Location: <?=str_replace('0', '', $location)?></p>
				<p>FRN Code: <?=str_replace('0', '', $frnCode)?></p>
              </div>
            </div>
            <div class="col">
              <h5>Default Billing Address <a class="pointer float-right text-primary" onclick="showEditAddressModal('<?=$id?>','billing_address')">Edit</a></h5>
              <div class="col-inner billing-address-container">
                <?php
$customerFirstName = isset($defaultBillingAddress['firstname']) ? $defaultBillingAddress['firstname'] : '';
$customerLastName = isset($defaultBillingAddress['lastname']) ? $defaultBillingAddress['lastname'] : '';
?>
                <p><?=$customerFirstName . ' ' . $customerLastName?></p>
                <p><?=isset($defaultBillingAddress['street']) ? $defaultBillingAddress['street'] : ''?></p>
                <p>
                  <?=isset($defaultBillingAddress['city']) ? $defaultBillingAddress['city'] . ', ' : ''?>
                  <?=isset($defaultBillingAddress['region']) ? $defaultBillingAddress['region'] . ', ' : ''?>
                  <?=isset($defaultBillingAddress['postcode']) ? $defaultBillingAddress['postcode'] : ''?>
                </p>
                <p><?=isset($defaultBillingAddress['country_id']) ? InventoryHelper::getCountryName($defaultBillingAddress['country_id']) : ''?></p>
                <p>T: <?=isset($defaultBillingAddress['telephone']) ? $defaultBillingAddress['telephone'] : ''?></p>
              </div>
            </div>
            <div class="w-100 d-block my-2 d-xl-none"></div>
            <div class="col">
              <h5>Default Shipping Address <a class="pointer float-right text-primary" onclick="showEditAddressModal('<?=$id?>','shipping_address')">Edit</a></h5>
              <div class="col-inner shipping-address-container">
                <?php
$customerFirstName = isset($defaultShippingAddress['firstname']) ? $defaultShippingAddress['firstname'] : '';
$customerLastName = isset($defaultShippingAddress['lastname']) ? $defaultShippingAddress['lastname'] : '';
?>
                <p><?=$customerFirstName . ' ' . $customerLastName?></p>
                <p><?=isset($defaultShippingAddress['street']) ? $defaultShippingAddress['street'] : ''?></p>
                <p>
                  <?=isset($defaultShippingAddress['city']) ? $defaultShippingAddress['city'] . ', ' : ''?>
                  <?=isset($defaultShippingAddress['region']) ? $defaultShippingAddress['region'] . ', ' : ''?>
                  <?=isset($defaultShippingAddress['postcode']) ? $defaultShippingAddress['postcode'] : ''?>
                </p>
                <p><?=isset($defaultShippingAddress['country_id']) ? InventoryHelper::getCountryName(isset($defaultShippingAddress['country_id']) ? $defaultShippingAddress['country_id'] : 'IN') : ''?></p>
                <p>T: <?=isset($defaultShippingAddress['telephone']) ? $defaultShippingAddress['telephone'] : ''?></p>
              </div>
            </div>
            <div class="col">
              <h5>GSTIN/PAN Card Info </h5>
              <div class="col-inner gst-pancard-container">
                <div class="d-flex justify-content-between align-items-center">
                  <span>GSTIN: <?=$gstinNumber?></span>
                  <span class="text-right">
                    <?php if (empty($gstinNumber) && empty($gstinAttachment)): ?>
                    <a class="text-white pointer" onclick="addGstinPan('<?=$id?>','gstin')">Add</a>
                    <?php else: ?>
                      <a class="text-white pointer" onclick="editGstinPan('<?=$id?>','gstin')">Edit</a>
                    <?php endif;?>
                    <?php if (!empty($gstinAttachment)): ?>
                      <a class="text-white pointer" onclick="viewAttachment('<?=$id?>','gstin')">View</a>
                    <?php endif;?>
                  </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span>PAN Card No: <?=$panCardNumber?></span>
                  <span class="text-right">
                    <?php if (empty($panCardNumber) && empty($panCardAtttachment)): ?>
                    <a class="text-white pointer" onclick="addGstinPan('<?=$id?>','pan_card')">Add</a>
                    <?php else: ?>
                      <a class="text-white pointer" onclick="editGstinPan('<?=$id?>','pan_card')">Edit</a>
                    <?php endif;?>
                    <?php if (!empty($panCardAtttachment)): ?>
                      <a class="text-white pointer" onclick="viewAttachment('<?=$id?>','pan_card')">View</a>
                    <?php endif;?>
                  </span>
                </div>
              </div>
            </div>
          </div>
		  <div class="col-md-12">
				<h4 class="fs-18 border-b-light-1 mt-3 mb-3 pb-2">Wallet Amount: <?= $walletAmount?></h4>
		  </div>
          </div>
		  
          <!-- Customer dashboard row start-->
          <div class="row  customer-info-container hide-menu" id="customer_dashboard">
              <div class="col-md-12 p-0 widget-holder content-area">
                  <div class="widget-bg p-0">
                      <div class="widget-body clearfix">
                          <div class="col-md-12 title">
                            <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Customer Dashboard</h4>
                          </div>
                          <div class="col-md-12 p-0 d-flex">
                              <div class="col-md-3 col-sm-6 widget-holder widget-full-height" id="customerdetail" onclick="showCustomerSection('customer_detail',this.id)">
                                  <div class="widget-bg bg-primary text-inverse pointer">
                                      <div class="widget-body">
                                          <div class="widget-counter">
                                              <h6>Customer Detail </h6>
                                              <h3 class="h1"><!-- <span class="counter"></span> --></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                              <!-- <h3 class="h1">&dollar;<span class="counter">741</span></h3><i class="material-icons list-icon">add_shopping_cart</i> -->
                                          </div>
                                          <!-- /.widget-counter -->
                                      </div>
                                      <!-- /.widget-body -->
                                  </div>
                                  <!-- /.widget-bg -->
                              </div>
                              <div class="col-md-3 col-sm-6 widget-holder widget-full-height" id="totalapprovals" onclick="showInventorySection('total-approvals',this.id)">
                                  <div class="widget-bg bg-color-scheme text-inverse pointer">
                                      <div class="widget-body clearfix">
                                          <div class="widget-counter">
                                              <h6>Approval Products </h6>
                                              <h3 class="h1"><span class="counter"><?php echo isset($approvalProductCollection['totalCount']) ? $approvalProductCollection['totalCount'] : 0; ?></span></h3><i class="material-icons list-icon">event_available</i>
                                          </div>
                                          <!-- /.widget-counter -->
                                      </div>
                                      <!-- /.widget-body -->
                                  </div>
                                  <!-- /.widget-bg -->
                              </div>
                              <div class="col-md-3 col-sm-6 widget-holder widget-full-height" id="totalinvoices" onclick="showCustomerSection('total-invoices',this.id)">
                                  <div class="widget-bg bg-primary text-inverse pointer">
                                      <div class="widget-body">
                                          <div class="widget-counter">
                                              <h6>Sold Products </h6>
                                              <h3 class="h1"><span class="counter"><?php echo $totalSoldProductsCount; ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                              <!-- <h3 class="h1">&dollar;<span class="counter">741</span></h3><i class="material-icons list-icon">add_shopping_cart</i> -->
                                          </div>
                                          <!-- /.widget-counter -->
                                      </div>
                                      <!-- /.widget-body -->
                                  </div>
                                  <!-- /.widget-bg -->
                              </div>
                              <div class="col-md-3 col-sm-6 widget-holder widget-full-height" >
                                  <div class="widget-bg bg-color-scheme text-inverse pointer">
                                      <div class="widget-body clearfix">
                                          <div class="widget-counter">
                                              <h6>Total Amount </h6>
                                              <h3 class="h1"><span class="counter"><?php echo ShowroomHelper::currencyFormatWithoutIcon(round($totalPaidAmount + $totalUnPaidAmount)); ?></span></h3><i class="material-icons list-icon">event_available</i>
                                          </div>
                                          <!-- /.widget-counter -->
                                      </div>
                                      <!-- /.widget-body -->
                                  </div>
                                  <!-- /.widget-bg -->
                              </div>
                          </div>
                          <div class="col-md-12 p-0 d-flex">
                                <div class="col-md-3 col-sm-6 widget-holder widget-full-height" id="quotationdata" onclick="showCustomerSection('quotation',this.id)">
                                    <div class="widget-bg bg-primary text-inverse pointer">
                                        <div class="widget-body">
                                            <div class="widget-counter">
                                                <h6>Quotation </h6>
                                                <h3 class="h1"><span class="counter"><?php echo $totalQuotationCount; ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                                <!-- <h3 class="h1">&dollar;<span class="counter">741</span></h3><i class="material-icons list-icon">add_shopping_cart</i> -->
                                            </div>
                                            <!-- /.widget-counter -->
                                        </div>
                                        <!-- /.widget-body -->
                                    </div>
                                    <!-- /.widget-bg -->
                                </div>
                                <div class="col-md-3 col-sm-6 widget-holder widget-full-height">
                                    <div class="widget-bg bg-color-scheme text-inverse pointer">
                                        <div class="widget-body clearfix">
                                            <div class="widget-counter">
                                                <h6>Orders </h6>
                                                <h3 class="h1"><span class="counter">111</span></h3><i class="material-icons list-icon">event_available</i>
                                            </div>
                                            <!-- /.widget-counter -->
                                        </div>
                                        <!-- /.widget-body -->
                                    </div>
                                    <!-- /.widget-bg -->
                                </div>
                                <div class="col-md-3 col-sm-6 widget-holder widget-full-height" onclick="showCustomerSection('sales-return',this.id)" id="salesreturn">
                                    <div class="widget-bg bg-primary text-inverse pointer">
                                        <div class="widget-body">
                                            <div class="widget-counter">
                                                <h6>Sales Return </h6>
                                                <h3 class="h1"><span class="counter"><?php echo $totalSalesReturnCount; ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                                <!-- <h3 class="h1">&dollar;<span class="counter">741</span></h3><i class="material-icons list-icon">add_shopping_cart</i> -->
                                            </div>
                                            <!-- /.widget-counter -->
                                        </div>
                                        <!-- /.widget-body -->
                                    </div>
                                    <!-- /.widget-bg -->
                                </div>
                                <div class="col-md-3 col-sm-6 widget-holder widget-full-height" onclick="showCustomerSection('credit-note',this.id)" id="creditnote">
                                    <div class="widget-bg bg-color-scheme text-inverse pointer">
                                        <div class="widget-body">
                                            <div class="widget-counter">
                                                <h6>Credit Note </h6>
                                                <h3 class="h1"><span class="counter"><?php echo $totalCreditNoteCount; ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                                <!-- <h3 class="h1">&dollar;<span class="counter">741</span></h3><i class="material-icons list-icon">add_shopping_cart</i> -->
                                            </div>
                                            <!-- /.widget-counter -->
                                        </div>
                                        <!-- /.widget-body -->
                                    </div>
                                    <!-- /.widget-bg -->
                                </div>
                          </div>
                      </div>
                  </div>
              </div>

            <div class="col-md-12 widget-holder content-area p-0 d-flex customer-info-container customer-statatics-container">
                <div class="col-md-6 col-sm-6 widget-holder widget-full-height">
                    <div class="widget-bg p-0">
                        <div class="widget-body">
                            <div class="widget-counter">
                                <h6>Products Statastics</h6>
                                <?php
$approvalProductsCount = isset($approvalProductCollection['totalCount']) ? $approvalProductCollection['totalCount'] : 0;
$soldProductCount = $totalSoldProductsCount;
?>
                                <p>Total Products: <?php echo (int) $approvalProductsCount + (int) $soldProductCount + (int) $salesReturnProductsCount ?></p>
                                <canvas id="products_statatics" height="150"></canvas>
                            </div>
                            <!-- /.widget-counter -->
                        </div>
                        <!-- /.widget-body -->
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 widget-holder widget-full-height">
                    <div class="widget-bg p-0">
                        <div class="widget-body">
                            <div class="widget-counter">
                                <h6>Total Amount Statastics</h6>
                                <p>Total Amount: <?php echo ShowroomHelper::currencyFormatWithoutIcon(round($totalPaidAmount + $totalUnPaidAmount)); ?></p>
                                <canvas id="total_amount_statatics" height="150"></canvas>
                            </div>
                            <!-- /.widget-counter -->
                        </div>
                        <!-- /.widget-body -->
                    </div>
                </div>
            </div>
          </div>
          <!-- Customer dashboard row end-->
        <!-- row start -->
        <div class="row hidden customer-info-container" id="customer-inventory">
          <!-- title column start -->
          <div class="col-md-12 title">
            <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Customer Inventory</h4>
          </div>
          <!-- content column start -->
          <div class="col-md-12">
            <?php
$customerInventoryCount = isset($customerInventory['totalCount']) ? $customerInventory['totalCount'] : 10;
$inventoryData = isset($customerInventory['productCollection']) ? $customerInventory['productCollection'] : '';
?>
            <?php if ($customerInventoryCount > 0): ?>
                <table class="table table-striped table-center table-head-box checkbox checkbox-primary custom-scroll" id="inventoryProductsTable">
                  <thead>
                    <tr class="bg-primary">
                      <th class="checkboxth">Image</th>
                      <th>SKU</th>
                      <th>Certificate</th>
                      <th>Category</th>
                      <th>Diamond Quality</th>
                      <th>Virtual Product Position</th>
                      <th>Price</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
$imageDirectory = config('constants.dir.website_url_for_product_image');
$defaultProductImage = $imageDirectory . 'def_1.png';
?>
                    @foreach ($inventoryData as $key => $product)
                    <?php
$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);

$position = strpos($product->sku, ' ');
$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
$categoryName = isset($categoryNames[0]->category_name) ? $categoryNames[0]->category_name : '';
$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
$status = 'N/A';
if (isset($product->purchased_as) && $product->purchased_as == 'purchased') {
	$status = 'Purchased';
} else if (isset($product->purchased_as) && $product->purchased_as == 'approval') {
	$status = 'Approval';
} else if (isset($product->purchased_as) && $product->purchased_as == 'return_memo') {
	$status = 'Return';
} else if (isset($product->purchased_as) && $product->purchased_as == 'sales_return') {
	$status = 'Sales Return';
}
?>
                    <tr>
                      <td><img src="{{!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
                      <td><?=$sku?></td>
                      <td>{{$product->certificate_no}}</td>
                      <td>{{$categoryName}}</td>
                      <td><?php echo !empty($product->rts_stone_quality) ? $product->rts_stone_quality : '-'; ?></td>
                      <td>{{$virtualproductposition}}</td>
                      <td>{{ShowroomHelper::currencyFormat(round($product->custom_price))}}</td>
                      <td>{{$status}}</td>
                    </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <th class="checkboxth">Image</th>
                      <th>SKU</th>
                      <th>Certificate</th>
                      <th>Category</th>
                      <th>Diamond Quality</th>
                      <th>Virtual Product Position</th>
                      <th>Price</th>
                      <th>Status</th>
                    </tr>
                  </tfoot>
                </table>
              <?php else: ?>
                <p><?php echo Config::get('constants.message.customer_inventory_product_not_available'); ?></p>
              <?php endif;?>
            </div>
          </div>
          <!-- row start -->
          <div class="row hidden customer-info-container" id="total-invoices">
            <!-- title column start -->
            <div class="col-md-12 title">
              <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Total Number of Invoice</h4>
            </div>
            <!-- content column start -->
            <div class="col-md-12">
              <?php
$totalInvoiceCount = isset($invoiceCollection['totalCount']) ? $invoiceCollection['totalCount'] : 10;
$invoiceData = isset($invoiceCollection['invoiceCollection']) ? $invoiceCollection['invoiceCollection'] : '';
?>
              <?php if (count($invoiceData) > 0): ?>
                <div class="table-responsive" style="overflow-x: hidden;">
                  <table class="table table-striped table-center" id="invoiceListTable" >
                    <thead>
                      <tr class="bg-primary">
                        <th>Invoice No.</th>
                        <th>Name</th>
                        <th>DMLUSERCODE</th>
                        <th>Date</th>
                        <th>Grand Total</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
$totalGrandTotalPrice = 0;
$totalDiscountAmount = 0;
$price = 0;
?>
                      @foreach ($invoiceData as $key => $invoice)
                      <?php
$customerName = InventoryHelper::getCustomerName($invoice->customer_id);
$invoiceDate = isset($invoice->invoice_created_date) ? date('d-m-Y', strtotime($invoice->invoice_created_date)) : '';
$invoiceNumber = isset($invoice->invoice_number) ? $invoice->invoice_number : '';
$customerId = isset($invoice->customer_id) ? $invoice->customer_id : '';
$finalGrandTotal = '';
$orderGrandTotal = isset($invoice->grand_total) ? $invoice->grand_total : 0;
if (isset($invoice->gst_percentage) && !empty($invoice->gst_percentage)) {
	$invoiceGstPercentage = $invoice->gst_percentage;
} else {
	$invoiceGstPercentage = 3;
}
$shippingCharge = isset($invoice->invoice_shipping_charge) ? $invoice->invoice_shipping_charge : 0;
$invoiceItems = InventoryHelper::getInvoiceItems($invoice->invoice_ent_id);
foreach ($invoiceItems as $key => $invoiceItem) {
	$price = $invoiceItem->price;
	$totalGrandTotalPrice += isset($invoiceItem->price) ? $invoiceItem->price : 0;
	$discountAmount = isset($invoiceItem->discount_amount) ? $invoiceItem->discount_amount : 0;
	$totalDiscountAmount += $discountAmount;
}
$totalInvoiceValue = ($totalGrandTotalPrice - $totalDiscountAmount);
$totalInvoiceValue += $shippingCharge;
$gstTotal = ($totalInvoiceValue * ($invoiceGstPercentage / 100));
//echo $gstTotal;exit;
$totalInvoiceValue += round($gstTotal, 2);
$finalGrandTotal = $totalGrandTotalPrice;

$orderItems = InventoryHelper::getOrderItems($invoice->entity_id);

?>

                      <tr>
                        <td>{{$invoiceNumber}}</td>
                        <td>{{$customerName}}</td>
                        <td>DML{{$customerId}}</td>
                        <td>{{$invoiceDate}}</td>
                        <td><?=ShowroomHelper::currencyFormat(intval($totalInvoiceValue))?></td>
                        <td>
                          <a title="View Invoice" target="_blank" class="color-content table-action-style1" href="{{ route('viewinvoice',['id'=>$invoice->invoice_ent_id]) }}"><i class="list-icon fa fa-book"></i></a>
                          <?php if (count($orderItems) > 0): ?>
                            <a title="Download Excel" target="_blank" class="color-content table-action-style1 pointer downloadexcel" data-id="{{$invoice->invoice_ent_id}}"><i class="list-icon fa fa-file-excel-o"></i></a>
                          <?php endif;?>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                <?php else: ?>
                  <p><?php echo Config::get('constants.message.customer_invoice_not_available'); ?></p>
                <?php endif;?>
              </div>
            </div>
            <!-- row start -->
            <div class="row hidden customer-info-container" id="total-approvals">
              <!-- title column start -->
              <div class="col-md-12 title">
                <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Approvals</h4>
              </div>
              <div class="col-md-12">
                  <div class="form-group row">
                    <label class="col-md-2 col-form-label" for="approval_filter">Filter By Type</label>
                    <div class="col-lg-4 col-md-4 col-sm-12 p-1">
                        <select id="approval_filter" class="form-control">
                            <option value=''>Select</option>
                            <option value='long_term'>Long Term</option>
                            <option value='short_term'>Short Term</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-md-3 p-1">
                        <button type="button" id="btn-filter-approval" class="btn btn-primary ripple"> Apply</button>
                    </div>
                  </div>
              </div>
              <!-- content column start -->
              <div class="col-md-12">
                <div class="tabs w-100">
                  <ul class="nav nav-tabs" id="approval-tab">
                    <?php foreach ($approvalType as $key => $type): ?>
                      <li class="nav-item"><a class="nav-link <?=($key == 'oldest_approval') ? 'active' : ''?>" href="#<?=$key?>" data-toggle="tab"><?=$type?></a></li>
                    <?php endforeach;?>
                      <li class="nav-item" id="approval-products-tab"><a class="nav-link" href="#approval_products" data-toggle="tab">No of Approval products</a></li>
                  </ul>
                  <div class="tab-content p-2 border border-top-0">
                    <?php
foreach ($approvalType as $typeKey => $type): ?>
                      <div class="tab-pane <?=($typeKey == 'oldest_approval') ? 'active' : ''?>" id="<?=$typeKey?>">
                        <?php
$approvalCount = isset($approvalMemoCollection['memoCount'][$typeKey]) ? $approvalMemoCollection['memoCount'][$typeKey] : '';
$approvalMemoData = isset($approvalMemoCollection['memoCollection'][$typeKey]) ? $approvalMemoCollection['memoCollection'][$typeKey] : '';
?>
                        <?php if ($approvalCount > 0): ?>
                          <div class="table-responsive" style="overflow-x: hidden;">
                            <table class="table table-striped table-center memoListTable <?=$typeKey;?> table-head-box checkbox checkbox-primary" id="memoListTable_<?=$typeKey;?>">
                              <thead>
                                <tr class="bg-primary">
                                  <th class="checkboxth"><label><input class="form-check-input chkAppApproval" type="checkbox" name="chkAppApproval" data-type="memoListTable_<?=$typeKey;?>"><span class="label-text"></span></label></th>
                                  <th>Memo No.</th>
                                  <th>DMUSERCODE</th>
                                  <th>Qty</th>
                                  <th>Date</th>
                                  <th>Grand Total</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php 
                                // echo "<pre>";
                                // print_r($approvalMemoData);
                                ?>
                                @foreach ($approvalMemoData as $memoKey => $memo)
                                
                                <tr id="order_id_<?=$memo->id?>">
                                <?php
$orderDate = date('d-m-Y', strtotime($memo->created_at));
$customerName = InventoryHelper::getCustomerName($memo->customer_id);
//$orderItems = InventoryHelper::getOrderItems($memo->entity_id);
$productIds = explode(',', $memo->product_ids);
$currentYear = date('y',strtotime($memo->created_at));
if (isset($memo->is_for_old_data) && $memo->is_for_old_data == 'yes') {
	$memoNumber = isset($memo->approval_no) ? $memo->approval_no : '';
} else {
	$memoNumber = isset($memo->approval_no) ? $currentYear . '-' . ($currentYear + 1) . '/' . $memo->approval_no : '';
}

$customerId = isset($memo->customer_id) ? $memo->customer_id : '';
$grandTotal = 0;
foreach ($productIds as $key => $productId) {
	DB::setTablePrefix('');
	if ($productId) {
		$product = DB::table('catalog_product_flat_1')->select('custom_price')->where('entity_id', '=', DB::raw("'" . $productId . "'"))->get()->first();
		if ($product) {
			$grandTotal += (float) $product->custom_price;
		}
	}
}
if (empty($memo->approval_no)) {
    $memoNumber = '-';
  }
$grandTotal = ShowroomHelper::currencyFormat(round($grandTotal));
?>
                                  <td><label><input class="form-check-input chkApproval" data-id="{{$memo->id}}" value="{{$memo->id}}" type="checkbox" name="chkApproval[]" id="chkApproval{{$memo->id}}"><span class="label-text"></label></td>
                                  <td>{{$memoNumber}}</td>
                                  <td>DML{{$customerId}}</td>
                                  <td><?=round(count($productIds))?></td>
                                  <td>{{$orderDate}}</td>
                                  <td>{{$grandTotal}}</td>
                                  <td>
                                    <a title="Generate Memo" target="_blank" data-memoid="{{$memo->id}}" class="color-content table-action-style1 pointer btn-generate-approval <?=(!empty($memo->approval_no) || (InventoryHelper::isReturnMemoGenerated($memoNumber) == true)) ? 'disabled' : ''?>" <?=(!empty($memo->approval_no) || (InventoryHelper::isReturnMemoGenerated($memoNumber) == true)) ? 'disabled' : ''?>><i class="list-icon fa fa-file-text-o"></i></a>

                                    <a title="Cancel Memo" target="_blank" data-memoid="{{$memo->id}}" class="color-content table-action-style1 pointer btn-cancel-approval <?=(!empty($memo->approval_no) || (InventoryHelper::isReturnMemoGenerated($memoNumber) == true)) ? 'disabled' : ''?>"><i class="list-icon fa fa-trash-o" <?=(!empty($memo->approval_no) || (InventoryHelper::isReturnMemoGenerated($memoNumber) == true)) ? 'disabled' : ''?>></i></a>

                                    <a title="View Memo" target="_blank" class="color-content table-action-style1" href="{{ route('viewmemo',['id'=>$memo->id]) }}"><i class="list-icon fa fa-file-text-o"></i></a>

                                    <?php if (count($productIds) > 0): ?>
                                    <a title="Download Excel" target="_blank" data-id="<?=$memo->id?>" class="pointer color-content table-action-style1 downloadmemoexcel"><i class="list-icon fa fa-file-excel-o"></i></a>
                                    <?php endif;?>

                                    <?php
                                    DB::setTablePrefix('dml_');
                                    $isInvoiceGenerate = ApprovalMemoHistroy::select('id')->where('approval_memo_id', '=', DB::raw("$memo->id"))->where('status','!=',DB::raw("'invoice'"))->get()->count();
                                    DB::setTablePrefix('');
                                    ?>
                                    <a title="Generate Invoice" target="_blank" data-id="<?=$memo->id?>" class="pointer color-content table-action-style1 btn-generate-invoice <?php echo ($isInvoiceGenerate == 0) ? 'disabled' : ''?>" <?php echo ($isInvoiceGenerate == 0) ? 'disabled' : ''?>><i class="list-icon fa fa-file-excel-o"></i></a>
                                    <?php 
                                    DB::setTablePrefix('dml_');
                                    $isReturnMemoGenerated = ApprovalMemoHistroy::select('id')->where('approval_memo_id', '=', DB::raw("$memo->id"))->where('status','!=',DB::raw("'return_memo'"))->where('status','!=',DB::raw("'invoice'"))->get()->count();
                                    DB::setTablePrefix('');
                                    ?>
                                    <a title="<?= ($isReturnMemoGenerated == 0) ? 'Return memo already generated' : 'Generate Return Memo'?>" target="_blank" data-id="<?=$memo->id?>" class="pointer color-content table-action-style1 btn-generate-returnmemo <?php echo ($isReturnMemoGenerated == 0) ? 'disabled' : ''?> <?= empty($memo->approval_no) ? 'disabled' : ''?>" <?php echo ($isReturnMemoGenerated == 0) ? 'disabled' : ''?> <?= empty($memo->approval_no) ? 'disabled' : ''?> data-approval="<?= $memo->approval_no?>"><i class="list-icon fa fa-retweet"></i></a>
                                    <?php
                                    $generateReturnMemoFlag = true;
                                    if(!empty($memo->is_delivered) && empty($memo->approval_no) || ($memo->is_for_old_data == 'yes'))
                                    {
                                      $generateReturnMemoFlag = false;
                                    }
                                    if(InventoryHelper::isReturnMemoGenerated($memoNumber) == true)
                                    {
                                      $generateReturnMemoFlag = false;  
                                    }
                                    ?>
                                    <a title="Delivery" id="btn-delivery-<?= $memo->id?>" target="_blank" data-id="<?=$memo->id?>" class="pointer btn-deliver-memo color-content table-action-style1 <?php echo ((!empty($memo->is_delivered)) || InventoryHelper::isReturnMemoGenerated($memoNumber) == true) ? 'disabled' : ''?>" <?php echo ((!empty($memo->is_delivered)) || InventoryHelper::isReturnMemoGenerated($memoNumber) == true) ? 'disabled' : ''?>><i class="list-icon fa fa-truck"></i></a>
                                  </td>
                                </tr>
                                @endforeach
                              </tbody>
                            </table>
                          </div>
                          <?php else: ?>
                            <p><?php echo Config::get('constants.message.customer_approval_memo_not_available'); ?></p>
                          <?php endif;?>
                        </div>
                      <?php endforeach;?>
                      <div class="tab-pane" id="approval_products">
                        <?php
$totalProducts = isset($approvalProductCollection['totalCount']) ? $approvalProductCollection['totalCount'] : 10;
$approvalProducts = isset($approvalProductCollection['productCollection']) ? $approvalProductCollection['productCollection'] : '';
$approvalNumberData = isset($approvalProductCollection['approvalMemoNumbers']) ? $approvalProductCollection['approvalMemoNumbers'] : '';
?>
                        <?php if ($totalProducts > 0): ?>
                          <div class="table-responsive mt-3" style="overflow-x: hidden;">
                            <table class="table table-striped table-center table-head-box checkbox checkbox-primary" id="approvalProductsTable" style="overflow-x: auto;">
                              <thead>
                                <tr class="bg-primary">
                                  <th class="checkboxth"><label><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct"><span class="label-text"></span></label></th>
                                  <th class="">Image</th>
                                  <th>Name</th>
                                  <th>SKU</th>
                                  <th>Certificate</th>
                                  <th>Approval No</th>
                                  <th>Category</th>
                                  <th>Diamond Quality</th>
                                  <th>Virtual Product Position</th>
                                  <th>Price</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php
$imageDirectory = config('constants.dir.website_url_for_product_image');
$defaultProductImage = $imageDirectory . 'def_1.png';
?>
                                @foreach ($approvalProducts as $key => $product)
                                <?php
$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);

$position = strpos($product->sku, ' ');
$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
$categoryName = isset($categoryNames[0]->category_name) ? $categoryNames[0]->category_name : '';
$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
$approvalMemoNumber = isset($approvalNumberData[$product->entity_id]) ? $approvalNumberData[$product->entity_id] : '';

$product_approval_memo_generated = (!empty($product->approval_memo_generated) ? $product->approval_memo_generated : 0);
$product_approval_invoice_generated = (!empty($product->approval_invoice_generated) ? $product->approval_invoice_generated : 0);
$product_return_memo_generated = (!empty($product->return_memo_generated) ? $product->return_memo_generated : 0);
?>
                                <tr>
                                  <td><label><input class="form-check-input chkProduct" data-id="{{$product->entity_id}}" value="{{$product->entity_id}}" type="checkbox" name="chkProduct[]" id="chkProduct{{$product->entity_id}}"><span class="label-text"></label></td>
                                  <td><img src="{{!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
                                  <td>{{$product->product_name}}</td>
                                  <td><?=$sku?></td>
                                  <td>{{$product->certificate_no}}</td>
                                  <td>{{$approvalMemoNumber}}</td>
                                  <td>{{$categoryName}}</td>
                                  <td><?php echo !empty($product->rts_stone_quality) ? $product->rts_stone_quality : '-'; ?></td>
                                  <td>{{$virtualproductposition}}</td>
                                  <td>{{ShowroomHelper::currencyFormat(round($product->custom_price))}}</td>
                                  <td>
                                      <?php if ($product_approval_invoice_generated == '1'): ?>
                                          <select class="form-control h-auto w-auto mx-auto inventory_action">
                                              <option value="">Select</option>
                                              <option value="invoice" disabled data-productid="<?=$product->entity_id?>">Generate Invoice</option>
                                               <option value="returnmemo" disabled data-productid="<?=$product->entity_id?>">Generate Return Memo</option>
                                          </select>
                                      <?php elseif ($product_approval_memo_generated == '1' && $product_return_memo_generated == '0'): ?>
                                          <select class="form-control h-auto w-auto mx-auto inventory_action">
                                              <option value="">Select</option>
                                              <option value="invoice" data-productid="<?=$product->entity_id?>">Generate Invoice</option>
                                              <option value="returnmemo" data-productid="<?=$product->entity_id?>">Generate Return Memo</option>
                                          </select>
                                      <?php elseif ($product_return_memo_generated == '1'): ?>
                                          <select class="form-control h-auto w-auto mx-auto inventory_action">
                                              <option value="">Select</option>
                                              <option value="invoice" data-productid="<?=$product->entity_id?>">Generate Invoice</option>
                                              <option value="returnmemo" disabled data-productid="<?=$product->entity_id?>">Generate Return Memo</option>
                                          </select>
                                      <?php else: ?>
                                          <select class="form-control h-auto w-auto mx-auto inventory_action">
                                              <option value="">Select</option>
                                              <option value="invoice" data-productid="<?=$product->entity_id?>">Generate Invoice</option>
                                              <option value="returnmemo" data-productid="<?=$product->entity_id?>">Generate Return Memo</option>
                                          </select>
                                      <?php endif;?>
                                  </td>
                                </tr>
                                @endforeach
                              </tbody>
                              <tfoot>
                                <tr>
                                  <th><input class="form-check-input" type="checkbox" name="chkAllProduct" id="chkAllProduct"></th>
                                  <th>Image</th>
                                  <th>Name</th>
                                  <th>SKU</th>
                                  <th>Certificate</th>
                                  <th>Approval No</th>
                                  <th>Category</th>
                                  <th>Diamond Quality</th>
                                  <th>Virtual Product Position</th>
                                  <th>Price</th>
                                  <th>Action</th>
                                </tr>
                              </tfoot>
                            </table>
                          </div>
                          <?php else: ?>
                            <p><?php echo Config::get('constants.message.customer_approval_products_not_available'); ?></p>
                          <?php endif;?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- row start -->
                <div class="row hidden customer-info-container" id="total-return-products">
                  <!-- title column start -->
                  <div class="col-md-12 title">
                    <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">No Of Return Products</h4>
                  </div>
                  <!-- content column start -->
                  <div class="col-md-12">
                    <?php
$totalReturnedProducts = isset($returnProductCollection['totalCount']) ? $returnProductCollection['totalCount'] : 10;
$returnedMemoProductsData = isset($returnProductCollection['returnMemoProductCollection']) ? $returnProductCollection['returnMemoProductCollection'] : '';
$returnMemoNumberData = isset($returnProductCollection['returnMemoNumbers']) ? $returnProductCollection['returnMemoNumbers'] : '';
$imageDirectory = config('constants.dir.website_url_for_product_image');
$defaultProductImage = $imageDirectory . 'def_1.png';
?>
                    <?php if ($totalReturnedProducts > 0): ?>
                      <div class="table-responsive" style="overflow-x: hidden;">
                        <table class="table table-striped table-center" id="returnedProductListTable" >
                          <thead>
                            <tr class="bg-primary">
                              <th class="checkboxth">Image</th>
                              <th>Name</th>
                              <th>SKU</th>
                              <th>Certificate</th>
                              <th>Return Memo No</th>
                              <th>Category</th>
                              <th>Diamond Quality</th>
                              <th>Virtual Product Position</th>
                              <th>Price</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($returnedMemoProductsData as $key => $product)
                            <?php
$product_image = $imageDirectory . ShowroomHelper::getProductImage($product->entity_id);
$position = strpos($product->sku, ' ');
$sku = html_entity_decode(substr_replace($product->sku, '&lt;br&gt;', $position, 0));
$categoryNames = ShowroomHelper::getCategoryNames($product->entity_id);
$categoryName = isset($categoryNames[0]->category_name) ? $categoryNames[0]->category_name : '';
$virtualproductposition = !empty(InventoryHelper::getVirtualProdPosition($product->certificate_no)) ? InventoryHelper::getVirtualProdPosition($product->certificate_no) : 'N/A';
$returnMemoNumber = isset($returnMemoNumberData[$product->entity_id]) ? $returnMemoNumberData[$product->entity_id] : '';
?>
                            <tr>
                              <td><img src="{{!empty(ShowroomHelper::getProductImage($product->entity_id)) ? $product_image: $defaultProductImage}}" class="product-img"/></td>
                              <td>{{$product->product_name}}</td>
                              <td><?=$sku?></td>
                              <td>{{$product->certificate_no}}</td>
                              <td>{{$returnMemoNumber}}</td>
                              <td>{{$categoryName}}</td>
                              <td><?php echo !empty($product->rts_stone_quality) ? $product->rts_stone_quality : '-'; ?></td>
                              <td>{{$virtualproductposition}}</td>
                              <td>{{ShowroomHelper::currencyFormat(round($product->custom_price))}}</td>
                            </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    <?php endif;?>
                  </div>
                </div>
                <!-- row start-->
                <div class="row hidden customer-info-container" id="sales-return">
                    <!-- title column start -->
                    <div class="col-md-12 title">
                      <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Sales Return</h4>
                    </div>
                    <!-- content column start -->
                    <div class="col-md-12">
                        <?php
$totalSalesReturn = isset($salesReturnList['totalCount']) ? $salesReturnList['totalCount'] : 10;
$salesReturnList = isset($salesReturnList['salesReturnCollection']) ? $salesReturnList['salesReturnCollection'] : '';
?>
                        <table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll" id="salesReturnList">
                            <thead>
                                <tr class="bg-primary">
                                    <th>Return No.</th>
                                    <th>Invoice No.</th>
                                    <th>Created Date</th>
                                    <th>Grand Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                              <?php
foreach ($salesReturnList as $key => $salesReturn) {
	$returnNumber = isset($salesReturn->sales_return_no) ? $salesReturn->sales_return_no : '';
	$invoiceNumber = isset($salesReturn->invoice_no) ? $salesReturn->invoice_no : '';
	$createdDate = isset($salesReturn->created_at) ? $salesReturn->created_at : '';
	$grandTotal = isset($salesReturn->total_invoice_value) ? ShowroomHelper::currencyFormat(round($salesReturn->total_invoice_value)) : '';
	$isCreditNoteGenerated = isset($salesReturn->is_credited) ? $salesReturn->is_credited : 'no';
	$generateCreditNoteClass = '';
	$viewCreditNoteClass = 'disabled';
	if (!empty($isCreditNoteGenerated) && $isCreditNoteGenerated == 'yes') {
		$generateCreditNoteClass = 'disabled';
		$viewCreditNoteClass = '';
	}
	?>
                                <tr>
                                    <td>{{$returnNumber}}</td>
                                    <td>{{$invoiceNumber}}</td>
                                    <td>{{$createdDate}}</td>
                                    <td>{{$grandTotal}}</td>
                                    <td>
                                      <!-- <a class="color-content table-action-style" href="{{ route('viewcreditpurchasenote',['id'=>$salesReturn->id]) }}">Credit Note Purchase</a> -->

                                      <a title="Generate Credit Note" class="color-content table-action-style btn-generate-creditnote pointer <?php echo $generateCreditNoteClass ?>" data-href="{{ route('generatecreditsalenote',['id'=>$salesReturn->id]) }}"><i class="material-icons">note_add</i></a>

                                      <a title="View Credit Note" class="color-content table-action-style <?php echo $viewCreditNoteClass; ?>" href="{{ route('viewcreditsalenote',['id'=>$salesReturn->id]) }}"><i class="material-icons">remove_red_eye</i></a>

                                      <!-- <a class="color-content table-action-style" href="{{ route('viewdebitpurchasenote',['id'=>$salesReturn->id]) }}">Credit Note Purchase</a> -->

                                      <!-- <a class="color-content table-action-style" href="{{ route('viewdebitsalenote',['id'=>$salesReturn->id]) }}">Credit Note Sale</a> -->
                                    </td>
                                  </tr>
                              <?php }?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Return No.</th>
                                    <th>Invoice No.</th>
                                    <th>Created Date</th>
                                    <th>Grand Total</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                          </table>
                    </div>
                </div>
                <!-- row end -->
                <!-- credit note start-->
                <div class="row hidden customer-info-container" id="credit-note">
                    <!-- title column start -->
                    <div class="col-md-12 title">
                      <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Credit Note</h4>
                    </div>
                    <!-- content column start -->
                    <div class="col-md-12">
                        <?php
$totalCreditNote = isset($creditNoteList['totalCount']) ? $creditNoteList['totalCount'] : 10;
$creditNoteList = isset($creditNoteList['creditNoteCollection']) ? $creditNoteList['creditNoteCollection'] : '';
?>
                        <table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll" id="creditNoteList">
                            <thead>
                                <tr class="bg-primary">
                                    <th>Return No.</th>
                                    <th>Invoice No.</th>
                                    <th>Created Date</th>
                                    <th>Grand Total</th>
                                    <th>Credited By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                              <?php
foreach ($creditNoteList as $key => $salesReturn) {
	$returnNumber = isset($salesReturn->sales_return_no) ? $salesReturn->sales_return_no : '';
	$invoiceNumber = isset($salesReturn->invoice_no) ? $salesReturn->invoice_no : '';
	$createdDate = isset($salesReturn->created_at) ? $salesReturn->created_at : '';
	$grandTotal = isset($salesReturn->total_invoice_value) ? ShowroomHelper::currencyFormat(round($salesReturn->total_invoice_value)) : '';
	$creditedBy = isset($salesReturn->credited_by) ? CustomersHelper::getUsername($salesReturn->credited_by) : '';
	$isCreditNoteGenerated = '';
	if (isset($salesReturn->is_credited) && $salesReturn->is_credited == 'no') {
		$isCreditNoteGenerated = 'disabled';
	}
	?>
                                <tr>
                                    <td>{{$returnNumber}}</td>
                                    <td>{{$invoiceNumber}}</td>
                                    <td>{{$createdDate}}</td>
                                    <td>{{$grandTotal}}</td>
                                    <td>{{$creditedBy}}</td>
                                    <td>
                                        <a class="color-content table-action-style <?php echo $isCreditNoteGenerated ?>" href="{{ route('viewcreditsalenote',['id'=>$salesReturn->id]) }}">View Credit Note</a>
                                    </td>
                                  </tr>
                              <?php }?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Return No.</th>
                                    <th>Invoice No.</th>
                                    <th>Created Date</th>
                                    <th>Grand Total</th>
                                    <th>Credited By</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                          </table>
                    </div>
                </div>
                <!-- credit note end-->
                <!-- row start -->
                <div class="row hidden customer-info-container" id="products-exchange">
                  <!-- title column start -->
                  <div class="col-md-12 title">
                    <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Exchange of products</h4>
                  </div>
                  <!-- content column start -->
                  <div class="col-md-12">
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                  </div>
                </div>
                <!-- row start -->
                <div class="row hidden customer-info-container" id="stock-order-templates">
                  <!-- title column start -->
                  <div class="col-md-12 title">
                    <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Templates for Stock & Online orders</h4>
                  </div>
                  <!-- content column start -->
                  <div class="col-md-12">
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                  </div>
                </div>
                <!-- row start -->
                <div class="row hidden customer-info-container" id="provision-performa">
                  <!-- title column start -->
                  <div class="col-md-12 title">
                    <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Provision Of Performa</h4>
                  </div>
                  <!-- content column start -->
                  <div class="col-md-12">
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                  </div>
                </div>
                <!-- row start -->
                <div class="row hidden customer-info-container" id="quotation">
                  <!-- title column start -->
                  <div class="col-md-12 title">
                    <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Quotation</h4>
                  </div>
                  <!-- content column start -->
                  <div class="col-md-12">
                    <?php if ($quotationCount > 0): ?>
                      <div class="accordion w-100" id="quotation-accordion" role="tablist" aria-multiselectable="true">
                        <div class="card card-outline-primary">
                          <div class="card-header" role="tab" id="heading4">
                            <h5 class="m-0"><a role="button" data-toggle="collapse" data-parent="#quotation-accordion" href="#diamond_data" aria-expanded="true" aria-controls="diamond_data">Diamond Detail</a></h5>
                          </div>
                          <!-- /.card-header -->
                          <div id="diamond_data" class="card-collapse collapse show" role="tabpanel" aria-labelledby="heading4">
                            <div class="card-body">
                              <div class="tabs w-100">
                                <ul class="nav nav-tabs">
                                  <?php $activeClass = 'active';foreach ($shapeArr as $key => $shape): ?>
                                  <?php
$stringIndex = strcspn($key, '0123456789');
list($start, $end) = preg_split('/(?<=.{' . $stringIndex . '})/', $key, 2);
$endChar = !empty($end) ? '-' . $end : '';
?>
                                  <?php if (isset($diamondShapeData[$key])): ?>
                                    <li class="nav-item <?php echo $activeClass ?>"><a class="nav-link" href="#<?php echo $key ?>_shape" data-toggle="tab"><?php echo ucfirst($start) . $endChar; ?></a></li>
                                  <?php endif;?>
                                  <?php $activeClass = '';endforeach;?>
                                </ul>
                                <div class="tab-content p-3 border border-top-0">
                                  <?php $activeClass = 'active';
//echo "<pre>";
//print_r($stoneRangeData);exit;
foreach ($shapeArr as $shapekey => $shape): ?>
                                    <div class="tab-pane <?php echo $activeClass; ?>" id="<?php echo $shapekey; ?>_shape">
                                      <?php if (isset($diamondShapeData[$shapekey])): ?>
                                        <?php foreach ($diamondShapeData[$shapekey] as $diamond) {
	$stoneQuality = '';
	$diamondShape = '';
	$rangeData = CustomersHelper::getCustomerStoneRangeData($diamond['diamondShape'], $diamond['stone_quality'], $id);
	?>
                                         <div class="form-group">
                                          <div class="col-12 px-0 stone-data-container">
                                            <h6 class="w-100 shape-title"><?php echo isset($diamond['diamondShape']) ? ucfirst($diamond['diamondShape']) : '' ?> (<?php echo isset($diamond['stone_quality']) ? $diamond['stone_quality'] : '' ?>)</h6>
                                            <div class="row m-0 py-3">
                                              <?php
$diamondShape = isset($rangeData->stone_shape) ? $rangeData->stone_shape : '';
	$stone_range_data = json_decode($rangeData->stone_range_data);
	$quotation = DB::table("quotation")->select("labour_charge")->where("id", "=", DB::raw("$rangeData->quotation_id"))->get()->first();
	$labour_charge = isset($quotation->labour_charge) ? json_decode($quotation->labour_charge) : '';
	?>
                                              <?php foreach ($stone_range_data->stone_range as $index => $stoneRange): ?>
                                                <div class="w-15 col-md px-1">
                                                  <label class="w-100 text-center" for="<?=$stoneRange . $diamond['stone_quality'] . $index?>"><?php echo $stoneRange ?></label>
                                                  <input type="text" class="form-control" name="stone_data[<?=isset($diamond['stone_quality']) ? $diamond['stone_quality'] : ''?>][stone_price][]" id="<?=$stoneRange . $diamond['stone_quality'] . $index?>" value="<?php echo isset($stone_range_data->stone_price[$index]) ? $stone_range_data->stone_price[$index] : '' ?>" readonly>
                                                </div>
                                              <?php endforeach;?>
                                            </div>
                                          </div>
                                        </div>
                                        <?php
}
?>
                                      <div class="form-group row p-3">
                                        <div class="w-15">
                                          <label class="text-center" for="txtlabourcharge_<?=$shapekey?>">Metal Labour Charge: </label>
                                          <input type="text" class="form-control" id="txtlabourcharge_<?=$shapekey?>" name="txtlabourcharge[<?=$shapekey?>][]" value="<?php echo isset($labour_charge->$shapekey[0]) ? $labour_charge->$shapekey[0] : '' ?>" readonly>
                                        </div>
                                      </div>
                                      <?php else: ?>
                                        <p>No products!</p>
                                      <?php endif;?>
                                    </div>
                                    <?php $activeClass = '';endforeach;?>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card-collapse -->
                          </div>
                          <!-- /.panel -->
                        </div>
                        <?php else: ?>
                          <p>No quotation available for this customer</p>
                        <?php endif;?>
                      </div>
                    </div>
                    <!-- row start -->
                    <div class="row hidden customer-info-container" id="invoice-discount-setting">
                      <!-- title column start -->
                      <div class="col-md-12 title">
                        <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Invoice discount settings</h4>
                      </div>
                      <!-- content column start -->
                      <div class="col-md-12">
                         {!! Form::open(array('route' => 'customers.invoicediscountstore','method'=>'POST', 'files'=>'true','id' => 'invoiceform')) !!}
                  <div class="row">
                    <div class="col-md-4 mb-4">
                      <div class="form-group">
                        <label for="txtfirstname">Discount Invoice Less 25k 14k(%)</label>
                        <div class="input-group">
                         <?php $discount_invoice_less_25 = CustomersHelper::getCustomerAttrValue($id, 'discount_invoice_less_25');?>
                         {!! Form::number('discount_invoice_less_25', $discount_invoice_less_25, ['class' => 'form-control number h-auto zertofive']) !!}
                         <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                         <input type="hidden" name="customerId" value="{{$id}}">
                       </div>
                     </div>
                   </div>
                   <div class="col-md-4 mb-4">
                     <div class="form-group">
                      <label for="txtfirstname">Discount Invoice Between 25k to 100k 14k(%)</label>
                      <div class="input-group">
                        <?php $discount_invoice_25_to_lakhs = CustomersHelper::getCustomerAttrValue($id, 'discount_invoice_25_to_lakhs');?>
                        {!! Form::number('discount_invoice_25_to_lakhs', $discount_invoice_25_to_lakhs, ['class' => 'form-control number h-auto fivetohund']) !!}
                        <input type="hidden" name="customerId" value="{{$id}}">
                        <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                   <div class="form-group">
                    <label for="txtfirstname">Discount Invoice Above 100k 14k(%)</label>
                    <div class="input-group">
                     <?php $discount_invoice_above_lakhs = CustomersHelper::getCustomerAttrValue($id, 'discount_invoice_above_lakhs');?>
                     {!! Form::number('discount_invoice_above_lakhs',$discount_invoice_above_lakhs, ['class' => 'form-control number h-auto hundton']) !!}
                     <input type="hidden" name="customerId" value="{{$id}}">
                     <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                   </div>
                 </div>
               </div>
             </div>
             <div class="row">
                    <div class="col-md-4 mb-4">
                      <div class="form-group">
                        <label for="txtfirstname">Discount Invoice Less 25k 18k(%)</label>
                        <div class="input-group">
                         <?php $discount_invoice_less_25_18k = CustomersHelper::getCustomerAttrValue($id, 'discount_invoice_less_25_18k');?>
                         {!! Form::number('discount_invoice_less_25_18k', $discount_invoice_less_25_18k, ['class' => 'form-control number h-auto zertofive']) !!}
                         <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                         <input type="hidden" name="customerId" value="{{$id}}">
                       </div>
                     </div>
                   </div>
                   <div class="col-md-4 mb-4">
                     <div class="form-group">
                      <label for="txtfirstname">Discount Invoice between 25k to 100k 18k(%)</label>
                      <div class="input-group">
                        <?php $discount_invoice_25_100k_18k = CustomersHelper::getCustomerAttrValue($id, 'discount_invoice_25_100k_18k');?>
                        {!! Form::number('discount_invoice_25_100k_18k', $discount_invoice_25_100k_18k, ['class' => 'form-control number h-auto fivetohund']) !!}
                        <input type="hidden" name="customerId" value="{{$id}}">
                        <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                   <div class="form-group">
                    <label for="txtfirstname">Discount Invoice Above 100k 18k(%)</label>
                    <div class="input-group">
                     <?php $discount_invoice_gt_100k_18k = CustomersHelper::getCustomerAttrValue($id, 'discount_invoice_gt_100k_18k');?>
                     {!! Form::number('discount_invoice_gt_100k_18k',$discount_invoice_gt_100k_18k, ['class' => 'form-control number h-auto hundton']) !!}
                     <input type="hidden" name="customerId" value="{{$id}}">
                     <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                   </div>
                 </div>
               </div>
             </div>
             <div class="row">
               <div class="col-md-5 mb-4">
                <button class="btn btn-primary" id="btn_save" type="submit">Submit</button>
                <button class="btn btn-outline-default" type="reset">Cancel</button>
              </div>
            </div>
            {!! Form::close() !!}
                      </div>
                    </div>
                    <!-- row start -->
                    <div class="row hidden customer-info-container" id="default-setting">
                      <!-- title column start -->
                      <div class="col-md-12 title">
                        <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Default settings</h4>
                      </div>
                      <!-- content column start -->
                      <div class="col-md-12">
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                      </div>
                    </div>
                    <!-- row start -->
                    <div class="row hidden customer-info-container" id="approval-product-discount">
                      <!-- title column start -->
                      <div class="col-md-12 title">
                        <h4 class="fs-18 border-b-light-1 mt-0 mb-4 pb-2">Approval product discount</h4>
                      </div>
                      <!-- content column start -->
                      <div class="col-md-12">
                        {!! Form::open(array('route' => 'customers.discountstore','method'=>'POST', 'files'=>'true','id' => 'myform')) !!}
                        <div class="row">
                          <div class="col-md-4 mb-4 ">
                            <div class="form-group">
                              <label for="txtfirstname">Discount Approval Less 25k 14k(%)</label>
                              <div class="input-group">
                               <?php $discount_approval_less_25 = CustomersHelper::getCustomerAttrValue($id, 'discount_approval_less_25');?>
                               {!! Form::number('discount_approval_less_25', $discount_approval_less_25, ['class' => 'form-control number h-auto zertofive']) !!}
                               <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                               <input type="hidden" name="customerId" value="{{$id}}">
                             </div>
                           </div>
                         </div>
                         <div class="col-md-4 mb-4">
                           <div class="form-group">
                            <label for="txtfirstname">Discount Approval Between 25k to 100k 14k(%)</label>
                            <div class="input-group">
                              <?php $discount_approval_25_to_lakhs = CustomersHelper::getCustomerAttrValue($id, 'discount_approval_25_to_lakhs');?>
                              {!! Form::number('discount_approval_25_to_lakhs', $discount_approval_25_to_lakhs, ['class' => 'form-control number h-auto fivetohund']) !!}
                              <input type="hidden" name="customerId" value="{{$id}}">
                              <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4 mb-4">
                         <div class="form-group">
                          <label for="txtfirstname">Discount Approval Above 100k 14k(%)</label>
                          <div class="input-group">
                           <?php $discount_approval_above_lakhs = CustomersHelper::getCustomerAttrValue($id, 'discount_approval_above_lakhs');?>
                           {!! Form::number('discount_approval_above_lakhs',$discount_approval_above_lakhs, ['class' => 'form-control number h-auto hundton']) !!}
                           <input type="hidden" name="customerId" value="{{$id}}">
                           <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                         </div>
                       </div>
                     </div>
                   </div>
                   <div class="row">
                      <div class="col-md-4 mb-4 ">
                        <div class="form-group">
                          <label for="txtfirstname">Discount Approval Less 25k 18k(%)</label>
                            <div class="input-group">
                              <?php $discount_approval_less_25_18k = CustomersHelper::getCustomerAttrValue($id, 'discount_approval_less_25_18k');?>
                              {!! Form::number('discount_approval_less_25_18k', $discount_approval_less_25_18k, ['class' => 'form-control number h-auto zertofive']) !!}
                              <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                                <input type="hidden" name="customerId" value="{{$id}}">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4 mb-4">
                          <div class="form-group">
                            <label for="txtfirstname">Discount Approval Between 25k to 100k 18k(%)</label>
                              <div class="input-group">
                                <?php $discount_approval_25_100k_18k = CustomersHelper::getCustomerAttrValue($id, 'discount_approval_25_100k_18k');?>
                                {!! Form::number('discount_approval_25_100k_18k', $discount_approval_25_100k_18k, ['class' => 'form-control number h-auto fivetohund']) !!}
                                <input type="hidden" name="customerId" value="{{$id}}">
                                <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                              </div>
                          </div>
                        </div>
                        <div class="col-md-4 mb-4">
                          <div class="form-group">
                            <label for="txtfirstname">Discount Approval Above 100k 18k(%)</label>
                              <div class="input-group">
                                <?php $discount_approval_gt_100k_18k = CustomersHelper::getCustomerAttrValue($id, 'discount_approval_gt_100k_18k');?>
                                {!! Form::number('discount_approval_gt_100k_18k',$discount_approval_gt_100k_18k, ['class' => 'form-control number h-auto hundton']) !!}
                                <input type="hidden" name="customerId" value="{{$id}}">
                                <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                              </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-5 mb-4">
                          <button class="btn btn-primary " id="btn_save" type="submit">Submit</button>
                          <button class="btn btn-outline-default" type="reset">Cancel</button>
                        </div>
                      </div>
                        {!! Form::close() !!}
                    </div>
                  </div>
              <!-- row start -->
              <div class="row hidden customer-info-container" id="deposit-product-discount">
                <!-- title column start -->
                <div class="col-md-12 title">
                  <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Deposit product discount</h4>
                </div>
                <!-- content column start -->
                <div class="col-md-12">

                  {!! Form::open(array('route' => 'customers.productdiscountstore','method'=>'POST', 'files'=>'true','id' => 'depositform')) !!}
                  <div class="row">
                    <div class="col-md-4 mb-4">
                      <div class="form-group">
                        <label for="txtfirstname">Discount Deposit Less 25k 14k(%)</label>
                        <div class="input-group">
                         <?php $discount_deposit_less_25 = CustomersHelper::getCustomerAttrValue($id, 'discount_deposit_less_25');?>
                         {!! Form::number('discount_deposit_less_25', $discount_deposit_less_25, ['class' => 'form-control number h-auto zertofive']) !!}
                         <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                         <input type="hidden" name="customerId" value="{{$id}}">
                       </div>
                     </div>
                   </div>
                   <div class="col-md-4 mb-4">
                     <div class="form-group">
                      <label for="txtfirstname">Discount Deposit Between 25k to 100k 14k(%)</label>
                      <div class="input-group">
                        <?php $discount_deposit_25_to_lakhs = CustomersHelper::getCustomerAttrValue($id, 'discount_deposit_25_to_lakhs');?>
                        {!! Form::number('discount_deposit_25_to_lakhs', $discount_deposit_25_to_lakhs, ['class' => 'form-control number h-auto fivetohund']) !!}
                        <input type="hidden" name="customerId" value="{{$id}}">
                        <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                   <div class="form-group">
                    <label for="txtfirstname">Discount Deposit Above 100k 14k(%)</label>
                    <div class="input-group">
                     <?php $discount_deposit_above_lakhs = CustomersHelper::getCustomerAttrValue($id, 'discount_deposit_above_lakhs');?>
                     {!! Form::number('discount_deposit_above_lakhs',$discount_deposit_above_lakhs, ['class' => 'form-control number h-auto hundton']) !!}
                     <input type="hidden" name="customerId" value="{{$id}}">
                     <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                   </div>
                 </div>
               </div>
             </div>
             <div class="row">
                    <div class="col-md-4 mb-4">
                      <div class="form-group">
                        <label for="txtfirstname">Discount Deposit Less 25k 18k(%)</label>
                        <div class="input-group">
                         <?php $discount_deposit_less_25_18k = CustomersHelper::getCustomerAttrValue($id, 'discount_deposit_less_25_18k');?>
                         {!! Form::number('discount_deposit_less_25_18k', $discount_deposit_less_25_18k, ['class' => 'form-control number h-auto zertofive']) !!}
                         <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                         <input type="hidden" name="customerId" value="{{$id}}">
                       </div>
                     </div>
                   </div>
                   <div class="col-md-4 mb-4">
                     <div class="form-group">
                      <label for="txtfirstname">Discount Deposit between 25k to 100k 18k(%)</label>
                      <div class="input-group">
                        <?php $discount_deposit_25_100k_18k = CustomersHelper::getCustomerAttrValue($id, 'discount_deposit_25_100k_18k');?>
                        {!! Form::number('discount_deposit_25_100k_18k', $discount_deposit_25_100k_18k, ['class' => 'form-control number h-auto fivetohund']) !!}
                        <input type="hidden" name="customerId" value="{{$id}}">
                        <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                   <div class="form-group">
                    <label for="txtfirstname">Discount Deposit Above 100k 18k(%)</label>
                    <div class="input-group">
                     <?php $discount_deposit_gt_100k_18k = CustomersHelper::getCustomerAttrValue($id, 'discount_deposit_gt_100k_18k');?>
                     {!! Form::number('discount_deposit_gt_100k_18k',$discount_deposit_gt_100k_18k, ['class' => 'form-control number h-auto hundton']) !!}
                     <input type="hidden" name="customerId" value="{{$id}}">
                     <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                   </div>
                 </div>
               </div>
             </div>
             <div class="row">
               <div class="col-md-5 mb-4">
                <button class="btn btn-primary" id="btn_save" type="submit">Submit</button>
                <button class="btn btn-outline-default" type="reset">Cancel</button>
              </div>
            </div>
            {!! Form::close() !!}
          </div>
        </div>
        <div class="row hidden customer-info-container" id="price-markup">
          {!! Form::open(array('method'=>'POST','id'=>'price-markup-form','class'=>'form-horizontal w-100','autocomplete'=>'nope')) !!}
            <div class="col-md-12 title">
              <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Price Markup</h4>
            </div>
            <div class="col-md-12">
                <div class="form-group row input-has-value">
                  <label class="col-md-2 col-form-label" for="l0">Price Markup</label>
                  <div class="input-group col-lg-4 col-md-4 col-sm-12">
                      {!! Form::number('price_markup',$priceMarkup, ['class' => 'form-control number h-auto hundton']) !!}
                      {!! Form::hidden('customer_id',$id, ['class' => 'form-control number h-auto hundton']) !!}
                      <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                 </div>
              </div>
            </div>
            <div class="col-md-12">
               <button class="btn btn-primary" id="btn-save-price-markup" type="button">Submit</button>
            </div>
            {!! Form::close() !!}
        </div>
        <div class="row hidden customer-info-container" id="paymentlist">
            <div class="col-md-12 title">
              <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Wallet Histroy</h4>
            </div>
            <div class="col-md-12">
              <?php
              $totalTransactionCount = isset($walletTransactionData['totalCount']) ? $walletTransactionData['totalCount'] : 0;
              $walletTransaction = isset($walletTransactionData['walletTransactionCollection']) ? $walletTransactionData['walletTransactionCollection'] : '';
              ?>
              <?php if($totalTransactionCount > 0):?>
                  <table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll" id="walletTransactionList">
                        <thead>
                            <tr class="bg-primary">
                                <th>Transaction Amount</th>
                                <th>Transaction Type</th>
                                <th>Ref. Number</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($walletTransaction as $key => $transaction):
                              $refNumber = !empty($transaction->ref_number) ? $transaction->ref_number : 'N/A';
                              $amount = isset($transaction->transaction_amt) ? ShowroomHelper::currencyFormat(round($transaction->transaction_amt)) : 0;
                              ?>
                              <tr>
                                  <td>{{$amount}}</td>
                                  <td>{{ucfirst($transaction->transaction_type)}}</td>
                                  <td>{{$refNumber}}</td>
                                  <td>{{date('d-m-Y',strtotime($transaction->created_at))}}</td>
                              </tr>
                            <?php endforeach;?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Transaction Amount</th>
                                <th>Transaction Type</th>
                                <th>Ref. Number</th>
                                <th>Created Date</th>
                            </tr>
                        </tfoot>
                  </table>
              <?php else:?>
                <p> No data available</p>
              <?php endif;?>
            </div>
        </div>
        <div class="row hidden customer-info-container hidden" id="paymenttransactionlist">
            
        </div>
        <div class="row hidden customer-info-container" id="createpayment">
            <div class="col-md-12 title">
              <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Create Payment</h4>
            </div>
            {!! Form::open(array('method'=>'POST','files'=>'true','id' => 'payment-form', 'class'=>'newuser' )) !!}
            <div class="col-md-12">
              <div class="row">
                <div class="col-lg-4 col-md-6">
                  <div class="form-group">
                    <label for="customer_name">Customer Name</label>
                    {!! Form::text('txtCustomerName', $customerName, array('placeholder' => 'Customer Name','class' => 'form-control','id'=>'txtCustomerName'  ,'readonly' => 'true','accept-charset'=>"UTF-8")) !!}
                    {!! Form::hidden('customer_id', $id, array('placeholder' => 'customer id','class' => 'form-control cid' ,'id' => 'customer_id','accept-charset'=>"UTF-8")) !!}
                  </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="form-group" >
                      <label for="invoice_number">Invoice Number </label>
                      {!! Form::text('txtInvoiceNumber', null,array('placeholder' => 'Invoice Number','class' => 'form-control','required')) !!}
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                  <div class="form-group">
                      <label for="invoice_amount">Invoice Amount <span class="text-danger">*</span></label>
                      {!! Form::number('txtInvoiceAmount', null, array('placeholder' => 'Invoice Amount','class' => 'form-control','min'=>'0.00', 'id' => 'txtInvoiceAmount', 'autocomplete'=>'nope')) !!}
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                  <div class="form-group">
                    <label for="l30">Due Date</label><br/>
                    {!! Form::text('txtDueDate', null, array('class' => 'required form-control datepicker', 'id' => 'txtDueDate', 'autocomplete' => 'off', 'data-plugin-options'=>'{"autoclose": true, "startDate": "tomorrow", "format": "yyyy-mm-dd"}')) !!}
                  </div>
                </div>
                <div class="col-lg-4 col-md-6">
                  <div class="form-group">
                    <label for="l30">Payment Form</label>
                    {!! Form::select('payment_form', ['Incoming' => 'Incoming', 'Outgoing' => 'Outgoing'], null,array('class' => 'form-control height-35 ')) !!}
                  </div>
                </div>
                <div class="col-lg-4 col-md-6">
                  <div class="form-group">
                    <label for="l30">Invoice Attachment</label><br/>
                      <div class="input-group">
                        <div class="input-group-btn width-90">
                          <div class="fileUpload btn w-100 btn-default height-35 lineheight-16">
                            <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
                            <input id="invoice_attachment" type="file" class="upload width-90"  name="invoice_attachment"    accept="application/pdf,image/png,image/jpeg, image/jpg"/>
                          </div>
                        </div>
                        <input id="uploadFile" name="uploadFile"  class="form-control border bg-transparent" placeholder="Choose File" disabled="disabled">
                      </div>
                    <small>jpeg jpg png pdf can select as Attachment</small><br/>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 childdropdown">
                  <div class="form-group">
                    <label for="l30">Payment Sub Header</label>
                    <select name="paymentSubType" class="form-control height-35 payment_child_header" id="paymentSubType">
                      <option value="" data-parent="0">Select Payment Sub Header</option>
                      @foreach($paymentType as $type)
                      <option value="{{ $type->id }}" data-parent="{{ $type->parent_id }}"> {{ $type->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-lg-4 col-md-6 payment hidden">
                  <div class="form-group">
                    <label for="l30">Payment Parent Header</label>
                      <select name="payment_type" id="payment_type"  class="form-control height-35" >
                        <option value="">Select Parent Type</option>
                        <option vaue=""> </option>
                      </select>
                  </div>
                  <input name="customer_type" type="hidden" value="Website"/>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 pl-0 pr-0">
              <div class="form-group">
                  <label for="remarks">Remarks <span class="text-danger">*</span></label>
                  {!! Form::textarea('txtRemarks', null, array('placeholder' => 'Remarks','class' => 'form-control', 'id' => 'txtRemarks', "rows"=>"3",'accept-charset'=>"UTF-8", 'autocomplete'=>'nope')) !!}
              </div>
            </div>
            <!-- <div class="col-lg-12 col-md-12"> -->
              <div class="col-12 form-actions btn-list pl-0 pr-0">
                  <input type="button" name="submit" class="btn btn-primary btn-sm px-3" id="btn-submit-payment" value="Submit"/>
                  <button class="btn btn-outline-default btn-sm px-3" id="btn-back-paymenthistroy" onclick="showCustomerSection('paymenthistroy',this.id)" type="reset">Cancel</button>
              </div>
            <!-- </div> -->
          </div>
          {!! Form::close() !!}
        </div>
        <div class="row hidden customer-info-container" id="paymenthistroy">
            <div class="col-md-12 title">
              <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Payment Histroy</h4>
              <button type="button" class="btn btn-primary small-btn-style ripple" id="create-payment" onclick="showCustomerSection('createpayment',this.id)"><i class="material-icons list-icon fs-24">playlist_add</i>Create Payment</button>
            </div>
            <div class="col-md-12">
              <?php
              $totalPaymentCount = isset($paymentData['totalCount']) ? $paymentData['totalCount'] : 0;
              $paymentList = isset($paymentData['paymentCollection']) ? $paymentData['paymentCollection'] : '';
              ?>
              <input type="hidden" name="txtPaymentSearch" id="txtPaymentSearch">
              <?php if($totalPaymentCount > 0):?>
                  <table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll" id="paymentListTable">
                        <thead>
                            <tr class="bg-primary">
                                <th>Amount</th>
                                <th>Payment Form</th>
                                <th>Billed Date</th>
                                <th>Due Date</th>
                                <th>Payment Type</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($paymentList as $key => $payment):?>
                              <tr>
                                  <td><?=CommonHelper::covertToCurrency($payment->invoice_amount);?></td>
                                  <td>{{ $payment->payment_form}}</td>
                                  <td>{{date('Y-m-d', strtotime($payment->created_at))}}</td>
                                  <td>{{ $payment->due_date}}</td>
                                  <td>{{ $payment->name}}</td>
                                  <?php 
                                  $paymentStatus = (isset($payment->payment_status) && $payment->payment_status) ? 'Paid' : 'Unpaid';
                                  ?>
                                  <td>{{$paymentStatus}}</td>
                                  <td>
                                      <a class="color-content table-action-style <?= empty($payment->invoice_attachment) ? 'disabled' : ''?>" href="{{ route('accountpayment.pdflisting',['id'=>$payment->id]) }}" <?= empty($payment->invoice_attachment) ? 'disabled' : ''?>><i class="material-icons md-18">file_download</i></a>
                                      <a class="pointer view-transaction" onclick="showPaymentTransaction('<?= $payment->id?>')"><i class="material-icons"  title="View Transaction">remove_red_eye</i></a>
                                  </td>
                              </tr>
                            <?php endforeach;?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Amount</th>
                                <th>Payment Form</th>
                                <th>Billed Date</th>
                                <th>Due Date</th>
                                <th>Payment Type</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                  </table>
              <?php else:?>
                <p> No data available</p>
              <?php endif;?>
            </div>
        </div>
      </div>
    </div>
  </div>

</div>
</div>
<!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->
<div class="modal fade bs-modal-lg" id="edit-customer-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-color-scheme modal-lg">
    <div class="modal-content ">
      {!! Form::open(array('method'=>'POST','id'=>'edit-customer-form','class'=>'form-horizontal edit_customer_form','autocomplete'=>'nope')) !!}

      {!! Form::close() !!}
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade bs-modal-lg" id="view-attachment-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-color-scheme modal-lg">
    <div class="modal-content">
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<div class="modal fade bs-modal-lg modal-color-scheme" id="invoice-memo-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {!! Form::open(array('method'=>'POST','id'=>'invoicememo-generate-form','class'=>'form-horizontal','autocomplete'=>'nope','enctype'=>'multipart/form-data')) !!}

            {!! Form::close() !!}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<style>
.product-img{max-width: 40px;}.view-transaction{color: #51d2b7 !important;}
#createpayment input[type="number"] + label.error{position: relative;}
</style>
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="<?=URL::to('/');?>/js/jquery.counterup.min.js"></script>
<script src="<?=URL::to('/');?>/js/jquery.waypoints.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?=URL::to('/');?>/js/additional-methods.min.js"></script>
<script>
  $(document).ready(function(){
    $(document).on("change","#paymentSubType",function(){
        if(this.value != '')
        {
            var parent_id = $(this).find(':selected').attr('data-parent');
            $.ajax({
                type: 'post',
                url: '<?=URL::to('/customers/getpaymentparentheader');?>',
                data:{payment_type:parent_id,_token:"{{ csrf_token() }}"},
                beforeSend: function(){
                    showLoader();
                },
                success: function(response){
                    hideLoader();
                    var res = JSON.parse(response);
                    if(res.status)
                    {
                        var paymentTypeOptions = '';
                        var data = JSON.parse(res.data);
                        $.each(data, function(value) {
                          var optionval = data[value];
                          paymentTypeOptions += "<option value='" + optionval.id + "'>" + optionval.name + "</option>";
                        });
                        $("#payment_type").html(paymentTypeOptions);
                        $(".payment").removeClass('hidden');
                    }
                },
            });
        }
    });
    if($("#invoice_attachment").length > 0)
    {
        document.getElementById("invoice_attachment").onchange = function () {
          document.getElementById("uploadFile").value = this.value.substring(12);
      };
    }
    $(document).on("click","#btn-submit-payment", function(){
        $.validator.addMethod('filesize', function (value, element, param) {
            return this.optional(element) || (element.files[0].size <= param)
        }, 'File size must be less than {0}');
        $("#payment-form").validate({
            rules: {
                txtCustomerName: "required",
                txtInvoiceNumber: "required",
                txtInvoiceAmount:{
                    required: true,
                    number: true,
                    min: 1
                },
                txtDueDate: "required",
                payment_form: "required",
                invoice_attachment: {
                    required: true,
                    extension: "png|jpg|jpeg|pdf",
                    filesize: 2097152
                },
                paymentSubType: "required",
                payment_type: "required",
                txtRemarks: "required"
            },
            messages: {
                txtCustomerName: "Name is required",
                txtInvoiceNumber: "Invoice number is required",
                invoice_attachment:{
                    required: 'Invoice attachment is required',
                    extension: 'Invalid file type',
                    filesize: 'File size must be less than 2 MB'
                },
                txtInvoiceAmount:{
                    required: 'Invoice amount is required',
                    number: 'Invalid invoice amount',
                    min: 'Invalid invoice amount'
                },
                txtDueDate: 'Due date is required',
                payment_form: 'Payment form is required',
                paymentSubType: 'Payment sub header is required',
                payment_type: 'Payment parent header is required',
                txtRemarks : 'Remarks is required'
            }
        });
        if($("#payment-form").valid())
        {
            var invoiceMemoForm=$("#payment-form");
            var formData = new FormData(invoiceMemoForm[0]);
            $.ajax({
                contentType: false,
                type: 'post',
                url: '<?=URL::to('/customers/createpayment');?>',
                processData: false,
                cache: false,
                data: formData,
                beforeSend: function(){
                    $("#btn-submit-payment").prop("disabled",true);
                    showLoader();
                },
                success: function(response){
                    hideLoader();
                    var res = JSON.parse(response);
                    $("#btn-submit-payment").prop("disabled",false);
                    if(res.status)
                    {
                        swal({
                          title: 'Success',
                          text: res.message,
                          type: 'success',
                          buttonClass: 'btn btn-primary'
                          //showSuccessButton: true,
                          //showConfirmButton: false,
                          //successButtonClass: 'btn btn-primary',
                          //successButtonText: 'Ok'
                        });
                        $("#payment_histroy").trigger('click');
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
    $(document).on("click","#btn-back-payment", function(){
        $("#paymenttransactionlist").addClass('hidden');
        $("#paymenthistroy").removeClass('hidden');
    });
    $(document).on("click",".btn-deliver-memo",function(){
      var memoId = $(this).data('id');
      var buttonId = $(this).attr('id');
      swal({
        title: 'Are you sure?',
        text: "<?php echo Config::get('constants.message.inventory_deliver_approval_confirmation'); ?>",
        type: 'info',
        showCancelButton: true,
        confirmButtonText: 'Confirm',
        confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
        }).then(function(deliverBtn) {
        if (deliverBtn.value) {
          if(memoId != '')
          {
          $.ajax({
            url:'<?=URL::to('inventory/deliverapprovalmemo');?>',
            method:"post",
            data:{memo_id: memoId,_token: "{{ csrf_token() }}"},
            beforeSend: function()
            {
              showLoader();
            },
            success: function(response){
                hideLoader();
                var res = JSON.parse(response);
                if(res.status)
                {
                  swal({
                    title: 'Success',
                    text: res.message,
                    type: 'success',
                    buttonClass: 'btn btn-primary'
                    });
                    $("#memoListTable_newest_approval #"+buttonId +', #memoListTable_all_approval #'+buttonId+', #memoListTable_oldest_approval #'+buttonId).attr('disabled','disabled');
                    $("#memoListTable_newest_approval #"+buttonId +', #memoListTable_all_approval #'+buttonId+', #memoListTable_oldest_approval #'+buttonId).addClass('disabled');
                }else{
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
          }else{
            swal({
              title: 'Oops!',
              text: '<?php echo Config::get('constants.message.inventory_default_failure_message'); ?>',
              type: 'error',
              showCancelButton: true,
              showConfirmButton: false,
              confirmButtonClass: 'btn btn-danger',
              cancelButtonText: 'Ok'
            });
          }
        }
      })
    });
      $(document).on('click','.btn-cancel-approval',function(){
          var memoId = $(this).data('memoid');
          swal({
                title: 'Are you sure?',
                text: "<?php echo Config::get('constants.message.inventory_cancel_approval_confirmation'); ?>",
                type: 'info',
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
                }).then(function(data) {
                if (data.value) {
                   if(memoId != '')
                  {
                      $.ajax({
                            url:'<?=URL::to('inventory/cancelapprovalmemo');?>',
                            method:"post",
                            data:{memo_id: memoId,_token: "{{ csrf_token() }}"},
                            beforeSend: function()
                            {
                              showLoader();
                            },
                            success: function(response){
                                hideLoader();
                                var res = JSON.parse(response);
                                if(res.status)
                                {
                                    swal({
                                        title: 'Success',
                                        text: res.message,
                                        type: 'success',
                                        buttonClass: 'btn btn-primary'
                                      });
                                    $('#memoListTable_all_approval').DataTable().draw();
                                    $("#memoListTable_newest_approval #order_id_"+memoId).remove();
                                    $("#newest_approval .dataTables_info").html('Showing 0 to 0 of 0 entries');
                                    $("#memoListTable_newest_approval tbody").html('<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">No data available in table</td></tr>');
                                    /*$("#memoListTable_oldest_approval #order_id_"+memoId).remove();
                                    $("#memoListTable_newest_approval #order_id_"+memoId).remove();
                                    $("#memoListTable_all_approval #order_id_"+memoId).remove();*/
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
                  else
                  {
                    swal({
                          title: 'Oops!',
                          text: '<?php echo Config::get('constants.message.inventory_default_failure_message'); ?>',
                          type: 'error',
                          showCancelButton: true,
                          showConfirmButton: false,
                          confirmButtonClass: 'btn btn-danger',
                          cancelButtonText: 'Ok'
                        });
                  }
                } 
                 
            })
        });
      $(document).on('click','.btn-generate-approval',function(){
        var memoId = $(this).data('memoid');
        swal({
            title: 'Are you sure?',
            text: "<?php echo Config::get('constants.message.inventory_generate_approval_confirmation'); ?>",
            type: 'info',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
            }).then(function(data) {
              if (data.value) {
                if(memoId != '')
                {
                  $.ajax({
                    url:'<?=URL::to('inventory/generateapprovalmemo');?>',
                    method:"post",
                    data:{memo_id: memoId,_token: "{{ csrf_token() }}"},
                    beforeSend: function()
                    {
                    showLoader();
                    },
                    success: function(response){
                      hideLoader();
                      var res = JSON.parse(response);
                      if(res.status)
                      {
                        swal({
                        title: 'Success',
                        text: res.message,
                        type: 'success',
                        buttonClass: 'btn btn-primary'
                        });
                        $("#memoListTable_oldest_approval #order_id_"+memoId).children('td').eq(1).html(res.approval_number);
                        $("#memoListTable_newest_approval #order_id_"+memoId).children('td').eq(1).html(res.approval_number);
                        $("#memoListTable_all_approval #order_id_"+memoId).children('td').eq(1).html(res.approval_number);

                        $("#memoListTable_oldest_approval #order_id_"+memoId+' .btn-generate-returnmemo').removeClass('disabled');
                        $("#memoListTable_newest_approval #order_id_"+memoId+' .btn-generate-returnmemo').removeClass('disabled');
                        $("#memoListTable_all_approval #order_id_"+memoId+' .btn-generate-returnmemo').removeClass('disabled');

                        $("#memoListTable_all_approval #order_id_"+memoId+' .btn-deliver-memo, #memoListTable_oldest_approval #order_id_'+memoId+' .btn-deliver-memo, #memoListTable_newest_approval #order_id_'+memoId+' .btn-deliver-memo').removeClass('disabled');

                        $("#memoListTable_all_approval #order_id_"+memoId+' .btn-deliver-memo, #memoListTable_oldest_approval #order_id_'+memoId+' .btn-deliver-memo, #memoListTable_newest_approval #order_id_'+memoId+' .btn-deliver-memo').removeAttr('disabled');

                        $("#memoListTable_oldest_approval #order_id_"+memoId+' .btn-cancel-approval, '+"#memoListTable_oldest_approval #order_id_"+memoId+' .btn-generate-approval').addClass('disabled');
                        $("#memoListTable_newest_approval #order_id_"+memoId+' .btn-cancel-approval, '+"#memoListTable_newest_approval #order_id_"+memoId+' .btn-generate-approval').addClass('disabled');
                        $("#memoListTable_all_approval #order_id_"+memoId+' .btn-cancel-approval, '+"#memoListTable_all_approval #order_id_"+memoId+' .btn-generate-approval').addClass('disabled');

                        $("#memoListTable_oldest_approval #order_id_"+memoId+' .btn-cancel-approval, '+"#memoListTable_oldest_approval #order_id_"+memoId+' .btn-generate-approval').attr('disabled',true);
                        $("#memoListTable_newest_approval #order_id_"+memoId+' .btn-cancel-approval, '+"#memoListTable_newest_approval #order_id_"+memoId+' .btn-generate-approval').attr('disabled',true);
                        $("#memoListTable_all_approval #order_id_"+memoId+' .btn-cancel-approval, '+"#memoListTable_all_approval #order_id_"+memoId+' .btn-generate-approval').attr('disabled',true);
                        }else{
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
                else
                {
                  swal({
                    title: 'Oops!',
                    text: '<?php echo Config::get('constants.message.inventory_default_failure_message'); ?>',
                    type: 'error',
                    showCancelButton: true,
                    showConfirmButton: false,
                    confirmButtonClass: 'btn btn-danger',
                    cancelButtonText: 'Ok'
                  });
                } 
              }
            })
      });
    var requestedSection = '';
    var currentUrl = window.location.href.toString();
    if(currentUrl.includes('#'))
    {
        requestedSection = currentUrl.split('#')[1];  
        showCustomerSection(requestedSection, '');
    }

      $(document).on('click','.btn-generate-creditnote', function(){
          var href = $(this).data('href');
          var generateCretidNote = $(this);
          var viewCreditNote = $(this).next();
          /*$(this).addClass('disabled');
          $(this).next().removeClass('disabled');*/
          swal({
                title: 'Are you sure?',
                text: "<?php echo Config::get('constants.message.sales_credit_note_generate_confirmation'); ?>",
                type: 'info',
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
                }).then(function(data) {
                  if (data.value) {
                    generateCretidNote.addClass('disabled');
                    viewCreditNote.removeClass('disabled');
                    window.location.href = href;
                  }
                   
                })
      });

      $(document).on("click","#btn-filter-approval", function(){
          $('#memoListTable_all_approval').DataTable().draw();
          $('#memoListTable_newest_approval').DataTable().draw();
          $('#memoListTable_oldest_approval').DataTable().draw();
          $('#approvalProductsTable').DataTable().draw();
      });
      $(document).on("click","#btn-invoice-return-products", function(){
          var action = $('#approval-actions option:selected').val();
          var productIds = new Array();
          $.each($(".chkProduct:checked"), function() {
              productIds.push($(this).val());
          });
          var ids = productIds.join(",");
          if(action == "invoice" || action == "memo")
          {
              $("#operation_type").val(action);
              if(ids!='')
              {
                  $.ajax({
                    url:'<?=URL::to('/inventory/getinvoicememomodalcontent');?>',
                    method:"post",
                    data:{productIds: ids, action: action, customer_id: $("#customer_id").val(),is_from_customer_view:true,_token: "{{ csrf_token() }}"},
                    success: function(response){
                        $("#invoice-memo-modal #invoicememo-generate-form").html(response);
                        $("#discount_type").html("<option value=''>Select</option><option value='approval_discount'>Approval Product Discount</option><option value='deposit_discount'>Deposit Product Discount</option><option value='default_discount'>Default Product Discount</option>");
                        $("#is_from_customer_view").val('true');
                        $("#approval_memo_id").val('');
                        $("#invoice-memo-modal").modal("show");
                    }
                  })
              }
              else
              {
                  swal({
                    title: 'Are you sure?',
                    text: "<?php echo Config::get('constants.message.inventory_generate_invoicememo_product_not_selected'); ?>",
                    type: 'info',
                    showCancelButton: true,
                    showConfirmButton: false
                    });
              }
          }
          else if(action == 'return_memo')
          {
              $.ajax({
                  url:'<?=URL::to('/inventory/getapprovalidbyproduct');?>',
                  method:"post",
                  data:{productIds: ids,_token: "{{ csrf_token() }}"},
                  beforeSend: function()
                  {
                    showLoader();
                  },
                  success: function(response){
                    var res = JSON.parse(response);
                    if(res.status)
                    {
                        $.ajax({
                            url:'<?=URL::to('/inventory/generatereturnmemo');?>',
                            method:"post",
                            data:{memo_id: res.memo_ids, productIds: ids,_token: "{{ csrf_token() }}"},
                            beforeSend: function()
                            {
                              showLoader();
                            },
                            success: function(response){
                                hideLoader();
                                var res = JSON.parse(response);
                                approvalProductsTable.draw();
                                if(res.status)
                                {
                                    swal({
                                      title: 'Success',
                                      text: res.message,
                                      type: 'success',
                                      buttonClass: 'btn btn-primary'
                                      //showSuccessButton: true,
                                      //showConfirmButton: false,
                                      //successButtonClass: 'btn btn-primary',
                                      //successButtonText: 'Ok'
                                    }).then(function(data) {
                                      if (data.value) {
                                        window.location.href = '<?=URL::to('/inventory/returnmemolist');?>';
                                      }
                                       
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
                          })
                    }
                  }
              });
          }
      });
      $("#chkAllProduct").click(function(){
          $('.chkProduct').prop('checked', this.checked);
      });
      $("#memoListTable_oldest_approval tr .checkboxth").removeClass('sorting_asc');
      $(document).on('click','.btn-generate-invoice-returnmemo',function(){
            var parentId = $(this).closest(".dataTables_wrapper").attr('id');
            var action = $('#'+parentId+' #inventory-status option:selected').val();
            var approvalMemoIds = new Array();
            $.each($("#"+parentId+" .chkApproval:checked"), function() {
                approvalMemoIds.push($(this).val());
            });
            var ids = approvalMemoIds.join(",");
            if(ids != '')
            {
              if(action == 'invoice'){
                  $.ajax({
                    url:'<?=URL::to('/inventory/getproductidsbyapproval');?>',
                    method:"post",
                    data:{memo_id: ids,_token: "{{ csrf_token() }}"},
                    success: function(response){
                      var res = JSON.parse(response);
                      if(res.status)
                      {
                          $.ajax({
                            url:'<?=URL::to('/inventory/getinvoicememomodalcontent');?>',
                            method:"post",
                            data:{productIds: res.product_ids, action: 'invoice',customer_id: $("#customer_id").val(),is_from_customer_view:true,_token: "{{ csrf_token() }}"},
                            success: function(response){
                                $("#invoice-memo-modal #invoicememo-generate-form").html(response);
                                $("#discount_type").html("<option value=''>Select</option><option value='approval_discount'>Approval Product Discount</option><option value='deposit_discount'>Deposit Product Discount</option><option value='default_discount'>Default Product Discount</option>");
                                $("#is_from_customer_view").val('true');
                                $("#approval_memo_id").val('');
                                $("#invoice-memo-modal").modal("show");
                            }
                          })
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
                  })
              }
              else if(action == 'return_memo')
              {
                  $.ajax({
                    url:'<?=URL::to('/inventory/getproductidsforreturnmemo');?>',
                    method:"post",
                    data:{memo_id: ids,_token: "{{ csrf_token() }}"},
                    success: function(response){
                      var res = JSON.parse(response);
                      if(res.status)
                      {
                          $.ajax({
                            url:'<?=URL::to('/inventory/generatereturnmemo');?>',
                            method:"post",
                            data:{memo_id: ids, productIds: res.product_ids,_token: "{{ csrf_token() }}"},
                            beforeSend: function()
                            {
                              showLoader();
                            },
                            success: function(response){
                                hideLoader();
                                var res = JSON.parse(response);
                                inventoryProductsTable.draw();
                                if(res.status)
                                {
                                  $('#memoListTable_all_approval').DataTable().draw();
                                  $('#approvalProductsTable').DataTable().draw();
                                    swal({
                                      title: 'Success',
                                      text: res.message,
                                      type: 'success',
                                      buttonClass: 'btn btn-primary'
                                    }).then(function() {
                                      window.location.href = '<?=URL::to('/inventory/returnmemolist');?>';
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
                          })
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
            }
            else
            {
                swal({
                  title: 'Are you sure?',
                  text: "<?php echo Config::get('constants.message.inventory_generate_invoicememo_approval_not_selected'); ?>",
                  type: 'info',
                  showCancelButton: true,
                  showConfirmButton: false
                });
            }
        });
        $(document).on('change','.inventory_action',function(){
          var productIds = $('option:selected', this).attr('data-productid');

          $("#product_ids").val(productIds);
          if(this.value == 'invoice' || this.value == 'memo')
          {
              $("#operation_type").val(this.value);
              if(productIds!='')
              {
                  $.ajax({
                    url:'<?=URL::to('/inventory/getinvoicememomodalcontent');?>',
                    method:"post",
                    data:{productIds: productIds, action: 'invoice',customer_id: $("#customer_id").val(),is_from_customer_view:true,_token: "{{ csrf_token() }}"},
                    success: function(response){
                        $("#invoice-memo-modal #invoicememo-generate-form").html(response);
                        $("#discount_type").html("<option value=''>Select</option><option value='approval_discount'>Approval Product Discount</option><option value='deposit_discount'>Deposit Product Discount</option><option value='default_discount'>Default Product Discount</option>");
                        $("#is_from_customer_view").val('true');
                        $("#approval_memo_id").val('');
                        $("#invoice-memo-modal").modal("show");
                    }
                  })
              }
              else
              {
                  swal({
                    title: 'Are you sure?',
                    text: "<?php echo Config::get('constants.message.inventory_generate_invoicememo_product_not_selected'); ?>",
                    type: 'info',
                    showCancelButton: true,
                    showConfirmButton: false
                    });
              }
          }
          else if(this.value == 'returnmemo')
          {
              if(productIds!='')
              {
                //var url = $("#generateReturnMemoAction").val()+'?productIds='+ids;
                //window.location.href = url;
                $.ajax({
                      url:'<?=URL::to('/inventory/getapprovalidbyproduct');?>',
                      method:"post",
                      data:{productIds: productIds,_token: "{{ csrf_token() }}"},
                      beforeSend: function()
                      {
                        showLoader();
                      },
                      success: function(response){
                        var res = JSON.parse(response);
                          if(res.status)
                          {
                              $.ajax({
                                  url:'<?=URL::to('/inventory/generatereturnmemo');?>',
                                  method:"post",
                                  data:{memo_id: res.memo_ids, productIds: productIds,_token: "{{ csrf_token() }}"},
                                  beforeSend: function()
                                  {
                                    showLoader();
                                  },
                                  success: function(response){
                                      hideLoader();
                                      var res = JSON.parse(response);
                                      approvalProductsTable.draw();
                                      if(res.status)
                                      {
                                          swal({
                                            title: 'Success',
                                            text: res.message,
                                            type: 'success',
                                            buttonClass: 'btn btn-primary'
                                            //showSuccessButton: true,
                                            //showConfirmButton: false,
                                            //successButtonClass: 'btn btn-primary',
                                            //successButtonText: 'Ok'
                                          }).then(function() {
                                              window.location.href = '<?=URL::to('/inventory/returnmemolist');?>';
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
                                })
                          }
                      }
                });

              }
              else
              {
                  swal({
                    title: 'Are you sure?',
                    text: "<?php echo Config::get('constants.message.inventory_generate_memo_product_not_selected'); ?>",
                    type: 'info',
                    showCancelButton: true,
                    showConfirmButton: false
                    });
              }
          }
        });
        $(document).on('click','.btn-generate-returnmemo',function(){
            event.stopPropagation();
            var orderId = $(this).data("id");
            if(orderId != '')
            {
              swal({
                  title: 'Are you sure?',
                  text: "<?php echo Config::get('constants.message.inventory_generate_returnmemo_confirmation'); ?>",
                  type: 'info',
                  showCancelButton: true,
                  confirmButtonText: 'Confirm',
                  confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
              }).then(function(data) {
                if (data.value) {
                  $.ajax({
                      url:'<?=URL::to('inventory/getproductidsbyorder');?>',
                      method:"post",
                      data:{orderId: orderId,_token: "{{ csrf_token() }}"},
                      beforeSend: function()
                      {
                        showLoader();
                      },
                      success: function(response){
                          hideLoader();
                          var res = JSON.parse(response);
                          $.ajax({
                          url:'<?=URL::to('/inventory/generatereturnmemo');?>',
                          method:"post",
                          data:{productIds: res.product_ids,_token: "{{ csrf_token() }}"},
                          beforeSend: function()
                          {
                            showLoader();
                          },
                          success: function(response){
                              hideLoader();
                              var res = JSON.parse(response);
                              if(res.status)
                              {
                                  memoListTable_all_approval.draw();
                                  setTimeout(function(){
                                      var all_approval_return_val = $("#memoListTable_all_approval .btn-generate-returnmemo[data-id='"+orderId+"']").attr('class');
                                          $("#memoListTable_oldest_approval .btn-generate-returnmemo[data-id='"+orderId+"'], #memoListTable_newest_approval .btn-generate-returnmemo[data-id='"+orderId+"']").attr('class',all_approval_return_val);
                                  }, 5000);
                                  swal({
                                    title: 'Success',
                                    text: res.message,
                                    type: 'success',
                                    buttonClass: 'btn btn-primary'
                                  }).then(function() {
                                      window.location.href = '<?=URL::to('/inventory/returnmemolist');?>';
                                });
                                  $("#order_id_"+orderId).remove();
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
                        })
                      }
                  });
                }
                  
              });
            }
        });
        $(document).on('click','.btn-generate-invoice',function(){
            var approvalMemoIds = new Array();
            approvalMemoIds.push($(this).data('id'));
            var ids = approvalMemoIds.join(",");
            if(ids != '')
            {
                $.ajax({
                    url:'<?=URL::to('/inventory/getproductidsbyapproval');?>',
                    method:"post",
                    data:{memo_id: ids,_token: "{{ csrf_token() }}"},
                    success: function(response){
                      var res = JSON.parse(response);
                      if(res.status)
                      {
                          $.ajax({
                            url:'<?=URL::to('/inventory/getinvoicememomodalcontent');?>',
                            method:"post",
                            data:{productIds: res.product_ids, action: 'invoice',customer_id: $("#customer_id").val(),is_from_customer_view:true,_token: "{{ csrf_token() }}"},
                            success: function(response){
                                $("#invoice-memo-modal #invoicememo-generate-form").html(response);
                                $("#discount_type").html("<option value=''>Select</option><option value='approval_discount'>Approval Product Discount</option><option value='deposit_discount'>Deposit Product Discount</option><option value='default_discount'>Default Product Discount</option>");
                                $("#is_from_customer_view").val('true');
                                $("#approval_memo_id").val(ids);
                                $("#invoice-memo-modal").modal("show");
                            }
                          })
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
                  })
            }
            else
            {
                swal({
                  title: 'Are you sure?',
                  text: "<?php echo Config::get('constants.message.inventory_generate_invoicememo_approval_not_selected'); ?>",
                  type: 'info',
                  showCancelButton: true,
                  showConfirmButton: false
                  });
            }
        });
        $(document).on('click','.downloadmemoexcel',function(){
          event.stopPropagation();
          var orderId = $(this).data("id");
          window.location.href = "<?=URL::to('inventory/downloadmemoproductexcel');?>/"+orderId;
        });
        $(".chkAppApproval").click(function(){
            var id = $(this).data('type');
            $('#'+id+' .chkApproval').prop('checked', this.checked);
        });

        $(document).on('click','.downloadexcel',function(){
          event.stopPropagation();
          var orderId = $(this).data("id");
          window.location.href = "<?=URL::to('inventory/downloadexcel');?>/"+orderId;
        });
        var approvalProductsTable = $('#approvalProductsTable').DataTable({
          //"dom": '<"datatable_top_custom_lengthinfo"i <"#inventory-toolbar">>frti<"datatable_bottom_custom_length"l>p',
          "dom": '<"datatable_top_custom_lengthinfo custom-page-length d-flex flex-wrap"i   <"datatable_top_showroom_length mx-3"l> <"#inventory-toolbar">>frtip',
          "language": {
            "infoEmpty": "No matched records found",
            "zeroRecords": "No matched records found",
            "emptyTable": "No data available in table",
          //"sProcessing": "<div id='loader'></div>"
        },
        "deferLoading": <?=$totalProducts?>,
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "ajax":{
          "url": '<?=URL::to('/customers/approvalproductsajaxlist');?>',
          "data": function(data, callback){
            data._token = "{{ csrf_token() }}";
            data.approval_type = $("#approval_filter").val();
            data.customer_id = $("#customer_id").val();
            showLoader();
            $(".dropdown").removeClass('show');
            $(".dropdown-menu").removeClass('show');
          },
          complete: function(response){
            if(response.responseJSON.recordsTotal < 1)
            {
              $('#approvalProductsTable').removeClass('d-block');
            }
            else
            {
              $('#approvalProductsTable').addClass('d-block');
            }
            $("#approvalProductsTable tr .checkboxth").removeClass('sorting_asc');
            hideLoader();
          }
        },
        "columnDefs": [
        { "orderable": false, "targets": [0] }
        ],
        //"responsive": true
      });
        $divContainer = $('<div class="inventory-action-container"/>').appendTo('#inventory-toolbar')
        $select = $('<select class="mx-2 mr-3 height-35 padding-four" id="approval-actions"/>').appendTo($divContainer)
        $('<option/>').val('invoice').text('Generate Invoice').appendTo($select);
        $('<option/>').val('return_memo').text('Return Memo').appendTo($select);
        $('<button class="btn btn-primary height-35" type="button" id="btn-invoice-return-products"/>').text('Submit').appendTo($divContainer);
        $('#approval-products .dataTables_filter input')
        .unbind() // Unbind previous default bindings
        .bind("input", function(e) { // Bind our desired behavior
            // If the length is 3 or more characters, or the user pressed ENTER, search
            if(this.value.length >= 3 || e.keyCode == 13) {
                // Call the API search function
                approvalProductsTable.search(this.value).draw();
              }
            // Ensure we clear the search if they backspace far enough
            if(this.value == "") {
              approvalProductsTable.search("").draw();
            }
            return;
          });
        $("#approvalProductsTable tr .checkboxth").removeClass('sorting_asc');
        $("#approvalProductsTable_length").addClass('mt-0');
        var oldNewMemoListTable = $('.oldest_approval,.newest_approval').DataTable({
          "lengthChange": false,
          "dom": '<"datatable_top_custom_lengthinfo"i <".approvalmemo-toolbar">>frti<"datatable_bottom_custom_length"l>p',
          "order": [[ 3, "desc" ]],
          "columnDefs": [
              { "orderable": false, "targets": [0] }
          ]
        });
        $divContainer = $('<div class="inventory-action-container"/>').appendTo('.approvalmemo-toolbar')
        $select = $('<select class="mx-2 mr-3 height-35 padding-four" id="inventory-status"/>').appendTo($divContainer)
        $('<option/>').val('invoice').text('Generate Invoice').appendTo($select);
        $('<option/>').val('return_memo').text('Return Memo').appendTo($select);
        $('<button class="btn btn-primary height-35 btn-generate-invoice-returnmemo" type="button"/>').text('Submit').appendTo($divContainer);
        var serverSideFlag = false;
        <?php foreach ($approvalType as $key => $type): ?>
          var table_key = '<?=$key?>';

          if(table_key == 'all_approval')
          {
            var memoListTable_<?=$key?> = $('#memoListTable_<?=$key?>').DataTable({
              "dom": '<"datatable_top_custom_lengthinfo"i <"#all-approvalmemo-toolbar">>frti<"datatable_bottom_custom_length"l>p',
              "language": {
                "infoEmpty": "No matched records found",
                "zeroRecords": "No matched records found",
                "emptyTable": "No data available in table",
                //"sProcessing": "<div id='loader'></div>"
              },
              "deferLoading": <?=$approvalMemoCollection['memoCount'][$key]?>,
              "processing": true,
              "serverSide": true,
              "serverMethod": "post",
              "ajax":{
                "url": '<?=URL::to('/customers/approvalmemoajaxlist');?>',
                "data": function(data, callback){
                  data._token = "{{ csrf_token() }}";
                  data.approval_type = $("#approval_filter").val();
                  data.customer_id = $("#customer_id").val();
                  showLoader();
                  $(".dropdown").removeClass('show');
                  $(".dropdown-menu").removeClass('show');
                },
                complete: function(response){
                  $(".memoListTable tr .checkboxth").removeClass('sorting_asc');
                  hideLoader();
                }
              },
              "lengthChange": false,
              "order": [[ 4, "DESC" ]],
              "columnDefs": [
                  { "orderable": false, "targets": [0,1] }
              ],
              createdRow: function( row, data, dataIndex ) {
                    var memo_id = $(data[0]).children('input').val();
                    $( row ).attr('id', 'order_id_'+memo_id);
                }
            });
            $divContainer = $('<div class="inventory-action-container"/>').appendTo('#all-approvalmemo-toolbar')
            $select = $('<select class="mx-2 mr-3 height-35 padding-four" id="inventory-status"/>').appendTo($divContainer)
            $('<option/>').val('invoice').text('Generate Invoice').appendTo($select);
            $('<option/>').val('return_memo').text('Return Memo').appendTo($select);
            $('<button class="btn btn-primary height-35 btn-generate-invoice-returnmemo" type="button" data-type="'+table_key+'"/>').text('Submit').appendTo($divContainer);
          }
        <?php endforeach;?>


        var invoiceListTable = $('#invoiceListTable').DataTable({
          "language": {
            "infoEmpty": "No matched records found",
            "zeroRecords": "No matched records found",
            "emptyTable": "No data available in table",
          //"sProcessing": "<div id='loader'></div>"
        },
        "deferLoading": <?=$totalInvoiceCount?>,
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "ajax":{
          "url": '<?=URL::to('/customers/invoiceajaxlist');?>',
          "data": function(data, callback){
            data._token = "{{ csrf_token() }}";
            data.customer_id = $("#customer_id").val();
            showLoader();
            $(".dropdown").removeClass('show');
            $(".dropdown-menu").removeClass('show');
          },
          complete: function(response){
            $("#invoiceListTable tr .checkboxth").removeClass('sorting_asc');
            hideLoader();
          }
        },
        "dom": '<"datatable_top_custom_lengthinfo"i>frti<"datatable_bottom_custom_length"l>p',
        "lengthChange": false,
        "order": [[ 4, "DESC" ]]
      });
        $('#total-invoices .dataTables_filter input')
        .unbind() // Unbind previous default bindings
        .bind("input", function(e) { // Bind our desired behavior
            // If the length is 3 or more characters, or the user pressed ENTER, search
            if(this.value.length >= 3 || e.keyCode == 13) {
                // Call the API search function
                invoiceListTable.search(this.value).draw();
              }
            // Ensure we clear the search if they backspace far enough
            if(this.value == "") {
              invoiceListTable.search("").draw();
            }
            return;
          });
        var returnedProductListTable = $('#returnedProductListTable').DataTable({
          "language": {
            "infoEmpty": "No matched records found",
            "zeroRecords": "No matched records found",
            "emptyTable": "No data available in table",
          //"sProcessing": "<div id='loader'></div>"
        },
        "deferLoading": <?=$totalReturnedProducts?>,
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "ajax":{
          "url": '<?=URL::to('/customers/returnedproductajaxlist');?>',
          "data": function(data, callback){
            data._token = "{{ csrf_token() }}";
            data.customer_id = $("#customer_id").val();
            showLoader();
            $(".dropdown").removeClass('show');
            $(".dropdown-menu").removeClass('show');
          },
          complete: function(response){
            $("#returnedProductListTable tr .checkboxth").removeClass('sorting_asc');
            hideLoader();
          }
        },
        "dom": '<"datatable_top_custom_lengthinfo"i>frti<"datatable_bottom_custom_length"l>p',
        "lengthChange": false,
        "order": [[ 4, "DESC" ]]
      });
          $('#total-return-products .dataTables_filter input')
          .unbind() // Unbind previous default bindings
          .bind("input", function(e) { // Bind our desired behavior
              // If the length is 3 or more characters, or the user pressed ENTER, search
              if(this.value.length >= 3 || e.keyCode == 13) {
                  // Call the API search function
                  returnedProductListTable.search(this.value).draw();
                }
              // Ensure we clear the search if they backspace far enough
              if(this.value == "") {
                returnedProductListTable.search("").draw();
              }
              return;
            });

            var inventoryProductsTable = $('#inventoryProductsTable').DataTable({
              "language": {
                "infoEmpty": "No matched records found",
                "zeroRecords": "No matched records found",
                "emptyTable": "No data available in table",
              //"sProcessing": "<div id='loader'></div>"
              },
              "deferLoading": <?=$customerInventoryCount?>,
              "processing": true,
              "serverSide": true,
              "serverMethod": "post",
              "ajax":{
                "url": '<?=URL::to('/customers/customerinventoryajaxlist');?>',
                "data": function(data, callback){
                  data._token = "{{ csrf_token() }}";
                  data.customer_id = $("#customer_id").val();
                  showLoader();
                  $(".dropdown").removeClass('show');
                  $(".dropdown-menu").removeClass('show');
                },
                complete: function(response){
                  $('#inventoryProductsTable').removeClass('d-block');
                  $("#inventoryProductsTable tr .checkboxth").removeClass('sorting_asc');
                  hideLoader();
                }
              },
              "columnDefs": [
              { "orderable": false, "targets": [0] }
              ],
              //"responsive": true
            });

        $('#customer-inventory .dataTables_filter input')
        .unbind() // Unbind previous default bindings
        .bind("input", function(e) { // Bind our desired behavior
            // If the length is 3 or more characters, or the user pressed ENTER, search
            if(this.value.length >= 3 || e.keyCode == 13) {
                // Call the API search function
                inventoryProductsTable.search(this.value).draw();
              }
            // Ensure we clear the search if they backspace far enough
            if(this.value == "") {
              inventoryProductsTable.search("").draw();
            }
            return;
          });
        $("#inventoryProductsTable tr .checkboxth").removeClass('sorting_asc');
        $("#inventoryProductsTable_length").addClass('mt-0');

        var formvalid = $("#myform").validate({
             ignore: ":hidden",
             rules: {
                discount_approval_less_25: {
                   min:0,
                   max:100,
                   number:true
                },
                 discount_approval_25_to_lakhs: {
                  min:0,
                  max:100,
                  number:true
                },
                discount_approval_above_lakhs: {
                  min:0,
                  max:100,
                  number:true
                },
                discount_approval_less_25_18k: {
                  min:0,
                  max:100,
                  number:true
                },
                discount_approval_25_100k_18k: {
                  min:0,
                  max:100,
                  number:true
                },
                discount_approval_gt_100k_18k: {
                  min:0,
                  max:100,
                  number:true
                }
            }
            });
          var formvalid = $("#depositform").validate({
           ignore: ":hidden",
           rules: {
              discount_deposit_less_25: {
               min:0,
               max:100,
               number:true
              },
              discount_deposit_25_to_lakhs: {
                min:0,
                max:100,
                number:true
              },
              discount_deposit_above_lakhs: {
                min:0,
                max:100,
                number:true
              },discount_deposit_less_25_18k: {
                min:0,
                max:100,
                number:true
              },
              discount_deposit_25_100k_18k: {
                min:0,
                max:100,
                number:true
              },
              discount_deposit_gt_100k_18k: {
                min:0,
                max:100,
                number:true
              }
          }
         });

          var formvalid = $("#invoiceform").validate({
           ignore: ":hidden",
           rules: {
                discount_invoice_less_25: {
                  min:0,
                  max:100,
                  number:true
                },
                discount_invoice_25_to_lakhs: {
                  min:0,
                  max:100,
                  number:true
                },
                discount_invoice_above_lakhs: {
                  min:0,
                  max:100,
                  number:true
                },discount_invoice_less_25_18k: {
                  min:0,
                  max:100,
                  number:true
                },
                discount_invoice_25_100k_18k: {
                  min:0,
                  max:100,
                  number:true
                },
                discount_invoice_gt_100k_18k: {
                  min:0,
                  max:100,
                  number:true
                }
            }
          });

          $("#btn-save-price-markup").click(function(){
              $("#price-markup-form").validate({
               ignore: ":hidden",
               rules: {
                    price_markup: {
                      min:0,
                      max:100,
                      number:true
                    }
                  }
              });
              if($("#price-markup-form").valid())
              {
                  $.ajax({
                      type: 'post',
                      url: '<?=URL::to('/customers/storepricemarkup');?>',
                      data: $("#price-markup-form").serialize(),
                      beforeSend: function(){
                        $("#btn-save-price-markup").prop("disabled",true);
                        showLoader();
                      },
                      success: function(response){
                          var res = JSON.parse(response);
                          $("#btn-save-price-markup").prop("disabled",false);
                          hideLoader();
                          if(res.status==true)
                          {
                              swal({
                                title: 'Success',
                                text: res.message,
                                type: 'success',
                                buttonClass: 'btn btn-primary'
                                //showSuccessButton: true,
                                //showConfirmButton: false,
                                //successButtonClass: 'btn btn-primary',
                                //successButtonText: 'Ok'
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
                  })
              }
            });
        var salesReturnListTable = $('#salesReturnList').DataTable({
          "language": {
            "infoEmpty": "No matched records found",
            "zeroRecords": "No matched records found",
            "emptyTable": "No data available in table",
          //"sProcessing": "<div id='loader'></div>"
        },
        "deferLoading": <?=$totalSalesReturn?>,
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "ajax":{
          "url": '<?=URL::to('/customers/salesreturnajaxlist');?>',
          "data": function(data, callback){
            data._token = "{{ csrf_token() }}";
            data.customer_id = $("#customer_id").val();
            showLoader();
            $(".dropdown").removeClass('show');
            $(".dropdown-menu").removeClass('show');
          },
          complete: function(response){
            hideLoader();
          }
        },
        "dom": '<"datatable_top_custom_lengthinfo"i>frti<"datatable_bottom_custom_length"l>p',
        "lengthChange": false,
        "order": [[ 4, "DESC" ]]
      });
      $('#salesReturnList .dataTables_filter input')
      .unbind() // Unbind previous default bindings
      .bind("input", function(e) { // Bind our desired behavior
          // If the length is 3 or more characters, or the user pressed ENTER, search
          if(this.value.length >= 3 || e.keyCode == 13) {
              // Call the API search function
              salesReturnListTable.search(this.value).draw();
            }
          // Ensure we clear the search if they backspace far enough
          if(this.value == "") {
            salesReturnListTable.search("").draw();
          }
          return;
        });
        //wallet transaction table
        var walletTransactionList = $('#walletTransactionList').DataTable({
          "language": {
            "infoEmpty": "No matched records found",
            "zeroRecords": "No matched records found",
            "emptyTable": "No data available in table",
            "search": "_INPUT_",
            "searchPlaceholder": "Search",
          //"sProcessing": "<div id='loader'></div>"
        },
        "deferLoading": <?=$totalTransactionCount?>,
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "ajax":{
          "url": '<?=URL::to('/customers/walletajaxlist');?>',
          "data": function(data, callback){
            data._token = "{{ csrf_token() }}";
            data.customer_id = $("#customer_id").val();
            showLoader();
            $(".dropdown").removeClass('show');
            $(".dropdown-menu").removeClass('show');
          },
          complete: function(response){
            hideLoader();
          }
        },
        "dom": '<"datatable_top_custom_lengthinfo custom-page-length d-flex flex-wrap"i   <"datatable_top_showroom_length mx-3"l>>frtip',
        //"lengthChange": false,
      });
    $("#walletTransactionList_length").addClass('mt-0');
    $("#walletTransactionList_length").addClass('height-35 mt-1 mb-0');
    $('#walletTransactionList .dataTables_filter input')
      .unbind() // Unbind previous default bindings
      .bind("input", function(e) { // Bind our desired behavior
          // If the length is 3 or more characters, or the user pressed ENTER, search
          if(this.value.length >= 3 || e.keyCode == 13) {
              // Call the API search function
              walletTransactionList.search(this.value).draw();
            }
          // Ensure we clear the search if they backspace far enough
          if(this.value == "") {
            walletTransactionList.search("").draw();
          }
          return;
        });

      //Payment list table
      var paymentListTable = $('#paymentListTable').DataTable({
          "language": {
            "infoEmpty": "No matched records found",
            "zeroRecords": "No matched records found",
            "emptyTable": "No data available in table",
            "search": "_INPUT_",
            "searchPlaceholder": "Search",
          //"sProcessing": "<div id='loader'></div>"
        },
        "deferLoading": <?=$totalPaymentCount?>,
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "ajax":{
          "url": '<?=URL::to('/customers/paymentajaxlist');?>',
          "data": function(data, callback){
            data._token = "{{ csrf_token() }}";
            data.payment_search_value = $("#txtPaymentSearch").val();
            data.customer_id = $("#customer_id").val();
            showLoader();
            $(".dropdown").removeClass('show');
            $(".dropdown-menu").removeClass('show');
          },
          complete: function(response){
            hideLoader();
          }
        },
        "dom": '<"datatable_top_custom_lengthinfo custom-page-length d-flex flex-wrap"i   <"datatable_top_showroom_length mx-3"l>>frtip',
        //"lengthChange": false,
      });
    $("#paymentListTable_length").addClass('mt-0');
    $("#paymentListTable_length").addClass('height-35 mt-1 mb-0');
    $('#paymenthistroy .dataTables_filter input')
      .unbind() // Unbind previous default bindings
      .bind("input", function(e) { // Bind our desired behavior
          // If the length is 3 or more characters, or the user pressed ENTER, search
          if(this.value.length >= 3 || e.keyCode == 13) {
              if(this.value.toLowerCase() == 'paid')
              {
                $("#txtPaymentSearch").val(1);  
              }
              else if(this.value.toLowerCase() == 'unpaid')
              {
                $("#txtPaymentSearch").val(0);  
              }
              else
              {
                $("#txtPaymentSearch").val(this.value); 
              }
              
              console.log(this.value);
              // Call the API search function
              paymentListTable.search(this.value).draw();
            }

          // Ensure we clear the search if they backspace far enough
          if(this.value == "") {
            $("#txtPaymentSearch").val('');
            paymentListTable.search("").draw();
          }
          return;
        });

        var creditNoteList = $('#creditNoteList').DataTable({
          "language": {
            "infoEmpty": "No matched records found",
            "zeroRecords": "No matched records found",
            "emptyTable": "No data available in table",
          //"sProcessing": "<div id='loader'></div>"
        },
        "deferLoading": <?=$totalCreditNote?>,
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "ajax":{
          "url": '<?=URL::to('/customers/creditnoteajaxlist');?>',
          "data": function(data, callback){
            data._token = "{{ csrf_token() }}";
            data.customer_id = $("#customer_id").val();
            showLoader();
            $(".dropdown").removeClass('show');
            $(".dropdown-menu").removeClass('show');
          },
          complete: function(response){
            hideLoader();
          }
        },
        "dom": '<"datatable_top_custom_lengthinfo"i>frti<"datatable_bottom_custom_length"l>p',
        "lengthChange": false,
        "order": [[ 4, "DESC" ]]
      });
      $('#creditNoteList .dataTables_filter input')
      .unbind() // Unbind previous default bindings
      .bind("input", function(e) { // Bind our desired behavior
          // If the length is 3 or more characters, or the user pressed ENTER, search
          if(this.value.length >= 3 || e.keyCode == 13) {
              // Call the API search function
              creditNoteList.search(this.value).draw();
            }
          // Ensure we clear the search if they backspace far enough
          if(this.value == "") {
            creditNoteList.search("").draw();
          }
          return;
        });
        showProductsStatatics();
        showTotalAmountStatatics();
    });
    function showPaymentTransaction(paymentId)
    {
        if(paymentId != '')
        {
            $.ajax({
                type: 'post',
                url: '<?=URL::to('/payment/getpaymenttransaction');?>',
                data:{customer_id:$("#customer_id").val(), payment_id: paymentId,_token:"{{ csrf_token() }}"},
                beforeSend: function(){
                    showLoader();
                },
                success: function(response){
                   var res = JSON.parse(response);
                   hideLoader();
                   if(res.status)
                   {
                      $("#paymenthistroy").addClass('hidden');
                      $("#paymenttransactionlist").html(res.html);
                      $("#paymenttransactionlist").removeClass('hidden');
                   }
                }
              });
        }
    }
    function showTotalAmountStatatics()
    {
        var ctx = document.getElementById("total_amount_statatics");
        if ( ctx === null ) return;

        var ctx4 = document.getElementById("total_amount_statatics").getContext("2d");
        var data4 = {
          labels: [
            "Paid Amount",
            "Unpaid Amount"
          ],
          datasets: [
            {
              data: [<?php echo round($totalPaidAmount); ?>,<?php echo round($totalUnPaidAmount); ?>],
              backgroundColor: [
                "#05d05b",
                "#00c1dd"

              ],
              hoverBackgroundColor: [
                "#05d05b",
                "#00c1dd"
              ]
            }]
          };
          var chartJsDoughnut = new Chart(ctx4, {
            type: "doughnut",
            data: data4,
            responsive: true,
            options: {
              legend: {
                display: false
              },
              maintainAspectRatio: true,
              tooltips: {
                mode: 'index',
                intersect: false,
                titleFontColor: "#000",
                titleMarginBottom: 10,
                backgroundColor: "rgba(255,255,255,.9)",
                bodyFontColor: "#000",
                borderColor: "#e9e9e9",
                bodySpacing: 10,
                borderWidth: 3,
                xPadding: 10,
                yPadding: 10,
              },
            }
          });
    }
    function showProductsStatatics() {
        var ctx = document.getElementById("products_statatics");
        if ( ctx === null ) return;

        var ctx4 = document.getElementById("products_statatics").getContext("2d");
        var data4 = {
          labels: [
            "Approval Products",
            "Sold Products",
            "Sales Return Products"
          ],
          datasets: [
            {
              data: [<?php echo isset($approvalProductCollection['totalCount']) ? $approvalProductCollection['totalCount'] : 0; ?>,<?php echo $totalSoldProductsCount; ?>, <?php echo $salesReturnProductsCount ?>],
              backgroundColor: [
                "#05d05b",
                "#00c1dd",
                "#fb9678"
              ],
              hoverBackgroundColor: [
                "#05d05b",
                "#00c1dd",
                "#fb9678"
              ]
            }]
          };
          var chartJsDoughnut = new Chart(ctx4, {
            type: "doughnut",
            data: data4,
            responsive: true,
            options: {
              legend: {
                display: false
              },
              tooltips: {
                mode: 'index',
                intersect: false,
                titleFontColor: "#000",
                titleMarginBottom: 10,
                backgroundColor: "rgba(255,255,255,.9)",
                bodyFontColor: "#000",
                borderColor: "#e9e9e9",
                bodySpacing: 10,
                borderWidth: 3,
                xPadding: 10,
                yPadding: 10,
              },
            }
          });
    }
    function showInventorySection(sectionId,currentSectionId)
    {
        showCustomerSection(sectionId,currentSectionId);
        $("#approval-tab .nav-item > .nav-link").removeClass('active');
        $("#approval-products-tab > .nav-link").addClass('active');
        $("#total-approvals .tabs .tab-content > .tab-pane").removeClass('active');
        $("#approval_products").addClass('active');
    }
    function showCustomerSection(sectionId,currentSectionId)
    {
	  var linkId = currentSectionId;
	  if(currentSectionId == '')
	  {
		  linkId = sectionId.replace('-','');
	  }
      $(".customer-info-container").addClass('hidden');
      $("#"+sectionId).removeClass("hidden");
      $(".customer-menu-container .nav-item").removeClass('active');
      $(".customer-menu-container .nav-item").removeClass('current-page');
      $("#"+linkId).parent().addClass('active');
      $("#"+linkId).parent().addClass('current-page');
      $(".nav-item").find('span').removeClass('color-color-scheme');
      $("#"+linkId).find('span').addClass('color-color-scheme');
      $("#approval-tab .nav-item > .nav-link").removeClass('active');
      $("#approval-tab .nav-item:first-child > .nav-link").addClass('active');
      $('html, body').animate({
        scrollTop: ($("#"+sectionId).offset().top - 95)
      }, 1500);
    }

    function showEditAddressModal(customerId,editType)
    {
      if(customerId != '')
      {
        $.ajax({
          url:'<?=URL::to('/customers/getdefaultbillingaddress');?>',
          method:"post",
          data:{customer_id: customerId,edit_type:editType,_token: "{{ csrf_token() }}"},
          beforeSend: function(){
            showLoader();
          },
          success: function(response){
            hideLoader();
            $("#edit-customer-modal #edit-customer-form").html(response);
            $("#edit-customer-modal").modal("show");
          }
        });
      }
    }
    function viewAttachment(customerId,attachmentType)
    {
      if(customerId != '')
      {
        $.ajax({
          url:'<?=URL::to('/customers/getcustomerattachment');?>',
          method:"post",
          data:{customer_id: customerId,attachment_type:attachmentType,_token: "{{ csrf_token() }}"},
          beforeSend: function(){
            showLoader();
          },
          success: function(response){
            hideLoader();
            $("#view-attachment-modal .modal-content").html(response);
            $("#view-attachment-modal").modal("show");
          }
        });
      }
    }
    function addGstinPan(customerId,attachmentType)
    {
      if(customerId != '')
      {
        $.ajax({
          url:'<?=URL::to('/customers/addcustomerattachment');?>',
          method:"post",
          data:{customer_id: customerId,attachment_type:attachmentType,_token: "{{ csrf_token() }}"},
          beforeSend: function(){
            showLoader();
          },
          success: function(response){
            hideLoader();
            $("#view-attachment-modal .modal-content").html(response);
            $("#view-attachment-modal").modal("show");
          }
        });
      }
    }
    function editGstinPan(customerId,attachmentType)
    {
      if(customerId != '')
      {
        $.ajax({
          url:'<?=URL::to('/customers/editgstinpancard');?>',
          method:"post",
          data:{customer_id: customerId,attachment_type:attachmentType,_token: "{{ csrf_token() }}"},
          beforeSend: function(){
            showLoader();
          },
          success: function(response){
            hideLoader();
            $("#view-attachment-modal .modal-content").html(response);
            $("#view-attachment-modal").modal("show");
          }
        });
      }
    }
    function showEditPersonalInfoModal(customerId)
    {
      if(customerId != '')
      {
        $.ajax({
          url:'<?=URL::to('/customers/editpersonalinfo');?>',
          method:"post",
          data:{customer_id: customerId,_token: "{{ csrf_token() }}"},
          beforeSend: function(){
            showLoader();
          },
          success: function(response){
            hideLoader();
            $("#view-attachment-modal .modal-content").html(response);
            $("#view-attachment-modal").modal("show");
          }
        });
      }
    }

</script>
@endsection