import React from 'react';
import {Head, Link} from '@inertiajs/react';

import MainLayout from '@/layouts/MainLayout';
import {CatalogFilters} from '@/features/catalog/components/CatalogFilters';
import {useCatalogParams} from '@/features/catalog/hooks/useCatalogParams';
import {useCatalogApi} from '@/features/catalog/hooks/useCatalogApi';

import {CatalogHeroBlock} from './components/CatalogHeroBlock';
import {CatalogNavigationBlock} from './components/CatalogNavigationBlock';

import {ApiInspector} from '@widgets/ApiInspector';

import {checkDevMode} from '@/shared/lib/dev';
import {ProductGridBlock} from "@/Pages/Catalog/components/ProductGridBlock";

export default function CatalogIndex() {
  const {
    family, productType, page, filters: activeFilters,
    setFamily, setProductType, setPage, toggleFilter, clearFilters
  } = useCatalogParams('stone');

  const {
    products, meta, filtersSchema, bootstrapConfig, isLoading, apiUrl
  } = useCatalogApi({family, productType, page, filters: activeFilters});

  
  const isDev = checkDevMode();

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
      <Head title={`${activeFamilyName || 'Кварцевый камень'} купить в Москве | Каталог QuartzMaster`}/>

      <CatalogHeroBlock/>

      <div className="max-w-[1412px] mx-auto px-4 md:px-8 py-6 lg:py-10 text-left">
        
        {/* Заголовок страницы и хлебные крошки */}
        <div className="mb-8 lg:mb-10">
          <div className="flex items-center gap-3.5 mb-2.5">
            <h1 className="font-heading font-extrabold text-3xl sm:text-4xl text-[#212B36] tracking-tight">
              {activeFamilyName || 'Кварцевый камень'}
            </h1>
            <span className="inline-flex items-center px-2.5 py-0.5 rounded-md border border-[#E5E5E5] text-xs font-semibold text-gray-500 bg-[#F8F8F8]">
              {meta?.total || products.length}
            </span>
          </div>
          <nav className="flex items-center gap-2 text-xs sm:text-sm text-gray-400">
            <Link href="/" className="hover:text-[#212B36] transition-colors">Главная</Link>
            <span>—</span>
            <span className="text-gray-500 font-medium">Каталог камня</span>
          </nav>
        </div>

        <CatalogNavigationBlock
          familiesList={familiesList}
          activeFamily={family}
          setFamily={setFamily}
          typesSchema={typesForActiveFamily}
          productType={productType}
          setProductType={setProductType}
        />

        <div className="flex flex-col lg:flex-row gap-8 lg:gap-12">
          <aside className="w-full lg:w-[280px] shrink-0">
            <div className="sticky top-28">
              <CatalogFilters filters={filtersSchema} activeFilters={activeFilters} onToggle={toggleFilter}/>
            </div>
          </aside>

          <div className="flex-1 min-w-0 w-full relative flex flex-col">

            <div className="flex-1">
              <ProductGridBlock
                isLoading={isLoading}
                products={products}
                meta={meta}
                setPage={setPage}
                clearFilters={clearFilters}
                bootstrapConfig={bootstrapConfig} 
              />
            </div>

            {}
            {!isLoading && isDev && (
              <div className="mt-8 border-t border-border pt-12 pb-8 w-full max-w-full overflow-hidden">
                <h3 className="text-xl font-bold text-foreground mb-6">Инспектор API запросов</h3>
                <ApiInspector requests={apiRequests}/>
              </div>
            )}

          </div>
        </div>
      </div>
    </MainLayout>
  );
}

CatalogIndex.layout = (page: any) => page;
