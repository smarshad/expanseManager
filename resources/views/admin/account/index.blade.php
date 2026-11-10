@extends('layoutsnew.app')
@push('styles')
<link href="{{ asset('backend/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/libs/datatables/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="content"><div class="container-fluid">
<div class="page-title-box"><h4 class="page-title">Accounts</h4></div>
<div class="card-box table-responsive">
<input type="hidden" id="listRoute" value="{{ route('account.list') }}">
<div class="d-flex justify-content-between mb-3"><h4 class="header-title">All Accounts</h4>
<button class="btn btn-primary btn-sm openModel" data-url="{{ route('account.create') }}" data-size="lg" data-title="Create Account" data-footer='<button class="btn btn-primary js-submit-btn">Create</button> <button class="btn btn-info" data-dismiss="modal">Close</button>'>+ Create New</button></div>
<x-alert />
<table id="datatable" class="table table-bordered" style="width:100%"><thead><tr>
<th>Sr No</th><th>Account</th><th>Bank</th><th>Type</th><th>Opening Balance</th><th>Methods</th><th>Platforms</th><th>Active</th><th>Action</th>
</tr></thead><tbody></tbody></table>
</div></div></div>
@endsection
@push('scripts')
<script src="{{ asset('backend/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('backend/libs/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/libs/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>window.masterConfig={edit:@json(route('account.edit',':id')),delete:@json(route('account.destroy',':id')),title:'Account',columns:[{data:'name'},{data:'bank_name',defaultContent:'-'},{data:'account_type'},{data:'opening_balance'},{data:'methods'},{data:'platforms',defaultContent:'-'},{data:'is_active',render:v=>v?'Yes':'No'}]};</script>
<script src="{{ asset('backend/js/manage-linked-master.js') }}"></script><script src="{{ asset('backend/js/ajax-form-submit.js') }}"></script>
@endpush
