import React, { useState, useEffect, useRef } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { NavItem } from '@/shared/config/site';
import { cn } from '@/shared/lib/utils';
import { ChevronDown, Menu as MenuIcon, ArrowRight, Tag, Calculator, Sparkles } from 'lucide-react';
import { route } from 'ziggy-js';

interface ExtendedNavItem extends NavItem {
  forceRefresh?: boolean;
}

export default function NavBar({ items }: { items: ExtendedNavItem[] }) {
  const { url } = usePage();
  const currentPathname = url.split('?')[0];
  const [isCatalogOpen, setIsCatalogOpen] = useState(false);
  const catalogRef = useRef<HTMLDivElement>(null);

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

        /* Специальный интерактивный пункт «Каталог камня» с выпадающим меню */
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

              {/* Выпадающее окно меню каталога */}
              {isCatalogOpen && (
                <div className="absolute top-[calc(100%-6px)] left-0 z-50 bg-white rounded-2xl border border-[#E2E6EA] shadow-2xl p-6 lg:p-7 min-w-[700px] xl:min-w-[820px] animate-in fade-in-0 zoom-in-95 duration-200">
                  <div className="grid grid-cols-3 gap-6 lg:gap-8 pb-5 border-b border-[#E2E6EA]">
                    {/* Колонка 1: Натуральный камень */}
                    <div className="flex flex-col">
                      <h4 className="font-heading font-extrabold text-[12px] uppercase text-[#08274D] tracking-wider pb-2 border-b border-[#E2E6EA] mb-2.5 flex items-center gap-1.5">
                        <Sparkles className="w-3.5 h-3.5 text-[#9B6A38]" /> Натуральный камень
                      </h4>
                      <ul className="flex flex-col gap-1.5 text-[13px] font-sans">
                        <li><Link href={`${route('catalog')}?family=stone&product_type=marble`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Мрамор</Link></li>
                        <li><Link href={`${route('catalog')}?family=stone&product_type=granite`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Гранит</Link></li>
                        <li><Link href={`${route('catalog')}?family=stone&product_type=quartzite`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Кварцит</Link></li>
                        <li><Link href={`${route('catalog')}?family=stone&product_type=onyx`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Оникс</Link></li>
                        <li><Link href={`${route('catalog')}?family=stone&product_type=travertine`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Травертин</Link></li>
                        <li><Link href={`${route('catalog')}?family=stone&product_type=labradorite`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Лабрадорит</Link></li>
                      </ul>
                    </div>

                    {/* Колонка 2: Искусственный камень */}
                    <div className="flex flex-col">
                      <h4 className="font-heading font-extrabold text-[12px] uppercase text-[#08274D] tracking-wider pb-2 border-b border-[#E2E6EA] mb-2.5">
                        Искусственный камень
                      </h4>
                      <ul className="flex flex-col gap-1.5 text-[13px] font-sans">
                        <li><Link href={`${route('catalog')}?family=agglomerate`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Кварцевый агломерат</Link></li>
                        <li><Link href={`${route('catalog')}?family=acrylic`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Акриловый камень</Link></li>
                        <li><Link href={`${route('catalog')}?family=ceramics`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Керамогранит</Link></li>
                      </ul>

                      <h4 className="font-heading font-extrabold text-[12px] uppercase text-[#08274D] tracking-wider pb-2 border-b border-[#E2E6EA] mt-4 mb-2.5">
                        Специальные предложения
                      </h4>
                      <ul className="flex flex-col gap-1.5 text-[13px] font-sans">
                        <li>
                          <Link href={`${route('catalog')}?family=stone&sale=true`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] flex items-center justify-between py-0.5">
                            <span>Остатки слэбов</span>
                            <span className="bg-[#EF5042] text-white text-[9px] font-heading font-extrabold px-1.5 py-0.2 rounded uppercase">Sale</span>
                          </Link>
                        </li>
                      </ul>
                    </div>

                    {/* Колонка 3: Изделия из камня */}
                    <div className="flex flex-col">
                      <h4 className="font-heading font-extrabold text-[12px] uppercase text-[#08274D] tracking-wider pb-2 border-b border-[#E2E6EA] mb-2.5">
                        Изделия из камня
                      </h4>
                      <ul className="flex flex-col gap-1.5 text-[13px] font-sans">
                        <li><Link href={`${route('catalog')}?product_type=countertop`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Столешницы для кухни</Link></li>
                        <li><Link href={`${route('catalog')}?product_type=sills`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Подоконники из камня</Link></li>
                        <li><Link href={`${route('catalog')}?product_type=stairs`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Лестницы и ступени</Link></li>
                        <li><Link href={`${route('catalog')}?product_type=fireplaces`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Камины и порталы</Link></li>
                        <li><Link href={`${route('catalog')}?product_type=sinks`} onClick={() => setIsCatalogOpen(false)} className="text-[#486581] hover:text-[#EF5042] hover:translate-x-1 transition-all block py-0.5">Мойки и раковины</Link></li>
                      </ul>
                    </div>
                  </div>

                  {/* Нижняя полоса быстрого перехода */}
                  <div className="pt-4 flex items-center justify-between text-xs">
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
