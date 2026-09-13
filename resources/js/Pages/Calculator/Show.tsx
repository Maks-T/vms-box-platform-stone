import React, { useEffect } from 'react';
import { Head } from '@inertiajs/react';
import MainLayout from '@/layouts/MainLayout';
import SectionLayout from '@/shared/components/layouts/SectionLayout';

interface Props {
  embedUrl: string;
  widgetSlug?: string;
  initialData: {
    apiUrl: string;
    assetsUrl: string;
    baseUrl: string;
    policyLink?: string;
    ofertaLink?: string;
    state: any;
    auth: any;
    type: string | null;
    widget?: string;
  };
  currentType: string | null;
}

declare global {
  interface Window {
    initCalculator?: (containerId: string, config: any) => () => void;
    initialData?: any;
  }
}

export default function CalculatorShow({
                                         embedUrl,
                                         widgetSlug = 'cpq-stone',
                                         initialData,
                                         currentType,
                                       }: Props) {
  useEffect(() => {
    window.initialData = initialData;

    const scriptId = `vms-embed-script-${widgetSlug}`;
    let script = document.getElementById(scriptId) as HTMLScriptElement;

    if (!script) {
      script = document.createElement('script');
      script.id = scriptId;
      script.src = embedUrl;
      script.dataset.target = 'calcAppRoot';
      script.dataset.type = currentType || 'user';
      script.dataset.widget = widgetSlug;
      script.async = true;
      document.body.appendChild(script);
    } else if (typeof window.initCalculator === 'function') {
      const container = document.getElementById('calcAppRoot');
      if (container) {
        container.innerHTML = '';
      }
      window.initCalculator('calcAppRoot', initialData);
    }

    return () => {
      const container = document.getElementById('calcAppRoot');
      if (container) {
        container.innerHTML = '';
      }
    };
  }, [embedUrl, widgetSlug, initialData, currentType]);

  return (
    <MainLayout headerOverlaps={false}>
      <Head title="Онлайн-калькулятор изделий" />
      <SectionLayout containerVariant="page" className="pt-8 md:pt-12 pb-24">
        <div className="w-full relative z-10 bg-white rounded-2xl border border-border p-4 md:p-8 shadow-sm">
          <div id="calcAppRoot" className="w-full min-h-[650px]" />
        </div>
      </SectionLayout>
    </MainLayout>
  );
}

CalculatorShow.layout = (page: any) => page;