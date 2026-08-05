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
      <div className="flex items-center justify-between mb-6 border-b border-border pb-4">
        <h2 className="text-xl font-bold text-zinc-900 tracking-tight">Результаты</h2>
        <span className="text-xs font-semibold text-zinc-500 bg-zinc-100 px-3 py-1 rounded">
          {meta?.total || products.length} товаров
        </span>
      </div>

      <div className="relative flex-1">
        {isLoading && (
          <div className="absolute inset-0 z-30 flex flex-col items-center justify-start pt-32 bg-white/70 backdrop-blur-[2px] transition-all duration-300">
            <div className="flex flex-col items-center gap-2.5">
              <Loader2 className="w-7 h-7 text-zinc-900 animate-spin stroke-[1.8]" />
              <span className="text-zinc-500 text-[10px] font-bold uppercase tracking-[0.2em] animate-pulse">
                Загрузка...
              </span>
            </div>
          </div>
        )}

        <div className={cn(
          "transition-all duration-300",
          isLoading ? "opacity-20 pointer-events-none" : "opacity-100"
        )}>
          {products.length > 0 ? (
            <>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-6">
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
            <div className="py-20 flex flex-col items-center justify-center bg-white rounded border border-dashed border-border">
              <div className="w-12 h-12 bg-zinc-100 rounded flex items-center justify-center mb-3">
                <Layers className="w-6 h-6 text-zinc-400" />
              </div>
              <p className="text-sm text-zinc-900 font-semibold mb-3">Ничего не найдено</p>
              <button
                onClick={clearFilters}
                className="bg-zinc-900 text-white hover:bg-zinc-800 px-4 py-2 rounded text-xs font-semibold uppercase tracking-wider transition-colors"
              >
                Сбросить фильтры
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}