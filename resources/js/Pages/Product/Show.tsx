import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { StoneProduct, BootstrapConfig } from '@/types/catalog';
import BaseContainer from '@/shared/components/layouts/SectionLayout';
import GlassPanel from '@/shared/components/ui/GlassPanel';
import { ApiInspector } from '@widgets/ApiInspector';
import { ProductHeader } from './components/ProductHeader';
import { ProductImagePreview } from './components/ProductImagePreview';
import { ProductMainInfo } from './components/ProductMainInfo';
import { ProductAttributes } from './components/ProductAttributes';
import ProductVariantsList from './components/ProductVariantsList';
import MainLayout from '@/layouts/MainLayout';

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

  const isDev = checkDevMode();
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
      <Head title={`${product.name} - Детали`} />

      <ProductHeader />

      <div className="flex-1 py-8 md:py-12">
        <BaseContainer containerVariant="content">
          <GlassPanel variant="light" className="mb-8 p-6 md:p-10 lg:p-12 bg-card border-border">
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
              <div className="lg:col-span-5">
                <ProductImagePreview
                  image={product.preview_picture}
                  name={product.name}
                  externalCode={product.external_code}
                  id={product.id}
                />
              </div>

              <div className="lg:col-span-7 flex flex-col">
                <ProductMainInfo
                  name={product.name}
                  priceFrom={product.price_from}
                  bootstrapConfig={bootstrapConfig}
                  shortDescription={product.short_description}
                  description={product.description}
                />
                <ProductAttributes attributes={product.attributes} />
                <ProductVariantsList
                  variants={product.variants || []}
                  bootstrapConfig={bootstrapConfig}
                />
              </div>
            </div>
          </GlassPanel>

          {}
          {isDev && (
            <ApiInspector requests={apiRequests} />
          )}
        </BaseContainer>
      </div>
    </MainLayout>
  );
}

ProductShow.layout = (page: any) => page;
