import React from 'react';
import SectionLayout from '@/shared/components/layouts/SectionLayout';
import WaveBackground from '@/shared/components/ui/WaveBackground';
import { H1, Text, Accent } from '@/shared/components/ui/Typography';
import StatusBadge from '@/shared/components/ui/StatusBadge';

export function BootstrapHeroBlock() {
  return (
    <SectionLayout
      bg="bg-[#0B0F19]"
      bgElement={<WaveBackground />}
      containerVariant="content"
      className="pt-2 md:pt-4"
    >
      <div className="flex flex-col pt-12 pb-16 lg:pt-20 lg:pb-24 max-w-3xl">
        <StatusBadge variant="blue" className="mb-6 self-start">
          Инициализация виджета
        </StatusBadge>

        <H1 className="mb-6">
          Bootstrap <Accent variant="light">API</Accent>
        </H1>

        <Text variant="leadDark" className="text-slate-400 leading-relaxed max-w-2xl">
          Единая точка входа. Этот эндпоинт возвращает все базовые настройки,
          глобальные справочники и плоское дерево семейств товаров для построения интерфейса калькулятора.
        </Text>
      </div>
    </SectionLayout>
  );
}
