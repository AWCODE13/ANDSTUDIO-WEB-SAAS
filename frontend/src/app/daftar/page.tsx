"use client";

import { useState, useEffect, Suspense } from "react";
import { motion, useReducedMotion } from "motion/react";
import { api } from "@/lib/api";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import {
  ArrowLeft,
  CalendarCheck,
  CreditCard,
  Loader2,
  MessageCircle,
  ShieldCheck,
  Sparkles,
  Store,
  User,
  Zap,
} from "lucide-react";
import { Bokeh, SparkleDeco, StudioBeam } from "@/components/StudioDecor";

const FEATURES = [
  { icon: CalendarCheck, title: "Kalender Otomatis", desc: "Slot jadwal terisi otomatis" },
  { icon: CreditCard, title: "Bayar Midtrans", desc: "DP 30% atau lunas" },
  { icon: MessageCircle, title: "Kirim Foto via WA", desc: "Link GDrive otomatis" },
  { icon: User, title: "Multi-Staff", desc: "Kelola tim studio" },
];

function DaftarContent() {
  const reduceMotion = useReducedMotion();
  const searchParams = useSearchParams();
  const plan = searchParams.get("plan") === "pro" ? "pro" : "free";

  const [formData, setFormData] = useState({
    studio_name: "",
    address: "",
    name: "",
    email: "",
    phone: "",
    password: "",
    password_confirmation: "",
    terms: false,
    plan: plan,
    instagram_url: "",
    operational_start: "09:00",
    operational_end: "21:00",
    logo_file: null, // File object untuk logo studio
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [logoPreview, setLogoPreview] = useState<string | null>(null);

  useEffect(() => {
    setFormData((prev) => ({ ...prev, plan }));
  }, [plan]);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value, type, files } = e.target as HTMLInputElement & {
      files?: FileList;
    };
    if (type === "file" && files && files[0]) {
      const file = files[0];
      setFormData((prev) => ({ ...prev, [name]: file }));
      // Preview logo
      const reader = new FileReader();
      reader.onloadend = () => setLogoPreview(reader.result as string);
      reader.readAsDataURL(file);
    } else if (type === "checkbox") {
      const checked = (e.target as HTMLInputElement).checked;
      setFormData((prev) => ({
        ...prev,
        [name]: checked,
      }));
    } else {
      setFormData((prev) => ({
        ...prev,
        [name]: value,
      }));
    }
  };

  // Temuan #12: Validasi URL Instagram
  const validateInstagramUrl = (url: string): string | null => {
    if (!url || url.trim() === "") return null; // Opsional
    const trimmed = url.trim();
    const validPrefixes = ["https://instagram.com/", "https://www.instagram.com/"];
    if (!validPrefixes.some((prefix) => trimmed.startsWith(prefix))) {
      return "URL Instagram harus diawali dengan https://instagram.com/ atau https://www.instagram.com/";
    }
    return null;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    // Validasi Instagram URL client-side
    const igError = validateInstagramUrl(formData.instagram_url);
    if (igError) {
      setError(igError);
      return;
    }

    setLoading(true);

    try {
      // Prepare FormData untuk mengirim file logo jika ada
      const formDataToSend = new FormData();
      Object.entries(formData).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
          // File check untuk TypeScript: typeof File === 'object' + name property
          if (typeof value === "object" && value !== null && "name" in value) {
            formDataToSend.append(key, value as File);
          } else if (typeof value === "boolean") {
            formDataToSend.append(key, value ? "1" : "0");
          } else {
            formDataToSend.append(key, String(value));
          }
        }
      });

      const res = await api.registerTenant(formDataToSend);
      if (res.success) {
        // Redirect ke dashboard Laravel pakai origin terpercaya + path statis.
        // Jangan pakai res.data.dashboard_url (server-controlled) — open redirect.
        const appUrl =
          process.env.NEXT_PUBLIC_APP_URL || "http://localhost:8000";
        // eslint-disable-next-line @next/next/no-location-assign-relative-destination
        window.location.href = `${appUrl}/app/dashboard`;
      }
    } catch (err: any) {
      setError(err.message || "Gagal mendaftar. Silakan coba lagi.");
      setLoading(false);
    }
  };

  const inputClass =
    "w-full px-4 py-3 border border-white/10 rounded-2xl text-sm bg-white/5 text-white focus:ring-2 focus:ring-emerald-400/50 focus:border-emerald-400/50 outline-none transition placeholder:text-slate-500";

  const appUrl = process.env.NEXT_PUBLIC_APP_URL || "http://localhost:8000";

  return (
    <main className="relative min-h-[100dvh] bg-transparent text-white transition-all duration-300 overflow-x-hidden">
      {/* Background Gelombang / Cahaya */}
      <div className="fixed inset-0 z-[-1] pointer-events-none transform-gpu will-change-transform">
        <Bokeh count={5} />
        <StudioBeam />
        <SparkleDeco count={4} />
      </div>

      <div className="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <Link
          href="/"
          className="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-emerald-400 transition-colors mb-6"
        >
          <ArrowLeft className="w-4 h-4" /> Kembali ke Beranda
        </Link>

        <div className="grid lg:grid-cols-5 gap-8 items-start">
          {/* ================= PANEL KIRI ================= */}
          <motion.div
            initial={reduceMotion ? false : { opacity: 0, x: -24 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
            className="lg:col-span-2 lg:sticky lg:top-24"
          >
            <span className="inline-flex items-center gap-2 px-3.5 py-1.5 bg-emerald-400/10 text-emerald-400 border border-emerald-400/20 rounded-full text-xs font-bold mb-5">
              {plan === 'pro' ? (
                <>
                  <Zap className="w-3.5 h-3.5 text-emerald-400" /> Plan Terpilih: PRO (Trial 14 Hari)
                </>
              ) : (
                <>
                  <Sparkles className="w-3.5 h-3.5 text-emerald-400" /> Plan Terpilih: FREE
                </>
              )}
            </span>

            <h1 className="text-3xl sm:text-4xl font-bold text-white tracking-tight leading-tight mb-4">
              Kelola Studio Foto
              <br />
              <span className="font-[family-name:var(--font-logo)] italic font-normal text-emerald-400">
                Jadi Lebih Profesional
              </span>
            </h1>

            <p className="text-slate-300 mb-7 leading-relaxed">
              Sistem reservasi lengkap: katalog paket, kalender jadwal,
              pembayaran Midtrans otomatis, dan pengiriman hasil foto via
              WhatsApp.
            </p>

            <div className="space-y-3">
              {FEATURES.map((f, i) => (
                <motion.div
                  key={f.title}
                  initial={reduceMotion ? false : { opacity: 0, x: -16 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.15 + i * 0.08, duration: 0.45 }}
                  className="flex items-center gap-3.5 backdrop-blur-md bg-[#0a0908]/80 rounded-2xl border border-white/10 p-3.5 shadow-sm"
                >
                  <div className="w-10 h-10 rounded-xl bg-emerald-400/10 border border-emerald-400/20 flex items-center justify-center shrink-0">
                    <f.icon className="w-4.5 h-4.5 text-emerald-400" />
                  </div>
                  <div>
                    <p className="text-sm font-bold text-white">{f.title}</p>
                    <p className="text-xs text-slate-400">{f.desc}</p>
                  </div>
                </motion.div>
              ))}
            </div>

            <div className="mt-6 flex items-center gap-2 text-xs text-slate-400">
              <ShieldCheck className="w-4 h-4 text-emerald-400" />
              Tanpa kartu kredit. Batalkan kapan saja.
            </div>
          </motion.div>

          {/* ================= FORM ================= */}
          <motion.div
            initial={reduceMotion ? false : { opacity: 0, y: 24 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.1, ease: [0.22, 1, 0.36, 1] }}
            className="lg:col-span-3 backdrop-blur-md bg-[#0a0908]/80 rounded-3xl border border-white/10 p-6 sm:p-8 shadow-xl"
          >
            <div className="flex items-center gap-3 mb-6">
              <div className="w-11 h-11 rounded-2xl bg-emerald-400/10 border border-emerald-400/20 flex items-center justify-center shadow-sm">
                <Store className="w-5 h-5 text-emerald-400" />
              </div>
              <div>
                <h2 className="text-xl font-bold text-white">
                  Daftar Studio Anda
                </h2>
                <p className="text-sm text-slate-400">
                  Isi data berikut untuk memulai
                </p>
              </div>
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

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-slate-200 mb-1.5">
                  Alamat Studio <span className="text-emerald-400">*</span>
                </label>
                <textarea
                  name="address"
                  value={formData.address}
                  onChange={handleChange}
                  required
                  rows={3}
                  placeholder="Jalan, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos"
                  className={inputClass}
                />
                <p className="text-[11px] text-slate-400 mt-1.5">
                  Alamat lengkap studio untuk keperluan invoice & tampilan publik
                </p>
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-200 mb-1.5">
                  Nama Studio <span className="text-emerald-400">*</span>
                </label>
                <input
                  type="text"
                  name="studio_name"
                  value={formData.studio_name}
                  onChange={handleChange}
                  required
                  placeholder="Contoh: Kaviagraduation"
                  className={inputClass}
                />
                <p className="text-[11px] text-slate-400 mt-1.5">
                  Akan jadi alamat web: andstudio.id/studio/nama-studio-anda
                </p>
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-200 mb-1.5">
                  Link / Akun Instagram <span className="text-slate-400 text-xs font-normal">(Opsional)</span>
                </label>
                <input
                  type="text"
                  name="instagram_url"
                  value={formData.instagram_url}
                  onChange={handleChange}
                  placeholder="https://instagram.com/studioanda"
                  className={inputClass}
                />
              </div>

              {/* Logo Studio / Foto Profil (Opsional) */}
              <div>
                <label className="block text-sm font-medium text-slate-200 mb-1.5">
                  Logo Studio / Foto Profil <span className="text-slate-400 text-xs font-normal">(Opsional)</span>
                </label>
                <div className="relative">
                  <input
                    type="file"
                    name="logo_file"
                    accept="image/*"
                    onChange={handleChange}
                    className={inputClass}
                    id="logo_file"
                  />
                  {logoPreview && (
                    <div className="mt-2">
                      <img
                        src={logoPreview}
                        alt="Preview Logo"
                        className="h-20 w-auto rounded-lg border border-slate-200"
                      />
                    </div>
                  )}
                </div>
                <p className="mt-1 text-xs text-slate-400">
                  Format: JPG, PNG, WebP. Maksimal 2MB.
                </p>
              </div>

              <div className="grid sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-slate-200 mb-1.5">
                    Jam Buka Operasional
                  </label>
                  <input
                    type="time"
                    name="operational_start"
                    value={formData.operational_start}
                    onChange={handleChange}
                    className={inputClass}
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-slate-200 mb-1.5">
                    Jam Tutup Operasional
                  </label>
                  <input
                    type="time"
                    name="operational_end"
                    value={formData.operational_end}
                    onChange={handleChange}
                    className={inputClass}
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-200 mb-1.5">
                  Nama Anda
                </label>
                <input
                  type="text"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  required
                  placeholder="Nama lengkap"
                  className={inputClass}
                />
              </div>

              <div className="grid sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-slate-200 mb-1.5">
                    Email
                  </label>
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    required
                    placeholder="studio@email.com"
                    className={inputClass}
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-slate-200 mb-1.5">
                    WhatsApp
                  </label>
                  <input
                    type="tel"
                    name="phone"
                    value={formData.phone}
                    onChange={handleChange}
                    required
                    placeholder="08xxxxxxxxxx"
                    className={inputClass}
                  />
                </div>
              </div>

              <div className="grid sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-slate-200 mb-1.5">
                    Kata Sandi
                  </label>
                  <input
                    type="password"
                    name="password"
                    value={formData.password}
                    onChange={handleChange}
                    required
                    minLength={8}
                    placeholder="Min. 8 karakter"
                    className={inputClass}
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-slate-200 mb-1.5">
                    Ulangi Kata Sandi
                  </label>
                  <input
                    type="password"
                    name="password_confirmation"
                    value={formData.password_confirmation}
                    onChange={handleChange}
                    required
                    minLength={8}
                    placeholder="Ulangi kata sandi"
                    className={inputClass}
                  />
                </div>
              </div>

              <label className="flex items-start gap-2.5 text-sm text-slate-400 cursor-pointer pt-1">
                <input
                  type="checkbox"
                  name="terms"
                  checked={formData.terms}
                  onChange={handleChange}
                  required
                  className="mt-0.5 rounded border-white/20 accent-emerald-500 w-4 h-4"
                />
                <span className="text-slate-300">
                  Saya menyetujui Syarat & Ketentuan ANDstudio, dan data
                  diproses sesuai Kebijakan Privasi.
                </span>
              </label>

              <button
                type="submit"
                disabled={loading}
                className="relative w-full inline-flex h-12 overflow-hidden rounded-xl p-[1px] focus:outline-none group mt-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span className="absolute inset-[-1000%] animate-[spin_3s_linear_infinite] bg-[conic-gradient(from_90deg_at_50%_50%,#0a0908_0%,#ffffff_50%,#0a0908_100%)]" />
                <span className="inline-flex h-full w-full cursor-pointer items-center justify-center rounded-xl bg-[#0a0908]/90 px-6 py-2 text-sm font-medium text-white backdrop-blur-3xl transition-all hover:bg-[#0a0908]/70">
                  {loading ? (
                    <>
                      <Loader2 className="mr-2 w-4 h-4 animate-spin text-emerald-400" />
                      Membuat akun...
                    </>
                  ) : (
                    <>
                      <svg
                        className="mr-2 w-4 h-4 text-emerald-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth="2"
                          d="M5 13l4 4L19 7"
                        />
                      </svg>
                      Buat Akun Studio Saya
                    </>
                  )}
                </span>
              </button>
            </form>

            <p className="mt-6 pt-5 border-t border-white/10 text-center text-sm text-slate-400">
              Sudah punya akun studio?{" "}
              <a
                href={`${appUrl}/login`}
                className="text-emerald-400 hover:text-emerald-300 font-semibold hover:underline"
              >
                Masuk di sini
              </a>
            </p>
          </motion.div>
        </div>
      </div>
    </main>
  );
}

export default function DaftarPage() {
  return (
    <Suspense fallback={<div className="min-h-screen bg-[#0a0908] text-white flex items-center justify-center">Memuat...</div>}>
      <DaftarContent />
    </Suspense>
  );
}
