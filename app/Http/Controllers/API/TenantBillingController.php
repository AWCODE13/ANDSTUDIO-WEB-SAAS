<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantBillingStoreRequest;
use App\Http\Requests\TenantBillingCancelRequest;
use App\Models\TenantInvoice;
use App\Services\MidtransService;
use Midtrans\Snap;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TenantBillingController extends Controller
{
    public function show(): JsonResponse
    {
        $tenant = Auth::user()->tenant;

        $invoices = $tenant->invoices()
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $unpaidInvoice = $invoices->firstWhere('status', 'unpaid');

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'plan' => $tenant->subscription_plan,
                'status' => $tenant->subscription_status,
                'trial_days_left' => $tenant->trialDaysLeft(),
                'is_partner' => $tenant->isPartner(),
                'is_trial_active' => $tenant->isTrialActive(),
                'is_paid_plan' => $tenant->isPaidPlan(),
            ],
            'invoices' => $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'plan' => $invoice->plan,
                    'period_month' => $invoice->period_month,
                    'period_start' => $invoice->period_start,
                    'period_end' => $invoice->period_end,
                    'amount' => $invoice->amount,
                    'status' => $invoice->status,
                    'midtrans_order_id' => $invoice->midtrans_order_id,
                    'created_at' => $invoice->created_at->toIso8601String(),
                ];
            }),
            'unpaid_invoice' => $unpaidInvoice ? [
                'id' => $unpaidInvoice->id,
                'invoice_number' => $unpaidInvoice->invoice_number,
                'amount' => $unpaidInvoice->amount,
                'due_date' => $unpaidInvoice->created_at->toDateString(),
                'status' => $unpaidInvoice->status,
            ] : null,
            'midtrans_snap_token' => $unpaidInvoice ? $this->getMidtransSnapToken($unpaidInvoice) : null,
        ]);
    }

    public function checkout(TenantBillingStoreRequest $request): JsonResponse
    {
        $tenant = Auth::user()->tenant;

        if ($tenant->isPartner()) {
            return response()->json([
                'message' => 'Akun partner tidak memerlukan tagihan.',
            ], 422);
        }

        $periodMonth = now()->format('Y-m');
        $periodStart = now()->startOfMonth()->toDateString();
        $periodEnd = now()->endOfMonth()->toDateString();

        $invoice = $tenant->invoices()->where('status', 'unpaid')->first();

        if (! $invoice) {
            $invoice = TenantInvoice::create([
                'tenant_id'      => $tenant->id,
                'invoice_number' => TenantInvoice::generateNumber($tenant->id, $periodMonth),
                'plan'           => 'pro',
                'period_month'   => $periodMonth,
                'period_start'   => $periodStart,
                'period_end'     => $periodEnd,
                'amount'         => 299000,
                'status'         => 'unpaid',
            ]);
        }

        $midtransOrderId = 'INV-' . $invoice->id . '-' . strtoupper(Str::random(8));
        $invoice->update(['midtrans_order_id' => $midtransOrderId]);

        MidtransService::config();

        $params = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) $invoice->amount,
            ],
            'customer_details' => [
                'first_name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone ?? '081234567890',
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'plan' => $invoice->plan,
                'period_month' => $invoice->period_month,
                'amount' => $invoice->amount,
                'status' => $invoice->status,
            ],
            'snap_token' => $snapToken,
        ]);
    }

    public function cancel(TenantBillingCancelRequest $request): JsonResponse
    {
        $invoice = TenantInvoice::find($request->invoice_id);

        if (! $invoice || $invoice->tenant_id !== Auth::user()->tenant_id) {
            return response()->json([
                'message' => 'Invoice tidak ditemukan.',
            ], 404);
        }

        if ($invoice->status !== 'unpaid') {
            return response()->json([
                'message' => 'Tagihan tidak dapat dibatalkan karena sudah dibayar atau ditolak.',
            ], 422);
        }

        $invoice->update(['status' => 'void']);

        return response()->json([
            'message' => 'Tagihan ' . $invoice->invoice_number . ' berhasil dibatalkan.',
        ]);
    }

    private function getMidtransSnapToken(TenantInvoice $invoice): string
    {
        MidtransService::config();

        $params = [
            'transaction_details' => [
                'order_id' => $invoice->midtrans_order_id,
                'gross_amount' => (int) $invoice->amount,
            ],
            'customer_details' => [
                'first_name' => $invoice->tenant->name,
                'email' => $invoice->tenant->email,
                'phone' => $invoice->tenant->phone ?? '081234567890',
            ],
        ];

        return Snap::getSnapToken($params);
    }
}