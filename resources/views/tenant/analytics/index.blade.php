{{--
Design Read: B2B SaaS analytics dashboard for studio admin (tenant).
Audience: Studio owner monitoring their own revenue, expense, profit.
Visual language: Dark‑mode studio‑professional (slate + copper palette, Plus Jakarta Sans).
Dial: ENERGY 2 (balanced) / RHYTHM 2 (consistent with few breaks) / MOTION 1 (hover‑only).
Purpose: Financial clarity with period comparison; copper accent for revenue, rose for expense, emerald for profit.
--}}

{{--
Technique Purposes (R-31):
- Radial glow hover on KPI cards: depth cue to mark interactive surface.
- Gradient bar fills (revenue amber→copper, expense rose→red): color‑code metric identity.
- Gradient line area fill (profit emerald fade): visual weight for trend direction.
- SVG icons per KPI: semantic mapping (money, arrow down, arrow up, calendar).
- Asymmetric chart split (60/40): revenue comparison needs width; profit trend secondary.
- Segmented period control: compact, single‑tap, thumb‑friendly on mobile.
- Delta badges (↑/↓ %): immediate period-over-period context.
- Copper accent ring on KPI icon containers: ties icon to metric color.
- Chart tooltips dark theme + rupiah format: matches page theme; locale‑aware currency.
--}}
@extends('layouts.tenant')

@section('title', 'Analytics')
@section('page_title', 'Analytics')

@section('content')
<div class="space-y-6 pb-8">

 {{-- ── Header ────────────────────────────────────────────────── --}}
 <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
 <div>
 <h1 class="text-2xl font-bold tracking-tight text-base-content">Analytics Studio</h1>
 <p class="text-sm text-base-content/50 mt-0.5">Pantau keuangan dan performa studio Anda.</p>
 </div>
 <form method="POST" action="{{ route('tenant.analytics.export') }}">
 @csrf
 <input type="hidden" name="period" value="{{ $period }}">
 <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
 <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
 <button type="submit"
 class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] transition-all duration-300 cursor-pointer">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
 Export CSV
 </button>
 </form>
 </div>

 {{-- ── Filter Bar ────────────────────────────────────────────── --}}
 <form id="analyticsFilterForm" method="GET" action="{{ route('tenant.analytics') }}">
 <input type="hidden" name="period" id="periodInput" value="{{ $period }}">

 <div class="flex flex-wrap items-end gap-3 bg-base-100 border border-base-300 rounded-xl px-5 py-4">

 {{-- Period Segmented --}}
 <div class="flex items-center gap-1 bg-base-200 rounded-lg p-1">
 @foreach(['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'] as $val => $label)
 <button type="button" onclick="setPeriodRange('{{ $val }}')"
 class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-150 cursor-pointer
 {{ $period === $val ? 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)]' : 'text-base-content/60 hover:text-base-content' }}">
 {{ $label }}
 </button>
 @endforeach
 </div>

 <div class="flex flex-col gap-1">
 <label class="text-xs text-base-content/50 font-medium">Mulai</label>
 <input type="date" name="start_date" id="startDateInput" value="{{ $startDate->format('Y-m-d') }}"
 class="input input-sm bg-base-200 border-base-300 text-base-content focus:border-emerald-500 text-sm">
 </div>
 <div class="flex flex-col gap-1">
 <label class="text-xs text-base-content/50 font-medium">Hingga</label>
 <input type="date" name="end_date" id="endDateInput" value="{{ $endDate->format('Y-m-d') }}"
 class="input input-sm bg-base-200 border-base-300 text-base-content focus:border-emerald-500 text-sm">
 </div>

 <button type="submit"
 class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] text-sm font-semibold transition-all duration-300 cursor-pointer self-end">
 Filter
 </button>
 </div>
 </form>

 {{-- ── KPI Cards ─────────────────────────────────────────────── --}}
 <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
 @php
 $kpis = [
 [
 'label' => 'Pemasukan',
 'value' => 'Rp '.number_format($totalRevenue,0,',','.'),
 'change' => $revenueChange,
 'color' => 'text-amber-400',
 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
 'ring' => 'rgba(217,119,6,.15)',
 ],
 [
 'label' => 'Pengeluaran',
 'value' => 'Rp '.number_format($totalExpense,0,',','.'),
 'change' => $expenseChange,
 'color' => 'text-rose-400',
 'icon' => 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6',
 'ring' => 'rgba(244,63,94,.15)',
 ],
 [
 'label' => 'Laba Bersih',
 'value' => 'Rp '.number_format($totalProfit,0,',','.'),
 'change' => $profitChange,
 'color' => 'text-emerald-400',
 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
 'ring' => 'rgba(52,211,153,.15)',
 ],
 [
 'label' => 'Total Booking',
 'value' => number_format($totalBookings),
 'change' => $bookingChange,
 'color' => 'text-sky-400',
 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
 'ring' => 'rgba(56,189,248,.15)',
 ],
 ];
 @endphp

 @foreach($kpis as $kpi)
 <div class="relative bg-base-100 border border-base-300 rounded-xl p-5 overflow-hidden group hover:border-base-300/80 transition-colors duration-200">
 <div class="absolute inset-0 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"
 style="background: radial-gradient(60% 60% at 80% 20%, {{ $kpi['ring'] }}, transparent)"></div>
 <div class="relative flex items-start justify-between gap-3">
 <div>
 <p class="text-xs text-base-content/50 font-medium uppercase tracking-wider mb-2">{{ $kpi['label'] }}</p>
 <p class="text-2xl font-bold {{ $kpi['color'] }} leading-none">{{ $kpi['value'] }}</p>
 <p class="text-xs font-semibold mt-2 flex items-center gap-1 {{ $kpi['change'] >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
 <span>{{ $kpi['change'] >= 0 ? '↑' : '↓' }}</span>
 <span>{{ abs($kpi['change']) }}%</span>
 <span class="text-base-content/40 font-normal">vs periode lalu</span>
 </p>
 </div>
 <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background: {{ $kpi['ring'] }}">
 <svg class="w-5 h-5 {{ $kpi['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $kpi['icon'] }}"/>
 </svg>
 </div>
 </div>
 </div>
 @endforeach
 </div>

 {{-- ── Charts ────────────────────────────────────────────────── --}}
 <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
 <div class="lg:col-span-3 bg-base-100 border border-base-300 rounded-xl p-6">
 <div class="flex items-center justify-between mb-6">
 <div>
 <h3 class="font-semibold text-base-content">Pemasukan vs Pengeluaran</h3>
 <p class="text-xs text-base-content/50 mt-0.5">{{ ucfirst($period) }} · {{ $startDate->format('d M') }} – {{ $endDate->format('d M Y') }}</p>
 </div>
 <div class="flex items-center gap-4 text-xs text-base-content/50">
 <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-amber-500 inline-block"></span>Masuk</span>
 <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-rose-500 inline-block"></span>Keluar</span>
 </div>
 </div>
 <div class="h-72"><canvas id="revenueChart"></canvas></div>
 </div>

 <div class="lg:col-span-2 bg-base-100 border border-base-300 rounded-xl p-6">
 <div class="mb-6">
 <h3 class="font-semibold text-base-content">Trend Laba</h3>
 <p class="text-xs text-base-content/50 mt-0.5">Laba bersih per periode</p>
 </div>
 <div class="h-72"><canvas id="profitChart"></canvas></div>
 </div>
 </div>

 {{-- ── Expense Categories ────────────────────────────────────── --}}
 @if($expenseCategories->isNotEmpty())
 <div class="bg-base-100 border border-base-300 rounded-xl p-6">
 <h3 class="font-semibold text-base-content mb-5">Pengeluaran per Kategori</h3>
 @php $totalExp = $expenseCategories->sum('total'); @endphp
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
 @foreach($expenseCategories as $i => $cat)
 @php
 $pct = $totalExp > 0 ? round(($cat->total / $totalExp) * 100) : 0;
 $palette = ['#d97706','#f87171','#60a5fa','#34d399','#a78bfa','#fb923c'];
 $col = $palette[$i % count($palette)];
 @endphp
 <div>
 <div class="flex justify-between text-sm mb-1.5">
 <span class="text-base-content/80 font-medium">{{ $cat->category }}</span>
 <div class="flex items-center gap-2">
 <span class="text-xs text-base-content/40">{{ $pct }}%</span>
 <span class="font-semibold text-base-content">Rp {{ number_format($cat->total,0,',','.') }}</span>
 </div>
 </div>
 <div class="w-full bg-base-200 rounded-full h-1.5">
 <div class="h-1.5 rounded-full transition-all duration-500" style="width:{{ $pct }}%; background:{{ $col }};"></div>
 </div>
 </div>
 @endforeach
 </div>
 </div>
 @endif

 {{-- Empty state --}}
 @if(empty($chartData) || count($chartData) === 0)
 <div class="bg-base-100 border border-base-300 rounded-xl py-20 text-center">
 <svg class="w-12 h-12 mx-auto text-base-content/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
 </svg>
 <p class="text-base-content/40 text-sm">Tidak ada data untuk filter yang dipilih.</p>
 </div>
 @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function setPeriodRange(p) {
 const now = new Date();
 let startDate = new Date();
 let endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());

 const pad = (n) => String(n).padStart(2, '0');
 const toYmd = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

 if (p === 'daily') {
 startDate.setDate(now.getDate() - 29);
 } else if (p === 'weekly') {
 startDate.setDate(now.getDate() - 83);
 } else if (p === 'monthly') {
 startDate = new Date(now.getFullYear(), now.getMonth() - 5, 1);
 endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
 }

 const form = document.getElementById('analyticsFilterForm');
 const startInput = document.getElementById('startDateInput');
 const endInput = document.getElementById('endDateInput');
 const periodInput = document.getElementById('periodInput');

 if (startInput) startInput.value = toYmd(startDate);
 if (endInput) endInput.value = toYmd(endDate);
 if (periodInput) periodInput.value = p;

 form.submit();
}

document.addEventListener('DOMContentLoaded', function () {
 const raw = {!! json_encode(array_values($chartData)) !!};
 if (!raw || raw.length === 0) return;

 const labels = raw.map(d => d.label);
 const revenues = raw.map(d => d.revenue);
 const expenses = raw.map(d => d.expense);
 const profits = raw.map(d => d.profit);

 const gridColor = 'rgba(51,65,85,0.5)';
 const tickColor = '#64748b';
 const rupiahFmt = v => 'Rp ' + Number(v).toLocaleString('id-ID');

 const baseOptions = {
 responsive: true,
 maintainAspectRatio: false,
 interaction: { intersect: false, mode: 'index' },
 animation: { duration: 600, easing: 'easeOutQuart' },
 plugins: {
 legend: { display: false },
 tooltip: {
 backgroundColor: '#0f1419',
 titleColor: '#f1f5f9',
 bodyColor: '#94a3b8',
 borderColor: '#334155',
 borderWidth: 1,
 padding: 12,
 cornerRadius: 8,
 displayColors: true,
 boxWidth: 10, boxHeight: 10, boxPadding: 4,
 }
 },
 scales: {
 y: {
 beginAtZero: true,
 ticks: { color: tickColor, font: { size: 11 }, callback: v => rupiahFmt(v) },
 grid: { color: gridColor, drawBorder: false },
 border: { dash: [4,4], display: false },
 },
 x: {
 ticks: { color: tickColor, font: { size: 11 } },
 grid: { display: false },
 border: { display: false },
 }
 }
 };

 /* Bar: Revenue vs Expense */
 const ctx1 = document.getElementById('revenueChart').getContext('2d');
 const gRev = ctx1.createLinearGradient(0,0,0,300);
 gRev.addColorStop(0,'#d97706'); gRev.addColorStop(1,'#b45309');
 const gExp = ctx1.createLinearGradient(0,0,0,300);
 gExp.addColorStop(0,'#f87171'); gExp.addColorStop(1,'#dc2626');

 new Chart(ctx1, {
 type: 'bar',
 data: {
 labels,
 datasets: [
 { label:'Pemasukan', data:revenues, backgroundColor:gRev, borderRadius:6, borderSkipped:false, barPercentage:0.55 },
 { label:'Pengeluaran', data:expenses, backgroundColor:gExp, borderRadius:6, borderSkipped:false, barPercentage:0.55 }
 ]
 },
 options: { ...baseOptions, plugins: { ...baseOptions.plugins, tooltip: { ...baseOptions.plugins.tooltip,
 callbacks: { label: ctx => ' '+ctx.dataset.label+': '+rupiahFmt(ctx.parsed.y) }
 }}}
 });

 /* Line: Profit Trend */
 const ctx2 = document.getElementById('profitChart').getContext('2d');
 const gPro = ctx2.createLinearGradient(0,0,0,280);
 gPro.addColorStop(0,'rgba(52,211,153,0.25)'); gPro.addColorStop(1,'rgba(52,211,153,0)');

 new Chart(ctx2, {
 type: 'line',
 data: {
 labels,
 datasets: [{
 label:'Laba Bersih', data:profits,
 borderColor:'#34d399', backgroundColor:gPro,
 borderWidth:2.5, fill:true, tension:0.45,
 pointBackgroundColor:'#34d399', pointBorderColor:'#0f1419',
 pointBorderWidth:2, pointRadius:4, pointHoverRadius:6,
 pointHoverBackgroundColor:'#34d399', pointHoverBorderColor:'#fff', pointHoverBorderWidth:2,
 }]
 },
 options: { ...baseOptions, plugins: { ...baseOptions.plugins, tooltip: { ...baseOptions.plugins.tooltip,
 callbacks: { label: ctx => { const v=ctx.parsed.y; return ' Laba: '+(v>=0?'+':'')+rupiahFmt(v); }}
 }}}
 });
});
</script>
@endpush
