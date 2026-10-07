import React from 'react';
import { Link } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { Check } from 'lucide-react';
import { StoneProduct, ProductVariant, EavValueOption } from '@/types/catalog';
import { cn } from '@/shared/lib/utils';

interface Props {
  product: StoneProduct;
  activeVariant: ProductVariant | null;
  availableColors: { option: EavValueOption; variant?: ProductVariant }[];
  onSelectColor: (item: { option: EavValueOption; variant?: ProductVariant }) => void;
  bootstrapConfig?: any;
}

export function ProductMainInfo({
                                  product,
                                  activeVariant,
                                  availableColors,
                                  onSelectColor,
                                  bootstrapConfig,
                                }: Props) {
  const { name, price_from, unit, attributes } = product;

  const defaultPriceType = bootstrapConfig?.price_types?.find((pt: any) => pt.is_default)?.slug || 'retail';
  const displayPrice = activeVariant
    ? (activeVariant.prices?.[defaultPriceType] || Object.values(activeVariant.prices || {})[0] || price_from)
    : price_from;

  const currencySymbol = '₽';
  const unitLabel = unit?.symbol || unit?.name || 'м²';

  const formattedNumber = displayPrice > 0
    ? new Intl.NumberFormat('ru-RU', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(displayPrice)
    : '';

  // Определение названия текущего цвета
  const activeColorKey = (activeVariant?.attributes?.color?.value as EavValueOption)?.key;
  const activeColorLabel = (activeVariant?.attributes?.color?.value as EavValueOption)?.label
    || availableColors.find((c) => c.option.key === activeColorKey)?.option.label
    || (availableColors.length === 1 ? availableColors[0].option.label : '');

  // Определение бренда
  const brandOption = attributes?.brand?.value as EavValueOption | undefined;
  const brandName = brandOption?.label || (typeof attributes?.brand?.value === 'string' ? attributes.brand.value : null);
  const isAvarus = brandName?.toLowerCase().includes('аварус') || name.toLowerCase().includes('аварус');

  // Краткие характеристики из атрибутов
  const materialName = (attributes?.material?.value as EavValueOption)?.label || (attributes?.material?.value as string) || 'Кварцевый агломерат';
  const countryName = (attributes?.country?.value as EavValueOption)?.label || (attributes?.country?.value as string) || 'Россия';
  const textureName = (attributes?.texture?.value as EavValueOption)?.label || (attributes?.finish?.value as string) || 'Глянцевая';
  const thicknessName = (attributes?.thickness?.value as string) || (attributes?.thickness_mm?.value as string) || '20 мм';

  return (
    <div className="space-y-6 text-left">
      {/* Заголовок, статус наличия и логотип бренда */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5E5] pb-5">
        <div>
          <h1 className="font-heading font-extrabold text-2xl sm:text-3xl lg:text-4xl text-[#212B36] tracking-tight leading-tight mb-2">
            {name}
          </h1>
          <div className="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
            <span className="w-2 h-2 rounded-full bg-emerald-500" />
            Склад РФ
          </div>
        </div>

        {/* Логотип бренда (Аварус либо бейдж бренда) */}
        <div className="shrink-0">
          {isAvarus ? (
            <img
              src="https://quartz-master.com/upload/resize_cache/iblock/969/200_40_1/pmbrh2seusc690cvgcabh0herm77lb23.png"
              alt="Аварус"
              className="h-[34px] w-auto object-contain"
            />
          ) : brandName ? (
            <span className="px-3 py-1.5 rounded-md bg-[#2D3A49] text-white font-heading font-bold text-xs uppercase tracking-wider">
              {brandName}
            </span>
          ) : null}
        </div>
      </div>

      {/* Блок выбора доступных цветов */}
      {availableColors.length > 0 && (
        <div className="space-y-2.5 pb-2 border-b border-[#E5E5E5]">
          <div className="flex items-center justify-between text-xs">
            <span className="font-heading font-bold uppercase tracking-wider text-[#212B36]">
              Цвет: <span className="font-medium text-slate-600 normal-case ml-1">{activeColorLabel || 'Все доступные цвета'}</span>
            </span>
            <span className="text-gray-400 text-[11px]">{availableColors.length} оттенков</span>
          </div>

          <div className="flex flex-wrap gap-2.5 items-center">
            {availableColors.map((item) => {
              const isSelected = activeColorKey
                ? item.option.key === activeColorKey
                : (activeVariant?.id === item.variant?.id);
              const hex = item.option.meta?.hex || '#CBD5E1';
              const img = item.option.meta?.image;

              return (
                <button
                  key={item.option.key}
                  type="button"
                  onClick={() => onSelectColor(item)}
                  title={item.option.label}
                  className={cn(
                    "relative size-8 rounded-sm transition-all cursor-pointer flex items-center justify-center border",
                    isSelected
                      ? "border-[#212B36] ring-2 ring-[#212B36] ring-offset-1 scale-105 shadow-xs"
                      : "border-gray-200 hover:border-gray-400 hover:scale-105 opacity-85 hover:opacity-100"
                  )}
                  style={{ backgroundColor: img ? undefined : hex }}
                >
                  {img && (
                    <img
                      src={img}
                      alt={item.option.label}
                      className="size-full object-cover rounded-xs"
                    />
                  )}
                  {isSelected && (
                    <div className="absolute inset-0 bg-black/25 rounded-xs flex items-center justify-center">
                      <Check className="size-3.5 text-white stroke-[3px]" />
                    </div>
                  )}
                </button>
              );
            })}
          </div>
        </div>
      )}

      {/* Блок заказа и кратких характеристик */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start pt-2">
        {/* Кнопка заказа расчета и цена */}
        <div className="space-y-4">
          <div>
            <span className="text-[11px] font-heading font-bold uppercase tracking-wider text-gray-400 block mb-1">
              Базовая цена материала
            </span>
            <div className="font-heading font-extrabold text-2xl sm:text-3xl text-[#212B36] flex items-baseline gap-1.5">
              <span>{displayPrice > 0 ? `${formattedNumber} ${currencySymbol}` : 'По запросу'}</span>
              <span className="text-sm font-normal text-gray-500">/ {unitLabel}</span>
            </div>
          </div>

          <Link
            href={route('calculator.show')}
            className="w-full py-4 px-6 bg-[#ED1C24] hover:bg-[#212B36] text-white font-heading font-bold text-xs uppercase tracking-wider rounded-md transition-all duration-200 shadow-md block text-center cursor-pointer"
          >
            Заказать расчет
          </Link>

          <p className="text-xs text-gray-400 leading-relaxed">
            Внимание! Цвет и текстура на экране могут незначительно отличаться от реального образца камня в шоуруме.
          </p>
        </div>

        {/* Краткий список характеристик справа с пунктиром */}
        <div className="border-l border-[#E5E5E5] pl-6 space-y-3">
          <h3 className="font-heading font-bold text-sm text-[#212B36] uppercase tracking-wide">
            Характеристики
          </h3>
          <dl className="space-y-2 text-xs text-gray-600">
            <div className="flex items-baseline gap-2">
              <dt className="text-gray-400 shrink-0">Материал —</dt>
              <dd className="font-medium text-[#212B36]">{materialName}</dd>
            </div>
            <div className="flex items-baseline gap-2">
              <dt className="text-gray-400 shrink-0">Страна —</dt>
              <dd className="font-medium text-[#212B36]">{countryName}</dd>
            </div>
            <div className="flex items-baseline gap-2">
              <dt className="text-gray-400 shrink-0">Фактура —</dt>
              <dd className="font-medium text-[#212B36]">{textureName}</dd>
            </div>
            <div className="flex items-baseline gap-2">
              <dt className="text-gray-400 shrink-0">Толщина —</dt>
              <dd className="font-medium text-[#212B36]">{thicknessName}</dd>
            </div>
          </dl>
          <div className="pt-2">
            <a href="#characteristics" className="text-xs text-[#212B36] hover:text-[#25CED1] border-b border-dashed border-gray-400 transition-colors font-medium">
              Все характеристики ↓
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}

export default ProductMainInfo;