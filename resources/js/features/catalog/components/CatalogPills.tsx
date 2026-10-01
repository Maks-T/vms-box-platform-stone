import React from 'react';
import { cn } from '@/shared/lib/utils';
import { ProductFamily } from '@/types/catalog';

interface Props {
  families: ProductFamily[];
  activeFamily: string;
  onChange: (code: string) => void;
}

export const CatalogPills = ({ families, activeFamily, onChange }: Props) => {
  const basePill = "group flex items-center px-4 py-2 rounded-lg border transition-all duration-150 shrink-0 cursor-pointer select-none font-heading text-[12px] font-semibold uppercase tracking-wider";
  const activeClass = "bg-[#08274D] border-[#08274D] text-white shadow-2xs";
  const inactiveClass = "bg-white border-slate-200 text-slate-600 hover:text-[#08274D] hover:border-slate-300 hover:bg-slate-50/80";

  return (
    <div className="mb-2">
      <div className="flex flex-nowrap md:flex-wrap items-center gap-3 md:gap-4 overflow-x-auto md:overflow-x-visible pb-4 md:pb-0 scrollbar-hide -mx-4 px-4 md:mx-0 md:px-0">
        {families.map((family) => {
          const isActive = activeFamily === family.code;

          return (
            <button
              key={family.code}
              onClick={() => onChange(family.code)}
              className={cn(basePill, isActive ? activeClass : inactiveClass)}
            >
              <span className="whitespace-nowrap">
                {family.name}
              </span>
            </button>
          );
        })}
      </div>
    </div>
  );
};