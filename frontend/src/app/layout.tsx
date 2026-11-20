import type { Metadata, Viewport } from "next";
import { Inter, Playfair_Display, Geist_Mono } from "next/font/google";
import { AnimatePresence } from "motion/react";
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import { ShaderBackground } from "@/components/ShaderBackground";
import ScrollToTop from "@/components/ScrollToTop";
import ScrollRestoration from "@/components/ScrollRestoration";
import { ThemeProvider } from "@/context/ThemeContext";
import "./globals.css";

const inter = Inter({
  variable: "--font-inter",
  subsets: ["latin"],
});

const playfair = Playfair_Display({
  variable: "--font-playfair",
  subsets: ["latin"],
  weight: ["400", "500", "600", "700"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: {
    default: "ANDstudio - Sistem Reservasi Studio Foto",
    template: "%s - ANDstudio",
  },
  description:
    "Platform reservasi studio foto online. Pesan jadwal sesi foto, bayar aman via Midtrans, dan terima hasil foto via WhatsApp.",
  keywords: ["studio foto", "reservasi foto", "wisuda", "self photo", "ANDstudio"],
  openGraph: {
    title: "ANDstudio - Sistem Reservasi Studio Foto",
    description:
      "Kelola reservasi studio foto Anda: katalog paket, kalender jadwal, pembayaran Midtrans, dan kirim hasil foto via WhatsApp.",
    type: "website",
    locale: "id_ID",
  },
};

export const viewport: Viewport = {
  themeColor: [
    { media: "(prefers-color-scheme: dark)", color: "#0f172a" },
    { media: "(prefers-color-scheme: light)", color: "#f8fafc" },
  ],
  width: "device-width",
  initialScale: 1,
};

const themeInitScript = `(function(){try{var t=localStorage.getItem('theme');if(!t){t=window.matchMedia('(prefers-color-scheme: light)').matches?'light':'dark';}document.documentElement.setAttribute('data-theme',t);if(t==='dark'){document.documentElement.classList.add('dark');}else{document.documentElement.classList.remove('dark');}}catch(e){}})();`;

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html
      lang="id"
      suppressHydrationWarning
      className={`${inter.variable} ${playfair.variable} ${geistMono.variable} h-full antialiased`}
    >
      <head>
        <script dangerouslySetInnerHTML={{ __html: themeInitScript }} />
      </head>
      <body className="min-h-[100dvh] flex flex-col text-foreground transition-colors overflow-x-hidden">
        <ShaderBackground className="fixed inset-0 z-[-1] pointer-events-none transform-gpu will-change-transform w-full h-full" />
        <ThemeProvider>
          <ScrollRestoration />
          <Navbar />
          <AnimatePresence mode="wait">
            <main className="flex-1 animate-page-enter overflow-x-hidden">
              {children}
            </main>
          </AnimatePresence>
          <Footer />
          <ScrollToTop />
        </ThemeProvider>
      </body>
    </html>
  );
}
