@extends('layoutsnew.app')
@push('styles')
<link href="{{asset('backend/libs/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backend/libs/bootstrap-select/bootstrap-select.min.css')}}" rel="stylesheet" type="text/css" />
@endpush
@push('scripts')
<script src="{{asset('backend/libs/select2/select2.min.js')}}"></script>
@endpush

@php
// Determine if we are in edit mode. Assume $menu is passed for editing.
$isEdit = isset($menu) && $menu->id;
$formAction = $isEdit  ? route('menu.update', $menu) : route('menu.store');
$pageTitle = $isEdit ? __('menu.edit_title') : __('menu.create_title');
$breadcrumbActive = $isEdit ? 'Edit' : 'New';
$buttonText = $isEdit ? __('global.update') : __('global.save');
$parent_id = $menu && $menu->exists ? $menu->parent_id : NULL
@endphp

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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('menu.title')}}</a></li>
                            <li class="breadcrumb-item active">{{ $breadcrumbActive }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{__('menu.title')}}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-3">{{ $pageTitle }}</h4>
                        <a href="{{ route('menu.index') }}" class="btn btn-primary">
                            {{__('global.back')}}
                        </a>
                    </div>
                    <x-alert />
                    <form id="editRoleForm" action="{{$formAction}}" method="POST" class="data-ajax-submit form-horizontal">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif
                        <div class="row mb-3">
                            <label for="name" class="col-3 col-form-label">Menu Name <span class="text-danger">*</span></label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="name" name="name" value="{{ $menu && $menu->exists ? $menu->name : '' }}" placeholder="«Name»">
                            </div>
                            <label for="parent_id" class="col-3 col-form-label">Parent Menu</label>
                            <div class="col-3">
                                <select name="parent_id" id="parent_id" class="form-control">
                                    {{-- Optional: Add a default, unselectable option --}}
                                    <option value="">-- Parent Menu --</option>

                                    @foreach($parentMenus as $option)
                                    <option value="{{ $option['id'] }}" @if($parent_id == $option['id']) selected @endif>
                                        {{ $option['name'] }}
                                    </option>
                                    @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="route" class="col-3 col-form-label">Laravel Route Name </label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="route" name="route" placeholder="route" value="{{ $menu && $menu->exists ? $menu->route : '' }}">
                                <div class="form-text">e.g., <code>menu.index</code></div>
                            </div>
                            <label for="url" class="col-3 col-form-label">External URL (Fallback)</label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="url" name="url" placeholder="Url" value="{{ $menu && $menu->exists ? $menu->url : '' }}">
                                <div class="form-text">e.g., <code>https://example.com</code></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="icon" class="col-3 col-form-label">Icon (Font Awesome Class)</label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="icon" name="icon" placeholder="icon" value="{{ $menu && $menu->exists ? $menu->icon : '' }}">
                                <div class="form-text">e.g., <code>fa-solid fa-house</code></div>
                            </div>
                            <label for="permission_id" class="col-3 col-form-label">Required Permission</label>
                            <div class="col-3">
                                <select class="form-control" id="permission_id" name="permission_id">
                                    <option value="">-- No Permission Required --</option>
                                    @foreach ($permissions as $permission)
                                    <option value="{{ $permission->id }}" {{ $menu->permission_id == $permission->id ? 'selected' : '' }}>
                                        {{ $permission->name }} ({{ $permission->module ?? 'N/A' }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="order" class="col-3 col-form-label">Order</label>
                            <div class="col-3">
                                <input type="number" class="form-control" id="order" name="order" placeholder="order" value="{{ $menu && $menu->exists ? $menu->order : '' }}">
                            </div>
                            <label class="col-md-3 col-form-label" for="is_active">Is Active</label>
                            <div class="col-md-3 pt-10">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $menu->is_active == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">&nbsp;</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">{{$buttonText}} {{__('menu.title')}}</button>
                                <button type="reset" class="btn btn-danger waves-effect waves-light">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('backend/js/ajax-form-submit.js') }}"></script>
@endpush