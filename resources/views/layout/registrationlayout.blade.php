<!DOCTYPE html>

<html lang="en">

 <head>

   @include('layout.registrationpartials.head')

 </head>

 <body class="body-bg-full profile-page" style="background-image: url(<?=URL::to('/');?>/assets/demo/night.jpg)">


@include('layout.registrationpartials.nav')

@include('layout.registrationpartials.header')

@yield('content')

@include('layout.registrationpartials.footer')

@include('layout.registrationpartials.footer-scripts')

 </body>

</html>