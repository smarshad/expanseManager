@extends('layoutsnew.app')

@section('title', 'Reports')

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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('category.title')}}</a></li>
                            <li class="breadcrumb-item active">All</li>
                        </ol>
                    </div>
                    <h4 class="page-title"><i class="fas fa-file-alt"></i> Reports</h4>
                </div>
            </div>
        </div>


        <!-- Filters Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter"></i> Report Filters
                </h5>
            </div>
            <div class="card-body">
                <form id="reportFiltersForm">
                    <div class="row g-3">
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" id="startDate" class="form-control"
                                value="{{ now()->subMonth()->toDateString() }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" id="endDate" class="form-control"
                                value="{{ now()->toDateString() }}">
                        </div>

                        <!-- Category Filter -->
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select id="categoryFilter" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->display_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment Method Filter -->
                        <div class="col-md-3">
                            <label class="form-label">Payment Method</label>
                            <select id="paymentMethodFilter" class="form-control">
                                <option value="">All Methods</option>
                                @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <!-- Expense For Filter -->
                        <div class="col-md-3">
                            <label class="form-label">Expense For</label>
                            <select id="expenseForFilter" class="form-control">
                                <option value="">All Expense For</option>
                                @foreach($expenseFors as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Amount Range -->
                        <div class="col-md-3">
                            <label class="form-label">Min Amount</label>
                            <input type="number" id="minAmount" class="form-control"
                                placeholder="0.00" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Amount</label>
                            <input type="number" id="maxAmount" class="form-control"
                                placeholder="9999.99" step="0.01">
                        </div>

                        <!-- Search -->
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" id="searchQuery" class="form-control"
                                placeholder="Search description...">
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="clearFiltersBtn">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row" id="spendingInsights">
            <div class="col-md-3"><div class="card-box"><h6 class="text-muted">Actual expense</h6><h3 id="insightExpenses">Rs. 0.00</h3><small id="insightComparison" class="text-muted">Compared with previous period</small></div></div>
            <div class="col-md-3"><div class="card-box"><h6 class="text-muted">Essential / Need</h6><h3 id="insightEssential">Rs. 0.00</h3><small class="text-muted">Necessary spending</small></div></div>
            <div class="col-md-3"><div class="card-box"><h6 class="text-muted">Potential saving</h6><h3 id="insightPotentialSaving">Rs. 0.00</h3><small class="text-muted">Discretionary / Want</small></div></div>
            <div class="col-md-3"><div class="card-box"><h6 class="text-muted">Investments</h6><h3 id="insightInvestments">Rs. 0.00</h3><small id="insightReview" class="text-warning">Review unclassified: Rs. 0.00</small></div></div>
        </div>

        <!-- Report Tabs -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card-box">

                    <ul class="nav nav-tabs tabs-bordered">
                        <li class="nav-item">
                            <a href="#home-b1" data-toggle="tab" aria-expanded="true" class="nav-link active">
                                <span class="d-block d-sm-none"><i class="fas fa-list"></i></span>
                                <span class="d-none d-sm-block"> <i class="fas fa-list"></i> Detailed Report</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#categoryTab" data-toggle="tab" aria-expanded="false" class="nav-link">
                                <span class="d-block d-sm-none"><i class="fas fa-folder"></i></span>
                                <span class="d-none d-sm-block"><i class="fas fa-folder"></i> By Category</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#paymentTab" data-toggle="tab" aria-expanded="false" class="nav-link">
                                <span class="d-block d-sm-none"><i class="fas fa-credit-card"></i></span>
                                <span class="d-none d-sm-block"><i class="fas fa-credit-card"></i> By Payment Method</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#monthlyTab" data-toggle="tab" aria-expanded="false" class="nav-link">
                                <span class="d-block d-sm-none"><i class="fas fa-calendar"></i></span>
                                <span class="d-none d-sm-block"><i class="fas fa-calendar"></i> Monthly Summary</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#custom-report" data-toggle="tab" aria-expanded="false" class="nav-link">
                                <span class="d-block d-sm-none"><i class="fas fa-calendar"></i></span>
                                <span class="d-none d-sm-block"><i class="fas fa-calendar"></i> Custom Report</span>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane show active" id="home-b1">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Detailed Expense Report <span id="noOfDays"></span></h5>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success" id="exportDetailedCsv">
                                            <i class="fas fa-download"></i> CSV
                                        </button>
                                        <button class="btn btn-outline-danger" id="exportDetailedPdf">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </button>
                                        <button class="btn btn-outline-info" id="exportDetailedExcel">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="detailedTable" class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Description</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Category</th>
                                                <th>Type</th>
                                                <th>Payment Method</th>
                                                <th>Platform</th>
                                                <th>Account</th>
                                                <th>Cashback</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detailedTableBody">
                                            <tr>
                                                <td colspan="10" class="text-center text-muted">
                                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Summary -->
                                <div class="row mt-4">
                                    <div class="col-md-3">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Total Expenses</h6>
                                            <h3 id="detailedTotal">RS. 0.00</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Count</h6>
                                            <h3 id="detailedCount">0</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Average</h6>
                                            <h3 id="detailedAverage">RS. 0.00</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Highest</h6>
                                            <h3 id="detailedHighest">RS. 0.00</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="categoryTab">
                            <div class="card-body">
                                <h5 class="mb-3">Expenses by Category</h5>
                                <div class="table-responsive">
                                    <table id="categoryTable" class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Category</th>
                                                <th>Count</th>
                                                <th>Total Amount</th>
                                                <th>Average</th>
                                                <th>Highest</th>
                                                <th>Lowest</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody id="categoryTableBody">
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Total Amount</h6>
                                            <h3 id="categoryTotal">RS. 0.00</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Categories</h6>
                                            <h3 id="categoryCount">0</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="paymentTab">
                            <div class="card-body">
                                <h5 class="mb-3">Expenses by Payment Method</h5>
                                <div class="table-responsive">
                                    <table id="paymentTable" class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Payment Method</th>
                                                <th>Type</th>
                                                <th>Count</th>
                                                <th>Total Amount</th>
                                                <th>Average</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody id="paymentTableBody">
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Total Amount</h6>
                                            <h3 id="paymentTotal">RS. 0.00</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Methods</h6>
                                            <h3 id="paymentCount">0</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="monthlyTab">
                            <div class="card-body">
                                <h5 class="mb-3">Monthly Summary</h5>
                                <div class="table-responsive">
                                    <table id="monthlyTable" class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Month</th>
                                                <th>Count</th>
                                                <th>Total Amount</th>
                                                <th>Average</th>
                                                <th>Highest</th>
                                                <th>Lowest</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody id="monthlyTableBody">
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Total Amount</h6>
                                            <h3 id="monthlyTotal">RS. 0.00</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Highest Month</h6>
                                            <h3 id="highestMonth">-</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="summary-box">
                                            <h6 class="text-muted">Lowest Month</h6>
                                            <h3 id="lowestMonth">-</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="custom-report" role="tabpanel">
                            @include('admin.reports.partials.custom-report')
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->

        </div>

    </div>

    @endsection

    @push('styles')
    <style>
        .reports-container {
            padding: 20px 0;
        }

        .summary-box {
            padding: 20px;
            background-color: #F2F2F2;
            border-radius: 8px;
            text-align: center;
        }

        .summary-box h3 {
            color: #0052CC;
            font-weight: 700;
            margin-bottom: 0;
        }

        .summary-box h6 {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .card-header {
            background-color: #F2F2F2;
            border-bottom: 1px solid #E0E0E0;
            padding: 15px 20px;
        }

        .nav-tabs .nav-link {
            color: #666;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 600;
        }

        .nav-tabs .nav-link.active {
            color: #0052CC;
            border-bottom-color: #0052CC;
            background-color: transparent;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #F2F2F2;
            border-bottom: 2px solid #E0E0E0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 82, 204, 0.05);
        }

        .btn-group-sm .btn {
            padding: 6px 12px;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .summary-box {
                margin-bottom: 15px;
            }

            .table {
                font-size: 12px;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script src="{{asset('backend/js/common-crud.js')}}"></script>

    <script>
        let currentFilters = {};

        async function exportReportToCSV(){

        }

        async function loadCustomReport(page = 1) {
            try {
                currentPage = page;
                showLoadingState();

                const filters = {
                    start_date: document.getElementById('report-start-date')?.value || '',
                    end_date: document.getElementById('report-end-date')?.value || '',
                    category_id: document.getElementById('report-category')?.value || '',
                    payment_method_id: document.getElementById('report-payment-method')?.value || '',
                    min_amount: document.getElementById('report-min-amount')?.value || '',
                    max_amount: document.getElementById('report-max-amount')?.value || '',
                    search: document.getElementById('report-search')?.value || '',
                    page: page,
                    per_page: 15
                };

                const params = new URLSearchParams();
                Object.entries(filters).forEach(([k, v]) => {
                    if (v !== '') params.append(k, v);
                });

                const response = await fetch(`/reports/custom?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });

                const data = await response.json();

                if (!data.success) throw new Error(data.message);

                renderCustomReportTable(data.data.expenses || []);
                updateReportSummary(data.data.summary || {});
                renderPagination(data.data.pagination);
                $('#summary-section').show();

            } catch (err) {
                console.error(err);
            } finally {
                hideLoadingState();
            }
        }

        function changePage(page) {
            if (page < 1) return;
            loadCustomReport(page);
        }
        /**
         * Render Custom Report Table
         */
        function renderCustomReportTable(expenses) {
            const tbody = document.querySelector('#report-table tbody');
            if (!tbody) return;

            tbody.innerHTML = '';

            if (!expenses || expenses.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No expenses found matching your criteria</p>
                    </td>
                </tr>
            `;
                return;
            }

            expenses.forEach(expense => {
                const row = document.createElement('tr');
                let formattedDate = new Date(expense.expense_date).toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
                row.innerHTML = `
                <td>${formattedDate}</td>
                <td>
                    <span class="badge badge-info">${expense.category?.name || '-'}</span>
                </td>
                <td>${expense.expense_for?.name || '-'}</td>
                <td class="font-weight-bold text-primary">Rs. ${parseFloat(expense.amount).toFixed(2)}</td>
                <td>
                    <span class="badge badge-secondary">${expense.payment_method?.name || '-'}</span>
                </td>
                <td>${expense.payment_platform?.name || '-'}</td>
                <td>${expense.account?.name || '-'}</td>
                <td>
                    <small>${expense.description || '-'}</small>
                </td>
                <td>
                    <span class="badge badge-${expense.status === 'approved' ? 'success' : expense.status === 'pending' ? 'warning' : 'danger'}">
                        ${expense.status || 'N/A'}
                    </span>
                </td>
            `;
                tbody.appendChild(row);
            });

            console.log(`Rendered ${expenses.length} expense rows`);
        }

        function renderPagination(pagination) {
            const container = document.getElementById('report-pagination');
            if (!container || !pagination) return;

            container.innerHTML = '';

            const {
                current_page,
                last_page
            } = pagination;
            if (last_page <= 1) return;

            // Prev button
            container.innerHTML += `
        <li class="page-item ${current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${current_page - 1})">«</a>
        </li>
    `;

            // Page numbers (windowed)
            const start = Math.max(1, current_page - 2);
            const end = Math.min(last_page, current_page + 2);

            if (start > 1) {
                container.innerHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(1)">1</a>
            </li>
            <li class="page-item disabled"><span class="page-link">…</span></li>
        `;
            }

            for (let i = start; i <= end; i++) {
                container.innerHTML += `
            <li class="page-item ${i === current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>
        `;
            }

            if (end < last_page) {
                container.innerHTML += `
            <li class="page-item disabled"><span class="page-link">…</span></li>
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${last_page})">${last_page}</a>
            </li>
        `;
            }

            // Next button
            container.innerHTML += `
        <li class="page-item ${current_page === last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${current_page + 1})">»</a>
        </li>
    `;
        }


        /**
         * Update Report Summary
         */
        function updateReportSummary(summary) {
            document.getElementById('report-total').textContent = 'Rs. ' + (summary.total_expenses || 0).toFixed(2);
            document.getElementById('report-count').textContent = summary.total_count || 0;
            document.getElementById('report-average').textContent = 'Rs. ' + (summary.average_expense || 0).toFixed(2);
            document.getElementById('report-highest').textContent = 'Rs. ' + (summary.highest_expense || 0).toFixed(2);
            document.getElementById('cashback_amount').textContent = 'Rs. ' + (summary.cashback_amount || 0).toFixed(2);
        }

        /**
         * Reset Report Filters
         */
        function resetReportFilters() {
            document.getElementById('report-start-date').value = '';
            document.getElementById('report-end-date').value = '';
            document.getElementById('report-category').value = '';
            document.getElementById('report-payment-method').value = '';
            document.getElementById('report-min-amount').value = '';
            document.getElementById('report-max-amount').value = '';
            document.getElementById('report-search').value = '';

            // Clear report
            document.querySelector('#report-table tbody').innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>Click "Generate Report" to view expense data</p>
                </td>
            </tr>
        `;
            document.getElementById('summary-section').style.display = 'none';
        }


        /**
         * Show Loading State
         */
        function showLoadingState() {
            const loader = document.getElementById('report-loader');
            if (loader) {
                loader.style.display = 'flex';
            }
        }

        /**
         * Hide Loading State
         */
        function hideLoadingState() {
            const loader = document.getElementById('report-loader');
            if (loader) {
                loader.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Load initial reports
            loadSpendingInsights();
            loadDetailedReport();
            loadCategoryReport();
            loadPaymentReport();
            loadMonthlyReport();

            // Filter buttons
            document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
            document.getElementById('clearFiltersBtn').addEventListener('click', clearFilters);

            // Export buttons
            document.getElementById('exportDetailedCsv').addEventListener('click', () => exportReport('csv'));
            document.getElementById('exportDetailedPdf').addEventListener('click', () => exportReport('pdf'));
            document.getElementById('exportDetailedExcel').addEventListener('click', () => exportReport('excel'));

            // Tab change listeners
            document.getElementById('categoryTab').addEventListener('click', loadCategoryReport);
            document.getElementById('paymentTab').addEventListener('click', loadPaymentReport);
            document.getElementById('monthlyTab').addEventListener('click', loadMonthlyReport);
        });

        function loadSpendingInsights() {
            const params = new URLSearchParams({
                start_date: document.getElementById("startDate").value,
                end_date: document.getElementById("endDate").value
            });

            fetch(`/reports/spending-insights?${params}`)
                .then(response => response.json())
                .then(response => {
                    if (!response.success) return;

                    const data = response.data;
                    const money = value => `Rs. ${Number(value || 0).toFixed(2)}`;
                    document.getElementById("insightExpenses").textContent = money(data.cash_flow.expenses);
                    document.getElementById("insightEssential").textContent = money(data.spending.essential);
                    document.getElementById("insightPotentialSaving").textContent = money(data.spending.potential_saving);
                    document.getElementById("insightInvestments").textContent = money(data.cash_flow.investments);
                    document.getElementById("insightReview").textContent = `Review unclassified: ${money(data.spending.unclassified)}`;

                    const change = data.comparison.change_percentage;
                    document.getElementById("insightComparison").textContent = change === null
                        ? "No previous-period baseline"
                        : `${Math.abs(change).toFixed(1)}% ${change > 0 ? "more" : change < 0 ? "less" : "same"} than previous period`;
                })
                .catch(error => console.error("Error loading spending insights:", error));
        }

        /**
         * Load detailed report
         */
        function loadDetailedReport() {
            const params = new URLSearchParams({
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                ...currentFilters
            });

            fetch(`/reports/detailed?${params}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('detailedTableBody');
                        tbody.innerHTML = '';

                        data.data.expenses.forEach(expense => {
                            const row = `
                            <tr>
                                <td>${expense.id}</td>
                                <td>${expense.description}</td>
                                <td>Rs. ${parseFloat(expense.amount).toFixed(2)}</td>
                                <td>${expense.expense_date}</td>
                                <td>${expense.category}</td>
                                <td>${expense.type}</td>
                                <td>${expense.payment_method}</td>
                                <td>${expense.payment_platform || '-'}</td>
                                <td>${expense.account || '-'}</td>
                                <td>${expense.cashback || 0}</td>
                            </tr>
                        `;
                            tbody.innerHTML += row;
                        });

                        // Update summary
                        document.getElementById('detailedTotal').textContent = 'Rs. ' + data.data.summary.total_expenses.toFixed(2);
                        document.getElementById('detailedCount').textContent = data.data.summary.total_count;
                        document.getElementById('detailedAverage').textContent = 'Rs. ' + data.data.summary.average_expense.toFixed(2);
                        document.getElementById('detailedHighest').textContent = 'Rs. ' + data.data.summary.highest_expense.toFixed(2);
                        document.getElementById('noOfDays').textContent =  '('+data.data.summary.noOfDays+')';
                    }
                })
                .catch(error => console.error('Error loading detailed report:', error));
        }

        /**
         * Load category report
         */
        function loadCategoryReport() {
            const params = new URLSearchParams({
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value
            });

            fetch(`/reports/category?${params}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('categoryTableBody');
                        tbody.innerHTML = '';

                        data.data.categories.forEach(category => {
                            const row = `
                            <tr>
                                <td><strong>${category.category_name}</strong></td>
                                <td>${category.count}</td>
                                <td>Rs. ${parseFloat(category.total_amount).toFixed(2)}</td>
                                <td>Rs. ${parseFloat(category.average_amount).toFixed(2)}</td>
                                <td>Rs. ${parseFloat(category.highest_amount).toFixed(2)}</td>
                                <td>Rs. ${parseFloat(category.lowest_amount).toFixed(2)}</td>
                                <td><span class="badge bg-info">${category.percentage}%</span></td>
                            </tr>
                        `;
                            tbody.innerHTML += row;
                        });

                        document.getElementById('categoryTotal').textContent = 'Rs. ' + data.data.summary.total_amount.toFixed(2);
                        document.getElementById('categoryCount').textContent = data.data.summary.total_categories;
                    }
                })
                .catch(error => console.error('Error loading category report:', error));
        }

        /**
         * Load payment method report
         */
        function loadPaymentReport() {
            const params = new URLSearchParams({
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value
            });

            fetch(`/reports/payment-method?${params}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('paymentTableBody');
                        tbody.innerHTML = '';

                        data.data.payment_methods.forEach(method => {
                            const row = `
                            <tr>
                                <td><strong>${method.method_name}</strong></td>
                                <td>${method.method_type}</td>
                                <td>${method.count}</td>
                                <td>Rs. ${parseFloat(method.total_amount).toFixed(2)}</td>
                                <td>Rs. ${parseFloat(method.average_amount).toFixed(2)}</td>
                                <td><span class="badge bg-success">${method.percentage}%</span></td>
                            </tr>
                        `;
                            tbody.innerHTML += row;
                        });

                        document.getElementById('paymentTotal').textContent = 'Rs. ' + data.data.summary.total_amount.toFixed(2);
                        document.getElementById('paymentCount').textContent = data.data.summary.total_methods;
                    }
                })
                .catch(error => console.error('Error loading payment report:', error));
        }

        /**
         * Load monthly summary report
         */
        function loadMonthlyReport() {
            const year = new Date().getFullYear();

            fetch(`/reports/monthly-summary?year=${year}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('monthlyTableBody');
                        tbody.innerHTML = '';

                        data.data.months.forEach(month => {
                            const row = `
                            <tr>
                                <td><strong>${month.month_name}</strong></td>
                                <td>${month.count}</td>
                                <td>Rs. ${parseFloat(month.total_amount).toFixed(2)}</td>
                                <td>Rs. ${parseFloat(month.average_amount).toFixed(2)}</td>
                                <td>Rs. ${parseFloat(month.highest_amount).toFixed(2)}</td>
                                <td>Rs. ${parseFloat(month.lowest_amount).toFixed(2)}</td>
                                <td><span class="badge bg-warning">${month.percentage}%</span></td>
                            </tr>
                        `;
                            tbody.innerHTML += row;
                        });

                        document.getElementById('monthlyTotal').textContent = 'Rs. ' + data.data.summary.total_amount.toFixed(2);
                        document.getElementById('highestMonth').textContent = data.data.summary.highest_month;
                        document.getElementById('lowestMonth').textContent = data.data.summary.lowest_month;
                    }
                })
                .catch(error => console.error('Error loading monthly report:', error));
        }

        /**
         * Apply filters
         */
        function applyFilters() {
            currentFilters = {
                category_id: document.getElementById('categoryFilter').value,
                payment_method_id: document.getElementById('paymentMethodFilter').value,
                expense_for_id: document.getElementById('expenseForFilter').value,
                min_amount: document.getElementById('minAmount').value,
                max_amount: document.getElementById('maxAmount').value,
                search: document.getElementById('searchQuery').value
            };

            // Remove empty filters
            Object.keys(currentFilters).forEach(key => {
                if (!currentFilters[key]) delete currentFilters[key];
            });

            loadSpendingInsights();
            loadDetailedReport();
            // CommonCRUD.showNotification('success', 'Filters applied successfully');
        }

        /**
         * Clear filters
         */
        function clearFilters() {
            document.getElementById('categoryFilter').value = '';
            document.getElementById('paymentMethodFilter').value = '';
            document.getElementById('expenseForFilter').value = '';
            document.getElementById('minAmount').value = '';
            document.getElementById('maxAmount').value = '';
            document.getElementById('searchQuery').value = '';

            currentFilters = {};
            loadSpendingInsights();
            loadDetailedReport();
            CommonCRUD.showNotification('success', 'Filters cleared');
        }

        /**
         * Export report
         */
        function exportReport(format) {
            const params = new URLSearchParams({
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                ...currentFilters
            });

            const urls = {
                csv: `/api/reports/export-csv?${params}`,
                pdf: `/api/reports/export-pdf?${params}`,
                excel: `/api/reports/export-excel?${params}`
            };

            window.location.href = urls[format];
            CommonCRUD.showNotification('success', `Exporting as ${format.toUpperCase()}...`);
        }
    </script>
    @endpush