import React from 'react';
import { Trash2, Image as ImageIcon } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { StoneProduct } from '@/types/catalog';

interface FavoriteItemRowProps {
  item: StoneProduct;
  onRemove: (id: number) => void;
  onNavigate: () => void;
  currencySymbol: string;
}

export const FavoriteItemRow = ({ item, onRemove, onNavigate, currencySymbol }: FavoriteItemRowProps) => {
  const formatPrice = (price: number) => {
    if (price <= 0) return '';
    return new Intl.NumberFormat('ru-RU', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }).format(price);
  };

  return (
    <div className="flex gap-4 p-3.5 rounded-md bg-white border border-[#E5E5E5] hover:border-gray-300 transition-colors shadow-2xs text-left">
      <div className="w-20 h-20 bg-[#F8F8F8] rounded-sm shrink-0 overflow-hidden p-1 flex items-center justify-center border border-[#E5E5E5]">
        {item.preview_picture ? (
          <img
            src={item.preview_picture}
            alt={item.name}
            className="w-full h-full object-contain mix-blend-multiply"
          />
        ) : (
          <ImageIcon className="w-8 h-8 text-slate-300" />
        )}
      </div>

      <div className="flex-1 min-w-0 flex flex-col">
        <span className="text-[10px] font-mono font-semibold text-[#8B9198] uppercase tracking-wider mb-1 block">
          Код: {item.external_code || item.id}
        </span>
        <Link
          href={route('product.show', item.slug)}
          onClick={onNavigate}
          className="font-heading font-bold text-[14px] leading-snug text-[#212B36] hover:text-[#25CED1] transition-colors line-clamp-2 cursor-pointer"
        >
          {item.name}
        </Link>

        <div className="mt-auto pt-2 flex items-center justify-between">
          <div className="font-heading font-black text-[#212B36] text-[15px] flex items-baseline gap-1">
            {item.price_from > 0 ? (
              <>
                <span>{formatPrice(item.price_from)}</span>
                <span className="text-[11px] font-normal text-[#696973] lowercase">
                  {currencySymbol} / м²
                </span>
              </>
            ) : (
              <span className="text-[11px] font-heading font-bold text-[#8B9198] uppercase">
                По запросу
              </span>
            )}
          </div>

          <button
            onClick={() => onRemove(item.id)}
            className="text-gray-400 hover:text-[#ED1C24] transition-colors p-1.5 cursor-pointer rounded-sm hover:bg-gray-100"
            title="Удалить"
          >
            <Trash2 size={16} />
          </button>
        </div>
      </div>
    </div>
  );
};
