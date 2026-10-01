import React from 'react';
import {Link} from '@inertiajs/react';
import {ArrowLeft} from 'lucide-react';
import {route} from "ziggy-js";
import {IconBox} from '@/shared/components/ui/IconBox';

export function ProductHeader() {
  return (
    <header className="bg-white border-b border-[#E2E6EA] sticky top-0 z-40 shadow-xs">
      <div className="max-w-[1440px] mx-auto px-4 md:px-8 h-16 flex items-center justify-between">
        <Link href={route('catalog')} className="flex items-center gap-4 group">
          <IconBox variant="light" size="sm"
                   className="group-hover:bg-[#08274D] group-hover:text-white group-hover:border-[#08274D] transition-colors">
            <ArrowLeft className="w-4 h-4"/>
          </IconBox>
          <span
            className="font-heading font-bold uppercase tracking-wider text-[#696973] group-hover:text-[#08274D] transition-colors text-[12px]">
            В каталог камня
          </span>
        </Link>
      </div>
    </header>
  );
}
