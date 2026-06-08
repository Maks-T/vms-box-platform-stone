import React, { useState } from 'react';
import GlassPanel from '@/shared/components/ui/GlassPanel';
import { toast } from "sonner";
import { ApiRequestInfo } from './types';
import InspectorHeader from './ui/InspectorHeader';
import InspectorBody from './ui/InspectorBody';

interface Props {
  requests: ApiRequestInfo[];
}

export function ApiInspector({ requests }: Props) {
  const [openIndex, setOpenIndex] = useState<number>(0);

  const copyApiUrl = (e: React.MouseEvent, endpoint: string) => {
    e.stopPropagation();
    navigator.clipboard.writeText(window.location.origin + endpoint);
    toast.success('URL API запроса скопирован!');
  };

  const copyJson = (e: React.MouseEvent, data: any) => {
    e.stopPropagation();
    navigator.clipboard.writeText(JSON.stringify(data, null, 2));
    toast.success('Тело JSON-ответа скопировано!');
  };

  const getDefaultHeaders = () => ({
    'Accept': 'application/json',
    'X-Sales-Channel': 'widget',
    'Accept-Language': localStorage.getItem('app_locale') || 'ru',
  });

  return (
    <div className="flex flex-col gap-4 w-full">
      {requests.map((req, index) => {
        const isOpen = openIndex === index;
        const currentHeaders = req.headers || getDefaultHeaders();

        return (
          <GlassPanel key={index} variant="deep" className="!bg-[#0B0F19] overflow-hidden p-0 rounded-2xl shadow-xl border-white/10 w-full">
            <InspectorHeader
              request={req}
              isOpen={isOpen}
              onToggle={() => setOpenIndex(isOpen ? -1 : index)}
              onCopyUrl={copyApiUrl}
              onCopyJson={copyJson}
            />

            {isOpen && (
              <InspectorBody headers={currentHeaders} data={req.data} />
            )}
          </GlassPanel>
        );
      })}
    </div>
  );
}

export * from './types';
