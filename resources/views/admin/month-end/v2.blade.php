@extends('layoutsnew.app')
@section('title', 'Financial Command Center')

@push('styles')
<style>
.fin-v2{--bg:#f4f7fb;--card:#fff;--ink:#14213d;--muted:#718096;--line:#e8edf5;--purple:#635bff;--green:#0caf83;--orange:#ff9f43;background:var(--bg);min-height:100vh;padding-bottom:70px;transition:.25s}
.fin-v2.dark{--bg:#0d1321;--card:#151e2f;--ink:#edf2f7;--muted:#9aa8bd;--line:#27344a}
.fin-v2 .premium-card{background:var(--card);border:1px solid var(--line);border-radius:20px;box-shadow:0 8px 30px rgba(20,33,61,.07);color:var(--ink)}
.fin-v2 .hero{background:radial-gradient(circle at 85% 10%,rgba(255,255,255,.2),transparent 28%),linear-gradient(125deg,#171a4a,#4f46e5 52%,#8b5cf6);border-radius:25px;padding:28px;color:#fff;box-shadow:0 20px 45px rgba(79,70,229,.25)}
.fin-v2 .eyebrow{font-size:.72rem;letter-spacing:.13em;text-transform:uppercase;opacity:.72}.fin-v2 .hero-value{font-size:2.35rem;font-weight:800;letter-spacing:-.04em}
.fin-v2 .score-ring{width:150px;height:150px;border-radius:50%;display:grid;place-items:center;position:relative;background:conic-gradient(#62e6b5 calc(var(--score)*1%),rgba(255,255,255,.16) 0)}
.fin-v2 .score-ring:before{content:"";position:absolute;inset:11px;border-radius:50%;background:#24216b}.fin-v2 .score-content{position:relative;text-align:center}.fin-v2 .score-number{font-size:2.1rem;font-weight:800}
.fin-v2 .kpi{padding:19px;min-height:122px;position:relative;overflow:hidden}.fin-v2 .kpi-icon{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;background:#eeedff;color:var(--purple)}
.fin-v2 .kpi-value{font-size:1.45rem;font-weight:800;margin-top:13px}.fin-v2 .label{color:var(--muted);font-size:.74rem;text-transform:uppercase;letter-spacing:.06em}
.fin-v2 .section-title{font-weight:750;margin:0}.fin-v2 .story-line{padding:12px 0;border-bottom:1px solid var(--line)}.fin-v2 .story-line:last-child{border:0}
.fin-v2 .category-tile{padding:14px;border:1px solid var(--line);border-radius:15px;margin-bottom:10px}.fin-v2 .category-icon{width:37px;height:37px;border-radius:11px;background:#eef2ff;color:#4f46e5;display:grid;place-items:center}
.fin-v2 .thin-progress{height:7px;background:var(--line);border-radius:20px;overflow:hidden}.fin-v2 .thin-progress span{display:block;height:100%;border-radius:20px;background:linear-gradient(90deg,#635bff,#9f7aea)}
.fin-v2 .nature-bar{height:15px;border-radius:30px;background:var(--line);overflow:hidden;display:flex}.fin-v2 .nature-bar .need{background:#0caf83}.fin-v2 .nature-bar .want{background:#ff9f43}.fin-v2 .nature-bar .unknown{background:#94a3b8}
.fin-v2 .mini-row{padding:12px 0;border-bottom:1px solid var(--line)}.fin-v2 .mini-row:last-child{border:0}.fin-v2 table{color:var(--ink)}.fin-v2 .table td,.fin-v2 .table th{border-color:var(--line)}
.fin-v2 .suggestion{background:linear-gradient(135deg,#fff7e6,#fff);border:1px solid #ffe0a6;border-radius:15px;padding:15px;color:#523b10}.fin-v2.dark .suggestion{background:#2a251d;color:#fbd38d;border-color:#544526}
.fin-v2 .theme-toggle{border:1px solid rgba(255,255,255,.3);background:rgba(255,255,255,.12);color:#fff;border-radius:30px;padding:8px 13px}
.quick-fab{position:fixed;right:28px;bottom:28px;z-index:50}.quick-fab>button{width:58px;height:58px;border:0;border-radius:50%;background:linear-gradient(135deg,#635bff,#8b5cf6);color:#fff;font-size:25px;box-shadow:0 12px 28px rgba(99,91,255,.4)}
.quick-menu{display:none;position:absolute;right:0;bottom:68px;background:var(--card);border:1px solid var(--line);border-radius:16px;padding:10px;width:175px;box-shadow:0 12px 35px rgba(0,0,0,.15)}.quick-menu.show{display:block}.quick-menu a{display:block;padding:9px 11px;color:var(--ink);border-radius:9px}.quick-menu a:hover{background:var(--line)}
.fin-v2 .chart-wrap{height:290px}.fin-v2 .health-part{margin-bottom:12px}.fin-v2 .badge-soft{background:#eeedff;color:#4f46e5;border-radius:20px;padding:5px 10px}
@media(max-width:767px){.fin-v2 .hero{padding:20px}.fin-v2 .hero-value{font-size:1.8rem}.fin-v2 .score-ring{width:125px;height:125px;margin-top:20px}.quick-fab{right:18px;bottom:18px}}
</style>
@endpush

@section('content')
@php
$s=$dashboard['summary'];$h=$dashboard['health_score'];$natureTotal=max(1,$s['need']+$s['want']+$s['unclassified']);
$needPct=round($s['need']/$natureTotal*100,1);$wantPct=round($s['want']/$natureTotal*100,1);$unknownPct=max(0,100-$needPct-$wantPct);
$change=$s['change_percentage'];$better=$change!==null&&$change<0;
@endphp
<div class="content fin-v2" id="financialDashboardV2"><div class="container-fluid py-4">
    <div class="hero mb-4"><div class="row align-items-center">
        <div class="col-lg-8"><div class="d-flex justify-content-between align-items-start"><div><div class="eyebrow">Financial command center</div><h2 class="text-white mt-2 mb-1">Good {{ now()->hour<12?'Morning':(now()->hour<17?'Afternoon':'Evening') }}, {{ auth()->user()->name }} 👋</h2><div style="opacity:.75">{{ $dashboard['period_label'] }}</div></div><button id="themeToggle" class="theme-toggle"><i class="fas fa-moon"></i></button></div>
            <div class="mt-4"><div class="eyebrow">Total Expense</div><div class="hero-value">Rs. {{ number_format($s['expenses'],2) }}</div><div class="mt-2">@if($change===null)<span style="opacity:.75">Start tracking to unlock comparison</span>@else<span class="badge badge-{{ $better?'success':'warning' }} p-2">{{ $better?'↓':'↑' }} {{ number_format(abs($change),1) }}% vs last month</span>@endif</div></div>
        </div>
        <div class="col-lg-4 d-flex justify-content-lg-end"><div class="score-ring" style="--score:{{ $h['score'] }}"><div class="score-content"><div class="score-number">{{ $h['score'] }}</div><div>/ 100</div><strong>{{ $h['status'] }}</strong></div></div></div>
    </div></div>

    <div class="row">
        @foreach([
          ['Investment',$s['investments'],'fa-chart-line','Money building wealth'],['Need',$s['need'],'fa-home',$needPct.'% of spending'],['Want',$s['want'],'fa-shopping-bag',$wantPct.'% saveable'],['Cashback',$s['cashback'],'fa-gift','Rewards earned'],['Daily Average',$s['daily_average'],'fa-calendar-day','Per active day'],['Transactions',$s['transactions'],'fa-receipt','Expense entries']
        ] as [$label,$value,$icon,$note])
        <div class="col-xl-2 col-md-4 col-6"><div class="premium-card kpi mb-3"><div class="d-flex justify-content-between"><span class="label">{{ $label }}</span><span class="kpi-icon"><i class="fas {{ $icon }}"></i></span></div><div class="kpi-value">{{ in_array($label,['Transactions'])?number_format($value):'Rs. '.number_format($value,2) }}</div><small class="text-muted">{{ $note }}</small></div></div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-8"><div class="premium-card p-4 mb-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><div class="label">Analytics</div><h4 class="section-title">Spending Trend</h4></div><span class="badge-soft">6 months</span></div><div class="chart-wrap"><canvas id="v2Trend"></canvas></div></div></div>
        <div class="col-lg-4"><div class="premium-card p-4 mb-4"><div class="label">At a glance</div><h4 class="section-title mb-3">This Month Story</h4>@foreach($dashboard['story'] as $line)<div class="story-line"><i class="fas fa-sparkles text-primary mr-2"></i>{{ $line }}</div>@endforeach</div></div>
    </div>

    <div class="row">
        <div class="col-lg-5"><div class="premium-card p-4 mb-4"><div class="label">Where money went</div><h4 class="section-title mb-3">Top Categories</h4>@foreach(array_slice($dashboard['top_categories'],0,6) as $i=>$row)<div class="category-tile"><div class="d-flex align-items-center"><span class="category-icon mr-3"><i class="fas {{ ['fa-shopping-basket','fa-home','fa-car','fa-heartbeat','fa-utensils','fa-shopping-bag'][$i]??'fa-tag' }}"></i></span><div class="flex-grow-1"><div class="d-flex justify-content-between"><strong>{{ $row['name'] }}</strong><strong>Rs. {{ number_format($row['total'],0) }}</strong></div><div class="thin-progress mt-2"><span style="width:{{ min(100,$row['percentage']) }}%"></span></div><small class="text-muted">{{ $row['percentage'] }}%</small></div></div></div>@endforeach</div></div>
        <div class="col-lg-3"><div class="premium-card p-4 mb-4"><div class="label">Spending quality</div><h4 class="section-title mb-4">Need vs Want</h4><div class="nature-bar"><span class="need" style="width:{{ $needPct }}%"></span><span class="want" style="width:{{ $wantPct }}%"></span><span class="unknown" style="width:{{ $unknownPct }}%"></span></div><div class="d-flex justify-content-between mt-4"><div><span class="text-success">●</span> Need<br><strong>{{ $needPct }}%</strong></div><div class="text-right"><span class="text-warning">●</span> Want<br><strong>{{ $wantPct }}%</strong></div></div><hr><div class="label mb-3">Expense For</div>@foreach($dashboard['expense_for'] as $row)<div class="mini-row d-flex justify-content-between"><span>{{ $row['name'] }}</span><strong>Rs. {{ number_format($row['total'],0) }}</strong></div>@endforeach</div></div>
        <div class="col-lg-4"><div class="premium-card p-4 mb-4"><div class="label">Score explained</div><h4 class="section-title mb-3">Financial Health</h4><p class="text-muted">{{ $h['message'] }}</p>@foreach($h['components'] as $part)@php $pct=round($part['points']/$part['max']*100); @endphp<div class="health-part"><div class="d-flex justify-content-between"><span>{{ $part['label'] }}</span><strong>{{ $part['points'] }}/{{ $part['max'] }}</strong></div><div class="thin-progress mt-2"><span style="width:{{ $pct }}%"></span></div></div>@endforeach</div></div>
    </div>

    <div class="row">
        <div class="col-lg-7"><div class="premium-card mb-4"><div class="p-4 pb-2"><div class="label">Control spending</div><h4 class="section-title">Budget Gauge</h4></div>@forelse($dashboard['budget_alerts'] as $b)<div class="px-4 py-3 border-top" style="border-color:var(--line)!important"><div class="d-flex justify-content-between"><strong>{{ $b['category'] }}</strong><span class="badge badge-{{ $b['status']==='over'?'danger':($b['status']==='warning'?'warning':'success') }}">{{ ucfirst($b['status']) }}</span></div><div class="d-flex justify-content-between text-muted small mt-2"><span>Rs. {{ number_format($b['actual'],0) }} used</span><span>Rs. {{ number_format($b['limit'],0) }} goal</span></div><div class="thin-progress mt-2"><span style="width:{{ min(100,$b['percentage']) }}%;background:{{ $b['status']==='over'?'#ef4444':($b['status']==='warning'?'#f59e0b':'#10b981') }}"></span></div></div>@empty<div class="p-4 text-muted">Set category budgets on the classic dashboard to improve your score.</div>@endforelse</div></div>
        <div class="col-lg-5"><div class="premium-card p-4 mb-4"><div class="label">Habit signal</div><h4 class="section-title mb-3">Weekly Heatmap</h4>@php $heatMax=max(1,collect($dashboard['day_heatmap'])->max('total')); @endphp @foreach($dashboard['day_heatmap'] as $day)<div class="mini-row d-flex align-items-center"><strong style="width:42px">{{ $day['label'] }}</strong><div class="thin-progress flex-grow-1"><span style="width:{{ round($day['total']/$heatMax*100) }}%"></span></div><span class="ml-3" style="width:85px;text-align:right">Rs. {{ number_format($day['total'],0) }}</span></div>@endforeach</div></div>
    </div>

    <div class="row">
        <div class="col-lg-5"><div class="premium-card p-4 mb-4"><div class="label">Smart coach</div><h4 class="section-title mb-3">Savings Suggestions</h4>@forelse($dashboard['savings_opportunities'] as $row)<div class="suggestion mb-3"><strong>{{ $row['name'] }}</strong><div class="mt-1">Reduce the increase to save <strong>Rs. {{ number_format($row['estimated_saving'],0) }}/month</strong>.</div></div>@empty<div class="suggestion">Add another month of data to unlock comparison-based saving suggestions.</div>@endforelse</div></div>
        <div class="col-lg-7"><div class="premium-card mb-4"><div class="p-4 pb-2"><div class="label">Latest activity</div><h4 class="section-title">Recent Expenses</h4></div><div class="table-responsive"><table class="table mb-0"><tbody>@foreach(array_slice($dashboard['recent_expenses'],0,7) as $row)<tr><td><strong>{{ $row['description'] }}</strong><small class="d-block text-muted">{{ $row['date'] }} · {{ $row['category'] }}</small></td><td>{{ $row['vendor']??'Payee not tagged' }}</td><td class="text-right"><strong>Rs. {{ number_format($row['total'],2) }}</strong></td></tr>@endforeach</tbody></table></div></div></div>
    </div>

    <div class="text-center"><a href="{{ route('month-end.index',['month'=>$dashboard['month']]) }}" class="btn btn-outline-secondary">Open detailed classic dashboard</a></div>
</div>
<div class="quick-fab"><div class="quick-menu" id="quickMenu">@foreach($dashboard['quick_add'] as $item)<a href="{{ $item['url'] }}">+ {{ $item['label'] }}</a>@endforeach<a href="{{ route('expense.create') }}">+ Custom Expense</a></div><button id="quickButton" aria-label="Quick add">+</button></div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/libs/chart-js/Chart.bundle.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const root=document.getElementById('financialDashboardV2'),toggle=document.getElementById('themeToggle'),quick=document.getElementById('quickButton'),menu=document.getElementById('quickMenu');
 if(localStorage.getItem('financeTheme')==='dark')root.classList.add('dark');
 toggle.addEventListener('click',()=>{root.classList.toggle('dark');localStorage.setItem('financeTheme',root.classList.contains('dark')?'dark':'light')});
 quick.addEventListener('click',()=>menu.classList.toggle('show'));
 const rows=@json($dashboard['monthly_trend']);new Chart(document.getElementById('v2Trend').getContext('2d'),{type:'line',data:{labels:rows.map(r=>r.label),datasets:[{data:rows.map(r=>r.total),label:'Expense',borderColor:'#635bff',backgroundColor:'rgba(99,91,255,.12)',pointBackgroundColor:'#fff',pointBorderColor:'#635bff',pointBorderWidth:3,pointRadius:5,borderWidth:3,lineTension:.35,fill:true}]},options:{responsive:true,maintainAspectRatio:false,legend:{display:false},scales:{yAxes:[{ticks:{beginAtZero:true,callback:v=>'Rs. '+Number(v).toLocaleString('en-IN')},gridLines:{color:'rgba(148,163,184,.12)'}}],xAxes:[{gridLines:{display:false}}]},tooltips:{callbacks:{label:i=>'Rs. '+Number(i.yLabel).toLocaleString('en-IN')}}}});
});
</script>
@endpush
