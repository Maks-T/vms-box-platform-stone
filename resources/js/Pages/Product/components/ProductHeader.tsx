import React from 'react';
import {Link} from '@inertiajs/react';
import {ArrowLeft} from 'lucide-react';
import {route} from "ziggy-js";
import {IconBox} from '@/shared/components/ui/IconBox';

export function ProductHeader() {
  return (
    <header className="bg-background border-b border-border sticky top-0 z-50 shadow-sm">
      <div className="max-w-[1920px] mx-auto px-4 md:px-8 h-20 flex items-center justify-between">
        <Link href={route('catalog')} className="flex items-center gap-4 group">
          <IconBox variant="light" size="default"
                   className="group-hover:bg-primary group-hover:text-primary-foreground group-hover:border-primary">
            <ArrowLeft className="w-5 h-5"/>
          </IconBox>
          <span
            className="font-bold uppercase tracking-widest text-muted-foreground group-hover:text-foreground transition-colors text-[13px]">
            В каталог (Sandbox)
          </span>
        </Link>
      </div>
    </header>
  );
}