import React from 'react';
import { cn } from '@/shared/lib/utils';
import { CatalogPills } from '@/features/catalog/components/CatalogPills';
import { BootstrapFamily } from '@/types/catalog';

interface Props {
  familiesList: BootstrapFamily[];
  activeFamily: string;
  setFamily: (family: string) => void;
  typesSchema: { code: string; name: string }[];
  productType: string;
  setProductType: (type: string) => void;
  isLoading?: boolean;
}

export function CatalogNavigationBlock({
                                         familiesList, activeFamily, setFamily, typesSchema, productType, setProductType, isLoading
                                       }: Props) {
  return (
    <div className="flex flex-col w-full mb-4 relative z-10 pt-1">
      <CatalogPills
        families={familiesList}
        activeFamily={activeFamily}
        onChange={setFamily}
        isLoading={isLoading}
      />

      {typesSchema.length > 0 && (
        <div className="flex flex-wrap items-center gap-2 mt-2 pt-3 border-t border-border/50">
          <button
            onClick={() => setProductType('')}
            className={cn(
              "px-4 py-1.5 rounded text-xs font-semibold transition-colors border",
              productType === ''
                ? "bg-zinc-900 border-zinc-900 text-white"
                : "bg-white border-border text-zinc-600 hover:border-zinc-400 hover:text-zinc-900"
            )}
          >
            Все типы
          </button>
          {typesSchema.map((t) => (
            <button
              key={t.code}
              onClick={() => setProductType(t.code)}
              className={cn(
                "px-4 py-1.5 rounded text-xs font-semibold transition-colors border",
                productType === t.code
                  ? "bg-zinc-900 border-zinc-900 text-white"
                  : "bg-white border-border text-zinc-600 hover:border-zinc-400 hover:text-zinc-900"
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