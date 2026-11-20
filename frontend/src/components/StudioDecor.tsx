"use client";

import { useReducedMotion } from "motion/react";

/**
 * StudioDecor: Efek pencahayaan studio foto elegan (bukan ikon literal).
 * Menghadirkan atmosfer studio profesional lewat cahaya ambient, bokeh, dan sparkle.
 */

// ========== AMBIENT BOKEH (Titik Cahaya Studio) ==========
export function Bokeh({ count = 5 }: { count?: number }) {
  const reduceMotion = useReducedMotion();
  if (reduceMotion) return null;

  const spots = [
    { top: "18%", left: "12%", size: 80, delay: "0s", opacity: 0.12 },
    { top: "65%", left: "8%", size: 60, delay: "-2.3s", opacity: 0.10 },
    { top: "32%", right: "15%", size: 90, delay: "-3.8s", opacity: 0.14 },
    { top: "78%", right: "20%", size: 50, delay: "-1.5s", opacity: 0.08 },
    { top: "45%", left: "50%", size: 70, delay: "-5.1s", opacity: 0.11 },
  ];

  return (
    <div className="pointer-events-none absolute inset-0 z-0 overflow-hidden" aria-hidden>
      {spots.slice(0, count).map((s, i) => (
        <div
          key={i}
          className="absolute animate-bokeh"
          style={
            {
              top: s.top,
              left: s.left,
              right: s.right,
              width: s.size,
              height: s.size,
              animationDelay: s.delay,
              opacity: s.opacity,
            } as React.CSSProperties
          }
        >
          <div
            className="w-full h-full rounded-full"
            style={{
              background:
                "radial-gradient(circle, rgba(212,175,55,0.18) 0%, rgba(140,110,74,0.08) 40%, transparent 70%)",
            }}
          />
        </div>
      ))}
    </div>
  );
}

// ========== STUDIO BEAM (Cahaya Lampu Studio) ==========
export function StudioBeam() {
  const reduceMotion = useReducedMotion();

  return (
    <div
      className={
        reduceMotion
          ? "pointer-events-none absolute hidden"
          : "pointer-events-none absolute -top-16 left-1/2 w-[28rem] h-[20rem] -translate-x-1/2 animate-beam origin-top z-0"
      }
      style={{
        background:
          "conic-gradient(from 180deg at 50% 0%, transparent 0deg, rgba(212,175,55,0.06) 18deg, transparent 36deg)",
      }}
      aria-hidden
    />
  );
}

// ========== SPARKLE DECO (Kilauan Blitz Kamera) ==========
export function SparkleDeco({ count = 4 }: { count?: number }) {
  const reduceMotion = useReducedMotion();
  if (reduceMotion) return null;

  const sparkles = [
    { top: "22%", left: "28%", size: 3, delay: "0s" },
    { top: "68%", left: "18%", size: 2.5, delay: "-1.8s" },
    { top: "38%", right: "25%", size: 3.5, delay: "-3.2s" },
    { top: "75%", right: "35%", size: 2, delay: "-2.4s" },
  ];

  return (
    <div className="pointer-events-none absolute inset-0 z-0 overflow-hidden" aria-hidden>
      {sparkles.slice(0, count).map((sp, i) => (
        <div
          key={i}
          className="absolute animate-sparkle"
          style={
            {
              top: sp.top,
              left: sp.left,
              right: sp.right,
              animationDelay: sp.delay,
            } as React.CSSProperties
          }
        >
          <div
            className="rounded-full bg-[#d4af37]"
            style={{
              width: `${sp.size}px`,
              height: `${sp.size}px`,
              boxShadow: `0 0 ${sp.size * 2}px rgba(212,175,55,0.5)`,
            }}
          />
        </div>
      ))}
    </div>
  );
}

// ========== SPINNING APERTURE (Dekorasi Aperture Halus) ==========
export function SpinningAperture({ className = "" }: { className?: string }) {
  const reduceMotion = useReducedMotion();

  return (
    <div className={`pointer-events-none absolute z-0 ${className}`} aria-hidden>
      <div
        className={reduceMotion ? "" : "animate-aperture"}
        style={{ opacity: 0.06 }}
      >
        <svg
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="1"
          className="w-full h-full text-[#d4af37]"
        >
          <circle cx="12" cy="12" r="10" />
          <path d="M12 2 L12 22 M2 12 L22 12 M4.93 4.93 L19.07 19.07 M19.07 4.93 L4.93 19.07" />
        </svg>
      </div>
    </div>
  );
}
