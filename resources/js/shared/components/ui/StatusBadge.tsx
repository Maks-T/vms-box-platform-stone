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
    blue: "text-[#08274D] border-slate-200 bg-slate-50",
    success: "text-emerald-700 border-emerald-200 bg-emerald-50/70",
    warning: "text-amber-800 border-amber-200 bg-amber-50/70",
  };

  const dotVariants = {
    blue: "bg-[#3D98FF]",
    success: "bg-emerald-500",
    warning: "bg-amber-500",
  };

  return (
    <Component
      className={cn(
        "group inline-flex items-center gap-2 px-2.5 py-1 rounded-md transition-all duration-150 border",
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