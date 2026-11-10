@php
    $isEdit = $category?->exists;
    $action = $isEdit ? route('category.update', $category) : route('category.store');
@endphp

<form id="categoryForm" action="{{ $action }}" method="POST" class="data-ajax-submit form-horizontal">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="parent_id">Parent Category</label>
        <div class="col-md-8">
            <select id="parent_id" name="parent_id" class="form-control">
                <option value="">Main Category</option>
                @foreach(($parentCategories ?? collect()) as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id ?? null) == $parent->id)>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Example: Groceries as parent, Vegetables as child.</small>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="name">Name</label>
        <div class="col-md-8">
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" placeholder="Vegetables, Petrol, Medicines">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="color_code">Color Code</label>
        <div class="col-md-8">
            <input type="text" id="color_code" name="color_code" class="form-control" maxlength="7" value="{{ old('color_code', $category->color_code ?? '') }}" placeholder="#28a745">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="nature">Nature</label>
        <div class="col-md-8">
            <select id="nature" name="nature" class="form-control">
                @foreach(['need' => 'Need', 'want' => 'Want', 'investment' => 'Investment'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('nature', $category->nature ?? 'need') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="budget_type">Budget Type</label>
        <div class="col-md-8">
            <select id="budget_type" name="budget_type" class="form-control">
                @foreach(['no_budget' => 'No Budget', 'soft_budget' => 'Soft Budget', 'hard_budget' => 'Hard Budget', 'fixed' => 'Fixed'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('budget_type', $category->budget_type ?? 'soft_budget') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="monthly_budget">Monthly Budget</label>
        <div class="col-md-8">
            <input type="number" step="0.01" min="0" id="monthly_budget" name="monthly_budget" class="form-control" value="{{ old('monthly_budget', $category->monthly_budget ?? '') }}" placeholder="Optional">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="sort_order">Sort Order</label>
        <div class="col-md-8">
            <input type="number" min="0" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="description">Description</label>
        <div class="col-md-8">
            <textarea id="description" name="description" class="form-control" placeholder="description">{{ old('description', $category->description ?? '') }}</textarea>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="is_active">Is Active</label>
        <div class="col-md-8 pt-10">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_active">&nbsp;</label>
            </div>
        </div>
    </div>
</form>
