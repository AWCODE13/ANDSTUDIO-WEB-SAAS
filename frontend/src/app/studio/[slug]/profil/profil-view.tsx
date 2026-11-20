"use client";

import { use, useEffect, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import {
  ArrowLeft,
  Camera,
  Clock,
  ExternalLink,
  Mail,
  MapPin,
  Phone,
  Star,
} from "lucide-react";
import { api, resolveMediaUrl, instagramHandle, type StudioReview } from "@/lib/api";
import { Skeleton } from "@/components/Skeleton";

export default function StudioProfileView({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = use(params);
  const [data, setData] = useState<any>(null);
  const [error, setError] = useState<string | null>(null);
  const [reviews, setReviews] = useState<{
    rating: number | null;
    reviews_count: number;
    reviews: StudioReview[];
  }>({ rating: null, reviews_count: 0, reviews: [] });

  useEffect(() => {
    api
      .getStudio(slug)
      .then((res: any) => setData(res.data))
      .catch(() => setError("Profil studio tidak ditemukan"));
  }, [slug]);

  useEffect(() => {
    api
      .getStudioReviews(slug)
      .then((res: any) => setReviews(res.data))
      .catch(() => undefined);
  }, [slug]);

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center px-4">
        <div className="max-w-md w-full text-center bg-[#0b1220] p-8 rounded-2xl shadow-soft border border-white/10">
          <Camera className="w-12 h-12 text-slate-200 mx-auto mb-4" />
          <h2 className="text-xl font-bold text-white mb-2">
            Profil tidak ditemukan
          </h2>
          <p className="text-slate-400 mb-6">{error}</p>
          <Link
            href="/"
            className="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-500 hover:bg-blue-400 text-white rounded-xl text-sm font-semibold transition-colors"
          >
            <ArrowLeft className="w-4 h-4" /> Kembali ke Beranda
          </Link>
        </div>
      </div>
    );
  }

  if (!data) {
    return (
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <Skeleton className="h-56 w-full rounded-3xl mb-8" />
        <Skeleton className="h-40 w-full rounded-2xl" />
      </div>
    );
  }

  const t = data.tenant;
  const logo = resolveMediaUrl(t.logo_url);

  // Rating asli dari ulasan pelanggan (bukan angka dummy).
  const rating = reviews.rating ?? t.rating ?? null;
  const reviewsCount = reviews.reviews_count ?? t.reviews_count ?? 0;
  const hasRating = reviewsCount > 0 && rating !== null;

  // Handle Instagram untuk ditampilkan sebagai link.
  const igHandle = instagramHandle(t.instagram_url);

  return (
    <div className="relative">
      <div className="pointer-events-none absolute inset-x-0 top-0 h-96 mesh-bg" />

      <div className="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <Link
          href={`/studio/${slug}`}
          className="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-blue-400 transition-colors mb-6"
        >
          <ArrowLeft className="w-4 h-4" /> Kembali ke Katalog
        </Link>

        {/* Kartu profil utama */}
        <div className="bg-slate-900/50 backdrop-blur-sm border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
          <div className="relative h-40 sm:h-48 overflow-hidden">
            {logo ? (
              <Image
                src={logo}
                alt={t.name}
                fill
                sizes="(max-width: 768px) 100vw, 768px"
                className="object-cover"
              />
            ) : (
              <div className="absolute inset-0 bg-gradient-to-br from-blue-600 via-blue-500 to-cyan-500" />
            )}
            <div
              className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"
              aria-hidden
            />
          </div>

          <div className="p-6 sm:p-8 -mt-12 relative">
            {/* Logo utama */}
            <div className="w-28 h-28 rounded-2xl overflow-hidden border border-slate-700 bg-slate-900 shadow-xl flex items-center justify-center shrink-0">
              {logo ? (
                <Image
                  src={logo}
                  alt={`Logo ${t.name}`}
                  width={112}
                  height={112}
                  className="w-full h-full object-cover"
                />
              ) : (
                <Camera className="w-10 h-10 text-slate-400" />
              )}
            </div>

            <div className="mt-5 flex flex-wrap items-center gap-3">
              <h1 className="text-3xl sm:text-4xl font-bold text-white tracking-tight">
                {t.name}
              </h1>
              {hasRating ? (
                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-400/15 text-amber-300 text-sm font-semibold ring-1 ring-inset ring-amber-300/25">
                  <Star className="w-4 h-4 fill-amber-300" />
                  {Number(rating).toFixed(1)}
                  <span className="text-amber-300/70 font-normal">
                    ({reviewsCount} ulasan)
                  </span>
                </span>
              ) : (
                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-slate-400 text-sm font-medium bg-white/[0.04] ring-1 ring-inset ring-white/10">
                  Belum ada rating
                </span>
              )}
            </div>

            {t.booking_policy && (
              <p className="text-slate-400 text-sm mt-3 max-w-2xl leading-relaxed">
                {t.booking_policy}
              </p>
            )}

            {/* Detail info */}
            <div className="grid sm:grid-cols-2 gap-3 mt-6">
              {t.address && (
                <div className="flex items-start gap-3 bg-slate-900/60 backdrop-blur-sm border border-slate-800 p-4 rounded-xl">
                  <MapPin className="w-4 h-4 text-blue-400 mt-0.5 shrink-0" />
                  <div>
                    <span className="block text-[11px] text-slate-400">Alamat</span>
                    <span className="text-sm text-white">{t.address}</span>
                  </div>
                </div>
              )}

              {t.phone && (
                <div className="flex items-start gap-3 bg-slate-900/60 backdrop-blur-sm border border-slate-800 p-4 rounded-xl">
                  <Phone className="w-4 h-4 text-cyan-400 mt-0.5 shrink-0" />
                  <div>
                    <span className="block text-[11px] text-slate-400">
                      WhatsApp
                    </span>
                    <a
                      href={`https://wa.me/${String(t.phone).replace(/^\+/, "")}`}
                      className="text-sm text-white hover:text-cyan-300 transition-colors"
                    >
                      {t.phone}
                    </a>
                  </div>
                </div>
              )}

              {t.email && (
                <div className="flex items-start gap-3 bg-slate-900/60 backdrop-blur-sm border border-slate-800 p-4 rounded-xl">
                  <Mail className="w-4 h-4 text-violet-400 mt-0.5 shrink-0" />
                  <div>
                    <span className="block text-[11px] text-slate-400">Email</span>
                    <a
                      href={`mailto:${t.email}`}
                      className="text-sm text-white hover:text-violet-300 transition-colors break-all"
                    >
                      {t.email}
                    </a>
                  </div>
                </div>
              )}

              {t.instagram_url && (
                <div className="flex items-start gap-3 bg-slate-900/60 backdrop-blur-sm border border-slate-800 p-4 rounded-xl">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="w-4 h-4 text-pink-400 mt-0.5 shrink-0"
                    aria-hidden="true"
                  >
                    <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                    <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                  </svg>
                  <div>
                    <span className="block text-[11px] text-slate-400">
                      Instagram
                    </span>
                    <a
                      href={t.instagram_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-sm font-semibold text-pink-300 hover:text-pink-200 underline decoration-pink-400/40 underline-offset-4 transition-colors break-all"
                    >
                      @{igHandle}
                    </a>
                  </div>
                </div>
              )}

              {(t.operational_start || t.operational_end) && (
                <div className="flex items-start gap-3 bg-slate-900/60 backdrop-blur-sm border border-slate-800 p-4 rounded-xl">
                  <Clock className="w-4 h-4 text-emerald-400 mt-0.5 shrink-0" />
                  <div>
                    <span className="block text-[11px] text-slate-400">
                      Jam Operasional
                    </span>
                    <span className="text-sm text-white">
                      {String(t.operational_start ?? "").substring(0, 5)} –{" "}
                      {String(t.operational_end ?? "").substring(0, 5)} WIB
                    </span>
                  </div>
                </div>
              )}
            </div>

            {/* Aksi: Instagram + katalog */}
            <div className="flex flex-col sm:flex-row gap-3 mt-7">
              {t.instagram_url && (
                <a
                  href={t.instagram_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-fuchsia-600 to-pink-500 text-white rounded-xl text-sm font-semibold shadow-[0_12px_30px_-14px_rgba(236,72,153,0.9)] hover:shadow-[0_16px_38px_-14px_rgba(236,72,153,1)] transition-shadow"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="w-4 h-4"
                    aria-hidden="true"
                  >
                    <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                    <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                  </svg> Kunjungi Instagram
                  <ExternalLink className="w-3.5 h-3.5 opacity-80" />
                </a>
              )}
              <Link
                href={`/studio/${slug}`}
                className="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white/[0.06] text-white rounded-xl text-sm font-semibold ring-1 ring-inset ring-white/20 hover:bg-white/[0.12] hover:ring-white/30 transition-colors"
              >
                Lihat Paket
              </Link>
            </div>
          </div>
        </div>

        {/* ══ ULASAN PELANGGAN ══ */}
        <div className="mt-8 bg-slate-900/50 backdrop-blur-sm border border-slate-800 rounded-3xl p-6 sm:p-8">
          <div className="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h2 className="text-lg font-bold text-white">Ulasan Pelanggan</h2>
            {hasRating ? (
              <span className="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-300">
                <Star className="w-4 h-4 fill-amber-300" />
                {Number(rating).toFixed(1)}
                <span className="text-slate-400 font-normal">
                  · {reviewsCount} ulasan
                </span>
              </span>
            ) : (
              <span className="text-sm text-slate-400">Belum ada rating</span>
            )}
          </div>

          {reviews.reviews.length === 0 ? (
            <p className="text-sm text-slate-400 text-center py-8 px-4 bg-white/[0.03] border border-dashed border-white/10 rounded-2xl">
              Belum ada ulasan. Rating akan muncul setelah pelanggan pertama
              menyelesaikan sesi foto di studio ini.
            </p>
          ) : (
            <div className="grid sm:grid-cols-2 gap-3">
              {reviews.reviews.map((review) => (
                <div
                  key={review.id}
                  className="bg-white/[0.03] border border-white/10 rounded-2xl p-4"
                >
                  <div className="flex items-center justify-between gap-3 mb-2">
                    <span className="text-sm font-semibold text-white truncate">
                      {review.customer_name}
                    </span>
                    <div className="flex items-center gap-0.5 shrink-0">
                      {[1, 2, 3, 4, 5].map((n) => (
                        <Star
                          key={n}
                          className={`w-3.5 h-3.5 ${
                            review.rating >= n
                              ? "text-amber-300 fill-amber-300"
                              : "text-slate-600"
                          }`}
                        />
                      ))}
                    </div>
                  </div>

                  {review.comment && (
                    <p className="text-sm text-slate-300 leading-relaxed">
                      &quot;{review.comment}&quot;
                    </p>
                  )}

                  <div className="flex flex-wrap items-center gap-2 mt-2 text-[11px] text-slate-500">
                    {review.package_name && (
                      <span className="truncate max-w-[60%]">
                        {review.package_name}
                      </span>
                    )}
                    {review.created_at && (
                      <span>
                        ·{" "}
                        {new Date(review.created_at).toLocaleDateString("id-ID", {
                          day: "2-digit",
                          month: "short",
                          year: "numeric",
                        })}
                      </span>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
