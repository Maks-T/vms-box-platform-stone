import React from 'react';
import { Head } from '@inertiajs/react';

import MainLayout from '@/layouts/MainLayout';
import SectionLayout from '@/shared/components/layouts/SectionLayout';
import { CatalogFilters } from '@/features/catalog/components/CatalogFilters';
import { CatalogSearchInput } from '@/features/catalog/components/CatalogSearchInput';
import { useCatalogParams } from '@/features/catalog/hooks/useCatalogParams';
import { useCatalogApi } from '@/features/catalog/hooks/useCatalogApi';

import { CatalogHeroBlock } from './components/CatalogHeroBlock';
import { CatalogNavigationBlock } from './components/CatalogNavigationBlock';
import { ProductGridBlock } from './components/ProductGridBlock';
import { ApiInspector } from '@widgets/ApiInspector';
import { useDevMode } from '@/shared/hooks/useDevMode';

export default function CatalogIndex() {
  const isDev = useDevMode();

  const {
    family, productType, search, page, filters: activeFilters,
    setFamily, setProductType, setSearch, setPage, toggleFilter, clearFilters
  } = useCatalogParams('stone');

  const {
    products, meta, filtersSchema, bootstrapConfig, isLoading, apiUrl
  } = useCatalogApi({ family, productType, search, page, filters: activeFilters });

  const familiesList = bootstrapConfig?.families || [];

  const activeFamilyData = familiesList.find(f => f.code === family);
  const typesForActiveFamily = activeFamilyData?.types || [];
  const activeFamilyName = activeFamilyData?.name;

  const hasActiveFilters = Object.keys(activeFilters).length > 0 || Boolean(search);

  const apiRequests = [
    {
      label: 'Данные Каталога (Товары / Услуги)',
      endpoint: apiUrl,
      data: { data: products, meta: meta }
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

      <div className="pt-4 md:pt-6 pb-2">
        <CatalogHeroBlock/>
      </div>

      <SectionLayout containerVariant="content" noPadding={true} className="py-2">

        <CatalogNavigationBlock
          familiesList={familiesList}
          activeFamily={family}
          setFamily={setFamily}
          typesSchema={typesForActiveFamily}
          productType={productType}
          setProductType={setProductType}
          isLoading={isLoading}
        />

        <div className="mb-5 w-full flex justify-start">
          <CatalogSearchInput
            value={search}
            onChange={setSearch}
            placeholder="Поиск по названию, коду, артикулу поставщика..."
          />
        </div>

        <div className="flex flex-col lg:flex-row gap-8 lg:gap-10">

          <aside className="w-full lg:w-[260px] xl:w-[280px] shrink-0">
            <div className="sticky top-20 max-h-[calc(100vh-100px)] overflow-y-auto pr-1 no-scrollbar">
              <div className="bg-white p-5 rounded border border-border space-y-5">
                <div className="flex items-center justify-between border-b border-border pb-3">
                  <h2 className="font-bold text-xs text-zinc-900 uppercase tracking-wider">Подбор параметров</h2>
                  {hasActiveFilters && (
                    <button
                      onClick={clearFilters}
                      className="text-[10px] font-semibold text-zinc-400 hover:text-red-700 transition-colors cursor-pointer"
                    >
                      Сбросить
                    </button>
                  )}
                </div>

                <CatalogFilters
                  filters={filtersSchema}
                  activeFilters={activeFilters}
                  onToggle={toggleFilter}
                />
              </div>
            </div>
          </aside>

          <div className="lg:col-span-9 flex-1 relative flex flex-col pt-0">
            <div className="relative flex-1 mb-16">
              <ProductGridBlock
                isLoading={isLoading}
                products={products}
                meta={meta}
                setPage={setPage}
                clearFilters={clearFilters}
                bootstrapConfig={bootstrapConfig}
              />
            </div>

            {!isLoading && isDev && (
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