<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Midtrans\Snap;

class BillingController extends Controller
{
    /**
     * Harga plan Pro per bulan.
     */
    public const PRO_PRICE = 299000;

    /**
     * Halaman Plan & Tagihan.
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;

        $invoices = $tenant->invoices()
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $unpaidInvoice = $invoices->firstWhere('status', 'unpaid');

        return view('tenant.billing.index', [
            'tenant'        => $tenant,
            'invoices'      => $invoices,
            'unpaidInvoice' => $unpaidInvoice,
            'features'      => Tenant::planFeatures('pro'),
            'proPrice'      => self::PRO_PRICE,
        ]);
    }

    /**
     * Buat invoice baru & generate Snap Token Midtrans.
     */
    public function checkout(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if ($tenant->isPartner()) {
            return back()->with('error', 'Akun partner tidak memerlukan tagihan.');
        }

        $periodMonth = now()->format('Y-m');
        $periodStart = now()->startOfMonth()->toDateString();
        $periodEnd = now()->endOfMonth()->toDateString();

        // 1. Pakai ulang tagihan yang belum dibayar (kalau ada)
        $invoice = $tenant->invoices()->where('status', 'unpaid')->first();

        if (! $invoice) {
            $invoice = TenantInvoice::create([
                'tenant_id'      => $tenant->id,
                'invoice_number' => TenantInvoice::generateNumber($tenant->id, $periodMonth),
                'plan'           => 'pro',
                'period_month'   => $periodMonth,
                'period_start'   => $periodStart,
                'period_end'     => $periodEnd,
                'amount'         => self::PRO_PRICE,
                'status'         => 'unpaid',
            ]);
        }

        // 2. Generate Snap Token
        MidtransService::config();

        $orderId = 'INV-' . $invoice->id . '-' . strtoupper(Str::random(8));
        $invoice->update(['midtrans_order_id' => $orderId]);

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $invoice->amount,
            ],
            'customer_details' => [
                'first_name' => $tenant->name,
                'email'      => $tenant->email,
                'phone'      => $tenant->phone ?? '081234567890',
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return view('tenant.billing.checkout', [
            'invoice'   => $invoice,
            'snapToken' => $snapToken,
        ]);
    }

    /**
     * Batalkan tagihan yang belum dibayar.
     */
    public function cancelInvoice(TenantInvoice $invoice)
    {
        if ($invoice->status !== 'unpaid') {
            return back()->with('error', 'Tagihan ini tidak bisa dibatalkan.');
        }

        $invoice->update(['status' => 'void']);

        return back()->with('success', 'Tagihan ' . $invoice->invoice_number . ' berhasil dibatalkan.');
    }
}
