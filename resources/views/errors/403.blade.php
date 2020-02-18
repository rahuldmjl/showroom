@extends('layout.errorlayout')

@section('title', 'Error 403')

@section('body_class', 'body-bg-full error-page error-403')
@section('body_style', 'background-image: url(assets/demo/body-bg-403.jpg)')

@section('content')

<div id="wrapper" class="wrapper">
    <div class="content-wrapper">
        <main class="main-wrapper">
            <div class="page-title">
                <h1>403</h1>
            </div>
            <h4>Access Denied!</h4>
            <p class="mr-t-10 mr-b-20">You don't have permission to access on this server.</p><a href="javascript: history.back();" class="btn btn-info btn-lg btn-rounded mr-b-20 ripple">Go Back</a>
        </main>
    </div>
    <!-- .content-wrapper -->
</div>

@endsection