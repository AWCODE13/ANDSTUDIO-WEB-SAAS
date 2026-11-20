"use client";

import { motion, useReducedMotion } from "motion/react";
import { useEffect, useRef, useState } from "react";

/**
 * Hitung mundur berbentuk lingkaran dengan animasi progress.
 */
export default function Countdown({
  deadline,
  onExpire,
  size = 64,
}: {
  deadline: string | null;
  onExpire?: () => void;
  size?: number;
}) {
  const reduceMotion = useReducedMotion();
  const [seconds, setSeconds] = useState<number | null>(null);
  const firedRef = useRef(false);

  // Countdown adalah nilai yang diturunkan dari jam dunia (Date.now()),
  // jadi harus disinkronkan via interval. Pola ini disengaja;
  // useSyncExternalStore tidak cocok karena snapshot-nya selalu berubah.
  useEffect(() => {
    if (!deadline) {
      setSeconds(null);
      return;
    }

    const tick = () => {
      const diff = Math.floor((new Date(deadline).getTime() - Date.now()) / 1000);
      setSeconds(diff > 0 ? diff : 0);
    };

    tick();
    const timer = setInterval(tick, 1000);
    return () => clearInterval(timer);
  }, [deadline]);

  useEffect(() => {
    if (seconds === 0 && !firedRef.current) {
      firedRef.current = true;
      onExpire?.();
    }
  }, [seconds, onExpire]);
  if (seconds === null) return null;

  const expired = seconds === 0;

  const total = 15 * 60;
  const pct = Math.max(0, Math.min(1, seconds / total));
  const radius = size / 2 - 5;
  const circumference = 2 * Math.PI * radius;
  const offset = circumference * (1 - pct);

  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  const label = `${m}:${s.toString().padStart(2, "0")}`;

  const urgent = seconds <= 120;
  const color = urgent ? "#fb7185" : "#fbbf24";

  return (
    <div className="flex items-center gap-3">
      <div className="relative" style={{ width: size, height: size }}>
        <svg width={size} height={size} className="-rotate-90">
          <circle
            cx={size / 2}
            cy={size / 2}
            r={radius}
            fill="none"
            stroke="currentColor"
            strokeWidth={4}
            className="text-white/10"
          />
          <motion.circle
            cx={size / 2}
            cy={size / 2}
            r={radius}
            fill="none"
            stroke={color}
            strokeWidth={4}
            strokeLinecap="round"
            strokeDasharray={circumference}
            initial={reduceMotion ? false : { strokeDashoffset: circumference }}
            animate={{ strokeDashoffset: offset }}
            transition={{ duration: 0.9, ease: "linear" }}
          />
        </svg>
        <div className="absolute inset-0 flex items-center justify-center">
          <span
            className={`font-bold tabular-nums text-sm ${
              urgent ? "text-rose-400" : "text-amber-400"
            }`}
          >
            {label}
          </span>
        </div>
      </div>

      <div className="text-xs">
        <p className="font-semibold text-slate-200">Sisa waktu pembayaran</p>
        <p className="text-slate-400">
          {expired
            ? "Waktu habis — booking dibatalkan otomatis"
            : "Selesaikan sebelum slot kembali tersedia"}
        </p>
      </div>
    </div>
  );
}
