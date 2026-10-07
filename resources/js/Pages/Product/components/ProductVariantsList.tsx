import React from 'react';
import {Image as ImageIcon} from 'lucide-react';
import {ProductVariant, BootstrapConfig} from '@/types/catalog';

interface Props {
  variants: ProductVariant[];
  bootstrapConfig?: BootstrapConfig | null;
}

export function ProductVariantsList({variants, bootstrapConfig}: Props) {
  if (!variants || variants.length === 0) return null;

  const defaultPriceType = bootstrapConfig?.price_types?.find((pt: any) => pt.is_default)?.slug || 'retail';
  const currencySymbol = '₽';

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
    <div className="space-y-4 pt-6 text-left">
      <div className="flex items-center justify-between border-b border-[#E5E5E5] pb-2 text-xs">
        <h3 className="font-heading font-bold text-sm text-[#212B36] uppercase tracking-wider">
          Доступные слэбы и размеры (SKU)
        </h3>
        <span className="text-gray-400">{variants.length} предложений</span>
      </div>

      <div className="flex flex-col gap-3">
        {variants.map((variant) => {
          // Сверяем имя с системным ключом 'code' (бывший 'sku')
          const hasFriendlyName = variant.name && variant.name !== variant.sku;

          return (
            <div key={variant.id}
                 className="border border-[#E5E5E5] rounded-md p-3.5 bg-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-2xs hover:border-gray-300 transition">
              <div className="flex items-center gap-4 overflow-hidden w-full">

                <div className="w-14 h-14 shrink-0 rounded-sm overflow-hidden p-0 border border-[#E5E5E5] bg-[#F8F8F8]">
                  {variant.preview_picture ? (
                    <img src={variant.preview_picture} alt={variant.sku} className="w-full h-full object-cover"/>
                  ) : (
                    <ImageIcon className="w-6 h-6 text-muted-foreground/40"/>
                  )}
                </div>

                <div className="min-w-0 flex-1">
                  {/* Если есть красивое имя (например, цвет), выводим его, иначе системный код */}
                  <div className="font-heading font-bold text-[#212B36] tracking-tight text-[14px]">
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
                    <div className="font-heading font-black text-[#212B36] text-[16px]">
                      {formattedNumber} {currencySymbol} / м²
                    </div>
                  ) : (
                    <span className="text-xs text-gray-500 font-medium">
                      По запросу
                    </span>
                  );
                })()}

                <span className="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                  <span className="w-1.5 h-1.5 rounded-full bg-emerald-500" />
                  {variant.stock > 0 ? `В наличии: ${variant.stock} слэбов` : 'Склад РФ (под заказ)'}
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
