
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
    {{ Breadcrumbs::render('product_list') }}
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
			<div class="col-md-12 widget-holder content-area">
				<div class="widget-bg">
					<div class="widget-heading clearfix">
						<h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">Filter</h5>
					  
					</div>
					<div class="widget-body clearfix dataTable-length-top-0">
						<form class="mr-b-30" method="post">
							{{ csrf_field() }}
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<select class="form-control" name="category">
											<option value="null">Select Category</option>
											@foreach($category as $cat){
												<option value={{$cat->entity_id}}>{{$cat->name}}</option>
											
											@endforeach
										</select>	
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<select class="form-control" name="color">
											<option value="null">Select Color</option>
											@foreach($color->unique('color') as $user){
												<option>{{$user->color}}</option>
											
											@endforeach
										</select>	
									</div>
								</div>
								
								<div class="col-md-3">
									<div class="form-group">
										<select class="form-control" name="status">
											<option value="null">Select Status</option>
											<option value="0">Pending</option>
											<option value="1">Done</option>
										</select>	
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<input class="form-control" name="sku" style="height: 43px;" placeholder="Sku Search" type="text">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<input class="btn btn-primary" style="    height: 43px;"   type="submit" value="Apply">
									</div>
								</div>
							</div>
						</form>


					</div>
				</div>
			</div>
		</div>
		
      	<div class="row">
  			<div class="col-md-12 widget-holder content-area">
  				<div class="widget-bg">
  					<div class="widget-heading clearfix">
  						<h5 class="border-b-light-1 pb-1 mb-2 mt-0 w-100">Product List</h5>
						
  					</div>
  					<div class="widget-body clearfix dataTable-length-top-0">
  						
	                    <table class="table table-striped table-center word-break mt-0"   data-toggle="datatables" >
  							<thead>
  								<tr class="bg-primary">
  									<th>Sku</th>
									  <th>Color</th>
									  <th>Category</th>
									  <th>Status</th>
  									<th>Action</th>
  								
  								</tr>
  							</thead>
  							<tbody>
                     @foreach($list as $item)

	                <tr>
		                <td>{{$item->sku}}</td>
	                    <td>{{$item->color}}</td>
						<td>{{$item->category->name}}</td>
						<td>
							<?php 
							if($item->status=='0')
							{
								echo "Pending";
							}
							else {
								echo "Done";
							}
							?>
							</td>
                      <td>
						<?php 
						if($item->status=='0')
						{
							?>
								<a class="color-content table-action-style btn-delete-customer" data-href="{{ route('photography.product.delete',['id'=>$item->id]) }}" style="cursor:pointer;"><i class="material-icons md-18">delete</i></a>
								<a href="javascript:void(0);"  class="color-content table-action-style"><i class="material-icons md-18">remove_red_eye</i></a>
							
							<?php 
						}
						else {
							?>
							<a href="javascript:void(0)"  class="color-content table-action-style"><i class="material-icons md-18">delete</i></a>
							
							<a href="{{ route('product.view',['id'=>$item->id]) }}" class="color-content table-action-style"><i class="material-icons md-18">remove_red_eye</i></a>
							<?php 
						}
						?>
						</td>

    </tr>
   
	@endforeach

							  </tbody>
							  <tfoot>
								<tr class="bg-primary">
									<th>Sku</th>
									<th>Color</th>
									<th>Category</th>
									<th>Status</th>
                                    <th>Action</th>
								
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
<script>
	$(document).on('click','.btn-delete-customer',function(){
			var deleteUrl = $(this).data('href');
		    swal({
		        title: 'Are you sure?',
		         type: 'info',
				 text:'Delete This Product',
		        showCancelButton: true,
		        confirmButtonText: 'Confirm',
		        confirmButtonClass: 'btn-confirm-all-productexcel btn btn-info'
		        }).then(function(data) {
		        	if (data.value) {
		        		window.location.href = deleteUrl;
		        	}

		    });
		});
</script>
@endsection