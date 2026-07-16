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

  let subtitle = 'Каталог';
  if (brand) subtitle = brand.label; // Был brand.name
  else if (collection) subtitle = collection.label; // Был collection.name
  else if (serviceTags && Array.isArray(serviceTags) && serviceTags.length > 0) {
    subtitle = serviceTags.map(t => t.label).join(', '); // Был t.name
  } else if (unit) subtitle = `Ед. изм: ${unit.name}`;

  const parentColor = attributes?.color?.value as EavValueOption | undefined;
  const variantColors: EavValueOption[] = [];

  if (variants?.length > 0) {
    const seen = new Set();
    variants.forEach(v => {
      const vColor = v.attributes?.color?.value as EavValueOption | undefined;
      if (vColor && !seen.has(vColor.key)) { // Был vColor.slug
        seen.add(vColor.key); // Был vColor.slug
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
      "w-5 h-5 rounded-full object-cover border border-slate-200/80 shadow-sm cursor-pointer transition-all duration-300",
      isSelected
        ? "ring-1 ring-sky-800/40 ring-offset-[1.5px] scale-105 opacity-100"
        : "opacity-65 hover:opacity-100 hover:scale-105"
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
          style={{backgroundColor: color.meta.hex}}
        />
      );
    }
    return null;
  };

  return (
    <div
      className="group flex flex-col h-full bg-card rounded-2xl overflow-hidden border border-border hover:shadow-lg transition-all duration-300">

      <div className="relative aspect-square bg-slate-50 overflow-hidden mb-5 border-b border-border">
        <Link href={route('product.show', slug)} className="block w-full h-full p-6">
          {displayImage ? (
            <img
              src={displayImage}
              alt={name}
              className="w-full h-full object-contain mix-blend-multiply transition-transform duration-700 group-hover:scale-105"
            />
          ) : (
            <div className="flex items-center justify-center w-full h-full opacity-20 text-muted-foreground">
              <ImageIcon className="w-16 h-16"/>
            </div>
          )}
        </Link>
        <div
          className="absolute top-4 left-4 bg-primary text-primary-foreground text-[10px] font-bold px-2 py-1 rounded uppercase tracking-widest shadow-sm">
          ID {id}
        </div>
        <FavoriteButton product={product} className="absolute top-4 right-4" />
      </div>

      <div className="flex flex-col flex-1 px-5 pb-5">
        <p className="text-[11px] text-muted-foreground uppercase tracking-widest mb-2 line-clamp-1">{subtitle}</p>
        <Link href={route('product.show', slug)} className="block mb-3 flex-1">
          <h3
            className="text-[16px] md:text-[18px] font-bold text-foreground leading-snug tracking-tight group-hover:text-primary transition-colors line-clamp-2">{name}</h3>
        </Link>

        {colorsToShow.length > 0 && (
          <div className="flex items-center gap-1.5 mb-4 mt-auto flex-wrap">
            {colorsToShow.length === 1 ? (
              <div className="flex items-center gap-2">
                {renderSwatch(colorsToShow[0])}
                <span className="text-xs text-muted-foreground truncate">{colorsToShow[0].label}</span>
              </div>
            ) : (
              <>
                {colorsToShow.slice(0, 6).map(c => renderSwatch(c))}
                {colorsToShow.length > 6 && <span
                  className="text-[11px] font-medium text-muted-foreground ml-1">+{colorsToShow.length - 6}</span>}
              </>
            )}
          </div>
        )}

        <div className="mt-auto flex flex-col gap-4">
          <div className="text-[20px] md:text-[24px] font-black text-foreground flex items-baseline gap-1 min-h-[32px]">
            {displayPrice > 0 ? (
              <>
                <span>{formattedNumber}</span>
                <span className="text-xs md:text-sm font-normal text-muted-foreground lowercase">
                  {currencySymbol}
                </span>
              </>
            ) : (
              <Badge variant="gray" className="!bg-muted !border-border !text-muted-foreground !shadow-none !px-3 !py-1 text-xs">
                Бесплатно / По запросу
              </Badge>
            )}
          </div>
          <Link href={route('product.show', slug)}
                className="w-full h-[46px] bg-slate-900 text-white hover:bg-sky-600 active:scale-[0.98] text-[13px] font-bold tracking-[0.1em] uppercase transition-all duration-300 flex items-center justify-center rounded-xl shadow-md">
            Подробнее
          </Link>
        </div>
      </div>
    </div>
  );
};
