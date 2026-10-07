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
    if (!urlStr || urlStr.startsWith('#') || urlStr.startsWith('http')) return '';
    try {
      const parsed = new URL(urlStr, window.location.origin);
      return parsed.pathname;
    } catch {
      return urlStr.split('?')[0];
    }
  };

  return (
    <nav className="h-[52px] flex items-center justify-between text-[13px] font-semibold text-gray-200 uppercase tracking-wide w-full select-none">
      <div className="flex items-center gap-7 h-full">
        {items.map((item) => {
          const isMainCatalog = item.label.toLowerCase().includes('каталог');
          const isActive = currentPathname === getPathname(item.href);

          /* Задизейбленные пункты (О компании, Контакты) */
          if (item.disabled) {
            return (
              <span
                key={item.label}
                className="text-gray-400/50 cursor-not-allowed select-none font-heading font-semibold text-[13px] uppercase tracking-wide py-4"
              >
                {item.label}
              </span>
            );
          }

          /* Пункт «Каталог камня» с динамическим выпадающим списком из API */
          if (isMainCatalog) {
            return (
              <div key={item.label} className="relative h-full flex items-center" ref={catalogRef}>
                <button
                  type="button"
                  onClick={() => setIsCatalogOpen((prev) => !prev)}
                  className={cn(
                    "font-heading font-semibold text-[13px] uppercase tracking-wide py-4 relative group transition-colors select-none flex items-center gap-1.5 cursor-pointer bg-transparent border-0",
                    isCatalogOpen || isActive ? "text-[#25CED1]" : "text-gray-200 hover:text-[#25CED1]"
                  )}
                  aria-expanded={isCatalogOpen}
                >
                  <MenuIcon className="w-3.5 h-3.5 text-gray-300 group-hover:text-[#25CED1]" />
                  <span>{item.label}</span>
                  <ChevronDown
                    className={cn(
                      "w-3.5 h-3.5 text-gray-400 transition-transform duration-200",
                      isCatalogOpen ? "rotate-180 text-[#25CED1]" : "group-hover:text-[#25CED1]"
                    )}
                  />
                  <span
                    className={cn(
                      "absolute bottom-0 left-0 h-[2px] bg-[#25CED1] transition-all duration-300",
                      isActive || isCatalogOpen ? "w-full" : "w-0 group-hover:w-full"
                    )}
                  />
                </button>

                {/* Выпадающее меню с реальными разделами VMS-NC */}
                {isCatalogOpen && (
                  <div className="absolute top-[calc(100%-4px)] left-0 z-50 bg-white rounded-md border border-[#E5E5E5] shadow-2xl p-6 min-w-[720px] xl:min-w-[820px] animate-in fade-in-0 zoom-in-95 duration-150">
                    <div className="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6 pb-4 border-b border-[#E5E5E5]">
                      {families.length > 0 ? (
                        families.map((family) => (
                          <div key={family.code} className="flex flex-col text-left">
                            <Link
                              href={`${route('catalog')}?family=${family.code}`}
                              onClick={() => setIsCatalogOpen(false)}
                              className="font-heading font-bold text-[12px] uppercase text-[#212B36] hover:text-[#25CED1] tracking-wider pb-1 mb-2 block border-b border-[#E5E5E5] transition-colors"
                            >
                              {family.name}
                            </Link>
                            {family.types && family.types.length > 0 && (
                              <ul className="flex flex-col gap-1 text-[13px] text-gray-600">
                                {family.types.map((t) => (
                                  <li key={t.code}>
                                    <Link
                                      href={`${route('catalog')}?family=${family.code}&product_type=${t.code}`}
                                      onClick={() => setIsCatalogOpen(false)}
                                      className="hover:text-[#25CED1] hover:translate-x-0.5 transition-all block py-0.5"
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
                        <div className="text-xs text-gray-400 italic py-2 col-span-full">
                          Загрузка категорий...
                        </div>
                      )}
                    </div>

                    <div className="pt-3.5 flex items-center justify-between text-xs">
                      <Link
                        href={route('catalog')}
                        onClick={() => setIsCatalogOpen(false)}
                        className="font-heading font-bold text-[#212B36] hover:text-[#25CED1] uppercase tracking-wider flex items-center gap-1.5 transition-colors"
                      >
                        <span>Смотреть весь каталог камня</span>
                        <ArrowRight className="w-3.5 h-3.5 text-[#25CED1]" />
                      </Link>
                      <Link
                        href={route('calculator.show')}
                        onClick={() => setIsCatalogOpen(false)}
                        className="font-heading font-bold text-[#25CED1] hover:text-[#148587] uppercase tracking-wider flex items-center gap-1.5 transition-colors"
                      >
                        <Calculator className="w-3.5 h-3.5" />
                        <span>Онлайн-калькулятор изделий</span>
                      </Link>
                    </div>
                  </div>
                )}
              </div>
            );
          }

          /* Обычный активный пункт (Калькулятор) */
          const classes = cn(
            "font-heading font-semibold text-[13px] uppercase tracking-wide py-4 relative group transition-colors select-none",
            isActive ? "text-[#25CED1]" : "text-gray-200 hover:text-[#25CED1]"
          );

          if (item.forceRefresh) {
            return (
              <a key={item.label} href={item.href} className={classes}>
                {item.label}
                <span
                  className={cn(
                    "absolute bottom-0 left-0 h-[2px] bg-[#25CED1] transition-all duration-300",
                    isActive ? "w-full" : "w-0 group-hover:w-full"
                  )}
                />
              </a>
            );
          }

          return (
            <Link key={item.label} href={item.href} className={classes}>
              {item.label}
              <span
                className={cn(
                  "absolute bottom-0 left-0 h-[2px] bg-[#25CED1] transition-all duration-300",
                  isActive ? "w-full" : "w-0 group-hover:w-full"
                )}
              />
            </Link>
          );
        })}
      </div>
    </nav>
  );
}