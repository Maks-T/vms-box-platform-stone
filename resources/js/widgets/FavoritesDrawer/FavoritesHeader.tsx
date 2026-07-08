import React from 'react';
import { Heart } from 'lucide-react';
import { SheetHeader, SheetTitle, SheetDescription } from '@/shared/ui/sheet';

interface FavoritesHeaderProps {
  count: number;
  onClear: () => void;
}

export const FavoritesHeader = ({ count, onClear }: FavoritesHeaderProps) => {
  return (
    <SheetHeader className="p-6 border-b border-white/5 bg-[#0B0F19] flex flex-row items-center justify-between shrink-0">
      <div className="flex items-center gap-3">
        <Heart className="w-5 h-5 text-destructive fill-destructive" />
        <SheetTitle className="text-lg font-bold tracking-tight text-white m-0">
          Избранное <span className="text-white/40 text-sm font-normal">({count})</span>
        </SheetTitle>
      </div>
      {count > 0 && (
        <button
          onClick={onClear}
          className="text-xs font-semibold text-muted-foreground hover:text-destructive transition-colors uppercase tracking-wider cursor-pointer mr-8"
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
