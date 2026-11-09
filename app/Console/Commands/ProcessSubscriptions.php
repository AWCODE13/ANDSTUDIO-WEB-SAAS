<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Services\WhatsAppBillingNotifier;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proses langganan: trial habis, buat invoice bulanan, tandai past_due, kirim notifikasi WA';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredTrials = 0;
        $createdInvoices = 0;
        $markedPastDue = 0;

        // 1. Kirim notifikasi H-3 trial habis
        $expiringTrials = Tenant::where('subscription_plan', 'free')
            ->where('is_partner', false)
            ->where('subscription_status', 'active')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(3)])
            ->get();

        foreach ($expiringTrials as $tenant) {
            WhatsAppBillingNotifier::sendTrialEndingReminder($tenant);
        }

        // 2. Trial yang sudah habis -> turun ke plan free + past_due
        $trialEnded = Tenant::where('subscription_plan', 'free')
            ->where('is_partner', false)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->where('subscription_status', 'active')
            ->get();

        foreach ($trialEnded as $tenant) {
            $tenant->update([
                'subscription_plan'   => 'free',
                'subscription_status' => 'past_due',
            ]);
            $expiredTrials++;
            WhatsAppBillingNotifier::sendTrialExpired($tenant);
            $this->line("Trial habis: {$tenant->name} -> plan free / past_due");
        }

        // 3. Buat invoice bulanan untuk tenant Pro yang jatuh tempo
        $proTenants = Tenant::where('subscription_plan', 'pro')
            ->where('is_partner', false)
            ->where('subscription_status', 'active')
            ->where(function ($q) {
                $q->whereNull('next_payment_at')
                    ->orWhere('next_payment_at', '<=', now());
            })
            ->get();

        foreach ($proTenants as $tenant) {
            $periodMonth = now()->format('Y-m');

            // Jangan buat invoice duplikat untuk periode yang sama
            $exists = $tenant->invoices()
                ->where('period_month', $periodMonth)
                ->whereIn('status', ['unpaid', 'paid'])
                ->exists();

            if ($exists) {
                $tenant->update(['next_payment_at' => now()->addMonth()]);
                continue;
            }

            $invoice = TenantInvoice::create([
                'tenant_id'      => $tenant->id,
                'invoice_number' => TenantInvoice::generateNumber($tenant->id, $periodMonth),
                'plan'           => 'pro',
                'period_month'   => $periodMonth,
                'period_start'   => now()->startOfMonth()->toDateString(),
                'period_end'     => now()->endOfMonth()->toDateString(),
                'amount'         => 299000,
                'status'         => 'unpaid',
            ]);

            $tenant->update([
                'next_payment_at' => now()->addMonth(),
                'plan_expires_at' => now()->addMonth(),
            ]);

            $createdInvoices++;
            WhatsAppBillingNotifier::sendInvoiceCreated($invoice);
            $this->line("Invoice dibuat: {$invoice->invoice_number} untuk {$tenant->name}");
        }

        // 4. Invoice unpaid yang lewat tenggat 7 hari -> tenant jadi past_due
        $stale = TenantInvoice::where('status', 'unpaid')
            ->where('created_at', '<=', now()->subDays(7))
            ->get();

        foreach ($stale as $invoice) {
            $tenant = $invoice->tenant;
            if ($tenant && ! $tenant->isPartner() && $tenant->subscription_status === 'active') {
                $tenant->update(['subscription_status' => 'past_due']);
                $markedPastDue++;
                WhatsAppBillingNotifier::sendSubscriptionPastDue($tenant, $invoice);
                $this->line("Past due: {$tenant->name} (invoice {$invoice->invoice_number} belum dibayar)");
            }
        }

        $this->info("Selesai: {$expiredTrials} trial habis, {$createdInvoices} invoice dibuat, {$markedPastDue} ditandai past_due.");

        return Command::SUCCESS;
    }
}
