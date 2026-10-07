import React from 'react';
import { Check } from 'lucide-react';
import { cn } from '@/shared/lib/utils';

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
              "w-4 h-4 border rounded-sm transition-all duration-150 flex items-center justify-center",
              isChecked ? "bg-[#212B36] border-[#212B36]" : "bg-white border-gray-300 group-hover:border-[#25CED1]"
            )}>
              <Check className={cn("w-3 h-3 text-white stroke-[3px] transition-opacity", isChecked ? "opacity-100" : "opacity-0")} />
            </div>
          </div>

          <div className="flex items-center gap-2.5">
            <span className={cn(
              "text-xs leading-tight transition-colors select-none",
              isChecked ? "text-[#212B36] font-semibold" : "text-gray-600 font-normal group-hover:text-[#212B36]"
            )}>
              {opt.label} {/* Был value */}
            </span>
          </div>
        </label>
      );
    })}
  </div>
);
