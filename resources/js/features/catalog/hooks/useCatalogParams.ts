import { useState, useEffect } from 'react';

export interface CatalogParams {
  family: string;
  productType: string;
  page: number;
  filters: Record<string, string[]>;
}

export function useCatalogParams(defaultFamily: string = 'stone') {
  const [params, setParams] = useState<CatalogParams>(() => {
    const searchParams = new URLSearchParams(window.location.search);
    const initial: CatalogParams = {
      family: searchParams.get('family') || defaultFamily,
      productType: searchParams.get('product_type') || '',
      page: Number(searchParams.get('page')) || 1,
      filters: {}
    };

    // === ИЗМЕНЕНО: Безопасный парсинг вложенных параметров вида attributes[color]=... ===
    for (const [key, value] of searchParams.entries()) {
      const match = key.match(/^attributes\[(.+?)\]$/);
      if (match) {
        const attrCode = match[1];
        initial.filters[attrCode] = value.split(',');
      }
    }

    return initial;
  });

  useEffect(() => {
    const searchParams = new URLSearchParams();
    searchParams.set('family', params.family);

    if (params.productType) searchParams.set('product_type', params.productType);
    if (params.page > 1) searchParams.set('page', params.page.toString());

    Object.entries(params.filters).forEach(([key, values]) => {
      if (values && values.length > 0) {
        searchParams.set(`attr[${key}]`, values.join(','));
      }
    });

    const newUrl = `${window.location.pathname}?${searchParams.toString()}`;
    window.history.replaceState({}, '', newUrl);
  }, [params]);

  const setPage = (page: number) => {
    setParams(prev => ({ ...prev, page }));
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const setFamily = (family: string) => {
    setParams({ family, productType: '', page: 1, filters: {} });
  };

  const setProductType = (type: string) => {
    setParams(prev => ({ ...prev, productType: type, page: 1 }));
  };

  const toggleFilter = (code: string, slug: string) => {
    setParams(prev => {
      const currentValues = prev.filters[code] || [];
      const newValues = currentValues.includes(slug)
        ? currentValues.filter(v => v !== slug)
        : [...currentValues, slug];

      const newFilters = { ...prev.filters };
      if (newValues.length > 0) {
        newFilters[code] = newValues;
      } else {
        delete newFilters[code];
      }

      return { ...prev, page: 1, filters: newFilters };
    });
  };

  const clearFilters = () => {
    setParams(prev => ({ ...prev, page: 1, filters: {} }));
  };

  return {
    family: params.family,
    productType: params.productType,
    page: params.page,
    filters: params.filters,
    setFamily,
    setProductType,
    setPage,
    toggleFilter,
    clearFilters
  };
}
