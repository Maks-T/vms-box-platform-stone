import React, { useState, useEffect, useRef } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { NavItem } from '@/shared/config/site';
import { cn } from '@/shared/lib/utils';
import { ChevronDown, Menu as MenuIcon, ArrowRight, Calculator } from 'lucide-react';
import { route } from 'ziggy-js';
import { bootstrapApi } from '@/shared/api/bootstrap.api';
import { BootstrapFamily } from '@/types/catalog';

interface ExtendedNavItem extends NavItem {
  forceRefresh?: boolean;
}

export default function NavBar({ items }: { items: ExtendedNavItem[] }) {
  const { url } = usePage();
  const currentPathname = url.split('?')[0];
  const [isCatalogOpen, setIsCatalogOpen] = useState(false);
  const [families, setFamilies] = useState<BootstrapFamily[]>([]);
  const catalogRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    bootstrapApi.getConfig().then((cfg) => {
      if (cfg?.families) {
        setFamilies(cfg.families);
      }
    });
  }, []);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (catalogRef.current && !catalogRef.current.contains(event.target as Node)) {
        setIsCatalogOpen(false);
      }
    };
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        setIsCatalogOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    document.addEventListener('keydown', handleKeyDown);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, []);

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
    <nav className="hidden lg:flex items-center gap-5 xl:gap-7 h-full">
      {items.map((item) => {
        if (item.disabled) {
          return (
            <span key={item.label} className="text-[#8B9198]/40 cursor-not-allowed select-none font-heading font-bold text-[12px] xl:text-[13px] uppercase tracking-wider py-4">
              {item.label}
            </span>
          );
        }

        const isActive = currentPathname === getPathname(item.href);
        const hasDropdown = item.label.includes('Каталог') || item.label.includes('Услуги') || item.label.includes('Изделия');
        const isMainCatalog = item.label.includes('Каталог');

        const classes = cn(
          "font-heading font-bold text-[12px] xl:text-[13px] uppercase tracking-wider py-4 relative group transition-colors select-none flex items-center gap-1.5",
          isActive ? "text-[#08274D]" : "text-[#1E252D] hover:text-[#EF5042]"
        );

        /* Динамический пункт «Каталог камня» с данными из API */
        if (isMainCatalog) {
          return (
            <div key={item.label} className="relative h-full flex items-center" ref={catalogRef}>
              <button
                type="button"
                onClick={() => setIsCatalogOpen(prev => !prev)}
                className={cn(classes, "cursor-pointer bg-transparent border-0")}
                aria-expanded={isCatalogOpen}
              >
                <MenuIcon className="w-3.5 h-3.5 text-[#08274D] group-hover:text-[#EF5042]" />
                {item.label}
                <ChevronDown className={cn(
                  "w-3.5 h-3.5 text-[#8B9198] transition-transform duration-300",
                  isCatalogOpen ? "rotate-180 text-[#EF5042]" : "group-hover:text-[#EF5042]"
                )} />
                <span className={cn(
                  "absolute bottom-2 left-0 h-[2px] bg-[#EF5042] transition-all duration-300",
                  (isActive || isCatalogOpen) ? "w-full" : "w-0 group-hover:w-full"
                )} />
              </button>

              {/* Элегантное выпадающее меню на основе API */}
              {isCatalogOpen && (
                <div className="absolute top-[calc(100%-6px)] left-0 z-50 bg-white rounded-xl border border-[#E2E6EA] shadow-xl p-5 md:p-6 min-w-[620px] xl:min-w-[740px] animate-in fade-in-0 zoom-in-95 duration-150">
                  <div className="grid grid-cols-2 md:grid-cols-3 gap-5 pb-4 border-b border-[#E2E6EA]">
                    {families.length > 0 ? (
                      families.map((family) => (
                        <div key={family.code} className="flex flex-col">
                          <Link
                            href={`${route('catalog')}?family=${family.code}`}
                            onClick={() => setIsCatalogOpen(false)}
                            className="font-heading font-bold text-[12px] uppercase text-[#08274D] hover:text-[#EF5042] tracking-wider pb-1.5 border-b border-[#E2E6EA] mb-2 block transition-colors"
                          >
                            {family.name}
                          </Link>
                          {family.types && family.types.length > 0 && (
                            <ul className="flex flex-col gap-1 text-[13px]">
                              {family.types.map((t) => (
                                <li key={t.code}>
                                  <Link
                                    href={`${route('catalog')}?family=${family.code}&product_type=${t.code}`}
                                    onClick={() => setIsCatalogOpen(false)}
                                    className="text-[#486581] hover:text-[#EF5042] hover:translate-x-0.5 transition-all block py-0.5"
                                  >
                                    {t.name}
                                  </Link>
                                </li>
                              ))}
                            </ul>
                          )}
                        </div>
                      ))
                    ) : (
                      <div className="text-xs text-muted-foreground italic col-span-full py-2">
                        Загрузка категорий...
                      </div>
                    )}
                  </div>

                  <div className="pt-3.5 flex items-center justify-between text-xs">
                    <Link href={route('catalog')} onClick={() => setIsCatalogOpen(false)} className="font-heading font-bold text-[#08274D] hover:text-[#EF5042] flex items-center gap-1.5 uppercase tracking-wider">
                      Смотреть весь каталог камня <ArrowRight className="w-3.5 h-3.5" />
                    </Link>
                    <Link href={route('calculator.show')} onClick={() => setIsCatalogOpen(false)} className="font-heading font-bold text-[#08274D] hover:text-[#EF5042] flex items-center gap-1.5 uppercase tracking-wider">
                      <Calculator className="w-3.5 h-3.5 text-[#9B6A38]" /> Онлайн-калькулятор изделий
                    </Link>
                  </div>
                </div>
              )}
            </div>
          );
        }

        if (item.forceRefresh) {
          return (
            <a
              key={item.label}
              href={item.href}
              className={classes}
            >
              {isMainCatalog && <MenuIcon className="w-3.5 h-3.5 text-[#08274D] group-hover:text-[#EF5042]" />}
              {item.label}
              {hasDropdown && <ChevronDown className="w-3.5 h-3.5 text-[#8B9198] group-hover:text-[#EF5042] transition-colors" />}
              <span className={cn(
                "absolute bottom-2 left-0 h-[2px] bg-[#EF5042] transition-all duration-300",
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
            {isMainCatalog && <MenuIcon className="w-3.5 h-3.5 text-[#08274D] group-hover:text-[#EF5042]" />}
            {item.label}
            {hasDropdown && <ChevronDown className="w-3.5 h-3.5 text-[#8B9198] group-hover:text-[#EF5042] transition-colors" />}
            <span className={cn(
              "absolute bottom-2 left-0 h-[2px] bg-[#EF5042] transition-all duration-300",
              isActive ? "w-full" : "w-0 group-hover:w-full"
            )} />
          </Link>
        );
      })}
    </nav>
  );
}
