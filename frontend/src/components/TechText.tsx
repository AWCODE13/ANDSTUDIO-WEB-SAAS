import React, { useEffect, useRef, useState } from 'react';

interface TechTextProps {
  text: string;
  fontWeight?: number;
  fontSize?: number;
  reveal?: string;
  dashLength?: number;
  dashGap?: number;
  specks?: number;
  intro?: string;
  introDuration?: number;
  clickKick?: boolean;
  guides?: boolean;
}

const TechText: React.FC<TechTextProps> = ({
  text,
  fontWeight = 600,
  fontSize = 150,
  reveal = "letter",
  dashLength = 4,
  dashGap = 2,
  specks = 15,
  intro = "none",
  introDuration = 2,
  clickKick = false,
}) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const [mousePos, setMousePos] = useState({ x: 0, y: 0 });

  useEffect(() => {
    const handleMouseMove = (e: MouseEvent) => {
      if (containerRef.current) {
        const rect = containerRef.current.getBoundingClientRect();
        setMousePos({
          x: e.clientX - rect.left,
          y: e.clientY - rect.top,
        });
      }
    };
    window.addEventListener('mousemove', handleMouseMove);
    return () => window.removeEventListener('mousemove', handleMouseMove);
  }, []);

  return (
    <div
      ref={containerRef}
      className="relative inline-flex items-center cursor-default overflow-visible"
      style={{ fontSize: `${fontSize}px`, fontWeight, height: `${fontSize * 1.2}px` }}
    >
      <span className="relative pointer-events-none whitespace-nowrap font-serif lowercase tracking-tight text-white transition-colors">
        {text.split("").map((char, i) => (
          <span key={i} className="relative inline-block">
            {char === " " ? (
              <span key={i} className="w-4" />
            ) : (
              char
            )}
          </span>
        ))}
      </span>
    </div>
  );
};

export default TechText;