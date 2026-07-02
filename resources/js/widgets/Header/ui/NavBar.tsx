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
            <span key={item.label} className="text-white/30 cursor-not-allowed select-none text-[15px] font-medium py-4">
              {item.label}
            </span>
          );
        }

        const isActive = currentPathname === getPathname(item.href);

        const classes = cn(
          "text-[15px] py-4 relative group transition-colors",
          isActive ? "text-white font-semibold" : "text-white/80 hover:text-white font-medium"
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
                "absolute bottom-3 left-0 h-[2px] bg-primary transition-all duration-300",
                isActive ? "w-full" : "w-0 group-hover:w-full"
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
              "absolute bottom-3 left-0 h-[2px] bg-primary transition-all duration-300",
              isActive ? "w-full" : "w-0 group-hover:w-full"
            )} />
          </Link>
        );
      })}
    </nav>
  );
}
