import React, { useState, useEffect } from 'react';
import { Menu, Heart } from 'lucide-react';
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
      <header className="sticky top-0 z-50 bg-[#212B36] text-white shadow-md w-full">
        {/* Верхний ряд: Логотип, поиск, телефон с шоурумом и CTA */}
        <TopBar
          locale={locale}
          onLanguageChange={handleLanguageChange}
          isDev={isDev}
          isEmployee={isEmployee}
        />

        {/* Нижний ряд: Основное меню навигации */}
        <div className="border-t border-[#2D3A49] bg-[#212B36]/95 backdrop-blur-md hidden lg:block">
          <div className="max-w-[1412px] mx-auto px-4 md:px-8 flex items-center justify-between">
            <NavBar items={visibleNavItems} />

            {/* Кнопка "В избранное" */}
            <button
              onClick={() => setIsOpen(true)}
              aria-label="Избранное"
              className="relative p-2 text-gray-300 hover:text-[#25CED1] transition-colors cursor-pointer flex items-center"
            >
              <Heart className="w-4 h-4" />
              {items.length > 0 && (
                <span className="absolute -top-1 -right-1 bg-[#ED1C24] text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full">
                  {items.length}
                </span>
              )}
            </button>
          </div>
        </div>

        {/* Кнопка вызова мобильного меню на экранах < lg */}
        <div className="lg:hidden absolute right-4 top-5 flex items-center gap-3">
          <button
            className="p-2 text-gray-300 hover:text-white cursor-pointer"
            onClick={() => setIsMobileMenuOpen(true)}
            aria-label="Меню"
          >
            <Menu className="w-6 h-6" />
          </button>
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