import React from 'react';
import { Heart } from 'lucide-react';
import { SheetHeader, SheetTitle, SheetDescription } from '@/shared/ui/sheet';

interface FavoritesHeaderProps {
  count: number;
  onClear: () => void;
}

export const FavoritesHeader = ({ count, onClear }: FavoritesHeaderProps) => {
  return (
    <SheetHeader className="p-5 border-b border-zinc-200 bg-white flex flex-row items-center justify-between shrink-0">
      <div className="flex items-center gap-2.5">
        <Heart className="w-4.5 h-4.5 fill-[#B92B3A] text-[#B92B3A]" />
        <SheetTitle className="text-base font-bold tracking-tight text-zinc-900 m-0">
          Избранное <span className="text-zinc-400 text-xs font-normal">({count})</span>
        </SheetTitle>
      </div>
      {count > 0 && (
        <button
          onClick={onClear}
          className="text-[11px] font-semibold text-zinc-400 hover:text-[#B92B3A] transition-colors uppercase tracking-wider cursor-pointer mr-6"
        >
          Очистить
        </button>
      )}
      <SheetDescription className="sr-only">
        Выбранные материалы и товары каменных изделий
      </SheetDescription>
    </SheetHeader>
  );
};