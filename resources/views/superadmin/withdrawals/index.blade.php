@extends('layouts.superadmin')

@section('title', 'Pencairan Saldo')
@section('page_title', 'Pencairan Saldo')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Pencairan Saldo</h1>
            <p class="text-base-content/50 text-sm mt-1">Proses pengajuan penarikan dana dari studio.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        @foreach([
            ['Pending', $stats['count_pending'], 'Rp ' . number_format($stats['total_pending'], 0, ',', '.'), 'text-warning'],
            ['Selesai', $stats['count_completed'], 'Rp ' . number_format($stats['total_completed'], 0, ',', '.'), 'text-success'],
            ['Ditolak', $stats['count_rejected'], '', 'text-error'],
        ] as $stat)
            <div class="bg-base-100 rounded-xl border border-base-300 p-4 sm:p-5">
                <span class="block text-xs font-semibold text-base-content/60">{{ $stat[0] }}</span>
                <span class="block text-xl sm:text-2xl font-extrabold {{ $stat[3] }} mt-1">{{ $stat[1] }}</span>
                @if($stat[2])
                    <span class="block text-xs text-base-content/50 mt-1">{{ $stat[2] }}</span>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('superadmin.withdrawals.index') }}"
          class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-5 mb-6 flex flex-col sm:flex-row gap-2 sm:gap-3">
        <div class="flex-1">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / email studio..."
                   class="w-full px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
        </div>
        <select name="status" class="px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none">
            <option value="">Semua Status</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
        </select>
        <button type="submit" class="px-3 py-2 sm:py-2.5 min-h-[40px] bg-primary hover:bg-primary/90 text-white text-xs sm:text-sm font-semibold rounded-xl transition">
            Filter
        </button>
        @if (request('q') || request('status'))
            <a href="{{ route('superadmin.withdrawals.index') }}" class="px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-200 hover:bg-base-300 text-base-content/70 text-xs sm:text-sm font-semibold rounded-xl transition">
                Reset
            </a>
        @endif
    </form>

    <div class="bg-base-100 rounded-2xl border border-base-300 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm [&_thead]:text-base-content/60 [&_thead]:bg-base-200 [&_th]:text-[11px] [&_th]:uppercase [&_th]:tracking-wider [&_th]:border-0 [&_td]:border-t-0 [&_td]:border-b [&_td]:border-base-300 [&_tr:hover]:bg-base-200/80">
                <thead>
                    <tr>
                        <th>Studio</th>
                        <th class="text-right">Nominal</th>
                        <th>Rekening Tujuan</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-base-content/70">
                    @forelse ($withdrawals as $wd)
                        <tr class="transition">
                            <td>
                                <span class="block font-bold text-base-content">{{ $wd->tenant->name ?? '—' }}</span>
                                <span class="block text-xs text-base-content/60">{{ $wd->tenant->email ?? '' }}</span>
                            </td>
                            <td class="text-right font-bold text-base-content">Rp {{ number_format($wd->amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="block text-sm text-base-content">{{ $wd->bank_name }} · {{ $wd->account_number }}</span>
                                <span class="block text-xs text-base-content/60">a.n. {{ $wd->account_name }}</span>
                            </td>
                            <td class="text-sm whitespace-nowrap">{{ $wd->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td>
                                @php
                                    $map = ['pending' => ['bg-warning/10 text-warning', 'Pending'],
                                            'completed' => ['bg-success/10 text-success', 'Selesai'],
                                            'rejected' => ['bg-error/10 text-error', 'Ditolak']];
                                    $badge = $map[$wd->status] ?? ['bg-base-300 text-base-content/50', $wd->status];
                                @endphp
                                <span class="inline-block px-2 py-1 rounded-md text-[10px] font-bold uppercase {{ $badge[0] }}">{{ $badge[1] }}</span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('superadmin.withdrawals.show', $wd) }}" class="btn btn-xs btn-primary border-0 shadow-none">
                                    {{ $wd->status === 'pending' ? 'Proses' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-base-content/60">
                                Tidak ada pengajuan penarikan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($withdrawals->hasPages())
            <div class="px-5 py-4 border-t border-base-300">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
@endsection
