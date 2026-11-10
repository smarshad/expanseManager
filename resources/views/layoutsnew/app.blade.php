<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="perPage" content="{{ config('pagination.default') }}">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('backend/images/favicon.ico') }}">
    @stack('styles')
    <!-- App css -->
    <link href="{{ asset('backend/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
    <link href="{{ asset('backend/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/css/app.min.css') }}" rel="stylesheet" type="text/css"  id="app-stylesheet" />
    <style>
        .pt-10{padding-top: 10px;}
        #sidebar-menu>ul>li>a{font-size:14px !important}
    </style>
</head>

<body>
    <div id="wrapper">
        @include('admin.partials.topnavbar')
        @include('admin.partials.leftnavbar')
        <div class="content-page">
            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->
            @yield('content')
            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->
        </div>
        <!-- Footer Start -->
        @include('admin.partials.footer')
        <!-- end Footer -->
        @include('admin.includes.modal')
    </div>
    <!-- end page -->

    <!-- Vendor js -->
    <script src="{{ asset('backend/js/vendor.min.js') }}"></script>
    @stack('scripts')
    <script src="{{ asset('backend/js/table-export.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('backend/js/app.min.js') }}"></script>
</body>

</html>