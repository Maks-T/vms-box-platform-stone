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
      className={cn("mt-12 flex flex-wrap items-center justify-center gap-1 md:gap-2 text-[14px] font-medium", className)}>
      {meta.links.map((link, idx) => {
        let label = link.label;

        if (label.includes('&laquo;')) label = prevLabel;
        if (label.includes('&raquo;')) label = nextLabel;

        if (!link.url) {
          return (
            <span key={idx} className="px-3 py-2 text-slate-300">
              {label}
            </span>
          );
        }

        const urlObj = new URL(link.url, 'http://localhost');
        const pageNum = Number(urlObj.searchParams.get('page'));

        return (
          <button
            key={idx}
            onClick={() => onPageChange(pageNum)}
            className={cn(
              "px-3.5 py-2 rounded-lg transition-colors",
              link.active
                ? "bg-sky-50 text-sky-700 font-bold cursor-default pointer-events-none"
                : "text-slate-500 hover:bg-slate-50 hover:text-slate-900"
            )}
          >
            {label}
          </button>
        );
      })}
    </div>
  );
}