import React from 'react';
import { Heart } from 'lucide-react';
import { SheetHeader, SheetTitle, SheetDescription } from '@/shared/ui/sheet';

interface FavoritesHeaderProps {
  count: number;
  onClear: () => void;
}

export const FavoritesHeader = ({ count, onClear }: FavoritesHeaderProps) => {
  return (
    <SheetHeader className="p-5 md:px-6 border-b border-[#E2E6EA] bg-white flex flex-row items-center justify-between shrink-0">
      <div className="flex items-center gap-3">
        <Heart className="w-5 h-5 text-[#EF5042] fill-[#EF5042]" />
        <SheetTitle className="text-base md:text-lg font-heading font-bold text-[#08274D] m-0">
          Избранное <span className="text-[#8B9198] text-sm font-normal">({count})</span>
        </SheetTitle>
      </div>
      {count > 0 && (
        <button
          onClick={onClear}
          className="text-xs font-heading font-bold text-[#8B9198] hover:text-[#EF5042] uppercase tracking-wider cursor-pointer mr-8 transition-colors"
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
