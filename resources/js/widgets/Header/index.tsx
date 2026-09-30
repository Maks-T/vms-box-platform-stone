import React, { useState, useEffect } from 'react';
import { Menu, BookOpen, ShieldCheck, Heart, Search } from 'lucide-react';
import { Logo } from '@/shared/components/ui/Logo';
import { siteConfig } from '@/shared/config/site';
import { usePage } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { useFavorites } from '@/store/useFavorites';

import TopBar from './ui/TopBar';
import NavBar from './ui/NavBar';
import MobileMenu from './ui/MobileMenu';
import { checkDevMode } from '@/shared/lib/dev';

export default function Header() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [locale, setLocale] = useState(localStorage.getItem('app_locale') || 'ru');

  const { auth } = usePage().props as any;
  const isEmployee = !!auth?.employee;

  const isDev = checkDevMode();
  const { items, setIsOpen } = useFavorites();

  useEffect(() => {
    localStorage.setItem('app_locale', locale);
  }, [locale]);

  const handleLanguageChange = (newLocale: string) => {
    setLocale(newLocale);
    window.location.reload();
  };

  useEffect(() => {
    document.body.style.overflow = isMobileMenuOpen ? 'hidden' : 'unset';
    return () => { document.body.style.overflow = 'unset'; };
  }, [isMobileMenuOpen]);

  const visibleNavItems = siteConfig.headerNav.filter(item => {
    if (item.href === route('bootstrap') || item.href === route('services')) {
      return isDev;
    }
    return true;
  });

  return (
    <>
      <header className="w-full z-50 bg-white sticky top-0 shadow-sm border-b border-[#E2E6EA]">
        <TopBar
          locale={locale}
          onLanguageChange={handleLanguageChange}
          isDev={isDev}
          isEmployee={isEmployee}
        />

        <div className="max-w-[1440px] mx-auto px-4 md:px-8 h-20 flex justify-between items-center gap-4">
          <Logo variant="dark-solid" />

          <NavBar items={visibleNavItems} />

          {(isDev || isEmployee) && (
            <a href="/admin" target="_blank" rel="noreferrer" className="hidden xl:flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 border border-[#E2E6EA] text-[#08274D] text-xs font-bold uppercase tracking-wider transition-all active:scale-[0.98]">
              <ShieldCheck className="w-4 h-4 text-emerald-600" />
              Админ-панель
            </a>
          )}

          <div className="flex items-center gap-4">
            {/* Кнопка поиска в стиле каталога камня */}
            <button
              aria-label="Поиск"
              className="p-2.5 text-[#08274D] hover:text-[#EF5042] hover:bg-slate-50 rounded-xl transition-all cursor-pointer flex items-center justify-center border border-transparent hover:border-[#E2E6EA]"
            >
              <Search className="w-5 h-5 stroke-[2]" />
            </button>

            <button
              onClick={() => setIsOpen(true)}
              className="relative p-2.5 bg-slate-50 hover:bg-slate-100 border border-[#E2E6EA] rounded-xl transition-all cursor-pointer text-[#08274D] flex items-center justify-center"
            >
              <Heart className="w-5 h-5 stroke-[1.8]" />
              {items.length > 0 && (
                <span className="absolute -top-1 -right-1 bg-[#EF5042] text-white text-[9px] font-black w-4.5 h-4.5 flex items-center justify-center rounded-full px-0.5 border-2 border-white">
                  {items.length}
                </span>
              )}
            </button>

            {isDev && (
              <a href="/docs/api" target="_blank" rel="noreferrer" className="hidden lg:flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 border border-[#E2E6EA] text-[#08274D] text-xs font-bold uppercase tracking-wider transition-all active:scale-[0.98]">
                <BookOpen className="w-4 h-4 text-[#08274D]" />
                API Docs
              </a>
            )}

            <button className="lg:hidden p-2 text-[#08274D] hover:text-[#EF5042]" onClick={() => setIsMobileMenuOpen(true)}>
              <Menu className="w-6 h-6" />
            </button>
          </div>
        </div>
      </header>

      <MobileMenu
        isOpen={isMobileMenuOpen}
        onClose={() => setIsMobileMenuOpen(false)}
        items={visibleNavItems}
        isDev={isDev}
      />
    </>
  );
}
