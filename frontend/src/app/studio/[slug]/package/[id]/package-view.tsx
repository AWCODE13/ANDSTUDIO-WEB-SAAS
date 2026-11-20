"use client";

import { use, useCallback, useEffect, useMemo, useRef, useState } from "react";
import { motion, useReducedMotion } from "motion/react";
import { api, resolveMediaUrl } from "@/lib/api";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { format } from "date-fns";
import {
  ArrowLeft,
  CalendarDays,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Clock,
  Loader2,
  MapPin,
  PartyPopper,
  User,
  Wallet,
} from "lucide-react";
import DepthCarousel from "@/components/DepthCarousel";
import { Skeleton } from "@/components/Skeleton";
import Badge from "@/components/Badge";
import { Bokeh, SparkleDeco } from "@/components/StudioDecor";

type Slot = {
  id: number;
  date: string;
  start_time: string;
  end_time: string;
  status: string;
};

const STEPS = [
  { id: 1, label: "Tanggal & Waktu", icon: CalendarDays },
  { id: 2, label: "Data Pemesan", icon: User },
  { id: 3, label: "Pembayaran", icon: Wallet },
];

export default function PackageView({
  params,
}: {
  params: Promise<{ slug: string; id: string }>;
}) {
  const { slug, id } = use(params);
  const router = useRouter();
  const reduceMotion = useReducedMotion();

  const [data, setData] = useState<any>(null);
  const [selectedDate, setSelectedDate] = useState<string | null>(null);
  const [selectedSlot, setSelectedSlot] = useState<Slot | null>(null);
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState({
    customer_name: "",
    customer_email: "",
    customer_phone: "",
    payment_option: "dp" as "dp" | "full",
    notes: "",
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);
  const [selectedImage, setSelectedImage] = useState<string | null>(null);

  // Muat detail paket dari API.
  useEffect(() => {
    api
      .getPackage(slug, id)
      .then((res: any) => setData(res.data))
      .catch(() => setError("Gagal memuat detail paket"));
  }, [slug, id]);

  // Group slot per tanggal
  const slotsByDate = useMemo(() => {
    const out: Record<string, Slot[]> = {};
    if (data?.availableSlots) {
      Object.entries(data.availableSlots as Record<string, Slot[]>).forEach(
        ([date, slots]) => {
          out[date] = slots;
        }
      );
    }
    return out;
  }, [data]);

  const carouselItems = useMemo(() => {
    const photos = data?.package?.photos || [];
    const packageName = data?.package?.name || "Photo";
    return photos.map((p: any) => ({
      image: resolveMediaUrl(p.photo_url) || "",
      alt: packageName,
    }));
  }, [data]);

  const dates = Object.keys(slotsByDate);

  // Auto pilih tanggal & slot pertama saat data paket sudah tersedia.
  useEffect(() => {
    if (dates.length > 0 && !selectedDate) {
      setSelectedDate(dates[0]);
      setSelectedSlot(slotsByDate[dates[0]][0]);
    }
  }, [dates, slotsByDate, selectedDate]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedSlot) return;

    setLoading(true);
    setError(null);

    try {
      const res = await api.createBooking(slug, id, {
        ...formData,
        booking_slot_id: selectedSlot.id,
      });

      if (res.success) {
        setSuccess(true);
        setTimeout(() => {
          router.push(`/pesanan/${res.data.booking_code}`);
        }, 1600);
      }
    } catch (err: any) {
      setError(err.message || "Gagal membuat booking");
      setLoading(false);
    }
  };

  if (error && !data) {
    return (
      <div className="min-h-screen flex items-center justify-center px-4">
        <div className="max-w-md w-full text-center bg-[#0b1220] p-8 rounded-2xl shadow-soft border border-white/10">
          <h2 className="text-xl font-bold text-white mb-2">Error</h2>
          <p className="text-slate-400 mb-6">{error}</p>
          <Link
            href={`/studio/${slug}`}
            className="inline-block px-5 py-2.5 bg-blue-500 hover:bg-blue-400 text-white rounded-xl text-sm font-semibold transition-colors"
          >
            Kembali ke Katalog
          </Link>
        </div>
      </div>
    );
  }

  if (!data) {
    return (
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <Skeleton className="h-52 w-full rounded-2xl mb-8" />
        <Skeleton className="h-96 w-full rounded-2xl" />
      </div>
    );
  }

  const pkg = data.package;
  const amountToPay =
    formData.payment_option === "full"
      ? Number(pkg.price)
      : Number(pkg.price) * 0.3;

  const inputClass =
    "w-full px-4 py-3 border border-white/10 rounded-xl text-sm bg-[#0b1220] focus:ring-2 focus:ring-blue-400 focus:border-blue-400/30 outline-none transition placeholder:text-slate-400";

  return (
    <div className="relative">
      <div className="pointer-events-none absolute inset-x-0 top-0 h-72 mesh-bg" />

      <div className="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Back */}
        <Link
          href={`/studio/${slug}`}
          className="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-blue-400 transition-colors mb-5"
        >
          <ArrowLeft className="w-4 h-4" /> Kembali ke Katalog
        </Link>

        {/* Ringkasan paket - SPLIT LAYOUT: frame foto vertikal (kiri),
            info paket (kanan). Semua section transparan (Unified Dark). */}
        <motion.div
          initial={reduceMotion ? false : { opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
          className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center mb-6"
        >
          {/* ══ KOLOM KIRI - Frame foto vertikal ══ */}
          <div className="lg:col-span-5">
            {pkg.photos && pkg.photos.length > 1 ? (
              <div className="relative w-full h-[460px] sm:h-[520px] rounded-2xl overflow-hidden border border-slate-800 shadow-2xl bg-slate-950/60">
                <DepthCarousel
                  items={carouselItems}
                  cardWidth={260}
                  cardHeight={520}
                  radius={14}
                  tint="#0f172a"
                  depth={180}
                  spread={85}
                  tilt={19}
                  tiltDirection="right"
                  perspective={1400}
                  visibleCards={3}
                  falloff={0.2}
                  blur={6}
                  autoplay={true}
                  loop={true}
                  showControls={true}
                  showIndicators={true}
                  onClick={(index, item) => {
                    setSelectedImage(item?.image || carouselItems[index]?.image || null);
                  }}
                />
              </div>
            ) : pkg.photos && pkg.photos.length === 1 ? (
              <div className="relative w-full aspect-[3/4] max-h-[520px] rounded-2xl overflow-hidden border border-slate-800 shadow-2xl bg-slate-950">
                <img
                  src={resolveMediaUrl(pkg.photos[0].photo_url)}
                  alt={pkg.name}
                  className="object-cover w-full h-full cursor-pointer transition-transform hover:scale-105"
                  onClick={() => setSelectedImage(resolveMediaUrl(pkg.photos[0].photo_url) || null)}
                />
              </div>
            ) : (
              <div className="w-full aspect-[3/4] max-h-[520px] rounded-2xl bg-gradient-to-br from-white/[0.06] to-white/[0.02] border border-blue-400/25 flex items-center justify-center">
                <span className="text-6xl">📸</span>
              </div>
            )}
          </div>

          {/* ══ KOLOM KANAN - Info paket ══ */}
          <div className="lg:col-span-7 flex flex-col gap-6">
            <div>
              <Badge tone="blue">{pkg.category}</Badge>
              <h1 className="text-2xl font-bold text-white mt-2 mb-1.5">
                {pkg.name}
              </h1>
              <p className="text-slate-400 text-sm">{pkg.description}</p>
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div className="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-5 rounded-xl">
                <span className="text-[11px] text-slate-400 block">Harga</span>
                <span className="font-bold text-white text-sm">
                  Rp {Number(pkg.price).toLocaleString("id-ID")}
                </span>
              </div>
              <div className="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-5 rounded-xl">
                <span className="text-[11px] text-emerald-400 block">
                  DP minimal
                </span>
                <span className="font-bold text-emerald-300 text-sm">
                  Rp {Number(pkg.down_payment_min).toLocaleString("id-ID")}
                </span>
              </div>
              <div className="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-5 rounded-xl">
                <span className="text-[11px] text-slate-400 block">Durasi</span>
                <span className="font-bold text-white text-sm">
                  {pkg.duration_minutes} menit
                </span>
              </div>
              <div className="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-5 rounded-xl">
                <span className="text-[11px] text-slate-400 block">
                  Maks orang
                </span>
                <span className="font-bold text-white text-sm">
                  {pkg.max_persons} org
                </span>
              </div>
            </div>
          </div>
        </motion.div>

        {/* Stepper + form booking (full width, di bawah split layout) */}
        <div className="bg-slate-900/50 backdrop-blur-sm rounded-2xl border border-slate-800 p-6 sm:p-8 shadow-soft">
          <div className="flex items-center justify-between mb-8 relative">
            <div
              className="absolute top-5 left-[16%] right-[16%] h-0.5 bg-[#0b1220] hidden sm:block"
              aria-hidden
            />
            {STEPS.map((s, i) => {
              const done = step > s.id;
              const active = step === s.id;
              return (
                <div
                  key={s.id}
                  className="relative flex flex-col items-center gap-2 flex-1"
                >
                  <motion.div
                    animate={{
                      scale: active ? 1.08 : 1,
                    }}
                    transition={{ type: "spring", stiffness: 400, damping: 22 }}
                    className={`w-10 h-10 rounded-full flex items-center justify-center z-10 transition-colors ${
                      done
                        ? "bg-emerald-500/100 text-white"
                        : active
                          ? "bg-gradient-to-br from-blue-500 to-cyan-400 text-white shadow-[0_8px_20px_-8px_rgba(37,99,235,0.8)]"
                          : "bg-[#0b1220] text-slate-400"
                    }`}
                  >
                    {done ? (
                      <CheckCircle2 className="w-5 h-5" />
                    ) : (
                      <s.icon className="w-4.5 h-4.5" />
                    )}
                  </motion.div>
                  <span
                    className={`text-[11px] sm:text-xs font-semibold text-center ${
                      active
                        ? "text-white"
                        : done
                          ? "text-emerald-400"
                          : "text-slate-400"
                    }`}
                  >
                    {s.label}
                  </span>
                </div>
              );
            })}
          </div>

          {error && (
            <div className="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-400/20 text-rose-300 text-sm flex items-start gap-2">
              <MapPin className="w-4 h-4 mt-0.5 shrink-0" />
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit}>
            {/* ===== STEP 1: Tanggal & Waktu ===== */}
            <motion.div
              animate={{ opacity: step === 1 ? 1 : 0.4 }}
              className={step === 1 ? "block" : "hidden"}
            >
              <h3 className="text-sm font-bold text-white mb-3">
                1. Pilih Tanggal
              </h3>
              {dates.length === 0 ? (
                <div className="p-4 rounded-xl bg-amber-500/10 border border-amber-400/20 text-amber-800 text-sm">
                  Belum ada jadwal tersedia untuk paket ini. Silakan hubungi admin
                  studio.
                </div>
              ) : (
                <div className="flex gap-2.5 overflow-x-auto no-scrollbar pb-2">
                  {dates.map((date) => (
                    <motion.button
                      key={date}
                      type="button"
                      whileHover={reduceMotion ? undefined : { y: -3 }}
                      whileTap={reduceMotion ? undefined : { scale: 0.95 }}
                      onClick={() => {
                        setSelectedDate(date);
                        setSelectedSlot(slotsByDate[date][0]);
                      }}
                      className={`relative shrink-0 px-5 py-3 rounded-xl border text-sm font-semibold transition-colors ${
                        selectedDate === date
                          ? "border-blue-400/60 bg-blue-500/20 text-blue-100"
                          : "border-white/10 bg-[#0b1220] text-slate-300 hover:border-white/25 hover:text-white"
                      }`}
                    >
                      {selectedDate === date && (
                        <motion.span
                          layoutId="date-pill"
                          className="absolute inset-0 rounded-xl border-2 border-blue-400"
                          transition={{
                            type: "spring",
                            stiffness: 380,
                            damping: 30,
                          }}
                        />
                      )}
                      <span className="relative block text-center">
                        <span className="block text-[11px] uppercase tracking-wide opacity-75">
                          {format(new Date(date), "EEE")}
                        </span>
                        <span className="block text-base">
                          {format(new Date(date), "dd MMM")}
                        </span>
                      </span>
                    </motion.button>
                  ))}
                </div>
              )}

              <h3 className="text-sm font-bold text-white mt-6 mb-3">
                2. Pilih Waktu Sesi
              </h3>
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                {selectedDate &&
                  slotsByDate[selectedDate].map((slot: Slot, i: number) => (
                    <motion.button
                      key={slot.id}
                      type="button"
                      initial={reduceMotion ? false : { opacity: 0, scale: 0.9 }}
                      animate={{ opacity: 1, scale: 1 }}
                      transition={{ delay: i * 0.04, duration: 0.3 }}
                      whileHover={reduceMotion ? undefined : { scale: 1.05 }}
                      whileTap={reduceMotion ? undefined : { scale: 0.95 }}
                      onClick={() => setSelectedSlot(slot)}
                      className={`px-4 py-3 rounded-xl border text-sm font-semibold flex flex-col items-center transition-colors ${
                        selectedSlot?.id === slot.id
                          ? "bg-gradient-to-br from-blue-500 to-cyan-400 text-white border-transparent shadow-[0_8px_20px_-8px_rgba(37,99,235,0.75)]"
                          : "bg-[#0b1220] border-white/10 text-slate-200 hover:border-white/20 hover:bg-[#0b1220]"
                      }`}
                    >
                      <span className="flex items-center gap-1">
                        <Clock className="w-3.5 h-3.5 opacity-70" />
                        {slot.start_time.substring(0, 5)}
                      </span>
                      <span className="text-[10px] opacity-70 mt-0.5">
                        s/d {slot.end_time.substring(0, 5)}
                      </span>
                    </motion.button>
                  ))}
              </div>

              {selectedSlot && (
                <motion.button
                  type="button"
                  onClick={() => setStep(2)}
                  initial={reduceMotion ? false : { opacity: 0, y: 8 }}
                  animate={{ opacity: 1, y: 0 }}
                  className="mt-6 w-full sm:w-auto px-6 py-3 bg-[#0f172a] hover:bg-blue-500 text-white rounded-xl text-sm font-semibold transition-colors"
                >
                  Lanjut isi data pemesan →
                </motion.button>
              )}
            </motion.div>

            {/* ===== STEP 2: Data Pemesan ===== */}
            <motion.div
              animate={{ opacity: step === 2 ? 1 : 0.4 }}
              className={step === 2 ? "block" : "hidden"}
            >
              <h3 className="text-sm font-bold text-white mb-4">
                Data Pemesan
              </h3>
              <div className="grid sm:grid-cols-2 gap-4">
                <div className="sm:col-span-2">
                  <label className="block text-xs font-semibold text-slate-400 mb-1.5">
                    Nama Lengkap
                  </label>
                  <input
                    type="text"
                    value={formData.customer_name}
                    onChange={(e) =>
                      setFormData({ ...formData, customer_name: e.target.value })
                    }
                    required
                    placeholder="Nama sesuai KTP"
                    className={inputClass}
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-400 mb-1.5">
                    Email
                  </label>
                  <input
                    type="email"
                    value={formData.customer_email}
                    onChange={(e) =>
                      setFormData({ ...formData, customer_email: e.target.value })
                    }
                    required
                    placeholder="email@contoh.com"
                    className={inputClass}
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-400 mb-1.5">
                    Nomor WhatsApp
                  </label>
                  <input
                    type="tel"
                    value={formData.customer_phone}
                    onChange={(e) =>
                      setFormData({ ...formData, customer_phone: e.target.value })
                    }
                    required
                    placeholder="08xxxxxxxxxx"
                    className={inputClass}
                  />
                </div>
                <div className="sm:col-span-2">
                  <label className="block text-xs font-semibold text-slate-400 mb-1.5">
                    Catatan untuk studio (opsional)
                  </label>
                  <textarea
                    value={formData.notes}
                    onChange={(e) =>
                      setFormData({ ...formData, notes: e.target.value })
                    }
                    rows={3}
                    placeholder="Misal: request kostum tertentu, dekorasi, dll"
                    className={`${inputClass} resize-none`}
                  />
                </div>
              </div>

              <div className="flex gap-3 mt-6">
                <button
                  type="button"
                  onClick={() => setStep(1)}
                  className="px-5 py-3 bg-[#0b1220] border border-white/10 text-slate-200 rounded-xl text-sm font-semibold hover:bg-[#0b1220] transition-colors"
                >
                  ← Kembali
                </button>
                <button
                  type="button"
                  onClick={() => setStep(3)}
                  className="flex-1 px-5 py-3 bg-[#0f172a] hover:bg-blue-500 text-white rounded-xl text-sm font-semibold transition-colors"
                >
                  Lanjut pilih pembayaran →
                </button>
              </div>
            </motion.div>

            {/* ===== STEP 3: Pembayaran ===== */}
            <motion.div
              animate={{ opacity: step === 3 ? 1 : 0.4 }}
              className={step === 3 ? "block" : "hidden"}
            >
              <h3 className="text-sm font-bold text-white mb-4">
                Pilih Opsi Pembayaran
              </h3>

              <div className="grid sm:grid-cols-2 gap-3 mb-6">
                {[
                  {
                    value: "dp" as const,
                    title: "Bayar DP (30%)",
                    amount: Number(pkg.price) * 0.3,
                    desc: "Sisa 70% bisa dilunasi via Midtrans atau langsung di studio.",
                    badge: "Hemat",
                  },
                  {
                    value: "full" as const,
                    title: "Bayar Lunas (100%)",
                    amount: Number(pkg.price),
                    desc: "Lunasi sekarang, tidak ada sisa tagihan.",
                    badge: "Recommended",
                  },
                ].map((opt) => (
                  <motion.label
                    key={opt.value}
                    whileHover={reduceMotion ? undefined : { y: -3 }}
                    transition={{ type: "spring", stiffness: 320, damping: 22 }}
                    className={`relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors ${
                      formData.payment_option === opt.value
                        ? "border-blue-400/40 bg-blue-500/10"
                        : "border-white/10 bg-[#0b1220] hover:border-white/20"
                    }`}
                  >
                    <input
                      type="radio"
                      name="payment_option"
                      value={opt.value}
                      checked={formData.payment_option === opt.value}
                      onChange={() =>
                        setFormData({ ...formData, payment_option: opt.value })
                      }
                      className="mt-0.5 w-4 h-4 accent-blue-600"
                    />
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-1">
                        <span className="font-bold text-white text-sm">
                          {opt.title}
                        </span>
                        <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-300">
                          {opt.badge}
                        </span>
                      </div>
                      <span className="block text-lg font-extrabold text-white">
                        Rp {opt.amount.toLocaleString("id-ID")}
                      </span>
                      <span className="block text-xs text-slate-400 mt-1 leading-relaxed">
                        {opt.desc}
                      </span>
                    </div>
                  </motion.label>
                ))}
              </div>

              {/* Ringkasan tagihan */}
              <div className="bg-gradient-to-br from-white/[0.06] to-white/[0.02] border border-blue-400/25 rounded-2xl p-5 mb-6">
                <div className="flex justify-between items-center mb-2">
                  <span className="text-sm font-semibold text-slate-400">
                    Harga paket normal
                  </span>
                  <span className="font-bold text-white">
                    Rp {Number(pkg.price).toLocaleString("id-ID")}
                  </span>
                </div>
                {formData.payment_option === "dp" && (
                  <div className="flex justify-between items-center mb-2">
                    <span className="text-sm font-semibold text-slate-400">
                      Sisa yang harus dibayar nanti
                    </span>
                    <span className="font-bold text-slate-400">
                      Rp{" "}
                      {(Number(pkg.price) * 0.7).toLocaleString("id-ID")}
                    </span>
                  </div>
                )}
                <div className="flex justify-between items-center pt-2 border-t border-blue-400/25/70">
                  <span className="text-sm font-bold text-slate-100">
                    Total dibayar sekarang
                  </span>
                  <span className="text-2xl font-extrabold text-blue-300">
                    Rp {amountToPay.toLocaleString("id-ID")}
                  </span>
                </div>
                <p className="text-[11px] text-blue-400 mt-2">
                  Pembayaran diproses aman melalui Midtrans. Slot terkunci 15
                  menit setelah booking dibuat.
                </p>
              </div>

              {/* Ringkasan pilihan */}
              {selectedSlot && (
                <div className="flex flex-wrap gap-2 mb-6">
                  <Badge tone="blue">
                    <CalendarDays className="w-3 h-3" />
                    {format(new Date(selectedSlot.date), "d MMM yyyy")}
                  </Badge>
                  <Badge tone="sky">
                    <Clock className="w-3 h-3" />
                    {selectedSlot.start_time.substring(0, 5)} WIB
                  </Badge>
                  {formData.customer_name && (
                    <Badge tone="slate">
                      <User className="w-3 h-3" />
                      {formData.customer_name}
                    </Badge>
                  )}
                </div>
              )}

              <div className="flex gap-3">
                <button
                  type="button"
                  onClick={() => setStep(2)}
                  className="px-5 py-3 bg-[#0b1220] border border-white/10 text-slate-200 rounded-xl text-sm font-semibold hover:bg-[#0b1220] transition-colors"
                >
                  ← Kembali
                </button>
                <button
                  type="submit"
                  disabled={loading || !selectedSlot}
                  className="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-blue-500 to-cyan-400 text-white rounded-xl text-sm font-semibold shadow-[0_12px_30px_-12px_rgba(37,99,235,0.75)] hover:shadow-[0_16px_38px_-12px_rgba(37,99,235,0.85)] transition-shadow disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {loading ? (
                    <>
                      <Loader2 className="w-4 h-4 animate-spin" /> Memproses...
                    </>
                  ) : (
                    <>
                      <PartyPopper className="w-4 h-4" /> Lanjut ke Pembayaran
                      (Rp {amountToPay.toLocaleString("id-ID")})
                    </>
                  )}
                </button>
              </div>
            </motion.div>
          </form>

          {/* Sukses */}
          {success && (
            <motion.div
              initial={{ opacity: 0, scale: 0.96 }}
              animate={{ opacity: 1, scale: 1 }}
              className="mt-6 p-6 bg-emerald-500/10 border border-emerald-400/25 rounded-2xl text-center"
            >
              <div className="w-14 h-14 bg-emerald-500/100 rounded-full flex items-center justify-center mx-auto mb-3">
                <CheckCircle2 className="w-7 h-7 text-white" />
              </div>
              <h3 className="text-emerald-900 font-bold text-lg mb-1">
                Booking Berhasil!
              </h3>
              <p className="text-emerald-300 text-sm">
                Anda akan dialihkan ke halaman pembayaran.
              </p>
            </motion.div>
          )}
        </div>
      </div>

      {/* Lightbox Overlay */}
      {selectedImage && (
        <div
          className="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 backdrop-blur-md p-4"
          onClick={() => setSelectedImage(null)}
        >
          <button
            className="absolute top-6 right-6 p-2 text-white/70 hover:text-emerald-400 rounded-full hover:bg-white/10 transition-all"
            onClick={(e) => {
              e.stopPropagation();
              setSelectedImage(null);
            }}
          >
            <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <img
            src={selectedImage}
            alt="Enlarged view"
            className="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl border border-white/10"
            onClick={(e) => e.stopPropagation()}
          />
        </div>
      )}
    </div>
  );
}
