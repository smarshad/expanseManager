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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('expense.title')}}</a></li>
                            <li class="breadcrumb-item active">All</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{__('expense.title')}}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card-box table-responsive">
                    <input type="hidden" id="listRoute" value="{{route('expense.list')}}">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h4 class="header-title mb-2">{{ __('expense.all') }}</h4>
                        <div class="btn-group btn-group-sm mb-2" role="group" aria-label="Expense actions">
                            @can('expense.create')
                            <a class="btn btn-outline-success" href="{{ route('expense.import.sample') }}">
                                <i class="fas fa-file-excel mr-1"></i> Download Sample
                            </a>
                            <button type="button" class="btn btn-outline-info" data-toggle="collapse" data-target="#expenseImportCard">
                                <i class="fas fa-upload mr-1"></i> Import Excel/CSV
                            </button>
                            <a class="btn btn-primary" href="{{ route('expense.create') }}">
                                + {{__('global.new')}}
                            </a>
                            @endcan
                        </div>
                    </div>
                    <x-alert />

                    @can('expense.create')
                    <div class="card border mb-3 collapse {{ session('import_result') || $errors->has('import_file') ? 'show' : '' }}" id="expenseImportCard">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <strong><i class="fas fa-file-import mr-1"></i> Import Expenses by Excel / CSV</strong>
                            <a href="{{ route('expense.import.sample') }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download mr-1"></i> Sample File
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('expense.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row align-items-end">
                                    <div class="col-lg-5 col-md-6 mb-2">
                                        <label class="small mb-1">Excel / CSV File</label>
                                        <input type="file" name="import_file" class="form-control-file" accept=".xlsx,.csv" required>
                                        <small class="text-muted d-block mt-1">Allowed: .xlsx, .csv. Required columns are already in sample file.</small>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <div class="custom-control custom-checkbox mb-1">
                                            <input type="checkbox" class="custom-control-input" id="create_vendors" name="create_vendors" value="1">
                                            <label class="custom-control-label" for="create_vendors">Create missing vendors</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="allow_duplicates" name="allow_duplicates" value="1">
                                            <label class="custom-control-label" for="allow_duplicates">Allow duplicate rows</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12 mb-2">
                                        <button type="submit" class="btn btn-success btn-sm mr-2">
                                            <i class="fas fa-upload mr-1"></i> Upload & Import
                                        </button>
                                        <a href="{{ route('expense.import.sample') }}" class="btn btn-outline-secondary btn-sm">
                                            Download Sample
                                        </a>
                                    </div>
                                </div>
                            </form>

                            @if(session('import_result'))
                                @php($importResult = session('import_result'))
                                <div class="mt-3 border-top pt-3">
                                    <div class="row text-center">
                                        <div class="col-md-3 col-6 mb-2"><div class="border rounded p-2"><div class="text-muted small">Total Rows</div><strong>{{ $importResult['total_rows'] ?? 0 }}</strong></div></div>
                                        <div class="col-md-3 col-6 mb-2"><div class="border rounded p-2"><div class="text-muted small">Valid</div><strong>{{ count($importResult['valid'] ?? []) }}</strong></div></div>
                                        <div class="col-md-3 col-6 mb-2"><div class="border rounded p-2"><div class="text-muted small">Duplicates</div><strong>{{ count($importResult['duplicates'] ?? []) }}</strong></div></div>
                                        <div class="col-md-3 col-6 mb-2"><div class="border rounded p-2"><div class="text-muted small">Errors</div><strong>{{ count($importResult['errors'] ?? []) }}</strong></div></div>
                                    </div>

                                    @if(!empty($importResult['errors']))
                                        <h6 class="text-danger mt-2">Rows to fix</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-2">
                                                <thead><tr><th style="width:90px;">Row</th><th>Error</th></tr></thead>
                                                <tbody>
                                                @foreach(array_slice($importResult['errors'], 0, 20) as $error)
                                                    <tr><td>{{ $error['row'] }}</td><td>{{ $error['errors'] }}</td></tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if(count($importResult['errors']) > 20)<small class="text-muted">Showing first 20 errors only.</small>@endif
                                    @endif

                                    @if(!empty($importResult['duplicates']))
                                        <h6 class="text-warning mt-2">Duplicate rows skipped</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead><tr><th style="width:90px;">Row</th><th>Description</th><th>Amount</th></tr></thead>
                                                <tbody>
                                                @foreach(array_slice($importResult['duplicates'], 0, 10) as $duplicate)
                                                    <tr><td>{{ $duplicate['row'] }}</td><td>{{ $duplicate['description'] }}</td><td>{{ number_format((float) $duplicate['amount'], 2) }}</td></tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @endcan

                    <div class="card border mb-3" id="expenseFilterCard">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <strong><i class="fas fa-filter mr-1"></i> Filter Expenses</strong>
                            <button type="button" class="btn btn-sm btn-link" data-toggle="collapse" data-target="#expenseFilters">Show / Hide</button>
                        </div>
                        <div id="expenseFilters" class="collapse show"><div class="card-body pb-2">
                            <div class="row">
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">From Date</label><input type="date" id="filter_date_from" class="form-control form-control-sm expense-filter"></div>
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">To Date</label><input type="date" id="filter_date_to" class="form-control form-control-sm expense-filter"></div>
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">Category</label><select id="filter_category_id" class="form-control form-control-sm expense-filter"><option value="">All Categories</option>
                                    @foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->full_name }}</option>@endforeach
                                </select></div>
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">Transaction Type</label><select id="filter_transaction_kind" class="form-control form-control-sm expense-filter"><option value="">All Types</option><option value="expense">Expense</option><option value="investment">Investment</option><option value="transfer">Transfer</option><option value="debt">Debt Repayment</option></select></div>
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">Nature</label><select id="filter_nature" class="form-control form-control-sm expense-filter"><option value="">All</option><option value="need">Need</option><option value="want">Want</option><option value="investment">Investment</option></select></div>
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">Frequency</label><select id="filter_frequency" class="form-control form-control-sm expense-filter"><option value="">All</option>
                                    @foreach(['daily','occasionally','weekly','monthly','quarterly','yearly'] as $frequency)<option value="{{ $frequency }}">{{ ucfirst($frequency) }}</option>@endforeach
                                </select></div>
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">Expense For</label><select id="filter_expense_for_id" class="form-control form-control-sm expense-filter"><option value="">All</option>
                                    @foreach($expenseFors as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                </select></div>
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">Payment Method</label><select id="filter_payment_method_id" class="form-control form-control-sm expense-filter"><option value="">All</option>
                                    @foreach($paymentMethods as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                </select></div>
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">Platform</label><select id="filter_payment_platform_id" class="form-control form-control-sm expense-filter"><option value="">All</option>
                                    @foreach($paymentPlatforms as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                </select></div>
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">Account</label><select id="filter_account_id" class="form-control form-control-sm expense-filter"><option value="">All</option>
                                    @foreach($accounts as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                </select></div>
                                <div class="col-lg-2 col-md-4 mb-2"><label class="small mb-1">Vendor / Payee</label><select id="filter_vendor_id" class="form-control form-control-sm expense-filter"><option value="">All</option>
                                    @foreach($vendors as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                </select></div>
                                <div class="col-lg-2 col-md-4 mb-2 d-flex align-items-end"><button type="button" id="applyExpenseFilters" class="btn btn-primary btn-sm mr-2"><i class="fas fa-search mr-1"></i>Apply</button><button type="button" id="resetExpenseFilters" class="btn btn-outline-secondary btn-sm">Reset</button></div>
                            </div>
                        </div></div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>Amount</th>
                                    <th>Category</th>
                                    <th>Expense For</th>
                                    <th>Payment Method</th>
                                    <th>Platform</th>
                                    <th>Account</th>
                                    <th>Vendor</th>
                                    <th>Cashback</th>
                                    <th>Description</th>
                                    <th>Notes</th>
                                    <th>File</th>
                                    <th>Expense Date</th>
                                    <th>Created At</th>
                                    <!-- <th>Created By</th> -->
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
        edit: "{{ route('expense.edit', ':id') }}",
        delete: "{{ route('expense.destroy', ':id') }}",
    };

    window.lang = {
        new: @json(__('global.new')),
        edit: @json(__('global.update')),
        close: @json(__('global.close')),
        title: @json(__('expense.title')),
    };
</script>
<script src="{{ asset('backend/js/manage-expense.js') }}"></script>
<script src="{{ asset('backend/js/ajax-form-submit.js') }}"></script>
@endpush