@extends('layoutsnew.app')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <a href="{{ route('category.index') }}" class="btn btn-light btn-sm">&larr; Categories</a>
                    </div>
                    <h4 class="page-title">Category Classification Mapping</h4>
                </div>
            </div>
        </div>

        <div class="card-box">
            <p class="text-muted mb-2">
                Set how each category is analysed. <b>Inherit</b> uses the parent category's value (then the default).
                Changes save instantly and apply to new expenses; to update old expenses run
                <code>php artisan expenses:classify --apply</code>.
            </p>
            <input type="text" id="catFilter" class="form-control form-control-sm mb-2" placeholder="Search category..." style="max-width:300px">
            <span id="saveStatus" class="small text-muted"></span>

            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="mapTable">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Expenses</th>
                            <th>Kind</th>
                            <th>Spending nature</th>
                            <th>Recurrence</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $row)
                        @php($c = $row['category'])
                        <tr data-id="{{ $c->id }}" data-url="{{ route('category.classification.update', $c) }}" data-name="{{ strtolower($c->full_name) }}">
                            <td>{{ $c->parent ? '— ' : '' }}<b>{{ $c->parent ? $c->name : $c->name }}</b>
                                @if($c->parent)<small class="text-muted">({{ $c->parent->name }})</small>@endif
                            </td>
                            <td>{{ $c->expenses_count }}</td>
                            @foreach([
                                'transaction_kind' => \App\Services\ExpenseClassificationService::KINDS,
                                'spending_nature' => \App\Services\ExpenseClassificationService::NATURES,
                                'recurrence' => \App\Services\ExpenseClassificationService::RECURRENCES,
                            ] as $field => $options)
                            <td>
                                <select class="form-control form-control-sm map-select" data-field="{{ $field }}">
                                    <option value="" data-inherit="1">Inherit ({{ $row['inherited'][$field] }})</option>
                                    @foreach($options as $opt)
                                        <option value="{{ $opt }}" @selected($c->$field === $opt)>{{ ucfirst($opt) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var status = document.getElementById('saveStatus');

    document.querySelectorAll('.map-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var tr = sel.closest('tr'), body = {};
            body[sel.dataset.field] = sel.value;
            status.textContent = 'Saving...';
            fetch(tr.dataset.url, {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token},
                body: JSON.stringify(body)
            }).then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                status.textContent = 'Saved ✓';
            }).catch(function (e) {
                status.textContent = 'Save failed: ' + e.message;
            });
        });
    });

    document.getElementById('catFilter').addEventListener('input', function (e) {
        var q = e.target.value.toLowerCase();
        document.querySelectorAll('#mapTable tbody tr').forEach(function (tr) {
            tr.style.display = tr.dataset.name.indexOf(q) === -1 ? 'none' : '';
        });
    });
})();
</script>
@endpush
