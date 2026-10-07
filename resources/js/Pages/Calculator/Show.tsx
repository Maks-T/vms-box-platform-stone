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
      <Head title="Онлайн-калькулятор изделий из кварца | QuartzMaster" />
      <div className="w-full max-w-[1412px] mx-auto px-4 md:px-8 py-8 md:py-12 flex-1 flex flex-col">
        <div className="w-full flex-1 relative z-10 bg-white rounded-md border border-[#E5E5E5] p-4 md:p-8 shadow-2xs text-left">
          <div id="calcAppRoot" className="w-full min-h-[650px]" />
        </div>
      </div>
    </MainLayout>
  );
}

CalculatorShow.layout = (page: any) => page;