"use client";

import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import Link from "next/link";
import { useEffect, useState, useRef } from "react";
import { Menu, X, CalendarCheck, History } from "lucide-react";
import StarBorder from "@/components/StarBorder";
import { useRouter } from "next/navigation";
import TechText from "@/components/TechText";
import PillNav from "@/components/ui/pill-nav";

const NAV_LINKS = [
  { label: "Beranda", href: "/" },
  { label: "Fitur Unggulan", href: "/#fitur" },
  { label: "Studio Partner", href: "/#partner" },
  { label: "Cara Kerja", href: "/#cara-kerja" },
  { label: "Statistik", href: "/#statistik" },
];

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false);
  const [open, setOpen] = useState(false);
  const buttonRef = useRef<HTMLButtonElement>(null);
  const reduceMotion = useReducedMotion();
  const router = useRouter();

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 8);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    const onResize = () => {
      if (window.innerWidth >= 768) setOpen(false);
    };
    window.addEventListener("resize", onResize);
    return () => window.removeEventListener("resize", onResize);
  }, []);

  useEffect(() => {
    document.body.style.overflow = open ? "hidden" : "";
    return () => {
      document.body.style.overflow = "";
    };
  }, [open]);

  return (
    <header
      className={`sticky top-0 z-50 transform-gpu isolate backdrop-blur-md bg-[#0a0908]/80 border-b border-white/5 transition-all duration-300`}
    >
      <nav className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-20 items-center">
                    {/* Logo */}
          <Link href="/" className="flex flex-col items-start leading-none group relative z-10">
            <div className="h-8 flex items-center">
              <TechText
                text="and studio"
                fontWeight={500}
                fontSize={28}
                reveal="letter"
                dashLength={4}
                dashGap={2}
                specks={15}
                intro="none"
                introDuration={2}
                clickKick
                guides={false}
              />
              <span className="text-white text-2xl font-serif ml-[-2px]">.</span>
            </div>
            <span className="text-[9px] uppercase tracking-[0.2em] text-slate-400/70 font-medium mt-1">
              saas photography management
            </span>
          </Link>

          {/* Desktop links - PillNav */}
          <div className="hidden md:flex items-center">
            <PillNav
              items={NAV_LINKS}
              linkComponent={Link}
              defaultActiveIndex={0}
              theme="dark"
              surface="glass"
              layout="split"
              itemStyle="pill"
              hoverEffect="fill"
              indicator="dot"
              size="md"
            />
          </div>

          {/* Desktop actions: pill buttons */}
          <div className="hidden md:flex items-center gap-3">
            <StarBorder
              as="button"
              className="backdrop-blur-3xl font-medium"
              backgroundColor="rgba(10, 9, 8, 0.9)"
              color="#ffffff"
              trailColor="#34d399"
              radius={99}
              theme="dark"
              stars={2}
              onClick={() => router.push("/pesanan")}
            >
              Cek Pesanan
            </StarBorder>

            <StarBorder
              as="button"
              className="backdrop-blur-3xl font-medium"
              backgroundColor="rgba(10, 9, 8, 0.9)"
              color="#34d399"
              trailColor="#ffffff"
              radius={99}
              theme="dark"
              stars={2}
              sparkle
              onClick={() => router.push("/pricing")}
            >
              <svg className="w-3.5 h-3.5 text-white inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              Daftar Studio
            </StarBorder>
          </div>

          {/* Mobile toggle */}
          <div className="flex md:hidden items-center gap-2">
            <StarBorder
              as="button"
              className="backdrop-blur-3xl font-medium"
              backgroundColor="rgba(10, 9, 8, 0.9)"
              color="#ffffff"
              trailColor="#34d399"
              radius={99}
              theme="dark"
              stars={2}
              onClick={() => setOpen((v) => !v)}
              aria-label={open ? "Tutup menu" : "Buka menu"}
              aria-expanded={open}
            >
              {open ? (
                <svg className="w-5 h-5 text-white inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              ) : (
                <svg className="w-5 h-5 text-white inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              )}
            </StarBorder>
          </div>
        </div>
      </nav>

      {/* Mobile menu */}
      <AnimatePresence>
        {open && (
          <motion.div
            initial={reduceMotion ? { opacity: 1 } : { opacity: 0, height: 0 }}
            animate={reduceMotion ? { opacity: 1 } : { opacity: 1, height: "auto" }}
            exit={reduceMotion ? { opacity: 0 } : { opacity: 0, height: 0 }}
            transition={{ duration: 0.28, ease: [0.22, 1, 0.36, 1] }}
            className="md:hidden overflow-y-auto max-h-[calc(100vh-80px)] border-t border-white/10 bg-[#0a0908]/95 backdrop-blur-md"
          >
            <div className="px-5 py-5 space-y-3">
              {NAV_LINKS.map((link) => (
                <Link
                  key={link.label}
                  href={link.href}
                  onClick={() => setOpen(false)}
                  className="block px-4 py-3 rounded-2xl text-base font-medium text-white hover:bg-white/5 transition-colors"
                >
                  {link.label}
                </Link>
              ))}

              <div className="pt-3 border-t border-white/10 space-y-3">
                <StarBorder
                  as="button"
                  className="w-full backdrop-blur-3xl font-medium"
                  backgroundColor="rgba(10, 9, 8, 0.9)"
                  color="#ffffff"
                  trailColor="#34d399"
                  radius={99}
                  theme="dark"
                  stars={2}
                  onClick={() => { router.push("/pesanan"); setOpen(false); }}
                >
                  Cek Pesanan Klien
                </StarBorder>

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
                  onClick={() => { router.push("/daftar"); setOpen(false); }}
                >
                  Daftarkan Studio Anda
                </StarBorder>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </header>
  );
}
