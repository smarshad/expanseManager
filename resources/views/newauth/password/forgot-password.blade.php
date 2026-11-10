@extends('layoutsnew.auth')

@section('content')
<div class="home-btn d-none d-sm-block">
    <a href="{{route('login')}}"><i class="fas fa-home h2 text-white"></i></a>
</div>
<div class="account-pages w-100 mt-5 mb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card mb-0">
                    <div class="card-body p-4">
                        <div class="account-box">
                            <div class="text-center account-logo-box">
                                <div>
                                    <a href="index.html">
                                        <img src="{{asset('backend/images/logo-dark.png')}}" alt="" height="30">
                                    </a>
                                </div>
                            </div>
                            <div class="account-content mt-4">
                                <div class="text-center">
                                    <p class="text-muted mb-0 mb-3">Enter your email address and we'll send you an email with instructions to reset your password. </p>
                                </div>

                                @if($errors->any())

                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach($errors->all() as $error)
                                        <li>{{$error}}</li>
                                        @endforeach
                                    </ul>
                                </div>

                                @endif
                                @if(session('status'))
                                <div class="alert alert-success">{{session('status')}}</div>
                                @endif
                                <form class="form-horizontal" method="POST" action="{{ route('password.forgot.password') }}">
                                    @csrf

                                    <div class="form-group row">
                                        <div class="col-12">
                                            <label for="email">Email address</label>
                                            <input class="form-control" id="email" type="email" name="email" value="{{old('email')}}" required placeholder="john@deo.com">
                                        </div>
                                    </div>

                                    <div class="form-group row text-center mt-2">
                                        <div class="col-12">
                                            <button class="btn btn-md btn-block btn-primary waves-effect waves-light" type="submit">Reset Password</button>
                                        </div>
                                    </div>

                                </form>
                                <div class="clearfix"></div>
                                <div class="row mt-4">
                                    <div class="col-sm-12 text-center">
                                        <p class="text-muted mb-0">Back to <a href="{{route('login')}}" class="text-dark ml-1"><b>Sign In</b></a></p>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- end card-box-->
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