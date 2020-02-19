
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
				<div class="widget-bg bg-primary text-inverse">
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
            <div class="col-md-12 widget-holder content-area">
                <div class="widget-bg">
                    <div class="widget-heading clearfix">
                        <h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100"> @foreach ($listproduct as $item)
             
                            {{$item->getProductdetail->sku}}
                            @endforeach</h5>
                      
                    </div>
                    <div class="widget-body clearfix dataTable-length-top-0">
                        
                      <table class="table table-striped table-center word-break mt-0"   data-toggle="datatables" >
                            <thead>
                                <tr class="bg-primary">
                                    <th>Sku</th>
                                    <th>Action Name</th>
                                    <th>Action By</th>
                                    <th>Date</th>
                                   
                                
                                </tr>
                            </thead>
                            <tbody>
                    
                 @foreach ($listproduct1 as $items)
                 <tr>
                    <td>{{$items->getProductdetail->sku}}</td>   
                     <td>{{$items->getDepartmentStatus->status_name}}</td>
                     <td>{{$items->getactionby->name}}</td>
                    <td>{{$items->action_date_time}}</td>
                  
                 

                       </tr>
                 @endforeach
            


                            </tbody>
                            <tfoot>
                              <tr class="bg-primary">
                                <th>Sku</th>
                                <th>Action Name</th>
                                <th>Action By</th>
                                <th>Date</th>
                               
                              
                              </tr>
                          </tfoot>
                        </table>
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