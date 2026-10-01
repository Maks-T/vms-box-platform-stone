import React from 'react';
import { cn } from '@/shared/lib/utils';
import { CatalogPills } from '@/features/catalog/components/CatalogPills';
import { ProductFamily } from '@/types/catalog';

interface Props {
  familiesList: ProductFamily[];
  activeFamily: string;
  setFamily: (family: string) => void;
  typesSchema: { code: string; name: string }[];
  productType: string;
  setProductType: (type: string) => void;
}

export function CatalogNavigationBlock({
                                         familiesList, activeFamily, setFamily, typesSchema, productType, setProductType
                                       }: Props) {
  return (
    <div className="flex flex-col w-full mb-8 relative z-10 pt-4">
      {}
      <CatalogPills
        families={familiesList}
        activeFamily={activeFamily}
        onChange={setFamily}
      />

      {}
      {typesSchema.length > 0 && (
        <div className="flex flex-wrap items-center gap-2 mt-2 pt-4 border-t border-slate-200/70">
          <button
            onClick={() => setProductType('')}
            className={cn(
              "px-3.5 py-1.5 rounded-lg text-[11px] md:text-[12px] font-heading font-semibold uppercase tracking-wider transition-all cursor-pointer",
              productType === ''
                ? "bg-[#08274D] text-white shadow-2xs"
                : "bg-slate-100/80 text-slate-600 hover:bg-slate-200/80 hover:text-[#08274D]"
            )}
          >
            Все типы
          </button>
          {typesSchema.map((t) => (
            <button
              key={t.code}
              onClick={() => setProductType(t.code)}
              className={cn(
                "px-3.5 py-1.5 rounded-lg text-[11px] md:text-[12px] font-heading font-semibold uppercase tracking-wider transition-all cursor-pointer",
                productType === t.code
                  ? "bg-[#08274D] text-white shadow-2xs"
                  : "bg-slate-100/80 text-slate-600 hover:bg-slate-200/80 hover:text-[#08274D]"
              )}
            >
              {t.name}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}