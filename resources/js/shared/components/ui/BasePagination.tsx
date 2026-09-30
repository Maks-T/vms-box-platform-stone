import React from 'react';
import {cn} from '@/shared/lib/utils';

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginationMeta {
  current_page: number;
  last_page: number;
  total: number;
  links: PaginationLink[];
}

interface BasePaginationProps {
  meta: PaginationMeta | null;
  onPageChange: (page: number) => void;
  prevLabel?: string;
  nextLabel?: string;
  className?: string;
}

export function BasePagination({
                                 meta,
                                 onPageChange,
                                 prevLabel = '‹ Назад',
                                 nextLabel = 'Вперед ›',
                                 className = ''
                               }: BasePaginationProps) {
  if (!meta || !meta.last_page || meta.last_page <= 1) {
    return null;
  }

  return (
    <div
      className={cn("mt-12 flex flex-wrap items-center justify-center gap-1.5 md:gap-2 select-none", className)}>
      {meta.links.map((link, idx) => {
        let label = link.label;

        if (label.includes('&laquo;')) label = prevLabel;
        if (label.includes('&raquo;')) label = nextLabel;

        if (!link.url) {
          return (
            <span key={idx} className="min-w-[38px] h-[38px] flex items-center justify-center text-[#8B9198] text-sm font-medium">
              {label}
            </span>
          );
        }

        const urlObj = new URL(link.url, 'http://localhost');
        const pageNum = Number(urlObj.searchParams.get('page'));
        const isNavButton = label === prevLabel || label === nextLabel;

        return (
          <button
            key={idx}
            onClick={() => onPageChange(pageNum)}
            className={cn(
              "transition-all duration-200 cursor-pointer",
              isNavButton
                ? "px-4 py-2 rounded-lg border border-[#E2E6EA] bg-white text-[#08274D] hover:text-[#EF5042] hover:border-[#08274D] font-heading font-bold text-xs uppercase tracking-wider"
                : link.active
                  ? "min-w-[38px] h-[38px] rounded-lg bg-[#08274D] text-white border border-[#08274D] font-heading font-bold text-sm shadow-xs flex items-center justify-center cursor-default pointer-events-none"
                  : "min-w-[38px] h-[38px] rounded-lg border border-[#E2E6EA] bg-white text-[#1E252D] hover:text-[#08274D] hover:border-[#08274D] hover:bg-[#F0F2F5] font-medium text-sm flex items-center justify-center"
            )}
          >
            {label}
          </button>
        );
      })}
    </div>
  );
}