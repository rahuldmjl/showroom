<!DOCTYPE html>
<html lang="en">

 <head>

   @include('layout.errorpartials.head')

 </head>

 <body class="@yield('body_class')" style="@yield('body_style')">

@yield('content')

@include('layout.errorpartials.footer')

 </body>

</html>