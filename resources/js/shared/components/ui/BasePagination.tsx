import React from 'react';
import {cn} from '@/shared/lib/utils';
import { ChevronRight } from 'lucide-react';

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
                                 prevLabel = '‹',
                                 nextLabel = '›',
                                 className = ''
                               }: BasePaginationProps) {
  if (!meta || !meta.last_page || meta.last_page <= 1) {
    return null;
  }

  return (
    <div
      className={cn("border border-[#E5E5E5] rounded-md bg-white p-2 sm:p-3 flex items-center justify-center gap-1 sm:gap-2 text-sm font-semibold text-gray-700 select-none mt-10", className)}>
      {meta.links.map((link, idx) => {
        let label = link.label;

        if (label.includes('&laquo;')) label = prevLabel;
        if (label.includes('&raquo;')) label = nextLabel;

        if (!link.url) {
          return (
            <span key={idx} className="w-9 h-9 flex items-center justify-center text-gray-400">
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
              "w-9 h-9 flex items-center justify-center transition-colors cursor-pointer rounded-md",
              link.active
                ? "border-b-2 border-[#212B36] text-[#212B36] font-bold cursor-default pointer-events-none rounded-none"
                : "text-gray-600 hover:bg-[#F8F8F8] hover:text-[#212B36]"
            )}
          >
            {label}
          </button>
        );
      })}
    </div>
  );
}