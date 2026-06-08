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
        <div className="flex flex-wrap items-center gap-2 mt-2 pt-6 border-t border-border/50">
          <button
            onClick={() => setProductType('')}
            className={cn(
              "px-5 py-2 rounded-full text-[13px] font-medium transition-colors border",
              productType === ''
                ? "bg-primary/10 border-primary/20 text-primary"
                : "bg-transparent border-transparent text-muted-foreground hover:bg-muted hover:text-foreground"
            )}
          >
            Все типы
          </button>
          {typesSchema.map((t) => (
            <button
              key={t.code}
              onClick={() => setProductType(t.code)}
              className={cn(
                "px-5 py-2 rounded-full text-[13px] font-medium transition-colors border",
                productType === t.code
                  ? "bg-primary/10 border-primary/20 text-primary"
                  : "bg-transparent border-transparent text-muted-foreground hover:bg-muted hover:text-foreground"
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