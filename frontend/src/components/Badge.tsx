"use client";

import { motion, useReducedMotion } from "motion/react";
import type { ReactNode } from "react";

type Tone =
  | "slate"
  | "blue"
  | "emerald"
  | "amber"
  | "rose"
  | "violet"
  | "sky";

const TONES: Record<Tone, string> = {
  slate: "bg-slate-400/10 text-slate-200 ring-white/15",
  blue: "bg-blue-500/20 text-blue-200 ring-blue-400/40",
  emerald: "bg-emerald-500/20 text-emerald-200 ring-emerald-400/40",
  amber: "bg-amber-500/20 text-amber-200 ring-amber-400/40",
  rose: "bg-rose-500/20 text-rose-200 ring-rose-400/40",
  violet: "bg-violet-500/20 text-violet-200 ring-violet-400/40",
  sky: "bg-sky-500/20 text-sky-200 ring-sky-400/40",
};

export default function Badge({
  children,
  tone = "slate",
  className = "",
  dot = false,
}: {
  children: ReactNode;
  tone?: Tone;
  className?: string;
  dot?: boolean;
}) {
  const reduceMotion = useReducedMotion();

  return (
    <motion.span
      whileHover={reduceMotion ? undefined : { scale: 1.05, y: -1 }}
      transition={{ type: "spring", stiffness: 400, damping: 22 }}
      className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold ring-1 ring-inset cursor-default ${TONES[tone]} ${className}`}
    >
      {dot && (
        <span className="w-1.5 h-1.5 rounded-full bg-current opacity-70" />
      )}
      {children}
    </motion.span>
  );
}
