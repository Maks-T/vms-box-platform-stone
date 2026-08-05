import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { NavItem } from '@/shared/config/site';
import { cn } from '@/shared/lib/utils';

interface ExtendedNavItem extends NavItem {
  forceRefresh?: boolean;
}

export default function NavBar({ items }: { items: ExtendedNavItem[] }) {
  const { url } = usePage();
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
    <nav className="hidden lg:flex items-center gap-8 h-full">
      {items.map((item) => {
        if (item.disabled) {
          return (
            <span key={item.label} className="text-zinc-300 cursor-not-allowed select-none text-xs font-medium py-3">
              {item.label}
            </span>
          );
        }

        const isActive = currentPathname === getPathname(item.href);

        const classes = cn(
          "text-xs uppercase tracking-wider py-3 relative transition-colors font-semibold",
          isActive ? "text-zinc-900" : "text-zinc-500 hover:text-zinc-900"
        );

        if (item.forceRefresh) {
          return (
            <a
              key={item.label}
              href={item.href}
              className={classes}
            >
              {item.label}
              <span className={cn(
                "absolute bottom-0 left-0 h-[2px] bg-zinc-900 transition-all duration-200",
                isActive ? "w-full" : "w-0 hover:w-full"
              )} />
            </a>
          );
        }

        return (
          <Link
            key={item.label}
            href={item.href}
            className={classes}
          >
            {item.label}
            <span className={cn(
              "absolute bottom-0 left-0 h-[2px] bg-zinc-900 transition-all duration-200",
              isActive ? "w-full" : "w-0 hover:w-full"
            )} />
          </Link>
        );
      })}
    </nav>
  );
}