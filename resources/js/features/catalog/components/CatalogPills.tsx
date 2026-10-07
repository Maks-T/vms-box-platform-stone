import React from 'react';
import { cn } from '@/shared/lib/utils';
import { ProductFamily } from '@/types/catalog';

interface Props {
  families: ProductFamily[];
  activeFamily: string;
  onChange: (code: string) => void;
}

export const CatalogPills = ({ families, activeFamily, onChange }: Props) => {
  const basePill = "group flex items-center px-4 py-2 rounded-md border transition-all duration-150 shrink-0 cursor-pointer select-none font-heading text-[12px] font-bold uppercase tracking-wider";
  const activeClass = "bg-[#212B36] border-[#212B36] text-white shadow-2xs";
  const inactiveClass = "bg-white border-[#E5E5E5] text-gray-700 hover:text-[#212B36] hover:border-gray-400 hover:bg-[#F8F8F8]";

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