@extends('layout.mainlayout')

@section('title', 'Settings')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
      {{ Breadcrumbs::render('settings') }}
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
            <div class="widget-body clearfix">
              @if ($message = Session::get('success'))
              <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <i class="material-icons list-icon">check_circle</i>
                <strong>Success</strong>: {{ $message }}
              </div>
              @endif
              <div class="tabs tabs-vertical">
                  <ul class="nav nav-tabs flex-column">
                      <li class="nav-item" aria-expanded="true"><a class="nav-link active" href="#general-tab-v1" data-toggle="tab" aria-expanded="false"><?php echo config('constants.labels.setting_page.general_setting'); ?></a>
                      </li>
                      <li class="nav-item"><a class="nav-link" href="#showroom-tab-v1" data-toggle="tab" aria-expanded="false"><?php echo config('constants.labels.setting_page.showroom'); ?></a>
                      </li>
                      <li class="nav-item"><a class="nav-link" href="#daimond-tab-v1" data-toggle="tab" aria-expanded="false"><?php echo config('constants.labels.setting_page.diamond_page'); ?></a>
                      </li>
                      <li class="nav-item"><a class="nav-link" href="#costing-tab-v1" data-toggle="tab" aria-expanded="false"><?php echo config('constants.labels.setting_page.costing'); ?></a>
                      </li>
                      <li class="nav-item"><a class="nav-link" href="#inventory-tab-v1" data-toggle="tab" aria-expanded="false"><?php echo config('constants.labels.setting_page.inventory'); ?></a>
                      </li>
                      <li class="nav-item"><a class="nav-link" href="#cache-tab-v1" data-toggle="tab" aria-expanded="false"><?php echo config('constants.labels.setting_page.cache'); ?></a>
                      </li>
                      <li class="nav-item"><a class="nav-link" href="#backup-tab-v1" data-toggle="tab" aria-expanded="false"><?php echo config('constants.labels.setting_page.backup'); ?></a>
                      </li>

                  </ul>
                  <!-- /.nav-tabs -->
                  <div class="tab-content">
				  <!-- general setting -->
                      <div class="tab-pane active" id="general-tab-v1" aria-expanded="true">
                         <div class="img-thumbnail p-3">
                          @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                </ul>
                            </div>
                        @endif

                        {!! Form::open(array('route' => 'settings.save','method'=>'POST', 'files'=>'true')) !!}
                          <div class="dynamicadd" id="dynamicadd">
                            <div class="form-group row">
                              <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.pagination'); ?></label>
                              <div class="col-lg-9 col-md-9 col-sm-12">
                                <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.pagination')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.pagination')];
}
?>
                              {!! Form::number('settings['.config('constants.settings.keys.pagination').']', $paginationLimitValue, array('class' => 'form-control')) !!}
                              </div>
                            </div>


                          </div>
                          <div class="form-actions">
                              <div class="form-group row">
                                  <div class="col-md-9 ml-md-auto btn-list">
                                      <button class="btn btn-primary" type="submit">Submit</button>
                                      <button class="btn btn-outline-default" type="reset">Cancel</button>
                                  </div>
                              </div>
                          </div>
                      {!! Form::close() !!}
                      </div>
                      </div>
					  <!-- showroom setting -->
                      <div class="tab-pane" id="showroom-tab-v1" aria-expanded="false">
                          <div class="img-thumbnail p-3">
                            @if (count($errors) > 0)
                              <div class="alert alert-danger">
                                  <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                  <ul>
                                  @foreach ($errors->all() as $error)
                                      <li>{{ $error }}</li>
                                  @endforeach
                                  </ul>
                              </div>
                          @endif

                          {!! Form::open(array('route' => 'settings.save','method'=>'POST', 'files'=>'true')) !!}
                            <div class="dynamicadd" id="dynamicadd">
                              <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.order_number'); ?></label>
                                <div class="col-lg-9 col-md-9 col-sm-12">
                                  <?php
$orderNumberValue = false;
if (!empty($settings[config('constants.settings.keys.showroom_order_number')])) {
	$orderNumberValue = $settings[config('constants.settings.keys.showroom_order_number')];
}
?>
                                {!! Form::text('settings['.config('constants.settings.keys.showroom_order_number').']', $orderNumberValue, array('class' => 'form-control required')) !!}
                                </div>
                              </div>
                              <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.sales_return_number'); ?></label>
                                <div class="col-lg-9 col-md-9 col-sm-12">
                                  <?php
$salesReturnNumber = false;
if (!empty($settings[config('constants.settings.keys.sales_return_number')])) {
	$salesReturnNumber = $settings[config('constants.settings.keys.sales_return_number')];
}
?>
                                {!! Form::text('settings['.config('constants.settings.keys.sales_return_number').']', $salesReturnNumber, array('class' => 'form-control required')) !!}
                                </div>
                              </div>
                              <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.quotation_number'); ?></label>
                                <div class="col-lg-9 col-md-9 col-sm-12">
                                  <?php
$quotationNumber = false;
if (!empty($settings[config('constants.settings.keys.quotation_number')])) {
	$quotationNumber = $settings[config('constants.settings.keys.quotation_number')];
}
?>
                                {!! Form::text('settings['.config('constants.settings.keys.quotation_number').']', $quotationNumber, array('class' => 'form-control required')) !!}
                                </div>
                              </div>
                            </div>
                            <div class="form-actions">
                                <div class="form-group row">
                                    <div class="col-md-9 ml-md-auto btn-list">
                                        <button class="btn btn-primary" type="submit">Submit</button>
                                        <button class="btn btn-outline-default" type="reset">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        {!! Form::close() !!}
                        </div>
                      </div>
					  <!-- diamond setting -->
                      <div class="tab-pane" id="daimond-tab-v1" aria-expanded="true">
                         <div class="img-thumbnail p-3">
                          @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                </ul>
                            </div>
                        @endif

                        {!! Form::open(array('route' => 'settings.save','method'=>'POST', 'files'=>'true' ,'id' =>'voucher')) !!}
                          <div class="dynamicadd" id="dynamicadd">
                            <div class="form-group row">
                              <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.diamond_loss'); ?></label>
                              <div class="col-lg-9 col-md-9 col-sm-12">
<?php $diamond_loss = false;
if (!empty($settings[config('constants.settings.keys.diamond_loss')])) {
	$diamond_loss = $settings[config('constants.settings.keys.diamond_loss')];
}?>
                              {!! Form::text('settings['.config('constants.settings.keys.diamond_loss').']', $diamond_loss, array('class' => 'form-control required ')) !!}
                              </div>
                            </div>
                            <div class="form-group row">
                              <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.issue_voucher_prifix'); ?></label>
                              <div class="col-lg-9 col-md-9 col-sm-12">
<?php $issue_voucher_prifix = false;
if (!empty($settings[config('constants.settings.keys.issue_voucher_prifix')])) {
	$issue_voucher_prifix = $settings[config('constants.settings.keys.issue_voucher_prifix')];

}?>
                              {!! Form::text('settings['.config('constants.settings.keys.issue_voucher_prifix').']', $issue_voucher_prifix, array('class' => 'form-control required')) !!}
                              </div>
                            </div>

                            <div class="form-group row">
                              <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.diamond_issue_voucher_prifix'); ?></label>
                              <div class="col-lg-9 col-md-9 col-sm-12">
<?php $diamond_issue_voucher_prifix = false;
if (!empty($settings[config('constants.settings.keys.diamond_issue_voucher_prifix')])) {
	$diamond_issue_voucher_prifix = $settings[config('constants.settings.keys.diamond_issue_voucher_prifix')];

}?>
                              {!! Form::text('settings['.config('constants.settings.keys.diamond_issue_voucher_prifix').']', $diamond_issue_voucher_prifix, array('class' => 'form-control required')) !!}
                              </div>
                            </div>


                            <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.diamond_invoice_number'); ?></label>
                            <div class="col-lg-9 col-md-9 col-sm-12">
                            <?php $diamond_invoice_number = false;
if (!empty($settings[config('constants.settings.keys.diamond_invoice_number')])) {
	$diamond_invoice_number = $settings[config('constants.settings.keys.diamond_invoice_number')];
}?>
                            {!! Form::number('settings['.config('constants.settings.keys.diamond_invoice_number').']', $diamond_invoice_number, array('class' => 'form-control required')) !!}
                            </div>
                            </div>

                              <div class="form-group row">
                              <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.diamond_voucher_series'); ?></label>
                              <div class="col-lg-9 col-md-9 col-sm-12">
<?php $diamond_voucher_series = false;
if (!empty($settings[config('constants.settings.keys.diamond_voucher_series')])) {
	$diamond_voucher_series = $settings[config('constants.settings.keys.diamond_voucher_series')];
}?>
                              {!! Form::number('settings['.config('constants.settings.keys.diamond_voucher_series').']', $diamond_voucher_series, array('class' => 'form-control required')) !!}
                              </div>
                            </div>

                            <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.gold_voucher_series'); ?></label>
                            <div class="col-lg-9 col-md-9 col-sm-12">
<?php $gold_voucher_series = false;
if (!empty($settings[config('constants.settings.keys.gold_voucher_series')])) {
	$gold_voucher_series = $settings[config('constants.settings.keys.gold_voucher_series')];
}?>
                            {!! Form::number('settings['.config('constants.settings.keys.gold_voucher_series').']', $gold_voucher_series, array('class' => 'form-control required')) !!}
                            </div>
                          </div>



                            <div class="form-group row">
                              <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.misc_loss_limit'); ?></label>
                              <div class="col-lg-9 col-md-9 col-sm-12">
<?php $misc_loss_limit = false;
if (!empty($settings[config('constants.settings.keys.misc_loss_limit')])) {
	$misc_loss_limit = $settings[config('constants.settings.keys.misc_loss_limit')];
}?>
                              {!! Form::number('settings['.config('constants.settings.keys.misc_loss_limit').']', $misc_loss_limit, array('class' => 'form-control required')) !!}
                              </div>
                            </div>



                          </div>
                          <div class="form-actions">
                              <div class="form-group row">
                                  <div class="col-md-9 ml-md-auto btn-list">
                                      <button class="btn btn-primary" type="submit">Submit</button>
                                      <button class="btn btn-outline-default" type="reset">Cancel</button>
                                  </div>
                              </div>
                          </div>
                      {!! Form::close() !!}
                      </div>
                      </div>
                       <!-- Costing setting -->
                       <div class="tab-pane" id="costing-tab-v1" aria-expanded="false">
                         <div class="img-thumbnail p-3">
                          @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                </ul>
                            </div>
                        @endif

                        {!! Form::open(array('route' => 'settings.save','method'=>'POST', 'files'=>'true')) !!}
                          <div class="dynamicadd" id="dynamicadd">
                            <div class="form-group row">
                              <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.costing_nearby_amount'); ?></label>
                              <div class="col-lg-9 col-md-9 col-sm-12">
                                <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.costing_nearby_amount')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.costing_nearby_amount')];
}
?>
                              {!! Form::number('settings['.config('constants.settings.keys.costing_nearby_amount').']', $paginationLimitValue, array('class' => 'form-control')) !!}

                              </div>
                            </div>
                          </div>
                          
                           <div class="dynamicadd" id="dynamicadd">
                            <div class="form-group row">
                              <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.qc_approval_voucher_no'); ?></label>
                              <div class="col-lg-9 col-md-9 col-sm-12">
                                <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.qc_approval_voucher_no')])) {
  $paginationLimitValue = $settings[config('constants.settings.keys.qc_approval_voucher_no')];
}
?>
                              {!! Form::number('settings['.config('constants.settings.keys.qc_approval_voucher_no').']', $paginationLimitValue, array('class' => 'form-control')) !!}

                              </div>
                            </div>
                          </div>

                          <div class="form-actions">
                              <div class="form-group row">
                                  <div class="col-md-9 ml-md-auto btn-list">
                                      <button class="btn btn-primary" type="submit">Submit</button>
                                      <button class="btn btn-outline-default" type="reset">Cancel</button>
                                  </div>
                              </div>
                          </div>
                      {!! Form::close() !!}
                      </div>
                      </div>
                      <!-- Inventory stting -->
                      <div class="tab-pane" id="inventory-tab-v1" aria-expanded="false">
                          <div class="img-thumbnail p-3">
                              @if (count($errors) > 0)
                                  <div class="alert alert-danger">
                                      <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                      <ul>
                                      @foreach ($errors->all() as $error)
                                          <li>{{ $error }}</li>
                                      @endforeach
                                      </ul>
                                  </div>
                              @endif
                              {!! Form::open(array('route' => 'settings.save','method'=>'POST', 'files'=>'true')) !!}
                              <div class="dynamicadd" id="dynamicadd">
							  <div class="row border-b-light-1">
							   <div class="col-md-12">
                  <div class="form-group row">
                              <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.download_image_limit'); ?></label>
                              <div class="col-lg-9 col-md-9 col-sm-12">
                                <?php
$downloadLimitValue = false;
if (!empty($settings[config('constants.settings.keys.download_image_limit')])) {
	$downloadLimitValue = $settings[config('constants.settings.keys.download_image_limit')];
}
?>
                              {!! Form::number('settings['.config('constants.settings.keys.download_image_limit').']', $downloadLimitValue, array('class' => 'form-control')) !!}
                              </div>
                            </div>

                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.approval_number'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.approval_number')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.approval_number')];
}
?>
                                          {!! Form::text('settings['.config('constants.settings.keys.approval_number').']', $paginationLimitValue, array('class' => 'form-control')) !!}
                                      </div>
                                  </div>

                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.return_memo_number'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.return_memo_number')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.return_memo_number')];
}
?>
                                          {!! Form::text('settings['.config('constants.settings.keys.return_memo_number').']', $paginationLimitValue, array('class' => 'form-control')) !!}
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.invoice_limit'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.invoice_limit')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.invoice_limit')];
}
?>
                                          {!! Form::text('settings['.config('constants.settings.keys.invoice_limit').']', $paginationLimitValue, array('class' => 'form-control')) !!}
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.invoice_increment_id_prefix'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.invoice_increment_id_prefix')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.invoice_increment_id_prefix')];
}
?>
                                          {!! Form::text('settings['.config('constants.settings.keys.invoice_increment_id_prefix').']', $paginationLimitValue, array('class' => 'form-control')) !!}
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.invoice_increment_id'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.invoice_increment_id')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.invoice_increment_id')];
}
?>
                                          {!! Form::text('settings['.config('constants.settings.keys.invoice_increment_id').']', $paginationLimitValue, array('class' => 'form-control')) !!}
                                      </div>
                                  </div>
                                   <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.Delivery_Challan_No'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$Delivery_Challan_No = false;
if (!empty($settings[config('constants.settings.keys.Delivery_Challan_No')])) {
	$Delivery_Challan_No = $settings[config('constants.settings.keys.Delivery_Challan_No')];
}
?>
                                          {!! Form::text('settings['.config('constants.settings.keys.Delivery_Challan_No').']', $Delivery_Challan_No, array('class' => 'form-control')) !!}
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.voucher_number'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$voucher_number = false;
if (!empty($settings[config('constants.settings.keys.voucher_number')])) {
  $voucher_number = $settings[config('constants.settings.keys.voucher_number')];
}
?>
                                          {!! Form::text('settings['.config('constants.settings.keys.voucher_number').']', $voucher_number, array('class' => 'form-control')) !!}
                                      </div>
                                  </div>
                                  
								  </div>
								  </div>
								  <!-- gst setting row -->
								  <div class="row border-b-light-1">
									<div class="col-md-12">
										<h4 class="w-100 fs-20 mb-4 fw-700 text-gray">GST Setting</h4>
										<div class="form-group row">
											<label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.igst_percentage'); ?></label>
											<div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$igstPercentage = false;
if (!empty($settings[config('constants.settings.keys.igst_percentage')])) {
	$igstPercentage = $settings[config('constants.settings.keys.igst_percentage')];
}
?>
												{!! Form::number('settings['.config('constants.settings.keys.igst_percentage').']', $igstPercentage, array('class' => 'form-control','min' => 0, 'max' => 9, 'step' => 0.1)) !!}
											</div>
										</div>
										<div class="form-group row">
											<label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.cgst_percentage'); ?></label>
											<div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$cgstPercentage = false;
if (!empty($settings[config('constants.settings.keys.cgst_percentage')])) {
	$cgstPercentage = $settings[config('constants.settings.keys.cgst_percentage')];
}
?>
												{!! Form::number('settings['.config('constants.settings.keys.cgst_percentage').']', $cgstPercentage, array('class' => 'form-control','min' => 0, 'max' => 9, 'step' => 0.1)) !!}
											</div>
										</div>
										<div class="form-group row">
											<label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.sgst_percentage'); ?></label>
											<div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$sgstPercentage = false;
if (!empty($settings[config('constants.settings.keys.sgst_percentage')])) {
	$sgstPercentage = $settings[config('constants.settings.keys.sgst_percentage')];
}
?>
												{!! Form::number('settings['.config('constants.settings.keys.sgst_percentage').']', $sgstPercentage, array('class' => 'form-control','min' => 0, 'max' => 9, 'step' => 0.1)) !!}
											</div>
										</div>
									</div>
								  </div>
								  <!-- gst setting row end -->
								  <!-- discount setting row -->
								  <div class="row">
								  <div class="col-md-12">
                                  <h4 class="w-100 fs-20 mb-4 fw-700 text-gray">Discount Setting</h4>
                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.discount_invoice_less_25'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.discount_invoice_less_25')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.discount_invoice_less_25')];
}
?>
                                          <div class="input-group input-has-value">
                                              {!! Form::number('settings['.config('constants.settings.keys.discount_invoice_less_25').']', $paginationLimitValue, array('class' => 'form-control number h-auto zertofive valid')) !!}
                                              <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.discount_invoice_25_to_lakhs'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.discount_invoice_25_to_lakhs')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.discount_invoice_25_to_lakhs')];
}
?>
                                          <div class="input-group input-has-value">
                                              {!! Form::number('settings['.config('constants.settings.keys.discount_invoice_25_to_lakhs').']', $paginationLimitValue, array('class' => 'form-control number h-auto zertofive valid')) !!}
                                              <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.discount_invoice_above_lakhs'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.discount_invoice_above_lakhs')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.discount_invoice_above_lakhs')];
}
?>
                                          <div class="input-group input-has-value">
                                              {!! Form::number('settings['.config('constants.settings.keys.discount_invoice_above_lakhs').']', $paginationLimitValue, array('class' => 'form-control number h-auto zertofive valid')) !!}
                                              <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.discount_invoice_less_25_18k'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.discount_invoice_less_25_18k')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.discount_invoice_less_25_18k')];
}
?>
                                          <div class="input-group input-has-value">
                                              {!! Form::number('settings['.config('constants.settings.keys.discount_invoice_less_25_18k').']', $paginationLimitValue, array('class' => 'form-control number h-auto zertofive valid')) !!}
                                              <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.discount_invoice_25_100k_18k'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.discount_invoice_25_100k_18k')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.discount_invoice_25_100k_18k')];
}
?>
                                          <div class="input-group input-has-value">
                                              {!! Form::number('settings['.config('constants.settings.keys.discount_invoice_25_100k_18k').']', $paginationLimitValue, array('class' => 'form-control number h-auto zertofive valid')) !!}
                                              <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label class="col-md-3 col-form-label" for="l0"><?php echo config('constants.labels.setting_page.discount_invoice_gt_100k_18k'); ?></label>
                                      <div class="col-lg-9 col-md-9 col-sm-12">
                                          <?php
$paginationLimitValue = false;
if (!empty($settings[config('constants.settings.keys.discount_invoice_gt_100k_18k')])) {
	$paginationLimitValue = $settings[config('constants.settings.keys.discount_invoice_gt_100k_18k')];
}
?>
                                          <div class="input-group input-has-value">
                                              {!! Form::number('settings['.config('constants.settings.keys.discount_invoice_gt_100k_18k').']', $paginationLimitValue, array('class' => 'form-control number h-auto zertofive valid')) !!}
                                              <span class="input-group-addon"><i class="fa fa-percent list-icon"></i></span>
                                          </div>
                                      </div>
                                  </div>
                              </div>
							  </div>
                              <div class="form-actions">
                                  <div class="form-group row">
                                      <div class="col-md-9 ml-md-auto btn-list">
                                          <button class="btn btn-primary" type="submit">Submit</button>
                                          <button class="btn btn-outline-default" type="reset">Cancel</button>
                                      </div>
                                  </div>
                              </div>
                              {!! Form::close() !!}
                      </div>
					  </div>
					  </div>
                      <!-- Cache setting -->
                      <div class="tab-pane" id="cache-tab-v1" aria-expanded="false">
                          <div class="img-thumbnail p-3">
                              <div class="dynamicadd" id="dynamicadd">
							  <div class="row">
                                <div class="col-md-12 widget-holder">
                                  <div class="widget-bg-transparent">
                                      <div class="widget-body clearfix">
                                          <h5 class="box-title border-b-light-1 pb-1 mb-4"><?php echo config('constants.labels.setting_page.cache_types'); ?></h5>
                                           <button class="btn-clear-right small-btn-style btn btn-primary" type="button" id="clear_all_cache">Clear All</button>
                                          <div class="list-group auto">
                                            <a href="javascript:void(0);" class="list-group-item list-group-item-action d-flex justify-content-end" id="clear_application_cache">
                                              <span class="mr-auto">
                                                <i class="material-icons list-icon icon-muted mr-2">web_asset</i>
                                                <?php echo config('constants.labels.setting_page.application_cache'); ?>
                                              </span>
                                              <span class="badge badge-pill badge-secondary fs-12 mr-1 my-auto">Clear
                                              </span>
                                              <i class="material-icons list-icon icon-muted my-auto">chevron_right</i>
                                            </a>
                                            <a href="javascript:void(0);" class="list-group-item list-group-item-action d-flex justify-content-end" id="clear_config_cache">
                                              <span class="mr-auto">
                                                <i class="material-icons list-icon icon-muted mr-2">settings_applications</i><?php echo config('constants.labels.setting_page.config_cache'); ?>
                                              </span>
                                              <span class="badge badge-pill badge-secondary fs-12 mr-1 my-auto">Clear
                                              </span>
                                              <i class="material-icons list-icon icon-muted my-auto">chevron_right</i>
                                            </a>
                                            <a href="javascript:void(0);" class="list-group-item list-group-item-action d-flex justify-content-end" id="clear_route_cache">
                                              <span class="mr-auto">
                                                <i class="material-icons list-icon icon-muted mr-2">router</i>
                                                <?php echo config('constants.labels.setting_page.route_cache'); ?>
                                              </span>
                                              <span class="badge badge-pill badge-secondary fs-12 mr-1 my-auto">Clear
                                              </span>
                                              <i class="material-icons list-icon icon-muted my-auto">chevron_right</i>
                                            </a>
                                            <a href="javascript:void(0);" class="list-group-item list-group-item-action d-flex justify-content-end" id="clear_view_cache">
                                              <span class="mr-auto">
                                                <i class="material-icons list-icon icon-muted mr-2">remove_red_eye</i>
                                                <?php echo config('constants.labels.setting_page.view_cache'); ?>
                                              </span>
                                              <span class="badge badge-pill badge-secondary fs-12 mr-1 my-auto">Clear
                                              </span>
                                              <i class="material-icons list-icon icon-muted my-auto">chevron_right</i>
                                            </a>
                                          </div>
                                          <!-- /.list-group -->
                                      </div>
                                      <!-- /.widget-body -->
                                  </div>
                                  <!-- /.widget-bg -->
                              </div>
							  </div>
                              </div>
                              {!! Form::close() !!}
                          </div>
                      </div>
                      <!-- backup setting -->
                      <div class="tab-pane" id="backup-tab-v1" aria-expanded="false">
                          <div class="img-thumbnail p-3">
                              <div class="dynamicadd" id="dynamicadd">
							  <div class="row">
                                <div class="col-md-12 widget-holder">
                                  <div class="widget-bg-transparent">
                                      <div class="widget-body clearfix">
                                          <h5 class="box-title border-b-light-1 pb-1 mb-4"><?php echo config('constants.labels.setting_page.backup_modules'); ?></h5>
                                            <button class="btn-backup-right small-btn-style btn btn-primary" type="button" id="backup_all">Backup All</button>
                                          <div class="list-group auto">
                                            <div class="list-group-item list-group-item-action d-flex justify-content-end">
                                              <span class="mr-auto">
                                                <i class="material-icons list-icon icon-muted mr-2">web_asset</i>
                                                <?php echo config('constants.labels.setting_page.diamond_and_gold'); ?>
                                              </span>
                                              <a href="javascript:void(0);" id="backup_dng">
                                                <span class="badge badge-pill badge-secondary fs-12 mr-1 my-auto">Backup
                                                </span>
                                              </a>
                                              <a href="javascript:void(0);" id="backup_dump_dng">
                                                <span class="badge badge-pill badge-secondary fs-12 mr-1 my-auto">Backup & Dump
                                                </span>
                                              </a>
                                              <i class="material-icons list-icon icon-muted my-auto">chevron_right</i>
                                            </div>
                                          </div>
                                          <!-- /.list-group -->
                                      </div>
                                      <!-- /.widget-body -->
                                  </div>
                                  <!-- /.widget-bg -->
                              </div>
							  </div>
                              </div>
                              {!! Form::close() !!}
                          </div>
                      </div>
                  <!-- /.tab-content -->
              </div>
            </div>
          </div>
		  </div>
        </div>
      </div>
  </div>
  <input type="hidden" name="clear-cache-route" id="clear_cache_route" value="">
  <?php // echo config('constants.labels.setting_page.tmp_var'); ?>
</main>

@endsection

@section('distinct_footer_script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script>
  $(document).ready(function() {

    function clearCache($type){
      console.log('Type : '+$type);
      if($type == 'Application'){
        src = "{{ route('settings.clear.application.cache') }}";
      } else if($type == 'Config'){
        src = "{{ route('settings.clear.config.cache') }}";
      } else if($type == 'Route'){
        src = "{{ route('settings.clear.raa.cache') }}";
      } else if($type == 'View'){
        src = "{{ route('settings.clear.view.cache') }}";
      } else {
        src = "{{ route('settings.clear.all.cache') }}";
      }
      $.ajax({
          url: src,
          dataType: "json",
          data: {
              //type : $type, //$(this).val()
          },
          success: function(data) {
              console.log('okkkk');
              console.log(data.msg);
              swal({
                  title: "Cleared !",
                  text: data.msg,
                  type: "success"
              }).then(function() {
                location.reload();
              });
          }
      });
    }

    $('#clear_all_cache').click(function (){
      clearCache('All');
    });

    $('#clear_application_cache').click(function (){
      clearCache('Application');
    });

    $('#clear_config_cache').click(function (){
      clearCache('Config');
    });

    $('#clear_route_cache').click(function (){
      clearCache('Route');
    });

    $('#clear_view_cache').click(function (){
      clearCache('View');
    });

    function backup($type){
      console.log('Type : '+$type);
      if($type == 'BACKUP_DNG'){
        src = "{{ route('settings.backup.dng') }}";
      } else if($type == 'BACKUP_DUMP_DNG'){
        src = "{{ route('settings.backup.dump.dng') }}";
      }/* else if($type == 'Route'){
        src = "{{ route('settings.clear.raa.cache') }}";
      } else if($type == 'View'){
        src = "{{ route('settings.clear.view.cache') }}";
      } else {
        src = "{{ route('settings.clear.all.cache') }}";
      }*/
      $.ajax({
          url: src,
          dataType: "json",
          data: {
              //type : $type, //$(this).val()
          },
          success: function(data) {
              console.log('okkkk');
              console.log(data.msg);
              /*swal({
                  title: "Cleared !",
                  text: data.msg,
                  type: "success"
              }).then(function() {
                location.reload();
              });*/
          }
      });
    }

    $('#backup_dng').click(function (){
      backup('BACKUP_DNG');
    });

    $('#backup_dump_dng').click(function (){
      backup('BACKUP_DUMP_DNG');
    });

  });
   jQuery.validator.addMethod("lettersonly", function(value, element) {
return this.optional(element) || /^[a-z\s]+$/i.test(value);
}, "Only characters");
 $("#voucher").validate({

   rules: {
    // simple rule, converted to {required:true}
    'settings[ISSUE_VOUCHER_NO_PREFIX]' : {
      required : true,
      maxlength: 5,
      //lettersonly:true
    },
    // simple rule, converted to {required:true}
    'settings[DIAMOND_ISSUE_VOUCHER_NO_PREFIX]' : {
      required : true,
      maxlength: 5,
      //lettersonly:true
    },
    // compound rule
    'settings[ISSUE_VOUCHER_NO_SERIES]': {
      required: true,
      minlength:2,
      maxlength : 9,
      number :true
    },
    // compound rule
    'settings[settings[DIAMOND_VOUCHER_NO_SERIES]]': {
      required: true,
      minlength:2,
      maxlength : 9,
      number :true
    }
  }

});
</script>

@endsection