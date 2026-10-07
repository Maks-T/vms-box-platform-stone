import React, { useState } from 'react';
import { Loader2, Layers, Grid3X3, Grid2X2, List, Table } from 'lucide-react';
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
  const [viewMode, setViewMode] = useState<'grid' | 'large' | 'list'>('grid');

  return (
    <div className="relative min-h-[500px] flex flex-col">
      {/* Панель сортировки и переключения вида Aspro */}
      <div className="border border-[#E5E5E5] rounded-md bg-white px-5 py-3 flex items-center justify-between mb-6 shadow-2xs">
        <div className="flex items-center gap-2 text-xs sm:text-sm font-medium text-[#212B36]">
          <span>Сортировка:</span>
          <span className="text-[#148587] font-semibold cursor-pointer hover:underline">По наименованию (А-Я)</span>
        </div>

        {/* Кнопки режимов отображения */}
        <div className="flex items-center gap-3 text-gray-400">
          <button
            type="button"
            onClick={() => setViewMode('grid')}
            className={cn("hover:text-[#212B36] transition-colors cursor-pointer", viewMode === 'grid' && "text-[#212B36]")}
            title="Плитка"
          >
            <Grid3X3 className="w-4 h-4" />
          </button>
          <button
            type="button"
            onClick={() => setViewMode('large')}
            className={cn("hover:text-[#212B36] transition-colors cursor-pointer", viewMode === 'large' && "text-[#212B36]")}
            title="Крупная плитка"
          >
            <Grid2X2 className="w-4 h-4" />
          </button>
          <div className="w-px h-3.5 bg-[#E5E5E5]" />
          <span className="text-xs text-gray-500 font-semibold">{meta?.total || products.length} товаров</span>
        </div>
      </div>

      <div className="relative flex-1">
        {isLoading && (
          <div className="absolute inset-0 z-30 flex flex-col items-center justify-start pt-32 bg-white/60 transition-all duration-300">
            <div className="flex flex-col items-center gap-4">
              <Loader2 className="w-12 h-12 text-[#25CED1] animate-spin stroke-[2.5px]" />
              <span className="text-[#212B36] text-xs font-bold uppercase tracking-[0.2em] animate-pulse">
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
              <div className={cn(
                "grid gap-5 mb-10",
                viewMode === 'large' ? "grid-cols-1 sm:grid-cols-2 md:grid-cols-3" : "grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4"
              )}>
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
            <div className="py-24 flex flex-col items-center justify-center bg-[#F8F8F8] rounded-md border border-dashed border-[#E5E5E5] shadow-xs">
              <div className="w-16 h-16 bg-white rounded-md flex items-center justify-center mb-4 border border-[#E5E5E5]">
                <Layers className="w-8 h-8 text-gray-400" />
              </div>
              <p className="text-lg text-[#212B36] font-bold mb-2">Ничего не найдено</p>
              <button onClick={clearFilters} className="bg-[#ED1C24] hover:bg-[#212B36] text-white px-6 py-2.5 rounded-md text-xs uppercase font-bold tracking-wider transition-colors">
                Сбросить фильтры
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
