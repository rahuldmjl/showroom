
@extends('layout.photo_navi')


@section('title', 'Photoshop')

@section('distinct_head')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
  <!-- Page Title Area -->
  <div class="row page-title clearfix">
    {{ Breadcrumbs::render('photography.index') }}
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
			<div class="col-md-3 col-sm-6 widget-holder widget-full-height">
				<div class="widget-bg bg-primary text-inverse">
					<div class="widget-body">
						<div class="widget-counter">
							<h6>Total  <small class="text-inverse">Product</small></h6>
							<h3 class="h1">&dollar;<span class="counter">{{$totoalproduct}}</span></h3><i class="material-icons list-icon">add_shopping_cart</i>
						</div>
						<!-- /.widget-counter -->
					</div>
					<!-- /.widget-body -->
				</div>
				<!-- /.widget-bg -->
			</div>
			<!-- /.widget-holder -->
			<!-- Counter: Subscriptions -->
			<div class="col-md-3 col-sm-6 widget-holder widget-full-height">
				<div class="widget-bg bg-color-scheme text-inverse">
					<div class="widget-body clearfix">
						<div class="widget-counter">
							<h6>photography <small class="text-inverse">Done</small></h6>
							<h3 class="h1"><span class="counter">{{$totalphotographydone}}</span></h3><i class="material-icons list-icon">event_available</i>
						</div>
						<!-- /.widget-counter -->
					</div>
					<!-- /.widget-body -->
				</div>
				<!-- /.widget-bg -->
			</div>
			<!-- /.widget-holder -->
			<!-- Counter: Users -->
			<div class="col-md-3 col-sm-6 widget-holder widget-full-height">
				<div class="widget-bg">
					<div class="widget-body clearfix">
						<div class="widget-counter">
							<h6>Photography<small>Pending</small></h6>
							<h3 class="h1"><span class="counter">{{$totalphotographypending}}</span></h3><i class="material-icons list-icon">public</i>
						</div>
						<!-- /.widget-counter -->
					</div>
					<!-- /.widget-body -->
				</div>
				<!-- /.widget-bg -->
			</div>
			<!-- /.widget-holder -->
			<!-- Counter: Pageviews -->
			<div class="col-md-3 col-sm-6 widget-holder widget-full-height">
				<div class="widget-bg">
					<div class="widget-body clearfix">
						<div class="widget-counter">
							<h6>Total Photography<small>Last 24 Hours</small></h6>
							<h3 class="h1"><span class="counter">2748</span></h3><i class="material-icons list-icon">show_chart</i>
						</div>
						<!-- /.widget-counter -->
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