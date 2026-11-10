@extends('layoutsnew.app')
@push('styles')
<!-- C3 Chart css -->
<link href="{{asset('backend/libs/c3/c3.min.css')}}" rel="stylesheet" type="text/css" />
<style>
    .float-right {
        float: right;
    }
</style>
@endpush
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
                            <li class="breadcrumb-item active">Dashboard 1</li>
                        </ol>
                    </div>
                    <h4 class="page-title"><i class="fas fa-chart-line"></i> Dashboard</h4>
                </div>
            </div>
        </div>

        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">

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
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-3 col-sm-6">
                <div class="card-box widget-box-two widget-two-custom">
                    <div class="media">
                        <div class="avatar-lg rounded-circle bg-primary widget-two-icon align-self-center">
                            <i class="fas fa-wallet avatar-title font-30 text-white"></i>
                        </div>

                        <div class="wigdet-two-content media-body">
                            <p class="m-0 text-uppercase font-weight-medium text-truncate" title="Statistics">Total Expenses</p>
                            <h3 class="font-weight-medium my-2"> <span data-plugin="counterup" id="totalExpenses">0</span></h3>
                            <p class="m-0 dateRange"></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end col -->

            <div class="col-xl-3 col-sm-6">
                <div class="card-box widget-box-two widget-two-custom ">
                    <div class="media">
                        <div class="avatar-lg rounded-circle bg-primary widget-two-icon align-self-center">
                            <i class="fas fa-receipt avatar-title font-30 text-white"></i>
                        </div>

                        <div class="wigdet-two-content media-body">
                            <p class="m-0 text-uppercase font-weight-medium text-truncate" title="Statistics">Transactions</p>
                            <h3 class="font-weight-medium my-2"> <span data-plugin="counterup" id="totalCount">0</span></h3>
                            <p class="m-0 dateRange"></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end col -->

            <div class="col-xl-3 col-sm-6">
                <div class="card-box widget-box-two widget-two-custom ">
                    <div class="media">
                        <div class="avatar-lg rounded-circle bg-primary widget-two-icon align-self-center">
                            <i class="fas fa-chart-bar avatar-title font-30 text-white"></i>
                        </div>

                        <div class="wigdet-two-content media-body">
                            <p class="m-0 text-uppercase font-weight-medium text-truncate" title="Statistics">Average Expense</p>
                            <h3 class="font-weight-medium my-2"><span data-plugin="counterup" id="averageExpense">0</span></h3>
                            <p class="m-0 dateRange"></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end col -->

            <div class="col-xl-3 col-sm-6">
                <div class="card-box widget-box-two widget-two-custom ">
                    <div class="media">
                        <div class="avatar-lg rounded-circle bg-primary widget-two-icon align-self-center">
                            <i class="fas fa-arrow-up avatar-title font-30 text-white"></i>
                        </div>

                        <div class="wigdet-two-content media-body">
                            <p class="m-0 text-uppercase font-weight-medium text-truncate" title="Statistics">Highest Expense</p>
                            <h3 class="font-weight-medium my-2"><span data-plugin="counterup" id="highestExpense">0</span></h3>
                            <p class="m-0 dateRange"></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- end row -->

        <div class="row">
            <div class="col-6">
                <div class="card-box">
                    <h4 class="header-title mb-3">Daily Breakdown <small id="dailyTotal" class="float-right">0</small></h4>
                    <!-- <div class="text-center">
                        <div class="row">
                            <div class="col-4">
                                <div class="mt-3 mb-3">
                                    <h3 class="mb-2">2563</h3>
                                    <p class="text-uppercase mb-1 font-13 font-weight-normal">Lifetime total sales</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mt-3 mb-3">
                                    <h3 class="mb-2">6952</h3>
                                    <p class="text-uppercase mb-1 font-13 font-weight-normal">Income amounts</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mt-3 mb-3">
                                    <h3 class="mb-2">1125</h3>
                                    <p class="text-uppercase mb-1 font-13 font-weight-normal">Total visits</p>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <div id="daily-chart" style="height: 310px;" class="morris-charts"></div>
                </div>
            </div><!-- end col -->
            <div class="col-6">
                <div class="card-box">
                    <h4 class="header-title mb-3">Daily Category Wise Expense <small id="dailyCatTotal" class="float-right"></small></h4>
                    <canvas id="dailyExpenseChart" style="height: 310px;" class="morris-charts"></canvas>
                </div>
            </div><!-- end col -->
        </div>

        <div class="row mb-4">
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
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card"></i> Expense Type
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
        <div class="row">
            <div class="col-12">
                <div class="card-box" id="categoryDetailsContainer">
                    <!-- <h4 class="header-title">Recent Users</h4> -->
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-xl-12">
                <div class="card-box">
                    <h4 class="header-title mb-4">Expenses by Category</h4>
                    <div class="chart-container" dir="ltr">
                        <div class="" style="height:280px" id="categoryChart"></div>
                        <div id="categoryDetails" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->


        <div class="row">
            <div class="col-xl-6 col-lg-12">
                <div class="card-box">
                    <h4 class="header-title">Recent Candidates</h4>
                    <p class="sub-header">
                        Your awesome text goes here.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-hover m-0 table-actions-bar">

                            <thead>
                                <tr>
                                    <th>
                                        <div class="btn-group dropdown">
                                            <button type="button" class="btn btn-light btn-xs dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false"> <i class="mdi mdi-chevron-down"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                                <a class="dropdown-item" href="#">Dropdown link</a>
                                            </div>
                                        </div>
                                    </th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Job Timing</th>
                                    <th>Salary</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <img src="{{asset('backend/images/users/avatar-2.jpg')}}" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm" />
                                    </td>

                                    <td>
                                        <h5 class="m-0 font-weight-medium">Tomaslau</h5>
                                    </td>

                                    <td>
                                        <i class="mdi mdi-map-marker text-primary"></i> New York
                                    </td>

                                    <td>
                                        <i class="mdi mdi-clock-outline text-success"></i> Full Time
                                    </td>

                                    <td>
                                        <i class="mdi mdi-currency-usd text-warning"></i> 3265
                                    </td>

                                    <td>
                                        <a href="#" class="table-action-btn"><i class="mdi mdi-pencil"></i></a>
                                        <a href="#" class="table-action-btn"><i class="mdi mdi-close"></i></a>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <img src="{{asset('backend/images/users/avatar-3.jpg')}}" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm" />
                                    </td>

                                    <td>
                                        <h5 class="m-0 font-weight-medium">Erwin E. Brown</h5>
                                    </td>

                                    <td>
                                        <i class="mdi mdi-map-marker text-primary"></i> California
                                    </td>

                                    <td>
                                        <i class="mdi mdi-clock-outline text-success"></i> Part Time
                                    </td>

                                    <td>
                                        <i class="mdi mdi-currency-usd text-warning"></i> 1365
                                    </td>

                                    <td>
                                        <a href="#" class="table-action-btn"><i class="mdi mdi-pencil"></i></a>
                                        <a href="#" class="table-action-btn"><i class="mdi mdi-close"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="{{asset('backend/images/users/avatar-4.jpg')}}" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm" />
                                    </td>

                                    <td>
                                        <h5 class="m-0 font-weight-medium">Margeret V. Ligon</h5>
                                    </td>

                                    <td>
                                        <i class="mdi mdi-map-marker text-primary"></i> New York
                                    </td>

                                    <td>
                                        <i class="mdi mdi-clock-outline text-success"></i> Full Time
                                    </td>

                                    <td>
                                        <i class="mdi mdi-currency-usd text-warning"></i> 115248
                                    </td>

                                    <td>
                                        <a href="#" class="table-action-btn"><i class="mdi mdi-pencil"></i></a>
                                        <a href="#" class="table-action-btn"><i class="mdi mdi-close"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="{{asset('backend/images/users/avatar-5.jpg')}}" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm" />
                                    </td>

                                    <td>
                                        <h5 class="m-0 font-weight-medium">Jose D. Delacruz</h5>
                                    </td>

                                    <td>
                                        <i class="mdi mdi-map-marker text-primary"></i> New York
                                    </td>

                                    <td>
                                        <i class="mdi mdi-clock-outline text-success"></i> Part Time
                                    </td>

                                    <td>
                                        <i class="mdi mdi-currency-usd text-warning"></i> 2451
                                    </td>

                                    <td>
                                        <a href="#" class="table-action-btn"><i class="mdi mdi-pencil"></i></a>
                                        <a href="#" class="table-action-btn"><i class="mdi mdi-close"></i></a>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <img src="{{asset('backend/images/users/avatar-8.jpg')}}" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm" />
                                    </td>

                                    <td>
                                        <h5 class="m-0 font-weight-medium">Luke J. Sain</h5>
                                    </td>

                                    <td>
                                        <i class="mdi mdi-map-marker text-primary"></i> Australia
                                    </td>

                                    <td>
                                        <i class="mdi mdi-clock-outline text-success"></i> Part Time
                                    </td>

                                    <td>
                                        <i class="mdi mdi-currency-usd text-warning"></i> 3265
                                    </td>

                                    <td>
                                        <a href="#" class="table-action-btn"><i class="mdi mdi-pencil"></i></a>
                                        <a href="#" class="table-action-btn"><i class="mdi mdi-close"></i></a>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <!-- end col -->

            <div class="col-xl-3 col-lg-6">
                <div class="card-box">
                    <h4 class="header-title mb-4">Total Unique Visitors</h4>

                    <div class="widget-chart text-center" dir="ltr">

                        <div id="donut-chart" style="height: 280px;"></div>

                        <div class="row text-center mt-4">
                            <div class="col-6">
                                <h3 data-plugin="counterup">1,507</h3>
                                <p class="text-muted mb-0">Visitors Male</p>
                            </div>
                            <div class="col-6">
                                <h3 data-plugin="counterup">854</h3>
                                <p class="text-muted mb-1">Visitors Female</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card-box">
                    <h4 class="header-title mb-4">Number of Transactions</h4>

                    <div class="widget-chart text-center" dir="ltr">

                        <div id="pie-chart" style="height: 280px;"></div>

                        <div class="row text-center mt-4">
                            <div class="col-6">
                                <h3 data-plugin="counterup">2,854</h3>
                                <p class="text-muted mb-0">Payment Done</p>
                            </div>
                            <div class="col-6">
                                <h3 data-plugin="counterup">22</h3>
                                <p class="text-muted mb-1">Payment Due</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <!--- end row -->

    </div> <!-- end container-fluid -->

</div> <!-- end content -->
@endsection

@push('scripts')
<!--C3 Chart-->
<script src="{{asset('backend/libs/d3/d3.min.js')}}"></script>
<script src="{{asset('backend/libs/c3/c3.min.js')}}"></script>
<script src="{{asset('backend/libs/echarts/echarts.min.js')}}"></script>

<script src="{{asset('backend/js/pages/dashboard.init.js')}}"></script>
<script src="{{asset('backend/libs/morris-js/morris.min.js')}}"></script>
<script src="{{asset('backend/libs/raphael/raphael.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Chart instances
    let charts = {};
    let currentPeriod = 'month';

    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardData();

    });

    function loadDashboardData() {
        Promise.all([
            fetch(`/dashboard/summary?period=${currentPeriod}`).then(r => r.json()),
            fetch(`/dashboard/category-wise?period=${currentPeriod}`).then(r => r.json()),
            fetch(`/dashboard/day-wise?period=${currentPeriod}`).then(r => r.json()),
            fetch(`/dashboard/day-wise-category?period=${currentPeriod}`).then(r => r.json()),
            fetch(`/dashboard/payment-method-wise?period=${currentPeriod}`).then(r => r.json()),
            fetch(`/dashboard/expense-type-wise?period=${currentPeriod}`).then(r => r.json()),
        ]).then(([summary, category, dayWise, dayWiseWithCategory, paymentMethod, expenseType]) => {
            updateSummaryCards(summary.data);
            updateCategoryChart(category.data);
            updateDailyChart(dayWise.data);
            renderDailyExpenseChart(dayWiseWithCategory.data, dayWiseWithCategory.total);
            updatePaymentChart(paymentMethod.data);
            updateTypeChart(expenseType.data);
        })
    }

    function updateSummaryCards(data) {
        // Elements to update
        const elements = {
            totalCount: document.getElementById('totalCount'),
            totalExpenses: document.getElementById('totalExpenses'),
            averageExpense: document.getElementById('averageExpense'),
            highestExpense: document.getElementById('highestExpense'),
            lowestExpense: document.getElementById('lowestExpense'),
        };
        const dateRangeElements = document.querySelectorAll('.dateRange') // or getElementById

        // Update date range immediately (not animated)

        dateRangeElements.forEach(element => {
            element.textContent = data.date_range;
        });

        // Store target values
        const targetValues = {
            totalCount: data.total_count,
            totalExpenses: data.total_expenses,
            averageExpense: data.average_expense,
            highestExpense: data.highest_expense,
            lowestExpense: data.lowest_expense
        };

        // Reset all counters to 0 for animation
        Object.keys(elements).forEach(key => {
            if (key !== 'dateRange' && elements[key]) {
                elements[key].textContent = '0';
                // Clear any existing counterUp data
                $(elements[key]).removeData('counterup');
                $(elements[key]).removeData('waypoint');
            }
        });

        // Animate all counters after a short delay
        setTimeout(() => {
            Object.keys(elements).forEach(key => {
                if (key !== 'dateRange' && elements[key] && targetValues[key] !== undefined) {
                    // Set final value
                    elements[key].textContent = targetValues[key];

                    // Initialize counterUp for each element
                    $(elements[key]).counterUp({
                        delay: 10,
                        time: 1000
                    });
                }
            });
        }, 50);
    }


    function updateCategoryChart(data) {
        console.log('Updating category chart with:', data);

        const container = document.getElementById('categoryChart');

        if (!container) {
            console.error('Container element #categoryChart not found');
            return;
        }

        // Check if ECharts is available
        if (typeof echarts === 'undefined') {
            console.error('ECharts is not loaded');
            container.innerHTML = '<div class="alert alert-danger">Chart library not loaded</div>';
            return;
        }

        // Initialize or get ECharts instance
        let chart = echarts.getInstanceByDom(container);
        if (!chart) {
            chart = echarts.init(container);
        }

        // Prepare ECharts options
        const option = {
            tooltip: {
                trigger: 'item',
                formatter: function(params) {
                    const value = params.value || 0;
                    const percentage = params.percent || 0;
                    return `${params.name}<br/>Rs. ${value.toFixed(2)} (${percentage}%)`;
                }
            },
            legend: {
                orient: 'vertical',
                right: 10,
                top: 'center',
                textStyle: {
                    fontSize: 12
                }
            },
            series: [{
                name: 'Category Distribution',
                type: 'pie',
                radius: ['40%', '70%'],
                center: ['40%', '50%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 5,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: false,
                    position: 'center'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: '16',
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: false
                },
                data: data.labels.map((label, index) => ({
                    name: label,
                    value: data.datasets[0].data[index],
                    itemStyle: {
                        color: data.datasets[0].backgroundColor[index]
                    }
                }))
            }]
        };

        // Set option and resize
        chart.setOption(option);
        chart.resize();

        // Store chart reference
        charts.category = chart;

        // Update details
        updateCategoryDetails(data);



        const dataItems = data.details;
        let detailsHtml = '';

        const columnsPerRow = 6; // 6 columns max per row

        for (let start = 0; start < dataItems.length; start += columnsPerRow) {
            const rowChunk = dataItems.slice(start, start + columnsPerRow);

            detailsHtml += '<div class="row">';

            rowChunk.forEach(item => {
                detailsHtml += `
            <div class="col-md-2 col-sm-6">
                <div class="card">
                    <div class="card-body p-2 text-center">
                        <strong>${item.category}</strong>
                        <hr />
                        Rs. ${item.amount.toLocaleString('en-IN')} / 
                        <b>(<span>${item.percentage}%</span>)</b>
                    </div>
                </div>
            </div>
        `;
            });

            detailsHtml += '</div>'; // End row
        }

        document.getElementById('categoryDetails').innerHTML = detailsHtml;
    }

    function updateCategoryDetails(data) {
        const detailsContainer = document.getElementById('categoryDetailsContainer');

        if (!detailsContainer) {
            console.warn('Details container not found');
            return;
        }

        if (!data.details || !Array.isArray(data.details) || data.details.length === 0) {
            detailsContainer.innerHTML = '<div class="alert alert-info">No category data available</div>';
            return;
        }

        let detailsHtml = `<h3 class="mb-3"> Latest Category Expense</h4>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">%</th>
                        <th class="text-center">Count</th>
                    </tr>
                </thead>
                <tbody>
    `;

        data.details.forEach(item => {
            const colorIndex = data.details.indexOf(item) % 12;
            const colors = [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                '#9966FF', '#FF9F40', '#8AC926', '#1982C4',
                '#6A4C93', '#F15BB5', '#00BBF9', '#00F5D4'
            ];

            detailsHtml += `
            <tr>
                <td>
                    <span class="badge" style="background-color: ${colors[colorIndex]}">&nbsp;&nbsp;</span>
                    ${item.category}
                </td>
                <td class="text-end">Rs. ${item.amount?.toFixed(2) || '0.00'}</td>
                <td class="text-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="progress flex-grow-1 me-2" style="height: 4px; max-width: 60px;">
                            <div class="progress-bar" style="width: ${item.percentage || 0}%"></div>
                        </div>
                        ${item.percentage?.toFixed(1) || '0'}%
                    </div>
                </td>
                <td class="text-center">${item.count || 0}</td>
            </tr>
        `;
        });

        detailsHtml += `
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="text-end"><strong>Rs. ${data.total?.toFixed(2) || '0.00'}</strong></td>
                        <td class="text-end"><strong>100%</strong></td>
                        <td class="text-center"><strong>${data.details.reduce((sum, item) => sum + (item.count || 0), 0)}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;

        detailsContainer.innerHTML = detailsHtml;
    }

    function getCategoryColor(categoryName, index) {
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
            '#9966FF', '#FF9F40', '#8AC926', '#1982C4',
            '#6A4C93', '#F15BB5', '#00BBF9', '#00F5D4'
        ];
        return colors[index % colors.length];
    }

    var dailyChart;

    dailyChart = Morris.Bar({
        element: 'daily-chart',
        data: [],
        xkey: 'y',
        ykeys: ['expenses'],
        labels: ['Daily Expenses'],
        barColors: ['#6ad9c3'],
        resize: true,
        hideHover: 'auto'
    });

    function updateDailyChart(apiResponse) {

        var morrisData = apiResponse.labels.map((label, index) => {
            return {
                y: label.replace("Day ", ""),
                expenses: apiResponse.datasets[0].data[index]
            };
        });
        dailyChart.setData(morrisData);
        $('#dailyTotal').html(`<b>(Total : ${apiResponse.total}, Average : ${apiResponse.average}) <b>`)
    }

    let dailyExpenseChart;

    function renderDailyExpenseChart(apiData, total) {

        const ctx = document.getElementById('dailyExpenseChart').getContext('2d');

        if (dailyExpenseChart) {
            dailyExpenseChart.destroy();
        }

        const xAxisLabels = addDailyTotals(apiData);

        dailyExpenseChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: xAxisLabels,
                datasets: apiData.datasets
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: {
                            font: ctx => ({
                                size: ctx.index === 1 ? 11 : 12
                            })
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.raw === 0) return null;
                                return `${ctx.dataset.label}: ₹${ctx.raw.toLocaleString()}`;
                            },
                            footer: function(items) {
                                const total = items.reduce((sum, i) => sum + i.raw, 0);
                                return `Total: ₹${total.toLocaleString()}`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        $("#dailyCatTotal").html(`<b>(Rs. ${total})</b>`)
    }

    function addDailyTotals(apiData) {

        const totals = apiData.labels.map((_, index) => {
            return apiData.datasets.reduce((sum, ds) => {
                return sum + (ds.data[index] || 0);
            }, 0);
        });

        return apiData.labels.map((date, index) => ([
            date,
            `Rs. ${totals[index].toLocaleString()}`
        ]));
    }

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
</script>

@endpush