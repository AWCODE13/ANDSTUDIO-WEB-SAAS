"use client";

import { useEffect, useState } from "react";
import { motion, useReducedMotion } from "motion/react";
import Link from "next/link";
import Image from "next/image";
import {
  ArrowRight,
  CalendarCheck,
  Clock,
  CreditCard,
  MessageCircle,
  BarChart3,
  Sparkles,
  Camera,
  CheckCircle2,
  Star,
  Store,
  ShieldCheck,
  Users,
  ChevronRight,
  Check,
  Aperture,
  Smartphone,
  MapPin,
} from "lucide-react";
import { resolveMediaUrl, instagramHandle, getBaseUrl, api } from "@/lib/api";
import Reveal, { RevealGroup, RevealItem } from "@/components/Reveal";
import SpotlightCard from "@/components/SpotlightCard";
import { AnimatedBorder } from "@/components/AnimatedBorder";
import {
  Bokeh,
  SparkleDeco,
  StudioBeam,
} from "@/components/StudioDecor";
import { useRouter } from "next/navigation";
import StarBorder from "@/components/StarBorder";

const SAAS_FEATURES = [
  {
    icon: CalendarCheck,
    title: "Kalender Slot Real-Time",
    category: "Jadwal Anti-Bentrok",
    desc: "Sistem penguncian slot otomatis per menit. Klien memesan langsung dari kalender live tanpa perlu konfirmasi manual berulang lewat chat.",
    tag: "Akurasi 100%",
  },
  {
    icon: CreditCard,
    title: "Otomasi Kasir & Midtrans",
    category: "Payment Gateway",
    desc: "Mendukung opsi DP 30%, 50%, hingga pelunasan via QRIS, VA, dan e-Wallet. Status pesanan dan kwitansi diterbitkan otomatis secara real-time.",
    tag: "Terverifikasi Otomatis",
  },
  {
    icon: Store,
    title: "Katalog & Add-On Dinamis",
    category: "Katalog & Pricing",
    desc: "Tampilkan paket wisuda, self photo, portrait, hingga add-on cetak frame dan fotografer tambahan dengan kalkulasi harga transparan.",
    tag: "Konversi Tinggi",
  },
  {
    icon: MessageCircle,
    title: "Notifikasi & File Delivery",
    category: "Integrasi WhatsApp",
    desc: "Pengingat sesi H-1, instruksi kedatangan, serta pengiriman tautan Google Drive hasil foto otomatis langsung ke nomor WhatsApp klien.",
    tag: "Instan & Personal",
  },
  {
    icon: BarChart3,
    title: "Laporan Finansial & Kasir",
    category: "Pembukuan Studio",
    desc: "Pantau omset harian, piutang pelunasan, performa fotografer, dan rekap mutasi kasir studio dalam satu dashboard eksekutif.",
    tag: "Laporan Bersih",
  },
  {
    icon: Users,
    title: "Multi-Staff & Hak Akses",
    category: "Operasional Tim",
    desc: "Pisahkan hak akses kasir resepsionis, fotografer lapangan, dan pemilik studio untuk menjaga keamanan data finansial bisnis Anda.",
    tag: "Aman & Terkontrol",
  },
];

const WORKFLOW_STEPS = [
  {
    step: "01",
    title: "Klien Pilih Jadwal Mandiri",
    desc: "Calon klien membuka link studio Anda, memilih paket, waktu sesi, dan add-on tanpa antre chat.",
  },
  {
    step: "02",
    title: "Pembayaran DP / Lunas Terkunci",
    desc: "Invoice Midtrans terbit instan. Slot jadwal diamankan otomatis begitu pembayaran berhasil diverifikasi.",
  },
  {
    step: "03",
    title: "Sesi Foto Berjalan Tertib",
    desc: "Tim studio menerima notifikasi jadwal rapi. Waktu mulai dan selesai terkontrol bebas bentrok jadwal.",
  },
  {
    step: "04",
    title: "Distribusi Foto Sekali Klik",
    desc: "Unggah file resolusi penuh ke cloud, sistem mengirim tautan unduhan via WhatsApp ke pelanggan.",
  },
];

export default function Home() {
  const [studios, setStudios] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState<{
    studio_partners: number;
    total_bookings: number;
    client_satisfaction: number;
    uptime_months: number;
  } | null>(null);
  const [statsLoading, setStatsLoading] = useState(true);
  const reduceMotion = useReducedMotion();

  useEffect(() => {
    setLoading(true);
    api.getStudios()
      .then((res) => {
        setStudios(res.data?.studios || []);
        setLoading(false);
      })
      .catch(() => {
        // Fallback langsung ke fetch jika fetchAPI gagal
        const url = `${getBaseUrl()}/studios`;
        fetch(url)
          .then((r) => r.json())
          .then((res) => {
            setStudios(res.data?.studios || []);
            setLoading(false);
          })
          .catch(() => {
            setStudios([]);
            setLoading(false);
          });
      });

    // Muat statistik platform dari API
    setStatsLoading(true);
    api.getStats()
      .then((res) => {
        setStats(res.data);
        setStatsLoading(false);
      })
      .catch(() => {
        // Fallback ke fetch langsung
        const url = `${getBaseUrl()}/stats`;
        fetch(url)
          .then((r) => r.json())
          .then((res) => {
            setStats(res.data);
            setStatsLoading(false);
          })
          .catch(() => {
            setStats(null);
            setStatsLoading(false);
          });
      });
  }, []);

  const router = useRouter();

  return (
    <main className="relative min-h-[100dvh] bg-transparent text-white transition-all duration-300">

      {/* LAYER 1: Background Cahaya Biru (z-0) */}
      <div className="absolute inset-0 z-0 pointer-events-none transform-gpu will-change-transform">
        <Bokeh count={5} />
        <StudioBeam />
        <SparkleDeco count={4} />
      </div>

      {/* LAYER 2: Konten Landing Page (z-10, bg-transparent) */}
      <div className="relative z-10 bg-transparent">

        {/* ===================== HERO SECTION ===================== */}
        <section className="relative isolate pt-12 pb-20 sm:pt-20 sm:pb-28 overflow-hidden bg-transparent">
          <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-4xl mx-auto">
            {/* Tagline Badge */}
            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5 }}
              className="inline-flex items-center gap-2 px-5 py-2 rounded-full backdrop-blur-xl bg-white/10 border border-white/20 shadow-sm mb-8"
            >
              <Sparkles className="w-4 h-4 text-white" />
              <span className="text-xs sm:text-sm font-semibold tracking-wide text-slate-100">
                Solusi Manajemen Terpadu untuk Studio Foto Modern
              </span>
            </motion.div>

{/* Headline Bergaya Elegan - font-sans bold + font-serif italic */}
            <motion.h1
              initial={reduceMotion ? false : { opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.1 }}
              className="max-w-5xl mx-auto text-4xl md:text-5xl lg:text-6xl text-center text-white mb-6 leading-[1.15]"
            >
              <span className="block font-sans font-bold tracking-tight mb-1 md:mb-2">
                Satu Sistem Untuk Mengelola
              </span>
              <span className="block font-serif italic font-medium tracking-wide text-slate-100">
                Reservasi, Jadwal, & Finansial
              </span>
              <span className="block font-serif italic font-medium tracking-wide text-slate-100">
                Studio.
              </span>
            </motion.h1>

            {/* Sub-headline Copywriting B2B */}
            <motion.p
              initial={reduceMotion ? false : { opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.2 }}
              className="text-base sm:text-lg lg:text-xl text-slate-300 mb-10 max-w-3xl mx-auto leading-relaxed"
            >
              Tinggalkan pencatatan manual yang rawan bentrok jadwal. Berikan
              pengalaman reservasi sekelas studio profesional kelas atas dengan
              sistem otomatisasi kalender, kasir terintegrasi Midtrans, dan
              pengiriman hasil foto ke WhatsApp klien.
            </motion.p>

            {/* Tombol Aksi Pill */}
            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.3 }}
              className="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8"
            >
              <StarBorder
                as="button"
                className="backdrop-blur-3xl w-full sm:w-auto font-medium"
                backgroundColor="rgba(10, 9, 8, 0.9)"
                color="#34d399"
                trailColor="#ffffff"
                radius={99}
                theme="dark"
                stars={2}
                sparkle
                onClick={() => router.push("/pricing")}
              >
                Mulai Uji Coba Gratis
                <svg className="ml-2 w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path></svg>
              </StarBorder>

              <StarBorder
                as="button"
                className="backdrop-blur-3xl w-full sm:w-auto font-medium"
                backgroundColor="rgba(10, 9, 8, 0.9)"
                color="#34d399"
                trailColor="#34d399"
                radius={99}
                theme="dark"
                stars={2}
                onClick={() => {
                  const el = document.getElementById("partner") || document.getElementById("studio");
                  if (el) el.scrollIntoView({ behavior: "smooth" });
                }}
              >
                Lihat Contoh Studio
              </StarBorder>
            </motion.div>

            {/* Trust Badges */}
            <motion.div
              initial={reduceMotion ? false : { opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ duration: 0.6, delay: 0.45 }}
              className="mt-12 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-xs sm:text-sm font-medium text-slate-300"
            >
              {[
                "Pembayaran Resmi Midtrans",
                "Kalender Slot Anti-Bentrok",
                "Tanpa Biaya Instalasi Awal",
                "Integrasi WhatsApp Otomatis",
              ].map((item) => (
                <span key={item} className="inline-flex items-center gap-2">
                  <Check className="w-4 h-4 text-white" />
                  {item}
                </span>
              ))}
            </motion.div>
          </div>
        </div>
      </section>

      {/* ===================== FITUR UNGGULAN (Glassmorphism Cards) ===================== */}
      <section id="fitur" className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 scroll-mt-20 bg-transparent">
        <Reveal className="text-center mb-16">
          <span className="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">
            Standar Arsitektur Studio
          </span>
          <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-3 mb-4">
            Fitur Operasional Siap Pakai
          </h2>
          <p className="text-slate-400 max-w-2xl mx-auto text-base">
            Seluruh kebutuhan manajemen studio fotografi dirancang khusus untuk
            menghemat waktu dan meningkatkan kepuasan pelanggan.
          </p>
        </Reveal>

        <RevealGroup className="grid md:grid-cols-2 lg:grid-cols-3 gap-6" stagger={0.08}>
          {SAAS_FEATURES.map((item) => (
            <RevealItem key={item.title}>
              <AnimatedBorder className="h-full flex flex-col justify-between">
                <div className="p-8">
                  <div className="flex items-center justify-between mb-6">
                    <div className="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300">
                      <item.icon className="w-5 h-5 text-slate-400" />
                    </div>
                    <span className="text-[11px] font-semibold tracking-wider uppercase px-3 py-1 rounded-full bg-white/10 text-white border border-white/20">
                      {item.tag}
                    </span>
                  </div>
                  <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                    {item.category}
                  </span>
                  <h3 className="text-xl font-bold text-white mt-1 mb-3">
                    {item.title}
                  </h3>
                  <p className="text-sm text-slate-400 leading-relaxed">
                    {item.desc}
                  </p>
                </div>
              </AnimatedBorder>
            </RevealItem>
          ))}
        </RevealGroup>
      </section>

      {/* ===================== WORKFLOW (Cara Kerja) ===================== */}
      <section id="cara-kerja" className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 scroll-mt-20 bg-transparent">
        <Reveal className="text-center mb-16">
          <span className="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">
            Alur Terstruktur
          </span>
          <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-3 mb-4">
            Pengalaman Reservasi Mulus
          </h2>
          <p className="text-slate-400 max-w-2xl mx-auto text-base">
            Otomasi dari saat pelanggan memilih jadwal hingga tautan galeri foto
            diterima tanpa hambatan.
          </p>
        </Reveal>

        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {WORKFLOW_STEPS.map((wf) => (
            <AnimatedBorder
              key={wf.step}
              className="relative flex flex-col justify-between"
            >
              <div className="p-8">
                <span className="font-[family-name:var(--font-logo)] text-3xl font-semibold text-slate-400">
                  {wf.step}
                </span>
                <h3 className="text-lg font-bold text-white mt-4 mb-2">
                  {wf.title}
                </h3>
                <p className="text-xs sm:text-sm text-slate-400 leading-relaxed">
                  {wf.desc}
                </p>
              </div>
            </AnimatedBorder>
          ))}
        </div>
      </section>

      {/* ===================== KATALOG STUDIO PARTNER ===================== */}
      <section id="partner" className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 scroll-mt-20 bg-transparent">
        <Reveal className="text-center mb-16">
          <span className="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">
            Direktori Mitra
          </span>
          <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-3 mb-4">
            Studio yang Menggunakan And Studio
          </h2>
          <p className="text-slate-400 max-w-2xl mx-auto text-base">
            Jelajahi paket sesi foto nyata dan rasakan kemudahan sistem
            pemesanan yang dirasakan oleh pelanggan mereka.
          </p>
        </Reveal>

        {loading ? (
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {[0, 1, 2].map((i) => (
              <div
                key={i}
                className="h-72 rounded-3xl backdrop-blur-xl bg-white/5 border border-white/20 animate-pulse"
                aria-hidden
              />
            ))}
          </div>
        ) : studios.length === 0 ? (
          <div className="text-center py-16 rounded-3xl backdrop-blur-xl bg-white/5 border border-white/10 p-8">
            <Store className="w-10 h-10 text-slate-400 mx-auto mb-3" />
            <p className="text-slate-300">
              Belum ada studio mitra yang terdaftar saat ini.
            </p>
          </div>
        ) : (
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {studios.map((studio) => (
              <motion.div
                key={studio.id}
                whileHover={reduceMotion ? undefined : { y: -6 }}
                transition={{ type: "spring", stiffness: 300, damping: 22 }}
                className="flex flex-col h-full"
              >
                <AnimatedBorder
                  className="relative flex flex-col h-full overflow-hidden"
                >
                  {/* Banner / Cover Studio - seragamkan tinggi */}
                  <div className="relative bg-[#f1ece1] overflow-hidden aspect-video w-full flex-shrink-0">
                    {studio.logo_url ? (
                      <Image
                        src={resolveMediaUrl(studio.logo_url)!}
                        alt={studio.name}
                        fill
                        sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                        className="object-cover group-hover:scale-105 transition-transform duration-500"
                      />
                    ) : (
                      <div className="absolute inset-0 flex items-center justify-center">
                        <Camera className="w-12 h-12 text-slate-400/30" />
                      </div>
                    )}

                    {/* Rating Float */}
                    <div className="absolute top-3 right-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-white">
                      <Star className="w-3.5 h-3.5 text-white fill-white" />
                      {studio.reviews_count > 0 && studio.rating !== null
                        ? Number(studio.rating).toFixed(1)
                        : "Baru"}
                    </div>
                  </div>

                  {/* Deskripsi & Aksi - flex-grow untuk merenggang, mt-auto untuk tombol di bawah */}
                  <div className="p-6 sm:p-7 flex flex-col flex-grow">
                    <div className="flex flex-col flex-grow">
                      <h3 className="text-xl font-bold text-white mb-2">
                        {studio.name}
                      </h3>

                      {(studio.operational_start || studio.operational_end) && (
                        <p className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 px-2.5 py-1 rounded-lg bg-white/5 border border-white/10 mb-3">
                          <Clock className="w-3.5 h-3.5 text-slate-400" />
                          Buka {String(studio.operational_start ?? "").substring(0, 5)}–
                          {String(studio.operational_end ?? "").substring(0, 5)} WIB
                        </p>
                      )}

                      {studio.address && (
                        <p className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-300 px-1 mb-3">
                          <MapPin className="w-3.5 h-3.5 text-emerald-400" />
                          {studio.address}
                        </p>
                      )}

                      {studio.instagram_url && (
                        <a
                          href={studio.instagram_url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-pink-400 transition-colors mb-4"
                        >
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            className="w-3.5 h-3.5 text-pink-400"
                            aria-hidden="true"
                          >
                            <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                          </svg>
                          {instagramHandle(studio.instagram_url)}
                        </a>
                      )}

                      <p className="text-sm text-slate-400 leading-relaxed mb-6">
                        Katalog sesi foto profesional, reservasi instan, dan
                        pembayaran otomatis.
                      </p>
                    </div>

                    <StarBorder
                      as="button"
                      className="backdrop-blur-3xl w-full sm:w-auto font-medium"
                      backgroundColor="rgba(10, 9, 8, 0.9)"
                      color="#34d399"
                      trailColor="#ffffff"
                      radius={99}
                      theme="dark"
                      stars={2}
                      sparkle
                      onClick={() => router.push(`/studio/${studio.slug}`)}
                    >
                      Buka Kalender & Reservasi
                      <svg className="ml-2 w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </StarBorder>
                  </div>
                </AnimatedBorder>
              </motion.div>
            ))}
          </div>
        )}
      </section>

      {/* ===================== STATISTIK ===================== */}
      <section id="statistik" className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 scroll-mt-20 bg-transparent">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 text-center">
          <div className="p-6 backdrop-blur-xl bg-white/5 border border-white/10 rounded-2xl">
            <div className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-2">
              {statsLoading ? "–" : (stats?.studio_partners ?? studios.length) + "+"}
            </div>
            <div className="text-sm text-slate-400">Studio Partner</div>
          </div>
          <div className="p-6 backdrop-blur-xl bg-white/5 border border-white/10 rounded-2xl">
            <div className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-2">
              {statsLoading ? "–" : (stats?.total_bookings ?? 0).toLocaleString("id-ID") + "+"}
            </div>
            <div className="text-sm text-slate-400">Reservasi Bulanan</div>
          </div>
          <div className="p-6 backdrop-blur-xl bg-white/5 border border-white/10 rounded-2xl">
            <div className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-2">
              {statsLoading ? "–" : (stats?.client_satisfaction ?? 0) + "%"}
            </div>
            <div className="text-sm text-slate-400">Kepuasan Klien</div>
          </div>
          <div className="p-6 backdrop-blur-xl bg-white/5 border border-white/10 rounded-2xl">
            <div className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-2">
              {statsLoading ? "–" : (stats?.uptime_months ?? 0) + "+"}
            </div>
            <div className="text-sm text-slate-400">Bulan Uptime</div>
          </div>
        </div>
      </section>

      {/* ===================== CALL TO ACTION (Tenant B2B) ===================== */}
      <section className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 bg-transparent">
        <div className="relative overflow-hidden rounded-3xl p-8 sm:p-14 lg:p-16 backdrop-blur-md bg-[#0a0908]/95 border border-white/10 shadow-2xl text-center">
          {/* Animasi Aperture Studio - Berputar lambat sebagai watermark */}
          <div className="absolute -right-24 -top-24 z-0 opacity-[0.03] dark:opacity-[0.05] pointer-events-none">
            <Aperture className="w-96 h-96 text-white animate-[spin_30s_linear_infinite]" strokeWidth={1} />
          </div>
          <div className="absolute -left-20 -bottom-20 z-0 opacity-[0.03] dark:opacity-[0.05] pointer-events-none">
            <Aperture className="w-72 h-72 text-white animate-[spin_40s_linear_infinite_reverse]" strokeWidth={1} />
          </div>

          {/* Ikon Kamera Melayang (Kiri Atas) */}
          <div 
            className="absolute left-10 md:left-24 top-10 md:top-16 z-0 opacity-30 pointer-events-none" 
            style={{ animation: 'float 6s ease-in-out infinite' }}
          >
            <Camera className="w-16 h-16 text-white -rotate-12 blur-[1px]" strokeWidth={1} />
          </div>

          {/* Ikon Smartphone Melayang (Kanan Bawah) */}
          <div 
            className="absolute right-10 md:right-32 bottom-12 md:bottom-20 z-0 opacity-30 pointer-events-none" 
            style={{ animation: 'float 8s ease-in-out infinite reverse' }}
          >
            <Smartphone className="w-14 h-14 text-white rotate-12 blur-[1px]" strokeWidth={1} />
          </div>

          {/* Aksen Radial Glow di Dalam Banner CTA - CSS Class Responsive */}
          <div
            className="pointer-events-none absolute inset-0 cta-glow opacity-40 blur-2xl"
            aria-hidden
          />

          <div className="relative z-10 max-w-3xl mx-auto">
            <span className="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">
              Mulai Transformasi Digital
            </span>
            <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-3 mb-5">
              Siap Mengoperasikan Studio Tanpa Ribet Chat?
            </h2>
            <p className="text-base sm:text-lg text-slate-400 leading-relaxed mb-9">
              Bergabunglah dengan jaringan pemilik studio yang telah beralih ke
              sistem reservasi mandiri dan pembukuan otomatis. Uji coba gratis
              14 hari tanpa komitmen.
            </p>

            <div className="flex flex-col gap-4 justify-center items-center w-full sm:flex-row sm:w-auto">
              <StarBorder
                as="button"
                className="backdrop-blur-3xl w-full sm:w-auto font-medium"
                backgroundColor="rgba(10, 9, 8, 0.9)"
                color="#34d399"
                trailColor="#ffffff"
                radius={99}
                theme="dark"
                stars={2}
                sparkle
                onClick={() => router.push("/pricing")}
              >
                Daftarkan Studio Anda
                <svg className="w-4 h-4 ml-1 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </StarBorder>

              <StarBorder
                as="button"
                className="backdrop-blur-3xl w-full sm:w-auto font-medium"
                backgroundColor="rgba(10, 9, 8, 0.9)"
                color="#ffffff"
                trailColor="#34d399"
                radius={99}
                theme="dark"
                stars={2}
                onClick={() => {
                  window.location.href = `${
                    process.env.NEXT_PUBLIC_APP_URL || "http://localhost:8000"
                  }/login`;
                }}
              >
                Masuk ke Dashboard
                <svg className="ml-2 w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg>
              </StarBorder>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>
  );
}
