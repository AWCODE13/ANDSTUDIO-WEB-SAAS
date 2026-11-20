"use client";

import { use, useEffect, useState } from "react";
import { motion, useReducedMotion } from "motion/react";
import { useSearchParams, useRouter } from "next/navigation";
import Script from "next/script";
import Link from "next/link";
import { ArrowLeft, CreditCard, Loader2, ShieldCheck, Sparkles } from "lucide-react";
import SpecularButton from "@/components/SpecularButton";
import { Bokeh, SparkleDeco } from "@/components/StudioDecor";
import { getBaseUrl, api } from "@/lib/api";

const MIDTRANS_CLIENT_KEY = process.env.NEXT_PUBLIC_MIDTRANS_CLIENT_KEY;

export default function BayarView({
  params,
}: {
  params: Promise<{ code: string }>;
}) {
  const { code: pathCode } = use(params);
  const searchParams = useSearchParams();
  const code = searchParams.get("code") || pathCode;
  const amountParam = searchParams.get("amount");

  const router = useRouter();
  const reduceMotion = useReducedMotion();
  const [amount, setAmount] = useState<number | null>(
    amountParam ? Number(amountParam) : null
  );
  const [snapReady, setSnapReady] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [paying, setPaying] = useState(false);
  const [snapToken, setSnapToken] = useState<string | null>(null);

  // Temuan #6: Ambil Snap Token dari sessionStorage (bukan URL)
  useEffect(() => {
    if (typeof window !== "undefined") {
      const token = sessionStorage.getItem("midtrans_snap_token");
      if (token) {
        setSnapToken(token);
        sessionStorage.removeItem("midtrans_snap_token");
      } else {
        // Fallback ke URL jika ada (backward compatibility / refresh)
        const urlToken = searchParams.get("snapToken");
        if (urlToken) setSnapToken(urlToken);
      }
    }
  }, [searchParams]);

  useEffect(() => {
    if (!code || amountParam) return;

    const fetchOrder = async () => {
      try {
        // Temuan #7: Gunakan api wrapper agar CSRF & credentials otomatis
        const res = await api.getOrder(code);
        if (res.success) setAmount(Number(res.data.remaining));
      } catch {
        // Non-blocking, nominal bersifat opsional
      }
    };

    fetchOrder();
  }, [code, amountParam]);

  const handlePay = () => {
    if (!snapToken) {
      setError("Token pembayaran tidak ditemukan. Silakan ulangi proses pembayaran.");
      return;
    }

    // @ts-expect-error snap.js dimuat via <script> global
    if (typeof window.snap === "undefined") {
      setError("Modul Midtrans belum siap. Muat ulang halaman.");
      return;
    }

    setPaying(true);

    // @ts-expect-error snap.js dimuat via <script> global
    window.snap.pay(snapToken, {
      onSuccess: () => {
        alert("Pembayaran berhasil! Pesanan Anda sudah dikonfirmasi.");
        router.push(`/pesanan/${code}`);
      },
      onPending: () => {
        alert("Menunggu pembayaran Anda diselesaikan.");
        router.push(`/pesanan/${code}`);
      },
      onError: () => {
        setPaying(false);
        alert("Pembayaran gagal. Silakan coba lagi.");
      },
      onClose: () => {
        setPaying(false);
        console.log("Pop-up Midtrans ditutup tanpa menyelesaikan pembayaran");
      },
    });
  };

  return (
    <div className="relative min-h-screen bg-[#fdfbf7] dark:bg-[#110f0e] text-slate-900 dark:text-white transition-all duration-300 overflow-hidden flex items-center justify-center p-4">
      <div className="pointer-events-none absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full aurora-tl opacity-60 dark:opacity-30 blur-3xl" aria-hidden />
      <div className="pointer-events-none absolute top-1/2 -right-40 w-[600px] h-[600px] rounded-full aurora-tr opacity-50 dark:opacity-25 blur-3xl" aria-hidden />
      <Bokeh count={5} />
      <SparkleDeco count={4} />

      {/* Script Midtrans Snap.
          WAJIB pakai next/script: React 19 tidak mengeksekusi <script> raw
          di dalam komponen, jadi snap.js tidak akan pernah ke-load. */}
      <Script
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key={MIDTRANS_CLIENT_KEY}
        strategy="afterInteractive"
        onLoad={() => setSnapReady(true)}
        onError={() =>
          setError("Gagal memuat modul Midtrans. Cek koneksi internet Anda.")
        }
      />

      <div className="relative max-w-md w-full">
        <motion.div
          initial={reduceMotion ? false : { opacity: 0, y: 24, scale: 0.97 }}
          animate={{ opacity: 1, y: 0, scale: 1 }}
          transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
          className="backdrop-blur-xl bg-white/70 dark:bg-black/30 rounded-3xl shadow-2xl border border-white/60 dark:border-white/15 p-8 text-center"
        >
          {/* Icon */}
          <motion.div
            initial={reduceMotion ? false : { scale: 0, rotate: -20 }}
            animate={{ scale: 1, rotate: 0 }}
            transition={{ delay: 0.15, type: "spring", stiffness: 260, damping: 16 }}
            className="w-16 h-16 bg-[#d4af37]/15 border border-[#d4af37]/25 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-sm"
          >
            <CreditCard className="w-7 h-7 text-[#8c6e4a] dark:text-[#d4af37]" />
          </motion.div>

          <h1 className="text-2xl font-bold text-slate-900 dark:text-white mb-2">
            Pembayaran Midtrans
          </h1>
          <p className="text-slate-600 dark:text-slate-400 text-sm mb-7">
            Selesaikan pembayaran untuk memproses pesanan Anda.
          </p>

          {/* Nominal */}
          {amount !== null && (
            <motion.div
              initial={reduceMotion ? false : { opacity: 0, scale: 0.96 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: 0.2, duration: 0.4 }}
              className="bg-black/5 dark:bg-white/5 border border-[#d4af37]/30 rounded-2xl p-5 mb-6"
            >
              <span className="text-sm text-slate-600 dark:text-slate-400 block mb-1">
                Total Pembayaran
              </span>
              <span className="text-3xl font-extrabold text-[#8c6e4a] dark:text-[#d4af37] tabular-nums">
                Rp {amount.toLocaleString("id-ID")}
              </span>
            </motion.div>
          )}

          {error && (
            <motion.div
              initial={{ opacity: 0, y: -6 }}
              animate={{ opacity: 1, y: 0 }}
              className="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-400/20 text-rose-500 dark:text-rose-300 text-sm text-left"
            >
              {error}
            </motion.div>
          )}

          {/* Tombol bayar */}
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
            onClick={handlePay}
            disabled={!snapToken || !snapReady || paying}
            className="w-full !py-4 shadow-xl"
          >
            {paying ? (
              <span className="inline-flex items-center gap-2">
                <Loader2 className="w-4 h-4 animate-spin" /> Memproses...
              </span>
            ) : snapReady ? (
              <span className="inline-flex items-center gap-2">
                <Sparkles className="w-4 h-4" /> Bayar Sekarang via Midtrans
              </span>
            ) : (
              "Memuat Modul Pembayaran..."
            )}
          </SpecularButton>

          <div className="mt-3">
            <SpecularButton
              variant="secondary"
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
              onClick={() => router.push(`/pesanan/${code}`)}
              className="w-full"
            >
              <ArrowLeft className="w-4 h-4" /> Kembali ke Detail Pesanan
            </SpecularButton>
          </div>

          {/* Trust badge */}
          <div className="mt-6 pt-5 border-t border-black/5 dark:border-white/10 flex items-center justify-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <ShieldCheck className="w-4 h-4 text-[#d4af37]" />
            Pembayaran aman & terverifikasi otomatis oleh Midtrans
          </div>
        </motion.div>
      </div>
    </div>
  );
}
