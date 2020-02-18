@extends('layout.mainlayout')

@section('title', 'Create Transaction Type')

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
    <div class="row page-title clearfix">
        {{ Breadcrumbs::render('transactiontype.create') }}
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
                        <h5 class="box-title box-title-style mr-b-0">Create New Transaction Type</h5>
                        <p class="text-muted">You can add transaction type by filling this form</p>

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

                        {!! Form::open(array('route' => 'transactiontype.store','method'=>'POST')) !!}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="l30">Name</label>
                                        <!-- <input class="form-control" id="l30" placeholder="Email Address" type="text"> -->
                                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
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

@endsection