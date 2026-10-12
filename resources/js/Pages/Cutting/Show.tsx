import React, { useEffect } from 'react';
import { Head } from '@inertiajs/react';
import MainLayout from '@/layouts/MainLayout';

interface Props {
  embedUrl: string;
  widgetSlug?: string;
  initialData: {
    apiUrl: string;
    baseUrl: string;
    assetsUrl: string;
    channel?: string;
    widget?: string;
    auth?: any;
  };
}

declare global {
  interface Window {
    initCuttingPro?: (containerId: string, initialData?: any) => () => void;
    initialData?: any;
  }
}

export default function CuttingShow({
  embedUrl,
  widgetSlug = 'cutting-pro',
  initialData,
}: Props) {
  useEffect(() => {
    window.initialData = initialData;

    const scriptId = `vms-embed-script-${widgetSlug}`;
    let script = document.getElementById(scriptId) as HTMLScriptElement;

    if (!script) {
      script = document.createElement('script');
      script.id = scriptId;
      script.src = embedUrl;
      script.dataset.target = 'cuttingProAppRoot';
      script.dataset.widget = widgetSlug;
      script.async = true;
      document.body.appendChild(script);
    } else if (typeof window.initCuttingPro === 'function') {
      const container = document.getElementById('cuttingProAppRoot');
      if (container) {
        container.innerHTML = '';
      }
      window.initCuttingPro('cuttingProAppRoot', initialData);
    }

    return () => {
      const container = document.getElementById('cuttingProAppRoot');
      if (container) {
        container.innerHTML = '';
      }
    };
  }, [embedUrl, widgetSlug, initialData]);

  return (
    <MainLayout headerOverlaps={false}>
      <Head title="Профессиональный раскрой камня" />
      <div className="w-full max-w-[1600px] mx-auto px-2 md:px-6 py-6 md:py-8 flex-1 flex flex-col">
        <div className="w-full flex-1 relative z-10 bg-[#0b1220] rounded-xl border border-white/10 p-2 md:p-4 shadow-xl text-left overflow-hidden">
          <div id="cuttingProAppRoot" className="w-full min-h-[750px]" />
        </div>
      </div>
    </MainLayout>
  );
}

CuttingShow.layout = (page: any) => page;