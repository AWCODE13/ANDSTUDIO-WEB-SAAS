@extends('layouts.tenant')

@section('title', 'Plan & Tagihan')
@section('page_title', 'Plan & Tagihan')

@section('content')
<div>
 <!-- Header -->
 <div class="flex items-center justify-between mb-6">
 <div>
 <h1 class="text-2xl font-bold text-base-content">Plan & Tagihan</h1>
 <p class="text-base-content/60 text-sm mt-1">Kelola paket langganan dan pantau riwayat invoice studio Anda.</p>
 </div>
 <span class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $tenant->subscriptionLabel()['color'] }}">
 {{ $tenant->subscriptionLabel()['text'] }}
 </span>
 </div>

 @if(session('error'))
 <div class="mb-5 p-4 rounded-xl bg-error/10 border border-error/20 text-error text-sm font-semibold">{{ session('error') }}</div>
 @endif

 <!-- Status Trial / Tagihan -->
 @if($tenant->isPastDue())
 <div class="mb-6 p-5 rounded-2xl bg-error/10 border border-error/20">
 <h2 class="font-bold text-error mb-1">Tagihan Belum Dibayar</h2>
 <p class="text-sm text-error mb-4">Akses Anda saat ini dalam mode read-only. Bayar tagihan untuk mengaktifkan kembali semua fitur.</p>
 @if($unpaidInvoice)
 <a href="{{ route('tenant.billing.checkout') }}" class="inline-block bg-error hover:bg-error text-white px-5 py-2.5 rounded-xl text-sm font-bold transition">Bayar {{ $unpaidInvoice->invoice_number }} — Rp {{ number_format($unpaidInvoice->amount, 0, ',', '.') }}</a>
 @endif
 </div>
 @endif

 <!-- Kartu Plan -->
 <div class="grid md:grid-cols-2 gap-5 mb-8">
 <!-- Free -->
 <div class="p-6 rounded-2xl bg-base-100 border-2 {{ $tenant->subscription_plan === 'free' ? 'border-base-300' : 'border-base-300' }}">
 <div class="flex items-center justify-between mb-1">
 <h3 class="text-lg font-bold text-base-content">Free</h3>
 @if($tenant->subscription_plan === 'free')
 <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-base-200 text-base-content/70">PLAN AKTIF</span>
 @endif
 </div>
 <p class="text-3xl font-black text-base-content mb-1">Rp 0<span class="text-sm font-medium text-base-content/50">/bulan</span></p>
 <p class="text-xs text-base-content/60 mb-5">Selamanya gratis, untuk studio yang baru mulai.</p>
 <ul class="text-xs space-y-2 mb-5">
 @foreach($features as $f)
 <li class="flex items-start gap-2">
 <span class="{{ $f['free'] === true ? 'text-success' : ($f['free'] === false ? 'text-base-content/70' : 'text-base-content/70') }} font-bold">✓</span>
 <span class="text-base-content/70">{{ $f['label'] }} — <strong class="text-base-content">{{ is_bool($f['free']) ? ($f['free'] ? '✓' : '✕') : $f['free'] }}</strong></span>
 </li>
 @endforeach
 </ul>
 </div>

 <!-- Pro -->
 <div class="p-6 rounded-2xl bg-base-100 border-2 {{ $tenant->subscription_plan === 'pro' ? 'border-primary' : 'border-base-300' }} relative overflow-hidden">
 <div class="absolute top-0 right-0 bg-primary text-white text-[10px] font-bold px-3 py-1 rounded-bl-lg">POPULER</div>
 <div class="flex items-center justify-between mb-1">
 <h3 class="text-lg font-bold text-base-content">Pro</h3>
 @if($tenant->subscription_plan === 'pro')
 <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary/20 text-primary">PLAN AKTIF</span>
 @endif
 </div>
 <p class="text-3xl font-black text-base-content mb-1">Rp {{ number_format($proPrice, 0, ',', '.') }}<span class="text-sm font-medium text-base-content/50">/bulan</span></p>
 <p class="text-xs text-base-content/60 mb-5">Untuk studio yang sudah berjalan dengan baik.</p>
 <ul class="text-xs space-y-2 mb-5">
 @foreach($features as $f)
 <li class="flex items-start gap-2">
 <span class="text-success font-bold">✓</span>
 <span class="text-base-content/70">{{ $f['label'] }} — <strong class="text-base-content">{{ is_bool($f['pro']) ? ($f['pro'] ? '✓' : '✕') : $f['pro'] }}</strong></span>
 </li>
 @endforeach
 </ul>

 @if($tenant->isPartner())
 <div class="p-3 rounded-xl bg-accent/10 border border-accent/20 text-accent text-xs font-semibold text-center">Akun Partner — akses Pro gratis selamanya</div>
 @elseif($tenant->isPaidPlan())
 <div class="p-3 rounded-xl bg-success/10 border border-success/20 text-success text-xs font-semibold text-center">Plan Pro aktif ✓</div>
 @elseif($unpaidInvoice)
 <a href="{{ route('tenant.billing.checkout') }}" class="block text-center bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] py-2.5 rounded-xl text-sm font-bold transition-all duration-300 shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)]">Bayar Tagihan Tertunda</a>
 @else
 <form action="{{ route('tenant.billing.checkout') }}" method="POST">
 @csrf
 <button type="submit" class="w-full bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] py-2.5 rounded-xl text-sm font-bold transition-all duration-300 shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)]">Upgrade ke Pro</button>
 </form>
 @endif
 </div>
 </div>

 <!-- Riwayat Tagihan -->
 <div class="bg-base-100 rounded-2xl border border-base-300 overflow-hidden">
 <div class="px-6 py-4 border-b border-base-300">
 <h2 class="text-sm font-bold text-base-content/80 uppercase tracking-wider">Riwayat Tagihan</h2>
 </div>
 @if($invoices->isEmpty())
 <p class="px-6 py-8 text-center text-sm text-base-content/60">Belum ada tagihan.</p>
 @else
 <div class="overflow-x-auto">
 <table class="w-full text-left text-sm">
 <thead class="bg-base-200 text-base-content/60 font-semibold text-xs">
 <tr>
 <th class="px-6 py-3">Nomor</th>
 <th class="px-6 py-3">Periode</th>
 <th class="px-6 py-3">Nominal</th>
 <th class="px-6 py-3">Status</th>
 <th class="px-6 py-3 text-center">Aksi</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-base-300">
 @foreach($invoices as $inv)
 <tr>
 <td class="px-6 py-3 font-mono text-xs">{{ $inv->invoice_number }}</td>
 <td class="px-6 py-3 text-xs text-base-content/70">{{ \Carbon\Carbon::parse($inv->period_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($inv->period_end)->format('d/m/Y') }}</td>
 <td class="px-6 py-3 font-semibold">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
 <td class="px-6 py-3">
 <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $inv->status === 'paid' ? 'bg-success/20 text-success' : ($inv->status === 'unpaid' ? 'bg-warning/20 text-warning' : 'bg-base-200 text-base-content/60') }}">{{ strtoupper($inv->status) }}</span>
 </td>
 <td class="px-6 py-3 text-center">
 @if($inv->status === 'unpaid')
 <a href="{{ route('tenant.billing.checkout') }}" class="text-xs font-bold text-primary hover:underline">Bayar</a>
 <form action="{{ route('tenant.billing.cancel', $inv->id) }}" method="POST" class="inline">
 @csrf @method('DELETE')
 <button type="submit" class="text-xs font-bold text-base-content/50 hover:text-error ml-2">Batal</button>
 </form>
 @else
 <span class="text-xs text-base-content/70">—</span>
 @endif
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @endif
 </div>
</div>
@endsection