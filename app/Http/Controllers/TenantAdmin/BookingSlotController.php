<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\BookingSlot;
use App\Models\Booking;
use App\Models\Package;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingSlotController extends Controller
{
    // Tampilkan daftar jadwal per tanggal
   public function index(Request $request)
    {
        $selectedMonth = $request->get('month', now()->format('Y-m'));
        $selectedPackage = $request->get('package_filter', ''); // Tangkap filter paket
        
        $packages = Package::where('is_active', true)->get();
        
        // Buat query dasar - hanya jadwal hari ini dan ke depan
        $query = BookingSlot::with('package')
            ->whereDate('date', '>=', now()->toDateString())
            ->where('date', 'like', $selectedMonth . '%');
        
        // Jika admin memilih dropdown filter paket, saring datanya
        if ($selectedPackage) {
            $query->where('package_id', $selectedPackage);
        }

        $slots = $query->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy('date');

        return view('tenant.booking_slots.index', compact('slots', 'selectedMonth', 'packages', 'selectedPackage'));
    }

    /**
     * Cek ketersediaan slot secara real-time untuk paket & tanggal tertentu.
     * Digunakan frontend saat klien memilih jadwal, untuk mencegah double booking.
     */
    public function checkAvailable(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'date'       => 'required|date|after_or_equal:today',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // Release slot yang expired (payment deadline lewat) agar kembali available
        \App\Services\BookingExpiryService::releaseExpired($tenantId, $request->package_id);

        $slots = BookingSlot::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('package_id', $request->package_id)
            ->where('date', $request->date)
            ->where('status', 'available')
            ->orderBy('start_time')
            ->get(['id', 'start_time', 'end_time'])
            ->map(function ($s) {
                return [
                    'id'         => $s->id,
                    'start_time' => substr((string) $s->start_time, 0, 5),
                    'end_time'   => substr((string) $s->end_time, 0, 5),
                    'label'      => substr((string) $s->start_time, 0, 5) . ' - ' . substr((string) $s->end_time, 0, 5),
                    'status'     => 'available',
                ];
            });

        // Deteksi slot yang terpakai oleh booking manual (tanpa booking_slot_id)
        // pada tanggal & paket yang sama, agar tidak ditampilkan sebagai available.
        $manualBookedTimes = Booking::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('package_id', $request->package_id)
            ->where('booking_date', $request->date)
            ->whereIn('booking_status', ['pending', 'confirmed', 'completed'])
            ->whereNull('booking_slot_id')
            ->pluck('start_time');

        $slots = $slots->filter(function ($slot) use ($manualBookedTimes) {
            return ! $manualBookedTimes->contains(substr((string) $slot['start_time'], 0, 5));
        })->values();

        return response()->json([
            'success' => true,
            'data'    => $slots,
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'package_id' => ['required', function ($attribute, $value, $fail) {
                if ($value !== 'all' && ! Package::whereKey($value)->exists()) {
                    $fail('Paket yang dipilih tidak valid.');
                }
            }],
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'interval'   => 'required|integer|min:15',
        ]);

        $tenantId = auth()->user()->tenant_id;

        if ($request->package_id === 'all') {
            $packageIds = Package::where('is_active', true)->pluck('id')->all();
            if (empty($packageIds)) {
                return back()->withErrors(['package_id' => 'Belum ada paket aktif untuk di-generate.']);
            }
        } else {
            $packageIds = [(int) $request->package_id];
        }

        $interval = (int) $request->interval;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->startOfDay();

        // Rentang jam dihitung sekali, dipakai ulang untuk semua paket.
        $timeRanges = [];
        $cursor = Carbon::parse($startDate->toDateString() . ' ' . $request->start_time);
        $limit = Carbon::parse($startDate->toDateString() . ' ' . $request->end_time);
        while ($cursor->lt($limit)) {
            $slotEnd = $cursor->copy()->addMinutes($interval);
            if ($slotEnd->gt($limit)) {
                break;
            }
            $timeRanges[] = [$cursor->format('H:i:s'), $slotEnd->format('H:i:s')];
            $cursor->addMinutes($interval);
        }

        // Ambil slot yang sudah ada sekali saja, hindari query exists() per slot.
        $existing = BookingSlot::whereIn('package_id', $packageIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get(['package_id', 'date', 'start_time'])
            ->keyBy(function ($slot) {
                $dateStr = $slot->date instanceof \DateTimeInterface
                    ? $slot->date->format('Y-m-d')
                    : (string) $slot->date;

                return $slot->package_id . '|' . $dateStr . '|' . substr((string) $slot->start_time, 0, 8);
            });

        $now = now();
        $rows = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->toDateString();
            foreach ($packageIds as $packageId) {
                foreach ($timeRanges as [$start, $end]) {
                    if ($existing->has($packageId . '|' . $dateStr . '|' . $start)) {
                        continue;
                    }
                    $rows[] = [
                        'tenant_id'  => $tenantId,
                        'package_id' => $packageId,
                        'date'       => $dateStr,
                        'start_time' => $start,
                        'end_time'   => $end,
                        'status'     => 'available',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        $slotsCreated = 0;
        foreach (array_chunk($rows, 500) as $chunk) {
            BookingSlot::insert($chunk);
            $slotsCreated += count($chunk);
        }

        $packageLabel = count($packageIds) > 1 ? count($packageIds) . ' paket' : 'paket terpilih';

        return back()->with('success', "Berhasil membuat $slotsCreated slot jadwal baru untuk $packageLabel!")->withInput();
    }
    
    // Hapus satu slot jadwal
    public function destroy(BookingSlot $bookingSlot)
    {
        if ($bookingSlot->status !== 'available') {
            return back()->withErrors(['Hanya slot yang masih kosong yang bisa dihapus.']);
        }
        
        $bookingSlot->delete();
        return back()->with('success', 'Slot berhasil dihapus.');
    }

    // Ubah status slot secara manual
    public function updateStatus(Request $request, BookingSlot $bookingSlot)
    {
        $request->validate([
            'status' => 'required|in:available,reserved,blocked'
        ]);

        $newStatus = $request->status;
        $oldStatus = $bookingSlot->status;

        // Jika status tidak berubah, tidak perlu update
        if ($oldStatus === $newStatus) {
            return back()->with('info', 'Status jadwal tidak berubah.');
        }

        // Validasi pergantian status reservasi:
        // - reserved → available: hanya boleh jika belum ada booking yang terkait dengan slot ini
        // - blocked → available: boleh (admin membuka kembali jadwal yang diblokir)
        // - available → reserved/blocked: boleh
        // - reserved → blocked: boleh (admin membatalkan jadwal yang sudah dipesan)
        if ($oldStatus === 'reserved' && $newStatus === 'available') {
            $hasBooking = Booking::withoutGlobalScopes()
                ->where('booking_slot_id', $bookingSlot->id)
                ->whereIn('booking_status', ['pending', 'confirmed', 'completed'])
                ->exists();

            if ($hasBooking) {
                return back()->withErrors([
                    'status' => 'Tidak dapat mengembalikan slot ke Available karena sudah ada booking yang terkait dengan slot ini. Batalkan booking terlebih dahulu.'
                ]);
            }
        }

        $bookingSlot->update([
            'status' => $newStatus
        ]);

        return back()->with('success', 'Status jadwal berhasil diperbarui dari ' . ucfirst($oldStatus) . ' menjadi ' . ucfirst($newStatus) . '.');
    }

    // Hapus semua slot pada tanggal tertentu sekaligus
    
    public function destroyByDate($date, $packageId)
    {
        $query = BookingSlot::where('date', $date)
            ->whereIn('status', ['available', 'blocked']);
            
        // Jika yang dihapus adalah slot tanpa paket spesifik (Umum)
        if ($packageId === 'umum') {
            $query->whereNull('package_id');
        } else {
            $query->where('package_id', $packageId);
        }

        $deleted = $query->delete();

        if ($deleted) {
            return back()->with('success', "Berhasil menghapus $deleted slot kosong pada tanggal " . \Carbon\Carbon::parse($date)->format('d-m-Y') . ".");
        }

        return back()->withErrors(['Tidak ada slot kosong yang bisa dihapus. (Slot yang berstatus Reserved tidak dapat dihapus).']);
    }
}