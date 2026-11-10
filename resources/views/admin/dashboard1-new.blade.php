@extends('layoutsnew.app')
@push('styles')
<!-- C3 Chart css -->
<link href="{{asset('backend/libs/c3/c3.min.css')}}" rel="stylesheet" type="text/css" />
@endpush
<!-- Begin page -->
@section('content')
<input type="hidden" id="listRoute" value="{{route('dashboard.summary')}}">
<div class="dashboard-container">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h2">
                    <i class="fas fa-chart-line"></i> Dashboard
                </h1>
                <p class="text-muted">Expense Analytics & Insights</p>
            </div>
            <div class="col-auto">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary period-btn active" data-period="today">
                        Today
                    </button>
                    <button type="button" class="btn btn-outline-primary period-btn" data-period="week">
                        Week
                    </button>
                    <button type="button" class="btn btn-outline-primary period-btn" data-period="month">
                        Month
                    </button>
                    <button type="button" class="btn btn-outline-primary period-btn" data-period="year">
                        Year
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Total Expenses</p>
                            <h3 class="mb-0" id="totalExpenses">$0.00</h3>
                        </div>
                        <div class="icon-box bg-primary">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <small class="text-success" id="totalTrend">
                        <i class="fas fa-arrow-up"></i> 0% vs last period
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Transactions</p>
                            <h3 class="mb-0" id="totalCount">0</h3>
                        </div>
                        <div class="icon-box bg-info">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                    <small class="text-muted">Total entries</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Average Expense</p>
                            <h3 class="mb-0" id="averageExpense">$0.00</h3>
                        </div>
                        <div class="icon-box bg-warning">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                    <small class="text-muted">Per transaction</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Highest Expense</p>
                            <h3 class="mb-0" id="highestExpense">$0.00</h3>
                        </div>
                        <div class="icon-box bg-danger">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                    <small class="text-muted">Maximum amount</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <!-- Category-wise Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-pie-chart"></i> Expenses by Category
                    </h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <div id="categoryDetails" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Payment Method Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card"></i> Payment Methods
                    </h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="paymentChart"></canvas>
                    </div>
                    <div id="paymentDetails" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mb-4">
        <!-- Monthly Trend Chart -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Monthly Trend
                    </h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 3 -->
    <div class="row mb-4">
        <!-- Day-wise Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day"></i> Daily Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="dayChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Type Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tags"></i> Expense Types
                    </h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="typeChart"></canvas>
                    </div>
                    <div id="typeDetails" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Row -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt"></i> Period Comparison
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="comparison-box">
                                <h6 class="text-muted">Current Period</h6>
                                <h3 id="currentTotal">$0.00</h3>
                                <p class="text-muted mb-0" id="currentRange"></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="comparison-box text-center">
                                <div class="trend-indicator" id="trendIndicator">
                                    <i class="fas fa-arrow-up text-success"></i>
                                    <p id="trendText">0% increase</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="comparison-box">
                                <h6 class="text-muted">Previous Period</h6>
                                <h3 id="previousTotal">$0.00</h3>
                                <p class="text-muted mb-0" id="previousRange"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Export Dashboard Data</h6>
                            <small class="text-muted">Download reports in various formats</small>
                        </div>
                        <div>
                            <button class="btn btn-outline-success btn-sm" id="exportCsv">
                                <i class="fas fa-download"></i> CSV
                            </button>
                            <button class="btn btn-outline-danger btn-sm" id="exportPdf">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                            <button class="btn btn-outline-info btn-sm" id="exportExcel">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-css')
<style>
    .dashboard-container {
        padding: 20px 0;
    }

    .summary-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .summary-card .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
    }

    .icon-box.bg-primary {
        background-color: #0052CC;
    }

    .icon-box.bg-info {
        background-color: #4ECDC4;
    }

    .icon-box.bg-warning {
        background-color: #FFD93D;
        color: #333;
    }

    .icon-box.bg-danger {
        background-color: #FF6B6B;
    }

    .card {
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .card-header {
        background-color: #F2F2F2;
        border-bottom: 1px solid #E0E0E0;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
    }

    .card-header h5 {
        font-weight: 600;
        color: #1A1A1A;
    }

    .card-body {
        padding: 20px;
    }

    .period-btn {
        font-weight: 600;
        padding: 8px 16px;
    }

    .period-btn.active {
        background-color: #0052CC;
        color: white;
        border-color: #0052CC;
    }

    .comparison-box {
        padding: 20px;
        background-color: #F2F2F2;
        border-radius: 8px;
        text-align: left;
    }

    .comparison-box h3 {
        color: #0052CC;
        font-weight: 700;
    }

    .trend-indicator {
        padding: 20px;
        font-size: 36px;
    }

    .trend-indicator i {
        font-size: 48px;
    }

    .trend-indicator p {
        font-size: 18px;
        font-weight: 600;
        margin-top: 10px;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #E0E0E0;
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 500;
        color: #666;
    }

    .detail-value {
        font-weight: 600;
        color: #1A1A1A;
    }

    .detail-percentage {
        display: inline-block;
        background-color: #E8F4F8;
        color: #0052CC;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .summary-card {
            margin-bottom: 15px;
        }

        .btn-group {
            width: 100%;
            margin-top: 10px;
        }

        .btn-group .btn {
            flex: 1;
        }
    }
</style>
@endsection

@push('scripts')
<!--C3 Chart-->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Chart instances
    let charts = {};
    let currentPeriod = 'month';

    // Initialize dashboard on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardData();
        
        // Period button listeners
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentPeriod = this.dataset.period;
                loadDashboardData();
            });
        });

        // Export buttons
        document.getElementById('exportCsv').addEventListener('click', () => exportData('csv'));
        document.getElementById('exportPdf').addEventListener('click', () => exportData('pdf'));
        document.getElementById('exportExcel').addEventListener('click', () => exportData('excel'));
    });

    /**
     * Load all dashboard data
     */
    function loadDashboardData() {
        showLoading();
        Promise.all([
            fetch(`/dashboard/summary?period=${currentPeriod}`).then(r => r.json()),
            fetch(`/dashboard/category-wise?period=${currentPeriod}`).then(r => r.json()),
            fetch(`/dashboard/payment-method-wise?period=${currentPeriod}`).then(r => r.json()),
            fetch(`/dashboard/expense-type-wise?period=${currentPeriod}`).then(r => r.json()),
            fetch(`/dashboard/monthly-trend`).then(r => r.json()),
            fetch(`/dashboard/day-wise?period=${currentPeriod}`).then(r => r.json()),
            fetch(`/dashboard/comparison?period=${currentPeriod}`).then(r => r.json())
        ])
        .then(([summary, category, payment, type, monthly, day, comparison]) => {
            updateSummaryCards(summary.data);
            updateCategoryChart(category.data);
            updatePaymentChart(payment.data);
            updateTypeChart(type.data);
            updateMonthlyChart(monthly.data);
            updateDayChart(day.data);
            updateComparison(comparison.data);
            hideLoading();
        })
        .catch(error => {
            console.error('Error loading dashboard:', error);
            CommonCRUD.showNotification('error', 'Failed to load dashboard data');
            hideLoading();
        });
    }

    /**
     * Update summary cards
     */
    function updateSummaryCards(data) {
        document.getElementById('totalExpenses').textContent = 'Rs. ' + data.total_expenses;
        document.getElementById('totalCount').textContent = data.total_count;
        document.getElementById('averageExpense').textContent = 'Rs. ' + data.average_expense.toFixed(2);
        document.getElementById('highestExpense').textContent = 'Rs. ' + data.highest_expense.toFixed(2);
    }

    /**
     * Update category-wise chart
     */
    function updateCategoryChart(data) {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        
        if (charts.category) {
            charts.category.destroy();
        }

        charts.category = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Update details
        let detailsHtml = '';
        data.details.forEach(item => {
            detailsHtml += `
                <div class="detail-item">
                    <span class="detail-label"><b>${item.category}</b> : </span>
                    <span class="detail-value">
                        <u>Rs. ${item.amount.toFixed(2)}</u>
                        <span class="detail-percentage">${item.percentage}%</span>
                    </span>
                </div>
            `;
        });
        document.getElementById('categoryDetails').innerHTML = detailsHtml;
    }

    /**
     * Update payment method chart
     */
    function updatePaymentChart(data) {
        const ctx = document.getElementById('paymentChart').getContext('2d');
        
        if (charts.payment) {
            charts.payment.destroy();
        }

        charts.payment = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Update details
        let detailsHtml = '';
        data.details.forEach(item => {
            detailsHtml += `
                <div class="detail-item">
                    <span class="detail-label">${item.method}</span>
                    <span class="detail-value">
                        Rs. ${item.amount.toFixed(2)}
                        <span class="detail-percentage">${item.percentage}%</span>
                    </span>
                </div>
            `;
        });
        document.getElementById('paymentDetails').innerHTML = detailsHtml;
    }

    /**
     * Update expense type chart
     */
    function updateTypeChart(data) {
        const ctx = document.getElementById('typeChart').getContext('2d');
        
        if (charts.type) {
            charts.type.destroy();
        }

        charts.type = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Update details
        let detailsHtml = '';
        data.details.forEach(item => {
            detailsHtml += `
                <div class="detail-item">
                    <span class="detail-label">${item.type}</span>
                    <span class="detail-value">
                        Rs. ${item.amount.toFixed(2)}
                        <span class="detail-percentage">${item.percentage}%</span>
                    </span>
                </div>
            `;
        });
        document.getElementById('typeDetails').innerHTML = detailsHtml;
    }

    /**
     * Update monthly trend chart
     */
    function updateMonthlyChart(data) {
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        
        if (charts.monthly) {
            charts.monthly.destroy();
        }

        charts.monthly = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rs. ' + value.toFixed(0);
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Update day-wise chart
     */
    function updateDayChart(data) {
        const ctx = document.getElementById('dayChart').getContext('2d');
        
        if (charts.day) {
            charts.day.destroy();
        }

        charts.day = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rs. ' + value.toFixed(0);
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Update comparison data
     */
    function updateComparison(data) {
        document.getElementById('currentTotal').textContent = 'Rs. ' + data.current_period.total.toFixed(2);
        document.getElementById('currentRange').textContent = data.current_period.date_range;
        
        document.getElementById('previousTotal').textContent = 'Rs. ' + data.previous_period.total.toFixed(2);
        document.getElementById('previousRange').textContent = data.previous_period.date_range;

        const trendIndicator = document.getElementById('trendIndicator');
        const trendText = document.getElementById('trendText');
        const difference = data.comparison.difference;
        const percentage = data.comparison.percentage_change;

        if (difference > 0) {
            trendIndicator.innerHTML = '<i class="fas fa-arrow-up text-danger"></i>';
            trendText.textContent = percentage.toFixed(2) + '% increase';
            trendText.className = 'text-danger';
        } else if (difference < 0) {
            trendIndicator.innerHTML = '<i class="fas fa-arrow-down text-success"></i>';
            trendText.textContent = Math.abs(percentage).toFixed(2) + '% decrease';
            trendText.className = 'text-success';
        } else {
            trendIndicator.innerHTML = '<i class="fas fa-minus text-muted"></i>';
            trendText.textContent = 'No change';
            trendText.className = 'text-muted';
        }
    }

    /**
     * Export data
     */
    function exportData(format) {
        // Implementation for CSV, PDF, Excel export
        CommonCRUD.showNotification('info', 'Exporting as ' + format.toUpperCase() + '...');
        // Add export logic here
    }

    /**
     * Show loading indicator
     */
    function showLoading() {
        document.querySelectorAll('canvas').forEach(canvas => {
            canvas.style.opacity = '0.5';
        });
    }

    /**
     * Hide loading indicator
     */
    function hideLoading() {
        document.querySelectorAll('canvas').forEach(canvas => {
            canvas.style.opacity = '1';
        });
    }
</script>
@endpush