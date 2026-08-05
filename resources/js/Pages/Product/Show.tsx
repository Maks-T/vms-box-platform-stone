import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { StoneProduct, ProductVariant, BootstrapConfig } from '@/types/catalog';
import BaseContainer from '@/shared/components/layouts/SectionLayout';
import { ApiInspector } from '@widgets/ApiInspector';
import { ProductHeader } from './components/ProductHeader';
import { ProductImagePreview } from './components/ProductImagePreview';
import { ProductMainInfo } from './components/ProductMainInfo';
import { ProductAttributes } from './components/ProductAttributes';
import { ProductVariantSelector } from './components/ProductVariantSelector';
import ProductVariantsList from './components/ProductVariantsList';
import MainLayout from '@/layouts/MainLayout';
import { FavoriteButton } from '@/shared/components/ui/FavoriteButton';

import { checkDevMode } from '@/shared/lib/dev';
import { bootstrapApi } from '@/shared/api/bootstrap.api';

interface Props {
  product: StoneProduct;
  familyCode: string;
}

export default function ProductShow({ product, familyCode }: Props) {
  const [bootstrapConfig, setBootstrapConfig] = useState<BootstrapConfig | null>(null);

  useEffect(() => {
    bootstrapApi.getConfig().then(setBootstrapConfig);
  }, []);

  const [activeVariant, setActiveVariant] = useState<ProductVariant | null>(() => {
    if (!product.variants || product.variants.length === 0) return null;
    return product.variants.find(v => v.is_default) || product.variants[0];
  });

  const isDev = checkDevMode();
  const defaultPriceType = bootstrapConfig?.price_types?.find((pt: any) => pt.is_default)?.slug || 'retail';

  const defaultVariant = product.variants?.find(v => v.is_default) || product.variants?.[0];

  const displayImage = activeVariant?.preview_picture
    || activeVariant?.detail_picture
    || product.preview_picture
    || defaultVariant?.preview_picture
    || defaultVariant?.detail_picture
    || product.detail_picture;

  const displayPrice = activeVariant
    ? (activeVariant.prices?.[defaultPriceType] || Object.values(activeVariant.prices || {})[0] || product.price_from)
    : product.price_from;

  const unitName = product.unit?.symbol || product.unit?.name || 'шт.';

  const apiEndpoint = `/api/v1/${familyCode}/products?id=${product.id}`;
  const apiRequests = [
    {
      label: 'Карточка товара',
      method: 'GET',
      endpoint: apiEndpoint,
      data: product
    }
  ];

  return (
    <MainLayout headerOverlaps={false}>
      <Head title={`${product.name} - Столешка.Ру`}/>

      <ProductHeader/>

      <div className="flex-1 py-6 md:py-8">
        <BaseContainer containerVariant="content">
          <div className="mb-8 p-6 md:p-8 border border-border bg-white rounded-lg shadow-sm">
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

              <div className="lg:col-span-5 sticky top-20">
                <ProductImagePreview
                  image={displayImage}
                  name={product.name}
                  externalCode={product.external_code}
                  sku={activeVariant?.sku}
                  id={product.id}
                />
              </div>

              <div className="lg:col-span-7 flex flex-col">
                <ProductMainInfo
                  name={product.name}
                  displayPrice={displayPrice}
                  activeVariant={activeVariant}
                  attributes={product.attributes}
                  unitName={unitName}
                  bootstrapConfig={bootstrapConfig}
                  shortDescription={product.short_description}
                  description={product.description}
                />

                <ProductVariantSelector
                  variants={product.variants || []}
                  activeVariant={activeVariant}
                  onSelectVariant={setActiveVariant}
                />

                <div className="flex items-center gap-3 my-4">
                  <a
                    href="/calculator"
                    className="flex-1 h-11 bg-[#B92B3A] hover:bg-[#9E2230] text-white text-xs font-bold uppercase tracking-wider rounded flex items-center justify-center shadow-sm transition-all active:scale-95"
                  >
                    Рассчитать проект
                  </a>

                  <div className="p-2.5 bg-white rounded border border-zinc-200 flex items-center justify-center">
                    <FavoriteButton product={product}/>
                  </div>
                </div>

                <ProductAttributes attributes={product.attributes}/>

                <ProductVariantsList
                  variants={product.variants || []}
                  activeVariant={activeVariant}
                  onSelectVariant={setActiveVariant}
                  bootstrapConfig={bootstrapConfig}
                />
              </div>

            </div>
          </div>

          {isDev && (
            <ApiInspector requests={apiRequests}/>
          )}
        </BaseContainer>
      </div>
    </MainLayout>
  );
}

ProductShow.layout = (page: any) => page;