"use client";

import { motion, useReducedMotion, type Variants } from "motion/react";
import { createContext, useContext, type ReactNode } from "react";

type Direction = "up" | "down" | "left" | "right" | "none";

interface RevealProps {
  children: ReactNode;
  delay?: number;
  duration?: number;
  direction?: Direction;
  distance?: number;
  once?: boolean;
  className?: string;
  as?: "div" | "section" | "li" | "span";
}

const OFFSET: Record<Direction, { x: number; y: number }> = {
  up: { x: 0, y: 24 },
  down: { x: 0, y: -24 },
  left: { x: 32, y: 0 },
  right: { x: -32, y: 0 },
  none: { x: 0, y: 0 },
};

const EASE = [0.22, 1, 0.36, 1] as const;

/**
 * Context untuk menyamakan delay antar item dalam satu group.
 */
const StaggerContext = createContext<{ stagger: number; index: number }>({
  stagger: 0,
  index: 0,
});

/**
 * Wrapper untuk animasi masuk saat elemen masuk viewport.
 * Otomatis dinonaktifkan bila pengguna memilih "reduce motion".
 */
export default function Reveal({
  children,
  delay = 0,
  duration = 0.55,
  direction = "up",
  distance,
  once = true,
  className = "",
  as = "div",
}: RevealProps) {
  const reduceMotion = useReducedMotion();
  const offset = OFFSET[direction];
  const d = distance ?? Math.max(Math.abs(offset.x), Math.abs(offset.y));

  const Component = motion[as];

  if (reduceMotion) {
    const Static = as;
    return <Static className={className}>{children}</Static>;
  }

  return (
    <Component
      className={className}
      initial={{ opacity: 0, x: offset.x ? Math.sign(offset.x) * d : 0, y: offset.y ? Math.sign(offset.y) * d : 0 }}
      whileInView={{ opacity: 1, x: 0, y: 0 }}
      viewport={{ once: true, amount: 0.1 }}
      transition={{ duration, delay, ease: EASE }}
    >
      {children}
    </Component>
  );
}

/**
 * Container untuk animasi stagger (anak-anak muncul berurutan).
 *
 * Catatan: item animasi secara mandiri (bukan mengandalkan propagasi
 * variant dari parent). Ini penting untuk daftar yang isinya berubah
 * dinamis — item yang baru di-mount akan selalu muncul, bukan
 * tertinggal di opacity 0.
 */
export function RevealGroup({
  children,
  className = "",
  stagger = 0.08,
  delay = 0,
  as = "div",
}: {
  children: ReactNode;
  className?: string;
  stagger?: number;
  delay?: number;
  as?: "div" | "ul" | "section";
}) {
  const reduceMotion = useReducedMotion();
  const Component = motion[as];

  const variants: Variants = {
    hidden: {},
    show: { transition: { staggerChildren: stagger, delayChildren: delay } },
  };

  if (reduceMotion) {
    const Static = as;
    return <Static className={className}>{children}</Static>;
  }

  return (
    <Component
      className={className}
      variants={variants}
      initial="hidden"
      whileInView="show"
      viewport={{ once: true, amount: 0.1 }}
    >
      {children}
    </Component>
  );
}

/**
 * Item anak dari RevealGroup.
 *
 * Animasi dijalankan sendiri (whileInView per item) supaya aman
 * terhadap daftar yang berubah isinya saat runtime.
 */
export function RevealItem({
  children,
  className = "",
  as = "div",
}: {
  children: ReactNode;
  className?: string;
  as?: "div" | "li" | "article";
}) {
  const reduceMotion = useReducedMotion();
  const Component = motion[as];

  if (reduceMotion) {
    const Static = as;
    return <Static className={className}>{children}</Static>;
  }

  return (
    <Component
      className={className}
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.05 }}
      transition={{ duration: 0.45, ease: EASE }}
    >
      {children}
    </Component>
  );
}

/**
 * Item dengan delay berurutan (untuk daftar statis yang ingin stagger).
 * Gunakan di dalam RevealGroup.
 */
export function StaggeredItem({
  children,
  index,
  className = "",
  as = "div",
}: {
  children: ReactNode;
  index: number;
  className?: string;
  as?: "div" | "li" | "article";
}) {
  const reduceMotion = useReducedMotion();
  const { stagger } = useContext(StaggerContext);
  const Component = motion[as];

  if (reduceMotion) {
    const Static = as;
    return <Static className={className}>{children}</Static>;
  }

  return (
    <Component
      className={className}
      initial={{ opacity: 0, y: 20, scale: 0.97 }}
      whileInView={{ opacity: 1, y: 0, scale: 1 }}
      viewport={{ once: true, margin: "-60px" }}
      transition={{ duration: 0.5, delay: index * stagger, ease: EASE }}
    >
      {children}
    </Component>
  );
}
