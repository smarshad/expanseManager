@extends('layoutsnew.app')

@push('styles')
<link href="{{ asset('backend/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/libs/datatables/dataTables.bootstrap4.css') }}" rel="stylesheet">
<link href="{{ asset('backend/libs/datatables/responsive.bootstrap4.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Vendors</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card-box table-responsive">
                    <input type="hidden" id="listRoute" value="{{ route('vendor.list') }}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-0">All Vendors</h4>
                        <button class="btn btn-primary btn-sm openModel"
                            data-size="lg"
                            data-title="Create Vendor"
                            data-footer='<button type="submit" class="btn btn-primary js-submit-btn">Create</button> <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>'
                            data-url="{{ route('vendor.create') }}">+ Create New</button>
                    </div>
                    <x-alert />
                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Vendor</th>
                                <th>Default Category</th>
                                <th>Active</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('backend/libs/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/libs/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>
window.routes = {
    vendorEdit: @json(route('vendor.edit', ':id')),
    vendorDelete: @json(route('vendor.destroy', ':id'))
};
</script>
<script src="{{ asset('backend/js/manage-vendor.js') }}"></script>
<script src="{{ asset('backend/js/ajax-form-submit.js') }}"></script>
@endpush
