import React from 'react';
import { ProductVariant, EavValueOption } from '@/types/catalog';
import { cn } from '@/shared/lib/utils';
import { Check } from 'lucide-react';

interface Props {
  variants: ProductVariant[];
  activeVariant: ProductVariant | null;
  onSelectVariant: (variant: ProductVariant) => void;
}

export function ProductVariantSelector({ variants, activeVariant, onSelectVariant }: Props) {
  if (!variants || variants.length <= 1) return null;

  const variantAttrsMap: Record<string, { name: string; options: { option: EavValueOption; variant: ProductVariant }[] }> = {};

  variants.forEach(v => {
    if (!v.attributes) return;

    Object.entries(v.attributes).forEach(([code, attr]) => {
      if (!attr.value) return;

      if (!variantAttrsMap[code]) {
        variantAttrsMap[code] = {
          name: attr.name || code,
          options: []
        };
      }

      const valObj = (typeof attr.value === 'object' && attr.value !== null && 'key' in attr.value)
        ? (attr.value as EavValueOption)
        : null;

      if (valObj) {
        if (!variantAttrsMap[code].options.some(item => item.option.key === valObj.key)) {
          variantAttrsMap[code].options.push({ option: valObj, variant: v });
        }
      }
    });
  });

  const attrEntries = Object.entries(variantAttrsMap);

  if (attrEntries.length > 0) {
    return (
      <div className="flex flex-col gap-3 py-4 border-y border-zinc-200 my-4">
        {attrEntries.map(([code, { name, options }]) => {
          const activeValObj = (activeVariant?.attributes?.[code]?.value as EavValueOption | undefined);
          const activeKey = activeValObj?.key;

          return (
            <div key={code} className="flex flex-col gap-2">
              <label className="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
                {name}:
              </label>
              <div className="flex flex-wrap gap-2">
                {options.map(({ option, variant }) => {
                  const isSelected = activeKey ? activeKey === option.key : activeVariant?.id === variant.id;

                  return (
                    <button
                      key={option.key}
                      type="button"
                      onClick={() => onSelectVariant(variant)}
                      className={cn(
                        "px-3 py-1.5 rounded text-xs font-semibold transition-all border cursor-pointer flex items-center gap-2",
                        isSelected
                          ? "bg-zinc-900 border-zinc-900 text-white shadow-sm"
                          : "bg-white border-zinc-200 text-zinc-700 hover:border-zinc-900 hover:text-zinc-900"
                      )}
                    >
                      {option.meta?.hex && (
                        <span
                          className="w-3.5 h-3.5 rounded-sm border border-zinc-300 shrink-0"
                          style={{ backgroundColor: option.meta.hex }}
                        />
                      )}
                      {option.meta?.image && (
                        <img
                          src={option.meta.image}
                          alt=""
                          className="w-4 h-4 rounded-sm object-cover border border-zinc-300 shrink-0"
                        />
                      )}
                      {isSelected && !option.meta?.hex && !option.meta?.image && <Check className="w-3.5 h-3.5 stroke-[3px]" />}
                      <span>{option.label}</span>
                    </button>
                  );
                })}
              </div>
            </div>
          );
        })}
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-2 py-4 border-y border-zinc-200 my-4">
      <label className="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
        Вариант исполнения (SKU):
      </label>
      <div className="flex flex-wrap gap-2">
        {variants.map((v) => {
          const isSelected = activeVariant?.id === v.id;
          const label = (v.name && v.name !== v.sku) ? v.name : v.sku;

          return (
            <button
              key={v.id}
              type="button"
              onClick={() => onSelectVariant(v)}
              className={cn(
                "px-3.5 py-1.5 rounded text-xs font-semibold transition-all border cursor-pointer",
                isSelected
                  ? "bg-zinc-900 border-zinc-900 text-white shadow-sm"
                  : "bg-white border-zinc-200 text-zinc-700 hover:border-zinc-900 hover:text-zinc-900"
              )}
            >
              {label}
            </button>
          );
        })}
      </div>
    </div>
  );
}