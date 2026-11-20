"use client";

import { motion, useReducedMotion } from "motion/react";
import Link from "next/link";
import { ArrowLeft, ArrowRight, Camera, Clock, Star, Users, Sparkles } from "lucide-react";
import { use, useEffect, useState } from "react";
import { api, resolveMediaUrl, instagramHandle } from "@/lib/api";
import { useParams, useRouter } from "next/navigation";
import Reveal, { RevealGroup, RevealItem } from "@/components/Reveal";
import Badge from "@/components/Badge";
import { Skeleton } from "@/components/Skeleton";
import { Bokeh, SparkleDeco, StudioBeam } from "@/components/StudioDecor";

export default function StudioView({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = use(params);
  const router = useRouter();
  const reduceMotion = useReducedMotion();
  const [data, setData] = useState<any>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [activeCategory, setActiveCategory] = useState<string>("all");

  useEffect(() => {
    setIsLoading(true);
    api
      .getStudio(slug)
      .then((res) => {
        setData(res.data);
        setIsLoading(false);
      })
      .catch(() => {
        setError("Studio tidak ditemukan");
        setIsLoading(false);
      });
  }, [slug]);

  if (isLoading) {
    return (
      <main className="relative min-h-[100dvh] bg-transparent text-white transition-all duration-300">
        {/* Background Cahaya Biru */}
        <div className="fixed inset-0 z-[-1] pointer-events-none transform-gpu will-change-transform">
          <Bokeh count={5} />
          <StudioBeam />
          <SparkleDeco count={4} />
        </div>

        <div className="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
          <Skeleton className="h-40 w-full rounded-3xl mb-10 bg-white/5 border border-white/10" />
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {[0, 1, 2].map((i) => (
              <Skeleton key={i} className="h-72 rounded-2xl bg-white/5 border border-white/10" />
            ))}
          </div>
        </div>
      </main>
    );
  }

  const igHandle = instagramHandle(data?.tenant?.instagram_url ?? "");

  const categories: string[] = data?.packages
    ? Array.from(
        new Set<string>(
          data.packages
            .map((p: any) => p.category)
            .filter((c: string | null): c is string => Boolean(c))
        )
      )
    : [];

  const categoryLabel: Record<string, string> = {
    wisuda: "Wisuda",
    "self-photo": "Self Photo",
    portrait: "Potret",
    keluarga: "Keluarga",
    produk: "Produk",
    akad: "Akad",
    prewedding: "Pre-wedding",
    engagement: "Engagement",
    maternity: "Maternity",
    newborn: "Newborn",
    birthday: "Ulang Tahun",
    corporate: "Korporat",
    event: "Event",
    lain: "Lainnya",
  };

  const filtered = data
    ? data.packages.filter((p: any) =>
        activeCategory === "all" ? true : p.category === activeCategory
      )
    : [];

  if (error) {
    return (
      <main className="relative min-h-[100dvh] bg-transparent text-white transition-all duration-300">
        {/* Background Cahaya Biru */}
        <div className="fixed inset-0 z-[-1] pointer-events-none transform-gpu will-change-transform">
          <Bokeh count={5} />
          <StudioBeam />
          <SparkleDeco count={4} />
        </div>

        <div className="relative z-10 max-w-md mx-auto px-4 py-12 flex items-center justify-center min-h-[60vh]">
          <div className="backdrop-blur-md bg-[#0a0908]/80 border border-white/10 p-8 rounded-3xl shadow-xl text-center">
            <Camera className="w-12 h-12 text-emerald-400 mx-auto mb-4" />
            <h2 className="text-xl font-bold text-white mb-2">
              Studio tidak ditemukan
            </h2>
            <p className="text-slate-400 mb-6">{error}</p>
            <button
              className="relative inline-flex h-11 overflow-hidden rounded-xl p-[1px] focus:outline-none group w-full sm:w-auto"
              onClick={() => router.push("/")}
            >
              <span className="absolute inset-[-1000%] animate-[spin_3s_linear_infinite] bg-[conic-gradient(from_90deg_at_50%_50%,#0a0908_0%,#ffffff_50%,#0a0908_100%)]" />
              <span className="inline-flex h-full w-full cursor-pointer items-center justify-center rounded-xl bg-[#0a0908]/90 px-6 py-2 text-sm font-medium text-white backdrop-blur-3xl transition-all hover:bg-[#0a0908]/70">
                <ArrowLeft className="w-4 h-4 mr-2" /> Kembali ke Beranda
              </span>
            </button>
          </div>
        </div>
      </main>
    );
  }

  if (!data) {
    return (
      <main className="relative min-h-[100dvh] bg-transparent text-white transition-all duration-300">
        {/* Background Cahaya Biru */}
        <div className="fixed inset-0 z-[-1] pointer-events-none transform-gpu will-change-transform">
          <Bokeh count={5} />
          <StudioBeam />
          <SparkleDeco count={4} />
        </div>

        <div className="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
          <Skeleton className="h-40 w-full rounded-3xl mb-10 bg-white/5 border border-white/10" />
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {[0, 1, 2].map((i) => (
              <Skeleton key={i} className="h-72 rounded-2xl bg-white/5 border border-white/10" />
            ))}
          </div>
        </div>
      </main>
    );
  }

  return (
    <main className="relative min-h-[100dvh] bg-transparent text-white transition-all duration-300">
      {/* LAYER 1: Background Gelombang Cahaya Biru */}
      <div className="fixed inset-0 z-[-1] pointer-events-none transform-gpu will-change-transform">
        <Bokeh count={5} />
        <StudioBeam />
        <SparkleDeco count={4} />
      </div>

      {/* LAYER 2: Konten Studio */}
      <div className="relative z-10 bg-transparent">
        {/* Header studio */}
        <section className="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-8">
          <motion.div
            initial={reduceMotion ? false : { opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.55, ease: [0.22, 1, 0.36, 1] }}
          >
            <Link
              href="/"
              className="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-emerald-400 transition-colors mb-5"
            >
              <ArrowLeft className="w-4 h-4" /> Semua Studio
            </Link>

            <div className="backdrop-blur-md bg-[#0a0908]/80 border border-white/10 rounded-3xl p-8 sm:p-10 text-white shadow-xl relative overflow-hidden">
              <div className="absolute -right-10 -top-10 w-64 h-64 aurora-tr rounded-full pointer-events-none" aria-hidden />
              <div className="absolute -bottom-16 -right-12 w-64 h-64 rounded-full aurora-br pointer-events-none animate-float-slow" aria-hidden />
              <div className="relative flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                <div>
                  <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-400/10 border border-emerald-400/20 rounded-full text-xs font-semibold mb-3 text-emerald-400">
                    <Sparkles className="w-3.5 h-3.5" /> Studio Partner ANDstudio
                  </span>
                  <h1 className="text-3xl sm:text-4xl font-bold tracking-tight mb-2">
                    <Link
                      href={`/studio/${slug}/profil`}
                      className="inline-flex items-center gap-2.5 hover:text-emerald-300 transition-colors"
                      title="Lihat profil studio"
                    >
                      {data.tenant.name}
                      <span className="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/10 ring-1 ring-inset ring-white/25 text-[11px] font-bold text-white/85">
                        i
                      </span>
                    </Link>
                  </h1>

                  {/* Rating + link Instagram */}
                  <div className="flex flex-wrap items-center gap-3 mb-4">
                    {data.tenant.reviews_count > 0 && data.tenant.rating !== null ? (
                      <Link
                        href={`/studio/${slug}/profil`}
                        className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-400/10 text-emerald-400 text-xs font-semibold ring-1 ring-inset ring-emerald-400/20 hover:bg-emerald-400/20 transition-colors"
                      >
                        <Star className="w-3.5 h-3.5 fill-emerald-400 text-emerald-400" />
                        {Number(data.tenant.rating).toFixed(1)}
                        <span className="text-emerald-400/70 font-normal">
                          ({data.tenant.reviews_count} ulasan)
                        </span>
                      </Link>
                    ) : (
                      <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium text-slate-300 bg-white/[0.05] ring-1 ring-inset ring-white/10">
                        Belum ada rating
                      </span>
                    )}

                    {data.tenant.instagram_url && (
                      <a
                        href={data.tenant.instagram_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-1.5 text-sm font-semibold text-pink-300 hover:text-pink-200 underline decoration-pink-400/40 underline-offset-4 transition-colors"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="2"
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          className="w-4 h-4 text-pink-400"
                          aria-hidden="true"
                        >
                          <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                          <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                          <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                        </svg>
                        {igHandle}
                      </a>
                    )}

                    {(data.tenant.operational_start ||
                      data.tenant.operational_end) && (
                      <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium text-slate-200 bg-white/[0.05] ring-1 ring-inset ring-white/10">
                        <Clock className="w-3.5 h-3.5 text-emerald-400" />
                        Buka{" "}
                        {String(data.tenant.operational_start ?? "").substring(0, 5)}–
                        {String(data.tenant.operational_end ?? "").substring(0, 5)} WIB
                      </span>
                    )}
                  </div>

                  <p className="text-slate-300 text-sm sm:text-base max-w-lg">
                    Pilih paket yang sesuai, pesan jadwal sesi foto, dan bayar
                    dengan aman melalui Midtrans.
                  </p>
                </div>
                <div className="shrink-0 flex gap-3">
                  <div className="bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-center">
                    <div className="text-2xl font-bold">
                      {data.packages.length}
                    </div>
                    <div className="text-[11px] text-slate-400">Paket</div>
                  </div>
                </div>
              </div>
            </div>
          </motion.div>
        </section>

        {/* Filter kategori */}
        {categories.length > 0 && (
          <section className="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <div className="flex gap-2 overflow-x-auto no-scrollbar pb-1">
              <button
                onClick={() => setActiveCategory("all")}
                className={`relative shrink-0 px-4 py-2 rounded-xl text-sm font-semibold transition-colors ${
                  activeCategory === "all"
                    ? "text-white"
                    : "bg-[#0a0908]/80 text-slate-400 border border-white/10 hover:border-white/20 hover:text-white"
                }`}
              >
                {activeCategory === "all" && (
                  <motion.span
                    layoutId="cat-pill"
                    className="absolute inset-0 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 shadow-[0_8px_20px_-8px_rgba(16,185,129,0.7)]"
                    transition={{ type: "spring", stiffness: 380, damping: 30 }}
                  />
                )}
                <span className="relative">Semua Paket</span>
              </button>
              {categories.map((c: string) => (
                <button
                  key={c}
                  onClick={() => setActiveCategory(c)}
                  className={`relative shrink-0 px-4 py-2 rounded-xl text-sm font-semibold transition-colors ${
                    activeCategory === c
                      ? "text-white"
                      : "bg-[#0a0908]/80 text-slate-400 border border-white/10 hover:border-white/20 hover:text-white"
                  }`}
                >
                  {activeCategory === c && (
                    <motion.span
                      layoutId="cat-pill"
                      className="absolute inset-0 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 shadow-[0_8px_20px_-8px_rgba(16,185,129,0.7)]"
                      transition={{ type: "spring", stiffness: 380, damping: 30 }}
                    />
                  )}
                  <span className="relative">
                    {categoryLabel[c] ?? c}
                  </span>
                </button>
              ))}
            </div>
          </section>
        )}

        {/* Grid paket */}
        <section className="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
          {/* key={activeCategory} wajib: memaksa group di-remount saat filter
              berubah, agar animasi whileInView ter-trigger ulang untuk item
              yang baru dimount (tanpa ini item tetap opacity 0 / tidak tampil). */}
          <RevealGroup
            key={activeCategory}
            className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6"
            stagger={0.08}
          >
            {filtered.map((pkg: any) => (
              <RevealItem key={pkg.id}>
                <motion.div
                  whileHover={reduceMotion ? undefined : { y: -8 }}
                  transition={{ type: "spring", stiffness: 300, damping: 20 }}
                  className="group h-full flex flex-col backdrop-blur-md bg-[#0a0908]/80 rounded-2xl border border-white/10 shadow-lg hover:shadow-2xl hover:border-emerald-400/40 transition-all duration-300"
                >
                  {/* Cover */}
                  <div className="relative h-36 bg-slate-900/50 flex items-center justify-center overflow-hidden">
                    {pkg.photos && pkg.photos.length > 0 ? (
                      <>
                        <img
                          src={resolveMediaUrl(pkg.photos[0].photo_url)}
                          alt={pkg.name}
                          className="absolute inset-0 w-full h-full object-cover object-top"
                        />
                        <div
                          className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"
                          aria-hidden
                        />
                      </>
                    ) : (
                      <>
                        <div
                          className="absolute inset-0 bg-[radial-gradient(circle_at_50%_120%,rgba(16,185,129,0.15),transparent_65%)]"
                          aria-hidden
                        />
                        <motion.div
                          whileHover={reduceMotion ? undefined : { scale: 1.12, rotate: 6 }}
                          transition={{ type: "spring", stiffness: 250, damping: 18 }}
                          className="relative"
                        >
                          <Camera className="w-11 h-11 text-emerald-400/60 group-hover:text-emerald-400 transition-colors duration-300" />
                        </motion.div>
                      </>
                    )}
                    <span className="absolute top-3 left-3">
                      <Badge tone="emerald">
                        {categoryLabel[pkg.category] ?? pkg.category}
                      </Badge>
                    </span>
                    {pkg.photos && pkg.photos.length > 1 && (
                      <span className="absolute bottom-3 right-3 px-2 py-0.5 bg-black/60 backdrop-blur-sm rounded-md text-[11px] text-white font-semibold">
                        +{pkg.photos.length - 1}
                      </span>
                    )}
                  </div>

                  {/* Body */}
                  <div className="p-5 flex flex-col flex-1">
                    <h3 className="text-lg font-bold text-white mb-1.5">
                      {pkg.name}
                    </h3>
                    <p className="text-sm text-slate-400 leading-relaxed mb-4 flex-1">
                      {pkg.description}
                    </p>

                    <div className="flex items-center gap-3 text-xs text-slate-400 mb-4 pb-4 border-b border-white/10">
                      <span className="inline-flex items-center gap-1">
                        <Clock className="w-3.5 h-3.5 text-emerald-400" />
                        {pkg.duration_minutes} menit
                      </span>
                      <span className="inline-flex items-center gap-1">
                        <Users className="w-3.5 h-3.5 text-emerald-400" />
                        Maks {pkg.max_persons} org
                      </span>
                    </div>

                    <div className="flex items-end justify-between mb-4">
                      <div>
                        <span className="block text-[11px] text-slate-400">
                          Harga paket
                        </span>
                        <span className="text-xl font-extrabold text-white">
                          Rp {Number(pkg.price).toLocaleString("id-ID")}
                        </span>
                      </div>
                      <div className="text-right">
                        <span className="block text-[11px] text-slate-400">
                          DP minimal
                        </span>
                        <span className="text-sm font-bold text-emerald-400">
                          Rp{" "}
                          {Number(pkg.down_payment_min).toLocaleString("id-ID")}
                        </span>
                      </div>
                    </div>

                    <button
                      className="relative w-full inline-flex h-12 overflow-hidden rounded-xl p-[1px] focus:outline-none group mt-4"
                      onClick={() => router.push(`/studio/${slug}/package/${pkg.id}`)}
                    >
                      <span className="absolute inset-[-1000%] animate-[spin_3s_linear_infinite] bg-[conic-gradient(from_90deg_at_50%_50%,#0a0908_0%,#ffffff_50%,#0a0908_100%)]" />
                      <span className="inline-flex h-full w-full cursor-pointer items-center justify-center rounded-xl bg-[#0a0908]/90 px-6 py-2 text-sm font-medium text-white backdrop-blur-3xl transition-all hover:bg-[#0a0908]/70">
                        Pilih Jadwal & Reservasi
                        <svg className="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                      </span>
                    </button>
                  </div>
                </motion.div>
              </RevealItem>
            ))}
          </RevealGroup>

          <Reveal className="mt-12 text-center">
            <Link
              href="/pesanan"
              className="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-emerald-400 transition-colors"
            >
              <ArrowLeft className="w-4 h-4" /> Cek Riwayat Pemesanan
            </Link>
          </Reveal>
        </section>
      </div>
    </main>
  );
}