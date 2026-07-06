import React, {useEffect, useRef, useState} from 'react';
import {Head, usePage} from '@inertiajs/react';
import {Loader2} from 'lucide-react';
import MainLayout from '@/layouts/MainLayout';
import SectionLayout from '@/shared/components/layouts/SectionLayout';

interface Props {
  assets: {
    js: string | null;
    css: string | null;
  };
  initialData: {
    apiUrl: string;
    assetsUrl: string;
    policyLink?: string;
    ofertaLink?: string;
    state: any;
  };
  currentType: string | null;
}

declare global {
  interface Window {
    initCalculator?: (containerId: string, config: any) => () => void;
  }
}

export default function CalculatorShow({assets, initialData, currentType}: Props) {
  const {auth} = usePage().props as any;
  const [isWidgetReady, setIsWidgetReady] = useState(false);

  const unmountFnRef = useRef<(() => void) | null>(null);

  useEffect(() => {
    if (!assets.js) {
      console.error('Калькулятор: JS-файл точки входа не найден в manifest.json');
      return;
    }

    setIsWidgetReady(false);

    const initWidget = () => {
      if (window.initCalculator) {
        if (unmountFnRef.current) {
          unmountFnRef.current();
          unmountFnRef.current = null;
        }

        const container = document.getElementById('calcAppRoot');
        if (container) {
          container.innerHTML = '';
        }

        const fullConfig = {
          ...initialData,
          user: auth?.client ?? null,
          employee: auth?.employee ?? null,
          type: currentType,
        };

        unmountFnRef.current = window.initCalculator('calcAppRoot', fullConfig);
        setIsWidgetReady(true);
      }
    };

    const existingScript = document.getElementById('external-calc-js');

    if (!existingScript) {
      if (assets.css && !document.getElementById('external-calc-css')) {
        const link = document.createElement('link');
        link.id = 'external-calc-css';
        link.rel = 'stylesheet';
        link.href = assets.css;
        document.head.appendChild(link);
      }

      const script = document.createElement('script');
      script.id = 'external-calc-js';
      script.src = assets.js;
      script.async = true;
      script.onload = initWidget;
      document.body.appendChild(script);
    } else {
      initWidget();
    }

    return () => {
      if (unmountFnRef.current) {
        unmountFnRef.current();
        unmountFnRef.current = null;
      }
    };
  }, [assets, initialData, currentType, auth?.user]);

  return (
    <MainLayout headerOverlaps={false}>
      <Head title="Онлайн-калькулятор изделий - VMS-NC"/>
      <SectionLayout containerVariant="page" className="pt-8 md:pt-12 pb-24">
        <div className="w-full relative z-10 bg-white rounded-2xl border border-border p-4 md:p-8 shadow-sm">
          <div className="relative w-full min-h-[650px]">
            {!isWidgetReady && (
              <div className="absolute inset-0 z-10 bg-white flex flex-col items-center justify-center rounded-2xl">
                <Loader2 className="w-10 h-10 text-primary animate-spin mb-4"/>
                <p className="text-slate-500 font-medium text-sm">
                  Загрузка модулей калькулятора...
                </p>
              </div>
            )}
            <div id="calcAppRoot" className="w-full min-h-[650px]"/>
          </div>
        </div>
      </SectionLayout>
    </MainLayout>
  );
}

CalculatorShow.layout = (page: any) => page;
