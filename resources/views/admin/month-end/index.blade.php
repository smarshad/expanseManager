@extends('layoutsnew.app')

@section('title', 'Money Dashboard')

@push('styles')
<style>
    .money-dashboard { --ink:#172033; --muted:#718096; --surface:#fff; --line:#edf1f7; }
    .money-dashboard .hero { background:linear-gradient(135deg,#172554 0%,#4338ca 58%,#7c3aed 100%); border-radius:20px; color:#fff; padding:26px; box-shadow:0 18px 40px rgba(67,56,202,.22); }
    .money-dashboard .hero .form-control { border:0; min-height:40px; }
    .money-dashboard .story { background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18); border-radius:14px; padding:16px 18px; }
    .money-dashboard .story li { margin:5px 0; }
    .money-dashboard .metric-card,.money-dashboard .section-card { border:0; border-radius:16px; box-shadow:0 5px 22px rgba(31,45,61,.08); overflow:hidden; }
    .money-dashboard .metric-card { min-height:126px; color:#fff; position:relative; }
    .money-dashboard .metric-card:after { content:""; position:absolute; width:86px; height:86px; border-radius:50%; background:rgba(255,255,255,.12); right:-20px; top:-18px; }
    .money-dashboard .metric-card .card-body { position:relative; z-index:1; }
    .metric-indigo{background:linear-gradient(135deg,#4f46e5,#7c3aed)} .metric-green{background:linear-gradient(135deg,#059669,#10b981)}
    .metric-orange{background:linear-gradient(135deg,#ea580c,#f59e0b)} .metric-blue{background:linear-gradient(135deg,#0284c7,#38bdf8)}
    .metric-pink{background:linear-gradient(135deg,#db2777,#f472b6)} .metric-slate{background:linear-gradient(135deg,#334155,#64748b)}
    .money-dashboard .metric-label { font-size:.76rem; text-transform:uppercase; letter-spacing:.07em; opacity:.82; }
    .money-dashboard .metric-value { font-size:1.45rem; font-weight:750; margin-top:8px; }
    .money-dashboard .metric-note { font-size:.76rem; opacity:.83; margin-top:5px; }
    .money-dashboard .section-card .card-header { background:#fff; border-bottom:1px solid var(--line); padding:17px 20px; }
    .money-dashboard .section-card .card-header h5 { color:var(--ink); font-weight:700; }
    .money-dashboard .chart-box { height:300px; position:relative; }
    .money-dashboard .table td,.money-dashboard .table th { vertical-align:middle; border-color:var(--line); }
    .money-dashboard .table thead th { border-top:0; color:var(--muted); font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; }
    .money-dashboard .progress { height:8px; background:#edf1f7; border-radius:20px; }
    .money-dashboard .quick-add { display:flex; gap:8px; flex-wrap:wrap; }
    .money-dashboard .quick-add a { border-radius:30px; padding:8px 14px; background:#eef2ff; color:#4338ca; font-weight:600; }
    .money-dashboard .heat-row { display:grid; grid-template-columns:repeat(7,1fr); gap:8px; }
    .money-dashboard .heat-day { border-radius:12px; padding:13px 6px; text-align:center; color:#fff; min-width:0; }
    .money-dashboard .calendar-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:7px; }
    .money-dashboard .calendar-head { text-align:center; color:var(--muted); font-size:.72rem; font-weight:700; padding:5px; }
    .money-dashboard .calendar-day { min-height:77px; border:1px solid var(--line); border-radius:10px; padding:8px; background:#fff; }
    .money-dashboard .calendar-day.has-spend { background:linear-gradient(145deg,#eef2ff,#fff); border-color:#c7d2fe; }
    .money-dashboard .calendar-day strong { color:var(--ink); }
    .money-dashboard .calendar-day small { display:block; color:#4f46e5; margin-top:7px; line-height:1.25; }
    .money-dashboard .opportunity { border-left:4px solid #f59e0b; background:#fffbeb; border-radius:10px; padding:12px 14px; margin-bottom:10px; }
    .money-dashboard .empty-state { color:var(--muted); padding:28px; text-align:center; }
    @media(max-width:767px){ .money-dashboard .hero{padding:20px}.money-dashboard .metric-value{font-size:1.2rem}.money-dashboard .heat-row{grid-template-columns:repeat(4,1fr)}.money-dashboard .calendar-day{min-height:58px;padding:5px}.money-dashboard .calendar-day small{font-size:.62rem} }
</style>
@endpush

@section('content')
<div class="content money-dashboard">
<div class="container-fluid">
    <div class="hero mb-4">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="text-uppercase small mb-2" style="opacity:.72;letter-spacing:.12em">Your money story</div>
                <h2 class="text-white mb-1">{{ $dashboard['period_label'] }} Summary</h2>
                <p class="mb-3" style="opacity:.78">Spending, habits and savings opportunities in one view.</p>
                <div class="story">
                    <ul class="mb-0 pl-3">
                        @foreach($dashboard['story'] as $line)<li>{{ $line }}</li>@endforeach
                    </ul>
                </div>
            </div>
            <div class="col-lg-5 mt-3 mt-lg-0">
                <form method="GET" action="{{ route('month-end.index') }}" class="form-inline justify-content-lg-end mb-3">
                    <input name="month" type="month" class="form-control mr-2" value="{{ $dashboard['month'] }}">
                    <button class="btn btn-light" type="submit">View Month</button>
                </form>
                <div class="quick-add justify-content-lg-end">
                    @foreach($dashboard['quick_add'] as $shortcut)<a href="{{ $shortcut['url'] }}">+ {{ $shortcut['label'] }}</a>@endforeach
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    @php
        $s = $dashboard['summary'];
        $changeText = $s['change_percentage'] === null ? 'No previous month data' : (($s['change_percentage'] >= 0 ? '↑ ' : '↓ ').number_format(abs($s['change_percentage']), 1).'% vs last month');
        $metrics = [
            ['Actual Expense',$s['expenses'],'metric-indigo','fa-wallet',$changeText,'expense'],
            ['Investment',$s['investments'],'metric-green','fa-chart-line','Separate from spending','investment'],
            ['Expense + Investment',$s['total_expense_including_investment'],'metric-slate','fa-coins','Total tracked outflow','total'],
            ['Need',$s['need'],'metric-blue','fa-home','Essential spending','need'],
            ['Want / Saveable',$s['want'],'metric-orange','fa-piggy-bank','Potential savings','want'],
            ['Cashback',$s['cashback'],'metric-pink','fa-gift','Earned this month','cashback'],
        ];
    @endphp
    <div class="row">
        @foreach($metrics as [$label,$value,$class,$icon,$note,$cardKey])
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card metric-card {{ $class }}" role="button" tabindex="0" style="cursor:pointer" data-card="{{ $cardKey }}" data-label="{{ $label }}" title="Click for details"><div class="card-body">
                <div class="d-flex justify-content-between"><div class="metric-label">{{ $label }}</div><i class="fas {{ $icon }}"></i></div>
                <div class="metric-value">Rs. {{ number_format($value, 2) }}</div><div class="metric-note">{{ $note }}</div>
            </div></div>
        </div>
        @endforeach
    </div>

    <div class="row">
        @foreach([
            ['Transactions',number_format($s['transactions']),'Expense entries'],
            ['Daily Average','Rs. '.number_format($s['daily_average'],2),'For selected month'],
            ['Highest Expense','Rs. '.number_format($s['highest_expense'],2),'Single transaction'],
            ['Last Month','Rs. '.number_format($s['previous_expenses'],2),'Comparable spending'],
        ] as [$label,$value,$note])
        <div class="col-md-3"><div class="card section-card"><div class="card-body">
            <div class="text-muted small text-uppercase">{{ $label }}</div><h3 class="mb-1 mt-2">{{ $value }}</h3><small class="text-muted">{{ $note }}</small>
        </div></div></div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-8"><div class="card section-card"><div class="card-header"><h5 class="mb-0">6-Month Spending Trend</h5></div><div class="card-body"><div class="chart-box"><canvas id="monthlyTrendChart"></canvas></div></div></div></div>
        <div class="col-lg-4"><div class="card section-card"><div class="card-header"><h5 class="mb-0">Category Breakdown</h5></div><div class="card-body"><div class="chart-box"><canvas id="categoryChart"></canvas></div></div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-8"><div class="card section-card"><div class="card-header"><h5 class="mb-0">Daily Spending</h5></div><div class="card-body"><div class="chart-box"><canvas id="dailyTrendChart"></canvas></div></div></div></div>
        <div class="col-lg-4"><div class="card section-card"><div class="card-header"><h5 class="mb-0">Need vs Want</h5></div><div class="card-body"><div class="chart-box"><canvas id="natureChart"></canvas></div></div></div></div>
    </div>

    <div class="row">
        @foreach([
            ['Expense For','expenseForChart'],['Payment Method','paymentMethodChart'],['Payment Platform','platformChart'],['Account Wise','accountChart']
        ] as [$title,$id])
        <div class="col-lg-6"><div class="card section-card"><div class="card-header"><h5 class="mb-0">{{ $title }}</h5></div><div class="card-body"><div class="chart-box"><canvas id="{{ $id }}"></canvas></div></div></div></div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-6"><div class="card section-card"><div class="card-header"><h5 class="mb-0">Top Categories</h5></div><div class="card-body p-0">
            <table class="table table-hover mb-0"><thead><tr><th>Category</th><th>Share</th><th class="text-right">Amount</th></tr></thead><tbody>
            @foreach($dashboard['top_categories'] as $row)<tr><td>{{ $row['name'] }}</td><td style="width:32%"><div class="progress"><div class="progress-bar bg-info" style="width:{{ min(100,$row['percentage']) }}%"></div></div><small>{{ $row['percentage'] }}%</small></td><td class="text-right">Rs. {{ number_format($row['total'],2) }}</td></tr>@endforeach
            </tbody></table>
        </div></div></div>
        <div class="col-lg-6"><div class="card section-card"><div class="card-header"><h5 class="mb-0">Top Payees</h5></div><div class="card-body p-0">
            @if($dashboard['top_vendors'])<table class="table table-hover mb-0"><thead><tr><th>Payee</th><th>Entries</th><th class="text-right">Amount</th></tr></thead><tbody>
            @foreach($dashboard['top_vendors'] as $row)<tr><td>{{ $row['name'] }}</td><td>{{ $row['transactions'] }}</td><td class="text-right">Rs. {{ number_format($row['total'],2) }}</td></tr>@endforeach
            </tbody></table>@else<div class="empty-state">Vendor/Payee add karne par report yahan dikhegi.</div>@endif
            @if($dashboard['unassigned_vendor_total']>0)<div class="alert alert-warning m-3 mb-0">Payee not tagged: Rs. {{ number_format($dashboard['unassigned_vendor_total'],2) }}</div>@endif
        </div></div></div>
    </div>

    <div class="card section-card"><div class="card-header d-flex flex-wrap justify-content-between align-items-center"><h5 class="mb-2 mb-md-0">Budget Tracker</h5>
        <form method="POST" action="{{ route('month-end.budgets.store') }}" class="form-inline">@csrf<input type="hidden" name="month" value="{{ $dashboard['month'] }}">
            <select name="category_id" class="form-control form-control-sm mr-2" required><option value="">Category</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->full_name }}</option>@endforeach</select>
            <input name="amount" type="number" min="1" step=".01" class="form-control form-control-sm mr-2" placeholder="Monthly limit" required><button class="btn btn-sm btn-primary">Save Budget</button>
        </form></div><div class="card-body p-0">
        @if($dashboard['budget_alerts'])<table class="table table-hover mb-0"><thead><tr><th>Category</th><th>Used / Limit</th><th>Remaining</th><th>Status</th></tr></thead><tbody>
        @foreach($dashboard['budget_alerts'] as $b)<tr><td>{{ $b['category'] }}</td><td>Rs. {{ number_format($b['actual'],2) }} / {{ number_format($b['limit'],2) }}</td><td class="{{ $b['remaining']<0?'text-danger':'text-success' }}">Rs. {{ number_format($b['remaining'],2) }}</td><td style="min-width:180px"><div class="progress"><div class="progress-bar {{ $b['status']==='over'?'bg-danger':($b['status']==='warning'?'bg-warning':'bg-success') }}" style="width:{{ min(100,$b['percentage']) }}%"></div></div><small>{{ $b['percentage'] }}% {{ ucfirst($b['status']) }}</small></td></tr>@endforeach
        </tbody></table>@else<div class="empty-state">No budgets set for this month.</div>@endif
    </div></div>

    <div class="row">
        <div class="col-lg-8"><div class="card section-card"><div class="card-header"><h5 class="mb-0">Day-wise Spending Heat Map</h5></div><div class="card-body">
            @php $heatMax=max(1,collect($dashboard['day_heatmap'])->max('total')); @endphp
            <div class="heat-row">@foreach($dashboard['day_heatmap'] as $day)@php $opacity=.28+(.72*$day['total']/$heatMax); @endphp<div class="heat-day" style="background:rgba(79,70,229,{{ $opacity }})"><strong>{{ $day['label'] }}</strong><small class="d-block">Rs. {{ number_format($day['total'],0) }}</small></div>@endforeach</div>
        </div></div></div>
        <div class="col-lg-4"><div class="card section-card"><div class="card-header"><h5 class="mb-0">Weekday vs Weekend</h5></div><div class="card-body"><div class="chart-box" style="height:190px"><canvas id="weekChart"></canvas></div></div></div></div>
    </div>

    <div class="card section-card"><div class="card-header"><h5 class="mb-0">Monthly Spending Calendar</h5></div><div class="card-body">
        <div class="calendar-grid">@foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)<div class="calendar-head">{{ $day }}</div>@endforeach
        @for($blank=0;$blank<$dashboard['calendar_start_offset'];$blank++)<div></div>@endfor
        @for($day=1;$day<=$dashboard['calendar_days'];$day++)@php $cell=$dashboard['calendar'][$day]??null; @endphp<div class="calendar-day {{ $cell?'has-spend':'' }}"><strong>{{ $day }}</strong>@if($cell)<small>Rs. {{ number_format($cell['total'],0) }}<br>{{ $cell['transactions'] }} entries</small>@endif</div>@endfor
        </div>
    </div></div>

    <div class="row">
        <div class="col-lg-6"><div class="card section-card"><div class="card-header"><h5 class="mb-0">Recurring Expenses</h5></div><div class="card-body p-0">
            @if($dashboard['recurring_expenses'])<table class="table table-hover mb-0"><thead><tr><th>Expense</th><th>Frequency</th><th class="text-right">Amount</th></tr></thead><tbody>@foreach($dashboard['recurring_expenses'] as $row)<tr><td>{{ $row['title'] }}<small class="d-block text-muted">{{ $row['category'] }} · {{ $row['occurrences'] }} entries</small></td><td>{{ ucfirst($row['frequency']) }}</td><td class="text-right">Rs. {{ number_format($row['total'],2) }}</td></tr>@endforeach</tbody></table>@else<div class="empty-state">No recurring expenses.</div>@endif
        </div></div></div>
        <div class="col-lg-6"><div class="card section-card"><div class="card-header"><h5 class="mb-0">Savings Opportunities</h5></div><div class="card-body">
            @forelse($dashboard['savings_opportunities'] as $row)<div class="opportunity"><strong>{{ $row['name'] }}</strong><span class="float-right">Save up to Rs. {{ number_format($row['estimated_saving'],2) }}</span><small class="d-block text-muted mt-1">Current Rs. {{ number_format($row['current'],2) }} vs last month Rs. {{ number_format($row['previous'],2) }}</small></div>@empty<div class="empty-state">Previous month data ke baad savings comparison dikhega.</div>@endforelse
        </div></div>
        <div class="card section-card"><div class="card-header"><h5 class="mb-0">Cashback by Platform</h5></div><div class="card-body p-0">@forelse($dashboard['cashback_by_platform'] as $row)<div class="d-flex justify-content-between border-bottom p-3"><span>{{ $row['name'] }}</span><strong>Rs. {{ number_format($row['total'],2) }}</strong></div>@empty<div class="empty-state">No cashback recorded.</div>@endforelse</div></div></div>
    </div>

    <div class="row">
        @foreach([['Recent Expenses','recent_expenses'],['Highest Expenses','highest_expenses']] as [$title,$key])<div class="col-lg-6"><div class="card section-card"><div class="card-header"><h5 class="mb-0">{{ $title }}</h5></div><div class="card-body p-0"><table class="table table-hover mb-0"><thead><tr><th>Date / Expense</th><th>Category</th><th class="text-right">Amount</th></tr></thead><tbody>@foreach($dashboard[$key] as $row)<tr><td>{{ $row['date'] }}<small class="d-block text-muted">{{ $row['description'] }}{{ $row['vendor']?' · '.$row['vendor']:'' }}</small></td><td>{{ $row['category'] }}</td><td class="text-right">Rs. {{ number_format($row['total'],2) }}</td></tr>@endforeach</tbody></table></div></div></div>@endforeach
    </div>
</div>
</div>

<div class="modal fade" id="cardDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cardDetailTitle"></h5>
                <a href="#" id="cardExportBtn" class="btn btn-sm btn-outline-success ml-auto mr-3"><i class="fas fa-file-excel"></i> Export Excel</a>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="cardDetailBody"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/libs/chart-js/Chart.bundle.min.js') }}"></script>
<script>
(function () {
    var month = @json($dashboard['month']);
    var base = @json(url('/month-end-dashboard/card'));
    var fmt = function (n) { return 'Rs. ' + Number(n).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };
    var esc = function (t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; };

    function open(card, label) {
        $('#cardDetailTitle').text(label + ' — ' + @json($dashboard['period_label']));
        $('#cardDetailBody').html('<p class="text-muted">Loading...</p>');
        $('#cardExportBtn').attr('href', base + '/' + card + '/export?month=' + month);
        $('#cardDetailModal').modal('show');
        fetch(base + '/' + card + '?month=' + month, {headers: {'Accept': 'application/json'}})
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (d) {
                var html = '<p><strong>' + fmt(d.total) + '</strong> across <strong>' + d.count + '</strong> transactions</p>';
                if (!d.rows.length) { $('#cardDetailBody').html(html + '<p class="text-muted">No transactions.</p>'); return; }
                html += '<h6>By category</h6><table class="table table-sm"><tbody>';
                d.by_category.forEach(function (c) {
                    html += '<tr><td>' + esc(c.name) + '</td><td class="text-muted">' + c.transactions + ' txn</td><td class="text-right">' + fmt(c.total) + '</td></tr>';
                });
                html += '</tbody></table><h6 class="mt-3">Transactions</h6><table class="table table-sm table-hover"><thead><tr><th>Date</th><th>Description</th><th>Category</th><th class="text-right">Amount</th></tr></thead><tbody>';
                d.rows.forEach(function (r) {
                    html += '<tr><td>' + esc(r.date) + '</td><td>' + esc(r.description) + (r.vendor ? '<small class="d-block text-muted">' + esc(r.vendor) + '</small>' : '') +
                        '</td><td>' + esc(r.category) + (r.subcategory && r.subcategory !== r.category ? '<small class="d-block text-muted">' + esc(r.subcategory) + '</small>' : '') +
                        '</td><td class="text-right">' + fmt(r.total) + '</td></tr>';
                });
                html += '</tbody></table>';
                if (d.count > d.rows.length) html += '<p class="text-muted small">Showing latest ' + d.rows.length + ' of ' + d.count + '.</p>';
                $('#cardDetailBody').html(html);
            })
            .catch(function (e) { $('#cardDetailBody').html('<p class="text-danger">Could not load details: ' + esc(e.message) + '</p>'); });
    }

    document.querySelectorAll('.metric-card[data-card]').forEach(function (el) {
        el.addEventListener('click', function () { open(el.dataset.card, el.dataset.label); });
        el.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(el.dataset.card, el.dataset.label); } });
    });
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const colors = ['#4f46e5','#10b981','#f59e0b','#0ea5e9','#ec4899','#8b5cf6','#14b8a6','#f97316','#64748b','#ef4444'];
    const rupees = value => 'Rs. ' + Number(value || 0).toLocaleString('en-IN');
    function chart(id, type, rows, options) {
        const canvas = document.getElementById(id); if (!canvas || !rows || !rows.length) return;
        return new Chart(canvas.getContext('2d'), {type:type,data:{labels:rows.map(r=>r.label||r.name),datasets:[{label:'Amount',data:rows.map(r=>r.total),backgroundColor:type==='line'?'rgba(79,70,229,.14)':colors,borderColor:'#4f46e5',borderWidth:2,pointBackgroundColor:'#4f46e5',fill:type==='line',lineTension:.28}]},options:Object.assign({responsive:true,maintainAspectRatio:false,legend:{display:type==='doughnut'},tooltips:{callbacks:{label:(item,data)=>{const value=type==='doughnut'?data.datasets[item.datasetIndex].data[item.index]:(type==='horizontalBar'?item.xLabel:item.yLabel);return rupees(value)}}}},options||{})});
    }
    chart('monthlyTrendChart','line',@json($dashboard['monthly_trend']),{scales:{yAxes:[{ticks:{beginAtZero:true,callback:rupees}}]}});
    chart('dailyTrendChart','line',@json($dashboard['daily_trend']),{scales:{yAxes:[{ticks:{beginAtZero:true,callback:rupees}}]}});
    chart('categoryChart','doughnut',@json($dashboard['top_categories']),{cutoutPercentage:62});
    chart('natureChart','doughnut',[
        {name:'Need',total:@json($s['need'])},{name:'Want',total:@json($s['want'])},{name:'Unclassified',total:@json($s['unclassified'])}
    ],{cutoutPercentage:65});
    const horizontal={legend:{display:false},scales:{xAxes:[{ticks:{beginAtZero:true,callback:rupees}}]}};
    chart('expenseForChart','horizontalBar',@json($dashboard['expense_for']),horizontal);
    chart('paymentMethodChart','horizontalBar',@json($dashboard['payment_methods']),horizontal);
    chart('platformChart','horizontalBar',@json($dashboard['payment_platforms']),horizontal);
    chart('accountChart','horizontalBar',@json($dashboard['accounts']),horizontal);
    chart('weekChart','doughnut',[{name:'Weekdays',total:@json($dashboard['weekday_weekend']['weekday'])},{name:'Weekends',total:@json($dashboard['weekday_weekend']['weekend'])}],{cutoutPercentage:65});
});
</script>
@endpush
