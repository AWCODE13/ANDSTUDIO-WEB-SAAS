"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { api } from "@/lib/api";
import Link from "next/link";
import { useRouter } from "next/navigation";
import {
  ArrowLeft,
  ArrowRight,
  CalendarDays,
  CheckCircle2,
  Clock,
  History,
  Loader2,
  MessageCircle,
  RefreshCw,
  Search,
  Star,
  Wallet,
} from "lucide-react";
import Badge from "@/components/Badge";
import { SkeletonList } from "@/components/Skeleton";
import SpecularButton from "@/components/SpecularButton";
import StarBorder from "@/components/StarBorder";
import { Bokeh, SparkleDeco, StudioBeam } from "@/components/StudioDecor";

const STATUS_LABEL: Record<string, string> = {
  pending: "Menunggu Pembayaran",
  confirmed: "Dikonfirmasi",
  in_progress: "Sedang Berjalan",
  completed: "Selesai",
  canceled: "Dibatalkan",
};

const POLL_INTERVAL = 20000;

export default function PesananPage() {
  const [query, setQuery] = useState("");
  const [orders, setOrders] = useState<any[]>([]);
  const [searched, setSearched] = useState(false);
  const [loading, setLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();
  const lastQuery = useRef("");
  const reduceMotion = useReducedMotion();

  const fetchOrders = useCallback(
    async (q: string, silent = false): Promise<any[]> => {
      if (!q) return [];

      if (silent) setRefreshing(true);
      else setLoading(true);

      try {
        const res = await api.lookupOrder(q);
        const result = res.data.orders || [];
        setOrders(result);
        setError(null);
        return result;
      } catch (err: any) {
        setOrders([]);
        setError(
          err.message ||
            "Order tidak ditemukan. Periksa kembali kode booking, email, atau nomor telepon Anda."
        );
        return [];
      } finally {
        setLoading(false);
        setRefreshing(false);
        setSearched(true);
      }
    },
    []
  );

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    lastQuery.current = query;

    const result = await fetchOrders(query);

    if (result.length === 1) {
      router.push(`/pesanan/${result[0].booking_code}`);
    }
  };

  const handleRefresh = () => {
    if (lastQuery.current) fetchOrders(lastQuery.current, true);
  };

  // Polling saat ada pesanan menunggu & tab aktif
  const hasPending = orders.some(
    (o) => o.can_pay_initial || o.can_pay_balance
  );

  useEffect(() => {
    if (!searched || !lastQuery.current || !hasPending) return;

    const interval = setInterval(() => {
      if (document.visibilityState === "visible") {
        fetchOrders(lastQuery.current, true);
      }
    }, POLL_INTERVAL);

    return () => clearInterval(interval);
  }, [searched, hasPending, fetchOrders]);

  return (
    <main className="relative min-h-[100dvh] bg-transparent text-white transition-all duration-300">

      {/* LAYER 1: Background Cahaya Studio (z-0) */}
      <div className="absolute inset-0 z-0 pointer-events-none transform-gpu will-change-transform">
        <Bokeh count={5} />
        <StudioBeam />
        <SparkleDeco count={4} />
      </div>

      {/* LAYER 2: Konten (z-10, bg-transparent) */}
      <div className="relative z-10 bg-transparent">
        <div className="relative max-w-2xl mx-auto px-4 sm:px-6 py-12">
        {/* Card pencarian */}
        <motion.div
          initial={reduceMotion ? false : { opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
          className="backdrop-blur-xl bg-white/70 dark:bg-black/30 rounded-3xl border border-white/60 dark:border-white/15 p-7 sm:p-8 shadow-xl"
        >
          <div className="text-center mb-7">
            <div className="w-14 h-14 bg-emerald-500/15 border border-emerald-500/25 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
              <History className="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
            </div>
            <h1 className="text-2xl font-bold text-slate-900 dark:text-white mb-1.5">
              Cek Pesanan
            </h1>
            <p className="text-slate-600 dark:text-slate-400 text-sm">
              Masukkan kode booking, email, atau nomor telepon Anda
            </p>
          </div>

          {error && (
            <motion.div
              initial={{ opacity: 0, y: -6 }}
              animate={{ opacity: 1, y: 0 }}
              className="mb-5 p-4 rounded-xl bg-rose-500/10 border border-rose-400/20 text-rose-300 text-sm"
            >
              {error}
            </motion.div>
          )}

          <form onSubmit={handleSubmit} className="space-y-3.5">
            <div className="relative">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
              <input
                type="text"
                name="query"
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="Contoh: FS-ABCD-2110"
                required
                className="w-full pl-11 pr-4 py-3.5 border border-black/10 dark:border-white/10 rounded-xl text-sm bg-white/70 dark:bg-black/30 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition placeholder:text-slate-400"
              />
            </div>

            <StarBorder
              type="submit"
              as="button"
              className={`w-full backdrop-blur-3xl font-medium !py-3.5 ${loading ? "opacity-50 pointer-events-none" : ""}`}
              backgroundColor="rgba(10, 9, 8, 0.9)"
              color="#34d399"
              trailColor="#ffffff"
              radius={9999}
              theme="dark"
              stars={2}
              sparkle
            >
              {loading ? (
                <>
                  <Loader2 className="w-4 h-4 animate-spin inline-block mr-2" /> Mencari...
                </>
              ) : (
                <>
                  <Search className="w-4 h-4 inline-block mr-2" /> Cari Pesanan
                </>
              )}
            </StarBorder>
          </form>

          <div className="mt-6 pt-5 border-t border-black/5 dark:border-white/10 text-center">
            <Link
              href="/"
              className="inline-flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-400 hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors"
            >
              <ArrowLeft className="w-4 h-4" /> Kembali ke Beranda
            </Link>
          </div>
        </motion.div>

        {/* Daftar pesanan */}
        {searched && loading && <div className="mt-6"><SkeletonList count={2} /></div>}

        {searched && !loading && orders.length > 0 && (
          <motion.div
            initial={reduceMotion ? false : { opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.1 }}
            className="mt-6 bg-[#0b1220] rounded-3xl border border-white/10 p-6 sm:p-7 shadow-soft"
          >
            <div className="flex items-center justify-between mb-5">
              <h2 className="text-sm font-bold text-slate-200 uppercase tracking-wider">
                {orders.length === 1
                  ? "Detail Pesanan"
                  : `${orders.length} Pesanan Ditemukan`}
              </h2>
              <StarBorder
                as="button"
                className={`backdrop-blur-3xl font-medium text-xs !py-1.5 px-3 ${refreshing ? "opacity-50 pointer-events-none" : ""}`}
                backgroundColor="rgba(10, 9, 8, 0.9)"
                color="#34d399"
                trailColor="#34d399"
                radius={99}
                theme="dark"
                stars={1}
                onClick={refreshing ? undefined : handleRefresh}
              >
                <RefreshCw
                  className={`w-3.5 h-3.5 inline-block mr-1.5 ${refreshing ? "animate-spin" : ""}`}
                />
                Refresh
              </StarBorder>
            </div>

            <div className="space-y-3.5">
              <AnimatePresence mode="popLayout">
                {orders.map((order) => (
                  <OrderRow key={order.booking_code} order={order} />
                ))}
              </AnimatePresence>
            </div>

            {orders.length >= 10 && (
              <p className="mt-5 text-center text-xs text-slate-400">
                Menampilkan 10 pesanan terbaru. Hubungi admin studio untuk
                riwayat yang lebih lama.
              </p>
            )}
          </motion.div>
        )}

        {searched && !loading && orders.length === 0 && !error && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.4 }}
            className="mt-8 text-center"
          >
            <div className="bg-[#151a24] rounded-xl p-6 border border-white/10">
              <Search className="w-10 h-10 text-slate-500 mx-auto mb-3" />
              <h2 className="text-lg font-bold text-slate-200 mb-2">
                Pesanan Tidak Ditemukan
              </h2>
              <p className="text-sm text-slate-400">
                Kode booking, email, atau nomor telepon tidak cocok dengan
                data manapun.
              </p>
            </div>
          </motion.div>
        )}
      </div>
    </div>
  </main>
);
}

/* ==========================================================
   Baris pesanan
   ========================================================== */

function OrderRow({ order }: { order: any }) {
  const reduceMotion = useReducedMotion();
  const router = useRouter();

  const isCanceled = order.booking_status === "canceled";
  const isCompleted = order.booking_status === "completed" || (order.is_fully_paid && Boolean(order.result_link));

  const badge =
    isCompleted || (order.is_fully_paid && Boolean(order.result_link))
      ? { tone: "emerald" as const, text: "SELESAI" }
      : order.is_fully_paid
        ? { tone: "emerald" as const, text: "LUNAS" }
        : isCanceled
          ? { tone: "slate" as const, text: "DIBATALKAN" }
          : order.payment_status === "partially_paid"
            ? { tone: "blue" as const, text: "DP TERBAYAR" }
            : { tone: "amber" as const, text: "MENUNGGU BAYAR" };

  return (
    <motion.div
      layout
      initial={reduceMotion ? false : { opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      exit={reduceMotion ? { opacity: 0 } : { opacity: 0, scale: 0.97 }}
      transition={{ duration: 0.35, ease: [0.22, 1, 0.36, 1] }}
      className="p-4 sm:p-5 bg-[#0b1220] rounded-2xl border border-white/10 hover:border-white/20 hover:bg-[#0b1220]/60 transition-colors"
    >
      <div className="flex items-start justify-between gap-3 mb-3">
        <div className="min-w-0">
          <p className="font-bold text-white truncate">
            #{order.booking_code}
          </p>
          {order.tenant_name && (
            <p className="text-xs font-semibold text-emerald-300 truncate mt-0.5">
              {order.tenant_name}
            </p>
          )}
          <p className="text-sm text-slate-200 truncate">{order.package_name}</p>
          <p className="text-xs text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
            <span className="inline-flex items-center gap-1">
              <CalendarDays className="w-3.5 h-3.5" />
              {order.booking_date_label}
            </span>
            <span className="inline-flex items-center gap-1">
              <Clock className="w-3.5 h-3.5" />
              {order.start_time} WIB
            </span>
          </p>
        </div>
        <Badge tone={badge.tone} dot>
          {badge.text}
        </Badge>
      </div>

      <div className="grid grid-cols-2 gap-x-4 gap-y-1 text-xs mb-4">
        <div className="flex justify-between">
          <span className="text-slate-400">Total</span>
          <span className="font-semibold text-white">
            Rp {Number(order.total_amount).toLocaleString("id-ID")}
          </span>
        </div>
        <div className="flex justify-between">
          <span className="text-slate-400">Terbayar</span>
          <span className="font-semibold text-emerald-400">
            Rp {Number(order.total_paid).toLocaleString("id-ID")}
          </span>
        </div>
        {!order.is_fully_paid && (
          <div className="flex justify-between col-span-2">
            <span className="text-slate-400">Sisa tagihan</span>
            <span className="font-semibold text-rose-400">
              Rp {Number(order.remaining).toLocaleString("id-ID")}
            </span>
          </div>
        )}
      </div>

      <div className="flex gap-2">
        {order.can_pay_initial ? (
          <>
            <StarBorder
              as="button"
              className="flex-1 backdrop-blur-3xl font-medium text-xs !py-2.5 inline-flex items-center justify-center gap-1.5"
              backgroundColor="rgba(10, 9, 8, 0.9)"
              color="#34d399"
              trailColor="#ffffff"
              radius={99}
              theme="dark"
              stars={2}
              sparkle
              onClick={() => router.push(`/pesanan/${order.booking_code}`)}
            >
              <Wallet className="w-3.5 h-3.5 inline-block" />
              Bayar {order.pending_type === "full" ? "Lunas" : "DP (30%)"}
            </StarBorder>
            <StarBorder
              as="button"
              className="backdrop-blur-3xl font-medium text-xs !py-2.5 px-4"
              backgroundColor="rgba(10, 9, 8, 0.9)"
              color="#ffffff"
              trailColor="#34d399"
              radius={99}
              theme="dark"
              stars={2}
              onClick={() => router.push(`/pesanan/${order.booking_code}`)}
            >
              Detail
            </StarBorder>
          </>
        ) : order.can_pay_balance ? (
          <>
            <StarBorder
              as="button"
              className="flex-1 backdrop-blur-3xl font-medium text-xs !py-2.5 inline-flex items-center justify-center gap-1.5"
              backgroundColor="rgba(10, 9, 8, 0.9)"
              color="#34d399"
              trailColor="#ffffff"
              radius={99}
              theme="dark"
              stars={2}
              sparkle
              onClick={() => router.push(`/pesanan/${order.booking_code}`)}
            >
              <Wallet className="w-3.5 h-3.5 inline-block" /> Bayar Sisa via Midtrans
            </StarBorder>
            <StarBorder
              as="button"
              className="backdrop-blur-3xl font-medium text-xs !py-2.5 px-4"
              backgroundColor="rgba(10, 9, 8, 0.9)"
              color="#ffffff"
              trailColor="#34d399"
              radius={99}
              theme="dark"
              stars={2}
              onClick={() => router.push(`/pesanan/${order.booking_code}`)}
            >
              Detail
            </StarBorder>
          </>
        ) : (
          <StarBorder
            as="button"
            className="w-full backdrop-blur-3xl font-medium text-xs !py-2.5 inline-flex items-center justify-center gap-1.5"
            backgroundColor="rgba(10, 9, 8, 0.9)"
            color="#ffffff"
            trailColor="#34d399"
            radius={99}
            theme="dark"
            stars={2}
            onClick={() => router.push(`/pesanan/${order.booking_code}`)}
          >
            Lihat Detail <ArrowRight className="w-3.5 h-3.5 inline-block" />
          </StarBorder>
        )}
      </div>

      {isCompleted && (
        <div className="mt-4 p-3.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-start gap-3">
          <div className="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center">
            <MessageCircle className="w-4 h-4 text-emerald-400" aria-hidden="true" />
          </div>
          <div className="flex-1 text-left">
            {(order.has_reviewed || (order.review && order.review.id)) ? (
              <p className="text-sm leading-relaxed text-emerald-100">
                Terima kasih telah memberikan ulasan! Ulasan Anda sangat berarti bagi kami.
              </p>
            ) : (
              <>
                <p className="text-sm leading-relaxed text-emerald-100 mb-2">
                  Hasil sesi foto telah dikirim. Jangan lupa berikan ulasan untuk studio ini!
                </p>
                <StarBorder
                  as="button"
                  className="text-xs font-medium !py-2 px-3"
                  backgroundColor="rgba(10, 9, 8, 0.9)"
                  color="#34d399"
                  trailColor="#ffffff"
                  radius={99}
                  theme="dark"
                  stars={1}
                  sparkle
                  onClick={() => router.push(`/pesanan/${order.booking_code}`)}
                >
                  Berikan Ulasan <ArrowRight className="w-3.5 h-3.5 inline-block" />
                </StarBorder>
              </>
            )}
          </div>
        </div>
      )}
    </motion.div>
  );
}
