import React, {ElementType, HTMLAttributes} from 'react';
import {cn} from '@/shared/lib/utils';

type BadgeVariant = 'gray' | 'blue' | 'white';

interface BadgeProps extends HTMLAttributes<HTMLElement> {
  variant?: BadgeVariant;
  as?: ElementType;
  href?: string;
}

export default function Badge({children, variant = 'gray', className, as: Component = 'div', ...props}: BadgeProps) {
  const isInteractive = props.href || props.onClick;

  const variants = {
    gray: "bg-white/5 border border-white/10 text-muted-foreground hover:bg-white/10 hover:text-white",
    blue: "bg-[#005ECA]/15 border border-[#005ECA]/20 text-[#3D98FF] hover:bg-[#005ECA]/25 hover:text-white",
    white: "bg-white border border-[#E8EDF1]/50 text-slate-900 shadow-[inset_0_0_5px_rgba(89,171,206,0.25)]",
  };

  return (
    <Component
      className={cn(
        "inline-flex items-center justify-center gap-2 px-3 py-1 md:px-4 md:py-1.5 rounded-lg text-[13px] md:text-[14px] font-medium transition-all duration-300",
        isInteractive && "cursor-pointer active:scale-[0.98]",
        variants[variant],
        className
      )}
      {...props}
    >
      {children}
    </Component>
  );
}