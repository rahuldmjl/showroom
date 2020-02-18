 @extends('layout.mainlayout')

@section('title', 'Move Products')

@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('virtualboxmanager.moveproducts') }}
        <!-- /.page-title-right -->
    </div>
    <!-- /.page-title -->
    <!-- =================================== -->
    <!-- Different data widgets ============ -->
    <!-- =================================== -->
    <div class="widget-list createvb">
        <div class="row">
            <div class="col-md-12 widget-holder">
                <div class="widget-bg">
                    <div class="widget-body clearfix">
                        <h5 class="box-title box-title-style mr-b-0">Move Products</h5>
                        <p class="text-muted">You can add move products by filling this form</p>
                        @if ($message = Session::get('success'))
                            <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close alert-closebtn-style" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                            <i class="material-icons list-icon">check_circle</i>
                            <strong>Success</strong>: {{ $message }}
                            </div>
                        @endif
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{!! $error !!}</li>
                                @endforeach
                                </ul>
                            </div>
                        @endif
                        {!! Form::open(array('route' => 'virtualboxmanager.storemoveproducts','method'=>'POST', 'files'=>true,'enctype'=>'multipart/form-data','id' => 'myform')) !!}
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-12 custom-select-style">
                                    <div class="form-group">
                                    <label for="l30">Select Category</label>
                                        <select class="text-uppercase vbcat" id="vbcat" name="catselectrange">
                                            <option value="">------Select------</option>
                                            <?php foreach ($categories as $cat) { ?>
                                                <option value="<?php echo $cat->entity_id ?>"><?php echo $cat->name ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 custom-select-style">
                                    <div class="form-group">
                                    <label for="l30">Select Product Range</label>
                                        <select class="text-uppercase vbrange" id="vbrange" name="rangeselected"></select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group error-file">
                                        <label for="l30">Import CSV</label><br/>
                                        <div class="input-group ">
                                            <div class="input-group-btn width-90">
                                                <div class="fileUpload btn w-100 btn-default">
                                                    <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
                                                    <input id="uploadBtnCsv" type="file" class="upload width-90"  name="vb_importcsv" />
                                                </div>
                                            </div>
                                            <input id="uploadFile"  class="form-control required border bg-transparent" placeholder="Choose File" disabled="true">                                            
                                        </div>
                                        <!-- <small class="text-muted">Technical information for user</small>
                                        <a href="<?//=URL::to('/') . '/uploads/vbdemo.csv'?>">Example Csv</a> -->
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="form-actions btn-list">
                                <button class="btn btn-primary" type="submit">Move</button>
                                <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
                            </div>
                            <input type="hidden" name="vbid" id="vbid" value="" />
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

<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="<?=URL::to('/');?>/js/common.js"></script>
<script src="<?php echo url('/') . '/cdnjs.cloudflare.com/ajax/libs/jquery/jquery.validate.js' ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script>
$('#myform').validate({
    ignore: ":hidden",
        rules: {                
            vb_importcsv: {
                required:true,
                extension: "csv",
                accept: "text/csv,text/comma-separated-value,application/vnd.ms-excel,application/vnd.msexcel,application/csv"
            },
            catselectrange:{
                required:true
            },
            rangeselected:{
                required:true
            }
        }
    });
$('#vbcat').on('change', function() {
    $.ajax({
	    type: "POST",
	    dataType: "JSON",
	    url: '<?=URL::to('/virtualboxmanager/ajaxgetrange');?>',
	    data:{category:this.value,_token:"{{ csrf_token() }}"},
	    success: function (response){
            //console.log(response.vbids);
            $("#vbid").val(response.vbids);
            $("#vbrange").html(response[0]);
	    }
	});
});
document.getElementById("uploadBtnCsv").onchange = function () {
    console.log(this.files[0].size);
    document.getElementById("uploadFile").value = this.value;
    document.getElementsByName("vb_importcsv").value = this.value;
}

$(".viewall").hide();
$(".show_hide").on("click", function () {
    var txt = $(".viewall").is(':visible') ? 'More' : 'Less';
    $(".show_hide").text(txt);
    $(this).next('.viewall').slideToggle(200);
});
</script>
@endsection
