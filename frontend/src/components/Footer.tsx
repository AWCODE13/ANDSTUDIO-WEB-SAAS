"use client";

import Link from "next/link";
import { motion, useReducedMotion } from "motion/react";
import { useSyncExternalStore } from "react";

function useCurrentYear(): number {
  return useSyncExternalStore(
    () => () => {},
    () => new Date().getFullYear(),
    () => 2026
  );
}

export default function Footer() {
  const reduceMotion = useReducedMotion();
  const year = useCurrentYear();

  // Temuan #8: Hapus hardcoded localhost:8000
  const appUrl = process.env.NEXT_PUBLIC_APP_URL || "http://localhost:8000";

  return (
    <footer className="relative overflow-hidden border-t border-white/10 backdrop-blur-xl bg-black/30 transition-all duration-300">
      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
          {/* Brand */}
          <div className="lg:col-span-2">
            <Link href="/" className="inline-flex items-center leading-none mb-4 group">
              <span className="text-2xl sm:text-3xl font-serif font-normal tracking-tight text-white lowercase">
                and studio<span className="text-[#d4af37]">.</span>
              </span>
            </Link>
            <p className="text-sm text-slate-400 leading-relaxed max-w-md">
              Platform operasional dan reservasi serba otomatis untuk pemilik studio
              foto modern. Mengintegrasikan kalender jadwal sesi, pembayaran DP
              Midtrans, katalog paket, hingga pembukuan kasir dalam satu sistem
              terpadu.
            </p>
          </div>

          {/* Navigasi Layanan */}
          <div>
            <h3 className="text-xs font-bold uppercase tracking-widest text-[#d4af37] mb-4">
              Layanan & Reservasi
            </h3>
            <ul className="space-y-3 text-sm">
              <li>
                <Link
                  href="/#studio"
                  className="text-slate-400 hover:text-white transition-colors"
                >
                  Katalog Studio Partner
                </Link>
              </li>
              <li>
                <Link
                  href="/pesanan"
                  className="text-slate-400 hover:text-white transition-colors"
                >
                  Lacak Pesanan / Resi
                </Link>
              </li>
              <li>
                <Link
                  href="/pricing"
                  className="text-slate-400 hover:text-white transition-colors"
                >
                  Registrasi Studio Baru
                </Link>
              </li>
               <li>
                 <Link
                   href="/tentang"
                   className="text-slate-400 hover:text-white transition-colors"
                 >
                   Tentang Kami
                 </Link>
               </li>
                <li>
                  <Link
                    href="/how-it-works"
                    className="text-slate-400 hover:text-white transition-colors"
                  >
                    Cara Kerja
                  </Link>
                </li>
                <li>
                  <Link
                    href="/faq"
                    className="text-slate-400 hover:text-white transition-colors"
                  >
                    FAQ
                  </Link>
                </li>
            </ul>
          </div>

          {/* Mitra Studio / Tenant */}
          <div>
            <h3 className="text-xs font-bold uppercase tracking-widest text-[#d4af37] mb-4">
              Portal Pengelola Studio
            </h3>
            <ul className="space-y-3 text-sm">
              <li>
                <motion.span
                  whileHover={reduceMotion ? undefined : { y: -1 }}
                  transition={{ type: "spring", stiffness: 400, damping: 22 }}
                >
                  <a
                    href={`${appUrl}/login`}
                    className="text-slate-400 hover:text-white transition-colors inline-block"
                  >
                    Masuk Dashboard Studio
                  </a>
                </motion.span>
              </li>
              <li>
                <motion.span
                  whileHover={reduceMotion ? undefined : { y: -1 }}
                  transition={{ type: "spring", stiffness: 400, damping: 22 }}
                >
                  <a
                    href={`${appUrl}/register`}
                    className="text-slate-400 hover:text-white transition-colors inline-block"
                  >
                    Mulai Uji Coba Gratis
                  </a>
                </motion.span>
              </li>
            </ul>
          </div>
        </div>

        <div className="pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p className="text-xs text-slate-400">
            © {year} and studio. Seluruh hak cipta dilindungi.
          </p>
          <p className="text-xs text-slate-400">
            Standar arsitektur fotografi oleh{" "}
            <span className="font-semibold text-slate-200">
              Andcode Digital
            </span>
          </p>
        </div>
      </div>
    </footer>
  );
}
