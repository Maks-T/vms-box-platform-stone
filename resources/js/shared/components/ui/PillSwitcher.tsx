import React from 'react';
import {cn} from '@/shared/lib/utils';

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
        "flex items-center gap-1 bg-zinc-100 rounded p-0.5 border border-zinc-200 select-none",
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
              "px-2.5 py-0.5 rounded-sm text-[11px] font-bold transition-colors cursor-pointer uppercase",
              isActive
                ? "bg-white text-zinc-900 shadow-sm border border-zinc-200/60"
                : "text-zinc-500 hover:text-zinc-900"
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