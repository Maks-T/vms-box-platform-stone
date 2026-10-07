import React from 'react';
import { Check } from 'lucide-react';
import { cn } from '@/shared/lib/utils';

export const ColorFilter = ({ options, activeValues, onToggle }: any) => (
  <div className="flex flex-wrap gap-2 pt-1">
    {options.map((opt: any) => {
      const isChecked = activeValues.includes(opt.key);
      const hex = opt.meta?.hex || '#CBD5E1';
      const image = opt.meta?.image;

      return (
        <button
          key={opt.key}
          type="button"
          onClick={() => onToggle(opt.key)}
          title={opt.label}
          className={cn(
            "relative w-7 h-7 rounded-sm border cursor-pointer transition-all duration-150 flex items-center justify-center shrink-0",
            isChecked
              ? "border-[#25CED1] ring-2 ring-[#25CED1] ring-offset-1 scale-105 shadow-xs"
              : "border-gray-200 hover:border-gray-400 hover:scale-105"
          )}
          style={{ backgroundColor: image ? undefined : hex }}
        >
          {image && (
            <img
              src={image}
              alt={opt.label}
              className="w-full h-full object-cover rounded-xs"
            />
          )}
          {isChecked && (
            <div className="absolute inset-0 bg-black/30 rounded-xs flex items-center justify-center">
              <Check className="w-3.5 h-3.5 text-white stroke-[3px]" />
            </div>
          )}
        </button>
      );
    })}
  </div>
);
