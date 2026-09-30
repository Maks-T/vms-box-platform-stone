import React from 'react';
import { cn } from '@/shared/lib/utils';
import { ProductFamily } from '@/types/catalog';

interface Props {
  families: ProductFamily[];
  activeFamily: string;
  onChange: (code: string) => void;
}

export const CatalogPills = ({ families, activeFamily, onChange }: Props) => {
  const basePill = "group flex items-center gap-3 h-[46px] md:h-[52px] rounded-full border transition-all duration-300 shrink-0 overflow-hidden cursor-pointer pl-2 pr-6 md:pr-7 select-none font-heading";

  const activeClass = "bg-[#08274D] border-[#08274D] text-white shadow-md";
  const inactiveClass = "bg-white border-[#E2E6EA] text-[#696973] hover:border-[#9B6A38] hover:text-[#08274D] shadow-sm";

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
              <div className={cn(
                "w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0 transition-colors",
                isActive
                  ? "bg-[#9B6A38] text-white shadow-inner"
                  : "bg-[#F0F2F5] text-[#08274D] group-hover:bg-[#9B6A38]/15 group-hover:text-[#9B6A38]"
              )}>
                {family.name.charAt(0)}
              </div>
              <span className="text-[12px] md:text-[13px] font-bold uppercase tracking-wider whitespace-nowrap">
                {family.name}
              </span>
            </button>
          );
        })}
      </div>
    </div>
  );
};