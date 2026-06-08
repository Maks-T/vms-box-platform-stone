import client from '@/shared/lib/client';
import { StoneProduct, Filter } from '@/types/catalog';

export const catalogApi = {
  getProducts: async (family: string, params: URLSearchParams) => {
    const endpoint = `/api/v1/${family}/products?${params.toString()}`;
    const { data } = await client.get<{ data: StoneProduct[]; meta: any }>(endpoint);
    return {
      products: data.data || [],
      meta: data.meta || null,
      endpoint
    };
  },

  getFilters: async (family: string) => {
    const { data } = await client.get<{ data: Filter[] }>(`/api/v1/${family}/filters`);
    return data.data || [];
  }
};
