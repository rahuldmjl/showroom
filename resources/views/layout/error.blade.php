<!DOCTYPE html>
<html lang="en">

 <head>

   @include('layout.errorpartials.head')

 </head>

 <body class="@yield('body_class')" @yield('body_etc_attrs')>

@yield('content')

@include('layout.errorpartials.footer')

 </body>

</html>