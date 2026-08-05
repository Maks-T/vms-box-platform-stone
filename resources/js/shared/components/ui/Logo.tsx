import React from 'react';
import {Link} from '@inertiajs/react';
import {cn} from '@/shared/lib/utils';
import {route} from 'ziggy-js';

type LogoVariant = 'white' | 'black' | 'dark-outline' | 'light-solid' | 'dark-solid' | 'orange-dark';

interface LogoProps {
  variant?: LogoVariant;
  className?: string;
  imgClassName?: string;
  href?: string;
  onClick?: () => void;
}

export function Logo({
                       variant = 'black',
                       className,
                       imgClassName,
                       href = route('catalog'),
                       onClick
                     }: LogoProps) {
  const getLogoSrc = () => {
    switch (variant) {
      case 'light-solid':
        return '/images/logo_stoleshka_white.png';
      case 'black':
        return '/images/logo_stoleshka_black.png';
      default:
        return '/images/logo_stoleshka_white.png';
    }
  };

  return (
    <Link
      href={href}
      onClick={onClick}
      className={cn(
        "shrink-0 flex items-center active:scale-[0.98] transition-transform",
        className
      )}
    >
      <img
        src={getLogoSrc()}
        alt="Столешка Ру"
        className={cn("h-12 md:h-16 w-auto object-contain", imgClassName)}
      />
    </Link>
  );
}