@extends('layoutsnew.app')
@section('content')
<div class="content">

    <!-- Start Content-->
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Adminox</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('users.title')}}</a></li>
                            <li class="breadcrumb-item active">New</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{__('users.title')}}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-3">{{ __('users.edit_title')}}</h4>
                        <a href="{{ route('users.index') }}" class="btn btn-primary">
                            {{__('global.back')}}
                        </a>
                    </div>
                    <x-alert />

                    <form id="editRoleForm" action="{{route('users.update', $user->id)}}" method="POST" class="data-ajax-submit form-horizontal">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="name" class="col-3 col-form-label">Name</label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="name" name="name" value="{{old('name', $user->name)}}" placeholder="create user">
                            </div>
                            <label for="mobile_no" class="col-3 col-form-label">Mobile No</label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="mobile_no" name="mobile_no" value="{{old('mobile_no', $user->mobile_no)}}" placeholder="Mobile No">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-3 col-form-label">Email</label>
                            <div class="col-3">
                                <input type="email" class="form-control" id="email" name="email" value="{{old('email', $user->email)}}" placeholder="email" autocomplete="FALSE">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="role" class="col-3 col-form-label">Select Role</label>
                            @foreach($roles as $role)
                            <div class="col-6 col-md-3 mb-2">
                                <div class="form-check">
                                    <input type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->id }}"
                                        id="role-{{ $role->id }}"
                                        class="form-check-input role-checkbox"
                                        {{ $user->roles->contains('id', $role->id) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label text-muted"
                                        for="role-{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="form-group mb-0 row">
                            @can('user.update')
                            <div class="offset-3 col-9">
                                <button type="submit" class="btn btn-info waves-effect waves-light">{{ __('global.update') }}</button>
                            </div>
                            @endcan
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('backend/js/ajax-form-submit.js') }}"></script>
@endpush