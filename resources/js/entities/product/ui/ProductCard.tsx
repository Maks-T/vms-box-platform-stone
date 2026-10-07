import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Image as ImageIcon } from 'lucide-react';
import { StoneProduct, EavValueOption, BootstrapConfig, ProductVariant } from '@/types/catalog';
import { route } from 'ziggy-js';
import { cn } from '@/shared/lib/utils';
import { FavoriteButton } from '@/shared/components/ui/FavoriteButton';

interface ProductCardProps {
  product: StoneProduct;
  bootstrapConfig?: BootstrapConfig | null;
}

export const ProductCard = ({ product, bootstrapConfig }: ProductCardProps) => {
  const { id, name, slug, price_from, preview_picture, unit, attributes, variants, external_code } = product;
  const [activeVariant, setActiveVariant] = useState<ProductVariant | null>(null);

  const defaultPriceType = bootstrapConfig?.price_types?.find((pt: any) => pt.is_default)?.slug || 'retail';
  const displayImage = activeVariant?.preview_picture || preview_picture;
  const displayPrice = activeVariant
    ? (activeVariant.prices?.[defaultPriceType] || Object.values(activeVariant.prices || {})[0] || price_from)
    : price_from;

  const currencySymbol = '₽';
  const formattedNumber = displayPrice > 0
    ? new Intl.NumberFormat('ru-RU', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(displayPrice)
    : '';

  const slabCode = external_code || (attributes?.code?.value as string) || `R${id}`;
  const unitLabel = unit?.symbol || unit?.name || 'м²';

  // Определение типа: слэб/текстура камня (object-cover) или оборудование/сантехника (object-contain)
  const isSlab = (() => {
    const nameLower = (name || '').toLowerCase();
    const slugLower = (slug || '').toLowerCase();
    const codeLower = (external_code || '').toLowerCase();

    const nonSlabKeywords = [
      'смесител', 'мойк', 'раковина', 'дозатор', 'сифон', 'кран',
      'труб', 'опор', 'сва', 'кляймер', 'крепеж', 'профил', 'поддон',
      'faucet', 'sink', 'tap', 'dispenser', 'level', 'blanco', 'omoikiri'
    ];

    if (nonSlabKeywords.some((kw) => nameLower.includes(kw) || slugLower.includes(kw) || codeLower.includes(kw))) {
      return false;
    }

    return true;
  })();

  // Извлечение всех доступных цветов для свотчей карточки
  const parentColor = attributes?.color?.value as EavValueOption | EavValueOption[] | undefined;
  const variantColors: EavValueOption[] = [];

  if (variants?.length > 0) {
    const seen = new Set();
    variants.forEach((v) => {
      const vColor = v.attributes?.color?.value as EavValueOption | undefined;
      if (vColor && vColor.key && !seen.has(vColor.key)) {
        seen.add(vColor.key);
        variantColors.push(vColor);
      }
    });
  }

  const parentList = Array.isArray(parentColor) ? parentColor : (parentColor ? [parentColor] : []);
  const colorsToShow = variantColors.length > 0 ? variantColors : parentList;
  const activeColorKey = (activeVariant?.attributes?.color?.value as EavValueOption)?.key;

  const handleColorClick = (e: React.MouseEvent, color: EavValueOption) => {
    e.preventDefault();
    e.stopPropagation();

    const match = variants?.find((v) => {
      const vColor = v.attributes?.color?.value as EavValueOption | undefined;
      return vColor?.key === color.key;
    });

    if (match) {
      setActiveVariant(match);
    }
  };

  return (
    <div className="group border border-[#E5E5E5] rounded-md bg-white overflow-hidden shadow-2xs hover:shadow-hover hover:border-gray-300 transition-all flex flex-col text-left">
      {/* Изображение слэба */}
      <Link href={route('product.show', slug)} className="relative aspect-square block bg-[#F8F8F8] overflow-hidden">
        {displayImage ? (
          <img
            src={displayImage}
            alt={name}
            className={cn(
              "w-full h-full transition-transform duration-300 group-hover:scale-105",
              isSlab ? "object-cover" : "object-contain p-4 mix-blend-multiply"
            )}
          />
        ) : (
          <div className="flex items-center justify-center w-full h-full opacity-20 text-slate-400">
            <ImageIcon className="w-16 h-16" />
          </div>
        )}

        {/* Фирменный шильдик на слэбе */}
        <span className="absolute bottom-2 left-2 px-1.5 py-0.5 bg-white/90 text-[10px] text-gray-700 font-mono rounded-xs border border-gray-200 shadow-2xs">
          {slabCode}
        </span>

        {/* Кнопка «В избранное» */}
        <div className="absolute top-2 right-2">
          <FavoriteButton
            product={product}
            className="p-1 rounded-sm bg-white/80 hover:bg-white text-gray-400 hover:text-[#ED1C24] transition-colors"
            iconClassName="w-4 h-4"
          />
        </div>
      </Link>

      {/* Описание слэба */}
      <div className="p-4 flex flex-col flex-1 justify-between">
        <div>
          <span className="text-[11px] text-gray-400 block mb-1">
            {unit?.name ? `Ед. изм: ${unit.name}` : 'Кварцевый камень'}
          </span>
          <Link
            href={route('product.show', slug)}
            className="font-heading font-bold text-sm text-[#212B36] hover:text-[#25CED1] transition-colors line-clamp-2 leading-snug"
          >
            {name}
          </Link>
        </div>

        {/* Свотчи цветов карточки */}
        {colorsToShow.length > 0 && (
          <div className="flex items-center gap-1.5 my-2.5 flex-wrap">
            {colorsToShow.slice(0, 6).map((color) => {
              const isSelected = color.key === activeColorKey;
              const hex = color.meta?.hex || '#CBD5E1';
              const img = color.meta?.image;

              return (
                <button
                  key={color.key}
                  type="button"
                  title={color.label}
                  onClick={(e) => handleColorClick(e, color)}
                  className={cn(
                    "size-4 rounded-xs border cursor-pointer transition-transform hover:scale-110 shrink-0",
                    isSelected ? "border-[#212B36] ring-1 ring-[#212B36] scale-105" : "border-gray-200"
                  )}
                  style={{ backgroundColor: img ? undefined : hex }}
                >
                  {img && <img src={img} alt={color.label} className="size-full object-cover rounded-xs" />}
                </button>
              );
            })}
            {colorsToShow.length > 6 && (
              <span className="text-[10px] text-gray-400 font-medium">+{colorsToShow.length - 6}</span>
            )}
          </div>
        )}

        {/* Статус наличия и стоимость */}
        <div className="mt-3 flex items-center justify-between pt-2 border-t border-gray-100">
          <span className="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
            <span className="w-1.5 h-1.5 rounded-full bg-emerald-500" />
            Склад РФ
          </span>

          <span className="font-heading font-bold text-xs text-[#212B36]">
            {displayPrice > 0 ? `${formattedNumber} ${currencySymbol} / ${unitLabel}` : 'По запросу'}
          </span>
        </div>
      </div>
    </div>
  );
};

export default ProductCard;