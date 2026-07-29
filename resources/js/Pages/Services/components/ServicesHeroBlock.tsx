import React from 'react';
import {Accent} from '@/shared/components/ui/Typography';
import {PageHero} from "@shared/components/ui/PageHero";

export function ServicesHeroBlock() {
  return (
    <PageHero
      badge="Матрица цен"
      title={<>Услуги <Accent variant="light">обработки</Accent></>}
      description="Полный прайс-лист и расчётная матрица цен на услуги обработки, вырезов, замера и монтажа в разрезе материалов."
    />
  );
}
