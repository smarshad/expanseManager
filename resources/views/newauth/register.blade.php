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
                                    <a href="{{route('login')}}">
                                        <img src="{{asset('backend/images/logo-dark.png')}}" alt="Logo" height="30">
                                    </a>
                                </div>
                                <h5 class="text-uppercase mb-1 mt-4">Register</h5>
                                <p class="mb-0">Get access to our admin panel</p>
                            </div>

                            <div class="account-content mt-4">
                                @if(session('error'))
                                <div class="alert alert-danger">{{session('error')}}</div>
                                @endif
                                @if($errors->any() && $errors->count() > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                <form class="form-horizontal" method="POST" action="{{ route('register.store') }}">
                                    @csrf
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <label for="username">Full Name</label>
                                            <input class="form-control" id="name" type="text" name="name" value="{{old('name')}}" required autofocus placeholder="Michael Zenaty">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12">
                                            <label for="email">Email address</label>
                                            <input class="form-control" id="email" type="email" name="email" value="{{old('email')}}" required placeholder="john@deo.com">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <label for="mobile_no">Mobile No</label>
                                            <input class="form-control" id="mobile_no" type="text" name="mobile_no" value="{{old('mobile_no')}}" required placeholder="1234567890">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12">
                                            {{--<a href="{{route('admin.forgot-password')}}" class="text-muted float-right"><small>Forgot your password?</small></a>--}}
                                            <label for="password">Password</label>
                                            <input class="form-control" type="password" name="password" required id="password" placeholder="Enter your password">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12">
                                            <label for="password_confirmation">Confirm Password</label>
                                            <input class="form-control" type="password" name="password_confirmation" required id="password_confirmation" placeholder="Enter your password">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12">
                                            <div class="checkbox checkbox-success">
                                                <input id="remember" type="checkbox" checked="">
                                                <label for="remember">
                                                    I accept <a href="#">Terms and Conditions</a>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row text-center mt-2">
                                        <div class="col-12">
                                            <button class="btn btn-md btn-block btn-primary waves-effect waves-light" type="submit">Sign Up Free</button>
                                        </div>
                                    </div>
                                </form>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="text-center">
                                            <button type="button" class="btn mr-1 btn-facebook waves-effect waves-light">
                                                <i class="fab fa-facebook-f"></i>
                                            </button>
                                            <button type="button" class="btn mr-1 btn-googleplus waves-effect waves-light">
                                                <i class="fab fa-google"></i>
                                            </button>
                                            <button type="button" class="btn mr-1 btn-twitter waves-effect waves-light">
                                                <i class="fab fa-twitter"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4 pt-2">
                                    <div class="col-sm-12 text-center">
                                        <p class="text-muted">Already have an account? <a href="{{route('login')}}" class="text-dark ml-1"><b>Sign In</b></a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end card-body -->
            </div>
            <!-- end card -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</div>
@endsection