import {useState, useEffect} from 'react';
import {servicesApi} from '@/shared/api/services.api';
import {ServiceMatrixItem} from '@/types/catalog';

export function useServicesMatrix() {
  const [services, setServices] = useState<ServiceMatrixItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [endpoint, setEndpoint] = useState<string>('');

  useEffect(() => {
    let isMounted = true;

    servicesApi.getMatrix()
      .then(res => {
        if (isMounted) {
          setServices(res.services);
          setEndpoint(res.endpoint);
        }
      })
      .finally(() => {
        if (isMounted) setIsLoading(false);
      });

    return () => {
      isMounted = false;
    };
  }, []);

  return {services, isLoading, endpoint};
}
