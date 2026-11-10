<div class="row">
    <div class="col-md-12">
        <!-- Filter Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fas fa-filter"></i> Report Filters
                </h5>

                <div class="row">
                    <!-- Date Range -->
                    <div class="col-md-3 mb-3">
                        <label for="report-start-date" class="form-label font-weight-bold">Start Date</label>
                        <input type="date" id="report-start-date" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="report-end-date" class="form-label font-weight-bold">End Date</label>
                        <input type="date" id="report-end-date" class="form-control">
                    </div>

                    <!-- Category Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="report-category" class="form-label font-weight-bold">Category</label>
                        <select id="report-category" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Method Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="report-payment-method" class="form-label font-weight-bold">Payment Method</label>
                        <select id="report-payment-method" class="form-control">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods ?? [] as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Amount Range -->
                    <div class="col-md-3 mb-3">
                        <label for="report-min-amount" class="form-label font-weight-bold">Min Amount ($)</label>
                        <input type="number" id="report-min-amount" class="form-control" placeholder="0.00" step="0.01">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="report-max-amount" class="form-label font-weight-bold">Max Amount ($)</label>
                        <input type="number" id="report-max-amount" class="form-control" placeholder="999999.99" step="0.01">
                    </div>

                    <!-- Search -->
                    <div class="col-md-6 mb-3">
                        <label for="report-search" class="form-label font-weight-bold">Search Description</label>
                        <input type="text" id="report-search" class="form-control" placeholder="Search by description...">
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <button class="btn btn-primary" id="generate-report-btn" onclick="loadCustomReport()">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                        <button class="btn btn-secondary" id="reset-filter-btn" onclick="resetReportFilters()">
                            <i class="fas fa-redo"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4" id="summary-section" style="display: none;">
            <div class="col mb-3">
                <div class="card border-0 shadow-sm border-top border-primary">
                    <div class="card-body">
                        <h6 class="card-title text-muted small font-weight-bold text-uppercase">Total Expenses</h6>
                        <h3 class="text-primary font-weight-bold" id="report-total">Rs. 00</h3>
                        <small class="text-muted">Sum of all expenses</small>
                    </div>
                </div>
            </div>

            <div class="col mb-3">
                <div class="card border-0 shadow-sm border-top border-danger">
                    <div class="card-body">
                        <h6 class="card-title text-muted small font-weight-bold text-uppercase">Transaction Count</h6>
                        <h3 class="text-danger font-weight-bold" id="report-count">0</h3>
                        <small class="text-muted">Number of transactions</small>
                    </div>
                </div>
            </div>

            <div class="col mb-3">
                <div class="card border-0 shadow-sm border-top border-info">
                    <div class="card-body">
                        <h6 class="card-title text-muted small font-weight-bold text-uppercase">Average Expense</h6>
                        <h3 class="text-info font-weight-bold" id="report-average">Rs. 00</h3>
                        <small class="text-muted">Average per transaction</small>
                    </div>
                </div>
            </div>

            <div class="col mb-3">
                <div class="card border-0 shadow-sm border-top border-success">
                    <div class="card-body">
                        <h6 class="card-title text-muted small font-weight-bold text-uppercase">Highest Expense</h6>
                        <h3 class="text-success font-weight-bold" id="report-highest">Rs. 00</h3>
                        <small class="text-muted">Maximum amount</small>
                    </div>
                </div>
            </div>

            <div class="col mb-3">
                <div class="card border-0 shadow-sm border-top border-secondary">
                    <div class="card-body">
                        <h6 class="card-title text-muted small font-weight-bold text-uppercase">Cashback Amount</h6>
                        <h3 class="text-secondary font-weight-bold" id="cashback_amount">Rs. 00</h3>
                        <small class="text-muted">Cashback Amount</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table"></i> Report Details
                </h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary" id="export-csv-btn" onclick="exportReportToCSV()" title="Export as CSV">
                        <i class="fas fa-download"></i> CSV
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="export-pdf-btn" onclick="exportReportToPDF()" title="Export as PDF">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="export-excel-btn" onclick="exportReportToExcel()" title="Export as Excel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="report-table" class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Platform</th>
                                <th>Account</th>
                                <th>Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>Click "Generate Report" to view expense data</p>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9">
                                    <nav class="mt-3">
                                        <ul id="report-pagination" class="pagination justify-content-end"></ul>
                                    </nav>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="loader" id="report-loader">
    <div class="spinner"></div>
</div>

<style>
    .border-top {
        border-top: 3px solid !important;
    }

    .border-primary {
        border-top-color: #0052cc !important;
    }

    .border-danger {
        border-top-color: #dc3545 !important;
    }

    .border-info {
        border-top-color: #17a2b8 !important;
    }

    .border-success {
        border-top-color: #28a745 !important;
    }

    .card-header {
        background-color: #f9f9f9;
    }

    .btn-group-sm .btn {
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #0052cc;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>