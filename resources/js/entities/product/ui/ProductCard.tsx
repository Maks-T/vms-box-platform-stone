import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Image as ImageIcon, ArrowUpRight } from 'lucide-react';
import { StoneProduct, EavValueOption, BootstrapConfig, ProductVariant } from '@/types/catalog';
import { route } from 'ziggy-js';
import Badge from '@/shared/components/ui/Badge';
import { cn } from '@/shared/lib/utils';
import { FavoriteButton } from '@/shared/components/ui/FavoriteButton';

interface ProductCardProps {
  product: StoneProduct;
  bootstrapConfig?: BootstrapConfig | null;
}

export const ProductCard = ({ product, bootstrapConfig }: ProductCardProps) => {
  const { id, name, slug, price_from, preview_picture, unit, attributes, variants } = product;

  const [activeVariant, setActiveVariant] = useState<ProductVariant | null>(null);

  const defaultPriceType = bootstrapConfig?.price_types?.find((pt: any) => pt.is_default)?.slug || 'retail';

  const displayImage = activeVariant?.preview_picture || preview_picture;

  const displayPrice = activeVariant
    ? (activeVariant.prices?.[defaultPriceType] || Object.values(activeVariant.prices || {})[0] || price_from)
    : price_from;

  const currencySymbol = bootstrapConfig?.base_currency?.symbol_native || bootstrapConfig?.base_currency?.symbol || 'Br';

  const formattedNumber = displayPrice > 0
    ? new Intl.NumberFormat('ru-RU', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }).format(displayPrice)
    : '';

  const collection = attributes?.collection?.value as EavValueOption | undefined;
  const brand = attributes?.brand?.value as EavValueOption | undefined;
  const serviceTags = attributes?.service_tags?.value as EavValueOption[] | undefined;

  let subtitle = 'Каталог камня';
  if (brand) subtitle = brand.label;
  else if (collection) subtitle = collection.label;
  else if (serviceTags && Array.isArray(serviceTags) && serviceTags.length > 0) {
    subtitle = serviceTags.map(t => t.label).join(', ');
  } else if (unit) subtitle = `Ед. изм: ${unit.name}`;

  const parentColor = attributes?.color?.value as EavValueOption | undefined;
  const variantColors: EavValueOption[] = [];

  if (variants?.length > 0) {
    const seen = new Set();
    variants.forEach(v => {
      const vColor = v.attributes?.color?.value as EavValueOption | undefined;
      if (vColor && !seen.has(vColor.key)) {
        seen.add(vColor.key);
        variantColors.push(vColor);
      }
    });
  }

  const colorsToShow = variantColors.length > 0 ? variantColors : (parentColor ? [parentColor] : []);

  const activeColorSlug = activeVariant
    ? (activeVariant.attributes?.color?.value as EavValueOption | undefined)?.key
    : (variants?.find(v => v.is_default)?.attributes?.color?.value as EavValueOption | undefined)?.key;

  const handleColorClick = (e: React.MouseEvent, color: EavValueOption) => {
    e.preventDefault();
    e.stopPropagation();

    const match = variants?.find(v => {
      const vColor = v.attributes?.color?.value as EavValueOption | undefined;
      return vColor?.key === color.key;
    });

    if (match) {
      setActiveVariant(match);
    }
  };

  const renderSwatch = (color: EavValueOption) => {
    const isSelected = color.key === activeColorSlug;

    const swatchClasses = cn(
      "w-5 h-5 rounded-full object-cover border border-slate-200/80 shadow-2xs cursor-pointer transition-all duration-200",
      isSelected
        ? "ring-2 ring-[#08274D] ring-offset-1 scale-105 opacity-100"
        : "opacity-75 hover:opacity-100 hover:scale-105"
    );

    if (color.meta?.image) {
      return (
        <img
          key={color.key}
          src={color.meta.image}
          title={color.label}
          alt={color.label}
          onClick={(e) => handleColorClick(e, color)}
          className={swatchClasses}
        />
      );
    }
    if (color.meta?.hex) {
      return (
        <div
          key={color.key}
          title={color.label}
          onClick={(e) => handleColorClick(e, color)}
          className={swatchClasses}
          style={{ backgroundColor: color.meta.hex }}
        />
      );
    }
    return null;
  };

  return (
    <div className="group flex flex-col h-full bg-white rounded-xl overflow-hidden border border-slate-200/90 hover:border-[#08274D] hover:shadow-xs transition-all duration-200">

      {/* Изображение слэба / текстуры */}
      <div className="relative aspect-square bg-slate-50/50 overflow-hidden border-b border-slate-100">
        <Link href={route('product.show', slug)} className="block w-full h-full p-6">
          {displayImage ? (
            <img
              src={displayImage}
              alt={name}
              className="w-full h-full object-contain mix-blend-multiply transition-transform duration-500 group-hover:scale-105"
            />
          ) : (
            <div className="flex items-center justify-center w-full h-full opacity-20 text-slate-400">
              <ImageIcon className="w-16 h-16" />
            </div>
          )}
        </Link>

        {/* Кнопка «В избранное» с лаконичным скруглением */}
        <FavoriteButton
          product={product}
          className="absolute top-3 right-3 bg-white/95 p-1.5 rounded-md border border-slate-200/70 shadow-2xs hover:bg-white transition-colors"
          iconClassName="w-4 h-4"
        />
      </div>

      {/* Описание и характеристики */}
      <div className="flex flex-col flex-1 p-5">
        <p className="text-[10px] font-heading font-bold text-[#9B6A38] uppercase tracking-[0.14em] mb-1 line-clamp-1">
          {subtitle}
        </p>

        <Link href={route('product.show', slug)} className="block mb-3">
          <h3 className="font-heading font-bold text-[15px] text-[#08274D] leading-snug tracking-tight group-hover:text-[#EF5042] transition-colors line-clamp-2">
            {name}
          </h3>
        </Link>

        {/* Свотчи оттенков и фактур */}
        {colorsToShow.length > 0 && (
          <div className="flex items-center gap-1.5 mb-4 flex-wrap">
            {colorsToShow.length === 1 ? (
              <div className="flex items-center gap-2">
                {renderSwatch(colorsToShow[0])}
                <span className="text-xs text-slate-500 truncate">{colorsToShow[0].label}</span>
              </div>
            ) : (
              <>
                {colorsToShow.slice(0, 6).map(c => renderSwatch(c))}
                {colorsToShow.length > 6 && (
                  <span className="text-[11px] font-medium text-slate-400 ml-1">
                    +{colorsToShow.length - 6}
                  </span>
                )}
              </>
            )}
          </div>
        )}

        {/* Стоимость и интерактивный переход */}
        <div className="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
          <div className="flex flex-col">
            <span className="text-[10px] font-heading uppercase text-slate-400 font-semibold tracking-wider">
              Цена за м²
            </span>
            <div className="font-heading text-[18px] md:text-[20px] font-black text-[#08274D] flex items-baseline gap-1">
              {displayPrice > 0 ? (
                <>
                  <span>{formattedNumber}</span>
                  <span className="text-[11px] font-normal text-slate-500 lowercase">
                    {currencySymbol}
                  </span>
                </>
              ) : (
                <span className="text-xs font-heading font-semibold text-slate-500 uppercase tracking-wide">
                  По запросу
                </span>
              )}
            </div>
          </div>

          <Link
            href={route('product.show', slug)}
            aria-label={`Подробнее о ${name}`}
            className="w-8 h-8 rounded-lg bg-slate-100/90 group-hover:bg-[#08274D] text-[#08274D] group-hover:text-white flex items-center justify-center transition-all duration-150"
          >
            <ArrowUpRight className="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
          </Link>
        </div>
      </div>

    </div>
  );
};

export default ProductCard;