@extends('layout.mainlayout')

@section('title', 'Edit User')
@section('distinct_head')

<link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.25/daterangepicker.min.css" rel="stylesheet" type="text/css">
    <link href="<?=URL::to('/');?>/cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">

@endsection
@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<?php
$authuser = Auth::user();
?>

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('users.edit', $user) }}
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
                        <h5 class="box-title box-title-style mr-b-0">Edit User</h5>
                        <p class="text-muted">You can modify user details here in this form</p>

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

                        {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id],'id'=>'edit_users_form']) !!}
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
                            <div class="form-group" >
                                <label for="l38">Role(s)</label>
                                {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control rolemaster','multiple')) !!}
                            </div>
                             <div class="form-group" id="vendor_dmcode">
                                 {!! Form::label('Vendor DM Code', 'Vendor DM Code') !!}
                                 {!! Form::text('vendor_dmcode', null, array('placeholder' => 'Vendor DM Code','class' => 'form-control')) !!}

                            </div>

                            <div class="form-group" id="address_val">
                                 {!! Form::label('Address', 'Address') !!}
                                 {!! Form::textarea('address', null, ['class' => 'form-control', 'id' => 'address', 'rows' => 4, 'cols' => 54]) !!} <!-- , 'style' => 'resize:none'-->
                            </div>

                            <div class="form-group" id="state_val">
                                 {!! Form::label('State', 'State') !!}
                                 {!! Form::text('state', null, array('placeholder' => 'State','class' => 'form-control')) !!}
                            </div>

                            <div class="form-group" id="gstin_val">
                                 {!! Form::label('GSTIN', 'GSTIN') !!}
                                 {!! Form::text('gstin', null, array('placeholder' => 'GSTIN','class' => 'form-control')) !!}
                            </div>

                            <?php
if ($authuser->hasRole('Super Admin') || $authuser->hasRole('User Manager')) {
	?>
                            <div class="form-group" id="is_admin">
                                <div class="checkbox checkbox-primary">
                                    <label class="">
                                        {!! Form::checkbox('is_admin', '1', $user->is_admin, array('class' => 'form-control1')) !!}
                                        <span class="label-text">Is Admin</span>
                                    </label>
                                </div>
                            </div>
                            <?php
} else {
	?>
                            <div class="form-group" id="is_admin" style="display: none;">
                                <div class="checkbox checkbox-primary">
                                    <label class="">
                                        {!! Form::checkbox('is_admin', '1', false, array('class' => 'form-control1')) !!}
                                        <span class="label-text">Is Admin</span>
                                    </label>
                                </div>
                            </div>
    <?php
}
?>
                            <div class="form-actions btn-list">
                                <button class="btn btn-primary" type="submit">Submit</button>
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
    <!-- /.widget-list -->
</main>
<!-- /.main-wrapper -->

@endsection
@section('distinct_footer_script')

<script type="text/javascript">
    $(document).ready( function() {

        jQuery.validator.addMethod("lettersandspace", function(value, element) {
            return this.optional(element) || /^[a-z\s]+$/i.test(value);
        }, "Only letters and space allowed");

        $("#edit_users_form").validate({
            ignore: ":hidden",
            rules: {
                name: {
                    required: true,
                    lettersandspace : true

                },
                email: {
                    required: true,
                    email:true
                },
                vendor_dmcode:{
                    required:true
                },
                address:{
                    required:true
                },
                gstin:{
                    required:true
                },
                state:{
                    required:true
                }


            },
            messages: {
                name: {
                 lettersandspace:"Only letters and space allowed."
                 }

            }

        });

        function HideShowDmCode(){
            $('#vendor_dmcode').hide();
            $('#address_val').hide();
            $('#gstin_val').hide();
            $('#state_val').hide();
            $('.rolemaster option:selected').each(function() {
                if($(this).val() == 'Vendor')
                {
                    $('#vendor_dmcode').show();
                    $('#address_val').show();
                    $('#gstin_val').show();
                    $('#state_val').show();
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
