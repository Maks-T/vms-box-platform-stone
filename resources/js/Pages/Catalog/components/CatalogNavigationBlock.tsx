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
        <div className="flex flex-wrap items-center gap-2 mt-2 pt-3 border-t border-[#E5E5E5]">
          <button
            onClick={() => setProductType('')}
            className={cn(
              "px-3 py-1 rounded-md text-[11px] font-heading font-semibold uppercase tracking-wider transition-all cursor-pointer",
              productType === ''
                ? "bg-[#2D3A49] text-white shadow-2xs"
                : "bg-[#F8F8F8] text-gray-700 hover:bg-gray-200 hover:text-[#212B36] border border-[#E5E5E5]"
            )}
          >
            Все типы
          </button>
          {typesSchema.map((t) => (
            <button
              key={t.code}
              onClick={() => setProductType(t.code)}
              className={cn(
                "px-3 py-1 rounded-md text-[11px] font-heading font-semibold uppercase tracking-wider transition-all cursor-pointer",
                productType === t.code
                  ? "bg-[#2D3A49] text-white shadow-2xs"
                  : "bg-[#F8F8F8] text-gray-700 hover:bg-gray-200 hover:text-[#212B36] border border-[#E5E5E5]"
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