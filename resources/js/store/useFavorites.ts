import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { StoneProduct } from '@/types/catalog';

interface FavoritesStore {
  items: StoneProduct[];
  isOpen: boolean;
  setIsOpen: (status: boolean) => void;
  toggleItem: (product: StoneProduct) => void;
  removeItem: (id: number) => void;
  clearFavorites: () => void;
  hasItem: (id: number) => boolean;
}

export const useFavorites = create<FavoritesStore>()(
  persist(
    (set, get) => ({
      items: [],
      isOpen: false,

      setIsOpen: (status) => set({ isOpen: status }),

      toggleItem: (product) => {
        const items = get().items;
        const exists = items.some((item) => item.id === product.id);

        if (exists) {
          set({ items: items.filter((item) => item.id !== product.id) });
        } else {
          set({ items: [...items, product] });
        }
      },

      removeItem: (id) => set({
        items: get().items.filter((item) => item.id !== id)
      }),

      clearFavorites: () => set({ items: [] }),

      hasItem: (id) => {
        return get().items.some((item) => item.id === id);
      },
    }),
    {
      name: 'vms-favorites-storage',
    }
  )
);
