import React from 'react';
import { Link } from '@inertiajs/react';
import { cn } from '@/shared/lib/utils';
import { route } from 'ziggy-js';

type LogoVariant = 'dark-outline' | 'light-solid' | 'dark-solid' | 'orange-dark';

interface LogoProps {
  variant?: LogoVariant;
  className?: string;
  imgClassName?: string;
  href?: string;
  onClick?: () => void;
}

export function Logo({
                       variant = 'dark-solid',
                       className,
                       imgClassName,
                       href = route('catalog'),
                       onClick
                     }: LogoProps) {
  const isDarkBg = variant === 'light-solid';

  return (
    <Link
      href={href}
      onClick={onClick}
      className={cn(
        "shrink-0 flex items-center gap-3 active:scale-[0.98] transition-transform cursor-pointer select-none",
        className
      )}
    >
      {/* Фирменный знак: бронзовое зубчатое каменное кольцо MasterStone */}
      <div className={cn("relative w-10 h-10 md:w-11 md:h-11 shrink-0 flex items-center justify-center", imgClassName)}>
        <svg viewBox="0 0 100 100" className="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="50" cy="50" r="44" stroke="#9B6A38" strokeWidth="7" strokeDasharray="14 5" />
          <circle cx="50" cy="50" r="32" stroke="#9B6A38" strokeWidth="2.5" />
          <path d="M35 63V37L50 51L65 37V63" stroke="#9B6A38" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </div>

      {/* Фирменная типографика */}
      <div className="flex flex-col leading-tight">
        <div className="flex items-baseline gap-1">
          <span className={cn(
            "font-heading font-extrabold text-[19px] md:text-[21px] tracking-tight",
            isDarkBg ? "text-white" : "text-[#08274D]"
          )}>
            Master<span className="text-[#9B6A38]">Stone</span>
          </span>
        </div>
        <span className={cn(
          "text-[9px] md:text-[10px] uppercase font-semibold tracking-[0.18em]",
          isDarkBg ? "text-white/60" : "text-[#696973]"
        )}>
          Изделия из камня
        </span>
      </div>
    </Link>
  );
}