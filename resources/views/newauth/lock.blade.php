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
                                        <img src="{{asset('backend//images/users/avatar-5.jpg')}}" class="rounded-circle img-thumbnail avatar-lg" alt="thumbnail">
                                    </div>

                                    <p class="text-muted mb-0 font-13">Enter your password to access the admin. </p>
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
                                <form class="form-horizontal" method="POST" action="{{route('lock.unlock')}}">
                                    @csrf
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <label for="password">Password</label>
                                            <input class="form-control" name="password" type="password" autofocus required id="password" placeholder="Enter your password">
                                        </div>
                                    </div>
                                    <div class="form-group row text-center mt-2">
                                        <div class="col-12">
                                            <button class="btn btn-md btn-block btn-primary waves-effect waves-light" type="submit">Unlock</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="clearfix"></div>
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