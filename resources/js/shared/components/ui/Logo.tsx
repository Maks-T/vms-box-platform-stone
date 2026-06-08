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
                       variant = 'orange-dark',
                       className,
                       imgClassName,
                       href = route('catalog'),
                       onClick
                     }: LogoProps) {
  const getLogoSrc = () => {
    switch (variant) {
      case 'dark-outline':
        return '/images/logo-dark-outline.svg';
      case 'light-solid':
        return '/images/logo-light-solid.svg';
      case 'dark-solid':
        return '/images/logo-dark-solid.svg';
      case 'orange-dark':
      default:
        return '/images/logo-orange-dark.svg';
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
        alt="VMS-NC Box"
        className={cn("h-12 md:h-16 w-auto", imgClassName)}
      />
    </Link>
  );
}