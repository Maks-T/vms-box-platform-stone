import React, { ButtonHTMLAttributes } from 'react';
import { Link } from '@inertiajs/react';
import { cn } from '@/shared/lib/utils';
import { ArrowUpRight } from 'lucide-react';

interface ArrowPillButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  href?: string;
  variant?: 'primary' | 'glass';
}

export default function ArrowPillButton({
                                          href,
                                          className,
                                          children,
                                          onClick,
                                          variant = "primary",
                                          ...props
                                        }: ArrowPillButtonProps) {
  const variants = {
    primary: "bg-primary text-primary-foreground shadow-md hover:opacity-90",
    glass: "bg-white/[0.02] backdrop-blur-sm border border-white/10 text-white hover:bg-white/[0.06]",
  };

  const baseClasses = cn(
    "group inline-flex items-center justify-between gap-4 rounded-full pl-6 pr-1.5 py-1.5 transition-all duration-300 active:scale-[0.98]",
    variants[variant],
    className
  );

  const content = (
    <>
      <span className="font-medium text-[13px] md:text-[14px] uppercase tracking-widest pl-2">
        {children}
      </span>
      <div className={cn(
        "w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center transition-transform duration-300 ease-out group-hover:rotate-45 shrink-0",
        variant === 'primary'
          ? "bg-primary-foreground text-primary"
          : "bg-white text-slate-900"
      )}>
        <ArrowUpRight strokeWidth={2.5} className="w-4 h-4 md:w-5 md:h-5" />
      </div>
    </>
  );

  if (href) {
    const isExternal = href.startsWith('http');
    const Comp = isExternal ? 'a' : Link;
    return (
      // @ts-ignore
      <Comp href={href} target={isExternal ? "_blank" : undefined} className={baseClasses} onClick={onClick}>
        {content}
      </Comp>
    );
  }

  return <button onClick={onClick} className={baseClasses} {...props}>{content}</button>;
}