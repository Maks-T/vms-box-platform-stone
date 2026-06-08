import { useState, useEffect } from 'react';
import { catalogApi } from '@/shared/api/catalog.api';
import { bootstrapApi } from '@/shared/api/bootstrap.api';
import { StoneProduct, Filter, BootstrapConfig } from '@/types/catalog';

interface UseCatalogApiProps {
  family: string;
  productType: string;
  page: number;
  filters: Record<string, string[]>;
}

export function useCatalogApi({ family, productType, page, filters }: UseCatalogApiProps) {
  const [products, setProducts] = useState<StoneProduct[]>([]);
  const [meta, setMeta] = useState<any>(null);
  const [filtersSchema, setFiltersSchema] = useState<Filter[]>([]);
  const [bootstrapConfig, setBootstrapConfig] = useState<BootstrapConfig | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [apiUrl, setApiUrl] = useState<string>('');

  useEffect(() => {
    let isMounted = true;

    const fetchData = async () => {
      setIsLoading(true);

      const queryParams = new URLSearchParams();
      queryParams.set('limit', '12');
      queryParams.set('page', page.toString());
      if (productType) queryParams.set('product_type', productType);

      Object.entries(filters).forEach(([key, values]) => {
        if (values && values.length > 0) {
          queryParams.set(`attr[${key}]`, values.join(','));
        }
      });

      try {
        const [productsRes, filtersRes, bootstrapRes] = await Promise.all([
          catalogApi.getProducts(family, queryParams),
          catalogApi.getFilters(family),
          bootstrapApi.getConfig()
        ]);

        if (isMounted) {
          setProducts(productsRes.products);
          setMeta(productsRes.meta);
          setApiUrl(productsRes.endpoint);
          setFiltersSchema(filtersRes);
          setBootstrapConfig(bootstrapRes);
        }
      } catch (error) {
        console.error('Ошибка загрузки данных каталога:', error);
      } finally {
        if (isMounted) setIsLoading(false);
      }
    };

    fetchData();

    return () => { isMounted = false; };
  }, [family, productType, page, filters]);

  return { products, meta, filtersSchema, bootstrapConfig, isLoading, apiUrl };

}
