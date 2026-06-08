import React from 'react';
import { Head } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';

import MainLayout from '@/layouts/MainLayout';
import SectionLayout from '@/shared/components/layouts/SectionLayout';
import { ServicesHeroBlock } from './components/ServicesHeroBlock';
import { ServiceCard } from '@/entities/service/ui/ServiceCard';
import { ApiInspector } from '@widgets/ApiInspector';
import { useServicesMatrix } from './hooks/useServicesMatrix';

export default function ServicesIndex() {
  const { services, isLoading, endpoint } = useServicesMatrix();

  const apiRequests = [{
    label: 'Матрица услуг',
    method: 'GET',
    endpoint: endpoint,
    data: services
  }];

  return (
    <MainLayout headerOverlaps={false}>
      <Head title="Прайс-лист на услуги - VMS-NC Box" />
      <ServicesHeroBlock />

      <SectionLayout containerVariant="content" className="pt-8 md:pt-12 pb-24">
        <div className="w-full relative z-10">
          <div className="flex items-center justify-between mb-8 border-b border-border pb-6">
            <h2 className="text-2xl font-bold text-foreground tracking-tight">Ответ от API</h2>
            <span className="text-sm font-medium text-muted-foreground bg-muted px-3 py-1 rounded-full">
              {services.length} позиций
            </span>
          </div>

          {isLoading ? (
            <div className="py-32 flex justify-center">
              <Loader2 className="w-10 h-10 text-primary animate-spin" />
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-16">
              {services.map((service) => (
                <ServiceCard key={service.id} service={service} />
              ))}
            </div>
          )}

          {!isLoading && (
            <ApiInspector requests={apiRequests} />
          )}
        </div>
      </SectionLayout>
    </MainLayout>
  );
}

ServicesIndex.layout = (page: any) => page;
