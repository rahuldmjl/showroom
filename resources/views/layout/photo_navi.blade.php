<!DOCTYPE html>

<html lang="en">

 <head>

   @include('layout.partials.head')

 </head>

 <body class="@yield('body_class')">


@include('layout.partials.nav')

@include('layout.partials.photo_header')

@yield('content')

@include('layout.partials.right-sidebar')

@include('layout.partials.footer')

@include('layout.partials.footer-scripts')

 </body>

</html>