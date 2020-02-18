@extends('layout.mainlayout')

@section('title', 'Edit Diamond Transaction')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('virtualboxmanager.editvb',$vbcoll->id) }}
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
                        <h5 class="box-title box-title-style mr-b-0">Edit Virtual Box</h5>
                        <p class="text-muted">You can edit virtual box by filling this form</p>
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

                        {!! Form::model($vbcoll,array('route' => ['virtualboxmanager.update', $vbcoll->id],'method'=>'PATCH', 'files'=>'true','id'=>'vb_edit')) !!}
                            <?php if($findproduct){ ?>
                                <?php $disabled = "disabled"; ?>
                            <?php }else{ ?>
                                <?php $disabled = ""; ?>
                            <?php } ?>
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                   <div class="form-group">
                                        <label for="l30">Box Name</label>
                                        <input type="text" name="name" value="{{$vbcoll->name}}" class="form-control" id="vb_name">
                                    </div> 
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                  <div class="form-group">
                                        <label for="l30">Code</label>
                                        <input type="text" name="code" value="{{$vbcoll->code}}" class="form-control" id="vb_code">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                    <label for="l30">Product Limit(In)</label>
                                        <input type="number" name="products_limit" value="{{$vbcoll->products_limit}}" class="form-control <?php echo $disabled; ?>" id="products_limit">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 custom-select-style">
                                    <div class="form-group">
                                        <label for="l30">Select Category</label>
                                        <select class="text-uppercase vbcat form-control  <?php echo $disabled; ?>" id="category_id" name="category_id">
                                            <option value="">------Select------</option>
                                            <?php 
                                            foreach ($categorys as $row) { ?>
                                            <option value="<?php echo $row->entity_id ?>" <?php if($vbcoll->category_id == $row->entity_id){ ?>selected='selected' <?php } ?>><?php echo $row->name; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                    <label for="l30">Price From</label>
                                    <input type="number" name="price_from" pattern="^-?([0-9]*\.?[0-9]+|[0-9]+\.?[0-9]*)$" value="{{$vbcoll->price_from}}" class="form-control <?php echo $disabled; ?>" id="price_from">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                    <label for="l30">Price To</label>
                                    <input type="number" name="price_to" pattern="^-?([0-9]*\.?[0-9]+|[0-9]+\.?[0-9]*)$" value="{{$vbcoll->price_to}}" class="form-control <?php echo $disabled; ?>" id="price_to">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="l30">Remarks</label>
                                        <textarea name="remarks" rows="3" class="form-control <?php echo $disabled; ?>">{{$vbcoll->remarks}}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions btn-list">
                                <button class="btn btn-primary" type="submit">Save</button>
                                <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
                            </div>
                            <!-- {{ Form::hidden('vendorId', '', array('id' => 'venID')) }} -->
                        {!! Form::close() !!}
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

@endsection

@section('distinct_footer_script')
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script type="text/javascript">
   $("#vb_edit").validate({
            ignore: ":hidden",
            rules: {
                category_id:{
                    required:true
                },
                name:{
                    required:true
                },
                price_from:{
                    required:true,
                    digits: true
                },
                price_to:{
                    required:true,
                    digits: true,
                    greaterThan: "#price_from"
                },
                products_limit:{
                    required:true,
                    min:1,
                    digits: true,
                    max:100
                },
                code:{
                    required:true,
                    alphanumeric: true
                }
            }
        });
        $.validator.addMethod("greaterThan",
        function (value, element, param) {
            var $otherElement = $(param);
            return parseInt(value, 10) > parseInt($otherElement.val(), 10);
        },'Must be greater than price from.');

        jQuery.validator.addMethod("alphanumeric", function(value, element) {
            return this.optional(element) || /^[\w.]+$/i.test(value);
        }, "Letters, numbers, and underscores only please");
   $(document).ready(function() {
   });
</script>
@endsection