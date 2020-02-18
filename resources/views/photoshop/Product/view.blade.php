
@extends('layout.photo_navi')


@section('title', 'Product List')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection
<style>
    table, td, th {
      
       width: 300px;
    }
 </style>
@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
   Product Detail
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
			<!-- Counter: Sales -->
			<div class="col-md-4 col-sm-6 widget-holder widget-full-height">
				<div class="widget-bg bg-color-scheme text-inverse">
					<div class="widget-body">
						<div class="widget-counter">
							<h6>Total  work<small class="text-inverse">Total Work</small></h6>
							<h3 class="h1">&dollar;<span class="counter">0</span></h3><i class="material-icons list-icon">add_shopping_cart</i>
						</div>
						<!-- /.widget-counter -->
					</div>
					<!-- /.widget-body -->
				</div>
				<!-- /.widget-bg -->
			</div>
			<!-- /.widget-holder -->
			<!-- Counter: Subscriptions -->
			<div class="col-md-4 col-sm-6 widget-holder widget-full-height">
				<div class="widget-bg bg-color-scheme text-inverse">
					<div class="widget-body clearfix">
						<div class="widget-counter">
							<h6>Rework <small class="text-inverse">Total Rework</small></h6>
							<h3 class="h1"><span class="counter">0</span></h3><i class="material-icons list-icon">event_available</i>
						</div>
						<!-- /.widget-counter -->
					</div>
					<!-- /.widget-body -->
				</div>
				<!-- /.widget-bg -->
			</div>
			<!-- /.widget-holder -->
			<!-- Counter: Users -->
			<div class="col-md-4 col-sm-6 widget-holder widget-full-height">
				<div class="widget-bg bg-color-scheme text-inverse">
					<div class="widget-body clearfix">
						<div class="widget-counter">
							<h6>Total Status<small>Total Count</small></h6>
							<h3 class="h1"><span class="counter">0</span></h3><i class="material-icons list-icon">public</i>
						</div>
						<!-- /.widget-counter -->
					</div>
					<!-- /.widget-body -->
				</div>
				<!-- /.widget-bg -->
			</div>
			<!-- /.widget-holder -->
			<!-- Counter: Pageviews -->
			
			<!-- /.widget-holder -->
		</div>
        <div class="row">
            <!-- Tabs Vertical Left -->
            <div class="col-md-12 widget-holder">
                <div class="widget-bg">
                    <div class="widget-body clearfix">
                        <h5 class="box-title">tabs Vertical Left</h5>
                        <div class="tabs tabs-vertical">
                            <ul class="nav nav-tabs flex-column">
                                <li class="nav-item active"><a class="nav-link" href="#home-tab-v1" data-toggle="tab" aria-expanded="true">Home</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="#profile-tab-v1" data-toggle="tab" aria-expanded="true">Profile</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="#messages-tab-v1" data-toggle="tab" aria-expanded="true">Messages</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="#settings-tab-v1" data-toggle="tab" aria-expanded="true">Settings</a>
                                </li>
                            </ul>
                            <!-- /.nav-tabs -->
                            <div class="tab-content">
                                <div class="tab-pane active" id="home-tab-v1">
                                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Lorem ipsum dolor sit amet.</p>
                                    <p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa.</p>
                                </div>
                                <div class="tab-pane" id="profile-tab-v1">
                                    <p>Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt.</p>
                                    <p>Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac.</p>
                                </div>
                                <div class="tab-pane" id="messages-tab-v1">
                                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Lorem ipsum dolor sit amet.</p>
                                    <p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa.</p>
                                </div>
                                <div class="tab-pane" id="settings-tab-v1">
                                    <p>Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt.</p>
                                    <p>Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac.</p>
                                </div>
                            </div>
                            <!-- /.tab-content -->
                        </div>
                        <!-- /.tabs -->
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
    </div>
  <!-- /.widget-list -->
</main>
<!-- /.main-wrappper -->

<style type="text/css">
.form-control[readonly] {background-color: #fff;}
</style>
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script>
<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<script src="<?=URL::to('/');?>/js/additional-methods.min.js"></script>

@endsection