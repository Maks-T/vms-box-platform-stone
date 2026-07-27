import React from 'react';
import {Layers, Image as ImageIcon} from 'lucide-react';
import {H3} from '@/shared/components/ui/Typography';
import {IconBox} from '@/shared/components/ui/IconBox';
import StatusBadge from '@/shared/components/ui/StatusBadge';
import GlassPanel from '@/shared/components/ui/GlassPanel';
import {ProductVariant, BootstrapConfig} from '@/types/catalog';
import Badge from "@/shared/components/ui/Badge";

interface Props {
  variants: ProductVariant[];
  bootstrapConfig?: BootstrapConfig | null;
}

export function ProductVariantsList({variants, bootstrapConfig}: Props) {
  if (!variants || variants.length === 0) return null;

  const defaultPriceType = bootstrapConfig?.price_types?.find((pt: any) => pt.is_default)?.slug || 'retail';
  const currencySymbol = bootstrapConfig?.base_currency?.symbol_native || bootstrapConfig?.base_currency?.symbol || 'Br';

  const renderAttributeValue = (data: any) => {
    if (typeof data === 'object' && data !== null) {
      return (
        <div className="flex items-center gap-2">
          {data.meta?.hex && (
            <div className="w-3.5 h-3.5 rounded-full border border-border shrink-0"
                 style={{backgroundColor: data.meta.hex}}/>
          )}
          {data.meta?.image && (
            <img src={data.meta.image} alt=""
                 className="w-4 h-4 rounded-full object-cover border border-border shrink-0"/>
          )}
          <span className="truncate">{data.label}</span>
        </div>
      );
    }
    return <span>{data}</span>;
  };

  return (
    <div className="mt-12 pt-8 border-t border-border">
      <div className="flex items-center gap-3 mb-6">
        <IconBox variant="glass" size="sm" className="bg-muted text-muted-foreground border-transparent">
          <Layers className="w-4 h-4"/>
        </IconBox>
        <H3 className="!text-muted-foreground !text-[13px] uppercase tracking-[0.15em] m-0">
          Торговые предложения (SKU)
        </H3>
      </div>

      <div className="flex flex-col gap-3">
        {variants.map((variant) => {
          // Сверяем имя с системным ключом 'code' (бывший 'sku')
          const hasFriendlyName = variant.name && variant.name !== variant.sku;

          return (
            <GlassPanel key={variant.id} variant="default" padding="sm"
                        className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-muted/30">
              <div className="flex items-center gap-4 overflow-hidden w-full">

                <IconBox variant="light" className="w-14 h-14 shrink-0 rounded-xl overflow-hidden p-0 border-border">
                  {variant.preview_picture ? (
                    <img src={variant.preview_picture} alt={variant.sku} className="w-full h-full object-cover"/>
                  ) : (
                    <ImageIcon className="w-6 h-6 text-muted-foreground/40"/>
                  )}
                </IconBox>

                <div className="min-w-0 flex-1">
                  {/* Если есть красивое имя (например, цвет), выводим его, иначе системный код */}
                  <div className="font-bold text-foreground tracking-tight text-[15px]">
                    {hasFriendlyName ? variant.name : variant.sku}
                  </div>

                  {/* Если вывели красивое имя, то ниже показываем системный код */}
                  {hasFriendlyName && (
                    <div className="text-[11px] font-mono text-muted-foreground/75 mt-0.5 lowercase">
                      Код: {variant.sku}
                    </div>
                  )}

                  <div className="flex flex-col gap-1 mt-1.5">
                    {Object.entries(variant.attributes || {}).map(([code, attr]) => {
                      if (attr.value === null || attr.value === undefined || attr.value === '') return null;

                      return (
                        <div key={code}
                             className="text-[13px] text-muted-foreground flex items-center gap-1.5 truncate">
                          <span className="font-semibold text-slate-500/70">{attr.name}:</span>
                          <span className="text-slate-800">{renderAttributeValue(attr.value)}</span>
                        </div>
                      );
                    })}
                  </div>
                </div>

              </div>

              <div
                className="flex flex-col items-start sm:items-end gap-2 w-full sm:w-auto border-t sm:border-0 border-border pt-4 sm:pt-0 shrink-0">

                {(() => {
                  const displayPrice = variant.prices?.[defaultPriceType] || Object.values(variant.prices || {})[0] || 0;
                  const formattedNumber = displayPrice > 0
                    ? new Intl.NumberFormat('ru-RU', {
                      minimumFractionDigits: 0,
                      maximumFractionDigits: 2
                    }).format(displayPrice)
                    : '';

                  return displayPrice > 0 ? (
                    <div className="font-black text-foreground text-[18px] flex items-baseline gap-1">
                      <span>{formattedNumber}</span>
                      <span className="text-xs font-normal text-muted-foreground lowercase">{currencySymbol}</span>
                    </div>
                  ) : (
                    <Badge variant="gray"
                           className="!bg-background !border-border !text-muted-foreground !shadow-none !px-2.5 !py-1 text-[11px] uppercase tracking-wider">
                      По запросу
                    </Badge>
                  );
                })()}

                <StatusBadge variant={variant.stock > 0 ? 'success' : 'warning'} className="px-2.5 py-1">
                  {variant.stock > 0 ? `Остаток: ${variant.stock} шт` : 'Под заказ'}
                </StatusBadge>

              </div>

            </GlassPanel>
          );
        })}
      </div>
    </div>
  );
}

export default ProductVariantsList;
