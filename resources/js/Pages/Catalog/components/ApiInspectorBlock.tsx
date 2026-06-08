
import React from 'react';
import { Code2, Copy } from 'lucide-react';
import { toast } from 'sonner';

export function ApiInspectorBlock({ apiUrl }: { apiUrl: string }) {
  const copyApiUrl = () => {
    navigator.clipboard.writeText(window.location.origin + apiUrl);
    toast.success('URL API запроса скопирован!');
  };

  return (
    <div className="mb-8 p-4 bg-[#0B0F19] rounded-xl flex items-center justify-between shadow-md border border-white/5 w-full">
      <div className="flex items-center gap-3 min-w-0 flex-1">
        <Code2 className="w-5 h-5 text-[#3D98FF] shrink-0" />

        <div className="flex items-center gap-3 min-w-0 flex-1">
          <span className="bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 px-2 py-0.5 rounded text-xs font-mono font-bold shrink-0">
            GET
          </span>
          {}
          <span className="text-zinc-200 font-mono text-sm truncate">
            {apiUrl}
          </span>
        </div>
      </div>

      <button
        onClick={copyApiUrl}
        className="shrink-0 ml-4 bg-white/5 hover:bg-white/10 border border-white/10 text-white px-4 py-2 rounded-lg text-xs font-medium transition-colors flex items-center gap-2"
      >
        <Copy className="w-3.5 h-3.5" /> Копировать
      </button>
    </div>
  );
}
