'use client';

import { useRef, useState } from 'react';

interface SpotlightCardProps {
  children: React.ReactNode;
  className?: string;
  noPadding?: boolean;
}

export default function SpotlightCard({
  children,
  className = '',
  noPadding = false,
}: SpotlightCardProps) {
  const divRef = useRef<HTMLDivElement>(null);
  const [isFocused, setIsFocused] = useState(false);
  const [position, setPosition] = useState({ x: 0, y: 0 });
  const [opacity, setOpacity] = useState(0);

  const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
    if (!divRef.current || isFocused) return;
    const div = divRef.current;
    const rect = div.getBoundingClientRect();
    setPosition({ x: e.clientX - rect.left, y: e.clientY - rect.top });
  };

  return (
    <div
      ref={divRef}
      onMouseMove={handleMouseMove}
      onMouseEnter={() => setOpacity(1)}
      onMouseLeave={() => setOpacity(0)}
      className={`relative overflow-hidden rounded-3xl bg-white/5 dark:bg-black/20 border border-white/20 dark:border-white/10 shadow-lg backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 group ${className}`}
    >
      {/* Efek Spotlight yang mengikuti kursor (z-0, di BAWAH konten) */}
      <div
        className="pointer-events-none absolute -inset-px z-0 transition-opacity duration-300"
        style={{
          opacity,
          background: `radial-gradient(600px circle at ${position.x}px ${position.y}px, rgba(255,255,255,0.15), transparent 40%)`,
        }}
      />

      {/* Konten Card (z-10, di ATAS spotlight) */}
      <div className={`relative z-10 bg-transparent ${noPadding ? '' : 'p-8'}`}>
        {children}
      </div>
    </div>
  );
}
