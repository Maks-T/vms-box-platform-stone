import React from 'react';

interface Props {
  headers: Record<string, string>;
  data: any;
}

export default function InspectorBody({headers, data}: Props) {
  return (
    <div className="flex flex-col bg-[#0B0F19]">
      <div className="p-5 md:px-8 md:py-6 border-b border-white/5 bg-black/20">
        <h4 className="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-4">
          Request Headers
        </h4>
        <div className="grid grid-cols-1 gap-2">
          {Object.entries(headers).map(([key, val]) => (
            <div key={key} className="flex text-[13px] font-mono">
              <span className="text-[#3D98FF] w-40 shrink-0">{key}:</span>
              <span className="text-emerald-400/90">{val}</span>
            </div>
          ))}
        </div>
      </div>

      <div className="overflow-x-auto custom-scrollbar max-h-[500px] p-5 md:p-8">
        <h4 className="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-4">
          Response Body
        </h4>
        <pre className="text-xs text-emerald-400/90 font-mono leading-relaxed">
          {JSON.stringify(data, null, 2)}
        </pre>
      </div>
    </div>
  );
}
