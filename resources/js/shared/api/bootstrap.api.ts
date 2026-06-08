import client from '@/shared/lib/client';
import { BootstrapConfig } from '@/types/catalog';

export const bootstrapApi = {
  getConfig: async () => {
    const { data } = await client.get<{ status: string; data: BootstrapConfig }>('/api/v1/bootstrap');
    return data.data;
  }
};
