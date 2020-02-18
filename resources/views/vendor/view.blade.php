<?php
use App\Helpers\CommonHelper;
?>
@extends('layout.vendorlayout')

@section('title', 'View Vendor')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
    {{ Breadcrumbs::render('vendor.view', $id) }}
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
        <input type="hidden" id="vendor_id" value="<?=$id;?>">
          <!-- row start -->
          <div class="row customer-info-container hide-menu hidden" id="vendor_detail">
          <!-- title column start -->
          <div class="col-md-12 title">
            <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Vendor Information</h4>
          </div>
          <!-- content column start -->
          <div class="col-md-12 tab-content-style paragraph-m-zero d-flex">
            <div class="col">
              <h5>Personal Info <a class="pointer float-right text-primary" onclick="showEditPersonalInfoModal('<?=$id?>')">Edit</a></h5>
              <div class="col-inner personal-info-container">
                <p>Vendor ID: <?=$id?></p>
                <p>Name: <?=$vendorName?></p>
                <p>Email: <?=$vendorEmail?></p>
                <p>Contact No: <?=$vendorPhone?></p>
                <p>DMcode: <?=$vendorDMcode?></p>
              </div>
            </div>      
            <div class="col">
              <h5>Address <a class="pointer float-right text-primary" onclick="showEditAddressModal('<?=$id?>')">Edit</a></h5>
              <div class="col-inner">
              <p><?=isset($vendorAddress) ? $vendorAddress : ''?></p>
              <p><?=isset($vendorState) ? $vendorState : ''?></p>
              <div class="d-flex justify-content-between align-items-center vendor-address-container">
                  <span>GSTIN: <?=$vendorGstin?></span>
                  <span class="text-right">
                    <?php if (empty($vendorGstin) && empty($gstinAttachment)): ?>
                    <a class="text-white pointer" onclick="addGstinPan('<?=$id?>','gstin')">Add</a>
                    <?php else: ?>
                      <a class="text-white pointer" onclick="editGstinPan('<?=$id?>','gstin')">Edit</a>
                    <?php endif;?>
                    <?php if (!empty($gstinAttachment)): ?>
                      <a class="text-white pointer" onclick="viewAttachment('<?=$id?>','gstin')">View</a>
                    <?php endif;?>
                  </span>
                </div>
              </div>
            </div>      
            <div class="w-100 d-block my-2 d-xl-none"></div>
            
          </div>
          </div>
          <!-- Customer dashboard row start-->
         <div class="row  customer-info-container hide-menu" id="vendor_dashboard">
              <div class="col-md-12 p-0 widget-holder content-area">
                  <div class="widget-bg p-0">
                      <div class="widget-body clearfix">
                          <div class="col-md-12 title">
                            <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Vendor Dashboard</h4>
                          </div>
                          <div class="col-md-12 p-0 d-flex">
                              <div class="col-md-3 col-sm-6 widget-holder widget-full-height" id="totalapprovals" onclick="showCustomerSection('diamond-given',this.id)">
                                  <div class="widget-bg bg-color-scheme text-inverse pointer">
                                      <div class="widget-body clearfix">
                                          <div class="widget-counter">
                                              <h6>Total Diamond Given</h6>
                                              <h3 class="h1"><span class="counter"><?php echo isset($VendorDiamodIssueCnt) ? $VendorDiamodIssueCnt : 0; ?></span></h3><i class="material-icons list-icon">event_available</i>
                                          </div>
                                          <!-- /.widget-counter -->
                                      </div>
                                      <!-- /.widget-body -->
                                  </div>
                                  <!-- /.widget-bg -->
                              </div>
                              <div class="col-md-3 col-sm-6 widget-holder widget-full-height" id="totalinvoices" onclick="showCustomerSection('diamond-return',this.id)">
                                  <div class="widget-bg bg-primary text-inverse pointer">
                                      <div class="widget-body">
                                          <div class="widget-counter">
                                              <h6>Total Diamond Return</h6>
                                              <h3 class="h1"><span class="counter"><?php echo isset($VendorDiamodIssueCnt) ? $VendorDiamodReturnCnt : 0; ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                          </div>
                                          <!-- /.widget-counter -->
                                      </div>
                                      <!-- /.widget-body -->
                                  </div>
                                  <!-- /.widget-bg -->
                              </div>
                              <div class="col-md-3 col-sm-6 widget-holder widget-full-height" onclick="showCustomerSection('gold-given',this.id)">
                                  <div class="widget-bg bg-color-scheme text-inverse pointer">
                                      <div class="widget-body clearfix">
                                          <div class="widget-counter">
                                              <h6>Total Gold Given</h6>
                                              <h3 class="h1"><span class="counter"><?php echo isset($VendorGoldGivenCnt) ? $VendorGoldGivenCnt : 0; ?></span></h3><i class="material-icons list-icon">event_available</i>
                                          </div>
                                          <!-- /.widget-counter -->
                                      </div>
                                      <!-- /.widget-body -->
                                  </div>
                                  <!-- /.widget-bg -->
                              </div>
                              <div class="col-md-3 col-sm-6 widget-holder widget-full-height" onclick="showCustomerSection('gold-return',this.id)">
                                  <div class="widget-bg bg-color-scheme text-inverse pointer">
                                      <div class="widget-body clearfix">
                                          <div class="widget-counter">
                                              <h6>Total Gold Return</h6>
                                              <h3 class="h1"><span class="counter"><?php echo isset($VendorGoldReturnCnt) ? $VendorGoldReturnCnt : 0; ?></span></h3><i class="material-icons list-icon">event_available</i>
                                          </div>
                                          <!-- /.widget-counter -->
                                      </div>
                                      <!-- /.widget-body -->
                                  </div>
                                  <!-- /.widget-bg -->
                              </div>
                          </div>
                          <div class="col-md-12 p-0 d-flex">
                                <div class="col-md-3 col-sm-6 widget-holder widget-full-height" id="" onclick="showCustomerSection('costing-accepted',this.id)">
                                    <div class="widget-bg bg-primary text-inverse pointer">
                                        <div class="widget-body">
                                            <div class="widget-counter">
                                                <h6>Total Costing Accepted</h6>
                                                <h3 class="h1"><span class="counter"><?php echo isset($totalcount) ? $totalcount : 0; ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                            </div>
                                            <!-- /.widget-counter -->
                                        </div>
                                        <!-- /.widget-body -->
                                    </div>
                                    <!-- /.widget-bg -->
                                </div>
                                <div class="col-md-3 col-sm-6 widget-holder widget-full-height" onclick="showCustomerSection('costing-rejected',this.id)">
                                    <div class="widget-bg bg-color-scheme text-inverse pointer">
                                        <div class="widget-body clearfix">
                                            <div class="widget-counter">
                                                <h6>Total Costing Rejected</h6>
                                                <h3 class="h1"><span class="counter"><?php echo isset($totalcountReject) ? $totalcountReject : 0; ?></span></h3><i class="material-icons list-icon">event_available</i>
                                            </div>
                                            <!-- /.widget-counter -->
                                        </div>
                                        <!-- /.widget-body -->
                                    </div>
                                    <!-- /.widget-bg -->
                                </div>
                                <div class="col-md-3 col-sm-6 widget-holder widget-full-height" onclick="showCustomerSection('paid-invoice',this.id)" id="">
                                    <div class="widget-bg bg-primary text-inverse pointer">
                                        <div class="widget-body">
                                            <div class="widget-counter">
                                                <h6>Total Paid Invoice </h6>
                                                <h3 class="h1"><span class="counter"><?php echo isset($Acceptedtotalcount) ? $Acceptedtotalcount : 0; ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                            </div>
                                            <!-- /.widget-counter -->
                                        </div>
                                        <!-- /.widget-body -->
                                    </div>
                                    <!-- /.widget-bg -->
                                </div>
                                <div class="col-md-3 col-sm-6 widget-holder widget-full-height" onclick="showCustomerSection('unpaid-invoice',this.id)" id="creditnote">
                                    <div class="widget-bg bg-color-scheme text-inverse pointer">
                                        <div class="widget-body">
                                            <div class="widget-counter">
                                                <h6>Total Unpaid Invoice</h6>
                                                <h3 class="h1"><span class="counter"><?php echo isset($totalcount) ? $totalcount : 0; ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
                                                <!-- <h3 class="h1">&dollar;<span class="counter">741</span></h3><i class="material-icons list-icon">add_shopping_cart</i> -->
                                            </div>
                                            <!-- /.widget-counter -->
                                        </div>
                                        <!-- /.widget-body -->
                                    </div>
                                    <!-- /.widget-bg -->
                                </div>
                                
                          </div>
                          <div class="col-md-12 p-0 d-flex">
                            <div class="col-md-3 col-sm-6 widget-holder widget-full-height" onclick="showCustomerSection('payment-history',this.id)" id="creditnote">
                                <div class="widget-bg bg-color-scheme text-inverse pointer">
                                    <div class="widget-body">
                                        <div class="widget-counter">
                                            <h6>Total Payment History</h6>
                                            <h3 class="h1"><span class="counter"><?php echo isset($totalPaymentListCnt) ? $totalPaymentListCnt : 0; ?></span></h3><i class="material-icons list-icon">add_shopping_cart</i>
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
          </div>
          <!-- Vendor dashboard row end-->
        <!-- row start -->
       <div class="row hidden customer-info-container" id="diamond-given">
          <!-- title column start -->
          <div class="col-md-12 title">
            <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">List Given Diamonds</h4>
          </div>
          <!-- content column start -->
          <div class="col-md-12">
          <?php
          $DiamondIssueGivenCnt = isset($VendorDiamodIssueCnt) ? $VendorDiamodIssueCnt : 10;
          $DiamondGivenData = isset($GivenDiamondColl) ? $GivenDiamondColl : '';
          ?>
            <?php if ($DiamondIssueGivenCnt > 0): ?>
                <table class="table table-striped table-center table-head-box checkbox checkbox-primary custom-scroll" id="diamondIssueTable">
                  <thead>
                    <tr class="bg-primary">
                      <th>Po Number</th>
                      <th>Issue Date</th>
                      <th>Voucher No</th>
                      <th>Total Wt</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  @foreach ($DiamondGivenData as $key => $Givenproduct)
                  <?php //echo "<pre>";print_r($Givenproduct);exit; ?>
                    <tr>
                      <td>{{$Givenproduct->po_number}}</td>
                      <td>{{$Givenproduct->purchased_at}}</td>
                      <td>{{$Givenproduct->issue_voucher_no}}</td>
                      <td>{{$Givenproduct->total_weight}}</td>
                      <td>                      
                      <a title="View transactions" href="javascript:void(0)" id="btn_view"  class="color-content table-action-style" data-id="<?php echo $Givenproduct->issue_voucher_no; ?>"><i class="material-icons md-18">remove_red_eye</i></a>
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Po Number</th>
                      <th>Issue Date</th>
                      <th>Voucher No</th>
                      <th>Total Wt</th>
                      <th>Action</th>
                    </tr>
                  </tfoot>
                </table>
                <?php else: ?>
                  <p><?php echo Config::get('constants.message.customer_inventory_product_not_available'); ?></p>
                <?php endif;?>
            </div>
          </div>
          
            <div class="row hidden customer-info-container" id="diamond-return">
            <!-- title column start -->
            <div class="col-md-12 title">
              <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Diamond Return List</h4>
            </div>
            <!-- content column start -->
            <div class="col-md-12">
              <?php
              $totalIReturnCount = isset($VendorDiamodReturnCnt) ? $VendorDiamodReturnCnt : 10;
              $ReturnData = isset($VendorDiamondRetunColl) ? $VendorDiamondRetunColl : '';
              ?>
              <?php if (count($ReturnData) > 0): ?>
                <div class="table-responsive" style="overflow-x: hidden;">
                  <table class="table table-striped table-center" id="diamondReturnListTable" >
                    <thead>
                      <tr class="bg-primary">
                        <th>Stone Shape.</th>
                        <th>Diamond Weight</th>
                        <th>Sieve Size</th>
                        <th>MM Size</th>
                        <th>Voucher No</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach ($ReturnData as $key => $returnDiamond)
                    <tr>
                      <td>{{$returnDiamond->stone_shape}}</td>
                      <td>{{$returnDiamond->diamond_weight}}</td>
                      <td>{{$returnDiamond->sieve_size}}</td>
                      <td>{{$returnDiamond->mm_size}}</td>
                      <td>{{$returnDiamond->issue_voucher_no}}</td>
                      <td>{{$returnDiamond->updated_at}}</td>
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
          <div class="row hidden customer-info-container" id="gold-given">
            <!-- title column start -->
            <div class="col-md-12 title">
              <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Gold Given List</h4>
            </div>
            <!-- content column start -->
            <div class="col-md-12">
              <?php
              $totalIGivenCount = isset($VendorGoldGivenCnt) ? $VendorGoldGivenCnt : 10;
              $GivenData = isset($VendorGoldGivenColl) ? $VendorGoldGivenColl : '';
              ?>
              <?php if (count($GivenData) > 0): ?>
                <div class="table-responsive" style="overflow-x: hidden;">
                  <table class="table table-striped table-center" id="goldGivenListTable" >
                    <thead>
                      <tr class="bg-primary">
                        <th>Po Number</th>
                        <th>Voucher No</th>
                        <th>Total Wt</th>
                        <th>Type</th>
                        <th>Total Amt</th>
                        <th>Issue Date</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach ($GivenData as $key => $GivenCollData)
                    <?php //echo "<pre>";print_r($GivenCollData);exit; ?>
                    <tr>
                      <td>{{$GivenCollData->gold_po}}</td>
                      <td>{{$GivenCollData->gold_voucher_no}}</td>
                      <td>{{$GivenCollData->metal_weight}}</td>
                      <td>{{$GivenCollData->gold_type}}</td>
                      <td>{{$GivenCollData->amount_paid}}</td>
                      <td>{{$GivenCollData->purchased_at}}</td>
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
          <div class="row hidden customer-info-container" id="gold-return">
            <!-- title column start -->
            <div class="col-md-12 title">
              <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Gold Return List</h4>
            </div>
            <!-- content column start -->
            <div class="col-md-12">
              <?php
              $totalIGivenCount = isset($VendorGoldReturnCnt) ? $VendorGoldReturnCnt : 10;
              $ReturnData = isset($VendorGoldReturnColl) ? $VendorGoldReturnColl : '';
              ?>
              <?php if (count($ReturnData) > 0): ?>
                <div class="table-responsive" style="overflow-x: hidden;">
                  <table class="table table-striped table-center" id="goldReturnListTable" >
                    <thead>
                      <tr class="bg-primary">
                        <th>Po Number</th>
                        <th>Voucher No</th>
                        <th>Total Wt</th>
                        <th>Type</th>
                        <th>Total Amt</th>
                        <th>Issue Date</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach ($ReturnData as $key => $GoldReturnColl)
                    <tr>
                      <td>{{$GoldReturnColl->gold_po}}</td>
                      <td>{{$GoldReturnColl->gold_voucher_no}}</td>
                      <td>{{$GoldReturnColl->metal_weight}}</td>
                      <td>{{$GoldReturnColl->gold_type}}</td>
                      <td>{{$GoldReturnColl->amount_paid}}</td>
                      <td>{{$GoldReturnColl->purchased_at}}</td>
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
                <!-- row start-->
                <div class="row hidden customer-info-container" id="costing-accepted">
                    <!-- title column start -->
                    <div class="col-md-12 title">
                      <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Rejected Products List</h4>
                    </div>
                    <!-- content column start -->
                    <div class="col-md-12">
                        <?php
                        $totalSalesReturn = isset($Acceptedtotalcount) ? $Acceptedtotalcount : 10;
                        $CostingAcceptedList = isset($CostingAccepted) ? $CostingAccepted : '';
                        ?>
                        <?php if (count($CostingAcceptedList) > 0): ?>
                        <div class="table-responsive" style="overflow-x: hidden;">
                        <table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll" id="costingacceptedTable">
                            <thead>
                                <tr class="bg-primary">
                                  <th>Sku</th>
                                  <th>Certificate/Betch No.</th>
                                  <th>Branding</th>
                                  <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($CostingAcceptedList as $key => $costingdata)
                              <tr>
                              <td>{{ $costingdata->sku }}</td>
                              @if(!empty($costingdata->certificate_no) ||  $costingdata->certificate_no != 0 )
                              <td>{{ $costingdata->certificate_no }}</td>
                              @else
                              <td>{{ $costingdata->item }}</td>
                              @endif
                              <td>{{ $costingdata->branding }}</td>
                              <td>
                              <a href="javascript:void(0)" class="color-content table-action-style"><i title="Detail" onclick="showDetail('<?php echo $costingdata->id; ?>')" class="material-icons list-icon">info</i></a>
                              </td>
                              </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                  <th>Sku</th>
                                  <th>Certificate/Betch No.</th>
                                  <th>Branding</th>
                                  <th>Detail</th>
                                </tr>
                            </tfoot>
                          </table>
                          </div>
                          <?php else: ?>
                            <p><?php echo Config::get('constants.message.costing_not_available'); ?></p>
                          <?php endif;?>
                    </div>
                </div>
                <!-- row end -->
                <!-- credit note start-->
                <div class="row hidden customer-info-container" id="costing-rejected">
                    <!-- title column start -->
                    <div class="col-md-12 title">
                      <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Rejected Products List</h4>
                    </div>
                    <!-- content column start -->
                    <div class="col-md-12">
                        <?php
                        $totalSalesReturn = isset($totalcountReject) ? $totalcountReject : 10;
                        $CostingRejectedList = isset($CostingRejected) ? $CostingRejected : '';
                        ?>
                        <?php if (count($CostingRejectedList) > 0): ?>
                        <div class="table-responsive" style="overflow-x: hidden;">
                        <table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll" id="costingrejectedTable">
                            <thead>
                                <tr class="bg-primary">
                                  <th>Sku</th>
                                  <th>Certificate/Betch No.</th>
                                  <th>Branding</th>
                                  <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($CostingRejectedList as $key => $costingdata)
                              <tr>
                              <td>{{ $costingdata->sku }}</td>
                              @if(!empty($costingdata->certificate_no) ||  $costingdata->certificate_no != 0 )
                              <td>{{ $costingdata->certificate_no }}</td>
                              @else
                              <td>{{ $costingdata->item }}</td>
                              @endif
                              <td>{{ $costingdata->branding }}</td>
                              <td>
                              <a href="javascript:void(0)" class="color-content table-action-style"><i title="Detail" onclick="showDetail('<?php echo $costingdata->id; ?>')" class="material-icons list-icon">info</i></a>
                              </td>
                              </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                  <th>Sku</th>
                                  <th>Certificate/Betch No.</th>
                                  <th>Branding</th>
                                  <th>Detail</th>
                                </tr>
                            </tfoot>
                          </table>
                          </div>
                          <?php else: ?>
                            <p><?php echo Config::get('constants.message.costing_not_available'); ?></p>
                          <?php endif;?>
                    </div>
                </div>
                <!-- credit note end-->
                <!-- row start -->
                <div class="row hidden customer-info-container" id="paid-invoice">
                    <!-- title column start -->
                    <div class="col-md-12 title">
                      <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Paid Invoice List</h4>
                    </div>
                    <!-- content column start -->
                    <div class="col-md-12">
                        <?php
                        $totalPaidInvoiceCnt = isset($paidtotalcount) ? $paidtotalcount : 10;
                        $paidInvoiceList = isset($paid) ? $paid : '';
                        ?>
                        <?php //if (count($CostingRejectedList) > 0): ?>
                        <div class="table-responsive" style="overflow-x: hidden;">
                        <table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll" id="PaidTable">
                            <thead>
                                <tr class="bg-primary">
                                  <th>No</th>
                                  <th>Customer Name</th>
                                  <th>Invoice Number</th>
                                  <th>Invoice Amount</th>
                                  <th>Due Date</th>
                                  <th>Payment Form</th>
                                  <th>Payment Header</th>
                                  <th>Account Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($paidInvoiceList as $key => $value)
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $value->customer_name}}</td>
                                    <td>{{ $value->invoice_number}}</td>
                                    <td><?=CommonHelper::covertToCurrency($value->invoice_amount);?></td>
                                    <td>{{ $value->due_date}}</td>
                                    <td>{{ $value->payment_form}}</td>
                                    <td>
                                        {{ $value->name}}
                                    </td>
                                    <td value="{{$value->account_status}}">
                                      <a  href="{{action('VendorController@paidshow',$value->id)}}#paid-invoice">
                                        <i class="material-icons"  title="View Transaction">remove_red_eye</i></a> 
                                    </td>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                  <th>No</th>
                                  <th>Customer Name</th>
                                  <th>Invoice Number</th>
                                  <th>Invoice Amount</th>
                                  <th>Due Date</th>
                                  <th>Payment Form</th>
                                  <th>Payment Header</th>
                                  <th>Account Status</th>
                                </tr>
                            </tfoot>
                          </table>
                          </div>
                          <?php //else: ?>
                            <?php //<p>No quotation available for this customer</p> ?>
                          <?php //endif;?>
                    </div>
                </div>         
                
                <div class="row hidden customer-info-container" id="unpaid-invoice">
                    <!-- title column start -->
                    <div class="col-md-12 title">
                      <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Unpaid Products List</h4>
                    </div>
                    <!-- content column start -->
                    <div class="col-md-12">
                        <?php
                        $totalUnpaidCnt = isset($totalcount) ? $totalcount : 10;
                        $UnpaidList = isset($UnpaidCollList) ? $UnpaidCollList : '';
                        ?>
                        <?php if (count($UnpaidList) > 0): ?>
                        <div class="table-responsive" style="overflow-x: hidden;">
                        <table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll dataTable" id="UnpaidTable" >
                          <thead>
                              <tr class="bg-primary">
                                  <th>No</th>
                                  <th>Customer Name</th>
                                  <th>Invoice Number</th>
                                  <th>Invoice Amount</th>
                                  <th>Due Date</th>
                                  <th>Payment Form</th>
                                  <th>Payment Header</th>
                                  <th>Action</th>
                                  
                                </tr>
                             </thead>
                             <tbody>
                              @foreach ($UnpaidList as $value)
                                  <tr>
                                      
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $value->customer_name}}</td>
                                    <td>{{ $value->invoice_number}}</td>
                                    <td> <?= CommonHelper::covertToCurrency($value->invoice_amount)?></td>
                                    <?php
                                    $due_date = isset($value->due_date) ? $value->due_date : '-';
                                    ?>
                                    <td>{{ $due_date}}</td>
                                    <td>{{ $value->payment_form}}</td>
                                     <td>
                                        {{ $value->name}}
                                    </td>
                                     <td>
                                        <a  href="{{action('VendorController@unpaidshow',$value->id)}}#unpaid-invoice" onclick=" "><i class="material-icons"  title="View Transaction">remove_red_eye</i></a>
                                    </td>
                                </tr>
                            @endforeach
                          </tbody>
                      </table>
                      </div>
                      <?php else: ?>
                        <p><?php echo Config::get('constants.message.costing_not_available'); ?></p>
                      <?php endif;?>
                    </div>
                </div>
                    <!-- row start -->
                    <div class="row hidden customer-info-container" id="payment-history">
                    <!-- title column start -->
                    <div class="col-md-12 title">
                      <h4 class="fs-18 border-b-light-1 mt-0 mb-3 pb-2">Payment History List</h4>
                    </div>
                    <!-- content column start -->
                    <div class="col-md-12">
                        <?php
                        $PaymentListCnt = isset($totalPaymentListCnt) ? $totalPaymentListCnt : 10;
                        $PaymentCollection = isset($PaymentHistoryList) ? $PaymentHistoryList : '';
                        ?>
                        <?php if (count($PaymentCollection) > 0): ?>
                        <div class="table-responsive" style="overflow-x: hidden;">
                        <table class="table table-striped thumb-sm table-center table-head-box checkbox checkbox-primary custom-scroll dataTable" id="PaymentTable" >
                          <thead>
                              <tr class="bg-primary">
                                  <th>No</th>
                                  <th>Customer Name</th>
                                  <th>Invoice Number</th>
                                  <th>Invoice Amount</th>
                                  <th>Due Date</th>
                                  <th>Payment Form</th>
                                  <th>Payment Header</th>
                                  <th>Action</th>
                                  
                                </tr>
                             </thead>
                             <tbody>
                              @foreach ($UnpaidList as $value)
                                  <tr>
                                      
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $value->customer_name}}</td>
                                    <td>{{ $value->invoice_number}}</td>
                                    <td> <?= CommonHelper::covertToCurrency($value->invoice_amount)?></td>
                                    <td>{{ $value->due_date}}</td>
                                    <td>{{ $value->payment_form}}</td>
                                     <td>
                                        {{ $value->name}}
                                    </td>
                                     <td>
                                        <a  href="{{action('VendorController@unpaidshow',$value->id)}}#unpaid-invoice" onclick=" "><i class="material-icons"  title="View Transaction">remove_red_eye</i></a>
                                    </td>
                                </tr>
                            @endforeach
                          </tbody>
                      </table>
                      </div>
                      <?php else: ?>
                        <p><?php echo Config::get('constants.message.costing_not_available'); ?></p>
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

<div class="modal fade bs-modal-lg view" id="view-attachment-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-color-scheme modal-lg">
    <div class="modal-content">
    <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-header text-inverse">
            <h5 class="modal-title" id="myLargeModalLabel">Vendor Diamond Information</h5>
        </div>
    <div class="modal-body">
    </div>
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
<div class="modal fade bs-modal-lg modal-color-scheme costing_showDetail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <button type="button" class="close pointer" data-dismiss="modal" aria-hidden="true">×</button>
          <div class="modal-header text-inverse">
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
<style>
.product-img{max-width: 40px;}
</style>
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="<?=URL::to('/');?>/js/jquery.waypoints.js"></script>
<script src="<?=URL::to('/');?>/js/jquery.counterup.min.js"></script>

<script>
  $(document).ready(function(){
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
          swal({
                title: 'Are you sure?',
                text: "<?php echo Config::get('constants.message.sales_credit_note_generate_confirmation'); ?>",
                type: 'info',
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
                }).then(function() {
                    generateCretidNote.addClass('disabled');
                    viewCreditNote.removeClass('disabled');
                    window.location.href = href;
                })
      });

      
      $("#chkAllProduct").click(function(){
          $('.chkProduct').prop('checked', this.checked);
      });

      var diamondIssueTable = $('#diamondIssueTable').DataTable({ // Diamond Given Table
      "language": {
        "infoEmpty": "No matched records found",
        "zeroRecords": "No matched records found",
        "emptyTable": "No data available in table"
      },
      "deferLoading": <?=$VendorDiamodIssueCnt?>,
      "processing": true,
      "serverSide": true,
      "serverMethod": "post",
      "ajax":{
        "url": '<?=URL::to('/vendor/vendordiamondgivenajaxlist');?>',
        "data": function(data, callback){
          data._token = "{{ csrf_token() }}";
          data.vendor_id = $("#vendor_id").val();
          showLoader();
          $(".dropdown").removeClass('show');
          $(".dropdown-menu").removeClass('show');
        },
        complete: function(response){
          hideLoader();
          console.log(response);
        }
      },
      "columnDefs": [{ "orderable": false, "targets": [4] }],
    });

    
    var diamondReturnListTable = $('#diamondReturnListTable').DataTable({ // Diamond Return List Table
      "language": {
        "infoEmpty": "No matched records found",
        "zeroRecords": "No matched records found",
        "emptyTable": "No data available in table"
      },
      "deferLoading": <?=$VendorDiamodIssueCnt?>,
      "processing": true,
      "serverSide": true,
      "serverMethod": "post",
      "ajax":{
        "url": '<?=URL::to('/vendor/vendordiamondreturnajaxlist');?>',
        "data": function(data, callback){
          data._token = "{{ csrf_token() }}";
          data.vendor_id = $("#vendor_id").val();
          showLoader();
          $(".dropdown").removeClass('show');
          $(".dropdown-menu").removeClass('show');
        },
        complete: function(response){
          hideLoader();
          console.log(response);
        }
      },
    });
    

    var goldGivenListTable = $('#goldGivenListTable').DataTable({ // Diamond Return List Table
      "language": {
        "infoEmpty": "No matched records found",
        "zeroRecords": "No matched records found",
        "emptyTable": "No data available in table"
      },
      "deferLoading": <?=$VendorGoldGivenCnt?>,
      "processing": true,
      "serverSide": true,
      "serverMethod": "post",
      "ajax":{
        "url": '<?=URL::to('/vendor/vendorgoldgivenajaxlist');?>',
        "data": function(data, callback){
          data._token = "{{ csrf_token() }}";
          data.vendor_id = $("#vendor_id").val();
          showLoader();
          $(".dropdown").removeClass('show');
          $(".dropdown-menu").removeClass('show');
        },
        complete: function(response){
          hideLoader();
          console.log(response);
        }
      },
    });
    
    var goldReturnListTable = $('#goldReturnListTable').DataTable({ // Diamond Return List Table
      "language": {
        "infoEmpty": "No matched records found",
        "zeroRecords": "No matched records found",
        "emptyTable": "No data available in table"
      },
      "deferLoading": <?=$VendorGoldGivenCnt?>,
      "processing": true,
      "serverSide": true,
      "serverMethod": "post",
      "ajax":{
        "url": '<?=URL::to('/vendor/vendorgoldreturnajaxlist');?>',
        "data": function(data, callback){
          data._token = "{{ csrf_token() }}";
          data.vendor_id = $("#vendor_id").val();
          showLoader();
        },
        complete: function(response){
          hideLoader();
        }
      },
    });

    

    var costingacceptedTable = $('#costingacceptedTable').DataTable({ // Diamond Return List Table
      "language": {
        "infoEmpty": "No matched records found",
        "zeroRecords": "No matched records found",
        "emptyTable": "No data available in table"
      },
      "deferLoading": <?=$totalSalesReturn?>,
      "processing": true,
      "serverSide": true,
      "serverMethod": "post",
      "ajax":{
        "url": '<?=URL::to('/vendor/costingaacceptedajaxlist');?>',
        "data": function(data, callback){
          data._token = "{{ csrf_token() }}";
          data.vendor_id = $("#vendor_id").val();
          showLoader();
        },
        complete: function(response){
          hideLoader();
        }
      },
      "columnDefs": [{ "orderable": false, "targets": [3] }],
    });

    
    var costingrejectedTable = $('#costingrejectedTable').DataTable({ // Diamond Return List Table
      "language": {
        "infoEmpty": "No matched records found",
        "zeroRecords": "No matched records found",
        "emptyTable": "No data available in table"
      },
      "deferLoading": <?=$totalcountReject?>,
      "processing": true,
      "serverSide": true,
      "serverMethod": "post",
      "ajax":{
        "url": '<?=URL::to('/vendor/costingrejectedajaxlist');?>',
        "data": function(data, callback){
          data._token = "{{ csrf_token() }}";
          data.vendor_id = $("#vendor_id").val();
          showLoader();
        },
        complete: function(response){
          hideLoader();
        }
      },
      "columnDefs": [{ "orderable": false, "targets": [3] }],
    });


    var costingrejectedTable = $('#PaidTable').DataTable({
        "language": {
        "infoEmpty": "No matched records found",
        "zeroRecords": "No matched records found",
        "emptyTable": "No data available in table",
        //"sProcessing": "<div id='loader'></div>"
      },
      "deferLoading": <?=$totalcount?>,
      "processing": true,
      "serverSide": true,
      "serverMethod": "post",
      "ajax":{
        "url": "{{action('VendorController@vendorpaidpayment_response')}}",
        "data": function(data, callback){
          data._token = "{{ csrf_token() }}";
        },
      },
      "columnDefs": [ {
      "targets": [5,6,7],
      "orderable": false
      }
    ]  
    });
    

    var UnpaidTable = $('#UnpaidTable').DataTable({
        "language": {
        "infoEmpty": "No matched records found",
        "zeroRecords": "No matched records found",
        "emptyTable": "No data available in table",
        //"sProcessing": "<div id='loader'></div>"
      },
      "deferLoading": <?=$totalUnpaidCnt?>,
      "processing": true,
      "serverSide": true,
      "serverMethod": "post",
      "ajax":{
        "url": "{{action('VendorController@vendor_unpaidresponse')}}",
        "data": function(data, callback){
          data._token = "{{ csrf_token() }}";
          data.vendor_id = $("#vendor_id").val();
        },
      },
      "columnDefs": [ {
      "targets": [5,6,7],
      "orderable": false
      }
    ]  
    });

    
    var PaymentTable = $('#PaymentTable').DataTable({
        "language": {
        "infoEmpty": "No matched records found",
        "zeroRecords": "No matched records found",
        "emptyTable": "No data available in table",
        //"sProcessing": "<div id='loader'></div>"
      },
      "deferLoading": <?=$PaymentListCnt?>,
      "processing": true,
      "serverSide": true,
      "serverMethod": "post",
      "ajax":{
        "url": "{{action('VendorController@vendor_paymenthistresponse')}}",
        "data": function(data, callback){
          data._token = "{{ csrf_token() }}";
          data.vendor_id = $("#vendor_id").val();
        },
      },
      "columnDefs": [ {
      "targets": [5,6,7],
      "orderable": false
      }
    ]  
    });

    $(document).on('click','#btn_view', function() {
			var id = $(this).attr('data-id');
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				url: "{{action('VendorController@diamondissuelist')}}",
				data: {
				"_token": '{{ csrf_token() }}',
				"id": id,
				},
				success: function(data) {
				  $('.modal-body').html(data.html);
				  $('.view').modal('show');
				}
		   });
		});
});

// For Costing Data Display Popup
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
  function showInventorySection(sectionId,currentSectionId)
  {
      showCustomerSection(sectionId,currentSectionId);
      $("#approval-tab .nav-item > .nav-link").removeClass('active');
      $("#approval-products-tab > .nav-link").addClass('active');
  }
  function showCustomerSection(sectionId,currentSectionId)
  {
  var linkId = currentSectionId;
  if(currentSectionId == '')
  {
    linkId = sectionId.toString().replace('-','');
  }
  
    $(".customer-info-container").addClass('hidden');
    $("#"+sectionId).removeClass("hidden");
    $(".vendor-menu-container .nav-item").removeClass('active');
    $(".vendor-menu-container .nav-item").removeClass('current-page');
    $("#"+linkId).parent().addClass('active');
    $("#"+linkId).parent().addClass('current-page');
    $(".nav-item").find('span').removeClass('color-color-scheme');
    if(linkId != "vendor_dashboard"){
      $("#"+linkId).find('span').addClass('color-color-scheme');
    }
    
    $("#approval-tab .nav-item > .nav-link").removeClass('active');
    $("#approval-tab .nav-item:first-child > .nav-link").addClass('active');
    $('html, body').animate({
      scrollTop: ($("#"+sectionId).offset().top - 95)
    }, 1500);
  }

  function showEditAddressModal(vendorId)
  {
    if(vendorId != '')
    {
      $.ajax({
        url:'<?=URL::to('/vendor/getaddress');?>',
        method:"post",
        data:{vendor_id: vendorId,_token: "{{ csrf_token() }}"},
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
  function viewAttachment(vendorId,attachmentType)
  {
    if(customerId != '')
    {
      $.ajax({
        url:'<?=URL::to('/vendor/getvendorattachment');?>',
        method:"post",
        data:{vendor_id: vendorId,attachment_type:attachmentType,_token: "{{ csrf_token() }}"},
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
  function addGstinPan(vendorId,attachmentType)
  {
    if(customerId != '')
    {
      $.ajax({
        url:'<?=URL::to('/vendor/addvendorattachment');?>',
        method:"post",
        data:{vendor_id: vendorId,gstnumber:gstnumber,attachment_type:attachmentType,_token: "{{ csrf_token() }}"},
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
  function editGstinPan(vendorId,attachmentType)
  {
    if(vendorId != '')
    {
      $.ajax({
        url:'<?=URL::to('/vendor/editgstinpancard');?>',
        method:"post",
        data:{vendor_id: vendorId,attachment_type:attachmentType,_token: "{{ csrf_token() }}"},
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
  function showEditPersonalInfoModal(vendorId)
  {
    if(vendorId != '')
    {
      $.ajax({
        url:'<?=URL::to('/vendor/editpersonalinfo');?>',
        method:"post",
        data:{vendor_id: vendorId,_token: "{{ csrf_token() }}"},
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