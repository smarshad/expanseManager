@extends('layoutsnew.app')

@push('styles')
<link href="{{asset('backend/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backend/libs/datatables/dataTables.bootstrap4.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backend/libs/datatables/buttons.bootstrap4.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backend/libs/datatables/responsive.bootstrap4.css')}}" rel="stylesheet" type="text/css" />

@endpush

@push('scripts')
<script src="{{asset('backend/libs/sweetalert2/sweetalert2.min.js')}}"></script>
<script src="{{asset('backend/js/pages/sweet-alerts.init.js')}}"></script>

<script src="{{asset('backend/libs/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/libs/datatables/dataTables.bootstrap4.min.js')}}"></script>
@endpush

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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('expensetype.title')}}</a></li>
                            <li class="breadcrumb-item active">All</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{__('expensetype.title')}}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card-box table-responsive">
                    <input type="hidden" id="listRoute" value="{{route('expensetype.list')}}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-3">{{ __('expensetype.all') }}</h4>
                        @can('expensetype.create')
                        <button
                            class="btn btn-primary btn-sm openModel"
                            data-size="lg"
                            data-title="{{__('global.new')}} {{__('expensetype.title')}}"
                            data-footer='<button type="submit" class="btn btn-primary waves-effect waves-light js-submit-btn">{{ __("global.new") }}</button> <button type="button" class="btn btn-info waves-effect waves-light" data-dismiss="modal">{{ __("global.close") }}</button>'
                            data-url="{{ route('expensetype.create') }}">
                            + {{__('global.new')}}
                        </button>
                        @endcan
                    </div>
                    <x-alert />

                    <div class="table-responsive">
                        <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>Expense type</th>
                                    <th>Description</th>
                                    <th>Created At</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                </tr>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    window.routes = {
        edit: "{{ route('expensetype.edit', ':id') }}",
        delete: "{{ route('expensetype.destroy', ':id') }}",
    };

    window.lang = {
        new: @json(__('global.new')),
        edit: @json(__('global.update')),
        close: @json(__('global.close')),
        title: @json(__('expensetype.title')),
    };
</script>
<script src="{{ asset('backend/js/manage-expensetype.js') }}"></script>
<script src="{{ asset('backend/js/ajax-form-submit.js') }}"></script>
@endpush