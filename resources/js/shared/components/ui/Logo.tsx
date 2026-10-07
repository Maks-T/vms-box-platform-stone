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
  return (
    <Link
      href={href}
      onClick={onClick}
      className={cn(
        "shrink-0 flex items-center active:scale-[0.98] transition-transform cursor-pointer select-none",
        className
      )}
    >
      <img
        src="https://quartz-master.com/upload/CAllcorp3/c31/p2j3dh6ddom78666pcvmdky3e2840039.svg"
        alt="QuartzMaster | Изделия из кварца"
        className={cn("h-[40px] md:h-[46px] w-auto", imgClassName)}
      />
    </Link>
  );
}