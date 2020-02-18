@extends('layout.mainlayout')

@section('title', 'Update Product')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">

<link href="<?=URL::to('/');?>/css/autocatch.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('productupload.updateproduct') }}
        <!-- /.page-title-right -->
    </div>
    <!-- /.page-title -->
    <!-- =================================== -->
    <!-- Different data widgets ============ -->
    <!-- =================================== -->
    <div class="widget-list creatediamond">
        <div class="row">
            <div class="widget-holder">
                <div class="widget-bg">
                    <div class="widget-body clearfix">
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

                        	<!-- <form  class="updateproductForm" method="POST" id="updateproductForm"  files = "true", action="{{action('ProductuploadController@updateproductstore')}}"> -->

                             {!! Form::open(array('route' => 'productupload.updateproductstore','method'=>'POST', 'files'=>'true','id' => 'myform','class' => 'updateproductForm')) !!}

                            <input type="hidden" name="id" value="<?= $id ?>">
                        	<?php foreach ($productdatas as $key => $productdata) { ?>
                        	<div class="row">	
                        		<h5 class="box-title box-title-style mr-b-0">Product Detail</h5>

                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Style</label><br/>
                                        <input type="text" name="style" class="form-control" value="<?php echo $productdata['style']; ?>">
									</div>
                                </div>

                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Item</label><br/>
                                        <input type="text" name="item" class="form-control" value="<?php echo $productdata['item']; ?>">
									</div>
                                </div>


                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Po No</label><br/>
                                        <input type="text" name="po_no" class="form-control" value="<?php echo $productdata['po_no']; ?>">
									</div>
                                </div>

                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Gross Weight</label><br/>
                                        <input type="text" name="gross_weight" class="form-control" value="<?php echo $productdata['gross_weight']; ?>">
									</div>
                                </div>

                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Certificate</label><br/>
                                        <input type="text" name="certificate" class="form-control" 
                                        value="<?php echo $productdata['certificate_no']; ?>">
									</div>
                                </div>

                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Sku</label><br/>
                                        <input type="text" name="sku" class="form-control" value="<?php echo $productdata['sku']; ?>">
									</div>
                                </div>

                                  <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Total Amount</label><br/>
                                        <input type="number" name="total_amount" class="form-control" value="<?php echo $productdata['total_amount']; ?>">
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Image</label><br/>
                                        <img src="<?= URL::to('/').'/uploads/img/'.$productdata['image']; ?>" height="100" weight="100"><br/><br/>
                                        <input type="hidden" name="old_image" class="old_image form-control" value="<?= $productdata['image']; ?> ">
                                        <input type="file" name="product_image" class="product_image form-control">
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <h5 class="box-title box-title-style mr-b-0">Category Detail</h5>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="l30">Category</label><br/>
                                    <select class="form-control" name="category_id">
                                        <?php foreach ($categoriesArr as $key => $category) { ?>
                                        <option  value="<?php echo $key?>" <?=$productdata['category_id'] == $key ? ' selected="selected"' : '';?>><?php echo $category; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                </div>
                            </div>
        
                            <div class="row">	
                        		<h5 class="box-title box-title-style mr-b-0">Metal Detail</h5>
                        		<div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Metal Weight</label><br/>
                                        <input type="text" name="metal_weight" class="form-control" value="<?= $productdata['metal_weight']; ?>">
									</div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Metal Rate</label><br/>
                                        <input type="text" name="metal_rate" class="form-control" value="<?= $productdata['metal_rate']?>">
									</div>
                                </div>
                                  <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Metal Amount</label><br/>
                                        <input type="text" name="metal_amount" class="form-control" value="<?= $productdata['metal_amount']?>">
                                    </div>
                                </div>
                                 <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group" name="metal_labour_charge">
                                        <label for="l30">Metal Labour Charge</label><br/>
                                        <input type="text" name="metal_labour_charge" class="form-control" value="<?= $productdata['metal_labour_charge']?>">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Metal Quality</label><br/>
                                        <select class="form-control" name="metal_quality_id">
                                            <?php foreach ($metal_Arr as $key => $metal_elem) { ?>
                                            <option  value="<?php echo $key?>" <?=$productdata['metal_quality_id'] == $key ? ' selected="selected"' : '';?>><?php echo $metal_elem; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">	
                        		<h5 class="box-title box-title-style mr-b-0">Diamond Detail</h5>
                                <?php 
                                for($stonei = 0; $stonei < count($productdata['stone_data']['stone_shape']); $stonei++) { ?>
                                    <div class="row">
                        		  <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label for="l30">Stone Shape</label><br/>
                                        <select class="form-control" name="stone_shape[]">
                                            <?php foreach ($stone_shape_arr as $key => $stone_shape_elem) {   
                                                ?>
                                                <option value="<?= $key ?>"<?= $productdata['stone_data']['stone_shape'][$stonei] == $key ?' selected="selected"':'' ?>><?= $stone_shape_elem; ?></option>
                                            <?php } ?>
                                        </select>
									</div>
                                </div>  


                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="l30">Stone Clarity</label><br/>
                                    <select class="form-control" name="stone_clarity[]">
                                        <?php foreach ($stone_clarity_arr as $key => $stone_clarity_elem) {   
                                            ?>
                                            <option value="<?= $key ?>"<?= $productdata['stone_data']['stone_clarity'][$stonei] == $key ?' selected="selected"':'' ?>><?= $stone_clarity_elem; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="l30">MM Size</label><br/>
                                    <input name="mm_size[]" type="text" name="mm_size" class="form-control" value="<?= $productdata['stone_data']['mm_size'][$stonei] ?>">
                                </div>
                            </div>

                             <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="l30">Seive Size</label><br/>
                                    <input name="seive_size[]" type="text" name="seive_size" class="form-control" value="<?= $productdata['stone_data']['seive_size'][$stonei] ?>">
                                </div>
                            </div>

                             <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="l30">Stone Rate</label><br/>
                                    <input name="stone_rate[]" type="text" name="stone_rate" class="form-control" value="<?= $productdata['stone_data']['stone_rate'][$stonei] ?>">
                                </div>
                            </div>

                             <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="l30">Stone Amount</label><br/>
                                    <input name="stone_amount[]" type="text" name="stone_amount" class="form-control" value="<?= $productdata['stone_data']['stone_amount'][$stonei] ?>">
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="l30">Stone Carat</label><br/>
                                    <input type="text" name="carat[]" class="form-control" value="<?= $productdata['stone_data']['carat'][$stonei] ?>">
                                </div>
                            </div>

                            <hr/>
                        </div>
                            <?php } ?>
                            </div>
                            <button class="btn btn-primary " id="btn_save" type="submit">Update</button>

                        <?php  } ?>
                       {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('distinct_footer_script')
<script type="text/javascript">
    /*$('#btn_save').click(function(e){
        e.preventDefault();
        var product_image = $('.product_image').val();
        $.ajax({
		type: "POST",
        dataType: "json",
        url: "<?=URL::to('/') . '/productupload/updateproductstore'?>",
        data: $("#updateproductForm").serialize() + "&product_image=" + product_image + "&_token={{ csrf_token() }}",
        success: function(data) {
            if(data.status == 'success') {
                swal({
                    title: 'Success!',
                    text: data.message,
                    type: 'success',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonText: "Cancel",
                  }).then((value) => {
                    if (value.value) {
                         window.location="<?=URL::to('/').'/costing/product_list' ?>";
                    }
                });

            }
            else {
                swal("Cancelled", data.message, "error");
            }
        }
	})
});*/
</script>
@endsection	