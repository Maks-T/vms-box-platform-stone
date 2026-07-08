import React from 'react';
import {Heart} from 'lucide-react';
import {useFavorites} from '@/store/useFavorites';
import {cn} from '@/shared/lib/utils';
import {StoneProduct} from '@/types/catalog';

interface FavoriteButtonProps {
  product: StoneProduct;
  className?: string;
  iconClassName?: string;
}

export const FavoriteButton = ({product, className, iconClassName}: FavoriteButtonProps) => {
  const {toggleItem, hasItem} = useFavorites();
  const isFavorite = hasItem(product.id);

  const handleClick = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    toggleItem(product);
  };

  return (
    <button
      onClick={handleClick}
      className={cn(
        "flex items-center justify-center transition-all duration-300 hover:scale-110 cursor-pointer",
        className
      )}
    >
      <Heart
        className={cn(
          "transition-colors duration-300 stroke-[1.5]",
          isFavorite
            ? "fill-destructive text-destructive"
            : "text-muted-foreground hover:text-destructive",
          iconClassName || "w-6 h-6"
        )}
      />
    </button>
  );
};
