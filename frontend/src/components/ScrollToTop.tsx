"use client";

import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { ArrowUp } from "lucide-react";
import { useEffect, useState } from "react";

export default function ScrollToTop() {
  const [visible, setVisible] = useState(false);
  const reduceMotion = useReducedMotion();

  useEffect(() => {
    const onScroll = () => setVisible(window.scrollY > 600);
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <AnimatePresence>
      {visible && (
        <motion.button
          initial={reduceMotion ? { opacity: 0 } : { opacity: 0, scale: 0.6, y: 20 }}
          animate={reduceMotion ? { opacity: 1 } : { opacity: 1, scale: 1, y: 0 }}
          exit={reduceMotion ? { opacity: 0 } : { opacity: 0, scale: 0.6, y: 20 }}
          whileHover={reduceMotion ? undefined : { y: -3, scale: 1.06 }}
          whileTap={reduceMotion ? undefined : { scale: 0.92 }}
          transition={{ type: "spring", stiffness: 380, damping: 24 }}
          onClick={() => window.scrollTo({ top: 0, behavior: "smooth" })}
          aria-label="Kembali ke atas"
          className="fixed bottom-6 right-6 z-40 w-11 h-11 rounded-full bg-[#0b1220] border border-white/15 text-slate-300 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.9)] hover:text-blue-300 hover:border-blue-400/40 flex items-center justify-center transition-colors"
        >
          <ArrowUp className="w-4 h-4" />
        </motion.button>
      )}
    </AnimatePresence>
  );
}
