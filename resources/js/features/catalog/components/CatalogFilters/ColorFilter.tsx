import React from 'react';
import { Check } from 'lucide-react';
import { cn } from '@/shared/lib/utils';
import { FilterSwatch } from './FilterSwatch';

export const ColorFilter = ({ options, activeValues, onToggle }: any) => (
  <div className="flex flex-col gap-3">
    {options.map((opt: any) => {
      const isChecked = activeValues.includes(opt.slug);

      return (
        <label
          key={opt.slug}
          className="flex items-center gap-3.5 cursor-pointer group select-none py-0.5"
        >
          <div className="relative flex items-center justify-center shrink-0">
            <input
              type="checkbox"
              className="peer sr-only"
              checked={isChecked}
              onChange={() => onToggle(opt.slug)}
            />

            {}
            <div className="relative rounded-full transition-colors duration-200">
              <FilterSwatch
                image={opt.meta?.image}
                hex={opt.meta?.hex}
                size="sm"
                className="w-6 h-6 border-slate-200 group-hover:border-slate-300 transition-colors"
              />


              {isChecked && (
                <div className="absolute inset-0 flex items-center justify-center bg-black/30 rounded-full transition-opacity">
                  <Check className="w-4 h-4 text-white drop-shadow-md stroke-[4px]" />
                </div>
              )}
            </div>
          </div>

          <span
            className={cn(
              "text-[14px] leading-tight transition-colors",
              isChecked
                ? "text-foreground font-bold"
                : "text-muted-foreground font-medium group-hover:text-primary"
            )}
          >
            {opt.value}
          </span>
        </label>
      );
    })}
  </div>
);
