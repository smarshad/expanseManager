@php
    $isEdit = $vendor?->exists;
    $action = $isEdit ? route('vendor.update', $vendor) : route('vendor.store');
@endphp

<form action="{{ $action }}" method="POST" class="data-ajax-submit form-horizontal">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="name">Vendor Name</label>
        <div class="col-md-8">
            <input type="text" id="name" name="name" class="form-control"
                value="{{ old('name', $vendor->name ?? '') }}"
                placeholder="DMart, Amazon, Local Grocery Shop">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="category_id">Default Category</label>
        <div class="col-md-8">
            <select id="category_id" name="category_id" class="form-control">
                <option value="">No default category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $vendor->category_id ?? null) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="is_active">Active</label>
        <div class="col-md-8 pt-2">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                    {{ old('is_active', $vendor->is_active ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_active">Available in expense form</label>
            </div>
        </div>
    </div>
</form>
