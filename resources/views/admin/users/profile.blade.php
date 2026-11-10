@extends('layoutsnew.app')
@push('scripts')
<!-- Sparkline charts -->
<script src="{{asset('backend/libs/jquery-sparkline/jquery.sparkline.min.js')}}"></script>

<script src="{{asset('backend/js/pages/profile.init.js')}}"></script>
@endpush
<!-- Begin page -->
@section('content')
<div class="content">

    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Adminox</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Extras</a></li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Profile</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-sm-12">
                <div class="profile-bg-picture"
                    style="background-image:url({{ asset('backend/images/bg-profile.jpg') }})">
                    <span class="picture-bg-overlay"></span><!-- overlay -->
                </div>

                <!-- meta -->
                <div class="profile-user-box">
                    <div class="row">
                        <div class="col-sm-6">
                            <span class="float-left mr-3"><img src="{{asset('backend/images/users/avatar-1.jpg')}}" alt=""
                                    class="avatar-xl rounded-circle"></span>
                            <div class="media-body">
                                <h4 class="mt-1 mb-1 font-18 ellipsis">{{$user->name}}</h4>
                                <p class="font-13"> User Experience Specialist</p>
                                <p class="text-muted mb-0"><small>California, United States</small></p>
                            </div>
                        </div>
                        <!-- <div class="col-sm-6">
                            <div class="text-right">
                                <button type="button" class="btn btn-success waves-effect waves-light">
                                    <i class="mdi mdi-account-settings-variant mr-1"></i> Edit Profile
                                </button>
                            </div>
                        </div> -->
                    </div>
                </div>
                <!--/ meta -->
            </div>
        </div>
        <!-- end row -->

        <div class="row">
            <div class="col-xl-4">
                <!-- Personal-Information -->
                <div class="card-box">
                    <h4 class="header-title mt-0 mb-4">Personal Information</h4>
                    <div class="panel-body">
                        <p class="text-muted font-13">
                            Hye, I’m Johnathan Doe residing in this beautiful world. I create websites and
                            mobile apps with great UX and UI design. I have done work with big companies
                            like Nokia, Google and Yahoo. Meet me or Contact me for any queries. One Extra
                            line for filling space. Fill as many you want.
                        </p>

                        <hr />

                        <div class="text-left">
                            <p class="text-muted font-13"><strong>Full Name :</strong> <span class="ml-3">{{$user->name}}</span></p>

                            <p class="text-muted font-13"><strong>Mobile :</strong><span class="ml-3">(+91) {{$user->mobile_no}}</span></p>

                            <p class="text-muted font-13"><strong>Email :</strong> <span class="ml-3">{{$user->email}}</span></p>

                            <p class="text-muted font-13"><strong>Location :</strong> <span class="ml-3">USA</span></p>

                            <p class="text-muted font-13"><strong>Languages :</strong>
                                <span class="ml-1">
                                    <span class="flag-icon flag-icon-us mr-1 mt-0" title="us"></span>
                                    <span>English</span>
                                </span>
                                <span class="ml-1">
                                    <span class="flag-icon flag-icon-de mr-1" title="de"></span>
                                    <span>German</span>
                                </span>
                                <span class="ml-1">
                                    <span class="flag-icon flag-icon-es mr-1" title="es"></span>
                                    <span>Spanish</span>
                                </span>
                                <span class="ml-1">
                                    <span class="flag-icon flag-icon-fr mr-1" title="fr"></span>
                                    <span>French</span>
                                </span>
                            </p>

                        </div>

                        <ul class="social-links list-inline mt-4 mb-0">
                            <li class="list-inline-item">
                                <a title="" data-placement="top" data-toggle="tooltip" class="tooltips" href="" data-original-title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            </li>
                            <li class="list-inline-item">
                                <a title="" data-placement="top" data-toggle="tooltip" class="tooltips" href="" data-original-title="Twitter"><i class="fab fa-twitter"></i></a>
                            </li>
                            <li class="list-inline-item">
                                <a title="" data-placement="top" data-toggle="tooltip" class="tooltips" href="" data-original-title="Skype"><i class="fab fa-skype"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Personal-Information -->
            </div>

            <div class="col-xl-8">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card-box">
                            <h4 class="header-title mb-3">Profile Information</h4>
                            @if (session('status'))
                            <div class="alert alert-success">{{session('status')}}</div>
                            @endif

                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <form method="post" action="{{ route('profile.update') }}" class="form-horizontal">
                                @csrf
                                @method('patch')
                                <div class="form-group row">
                                    <label for="name" class="col-3 col-form-label">Full Name</label>
                                    <input type="text" class="form-control col-9" id="name" name="name" value="{{old('name', $user->name)}}" required autofocus placeholder="Enter email">
                                </div>

                                <div class="form-group row">
                                    <label for="email" class="col-3 col-form-label">Email</label>
                                    <input type="text" class="form-control col-9" id="email" name="email" value="{{old('email', $user->email)}}" required placeholder="Enter email">

                                </div>

                                <div class="form-group row">
                                    <label for="mobile_no" class="col-3 col-form-label">Mobile No</label>
                                    <input type="text" class="form-control col-9" id="mobile_no" name="mobile_no" value="{{old('mobile_no', $user->mobile_no)}}" required placeholder="Enter Mobile Number">
                                    @error('mobile_no')
                                    <span>{{ $message }}</span>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary waves-effect waves-light">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card-box">
                            <h4 class="header-title mb-3">Update Password</h4>
                            @if (session('password'))
                            <div class="alert alert-success">{{session('password')}}</div>
                            @endif

                            @if ($errors->updatePassword->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->updatePassword->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <form method="post" action="{{ route('password.update') }}" autocomplete="off" class="form-horizontal">
                                @csrf
                                @method('put')
                                <div class="form-group row">
                                    <label for="update_password_current_password" class="col-3 col-form-label">Current Password</label>
                                    <input type="password" class="form-control col-9" id="update_password_current_password" name="current_password" required placeholder="Enter current password" autocomplete="current-password">
                                </div>

                                <div class="form-group row">
                                    <label for="update_password_password" class="col-3 col-form-label">New Password</label>
                                    <input type="password" class="form-control col-9" id="update_password_password" name="password" required placeholder="Enter New Password" autocomplete="new-password">
                                </div>

                                <div class="form-group row">
                                    <label for="update_password_password_confirmation" class="col-3 col-form-label">Confrim Password</label>
                                    <input type="password" class="form-control col-9" id="update_password_password_confirmation" name="password_confirmation" required placeholder="Enter Confirm Password">
                                </div>
                                <button type="submit" class="btn btn-purple waves-effect waves-light">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card-box">
                            <h4 class="header-title mb-3">Two Factor Authentication</h4>
                            <p>If you want two secure youe account enable two factor authentication.</p>
                            @if (session('success'))
                            <div class="alert alert-success">{!!session('success')!!}</div>
                            @endif
                            @if (session('error'))
                            <div class="alert alert-error">{{session('error')}}</div>
                            @endif
                            @if ($errors->updatePassword->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->updatePassword->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <form method="post" action="{{ route('profile.two.factor.auth') }}" autocomplete="off" class="form-horizontal">
                                @csrf
                                @method('put')
                                <input type="hidden" name="enable_two_factor_auth" value="{{$user->enable_two_factor_auth}}">
                                <button type="submit" class="btn btn-{{$user->enable_two_factor_auth ? 'danger' : 'primary'}} waves-effect waves-light">{{$user->enable_two_factor_auth ? 'Disable' : 'Enabled'}}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- end container-fluid -->
</div>
@endsection