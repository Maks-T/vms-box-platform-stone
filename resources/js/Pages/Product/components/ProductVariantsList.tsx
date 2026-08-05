import React from 'react';
import {Layers, Image as ImageIcon} from 'lucide-react';
import {ProductVariant, BootstrapConfig} from '@/types/catalog';
import {cn} from '@/shared/lib/utils';

interface Props {
  variants: ProductVariant[];
  activeVariant: ProductVariant | null;
  onSelectVariant: (variant: ProductVariant) => void;
  bootstrapConfig?: BootstrapConfig | null;
}

export function ProductVariantsList({variants, activeVariant, onSelectVariant, bootstrapConfig}: Props) {
  if (!variants || variants.length === 0) return null;

  const defaultPriceType = bootstrapConfig?.price_types?.find((pt: any) => pt.is_default)?.slug || 'retail';
  const currencySymbol = bootstrapConfig?.base_currency?.symbol_native || bootstrapConfig?.base_currency?.symbol || 'Br';

  return (
    <div className="mt-8 pt-6 border-t border-zinc-200">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-xs font-bold text-zinc-400 uppercase tracking-widest flex items-center gap-2">
          <Layers className="w-4 h-4 text-zinc-500"/>
          Варианты исполнения ({variants.length})
        </h3>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        {variants.map((variant) => {
          const isSelected = activeVariant?.id === variant.id;
          const displayPrice = variant.prices?.[defaultPriceType] || Object.values(variant.prices || {})[0] || 0;
          const formattedNumber = displayPrice > 0
            ? new Intl.NumberFormat('ru-RU', {minimumFractionDigits: 0, maximumFractionDigits: 2}).format(displayPrice)
            : '';

          const hasFriendlyName = variant.name && variant.name !== variant.sku;
          const title = hasFriendlyName ? variant.name : variant.sku;

          return (
            <div
              key={variant.id}
              onClick={() => onSelectVariant(variant)}
              className={cn(
                "group relative bg-white border rounded p-3 flex flex-col justify-between cursor-pointer transition-all duration-200",
                isSelected
                  ? "border-[#B92B3A] border-2 bg-red-50/20 shadow-xs"
                  : "border-zinc-200 hover:border-zinc-400 hover:bg-zinc-50/50"
              )}
            >
              <div>
                <div
                  className="relative aspect-square w-full bg-zinc-50 border border-zinc-100 rounded-sm overflow-hidden mb-2 flex items-center justify-center p-2">
                  {variant.preview_picture ? (
                    <img src={variant.preview_picture} alt={variant.sku} className="w-full h-full object-contain"/>
                  ) : (
                    <ImageIcon className="w-6 h-6 text-zinc-300"/>
                  )}
                </div>

                <div className="text-xs font-bold text-zinc-900 line-clamp-2 leading-tight mb-1">
                  {title}
                </div>

                <div className="flex flex-col gap-0.5 mb-2">
                  {Object.entries(variant.attributes || {}).map(([code, attr]) => {
                    if (!attr.value) return null;
                    const valLabel = typeof attr.value === 'object' && attr.value !== null && 'label' in attr.value
                      ? (attr.value as any).label
                      : String(attr.value);

                    return (
                      <div key={code} className="text-[10px] text-zinc-500 font-medium truncate">
                        <span className="text-zinc-400">{attr.name}:</span> {valLabel}
                      </div>
                    );
                  })}
                </div>
              </div>

              <div className="pt-2 border-t border-zinc-100 flex items-center justify-between gap-1 mt-auto">
                <div className="text-xs font-bold text-zinc-900">
                  {displayPrice > 0 ? `${formattedNumber} ${currencySymbol}` : 'По запросу'}
                </div>
                <span className={cn(
                  "text-[9px] font-bold px-1.5 py-0.5 rounded-sm uppercase tracking-wider",
                  variant.stock > 0 ? "bg-emerald-50 text-emerald-700 border border-emerald-200" : "bg-amber-50 text-amber-700 border border-amber-200"
                )}>
                  {variant.stock > 0 ? `${variant.stock} шт` : 'Заказ'}
                </span>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

export default ProductVariantsList;