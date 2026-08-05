import React from 'react';
import {Trash2, Image as ImageIcon} from 'lucide-react';
import {Link} from '@inertiajs/react';
import {route} from 'ziggy-js';
import {StoneProduct} from '@/types/catalog';

interface FavoriteItemRowProps {
  item: StoneProduct;
  onRemove: (id: number) => void;
  onNavigate: () => void;
  currencySymbol: string;
}

export const FavoriteItemRow = ({item, onRemove, onNavigate, currencySymbol}: FavoriteItemRowProps) => {
  const formatPrice = (price: number) => {
    if (price <= 0) return '';
    return new Intl.NumberFormat('ru-RU', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }).format(price);
  };

  return (
    <div className="flex gap-3 p-3 rounded bg-white border border-zinc-200 hover:border-zinc-400 transition-colors">
      <div
        className="w-16 h-16 bg-zinc-50 rounded shrink-0 overflow-hidden p-1 flex items-center justify-center border border-zinc-100">
        {item.preview_picture ? (
          <img
            src={item.preview_picture}
            alt={item.name}
            className="w-full h-full object-contain"
          />
        ) : (
          <ImageIcon className="w-6 h-6 text-zinc-300"/>
        )}
      </div>

      <div className="flex-1 min-w-0 flex flex-col justify-between">
        <div>
          <span className="text-[9px] text-zinc-400 font-bold uppercase tracking-widest block mb-0.5">
            Арт: {item.code || item.id}
          </span>
          <Link
            href={route('product.show', item.slug)}
            onClick={onNavigate}
            className="font-bold text-xs text-zinc-900 hover:text-[#B92B3A] transition-colors line-clamp-2 cursor-pointer leading-snug"
          >
            {item.name}
          </Link>
        </div>

        <div className="pt-2 flex items-center justify-between">
          <div className="font-bold text-zinc-900 text-xs flex items-baseline gap-0.5">
            {item.price_from > 0 ? (
              <>
                <span>{formatPrice(item.price_from)}</span>
                <span className="text-[10px] font-normal text-zinc-500 lowercase">
                  {currencySymbol}
                </span>
              </>
            ) : (
              <span className="text-[10px] font-medium text-zinc-400">
                По запросу
              </span>
            )}
          </div>

          <button
            onClick={() => onRemove(item.id)}
            className="text-zinc-400 hover:text-red-600 transition-colors p-1 cursor-pointer rounded hover:bg-zinc-100"
            title="Удалить"
          >
            <Trash2 size={14}/>
          </button>
        </div>
      </div>
    </div>
  );
};