@extends('layoutsnew.auth')
<div class="home-btn d-none d-sm-block">
    <a href="{{route('login')}}"><i class="fas fa-home h2 text-white"></i></a>
</div>
@section('content')
<div class="account-pages w-100 mt-5 mb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card mb-0">
                    <div class="card-body p-4">
                        <div class="account-box">
                            <div class="account-logo-box">
                                <div class="text-center">
                                    <a href="index.html">
                                        <img src="{{asset('backend/images/logo-dark.png')}}" alt="" height="30">
                                    </a>
                                </div>
                            </div>
                            <div class="account-content mt-4">
                                <div class="text-center mb-3">
                                    <div class="mb-3">
                                        <img src="{{asset('backend/images/users/avatar-5.jpg')}}" class="rounded-circle img-thumbnail avatar-lg" alt="thumbnail">
                                    </div>
                                    <p class="text-muted mb-0 font-13">Please enter the 6-digit OTP we sent to your email to verify your account.</p>
                                </div>
                                @if (!session()->has('users') || empty(session('users')))
                                <!-- <script>
                                    window.location.href = "{{ route('register') }}";
                                </script> -->
                                <div class="alert alert-danger">
                                    Session expired or invalid access. Please register again.
                                </div>
                                @endif
                                @if(session('error'))
                                <div class="alert alert-danger">{!!session('error')!!}</div>
                                @endif
                                @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach($errors->all() as $error)
                                        <li>{{$error}}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                <form class="form-horizontal" method="POST" action="{{route('register.otp.verify')}}">
                                    @csrf
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <label for="otp">OTP</label>
                                            <input class="form-control" maxlength="6" minlength="6" name="otp" type="text" id="otp" placeholder="Enter your OTP" autofocus required>
                                        </div>
                                    </div>
                                    <div class="form-group row text-center mt-2">
                                        <div class="col-12">
                                            <button class="btn btn-md btn-block btn-primary waves-effect waves-light" type="submit">Verify & Register</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="clearfix"></div>
                                <div class="row mt-3">
                                    <div class="col-sm-12 text-center">
                                        <p class="text-muted mb-0">Already Registered Users? return<a href="{{route('login')}}" class="text-dark ml-1"><b>Sign In</b></a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card-body -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</div>
<!-- end page -->
@endsection