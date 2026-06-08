import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Menu, BookOpen, ShieldCheck } from 'lucide-react';
import { Logo } from '@/shared/components/ui/Logo';
import { siteConfig } from '@/shared/config/site';

import TopBar from './ui/TopBar';
import NavBar from './ui/NavBar';
import MobileMenu from './ui/MobileMenu';

export default function Header() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);


  const [locale, setLocale] = useState(localStorage.getItem('app_locale') || 'ru');

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

  return (
    <>
      <header className="w-full z-50 bg-[#16191B] sticky top-0 shadow-lg border-b border-white/5">
        <TopBar locale={locale} onLanguageChange={handleLanguageChange} />

        <div className="max-w-[1400px] mx-auto px-4 md:px-8 h-20 flex justify-between items-center">
          <Logo variant="orange-dark" />
          <NavBar items={siteConfig.headerNav} />

          <a href="/admin" target="_blank" rel="noreferrer" className="hidden lg:flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/[0.04] border border-white/10 hover:bg-white/[0.08] text-white text-sm font-medium transition-all active:scale-[0.98]">
            <ShieldCheck className="w-4 h-4 text-emerald-400" />
            Админ-панель
          </a>

          <div className="flex items-center gap-4">
            <a href="/docs/api" target="_blank" rel="noreferrer" className="hidden lg:flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/[0.04] border border-white/10 hover:bg-white/[0.08] text-white text-sm font-medium transition-all active:scale-[0.98]">
              <BookOpen className="w-4 h-4 text-primary" />
              API Docs
            </a>

            <button className="lg:hidden p-2 text-white/80 hover:text-white" onClick={() => setIsMobileMenuOpen(true)}>
              <Menu className="w-6 h-6" />
            </button>
          </div>
        </div>
      </header>

      <MobileMenu
        isOpen={isMobileMenuOpen}
        onClose={() => setIsMobileMenuOpen(false)}
        items={siteConfig.headerNav}
      />
    </>
  );
}
