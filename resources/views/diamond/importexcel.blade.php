@extends('layout.mainlayout')

@section('title', 'Import Excel')

@section('distinct_head')

@endsection

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('diamond.importexcel') }}
        <!-- /.page-title-right -->
    </div>

    <div class="widget-list">
        <div class="row">
            <div class="col-md-12 widget-holder">
                <div class="widget-bg">
                    <div class="widget-body clearfix">
                        <h5 class="box-title box-title-style mr-b-0">Import Excel</h5>
                        <p class="text-muted">You can add diamond by import excel</p>

                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                                <ul>

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($message = Session::get('success'))
                        <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                            <i class="material-icons list-icon">check_circle</i>
                            <strong>Success</strong>: {{ $message }}
                        </div>
                        @endif


                        @if ($message = Session::get('error'))
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                         <strong>Error</strong>: {{ $message }}
                        </div>
                      @endif
                      
                            {!! Form::open(array('route' => 'diamond.importexceldata','method'=>'POST', 'files'=>'true','id' => 'myform')) !!}
                            
                            <div class="row">
                                  <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group error-file">
                                          <label for="l30">Import Excel</label><br/>
                                    <div class="input-group ">
                                        <div class="input-group-btn width-90">
                                          <div class="fileUpload btn w-100 btn-default">
                                            <span><i class="glyphicon glyphicon-upload"></i> Upload</span>
                                            <input id="uploadBtn" type="file" class="upload width-90"  name="diamond_importexcel" accept="application/xlsx,application/xls"  />
                                          </div>
                                        </div>
                                        <input id="uploadFile"  class="form-control required border bg-transparent" placeholder="Choose File" disabled="true">
                                    </div>
                                    </div>
                                </div>
                                 <div class="col-lg-4 col-md-4 col-sm-12 align-self-end">
                                    <div class="form-group"><br>
                                        <div class="input-group ">
                                    <a href="<?=URL::to('/') . '/uploads/diamond-inventory.xlsx'?>" class="btn btn-primary " >Example Sheet</a>
                                </div>
                                </div>
                                </div>
                            </div>
                             
                            <div class="form-actions btn-list">
                                <button class="btn btn-primary" type="submit">Save</button>
                                <button class="btn btn-outline-default" onclick="goBack()" type="reset">Cancel</button>
                            </div>
                           
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


</main>
@endsection

@section('distinct_footer_script')
<script src="<?=URL::to('/');?>/js/jquery.validate.min.js"></script>
<script type="text/javascript">
 $(document).ready(function() {
   document.getElementById("uploadBtn").onchange = function () {
            document.getElementById("uploadFile").value = this.value.substring(12);
            document.getElementsByName("diamond_importexcel").value = this.value.substring(50);
        }

});
 $('#myform').validate({
    ignore: ":hidden",
            rules: {                
                diamond_importexcel: {
                    required:true,
                    accept:".xlsx,.xls"
                }
            }
        });

</script>
@endsection
