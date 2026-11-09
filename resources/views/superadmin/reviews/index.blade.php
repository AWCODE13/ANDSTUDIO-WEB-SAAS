@extends('layouts.superadmin')

@section('title', __('reviews.title'))
@section('page_title', __('reviews.title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-base-content">{{ __('reviews.title') }}</h1>
        <p class="text-base-content/60 text-xs sm:text-sm mt-1">
            {{ __('reviews.subtitle') }}
        </p>
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('superadmin.reviews.index') }}"
          class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-5 mb-6 flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-3">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="q" value="{{ $query }}" placeholder="{{ __('reviews.table.customer') }}"
                   class="w-full px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
        </div>
        <select name="tenant" class="px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none">
            <option value="">{{ __('superadmin_tenants.title') }}</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected((string) $tenantId === (string) $tenant->id)>{{ $tenant->name }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none">
            <option value="">{{ __('reviews.table.status') }}</option>
            <option value="visible" @selected($status === 'visible')>{{ __('reviews.status.visible') }}</option>
            <option value="hidden" @selected($status === 'hidden')>{{ __('reviews.status.hidden') }}</option>
        </select>
        <select name="rating" class="px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none">
            <option value="">{{ __('reviews.table.rating') }}</option>
            @for ($r = 5; $r >= 1; $r--)
                <option value="{{ $r }}" @selected((string) $rating === (string) $r)>{{ $r }} ★</option>
            @endfor
        </select>
        <div class="flex gap-2 w-full sm:w-auto">
            <button type="submit" class="flex-1 sm:flex-none px-4 py-2 min-h-[40px] bg-primary hover:bg-primary/90 text-white text-xs sm:text-sm font-semibold rounded-xl transition">
                {{ __('reviews.btn_filter') }}
            </button>
            @if ($query || $tenantId || $status || $rating)
                <a href="{{ route('superadmin.reviews.index') }}" class="px-3 py-2 min-h-[40px] bg-base-200 hover:bg-base-300 text-base-content/70 text-xs sm:text-sm font-semibold rounded-xl transition flex items-center justify-center">
                    {{ __('reviews.btn_reset') }}
                </a>
            @endif
        </div>
    </form>

    <!-- Desktop: Tabel -->
    <div class="hidden lg:block bg-base-100 rounded-2xl border border-base-300 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm [&_thead]:text-base-content/60 [&_thead]:bg-base-200 [&_th]:text-[11px] [&_th]:uppercase [&_th]:tracking-wider [&_th]:border-0 [&_td]:border-t-0 [&_td]:border-b [&_td]:border-base-300 [&_tr:hover]:bg-base-200/80">
                <thead>
                    <tr>
                        <th>{{ __('reviews.table.studio') }}</th>
                        <th>{{ __('reviews.table.customer') }}</th>
                        <th>{{ __('reviews.table.rating') }}</th>
                        <th>{{ __('reviews.table.comment') }}</th>
                        <th>{{ __('reviews.table.status') }}</th>
                        <th class="text-right">{{ __('reviews.table.action') }}</th>
                    </tr>
                </thead>
                <tbody class="text-base-content/70">
                    @forelse ($reviews as $review)
                        <tr class="transition align-top">
                            <td class="border-b border-base-300 font-bold text-base-content text-sm whitespace-nowrap">
                                {{ $review->tenant?->name }}
                            </td>
                            <td class="border-b border-base-300">
                                <span class="block text-base-content/70 font-medium">{{ $review->maskedName() }}</span>
                                <span class="block text-[10px] text-base-content/60">{{ $review->created_at?->translatedFormat('d M Y') }}</span>
                            </td>
                            <td class="border-b border-base-300 text-warning whitespace-nowrap">
                                {{ str_repeat('★', $review->rating) }}<span class="text-base-content/80">{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            </td>
                            <td class="border-b border-base-300 text-base-content/50 max-w-xs">
                                {{ \Illuminate\Support\Str::limit($review->comment ?? '—', 90) }}
                            </td>
                            <td class="border-b border-base-300">
                                @if ($review->isHidden())
                                    <span class="text-[10px] font-bold text-warning bg-warning/10 px-2 py-1 rounded-full">{{ __('reviews.status.hidden') }}</span>
                                    @if ($review->hidden_reason)
                                        <span class="block text-[10px] text-base-content/60 mt-1">{{ $review->hidden_reason }}</span>
                                    @endif
                                @else
                                    <span class="text-[10px] font-bold text-success bg-success/10 px-2 py-1 rounded-full">{{ __('reviews.status.visible') }}</span>
                                @endif
                            </td>
                            <td class="border-b border-base-300 text-right whitespace-nowrap">
                                @if ($review->isHidden())
                                    <form action="{{ route('superadmin.reviews.restore', $review) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn-xs btn-ghost border-0 text-base-content/70 hover:text-base-content hover:bg-base-200">
                                            {{ __('reviews.action.show') }}
                                        </button>
                                    </form>
                                @else
                                    <details class="inline-block relative text-left">
                                        <summary class="cursor-pointer list-none btn btn-xs btn-ghost border-0 text-error hover:bg-base-200">
                                            {{ __('reviews.action.hide') }}
                                        </summary>
                                        <form action="{{ route('superadmin.reviews.hide', $review) }}" method="POST"
                                              class="mt-2 w-64 bg-base-200 border border-base-300 rounded-xl p-3 space-y-2">
                                            @csrf
                                            @method('PUT')
                                            <label class="block text-xs font-bold text-base-content">{{ __('reviews.hide_reason') }}</label>
                                            <textarea name="reason" rows="2" required
                                                      class="textarea textarea-bordered textarea-sm w-full bg-base-200 border-base-300 text-base-content/70 placeholder-base-content/50 focus:border-primary"
                                                      placeholder="{{ __('reviews.hide_reason_placeholder') }}"></textarea>
                                            <button type="submit" class="w-full py-1.5 btn btn-xs btn-error border-0 text-xs font-bold shadow-none">
                                                {{ __('reviews.btn_save') }}
                                            </button>
                                        </form>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border-b border-base-300 px-5 py-14 text-center text-sm text-base-content/60">{{ __('reviews.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reviews->hasPages())
            <div class="px-5 py-4 border-t border-base-300">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile: Cards -->
    <div class="lg:hidden space-y-3">
        @forelse ($reviews as $review)
            <div class="bg-base-100 rounded-2xl border border-base-300 p-4 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="block font-bold text-sm text-base-content">{{ $review->tenant?->name }}</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-xs text-base-content/70 font-medium">{{ $review->maskedName() }}</span>
                            <span class="text-[10px] text-base-content/40">· {{ $review->created_at?->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-warning text-xs font-bold">{{ str_repeat('★', $review->rating) }}</span>
                        <div class="mt-1">
                            @if ($review->isHidden())
                                <span class="text-[10px] font-bold text-warning bg-warning/10 px-2 py-0.5 rounded-md">{{ __('reviews.status.hidden') }}</span>
                            @else
                                <span class="text-[10px] font-bold text-success bg-success/10 px-2 py-0.5 rounded-md">{{ __('reviews.status.visible') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($review->comment)
                    <p class="text-xs text-base-content/70 bg-base-200/50 p-3 rounded-xl leading-relaxed italic">
                        "{{ $review->comment }}"
                    </p>
                @endif

                @if ($review->isHidden() && $review->hidden_reason)
                    <p class="text-[10px] text-warning bg-warning/5 border border-warning/20 p-2 rounded-lg">
                        <strong>Alasan sembunyikan:</strong> {{ $review->hidden_reason }}
                    </p>
                @endif

                <div class="pt-2 border-t border-base-300/50 flex justify-end">
                    @if ($review->isHidden())
                        <form action="{{ route('superadmin.reviews.restore', $review) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="px-3 py-1.5 bg-base-200 hover:bg-base-300 text-xs font-semibold rounded-lg text-base-content transition">
                                {{ __('reviews.action.show') }}
                            </button>
                        </form>
                    @else
                        <details class="relative inline-block text-left">
                            <summary class="cursor-pointer list-none px-3 py-1.5 bg-error/10 hover:bg-error/20 text-error text-xs font-semibold rounded-lg transition">
                                {{ __('reviews.action.hide') }}
                            </summary>
                            <div class="fixed inset-0 z-40 bg-black/40 lg:hidden" onclick="this.parentElement.removeAttribute('open')"></div>
                            <div class="absolute right-0 bottom-full mb-2 w-72 bg-base-100 rounded-xl border border-base-300 shadow-xl p-3 z-50">
                                <form action="{{ route('superadmin.reviews.hide', $review) }}" method="POST">
                                    @csrf @method('PUT')
                                    <label class="block text-xs font-semibold text-base-content mb-1">
                                        {{ __('reviews.reason_label') }}
                                    </label>
                                    <textarea name="reason" rows="2" required placeholder="{{ __('reviews.reason_placeholder') }}"
                                              class="w-full text-xs p-2 rounded-lg bg-base-200 border border-base-300 text-base-content focus:border-primary outline-none mb-2"></textarea>
                                    <div class="flex justify-end gap-1">
                                        <button type="submit" class="px-2.5 py-1 bg-error text-white text-xs font-semibold rounded-lg">
                                            {{ __('reviews.action.confirm_hide') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </details>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-base-100 p-8 text-center rounded-2xl border border-base-300">
                <p class="text-base-content/60 font-medium text-sm">{{ __('reviews.empty') }}</p>
            </div>
        @endforelse

        @if ($reviews->hasPages())
            <div class="py-4">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
@endsection
