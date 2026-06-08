import {useState, useEffect} from 'react';
import {bootstrapApi} from '@/shared/api/bootstrap.api';
import {BootstrapConfig} from '@/types/catalog';

export function useBootstrap() {
  const [data, setData] = useState<BootstrapConfig | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let isMounted = true;

    bootstrapApi.getConfig()
      .then(res => {
        if (isMounted) setData(res);
      })
      .finally(() => {
        if (isMounted) setIsLoading(false);
      });

    return () => {
      isMounted = false;
    };
  }, []);

  return {data, isLoading, endpoint: '/api/v1/bootstrap'};
}
