import React from 'react';
import { cn } from '@/shared/lib/utils';


export interface PillOption<T> {
  value: T;
  label: string;
  title?: string;
}

interface PillSwitcherProps<T> {
  options: PillOption<T>[];
  activeValue: T;
  onChange: (value: T) => void;
  className?: string;
}


export default function PillSwitcher<T extends string | number | boolean>({
  options,
  activeValue,
  onChange,
  className,
}: PillSwitcherProps<T>) {
  return (
    <div
      className={cn(
        "flex items-center gap-2 bg-white/5 rounded-full p-1 border border-white/10 select-none",
        className
      )}
    >
      {options.map((option) => {
        const isActive = option.value === activeValue;

        return (
          <button
            key={String(option.value)}
            onClick={() => onChange(option.value)}
            className={cn(
              "px-3 py-1 rounded-full text-xs font-bold transition-colors cursor-pointer uppercase",
              isActive
                ? "bg-white text-slate-900 shadow-sm"
                : "text-white/60 hover:text-white"
            )}
            title={option.title}
          >
            {option.label}
          </button>
        );
      })}
    </div>
  );
}
