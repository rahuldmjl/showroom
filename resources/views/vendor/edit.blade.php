@extends('layout.mainlayout')

@section('title', 'Edit Vendor')
@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
    <link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">

@endsection
@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
     <div class="row page-title clearfix">
        {{ Breadcrumbs::render('vendor.edit',[$vendors->id]) }}
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
                        <h5 class="box-title mr-b-0">Edit Vendor</h5>
                        <p class="text-muted">You can modify vendor details here in this form</p>

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

                        {!! Form::model($vendors, ['method' => 'PATCH','route' => ['vendor.update', $vendors->id],'id'=>'edit_users_form']) !!}
                             <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="l30">Name</label>
                                        <!-- <input class="form-control" id="l30" placeholder="Email Address" type="text"> -->
                                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="l30">Email</label>
                                        {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="l31">Password</label>
                                        <div class="input-group">
                                            {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                                            <span class="input-group-addon"><i class="material-icons list-icon">lock</i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="l31">Confirm Password</label>
                                        <div class="input-group">
                                            {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
                                            <span class="input-group-addon"><i class="material-icons list-icon">lock</i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {!! Form::hidden('roles', $roles['Vendor']) !!}
                             <div class="form-group">
                                 {!! Form::label('Vendor DM CODE', 'Vendor DM CODE') !!}
                                 {!! Form::text('vendor_dmcode', null, array('placeholder' => 'Vendor DM CODE','class' => 'form-control')) !!}

                            </div>
                            <div class="form-actions btn-list">
                                <button class="btn btn-primary" type="submit">Submit</button>
                                <button class="btn btn-outline-default" type="reset">Cancel</button>
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
    <!-- /.widget-list -->
</main>
<!-- /.main-wrapper -->

@endsection
@section('distinct_footer_script')

<script type="text/javascript">
    $(document).ready( function() {
        $("#edit_users_form").validate({
            ignore: ":hidden",
            rules: {
                name: {
                    required: true,
                    accept : "[a-zA-Z]+"

                },
                email: {
                    required: true,
                    email:true
                },
                vendor_dmcode:{
                    required:true
                }


            },
            messages: {
                name: {
                 required:   "Please enter your name",
                 lettersonly:"Name can only contain letters."
                 }

            }

        });

        function HideShowDmCode(){
            $('#vendor_dmcode').hide();
            $('.rolemaster option:selected').each(function() {
                if($(this).val() == 'Vendor')
                {
                    $('#vendor_dmcode').show();
                }
            });
        }

        HideShowDmCode();
        $('.rolemaster').change(function(){
            HideShowDmCode();
        });
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script src="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/datatables/1.10.15/js/jquery.dataTables.min.js"></script>

@endsection
