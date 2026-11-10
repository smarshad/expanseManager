@php
    $isEdit = $expensetype?->exists;
    $action = $isEdit ? route('expensetype.update', $expensetype) : route('expensetype.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form id="expensetypeForm" action="{{ $action }}" method="{{$method}}" class="data-ajax-submit form-horizontal">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="name">Name</label>
        <div class="col-md-8">
            <input type="text" id="name" name="name" class="form-control" value="{{ $expensetype && $expensetype->exists ? $expensetype->name : '' }}" placeholder="Lunch, Dinner">
        </div>
    </div>
   
    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="description">Description</label>
        <div class="col-md-8">
            <textarea id="description" name="description" class="form-control" placeholder="description">{{ isset($expensetype) ? $expensetype->description : '' }}</textarea>
        </div>
    </div>
</form>