"use client";

import { motion, useReducedMotion } from "motion/react";
import Link from "next/link";
import { ArrowLeft } from "lucide-react";
import { Bokeh, SparkleDeco, StudioBeam } from "@/components/StudioDecor";
import StarBorder from "@/components/StarBorder";
import { useRouter } from "next/navigation";

const STEPS = [
  {
    step: "01",
    title: "Klien Pilih Jadwal Mandiri",
    desc: "Calon klien membuka link studio Anda, memilih paket, waktu sesi, dan add-on tanpa perlu chat berkali-kali. Semua transparansi harga terlihat jelas.",
  },
  {
    step: "02",
    title: "Pembayaran DP atau Lunas",
    desc: "Invoice Midtrans terbit instan. Klien bisa bayar DP 30%, 50%, atau lunas langsung via transfer bank, e-wallet, atau QRIS. Slot jadwal diamankan otomatis saat pembayaran berhasil.",
  },
  {
    step: "03",
    title: "Tim Studio Dapat Notifikasi",
    desc: "Booking masuk ke dashboard studio real-time. Tim Anda dapat notifikasi WhatsApp + notifikasi in-app tentang klien baru, jadwal, lokasi, dan catatan khusus.",
  },
  {
    step: "04",
    title: "Sesi Foto Berjalan Tertib",
    desc: "Tidak ada bentrok jadwal. Setiap sesi sudah dikurasi waktu start dan end yang jelas. Fotografer tahu jam berapa harus siap, dan kapan sesi berakhir untuk persiapan klien berikutnya.",
  },
  {
    step: "05",
    title: "Upload Foto ke Cloud",
    desc: "Setelah sesi, Anda upload foto hasil ke Google Drive atau cloud storage yang tersambung. Sistem kami handle kompresi dan backup otomatis.",
  },
  {
    step: "06",
    title: "Klien Dapat Link Download Otomatis",
    desc: "Link Google Drive + instruksi download otomatis dikirim ke WhatsApp klien dalam hitungan detik. Klien tinggal buka link dan download. Tidak perlu Anda manual chat.",
  },
];

const BENEFITS = [
  {
    title: "Efisiensi Waktu",
    desc: "Studio bisa handle 3-5x lebih banyak klien tanpa tambah staff, karena proses otomatis.",
  },
  {
    title: "Pembayaran Terjamin",
    desc: "Tidak ada lagi piutang menggantung atau klien yang kabur. Bayar duluan, jadwal terkunci.",
  },
  {
    title: "Klien Puas",
    desc: "Pengalaman professional, komunikasi clear, hasil cepat. Rating tinggi = referral lebih banyak.",
  },
  {
    title: "Data Aman",
    desc: "Semua data klien, transaksi, dan foto backup di cloud terenkripsi. Tidak perlu takut kehilangan.",
  },
];

export default function HowItWorksPage() {
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
                Cara Kerja AndStudio
              </h1>

              <p className="text-lg sm:text-xl text-slate-300 leading-relaxed max-w-2xl">
                Dari klien booking hingga foto terkirim, semua otomatis dan mudah. Inilah
                alur lengkapnya.
              </p>
            </motion.div>
          </div>
        </section>

        {/* STEPS */}
        <section className="relative py-16 sm:py-24">
          <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="space-y-8 relative">
              {/* Vertical line connecting steps */}
              <div className="absolute left-4 sm:left-1/2 top-0 bottom-0 w-0.5 bg-gradient-to-b from-white/20 to-transparent transform sm:-translate-x-1/2"></div>

              {STEPS.map((item, i) => (
                <motion.div
                  key={item.step}
                  initial={reduceMotion ? false : { opacity: 0, x: i % 2 === 0 ? -24 : 24 }}
                  whileInView={{ opacity: 1, x: 0 }}
                  transition={{ delay: i * 0.1, duration: 0.5 }}
                  viewport={{ once: true }}
                  className={`flex gap-6 sm:gap-0 ${i % 2 === 0 ? "sm:flex-row" : "sm:flex-row-reverse"}`}
                >
                  {/* Step number dot */}
                  <div className="flex flex-col items-center flex-shrink-0 sm:w-1/2">
                    <div className="w-10 h-10 rounded-full bg-white/10 border border-white/30 flex items-center justify-center z-10 relative">
                      <span className="text-sm font-bold text-white">{item.step}</span>
                    </div>
                  </div>

                  {/* Content */}
                  <div className={`flex-1 ${i % 2 === 0 ? "sm:text-right sm:pr-12" : "sm:pl-12"}`}>
                    <h3 className="text-xl font-bold text-white mb-3">{item.title}</h3>
                    <p className="text-slate-400 leading-relaxed">{item.desc}</p>
                  </div>
                </motion.div>
              ))}
            </div>
          </div>
        </section>

        {/* BENEFITS */}
        <section className="relative py-16 sm:py-24 border-t border-white/10">
          <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              viewport={{ once: true }}
              className="mb-12"
            >
              <h2 className="text-3xl sm:text-4xl font-bold text-white mb-4">
                Hasil yang Dirasakan Studio
              </h2>
              <p className="text-slate-400 max-w-2xl">
                Bukan hanya teknologi, tapi dampak nyata untuk bisnis Anda
              </p>
            </motion.div>

            <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
              {BENEFITS.map((benefit, i) => (
                <motion.div
                  key={benefit.title}
                  initial={reduceMotion ? false : { opacity: 0, y: 16 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ delay: i * 0.1, duration: 0.5 }}
                  viewport={{ once: true }}
                  className="border border-white/10 rounded-xl p-6"
                >
                  <h3 className="text-lg font-bold text-white mb-3">{benefit.title}</h3>
                  <p className="text-sm text-slate-400 leading-relaxed">{benefit.desc}</p>
                </motion.div>
              ))}
            </div>
          </div>
        </section>

        {/* CTA */}
        <section className="relative py-16 sm:py-24 border-t border-white/10">
          <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              viewport={{ once: true }}
            >
              <h2 className="text-3xl sm:text-4xl font-bold text-white mb-6">
                Siap Transform Studio Anda?
              </h2>

              <motion.div
                initial={reduceMotion ? false : { opacity: 0, y: 12 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.2, duration: 0.5 }}
                viewport={{ once: true }}
                className="flex flex-col sm:flex-row items-center justify-center gap-4"
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
                  onClick={() => router.push("/daftar")}
                >
                  Mulai Uji Coba Gratis
                  <svg className="ml-2 w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </StarBorder>

                <button
                  onClick={() => router.push("/faq")}
                  className="px-6 py-3 rounded-full border border-white/20 text-white hover:border-white/50 transition-colors font-medium"
                >
                  Lihat FAQ
                </button>
              </motion.div>
            </motion.div>
          </div>
        </section>

        <div className="h-8"></div>
      </div>
    </main>
  );
}
