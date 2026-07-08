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
    <div className="flex gap-4 p-4 rounded-xl bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors">
      <div className="w-20 h-20 bg-white rounded-lg shrink-0 overflow-hidden p-1 flex items-center justify-center border border-white/10">
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
        <span className="text-[10px] text-muted-foreground uppercase tracking-widest mb-1 block">
          ID {item.id}
        </span>
        <Link
          href={route('product.show', item.slug)}
          onClick={onNavigate}
          className="font-bold text-[14px] leading-snug text-white hover:text-[#3D98FF] transition-colors line-clamp-2 uppercase cursor-pointer"
        >
          {item.name}
        </Link>

        <div className="mt-auto pt-2 flex items-center justify-between">
          <div className="font-black text-[#3D98FF] text-[16px] flex items-baseline gap-0.5">
            {item.price_from > 0 ? (
              <>
                <span>{formatPrice(item.price_from)}</span>
                <span className="text-[10px] font-normal opacity-70 text-slate-400 lowercase">
                  {currencySymbol}
                </span>
              </>
            ) : (
              <span className="text-[11px] font-normal text-muted-foreground">
                По запросу
              </span>
            )}
          </div>

          <button
            onClick={() => onRemove(item.id)}
            className="text-white/30 hover:text-destructive transition-colors p-1.5 cursor-pointer rounded-lg hover:bg-white/5"
            title="Удалить"
          >
            <Trash2 size={16} />
          </button>
        </div>
      </div>
    </div>
  );
};
