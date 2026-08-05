import React from 'react';
import { Heart } from 'lucide-react';

interface FavoritesEmptyStateProps {
  onClose: () => void;
}

export const FavoritesEmptyState = ({ onClose }: FavoritesEmptyStateProps) => {
  return (
    <div className="h-full flex flex-col items-center justify-center text-center p-6 min-h-[350px]">
      <div className="w-14 h-14 rounded bg-zinc-100 flex items-center justify-center mb-4 border border-zinc-200">
        <Heart className="w-6 h-6 text-zinc-400" strokeWidth={1.5} />
      </div>
      <p className="text-sm font-bold uppercase tracking-wider text-zinc-900 mb-1.5">
        Здесь пока пусто
      </p>
      <p className="text-xs text-zinc-500 max-w-[240px] leading-relaxed mb-6 font-medium">
        Добавляйте понравившиеся материалы в избранное, чтобы быстро вернуться к ним позже.
      </p>
      <button
        onClick={onClose}
        className="px-5 py-2.5 bg-[#B92B3A] hover:bg-[#9E2230] transition-colors text-white text-xs font-bold uppercase tracking-wider rounded shadow-xs cursor-pointer active:scale-95"
      >
        Перейти в каталог
      </button>
    </div>
  );
};