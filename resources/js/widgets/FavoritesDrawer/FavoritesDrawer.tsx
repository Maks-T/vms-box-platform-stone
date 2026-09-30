import React, { useEffect, useState } from 'react';
import { useFavorites } from '@/store/useFavorites';
import { bootstrapApi } from '@/shared/api/bootstrap.api';
import { BootstrapConfig } from '@/types/catalog';
import { Sheet, SheetContent } from '@/shared/ui/sheet';
import { FavoritesHeader } from './FavoritesHeader';
import { FavoritesEmptyState } from './FavoritesEmptyState';
import { FavoriteItemRow } from './FavoriteItemRow';

export const FavoritesDrawer = () => {
  const { isOpen, setIsOpen, items, removeItem, clearFavorites } = useFavorites();
  const [bootstrapConfig, setBootstrapConfig] = useState<BootstrapConfig | null>(null);

  useEffect(() => {
    if (isOpen) {
      bootstrapApi.getConfig().then(setBootstrapConfig);
    }
  }, [isOpen]);

  const currencySymbol = bootstrapConfig?.base_currency?.symbol_native || bootstrapConfig?.base_currency?.symbol || 'Br';

  return (
    <Sheet open={isOpen} onOpenChange={setIsOpen}>
      <SheetContent
        side="right"
        className="w-full sm:max-w-[460px] p-0 flex flex-col gap-0 border-l border-[#E2E6EA] bg-white text-[#1E252D] shadow-2xl"
      >
        <FavoritesHeader count={items.length} onClear={clearFavorites} />

        <div className="flex-1 overflow-y-auto custom-scrollbar p-6">
          {items.length === 0 ? (
            <FavoritesEmptyState onClose={() => setIsOpen(false)} />
          ) : (
            <div className="flex flex-col gap-5">
              {items.map((item) => (
                <FavoriteItemRow
                  key={item.id}
                  item={item}
                  onRemove={removeItem}
                  onNavigate={() => setIsOpen(false)}
                  currencySymbol={currencySymbol}
                />
              ))}
            </div>
          )}
        </div>

        <div className="p-4 border-t border-[#E2E6EA] bg-[#F8F9FA] shrink-0 text-center text-[#8B9198] text-[11px] font-heading font-semibold uppercase tracking-wider">
          МАСТЕРСТОУН • ИЗБРАННЫЕ МАТЕРИАЛЫ
        </div>
      </SheetContent>
    </Sheet>
  );
};
