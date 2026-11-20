"use client";

import { motion, useReducedMotion, type HTMLMotionProps } from "motion/react";
import Link from "next/link";
import type { ReactNode } from "react";

type Variant = "primary" | "secondary" | "ghost" | "success" | "danger";
type Size = "sm" | "md" | "lg";

const VARIANTS: Record<Variant, string> = {
  primary:
    "glass-button text-accent border border-accent/40 hover:bg-accent/20 active:scale-95 shadow-[0_4px_16px_rgba(6,182,212,0.15)]",
  secondary:
    "bg-white/[0.06] text-white ring-1 ring-inset ring-white/15 hover:bg-white/[0.12] hover:ring-white/25",
  ghost: "bg-transparent text-muted-foreground hover:bg-white/5 hover:text-white",
  success:
    "bg-gradient-to-r from-emerald-500 to-teal-400 text-white shadow-[0_0_0_1px_rgba(52,211,153,0.25),0_12px_30px_-12px_rgba(16,185,129,0.85)]",
  danger:
    "bg-gradient-to-r from-rose-500 to-red-500 text-white shadow-[0_0_0_1px_rgba(251,113,133,0.25),0_12px_30px_-12px_rgba(244,63,94,0.85)]",
};

const SIZES: Record<Size, string> = {
  sm: "px-3.5 py-2 text-xs rounded-lg gap-1.5",
  md: "px-5 py-2.5 text-sm rounded-xl gap-2",
  lg: "px-7 py-3.5 text-base rounded-xl gap-2",
};

const BASE =
  "relative inline-flex items-center justify-center font-semibold transition-colors duration-200 select-none whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden";

interface CommonProps {
  children: ReactNode;
  variant?: Variant;
  size?: Size;
  className?: string;
  shine?: boolean;
}

function useMotionProps(reduce: boolean | null) {
  return reduce
    ? {}
    : {
        whileHover: { y: -2 },
        whileTap: { scale: 0.97 },
        transition: { type: "spring" as const, stiffness: 400, damping: 22 },
      };
}

/**
 * Tombol dengan animasi hover/tap + efek kilau.
 */
export function Button({
  children,
  variant = "primary",
  size = "md",
  className = "",
  shine = true,
  ...props
}: CommonProps & HTMLMotionProps<"button">) {
  const reduceMotion = useReducedMotion();
  const motionProps = useMotionProps(reduceMotion);

  return (
    <motion.button
      className={`${BASE} ${VARIANTS[variant]} ${SIZES[size]} ${
        shine ? "shine" : ""
      } ${className}`}
      {...motionProps}
      {...props}
    >
      {children}
    </motion.button>
  );
}

/**
 * Link yang tampil seperti tombol, dengan animasi yang sama.
 */
export function ButtonLink({
  children,
  variant = "primary",
  size = "md",
  className = "",
  shine = true,
  ...props
}: CommonProps & HTMLMotionProps<"a">) {
  const reduceMotion = useReducedMotion();
  const motionProps = useMotionProps(reduceMotion);

  return (
    <motion.a
      className={`${BASE} ${VARIANTS[variant]} ${SIZES[size]} ${
        shine ? "shine" : ""
      } ${className}`}
      {...motionProps}
      {...props}
    >
      {children}
    </motion.a>
  );
}

/**
 * Link Next.js dengan gaya tombol.
 */
export function NextButtonLink({
  children,
  variant = "primary",
  size = "md",
  className = "",
  shine = true,
  href,
  ...props
}: CommonProps & { href: string; onClick?: () => void; target?: string }) {
  const reduceMotion = useReducedMotion();

  return (
    <motion.div
      className="inline-flex"
      whileHover={reduceMotion ? undefined : { y: -2 }}
      whileTap={reduceMotion ? undefined : { scale: 0.97 }}
      transition={{ type: "spring", stiffness: 400, damping: 22 }}
    >
      <Link
        href={href}
        target={props.target}
        onClick={props.onClick}
        className={`${BASE} ${VARIANTS[variant]} ${SIZES[size]} ${
          shine ? "shine" : ""
        } ${className}`}
      >
        {children}
      </Link>
    </motion.div>
  );
}
