import React from 'react';
import { Heart } from 'lucide-react';

interface FavoritesEmptyStateProps {
  onClose: () => void;
}

export const FavoritesEmptyState = ({ onClose }: FavoritesEmptyStateProps) => {
  return (
    <div className="h-full flex flex-col items-center justify-center text-center p-6 min-h-[350px]">
      <div className="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mb-5 border border-white/10">
        <Heart className="w-8 h-8 text-white/20" strokeWidth={1.5} />
      </div>
      <p className="text-md font-bold uppercase tracking-wider text-white/90 mb-2">
        Здесь пока пусто
      </p>
      <p className="text-xs text-muted-foreground max-w-[240px] leading-relaxed mb-8">
        Добавляйте понравившиеся материалы в избранное, чтобы быстро вернуться к ним позже.
      </p>
      <button
        onClick={onClose}
        className="px-6 py-3 bg-[#3D98FF] hover:bg-[#3D98FF]/90 transition-colors text-white text-xs font-bold uppercase tracking-widest rounded-xl shadow-md cursor-pointer"
      >
        В каталог
      </button>
    </div>
  );
};
