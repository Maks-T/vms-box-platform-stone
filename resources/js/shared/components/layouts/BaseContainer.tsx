import React, { HTMLAttributes } from 'react';
import { cn } from '@/shared/lib/utils';

interface BaseContainerProps extends HTMLAttributes<HTMLDivElement> {
  variant?: 'page' | 'content' | 'none';
}

export default function BaseContainer({ children, variant = 'content', className, ...props }: BaseContainerProps) {
  const variants = {
    page: "w-full max-w-[1920px] mx-auto px-2 md:px-6",
    content: "w-full max-w-[1280px] mx-auto px-4 md:px-8 lg:px-10",
    none: "",
  };

  return (
    <div className={cn(variants[variant], className)} {...props}>
      {children}
    </div>
  );
}