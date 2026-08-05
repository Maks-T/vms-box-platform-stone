import React from 'react';
import {H1} from '@/shared/components/ui/Typography';
import Badge from "@shared/components/ui/Badge";
import {checkDevMode} from '@/shared/lib/dev';
import {ProductVariant, EavAttribute, EavValueOption} from '@/types/catalog';

interface Props {
  name: string;
  displayPrice: number;
  activeVariant?: ProductVariant | null;
  attributes?: Record<string, EavAttribute>;
  unitName?: string;
  bootstrapConfig?: any;
  shortDescription?: string | null;
  description?: string | null;
}

export function ProductMainInfo({
                                  name,
                                  displayPrice,
                                  activeVariant,
                                  attributes,
                                  unitName = 'шт.',
                                  bootstrapConfig,
                                  shortDescription,
                                  description
                                }: Props) {
  const isDev = checkDevMode();
  const currencySymbol = bootstrapConfig?.base_currency?.symbol_native || bootstrapConfig?.base_currency?.symbol || 'Br';

  const formattedNumber = displayPrice > 0
    ? new Intl.NumberFormat('ru-RU', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }).format(displayPrice)
    : '';

  const stockCount = activeVariant ? activeVariant.stock : null;

  const brandAttr = attributes?.brand?.value;
  const brandObj = (typeof brandAttr === 'object' && brandAttr !== null && 'label' in brandAttr)
    ? (brandAttr as EavValueOption)
    : null;

  const cleanShortDesc = shortDescription?.startsWith('Бренд:')
    ? null
    : shortDescription;

  return (
    <div className="mb-4 border-b border-zinc-200 pb-5">
      <div className="flex items-center justify-between gap-4 mb-2">
        <span className="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
          {activeVariant?.sku ? `Код: ${activeVariant.sku}` : 'Товар'}
        </span>

        {stockCount !== null && (
          <span
            className={`text-xs font-semibold px-2.5 py-0.5 rounded ${stockCount > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'}`}>
            {stockCount > 0 ? `В наличии: ${stockCount} ${unitName}` : 'Под заказ'}
          </span>
        )}
      </div>

      <H1 className="!text-zinc-900 !text-2xl md:!text-3xl font-extrabold mb-4">
        {name}
      </H1>

      <div
        className="flex items-center justify-between gap-4 mb-4 bg-zinc-50/80 border border-zinc-200/80 p-1 rounded">
        {brandObj ? (
          <div className="flex items-center gap-2.5">
            {brandObj.meta?.image ? (
              <img src={brandObj.meta.image} alt={brandObj.label} className="h-7 md:h-12 max-w-[240px] object-contain"/>
            ) : (
              <span className="text-sm font-bold text-zinc-900">{brandObj.label}</span>
            )}
          </div>
        ) : (
          <span className="text-xs font-semibold text-zinc-400">Столешка.Ру</span>
        )}

        <div className="text-right">
          {displayPrice > 0 ? (
            <div className="text-2xl md:text-3xl font-extrabold text-[#B92B3A] leading-none flex items-baseline gap-1">
              <span>{formattedNumber}</span>
              <span className="text-xs md:text-sm font-medium text-zinc-500 lowercase">
                {currencySymbol} / {unitName}
              </span>
            </div>
          ) : (
            <Badge variant="gray"
                   className="!bg-zinc-100 !border-zinc-200 !text-zinc-500 !shadow-none !px-3 !py-1 text-xs">
              Цена по запросу
            </Badge>
          )}
        </div>
      </div>

      {cleanShortDesc && (
        <div className="text-xs sm:text-sm text-zinc-500 leading-relaxed max-w-2xl mb-3">
          {cleanShortDesc}
        </div>
      )}

      {description && (
        <div
          className="text-xs sm:text-sm text-zinc-600 leading-relaxed max-w-2xl border-t border-zinc-100 pt-3 prose prose-zinc"
          dangerouslySetInnerHTML={{__html: description}}
        />
      )}
    </div>
  );
}