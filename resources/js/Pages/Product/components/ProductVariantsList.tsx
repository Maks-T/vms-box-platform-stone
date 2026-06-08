import React from 'react';
import {Layers, Image as ImageIcon} from 'lucide-react';
import {H3} from '@/shared/components/ui/Typography';
import {IconBox} from '@/shared/components/ui/IconBox';
import StatusBadge from '@/shared/components/ui/StatusBadge';
import GlassPanel from '@/shared/components/ui/GlassPanel';
import {ProductVariant} from '@/types/catalog';
import Badge from "@/shared/components/ui/Badge";

interface Props {
  variants: ProductVariant[];
}

export function ProductVariantsList({variants}: Props) {
  if (!variants || variants.length === 0) return null;

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
          <span className="truncate">{data.name}</span>
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
        {variants.map((variant) => (
          <GlassPanel key={variant.id} variant="default" padding="sm"
                      className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-muted/30">
            <div className="flex items-center gap-4 overflow-hidden">

              <IconBox variant="light" className="w-14 h-14 shrink-0 rounded-xl overflow-hidden p-0 border-border">

                {variant.preview_picture ? (
                  <img src={variant.preview_picture} alt={variant.sku} className="w-full h-full object-cover"/>
                ) : (
                  <ImageIcon className="w-6 h-6 text-muted-foreground/40"/>
                )}
              </IconBox>

              <div className="min-w-0">
                <div className="font-bold text-foreground tracking-tight truncate text-[15px]">{variant.sku}</div>
                {variant.attributes?.color?.value && (
                  <div className="text-[13px] text-muted-foreground mt-1.5 flex items-center gap-1 truncate">
                    {renderAttributeValue(variant.attributes.color.value)}
                  </div>
                )}
              </div>

            </div>

            {/* Блок цены и остатка */}
            <div className="flex flex-col items-start sm:items-end gap-2 w-full sm:w-auto border-t sm:border-0 border-border pt-4 sm:pt-0 shrink-0">

              {(() => {
                const displayPrice = variant.prices?.retail || Object.values(variant.prices || {})[0] || 0;

                return displayPrice > 0 ? (
                  <div className="font-black text-foreground text-[18px]">
                    {new Intl.NumberFormat('ru-RU', {
                      style: 'currency',
                      currency: 'RUB',
                      minimumFractionDigits: 0
                    }).format(displayPrice)}
                  </div>
                ) : (
                  <Badge variant="gray" className="!bg-background !border-border !text-muted-foreground !shadow-none !px-2.5 !py-1 text-[11px] uppercase tracking-wider">
                    По запросу
                  </Badge>
                );
              })()}

              <StatusBadge variant={variant.stock > 0 ? 'success' : 'warning'} className="px-2.5 py-1">
                {variant.stock > 0 ? `Остаток: ${variant.stock} шт` : 'Под заказ'}
              </StatusBadge>

            </div>

          </GlassPanel>
        ))}
      </div>
    </div>
  );
}
