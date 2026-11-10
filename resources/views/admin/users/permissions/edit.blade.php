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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('users.permission')}}</a></li>
                            <li class="breadcrumb-item active">New</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{__('users.permission')}}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-3">{{ __('users.manage_permission')}}</h4>
                        <a href="{{ route('users.index') }}" class="btn btn-primary">
                            {{__('global.back')}}
                        </a>
                    </div>
                    @if($user->hasRole('Super Admin'))
                    <h4 class="text-bold">This user has Super Admin so no need to add <b>Permission</b></h4>
                    @else
                    <x-alert />
                    <h4>Manage Direct Permissions for User</h4>
                    <p><b>User Full Name:</b> {{ $user->name }}</p>
                    <p><b>User Id:</b> {{ $user->id }}</p>
                    <p><b>Email:</b> {{ $user->email }}</p>
                    <p><b>Role:</b> {{ $user->roles->pluck('name')->implode(', ') }}</p>
                    <p><b>Last Login:</b> {{ $user->last_login_at }}</p>
                    <form id="editRoleForm" method="POST" action="{{ route('users.permissions.update', $user) }}" class="data-ajax-submit form-horizontal">
                        @csrf
                        @method('PUT') {{-- Use PUT method for updates --}}

                        <div class="card">
                            <div class="card-header">
                                Direct Permissions
                            </div>
                            <div class="card-body">
                                <div id="permissions-section" class="form-group row permissions-section">
                                    <div class="mb-3 col-12">
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
                                                @php
                                                    $hasDirect = in_array($permission->id, $userDirectPermissions ?? []);
                                                    $hasViaRole = in_array($permission->id, $userRolePermissions ?? []);
                                                @endphp
                                                <div class="col-md-2 mb-2">
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                            name="permissions[]"
                                                            value="{{ $permission->id }}"
                                                            id="permission-{{ $permission->id }}"
                                                            class="form-check-input permission-checkbox"
                                                            data-module="{{ \Illuminate\Support\Str::slug($module) }}"
                                                            {{ ($hasDirect || $hasViaRole) ? 'checked' : '' }}>
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
                        </div>

                        <div class="mt-4 text-center">
                            @can('user.editPermission')
                            <button type="submit" class="btn btn-primary">
                                {{ __('users.update_permission') }}
                            </button>
                            @endcan
                            <a href="{{ route('users.list') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                    @endif
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