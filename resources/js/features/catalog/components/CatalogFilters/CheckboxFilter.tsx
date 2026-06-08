import React from 'react';
import { Check } from 'lucide-react';
import { cn } from '@/shared/lib/utils';
import { FilterSwatch } from './FilterSwatch';

export const CheckboxFilter = ({ options, activeValues, onToggle }: any) => (
  <div className="flex flex-col gap-3">
    {options.map((opt: any) => {
      const isChecked = activeValues.includes(opt.slug);
      const { hex, image } = opt.meta || {};

      const hasVisual = (typeof image === 'string' && image.trim() !== '') ||
        (typeof hex === 'string' && hex.trim() !== '');

      return (
        <label key={opt.slug} className="flex items-center gap-3.5 cursor-pointer group select-none py-0.5">
          <div className="relative flex items-center justify-center shrink-0">
            <input
              type="checkbox"
              className="peer sr-only"
              checked={isChecked}
              onChange={() => onToggle(opt.slug)}
            />
            <div className={cn(
              "w-5 h-5 border-2 rounded-[6px] transition-all duration-200 flex items-center justify-center",
              isChecked ? "bg-primary border-primary shadow-sm" : "bg-white border-slate-200 group-hover:border-slate-300"
            )}>
              <Check className={cn("w-3.5 h-3.5 text-white stroke-[4px] transition-opacity", isChecked ? "opacity-100" : "opacity-0")} />
            </div>
          </div>

          <div className="flex items-center gap-3">

            {hasVisual && <FilterSwatch image={image} hex={hex} size="sm" />}

            <span className={cn(
              "text-[14px] leading-tight transition-colors",
              isChecked ? "text-foreground font-bold" : "text-muted-foreground font-medium group-hover:text-primary"
            )}>
              {opt.value}
            </span>
          </div>
        </label>
      );
    })}
  </div>
);
