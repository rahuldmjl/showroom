@extends('layout.mainlayout')

@section('title', 'Create Vendor')

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')



<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('vendor.create') }}
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
                        <h5 class="box-title mr-b-0">Create New Vendor</h5>
                        <p class="text-muted">You can add new vendor by filling this form</p>
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

                        {!! Form::open(array('route' => 'vendor.store','method'=>'POST','id'=>'users_form')) !!}
                            <div class="row">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="l30">Name</label>
                                        <!-- <input class="form-control" id="l30" placeholder="Email Address" type="text"> -->
                                        {{ Form::hidden('created_by',Auth::user()->id) }}
                                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="l30">Email</label>
                                        {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="l31">Password</label>
                                        <div class="input-group">
                                            {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control','id'=>'password')) !!}
                                            <span class="input-group-addon"><i class="material-icons list-icon">lock</i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="l31">Confirm Password</label>
                                        <div class="input-group">
                                            {!! Form::password('confirm_password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
                                            <span class="input-group-addon"><i class="material-icons list-icon">lock</i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                {!! Form::hidden('roles', $roles['Vendor']) !!}
                            <div class="form-group" id="vendor_dmcode">
                                 {!! Form::label('Vendor DM CODE', 'Vendor DM CODE') !!}
                                 {!! Form::text('vendor_dmcode', null, array('placeholder' => 'Vendor DM CODE','class' => 'form-control')) !!}

                            </div>
                            <div class="form-actions btn-list">
                                <input  class="btn btn-primary" type="submit" name="submit" value="Submit"/>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script type="text/javascript">
    $(document).ready( function() {
      $("#users_form").validate({
            ignore: ":hidden",
            rules: {
                name: {
                    required: true,
                    lettersonly: true

                },
                email: {
                    required: true,
                    email:true
                },
                password:{
                    required:true
                },
                confirm_password:{
                    required:true,
                    equalTo: "#password"

                },
                 'roles[]':{
                    required:true
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

       
            

    });
</script>
@endsection
