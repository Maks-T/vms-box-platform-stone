import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import { StoneProduct, BootstrapConfig, ProductVariant, EavValueOption } from '@/types/catalog';
import { route } from 'ziggy-js';
import { ProductHeader } from './components/ProductHeader';
import { ProductImagePreview } from './components/ProductImagePreview';
import { ProductMainInfo } from './components/ProductMainInfo';
import { ProductAttributes } from './components/ProductAttributes';
import ProductVariantsList from './components/ProductVariantsList';
import MainLayout from '@/layouts/MainLayout';
import { bootstrapApi } from '@/shared/api/bootstrap.api';

interface Props {
  product: StoneProduct;
  familyCode: string;
}

export default function ProductShow({ product }: Props) {
  const [bootstrapConfig, setBootstrapConfig] = useState<BootstrapConfig | null>(null);
  const [activeVariant, setActiveVariant] = useState<ProductVariant | null>(null);

  useEffect(() => {
    bootstrapApi.getConfig().then(setBootstrapConfig);
  }, []);

  // Инициализация дефолтного варианта товара
  useEffect(() => {
    if (product.variants && product.variants.length > 0) {
      const defaultVar = product.variants.find((v) => v.is_default) || product.variants[0];
      setActiveVariant(defaultVar);
    }
  }, [product]);

  // Извлечение всех доступных уникальных цветов товара и вариантов
  const parentColor = product.attributes?.color?.value as EavValueOption | EavValueOption[] | undefined;
  const colorsMap = new Map<string, { option: EavValueOption; variant?: ProductVariant }>();

  if (product.variants?.length > 0) {
    product.variants.forEach((v) => {
      const vColor = v.attributes?.color?.value as EavValueOption | undefined;
      if (vColor && vColor.key && !colorsMap.has(vColor.key)) {
        colorsMap.set(vColor.key, { option: vColor, variant: v });
      }
    });
  }

  if (colorsMap.size === 0 && parentColor) {
    const list = Array.isArray(parentColor) ? parentColor : [parentColor];
    list.forEach((c) => {
      if (c && typeof c === 'object' && 'key' in c && !colorsMap.has(c.key)) {
        colorsMap.set(c.key, { option: c });
      }
    });
  }

  const availableColors = Array.from(colorsMap.values());

  const handleSelectColor = (item: { option: EavValueOption; variant?: ProductVariant }) => {
    if (item.variant) {
      setActiveVariant(item.variant);
    } else {
      const match = product.variants?.find(
        (v) => (v.attributes?.color?.value as EavValueOption)?.key === item.option.key
      );
      if (match) setActiveVariant(match);
    }
  };

  const currentImage = activeVariant?.detail_picture || activeVariant?.preview_picture || product.detail_picture || product.preview_picture;
  const currentCode = activeVariant?.sku || activeVariant?.external_code || product.external_code;

  return (
    <MainLayout headerOverlaps={false}>
      <Head title={`Кварцевый камень ${product.name} | QuartzMaster`} />

      <main className="max-w-[1412px] mx-auto px-4 md:px-8 py-6 lg:py-10 space-y-10 lg:space-y-14 text-left">
        {/* 1. Хлебные крошки и кнопка "Поделиться" */}
        <ProductHeader productName={product.name} />

        {/* 2. Главный блок товара (Большое фото + Заголовок + CTA + Характеристики) */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
          <div className="lg:col-span-6 xl:col-span-5">
            <ProductImagePreview
              image={currentImage}
              name={product.name}
              externalCode={currentCode}
              id={product.id}
            />
          </div>

          <div className="lg:col-span-6 xl:col-span-7">
            <ProductMainInfo
              product={product}
              activeVariant={activeVariant}
              availableColors={availableColors}
              onSelectColor={handleSelectColor}
              bootstrapConfig={bootstrapConfig}
            />

            {/* Торговые предложения (SKU) слэбов */}
            <ProductVariantsList
              variants={product.variants || []}
              bootstrapConfig={bootstrapConfig}
            />
          </div>
        </div>

        {/* 3. Текстовое описание изделия из референса */}
        <section className="text-sm text-gray-600 leading-relaxed max-w-5xl border-t border-[#E5E5E5] pt-8">
          <p>
            Столешница для кухни из кварцевого камня {product.name} – идеальное решение для Вашего интерьера.
            Также мы можем изготовить для Вас подоконники, ступени и другие изделия из кварца {product.name}.
            Для расчета стоимости изделия обратитесь к нашим менеджерам по телефону +7 (495) 565 31 66
            или воспользуйтесь разделом On-line дизайнера.
          </p>
        </section>

        {/* 4. Полная таблица характеристик с точечными линиями-лидерами */}
        <ProductAttributes attributes={product.attributes} productName={product.name} />

        {/* 5. Нижняя навигация (возврат к каталогу) */}
        <div className="pt-6 border-t border-[#E5E5E5] flex items-center justify-between">
          <Link
            href={route('catalog')}
            className="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-[#212B36] group transition-colors"
          >
            <span className="w-7 h-7 rounded-md border border-[#E5E5E5] flex items-center justify-center group-hover:border-[#212B36] transition-colors">
              ←
            </span>
            <span>Назад к списку камня</span>
          </Link>
        </div>
      </main>
    </MainLayout>
  );
}

ProductShow.layout = (page: any) => page;