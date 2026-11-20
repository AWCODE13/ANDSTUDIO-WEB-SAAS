"use client";

import { motion, useReducedMotion } from "motion/react";
import Link from "next/link";
import { ArrowLeft } from "lucide-react";
import { Bokeh, SparkleDeco, StudioBeam } from "@/components/StudioDecor";
import StarBorder from "@/components/StarBorder";
import { useRouter } from "next/navigation";

const TEAM_VALUES = [
  {
    title: "Inovasi Tanpa Henti",
    desc: "Kami membangun teknologi yang beradaptasi dengan kebutuhan studio foto modern, bukan sebaliknya.",
  },
  {
    title: "Kolaborasi Jujur",
    desc: "Feedback langsung dari studio partner adalah bahan bakar R&D kami. Suara Anda = Produk yang lebih baik.",
  },
  {
    title: "Obsesi Detail",
    desc: "Dari keamanan data hingga UX microinteraction, setiap aspek dirancang dengan presisi fotografer.",
  },
  {
    title: "Misi Sosial",
    desc: "Kami percaya studio foto adalah bisnis keluarga. Kami membantu UMKM bertahan dan berkembang.",
  },
];

const MILESTONES = [
  {
    year: "2024",
    title: "AndStudio Dilahirkan",
    desc: "Dimulai dari masalah pribadi: teman fotografer kewalahan kelola reservasi manual.",
  },
  {
    year: "2025",
    title: "50 Studio Partner",
    desc: "Ekspansi ke Jakarta, Bandung, dan Yogyakarta. Lebih dari 5000 pesanan berhasil dikelola.",
  },
  {
    year: "2026",
    title: "Platform Nasional",
    desc: "Infrastruktur cloud multi-region. Integrasi Midtrans, WhatsApp, dan Google Drive penuh.",
  },
];

const TEAM_MEMBERS = [
  {
    name: "Achmad Fauzin",
    role: "Founder & Product Lead",
    bio: "12 tahun exp backend development. Punya studio foto sendiri di Bandung.",
    emoji: "👨‍💻",
  },
  {
    name: "Rina Sutrisno",
    role: "Co-Founder & Design",
    bio: "UX Designer dari agensi branding ternama. Obsesi dengan typography dan spacing.",
    emoji: "🎨",
  },
  {
    name: "Budi Santoso",
    role: "Engineering Lead",
    bio: "DevOps specialist. Expertise dalam scaling infrastructure. Coffee enthusiast.",
    emoji: "⚙️",
  },
  {
    name: "Siti Nurhaliza",
    role: "Customer Success",
    bio: "Mantan project manager di studio foto. Paham pain point klien dari dalam.",
    emoji: "💬",
  },
];

export default function TentangPage() {
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
          <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
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
              className="max-w-4xl"
            >
              <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                Teknologi untuk Studio Foto yang Lebih Manusiawi
              </h1>

              <p className="text-lg sm:text-xl text-slate-300 leading-relaxed mb-8">
                AndStudio lahir dari pertanyaan sederhana: mengapa teknologi studio harus
                rumit, impersonal, dan jauh dari kebutuhan UMKM? Kami percaya bahwa setiap
                studio foto berhak memiliki sistem manajemen yang elegan, intuitif, dan
                dirancang khusus untuk mereka.
              </p>

              <p className="text-base sm:text-lg text-slate-400 leading-relaxed">
                Sejak 2024, kami telah membantu puluhan studio di Indonesia mengelola
                reservasi, jadwal, dan keuangan dengan lebih percaya diri. Bukan hanya
                platform—kami adalah mitra pertumbuhan bisnis foto Anda.
              </p>
            </motion.div>
          </div>
        </section>

        {/* MISI & VISI */}
        <section className="relative py-20 sm:py-28">
          <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="grid md:grid-cols-2 gap-16">
              {/* Misi */}
              <motion.div
                initial={reduceMotion ? false : { opacity: 0, x: -24 }}
                whileInView={{ opacity: 1, x: 0 }}
                transition={{ duration: 0.6 }}
                viewport={{ once: true }}
              >
                <h2 className="text-3xl sm:text-4xl font-bold text-white mb-6 leading-tight">Misi Kami</h2>
                <p className="text-slate-300 text-lg leading-relaxed">
                  Memberdayakan setiap studio foto di Indonesia dengan teknologi yang mudah
                  digunakan, aman, dan terjangkau—sehingga mereka bisa fokus pada seni
                  fotografi, bukan administrasi.
                </p>
              </motion.div>

              {/* Visi */}
              <motion.div
                initial={reduceMotion ? false : { opacity: 0, x: 24 }}
                whileInView={{ opacity: 1, x: 0 }}
                transition={{ duration: 0.6 }}
                viewport={{ once: true }}
              >
                <h2 className="text-3xl sm:text-4xl font-bold text-white mb-6 leading-tight">Visi Kami</h2>
                <p className="text-slate-300 text-lg leading-relaxed">
                  Menjadi platform manajemen studio foto paling dipercaya di Asia Tenggara,
                  dengan teknologi yang setara internasional namun tetap memahami konteks
                  lokal UMKM Indonesia.
                </p>
              </motion.div>
            </div>
          </div>
        </section>

        {/* VALUES */}
        <section className="relative py-16 sm:py-24 border-t border-white/10">
          <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              viewport={{ once: true }}
              className="mb-12"
            >
              <h2 className="text-3xl sm:text-4xl font-bold text-white mb-4">Nilai-Nilai Kami</h2>
              <p className="text-slate-400">
                Prinsip yang memandu setiap keputusan produk dan layanan kami
              </p>
            </motion.div>

            <div className="space-y-8">
              {TEAM_VALUES.map((value, i) => (
                <motion.div
                  key={value.title}
                  initial={reduceMotion ? false : { opacity: 0, x: -16 }}
                  whileInView={{ opacity: 1, x: 0 }}
                  transition={{ delay: i * 0.1, duration: 0.5 }}
                  viewport={{ once: true }}
                  className="border-l-2 border-white/10 pl-6"
                >
                  <h3 className="text-xl font-bold text-white mb-2">{value.title}</h3>
                  <p className="text-slate-400 leading-relaxed">{value.desc}</p>
                </motion.div>
              ))}
            </div>
          </div>
        </section>

        {/* MILESTONES */}
        <section className="relative py-16 sm:py-24 border-t border-white/10">
          <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              viewport={{ once: true }}
              className="mb-12"
            >
              <h2 className="text-3xl sm:text-4xl font-bold text-white mb-4">Perjalanan Kami</h2>
              <p className="text-slate-400">
                Dari ide sederhana hingga platform yang dipercaya ratusan studio
              </p>
            </motion.div>

            <div className="space-y-6">
              {MILESTONES.map((milestone, i) => (
                <motion.div
                  key={milestone.year}
                  initial={reduceMotion ? false : { opacity: 0, y: 16 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ delay: i * 0.1, duration: 0.5 }}
                  viewport={{ once: true }}
                >
                  <div className="flex gap-4 sm:gap-6">
                    <div className="flex-shrink-0 pt-1">
                      <span className="inline-block px-3 py-1 text-sm font-bold text-white border border-white/20 rounded">
                        {milestone.year}
                      </span>
                    </div>
                    <div className="flex-1">
                      <h3 className="text-lg font-bold text-white mb-2">{milestone.title}</h3>
                      <p className="text-slate-400">{milestone.desc}</p>
                    </div>
                  </div>
                </motion.div>
              ))}
            </div>
          </div>
        </section>

        {/* TEAM */}
        <section className="relative py-16 sm:py-24 border-t border-white/10">
          <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              viewport={{ once: true }}
              className="mb-12"
            >
              <h2 className="text-3xl sm:text-4xl font-bold text-white mb-4">Tim AndStudio</h2>
              <p className="text-slate-400">
                Dikurasi dari profesional di bidang teknologi, desain, dan fotografi
              </p>
            </motion.div>

            <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
              {TEAM_MEMBERS.map((member, i) => (
                <motion.div
                  key={member.name}
                  initial={reduceMotion ? false : { opacity: 0, y: 16 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ delay: i * 0.1, duration: 0.5 }}
                  viewport={{ once: true }}
                >
                  <div className="mb-4">
                    <h3 className="text-lg font-bold text-white mb-1">{member.name}</h3>
                    <p className="text-sm text-slate-400 font-medium mb-3">{member.role}</p>
                  </div>
                  <p className="text-sm text-slate-400 leading-relaxed">{member.bio}</p>
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
                Siap Bergabung dengan AndStudio?
              </h2>
              <p className="text-lg text-slate-300 mb-8 leading-relaxed">
                Ratusan studio foto Indonesia telah mempercayai kami. Saatnya studio Anda
                merasakan perbedaannya.
              </p>

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
                  onClick={() => router.push("/")}
                  className="px-6 py-3 rounded-full border border-white/20 text-white hover:border-white/50 hover:text-white transition-colors font-medium"
                >
                  Lihat Paket Harga
                </button>
              </motion.div>
            </motion.div>
          </div>
        </section>

        {/* FOOTER SPACING */}
        <div className="h-8"></div>
      </div>
    </main>
  );
}
