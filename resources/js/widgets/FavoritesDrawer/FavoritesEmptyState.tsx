import React from 'react';
import { Heart } from 'lucide-react';

interface FavoritesEmptyStateProps {
  onClose: () => void;
}

export const FavoritesEmptyState = ({ onClose }: FavoritesEmptyStateProps) => {
  return (
    <div className="h-full flex flex-col items-center justify-center text-center p-6 min-h-[350px]">
      <div className="w-16 h-16 rounded-full bg-[#F0F2F5] flex items-center justify-center mb-5 border border-[#E2E6EA]">
        <Heart className="w-8 h-8 text-[#8B9198]/40" strokeWidth={1.5} />
      </div>
      <p className="font-heading font-bold text-[#08274D] text-base uppercase tracking-wider mb-2">
        Здесь пока пусто
      </p>
      <p className="text-xs text-[#696973] max-w-[240px] leading-relaxed mb-8">
        Добавляйте понравившиеся материалы в избранное, чтобы быстро вернуться к ним позже.
      </p>
      <button
        onClick={onClose}
        className="px-7 py-3.5 bg-[#08274D] hover:bg-[#EF5042] text-white font-heading text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm cursor-pointer active:scale-95"
      >
        В каталог
      </button>
    </div>
  );
};
