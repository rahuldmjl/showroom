@extends('layout.mainlayout')

@section('title', 'Change Password')

@section('body_class', 'header-light sidebar-dark sidebar-expandheader-light sidebar-dark sidebar-expand')

@section('content')
<main class="main-wrapper clearfix">
     <div class="row page-title clearfix">
        <div class="page-title-left">
            <h5 class="mr-0 mr-r-5">Form Elements</h5>
            <p class="mr-0 text-muted d-none d-md-inline-block">statistics, charts, events and reports</p>
        </div>
        <div class="page-title-right d-inline-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index-2.html">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Form Elements</li>
            </ol>
        </div>
     </div>
    
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
    <!--  @if ($message = Session::get('success'))
                      <div class="alert alert-icon alert-success border-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <i class="material-icons list-icon">check_circle</i>
                        <strong>Success</strong>: {{ $message }}
                      </div>
                      @endif -->
    <form class="form-horizontal" method="POST" action="{{ route('changePassword') }}" id="password_form">

    @csrf

        <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }}">
            <label for="new-password" class="col-md-4 control-label">Current Password</label>
            <div class="col-md-6">
                <input id="current-password" type="password" class="form-control" name="current-password" required>

                @if ($errors->has('current-user.jpgpassword'))
                    <span class="help-block">
                    <strong>{{ $errors->first('current-password') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="form-group{{ $errors->has('new-password') ? ' has-error' : '' }}">
            <label for="new-password" class="col-md-4 control-label">New Password</label>
                <div class="col-md-6">
                    <input id="new-password" type="password" class="form-control" name="new-password" required>
                        @if ($errors->has('new-password'))
                            <span class="help-block">
                                <strong>{{ $errors->first('new-password') }}</strong>
                            </span>
                        @endif
                </div>
        </div>
        <div class="form-group">
            <label for="new-password-confirm" class="col-md-4 control-label">Confirm New Password</label>
                <div class="col-md-6">
                    <input id="new-password-confirm" type="password" class="form-control" name="new-password_confirmation" required>
                </div>
        </div>
        <div class="form-group">
            <div class="col-md-6 col-md-offset-4">
                <button type="submit" class="btn btn-primary">
                    Change Password
                </button>
            </div>
        </div>
@endsection
@section('distinct_footer_script')

<script type="text/javascript">
   $.ajaxSetup({

        headers: {

            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

    });
    $(document).ready( function() {
       $("#password_form").validate({
       ignore: ":hidden",
        rules: {
            current-password: {
                required:true,
            },
            new-password: {
                required:true,
            },
            new-password-confirm: {
                required:true,
                equalTo: "#new-password"
            },
        },
        messages: {
               current-password: "Please provide a current password",
              
        }

      });
});

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
@endsection