"use client";

import { motion, useReducedMotion } from "motion/react";
import Link from "next/link";
import { ArrowLeft, ChevronDown } from "lucide-react";
import { Bokeh, SparkleDeco, StudioBeam } from "@/components/StudioDecor";
import { useState } from "react";
import { useRouter } from "next/navigation";

const FAQ_ITEMS = [
  {
    category: "Produk & Fitur",
    items: [
      {
        q: "Apa saja fitur utama AndStudio?",
        a: "AndStudio menyediakan: kalender slot real-time, otomasi pembayaran via Midtrans, katalog paket dinamis, notifikasi WhatsApp otomatis, laporan finansial, dan multi-staff dengan hak akses berbeda.",
      },
      {
        q: "Apakah bisa integrasi dengan Instagram atau social media lain?",
        a: "Saat ini kami support integrasi Instagram untuk display profil studio. Integrasi platform lain sedang dalam roadmap kami.",
      },
      {
        q: "Berapa lama proses upload dan delivery foto ke klien?",
        a: "Tidak ada batasan. Anda bisa upload foto kapan saja ke cloud storage yang tersambung. Link download otomatis dikirim ke WhatsApp klien dalam hitungan detik setelah upload.",
      },
    ],
  },
  {
    category: "Harga & Pembayaran",
    items: [
      {
        q: "Berapa biaya langganan AndStudio?",
        a: "Kami punya dua plan: FREE (fitur dasar, unlimited pesanan) dan PRO (Rp 199k/bulan, fitur lengkap + prioritas support). Lihat detail di halaman pricing.",
      },
      {
        q: "Apakah ada trial gratis?",
        a: "Ya. Plan PRO bisa dicoba gratis selama 14 hari tanpa perlu kartu kredit.",
      },
      {
        q: "Apa saja metode pembayaran yang diterima?",
        a: "Kami support semua metode Midtrans: transfer bank, e-wallet (GoPay, OVO, DANA), QRIS, dan cicilan 0% tertentu.",
      },
    ],
  },
  {
    category: "Penggunaan & Teknis",
    items: [
      {
        q: "Bagaimana cara klien membuat reservasi?",
        a: "Klien cukup buka link studio Anda (misal: andstudio.app/studio/nama-studio), pilih paket, tanggal, waktu, dan add-on yang diinginkan. Bayar via Midtrans, dan jadwal langsung terkunci.",
      },
      {
        q: "Apakah sistem aman untuk data klien dan transaksi?",
        a: "Iya. Kami gunakan enkripsi SSL, secure API, dan comply dengan PCI DSS untuk keamanan pembayaran. Semua data disimpan di cloud terenkripsi.",
      },
      {
        q: "Berapa lama server AndStudio uptime?",
        a: "Kami target 99.9% uptime. Infrastruktur di multi-region cloud untuk reliability maksimal.",
      },
    ],
  },
  {
    category: "Dukungan & Onboarding",
    items: [
      {
        q: "Apakah ada dukungan dalam bahasa Indonesia?",
        a: "Tentu saja. Tim support kami siap dalam bahasa Indonesia via WhatsApp, email, atau chat.",
      },
      {
        q: "Bagaimana proses setup awal?",
        a: "1. Daftar akun. 2. Isi data studio (nama, lokasi, jam operasional). 3. Buat paket foto. 4. Customize warna/logo. 5. Share link ke klien. Semua bisa selesai dalam 30 menit.",
      },
      {
        q: "Apakah ada training atau dokumentasi?",
        a: "Ya. Kami sediakan video tutorial, panduan tertulis, dan live onboarding call untuk klien baru (plan PRO).",
      },
    ],
  },
];

export default function FAQPage() {
  const [expandedId, setExpandedId] = useState<string | null>(null);
  const reduceMotion = useReducedMotion();
  const router = useRouter();

  return (
    <main className="relative min-h-[100dvh] bg-transparent text-white transition-all duration-300 overflow-x-hidden">
      {/* Background */}
      <div className="fixed inset-0 z-[-1] pointer-events-none transform-gpu will-change-transform">
        <Bokeh count={5} />
        <StudioBeam />
        <SparkleDeco count={4} />
      </div>

      <div className="relative z-10">
        {/* HERO */}
        <section className="relative pt-12 pb-16 sm:pt-20 sm:pb-24">
          <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <Link
              href="/"
              className="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition-colors mb-8"
            >
              <ArrowLeft className="w-4 h-4" /> Kembali ke Beranda
            </Link>

            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 24 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
            >
              <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                Pertanyaan Umum
              </h1>

              <p className="text-lg sm:text-xl text-slate-300 leading-relaxed max-w-2xl">
                Jawaban untuk pertanyaan yang paling sering kami terima dari studio partner.
              </p>
            </motion.div>
          </div>
        </section>

        {/* FAQ SECTIONS */}
        <section className="relative py-16 sm:py-24">
          <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="space-y-12">
              {FAQ_ITEMS.map((section, sIdx) => (
                <motion.div
                  key={section.category}
                  initial={reduceMotion ? false : { opacity: 0, y: 24 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ delay: sIdx * 0.1, duration: 0.5 }}
                  viewport={{ once: true }}
                >
                  <h2 className="text-2xl font-bold text-white mb-6">{section.category}</h2>

                  <div className="space-y-3">
                    {section.items.map((item, iIdx) => {
                      const itemId = `${sIdx}-${iIdx}`;
                      const isOpen = expandedId === itemId;

                      return (
                        <motion.div
                          key={itemId}
                          initial={reduceMotion ? false : { opacity: 0, x: -16 }}
                          whileInView={{ opacity: 1, x: 0 }}
                          transition={{ delay: iIdx * 0.05, duration: 0.4 }}
                          viewport={{ once: true }}
                          className="border border-white/10 rounded-xl overflow-hidden"
                        >
                          <button
                            onClick={() => setExpandedId(isOpen ? null : itemId)}
                            className="w-full px-6 py-4 text-left hover:bg-white/5 transition-colors flex items-center justify-between gap-4"
                          >
                            <span className="font-medium text-white leading-tight">{item.q}</span>
                            <ChevronDown
                              className={`w-5 h-5 text-slate-400 flex-shrink-0 transition-transform ${
                                isOpen ? "rotate-180" : ""
                              }`}
                            />
                          </button>

                          <motion.div
                            initial={false}
                            animate={{ height: isOpen ? "auto" : 0, opacity: isOpen ? 1 : 0 }}
                            transition={{ duration: 0.3 }}
                            className="overflow-hidden"
                          >
                            <div className="px-6 py-4 border-t border-white/10 bg-white/[0.02] text-slate-300 leading-relaxed">
                              {item.a}
                            </div>
                          </motion.div>
                        </motion.div>
                      );
                    })}
                  </div>
                </motion.div>
              ))}
            </div>
          </div>
        </section>

        {/* CTA */}
        <section className="relative py-16 sm:py-24 border-t border-white/10">
          <div className="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              viewport={{ once: true }}
            >
              <h2 className="text-2xl sm:text-3xl font-bold text-white mb-4">
                Pertanyaan lain?
              </h2>
              <p className="text-slate-300 mb-6">
                Tim support kami siap membantu. Hubungi kami melalui WhatsApp atau email.
              </p>
              <button
                onClick={() => router.push("/daftar")}
                className="px-6 py-3 rounded-full border border-white/20 text-white hover:border-white/50 transition-colors font-medium"
              >
                Hubungi Support
              </button>
            </motion.div>
          </div>
        </section>

        <div className="h-8"></div>
      </div>
    </main>
  );
}
