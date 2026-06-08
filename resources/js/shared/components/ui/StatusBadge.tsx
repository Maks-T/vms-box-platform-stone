import React, {ElementType, HTMLAttributes} from 'react';
import {cn} from '@/shared/lib/utils';

type StatusBadgeVariant = 'blue' | 'success' | 'warning';

interface StatusBadgeProps extends HTMLAttributes<HTMLElement> {
  variant?: StatusBadgeVariant;
  as?: ElementType;
  href?: string;
}

export default function StatusBadge({
                                      children,
                                      variant = 'blue',
                                      className,
                                      as: Component = 'div',
                                      ...props
                                    }: StatusBadgeProps) {
  const isInteractive = props.href || props.onClick || Component === 'a';

  const variants = {
    blue: "text-[#3D98FF] border-white/5 shadow-[inset_0_0_14px_rgba(144,198,221,0.20)] bg-white/[0.01] hover:bg-white/[0.04]",
    success: "text-emerald-500 border-emerald-500/30 shadow-[inset_0_0_12px_rgba(16,185,129,0.15)] bg-emerald-500/5 hover:bg-emerald-500/10",
    warning: "text-amber-500 border-amber-500/30 shadow-[inset_0_0_12px_rgba(245,158,11,0.15)] bg-amber-500/5 hover:bg-amber-500/10",
  };

  const dotVariants = {
    blue: "bg-[#3D98FF]",
    success: "bg-emerald-500",
    warning: "bg-amber-500",
  };

  return (
    <Component
      className={cn(
        "group inline-flex items-center gap-2.5 px-3 py-1.5 md:px-4 md:py-2 rounded-full transition-all duration-300 backdrop-blur-md border",
        isInteractive && "cursor-pointer active:scale-[0.98]",
        variants[variant],
        className
      )}
      {...props}
    >
      <div className="relative flex items-center justify-center w-1.5 h-1.5 shrink-0">
        <span className={cn("absolute w-full h-full rounded-full animate-ping opacity-60", dotVariants[variant])}
              style={{animationDuration: '3s'}}/>
        <span className={cn("relative w-full h-full rounded-full", dotVariants[variant])}/>
      </div>
      <span className="text-[12px] font-medium leading-normal font-sans break-words whitespace-nowrap">
        {children}
      </span>
    </Component>
  );
}