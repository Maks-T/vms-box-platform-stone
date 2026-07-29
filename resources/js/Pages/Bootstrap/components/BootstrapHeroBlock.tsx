import React from 'react';
import { Accent } from '@/shared/components/ui/Typography';
import {PageHero} from "@shared/components/ui/PageHero";

export function BootstrapHeroBlock() {
  return (
    <PageHero
      badge="Инициализация виджета"
      title={<>Bootstrap <Accent variant="light">API</Accent></>}
      description="Единая точка входа. Этот эндпоинт возвращает все базовые настройки, глобальные справочники и плоское дерево семейств товаров для построения интерфейса калькулятора."
    />
  );
}