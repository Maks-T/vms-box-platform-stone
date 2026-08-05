import React from 'react';
import {cn} from '@/shared/lib/utils';
import {BootstrapFamily} from '@/types/catalog';

interface Props {
  families: BootstrapFamily[];
  activeFamily: string;
  onChange: (code: string) => void;
  isLoading?: boolean;
}

export const CatalogPills = ({families, activeFamily, onChange}: Props) => {
  if (!families || families.length === 0) {
    return (
      <div className="w-full bg-white border border-border rounded-md p-3 my-2 shadow-sm">
        <div className="flex items-center justify-between overflow-x-auto no-scrollbar gap-3 sm:gap-4">
          {[1, 2, 3, 4, 5, 6, 7].map((i) => (
            <div
              key={i}
              className="flex flex-col items-center justify-center gap-2 py-2 px-3.5 rounded shrink-0 flex-1 min-w-[100px] animate-pulse"
            >
              <div className="w-8 h-8 rounded bg-zinc-100"/>
              <div className="h-3 bg-zinc-100 rounded-sm w-16"/>
            </div>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="w-full bg-white border border-border rounded-md p-3 my-2 shadow-sm">
      <div className="flex items-center justify-between overflow-x-auto no-scrollbar gap-3 sm:gap-4">
        {families.map((family) => {
          const isActive = activeFamily === family.code;

          return (
            <button
              key={family.code}
              onClick={() => onChange(family.code)}
              className={cn(
                "flex flex-col items-center justify-center gap-2 py-2 px-3.5 rounded transition-all cursor-pointer shrink-0 flex-1 min-w-[100px]",
                isActive
                  ? "bg-zinc-100 text-zinc-900 font-bold border border-zinc-200"
                  : "text-zinc-500 hover:text-zinc-900 hover:bg-zinc-50 border border-transparent"
              )}
            >
              <div className="w-8 h-8 flex items-center justify-center shrink-0">
                <img
                  src={`/images/categories/${family.code}.png`}
                  alt={family.name}
                  className="w-full h-full object-contain"
                  onError={(e) => {
                    (e.target as HTMLElement).style.display = 'none';
                  }}
                />
              </div>
              <span className="text-[11px] font-medium tracking-tight text-center leading-tight whitespace-nowrap">
                {family.name}
              </span>
              <span className={cn(
                "h-[2px] w-6 transition-all duration-200 rounded-full",
                isActive ? "bg-[#B92B3A]" : "bg-transparent"
              )}/>
            </button>
          );
        })}
      </div>
    </div>
  );
};