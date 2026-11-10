@extends('layoutsnew.app')
@push('styles')
<link href="{{asset('backend/libs/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backend/libs/bootstrap-select/bootstrap-select.min.css')}}" rel="stylesheet" type="text/css" />
@endpush
@push('scripts')
<script src="{{asset('backend/libs/select2/select2.min.js')}}"></script>
@endpush

@php
// Determine if we are in edit mode. Assume $expense is passed for editing.
$isEdit = isset($expense) && $expense->id;
$formAction = $isEdit ? route('expense.update', $expense->id) : route('expense.store');
$pageTitle = $isEdit ? __('expense.edit_title') : __('expense.create_title');
$breadcrumbActive = $isEdit ? 'Edit' : 'New';
$buttonText = $isEdit ? __('global.update') : __('global.save');
$canSubmit = $isEdit ? Gate::allows('update', $expense) : Gate::allows('create', App\Models\Expense::class);
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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('expense.title')}}</a></li>
                            <li class="breadcrumb-item active">{{ $breadcrumbActive }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{__('expense.title')}}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-3">{{ $pageTitle }}</h4>
                        <a href="{{ route('expense.index') }}" class="btn btn-primary">
                            {{__('global.back')}}
                        </a>
                    </div>
                    <x-alert />

                    <form id="editRoleForm" action="{{ $formAction }}" method="POST" class="data-ajax-submit form-horizontal" @if($isEdit) enctype="multipart/form-data" @endif>
                        @csrf
                        @if($isEdit)
                        @method('PUT')
                        @endif

                        @if(!$canSubmit)
                            <p class="text-danger d-block mt-1">
                                You do not have permission to {{ $isEdit ? 'update' : 'create' }} this expense.
                            </p>
                        @endif
                        <div class="form-group row">
                            <label for="category_id" class="col-3 col-form-label">Select Category</label>
                            @if($categories->isNotEmpty())
                            <div class="col-3">
                                <select name="category_id" id="category_id" class="form-control" data-toggle="select2">
                                    <option value="">Select Category / Sub Category</option>
                                    @foreach($categories->whereNull('parent_id') as $parent)
                                        <option value="{{ $parent->id }}" @if(old('category_id', $expense->category_id ?? request('category_id')) == $parent->id) selected @endif>{{ $parent->name }}</option>
                                        @foreach($categories->where('parent_id', $parent->id) as $child)
                                            <option value="{{ $child->id }}" @if(old('category_id', $expense->category_id ?? request('category_id')) == $child->id) selected @endif>&nbsp;&nbsp;— {{ $child->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <label for="expense_for_id" class="col-3 col-form-label">Expense For</label>
                            @if($expenseFors->isNotEmpty())
                            <div class="col-3">
                                <select name="expense_for_id" id="expense_for_id" class="form-control" data-toggle="select2">
                                    <option value="">Expense For</option>
                                    @foreach($expenseFors as $expenseFor)
                                    <option value="{{$expenseFor->id}}" @if(old('expense_for_id', $expense->expense_for_id ?? null) == $expenseFor->id) selected @endif>{{$expenseFor->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                        <div class="form-group row">
                            <label for="payment_method_id" class="col-3 col-form-label">Payment Method</label>
                            <div class="col-3">
                                <select name="payment_method_id" id="payment_method_id" class="form-control" data-toggle="select2">
                                    <option value="">Select Payment Method</option>
                                    @foreach($paymentMethods as $paymentMethod)
                                        <option value="{{ $paymentMethod->id }}" @selected(old('payment_method_id', $expense->payment_method_id ?? null) == $paymentMethod->id)>{{ $paymentMethod->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="payment_platform_id" id="payment_platform_label" class="col-3 col-form-label">Payment Platform</label>
                            <div class="col-3" id="payment_platform_group">
                                <select name="payment_platform_id" id="payment_platform_id" class="form-control" data-toggle="select2"><option value="">Select Platform</option></select>
                                <small class="text-muted"><a href="{{ route('payment-platform.index') }}">Manage platforms</a></small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="account_id" class="col-3 col-form-label">Account</label>
                            <div class="col-3">
                                <select name="account_id" id="account_id" class="form-control" data-toggle="select2"><option value="">Select Account</option></select>
                                <small class="text-muted"><a href="{{ route('account.index') }}">Manage accounts</a></small>
                            </div>
                            <label class="col-md-3 col-form-label" for="expense_date">Expense Date</label>
                            <div class="col-md-3">
                                @php
                                    $defaultDate = date('Y-m-d');
                                    $expenseDateValue = old('expense_date', $expense->expense_date ?? $defaultDate);
                                    if ($expenseDateValue && !is_string($expenseDateValue)) {
                                        $expenseDateValue = $expenseDateValue->format('Y-m-d');
                                    } elseif ($expenseDateValue) {
                                        $expenseDateValue = date('Y-m-d', strtotime($expenseDateValue));
                                    }
                                @endphp
                                <input class="form-control" id="expense_date" name="expense_date" type="date" value="{{ $expenseDateValue }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="vendor_id" class="col-3 col-form-label">Vendor</label>
                            <div class="col-3">
                                <select name="vendor_id" id="vendor_id" class="form-control" data-toggle="select2">
                                    <option value="">Select Vendor (Optional)</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" @selected(old('vendor_id', $expense->vendor_id ?? null) == $vendor->id)>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted"><a href="{{ route('vendor.index') }}">Manage vendors</a></small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="amount" class="col-3 col-form-label">Amount</label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="amount" name="amount" value="{{old('amount', $expense->amount ?? '')}}" placeholder="Amount">
                            </div>
                            <label for="description" class="col-3 col-form-label">Description</label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="description" name="description" value="{{old('description', $expense->description ?? request('description', ''))}}" placeholder="Description">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="cashback" class="col-3 col-form-label">Cashback</label>
                            <div class="col-3">
                                <input type="number" step="0.01" class="form-control" id="cashback" name="cashback" value="{{old('cashback', $expense->cashback ?? '')}}" placeholder="0.00">
                            </div>
                            <label for="nature" class="col-3 col-form-label">Nature</label>
                            <div class="col-3">
                                <select name="nature" id="nature" class="form-control">
                                    @foreach(['need' => 'Need', 'want' => 'Want', 'investment' => 'Investment'] as $key => $label)
                                        <option value="{{ $key }}" @selected(old('nature', $expense->nature ?? 'need') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="notes" class="col-3 col-form-label">Notes</label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="notes" name="notes" value="{{old('notes', $expense->notes ?? '')}}" placeholder="Notes">
                            </div>
                            <label for="transaction_kind" class="col-3 col-form-label">Transaction Type</label>
                            <div class="col-3">
                                <select name="transaction_kind" id="transaction_kind" class="form-control">
                                    @foreach(['expense' => 'Expense', 'investment' => 'Investment', 'transfer' => 'Transfer', 'debt' => 'Debt Repayment'] as $key => $label)
                                        <option value="{{ $key }}" @selected(old('transaction_kind', $expense->transaction_kind ?? 'expense') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="is_recurring" class="col-3 col-form-label">Recurring?</label>
                            <div class="col-3 pt-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring', $expense->is_recurring ?? false) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_recurring">Yes</label>
                                </div>
                            </div>
                            <label for="frequency" class="col-3 col-form-label">Frequency</label>
                            <div class="col-3">
                                <select name="frequency" id="frequency" class="form-control">
                                    <option value="">Select</option>
                                    @foreach(['daily' => 'Daily', 'occasionally' => 'Occasionally',  'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly'] as $key => $label)
                                        <option value="{{ $key }}" @selected(old('frequency', $expense->frequency ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="uploaded_file" class="col-3 col-form-label">File Path</label>
                            <div class="col-3">
                                <input type="file" class="form-control" id="uploaded_file" name="uploaded_file">
                                @if($isEdit && $expense->uploaded_file)
                                <small class="form-text text-muted">Current file: <a href="{{ asset('path/to/your/files/' . $expense->uploaded_file) }}" target="_blank">{{ $expense->uploaded_file }}</a></small>
                                @endif
                            </div>

                            {{-- Existing file preview (edit mode) --}}
                            @if($isEdit && $expense->file_path)
                            <label class="col-3 col-form-label">Old File</label>
                            <div class="col-3">
                                @php
                                // Build public URL for file on 'public' disk. Adjust if you use a different disk.
                                $url = \Illuminate\Support\Facades\Storage::disk('public')->url($expense->file_path);
                                $ext = strtolower(pathinfo($expense->file_path, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg','jpeg','png']);
                                $isPdf = $ext === 'pdf';
                                @endphp
                                @if($isImage)
                                <div class="mt-1">
                                    <a href="{{ $url }}" target="_blank" rel="noopener">
                                        <img src="{{ $url }}"
                                            alt="Current Image"
                                            class="img-thumbnail"
                                            style="max-width:180px; max-height:180px;">
                                    </a>
                                </div>

                                {{-- PDF PREVIEW --}}
                                @elseif($isPdf)
                                <div class="mt-2" style="max-width:300px; max-height:300px; overflow:hidden; border:1px solid #ddd;">
                                    <object data="{{ $url }}" type="application/pdf" width="100%" height="250">
                                        <p>
                                            PDF preview unavailable.
                                            <a href="{{ $url }}" target="_blank">Click to view PDF</a>
                                        </p>
                                    </object>
                                </div>
                                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                    View PDF
                                </a>

                                {{-- OTHER FILE TYPES (VIEW ONLY) --}}
                                @else
                                <p class="mb-1">File: <a href="{{ $url }}" target="_blank">{{ basename($expense->file_path) }}</a></p>
                                @endif
                            </div>
                            @endif

                        </div>
                       
                        <div class="form-group mb-0 row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-info waves-effect waves-light" @disabled(!$canSubmit)>{{ $buttonText }}</button>
                                <button type="reset" class="btn btn-danger waves-effect waves-light">{{ __('global.reset') }}</button>
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
<script src="{{ asset('backend/js/ajax-form-submit.js') }}"></script>
<script>
window.expensePaymentOptions = @json($paymentOptions);
window.expensePaymentOptions.selectedAccount = @json((int) old('account_id', $expense->account_id ?? 0));
window.expensePaymentOptions.selectedPlatform = @json((int) old('payment_platform_id', $expense->payment_platform_id ?? 0));
</script>

<script src="{{ asset('backend/js/expense.js') }}"></script>
@endpush