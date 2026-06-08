import React from 'react';
import {Head} from '@inertiajs/react';

import MainLayout from '@/layouts/MainLayout';
import SectionLayout from '@/shared/components/layouts/SectionLayout';
import {CatalogFilters} from '@/features/catalog/components/CatalogFilters';
import {useCatalogParams} from '@/features/catalog/hooks/useCatalogParams';
import {useCatalogApi} from '@/features/catalog/hooks/useCatalogApi';

import {CatalogHeroBlock} from './components/CatalogHeroBlock';
import {CatalogNavigationBlock} from './components/CatalogNavigationBlock';
import {ProductGridBlock} from './components/ProductGridBlock';
import {ApiInspector} from '@widgets/ApiInspector';

export default function CatalogIndex() {
  const {
    family, productType, page, filters: activeFilters,
    setFamily, setProductType, setPage, toggleFilter, clearFilters
  } = useCatalogParams('stone');

  const {
    products, meta, filtersSchema, bootstrapConfig, isLoading, apiUrl
  } = useCatalogApi({family, productType, page, filters: activeFilters});

  const familiesList = bootstrapConfig?.families || [];

  const activeFamilyData = familiesList.find(f => f.code === family);
  const typesForActiveFamily = activeFamilyData?.types || [];
  const activeFamilyName = activeFamilyData?.name;

  const hasActiveFilters = Object.keys(activeFilters).length > 0;

  const apiRequests = [
    {
      label: 'Данные Каталога (Товары / Услуги)',
      endpoint: apiUrl,
      data: {data: products, meta: meta}
    },
    {
      label: 'Схема Фильтров Каталога',
      endpoint: `/api/v1/${family}/filters`,
      data: filtersSchema
    },
    {
      label: 'Глобальная Конфигурация (Bootstrap)',
      endpoint: '/api/v1/bootstrap',
      data: bootstrapConfig
    }
  ];

  return (
    <MainLayout headerOverlaps={false}>
      <Head title={`${activeFamilyName || 'Каталог'} - VMS-NC Box`}/>

      <CatalogHeroBlock/>

      <SectionLayout containerVariant="content" className="pt-0 -mt-6 md:-mt-10">

        <CatalogNavigationBlock
          familiesList={familiesList}
          activeFamily={family}
          setFamily={setFamily}
          typesSchema={typesForActiveFamily}
          productType={productType}
          setProductType={setProductType}
        />

        <div className="flex flex-col lg:flex-row gap-8 lg:gap-12">
          <aside className="hidden lg:block lg:w-[260px] xl:w-[280px] shrink-0">
            <div className="sticky top-28 max-h-[calc(100vh-140px)] overflow-y-auto pr-4 custom-scrollbar">
              {hasActiveFilters && (
                <button onClick={clearFilters}
                        className="mb-8 text-[12px] font-bold text-muted-foreground hover:text-primary uppercase tracking-widest border-b border-border hover:border-primary pb-1 transition-colors">
                  Сбросить фильтры
                </button>
              )}
              <CatalogFilters filters={filtersSchema} activeFilters={activeFilters} onToggle={toggleFilter}/>
            </div>
          </aside>

          <div className="lg:col-span-9 flex-1 relative flex flex-col pt-2 md:pt-4">

            <div className="relative flex-1 mb-16">
              <ProductGridBlock
                isLoading={isLoading}
                products={products}
                meta={meta}
                setPage={setPage}
                clearFilters={clearFilters}
              />
            </div>

            {!isLoading && (
              <div className="mt-8 border-t border-border pt-12 pb-8">
                <h3 className="text-xl font-bold text-foreground mb-6">Инспектор API запросов</h3>
                <ApiInspector requests={apiRequests}/>
              </div>
            )}

          </div>
        </div>
      </SectionLayout>
    </MainLayout>
  );
}

CatalogIndex.layout = (page: any) => page;
