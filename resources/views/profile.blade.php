@extends('layout.mainlayout')

@section('title', 'Edit Profile')

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')

<main class="main-wrapper clearfix">
    <!-- Page Title Area -->
     <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
    <div class="row page-title clearfix">
        <div class="page-title-left">
            <h5 class="mr-0 mr-r-5">Form Elements</h5>
            <p class="mr-0 text-muted d-none d-md-inline-block">statistics, charts, events and reports</p>
        </div>
        <!-- /.page-title-left -->
        <div class="page-title-right d-inline-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index-2.html">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Form Elements</li>
            </ol>
            <div class="d-none d-sm-inline-flex justify-center align-items-center"><a href="javascript: void(0);" class="btn btn-outline-primary mr-l-20 btn-sm btn-rounded hidden-xs hidden-sm ripple" target="_blank">Buy Now</a>
            </div>
        </div>
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
                        <h5 class="box-title box-title-style mr-b-0">Edit Profile</h5>
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
                            <div class="profile-header-container">
                                <div class="profile-header-img">
                                    <?php
                                        if (file_exists('assets/images/' . Auth::user()->avatar)) {
                                            ?>
                                        <img class="rounded-circle" src="<?=URL::to('/');?>/assets/images/{{$user->avatar}} " style="height: 150px;width: 160px;"/>
                                    <?php
                                        } else {
                                            ?>
                                        <img class="rounded-circle" src="<?=URL::to('/');?>/assets/images/users.jpeg " style="height: 150px;width: 160px;"/>
                                        <?php
                                    }
                                    ?>
                                        <!-- badge -->
                                        <div class="rank-label-container">
                                            <span class="label label-default rank-label">{{$user->name}}</span>
                                        </div>
                                </div>
                            </div>
                            <form class="form-horizontal" method="post" action="profile"  enctype="multipart/form-data">
                                        @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                                <label for="l30">Select</label>
                                                <input type="file" class="form-control-file" name="avatar" id="avatarFile" aria-describedby="fileHelp">
                                                <small id="fileHelp" class="form-text text-muted"></small>
                                                <a class="btn btn-primary small-btn-style mt-3" name="avatar" href="{{ route('removeavatar') }}">Remove Avtar</a>
                                        </div>
                                    </div>   
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                                <label for="l30">Name</label>
                                                {!! Form::text('name', $user->name, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">Email</label>
                                            {!! Form::text('email', $user->email, array('placeholder' => 'Email','class' => 'form-control')) !!}
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="l30">phone number</label>
                                            {!! Form::text('phone', $user->phone, array('placeholder' => 'phone','class' => 'form-control')) !!}
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection