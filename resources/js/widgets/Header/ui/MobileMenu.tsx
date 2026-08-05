import React from 'react';
import {Link, usePage} from '@inertiajs/react';
import {X, BookOpen} from 'lucide-react';
import {cn} from '@/shared/lib/utils';
import {Logo} from '@/shared/components/ui/Logo';
import {NavItem} from '@/shared/config/site';

interface ExtendedNavItem extends NavItem {
  forceRefresh?: boolean;
}

interface MobileMenuProps {
  isOpen: boolean;
  onClose: () => void;
  items: ExtendedNavItem[];
  isDev: boolean;
}

export default function MobileMenu({isOpen, onClose, items, isDev}: MobileMenuProps) {
  if (!isOpen) return null;

  const {url} = usePage();
  const currentPathname = url.split('?')[0];

  const getPathname = (urlStr: string) => {
    if (!urlStr || urlStr.startsWith('#')) return '';
    try {
      const parsed = new URL(urlStr, window.location.origin);
      return parsed.pathname;
    } catch {
      return urlStr.split('?')[0];
    }
  };

  return (
    <div className={cn(
      "fixed inset-0 z-[100] bg-white flex flex-col transition-transform duration-300 ease-in-out lg:hidden",
      isOpen ? "translate-x-0" : "translate-x-full"
    )}>
      <div className="px-6 py-4 border-b border-border flex justify-between items-center shrink-0">
        <Logo variant="orange-dark" onClick={onClose}/>
        <button
          className="w-9 h-9 bg-zinc-100 rounded border border-zinc-200 flex items-center justify-center text-zinc-800 active:scale-95 transition-all"
          onClick={onClose}
        >
          <X className="w-5 h-5"/>
        </button>
      </div>

      <nav className="flex flex-col px-6 py-4 flex-1">
        {items.map((item) => {
          if (item.disabled) {
            return (
              <span key={item.label}
                    className="py-3 text-sm text-zinc-300 font-medium border-b border-zinc-100 cursor-not-allowed select-none">
                {item.label}
              </span>
            );
          }

          const isActive = currentPathname === getPathname(item.href);

          const classes = cn(
            "py-3 text-sm uppercase tracking-wider border-b border-zinc-100 transition-colors",
            isActive ? "text-zinc-900 font-bold" : "text-zinc-600 font-medium"
          );

          if (item.forceRefresh) {
            return (
              <a key={item.label} href={item.href} className={classes}>
                {item.label}
              </a>
            );
          }

          return (
            <Link key={item.label} href={item.href} className={classes} onClick={onClose}>
              {item.label}
            </Link>
          );
        })}

        {isDev && (
          <a
            href="/docs/api"
            target="_blank"
            rel="noreferrer"
            className="mt-6 flex items-center justify-center gap-2 w-full py-3 rounded bg-zinc-900 text-white font-bold text-xs tracking-widest uppercase"
          >
            <BookOpen className="w-4 h-4"/>
            Swagger API
          </a>
        )}
      </nav>
    </div>
  );
}