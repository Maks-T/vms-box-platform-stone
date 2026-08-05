import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Image as ImageIcon } from 'lucide-react';
import { StoneProduct, EavValueOption, BootstrapConfig, ProductVariant } from '@/types/catalog';
import { route } from "ziggy-js";
import Badge from '@/shared/components/ui/Badge';
import { cn } from '@/shared/lib/utils';
import { FavoriteButton } from '@/shared/components/ui/FavoriteButton';

interface ProductCardProps {
  product: StoneProduct;
  bootstrapConfig?: BootstrapConfig | null;
}

export const ProductCard = ({ product, bootstrapConfig }: ProductCardProps) => {
  const { id, name, slug, price_from, preview_picture, detail_picture, attributes, variants, unit } = product;

  const [activeVariant, setActiveVariant] = useState<ProductVariant | null>(null);

  const defaultPriceType = bootstrapConfig?.price_types?.find((pt: any) => pt.is_default)?.slug || 'retail';

  const defaultVariant = variants?.find(v => v.is_default) || variants?.[0];

  const displayImage = activeVariant?.preview_picture
    || activeVariant?.detail_picture
    || preview_picture
    || defaultVariant?.preview_picture
    || defaultVariant?.detail_picture
    || detail_picture;

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

  const brand = attributes?.brand?.value as EavValueOption | undefined;
  const collection = attributes?.collection?.value as EavValueOption | undefined;
  const serviceTags = attributes?.service_tags?.value as EavValueOption[] | undefined;

  let subtitle = '';
  if (brand?.label) {
    subtitle = brand.label;
  } else if (collection?.label) {
    subtitle = collection.label;
  } else if (serviceTags && Array.isArray(serviceTags) && serviceTags.length > 0) {
    subtitle = serviceTags.map(t => t.label).join(', ');
  } else if (unit?.name) {
    subtitle = unit.name;
  } else {
    subtitle = 'Каталог';
  }

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
      "w-4 h-4 rounded-sm object-cover border border-slate-200 shadow-sm cursor-pointer transition-all duration-200",
      isSelected
        ? "ring-1 ring-zinc-900 ring-offset-1 scale-105 opacity-100"
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
    <div className="group flex flex-col h-full bg-white rounded border border-border hover:border-zinc-400 transition-all duration-200 overflow-hidden">
      <div className="relative aspect-[4/3] bg-zinc-50 overflow-hidden mb-3 border-b border-border">
        <Link href={route('product.show', slug)} className="block w-full h-full p-4">
          {displayImage ? (
            <img
              src={displayImage}
              alt={name}
              className="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
            />
          ) : (
            <div className="flex flex-col items-center justify-center w-full h-full opacity-20 text-zinc-400">
              <ImageIcon className="w-10 h-10" />
            </div>
          )}
        </Link>
        <div className="absolute top-3 left-3 bg-zinc-900 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-sm uppercase tracking-wider">
          ID {id}
        </div>
        <FavoriteButton product={product} className="absolute top-3 right-3 text-zinc-400 hover:text-red-600" />
      </div>

      <div className="flex flex-col flex-1 px-4 pb-4">
        {subtitle && (
          <p className="text-[10px] text-zinc-400 uppercase font-bold tracking-widest mb-1 line-clamp-1">{subtitle}</p>
        )}
        <Link href={route('product.show', slug)} className="block mb-2 flex-1">
          <h3 className="text-sm font-bold text-zinc-900 leading-snug tracking-tight group-hover:text-[#B92B3A] transition-colors line-clamp-2">
            {name}
          </h3>
        </Link>

        {colorsToShow.length > 0 && (
          <div className="flex items-center gap-1.5 mb-3 mt-auto flex-wrap">
            {colorsToShow.length === 1 ? (
              <div className="flex items-center gap-1.5">
                {renderSwatch(colorsToShow[0])}
                <span className="text-[11px] text-zinc-500 font-medium truncate">{colorsToShow[0].label}</span>
              </div>
            ) : (
              <>
                {colorsToShow.slice(0, 6).map(c => renderSwatch(c))}
                {colorsToShow.length > 6 && (
                  <span className="text-[10px] font-medium text-zinc-400 ml-1">+{colorsToShow.length - 6}</span>
                )}
              </>
            )}
          </div>
        )}

        <div className="mt-auto pt-2 border-t border-zinc-100 flex items-center justify-between gap-2">
          <div className="text-base md:text-lg font-bold text-zinc-900 flex items-baseline gap-1">
            {displayPrice > 0 ? (
              <>
                <span>{formattedNumber}</span>
                <span className="text-xs font-normal text-zinc-500 lowercase">
                  {currencySymbol}
                </span>
              </>
            ) : (
              <Badge variant="gray" className="!bg-zinc-100 !border-zinc-200 !text-zinc-500 !shadow-none !px-2 !py-0.5 text-[10px]">
                По запросу
              </Badge>
            )}
          </div>
          <Link
            href={route('product.show', slug)}
            className="h-8 px-3.5 bg-zinc-900 hover:bg-[#B92B3A] text-white text-[11px] font-bold tracking-wider uppercase transition-colors flex items-center justify-center rounded-sm"
          >
            Подробнее
          </Link>
        </div>
      </div>
    </div>
  );
};