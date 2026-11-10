@extends('layoutsnew.app')
<!-- Begin page -->

@section('content')
<div class="content">

    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Adminox</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Dashboard 2</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Dashboard 2</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="card-body">
                <form id="backupForm">
                    @csrf

                    {{-- Tables --}}
                    <div class="mb-3">
                        <label class="form-label">Tables <span class="text-danger">*</span></label>
                        <select name="tables[]" multiple class="form-control" required>
                            @foreach($tables as $table)
                            <option value="{{ $table }}">{{ $table }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl / Cmd to select multiple</small>
                    </div>

                    {{-- Date range --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to" class="form-control">
                        </div>
                    </div>

                    {{-- Created By --}}
                    <div class="mb-3">
                        <label class="form-label">Created By (User ID)</label>
                        <input type="number" name="created_by" class="form-control" placeholder="Optional">
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger" id="backupBtn">
                            Generate & Download Backup
                        </button>

                        <span id="backupStatus" class="text-muted align-self-center"></span>
                    </div>
                </form>
            </div>

        </div>
        <!-- end row -->

    </div> <!-- end container-fluid -->

</div> <!-- end content -->
@endsection
@push('scripts')
<script>
document.getElementById('backupForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = e.target;
    const btn = document.getElementById('backupBtn');
    const status = document.getElementById('backupStatus');

    btn.disabled = true;
    status.textContent = 'Generating backup... please wait';

    fetch('{{ route("db.backup.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
            'Accept': 'application/json'
        },
        body: new FormData(form)
    })
    .then(res => res.json())
    .then(data => {
        if (data.download_url) {
            status.textContent = 'Download started';
            window.location.href = data.download_url;
        } else {
            status.textContent = 'Backup queued';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Backup failed');
        status.textContent = '';
    })
    .finally(() => {
        btn.disabled = false;
    });
});
</script>

@endpush
