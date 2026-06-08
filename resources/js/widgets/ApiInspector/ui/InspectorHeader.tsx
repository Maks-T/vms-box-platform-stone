import React from 'react';
import {Code2, ChevronDown, ChevronUp, FileJson, Link2} from 'lucide-react';
import {ApiRequestInfo} from '../types';

interface Props {
  request: ApiRequestInfo;
  isOpen: boolean;
  onToggle: () => void;
  onCopyUrl: (e: React.MouseEvent, endpoint: string) => void;
  onCopyJson: (e: React.MouseEvent, data: any) => void;
}

export default function InspectorHeader({request, isOpen, onToggle, onCopyUrl, onCopyJson}: Props) {
  return (
    <div
      onClick={onToggle}
      className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 md:px-8 border-b border-white/5 bg-white/[0.02] cursor-pointer hover:bg-white/[0.04] transition-colors w-full"
    >
      <div className="flex items-center gap-4 overflow-hidden w-full flex-1 min-w-0">
        <div className="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center shrink-0">
          <Code2 className="w-5 h-5 text-primary-light"/>
        </div>

        <div className="flex-1 min-w-0">
          <h2 className="text-[15px] font-bold text-white tracking-tight flex items-center gap-2 truncate">
            <span className="truncate">{request.label}</span>
            {isOpen ? <ChevronUp className="w-4 h-4 text-white/50 shrink-0"/> :
              <ChevronDown className="w-4 h-4 text-white/50 shrink-0"/>}
          </h2>

          <div className="flex items-center gap-2 mt-1 min-w-0">
            <span
              className="bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 px-2 py-0.5 rounded text-[10px] font-mono font-bold tracking-wider shrink-0 uppercase">
              {request.method || 'GET'}
            </span>
            <span className="text-white/50 font-mono text-xs truncate">
              {request.endpoint}
            </span>
          </div>
        </div>
      </div>

      <div className="flex items-center gap-2 w-full sm:w-auto shrink-0">
        <button
          onClick={(e) => onCopyJson(e, request.data)}
          className="flex-1 sm:flex-none justify-center shrink-0 bg-white/5 hover:bg-white/10 border border-white/10 text-white px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center gap-2"
          title="Копировать JSON"
        >
          <FileJson className="w-3.5 h-3.5 text-emerald-400"/> JSON
        </button>
        <button
          onClick={(e) => onCopyUrl(e, request.endpoint)}
          className="flex-1 sm:flex-none justify-center shrink-0 bg-white/5 hover:bg-white/10 border border-white/10 text-white px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center gap-2"
          title="Копировать URL"
        >
          <Link2 className="w-3.5 h-3.5 text-primary-light"/> URL
        </button>
      </div>
    </div>
  );
}
