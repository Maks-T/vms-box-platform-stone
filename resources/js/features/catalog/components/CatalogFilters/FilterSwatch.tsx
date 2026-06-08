import React from 'react';
import { cn } from '@/shared/lib/utils';

interface Props {
  image?: string | null;
  hex?: string | null;
  size?: 'sm' | 'md';
  className?: string;
}

export const FilterSwatch = ({ image, hex, size = 'sm', className }: Props) => {
  const sizeClasses = size === 'sm' ? 'w-5 h-5' : 'w-10 h-10';


  const hasImage = typeof image === 'string' && image.trim() !== '';

  const content = hasImage ? (
    <img src={image} className="w-full h-full object-cover" alt="" />
  ) : (

    <div className="w-full h-full" style={{ backgroundColor: hex || '#e2e8f0' }} />
  );

  return (
    <div
      className={cn(
        sizeClasses,
        "rounded-full border border-black/10 shadow-[inset_0_1px_2px_rgba(0,0,0,0.1),0_1px_1px_rgba(0,0,0,0.05)] overflow-hidden shrink-0 transition-transform",
        className
      )}
    >
      {content}
    </div>
  );
};
