import client from '@/shared/lib/client';
import { ServiceMatrixItem } from '@/types/catalog';

export const servicesApi = {
  getMatrix: async () => {
    const endpoint = '/api/v1/stone/services-matrix';
    const { data } = await client.get<{ data: { services: ServiceMatrixItem[] } }>(endpoint);
    return {
      services: data.data.services || [],
      endpoint
    };
  }
};
