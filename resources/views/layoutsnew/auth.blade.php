<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Authentication')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico') }}">

    <!-- App css -->
    <link href="{{ asset('backend/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
    <link href="{{ asset('backend/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/css/app.min.css') }}" rel="stylesheet" type="text/css"  id="app-stylesheet" />
    @stack('styles')

</head>

<body {!! $bodyCss ?? '' !!}>

    @yield('content')
    <!-- end page -->

    <!-- Vendor js -->
    <script src="{{ asset('backend/js/vendor.min.js') }}"></script>
    @stack('scripts')

    <!-- App js -->
    <script src="{{ asset('backend/js/app.min.js') }}"></script>
</body>

</html>