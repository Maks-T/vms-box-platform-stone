import React, { useState } from 'react';
import { Check, ChevronDown, ChevronUp } from 'lucide-react';
import { cn } from '@/shared/lib/utils';
import { FilterSwatch } from './FilterSwatch';

export const CheckboxFilter = ({ options, activeValues, onToggle, limit = 5 }: any) => {
  const [isExpanded, setIsExpanded] = useState(false);

  const hasMore = options.length > limit;
  const visibleOptions = isExpanded ? options : options.slice(0, limit);

  return (
    <div className="flex flex-col gap-2">
      <div className={cn(
        "flex flex-col gap-2",
        isExpanded && options.length > 8 && "max-h-52 overflow-y-auto custom-scrollbar pr-1"
      )}>
        {visibleOptions.map((opt: any) => {
          const isChecked = activeValues.includes(opt.key);
          const { hex, image } = opt.meta || {};
          const hasVisual = (typeof image === 'string' && image.trim() !== '') ||
            (typeof hex === 'string' && hex.trim() !== '');

          return (
            <label key={opt.key} className="flex items-center gap-2.5 cursor-pointer group select-none py-0.5">
              <div className="relative flex items-center justify-center shrink-0">
                <input
                  type="checkbox"
                  className="peer sr-only"
                  checked={isChecked}
                  onChange={() => onToggle(opt.key)}
                />
                <div className={cn(
                  "w-4 h-4 border rounded-sm transition-colors flex items-center justify-center",
                  isChecked ? "bg-zinc-900 border-zinc-900" : "bg-white border-zinc-300 group-hover:border-zinc-400"
                )}>
                  <Check className={cn("w-3 h-3 text-white stroke-[3px] transition-opacity", isChecked ? "opacity-100" : "opacity-0")} />
                </div>
              </div>

              <div className="flex items-center gap-2 min-w-0">
                {hasVisual && <FilterSwatch image={image} hex={hex} size="sm" />}
                <span className={cn(
                  "text-xs transition-colors leading-tight truncate block",
                  isChecked ? "text-zinc-900 font-bold" : "text-zinc-600 font-medium group-hover:text-zinc-900"
                )}>
                  {opt.label}
                </span>
              </div>
            </label>
          );
        })}
      </div>

      {hasMore && (
        <button
          type="button"
          onClick={() => setIsExpanded(!isExpanded)}
          className="text-[11px] font-semibold text-zinc-500 hover:text-zinc-900 flex items-center gap-1 mt-1 pt-1 transition-colors cursor-pointer self-start"
        >
          {isExpanded ? (
            <>
              Свернуть <ChevronUp className="w-3 h-3" />
            </>
          ) : (
            <>
              Показать ещё (+{options.length - limit}) <ChevronDown className="w-3 h-3" />
            </>
          )}
        </button>
      )}
    </div>
  );
};