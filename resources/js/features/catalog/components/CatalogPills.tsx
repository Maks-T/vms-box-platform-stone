import React from 'react';
import { cn } from '@/shared/lib/utils';
import { ProductFamily } from '@/types/catalog';

interface Props {
  families: ProductFamily[];
  activeFamily: string;
  onChange: (code: string) => void;
}

export const CatalogPills = ({ families, activeFamily, onChange }: Props) => {
  
  const basePill = "group flex items-center gap-3 h-[46px] md:h-[54px] rounded-full border transition-all duration-300 shrink-0 overflow-hidden cursor-pointer pl-2 pr-6 md:pr-8";

  
  const activeClass = "bg-primary border-primary text-primary-foreground shadow-md";
  const inactiveClass = "bg-card border-border text-muted-foreground hover:border-primary/40 hover:text-foreground shadow-sm";

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
                  ? "bg-primary-foreground text-primary"
                  : "bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary"
              )}>
                {family.name.charAt(0)}
              </div>
              <span className="text-[12px] md:text-[14px] font-bold uppercase tracking-widest whitespace-nowrap">
                {family.name}
              </span>
            </button>
          );
        })}
      </div>
    </div>
  );
};