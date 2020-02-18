@extends('layout.mainlayout')

@section('title', 'Edit Role')

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('roles.edit', $role) }}
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
                        <h5 class="box-title box-title-style mr-b-0">Edit Role</h5>
                        <p class="text-muted">You can modify role details here in this form</p>

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

                        {!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id],'id'=>'roles_form']) !!}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="l30">Name</label>
                                        <!-- <input class="form-control" id="l30" placeholder="Email Address" type="text"> -->
                                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group border checkbox checkbox-primary p-0">
                                    <div class="w-100 permission-title"><label for="l30">Permission</label></div>
                                           <div class="permission-list d-flex flex-wrap p-2">
                                            <?php
                                                //echo count($permission);
                                                //echo $items_per_row = round(count($permission) / 3);
                                                $items_per_row = 3;
                                                ?>
                                                @foreach($permission as $key => $value)
                                                    <?php
                                                $round = $key + 1;
                                                if ($round % $items_per_row == 1) {
                                                    ?>
                                                    <?php
                                                }
                                                ?>
                                                    <label class="text-truncate">{{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'name')) }}
                                                    <span class="label-text">{{ $value->name }}</span>
                                                    </label>
                                                <?php
                                                if ($round % $items_per_row == 0) {
                                                    ?>
                                                    <?php
                                                }
                                                ?>

                                        @endforeach
                                            </div>

                                    </div>
                                </div>
                            </div>
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

<div class="row" style="display: none;">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Edit Role</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('roles.index') }}"> Back</a>
        </div>
    </div>
</div>

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


{!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id],'id'=>'roles_form']) !!}
<div class="row" style="display: none;">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Name:</strong>
            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Permission:</strong>
            <br/>
            @foreach($permission as $value)
                <label>{{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'name')) }}
                {{ $value->name }}</label>
            <br/>
            @endforeach
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>
{!! Form::close() !!}


@endsection
@section('distinct_footer_script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
<script type="text/javascript">
    $(document).ready( function() {

    jQuery.validator.addMethod("lettersandspace", function(value, element) {
        return this.optional(element) || /^[a-z\s]+$/i.test(value);
    }, "Only letters and space allowed");

      $("#roles_form").validate({
            ignore: ":hidden",
            rules: {
                name: {
                    required: true,
                    lettersandspace: true

                }
            },
            messages: {
                name: {
                 lettersandspace:"Only letters and space allowed."
                 }

            }

        });
  });
</script>
@endsection