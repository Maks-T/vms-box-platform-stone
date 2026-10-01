import React from 'react';
import { Check } from 'lucide-react';
import { cn } from '@/shared/lib/utils';
import { FilterSwatch } from './FilterSwatch';

export const CheckboxFilter = ({ options, activeValues, onToggle }: any) => (
  <div className="flex flex-col gap-3">
    {options.map((opt: any) => {
      const isChecked = activeValues.includes(opt.key);
      const { hex, image } = opt.meta || {};

      const hasVisual = (typeof image === 'string' && image.trim() !== '') ||
        (typeof hex === 'string' && hex.trim() !== '');

      return (
        <label key={opt.key} className="flex items-center gap-3.5 cursor-pointer group select-none py-0.5">
          <div className="relative flex items-center justify-center shrink-0">
            <input
              type="checkbox"
              className="peer sr-only"
              checked={isChecked}
              onChange={() => onToggle(opt.key)}
            />
            <div className={cn(
              "w-4 h-4 border rounded-[4px] transition-all duration-150 flex items-center justify-center",
              isChecked ? "bg-[#08274D] border-[#08274D]" : "bg-white border-slate-300 group-hover:border-slate-400"
            )}>
              <Check className={cn("w-3 h-3 text-white stroke-[2.5px] transition-opacity", isChecked ? "opacity-100" : "opacity-0")} />
            </div>
          </div>

          <div className="flex items-center gap-2.5">
            {hasVisual && <FilterSwatch image={image} hex={hex} size="sm" />}

            <span className={cn(
              "text-[13px] leading-tight transition-colors select-none",
              isChecked ? "text-[#08274D] font-semibold" : "text-slate-600 font-normal group-hover:text-[#08274D]"
            )}>
              {opt.label} {/* Был value */}
            </span>
          </div>
        </label>
      );
    })}
  </div>
);
