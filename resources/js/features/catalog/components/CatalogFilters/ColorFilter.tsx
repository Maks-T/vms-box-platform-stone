import React from 'react';
import { Check } from 'lucide-react';
import { cn } from '@/shared/lib/utils';
import { FilterSwatch } from './FilterSwatch';

export const ColorFilter = ({ options, activeValues, onToggle }: any) => (
  <div className="flex flex-col gap-2">
    {options.map((opt: any) => {
      const isChecked = activeValues.includes(opt.key);

      return (
        <label key={opt.key} className="flex items-center gap-2.5 cursor-pointer group select-none py-0.5">
          <div className="relative flex items-center justify-center shrink-0">
            <input
              type="checkbox"
              className="peer sr-only"
              checked={isChecked}
              onChange={() => onToggle(opt.key)}
            />

            <div className="relative transition-transform duration-200">
              <FilterSwatch
                image={opt.meta?.image}
                hex={opt.meta?.hex}
                size="sm"
                className="w-4 h-4 rounded-sm border-zinc-300 group-hover:border-zinc-400 transition-colors"
              />

              {isChecked && (
                <div className="absolute inset-0 flex items-center justify-center bg-black/40 rounded-sm">
                  <Check className="w-3 h-3 text-white stroke-[3px]" />
                </div>
              )}
            </div>
          </div>

          <span className={cn(
            "text-xs transition-colors leading-tight",
            isChecked ? "text-zinc-900 font-bold" : "text-zinc-600 font-medium group-hover:text-zinc-900"
          )}>
            {opt.label}
          </span>
        </label>
      );
    })}
  </div>
);