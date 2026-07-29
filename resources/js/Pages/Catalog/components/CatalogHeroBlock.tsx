import React from 'react';
import { Accent } from '@/shared/components/ui/Typography';
import {PageHero} from "@shared/components/ui/PageHero";

export function CatalogHeroBlock() {
  return (
    <PageHero
      badge="Каталог продукции"
      title={<>Каталог <Accent variant="light">материалов</Accent></>}
      description="Широкий выбор материалов, декоров и комплектующих. Удобная фильтрация по категориям, брендам, цветам и техническим характеристикам."
    />
  );
}