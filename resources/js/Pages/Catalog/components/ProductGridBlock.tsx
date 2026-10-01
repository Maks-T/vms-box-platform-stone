import React from 'react';
import { Loader2, Layers } from 'lucide-react';
import { ProductCard } from '@/entities/product/ui/ProductCard';
import { BasePagination } from '@/shared/components/ui/BasePagination';
import { StoneProduct, BootstrapConfig } from '@/types/catalog';
import { cn } from '@/shared/lib/utils';

interface Props {
  isLoading: boolean;
  products: StoneProduct[];
  meta: any;
  setPage: (page: number) => void;
  clearFilters: () => void;
  bootstrapConfig: BootstrapConfig | null; 
}

export function ProductGridBlock({ isLoading, products, meta, setPage, clearFilters, bootstrapConfig }: Props) {
  return (
    <div className="relative min-h-[500px] flex flex-col">
      <div className="flex items-center justify-between mb-6 border-b border-slate-200/70 pb-4">
        <div className="flex items-baseline gap-3">
          <h2 className="font-heading font-bold text-[18px] md:text-[22px] text-[#08274D] tracking-tight">Материалы</h2>
          <span className="text-xs text-slate-400 font-medium hidden sm:inline">• в наличии и под заказ</span>
        </div>
        <span className="text-[12px] font-heading font-bold text-slate-500 uppercase tracking-wider">
          {meta?.total || products.length} позиций
        </span>
      </div>

      <div className="relative flex-1">
        {isLoading && (
          <div className="absolute inset-0 z-30 flex flex-col items-center justify-start pt-32 bg-white/60 transition-all duration-300">
            <div className="flex flex-col items-center gap-4">
              <Loader2 className="w-12 h-12 text-sky-500 animate-spin stroke-[2.5px]" />
              <span className="text-sky-600/60 text-xs font-bold uppercase tracking-[0.2em] animate-pulse">
                 Загрузка...
               </span>
            </div>
          </div>
        )}

        <div className={cn(
          "transition-opacity duration-300",
          isLoading ? "opacity-30 scale-[0.99] grayscale-[0.5]" : "opacity-100 scale-100"
        )}>
          {products.length > 0 ? (
            <>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-8 md:gap-x-6 md:gap-y-10">
                {products.map((product) => (
                  <ProductCard
                    key={product.id}
                    product={product}
                    bootstrapConfig={bootstrapConfig} 
                  />
                ))}
              </div>
              <BasePagination meta={meta} onPageChange={setPage} />
            </>
          ) : !isLoading && (
            <div className="py-24 flex flex-col items-center justify-center bg-muted/30 rounded-3xl border border-dashed border-border shadow-sm">
              <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center mb-4">
                <Layers className="w-8 h-8 text-muted-foreground" />
              </div>
              <p className="text-lg text-foreground font-medium mb-2">Ничего не найдено</p>
              <button onClick={clearFilters} className="bg-primary text-primary-foreground hover:bg-primary/90 px-6 py-2.5 rounded-lg text-sm font-medium transition-colors">
                Сбросить фильтры
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
