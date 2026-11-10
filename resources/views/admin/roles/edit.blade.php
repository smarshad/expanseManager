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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('roles.title')}}</a></li>
                            <li class="breadcrumb-item active">New</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{__('roles.title')}}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-3">{{ __('roles.edit_title')}}</h4>
                        <a href="{{ route('roles.index') }}" class="btn btn-primary">
                            {{__('global.back')}}
                        </a>
                    </div>
                    <x-alert />

                    {{-- <form class="form-horizontal" method="POST" action="{{route('roles.update')}}"> --}}
                    <form id="editRoleForm" action="{{ route('roles.update', $role) }}" method="POST" class="data-ajax-submit">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="name" class="col-3 col-form-label">Name</label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="name" name="name" value="{{old('name', $role->name)}}" placeholder="create user">
                            </div>
                            <label for="description" class="col-3 col-form-label">Description</label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="description" name="description" value="{{old('description', $role->description)}}" placeholder="create new user">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div id="permissions-section" class="permissions-section col-12">
                                <div class="mb-3 col-12">
                                    <strong>Permissions:</strong>
                                    <br />
                                    @foreach($permissionsGrouped as $module => $permissions)
                                    <div class="">
                                        {{-- Module Checkbox --}}
                                        <hr />
                                        <div class="form-check mb-2">
                                            <input type="checkbox"
                                                id="module-{{ \Illuminate\Support\Str::slug($module) }}"
                                                class="form-check-input module-checkbox"
                                                data-module="{{ \Illuminate\Support\Str::slug($module) }}">

                                            <label class="form-check-label font-weight-bold text-dark"
                                                for="module-{{ \Illuminate\Support\Str::slug($module) }}">
                                                {{ ucfirst($module) }}
                                            </label>
                                        </div>

                                        {{-- Permissions --}}
                                        <div class="row ml-3">
                                            @foreach($permissions as $permission)
                                            <div class="col-9 col-md-2 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $permission->id }}"
                                                        id="permission-{{ $permission->id }}"
                                                        class="form-check-input permission-checkbox"
                                                        data-module="{{ \Illuminate\Support\Str::slug($module) }}"
                                                        {{ isset($rolePermissions) && in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-muted"
                                                        for="permission-{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0 row">
                            <div class="offset-3 col-9">
                                <button type="submit" class="btn btn-info waves-effect waves-light">{{ __('global.update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('backend/js/permission-checkbox.js') }}"></script>
<script src="{{ asset('backend/js/ajax-form-submit.js') }}"></script>
@endpush