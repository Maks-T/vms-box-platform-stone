import React, { useState, useEffect } from 'react';
import { Menu, BookOpen, ShieldCheck, Heart } from 'lucide-react';
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
      <header className="w-full z-50 bg-white sticky top-0 border-b border-border shadow-sm">
        <TopBar
          locale={locale}
          onLanguageChange={handleLanguageChange}
          isDev={isDev}
          isEmployee={isEmployee}
        />

        <div className="max-w-[1400px] mx-auto px-4 md:px-8 h-20 flex justify-between items-center">
          <Logo variant="orange-dark" imgClassName="h-14 md:h-16 w-auto" />

          <NavBar items={visibleNavItems} />

          {(isDev || isEmployee) && (
            <a
              href="/admin"
              target="_blank"
              rel="noreferrer"
              className="hidden lg:flex items-center gap-2 px-3.5 py-2 rounded border border-zinc-300 hover:border-zinc-900 bg-white text-zinc-900 text-xs font-semibold transition-all active:scale-[0.98]"
            >
              <ShieldCheck className="w-4 h-4 text-emerald-600" />
              Админ-панель
            </a>
          )}

          <div className="flex items-center gap-3">
            <button
              onClick={() => setIsOpen(true)}
              className="relative p-2.5 bg-white hover:bg-zinc-50 border border-zinc-200 hover:border-zinc-400 rounded transition-all cursor-pointer text-zinc-800 flex items-center justify-center"
              title="Избранное"
            >
              <Heart className="w-4.5 h-4.5 stroke-[1.8]" />
              {items.length > 0 && (
                <span className="absolute -top-1 -right-1 bg-zinc-900 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-sm">
                  {items.length}
                </span>
              )}
            </button>

            {isDev && (
              <a
                href="/docs/api"
                target="_blank"
                rel="noreferrer"
                className="hidden lg:flex items-center gap-2 px-3.5 py-2 rounded border border-zinc-200 hover:border-zinc-400 bg-white text-zinc-700 text-xs font-medium transition-all"
              >
                <BookOpen className="w-4 h-4 text-zinc-500" />
                API Docs
              </a>
            )}

            <button className="lg:hidden p-2 text-zinc-700 hover:text-zinc-900" onClick={() => setIsMobileMenuOpen(true)}>
              <Menu className="w-5 h-5" />
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