@extends('layouts.superadmin')

@section('title', __('review_requests.title'))
@section('page_title', __('review_requests.title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-base-content">{{ __('review_requests.title') }}</h1>
        <p class="text-base-content/60 text-xs sm:text-sm mt-1">
            {{ __('review_requests.subtitle') }}
        </p>
    </div>

    <!-- Desktop: Tabel -->
    <div class="hidden lg:block bg-base-100 rounded-2xl border border-base-300 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.05)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-base-200 text-base-content/60 font-semibold text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3.5">{{ __('review_requests.table.studio') }}</th>
                        <th class="px-5 py-3.5">{{ __('review_requests.table.review') }}</th>
                        <th class="px-5 py-3.5">{{ __('review_requests.table.reason') }}</th>
                        <th class="px-5 py-3.5">{{ __('review_requests.table.status') }}</th>
                        <th class="px-5 py-3.5 text-right">{{ __('review_requests.table.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-300">
                    @forelse ($requests as $req)
                        <tr class="hover:bg-base-200/70 transition align-top">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="block font-bold text-base-content">{{ $req->tenant?->name }}</span>
                                <span class="block text-xs text-base-content/50">{{ $req->requester?->name ?? '-' }}</span>
                                <span class="block text-xs text-base-content/50">{{ $req->created_at?->translatedFormat('d M Y H:i') }}</span>
                            </td>
                            <td class="px-5 py-4 max-w-xs">
                                @if ($req->review)
                                    <span class="text-warning text-xs">{{ str_repeat('★', $req->review->rating) }}</span>
                                    <span class="block text-xs text-base-content/70 mt-1">
                                        {{ $req->review->customer_name }}
                                        @if ($req->review->comment)
                                            — “{{ \Illuminate\Support\Str::limit($req->review->comment, 60) }}”
                                        @endif
                                    </span>
                                @else
                                    <span class="text-xs text-base-content/50">{{ __('review_requests.review_not_found') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-base-content/70 max-w-xs">{{ $req->reason }}</td>
                            <td class="px-5 py-4">
                                @if ($req->status === 'pending')
                                    <span class="text-[11px] font-bold text-warning bg-warning/10 px-2 py-1 rounded-full">{{ __('review_requests.status.pending') }}</span>
                                @elseif ($req->status === 'approved')
                                    <span class="text-[11px] font-bold text-success bg-success/10 px-2 py-1 rounded-full">{{ __('review_requests.status.approved') }}</span>
                                @else
                                    <span class="text-[11px] font-bold text-error bg-error/10 px-2 py-1 rounded-full">{{ __('review_requests.status.rejected') }}</span>
                                @endif

                                @if ($req->status === 'rejected' && $req->admin_note)
                                    <span class="block text-[11px] text-base-content/60 mt-1">{{ $req->admin_note }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                @if ($req->status === 'pending')
                                    <form action="{{ route('superadmin.review-requests.approve', $req) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button class="px-3 py-1.5 bg-success hover:bg-success/90 text-base-content text-xs font-bold rounded-lg transition">
                                            {{ __('review_requests.btn_approve') }}
                                        </button>
                                    </form>

                                    <details class="inline-block relative text-left mt-2">
                                        <summary class="cursor-pointer list-none px-3 py-1.5 bg-error/10 hover:bg-error/20 text-error text-xs font-bold rounded-lg transition">
                                            {{ __('review_requests.btn_reject') }}
                                        </summary>
                                        <form action="{{ route('superadmin.review-requests.reject', $req) }}" method="POST"
                                              class="mt-2 w-64 bg-base-200 border border-base-300 rounded-xl p-3 space-y-2">
                                            @csrf
                                            @method('PUT')
                                            <label class="block text-xs font-bold text-base-content/80">{{ __('review_requests.label_admin_note') }}</label>
                                            <textarea name="admin_note" rows="2"
                                                      class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-lg text-xs focus:border-primary outline-none"
                                                      placeholder="{{ __('review_requests.placeholder_admin_note') }}"></textarea>
                                            <button type="submit" class="w-full py-1.5 bg-error hover:bg-error/90 text-base-content font-bold rounded-lg text-xs transition">
                                                {{ __('review_requests.btn_reject_request') }}
                                            </button>
                                        </form>
                                    </details>
                                @else
                                    <span class="text-xs text-base-content/50">{{ __('review_requests.status.done') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center text-sm text-base-content/50">{{ __('review_requests.empty_detail') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($requests->hasPages())
            <div class="px-5 py-4 border-t border-base-300">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
