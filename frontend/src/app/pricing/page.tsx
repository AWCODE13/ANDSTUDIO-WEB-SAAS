"use client";

import { Bokeh as StudioBokeh, SparkleDeco as StudioSparkleDeco, StudioBeam } from '@/components/StudioDecor';
import StarBorder from '@/components/StarBorder';
import { Sparkles, Check, X, CreditCard, Users, Megaphone, Globe, Shield, Zap, TrendingUp } from 'lucide-react';
import { useState, useEffect } from 'react';

export default function PricingPage() {
  const [appUrl, setAppUrl] = useState('http://localhost:8000');

  useEffect(() => {
    if (typeof window !== 'undefined') {
      const backendPort = window.location.port === '3000' ? '8000' : window.location.port;
      setAppUrl(`${window.location.protocol}//${window.location.hostname}${backendPort ? `:${backendPort}` : ''}`);
    }
  }, []);

  const features = [
    { label: 'Jumlah paket foto', free: '3 paket', pro: 'Unlimited' },
    { label: 'Booking per bulan', free: '50 booking', pro: 'Unlimited' },
    { label: 'Kirim link foto via WA', free: true, pro: true },
    { label: 'Laporan keuangan', free: true, pro: true },
    { label: 'WhatsApp Blast otomatis', free: false, pro: true },
    { label: 'Custom domain studio', free: false, pro: true },
    { label: 'Manajemen Multi-Staff', free: '1 admin', pro: 'Unlimited' },
    { label: 'Prioritas support', free: false, pro: true },
  ];

  return (
    <>
      {/* Background layers */}
      <div className="fixed inset-0 z-[-1] overflow-hidden">
        <StudioBeam />
        <StudioBokeh />
        <StudioSparkleDeco />
      </div>

      {/* Header component removed – using default layout header */}

      <main className="relative z-10 min-h-screen bg-transparent pt-20">
        {/* Hero Section */}
        <section className="relative px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
          <div className="max-w-5xl mx-auto text-center">
            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium mb-6">
              <Sparkles className="w-4 h-4" />
              <span>Harga Transparan &ndash; Tanpa Biaya Tersembunyi</span>
            </div>
            <h1 className="max-w-5xl mx-auto text-4xl md:text-5xl lg:text-6xl leading-[1.15] font-bold text-white mb-6">
              Pilih Plan yang Tepat<br />
              <span className="text-4xl md:text-5xl lg:text-6xl font-serif italic text-emerald-400">untuk Studio Anda</span>
            </h1>
            <p className="max-w-2xl mx-auto text-lg md:text-xl text-slate-300 mb-10">
              Mulai dari <strong className="text-white">Rp 0</strong> atau upgrade ke Pro untuk akses tidak terbatas.
              Semua plan mencakup <strong className="text-emerald-400">trial 14 hari gratis</strong> tanpa kartu kredit.
            </p>
          </div>
        </section>

        {/* Plan Comparison */}
        <section className="relative px-4 sm:px-6 lg:px-8 pb-20">
          <div className="max-w-5xl mx-auto">
            <div className="grid lg:grid-cols-2 gap-8">
              {/* Free Plan */}
              <article className="relative group bg-white/[0.02] rounded-2xl border border-white/10 p-8 transition-all duration-300 hover:border-white/20">
                <div className="absolute top-4 right-4 px-3 py-1 rounded-full bg-white/5 text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                  Free
                </div>
                <div className="flex items-center gap-3 mb-6">
                  <div className="w-14 h-14 rounded-2xl bg-slate-800/50 border border-white/10 flex items-center justify-center">
                    <CreditCard className="w-7 h-7 text-slate-400" />
                  </div>
                  <div>
                    <h3 className="text-2xl font-bold text-white">Plan Free</h3>
                    <p className="text-sm text-slate-400">Selamanya Rp 0 / bulan</p>
                  </div>
                </div>

                <ul className="space-y-4 mb-8">
                  {features.map((f, i) => (
                    <li key={i} className="flex items-start gap-3">
                      {f.free === true ? (
                        <Check className="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" />
                      ) : f.free === false ? (
                        <X className="w-5 h-5 text-slate-500 flex-shrink-0 mt-0.5" />
                      ) : (
                        <span className="w-5 h-5 flex-shrink-0 mt-0.5 text-emerald-400 font-medium">✓</span>
                      )}
                      <div className="text-sm text-slate-300">
                        <span className="font-medium text-white">{f.label}</span>
                        {' '}&ndash;{' '}
                        <span className="text-emerald-400 font-semibold">
                          {typeof f.free === 'string' ? f.free : (f.free ? '✓' : '✕')}
                        </span>
                      </div>
                    </li>
                  ))}
                </ul>

                <StarBorder
                  as="button"
                  className="w-full backdrop-blur-3xl font-medium"
                  backgroundColor="rgba(10, 9, 8, 0.9)"
                  color="#ffffff"
                  trailColor="#34d399"
                  radius={99}
                  theme="dark"
                  stars={2}
                  onClick={() => window.location.href = '/daftar?plan=free'}
                >
                  Mulai Trial 14 Hari Gratis
                </StarBorder>
              </article>

              {/* Pro Plan */}
              <article className="relative group bg-emerald-500/5 rounded-2xl border-2 border-emerald-500/30 p-8 transition-all duration-300">
                <div className="absolute -top-3 right-6 px-3 py-1 rounded-full bg-emerald-500 text-white text-[10px] font-bold">
                  POPULER
                </div>
                <div className="flex items-center gap-3 mb-6">
                  <div className="w-14 h-14 rounded-2xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center">
                    <Zap className="w-7 h-7 text-emerald-400" />
                  </div>
                  <div>
                    <h3 className="text-2xl font-bold text-white">Plan Pro</h3>
                    <p className="text-sm text-slate-400">Rp 299.000 <span className="font-normal">/ bulan</span></p>
                  </div>
                </div>

                <ul className="space-y-4 mb-8">
                  {features.map((f, i) => (
                    <li key={`pro-${i}`} className="flex items-start gap-3">
                      {f.pro === true ? (
                        <Check className="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" />
                      ) : f.pro === false ? (
                        <X className="w-5 h-5 text-slate-500 flex-shrink-0 mt-0.5" />
                      ) : (
                        <span className="w-5 h-5 flex-shrink-0 mt-0.5 text-emerald-400 font-medium">✓</span>
                      )}
                      <div className="text-sm text-slate-300">
                        <span className="font-medium text-white">{f.label}</span>
                        {' '}&ndash;{' '}
                        <span className="text-emerald-400 font-semibold">
                          {typeof f.pro === 'string' ? f.pro : (f.pro ? '✓' : '✕')}
                        </span>
                      </div>
                    </li>
                  ))}
                </ul>

                <StarBorder
                  as="button"
                  className="w-full backdrop-blur-3xl font-medium"
                  backgroundColor="rgba(10, 9, 8, 0.9)"
                  color="#34d399"
                  trailColor="#ffffff"
                  radius={99}
                  theme="dark"
                  stars={2}
                  sparkle
                  onClick={() => window.location.href = '/daftar?plan=pro'}
                >
                  Upgrade ke Pro &ndash; Trial 14 Hari
                </StarBorder>
              </article>
            </div>
          </div>
        </section>

        {/* FAQ */}
        <section className="relative px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
          <div className="max-w-3xl mx-auto">
            <div className="text-center mb-16">
              <h2 className="text-3xl md:text-4xl font-bold text-white mb-4">Pertanyaan Umum</h2>
              <p className="text-slate-400 text-lg">Semua yang perlu Anda ketahui sebelum memulai.</p>
            </div>

            <dl className="space-y-4">
              {[
                {
                  q: 'Apa yang terjadi setelah trial 14 hari habis?',
                  a: 'Akun Anda otomatis beralih ke <strong>Plan Free</strong> (bukan nonaktif). Anda tetap bisa mengakses dashboard dengan fitur terbatas: 3 paket foto, 50 booking/bulan, 1 staff. Upgrade ke Pro kapan saja untuk membuka fitur unlimited.'
                },
                {
                  q: 'Apakah saya bisa downgrade dari Pro ke Free?',
                  a: 'Ya. Anda bisa membatalkan langganan Pro kapan saja di halaman <em>Plan & Tagihan</em>. Fitur Pro akan aktif sampai akhir periode tagihan yang sudah dibayar, lalu otomatis turun ke Free. Data dan riwayat booking Anda tetap aman.'
                },
                {
                  q: 'Bagaimana cara pembayarannya?',
                  a: 'Pembayaran via <strong>Midtrans</strong> mendukung Virtual Account (BCA, BRI, BNI, Mandiri, dll), E-Wallet (GoPay, ShopeePay, DANA, OVO), QRIS, dan Kartu Kredit/Debit. Invoice dibuat otomatis setiap bulan di tanggal yang sama.'
                },
                {
                  q: 'Apakah ada biaya tambahan per transaksi/booking?',
                  a: '<strong>Tidak.</strong> Harga bulanan sudah include semua fitur tanpa fee persentase per booking. Anda hanya bayar fee Midtrans standar (biasanya Rp 2.000-3.500 per transaksi) yang dibebankan ke pelanggan, bukan ke studio.'
                },
                {
                  q: 'Bisakah saya pakai custom domain sendiri?',
                  a: 'Ya, fitur <strong>Custom Domain</strong> tersedia di Plan Pro. Anda bisa mengarahkan domain sendiri (contoh: <code>studio-anda.com</code>) ke halaman booking ANDstudio Anda. Setup DNS dilakukan sekali saja dari dashboard.'
                },
                {
                  q: 'Bagaimana jika tagihan Pro tidak dibayar tepat waktu?',
                  a: 'Ada <strong>grace period 7 hari</strong> setelah invoice dibuat. Selama itu akses Pro tetap penuh. Jika lewat 7 hari belum dibayar, akun otomatis beralih ke mode <strong>read-only (Plan Free)</strong>. Bayar tagihan tertunggak kapan saja untuk mengaktifkan kembali Pro.'
                }
              ].map((faq, i) => (
                <div key={i} className="group bg-white/[0.02] rounded-xl border border-white/10 overflow-hidden transition-all duration-300 hover:border-emerald-500/30">
                  <dt className="p-5 pr-12 font-medium text-white flex items-center justify-between cursor-pointer">
                    {faq.q}
                    <TrendingUp className="w-5 h-5 text-slate-500 group-hover:text-emerald-400 transition-transform duration-300 group-data-[open=true]:rotate-180" />
                  </dt>
                  <dd className="px-5 pb-5 text-slate-300 text-sm leading-relaxed border-t border-white/5">
                    {faq.a}
                  </dd>
                </div>
              ))}
            </dl>
          </div>
        </section>

        {/* Bottom CTA */}
        <section className="relative px-4 sm:px-6 lg:px-8 pb-20 lg:pb-28">
          <div className="max-w-2xl mx-auto text-center">
            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium mb-6">
              <Shield className="w-4 h-4" />
              <span>Tidak ada komitmen jangka panjang &ndash; Batalkan kapan saja</span>
            </div>
            <h3 className="text-3xl md:text-4xl font-bold text-white mb-4">
              Siap Memulai Manajemen Studio Modern?
            </h3>
            <p className="text-slate-400 text-lg mb-8 max-w-lg mx-auto">
              Bergabung dengan ratusan studio yang sudah beralih ke sistem reservasi mandiri, 
              pembukuan otomatis, dan pertumbuhan pendapatan yang terukur.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
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
                onClick={() => window.location.href = '/daftar?plan=pro'}
              >
                Mulai Uji Coba 14 Hari Gratis
                <Zap className="ml-2 w-4 h-4 inline-block" />
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
                onClick={() => { document.getElementById('partner')?.scrollIntoView({ behavior: 'smooth' }); }}
              >
                Lihat Studio Partner
              </StarBorder>
            </div>
            <p className="mt-6 text-slate-400 text-sm text-center">
              Sudah punya akun studio?{' '}
              <a
                href={`${appUrl}/login`}
                className="text-emerald-400 hover:text-emerald-300 font-medium underline underline-offset-2 transition-colors"
              >
                Masuk di sini
              </a>
            </p>
          </div>
        </section>
      </main>
    </>
  );
}