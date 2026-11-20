"use client";

import { use, useCallback, useEffect, useState } from "react";
import { motion, useReducedMotion } from "motion/react";
import { api } from "@/lib/api";
import Link from "next/link";
import { useRouter } from "next/navigation";
import {
  ArrowLeft,
  CheckCircle2,
  CircleDollarSign,
  Clock,
  Loader2,
  Mail,
  Phone,
  RefreshCw,
  Star,
  StickyNote,
  Timer,
  Wallet,
  XCircle,
} from "lucide-react";
import Badge from "@/components/Badge";
import Countdown from "@/components/Countdown";
import { Skeleton } from "@/components/Skeleton";
import SpecularButton from "@/components/SpecularButton";
import { Bokeh, SparkleDeco } from "@/components/StudioDecor";

const STATUS_LABEL: Record<string, string> = {
  pending: "Menunggu Pembayaran",
  confirmed: "Dikonfirmasi",
  in_progress: "Sedang Berjalan",
  completed: "Selesai",
  canceled: "Dibatalkan",
};

const POLL_INTERVAL = 20000;
  const MAX_POLL_RETRIES = 20; // Temuan #10: batas maksimal polling retry

const MONTHS_ID = [
  "Jan",
  "Feb",
  "Mar",
  "Apr",
  "Mei",
  "Jun",
  "Jul",
  "Agu",
  "Sep",
  "Okt",
  "Nov",
  "Des",
];

/**
 * Format tanggal sesi tanpa pergeseran zona waktu.
 * API mengirim "2026-10-21T00:00:00.000000Z" -> "21 Okt 2026".
 */
function formatSessionDate(raw?: string | null): string {
  if (!raw) return "-";

  const [year, month, day] = String(raw).slice(0, 10).split("-");

  if (!year || !month || !day) return String(raw);

  return `${day} ${MONTHS_ID[Number(month) - 1] ?? month} ${year}`;
}

export default function OrderView({
  params,
}: {
  params: Promise<{ code: string }>;
}) {
  const { code } = use(params);
  const router = useRouter();
  const reduceMotion = useReducedMotion();

  const [order, setOrder] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [paying, setPaying] = useState(false);
  const [extending, setExtending] = useState(false);

  // Form rating / ulasan
  const [starValue, setStarValue] = useState(0);
  const [starHover, setStarHover] = useState(0);
  const [reviewContact, setReviewContact] = useState("");
  const [reviewComment, setReviewComment] = useState("");
  const [submittingReview, setSubmittingReview] = useState(false);

  const loadOrder = useCallback(
    async (silent = false) => {
      if (silent) setRefreshing(true);
      else setLoading(true);

      try {
        const res = await api.getOrder(code);
        setOrder(res.data);
        setError(null);
      } catch (err: any) {
        if (!silent) setError(err.message || "Pesanan tidak ditemukan.");
      } finally {
        setLoading(false);
        setRefreshing(false);
      }
    },
    [code]
  );

  // Fetch data pesanan saat komponen dimuat / kode berubah.
  useEffect(() => {
    loadOrder();
  }, [loadOrder]);

  // Polling saat masih menunggu pembayaran
  const hasPending = order?.can_pay_initial === true;

  // Temuan #10: counter retry untuk batasi polling DoS
  const [pollRetryCount, setPollRetryCount] = useState(0);

  // Polling untuk memperbarui status pembayaran dari server.
  useEffect(() => {
    if (!hasPending) return;

    const interval = setInterval(() => {
      // a) Jika tab tidak aktif, skip request
      if (document.visibilityState !== "visible") return;

      // b) Batasi maksimal retry untuk mencegah DoS
      if (pollRetryCount >= MAX_POLL_RETRIES) {
        console.log(`[Polling] Max retries (${MAX_POLL_RETRIES}) reached. Stopping.`);
        clearInterval(interval);
        return;
      }

      loadOrder(true);
      setPollRetryCount((c) => c + 1);
    }, POLL_INTERVAL);

    return () => clearInterval(interval);
  }, [hasPending, loadOrder, pollRetryCount]);

  const handlePayInitial = () => {
    if (!order?.snap_token) {
      setError("Token pembayaran tidak tersedia. Muat ulang halaman.");
      return;
    }
    const amount = order.pending_payment?.amount ?? 0;
    
    // Temuan #6: Sembunyikan Snap Token dari URL, gunakan sessionStorage
    if (typeof window !== "undefined") {
      sessionStorage.setItem("midtrans_snap_token", order.snap_token);
    }

    router.push(
      `/pesanan/${code}/bayar?code=${encodeURIComponent(
        code
      )}&amount=${amount}`
    );
  };

  const handlePayBalance = async () => {
    setPaying(true);
    setError(null);

    try {
      const res = await api.payBalance(code);
      if (res.success && res.data?.snap_token) {
        // Temuan #6: Sembunyikan Snap Token dari URL
        if (typeof window !== "undefined") {
          sessionStorage.setItem("midtrans_snap_token", res.data.snap_token);
        }

        router.push(
          `/pesanan/${code}/bayar?code=${encodeURIComponent(
            code
          )}&amount=${res.data.amount}`
        );
        return;
      }
    } catch (err: any) {
      setError(err.message || "Gagal membuat pembayaran.");
    } finally {
      setPaying(false);
    }
  };

  const handleExtend = async () => {
    setExtending(true);
    setError(null);

    try {
      await api.extendDeadline(code);
      loadOrder();
    } catch (err: any) {
      setError(err.message || "Gagal memperpanjang waktu pembayaran.");
    } finally {
      setExtending(false);
    }
  };

  // Kirim ulasan (butuh email/HP yang cocok dengan booking sebagai verifikasi).
  const handleSubmitReview = async () => {
    const contact = reviewContact.trim();

    if (!starValue) {
      setError("Pilih jumlah bintang dulu.");
      return;
    }

    if (!contact) {
      setError("Isi email atau nomor WhatsApp yang dipakai saat booking.");
      return;
    }

    setSubmittingReview(true);
    setError(null);

    try {
      await api.submitReview({
        booking_code: code,
        ...(contact.includes("@") ? { email: contact } : { phone: contact }),
        rating: starValue,
        comment: reviewComment.trim() || null,
      });

      await loadOrder(true);
    } catch (err: any) {
      setError(err.message || "Gagal menyimpan ulasan.");
    } finally {
      setSubmittingReview(false);
    }
  };

  if (loading) {
    return (
      <div className="relative min-h-screen bg-[#fdfbf7] dark:bg-[#110f0e] text-slate-900 dark:text-white overflow-hidden">
        <div className="pointer-events-none absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full aurora-tl opacity-60 dark:opacity-30 blur-3xl" aria-hidden />
        <div className="pointer-events-none absolute top-1/3 -right-40 w-[600px] h-[600px] rounded-full aurora-tr opacity-50 dark:opacity-25 blur-3xl" aria-hidden />
        <Bokeh count={4} />
        <SparkleDeco count={3} />
        <div className="max-w-3xl mx-auto px-4 sm:px-6 py-10 space-y-5 relative">
          <Skeleton className="h-12 w-40 rounded-xl" />
          <Skeleton className="h-48 w-full rounded-2xl" />
          <Skeleton className="h-40 w-full rounded-2xl" />
          <Skeleton className="h-64 w-full rounded-2xl" />
        </div>
      </div>
    );
  }

  if (error && !order) {
    return (
      <div className="min-h-screen flex items-center justify-center px-4">
        <div className="max-w-md w-full text-center bg-[#0b1220] p-8 rounded-2xl shadow-soft border border-white/10">
          <XCircle className="w-12 h-12 text-rose-400 mx-auto mb-4" />
          <h2 className="text-xl font-bold text-white mb-2">
            Pesanan Tidak Ditemukan
          </h2>
          <p className="text-slate-400 mb-6">{error}</p>
          <Link
            href="/pesanan"
            className="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-500 hover:bg-blue-400 text-white rounded-xl text-sm font-semibold transition-colors"
          >
            <ArrowLeft className="w-4 h-4" /> Cari Pesanan Lain
          </Link>
        </div>
      </div>
    );
  }

  const status = order.booking_status || "pending";
  const isCanceled = status === "canceled";
  const isCompleted = status === "completed";

  const badgeTone = isCompleted || order.is_fully_paid
    ? "emerald"
    : isCanceled
      ? "slate"
      : order.payment_status === "partially_paid"
        ? "blue"
        : "amber";

  // Timeline status
  const timeline = [
    {
      label: "Booking Dibuat",
      done: true,
      desc: order.booking_date_label,
      icon: StickyNote,
    },
    {
      label:
        order.payment_status === "partially_paid" || order.is_fully_paid
          ? "DP Terverifikasi"
          : "Menunggu Pembayaran",
      done: order.payment_status !== "unpaid",
      desc:
        order.total_paid > 0
          ? `Rp ${Number(order.total_paid).toLocaleString("id-ID")}`
          : "Belum ada pembayaran",
      icon: Wallet,
    },
    {
      label: order.is_fully_paid ? "Lunas" : "Pelunasan Sisa",
      done: order.is_fully_paid,
      desc: order.is_fully_paid
        ? "Semua tagihan terbayar"
        : `Sisa Rp ${Number(order.remaining).toLocaleString("id-ID")}`,
      icon: CircleDollarSign,
    },
    {
      label: "Foto Dikirim",
      done: isCompleted,
      desc: isCompleted ? "Selesai" : "Menunggu admin studio",
      icon: CheckCircle2,
    },
  ];

  return (
    <div className="relative min-h-screen bg-[#fdfbf7] dark:bg-[#110f0e] text-slate-900 dark:text-white transition-all duration-300 overflow-hidden">
      <div className="pointer-events-none absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full aurora-tl opacity-60 dark:opacity-30 blur-3xl" aria-hidden />
      <div className="pointer-events-none absolute top-1/3 -right-40 w-[600px] h-[600px] rounded-full aurora-tr opacity-50 dark:opacity-25 blur-3xl" aria-hidden />
      <Bokeh count={4} />
      <SparkleDeco count={3} />

      {/* Navbar halaman */}
      <div className="relative backdrop-blur-xl bg-white/70 dark:bg-black/30 border-b border-white/60 dark:border-white/15">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
          <Link
            href="/pesanan"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-[#d4af37] transition-colors"
          >
            <ArrowLeft className="w-4 h-4" /> Kembali
          </Link>
          <div className="flex items-center gap-3">
            <button
              onClick={() => loadOrder(true)}
              disabled={refreshing}
              className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-[#d4af37] disabled:opacity-50 transition"
            >
              <RefreshCw
                className={`w-3.5 h-3.5 ${refreshing ? "animate-spin" : ""}`}
              />
              Refresh
            </button>
            <span className="text-sm font-bold text-slate-900 dark:text-white">
              #{order.booking_code}
            </span>
          </div>
        </div>
      </div>

      <div className="relative max-w-3xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        {error && (
          <motion.div
            initial={{ opacity: 0, y: -6 }}
            animate={{ opacity: 1, y: 0 }}
            className="p-4 rounded-xl bg-rose-500/10 border border-rose-400/20 text-rose-300 text-sm"
          >
            {error}
          </motion.div>
        )}

        {isCanceled && (
          <div className="p-4 rounded-xl bg-[#0b1220] border border-white/20 text-slate-200 text-sm flex items-start gap-2.5">
            <XCircle className="w-4.5 h-4.5 mt-0.5 shrink-0" />
            <span>
              Pesanan ini <strong>dibatalkan</strong> karena pembayaran tidak
              diselesaikan dalam batas waktu. Silakan buat pesanan baru.
            </span>
          </div>
        )}

        {/* Ringkasan order */}
        <motion.div
          initial={reduceMotion ? false : { opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.45, ease: [0.22, 1, 0.36, 1] }}
          className="bg-[#0b1220] rounded-2xl border border-white/10 p-6 shadow-soft"
        >
          <div className="flex items-start justify-between gap-4 mb-5">
            <div>
              <Badge tone="blue">{order.package?.category ?? "Studio"}</Badge>
              <h1 className="text-xl font-bold text-white mt-2 mb-1">
                {order.package?.name ?? "Paket Foto"}
              </h1>
              <p className="text-sm text-slate-400">
                {order.package?.description ?? ""}
              </p>
            </div>
            <Badge tone={badgeTone} dot>
              {STATUS_LABEL[status] ?? status}
            </Badge>
          </div>

          <div className="grid grid-cols-2 gap-4 pt-5 border-t border-white/10 text-sm">
            <div>
              <span className="text-slate-400 block text-xs mb-0.5">
                Tanggal Sesi
              </span>
              <span className="font-bold text-white flex items-center gap-1.5">
                <Clock className="w-3.5 h-3.5 text-slate-400" />
                {order.booking_date_label || formatSessionDate(order.booking_date)} ·{" "}
                {order.start_time} WIB
              </span>
            </div>
            <div>
              <span className="text-slate-400 block text-xs mb-0.5">
                Total Paket
              </span>
              <span className="font-bold text-white">
                Rp {Number(order.total_amount).toLocaleString("id-ID")}
              </span>
            </div>
          </div>
        </motion.div>

        {/* Ringkasan pembayaran */}
        <motion.div
          initial={reduceMotion ? false : { opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.45, delay: 0.08, ease: [0.22, 1, 0.36, 1] }}
          className="bg-[#0b1220] rounded-2xl border border-white/10 p-6 shadow-soft"
        >
          <div className="flex items-center justify-between mb-5">
            <h2 className="text-sm font-bold text-slate-200 uppercase tracking-wider">
              Ringkasan Pembayaran
            </h2>
            <Badge
              tone={
                order.payment_status === "paid"
                  ? "emerald"
                  : order.payment_status === "partially_paid"
                    ? "blue"
                    : "amber"
              }
            >
              {
                order.payment_status === "unpaid"
                  ? "Belum Bayar"
                  : order.payment_status === "partially_paid"
                    ? "DP Terbayar"
                    : order.payment_status === "paid"
                      ? "Lunas"
                      : order.payment_status
              }
            </Badge>
          </div>

          <div className="space-y-2.5 text-sm">
            <div className="flex justify-between">
              <span className="text-slate-400">Total tagihan</span>
              <span className="font-semibold text-white">
                Rp {Number(order.total_amount).toLocaleString("id-ID")}
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-400">Sudah dibayar</span>
              <span className="font-semibold text-emerald-400">
                Rp {Number(order.total_paid).toLocaleString("id-ID")}
              </span>
            </div>
            <div className="flex justify-between pt-3 border-t border-white/10">
              <span className="font-semibold text-slate-200">Sisa tagihan</span>
              <span
                className={`font-extrabold text-lg ${
                  Number(order.remaining) > 0
                    ? "text-rose-400"
                    : "text-emerald-400"
                }`}
              >
                Rp {Number(order.remaining).toLocaleString("id-ID")}
              </span>
            </div>
          </div>

          {/* Countdown */}
          {order.can_pay_initial && order.payment_deadline && (
            <div className="mt-5 pt-5 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <Countdown deadline={order.payment_deadline} size={70} />
              {order.can_extend_deadline && (
                <button
                  onClick={handleExtend}
                  disabled={extending}
                  className="inline-flex items-center gap-1.5 self-start text-xs font-semibold text-blue-400 hover:text-blue-300 hover:underline disabled:opacity-50 transition"
                >
                  {extending ? (
                    <Loader2 className="w-3.5 h-3.5 animate-spin" />
                  ) : (
                    <Timer className="w-3.5 h-3.5" />
                  )}
                  Perpanjang 15 menit
                </button>
              )}
            </div>
          )}
        </motion.div>

        {/* Timeline status */}
        <motion.div
          initial={reduceMotion ? false : { opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.45, delay: 0.14, ease: [0.22, 1, 0.36, 1] }}
          className="bg-[#0b1220] rounded-2xl border border-white/10 p-6 shadow-soft"
        >
          <h2 className="text-sm font-bold text-slate-200 uppercase tracking-wider mb-5">
            Status Pesanan
          </h2>

          <div className="space-y-0">
            {timeline.map((item, i) => (
              <div key={i} className="flex gap-4">
                {/* Marker + garis */}
                <div className="flex flex-col items-center">
                  <div
                    className={`w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-colors ${
                      item.done
                        ? "bg-emerald-500/100 text-white shadow-[0_6px_16px_-6px_rgba(5,150,105,0.7)]"
                        : "bg-[#0b1220] text-slate-400"
                    }`}
                  >
                    <item.icon className="w-4 h-4" />
                  </div>
                  {i < timeline.length - 1 && (
                    <div
                      className={`w-0.5 flex-1 my-1 rounded ${
                        item.done ? "bg-emerald-400/30" : "bg-white/10"
                      }`}
                    />
                  )}
                </div>

                {/* Teks */}
                <div className={`pb-5 ${i === timeline.length - 1 ? "pb-0" : ""}`}>
                  <p
                    className={`text-sm font-semibold ${
                      item.done ? "text-white" : "text-slate-400"
                    }`}
                  >
                    {item.label}
                  </p>
                  <p className="text-xs text-slate-400 mt-0.5">{item.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </motion.div>

        {/* Data pemesan */}
        <motion.div
          initial={reduceMotion ? false : { opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.45, delay: 0.2, ease: [0.22, 1, 0.36, 1] }}
          className="bg-[#0b1220] rounded-2xl border border-white/10 p-6 shadow-soft"
        >
          <h2 className="text-sm font-bold text-slate-200 uppercase tracking-wider mb-4">
            Data Pemesan
          </h2>
          <div className="space-y-3 text-sm">
            <div className="flex justify-between items-center">
              <span className="text-slate-400">Nama</span>
              <span className="text-white font-medium">
                {order.customer_name}
              </span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-slate-400">Email</span>
              <span className="text-white inline-flex items-center gap-1.5">
                <Mail className="w-3.5 h-3.5 text-slate-400" />
                {order.customer_email}
              </span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-slate-400">WhatsApp</span>
              <span className="text-white inline-flex items-center gap-1.5">
                <Phone className="w-3.5 h-3.5 text-slate-400" />
                {order.customer_phone}
              </span>
            </div>
            {order.notes && (
              <div className="flex justify-between gap-4">
                <span className="text-slate-400 shrink-0">Catatan</span>
                <span className="text-white text-right">{order.notes}</span>
              </div>
            )}
          </div>
        </motion.div>

        {/* Riwayat pembayaran */}
        <motion.div
          initial={reduceMotion ? false : { opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.45, delay: 0.26, ease: [0.22, 1, 0.36, 1] }}
          className="bg-[#0b1220] rounded-2xl border border-white/10 p-6 shadow-soft"
        >
          <h2 className="text-sm font-bold text-slate-200 uppercase tracking-wider mb-4">
            Riwayat Pembayaran
          </h2>
          {!order.payments || order.payments.length === 0 ? (
            <p className="text-slate-400 text-sm">Belum ada pembayaran.</p>
          ) : (
            <div className="space-y-2.5">
              {order.payments.map((payment: any) => (
                <div
                  key={payment.id}
                  className="flex items-center justify-between p-3.5 bg-[#0b1220] rounded-xl border border-white/10"
                >
                  <div>
                    <span className="font-bold text-slate-200 uppercase text-[10px]">
                      {payment.payment_type}
                    </span>
                    <span className="block text-xs text-slate-400">
                      {payment.payment_method}
                    </span>
                  </div>
                  <div className="text-right">
                    <span className="font-bold text-white">
                      Rp {Number(payment.amount).toLocaleString("id-ID")}
                    </span>
                    <span
                      className={`block text-xs font-bold ${
                        payment.status === "verified"
                          ? "text-emerald-400"
                          : payment.status === "rejected"
                            ? "text-rose-400"
                            : "text-amber-400"
                      }`}
                    >
                      {payment.status === "verified"
                        ? "Terverifikasi"
                        : payment.status === "rejected"
                          ? "Dibatalkan"
                          : "Menunggu"}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </motion.div>

        {/* Aksi pembayaran */}
        {order.can_pay_initial && (
          <motion.div
            initial={reduceMotion ? false : { opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.45, delay: 0.3, ease: [0.22, 1, 0.36, 1] }}
            className="bg-[#0b1220] rounded-2xl border-2 border-blue-400/25 p-6 shadow-soft text-center"
          >
            <div className="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center mx-auto mb-3">
              <Wallet className="w-5.5 h-5.5 text-blue-400" />
            </div>
            <h2 className="text-sm font-bold text-slate-200 uppercase tracking-wider mb-2">
              Pembayaran {order.pending_payment?.payment_type === "full" ? "Lunas" : "DP (30%)"}
            </h2>
            <p className="text-slate-400 text-sm mb-1">
              Selesaikan pembayaran{" "}
              <span className="font-bold text-white">
                Rp{" "}
                {Number(order.pending_payment?.amount ?? 0).toLocaleString("id-ID")}
              </span>{" "}
              untuk mengonfirmasi pesanan Anda.
            </p>
            <p className="text-xs text-slate-400 mb-5">
              {order.pending_payment?.payment_type === "full"
                ? "Pembayaran penuh agar tidak ada sisa tagihan."
                : "Sisa 70% bisa dilunasi nanti via Midtrans atau langsung di studio."}
            </p>
            <SpecularButton
              size="lg"
              radius={40}
              tint="#ffffff"
              tintOpacity={0.04}
              textColor="#f5f5f5"
              lineColor="#ffffff"
              baseColor="#224ad9"
              intensity={1.25}
              idleIntensity={0.35}
              shineSize={10}
              shineFade={36}
              thickness={1.8}
              glow={1}
              followMouse
              proximity={170}
              onClick={handlePayInitial}
              className="w-full !py-3.5"
            >
              Bayar {order.pending_payment?.payment_type === "full" ? "Lunas" : "DP (30%)"} via Midtrans
            </SpecularButton>
          </motion.div>
        )}

        {order.can_pay_balance && (
          <motion.div
            initial={reduceMotion ? false : { opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.45, delay: 0.3, ease: [0.22, 1, 0.36, 1] }}
            className="bg-[#0b1220] rounded-2xl border-2 border-emerald-400/25 p-6 shadow-soft text-center"
          >
            <div className="w-12 h-12 bg-emerald-500/15 rounded-xl flex items-center justify-center mx-auto mb-3">
              <Wallet className="w-5.5 h-5.5 text-emerald-400" />
            </div>
            <h2 className="text-sm font-bold text-slate-200 uppercase tracking-wider mb-2">
              Pelunasan Sisa Tagihan
            </h2>
            <p className="text-slate-400 text-sm mb-5">
              Sisa sebesar{" "}
              <span className="font-bold text-white">
                Rp {Number(order.remaining).toLocaleString("id-ID")}
              </span>{" "}
              perlu dilunasi sebelum foto dikirim. Bisa juga dilunasi langsung di
              studio (tunai/transfer).
            </p>
            <SpecularButton
              variant="primary"
              size="lg"
              radius={40}
              tint="#ffffff"
              tintOpacity={0.04}
              textColor="#f5f5f5"
              lineColor="#ffffff"
              baseColor="#224ad9"
              intensity={1.25}
              idleIntensity={0.35}
              shineSize={10}
              shineFade={36}
              thickness={1.8}
              glow={1}
              followMouse
              proximity={170}
              onClick={handlePayBalance}
              disabled={paying}
              className="w-full !py-3.5"
            >
              {paying ? (
                <>
                  <Loader2 className="w-4 h-4 animate-spin" /> Memproses...
                </>
              ) : (
                "Bayar Sisa via Midtrans"
              )}
            </SpecularButton>
          </motion.div>
        )}

        {order.is_fully_paid && !isCompleted && (
          <motion.div
            initial={reduceMotion ? false : { opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-emerald-500/10 rounded-2xl border border-emerald-400/25 p-6 text-center"
          >
            <div className="w-12 h-12 bg-emerald-500/100 rounded-full flex items-center justify-center mx-auto mb-3">
              <CheckCircle2 className="w-6 h-6 text-white" />
            </div>
            <h2 className="text-sm font-bold text-emerald-800 uppercase tracking-wider mb-2">
              Pesanan Sudah Lunas
            </h2>
            <p className="text-emerald-300 text-sm">
              Silakan tunggu admin studio mengirim link hasil foto melalui
              WhatsApp Anda.
            </p>
          </motion.div>
        )}

        {/* ══ ULASAN / RATING ══ */}
        {isCompleted && order.review && (
          <motion.div
            initial={reduceMotion ? false : { opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-[#0b1220] rounded-2xl border border-white/10 p-6 shadow-soft"
          >
            <div className="flex items-center gap-3 mb-3">
              <div className="w-10 h-10 rounded-xl bg-amber-400/15 flex items-center justify-center shrink-0">
                <Star className="w-5 h-5 text-amber-300 fill-amber-300" />
              </div>
              <div>
                <h2 className="text-sm font-bold text-white uppercase tracking-wider">
                  Ulasan Anda
                </h2>
                <p className="text-xs text-slate-400">
                  Terima kasih atas ulasan Anda untuk studio ini.
                </p>
              </div>
            </div>
            <div className="flex items-center gap-1 mb-3">
              {[1, 2, 3, 4, 5].map((n) => (
                <Star
                  key={n}
                  className={`w-4 h-4 ${
                    order.review.rating >= n
                      ? "text-amber-300 fill-amber-300"
                      : "text-slate-600"
                  }`}
                />
              ))}
            </div>
            {order.review.comment && (
              <p className="text-sm text-slate-300 italic">
                &quot;{order.review.comment}&quot;
              </p>
            )}
          </motion.div>
        )}

        {isCompleted && order.can_review && (
          <motion.div
            initial={reduceMotion ? false : { opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-[#0b1220] rounded-2xl border border-amber-400/25 p-6 shadow-soft"
          >
            <div className="flex items-center gap-3 mb-4">
              <div className="w-10 h-10 rounded-xl bg-amber-400/15 flex items-center justify-center shrink-0">
                <Star className="w-5 h-5 text-amber-300" />
              </div>
              <div>
                <h2 className="text-sm font-bold text-white uppercase tracking-wider">
                  Beri Rating Studio
                </h2>
                <p className="text-xs text-slate-400">
                  Bagaimana pengalaman sesi fotomu? Ulasanmu tampil di profil studio.
                </p>
              </div>
            </div>

            <div className="flex items-center gap-1 mb-5">
              {[1, 2, 3, 4, 5].map((n) => (
                <button
                  key={n}
                  type="button"
                  aria-label={`${n} bintang`}
                  onClick={() => setStarValue(n)}
                  onMouseEnter={() => setStarHover(n)}
                  onMouseLeave={() => setStarHover(0)}
                  className="p-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60 rounded-lg"
                >
                  <Star
                    className={`w-8 h-8 transition-colors ${
                      (starHover || starValue) >= n
                        ? "text-amber-300 fill-amber-300"
                        : "text-slate-600 hover:text-slate-400"
                    }`}
                  />
                </button>
              ))}
              {starValue > 0 && (
                <span className="ml-2 text-sm font-semibold text-amber-300">
                  {starValue}/5
                </span>
              )}
            </div>

            <div className="space-y-3">
              <div>
                <label className="block text-xs font-semibold text-slate-400 mb-1.5">
                  Email / No. WhatsApp (untuk verifikasi)
                </label>
                <input
                  type="text"
                  value={reviewContact}
                  onChange={(e) => setReviewContact(e.target.value)}
                  placeholder="contoh: nama@email.com atau 0812xxxx"
                  className="w-full px-4 py-3 border border-white/10 rounded-xl text-sm bg-[#0b1220] focus:ring-2 focus:ring-amber-300/50 focus:border-amber-300/30 outline-none transition placeholder:text-slate-500"
                />
                <p className="text-[11px] text-slate-500 mt-1">
                  Harus sama dengan data yang dipakai saat booking.
                </p>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-400 mb-1.5">
                  Komentar (opsional)
                </label>
                <textarea
                  value={reviewComment}
                  onChange={(e) => setReviewComment(e.target.value)}
                  rows={3}
                  maxLength={500}
                  placeholder="Ceritakan pengalamanmu di studio ini..."
                  className="w-full px-4 py-3 border border-white/10 rounded-xl text-sm bg-[#0b1220] focus:ring-2 focus:ring-amber-300/50 focus:border-amber-300/30 outline-none transition placeholder:text-slate-500 resize-none"
                />
              </div>
            </div>

            <SpecularButton
              size="md"
              radius={40}
              tint="#ffffff"
              tintOpacity={0.04}
              textColor="#f5f5f5"
              lineColor="#ffffff"
              baseColor="#224ad9"
              intensity={1.25}
              idleIntensity={0.35}
              shineSize={10}
              shineFade={36}
              thickness={1.8}
              glow={1}
              followMouse
              proximity={170}
              onClick={handleSubmitReview}
              disabled={submittingReview || !starValue}
              className="w-full mt-4 !py-3"
            >
              {submittingReview ? (
                <>
                  <Loader2 className="w-4 h-4 animate-spin" /> Menyimpan...
                </>
              ) : (
                <>
                  <Star className="w-4 h-4" /> Kirim Rating
                </>
              )}
            </SpecularButton>
          </motion.div>
        )}
      </div>
    </div>
  );
}
