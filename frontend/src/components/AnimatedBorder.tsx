'use client';

import React from "react";

export function AnimatedBorder({
  children,
  className = "",
}: {
  children: React.ReactNode;
  className?: string;
}) {
  return (
    <div className={`relative overflow-hidden flex flex-col rounded-3xl p-[1px] group ${className}`}>
      <div className="absolute inset-0 z-0 h-full w-full rounded-3xl border border-white/5" />

      {/* Kembalikan pendaran komet menjadi putih statis */}
      <div className="absolute inset-[0] z-0 h-full w-full scale-[2] animate-[spin_4s_linear_infinite] bg-[conic-gradient(from_0deg,transparent_60%,rgba(255,255,255,1)_100%)] group-hover:bg-[conic-gradient(from_0deg,transparent_60%,rgba(6,182,212,1)_100%)] transition-colors duration-500" />

      {/* Kontainer dalam gelap pekat statis */}
      <div className="relative z-10 flex flex-1 flex-col h-full w-full rounded-[calc(1.5rem-1px)] bg-[#09090b]/95 backdrop-blur-sm text-white">
        {children}
      </div>
    </div>
  );
}