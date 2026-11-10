@php
$isEdit = $paymentmethod?->exists;
$action = $isEdit ? route('paymentmethod.update', $paymentmethod) : route('paymentmethod.store');
$method = $isEdit ? 'PUT' : 'POST';
@endphp

<form id="paymentmethodForm" action="{{ $action }}" method="POST" class="data-ajax-submit form-horizontal">
    @csrf
    @if($isEdit)
    @method('PUT')
    @endif
    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="name">Name</label>
        <div class="col-md-8">
            <input type="text" id="name" name="name" class="form-control" value="{{ $paymentmethod && $paymentmethod->exists ? $paymentmethod->name : '' }}" placeholder="Lunch, Dinner">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="code">Code</label>
        <div class="col-md-8">
            <input type="text" id="code" name="code" class="form-control" placeholder="code" value="{{ isset($paymentmethod) ? $paymentmethod->code : '' }}">
        </div>
    </div>
</form>